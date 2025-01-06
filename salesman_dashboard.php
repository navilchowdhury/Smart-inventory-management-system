<?php
session_start();

// Check if the user is logged in and has the correct role
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Sales Man') {
    header("Location: login.php");
    exit();
}

// Database Connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "inventory_mng";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch Low Stock Alerts from the `alert` table
$alerts = [];
$sql = "SELECT alert_msg, threshold_quantity, created_at FROM alert";
$stmt = $conn->prepare($sql);

if ($stmt === false) {
    die("Error preparing statement: " . $conn->error);
}

$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $alerts[] = $row;
}

$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Salesman Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1>Welcome to the Salesman Dashboard</h1>
        
        <!-- Display Low Stock Alerts -->
        <h2>Low Stock Alerts</h2>
        <?php if (!empty($alerts)): ?>
            <div class="alert alert-warning">
                <strong>Warning!</strong> The following alerts have been generated:
                <ul>
                    <?php foreach ($alerts as $alert): ?>
                        <li>
                            <?php echo $alert['alert_msg']; ?> 
                            (Threshold: <?php echo $alert['threshold_quantity']; ?>, 
                            Time: <?php echo date('d-m-Y H:i:s', strtotime($alert['created_at'])); ?>)
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php else: ?>
            <div class="alert alert-success">
                No low stock alerts at the moment.
            </div>
        <?php endif; ?>


    <!-- Creating Sales Invoice -->
    <h2>Create Sales Invoice</h2>
    <form action="transaction.php" method="POST">
        <div class="mb-3">
            <label for="item_name" class="form-label">Item Name</label>
            <input type="text" class="form-control" id="item_name" name="item_name" required>
        </div>
        <div class="mb-3">
            <label for="quantity" class="form-label">Quantity</label>
            <input type="number" class="form-control" id="quantity" name="quantity" required>
        </div>
        <div class="mb-3">
            <label for="price" class="form-label">Price</label>
            <input type="number" step="0.01" class="form-control" id="price" name="price" required>
        </div>
        <button type="submit" class="btn btn-primary">Create Invoice</button>
    </form>
    <a href="logout.php" class="btn btn-danger mt-3">Logout</a>
    </div>
</body>
</html>

