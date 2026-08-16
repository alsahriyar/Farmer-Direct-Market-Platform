
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


/* ================= SESSION ================= */

session_start();

if (
    !isset($_SESSION['user_id']) ||
    !isset($_SESSION['user_type']) ||
    $_SESSION['user_type'] !== 'buyer'
) {
    header("Location: login.php");
    exit();
}

$user_id = (int) $_SESSION['user_id'];

$user_name = $_SESSION['user_name'] ?? 'Buyer';


/* =========================================================
   CART ACTIONS
   ========================================================= */


/* ================= REMOVE PRODUCT ================= */

if (
    isset($_GET['remove']) &&
    is_numeric($_GET['remove'])
) {

    $cart_id = (int) $_GET['remove'];

    $query = "
        DELETE FROM cart
        WHERE id = ?
        AND user_id = ?
    ";

    $stmt = mysqli_prepare($conn, $query);

    mysqli_stmt_bind_param(
        $stmt,
        "ii",
        $cart_id,
        $user_id
    );

    mysqli_stmt_execute($stmt);

    mysqli_stmt_close($stmt);

    header("Location: cart.php");
    exit();
}


/* ================= CLEAR CART ================= */

if (
    isset($_GET['clear']) &&
    $_GET['clear'] === '1'
) {

    $query = "
        DELETE FROM cart
        WHERE user_id = ?
    ";

    $stmt = mysqli_prepare($conn, $query);

    mysqli_stmt_bind_param(
        $stmt,
        "i",
        $user_id
    );

    mysqli_stmt_execute($stmt);

    mysqli_stmt_close($stmt);

    header("Location: cart.php");
    exit();
}


/* ================= UPDATE CART ================= */

if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['update_cart'])
) {

    if (
        isset($_POST['quantity']) &&
        is_array($_POST['quantity'])
    ) {

        foreach (
            $_POST['quantity']
            as $cart_id => $quantity
        ) {

            $cart_id = (int) $cart_id;

            $quantity = (int) $quantity;


            if ($quantity < 1) {
                $quantity = 1;
            }


            /*
             * Make sure the cart item belongs
             * to the logged-in buyer.
             */

            $query = "
                SELECT
                    c.id,
                    c.product_id,
                    p.stock
                FROM cart c

                INNER JOIN products p
                    ON c.product_id = p.id

                WHERE c.id = ?
                AND c.user_id = ?

                LIMIT 1
            ";

            $stmt = mysqli_prepare(
                $conn,
                $query
            );

            mysqli_stmt_bind_param(
                $stmt,
                "ii",
                $cart_id,
                $user_id
            );

            mysqli_stmt_execute($stmt);

            $result = mysqli_stmt_get_result(
                $stmt
            );

            $item = mysqli_fetch_assoc(
                $result
            );

            mysqli_stmt_close($stmt);


            if ($item) {

                $stock = (int) $item['stock'];


                /*
                 * Quantity cannot exceed
                 * available stock.
                 */

                if ($stock > 0 &&
                    $quantity > $stock
                ) {

                    $quantity = $stock;

                }


                $update_query = "
                    UPDATE cart

                    SET quantity = ?

                    WHERE id = ?
                    AND user_id = ?
                ";

                $update_stmt =
                    mysqli_prepare(
                        $conn,
                        $update_query
                    );

                mysqli_stmt_bind_param(
                    $update_stmt,
                    "iii",
                    $quantity,
                    $cart_id,
                    $user_id
                );

                mysqli_stmt_execute(
                    $update_stmt
                );

                mysqli_stmt_close(
                    $update_stmt
                );

            }

        }

    }


    header("Location: cart.php");
    exit();
}


/* =========================================================
   FETCH CART PRODUCTS
   ========================================================= */

$cart_items = [];

$query = "
    SELECT

        c.id AS cart_id,

        c.product_id,

        c.quantity,

        c.added_at,

        p.name,

        p.price,

        p.unit,

        p.stock,

        p.image,

        p.location,

        p.description,

        p.farmer_id,

        f.name AS farmer_name

    FROM cart c

    INNER JOIN products p
        ON c.product_id = p.id

    LEFT JOIN farmers f
        ON p.farmer_id = f.id

    WHERE c.user_id = ?

    ORDER BY c.added_at DESC
