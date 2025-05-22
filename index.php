<?php
$head = '<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Liste de plantes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-SgOJa3DmI69IUzQ2PVdRZhwQ+dy64/BUtbMJw1MZ8t5HZApcHrRKUc4W0kG879m7" crossorigin="anonymous">
  </head>
  <body>';
echo $head;
$footer = '
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js" integrity="sha384-k6d4wzSIapyDyv1kpU366/PK5hCdSbCRGRCMv+eplOQJWyd1fbcAu9OCUj5zNLiq" crossorigin="anonymous"></script>
  </body>
</html>';
require_once('db.php');
define('BASE_PATH', '/plantsdb/index.php/' );

$modelsArray = [
    'plantes' => 'plants',
];

$requestUri = $_SERVER['REQUEST_URI'];
$requestMethod = $_SERVER['REQUEST_METHOD'];

$requestUri = str_replace(BASE_PATH, '', $requestUri);

$requestUriArray = explode('/', $requestUri);
$requestUriArray = array_filter($requestUriArray, function($value) {
    return $value !== '';
});


if (empty($requestUriArray)) {
//if (count($requestUriArray) === 0) {
    echo 'Homepage';
    die;
}

$model = $requestUriArray[0];

if (count($requestUriArray) === 1) {
    // Vue "liste"
    echo 'Model: ' . $model;
    $tableName  = $modelsArray[$model] ?? false;
    if (!$tableName) {
        echo 'Invalid model';
        die;
    }
    $results = fetchAll($db, $tableName);
    $firstRow = $results[0];
    $columns = array_keys($firstRow);

    $str = '<table class="table">';
    $str .= '<thead>';

    // Boucle pour les colonnes
    foreach ($columns as $col) {
        $str .= '<th>' . $col . '</th>';
    }

    $str .= '</thead>';
    $str .= '<tbody>';

    // Boucle pour les lignes
    foreach($results as $row) {
        $str .= '<tr>';
        foreach ($row as $col => $value) {
            $str .= '<td>' . $value . '</td>';
        }
        $str .= '<td>';
        $str .= '<a href="'. BASE_PATH . $model . '/' . $row['id'] .'">View</a>';
        $str .= '</td>';
        $str .= '<td>';
        $str .= '<a href="'. BASE_PATH . $model . '/' . $row['id'] .'/edit">Edit</a>';
        $str .= '</td>';
        $str .= '<td>';
        $str .= '<a href="'. BASE_PATH . $model . '/' . $row['id'] .'/delete">Delete</a>';
        $str .= '</td>';       
        $str .= '</tr>';
    }

        $str .= '<td>';
        $str .= '<a href="'. BASE_PATH . $model .'/add">Add new</a>';
        $str .= '</td>';  

    $str .= '</tbody>';
    $str .= '</table>';

    echo $str;
    echo $footer;
}

if (count($requestUriArray) === 2) {
    $isAdd = $requestUriArray[1] === 'add';
    if ($isAdd && $requestMethod === 'GET') {
        // Vue "ajout"
        echo 'Add<br>';
        echo '<a href='. BASE_PATH . $model . '>Back to '. $model .'</a><br>';

        $tableName  = $modelsArray[$model] ?? false;
        if (!$tableName) {
            echo 'Invalid model';
            die;
        }

        $columns = getColumns($db, $tableName);
        //var_dump($columns);

        /*$results = fetchAll($db, $tableName);
        $firstRow = $results[0];
        $columns = array_keys($firstRow);
        $columns = array_filter($columns, function($col) {
            return $col !== 'id';
        });*/
        $str = '<form method="POST" action="">';
        // Boucle pour les colonnes
        foreach ($columns as $col) {
            // if ($col === 'id') {
            //     continue;
            // }
            $columnName = $col['COLUMN_NAME'];
            $columnType = $col['DATA_TYPE'];
            $isNullable = $col['IS_NULLABLE'];

            if ($columnName === 'id') {
                continue;
            }

            $inputStr = '<input name="' . $columnName . '" placeholder="' . $columnName . '" ';

            if ($columnType === 'int' || $columnType === 'double') {
                $inputStr .= 'type="number" ';
            }

            if ($columnType === 'date') {
                $inputStr .= 'type="date" ';
            }

            $inputStr .= '/>';

            $str .= $inputStr;
            $str .= '<br>';

            // $str .= '<input type="text" name="' . $col . '" placeholder="' . $col . '" /><br>';
        }

        $str .= '<input type="submit" value="Save" />';

        $str .= '</form>';
        echo $str;
        die;
    }

    if ($isAdd && $requestMethod === 'POST') {
        // Enregistrer le nouveau modèle
        $tableName  = $modelsArray[$model] ?? false;
        if (!$tableName) {
            echo 'Invalid model';
            die;
        }
        save($db, $tableName, $_POST);
        die;
    }

    if (ctype_digit($requestUriArray[1])) {
        // Vue "détail"
        echo 'Detail<br>';
        echo '<a href='. BASE_PATH . $model . '>Back to '. $model .'</a><br>';
        echo '<a href='. BASE_PATH . $model . '/'.$requestUriArray[1].'/edit>Edit</a><br>';
        $tableName  = $modelsArray[$model] ?? false;
        if (!$tableName) {
            echo 'Invalid model';
            die;
        }
        $id = $requestUriArray[1];
        $result = fetchById($db, $tableName, $id);
        foreach ($result as $col => $value) {
            echo $col . ': ' . $value . '<br>';
        }
        die;
    }

    echo 'Invalid id or action';
    die;
}

