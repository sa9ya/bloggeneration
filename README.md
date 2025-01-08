# Blog Generation Web Application
Welcome to the Blog Generation Web Application! This test project is a PHP-based web application designed to streamline the process of creating, managing, and publishing blog content with AI. Everything managing by telegram bot. From telegram you can set up this bot and start the cron jobs to generates blog post for meta products, telegram or websites.
### Features
* **Dynamic Blog Creation**: Easily create and manage blog posts and images for them.
* **User Management**: Manage user settings and profiles.
* **Caching with Redis (File caching)**: Boost performance using Redis for data caching.
* **Modular Architecture**: Flexible and scalable core structure.
* **MVC Pattern**: Organized with models, views, and controllers.
* **API Integration**: Seamless integration with external APIs (OpenAI, Telegram).
### Project Structure
* **core/**: Application core files (controllers, models, database handlers, etc.)
* **app/**: User-defined models, controllers, and views.
* **config/**: Application configuration files.
* **public/**: Publicly accessible files (index.php, .htaccess, etc.).
* **tests/**: Unit and API tests.
* **vendor/**: Third-party dependencies (managed by Composer).
### Requirements
PHP 8.0 or higher  
Redis Server (not required)  
Composer (for dependency management)  
Web Server (Apache/Nginx)  
### Installation
Clone the repository:
```
git clone https://github.com/yourusername/bloggeneration.git
```
Navigate to the project directory:
```
cd bloggeneration
```
Install dependencies:
```
composer install
```
**Configure the application:**  
rename `config-test` folder to `config`

### Testing

Run tests to ensure the application is functioning correctly:
```
phpunit tests/
```

Happy coding!
