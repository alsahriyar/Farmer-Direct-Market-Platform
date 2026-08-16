<?php

session_start();


/* =========================================================
   DATABASE CONNECTION
   ========================================================= */

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


/* CONNECTION CHECK */

if ($conn->connect_error) {

    die(
        "Database Connection Failed: "
        . $conn->connect_error
    );

}


$conn->set_charset("utf8mb4");


/* =========================================================
   BUYER LOGIN CHECK
   ========================================================= */

if (
    !isset($_SESSION['user_id']) ||
    !isset($_SESSION['user_type']) ||
    $_SESSION['user_type'] !== 'buyer'
) {

    header("Location: login.php");

    exit();

}


$user_id =
    (int) $_SESSION['user_id'];


$user_name =
    $_SESSION['user_name']
    ??
    "Buyer";


/* =========================================================
   GET BUYER INFORMATION
   ========================================================= */

$buyer_name =
    $user_name;

$buyer_email =
    "";

$buyer_phone =
    "";

$buyer_address =
    "";


/*
   এখানে তোমার users table-এর column অনুযায়ী
   তথ্য নেওয়া হচ্ছে।
*/

$user_sql = "

    SELECT
        name,
        email,
        phone,
        address

    FROM users

    WHERE id = ?

    LIMIT 1

";


$user_stmt =
    $conn->prepare($user_sql);


if ($user_stmt) {

    $user_stmt->bind_param(
        "i",
        $user_id
    );

    $user_stmt->execute();

    $user_result =
        $user_stmt->get_result();

    $user_data =
        $user_result->fetch_assoc();

    $user_stmt->close();


    if ($user_data) {

        $buyer_name =
            $user_data['name']
            ??
            $user_name;

        $buyer_email =
            $user_data['email']
            ??
            "";

        $buyer_phone =
            $user_data['phone']
            ??
            "";

        $buyer_address =
            $user_data['address']
            ??
            "";

    }

}


/* =========================================================
   MESSAGE
   ========================================================= */

$message = "";

$message_type = "";


/* =========================================================
   PLACE ORDER
   ========================================================= */