if (count($requestUriArray) === 3) {
    // Vérifier que l'id est numérique
    $id = $requestUriArray[1];
    $isValidId = ctype_digit($requestUriArray[1]); // Vérifier que le 2e element est numérique

    if (!$isValidId) {
        echo 'Invalid ID';
        die;
    }

    // Vérifier que le 3e element est "edit" ou "delete"
    if ($requestUriArray[2] !== 'edit' && $requestUriArray[2] !== 'delete') {
        echo 'Invalid action';
        die;
    }

    if ($requestUriArray[2] === 'delete') {
        // Comportement de suppression

        // 1 - vérifier la validité du modèle
        $tableName  = $modelsArray[$model] ?? false;
        if (!$tableName) {
            echo 'Invalid model';
            die;
        }
        // 2 - vérifier qu'il y a une ligne avec l'id spécifié
        $result = fetchById($db, $tableName, $id);

        if (!$result) {
            echo 'Invalid ID';
            die;
        }
        // 3 - effectuer la suppression
        deleteById($db, $tableName, $id);

        // 4 - rediriger vers la vue liste
        header('Location: ' . BASE_PATH . $model . '/');
    }

    echo 'Edit';

    if ($requestMethod === 'GET') {
        // Afficher le formulaire
        $tableName  = $modelsArray[$model] ?? false;
        if (!$tableName) {
            echo 'Invalid model';
            die;
        }
        $result = fetchById($db, $tableName, $id);

        if (!$result) {
            echo 'Invalid ID';
            die;
        }

        $columns = getColumns($db, $tableName);
        //var_dump($columns);

        /*$results = fetchAll($db, $tableName);
        $firstRow = $results[0];
        $columns = array_keys($firstRow);
        $columns = array_filter($columns, function($col) {
            return $col !== 'id';
        });*/
        $str = '<form method="POST" action="">';
        // Boucle pour les colonnes
        foreach ($columns as $col) {
            // if ($col === 'id') {
            //     continue;
            // }
            $columnName = $col['COLUMN_NAME'];
            $columnType = $col['DATA_TYPE'];
            $isNullable = $col['IS_NULLABLE'];

            if ($columnName === 'id') {
                continue;
            }

            $inputStr = '<input name="' . $columnName . '" placeholder="' . $columnName . '" value="' . $result[$columnName] . '" ';

            if ($columnType === 'int' || $columnType === 'double') {
                $inputStr .= 'type="number" ';
            }

            if ($columnType === 'date') {
                $inputStr .= 'type="date" ';
            }

            $inputStr .= '/>';

            $str .= $inputStr;
            $str .= '<br>';

            // $str .= '<input type="text" name="' . $col . '" placeholder="' . $col . '" /><br>';
        }

        $str .= '<input type="submit" value="Save" />';

        $str .= '</form>';
        echo $str;
        die;
    }

    if ($requestMethod === 'POST') {
        // Enregistrer les modifications
        $tableName  = $modelsArray[$model] ?? false;
        if (!$tableName) {
            echo 'Invalid model';
            die;
        }
        edit($db, $tableName, $_POST, $id);

        header('Location: ' . BASE_PATH . $model . '/' . $id);
    }
}

// '' => 0
// videogames => 1 => afficher toutes les données de ce type de ressource
// videogames/1 => 2 => afficher les données de la ressource avec l'id numérique
// videogames/add => 2 => afficher le formulaire d'ajout
// videogames/1/edit => 3 => afficher le formulaire d'édition
// videogames/1/delete => 3 => supprimer la ressource