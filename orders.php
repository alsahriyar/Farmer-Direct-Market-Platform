
<?php


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


/* ================= SESSION ================= */

session_start();


/* ================= BUYER LOGIN CHECK ================= */

if (
    !isset($_SESSION['user_id']) ||
    !isset($_SESSION['user_type']) ||
    $_SESSION['user_type'] !== 'buyer'
) {
    header("Location: login.php");
    exit();
}


$user_id = (int) $_SESSION['user_id'];

$user_name =
    $_SESSION['user_name']
    ?? 'Buyer';


/* =========================================================
   ORDER STATUS FILTER
   ========================================================= */

$filter_status =
    isset($_GET['status'])
    ? trim($_GET['status'])
    : '';


$allowed_statuses = [

    'pending',

    'processing',

    'shipped',

    'delivered',

    'cancelled'

];


if (
    $filter_status !== '' &&
    !in_array(
        $filter_status,
        $allowed_statuses,
        true
    )
) {

    $filter_status = '';

}


/* =========================================================
   FETCH BUYER ORDERS
   ========================================================= */

$orders = [];


if ($filter_status !== '') {

    $sql = "

        SELECT

            o.id,

            o.buyer_id,

            o.total_amount,

            o.status,

            o.created_at

        FROM orders o

        WHERE o.buyer_id = ?

        AND o.status = ?

        ORDER BY o.created_at DESC

    ";


    $stmt =
        $conn->prepare(
            $sql
        );


    $stmt->bind_param(
        "is",
        $user_id,
        $filter_status
    );

}


else {

    $sql = "

        SELECT

            o.id,

            o.buyer_id,

            o.total_amount,

            o.status,

            o.created_at

        FROM orders o

        WHERE o.buyer_id = ?

        ORDER BY o.created_at DESC

    ";


    $stmt =
        $conn->prepare(
            $sql
        );


    $stmt->bind_param(
        "i",
        $user_id
    );

}


$stmt->execute();


$result =
    $stmt->get_result();


while (
    $row =
    $result->fetch_assoc()
) {

    $orders[] =
        $row;

}


$stmt->close();


/* =========================================================
   ORDER STATISTICS
   ========================================================= */

$total_orders = 0;

$pending_orders = 0;

$processing_orders = 0;

$delivered_orders = 0;

$cancelled_orders = 0;


$stats_sql = "

    SELECT

        COUNT(*) AS total_orders,

        SUM(
            status = 'pending'
        ) AS pending_orders,

        SUM(
            status = 'processing'
        ) AS processing_orders,

        SUM(
            status = 'delivered'
        ) AS delivered_orders,

        SUM(
            status = 'cancelled'
        ) AS cancelled_orders

    FROM orders

    WHERE buyer_id = ?

";


$stats_stmt =
    $conn->prepare(
        $stats_sql
    );


$stats_stmt->bind_param(
    "i",
    $user_id
);


$stats_stmt->execute();


$stats_result =
    $stats_stmt->get_result();


$stats =
    $stats_result->fetch_assoc();


$total_orders =
    (int) (
        $stats['total_orders']
        ?? 0
    );


$pending_orders =
    (int) (
        $stats['pending_orders']
        ?? 0
    );


$processing_orders =
    (int) (
        $stats['processing_orders']
        ?? 0
    );


$delivered_orders =
    (int) (
        $stats['delivered_orders']
        ?? 0
    );


$cancelled_orders =
    (int) (
        $stats['cancelled_orders']
        ?? 0
    );


$stats_stmt->close();


/* =========================================================
   GET ORDER ITEMS
   ========================================================= */

function getOrderItems(
    mysqli $conn,
    int $order_id
): array {

    $items = [];


    $sql = "

        SELECT

            oi.id,

            oi.product_id,

            oi.quantity,

            oi.price,

            p.name,

            p.image,

            p.unit,

            f.name AS farmer_name

        FROM order_items oi

        INNER JOIN products p

            ON oi.product_id = p.id

        LEFT JOIN farmers f

            ON p.farmer_id = f.id

        WHERE oi.order_id = ?

        ORDER BY oi.id ASC

    ";


    $stmt =
        $conn->prepare(
            $sql
        );


    if (!$stmt) {

        return [];

    }


    $stmt->bind_param(
        "i",
        $order_id
    );


    $stmt->execute();


    $result =
        $stmt->get_result();


    while (
        $row =
        $result->fetch_assoc()
    ) {

        $items[] =
            $row;

    }


    $stmt->close();


    return $items;

}


