<?php    
    try {

        // if (!isset($_GET)) {
        //     throw new Exception("No se ha recibido la GET request.");
        // }

        require __DIR__ . '/../db.php';
    
        $res = Token::get_auth_user_session();
    
        echo json_encode($res);
    } catch (Exception $e) {
        echo json_encode($e->getMessage());
    }
