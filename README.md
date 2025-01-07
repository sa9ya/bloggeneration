<h1>Blog Generation Web Application</h1>
<p>Welcome to the Blog Generation Web Application! This test project is a PHP-based web application designed to streamline the process of creating, managing, and publishing blog content with AI. Everything managing by telegram bot. From telegram you can set up this bot and start the cron jobs to generates blog post for meta products, telegram or websites.</p>
<hr>
<h3>Features</h3>
<ul>
<li><b>Dynamic Blog Creation</b>: Easily create and manage blog posts and images for them.</li>
<li><b>User Management</b>: Manage user settings and profiles.</li>
<li><b>Caching with Redis (File caching)</b>: Boost performance using Redis for data caching.</li>
<li><b>Modular Architecture</b>: Flexible and scalable core structure.</li>
<li><b>MVC Pattern</b>: Organized with models, views, and controllers.</li>
<li><b>API Integration</b>: Seamless integration with external APIs (OpenAI, Telegram).</li>
</ul>
<h3>Project Structure</h3>
<ul>
<li><b>core/</b>: Application core files (controllers, models, database handlers, etc.)</li>
<li><b>app/</b>: User-defined models, controllers, and views.</li>
<li><b>config/</b>: Application configuration files.</li>
<li><b>public/</b>: Publicly accessible files (index.php, .htaccess, etc.).</li>
<li><b>tests/</b>: Unit and API tests.</li>
<li><b>vendor/</b>: Third-party dependencies (managed by Composer).</li>
</ul>
<h3>Requirements</h3>
<p>PHP 8.0 or higher<br>
Redis Server (not required)<br>
Composer (for dependency management)<br>
Web Server (Apache/Nginx)</p>
<h3>Installation</h3>
<p>Clone the repository:</br>
git clone https://github.com/yourusername/bloggeneration.git</br>
Navigate to the project directory:</br>
cd bloggeneration</br>
rename config-test folder to config</br><p>
<h4>Install dependencies:</h4>
<p>composer install
Configure the application:
Copy the .env.example to .env and update the necessary configurations.
cp .env.example .env

Run the application:

php -S localhost:8000 -t public

Usage

Access the application at http://localhost:8000

Log in and start creating blog posts.

Manage user settings from the admin panel.

Testing

Run tests to ensure the application is functioning correctly:

phpunit tests/

Happy coding!</p>
