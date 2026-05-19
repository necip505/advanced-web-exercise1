# Advanced Web Programming - Lab Exercises Portfolio

**Faculty of Electrical Engineering, Computer Science and Information Technology Osijek**  
*Course: Advanced Web Programming*

---

This repository contains a clean, organized collection of all three lab exercises. Each exercise is fully self-contained in its own submission directory, complete with everything required to run and test immediately.

---

## 📁 Repository Structure

*   **`Exercise1_Submission/`** - Web Scraping, Object-Oriented Interface, and Database Integration.
*   **`Exercise2_Submission/`** - Database Backups, XML Profile Parser, and OpenSSL Encrypted File Uploads.
*   **`Exercise3_Submission/`** - Multi-Language Laravel Project Management Platform.

---

## 🚀 How to Test Each Exercise

### 1️⃣ Exercise 1: FERIT STUP Thesis Scraper
A standalone PHP script that dynamically scrapes pages 2–6 of `stup.ferit.hr` to fetch graduate thesis listings, saves them to a MySQL database, and reads them back to render a premium dashboard.

*   **Prerequisites:** Local MySQL server running (e.g. via Laragon, username: `root`, password: empty).
*   **How to run (Browser):** Open your browser and navigate to:
    ```
    http://localhost/test/Exercise1_Submission/main.php
    ```
*   **How to run (CLI):** Open your terminal, navigate to the folder, and run:
    ```bash
    php main.php
    ```
*   **What happens:** 
    *   The script fetches pages 2–6 of graduate theses.
    *   It creates a MySQL database named `thesis` and a `graduate_theses` table automatically.
    *   It prints CLI or HTML log messages during execution.
    *   It reads the database and displays the data beautifully in a premium dark-themed grid of thesis cards.

---

### 2️⃣ Exercise 2: Advanced PHP Integration
A beautiful glassmorphism-themed landing page hosting three major standalone PHP utility tasks.

*   **Prerequisites:** PHP server running.
*   **How to run:** Navigate to the folder in your browser:
    ```
    http://localhost/test/Exercise2_Submission/index.php
    ```
*   **Included Tasks:**
    *   **Task 1 (Database Backup):** Input a database name (e.g. `thesis` or `mysql`) to generate an SQL backup script containing `INSERT INTO` statements, saved as a `.txt` file inside a compressed `.zip` package for instant download.
    *   **Task 2 (XML Parser):** Parses `LV2.xml` and beautifully renders user profile cards containing names, email addresses, resumes, and avatar pictures fetched dynamically.
    *   **Task 3 (Secure Document Upload):** Uploads documents (PDF, JPG, PNG) securely by automatically encrypting them on the server using **OpenSSL (AES-256-CBC)**. It lists all encrypted files and allows users to decrypt and download them instantly via secure links.

---

### 3️⃣ Exercise 3: Multi-Language Laravel Platform
A full-fledged, secure Project Management platform built using Laravel. It includes multi-language support (English and Polish), role-based access control, and a localized database structure.

*   **Prerequisites:** Laragon or local PHP server running.
*   **Self-Contained Database:** The project uses a pre-configured, migrated SQLite database (`database/database.sqlite`) which contains standard tables and test structure out of the box. No MySQL configuration required.
*   **How to run (php artisan serve):** Navigate to the `Exercise3_Submission` folder in your terminal and run:
    ```bash
    php artisan serve
    ```
    Then open `http://127.0.0.1:8000` in your browser.
*   **How to run (Laragon / Web Server):** Visit the public path:
    ```
    http://localhost/test/Exercise3_Submission/public
    ```
*   **Key Features to Test:**
    *   **Self-Registration & Authentication:** Register a new user, log in, and log out securely.
    *   **Role-Based Project Control:** 
        *   **Project Managers** (creators of the project) can update all project details (name, description, price, dates) and manage team members.
        *   **Team Members** (added to the project by managers) can ONLY view project details and edit the "jobs completed" (done_jobs) progress counter.
    *   **Localization (EN / PL):** Use the navigation language switcher in the header to seamlessly switch the entire user interface, alerts, forms, and validation errors between English and Polish.