/* =========================================================
   AVATAR
   ========================================================= */

$avatar_letter = strtoupper(

    mb_substr(

        trim(
            $user_name
        ),

        0,

        1,

        'UTF-8'

    )

);

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
    My Orders | Farmer Direct Market
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

    font-family:
        'Poppins',
        sans-serif;

}


body {

    background:
        #f5f7fa;

    color:
        #222;

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
        #0f9d58;

    color:
        white;

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

    transition:
        .3s;

}


.menu a:hover,

.menu a.active {

    background:
        rgba(
            255,
            255,
            255,
            .15
        );

}


/* =========================================================
   MAIN
   ========================================================= */

.main {

    margin-left:
        260px;

    padding:
        30px;

}


/* =========================================================
   TOPBAR
   ========================================================= */

.topbar {

    display: flex;

    justify-content:
        space-between;

    align-items:
        center;

    margin-bottom:
        30px;

}


.profile {

    display: flex;

    align-items:
        center;

    gap:
        12px;

}


.avatar {

    width:
        45px;

    height:
        45px;

    border-radius:
        50%;

    background:
        #0f9d58;

    color:
        white;

    display: flex;

    justify-content:
        center;

    align-items:
        center;

    font-weight:
        bold;

}


/* =========================================================
   STAT CARDS
   ========================================================= */

.stats {

    display: grid;

    grid-template-columns:
        repeat(
            auto-fit,
            minmax(
                180px,
                1fr
            )
        );

    gap:
        20px;

    margin-bottom:
        30px;

}


.stat-card {

    background:
        white;

    padding:
        22px;

    border-radius:
        15px;

    box-shadow:
        0 4px 15px
        rgba(
            0,
            0,
            0,
            .08
        );

}


.stat-card i {

    font-size:
        25px;

    color:
        #0f9d58;

    margin-bottom:
        10px;

}


.stat-card h2 {

    margin:
        5px 0;

}


/* =========================================================
   FILTER
   ========================================================= */

.filter-box {

    background:
        white;

    padding:
        20px;

    border-radius:
        15px;

    box-shadow:
        0 4px 15px
        rgba(
            0,
            0,
            0,
            .08
        );

    margin-bottom:
        25px;

}


.filter-links {

    display:
        flex;

    flex-wrap:
        wrap;

    gap:
        10px;

    margin-top:
        15px;

}


.filter-links a {

    text-decoration:
        none;

    color:
        #555;

    background:
        #f1f3f5;

    padding:
        8px 15px;

    border-radius:
        20px;

    font-size:
        13px;

    transition:
        .3s;

}


.filter-links a:hover,

.filter-links a.active {

    background:
        #0f9d58;

    color:
        white;

}


/* =========================================================
   ORDER CARD
   ========================================================= */

.order-card {

    background:
        white;

    border-radius:
        15px;

    margin-bottom:
        20px;

    overflow:
        hidden;

    box-shadow:
        0 4px 15px
        rgba(
            0,
            0,
            0,
            .08
        );

}


.order-header {

    display:
        flex;

    justify-content:
        space-between;

    align-items:
        center;

    padding:
        20px;

    border-bottom:
        1px solid
        #eee;

}


.order-id {

    font-size:
        18px;

    font-weight:
        600;

}


.order-date {

    color:
        #777;

    font-size:
        13px;

    margin-top:
        4px;

}


/* =========================================================
   STATUS
   ========================================================= */

.status {

    padding:
        7px 14px;

    border-radius:
        20px;

    font-size:
        12px;

    font-weight:
        600;

    text-transform:
        capitalize;

}


.status-pending {

    background:
        #fff3cd;

    color:
        #856404;

}


.status-processing {

    background:
        #cfe2ff;

    color:
        #084298;

}


.status-shipped {

    background:
        #e2d9f3;

    color:
        #59359a;

}


.status-delivered {

    background:
        #d4edda;

    color:
        #155724;

}


.status-cancelled {

    background:
        #f8d7da;

    color:
        #842029;

}


/* =========================================================
   ORDER ITEMS
   ========================================================= */

.order-items {

    padding:
        20px;

}