if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['place_order'])
) {


    /* =====================================================
       GET CART PRODUCTS FROM DATABASE
       ===================================================== */

    $cart_sql = "

        SELECT

            c.product_id,

            c.quantity,

            p.name,

            p.category,

            p.price,

            p.unit,

            p.stock,

            p.image,

            p.farmer_id

        FROM cart c

        INNER JOIN products p

            ON c.product_id = p.id

        WHERE c.user_id = ?

        ORDER BY c.id DESC

    ";


    $cart_stmt =
        $conn->prepare($cart_sql);


    if (!$cart_stmt) {

        $message =
            "❌ Unable to load your cart.";

        $message_type =
            "error";

    }

    else {


        $cart_stmt->bind_param(
            "i",
            $user_id
        );


        $cart_stmt->execute();


        $cart_result =
            $cart_stmt->get_result();


        $order_items =
            [];


        $final_total =
            0;


        while (
            $cart_product =
            $cart_result->fetch_assoc()
        ) {


            $product_id =
                (int)
                $cart_product['product_id'];


            $quantity =
                (int)
                $cart_product['quantity'];


            $stock =
                (int)
                $cart_product['stock'];


            $price =
                (float)
                $cart_product['price'];


            /* =============================================
               VALIDATE QUANTITY
               ============================================= */

            if (
                $quantity <= 0
            ) {

                continue;

            }


            /* =============================================
               CHECK STOCK
               ============================================= */

            if (
                $stock < $quantity
            ) {

                $message =

                    "❌ Not enough stock available for: "
                    .
                    $cart_product['name'];

                $message_type =
                    "error";

                break;

            }


            /* =============================================
               CALCULATE SUBTOTAL
               ============================================= */

            $subtotal =
                $price * $quantity;


            $final_total +=
                $subtotal;


            /* =============================================
               SAVE ORDER ITEM DATA
               ============================================= */

            $order_items[] = [

                "product_id"
                    =>
                    $product_id,

                "quantity"
                    =>
                    $quantity,

                "price"
                    =>
                    $price,

                "farmer_id"
                    =>
                    (int)
                    $cart_product['farmer_id']

            ];

        }


        $cart_stmt->close();


        /* =================================================
           IF NO ERROR
           ================================================= */

        if (
            $message === ""
        ) {


            if (
                empty($order_items) ||
                $final_total <= 0
            ) {

                $message =

                    "❌ Your cart is empty. Please add products before checkout.";

                $message_type =
                    "error";

            }

            else {


                /* =========================================
                   START DATABASE TRANSACTION
                   ========================================= */

                $conn->begin_transaction();


                try {


                    /* =====================================
                       CREATE ORDER
                       ===================================== */

                    $status =
                        "pending";


                    $order_sql = "

                        INSERT INTO orders

                        (
                            buyer_id,

                            total_amount,

                            status

                        )

                        VALUES

                        (
                            ?,

                            ?,

                            ?

                        )

                    ";


                    $order_stmt =
                        $conn->prepare(
                            $order_sql
                        );


                    if (
                        !$order_stmt
                    ) {

                        throw new Exception(

                            "Order creation failed: "
                            .
                            $conn->error

                        );

                    }


                    $order_stmt->bind_param(

                        "ids",

                        $user_id,

                        $final_total,

                        $status

                    );


                    if (
                        !$order_stmt->execute()
                    ) {

                        throw new Exception(

                            "Could not create order."

                        );

                    }


                    /* GET NEW ORDER ID */

                    $order_id =
                        $conn->insert_id;


                    $order_stmt->close();


                    /* =====================================
                       INSERT ORDER ITEMS
                       ===================================== */

                    $item_sql = "

                        INSERT INTO order_items

                        (
                            order_id,

                            product_id,

                            quantity,

                            price

                        )

                        VALUES

                        (
                            ?,

                            ?,

                            ?,

                            ?

                        )

                    ";


                    $item_stmt =
                        $conn->prepare(
                            $item_sql
                        );


                    if (
                        !$item_stmt
                    ) {

                        throw new Exception(

                            "Order item creation failed."

                        );

                    }


                    /* =====================================
                       UPDATE PRODUCT STOCK
                       ===================================== */

                    $stock_sql = "

                        UPDATE products

                        SET stock =
                            stock - ?

                        WHERE id = ?

                        AND stock >= ?

                    ";


                    $stock_stmt =
                        $conn->prepare(
                            $stock_sql
                        );


                    if (
                        !$stock_stmt
                    ) {

                        throw new Exception(

                            "Stock update failed."

                        );

                    }


                    /* =====================================
                       PROCESS ORDER ITEMS
                       ===================================== */

                    foreach (
                        $order_items
                        as $item
                    ) {


                        $product_id =
                            $item['product_id'];


                        $quantity =
                            $item['quantity'];


                        $price =
                            $item['price'];


                        /* INSERT ORDER ITEM */

                        $item_stmt->bind_param(

                            "iiid",

                            $order_id,

                            $product_id,

                            $quantity,

                            $price

                        );


                        if (
                            !$item_stmt->execute()
                        ) {

                            throw new Exception(

                                "Failed to save order item."

                            );

                        }


                        /* UPDATE STOCK */

                        $stock_stmt->bind_param(

                            "iii",

                            $quantity,

                            $product_id,

                            $quantity

                        );


                        if (
                            !$stock_stmt->execute()
                        ) {

                            throw new Exception(

                                "Failed to update product stock."

                            );

                        }


                        if (
                            $stock_stmt->affected_rows
                            ===
                            0
                        ) {

                            throw new Exception(

                                "Product stock is no longer available."

                            );

                        }

                    }


                    $item_stmt->close();

                    $stock_stmt->close();


                    /* =====================================
                       DELETE BUYER CART
                       ===================================== */

                    $delete_cart_sql = "

                        DELETE FROM cart

                        WHERE user_id = ?

                    ";


                    $delete_cart_stmt =
                        $conn->prepare(
                            $delete_cart_sql
                        );


                    if (
                        !$delete_cart_stmt
                    ) {

                        throw new Exception(

                            "Could not clear cart."

                        );

                    }


                    $delete_cart_stmt->bind_param(

                        "i",

                        $user_id

                    );


                    if (
                        !$delete_cart_stmt->execute()
                    ) {

                        throw new Exception(

                            "Could not clear cart."

                        );

                    }


                    $delete_cart_stmt->close();


                    /* =====================================
                       COMMIT TRANSACTION
                       ===================================== */

                    $conn->commit();


                    /* =====================================
                       REDIRECT TO ORDERS
                       ===================================== */

                    header(

                        "Location: orders.php?success=1&order_id="
                        .
                        $order_id

                    );


                    exit();


                }

                catch (
                    Exception $e
                ) {


                    /* ROLLBACK */

                    $conn->rollback();


                    $message =

                        "❌ Order failed: "
                        .
                        $e->getMessage();


                    $message_type =
                        "error";

                }

            }

        }

    }

}


