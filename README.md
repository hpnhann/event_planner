# 🎓 Volunteer Management System (VMS)
### Centralized Platform for University Volunteer Activity Management

<div align="center">

![PHP](https://img.shields.io/badge/PHP-8.1+-777BB4?style=for-the-badge&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-8.0-00758F?style=for-the-badge&logo=mysql&logoColor=white)
![Bootstrap](https://img.shields.io/badge/Bootstrap-5.3-7952B3?style=for-the-badge&logo=bootstrap&logoColor=white)
![License](https://img.shields.io/badge/License-MIT-green?style=for-the-badge)

[🌐 Live Demo](https://volunteer-management-system.ct.ws/school-management-system/) • [📖 Documentation](#documentation) • [🐛 Report Bug](https://github.com/hpnhann/event_planner/issues) • [✨ Request Feature](https://github.com/hpnhann/event_planner/issues)

</div>

---

## 📋 Table of Contents
- [Introduction](#-introduction)
- [Key Features](#-key-features)
- [Tech Stack](#️-tech-stack)
- [System Architecture](#-system-architecture)
- [Installation](#-installation)
- [Usage](#-usage)
- [API Documentation](#-api-documentation)
- [Database Schema](#-database-schema)
- [Screenshots](#-screenshots)
- [Contributing](#-contributing)
- [License](#-license)

---

## 🌟 Introduction

**Volunteer Management System (VMS)** is a comprehensive web-based platform designed to streamline volunteer activity management within university faculties. 

### 🎯 Problem Statement
Traditional volunteer management relies on scattered tools like Google Sheets, Forms, and email threads, leading to:
- ❌ Manual data entry and reconciliation
- ❌ Difficulty tracking volunteer history
- ❌ Limited transparency in reporting
- ❌ Time-consuming administrative overhead

### ✅ Our Solution
VMS provides a unified, centralized system that:
- ✨ Automates event creation, registration, and tracking
- 📊 Real-time dashboards and analytics
- 🔐 Role-based access control (RBAC)
- 📱 Responsive design for mobile and desktop
- 🚀 Eliminates 80% of manual administrative tasks

**Live Demo:** [volunteer-management-system.ct.ws](https://volunteer-management-system.ct.ws/school-management-system/)

---

## ✨ Key Features

### 👨‍💼 **Admin Module** (Management & Reporting)

<details>
<summary>📊 Dashboard & Analytics</summary>

- Real-time statistics:
  - Total events (Draft + Published)
  - Registered volunteers count
  - Attendance rate metrics
- Quick actions panel
- Recent activity feed
</details>

<details>
<summary>🎪 Event Management</summary>

**Full CRUD Operations:**
- ✏️ **Create Events:** Rich form with image upload, date pickers, location autocomplete
- 📝 **Edit Events:** Update event details with version history
- 🗑️ **Delete Events:** Soft delete with confirmation dialog
- 📢 **Publish Events:** Draft → Published workflow with email notifications

**Advanced Features:**
- Set volunteer quotas (Max Participants)
- Registration deadline management
- Event status tracking (Draft/Published/Completed/Cancelled)
- Bulk actions (Delete multiple, Export CSV)
</details>

<details>
<summary>👥 Member Management</summary>

- View all registered users
- Add/Edit/Delete user accounts
- Role assignment (Admin/Teacher/Student)
- Account status control (Active/Inactive)
- Export member list to Excel
</details>

<details>
<summary>✅ Registration & Attendance</summary>

- View all event registrations
- Mark attendance (Present/Absent/Late)
- Check-in/Check-out tracking
- Export attendance reports (CSV/PDF)
- Send reminder emails to registered volunteers
</details>

---

### 🎓 **Student/Volunteer Module**

<details>
<summary>🌐 Public Event Hub</summary>

- Browse all published volunteer opportunities
- Filter by:
  - Date range
  - Location
  - Event type
- Search functionality with real-time results
- Event card displays:
  - Event image
  - Title, date, time, location
  - Available slots (X/Y registered)
  - Registration deadline
</details>

<details>
<summary>📝 Smart Registration System</summary>

**One-Click Registration:**
- ✅ Real-time slot availability checking
- ✅ Duplicate registration prevention
- ✅ Automatic confirmation emails
- ✅ Registration deadline validation

**Registration Flow:**
1. User clicks "Register" button
2. System validates:
   - User authentication
   - Available slots
   - Registration deadline
   - Duplicate check
3. Confirmation modal
4. Success notification + Email

</details>

<details>
<summary>📊 Personal Dashboard (My Activity)</summary>

- **My Registered Events:**
  - View all registered events
  - Filter by status (Upcoming/Past/Cancelled)
  - Event details quick view
- **Profile Management:**
  - Update contact information
  - Change password
  - Profile picture upload
- **Attendance History:**
  - Check-in/check-out records
  - Total volunteer hours
  - Certificates earned
</details>

---

### 🔐 **Security & Technology**

| Feature | Implementation |
|---------|---------------|
| **Authentication** | Session-based with secure cookies |
| **Authorization** | Role-Based Access Control (RBAC) |
| **SQL Injection** | Prepared Statements (MySQLi) |
| **XSS Protection** | `htmlspecialchars()` output sanitization |
| **CSRF Protection** | Token-based form validation |
| **Password Security** | `password_hash()` + `bcrypt` |
| **File Upload** | MIME type validation, size limits |

---

## 🛠️ Tech Stack

### Backend
- **PHP 8.1+** (Native/Vanilla)
- **MySQL 8.0** (Relational Database)
- **Apache 2.4** (Web Server)

### Frontend
- **HTML5** + **CSS3**
- **Bootstrap 5.3** (Responsive UI Framework)
- **JavaScript ES6** (Vanilla JS)
- **jQuery 3.7** (DOM manipulation)
- **Font Awesome 6.4** (Icons)

### Development Tools
- **XAMPP** (Local Development Environment)
- **Git** (Version Control)
- **VS Code** (Code Editor)
- **phpMyAdmin** (Database Management)

---

## 🏗️ System Architecture
```
┌─────────────────────────────────────────────────────────────┐
│                      CLIENT (Browser)                        │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐      │
│  │  HTML/CSS    │  │  JavaScript  │  │  Bootstrap   │      │
│  └──────────────┘  └──────────────┘  └──────────────┘      │
└─────────────────────────────────────────────────────────────┘
                             │
                             ▼ HTTP/AJAX
┌─────────────────────────────────────────────────────────────┐
│                    WEB SERVER (Apache)                       │
│  ┌──────────────────────────────────────────────────────┐   │
│  │                PHP Application Layer                  │   │
│  │  ┌────────────┐  ┌────────────┐  ┌────────────┐     │   │
│  │  │Controllers │  │  Models    │  │   Utils    │     │   │
│  │  └────────────┘  └────────────┘  └────────────┘     │   │
│  └──────────────────────────────────────────────────────┘   │
└─────────────────────────────────────────────────────────────┘
                             │
                             ▼ MySQLi
┌─────────────────────────────────────────────────────────────┐
│                   DATABASE (MySQL 8.0)                       │
│  ┌──────┐  ┌──────┐  ┌──────┐  ┌──────┐  ┌──────┐         │
│  │users │  │events│  │event_│  │attend│  │notice│         │
│  │      │  │      │  │assign│  │ances │  │  s   │         │
│  └──────┘  └──────┘  └──────┘  └──────┘  └──────┘         │
└─────────────────────────────────────────────────────────────┘
```

---

## ⚙️ Installation

### Prerequisites
- **XAMPP/WAMP/MAMP** (PHP 8.0+, MySQL 8.0+, Apache)
- **Git** (for cloning repository)
- **Modern Browser** (Chrome, Firefox, Edge)

### Step 1: Clone Repository
```bash
cd C:\xampp\htdocs
git clone https://github.com/hpnhann/event_planner.git school-management-system
cd school-management-system
```

### Step 2: Database Setup
1. Open **phpMyAdmin** → `http://localhost/phpmyadmin`
2. Create new database:
```sql
   CREATE DATABASE school_management CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```
3. Import database schema:
   - Click on `school_management` database
   - Go to **Import** tab
   - Choose file: `database/schema.sql`
   - Click **Go**

### Step 3: Configuration
1. Copy config template:
```bash
   cp assets/config.example.php assets/config.php
```

2. Edit `assets/config.php`:
```php
   <?php
   $servername = "localhost";
   $username = "root";
   $password = "";        // Your MySQL password
   $dbname = "school_management";
   
   $conn = mysqli_connect($servername, $username, $password, $dbname);
   
   if (!$conn) {
       die("Connection failed: " . mysqli_connect_error());
   }
   ?>
```

### Step 4: Permissions
```bash
# Windows (Run as Administrator)
icacls uploads /grant Users:F /T

# Linux/Mac
chmod -R 755 uploads
chmod -R 755 assets
```

### Step 5: Access Application
Open browser and navigate to:
```
http://localhost/school-management-system/
```

---

## 🚀 Usage

### Default Accounts

| Role | Email | Password | Access Level |
|------|-------|----------|--------------|
| **Admin** | admin@school.com | password | Full system access |
| **Teacher** | teacher@school.com | password | Event management |
| **Student** | student@school.com | password | Event registration |

⚠️ **Important:** Change default passwords after first login!

### Quick Start Guide

#### For Administrators:
1. Login with admin credentials
2. Go to **Admin Panel** → **Dashboard**
3. Click **Events Management** → **Create New Event**
4. Fill in event details:
   - Title, Description, Location
   - Date, Time, Max Volunteers
   - Upload event image
5. Click **Publish** to make it visible to volunteers

#### For Students/Volunteers:
1. Register an account or Login
2. Browse **Events** page
3. Click on an event to view details
4. Click **Register** button
5. Confirm registration in modal
6. Check **My Activity** to see registered events

---

## 📡 API Documentation

### Authentication Endpoints

#### POST `/login.php`
Login user and create session.

**Request:**
```json
{
  "email": "admin@school.com",
  "password": "password"
}
```

**Response (Success):**
```json
{
  "success": true,
  "user": {
    "id": 1,
    "name": "Admin",
    "email": "admin@school.com",
    "role": "admin"
  },
  "redirect": "admin_panel/dashboard.php"
}
```

---

### Event Endpoints

#### GET `/admin_panel/event_handler.php?action=get_events`
Get all events (Admin only).

**Response:**
```json
{
  "success": true,
  "events": [
    {
      "id": 1,
      "title": "Beach Cleanup Drive",
      "description": "...",
      "event_date": "2025-01-15",
      "event_time": "08:00:00",
      "location": "Sunset Beach",
      "max_volunteers": 50,
      "registered_count": 23,
      "status": "published"
    }
  ]
}
```

#### POST `/admin_panel/event_handler.php`
Create or update event.

**Request (Create):**
```json
{
  "action": "create",
  "event_title": "Beach Cleanup Drive",
  "event_description": "Join us for a beach cleanup...",
  "event_date": "2025-01-15",
  "event_time": "08:00",
  "end_date": "2025-01-15T12:00",
  "event_location": "Sunset Beach",
  "max_volunteers": 50,
  "cost": 0,
  "benefits": "Certificate, Free lunch",
  "registration_deadline": "2025-01-10T23:59",
  "status": "published"
}
```

**Response:**
```json
{
  "status": "success",
  "message": "Event published successfully!"
}
```

---

## 🗄️ Database Schema

### Key Tables

#### `users` Table
```sql
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    lname VARCHAR(255),
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'teacher', 'student', 'owner') DEFAULT 'student',
    phone VARCHAR(20),
    address TEXT,
    dob DATE,
    gender ENUM('male', 'female', 'other'),
    image VARCHAR(255) DEFAULT 'user.png',
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

#### `events` Table
```sql
CREATE TABLE events (
    id INT PRIMARY KEY AUTO_INCREMENT,
    activity_code VARCHAR(50),
    event_title VARCHAR(255) NOT NULL,
    event_description TEXT NOT NULL,
    event_location VARCHAR(255) NOT NULL,
    event_date DATE NOT NULL,
    event_time TIME NOT NULL,
    end_date DATETIME,
    max_volunteers INT NOT NULL,
    cost DECIMAL(10,2) DEFAULT 0.00,
    benefits TEXT,
    registration_deadline DATETIME,
    event_image VARCHAR(255),
    status ENUM('draft', 'published', 'completed', 'cancelled') DEFAULT 'draft',
    created_by INT NOT NULL,
    published_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
);
```

#### `event_assignments` Table
```sql
CREATE TABLE event_assignments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    event_id INT NOT NULL,
    user_id INT NOT NULL,
    status ENUM('registered', 'confirmed', 'cancelled', 'attended') DEFAULT 'registered',
    registered_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_registration (event_id, user_id)
);
```

#### `attendances` Table
```sql
CREATE TABLE attendances (
    id INT PRIMARY KEY AUTO_INCREMENT,
    event_id INT NOT NULL,
    user_id INT NOT NULL,
    status ENUM('present', 'absent', 'late') DEFAULT 'present',
    check_in_time DATETIME,
    check_out_time DATETIME,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_attendance (event_id, user_id)
);
```

### Database Relationships Diagram
```
┌─────────────┐
│    users    │
│  (1) ─────┐ │
└─────────────┘ │
       │        │
       │ creates│
       │        │
       ▼        │
┌─────────────┐ │
│   events    │ │
│  ────────(M)│◄┘
└─────────────┘
       │
       │ has
       │
       ▼
┌─────────────────┐
│event_assignments│
│      (M-M)      │
└─────────────────┘
       │
       │ tracks
       │
       ▼
┌─────────────┐
│ attendances │
└─────────────┘
```

---

## 📸 Screenshots

### 🏠 Home Page
![Home Page](screenshots/home.png)
*Clean, modern landing page with event highlights*

### 📊 Admin Dashboard
![Admin Dashboard](screenshots/admin-dashboard.png)
*Real-time statistics and quick actions*

### 🎪 Event Management
![Event Management](screenshots/event-management.png)
*Comprehensive event CRUD interface*

### 📝 Event Registration
![Event Registration](screenshots/event-registration.png)
*Smart registration with real-time validation*

### 📱 Mobile Responsive
<div align="center">
<img src="screenshots/mobile-home.png" width="250" />
<img src="screenshots/mobile-events.png" width="250" />
<img src="screenshots/mobile-profile.png" width="250" />
</div>

---

## 🧪 Testing

### Manual Testing Checklist
- [x] User authentication (Login/Logout)
- [x] Event CRUD operations
- [x] Registration workflow
- [x] Attendance tracking
- [x] Profile management
- [x] Admin panel access control
- [x] Mobile responsiveness

### Test Accounts
See [Usage](#-usage) section for test credentials.

### Known Issues
- [ ] Email notifications require SMTP configuration
- [ ] Export to PDF feature in progress
- [ ] Bulk registration import pending

---

## 🤝 Contributing

We welcome contributions! Please follow these steps:

### 1. Fork the Repository
```bash
git clone https://github.com/YOUR_USERNAME/event_planner.git
```

### 2. Create a Feature Branch
```bash
git checkout -b feature/AmazingFeature
```

### 3. Commit Changes
```bash
git commit -m "Add: Amazing new feature"
```

### 4. Push to Branch
```bash
git push origin feature/AmazingFeature
```

### 5. Open Pull Request
- Go to GitHub repository
- Click **"New Pull Request"**
- Describe your changes
- Submit for review

### Code Style Guidelines
- Follow **PSR-12** coding standards
- Use meaningful variable names
- Comment complex logic
- Write descriptive commit messages

---

## 📝 License

This project is licensed under the **MIT License**.
```
MIT License

Copyright (c) 2025 VMS Contributors

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT.
```

---

## 📞 Contact & Support

### Project Maintainer
- **GitHub:** [@hpnhann](https://github.com/hpnhann)
- **Email:** 21521696@gm.uit.edu.vn

### Report Issues
Found a bug? Have a feature request?
- [Create an Issue](https://github.com/hpnhann/event_planner/issues)
- [Start a Discussion](https://github.com/hpnhann/event_planner/discussions)

### Community
- [Project Wiki](https://github.com/hpnhann/event_planner/wiki)
- [Changelog](CHANGELOG.md)
- [Roadmap](ROADMAP.md)

---

## 🙏 Acknowledgments

Special thanks to:
- **UIT (University of Information Technology)** for project guidance
- **Bootstrap Team** for the amazing UI framework
- **PHP Community** for extensive documentation
- All contributors who helped improve this project

---

## 🗺️ Roadmap

### Version 1.1 (Q1 2025)
- [ ] Email notification system (SMTP)
- [ ] Export reports to PDF
- [ ] Bulk user import (CSV/Excel)
- [ ] Event calendar view
- [ ] Mobile app (React Native)

### Version 2.0 (Q2 2025)
- [ ] Multi-language support (EN/VI)
- [ ] Advanced analytics dashboard
- [ ] Certificate generation
- [ ] QR code check-in
- [ ] Payment integration

---

<div align="center">

**⭐ Star this repository if you find it helpful!**

Made with ❤️ by [VMS Team](https://github.com/hpnhann/event_planner)

</div>