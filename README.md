# CADM Document Management System

A secure, role-based document management platform for organizations and universities.  
**Features:** file upload/download, approval workflow, trash/recycle bin, audit logs, user management, and access control.

---

## 🚀 Features

- **User Authentication**: Secure login for Admin, Manager, and Employee roles.
- **Role-Based Access**: Fine-grained permissions for uploading, modifying, deleting, and sharing files.
- **File Management**: Upload, download, approve, and delete files with a modern UI.
- **Trash/Recycle Bin**: Restore or permanently delete files.
- **Audit Logs**: Track all user actions for transparency and compliance.
- **User & Access Control**: Manage users, roles, and permissions from the admin panel.
- **Responsive Design**: Works on desktop and mobile.
- **Database-Backed**: MySQL/MariaDB for robust data storage.

---

## 🗂️ Project Structure

```
succlogin/
│
├── css/                # Tailwind CSS and custom styles
├── DB/                 # Database folder (see below)
│   └── succlogin/      # MySQL table files (for local dev, not for production)
├── uploads/            # Uploaded files (documents, spreadsheets, etc.)
├── *.php               # Main PHP application files
├── *.html              # Landing, policy, about, contact, etc.
├── README.md           # This file
└── ...
```

---

## ⚡ Quick Start

### 1. Clone the Repository

```bash
git clone https://github.com/doudi230604/License_project.git
cd License_project
```

### 2. Database Setup

- **Option 1: Use Provided DB Folder (For Local Dev)**
  - Copy the entire `DB/succlogin` folder into your MySQL data directory (e.g., `/var/lib/mysql/`).
  - Make sure MySQL/MariaDB is stopped before copying.
  - Set correct permissions:  
    ```bash
    sudo chown -R mysql:mysql /var/lib/mysql/succlogin
    sudo systemctl start mysql
    ```
  - The database will be available as `succlogin`.

- **Option 2: Import SQL (Recommended for Production)**
  - If you have a `.sql` dump, import it:
    ```bash
    mysql -u root -p < succlogin.sql
    ```

### 3. Configure Database Connection

- Edit [`config.php`](config.php) if your MySQL credentials differ:
  ```php
  $host = 'localhost';
  $db   = 'succlogin';
  $user = 'root';
  $pass = '';
  ```

### 4. Install Dependencies

- No Composer dependencies required (pure PHP + MySQL).
- For CSS, Tailwind is included as a static file.

### 5. Run the Application

- Place the project in your web server root (e.g., `/var/www/html/succlogin`).
- Access via:  
  `http://localhost/succlogin/home.html` (landing)  
  `http://localhost/succlogin/login2.php` (login)

---

## 📝 Usage

- **Login** with your credentials.
- **Admins** can manage users, roles, and permissions.
- **Managers/Employees** can upload, download, and manage files as allowed by their role.
- **Trash**: Deleted files go to Trash and can be restored or permanently deleted.
- **Audit Logs**: View all actions for accountability.

---

## 📁 Important Notes

- **DB Folder**:  
  - The `DB/succlogin` folder contains MySQL table files.  
  - Only use this for local development with compatible MySQL versions.
  - For production, use SQL dumps and migrations.

- **Uploads Folder**:  
  - All uploaded files are stored in `/uploads`.
  - Ensure this folder is writable by the web server.

- **Security**:  
  - Change default MySQL passwords.
  - Use HTTPS in production.
  - Regularly backup your database and uploads.

---

## 🤝 Contributing

Pull requests are welcome!  
Open an issue to discuss new features or bug fixes.

---

## 📄 License

MIT License. See [`LICENSE`](LICENSE).

---

## 📞 Support

- [Contact Us](contactus.html)
- [Policies](policies.html)
- [Terms of Service](termesService.html)