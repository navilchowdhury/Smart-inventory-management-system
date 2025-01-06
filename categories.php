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

// Handle Form Submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $category_name = $_POST['category_name'];
    $description = $_POST['description'];

    // Insert category into the database
    $stmt = $conn->prepare("INSERT INTO category (category_name, description) VALUES (?, ?)");
    if ($stmt === false) {
        die("Error preparing statement: " . $conn->error);
    }
    $stmt->bind_param("ss", $category_name, $description);

    if ($stmt->execute()) {
        // Redirect back to the manager dashboard with a success message
        header("Location: manager_dashboard.php?message=Category added successfully!");
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
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
</head>
<body>
    <div class="container mt-5">
        <h1 class="fw-bold text-center">Product Categories</h1>
        <p class="text-muted text-center">Explore our diverse range of product categories</p>

        <div class="row mt-4">
            <?php if (!empty($categories)): ?>
                <?php foreach ($categories as $category): ?>
                    <!-- Dynamic Category Card -->
                    <div class="col-lg-4 mb-4">

                        <div class="card shadow-sm h-100">
                            <img src="<?php echo htmlspecialchars($category['image_url']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($category['category_name']); ?>">
                            <div class="card-body text-center">
                                <h5 class="card-title"><?php echo htmlspecialchars($category['category_name']); ?></h5>
                                <p class="card-text"><?php echo htmlspecialchars($category['description']); ?></p>
                                <a href="item.php?category_id=<?php echo $category['category_id']; ?>" class="btn btn-primary">
                                    View <?php echo htmlspecialchars($category['category_name']); ?>
                                </a>
                            </div>
                        </div>

                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="text-center">No categories available.</p>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
