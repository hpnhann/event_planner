# Volunteer Management System (VMS)
### Centralized Platform for University Volunteer Activity Management

![PHP](https://img.shields.io/badge/PHP-8.1-777BB4?style=for-the-badge&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-00000F?style=for-the-badge&logo=mysql&logoColor=white)
![Bootstrap](https://img.shields.io/badge/Bootstrap-5-563D7C?style=for-the-badge&logo=bootstrap&logoColor=white)

## 🌟 Introduction

The **Volunteer Management System (VMS)** is a web-based application designed to centralize the volunteer lifecycle within university faculties. It replaces scattered tools like Google Sheets/Forms with a unified platform for creating events, managing registrations, and tracking volunteer history.

Our goal is to minimize manual administrative work and improve transparency in volunteer activity reporting.

## ✨ Key Features

The system is secured with **Role-Based Access Control (RBAC)**, supporting Administrators and Students/Volunteers.

### 1. Admin Module (Management & Reporting)
* **Dashboard Overview:** View real-time statistics (Total Events, Registered Users, Pending Applications).
* **Event Management:** * Create, Edit, and Delete volunteer events.
    * Set quotas (Max Volunteers) and deadlines (Registration Close Date).
    * Manage event status (Draft/Published).
* **Member Control:** Manage user accounts and verify roles.

### 2. Student/Volunteer Module
* **Public Event Hub:** Browse all available volunteer opportunities with detailed information (Time, Location, Benefits).
* **Smart Registration:** * One-click registration for logged-in users.
    * Real-time slot checking (prevents over-booking).
    * Duplicate registration prevention.
* **Personal Dashboard:** * **My Events:** View history of registered events and their status.
    * **Profile Management:** Update contact information (Phone, Full Name).

### 3. Security & Technology
* **Authentication:** Secure Login/Logout system with Session management.
* **Data Integrity:** Uses **Prepared Statements** (MySQLi) to prevent SQL Injection.
* **Responsive UI:** Built with **Bootstrap 5**, fully responsive on mobile and desktop.

---

## 🛠️ Tech Stack

* **Backend:** PHP 8.1 (Native/Vanilla)
* **Database:** MySQL
* **Frontend:** HTML5, CSS3, Bootstrap 5, JavaScript (ES6), jQuery
* **Server Environment:** XAMPP / Apache

---

## ⚙️ Installation & Setup (Localhost)

To run this project locally, please follow these steps:

### 1. Prerequisites
* Install **XAMPP** (or WAMP/MAMP) to get Apache and MySQL.
* Ensure PHP 8.0 or higher is installed.

### 2. Clone the Repository
Go to your `htdocs` folder (usually inside `C:\xampp\htdocs`) and clone the project:

```bash
cd C:\xampp\htdocs
git clone [https://github.com/hpnhann/event_planner.git](https://github.com/hpnhann/event_planner.git) school-management-system

