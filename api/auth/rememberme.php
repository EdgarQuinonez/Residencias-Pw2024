<?php
    // Asumir que voy a llamar esta url una vez en el index.php o cada una de las rutas protegidas y también en el login para logearlo automaticamente (redirigir a index.php) en caso de tener cookie válida     
    $env = parse_ini_file(__DIR__ . "/../../.env");
    try {


        // if (!isset($_GET)) {
        //     throw new Exception("No se ha recibido la GET request.");
        // }

        global $env;
        $baseUrl = $env['BASE_URL'];

        require __DIR__ . '/../../db.php';
        $res = Token::remember_me();

        if (!$res['remembermeIsValid']) {

            header("Location: $baseUrl/pages/login/index.php");
        }
        // TODO: Comment out this line when rememberme, login and logout work properly
        // echo json_encode($res);        
                    
    } catch (Exception $e) {
        echo json_encode($e->getMessage());
    }
