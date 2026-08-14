
<?php


$servername = "localhost";
$username   = "root";
$password   = "";
$dbname     = "farm_db";

$conn = mysqli_connect(
    $servername,
    $username,
    $password,
    $dbname
);

if (!$conn) {
    die("Database Connection Failed: " . mysqli_connect_error());
}

mysqli_set_charset($conn, "utf8mb4");


/* ================= SESSION CHECK ================= */

session_start();

if (
    !isset($_SESSION['user_id']) ||
    !isset($_SESSION['user_type']) ||
    $_SESSION['user_type'] !== 'buyer'
) {
    header("Location: login.php");
    exit();
}

$user_id   = (int) $_SESSION['user_id'];
$user_name = $_SESSION['user_name'] ?? 'Buyer';


/* =========================================================
   1. BUYER INFORMATION
   ========================================================= */

$user_query = "
    SELECT 
        id,
        name,
        full_name,
        email,
        phone,
        address
    FROM users
    WHERE id = ?
      AND user_type = 'buyer'
    LIMIT 1
";

$stmt = mysqli_prepare($conn, $user_query);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);

$user_result = mysqli_stmt_get_result($stmt);
$user_data   = mysqli_fetch_assoc($user_result);

mysqli_stmt_close($stmt);

if ($user_data) {
    $user_name = !empty($user_data['full_name'])
        ? $user_data['full_name']
        : $user_data['name'];
}


/* =========================================================
   2. TOTAL ORDERS
   ========================================================= */

$total_orders = 0;

$query = "
    SELECT COUNT(*) AS total
    FROM orders
    WHERE buyer_id = ?
";

$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$row = mysqli_fetch_assoc($result);

$total_orders = (int) ($row['total'] ?? 0);

mysqli_stmt_close($stmt);


/* =========================================================
   3. PENDING / PROCESSING ORDERS
   ========================================================= */

$pending_orders = 0;

$query = "
    SELECT COUNT(*) AS total
    FROM orders
    WHERE buyer_id = ?
    AND status IN ('pending', 'processing', 'shipped')
";

$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$row = mysqli_fetch_assoc($result);

$pending_orders = (int) ($row['total'] ?? 0);

mysqli_stmt_close($stmt);


/* =========================================================
   4. DELIVERED ORDERS
   ========================================================= */

$delivered_orders = 0;

$query = "
    SELECT COUNT(*) AS total
    FROM orders
    WHERE buyer_id = ?
    AND status = 'delivered'
";

$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$row = mysqli_fetch_assoc($result);

$delivered_orders = (int) ($row['total'] ?? 0);

mysqli_stmt_close($stmt);


/* =========================================================
   5. WISHLIST COUNT
   ========================================================= */

$wishlist_count = 0;

$query = "
    SELECT COUNT(*) AS total
    FROM wishlist
    WHERE user_id = ?
";

$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$row = mysqli_fetch_assoc($result);

$wishlist_count = (int) ($row['total'] ?? 0);

mysqli_stmt_close($stmt);


/* =========================================================
   6. RECENT ORDERS
   ========================================================= */

$recent_orders = [];

$query = "
    SELECT
        o.id AS order_id,
        o.status,
        o.created_at,
        oi.quantity,
        oi.price,
        p.name AS product_name,
        f.name AS farmer_name
    FROM orders o

    INNER JOIN order_items oi
        ON o.id = oi.order_id

    INNER JOIN products p
        ON oi.product_id = p.id

    LEFT JOIN farmers f
        ON p.farmer_id = f.id

    WHERE o.buyer_id = ?

    ORDER BY o.created_at DESC

    LIMIT 5
";

$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

while ($row = mysqli_fetch_assoc($result)) {
    $recent_orders[] = $row;
}

mysqli_stmt_close($stmt);


/* =========================================================
   7. WISHLIST PRODUCTS
   ========================================================= */

$wishlist_products = [];

