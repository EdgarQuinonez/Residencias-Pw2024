<?php
    // Asumir que voy a llamar esta url una vez en el index.php o cada una de las rutas protegidas y también en el login para logearlo automaticamente (redirigir a index.php) en caso de tener cookie válida     
    try {

        if (!isset($_GET)) {
            throw new Exception("No se ha recibido la GET request.");
        }

        require __DIR__ . '/../../db.php';
    
        $res = Token::remember_me();
    
        echo json_encode($res);
    } catch (Exception $e) {
        echo json_encode($e->getMessage());
    }
