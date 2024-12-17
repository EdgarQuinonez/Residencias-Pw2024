<?php
    try {
        require __DIR__ . "/../db.php";
        $requestMethod = $_SERVER['REQUEST_METHOD'];
        
        switch ($requestMethod) {
            case 'GET':
                $res = User::get_all();                
                break;
            case 'DELETE':                
                break;
            case 'PUT':
                break;
            default:
                throw new Exception("Método HTTP no soportado.");                
        }        
        echo json_encode($res);
    } catch (Exception $e) {
        echo json_encode($e->getMessage());
    }
