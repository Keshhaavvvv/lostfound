# Campus Lost & Found System 🎓🔍

A web-based platform for college students to report lost items and claim found ones. Features secure login, real-time chat, email notifications, and an admin dashboard.

---

## 📂 Project Setup Guide

### 1. Prerequisites
To run this project on any computer, you need:
* **XAMPP** (or WAMP/MAMP) installed.
* A Web Browser (Chrome, Edge, etc.).
* An active Internet connection (required for sending emails via Gmail).

---

### 2. Installation Steps

#### **Step A: Move Files**
1.  Locate the `htdocs` folder inside your XAMPP installation (usually `C:\xampp\htdocs`).
2.  Create a new folder named `lostfound`.
3.  Paste all the project files (PHP, CSS, JS) into this folder.

#### **Step B: Setup Database**
1.  Open XAMPP Control Panel and start **Apache** and **MySQL**.
2.  Go to your browser and type: `http://localhost/phpmyadmin`.
3.  Click **New** and create a database named: `lostfound_db`.
4.  Click **Import** > **Choose File** > Select the `database.sql` file provided with this project.
    * *(Note: If you haven't exported your database yet, go to your current phpMyAdmin, select `lostfound_db`, click Export, and save the file).*
5.  Click **Go** to import the tables.

#### **Step C: Configure Email (⚠️ Important)**
To prevent using your personal Gmail when sharing the code:
1.  Open the file **`mail_config.php`**.
2.  Find the lines for `$mail->Username` and `$mail->Password`.
3.  **Change these credentials** to the new user's Gmail and App Password.

```php
// mail_config.php
$mail->Username   = 'new_email@gmail.com'; // Change this
$mail->Password   = 'xxxx xxxx xxxx xxxx'; // Change this App Password