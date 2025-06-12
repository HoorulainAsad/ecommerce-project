    <?php
    // admin/generate_hash.php
    // IMPORTANT: Delete this file after you have generated your new password hash!

    $new_password = 'password123msgm'; // <--- CHANGE THIS to your desired password!

    // Generate a new password hash using the same method as the login script expects
    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

    echo "Your new password: <strong>" . htmlspecialchars($new_password) . "</strong><br>";
    echo "Copy this HASH and paste it into the 'password' field in phpMyAdmin:<br>";
    echo "<textarea rows='3' cols='70' style='width:100%; max-width: 600px; padding: 10px; font-family: monospace;'>" . htmlspecialchars($hashed_password) . "</textarea>";
    echo "<br><br><strong>Remember to set the 'Function' dropdown to 'None' or 'Normal' when pasting the hash in phpMyAdmin.</strong>";
    echo "<br><br><strong>DELETE THIS FILE ('generate_hash.php') FROM YOUR SERVER IMMEDIATELY AFTER USE!</strong>";
    ?>
    