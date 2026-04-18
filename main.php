<?php

/**
 * main.php
 * --------
 * Entry point for Exercise 1.
 *
 * Run from the command line:
 *   php main.php
 *
 * Or visit via browser (if Laragon is running):
 *   http://localhost/test/main.php
 *
 * What it does:
 *  1. Scrapes thesis posts from pages 2–6 of stup.ferit.hr
 *  2. Creates a GraduateThesis object for each post (via create())
 *  3. Saves all theses to the MySQL `thesis` database  (via save())
 *  4. Reads them back and prints a summary              (via read())
 */

declare(strict_types=1);

// ── Pretty output for both CLI and browser ──────────────────────────────────
$isCli = (PHP_SAPI === 'cli');
if (!$isCli) {
    header('Content-Type: text/html; charset=utf-8');
    echo '<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Graduate Theses – FERIT STUP Scraper</title>
<style>
  :root {
    --bg: #0f1117; --surface: #1a1d27; --accent: #6c63ff;
    --text: #e2e8f0; --muted: #94a3b8; --success: #22c55e;
    --warning: #f59e0b; --border: #2d3148;
  }
  * { box-sizing: border-box; margin: 0; padding: 0; }
  body { background: var(--bg); color: var(--text); font-family: "Segoe UI", system-ui, sans-serif;
         padding: 2rem; line-height: 1.6; }
  h1   { font-size: 2rem; font-weight: 700; color: var(--accent); margin-bottom: .25rem; }
  .subtitle { color: var(--muted); margin-bottom: 2rem; font-size: .95rem; }
  .log  { background: var(--surface); border: 1px solid var(--border); border-radius: 10px;
          padding: 1.25rem 1.5rem; margin-bottom: 2rem; font-family: monospace; font-size: .9rem;
          white-space: pre-wrap; }
  .log  .ok  { color: var(--success); }
  .log  .warn { color: var(--warning); }
  h2   { font-size: 1.4rem; margin-bottom: 1rem; color: var(--accent); border-bottom: 1px solid var(--border); padding-bottom: .5rem; }
  .grid { display: grid; gap: 1rem; grid-template-columns: repeat(auto-fill, minmax(340px, 1fr)); }
  .card { background: var(--surface); border: 1px solid var(--border); border-radius: 12px;
          padding: 1.25rem; transition: transform .2s, box-shadow .2s; }
  .card:hover { transform: translateY(-3px); box-shadow: 0 8px 24px rgba(108,99,255,.18); }
  .card-id   { font-size: .75rem; color: var(--muted); margin-bottom: .35rem; }
  .card-title { font-weight: 600; font-size: 1rem; margin-bottom: .5rem; }
  .card-title a { color: var(--text); text-decoration: none; }
  .card-title a:hover { color: var(--accent); }
  .card-text { color: var(--muted); font-size: .85rem; display: -webkit-box;
               -webkit-line-clamp: 4; -webkit-box-orient: vertical; overflow: hidden; }
  .badge { display: inline-block; background: var(--accent); color: #fff;
           border-radius: 999px; font-size: .7rem; padding: .15rem .55rem; margin-top: .75rem; }
  footer { margin-top: 3rem; text-align: center; color: var(--muted); font-size: .8rem; }
</style>
</head>
<body>
<h1>🎓 Graduate Theses – FERIT STUP</h1>
<p class="subtitle">Faculty of Electrical Engineering, Computer Science and Information Technology Osijek · Advanced Web Programming · Exercise 1</p>
<div class="log" id="log">';
}

// ── Helpers ──────────────────────────────────────────────────────────────────
function out(string $msg, string $type = ''): void
{
    global $isCli;
    if ($isCli) {
        echo $msg . "\n";
    } else {
        $cls = $type ? " class=\"{$type}\"" : '';
        echo "<span{$cls}>" . htmlspecialchars($msg, ENT_QUOTES, 'UTF-8') . "\n</span>";
    }
}

// ── Load classes ─────────────────────────────────────────────────────────────
require_once __DIR__ . '/iRadio.php';
require_once __DIR__ . '/GraduateThesis.php';

// ── Step 1 – Scrape pages 2–6 ────────────────────────────────────────────────
out('── Step 1: Scraping stup.ferit.hr (pages 2–6) ──────────────────────');
GraduateThesis::scrapeAll();

// ── Step 2 – Save to DB ──────────────────────────────────────────────────────
out("\n── Step 2: Saving to MySQL database ────────────────────────────────", 'ok');
$thesis = new GraduateThesis();

try {
    $thesis->save();
} catch (PDOException $e) {
    out('❌ Database error: ' . $e->getMessage(), 'warn');
    out('   Make sure Laragon (MySQL) is running and the credentials are correct.', 'warn');
    if (!$isCli) {
        echo '</div></body></html>';
    }
    exit(1);
}

// ── Step 3 – Read back from DB ───────────────────────────────────────────────
out("\n── Step 3: Reading all theses from the database ────────────────────", 'ok');
$allTheses = $thesis->read();
out('📚 Total records in database: ' . count($allTheses), 'ok');

if (!$isCli) {
    echo '</div>'; // end .log

    echo '<h2>📋 All Theses in Database</h2>';
    echo '<div class="grid">';

    foreach ($allTheses as $row) {
        $safeTitle = htmlspecialchars($row['work_name'],  ENT_QUOTES, 'UTF-8');
        $safeText  = htmlspecialchars($row['work_text'],  ENT_QUOTES, 'UTF-8');
        $safeLink  = htmlspecialchars($row['work_link'],  ENT_QUOTES, 'UTF-8');
        $safeId    = (int) $row['identification_number'];
        echo <<<HTML
        <div class="card">
          <div class="card-id"># {$safeId}</div>
          <div class="card-title"><a href="{$safeLink}" target="_blank" rel="noopener">{$safeTitle}</a></div>
          <div class="card-text">{$safeText}</div>
          <a class="badge" href="{$safeLink}" target="_blank" rel="noopener">Visit →</a>
        </div>
        HTML;
    }

    echo '</div>';
    echo '<footer>Generated by GraduateThesis scraper · Advanced Web Programming – FERIT Osijek</footer>';
    echo '</body></html>';
} else {
    // CLI: print a simple table
    echo str_repeat('-', 80) . "\n";
    printf("%-5s %-50s %s\n", 'ID', 'Title', 'Link');
    echo str_repeat('-', 80) . "\n";
    foreach ($allTheses as $row) {
        printf(
            "%-5d %-50s %s\n",
            $row['identification_number'],
            mb_substr($row['work_name'], 0, 48),
            $row['work_link']
        );
    }
    echo str_repeat('-', 80) . "\n";
    echo "Done.\n";
}
