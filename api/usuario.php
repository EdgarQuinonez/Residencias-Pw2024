<?php
    try {
        require __DIR__ . "/../db.php";
        $requestMethod = $_SERVER['REQUEST_METHOD'];
        
        switch ($requestMethod) {
            case 'GET':
                $userId = filter_input(INPUT_GET, "id");
                if(isset($userId)) {
                    $res = User::get_user_by_username($username);
                } else {
                    $res = User::get_all();                
                }
                break;
            case 'DELETE':
                parse_str(file_get_contents("php://input"),$request_vars);
                $username = $request_vars['user'];
                if (empty($username)) {
                    throw new Exception("La id de usuario no puede ser nula.");
                }
                $res = User::delete_user($username);                
                break;
            case 'PUT':
                $request_vars = json_decode(file_get_contents("php://input"), true);

                $uid = $request_vars['uid'];
                $oldPassword = $request_vars['oldPassword'];
                $newPassword = $request_vars['newPassword'];

                if (empty($uid)) {
                    throw new Exception("La variable uid deben de estar definidas.");
                }

                if (empty($oldPassword) || empty($newPassword)) {
                    throw new Exception("Las variables oldPassword y newPassword deben de estar definidas.");
                }

                $res = User::update_password($uid, $oldPassword, $newPassword);

                break;
            default:
                throw new Exception("Método HTTP no soportado.");                
        }        
        echo json_encode($res);
    } catch (Exception $e) {
        echo json_encode($e->getMessage());
    }
