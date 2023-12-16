<?php

$table = 'user';

try {
    $PDO = new PDO('mysql:host=localhost:3306;dbname=app', 'root', 'root');
} catch (Exception $e) {
    die('Error: ' . $e->getMessage());
}

function partial_update_fields_by_id($table, $fields)
{
    global $PDO;
    
    $id_name = $_POST['id_name'];
    $id_value = $_POST['id_value'];
    $fields = [];
    foreach ($_POST['fields'] as $field) {
        $fields[$field['key']] = $field['value'];
    }

    $sql = "UPDATE $table SET ";
    $i = 0;
    foreach ($fields as $field => $value)
    {
        $sql .= $field . ' = :' . $field;
        if ($i < count($fields) - 1)
        {
            $sql .= ', ';
        }
        $i++;
    }
    $sql .= " WHERE $id_name = $id_value";

    // echo $sql;

    $query = $PDO->prepare($sql);
    $query->execute($fields);

    return $query->rowCount() > 0 ? true : false;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['id_name'], $_POST['id_value'], $_POST['fields']) && is_array($_POST['fields'])) {
        $id_name = $_POST['id_name'];
        $id_value = $_POST['id_value'];
        $fields = [];
        foreach ($_POST['fields'] as $field) {
            if (isset($field['key'], $field['value'])) {
                $fields[$field['key']] = $field['value'];
            } else {
                echo "Error: Each field must be an object with a 'key' and 'value'.";
                exit;
            }
        }

        partial_update_fields_by_id($table, $fields);

    } else {
        echo "Error: 'id_name', 'id_value', and 'fields' must be set in POST data and 'fields' must be an array.";
    }
}

function fetchAll($PDO, $table) {
    $stmt = $PDO->prepare("SELECT * FROM $table");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$rows = fetchAll($PDO, $table);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Dashboard</title>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
</head>
<body>
    <table>
        <thead>
            <tr>
                <th>Key</th>
                <th>Value</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($rows as $rowIndex => $row): ?>
                <?php foreach ($row as $key => $value): ?>
                    <?php
                    // var_dump($row);
                    // var_dump(array_keys($row));
                    $id_name = array_keys($row)[0];
                    $id_value = $row[$id_name];
                    ?>
                    <tr>
                        <td><?= $key ?></td>
                        <td>
                            <?php
                                $type = gettype($value);
                                $inputType = 'text'; // default input type
                                if ($type == 'integer' || $type == 'double') {
                                    $inputType = 'number';
                                } else if ($type == 'boolean') {
                                    $inputType = 'checkbox';
                                } else if ($type == 'string' && strtotime($value) !== false) {
                                    $inputType = 'date';
                                    $value = date('Y-m-d', strtotime($value));
                                }
                            ?>
                            <input
                                id="input--<?= $id_name ?>--<?= $id_value ?>--<?= $key ?>"
                                type="<?= $inputType ?>"
                                value="<?= $value ?>"
                                maxlength="255"
                            />
                        </td>
                        <td>
                            <button id="save--<?= $id_name ?>--<?= $id_value ?>--<?= $key ?>" class="save">Save</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <tr>
                    <td colspan="3">
                        <button class="delete">Delete User</button>
                        <hr>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <script>
        $(document).ready(function() {
        $('.save').click(function() {
            let split_data = $(this).attr('id').replace('save', '').split('--');
            let id_name = split_data[1];
            let id_value = split_data[2];
            let key = split_data[3];
            let value = $('#input--' + id_name + '--' + id_value + '--' + key).val();
            let data = {
                'id_name': id_name,
                'id_value': id_value,
                'fields': [
                    {
                        'key': key,
                        'value': value
                    }
                ]
            };
            // Send AJAX request to server to save row data
            $.post('#', data, function(response) {
                // Handle response here
            }, 'json');
        });
    });
    </script>
</body>
</html>