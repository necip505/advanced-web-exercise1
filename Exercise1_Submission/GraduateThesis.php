<?php

require_once __DIR__ . '/iRadio.php';

/**
 * Class GraduateThesis
 *
 * Implements the iRadio interface. Scrapes graduate thesis data from
 * https://stup.ferit.hr/zavrsni-radovi/page/$number/ (pages 2–6),
 * stores them in a MySQL database called 'thesis', and can read them back.
 */
class GraduateThesis implements iRadio
{
    // -----------------------------------------------------------------------
    // Class properties
    // -----------------------------------------------------------------------
    public string $work_name           = '';
    public string $work_text           = '';
    public string $work_link           = '';
    public int    $identification_number = 0;

    // Shared collection of all scraped thesis objects
    private static array $theses = [];

    // -----------------------------------------------------------------------
    // Database connection settings
    // -----------------------------------------------------------------------
    private static string $db_host     = '127.0.0.1';
    private static int    $db_port     = 3306;
    private static string $db_name     = 'thesis';
    private static string $db_user     = 'root';
    private static string $db_password = '';

    // -----------------------------------------------------------------------
    // iRadio: create
    // -----------------------------------------------------------------------

    /**
     * Populate this object's properties.
     */
    public function create(
        string $work_name,
        string $work_text,
        string $work_link,
        int    $identification_number
    ): void {
        $this->work_name            = $work_name;
        $this->work_text            = $work_text;
        $this->work_link            = $work_link;
        $this->identification_number = $identification_number;

        // Keep a global collection so save() can batch-insert everything
        self::$theses[] = $this;
    }

    // -----------------------------------------------------------------------
    // iRadio: save
    // -----------------------------------------------------------------------

    /**
     * Save all scraped theses to the graduate_theses table.
     */
    public function save(): void
    {
        $pdo = self::getConnection();

        // Create the table if it does not exist yet
        $pdo->exec(
            "CREATE TABLE IF NOT EXISTS graduate_theses (
                id                    INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                identification_number INT UNSIGNED NOT NULL,
                work_name             TEXT         NOT NULL,
                work_text             MEDIUMTEXT   NOT NULL,
                work_link             VARCHAR(512) NOT NULL,
                created_at            TIMESTAMP    DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        );

        // Clear existing records so re-running the script never creates duplicates
        $pdo->exec('TRUNCATE TABLE graduate_theses');

        $stmt = $pdo->prepare(
            "INSERT INTO graduate_theses
                (identification_number, work_name, work_text, work_link)
             VALUES
                (:identification_number, :work_name, :work_text, :work_link)"
        );

        $savedCount = 0;
        foreach (self::$theses as $thesis) {
            $stmt->execute([
                ':identification_number' => $thesis->identification_number,
                ':work_name'             => $thesis->work_name,
                ':work_text'             => $thesis->work_text,
                ':work_link'             => $thesis->work_link,
            ]);
            $savedCount++;
        }

        echo "✅ {$savedCount} thesis record(s) saved to the database.\n";
    }

    // -----------------------------------------------------------------------
    // iRadio: read
    // -----------------------------------------------------------------------

    /**
     * Retrieve all theses from the graduate_theses table.
     *
     * @return array  Array of associative arrays (one per row)
     */
    public function read(): array
    {
        $pdo  = self::getConnection();
        $stmt = $pdo->query("SELECT * FROM graduate_theses ORDER BY identification_number ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // -----------------------------------------------------------------------
    // Scraper
    // -----------------------------------------------------------------------

    /**
     * Fetch pages 2–6 from stup.ferit.hr, parse each thesis, and create
     * GraduateThesis objects.
     *
     * Uses cURL if available; falls back to sockets otherwise.
     */
    public static function scrapeAll(): void
    {
        $idCounter = 1;

        for ($pageNumber = 2; $pageNumber <= 6; $pageNumber++) {
            $url  = "https://stup.ferit.hr/zavrsni-radovi/page/{$pageNumber}/";
            $html = self::fetchUrl($url);

            if ($html === false) {
                echo "⚠️  Could not fetch page {$pageNumber}. Skipping.\n";
                continue;
            }

            $articles = self::parseArticles($html);

            foreach ($articles as $article) {
                $thesis = new self();
                $thesis->create(
                    $article['title'],
                    $article['excerpt'],
                    $article['link'],
                    $idCounter++
                );
            }

            echo "📄 Page {$pageNumber}: " . count($articles) . " theses found.\n";
        }
    }

    // -----------------------------------------------------------------------
    // Internal helpers
    // -----------------------------------------------------------------------

    /**
     * Fetch a URL via cURL (preferred) or sockets.
     *
     * @return string|false  HTML content or false on failure
     */
    private static function fetchUrl(string $url): string|false
    {
        if (function_exists('curl_init')) {
            return self::fetchViaCurl($url);
        }

        return self::fetchViaSocket($url);
    }

    /**
     * Fetch URL content using cURL.
     */
    private static function fetchViaCurl(string $url): string|false
    {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_ENCODING       => '',          // Accept any encoding
            CURLOPT_USERAGENT      => 'Mozilla/5.0 (compatible; GraduateThesisScraper/1.0)',
            CURLOPT_SSL_VERIFYPEER => true,
        ]);

        $response = curl_exec($ch);
        $error    = curl_error($ch);
        curl_close($ch);

        if ($error) {
            echo "cURL error for {$url}: {$error}\n";
            return false;
        }

        return $response ?: false;
    }

