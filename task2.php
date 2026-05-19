<?php
// Task 2: Parse LV2.xml

$xmlFile = 'LV2.xml';

if (!file_exists($xmlFile)) {
    die("Error: The file $xmlFile does not exist.");
}

$xml = simplexml_load_file($xmlFile);

if ($xml === false) {
    die("Error: Failed to parse XML.");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profiles - Task 2</title>
    <style>
        body { font-family: sans-serif; background: #f4f4f9; padding: 20px; }
        .profile { background: white; padding: 20px; margin-bottom: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); display: flex; gap: 20px; }
        .profile img { width: 100px; height: 100px; border-radius: 50%; object-fit: cover; }
        .info h2 { margin: 0 0 10px 0; }
        .info p { margin: 5px 0; color: #555; }
    </style>
</head>
<body>
    <h1>User Profiles</h1>
    <?php foreach ($xml->person as $person): ?>
        <div class="profile">
            <img src="<?php echo htmlspecialchars($person->picture); ?>" alt="Profile Picture">
            <div class="info">
                <h2><?php echo htmlspecialchars($person->firstname . ' ' . $person->lastname); ?></h2>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($person->email); ?></p>
                <p><strong>Resume:</strong> <?php echo htmlspecialchars($person->resume); ?></p>
            </div>
        </div>
    <?php endforeach; ?>
</body>
</html>
