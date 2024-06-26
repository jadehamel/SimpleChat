# SimpleChat

SimpleChat is a basic direct messaging application built with PHP and MySQL. It allows users to register, log in, and send messages to other registered users using their usernames.

## Features

- User registration and login system
- Send and receive messages to/from other users
- Simple and clean interface

## Requirements

- PHP 7.0 or higher
- MySQL 5.6 or higher
- Web server (Apache, Nginx, etc.)

## Installation

1. **Clone the repository:**
    ```bash
    git clone https://github.com/jadehamel/simplechat.git
    cd simplechat
    ```

2. **Setup the Database:**
    - Create a new MySQL database and user.
    - Import the provided SQL schema to create necessary tables.

    ```sql
    CREATE DATABASE simple_messenger;

    USE simple_messenger;

    CREATE TABLE users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL
    );

    CREATE TABLE messages (
        id INT AUTO_INCREMENT PRIMARY KEY,
        sender_id INT NOT NULL,
        receiver_id INT NOT NULL,
        message TEXT NOT NULL,
        timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (sender_id) REFERENCES users(id),
        FOREIGN KEY (receiver_id) REFERENCES users(id)
    );
    ```

3. **Configure the Database Connection:**
    - Open `config.php` and update the database connection settings to match your MySQL credentials.

    ```php
    <?php
    $host = 'localhost';
    $db = 'simple_messenger';
    $user = 'your_db_user';
    $pass = 'your_db_password';

    try {
        $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {
        die("Could not connect to the database $db :" . $e->getMessage());
    }
    ?>
    ```

4. **Run the Application:**
    - Place the project files in your web server's root directory (e.g., `htdocs` for XAMPP or `www` for WAMP).
    - Start your web server and navigate to the project URL (e.g., `http://localhost/simplechat`).

## Usage

1. **Register a new user:**
    - Go to `http://localhost/simplechat/register.php`
    - Enter a username and password to create a new account.

2. **Login:**
    - Go to `http://localhost/simplechat/login.php`
    - Enter your registered username and password to log in.

3. **Send a Message:**
    - After logging in, use the chat interface to send messages to other users by entering their username.

## Notes

- This is a simple implementation for educational purposes. For a production application, ensure proper input validation, error handling, and security measures like HTTPS and session management.
- The UI is very basic; consider using CSS frameworks like Bootstrap for a better appearance.
- You might want to add features like real-time messaging with WebSockets for a more advanced messenger.

## License

This project is licensed under the MIT License. See the LICENSE file for details.
