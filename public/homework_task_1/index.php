<?php

require_once 'GraduateThesis.php';

// Supress warnings for broken HTML
libxml_use_internal_errors(true);

echo "<pre>Starting scraping process...\n";

// To demonstrate create, save and read, we will initialize one object at the end to read.
$dbHelper = new GraduateThesis();

for ($number = 2; $number <= 6; $number++) {
    $url = "https://stup.ferit.hr/zavrsni-radovi/page/$number/";
    echo "Fetching $url ...\n";
    
    // Connect to the page using cURL
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    // Ignore SSL certificate verification 
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $html = curl_exec($ch);
    curl_close($ch);
    
    if (!$html) {
        echo "Failed to fetch $url\n";
        continue;
    }
    
    $dom = new DOMDocument();
    $dom->loadHTML($html);
    $xpath = new DOMXPath($dom);
    
    // Find all article elements on the page that represent the theses
    $articles = $xpath->query("//article[contains(@class, 'fusion-post-medium')]");
    
    foreach ($articles as $article) {
        // Name and Link
        $nameNode = $xpath->query(".//h2[contains(@class, 'entry-title')]/a", $article);
        $work_name = $nameNode->length > 0 ? trim($nameNode->item(0)->textContent) : '';
        $work_link = $nameNode->length > 0 ? $nameNode->item(0)->getAttribute('href') : '';
        
        // Text
        $textNode = $xpath->query(".//div[contains(@class, 'fusion-post-content-container')]/p", $article);
        $work_text = $textNode->length > 0 ? trim($textNode->item(0)->textContent) : '';
        
        // Identification Number
        $imgNode = $xpath->query(".//img[contains(@src, '/logos/')]", $article);
        $identification_number = '';
        if ($imgNode->length > 0) {
            $src = $imgNode->item(0)->getAttribute('src');
            // Extract the number from the logo URL (e.g. logos/47726994562.png)
            if (preg_match('/logos\/(\d+)\./', $src, $matches)) {
                $identification_number = $matches[1];
            } else {
                // If it's not numbers only, get the filename without extension
                $basename = basename($src);
                $identification_number = pathinfo($basename, PATHINFO_FILENAME);
            }
        }
        
        if (!empty($work_name)) {
            // Create a new GraduateThesis class object
            $thesis = new GraduateThesis();
            
            // Save the information using create method
            $thesis->create($work_name, $work_text, $work_link, $identification_number);
            
            // Save all received theses in the table graduate_theses
            $thesis->save();
        }
    }
}

echo "All pages processed.\n";

echo "----------------------------------------\n";
echo "Read all theses from the database:\n";
$allTheses = $dbHelper->read();
echo "Total records: " . count($allTheses) . "\n\n";

foreach ($allTheses as $index => $t) {
    if ($index < 5) {
        echo "- " . $t['work_name'] . " | OIB: " . $t['identification_number'] . "\n";
    }
}
if (count($allTheses) > 5) {
    echo "... and " . (count($allTheses) - 5) . " more.\n";
}

?>

