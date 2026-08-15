
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


/* =========================================================
   FARMER LOGIN CHECK
   ========================================================= */

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
   UPDATE ORDER STATUS
   ========================================================= */

$message = "";
$message_type = "";

if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['update_status'])
) {

    $order_id = (int) ($_POST['order_id'] ?? 0);

    $new_status =
        trim($_POST['status'] ?? '');


    $allowed_statuses = [
        'Pending',
        'Processing',
        'Shipped',
        'Delivered',
        'Cancelled'
    ];


    if (
        $order_id <= 0 ||
        !in_array(
            $new_status,
            $allowed_statuses,
            true
        )
    ) {

        $message =
            "Invalid order status.";

        $message_type =
            "error";

    } else {

        /*
         * IMPORTANT:
         * Only allow farmer to update an order
         * that contains his own product.
         */

        $update_sql = "

            UPDATE orders o

            INNER JOIN order_items oi
                ON o.id = oi.order_id

            INNER JOIN products p
                ON oi.product_id = p.id

            SET o.status = ?

            WHERE o.id = ?
            AND p.farmer_id = ?

        ";


        $update_stmt =
            $conn->prepare(
                $update_sql
            );


        if ($update_stmt) {

            $update_stmt->bind_param(
                "sii",
                $new_status,
                $order_id,
                $farmer_id
            );


            if (
                $update_stmt->execute()
            ) {

                if (
                    $update_stmt->affected_rows > 0
                ) {

                    $message =
                        "Order status updated successfully.";

                    $message_type =
                        "success";

                } else {

                    $message =
                        "Order not found or you do not have permission.";

                    $message_type =
                        "error";
                }

            } else {

                $message =
                    "Failed to update order status.";

                $message_type =
                    "error";
            }


            $update_stmt->close();

        } else {

            $message =
                "Database error: "
                . $conn->error;

            $message_type =
                "error";
        }
    }
}


/* =========================================================
   SEARCH
   ========================================================= */

$search =
    trim(
        $_GET['search'] ?? ''
    );


/* =========================================================
   STATUS FILTER
   ========================================================= */

$status_filter =
    trim(
        $_GET['status'] ?? ''
    );


/* =========================================================
   GET FARMER ORDERS
   ========================================================= */

$sql = "

    SELECT

        o.id AS order_id,

        o.status AS order_status,

        o.created_at,

        o.total_amount,

        u.name AS buyer_name,

        p.name AS product_name,

        oi.quantity,

        oi.price AS item_price

    FROM orders o

    INNER JOIN order_items oi
        ON o.id = oi.order_id

    INNER JOIN products p
        ON oi.product_id = p.id

    LEFT JOIN users u
        ON o.buyer_id = u.id

    WHERE p.farmer_id = ?

";


$params = [
    $farmer_id
];

$types = "i";


/* =========================================================
   SEARCH FILTER
   ========================================================= */

if ($search !== '') {

    $sql .= "

        AND (
            p.name LIKE ?
            OR u.name LIKE ?
            OR CAST(o.id AS CHAR) LIKE ?
        )

    ";

    $search_value =
        "%" . $search . "%";


    $params[] =
        $search_value;

    $params[] =
        $search_value;

    $params[] =
        $search_value;

    $types .= "sss";
}


/* =========================================================
   STATUS FILTER
   ========================================================= */

if ($status_filter !== '') {

    $sql .= "

        AND o.status = ?

    ";

    $params[] =
        $status_filter;

    $types .= "s";
}


/* =========================================================
   ORDER BY
   ========================================================= */

$sql .= "

    ORDER BY
        o.created_at DESC

";


$stmt =
    $conn->prepare($sql);


if (!$stmt) {

    die(
        "Database Query Error: "
        . $conn->error
    );

}


/* =========================================================
   DYNAMIC BIND PARAMS
   ========================================================= */

$bind_params = [];

$bind_params[] =
    $types;


foreach ($params as $key => $value) {

    $bind_params[] =
        &$params[$key];

}


call_user_func_array(
    [
        $stmt,
        'bind_param'
    ],
    $bind_params
);


$stmt->execute();


$result =
    $stmt->get_result();