$query = "
    SELECT
        p.id,
        p.name,
        p.price,
        p.unit,
        p.image,
        p.location,
        p.stock,
        f.name AS farmer_name

    FROM wishlist w

    INNER JOIN products p
        ON w.product_id = p.id

    LEFT JOIN farmers f
        ON p.farmer_id = f.id

    WHERE w.user_id = ?
    AND p.status = 'active'

    ORDER BY w.added_at DESC

    LIMIT 6
";

$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

while ($row = mysqli_fetch_assoc($result)) {
    $wishlist_products[] = $row;
}

mysqli_stmt_close($stmt);


/* =========================================================
   8. AVATAR LETTER
   ========================================================= */

$avatar_letter = strtoupper(
    mb_substr(trim($user_name), 0, 1, 'UTF-8')
);

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
      content="width=device-width, initial-scale=1.0">

<title>
    Buyer Dashboard | Farmer Direct Market
</title>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
      rel="stylesheet">

<link rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">


<style>

/* =========================================================
   GLOBAL
   ========================================================= */

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: 'Poppins', sans-serif;
}

body {
    background: #f5f7fa;
}


/* =========================================================
   SIDEBAR
   ========================================================= */

.sidebar {
    position: fixed;
    top: 0;
    left: 0;

    width: 260px;
    height: 100vh;

    background: #0f9d58;
    color: white;

    padding: 25px;
}

.logo {
    font-size: 24px;
    font-weight: 700;

    margin-bottom: 40px;
}

.menu a {
    display: block;

    color: white;
    text-decoration: none;

    padding: 12px;
    margin-bottom: 10px;

    border-radius: 8px;

    transition: .3s;
}

.menu a:hover {
    background: rgba(255,255,255,.15);
}


/* =========================================================
   MAIN
   ========================================================= */

.main {
    margin-left: 260px;
    padding: 30px;
}


/* =========================================================
   TOPBAR
   ========================================================= */

.topbar {
    display: flex;

    justify-content: space-between;
    align-items: center;

    margin-bottom: 30px;
}

.profile {
    display: flex;

    align-items: center;

    gap: 12px;
}

.avatar {
    width: 45px;
    height: 45px;

    border-radius: 50%;

    background: #0f9d58;
    color: white;

    display: flex;

    justify-content: center;
    align-items: center;

    font-weight: bold;
}


/* =========================================================
   STATS
   ========================================================= */

.stats {
    display: grid;

    grid-template-columns:
        repeat(auto-fit, minmax(220px, 1fr));

    gap: 20px;
}

.card {
    background: white;

    padding: 25px;

    border-radius: 15px;

    box-shadow:
        0 4px 15px rgba(0,0,0,.08);
}

.card i {
    font-size: 28px;

    color: #0f9d58;

    margin-bottom: 10px;
}

.card h2 {
    margin: 8px 0;
}


/* =========================================================
   SECTION
   ========================================================= */

.section {
    margin-top: 35px;
}

.section h2 {
    margin-bottom: 15px;
}


/* =========================================================
   TABLE
   ========================================================= */

.table-box {
    background: white;

    border-radius: 15px;

    overflow-x: auto;

    box-shadow:
        0 4px 15px rgba(0,0,0,.08);
}

table {
    width: 100%;

    border-collapse: collapse;

    min-width: 650px;
}

th {
    background: #0f9d58;

    color: white;

    padding: 15px;

    text-align: left;
}

td {
    padding: 15px;

    border-bottom:
        1px solid #eee;
}


/* =========================================================
   STATUS
   ========================================================= */

.status {
    display: inline-block;

    padding: 6px 12px;

    border-radius: 20px;

    font-size: 12px;

    font-weight: 600;
}

.processing {
    background: #fff3cd;
    color: #856404;
}

.delivered {
    background: #d4edda;
    color: #155724;
}

.pending {
    background: #fff3cd;
    color: #856404;
}

.shipped {
    background: #cce5ff;
    color: #004085;
}

.cancelled {
    background: #f8d7da;
    color: #721c24;
}


/* =========================================================
   WISHLIST
   ========================================================= */

