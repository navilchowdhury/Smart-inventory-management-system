<?php
session_start();

// Check if the user is logged in and has the correct role
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'manager') {
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

// Fetch Items with Low Stock
$alerts = [];
$sql_inventory = "SELECT item_name, quantity, threshold_quantity FROM inventory WHERE quantity < threshold_quantity";
$result_inventory = $conn->query($sql_inventory);

if ($result_inventory->num_rows > 0) {
    while ($row = $result_inventory->fetch_assoc()) {
        $item_name = $row['item_name'];
        $quantity = $row['quantity'];
        $threshold_quantity = $row['threshold_quantity'];

        // Generate alert message
        $alert_msg = "Low stock for $item_name (Quantity: $quantity, Threshold: $threshold_quantity)";

        // Check if alert already exists
        $sql_check = "SELECT alert_id FROM alert WHERE alert_msg = ?";
        $stmt_check = $conn->prepare($sql_check);
        $stmt_check->bind_param("s", $alert_msg);
        $stmt_check->execute();
        $result_check = $stmt_check->get_result();

        if ($result_check->num_rows === 0) {
            // Insert new alert into the alert table
            $sql_insert = "INSERT INTO alert (alert_msg, threshold_quantity) VALUES (?, ?)";
            $stmt_insert = $conn->prepare($sql_insert);
            $stmt_insert->bind_param("si", $alert_msg, $threshold_quantity);
            $stmt_insert->execute();
            $stmt_insert->close();
        }
        $stmt_check->close();
    }
}
// Remove alerts for items no longer low in stock
$sql_remove_alerts = "SELECT item_name FROM inventory WHERE quantity >= threshold_quantity";
$result_remove_alerts = $conn->query($sql_remove_alerts);

if ($result_remove_alerts->num_rows > 0) {
    while ($row = $result_remove_alerts->fetch_assoc()) {
        $item_name = $row['item_name'];

        // Delete the alert for this item
        $sql_delete_alert = "DELETE FROM alert WHERE alert_msg LIKE ?";
        $stmt_delete = $conn->prepare($sql_delete_alert);
        $like_pattern = "Low stock for $item_name%";
        $stmt_delete->bind_param("s", $like_pattern);
        $stmt_delete->execute();
        $stmt_delete->close();
    }
}

// Fetch Low Stock Alerts from the alert table
$sql_alert = "SELECT alert_msg, threshold_quantity, created_at FROM alert";
$result_alert = $conn->query($sql_alert);

if ($result_alert->num_rows > 0) {
    while ($row = $result_alert->fetch_assoc()) {
        $alerts[] = $row;
    }
}

// Fetch Categories
$categories = [];
$sql_category = "SELECT * FROM category";
$result_category = $conn->query($sql_category);

if ($result_category->num_rows > 0) {
    while ($row = $result_category->fetch_assoc()) {
        $categories[] = $row;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manager Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1>Welcome to the Manager Dashboard</h1>
        
        <!-- Display Low Stock Alerts -->
        <h2>Low Stock Alerts</h2>
        <?php if (!empty($alerts)): ?>
            <div class="alert alert-warning">
                <strong>Warning!</strong> The following alerts have been generated:
                <ul>
                    <?php foreach ($alerts as $alert): ?>
                        <li>
                            <?php echo htmlspecialchars($alert['alert_msg']); ?> 
                            (Threshold: <?php echo htmlspecialchars($alert['threshold_quantity']); ?>, 
                            Time: <?php echo htmlspecialchars(date('d-m-Y H:i:s', strtotime($alert['created_at']))); ?>)
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php else: ?>
            <div class="alert alert-success">
                No low stock alerts at the moment.
            </div>
        <?php endif; ?>

        <!-- Add New Category -->
        <h2 class="mt-5">Add Product Category</h2>
        <form action="categories.php" method="POST">
            <div class="mb-3">
                <label for="category_name" class="form-label">Category Name</label>
                <input type="text" class="form-control" id="category_name" name="category_name" required>
            </div>
            <div class="mb-3">
                <label for="description" class="form-label">Description</label>
                <textarea class="form-control" id="description" name="description" rows="3" required></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Add Category</button>
        </form>

        <!-- Display Categories -->
        <h2 class="mt-5">Existing Categories</h2>
        <?php if (!empty($categories)): ?>
            <ul class="list-group">
                <?php foreach ($categories as $category): ?>
                    <li class="list-group-item">
                        <strong><?php echo htmlspecialchars($category['category_name']); ?></strong>: 
                        <?php echo htmlspecialchars($category['description']); ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p>No categories available.</p>
        <?php endif; ?>

        <!-- Updating Inventory Items -->
        <h2 class="mt-5">Update Inventory Items</h2>
        <form action="inventory.php" method="POST">
            <div class="mb-3">
                <label for="item_name" class="form-label">Item Name</label>
                <input type="text" class="form-control" id="item_name" name="item_name" required>
            </div>
            <div class="mb-3">
                <label for="quantity" class="form-label">New Quantity</label>
                <input type="number" class="form-control" id="quantity" name="quantity" required>
            </div>
            <div class="mb-3">
                <label for="threshold_quantity" class="form-label">Threshold Quantity</label>
                <input type="number" class="form-control" id="threshold_quantity" name="threshold_quantity" required>
            </div>
            <div class="mb-3">
                <label for="price" class="form-label">New Price</label>
                <input type="number" step="0.01" class="form-control" id="price" name="price">
            </div>
            <div class="mb-3">
                <label for="category_id" class="form-label">Category</label>
                <select class="form-select" id="category_id" name="category_id" required>
                    <option value="">Select a category</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?php echo htmlspecialchars($category['category_id']); ?>">
                            <?php echo htmlspecialchars($category['category_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Update Inventory</button>
        </form>

        <a href="logout.php" class="btn btn-danger mt-5">Logout</a>
    </div>
</body>
</html>
