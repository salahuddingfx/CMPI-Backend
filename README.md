<p align="center">
  <img src="https://img.shields.io/badge/Laravel-12.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white" alt="Laravel">
  <img src="https://img.shields.io/badge/PHP-8.2+-777BB4?style=for-the-badge&logo=php&logoColor=white" alt="PHP">
  <img src="https://img.shields.io/badge/License-GPL%20v3-blue?style=for-the-badge" alt="License">
  <img src="https://img.shields.io/badge/Status-Active-success?style=for-the-badge" alt="Status">
</p>

<h1 align="center">🎓 CMPI Backend API</h1>

<p align="center">
  A robust RESTful API backend for the <strong>College/Institute Management & Public Information (CMPI)</strong> platform — built with Laravel 12 and PHP 8.2+.
</p>

---

## 📌 About CMPI Backend

CMPI Backend is the server-side API powering the CMPI platform — a full-featured institutional web system designed for colleges and institutes. It handles authentication, student dashboards, admissions, notices, faculty, departments, blogs, events, and much more.

---

## ✨ Features

- 🔐 **Authentication** — Secure login/logout with Laravel Sanctum token-based auth
- 🏛️ **Institute Info** — Public institute details and stats
- 📢 **Notices** — Create, list, and view institutional notices
- 📅 **Events** — Institute event management
- 📝 **Blog** — Blog post system with slug-based routing
- 🏫 **Departments** — Department listing and detail pages
- 👩‍🏫 **Faculty** — Faculty profile management
- 🎓 **Student Dashboard** — Courses, results, bills, emails, profile
- 📋 **Admissions** — Online admission form submission
- 💬 **Feedback** — Public feedback with upvote support
- 🔍 **Search** — Global search across institute content
- 📁 **File Uploads** — Single & multiple file upload with deletion
- 🌐 **CORS Ready** — Configured for cross-origin frontend access

---

## 🛠️ Tech Stack

| Layer | Technology |
|---|---|
| Framework | Laravel 12 |
| Language | PHP 8.2+ |
| Authentication | Laravel Sanctum |
| Database | MySQL / PostgreSQL |
| API | RESTful JSON API |
| File Storage | Laravel Filesystem |

---

## 📡 API Endpoints

### Public Routes
| Method | Endpoint | Description |
|--------|----------|-------------|
| `POST` | `/api/login` | User login |
| `GET` | `/api/institute` | Institute information |
| `GET` | `/api/notices` | List all notices |
| `GET` | `/api/notices/{id}` | Single notice |
| `GET` | `/api/events` | List all events |
| `GET` | `/api/events/{id}` | Single event |
| `GET` | `/api/blogs` | List all blogs |
| `GET` | `/api/blogs/{slug}` | Blog by slug |
| `GET` | `/api/departments` | List departments |
| `GET` | `/api/departments/{slug}` | Department by slug |
| `GET` | `/api/faculty` | List faculty |
| `GET` | `/api/faculty/{id}` | Single faculty |
| `GET` | `/api/search` | Global search |
| `POST` | `/api/admissions` | Submit admission form |
| `GET` | `/api/feedbacks` | List feedbacks |
| `POST` | `/api/feedbacks` | Submit feedback |
| `POST` | `/api/feedbacks/{id}/upvote` | Upvote feedback |

### Protected Routes (Sanctum Auth Required)
| Method | Endpoint | Description |
|--------|----------|-------------|
| `POST` | `/api/logout` | User logout |
| `GET` | `/api/user` | Authenticated user info |
| `GET` | `/api/dashboard` | Student dashboard |
| `GET` | `/api/dashboard/courses` | Student courses |
| `GET` | `/api/dashboard/results` | Student results |
| `GET` | `/api/dashboard/bills` | Student bills |
| `GET` | `/api/dashboard/profile` | Student profile |
| `GET` | `/api/dashboard/emails` | Student emails |
| `POST` | `/api/upload` | Upload single file |
| `POST` | `/api/upload/multiple` | Upload multiple files |
| `DELETE` | `/api/upload` | Delete uploaded file |

---

## 🚀 Getting Started

### Prerequisites
- PHP 8.2+
- Composer
- MySQL or PostgreSQL
- Node.js (for asset compilation)

### Installation

```bash
# 1. Clone the repository
git clone https://github.com/salahuddingfx/CMPI-Backend.git
cd CMPI-Backend

# 2. Install PHP dependencies
composer install

# 3. Copy environment file
cp .env.example .env

# 4. Generate application key
php artisan key:generate

# 5. Configure your database in .env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=cmpi
DB_USERNAME=root
DB_PASSWORD=

# 6. Run migrations
php artisan migrate

# 7. (Optional) Seed the database
php artisan db:seed

# 8. Start the server
php artisan serve
```

The API will be available at `http://localhost:8000/api`

---

## 🔒 Authentication

This project uses **Laravel Sanctum** for API token authentication.

```bash
# Login to get token
POST /api/login
Body: { "email": "user@example.com", "password": "password" }

# Use token in header
Authorization: Bearer {your-token}
```

---

## 🤝 Contributing

We welcome contributions! Please read our [Code of Conduct](CODE_OF_CONDUCT.md) and [Security Policy](SECURITY.md) before contributing.

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'feat: add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

---

## 🔐 Security

For reporting security vulnerabilities, please refer to our [Security Policy](SECURITY.md). **Do not open public issues for security vulnerabilities.**

---

## 📄 License

This project is licensed under the **GNU General Public License v3.0** — see the [LICENSE](LICENSE) file for details.

This means:
- ✅ You can use, study, share, and improve this software
- ✅ Any modified version must also be open source under GPL-3.0
- ❌ You cannot distribute closed-source versions of this code

---

## 👨‍💻 Author

**Salah Uddin Kader**
- GitHub: [@salahuddingfx](https://github.com/salahuddingfx)

---

<p align="center">Made with ❤️ using Laravel</p>