.wishlist-grid {
    display: grid;

    grid-template-columns:
        repeat(auto-fit, minmax(250px, 1fr));

    gap: 20px;
}

.product-card {
    background: white;

    border-radius: 15px;

    overflow: hidden;

    box-shadow:
        0 4px 15px rgba(0,0,0,.08);

    transition: .3s;
}

.product-card:hover {
    transform: translateY(-5px);
}

.product-card img {
    width: 100%;

    height: 180px;

    object-fit: cover;
}

.product-info {
    padding: 15px;
}

.price {
    color: #0f9d58;

    font-weight: 700;

    margin-top: 8px;
}

.product-location {
    color: #777;

    font-size: 13px;

    margin-top: 5px;
}

.btn {
    display: inline-block;

    margin-top: 12px;

    background: #0f9d58;

    color: white;

    text-decoration: none;

    padding: 10px 15px;

    border-radius: 8px;

    transition: .3s;
}

.btn:hover {
    background: #087f47;
}


/* =========================================================
   EMPTY MESSAGE
   ========================================================= */

.empty-message {
    text-align: center;

    padding: 35px;

    color: #777;
}

.empty-message i {
    font-size: 40px;

    color: #ccc;

    margin-bottom: 10px;
}


/* =========================================================
   RESPONSIVE
   ========================================================= */

@media (max-width: 768px) {

    .sidebar {
        position: relative;

        width: 100%;

        height: auto;
    }

    .main {
        margin-left: 0;

        padding: 20px;
    }

    .topbar {
        flex-direction: column;

        align-items: flex-start;

        gap: 20px;
    }

}

</style>

</head>


<body>


<!-- =====================================================
     SIDEBAR
     ===================================================== -->

<div class="sidebar">

    <div class="logo">
        🛒 Buyer Panel
    </div>

    <div class="menu">

        <a href="buyer-dashboard.php">
            <i class="fas fa-chart-line"></i>
            Dashboard
        </a>

        <a href="products.php">
            <i class="fas fa-store"></i>
            Browse Products
        </a>

        <a href="cart.php">
            <i class="fas fa-shopping-cart"></i>
            My Cart
        </a>

        <a href="orders.php">
            <i class="fas fa-box"></i>
            My Orders
        </a>

        <a href="wishlist.php">
            <i class="fas fa-heart"></i>
            Wishlist
        </a>

        <a href="profile.php">
            <i class="fas fa-user"></i>
            Profile
        </a>

        <a href="logout.php">
            <i class="fas fa-sign-out-alt"></i>
            Logout
        </a>

    </div>

</div>



<!-- =====================================================
     MAIN CONTENT
     ===================================================== -->

