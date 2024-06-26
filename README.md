# Simple PHP Messenger

This is a simple direct messaging application built with PHP and MySQL. It allows users to register, login, send messages to each other, and view their message history.

## Features

- **User Registration:** Users can register with a username and password.
- **User Login:** Secure login system using password hashing.
- **Direct Messaging:** Send messages to other registered users.
- **Message History:** View message history with other users.

## Security Measures Implemented

1. **Password Hashing:** User passwords are hashed using PHP's `password_hash()` function to protect against password theft.
2. **Prepared Statements:** All database queries use prepared statements (`PDO::prepare`) to prevent SQL injection attacks.
3. **Session Management:** Sessions are securely managed with `session_start()` and validated to ensure users are authenticated (`$_SESSION['user_id']`).
4. **Input Sanitization:** User inputs are sanitized using `FILTER_SANITIZE_STRING` to prevent XSS attacks.
5. **Rate Limiting:** Implemented rate limiting on login attempts to mitigate brute force attacks.
6. **HTTPS:** Ensure the application runs over HTTPS to encrypt data transmission.

## Requirements

- PHP 7.x or higher
- MySQL database
- Apache or Nginx server

## Installation

1. Clone or download the repository.
2. Import the `database.sql` file into your MySQL database.
3. Configure database credentials in `config.php`.
4. Ensure your web server (Apache or Nginx) is configured to serve PHP files.

## Usage

1. Navigate to the application URL.
2. Register with a username and password.
3. Login using your registered credentials.
4. Start a new chat by entering the username of the recipient.
5. Send and receive messages with other registered users.

## Example

```php
// Example of sending a message in chat.php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitize and validate receiver and message inputs
    $receiver = filter_var(trim($_POST['receiver']), FILTER_SANITIZE_STRING);
    $message = filter_var(trim($_POST['message']), FILTER_SANITIZE_STRING);
    $sender_id = $_SESSION['user_id'];

    // Retrieve receiver's user ID from database
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = :username");
    $stmt->execute(['username' => $receiver]);
    $receiver_id = $stmt->fetchColumn();

    // If receiver exists, insert message into database
    if ($receiver_id) {
        $stmt = $pdo->prepare("INSERT INTO messages (sender_id, receiver_id, message) VALUES (:sender_id, :receiver_id, :message)");
        $stmt->execute(['sender_id' => $sender_id, 'receiver_id' => $receiver_id, 'message' => $message]);
    } else {
        echo "User not found";
    }
}
```

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