    /**
     * Fetch URL content using a raw TCP socket (HTTPS is not supported here
     * without the openssl extension; this is a plain-HTTP fallback only).
     */
    private static function fetchViaSocket(string $url): string|false
    {
        $parsed = parse_url($url);
        $host   = $parsed['host']                                     ?? '';
        $path   = ($parsed['path'] ?? '/') . (isset($parsed['query']) ? '?' . $parsed['query'] : '');
        $port   = $parsed['port']  ?? ($parsed['scheme'] === 'https' ? 443 : 80);
        $scheme = $parsed['scheme'] ?? 'http';

        // For HTTPS use ssl:// transport
        $transport = ($scheme === 'https') ? "ssl://{$host}" : $host;

        $socket = fsockopen($transport, $port, $errno, $errstr, 30);
        if (!$socket) {
            echo "Socket error ({$errno}): {$errstr}\n";
            return false;
        }

        $request = implode("\r\n", [
            "GET {$path} HTTP/1.1",
            "Host: {$host}",
            "User-Agent: Mozilla/5.0 (compatible; GraduateThesisScraper/1.0)",
            "Accept: text/html,application/xhtml+xml",
            "Accept-Encoding: identity",
            "Connection: close",
            "",
            "",
        ]);

        fwrite($socket, $request);

        $response = '';
        while (!feof($socket)) {
            $response .= fread($socket, 8192);
        }
        fclose($socket);

        // Strip HTTP headers
        $bodyStart = strpos($response, "\r\n\r\n");
        return ($bodyStart !== false) ? substr($response, $bodyStart + 4) : $response;
    }

    /**
     * Parse article data out of the HTML returned by stup.ferit.hr.
     *
     * The site uses standard WordPress markup:
     *   <article id="post-NNN" class="post-NNN post type-post ...">
     *     ...
     *     <h2 class="entry-title"><a href="URL">Title</a></h2>
     *     ...
     *     <div class="entry-content">...excerpt...</div>
     *     ...
     *   </article>
     *
     * @return array  Each element: ['title' => ..., 'excerpt' => ..., 'link' => ...]
     */
    private static function parseArticles(string $html): array
    {
        $articles = [];

        // ---- Use DOMDocument + XPath for robust parsing ----
        libxml_use_internal_errors(true);
        $dom = new DOMDocument();
        $dom->loadHTML('<?xml encoding="utf-8" ?>' . $html, LIBXML_NOWARNING | LIBXML_NOERROR);
        libxml_clear_errors();

        $xpath   = new DOMXPath($dom);
        // Find all <article> elements
        $articleNodes = $xpath->query('//article');

        foreach ($articleNodes as $articleNode) {
            // ---- Title + Link ----
            $titleNodes = $xpath->query('.//h2[contains(@class,"entry-title")]/a', $articleNode);
            if ($titleNodes->length === 0) {
                // Try any h2 > a as fallback
                $titleNodes = $xpath->query('.//h2/a', $articleNode);
            }
            if ($titleNodes->length === 0) {
                continue; // Skip if no title found
            }

            $titleNode = $titleNodes->item(0);
            $title     = trim($titleNode->textContent);
            $link      = trim($titleNode->getAttribute('href'));

            if (empty($title) || empty($link)) {
                continue;
            }

            // ---- Excerpt / work_text ----
            // Try <div class="entry-content"> first
            $excerptNodes = $xpath->query(
                './/div[contains(@class,"entry-content")] | .//div[contains(@class,"entry-summary")]',
                $articleNode
            );

            $excerpt = '';
            if ($excerptNodes->length > 0) {
                $excerpt = trim($excerptNodes->item(0)->textContent);
            } else {
                // Fallback: whole article text minus the heading
                $excerpt = trim($articleNode->textContent);
            }

            // Collapse whitespace and truncate to 1 000 chars
            $excerpt = preg_replace('/\s+/', ' ', $excerpt);
            $excerpt = mb_substr($excerpt, 0, 1000);

            $articles[] = [
                'title'   => $title,
                'excerpt' => $excerpt,
                'link'    => $link,
            ];
        }

        return $articles;
    }

    /**
     * Return a PDO connection, creating the 'thesis' database if it doesn't exist.
     */
    private static function getConnection(): PDO
    {
        // Connect without a database first to create it if needed
        $dsnNoDB = sprintf(
            'mysql:host=%s;port=%d;charset=utf8mb4',
            self::$db_host,
            self::$db_port
        );

        $pdoInit = new PDO($dsnNoDB, self::$db_user, self::$db_password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ]);
        $pdoInit->exec(
            "CREATE DATABASE IF NOT EXISTS `" . self::$db_name . "`
             CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"
        );
        unset($pdoInit);

        // Now connect with the database selected
        $dsn = sprintf(
            'mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4',
            self::$db_host,
            self::$db_port,
            self::$db_name
        );

        return new PDO($dsn, self::$db_user, self::$db_password, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    }
}