/* =========================================================
   GET CART FOR DISPLAY
   ========================================================= */

$cart_products =
    [];


$total_amount =
    0;


$display_cart_sql = "

    SELECT

        c.product_id,

        c.quantity,

        p.name,

        p.category,

        p.price,

        p.unit,

        p.stock,

        p.image,

        p.farmer_id

    FROM cart c

    INNER JOIN products p

        ON c.product_id = p.id

    WHERE c.user_id = ?

    ORDER BY c.id DESC

";


$display_cart_stmt =
    $conn->prepare(
        $display_cart_sql
    );


if ($display_cart_stmt) {


    $display_cart_stmt->bind_param(

        "i",

        $user_id

    );


    $display_cart_stmt->execute();


    $display_cart_result =
        $display_cart_stmt->get_result();


    while (
        $product =
        $display_cart_result->fetch_assoc()
    ) {


        $quantity =
            (int)
            $product['quantity'];


        $price =
            (float)
            $product['price'];


        $subtotal =
            $quantity * $price;


        $product['subtotal'] =
            $subtotal;


        $cart_products[] =
            $product;


        $total_amount +=
            $subtotal;

    }


    $display_cart_stmt->close();

}


/* =========================================================
   CHECK EMPTY CART
   ========================================================= */

$cart_is_empty =
    empty($cart_products);


/* =========================================================
   AVATAR
   ========================================================= */