";

$stmt = mysqli_prepare(
    $conn,
    $query
);

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $user_id
);

mysqli_stmt_execute(
    $stmt
);

$result = mysqli_stmt_get_result(
    $stmt
);


while (
    $row = mysqli_fetch_assoc(
        $result
    )
) {

    $cart_items[] = $row;

}


mysqli_stmt_close(
    $stmt
);


/* =========================================================
   CALCULATE CART TOTAL
   ========================================================= */

$subtotal = 0;

$total_items = 0;


foreach (
    $cart_items
    as $item
) {

    $quantity =
        (int) $item['quantity'];

    $price =
        (float) $item['price'];

    $subtotal +=
        $price * $quantity;

    $total_items +=
        $quantity;

}


$delivery_charge = 0;


/*
 * Example delivery rule:
 * Free delivery for orders
 * above ৳1000.
 *
 * Otherwise ৳60.
 */

if (
    $subtotal > 0 &&
    $subtotal < 1000
) {

    $delivery_charge = 60;

}


$grand_total =
    $subtotal +
    $delivery_charge;


/* =========================================================
   AVATAR
   ========================================================= */

$avatar_letter = strtoupper(
    mb_substr(
        trim($user_name),
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
    content="width=device-width,
             initial-scale=1.0"
>


<title>
    My Cart | Farmer Direct Market
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

    background:
        rgba(255,255,255,.15);

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

    justify-content:
        space-between;

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
   CART LAYOUT
   ========================================================= */

.cart-layout {

    display: grid;

    grid-template-columns:
        minmax(0, 2fr)
        minmax(300px, 1fr);

    gap: 25px;

    align-items: start;

}


/* =========================================================
   CART BOX
   ========================================================= */

.cart-box {

    background: white;

    border-radius: 15px;

    padding: 20px;

    box-shadow:
        0 4px 15px
        rgba(0,0,0,.08);

}


.cart-header {

    display: flex;

    justify-content:
        space-between;

    align-items: center;

    padding-bottom: 15px;

    margin-bottom: 15px;

    border-bottom:
        1px solid #eee;

}


.clear-btn {

    color: #dc3545;

    text-decoration: none;

    font-size: 14px;

    font-weight: 600;

}


.clear-btn:hover {

    text-decoration: underline;

}


/* =========================================================
   CART ITEM
   ========================================================= */

.cart-item {

    display: grid;

    grid-template-columns:
        100px
        1fr
        auto;

    gap: 18px;

    align-items: center;

    padding: 20px 0;

    border-bottom:
        1px solid #eee;

}


.cart-item:last-child {

    border-bottom: none;

}


.cart-image {

    width: 100px;

    height: 100px;

    border-radius: 12px;

    overflow: hidden;

}


.cart-image img {

    width: 100%;

    height: 100%;

    object-fit: cover;

}


.product-name {

    font-size: 17px;

    font-weight: 600;

    margin-bottom: 5px;

}


.farmer {

    color: #777;

    font-size: 13px;

}


.location {

    color: #777;

    font-size: 13px;

    margin-top: 3px;

}


.product-price {

    color: #0f9d58;

    font-weight: 700;

    margin-top: 8px;

}


/* =========================================================
   QUANTITY
   ========================================================= */

.quantity-box {

    display: flex;

    align-items: center;

    gap: 8px;

    margin-top: 10px;

}


.quantity-box label {

    font-size: 13px;

    color: #666;

}


.quantity-box input {

    width: 65px;

    padding: 7px;

    text-align: center;

    border: 1px solid #ddd;

    border-radius: 7px;

}


.stock-text {

    font-size: 12px;

    color: #777;

    margin-top: 5px;

}


.remove-btn {

    display: inline-block;

    color: #dc3545;

    text-decoration: none;

    padding: 8px;

    border-radius: 6px;

    transition: .3s;

}


.remove-btn:hover {

    background:
        #fff0f0;

}


/* =========================================================
   CART SUMMARY
   ========================================================= */

.summary-box {

    background: white;

    border-radius: 15px;

    padding: 25px;

    box-shadow:
        0 4px 15px
        rgba(0,0,0,.08);

    position: sticky;

    top: 30px;

}


.summary-box h2 {

    margin-bottom: 20px;

}


.summary-row {

    display: flex;

    justify-content:
        space-between;

    padding: 12px 0;

    border-bottom:
        1px solid #eee;

}


.summary-row.total {

    border-bottom: none;

    font-size: 20px;

    font-weight: 700;

    color: #0f9d58;

}


.update-btn {

    width: 100%;

    margin-top: 15px;

    border: none;

    cursor: pointer;

    background: #0f9d58;

    color: white;

    padding: 12px;

    border-radius: 8px;

    font-size: 15px;

    font-weight: 600;

}


.update-btn:hover {

    background: #087f47;

}


.checkout-btn {

    display: block;

    width: 100%;

    text-align: center;

    margin-top: 10px;

    background: #198754;

    color: white;

    text-decoration: none;

    padding: 12px;

    border-radius: 8px;

    font-weight: 600;

}


.continue-btn {

    display: block;

    width: 100%;

    text-align: center;

    margin-top: 10px;

    background: #f1f3f5;

    color: #333;

    text-decoration: none;

    padding: 12px;

    border-radius: 8px;

}


.checkout-btn:hover {

    background: #157347;

}


.continue-btn:hover {

    background: #e2e6ea;

}


/* =========================================================
   EMPTY CART
   ========================================================= */

.empty-cart {

    text-align: center;

    padding: 70px 20px;

}


.empty-cart i {

    font-size: 60px;

    color: #ccc;

    margin-bottom: 20px;

}


.empty-cart h2 {

    margin-bottom: 10px;

}


.empty-cart p {

    color: #777;

    margin-bottom: 20px;

}


.shop-btn {

    display: inline-block;

    background: #0f9d58;

    color: white;

    text-decoration: none;

    padding: 12px 20px;

    border-radius: 8px;

}


/* =========================================================
   RESPONSIVE
   ========================================================= */

@media (
    max-width: 900px
) {

    .cart-layout {

        grid-template-columns: 1fr;

    }

    .summary-box {

        position: static;

    }

}


@media (
    max-width: 768px
) {

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


    .cart-item {

        grid-template-columns:
            80px
            1fr;

    }


    .cart-image {

        width: 80px;

        height: 80px;

    }


    .remove-btn {

        grid-column: 2;

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
            class="active"
        >

            <i class="fas fa-shopping-cart"></i>

            My Cart

        </a>


        <a
            href="orders.php"
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


    <!-- =================================================
         TOPBAR
         ================================================= -->

    <div class="topbar">


        <div>

            <h1>
                My Cart 🛒
            </h1>

            <p>
                Review your selected products.
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



    <?php if (
        !empty($cart_items)
    ): ?>


        <!-- =================================================
             CART LAYOUT
             ================================================= -->

        <div class="cart-layout">


            <!-- =============================================
                 CART PRODUCTS
                 ============================================= -->

            <div class="cart-box">


                <div class="cart-header">


                    <h2>

                        Cart Items
                        (<?= $total_items ?>)

                    </h2>


                    <a
                        href="cart.php?clear=1"
                        class="clear-btn"
                        onclick="
                            return confirm(
                                'Are you sure you want to clear your cart?'
                            );
                        "
                    >

                        <i class="fas fa-trash"></i>

                        Clear Cart

                    </a>


                </div>



                <form
                    method="POST"
                    action="cart.php"
                >


                    <?php foreach (
                        $cart_items
                        as $item
                    ): ?>


                        <?php

                        $image =
                            !empty(
                                $item['image']
                            )

                            ? trim(
                                $item['image']
                            )

                            : 'https://via.placeholder.com/500x500?text=No+Image';


                        $item_total =
                            (float)
                            $item['price']

                            *

                            (int)
                            $item['quantity'];

                        ?>


                        <div class="cart-item">


                            <!-- IMAGE -->

                            <div class="cart-image">


                                <img
                                    src="<?= htmlspecialchars(
                                        $image
                                    ) ?>"
                                    alt="<?= htmlspecialchars(
                                        $item['name']
                                    ) ?>"
                                >


                            </div>



                            <!-- PRODUCT INFO -->

                            <div>


                                <div
                                    class="product-name"
                                >

                                    <?= htmlspecialchars(
                                        $item['name']
                                    ) ?>

                                </div>


                                <div
                                    class="farmer"
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


                                <?php if (
                                    !empty(
                                        $item['location']
                                    )
                                ): ?>


                                    <div
                                        class="location"
                                    >

                                        <i
                                            class="fas fa-location-dot"
                                        ></i>

                                        <?= htmlspecialchars(
                                            $item['location']
                                        ) ?>

                                    </div>


                                <?php endif; ?>


                                <div
                                    class="product-price"
                                >

                                    ৳

                                    <?= number_format(
                                        (float)
                                        $item['price'],
                                        2
                                    ) ?>

                                    /

                                    <?= htmlspecialchars(
                                        $item['unit']
                                    ) ?>


                                </div>



                                <div
                                    class="quantity-box"
                                >


                                    <label>

                                        Quantity:

                                    </label>


                                    <input
                                        type="number"
                                        name="quantity[<?= (int) $item['cart_id'] ?>]"
                                        value="<?= (int) $item['quantity'] ?>"
                                        min="1"
                                        max="<?= (int) $item['stock'] ?>"
                                    >


                                </div>


                                <div
                                    class="stock-text"
                                >

                                    Available Stock:

                                    <?= (int) $item['stock'] ?>

                                    <?= htmlspecialchars(
                                        $item['unit']
                                    ) ?>

                                </div>


                            </div>



                            <!-- REMOVE -->

                            <div>


                                <strong>

                                    ৳

                                    <?= number_format(
                                        $item_total,
                                        2
                                    ) ?>

                                </strong>


                                <br>


                                <a
                                    href="cart.php?remove=<?= (int) $item['cart_id'] ?>"
                                    class="remove-btn"
                                    onclick="
                                        return confirm(
                                            'Remove this product from cart?'
                                        );
                                    "
                                >

                                    <i
                                        class="fas fa-trash"
                                    ></i>

                                    Remove

                                </a>


                            </div>


                        </div>


                    <?php endforeach; ?>



                    <button
                        type="submit"
                        name="update_cart"
                        class="update-btn"
                    >

                        <i
                            class="fas fa-refresh"
                        ></i>

                        Update Cart

                    </button>


                </form>


            </div>



            <!-- =============================================
                 CART SUMMARY
                 ============================================= -->

            <div class="summary-box">


                <h2>
                    Order Summary
                </h2>


                <div class="summary-row">

                    <span>
                        Total Items
                    </span>

                    <strong>

                        <?= $total_items ?>

                    </strong>

                </div>


                <div class="summary-row">

                    <span>
                        Subtotal
                    </span>

                    <strong>

                        ৳

                        <?= number_format(
                            $subtotal,
                            2
                        ) ?>

                    </strong>

                </div>


                <div class="summary-row">

                    <span>
                        Delivery
                    </span>

                    <strong>

                        <?php if (
                            $delivery_charge > 0
                        ): ?>

                            ৳

                            <?= number_format(
                                $delivery_charge,
                                2
                            ) ?>

                        <?php else: ?>

                            Free

                        <?php endif; ?>

                    </strong>

                </div>


                <div
                    class="summary-row total"
                >

                    <span>
                        Total
                    </span>

                    <strong>

                        ৳

                        <?= number_format(
                            $grand_total,
                            2
                        ) ?>

                    </strong>

                </div>


                <a
                    href="checkout.php"
                    class="checkout-btn"
                >

                    <i
                        class="fas fa-credit-card"
                    ></i>

                    Proceed to Checkout

                </a>


                <a
                    href="products.php"
                    class="continue-btn"
                >

                    <i
                        class="fas fa-arrow-left"
                    ></i>

                    Continue Shopping

                </a>


            </div>


        </div>


    <?php else: ?>


        <!-- =================================================
             EMPTY CART
             ================================================= -->

        <div class="cart-box">


            <div class="empty-cart">


                <i
                    class="fas fa-cart-shopping"
                ></i>


                <h2>

                    Your Cart is Empty

                </h2>


                <p>

                    You haven't added any products
                    to your cart yet.

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


        </div>


    <?php endif; ?>


</div>


</body>

</html>


<?php

/* ================= CLOSE DATABASE ================= */

mysqli_close(
    $conn
);

?>
```