/* =========================================================
   GET STATISTICS
   ========================================================= */

$stats_sql = "

    SELECT

        COUNT(DISTINCT o.id) AS total_orders,

        SUM(
            CASE
                WHEN o.status = 'Pending'
                THEN 1
                ELSE 0
            END
        ) AS pending_orders,

        SUM(
            CASE
                WHEN o.status = 'Processing'
                THEN 1
                ELSE 0
            END
        ) AS processing_orders,

        SUM(
            CASE
                WHEN o.status = 'Delivered'
                THEN 1
                ELSE 0
            END
        ) AS delivered_orders

    FROM orders o

    INNER JOIN order_items oi
        ON o.id = oi.order_id

    INNER JOIN products p
        ON oi.product_id = p.id

    WHERE p.farmer_id = ?

";


$stats_stmt =
    $conn->prepare(
        $stats_sql
    );


$stats_stmt->bind_param(
    "i",
    $farmer_id
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
    Farmer Orders | Farmer Direct Market
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
    background: #f5f7fa;
    color: #222;
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

.menu a:hover,
.menu a.active {
    background: rgba(255,255,255,.15);
}

.menu a i {
    width: 25px;
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

.topbar p {
    color: #777;
    margin-top: 5px;
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
    align-items: center;
    justify-content: center;
    font-weight: bold;
}


/* =========================================================
   STAT CARDS
   ========================================================= */

.stats {
    display: grid;
    grid-template-columns:
        repeat(
            auto-fit,
            minmax(200px, 1fr)
        );

    gap: 20px;
}


.card {
    background: white;
    padding: 25px;
    border-radius: 15px;
    box-shadow:
        0 5px 15px
        rgba(0,0,0,.08);
}


.stat-card {
    display: flex;
    align-items: center;
    gap: 18px;
}


.stat-icon {
    width: 55px;
    height: 55px;
    border-radius: 12px;
    background: #e8f7ef;
    color: #0f9d58;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
}


.stat-card h2 {
    margin-bottom: 3px;
}


.stat-card p {
    color: #777;
    font-size: 13px;
}


/* =========================================================
   SECTION
   ========================================================= */

.section {
    margin-top: 35px;
}


.section-title {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 18px;
}


/* =========================================================
   MESSAGE
   ========================================================= */

.message {
    padding: 14px 18px;
    border-radius: 10px;
    margin-bottom: 20px;
}


.message.success {
    background: #d4edda;
    color: #155724;
}


.message.error {
    background: #f8d7da;
    color: #842029;
}


/* =========================================================
   FILTER BOX
   ========================================================= */

.filter-box {
    background: white;
    padding: 20px;
    border-radius: 15px;
    box-shadow:
        0 5px 15px
        rgba(0,0,0,.08);

    display: flex;
    gap: 12px;
    flex-wrap: wrap;
}


.filter-box input,
.filter-box select {
    padding: 11px 15px;
    border: 1px solid #ddd;
    border-radius: 8px;
    outline: none;
    background: #fafafa;
}


.filter-box input {
    flex: 1;
    min-width: 220px;
}


.filter-box input:focus,
.filter-box select:focus {
    border-color: #0f9d58;
}


.filter-btn {
    background: #0f9d58;
    color: white;
    border: none;
    padding: 11px 20px;
    border-radius: 8px;
    cursor: pointer;
    font-weight: 600;
}


.clear-btn {
    background: #eee;
    color: #555;
    text-decoration: none;
    padding: 11px 20px;
    border-radius: 8px;
}


/* =========================================================
   TABLE
   ========================================================= */

.table-box {
    background: white;
    border-radius: 15px;
    overflow-x: auto;
    box-shadow:
        0 5px 15px
        rgba(0,0,0,.08);
}


table {
    width: 100%;
    min-width: 950px;
    border-collapse: collapse;
}


table th {
    background: #0f9d58;
    color: white;
    padding: 15px;
    text-align: left;
    font-size: 14px;
}


table td {
    padding: 15px;
    border-bottom: 1px solid #eee;
    font-size: 14px;
}


table tr:hover {
    background: #f9fffb;
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


.status-pending {
    background: #fff3cd;
    color: #856404;
}


.status-processing {
    background: #cfe2ff;
    color: #084298;
}


.status-shipped {
    background: #e2d9f3;
    color: #432874;
}


.status-delivered {
    background: #d4edda;
    color: #155724;
}


.status-cancelled {
    background: #f8d7da;
    color: #842029;
}


/* =========================================================
   STATUS FORM
   ========================================================= */

.status-form {
    display: flex;
    gap: 7px;
    align-items: center;
}


.status-form select {
    padding: 7px 8px;
    border: 1px solid #ddd;
    border-radius: 7px;
    outline: none;
}


.update-btn {
    background: #0f9d58;
    color: white;
    border: none;
    padding: 7px 10px;
    border-radius: 7px;
    cursor: pointer;
}


.update-btn:hover {
    background: #0b8043;
}


/* =========================================================
   EMPTY STATE
   ========================================================= */

.empty {
    text-align: center;
    padding: 60px 20px;
    color: #777;
}


.empty i {
    font-size: 55px;
    color: #0f9d58;
    margin-bottom: 15px;
}


.empty h3 {
    color: #444;
    margin-bottom: 8px;
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

        🌾 Farmer Panel

    </div>


    <div class="menu">


        <a href="farmer-dashboard.php">

            <i class="fas fa-chart-line"></i>

            Dashboard

        </a>


        <a href="products.php">

            <i class="fas fa-seedling"></i>

            Products

        </a>


        <a
            href="farmer-orders.php"
            class="active"
        >

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
            onclick="return confirm('Are you sure you want to logout?');"
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


        <div>

            <h1>

                My Orders 📦

            </h1>


            <p>

                Manage orders received for your products.

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


    <!-- MESSAGE -->

    <?php if ($message !== ""): ?>

        <div
            class="message
            <?= htmlspecialchars(
                $message_type
            ) ?>"
        >

            <?= htmlspecialchars(
                $message
            ) ?>

        </div>

    <?php endif; ?>


    <!-- =================================================
         STATISTICS
         ================================================= -->

    <div class="stats">


        <div class="card stat-card">


            <div class="stat-icon">

                <i class="fas fa-box"></i>

            </div>


            <div>

                <h2>

                    <?= $total_orders ?>

                </h2>


                <p>

                    Total Orders

                </p>

            </div>


        </div>


        <div class="card stat-card">


            <div class="stat-icon">

                <i class="fas fa-clock"></i>

            </div>


            <div>

                <h2>

                    <?= $pending_orders ?>

                </h2>


                <p>

                    Pending Orders

                </p>

            </div>


        </div>


        <div class="card stat-card">


            <div class="stat-icon">

                <i class="fas fa-spinner"></i>

            </div>


            <div>

                <h2>

                    <?= $processing_orders ?>

                </h2>


                <p>

                    Processing

                </p>

            </div>


        </div>


        <div class="card stat-card">


            <div class="stat-icon">

                <i class="fas fa-check-circle"></i>

            </div>


            <div>

                <h2>

                    <?= $delivered_orders ?>

                </h2>


                <p>

                    Delivered

                </p>

            </div>


        </div>


    </div>


    <!-- =================================================
         FILTER
         ================================================= -->

    <div class="section">


        <div class="section-title">


            <h2>

                Order Management

            </h2>


        </div>


        <form
            method="GET"
            class="filter-box"
        >


            <input
                type="text"
                name="search"
                placeholder="Search by order ID, buyer or product..."
                value="<?= htmlspecialchars(
                    $search
                ) ?>"
            >


            <select name="status">


                <option value="">

                    All Status

                </option>


                <option
                    value="Pending"
                    <?= $status_filter === 'Pending'
                        ? 'selected'
                        : '' ?>
                >

                    Pending

                </option>


                <option
                    value="Processing"
                    <?= $status_filter === 'Processing'
                        ? 'selected'
                        : '' ?>
                >

                    Processing

                </option>


                <option
                    value="Shipped"
                    <?= $status_filter === 'Shipped'
                        ? 'selected'
                        : '' ?>
                >

                    Shipped

                </option>


                <option
                    value="Delivered"
                    <?= $status_filter === 'Delivered'
                        ? 'selected'
                        : '' ?>
                >

                    Delivered

                </option>


                <option
                    value="Cancelled"
                    <?= $status_filter === 'Cancelled'
                        ? 'selected'
                        : '' ?>
                >

                    Cancelled

                </option>


            </select>


            <button
                type="submit"
                class="filter-btn"
            >

                <i class="fas fa-search"></i>

                Search

            </button>


            <a
                href="farmer-orders.php"
                class="clear-btn"
            >

                Clear

            </a>


        </form>


    </div>


    <!-- =================================================
         ORDERS TABLE
         ================================================= -->

    <div class="section">


        <div class="table-box">


            <?php if ($result->num_rows > 0): ?>


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

                                Price

                            </th>


                            <th>

                                Order Date

                            </th>


                            <th>

                                Status

                            </th>


                            <th>

                                Update

                            </th>


                        </tr>


                    </thead>


                    <tbody>


                    <?php while (
                        $order =
                        $result->fetch_assoc()
                    ): ?>


                        <?php

                        $status =
                            $order['order_status']
                            ?? 'Pending';


                        $status_class =
                            strtolower(
                                $status
                            );


                        ?>


                        <tr>


                            <td>

                                <strong>

                                    #<?= (int)
                                    $order['order_id'] ?>

                                </strong>

                            </td>


                            <td>

                                <?= htmlspecialchars(
                                    $order['buyer_name']
                                    ?? 'Unknown Buyer'
                                ) ?>

                            </td>


                            <td>

                                <?= htmlspecialchars(
                                    $order['product_name']
                                ) ?>

                            </td>


                            <td>

                                <?= (int)
                                $order['quantity'] ?>

                            </td>


                            <td>

                                ৳
                                <?= number_format(
                                    (float)
                                    $order['item_price'],
                                    2
                                ) ?>

                            </td>


                            <td>

                                <?= date(
                                    'd M Y',
                                    strtotime(
                                        $order['created_at']
                                    )
                                ) ?>

                            </td>


                            <td>


                                <span
                                    class="status
                                    status-<?= htmlspecialchars(
                                        $status_class
                                    ) ?>"
                                >

                                    <?= htmlspecialchars(
                                        $status
                                    ) ?>

                                </span>


                            </td>


                            <td>


                                <form
                                    method="POST"
                                    class="status-form"
                                >


                                    <input
                                        type="hidden"
                                        name="order_id"
                                        value="<?= (int)
                                        $order['order_id'] ?>"
                                    >


                                    <select
                                        name="status"
                                    >


                                        <option
                                            value="Pending"
                                            <?= $status === 'Pending'
                                                ? 'selected'
                                                : '' ?>
                                        >

                                            Pending

                                        </option>


                                        <option
                                            value="Processing"
                                            <?= $status === 'Processing'
                                                ? 'selected'
                                                : '' ?>
                                        >

                                            Processing

                                        </option>


                                        <option
                                            value="Shipped"
                                            <?= $status === 'Shipped'
                                                ? 'selected'
                                                : '' ?>
                                        >

                                            Shipped

                                        </option>


                                        <option
                                            value="Delivered"
                                            <?= $status === 'Delivered'
                                                ? 'selected'
                                                : '' ?>
                                        >

                                            Delivered

                                        </option>


                                        <option
                                            value="Cancelled"
                                            <?= $status === 'Cancelled'
                                                ? 'selected'
                                                : '' ?>
                                        >

                                            Cancelled

                                        </option>


                                    </select>


                                    <button
                                        type="submit"
                                        name="update_status"
                                        class="update-btn"
                                    >

                                        <i class="fas fa-save"></i>

                                    </button>


                                </form>


                            </td>


                        </tr>


                    <?php endwhile; ?>


                    </tbody>


                </table>


            <?php else: ?>


                <div class="empty">


                    <i class="fas fa-box-open"></i>


                    <h3>

                        No Orders Found

                    </h3>


                    <p>

                        You don't have any orders
                        for your products yet.

                    </p>


                </div>


            <?php endif; ?>


        </div>


    </div>


</div>


</body>

</html>


<?php

$stmt->close();

$stats_stmt->close();

$conn->close();

?>
