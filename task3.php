<?php
// Task 3: Encrypt Document

$uploadDir = __DIR__ . '/uploads/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// Encryption settings using OpenSSL (mcrypt is removed in modern PHP)
$encryptionKey = "my_secret_key_12345";
$cipherAlgo = "aes-256-cbc";

// Handle Download
if (isset($_GET['download'])) {
    $filename = basename($_GET['download']);
    $filepath = $uploadDir . $filename;
    
    if (file_exists($filepath)) {
        $encryptedContent = file_get_contents($filepath);
        
        // The first 16 bytes are the IV
        $ivLength = openssl_cipher_iv_length($cipherAlgo);
        $iv = substr($encryptedContent, 0, $ivLength);
        $ciphertext = substr($encryptedContent, $ivLength);
        
        $decryptedContent = openssl_decrypt($ciphertext, $cipherAlgo, $encryptionKey, 0, $iv);
        
        if ($decryptedContent === false) {
            die("Failed to decrypt file.");
        }
        
        // Remove .enc extension to get original name
        $originalName = preg_replace('/\.enc$/', '', $filename);
        
        // Determine mime type based on extension (basic)
        $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        $mimeTypes = [
            'pdf' => 'application/pdf',
            'png' => 'image/png',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg'
        ];
        $contentType = isset($mimeTypes[$ext]) ? $mimeTypes[$ext] : 'application/octet-stream';
        
        header('Content-Description: File Transfer');
        header('Content-Type: ' . $contentType);
        header('Content-Disposition: attachment; filename="' . $originalName . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . strlen($decryptedContent));
        
        echo $decryptedContent;
        exit;
    } else {
        die("File not found.");
    }
}

// Handle Upload
$message = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['document'])) {
    $file = $_FILES['document'];
    
    // Check for errors
    if ($file['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowedExts = ['pdf', 'jpeg', 'jpg', 'png'];
        
        if (in_array($ext, $allowedExts)) {
            $content = file_get_contents($file['tmp_name']);
            
            // Encrypt content
            $ivLength = openssl_cipher_iv_length($cipherAlgo);
            $iv = openssl_random_pseudo_bytes($ivLength);
            $encryptedContent = openssl_encrypt($content, $cipherAlgo, $encryptionKey, 0, $iv);
            
            // Prepend IV to encrypted content for decryption later
            $finalContent = $iv . $encryptedContent;
            
            // Save encrypted file
            $encryptedFileName = $file['name'] . '.enc';
            $destination = $uploadDir . $encryptedFileName;
            
            if (file_put_contents($destination, $finalContent)) {
                $message = "File uploaded and encrypted successfully!";
            } else {
                $message = "Failed to save encrypted file.";
            }
        } else {
            $message = "Invalid file type. Only PDF, JPEG, and PNG are allowed.";
        }
    } else {
        $message = "Error during file upload.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Task 3 - Encrypted Uploads</title>
    <style>
        body { font-family: sans-serif; padding: 20px; }
        .box { border: 1px solid #ccc; padding: 20px; margin-bottom: 20px; border-radius: 5px; }
        input[type=file], button { margin-top: 10px; }
        button { padding: 10px 15px; background: #007bff; color: white; border: none; border-radius: 3px; cursor: pointer; }
        button:hover { background: #0056b3; }
        a { color: #007bff; text-decoration: none; }
        a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <h1>Task 3: Upload and Encrypt Document</h1>
    
    <?php if ($message): ?>
        <p><strong><?php echo htmlspecialchars($message); ?></strong></p>
    <?php endif; ?>
    
    <div class="box">
        <h3>Upload File</h3>
        <form action="task3.php" method="POST" enctype="multipart/form-data">
            <label>Select a file (pdf, jpeg, png):</label><br>
            <input type="file" name="document" accept=".pdf,.jpeg,.jpg,.png" required><br>
            <button type="submit">Upload & Encrypt</button>
        </form>
    </div>
    
    <div class="box">
        <h3>Encrypted Documents on Server</h3>
        <ul>
            <?php
            $hasFiles = false;
            if (is_dir($uploadDir)) {
                $files = scandir($uploadDir);
                foreach ($files as $f) {
                    if ($f !== '.' && $f !== '..' && is_file($uploadDir . $f)) {
                        $hasFiles = true;
                        echo "<li>" . htmlspecialchars($f) . " - <a href='task3.php?download=" . urlencode($f) . "'>[Decrypt & Download]</a></li>";
                    }
                }
            }
            if (!$hasFiles) {
                echo "<li>No files found.</li>";
            }
            ?>
        </ul>
    </div>
</body>
</html>
