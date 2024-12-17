<?php
    try {

        if (!isset($_POST)) {
            throw new Exception("No se ha recibido la POST request.");
        }

        require __DIR__ . '/../../db.php';
                    
        $username = filter_input(INPUT_POST, "user");
        $password = filter_input(INPUT_POST,  "password");
            
        $res = User::login_user_with_password($username, $password);

        
                        
        echo json_encode($res);                             
    } catch (Exception $e) {
        echo json_encode($e->getMessage());
    }