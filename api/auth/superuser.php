<?php

    try {

        if (!isset($_POST)) {
           throw new Exception("No se ha recibido la POST request.");
       }
   
       $username = filter_input(INPUT_POST, "user");
       $password = filter_input(INPUT_POST, "password");
   
       require __DIR__ . '/../../db.php';          
   
       $res = Superuser::register_superuser($username, $password);
       
        echo json_encode($res);        
    } catch (Exception $e) {
        echo json_encode($e->getMessage());
    }