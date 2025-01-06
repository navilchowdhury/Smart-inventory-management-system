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
     $category_name = $_POST['category_name'];
     $description = $_POST['description'];


 }




// Fetch Categories
$categories = [];
$sql = "SELECT * FROM category";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
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
    <title>Categories - InventoryPro</title>
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
        .btn-primary {
            background-color:rgb(160, 31, 61);
            border-color:rgb(3, 8, 13);
        }
        .btn-primary:hover {
            background-color: #0056b3;
            border-color: #004085;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
    <h1 class="fw-bold text-center" style="color: white;">Product Categories</h1>
        <p class="text-center mb-4" style="color: white;">Explore our diverse range of product categories</p>

        <div class="row">
            <?php if (!empty($categories)): ?>
                <?php foreach ($categories as $category): ?>
                    <!-- Dynamic Category Card -->
                    <div class="col-lg-4 col-md-6 mb-4">
                        <div class="card h-100 shadow-sm">


                            <div class="card-body text-center">
                                <h5 class="card-title"><?php echo htmlspecialchars($category['category_name']); ?></h5>
                                <p class="text-center mb-4" style="color: white;"><?php echo htmlspecialchars($category['description']); ?></p>
                                <a href="item.php?category_id=<?php echo $category['category_id']; ?>" class="btn btn-primary">
                                    View <?php echo htmlspecialchars($category['category_name']); ?>
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="text-center text-muted">No categories available. Check back soon!</p>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
