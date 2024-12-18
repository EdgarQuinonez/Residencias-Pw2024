<?php
    try {
        $env = parse_ini_file(__DIR__ . "/../../.env");
        $baseUrl = $env['BASE_URL'];


        require __DIR__ . "/../../db.php";
        
        $res = User::logout_user();    
        echo json_encode($res);
        // header("Location: $baseUrl/index.php");
    } catch (Exception $e) {
        echo json_encode($e->getMessage());
    }

