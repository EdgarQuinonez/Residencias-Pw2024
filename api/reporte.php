<?php
    try {
        require __DIR__ . "/../db.php";
        $requestMethod = $_SERVER['REQUEST_METHOD'];
        
        switch ($requestMethod) {
            case 'GET':
                $res = Reporte::get_all();
                break;
            case 'POST':
                $title = filter_input(INPUT_POST, 'title');                
                $publishDate = filter_input(INPUT_POST, 'publishDate');        
                $authors = filter_input(INPUT_POST, 'author'); // Should be an array of objs
                $asesorInterno = filter_input(INPUT_POST, 'asesorInterno'); // Should be an array of objs
                $asesorExterno = filter_input(INPUT_POST, 'asesorExterno'); // Should be an array of objs
                if (!isset($_FILES['file'])) {
                    throw new Exception("Ningún archivo cargado.");
                }
                $targetBase = __DIR__ . "public/reportes";
                $filename = basename($_FILES['file']['name']);
                $uri = "$targetBase/$filename";

                $res = Reporte::upload_file($title, $authors, $publishDate, $asesorInterno, $asesorExterno, $uri);                
                break;

            default:
                throw new Exception("Método HTTP no soportado.");                
        }        
        echo json_encode($res);
    } catch (Exception $e) {
        echo json_encode($e->getMessage());
    }
