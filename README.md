# KnowledgeBase

A Laravel-based knowledge base website for publishing guides, tutorials, and FAQs. Visitors can search and browse articles, writers create and manage their own content, and admins oversee the whole platform.

## Features

### Public site
- Home page with a keyword search bar and category filters (All, FAQ, Guide, Tutorial)
- Article cards showing category, title, excerpt, author, publish date, and estimated reading time
- About and Contact pages
- Login and writer registration

### Writer registration
- Sign-up form with name, email, password (minimum 8 characters), and the topics the applicant wants to write about (minimum 20 characters)
- New writer accounts can log in only after an admin approves them

### Admin dashboard
- Overview stats: published articles, active writers, total articles, and unread messages
- Latest articles and latest messages at a glance
- **Articles:** manage all articles on the platform
- **Categories:** manage article categories
- **Writers:** manage writer accounts and registrations
- **Messages:** inbox for messages sent through the site
- **Website Settings:** configure site-wide settings
- **Profile:** manage the admin's own profile

### Writer dashboard
- Personal stats: published articles, drafts, and total articles
- **My Articles:** create, edit, and manage your own articles (published or draft)
- Articles are written in Markdown
- **Profile:** manage your own profile

## Tech Stack

- **Framework:** Laravel (PHP)
- **Database:** MySQL
- **Frontend:** Blade templates, CSS, JavaScript

## Requirements

- PHP >= 8.1
- Composer
- Node.js and npm
- MySQL

## Installation

1. **Clone the repository**
   ```bash
   git clone https://github.com/annisaaef24/Knowledge-Base.git
   cd Knowledge-Base
   ```

2. **Install dependencies**
   ```bash
   composer install
   npm install
   ```

3. **Set up the environment file**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Configure the database** in `.env`
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=knowledge_base
   DB_USERNAME=root
   DB_PASSWORD=
   ```

5. **Run migrations and seeders**
   ```bash
   php artisan migrate --seed
   ```

6. **Build assets and start the server**
   ```bash
   npm run build
   php artisan serve
   ```

   The app will be available at `http://localhost:8000`.

## Default Accounts

The seeder creates sample data, including an admin account and a sample writer. Replace the values below with the credentials from your seeder.

| Role   | Email             | Password        |
|--------|-------------------|-----------------|
| Admin  | admin@example.com | `your-password` |

> Change the default credentials before deploying to production.

## Roles and Permissions

| Action                      | Visitor | Writer       | Admin |
|-----------------------------|:-------:|:------------:|:-----:|
| Read and search articles    | Yes     | Yes          | Yes   |
| Apply to become a writer    | Yes     | -            | -     |
| Create and edit articles    | No      | Own articles | All   |
| Manage categories           | No      | No           | Yes   |
| Approve and manage writers  | No      | No           | Yes   |
| Read contact messages       | No      | No           | Yes   |
| Change website settings     | No      | No           | Yes   |

## Contributing

Contributions are welcome. Fork the repository, create a feature branch, and open a pull request.

## License

This project is open-sourced under the [MIT License](LICENSE).

## Image
<img width="1366" height="1082" alt="screencapture-knowledge-base-test-2026-10-29-15_02_40" src="https://github.com/user-attachments/assets/d64f3f07-cad7-4b9f-8ea7-ad9462cc22b2" />

<img width="1366" height="641" alt="screencapture-knowledge-base-test-penulis-2026-10-29-15_25_36" src="https://github.com/user-attachments/assets/70ccbc63-f483-489b-b7cd-3c8cf0a03c84" />

<img width="1366" height="641" alt="screencapture-knowledge-base-test-admin-2026-10-29-15_04_48" src="https://github.com/user-attachments/assets/efbe14b7-f05e-4865-b531-242ce36b6ca2" />

