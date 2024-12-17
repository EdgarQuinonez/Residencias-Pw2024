<?php
    try {
        require __DIR__ . "/../db.php";
        $requestMethod = $_SERVER['REQUEST_METHOD'];        
        
        switch ($requestMethod) {
            case 'GET':
                $reporteID = filter_input(INPUT_GET, 'id');                
                if (isset($reporteID)) {
                    $res = Reporte::get_reporte_by_id($reporteID);
                } else {
                    $res = Reporte::get_all();
                }
                break;
            case 'POST':
                $title = filter_input(INPUT_POST, 'title');                
                $publishDate = filter_input(INPUT_POST, 'publishDate');        
                $authors = filter_input(INPUT_POST, 'author'); // Should be an array of objs
                $asesorInterno = filter_input(INPUT_POST, 'asesorInterno');
                $asesorExterno = filter_input(INPUT_POST, 'asesorExterno');
                if (!isset($_FILES['file'])) {
                    throw new Exception("Ningún archivo cargado.");
                }
                $targetBase = __DIR__ . "/../public/reportes";
                $filename = basename($_FILES['file']['name']);
                $uri = "$targetBase/$filename";

                $uploadFileRes = save_to_file_system($uri);

                if (!$uploadFileRes['uploadOk']) {  
                    $errMsg = $uploadFileRes['message'];                  
                    throw new Exception("Hubo un error al subir el archivo: $errMsg");                    
                }
                $res = Reporte::upload_file($title, $authors, $publishDate, $asesorInterno, $asesorExterno, $uri);                
                break;
            case 'PUT':
                $request_vars = json_decode(file_get_contents("php://input"),true);
                $reporteID = $request_vars['reporteID'];
                $title = $request_vars['title'];                
                $authors = $request_vars['author'];

                if (empty($reporteID) || empty($reporteID) || empty($authors)) {
                    throw new Exception("Las variables reporteID, title y author no pueden estar indefinidas.");
                }

                $publishDate = $request_vars['publishDate'];
                $asesorInterno = $request_vars['asesorInterno'];
                $asesorExterno = $request_vars['asesorExterno'];

                $res = Reporte::update_reporte($reporteID, $title, $authors, $publishDate, $asesorInterno, $asesorExterno);
                break;
            case 'DELETE':
                parse_str(file_get_contents("php://input"),$request_vars);
                // TODO: Confirm wether .pdf is added here or in body request.
                $filename = $request_vars['filename'];
                $reporteID = $request_vars['id'];

                if (empty($filename) || empty($reporteID)) {
                    throw new Exception("Error al borrar el archivo: Las variables filename y reporteID deben de estar definidas.");
                }

                $targetBase = __DIR__ . "/../public/reportes";                
                $uri = "$targetBase/$filename";
                $path = realpath($uri);
                if (!$path) {
                    throw new Exception("Error al borrar el archivo: $filename no existe.");
                }
                delete_from_file_system($path);
                $res = Reporte::delete_file($reporteID);                
                break;
            default:
                throw new Exception("Método HTTP no soportado.");                
        }        
        echo json_encode($res);
    } catch (Exception $e) {
        echo json_encode($e->getMessage());
    }

    function save_to_file_system($targetFile) {
        try {

            $filetype = strtolower(pathinfo($targetFile,PATHINFO_EXTENSION));
            if ($filetype != 'pdf') {
                throw new Exception("Solo se admiten archivos PDF.");            
            }
    
            if (file_exists($targetFile)) {
                throw new Exception("El archivo cargado ya existe.");            
            }
            
            if($_FILES['file']['size'] > 50 * 1024 * 1024) {
                throw new Exception("El archivo cargado es demasiado grande (>50 MB).");            
            }
            
            $uploadOk = move_uploaded_file($_FILES['file']['tmp_name'], $targetFile);

            if (!$uploadOk) {
                throw new Exception("Error en move_uploaded_file.");
            }
                 
            return ['message'=>"Se subió el archivo con éxito.",'uploadOk'=>true];
        } catch (Exception $e) {
            return ['message'=>$e->getMessage(), 'uploadOk'=>false];
        }
    }

    function delete_from_file_system($targetFile) {
        if (!is_writable($targetFile)) {
            throw new Exception("No tienes permisos de escritura.");
        }

        unlink($targetFile);
    }
