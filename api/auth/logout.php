<?php
    try {

        require __DIR__ . "/../../db.php";
        
        $res = User::logout_user();
        echo json_encode($res);
    } catch (Exception $e) {
        echo json_encode($e->getMessage());
    }

