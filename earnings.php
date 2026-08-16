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

$stats_sql = "

    SELECT

        COALESCE(
            SUM(
                oi.quantity * oi.price
            ),
            0
        ) AS total_sales,

        COALESCE(
            SUM(
                CASE
                    WHEN o.status = 'Delivered'
                    THEN oi.quantity * oi.price
                    ELSE 0
                END
            ),
            0
        ) AS total_earnings,

        COALESCE(
            SUM(
                CASE
                    WHEN o.status = 'Pending'
                    OR o.status = 'Processing'
                    OR o.status = 'Shipped'
                    THEN oi.quantity * oi.price
                    ELSE 0
                END
            ),
            0
        ) AS pending_amount,

        COUNT(
            DISTINCT o.id
        ) AS total_orders,

        COUNT(
            DISTINCT CASE
                WHEN o.status = 'Delivered'
                THEN o.id
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


if (!$stats_stmt) {
    die(
        "Statistics Query Error: "
        . $conn->error
    );
}


$stats_stmt->bind_param(
    "i",
    $farmer_id
);


$stats_stmt->execute();


$stats_result =
    $stats_stmt->get_result();


$stats =
    $stats_result->fetch_assoc();


$total_sales =
    (float) (
        $stats['total_sales']
        ?? 0
    );


$total_earnings =
    (float) (
        $stats['total_earnings']
        ?? 0
    );


$pending_amount =
    (float) (
        $stats['pending_amount']
        ?? 0
    );


$total_orders =
    (int) (
        $stats['total_orders']
        ?? 0
    );


$delivered_orders =
    (int) (
        $stats['delivered_orders']
        ?? 0
    );


/* =========================================================
   RECENT EARNINGS / SALES
   ========================================================= */

$recent_sql = "

    SELECT

        o.id AS order_id,

        o.status,

        o.created_at,

        u.name AS buyer_name,

        p.name AS product_name,

        oi.quantity,

        oi.price,

        (
            oi.quantity * oi.price
        ) AS subtotal

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

    LIMIT 20

";


$recent_stmt =
    $conn->prepare(
        $recent_sql
    );


if (!$recent_stmt) {
    die(
        "Recent Earnings Query Error: "
        . $conn->error
    );
}


$recent_stmt->bind_param(
    "i",
    $farmer_id
);


$recent_stmt->execute();


$recent_result =
    $recent_stmt->get_result();


/* =========================================================
   MONTHLY EARNINGS
   ========================================================= */

$monthly_sql = "

    SELECT

        DATE_FORMAT(
            o.created_at,
            '%Y-%m'
        ) AS earning_month,

        SUM(
            oi.quantity * oi.price
        ) AS monthly_earnings

    FROM orders o

    INNER JOIN order_items oi
        ON o.id = oi.order_id

    INNER JOIN products p
        ON oi.product_id = p.id

    WHERE p.farmer_id = ?

    AND o.status = 'Delivered'

    GROUP BY
        DATE_FORMAT(
            o.created_at,
            '%Y-%m'
        )

    ORDER BY
        earning_month DESC

    LIMIT 6

";


$monthly_stmt =
    $conn->prepare(
        $monthly_sql
    );


$monthly_stmt->bind_param(
    "i",
    $farmer_id
);


$monthly_stmt->execute();


$monthly_result =
    $monthly_stmt->get_result();


$monthly_data = [];


while (
    $month =
    $monthly_result->fetch_assoc()
) {

    $monthly_data[] =
        $month;

}

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
    Farmer Earnings | Farmer Direct Market
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
        repeat(
            auto-fit,
            minmax(210px, 1fr)
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
   EARNINGS SUMMARY
   ========================================================= */

.summary-card {
    background: white;
    border-radius: 15px;
    padding: 25px;
    box-shadow:
        0 5px 15px
        rgba(0,0,0,.08);
}


.summary-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}


.summary-header h2 {
    font-size: 20px;
}


.earning-highlight {
    font-size: 32px;
    font-weight: 700;
    color: #0f9d58;
}


.summary-line {
    height: 10px;
    width: 100%;
    background: #eee;
    border-radius: 10px;
    overflow: hidden;
}


.summary-progress {
    height: 100%;
    background: #0f9d58;
    border-radius: 10px;
}


.summary-details {
    display: flex;
    justify-content: space-between;
    margin-top: 12px;
    color: #777;
    font-size: 13px;
}


/* =========================================================
   MONTHLY EARNINGS
   ========================================================= */

.monthly-grid {
    display: grid;
    grid-template-columns:
        repeat(
            auto-fit,
            minmax(180px, 1fr)
        );

    gap: 15px;
}


.month-card {
    background: white;
    padding: 20px;
    border-radius: 12px;
    box-shadow:
        0 4px 12px
        rgba(0,0,0,.06);
}


.month-card p {
    color: #777;
    font-size: 13px;
}


.month-card h3 {
    color: #0f9d58;
    margin-top: 7px;
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
    min-width: 850px;
    border-collapse: collapse;
}


table th {
    background: #0f9d58;
    color: white;
    padding: 15px;
    text-align: left;
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
   EMPTY
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

    .summary-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 10px;
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


        <a href="farmer-orders.php">

            <i class="fas fa-shopping-cart"></i>

            Orders

        </a>


        <a
            href="earnings.php"
            class="active"
        >

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
     MAIN CONTENT
     ===================================================== -->

<div class="main">


    <!-- TOPBAR -->

    <div class="topbar">


        <div>

            <h1>

                Earnings 💰

            </h1>


            <p>

                Track your sales and earnings.

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


        <div class="card stat-card">


            <div class="stat-icon">

                <i class="fas fa-money-bill-wave"></i>

            </div>


            <div>

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


        <div class="card stat-card">


            <div class="stat-icon">

                <i class="fas fa-chart-line"></i>

            </div>


            <div>

                <h2>

                    ৳ <?= number_format(
                        $total_sales,
                        2
                    ) ?>

                </h2>


                <p>

                    Total Sales

                </p>

            </div>


        </div>


        <div class="card stat-card">


            <div class="stat-icon">

                <i class="fas fa-clock"></i>

            </div>


            <div>

                <h2>

                    ৳ <?= number_format(
                        $pending_amount,
                        2
                    ) ?>

                </h2>


                <p>

                    Pending Amount

                </p>

            </div>


        </div>


        <div class="card stat-card">


            <div class="stat-icon">

                <i class="fas fa-box"></i>

            </div>


            <div>

                <h2>

                    <?= $delivered_orders ?>

                </h2>


                <p>

                    Completed Orders

                </p>

            </div>


        </div>


    </div>


    <!-- =================================================
         EARNINGS SUMMARY
         ================================================= -->

    <div class="section">


        <div class="section-title">


            <h2>

                Earnings Overview

            </h2>


        </div>


        <div class="summary-card">


            <div class="summary-header">


                <div>

                    <h2>

                        Completed Earnings

                    </h2>


                    <p>

                        Earnings from delivered orders

                    </p>

                </div>


                <div class="earning-highlight">

                    ৳ <?= number_format(
                        $total_earnings,
                        2
                    ) ?>

                </div>


            </div>


            <?php

            $percentage = 0;

            if (
                $total_sales > 0
            ) {

                $percentage =
                    (
                        $total_earnings
                        /
                        $total_sales
                    )
                    * 100;

            }

            if (
                $percentage > 100
            ) {

                $percentage = 100;

            }

            ?>


            <div class="summary-line">


                <div
                    class="summary-progress"
                    style="width:
                    <?= $percentage ?>%;"
                ></div>


            </div>


            <div class="summary-details">


                <span>

                    Delivered Sales

                </span>


                <span>

                    <?= number_format(
                        $percentage,
                        1
                    ) ?>% Completed

                </span>


            </div>


        </div>


    </div>


    <!-- =================================================
         MONTHLY EARNINGS
         ================================================= -->

    <div class="section">


        <div class="section-title">


            <h2>

                Monthly Earnings

            </h2>


        </div>


        <?php if (
            count($monthly_data) > 0
        ): ?>


            <div class="monthly-grid">


                <?php foreach (
                    $monthly_data
                    as $month
                ): ?>


                    <div class="month-card">


                        <p>

                            <?= date(
                                'F Y',
                                strtotime(
                                    $month[
                                        'earning_month'
                                    ] . '-01'
                                )
                            ) ?>

                        </p>


                        <h3>

                            ৳ <?= number_format(
                                (float)
                                $month[
                                    'monthly_earnings'
                                ],
                                2
                            ) ?>

                        </h3>


                    </div>


                <?php endforeach; ?>


            </div>


        <?php else: ?>


            <div class="card empty">


                <i class="fas fa-chart-line"></i>


                <h3>

                    No Earnings Yet

                </h3>


                <p>

                    Your monthly earnings
                    will appear here after
                    completing orders.

                </p>


            </div>


        <?php endif; ?>


    </div>


    <!-- =================================================
         RECENT SALES
         ================================================= -->

    <div class="section">


        <div class="section-title">


            <h2>

                Recent Sales

            </h2>


        </div>


        <div class="table-box">


            <?php if (
                $recent_result->num_rows > 0
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

                                Date

                            </th>


                            <th>

                                Status

                            </th>


                        </tr>


                    </thead>


                    <tbody>


                    <?php while (
                        $sale =
                        $recent_result->fetch_assoc()
                    ): ?>


                        <?php

                        $sale_status =
                            $sale['status']
                            ?? 'Pending';


                        $status_class =
                            strtolower(
                                $sale_status
                            );

                        ?>


                        <tr>


                            <td>

                                <strong>

                                    #<?= (int)
                                    $sale['order_id'] ?>

                                </strong>

                            </td>


                            <td>

                                <?= htmlspecialchars(
                                    $sale[
                                        'buyer_name'
                                    ]
                                    ??
                                    'Unknown Buyer'
                                ) ?>

                            </td>


                            <td>

                                <?= htmlspecialchars(
                                    $sale[
                                        'product_name'
                                    ]
                                ) ?>

                            </td>


                            <td>

                                <?= (int)
                                $sale[
                                    'quantity'
                                ] ?>

                            </td>


                            <td>

                                <strong>

                                    ৳ <?= number_format(
                                        (float)
                                        $sale[
                                            'subtotal'
                                        ],
                                        2
                                    ) ?>

                                </strong>

                            </td>


                            <td>

                                <?= date(
                                    'd M Y',
                                    strtotime(
                                        $sale[
                                            'created_at'
                                        ]
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
                                        $sale_status
                                    ) ?>

                                </span>


                            </td>


                        </tr>


                    <?php endwhile; ?>


                    </tbody>


                </table>


            <?php else: ?>


                <div class="empty">


                    <i class="fas fa-wallet"></i>


                    <h3>

                        No Sales Found

                    </h3>


                    <p>

                        Your sales and earnings
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

$stats_stmt->close();

$recent_stmt->close();

$monthly_stmt->close();

$conn->close();

?>
