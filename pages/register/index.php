<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrarse</title>
    <link rel="stylesheet" href="../../globals.css">
    <link rel="stylesheet" href="style.css">
    <script src="script.js" type="module" defer></script>
</head>
<body>

<form id="registerForm">
    <h2>Registrarse</h2>
    <label>
        <p>Usuario: </p>
        <input type="text" name="user" id="user">
    </label>
    <label>
        <p>Contraseña: </p>
        <input type="password" name="password" id="password">
    </label>

    <div class="form-btns-container">
        <a href="../login/index.php">
            <button type="button">
                <p>Iniciar Sesión</p>
            </button>
        </a>
        <button type="submit">
            <p>Siguiente</p>
        </button>
    </div>
</form>
    
</body>
</html>