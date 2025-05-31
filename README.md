Sure Wael! Here's a clean and bug-free version of your README with improved formatting and correctly rendered code blocks:

---

# 📂 CADM – Document Management System

A secure, role-based document management platform designed for organizations and universities.

> Upload, manage, approve, and audit documents with fine-grained control over user access.

---

## ✨ Key Features

* 🔐 **User Authentication** – Login system with Admin, Manager, and Employee roles.
* 🛡️ **Role-Based Access Control** – Precise permissions for file actions (upload, delete, modify).
* 📁 **File Management** – Upload/download with an intuitive interface.
* 🗑️ **Trash & Recovery** – Recover or permanently delete files from the trash.
* 📜 **Audit Logs** – Track all user actions for transparency.
* 👥 **User & Permission Management** – Admin panel for managing users and access levels.
* 📱 **Responsive UI** – Works seamlessly on desktop and mobile.
* 🛢️ **MySQL/MariaDB Backend** – Reliable and scalable database support.

---

## 📁 Project Structure

```text
succlogin/
├── css/             # Tailwind CSS and custom styles
├── DB/
│   └── succlogin/   # MySQL table files (raw data files for local dev)
├── uploads/         # Uploaded documents and files
├── *.php            # Core PHP app files
├── *.html           # Static pages (landing, about, contact, etc.)
└── README.md        # This file
```

---

## 🚀 Getting Started

### 1. Clone the Project

```bash
git clone https://github.com/doudi230604/License_project.git
cd License_project
```

### 2. Set Up the Database

#### Option A: Use Pre-built MySQL Files (Local Dev Only)

> ⚠️ Only for compatible MySQL installations!

```bash
# Stop MySQL first
sudo systemctl stop mysql

# Copy the raw DB folder
sudo cp -r DB/succlogin /var/lib/mysql/

# Set correct ownership
sudo chown -R mysql:mysql /var/lib/mysql/succlogin

# Restart MySQL
sudo systemctl start mysql
```

#### Option B: Import SQL Dump (Preferred for Production)

```bash
mysql -u root -p < succlogin.sql
```

---

### 3. Configure DB Connection

Update your credentials in [`config.php`](config.php):

```php
$host = 'localhost';
$db   = 'succlogin';
$user = 'root';
$pass = '';
```

---

### 4. Serve the App

Place the project in your XAMPP server directory, e.g.:

```bash
sudo cp -r License_project /opt/lampp/htdocs/succlogin
```

or:

```bash
sudo cp -r License_project ~/xampp/htdocs/succlogin
```

Open in your browser:

* `http://localhost/succlogin/home.html` – Landing page
* `http://localhost/succlogin/login2.php` – Login page

---

## 🧪 How to Use

* **Admins**: Add/remove users, manage roles and permissions.
* **Managers/Employees**: Upload, download, approve files.
* **Trash**: Deleted files are recoverable until permanently removed.
* **Audit**: Track user actions for security and compliance.

---

## 📌 Notes

* **Database Folder**: Use only for local development; avoid using raw DB files in production.
* **Uploads Folder**: Ensure `uploads/` is writable by the web server.
* **Security Tips**:

  * Change default passwords
  * Enable HTTPS
  * Regularly back up data

---

## 🤝 Contributing

Open to pull requests and suggestions!
Please create an issue to propose changes or report bugs.

---

## 🪪 License

This project is licensed under the [MIT License](LICENSE).

---

## 📬 Contact & Support

* [Contact Page](contactus.html)
* [Policy Page](policies.html)
* [Terms of Service](termesService.html)

---

Let me know if you'd like me to help with badges or a UI screenshot!
