<?php    
    try {
     
        require __DIR__ . '/../db.php';

        $column = filter_input(INPUT_GET, 'column');
        $value = filter_input(INPUT_GET, 'value');
    
        $res = Reporte::filter_by_column($column, $value);
    
        echo json_encode($res);
    } catch (Exception $e) {
        echo json_encode($e->getMessage());
    }
