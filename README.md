# 📂 CADM – Document Management System

A secure, role-based document management platform for organizations and universities.

> Upload, manage, approve, and audit documents with fine-grained control over user access.

---

## ✨ Key Features

- 🔐 **User Authentication** — Login system with Admin, Manager, and Employee roles
- 🛡️ **Role-Based Access Control** — Precise permissions for file actions (upload, delete, modify)
- 📁 **File Management** — Upload/download with an intuitive interface
- 🗑️ **Trash & Recovery** — Recover or permanently delete files from the trash
- 📜 **Audit Logs** — Track all user actions for transparency
- 👥 **User & Permission Management** — Admin panel for managing users and access levels
- 📱 **Responsive UI** — Works seamlessly on desktop and mobile
- 🛢️ **MySQL/MariaDB Backend** — Reliable and scalable database support

---

## 📁 Project Structure

```
succlogin/
│
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

### 1️⃣ Clone the Project

```bash
git clone https://github.com/doudi230604/License_project.git
cd License_project
```

---

### 2️⃣ Set Up the Database

#### Option A: Use Pre-built MySQL Files (Local Dev Only)

> ⚠️ Only for compatible MySQL installations!

**Linux (Ubuntu):**
```bash
sudo systemctl stop mysql
sudo cp -r DB/succlogin /var/lib/mysql/
sudo chown -R mysql:mysql /var/lib/mysql/succlogin
sudo systemctl start mysql
```

**Windows (XAMPP):**
1. Stop MySQL using XAMPP Control Panel.
2. Copy `DB/succlogin` to `C:\xampp\mysql\data`.
3. Restart MySQL from XAMPP Control Panel.

#### Option B: Import SQL Dump (Preferred for Production)

```bash
mysql -u root -p < succlogin.sql
```

---

### 3️⃣ Configure Database Connection

Edit `config.php` if needed:
```php
$host = 'localhost';
$db   = 'succlogin';
$user = 'root';
$pass = '';
```

---

### 4️⃣ Serve the App

**Linux (XAMPP):**
```bash
sudo cp -r License_project /opt/lampp/htdocs/succlogin
# or if XAMPP is in your home directory:
sudo cp -r License_project ~/xampp/htdocs/succlogin
```

**Windows (XAMPP):**
Copy the project folder to `C:\xampp\htdocs\succlogin`.

Open your browser and visit:
- [http://localhost/succlogin/home.html](http://localhost/succlogin/home.html) — Landing page  
- [http://localhost/succlogin/login2.php](http://localhost/succlogin/login2.php) — Login page

---

## 🧪 Usage

- **Admins:** Add/remove users, manage roles and permissions
- **Managers/Employees:** Upload, download, approve files
- **Trash:** Deleted files are recoverable until permanently removed
- **Audit:** Track user actions for security and compliance

---

## 📌 Notes

- **Database Folder:** Use only for local development; avoid raw DB files in production.
- **Uploads Folder:** Ensure `uploads/` is writable by the web server.
- **Security Tips:**
  - Change default passwords
  - Enable HTTPS
  - Regularly back up data

---

## 🤝 Contributing

Pull requests and suggestions are welcome!  
Please create an issue to propose changes or report bugs.

---

## 🪪 License

This project is licensed under the [MIT License](LICENSE).

---

## 📬 Contact & Support

- [Contact Page](contactus.html)
- [Policy Page](policies.html)
- [Terms of Service](termesService.html)