.order-item {

    display:
        flex;

    align-items:
        center;

    gap:
        15px;

    padding:
        12px 0;

    border-bottom:
        1px solid
        #eee;

}


.order-item:last-child {

    border-bottom:
        none;

}


.item-image {

    width:
        70px;

    height:
        70px;

    border-radius:
        10px;

    overflow:
        hidden;

    flex-shrink:
        0;

}


.item-image img {

    width:
        100%;

    height:
        100%;

    object-fit:
        cover;

}


.item-info {

    flex:
        1;

}


.item-name {

    font-weight:
        600;

}


.item-farmer {

    color:
        #777;

    font-size:
        12px;

    margin-top:
        3px;

}


.item-quantity {

    color:
        #777;

    font-size:
        13px;

    margin-top:
        5px;

}


.item-price {

    font-weight:
        600;

    color:
        #0f9d58;

}


/* =========================================================
   ORDER FOOTER
   ========================================================= */

.order-footer {

    display:
        flex;

    justify-content:
        flex-end;

    align-items:
        center;

    gap:
        20px;

    padding:
        15px 20px;

    background:
        #fafafa;

}


.total-label {

    color:
        #666;

}


.total-price {

    color:
        #0f9d58;

    font-size:
        20px;

    font-weight:
        700;

}


/* =========================================================
   EMPTY
   ========================================================= */

.empty-orders {

    background:
        white;

    padding:
        70px 20px;

    border-radius:
        15px;

    text-align:
        center;

    box-shadow:
        0 4px 15px
        rgba(
            0,
            0,
            0,
            .08
        );

}


.empty-orders i {

    font-size:
        60px;

    color:
        #ccc;

    margin-bottom:
        20px;

}


.empty-orders p {

    color:
        #777;

    margin:
        10px 0 20px;

}


.shop-btn {

    display:
        inline-block;

    background:
        #0f9d58;

    color:
        white;

    text-decoration:
        none;

    padding:
        11px 20px;

    border-radius:
        8px;

}


/* =========================================================
   RESPONSIVE
   ========================================================= */

