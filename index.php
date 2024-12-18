<?php
    require __DIR__ . '/api/auth/rememberme.php';    
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Residencias</title>
    <link rel="stylesheet" href="globals.css">
    <link rel="stylesheet" href="style.css">  
    <script src="index.js" type="module" defer></script>      
</head>
<body>
    <nav>        
        <a>
            <button class="logo-container">

                <img src="public/assets/apple-logo.png" alt="Apple Logo" class="nav-logo">
            </button>
        </a>
        <div>            

                <button id="logoutBtn">
                    <p>
                        Cerrar Sesión
                    </p>
                </button>
            
        </div>
    </nav>
    <main class="container">
        <h1 class="title">Reportes de residencias</h1>
        <div>
            <button id="addReporte">
                <p>
                    <span>+</span> Nuevo
                </p>
            </button>
        </div>
        <?php include "./components/reportesTable.php" ?>
    </main>
    
    <?php include "./components/reporteForm.php" ?>

</body>
</html>