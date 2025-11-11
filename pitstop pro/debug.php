<?php
// debug.php - Test database connection and data
require_once 'config.php';

echo "<h2>Database Debug Information</h2>";

// Test connection
if ($conn->ping()) {
    echo "✅ Database connected successfully!<br>";
} else {
    echo "❌ Database connection failed: " . $conn->error . "<br>";
    exit;
}

// Check tables and data
$tables = ['tbl_admins', 'tbl_bookings', 'tbl_customer', 'tbl_services', 'tbl_slot', 'tbl_bill'];

foreach ($tables as $table) {
    echo "<h3>Table: $table</h3>";
    
    $result = $conn->query("SELECT COUNT(*) as count FROM $table");
    if ($result) {
        $row = $result->fetch_assoc();
        echo "Records: " . $row['count'] . "<br>";
        
        if ($row['count'] > 0) {
            $data = $conn->query("SELECT * FROM $table LIMIT 3");
            echo "Sample data:<br>";
            while ($sample = $data->fetch_assoc()) {
                echo "<pre>" . print_r($sample, true) . "</pre>";
            }
        } else {
            echo "No data found<br>";
        }
    } else {
        echo "Error: " . $conn->error . "<br>";
    }
    echo "<hr>";
}
?>