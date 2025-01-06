<?php
session_start();

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
    $price = $_POST['price'];
    $total_price = $quantity * $price;

    // Check if the item exists and retrieve its current quantity
    $stmt_check = $conn->prepare("SELECT item_id, quantity FROM item WHERE item_name = ?");
    $stmt_check->bind_param("s", $item_name);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();

    if ($result_check->num_rows > 0) {
        $row = $result_check->fetch_assoc();
        $item_id = $row['item_id'];
        $current_quantity = $row['quantity'];

        // Check if sufficient stock is available
        if ($current_quantity >= $quantity) {
            // Deduct the quantity from the `item` table
            $stmt_update_item = $conn->prepare("UPDATE item SET quantity = quantity - ? WHERE item_id = ?");
            $stmt_update_item->bind_param("ii", $quantity, $item_id);
            $stmt_update_item->execute();
            $stmt_update_item->close();

            // Deduct the quantity from the `inventory` table
            $stmt_update_inventory = $conn->prepare("UPDATE inventory SET quantity = quantity - ? WHERE item_name = ?");
            $stmt_update_inventory->bind_param("is", $quantity, $item_name);
            $stmt_update_inventory->execute();
            $stmt_update_inventory->close();

            // Insert the transaction into the `transaction` table
            $stmt_transaction = $conn->prepare("INSERT INTO transaction (item_id, quantity, price, transaction_date) VALUES (?, ?, ?, NOW())");
            $stmt_transaction->bind_param("iid", $item_id, $quantity, $price);

            if ($stmt_transaction->execute()) {
                echo "Transaction completed successfully!";
            } else {
                echo "Error adding transaction: " . $stmt_transaction->error;
            }

            $stmt_transaction->close();
        } else {
            echo "Error: Insufficient stock for $item_name. Current stock: $current_quantity.";
        }
    } else {
        echo "Error: Item $item_name not found.";
    }

    $stmt_check->close();
}

$conn->close();
?>

