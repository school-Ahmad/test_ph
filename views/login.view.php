<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <title>Inloggen</title>
</head>
<body>
    <h1>Inloggen</h1>

    <?php if (!empty($error)): ?>
        <p style="color:red"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <form method="POST">
        <input type="text" name="gebruikersnaam" placeholder="Gebruikersnaam" required><br>
        <input type="password" name="wachtwoord" placeholder="Wachtwoord" required><br>
        <button type="submit">Inloggen</button>
    </form>
</body>
</html>
