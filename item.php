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

// Get the category_id from the URL
$category_id = isset($_GET['category_id']) ? intval($_GET['category_id']) : 0;

// Fetch Category Name
$category_name = "";
$sql_category = "SELECT category_name FROM category WHERE category_id = ?";
$stmt_category = $conn->prepare($sql_category);
$stmt_category->bind_param("i", $category_id);
$stmt_category->execute();
$result_category = $stmt_category->get_result();
if ($result_category->num_rows > 0) {
    $row_category = $result_category->fetch_assoc();
    $category_name = $row_category['category_name'];
} else {
    die("Invalid category.");
}

// Fetch Items for the Selected Category
$items = [];
$sql_items = "SELECT * FROM item WHERE category_id = ?";
$stmt_items = $conn->prepare($sql_items);
$stmt_items->bind_param("i", $category_id);
$stmt_items->execute();
$result_items = $stmt_items->get_result();
if ($result_items->num_rows > 0) {
    while ($row = $result_items->fetch_assoc()) {
        $items[] = $row;
    }
}

$stmt_category->close();
$stmt_items->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Items in <?php echo htmlspecialchars($category_name); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color:rgb(5, 31, 56);
        }
        .card {
            background-color:rgb(38, 93, 221);
            border: 1px solidrgb(0, 6, 12);
            border-radius: 8px;
            transition: transform 0.3s, box-shadow 0.3s;
        }
        .card:hover {
            transform: translateY(-10px);
            box-shadow: 0 6px 25px rgba(0, 0, 0, 0.1);
        }
        .card-title {
            color:rgb(210, 210, 210);
        }
        .card-text {
            color:rgb(248, 242, 242);
        }
        .btn-secondary {
            background-color: #007bff;
            border: none;
        }
        .btn-secondary:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <h1 class="fw-bold text-center" style="color: white;">Items in <?php echo htmlspecialchars($category_name); ?></h1>
        <p class="text-center mb-4" style="color: white;">Explore all items under this category</p>


        <div class="row">
            <?php if (!empty($items)): ?>
                <?php foreach ($items as $item): ?>
                    <!-- Dynamic Item Card -->
                    <div class="col-lg-4 col-md-6 mb-4">
                        <div class="card shadow-sm h-100">
                            <div class="card-body text-center">
                                <h5 class="card-title"><?php echo htmlspecialchars($item['item_name']); ?></h5>
                                <p class="card-text">Price: <strong><?php echo number_format($item['price'], 2). " Taka"; ?></strong></p>
                                <p class="card-text">Quantity: <strong><?php echo $item['quantity']; ?></strong></p>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="text-center text-muted">No items available in this category.</p>
            <?php endif; ?>
        </div>

        <div class="text-center mt-4">
            <a href="categ.php" class="btn btn-secondary">Back to Categories</a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
