<?php

try {
    $db = new PDO('mysql:host=localhost;dbname=plant_db', 'root', '');
} catch (PDOException $e) {
    echo 'Connection failed: ' . $e->getMessage();
    die;
}

function fetchAll($connection, $table) {
    $query = $connection->prepare("SELECT * FROM ".$table);
    $query->execute();
    $plants = $query->fetchAll(PDO::FETCH_ASSOC);

    foreach ($plants as &$plant) {
        $plant['pet_friendly'] = ($plant['pet_friendly'] == 1) ? 'Oui' : 'Non';
    }
    return $plants;
}

function fetchById($connection, $table, $id) {
    $query = $connection->prepare("SELECT * FROM ".$table." WHERE id = :id");
    $query->bindParam(':id', $id);
    $query->execute();
    return $query->fetch(PDO::FETCH_ASSOC);
}

function getColumns($connection, $table) {
    $sql = "SELECT * FROM information_schema.COLUMNS WHERE TABLE_NAME = '".$table."'";
    $query = $connection->prepare($sql);
    $query->execute();
    return $query->fetchAll(PDO::FETCH_ASSOC);
}

function save($connection, $table, $data) {
    // INSERT INTO $table (columns...) VALUES (values...);
    // INSERT INTO jeux_video (nom, possesseur) VALUES (:nom, :possesseur)
    $columns = array_keys($data);
    $columnsStr = implode(', ', $columns); // split ; join
    $placeholdersStr = ':' . implode(', :', $columns); // split ; join
    $sql = 'INSERT INTO ' . $table . ' (' . $columnsStr . ') VALUES (' . $placeholdersStr . ');';
    try {
        $query = $connection->prepare($sql);
        foreach ($data as $key => $value) {
            $query->bindValue(':'.$key, $value);
            echo 'value => ' . $value . '<br>';
            // $query->bindParam(':nom', valeur de nom dans $data);
        }
        $query->execute();
    } catch (PDOException $e) {
        echo 'Error: ' . $e->getMessage();
        die;
    }
}

function edit($connection, $table, $data, $id) {
    // INSERT INTO $table (columns...) VALUES (values...);
    // INSERT INTO jeux_video (nom, possesseur) VALUES (:nom, :possesseur)
    $columns = array_keys($data);
    $columnsStr = implode(', ', $columns); // split ; join
    $placeholdersStr = ':' . implode(', :', $columns); // split ; join
    $sql = 'UPDATE ' . $table . ' SET ';
    foreach ($columns as $col) {
        $sql .= $col . ' = :' . $col . ', ';
        // name = :name, possesseur = :possesseur, 
    }

    $sql = rtrim($sql, ', '); // remove last comma
    $sql .= ' WHERE id = :id';

    // UPDATE jeux_video SET name = :name, possesseur = :possesseur WHERE id = :id

    try {
        $query = $connection->prepare($sql);
        foreach ($data as $key => $value) {
            $query->bindValue(':'.$key, $value !== '' ? $value : null);
            echo 'value => ' . $value . '<br>';
            // $query->bindParam(':nom', valeur de nom dans $data);
        }
        $query->bindValue(':id', $id);
        $query->execute();
    } catch (PDOException $e) {
        echo 'Error: ' . $e->getMessage();
        die;
    }
}

function deleteById($connection, $table, $id) {
    $query = $connection->prepare('DELETE FROM ' . $table . ' WHERE id=:id;');
    $query->bindParam(':id', $id);
    $query->execute();
    return;
}


