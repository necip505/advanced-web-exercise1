# Advanced Web Programming – Exercise 1

**Faculty of Electrical Engineering, Computer Science and Information Technology Osijek**  
Course: Advanced Web Programming

---

## Task

Scrape graduate thesis listings from [stup.ferit.hr](https://stup.ferit.hr/zavrsni-radovi/) (pages 2–6), store them in a MySQL database, and expose them via a `GraduateThesis` class that implements the `iRadio` interface.

---

## Files

| File | Description |
|------|-------------|
| `iRadio.php` | Interface defining `create()`, `save()`, `read()` |
| `GraduateThesis.php` | Class implementing `iRadio`; scrapes via cURL/socket, parses HTML, stores in MySQL |
| `main.php` | Entry point – runs scrape → save → read and displays results in browser |

---

## Setup

1. Start **Laragon** (Apache + MySQL)
2. Open `http://localhost/test/main.php` in your browser
3. The script will:
   - Scrape pages 2–6 from `stup.ferit.hr`
   - Create the `thesis` database and `graduate_theses` table automatically
   - Save all theses and display them in a dark-mode card grid

---

## Database

- **Database:** `thesis`
- **Table:** `graduate_theses`

| Column | Type |
|--------|------|
| `id` | INT AUTO_INCREMENT PK |
| `identification_number` | INT |
| `work_name` | TEXT |
| `work_text` | MEDIUMTEXT |
| `work_link` | VARCHAR(512) |
| `created_at` | TIMESTAMP |

---

## GraduateThesis Properties

```php
public string $work_name;
public string $work_text;
public string $work_link;
public int    $identification_number;
```
