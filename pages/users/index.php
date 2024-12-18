<?php
    require __DIR__ . '/../../api/auth/isAdmin.php';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Usuarios</title>
    <link rel="stylesheet" href="../../globals.css">
    <link rel="stylesheet" href="style.css">
    <script src="script.js" type="module" defer></script>
</head>
<body>
<nav>        
        <a href="../../index.php">
            <button class="logo-container">
                <img src="../../public/assets/apple-logo.png" alt="Apple Logo" class="nav-logo">
            </button>
        </a>
        <a id="userLink" href="./index.php">
            <button>
                <p>Usuarios</p>
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
        <h1 class="title">Administración de usuarios</h1>
        <table id="usersTable" class="table">
    <!-- Header -->
    <thead class="table-header">
        <tr class="header-row">
            <th class="header-cell">
                <p>Usuario</p>                
            </th>
            <th class="header-cell"><p>Rol</p></th>                                    
            <th class="header-cell"><p>Creado en</p></th>
            <th class="header-cell"><p>Controles</p></th>
        </tr>
    </thead>
    <tbody class="table-body"></tbody>
</table>

    </main>
    
</body>
</html>