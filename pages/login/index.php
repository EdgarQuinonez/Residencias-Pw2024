<?php
    // require_once __DIR__ . '/../../api/auth/rememberme.php';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión</title>
    <link rel="stylesheet" href="../../globals.css">
    <link rel="stylesheet" href="style.css">
    <script src="script.js" type="module" defer></script>
</head>
<body>

<form id="loginForm">
    <h2>Iniciar Sesión</h2>
    <label>
        <p>Usuario: </p>
        <input type="text" name="user" id="user">
    </label>
    <label>
        <p>Contraseña: </p>
        <input type="password" name="password" id="password">
    </label>

    <div class="form-btns-container">
        <a href="../register/index.php">

            <button type="button">
                <p>Registrarse</p>
            </button>
        </a>
        <button type="submit">
            <p>Siguiente</p>
        </button>
    </div>
</form>    
</body>
</html>