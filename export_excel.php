<?php
require_once 'config.php';
requireAdmin();

header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment; filename="mga_encoding_export_' . date('Y-m-d') . '.xls"');

// Fetch all data
$info_query = "SELECT * FROM information";
$info_result = $conn->query($info_query);

echo "<table border='1'>";
echo "<tr><th colspan='8'>Company Information</th></tr>";
echo "<tr>
    <th>ID</th>
    <th>Company Name</th>
    <th>Address</th>
    <th>TIN</th>
    <th>Month/Year</th>
    <th>Contact</th>
    <th>Email</th>
    <th>Submitted Date</th>
</tr>";

while($row = $info_result->fetch_assoc()) {
    echo "<tr>";
    echo "<td>" . $row['id'] . "</td>";
    echo "<td>" . $row['company_name'] . "</td>";
    echo "<td>" . $row['address'] . "</td>";
    echo "<td>" . $row['tin_number'] . "</td>";
    echo "<td>" . $row['month'] . " " . $row['year'] . "</td>";
    echo "<td>" . $row['contact_number'] . "</td>";
    echo "<td>" . $row['email'] . "</td>";
    echo "<td>" . $row['submitted_at'] . "</td>";
    echo "</tr>";
}
echo "</table>";

// Add other tables as needed...
?>