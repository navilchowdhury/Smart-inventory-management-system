<?php
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

// Handle Form Submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $item_name = $_POST['item_name'];
    $quantity = $_POST['quantity'];
    $threshold_quantity = $_POST['threshold_quantity'];
    $price = $_POST['price'];
    $category_id = $_POST['category_id'];

    // Check if the item exists in the inventory
    $stmt_check = $conn->prepare("SELECT quantity FROM inventory WHERE item_name = ?");
    $stmt_check->bind_param("s", $item_name);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();

    if ($result_check->num_rows > 0) {
        // Item exists: Update it
        $row = $result_check->fetch_assoc();
        $new_quantity = $row['quantity'] + $quantity;

        // Update inventory table
        $stmt_update_inventory = $conn->prepare(
            "UPDATE inventory SET quantity = ?, threshold_quantity = ?, price = ?, category_id = ? WHERE item_name = ?"
        );
        $stmt_update_inventory->bind_param("idiss", $new_quantity, $threshold_quantity, $price, $category_id, $item_name);

        if ($stmt_update_inventory->execute()) {
            echo "Inventory updated successfully!";
        } else {
            echo "Error updating inventory: " . $stmt_update_inventory->error;
        }
        $stmt_update_inventory->close();

        // Update item table
        $stmt_update_item = $conn->prepare(
            "UPDATE item SET quantity = ?, price = ?, category_id = ? WHERE item_name = ?"
        );
        $stmt_update_item->bind_param("idis", $new_quantity, $price, $category_id, $item_name);

        if ($stmt_update_item->execute()) {
            echo "Item updated successfully!";
        } else {
            echo "Error updating item: " . $stmt_update_item->error;
        }
        $stmt_update_item->close();
    } else {
        // Item does not exist: Insert a new item
        $stmt_insert_inventory = $conn->prepare(
            "INSERT INTO inventory (item_name, quantity, threshold_quantity, price, category_id) VALUES (?, ?, ?, ?, ?)"
        );
        $stmt_insert_inventory->bind_param("sidi", $item_name, $quantity, $threshold_quantity, $price, $category_id);

        if ($stmt_insert_inventory->execute()) {
            echo "Item added to inventory successfully!";
        } else {
            echo "Error adding item to inventory: " . $stmt_insert_inventory->error;
        }
        $stmt_insert_inventory->close();

        // Insert into item table
        $stmt_insert_item = $conn->prepare(
            "INSERT INTO item (item_name, quantity, price, category_id) VALUES (?, ?, ?, ?)"
        );
        $stmt_insert_item->bind_param("sidi", $item_name, $quantity, $price, $category_id);

        if ($stmt_insert_item->execute()) {
            echo "Item added successfully!";
        } else {
            echo "Error adding item: " . $stmt_insert_item->error;
        }
        $stmt_insert_item->close();
    }

    $stmt_check->close();
}

$conn->close();
?>


