<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <title>Inloggen</title>
</head>
<body>
    <h1>Inloggen</h1>

    <?php if (!empty($error)): ?>
        <p style="color: red"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <form method="POST">
        <label for="gebruikersnaam">Gebruikersnaam:</label><br>
        <input type="text" name="gebruikersnaam" required><br><br>

        <label for="wachtwoord">Wachtwoord:</label><br>
        <input type="password" name="wachtwoord" required><br><br>

        <button type="submit">Inloggen</button>
    </form>
</body>
</html>
