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
        <label for="username">Gebruikersnaam:</label><br>
        <input type="text" name="username" required><br><br>

        <label for="password">Wachtwoord:</label><br>
        <input type="password" name="password" required><br><br>

        <button type="submit">Inloggen</button>
    </form>
</body>
</html>