<div class="main">


    <!-- =================================================
         TOPBAR
         ================================================= -->

    <div class="topbar">

        <div>

            <h1>
                Welcome Back 👋
            </h1>

            <p>
                Manage your purchases and orders.
            </p>

        </div>


        <div class="profile">

            <div class="avatar">
                <?= htmlspecialchars($avatar_letter) ?>
            </div>

            <div>

                <strong>
                    <?= htmlspecialchars($user_name) ?>
                </strong>

                <p>
                    Buyer Account
                </p>

            </div>

        </div>

    </div>



    <!-- =================================================
         STATISTICS
         ================================================= -->

    <div class="stats">


        <!-- TOTAL ORDERS -->

        <div class="card">

            <i class="fas fa-box"></i>

            <h2>
                <?= $total_orders ?>
            </h2>

            <p>
                Total Orders
            </p>

        </div>


        <!-- PENDING ORDERS -->

        <div class="card">

            <i class="fas fa-clock"></i>

            <h2>
                <?= $pending_orders ?>
            </h2>

            <p>
                Pending Orders
            </p>

        </div>


        <!-- DELIVERED ORDERS -->

        <div class="card">

            <i class="fas fa-check-circle"></i>

            <h2>
                <?= $delivered_orders ?>
            </h2>

            <p>
                Delivered Orders
            </p>

        </div>


        <!-- WISHLIST -->

        <div class="card">

            <i class="fas fa-heart"></i>

            <h2>
                <?= $wishlist_count ?>
            </h2>

            <p>
                Wishlist Items
            </p>

        </div>


    </div>



    <!-- =================================================
         RECENT ORDERS
         ================================================= -->

    <div class="section">

        <h2>
            Recent Orders
        </h2>


        <div class="table-box">

            <?php if (!empty($recent_orders)): ?>

                <table>

                    <tr>

                        <th>
                            Order ID
                        </th>

                        <th>
                            Product
                        </th>

                        <th>
                            Farmer
                        </th>

                        <th>
                            Status
                        </th>

                    </tr>


                    <?php foreach ($recent_orders as $order): ?>


                        <?php

                        $status = strtolower(
                            $order['status']
                        );

                        $status_class = $status;

                        if (
                            $status === 'processing'
                        ) {
                            $status_class = 'processing';
                        }

                        ?>


                        <tr>

                            <td>
                                #<?= (int) $order['order_id'] ?>
                            </td>

                            <td>
                                <?= htmlspecialchars(
                                    $order['product_name']
                                ) ?>
                            </td>

                            <td>

                                <?= htmlspecialchars(
                                    $order['farmer_name']
                                    ?? 'Unknown Farmer'
                                ) ?>

                            </td>

                            <td>

                                <span
                                    class="status
                                    <?= htmlspecialchars(
                                        $status_class
                                    ) ?>"
                                >

                                    <?= ucfirst(
                                        htmlspecialchars(
                                            $order['status']
                                        )
                                    ) ?>

                                </span>

                            </td>

                        </tr>


                    <?php endforeach; ?>


                </table>


            <?php else: ?>


                <div class="empty-message">

                    <i class="fas fa-box-open"></i>

                    <p>
                        You have no orders yet.
                    </p>

                </div>


            <?php endif; ?>

        </div>

    </div>



    <!-- =================================================
         WISHLIST PRODUCTS
         ================================================= -->

    <div class="section">

        <h2>
            Wishlist Products
        </h2>


        <?php if (!empty($wishlist_products)): ?>


            <div class="wishlist-grid">


                <?php foreach (
                    $wishlist_products
                    as $product
                ): ?>


                    <div class="product-card">


                        <?php

                        $product_image =
                            !empty($product['image'])
                            ? trim($product['image'])
                            : 'https://via.placeholder.com/800x500?text=No+Image';

                        ?>


                        <img
                            src="<?= htmlspecialchars(
                                $product_image
                            ) ?>"
                            alt="<?= htmlspecialchars(
                                $product['name']
                            ) ?>"
                        >


                        <div class="product-info">


                            <h3>

                                <?= htmlspecialchars(
                                    $product['name']
                                ) ?>

                            </h3>


                            <div class="price">

                                ৳ <?= number_format(
                                    (float) $product['price'],
                                    2
                                ) ?>

                                /

                                <?= htmlspecialchars(
                                    $product['unit']
                                ) ?>

                            </div>


                            <?php if (
                                !empty(
                                    $product['location']
                                )
                            ): ?>

                                <div class="product-location">

                                    <i class="fas fa-location-dot"></i>

                                    <?= htmlspecialchars(
                                        $product['location']
                                    ) ?>

                                </div>

                            <?php endif; ?>


                            <a
                                href="product-details.php?id=<?= (int) $product['id'] ?>"
                                class="btn"
                            >

                                View Product

                            </a>


                        </div>


                    </div>


                <?php endforeach; ?>


            </div>


        <?php else: ?>


            <div class="table-box">

                <div class="empty-message">

                    <i class="fas fa-heart"></i>

                    <p>
                        Your wishlist is empty.
                    </p>

                    <a
                        href="products.php"
                        class="btn"
                    >
                        Browse Products
                    </a>

                </div>

            </div>


        <?php endif; ?>


    </div>


</div>


</body>

</html>


<?php

/* ================= CLOSE DATABASE ================= */

mysqli_close($conn);

?>
```
