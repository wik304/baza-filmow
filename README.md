# 🎬 Kinoteka – Advanced Movie Database & Review Platform

**Kinoteka** is a comprehensive custom web application built in PHP (using vanilla JavaScript, HTML5, and CSS3), serving as both an interactive movie catalog and a social platform for movie enthusiasts and professional critics. The project's architecture and functionality are inspired by popular services such as Filmweb and IMDb.

The application features an extensive user role system, an advanced rating mechanism, gamification through achievements, and a fully featured administration panel. It was designed with a strong focus on user experience (UX), utilizing AJAX-powered asynchronous requests for most interactions.

---

## ✨ Key Features

### 👥 User System & Community

* **Role Hierarchy:** The platform supports five account types: Guest, User (`user`), Critic (`critic`), Administrator (`admin`), and Owner (`owner`).
* **User Profiles:** Every registered user has a personalized profile. Users can customize their avatar and profile banner through asynchronous uploads using `FormData` and the Fetch API.
* **Critic Accounts:** Users with the `critic` role receive a verification badge, a dedicated professional bio section, and their ratings contribute to a separate "Critics Rating" average.
* **Follower System:** Users can follow their favorite critics and movie enthusiasts, building their own social network within the platform.
* **Gamification (Achievements):** An automated badge system rewards users for specific activities (e.g., writing a first review, adding a certain number of movies to favorites).

### 🎞️ Movie Interactions

* **Dual Rating System:** Movies feature two independent rating averages: one from regular viewers and another from certified critics.
* **Reviews & Likes:** Users can write full movie reviews. Other readers can evaluate review usefulness by leaving likes, all without page reloads.
* **Personal Lists:** Users can add movies to **Favorites** and **Watchlist** collections.
* **Guest Session Migration:** Unregistered visitors can rate movies and create lists. Their activity is stored in the session (`$_SESSION`). After registering or logging in, all guest data is automatically migrated and assigned to the user's account in the database.

### 🔍 Advanced Search & Filtering

* **Live Search (Autocomplete):** The search bar dynamically suggests movie titles, posters, and release years after entering just two characters.
* **Advanced Filters:** Search results can be filtered by:

  * Title
  * Genre (multiple selection)
  * Release year range
  * User rating range
  * Critic rating range
* **Sorting Options:** Results can be sorted by release date, popularity, highest ratings, and more.

### 🛡️ Administration Panel (CMS)

A comprehensive management panel designed for administrators:

* **Movie Management:** Add, edit, and remove movies. Includes an intuitive system for managing directors and genres using dynamic tags powered by custom JavaScript. Supports movie statuses such as `available` and `upcoming`.
* **Announcements Module (Hero Slider):** Administrators can manage the homepage hero banner, associate slides with movies, upload high-resolution backgrounds, enable/disable slides, and reorder them using **Drag & Drop** functionality powered by **SortableJS**.
* **User Management:** Promote users to different roles (e.g., Critic) and ban toxic or abusive accounts.
* **Review Moderation:** Browse, edit, and remove user-generated reviews.

---

## 💻 Technical Aspects & Architecture

The project was developed with a strong focus on code quality, maintainability, and security despite not using a PHP framework.

### Database

* Relational **MySQL** database.
* Extensive use of:

  * `GROUP_CONCAT` queries,
  * Subqueries for dynamically calculating rating averages,
  * `ON DUPLICATE KEY UPDATE` for optimized inserts and updates (e.g., genres and directors dictionaries).

### Security

* Password hashing using `password_hash()`.
* Protection against SQL Injection through consistent use of **Prepared Statements** (`bind_param`) with the `mysqli` extension.
* XSS prevention through input sanitization (`strip_tags`) and output escaping (`htmlspecialchars`).

### Backend (PHP)

* Modular structure based on dedicated action files (`actions/` directory), separating business logic from presentation.
* Advanced session management.
* Clear separation of responsibilities across modules.

### Frontend (UI/UX)

* Responsive interface built with custom CSS, Flexbox, and CSS Grid.
* No heavy frontend frameworks such as Bootstrap.
* Dynamic Toast Notifications providing instant user feedback.
* Movie carousel powered by the lightweight **Splide.js** library.

### JavaScript (ES6+)

* Extensive use of the **Fetch API** for asynchronous communication with the backend.
* Many features (such as likes, avatar updates, and filtering forms) operate similarly to a Single Page Application (SPA), reducing server load and improving user experience.

### File Management

* Secure image upload system supporting:

  * Movie posters
  * Hero banner backgrounds
  * User avatars
* MIME type validation.
* File extension verification.
* File size limits.
* Automatically generated unique filenames to prevent overwriting existing files.

---

## 🚀 Running the Project Locally

### Prerequisites

A local web server environment with PHP and MySQL support is required, such as:

* XAMPP
* WAMP
* Laragon
* MAMP

### Installation Guide

#### 1. Download the Project

Clone this repository or download it as a ZIP archive and extract it.

#### 2. Place Files on Your Server

Copy the entire project folder into your local web server's root directory.

Example for XAMPP:

```text
C:\xampp\htdocs\
```

#### 3. Configure the Database

* Start Apache and MySQL from your local environment.
* Open **phpMyAdmin** (typically available at `http://localhost/phpmyadmin`).
* Create a new database named:

```text
movie_database_db
```

* Use the collation:

```text
utf8mb4_general_ci
```

* Import the provided SQL dump file (if included in the repository) or create the tables according to the application's schema.

#### 4. Database Connection

Open the configuration file:

```php
config/db_connect.php
```

Verify that the database credentials match your local setup:

```php
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'movie_database_db');
```

#### 5. Launch the Application

Open your browser and navigate to:

```text
http://localhost/kinoteka
```

The application should now be fully operational.

---

## 🛠 Technologies Used

* **PHP**
* **MySQL**
* **JavaScript (ES6+)**
* **HTML5**
* **CSS3**
* **AJAX / Fetch API**
* **Splide.js**
* **SortableJS**

---

## 📌 Highlights

* Dual audience and critic rating system
* Social features with followers and profiles
* Achievement-based gamification
* Advanced movie search and filtering
* Guest-to-user data migration
* Responsive UI with SPA-like interactions
* Comprehensive administration panel
* Secure file uploads and authentication
* Custom-built without external PHP frameworks