@media (
    max-width: 768px
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


    .order-header {

        flex-direction:
            column;

        align-items:
            flex-start;

        gap:
            12px;

    }


    .order-footer {

        justify-content:
            space-between;

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


        <a
            href="buyer-dashboard.php"
        >

            <i class="fas fa-chart-line"></i>

            Dashboard

        </a>


        <a
            href="products.php"
        >

            <i class="fas fa-store"></i>

            Browse Products

        </a>


        <a
            href="cart.php"
        >

            <i class="fas fa-shopping-cart"></i>

            My Cart

        </a>


        <a
            href="orders.php"
            class="active"
        >

            <i class="fas fa-box"></i>

            My Orders

        </a>


        <a
            href="wishlist.php"
        >

            <i class="fas fa-heart"></i>

            Wishlist

        </a>


        <a
            href="profile.php"
        >

            <i class="fas fa-user"></i>

            Profile

        </a>


        <a
            href="logout.php"
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


<!-- =====================================================
     TOPBAR
     ===================================================== -->

<div class="topbar">


<div>


<h1>

My Orders 📦

</h1>


<p>

Track and manage your purchases.

</p>


</div>



<div class="profile">


<div class="avatar">

<?= htmlspecialchars(
    $avatar_letter
) ?>

</div>


<div>


<strong>

<?= htmlspecialchars(
    $user_name
) ?>

</strong>


<p>

Buyer Account

</p>


</div>


</div>


</div>



<!-- =====================================================
     STATISTICS
     ===================================================== -->

<div class="stats">


<div class="stat-card">

<i
    class="fas fa-box"
></i>

<h2>

<?= $total_orders ?>

</h2>

<p>

Total Orders

</p>

</div>



<div class="stat-card">

<i
    class="fas fa-clock"
></i>

<h2>

<?= $pending_orders ?>

</h2>

<p>

Pending Orders

</p>

</div>



<div class="stat-card">

<i
    class="fas fa-spinner"
></i>

<h2>

<?= $processing_orders ?>

</h2>

<p>

Processing Orders

</p>

</div>



<div class="stat-card">

<i
    class="fas fa-check-circle"
></i>

<h2>

<?= $delivered_orders ?>

</h2>

<p>

Delivered Orders

</p>

</div>



<div class="stat-card">

<i
    class="fas fa-times-circle"
></i>

<h2>

<?= $cancelled_orders ?>

</h2>

<p>

Cancelled Orders

</p>

</div>


</div>



<!-- =====================================================
     FILTER
     ===================================================== -->

<div class="filter-box">


<h3>

Filter Orders

</h3>


<div class="filter-links">


<a
    href="orders.php"
    class="<?= $filter_status === '' ? 'active' : '' ?>"
>

All

</a>


<?php foreach (
    $allowed_statuses
    as $status
): ?>


<a
    href="orders.php?status=<?= urlencode($status) ?>"
    class="<?= $filter_status === $status ? 'active' : '' ?>"
>

<?= ucfirst(
    $status
) ?>

</a>


<?php endforeach; ?>


</div>


</div>



<!-- =====================================================
     ORDERS
     ===================================================== -->

<?php if (
    !empty($orders)
): ?>


<?php foreach (
    $orders
    as $order
): ?>


<?php

$order_id =
    (int) $order['id'];


$order_items =
    getOrderItems(
        $conn,
        $order_id
    );


$status =
    strtolower(
        $order['status']
    );


$status_class =
    'status-'
    . $status;


?>


<div
    class="order-card"
>


<!-- ORDER HEADER -->

<div
    class="order-header"
>


<div>


<div
    class="order-id"
>

Order #<?= $order_id ?>

</div>


<div
    class="order-date"
>

<i
    class="far fa-calendar"
></i>

<?= date(
    'd M Y, h:i A',
    strtotime(
        $order['created_at']
    )
) ?>

</div>


</div>



<span
    class="status <?= $status_class ?>"
>

<?= htmlspecialchars(
    ucfirst(
        $status
    )
) ?>

</span>


</div>



<!-- ORDER ITEMS -->

<div
    class="order-items"
>


<?php if (
    !empty($order_items)
): ?>


<?php foreach (
    $order_items
    as $item
): ?>


<?php

$item_image =
    !empty(
        $item['image']
    )

    ? trim(
        $item['image']
    )

    : 'https://via.placeholder.com/150?text=No+Image';


$item_total =

    (float)
    $item['price']

    *

    (int)
    $item['quantity'];

?>


<div
    class="order-item"
>


<div
    class="item-image"
>


<img
    src="<?= htmlspecialchars(
        $item_image
    ) ?>"
    alt="<?= htmlspecialchars(
        $item['name']
    ) ?>"
>


</div>



<div
    class="item-info"
>


<div
    class="item-name"
>

<?= htmlspecialchars(
    $item['name']
) ?>

</div>


<div
    class="item-farmer"
>

<i
    class="fas fa-user"
></i>

Farmer:

<?= htmlspecialchars(
    $item['farmer_name']
    ??
    'Unknown'
) ?>

</div>


<div
    class="item-quantity"
>

Quantity:

<?= (int) $item['quantity'] ?>

<?= htmlspecialchars(
    $item['unit']
) ?>

× ৳

<?= number_format(
    (float) $item['price'],
    2
) ?>

</div>


</div>



<div
    class="item-price"
>

৳

<?= number_format(
    $item_total,
    2
) ?>

</div>


</div>


<?php endforeach; ?>


<?php else: ?>


<p
    style="color:#777;"
>

No product details found for this order.

</p>


<?php endif; ?>


</div>



<!-- ORDER FOOTER -->

<div
    class="order-footer"
>


<span
    class="total-label"
>

Order Total:

</span>


<span
    class="total-price"
>

৳

<?= number_format(
    (float) $order['total_amount'],
    2
) ?>

</span>


</div>


</div>


<?php endforeach; ?>


<?php else: ?>


<!-- =================================================
     EMPTY ORDERS
     ================================================= -->

<div
    class="empty-orders"
>


<i
    class="fas fa-box-open"
></i>


<h2>

No Orders Found

</h2>


<p>

<?php if (
    $filter_status !== ''
): ?>

You don't have any
<?= htmlspecialchars(
    $filter_status
) ?>
orders yet.

<?php else: ?>

You haven't placed
any orders yet.

<?php endif; ?>

</p>


<a
    href="products.php"
    class="shop-btn"
>


<i
    class="fas fa-store"
></i>


Browse Products


</a>


</div>


<?php endif; ?>


</div>


</body>

</html>


<?php

mysqli_close(
    $conn
);

?>
```
