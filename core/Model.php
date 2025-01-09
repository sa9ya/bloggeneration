<?php
namespace Core;

use App;
use PDO;

class Model extends ModelBase {
	protected static $instance = null;
	protected static PDO $pdo;
	protected string $table;
	protected array $attributes = [];
	protected array $conditions = [];
	protected array $joins = [];
	protected static string $tablePrefix = '';
	protected array $aliasMap = [];

	public function __construct() {
		self::$pdo = Database::getConnection();
		self::$tablePrefix = App::$app->config->get('db')['table_prefix'];
	}

	protected function getClassName(): string {
		return (new \ReflectionClass($this))->getShortName();
	}

	protected function getTableNameFromClass(): string {
		return $snake = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $this->getClassName()));
	}

	protected function generateAlias(): string {
		$className = (new \ReflectionClass(static::class))->getShortName();
		preg_match_all('/[A-Z]/', $className, $matches);
		return strtolower(implode('', $matches[0]));
	}

	protected static function getInstance(): Model {
		if (self::$instance === null) {
			self::$instance = new static();
		}
		return self::$instance;
	}

	protected function getTableName(): string {
		if(!empty($this->table)) {
			return self::$tablePrefix . $this->table;
		}
		return self::$tablePrefix . $this->getTableNameFromClass();
	}

	public static function find(): static {
		return new static();
	}

	public static function findOne(array $conditions): Model {
		return self::find()->where($conditions)->one();
	}

	/**
	 * @throws \ReflectionException
	 */
	public function leftJoin(string $modelClass, array $on): self {
		$onConditions = [];
		$instance = self::getInstance();
		$joinedInstance = new $modelClass();
		$this->aliasMap[$joinedInstance->generateAlias()] = $modelClass;
		$modelTable = $joinedInstance->getTableName() . " AS " . $joinedInstance->generateAlias();
		foreach ($on as $left => $right) {
			$onConditions[] = $joinedInstance->generateAlias() . ".$left = ".$instance->generateAlias().".$right";
		}
		$this->joins[] = "LEFT JOIN $modelTable ON " . implode(' AND ', $onConditions);
		return $this;
	}

	public function where(array $conditions): self {
		$this->conditions = $conditions;
		return $this;
	}

	protected function buildSelectQuery(): string {
		$alias = $this->generateAlias();
		$sql = "SELECT {$alias}.*";

		foreach ($this->joins as $join) {
			preg_match('/AS (\w+)/', $join, $matches);
			if (isset($matches[1])) {
				$sql .= ", {$matches[1]}.*";
			}
		}

		$sql .= " FROM {$this->getTableName()} AS {$alias}";
		$sql .= ' ' . implode(' ', $this->joins);

		if (!empty($this->conditions)) {
			$conditionParts = [];
			foreach ($this->conditions as $key => $value) {
				$conditionParts[] = "$alias.$key = :$key";
			}
			$sql .= ' WHERE ' . implode(' AND ', $conditionParts);
		}

		return $sql;
	}

	protected function setRelatedObjects($obj, $data = []) {
		foreach ($this->aliasMap as $className) {
			$cls = new $className();
			if($cls->hasMatchingAttributes($data)) {
				$attrName = lcfirst($cls->getClassName());
				$obj->$attrName = $cls;
				$obj->$attrName->loadExistingAttributes($data);
			}
		}
		return $obj;
	}

	/**
	 * @param array $result
	 * @return static
	 */
	protected function hydrateResult(array $result): static {
        $instance = $this->setRelatedObjects(new static(), $result);
		$instance->loadExistingAttributes($result);
		return $instance;
	}

	public function save(): ?self {
		$now = date('Y-m-d H:i:s');
		$this->attributes['date_updated'] = $now;
		if (!isset($this->attributes['id'])) {
			$this->attributes['date_created'] = $now;
		}

		$columns = $this->getTableColumns();
		$filteredAttributes = array_intersect_key($this->attributes, array_flip($columns));
		$placeholders = array_map(fn($key) => ":$key", array_keys($filteredAttributes));

		if (isset($this->attributes['id'])) {
			$set = implode(", ", array_map(fn($key) => "$key = :$key", array_keys($filteredAttributes)));
			$sql = "UPDATE {$this->getTableName()} SET $set WHERE id = :id";
		} else {
			unset($columns[0]);
			$sql = "INSERT INTO {$this->getTableName()} (" . implode(',', array_keys($filteredAttributes)) . ") 
                VALUES (" . implode(',', $placeholders) . ")";
		}

		try {
			$result = self::$pdo->prepare($sql)->execute($filteredAttributes);

			if (!isset($this->attributes['id']) && $result) {
				$this->attributes['id'] = self::$pdo->lastInsertId();
			}

			return $this;
		} catch (\PDOException $e) {
			Logger::error('Save failed', [
				'error' => $e->getMessage(),
				'attributes' => $filteredAttributes,
				'sql' => $sql
			]);
			return null;
		}
	}

	protected function getTableColumns(): array {
		static $cache = [];

		$tableName = $this->getTableName();

		if (isset($cache[$tableName])) {
			return $cache[$tableName];
		}

		try {
			$stmt = self::$pdo->prepare("DESCRIBE {$tableName}");
			$stmt->execute();
			$columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
			$cache[$tableName] = $columns;
			return $columns;
		} catch (\PDOException $e) {
			Logger::error("Failed to fetch table columns for {$tableName}", $e);
			return [];
		}
	}

	/**
	 * @return static|null
	 */
	public function one(): ?static {
		$sql = $this->buildSelectQuery();

		try {
			$stmt = self::$pdo->prepare($sql);
			$stmt->execute($this->conditions);
			$result = $stmt->fetch(PDO::FETCH_ASSOC);
			return $result ? $this->hydrateResult($result) : null;
		} catch (\PDOException $e) {
			Logger::error('Select query failed', $e);
			return null;
		}
	}

	public function all(bool $asArray = false): array {
		$sql = $this->buildSelectQuery();

		try {
			$stmt = self::$pdo->prepare($sql);
			$stmt->execute($this->conditions);
			$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

			if ($asArray) {
				return $results;
			}

			$instances = [];
			foreach ($results as $result) {
				$instances[] = $this->hydrateResult($result);
			}

			return $instances;
		} catch (\PDOException $e) {
			Logger::error('Select query failed', $e);
			return [];
		}
	}

	public function toArray(): array {
		return get_object_vars($this);
	}
}