<?php

session_start();


$servername = "localhost";
$username   = "root";
$password   = "";
$dbname     = "farm_db";

$conn = new mysqli(
    $servername,
    $username,
    $password,
    $dbname
);

if ($conn->connect_error) {
    die("Database Connection Failed: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");



if (
    !isset($_SESSION['user_id']) ||
    !isset($_SESSION['user_type']) ||
    $_SESSION['user_type'] !== 'farmer'
) {
    header("Location: login.php");
    exit();
}

$farmer_id = (int) $_SESSION['user_id'];

$user_name = $_SESSION['user_name'] ?? 'Farmer';


/* =========================================================
   FARMER STATISTICS
   ========================================================= */

/* Total Products */

$product_count = 0;

$stmt = $conn->prepare("
    SELECT COUNT(*) AS total
    FROM products
    WHERE farmer_id = ?
");

$stmt->bind_param("i", $farmer_id);
$stmt->execute();

$result = $stmt->get_result();
$row = $result->fetch_assoc();

$product_count = (int) ($row['total'] ?? 0);

$stmt->close();


/* Total Orders */

$total_orders = 0;

$stmt = $conn->prepare("
    SELECT COUNT(DISTINCT o.id) AS total
    FROM orders o
    INNER JOIN order_items oi
        ON o.id = oi.order_id
    INNER JOIN products p
        ON oi.product_id = p.id
    WHERE p.farmer_id = ?
");

$stmt->bind_param("i", $farmer_id);
$stmt->execute();

$result = $stmt->get_result();
$row = $result->fetch_assoc();

$total_orders = (int) ($row['total'] ?? 0);

$stmt->close();


/* Total Earnings */

$total_earnings = 0;

$stmt = $conn->prepare("
    SELECT COALESCE(
        SUM(oi.quantity * oi.price),
        0
    ) AS earnings

    FROM orders o

    INNER JOIN order_items oi
        ON o.id = oi.order_id

    INNER JOIN products p
        ON oi.product_id = p.id

    WHERE p.farmer_id = ?

    AND o.status = 'Delivered'
");

$stmt->bind_param("i", $farmer_id);
$stmt->execute();

$result = $stmt->get_result();
$row = $result->fetch_assoc();

$total_earnings = (float) ($row['earnings'] ?? 0);

$stmt->close();


/* =========================================================
   FARMER RATING
   ========================================================= */

$farmer_rating = 0.0;

/*
   If reviews table exists, calculate rating.
   Otherwise rating remains 0.0.
*/

$review_table_check = $conn->query("
    SHOW TABLES LIKE 'reviews'
");

if (
    $review_table_check &&
    $review_table_check->num_rows > 0
) {

    $stmt = $conn->prepare("
        SELECT
            COALESCE(
                AVG(r.rating),
                0
            ) AS rating

        FROM reviews r

        INNER JOIN products p
            ON r.product_id = p.id

        WHERE p.farmer_id = ?
    ");

    if ($stmt) {

        $stmt->bind_param(
            "i",
            $farmer_id
        );

        $stmt->execute();

        $result =
            $stmt->get_result();

        $row =
            $result->fetch_assoc();

        $farmer_rating =
            round(
                (float)
                ($row['rating'] ?? 0),
                1
            );

        $stmt->close();
    }
}


/* =========================================================
   RECENT PRODUCTS
   ========================================================= */

$products_sql = "

    SELECT
        id,
        name,
        price,
        unit,
        stock,
        image,
        category

    FROM products

    WHERE farmer_id = ?

    ORDER BY id DESC

    LIMIT 5

";

$products_stmt =
    $conn->prepare(
        $products_sql
    );

$products_stmt->bind_param(
    "i",
    $farmer_id
);

$products_stmt->execute();

$products_result =
    $products_stmt->get_result();


/* =========================================================
   RECENT ORDERS
   ========================================================= */

$orders_sql = "

    SELECT

        o.id AS order_id,

        o.status,

        o.created_at,

        p.name AS product_name,

        oi.quantity,

        oi.price,

        COALESCE(
            u.name,
            'Unknown Buyer'
        ) AS buyer_name

    FROM orders o

    INNER JOIN order_items oi
        ON o.id = oi.order_id

    INNER JOIN products p
        ON oi.product_id = p.id

    LEFT JOIN users u
        ON o.buyer_id = u.id

    WHERE p.farmer_id = ?

    ORDER BY
        o.created_at DESC

    LIMIT 5

";

$orders_stmt =
    $conn->prepare(
        $orders_sql
    );

$orders_stmt->bind_param(
    "i",
    $farmer_id
);

$orders_stmt->execute();

$orders_result =
    $orders_stmt->get_result();


/* =========================================================
   LOW STOCK COUNT
   ========================================================= */

$low_stock = 0;

$stmt = $conn->prepare("
    SELECT COUNT(*) AS total
    FROM products
    WHERE farmer_id = ?
    AND stock <= 10
");

$stmt->bind_param(
    "i",
    $farmer_id
);

$stmt->execute();

$result =
    $stmt->get_result();

$row =
    $result->fetch_assoc();

$low_stock =
    (int) (
        $row['total']
        ?? 0
    );

$stmt->close();

?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>

<title>
    Farmer Dashboard | Farmer Direct Market
</title>


<link
    href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
    rel="stylesheet"
>


<link
    rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
>


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
    background:
        linear-gradient(
            135deg,
            #f5f7fa 0%,
            #eef8f2 100%
        );

    color: #1f2937;
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

    background:
        linear-gradient(
            180deg,
            #0f9d58,
            #087f45
        );

    color: white;

    padding: 25px 18px;

    box-shadow:
        5px 0 25px
        rgba(0,0,0,.08);

    z-index: 1000;
}


.logo {

    font-size: 23px;

    font-weight: 700;

    padding:
        5px 10px 25px;

    margin-bottom: 20px;

    border-bottom:
        1px solid
        rgba(255,255,255,.18);

}


.menu a {

    display: flex;

    align-items: center;

    gap: 12px;

    color: white;

    text-decoration: none;

    padding: 13px 15px;

    margin-bottom: 7px;

    border-radius: 12px;

    transition:
        all .3s ease;

}


.menu a i {

    width: 22px;

    text-align: center;

}


.menu a:hover,
.menu a.active {

    background:
        rgba(255,255,255,.16);

    transform:
        translateX(4px);

}


.menu a:last-child {

    margin-top: 25px;

    background:
        rgba(255,255,255,.08);

}


/* =========================================================
   MAIN
   ========================================================= */

.main {

    margin-left: 260px;

    padding: 30px 35px;

    min-height: 100vh;

}


/* =========================================================
   TOPBAR
   ========================================================= */

.topbar {

    display: flex;

    justify-content:
        space-between;

    align-items: center;

    margin-bottom: 30px;

}


.welcome h1 {

    font-size: 28px;

    font-weight: 700;

    color: #17202a;

}


.welcome p {

    color: #7b8794;

    margin-top: 5px;

}


.profile {

    display: flex;

    align-items: center;

    gap: 12px;

    background: white;

    padding: 8px 15px 8px 8px;

    border-radius: 50px;

    box-shadow:
        0 5px 20px
        rgba(0,0,0,.06);

}


.avatar {

    width: 45px;

    height: 45px;

    border-radius: 50%;

    background:
        linear-gradient(
            135deg,
            #0f9d58,
            #34c77b
        );

    color: white;

    display: flex;

    justify-content: center;

    align-items: center;

    font-weight: 700;

    font-size: 18px;

}


.profile strong {

    font-size: 14px;

}


.profile p {

    color: #888;

    font-size: 11px;

    margin-top: 2px;

}


/* =========================================================
   STATS
   ========================================================= */

.stats {

    display: grid;

    grid-template-columns:
        repeat(
            auto-fit,
            minmax(210px, 1fr)
        );

    gap: 20px;

}


.card {

    background: white;

    padding: 24px;

    border-radius: 18px;

    box-shadow:
        0 8px 25px
        rgba(0,0,0,.06);

    border:
        1px solid
        rgba(0,0,0,.03);

    transition:
        all .3s ease;

}


.card:hover {

    transform:
        translateY(-5px);

    box-shadow:
        0 15px 35px
        rgba(0,0,0,.09);

}


.stat-card {

    display: flex;

    align-items: center;

    gap: 18px;

}


.stat-icon {

    width: 58px;

    height: 58px;

    border-radius: 16px;

    background:
        #e9f8f0;

    color:
        #0f9d58;

    display: flex;

    justify-content: center;

    align-items: center;

    font-size: 24px;

}


.stat-content h2 {

    font-size: 24px;

    margin-bottom: 3px;

}


.stat-content p {

    color: #8a94a6;

    font-size: 13px;

}


.warning-icon {

    background:
        #fff4d6;

    color:
        #d89b00;

}


/* =========================================================
   QUICK ACTIONS
   ========================================================= */

.quick-actions {

    display: flex;

    gap: 12px;

    flex-wrap: wrap;

}


.quick-btn {

    display: inline-flex;

    align-items: center;

    gap: 8px;

    padding:
        11px 17px;

    border-radius: 10px;

    text-decoration: none;

    color: white;

    background:
        #0f9d58;

    font-size: 13px;

    font-weight: 500;

    transition:
        all .3s ease;

}


.quick-btn:hover {

    background:
        #087f45;

    transform:
        translateY(-2px);

    box-shadow:
        0 8px 20px
        rgba(15,157,88,.25);

}


.quick-btn.light {

    background: white;

    color: #0f9d58;

    border:
        1px solid
        #dcefe5;

}


.quick-btn.light:hover {

    background:
        #f0faf4;

}


/* =========================================================
   SECTION
   ========================================================= */

.section {

    margin-top: 35px;

}


.section-header {

    display: flex;

    justify-content:
        space-between;

    align-items: center;

    margin-bottom: 17px;

}


.section-header h2 {

    font-size: 20px;

}


.view-all {

    color:
        #0f9d58;

    text-decoration: none;

    font-size: 13px;

    font-weight: 600;

}


.view-all:hover {

    text-decoration:
        underline;

}


/* =========================================================
   TABLE
   ========================================================= */

.table-box {

    background: white;

    border-radius: 18px;

    overflow-x: auto;

    box-shadow:
        0 8px 25px
        rgba(0,0,0,.06);

}


table {

    width: 100%;

    min-width: 700px;

    border-collapse:
        collapse;

}


table th {

    background:
        #0f9d58;

    color: white;

    padding: 15px 18px;

    text-align: left;

    font-size: 13px;

    font-weight: 500;

}


table td {

    padding: 15px 18px;

    border-bottom:
        1px solid
        #f0f0f0;

    font-size: 13px;

}


table tr:last-child td {

    border-bottom:
        none;

}


table tr:hover {

    background:
        #f9fdfb;

}


/* =========================================================
   PRODUCT
   ========================================================= */

.product-name {

    display: flex;

    align-items: center;

    gap: 12px;

}


.product-image {

    width: 45px;

    height: 45px;

    border-radius: 10px;

    object-fit: cover;

    background:
        #e8f7ef;

}


.no-image {

    display: flex;

    justify-content: center;

    align-items: center;

    color:
        #0f9d58;

}


.price {

    color:
        #0f9d58;

    font-weight: 700;

}


/* =========================================================
   STATUS
   ========================================================= */

.status {

    display: inline-block;

    padding:
        6px 12px;

    border-radius:
        20px;

    font-size:
        11px;

    font-weight:
        600;

}


.available {

    background:
        #d4edda;

    color:
        #155724;

}


.low-stock {

    background:
        #fff3cd;

    color:
        #856404;

}


.out-stock {

    background:
        #f8d7da;

    color:
        #842029;

}


.processing {

    background:
        #fff3cd;

    color:
        #856404;

}


.delivered {

    background:
        #d4edda;

    color:
        #155724;

}


.pending {

    background:
        #cfe2ff;

    color:
        #084298;

}


.shipped {

    background:
        #e2d9f3;

    color:
        #432874;

}


.cancelled {

    background:
        #f8d7da;

    color:
        #842029;

}


/* =========================================================
   EMPTY STATE
   ========================================================= */

.empty {

    text-align:
        center;

    padding:
        50px 20px;

    color:
        #888;

}


.empty i {

    font-size:
        45px;

    color:
        #0f9d58;

    margin-bottom:
        12px;

}


.empty h3 {

    color:
        #444;

    margin-bottom:
        5px;

}


/* =========================================================
   RESPONSIVE
   ========================================================= */

@media (
    max-width: 900px
) {

    .sidebar {

        position:
            relative;

        width:
            100%;

        height:
            auto;

    }


    .main {

        margin-left:
            0;

        padding:
            20px;

    }


    .topbar {

        flex-direction:
            column;

        align-items:
            flex-start;

        gap:
            20px;

    }


    .profile {

        width:
            100%;

    }

}


@media (
    max-width: 600px
) {

    .welcome h1 {

        font-size:
            22px;

    }


    .stats {

        grid-template-columns:
            1fr;

    }


    .quick-actions {

        flex-direction:
            column;

    }


    .quick-btn {

        justify-content:
            center;

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

        🌾 Farmer Panel

    </div>


    <div class="menu">


        <a
            href="farmer-dashboard.php"
            class="active"
        >

            <i class="fas fa-chart-line"></i>

            Dashboard

        </a>


        <a href="products.php">

            <i class="fas fa-seedling"></i>

            Products

        </a>


        <a href="farmer-orders.php">

            <i class="fas fa-shopping-cart"></i>

            Orders

        </a>


        <a href="earnings.php">

            <i class="fas fa-wallet"></i>

            Earnings

        </a>


        <a href="crop-price-trends.php">

            <i class="fas fa-chart-bar"></i>

            Price Trends

        </a>


        <a href="farmer-profile.php">

            <i class="fas fa-user"></i>

            Profile

        </a>


        <a
            href="logout.php"
            onclick="
                return confirm(
                    'Are you sure you want to logout?'
                );
            "
        >

            <i class="fas fa-sign-out-alt"></i>

            Logout

        </a>


    </div>


</div>


<!-- =====================================================
     MAIN
     ===================================================== -->

<div class="main">


    <!-- TOPBAR -->

    <div class="topbar">


        <div class="welcome">


            <h1>

                Welcome Back,
                <?= htmlspecialchars(
                    $user_name
                ) ?>
                👋

            </h1>


            <p>

                Manage your products,
                orders and earnings.

            </p>


        </div>


        <div class="profile">


            <div class="avatar">

                <?= htmlspecialchars(
                    strtoupper(
                        substr(
                            $user_name,
                            0,
                            1
                        )
                    )
                ) ?>

            </div>


            <div>


                <strong>

                    <?= htmlspecialchars(
                        $user_name
                    ) ?>

                </strong>


                <p>

                    Verified Farmer

                </p>


            </div>


        </div>


    </div>


    <!-- =================================================
         STATISTICS
         ================================================= -->

    <div class="stats">


        <!-- PRODUCTS -->

        <div class="card stat-card">


            <div class="stat-icon">

                <i class="fas fa-box"></i>

            </div>


            <div class="stat-content">


                <h2>

                    <?= number_format(
                        $product_count
                    ) ?>

                </h2>


                <p>

                    Total Products

                </p>


            </div>


        </div>


        <!-- ORDERS -->

        <div class="card stat-card">


            <div class="stat-icon">

                <i class="fas fa-shopping-bag"></i>

            </div>


            <div class="stat-content">


                <h2>

                    <?= number_format(
                        $total_orders
                    ) ?>

                </h2>


                <p>

                    Total Orders

                </p>


            </div>


        </div>


        <!-- EARNINGS -->

        <div class="card stat-card">


            <div class="stat-icon">

                <i class="fas fa-money-bill-wave"></i>

            </div>


            <div class="stat-content">


                <h2>

                    ৳ <?= number_format(
                        $total_earnings,
                        2
                    ) ?>

                </h2>


                <p>

                    Total Earnings

                </p>


            </div>


        </div>


        <!-- RATING -->

        <div class="card stat-card">


            <div class="stat-icon">

                <i class="fas fa-star"></i>

            </div>


            <div class="stat-content">


                <h2>

                    <?= number_format(
                        $farmer_rating,
                        1
                    ) ?>

                    <small>
                        / 5
                    </small>

                </h2>


                <p>

                    Farmer Rating

                </p>


            </div>


        </div>


        <!-- LOW STOCK -->

        <div class="card stat-card">


            <div class="
                stat-icon
                warning-icon
            ">


                <i class="fas fa-triangle-exclamation"></i>


            </div>


            <div class="stat-content">


                <h2>

                    <?= number_format(
                        $low_stock
                    ) ?>

                </h2>


                <p>

                    Low Stock Products

                </p>


            </div>


        </div>


    </div>


    <!-- =================================================
         QUICK ACTIONS
         ================================================= -->

    <div class="section">


        <div class="section-header">


            <h2>

                Quick Actions

            </h2>


        </div>


        <div class="quick-actions">


            <a
                href="products.php"
                class="quick-btn"
            >

                <i class="fas fa-seedling"></i>

                Manage Products

            </a>


            <a
                href="farmer-orders.php"
                class="quick-btn"
            >

                <i class="fas fa-shopping-cart"></i>

                View Orders

            </a>


            <a
                href="earnings.php"
                class="quick-btn light"
            >

                <i class="fas fa-wallet"></i>

                Check Earnings

            </a>


            <a
                href="crop-price-trends.php"
                class="quick-btn light"
            >

                <i class="fas fa-chart-line"></i>

                Price Trends

            </a>


        </div>


    </div>


    <!-- =================================================
         RECENT PRODUCTS
         ================================================= -->

    <div class="section">


        <div class="section-header">


            <h2>

                Recent Products

            </h2>


            <a
                href="products.php"
                class="view-all"
            >

                View All

                <i class="fas fa-arrow-right"></i>

            </a>


        </div>


        <div class="table-box">


            <?php if (
                $products_result->num_rows > 0
            ): ?>


                <table>


                    <thead>


                        <tr>


                            <th>

                                Product

                            </th>


                            <th>

                                Category

                            </th>


                            <th>

                                Price

                            </th>


                            <th>

                                Stock

                            </th>


                            <th>

                                Status

                            </th>


                        </tr>


                    </thead>


                    <tbody>


                    <?php while (
                        $product =
                        $products_result->fetch_assoc()
                    ): ?>


                        <?php

                        $stock =
                            (int)
                            $product[
                                'stock'
                            ];


                        if (
                            $stock <= 0
                        ) {

                            $stock_status =
                                'Out of Stock';

                            $stock_class =
                                'out-stock';

                        }
                        elseif (
                            $stock <= 10
                        ) {

                            $stock_status =
                                'Low Stock';

                            $stock_class =
                                'low-stock';

                        }
                        else {

                            $stock_status =
                                'Available';

                            $stock_class =
                                'available';

                        }


                        ?>


                        <tr>


                            <td>


                                <div class="
                                    product-name
                                ">


                                    <?php if (
                                        !empty(
                                            $product[
                                                'image'
                                            ]
                                        )
                                    ): ?>


                                        <img
                                            src="<?= htmlspecialchars(
                                                $product[
                                                    'image'
                                                ]
                                            ) ?>"
                                            class="
                                                product-image
                                            "
                                            alt="
                                                Product
                                            "
                                        >


                                    <?php else: ?>


                                        <div class="
                                            product-image
                                            no-image
                                        ">


                                            <i class="
                                                fas
                                                fa-seedling
                                            "></i>


                                        </div>


                                    <?php endif; ?>


                                    <strong>

                                        <?= htmlspecialchars(
                                            $product[
                                                'name'
                                            ]
                                        ) ?>

                                    </strong>


                                </div>


                            </td>


                            <td>

                                <?= htmlspecialchars(
                                    $product[
                                        'category'
                                    ]
                                    ??
                                    'N/A'
                                ) ?>

                            </td>


                            <td class="price">

                                ৳ <?= number_format(
                                    (float)
                                    $product[
                                        'price'
                                    ],
                                    2
                                ) ?>

                                /

                                <?= htmlspecialchars(
                                    $product[
                                        'unit'
                                    ]
                                    ??
                                    'unit'
                                ) ?>

                            </td>


                            <td>

                                <?= number_format(
                                    $stock
                                ) ?>

                            </td>


                            <td>


                                <span class="
                                    status
                                    <?= $stock_class ?>
                                ">


                                    <?= $stock_status ?>


                                </span>


                            </td>


                        </tr>


                    <?php endwhile; ?>


                    </tbody>


                </table>


            <?php else: ?>


                <div class="empty">


                    <i class="
                        fas
                        fa-seedling
                    "></i>


                    <h3>

                        No Products Yet

                    </h3>


                    <p>

                        Your products will
                        appear here.

                    </p>


                </div>


            <?php endif; ?>


        </div>


    </div>


    <!-- =================================================
         RECENT ORDERS
         ================================================= -->

    <div class="section">


        <div class="section-header">


            <h2>

                Recent Orders

            </h2>


            <a
                href="farmer-orders.php"
                class="view-all"
            >

                View All

                <i class="
                    fas
                    fa-arrow-right
                "></i>

            </a>


        </div>


        <div class="table-box">


            <?php if (
                $orders_result->num_rows > 0
            ): ?>


                <table>


                    <thead>


                        <tr>


                            <th>

                                Order ID

                            </th>


                            <th>

                                Buyer

                            </th>


                            <th>

                                Product

                            </th>


                            <th>

                                Quantity

                            </th>


                            <th>

                                Amount

                            </th>


                            <th>

                                Status

                            </th>


                        </tr>


                    </thead>


                    <tbody>


                    <?php while (
                        $order =
                        $orders_result->fetch_assoc()
                    ): ?>


                        <?php

                        $status =
                            $order[
                                'status'
                            ]
                            ??
                            'Pending';


                        $status_class =
                            strtolower(
                                $status
                            );


                        $amount =
                            (
                                (float)
                                $order[
                                    'price'
                                ]
                                *
                                (int)
                                $order[
                                    'quantity'
                                ]
                            );

                        ?>


                        <tr>


                            <td>

                                <strong>

                                    #<?= (int)
                                    $order[
                                        'order_id'
                                    ] ?>

                                </strong>

                            </td>


                            <td>

                                <?= htmlspecialchars(
                                    $order[
                                        'buyer_name'
                                    ]
                                ) ?>

                            </td>


                            <td>

                                <?= htmlspecialchars(
                                    $order[
                                        'product_name'
                                    ]
                                ) ?>

                            </td>


                            <td>

                                <?= (int)
                                $order[
                                    'quantity'
                                ] ?>

                            </td>


                            <td class="price">

                                ৳ <?= number_format(
                                    $amount,
                                    2
                                ) ?>

                            </td>


                            <td>


                                <span class="
                                    status
                                    <?= htmlspecialchars(
                                        $status_class
                                    ) ?>
                                ">


                                    <?= htmlspecialchars(
                                        $status
                                    ) ?>


                                </span>


                            </td>


                        </tr>


                    <?php endwhile; ?>


                    </tbody>


                </table>


            <?php else: ?>


                <div class="empty">


                    <i class="
                        fas
                        fa-shopping-cart
                    "></i>


                    <h3>

                        No Orders Yet

                    </h3>


                    <p>

                        New customer orders
                        will appear here.

                    </p>


                </div>


            <?php endif; ?>


        </div>


    </div>


</div>


</body>

</html>


<?php

$products_stmt->close();

$orders_stmt->close();

$conn->close();

?>
