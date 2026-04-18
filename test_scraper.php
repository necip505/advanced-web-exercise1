<?php
require __DIR__ . '/iRadio.php';
require __DIR__ . '/GraduateThesis.php';

GraduateThesis::scrapeAll();
echo PHP_EOL;

// Use reflection to access private static $theses
$ref   = new ReflectionClass('GraduateThesis');
$prop  = $ref->getProperty('theses');
$prop->setAccessible(true);
$theses = $prop->getValue(null);

echo '=== SCRAPED THESES ===' . PHP_EOL;
foreach ($theses as $i => $t) {
    echo ($i + 1) . '. [#' . $t->identification_number . '] ' . mb_substr($t->work_name, 0, 70) . PHP_EOL;
    echo '   Link: ' . $t->work_link . PHP_EOL;
    echo '   Text: ' . mb_substr($t->work_text, 0, 100) . '...' . PHP_EOL;
    echo PHP_EOL;
}
echo 'Total scraped: ' . count($theses) . PHP_EOL;
