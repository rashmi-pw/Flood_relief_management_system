# Flood Relief Management System (FRMS)

A web-based Flood Relief Management System built to support the collection and management of basic relief data during flood situations in Sri Lanka.

Developed as part of **CSC 1051 — Full-Stack Fundamentals** at the **University of Sri Jayewardenepura**.

---

## Project Overview

During flood disasters, coordinating relief efforts requires accurate, real-time data about affected households and their needs. FRMS provides a structured platform where:

- Affected persons can register and submit relief requests specifying the type of aid needed, their location, and household details.
- Administrators can monitor all requests, manage registered users, and generate filtered reports to support decision-making.

---

## Features

### User (Affected Person)
- Secure registration with NIC and email uniqueness validation
- Login / logout with session-based authentication
- Submit relief requests (Food, Water, Medicine, Shelter)
- Specify district, divisional secretariat, GN division, and flood severity
- View, edit, and delete own requests
- Personal dashboard with request summary statistics

### Admin
- Role-based access control (admin vs user)
- System-wide dashboard with live statistics
- View and search all registered users
- Per-user detailed report with full request history
- Safe user deletion (with cascade to associated requests)
- Filtered system reports by district, relief type, and severity level
- Breakdown tables by district and relief type
- Summary KPIs including total family members affected

---

## Project Structure
```
flood_relief/
│
├── index.php                   # Entry point — redirects by role
│
├── auth/
│   ├── login.php               # Login form and handler
│   ├── register.php            # Registration form and handler
│   └── logout.php              # Session destroy and redirect
│
├── user/
│   ├── dashboard.php           # User home with stats
│   ├── create_request.php      # Submit new relief request
│   ├── view_requests.php       # List all own requests
│   ├── edit_request.php        # Edit an existing request
│   └── delete_request.php      # Delete a request
│
├── admin/
│   ├── dashboard.php           # Admin overview with system stats
│   ├── users.php               # User listing with search
│   ├── user_detail.php         # Per-user detail and report
│   ├── delete_user.php         # Safe user deletion
│   └── reports.php             # Filtered system reports
│
├── includes/
│   ├── auth_check.php          # Session guard for protected pages
│   ├── admin_check.php         # Role guard for admin pages
│   ├── header.php              # Shared navbar and HTML head
│   └── footer.php              # Shared footer with toast system
│
├── assets/
│   ├── css/
│   │   └── style.css           # Full stylesheet
│   └── js/
│       └── main.js             # Frontend interactions
│
└── db.php                      # PDO database connection
```