$avatar =
    strtoupper(

        mb_substr(

            $buyer_name,

            0,

            1,

            "UTF-8"

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

Checkout | Farmer Direct Market

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
   RESET
   ========================================================= */

* {

    margin:
        0;

    padding:
        0;

    box-sizing:
        border-box;

    font-family:
        'Poppins',
        sans-serif;

}


/* =========================================================
   BODY
   ========================================================= */

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

    position:
        fixed;

    top:
        0;

    left:
        0;

    width:
        260px;

    height:
        100vh;

    background:
        #0f9d58;

    color:
        white;

    padding:
        25px;

}


.logo {

    font-size:
        24px;

    font-weight:
        700;

    margin-bottom:
        40px;

}


.menu a {

    display:
        block;

    color:
        white;

    text-decoration:
        none;

    padding:
        12px;

    margin-bottom:
        10px;

    border-radius:
        8px;

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

    display:
        flex;

    justify-content:
        space-between;

    align-items:
        center;

    margin-bottom:
        30px;

}


.topbar p {

    color:
        #777;

    margin-top:
        5px;

}


.profile {

    display:
        flex;

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

    display:
        flex;

    justify-content:
        center;

    align-items:
        center;

    font-weight:
        bold;

}


/* =========================================================
   CHECKOUT GRID
   ========================================================= */

.checkout-grid {

    display:
        grid;

    grid-template-columns:
        1.5fr 1fr;

    gap:
        25px;

    align-items:
        start;

}


/* =========================================================
   CARD
   ========================================================= */

.card {

    background:
        white;

    padding:
        25px;

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


.card h2 {

    margin-bottom:
        20px;

}


/* =========================================================
   FORM
   ========================================================= */

.form-group {

    margin-bottom:
        18px;

}


.form-group label {

    display:
        block;

    font-size:
        13px;

    font-weight:
        600;

    color:
        #555;

    margin-bottom:
        7px;

}


.form-group input,

.form-group textarea {

    width:
        100%;

    padding:
        12px 15px;

    border:
        1px solid
        #ddd;

    border-radius:
        9px;

    outline:
        none;

    background:
        #f9fafb;

}


.form-group textarea {

    resize:
        vertical;

}


/* =========================================================
   PRODUCT ITEM
   ========================================================= */

.checkout-item {

    display:
        flex;

    align-items:
        center;

    gap:
        15px;

    padding:
        15px 0;

    border-bottom:
        1px solid
        #eee;

}


.item-image {

    width:
        75px;

    height:
        75px;

    border-radius:
        10px;

    overflow:
        hidden;

    background:
        #eee;

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


.item-info h3 {

    font-size:
        15px;

    margin-bottom:
        5px;

}


.item-info p {

    font-size:
        12px;

    color:
        #777;

}


.item-price {

    color:
        #0f9d58;

    font-weight:
        700;

}


/* =========================================================
   SUMMARY
   ========================================================= */

.summary-row {

    display:
        flex;

    justify-content:
        space-between;

    padding:
        10px 0;

    color:
        #555;

}


.summary-total {

    display:
        flex;

    justify-content:
        space-between;

    padding-top:
        18px;

    margin-top:
        10px;

    border-top:
        1px solid
        #eee;

    font-size:
        20px;

    font-weight:
        700;

}


.summary-total span:last-child {

    color:
        #0f9d58;

}


/* =========================================================
   PAYMENT
   ========================================================= */

.payment-option {

    display:
        flex;

    align-items:
        center;

    gap:
        12px;

    padding:
        13px;

    border:
        1px solid
        #ddd;

    border-radius:
        10px;

    margin-bottom:
        10px;

}


.payment-option input {

    accent-color:
        #0f9d58;

}


.payment-option i {

    color:
        #0f9d58;

}


/* =========================================================
   BUTTON
   ========================================================= */

.place-order-btn {

    width:
        100%;

    border:
        none;

    background:
        #0f9d58;

    color:
        white;

    padding:
        15px;

    border-radius:
        10px;

    font-size:
        16px;

    font-weight:
        600;

    cursor:
        pointer;

    margin-top:
        15px;

    transition:
        .3s;

}


.place-order-btn:hover {

    background:
        #0b8043;

    transform:
        translateY(
            -2px
        );

}


/* =========================================================
   BACK BUTTON
   ========================================================= */

.back-cart {

    display:
        inline-block;

    color:
        #0f9d58;

    text-decoration:
        none;

    margin-bottom:
        20px;

}


.back-cart:hover {

    text-decoration:
        underline;

}


/* =========================================================
   EMPTY CART
   ========================================================= */

.empty-checkout {

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


.empty-checkout i {

    font-size:
        60px;

    color:
        #ccc;

    margin-bottom:
        20px;

}


.shop-btn {

    display:
        inline-block;

    margin-top:
        15px;

    background:
        #0f9d58;

    color:
        white;

    text-decoration:
        none;

    padding:
        11px 22px;

    border-radius:
        8px;

}


/* =========================================================
   ERROR MESSAGE
   ========================================================= */

.message {

    padding:
        14px 18px;

    border-radius:
        10px;

    margin-bottom:
        20px;

}


.message.error {

    background:
        #f8d7da;

    color:
        #842029;

}


/* =========================================================
   RESPONSIVE
   ========================================================= */

@media (
    max-width: 900px
) {

    .checkout-grid {

        grid-template-columns:
            1fr;

    }

}


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


<!-- TOPBAR -->

<div class="topbar">


<div>


<h1>

Checkout 🛍️

</h1>


<p>

Review your order and complete your purchase.

</p>


</div>


<div class="profile">


<div class="avatar">

<?= htmlspecialchars(
    $avatar
) ?>

</div>


<div>


<strong>

<?= htmlspecialchars(
    $buyer_name
) ?>

</strong>


<p>

Buyer Account

</p>


</div>


</div>


</div>



<?php if (
    $message !== ""
): ?>


<div
    class="message <?= htmlspecialchars(
        $message_type
    ) ?>"
>

<?= htmlspecialchars(
    $message
) ?>

</div>


<?php endif; ?>



<?php if (
    $cart_is_empty
): ?>


<!-- EMPTY CART -->

<div
    class="empty-checkout"
>


<i
    class="fas fa-cart-shopping"
></i>


<h2>

Your Cart is Empty

</h2>


<p>

Please add products to your cart before checkout.

</p>


<a
    href="products.php"
    class="shop-btn"
>

Browse Products

</a>


</div>


<?php else: ?>


<a
    href="cart.php"
    class="back-cart"
>

<i
    class="fas fa-arrow-left"
></i>

Back to Cart

</a>



<!-- CHECKOUT GRID -->

<div
    class="checkout-grid"
>


<!-- LEFT -->

<div>


<!-- DELIVERY INFORMATION -->

<div
    class="card"
>


<h2>

Delivery Information

</h2>


<div
    class="form-group"
>


<label>

Full Name

</label>


<input
    type="text"
    value="<?= htmlspecialchars(
        $buyer_name
    ) ?>"
    readonly
>


</div>


<div
    class="form-group"
>


<label>

Email

</label>


<input
    type="email"
    value="<?= htmlspecialchars(
        $buyer_email
    ) ?>"
    readonly
>


</div>


<div
    class="form-group"
>


<label>

Phone Number

</label>


<input
    type="text"
    value="<?= htmlspecialchars(
        $buyer_phone
    ) ?>"
    readonly
