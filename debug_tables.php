<?php
// debug_tables.php
require_once 'config.php';
requireAdmin();

echo "<h2>Database Table Structure</h2>";

$tables = ['vatable_sales', 'non_vat_sales', 'vatable_purchases', 'non_vat_purchases', 
           'vatable_expenses', 'non_vat_expenses', 'capex', 'taxes_licenses'];

foreach ($tables as $table) {
    echo "<h3>Table: $table</h3>";
    $result = $conn->query("SHOW COLUMNS FROM $table");
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    while($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>{$row['Field']}</td>";
        echo "<td>{$row['Type']}</td>";
        echo "<td>{$row['Null']}</td>";
        echo "<td>{$row['Key']}</td>";
        echo "<td>{$row['Default']}</td>";
        echo "<td>{$row['Extra']}</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Also show some sample data
    echo "<h4>Sample Data (First 5 rows):</h4>";
    $data = $conn->query("SELECT * FROM $table LIMIT 5");
    if ($data->num_rows > 0) {
        echo "<table border='1' cellpadding='5'>";
        $fields = $data->fetch_fields();
        echo "<tr>";
        foreach ($fields as $field) {
            echo "<th>{$field->name}</th>";
        }
        echo "</tr>";
        
        $data->data_seek(0);
        while($row = $data->fetch_assoc()) {
            echo "<tr>";
            foreach($row as $value) {
                echo "<td>" . htmlspecialchars($value) . "</td>";
            }
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>No data in table</p>";
    }
    echo "<hr>";
}
?>