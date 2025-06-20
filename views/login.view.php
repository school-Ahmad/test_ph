<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-A">
    <title>Inloggen</title>
</head>
<body>
    <h1>Inloggen</h1>

    <form id="loginForm">
        <input type="text" name="username" placeholder="Gebruikersnaam" required><br>
        <input type="password" name="password" placeholder="Wachtwoord" required><br>
        <button type="submit">Inloggen</button>
    </form>

    <p id="error" style="color:red;"></p>

    <script>
    document.getElementById('loginForm').addEventListener('submit', async function (e) {
        e.preventDefault();

        const form = e.target;
        const formData = new FormData(form);
        const payload = new URLSearchParams(formData);

        const res = await fetch('https://api.interpol.sd-lab.nl/api/create-session', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: payload
        });

        let data;
        try {
            // Lees de body maar één keer als JSON
            data = await res.json();
        } catch (err) {
            console.log(err);
            document.getElementById('error').textContent = 'Server gaf geen geldige JSON terug.';
            return;
        }

        if (data.message && data.session) {
            // Sessie lokaal in PHP opslaan
            const setSession = await fetch('index.php?page=set-session', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ session: data.session })
            });

            const setResult = await setSession.json();

            if (setResult.status === 'ok') {
                if (data.session.ingelogdAls === 'DOCENT') {
                    window.location = 'index.php?page=dashboard';
                } else {
                    window.location = '../klant/views/index.php';
                }
            } else {
                document.getElementById('error').textContent = 'Kon sessie niet aanmaken.';
            }
        } else {
            document.getElementById('error').textContent = data.error ?? 'Ongeldige inloggegevens.';
        }
    });
    </script>
</body>
</html>