>


</div>


<div
    class="form-group"
>


<label>

Delivery Address

</label>


<textarea
    rows="4"
    readonly
><?= htmlspecialchars(
    $buyer_address
) ?></textarea>


</div>


</div>



<!-- PAYMENT -->

<div
    class="card"
    style="margin-top:25px;"
>


<h2>

Payment Method

</h2>


<label
    class="payment-option"
>


<input
    type="radio"
    checked
>


<i
    class="fas fa-money-bill-wave"
></i>


<div>


<strong>

Cash on Delivery

</strong>


<p
    style="font-size:12px;color:#777;"
>

Pay when your order arrives.

</p>


</div>


</label>


<label
    class="payment-option"
>


<input
    type="radio"
    disabled
>


<i
    class="fas fa-credit-card"
></i>


<div>


<strong>

Online Payment

</strong>


<p
    style="font-size:12px;color:#777;"
>

Coming soon.

</p>


</div>


</label>


</div>


</div>



<!-- RIGHT -->

<div>


<div
    class="card"
>


<h2>

Order Summary

</h2>


<?php foreach (
    $cart_products
    as $product
): ?>


<?php

$image =

    !empty(
        $product['image']
    )

    ?

    $product['image']

    :

    'https://via.placeholder.com/150?text=No+Image';

?>


<div
    class="checkout-item"
>


<div
    class="item-image"
>


<img
    src="<?= htmlspecialchars(
        $image
    ) ?>"
    alt="<?= htmlspecialchars(
        $product['name']
    ) ?>"
>


</div>


<div
    class="item-info"
>


<h3>

<?= htmlspecialchars(
    $product['name']
) ?>

</h3>


<p>

<?= (int)
    $product['quantity']
?>

×

৳

<?= number_format(
    (float)
    $product['price'],
    2
) ?>


/

<?= htmlspecialchars(
    $product['unit']
) ?>

</p>


</div>


<div
    class="item-price"
>

৳

<?= number_format(
    (float)
    $product['subtotal'],
    2
) ?>

</div>


</div>


<?php endforeach; ?>



<!-- SUBTOTAL -->

<div
    class="summary-row"
>


<span>

Subtotal

</span>


<span>

৳

<?= number_format(
    $total_amount,
    2
) ?>

</span>


</div>



<!-- DELIVERY -->

<div
    class="summary-row"
>


<span>

Delivery Charge

</span>


<span>

৳ 0.00

</span>


</div>



<!-- TOTAL -->

<div
    class="summary-total"
>


<span>

Total

</span>


<span>

৳

<?= number_format(
    $total_amount,
    2
) ?>

</span>


</div>



<!-- PLACE ORDER -->

<form
    method="POST"
    onsubmit="return confirmOrder();"
>


<input
    type="hidden"
    name="place_order"
    value="1"
>


<button
    type="submit"
    class="place-order-btn"
>


<i
    class="fas fa-check-circle"
></i>


Place Order


</button>


</form>


</div>


</div>


</div>


<?php endif; ?>


</div>



<script>


function confirmOrder() {


    return confirm(

        "Are you sure you want to place this order?"

    );


}


</script>


</body>


</html>


<?php

$conn->close();

?>
