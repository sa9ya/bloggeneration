<?php
namespace Core;

class Controller extends Core {
	protected function response($data, $status = 200, $headers = []): void {
        header("Content-Type: application/json");
		foreach ($headers as $key => $value) {
			header("{$key}: {$value}");
		}
        http_response_code($status);
        echo json_encode($data);
        exit;
    }
}