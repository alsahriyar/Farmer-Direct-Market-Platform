
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
   REMOVE FROM WISHLIST
   ========================================================= */

if (
    isset($_GET['remove'])
) {

    $wishlist_id =
        (int) $_GET['remove'];


    if ($wishlist_id > 0) {

        $delete_sql = "

            DELETE FROM wishlist

            WHERE id = ?

            AND buyer_id = ?

        ";


        $delete_stmt =
            $conn->prepare(
                $delete_sql
            );


        if ($delete_stmt) {

            $delete_stmt->bind_param(
                "ii",
                $wishlist_id,
                $user_id
            );


            $delete_stmt->execute();


            $delete_stmt->close();

        }

    }


    header(
        "Location: wishlist.php?removed=1"
    );

    exit();

}


/* =========================================================
   REMOVE BY PRODUCT ID
   ========================================================= */

if (
    isset($_GET['remove_product'])
) {

    $product_id =
        (int) $_GET['remove_product'];


    if ($product_id > 0) {

        $delete_product_sql = "

            DELETE FROM wishlist

            WHERE product_id = ?

            AND buyer_id = ?

        ";


        $delete_product_stmt =
            $conn->prepare(
                $delete_product_sql
            );


        if (
            $delete_product_stmt
        ) {

            $delete_product_stmt->bind_param(
                "ii",
                $product_id,
                $user_id
            );


            $delete_product_stmt->execute();


            $delete_product_stmt->close();

        }

    }


    header(
        "Location: wishlist.php?removed=1"
    );

    exit();

}


/* =========================================================
   FETCH WISHLIST PRODUCTS
   ========================================================= */

$wishlist = [];


$sql = "

    SELECT

        w.id AS wishlist_id,

        w.product_id,

        p.name,

        p.category,

        p.price,

        p.unit,

        p.stock,

        p.description,

        p.image,

        p.location,

        p.farmer_id,

        f.name AS farmer_name

    FROM wishlist w

    INNER JOIN products p

        ON w.product_id = p.id

    LEFT JOIN farmers f

        ON p.farmer_id = f.id

    WHERE w.buyer_id = ?

    ORDER BY w.id DESC

";


$stmt =
    $conn->prepare(
        $sql
    );


if (!$stmt) {

    die(
        "Wishlist Query Error: "
        . $conn->error
    );

}


$stmt->bind_param(
    "i",
    $user_id
);


$stmt->execute();


$result =
    $stmt->get_result();


while (
    $row =
    $result->fetch_assoc()
) {

    $wishlist[] =
        $row;

}


$stmt->close();


/* =========================================================
   WISHLIST COUNT
   ========================================================= */

$wishlist_count =
    count(
        $wishlist
    );


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
    Wishlist | Farmer Direct Market
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


.topbar h1 {

    margin-bottom:
        5px;

}


.topbar p {

    color:
        #777;

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
   WISHLIST HEADER
   ========================================================= */

.wishlist-header {

    display:
        flex;

    justify-content:
        space-between;

    align-items:
        center;

    margin-bottom:
        20px;

}


.wishlist-count {

    background:
        #e8f5e9;

    color:
        #0f9d58;

    padding:
        8px 15px;

    border-radius:
        20px;

    font-size:
        13px;

    font-weight:
        600;

}


/* =========================================================
   WISHLIST GRID
   ========================================================= */

.wishlist-grid {

    display:
        grid;

    grid-template-columns:
        repeat(
            auto-fit,
            minmax(
                250px,
                1fr
            )
        );

    gap:
        22px;

}


/* =========================================================
   PRODUCT CARD
   ========================================================= */

.product-card {

    background:
        white;

    border-radius:
        15px;

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

    transition:
        .3s;

    position:
        relative;

}


.product-card:hover {

    transform:
        translateY(
            -5px
        );

    box-shadow:
        0 10px 25px
        rgba(
            0,
            0,
            0,
            .12
        );

}


/* =========================================================
   IMAGE
   ========================================================= */

.product-image {

    position:
        relative;

    height:
        200px;

    background:
        #f1f3f5;

}


.product-image img {

    width:
        100%;

    height:
        100%;

    object-fit:
        cover;

}


.remove-btn {

    position:
        absolute;

    top:
        12px;

    right:
        12px;

    width:
        38px;

    height:
        38px;

    border-radius:
        50%;

    background:
        rgba(
            255,
            255,
            255,
            .95
        );

    color:
        #e53935;

    display:
        flex;

    justify-content:
        center;

    align-items:
        center;

    text-decoration:
        none;

    box-shadow:
        0 3px 10px
        rgba(
            0,
            0,
            0,
            .15
        );

    transition:
        .3s;

}


.remove-btn:hover {

    background:
        #e53935;

    color:
        white;

    transform:
        scale(
            1.1
        );

}


/* =========================================================
   PRODUCT INFO
   ========================================================= */

.product-info {

    padding:
        18px;

}


.product-category {

    display:
        inline-block;

    color:
        #0f9d58;

    background:
        #e8f5e9;

    padding:
        4px 10px;

    border-radius:
        15px;

    font-size:
        11px;

    margin-bottom:
        8px;

}


.product-info h3 {

    font-size:
        18px;

    margin-bottom:
        5px;

}


.farmer {

    color:
        #777;

    font-size:
        12px;

    margin-bottom:
        8px;

}


.price {

    color:
        #0f9d58;

    font-size:
        19px;

    font-weight:
        700;

    margin-top:
        8px;

}


.stock {

    font-size:
        12px;

    margin-top:
        5px;

}


.in-stock {

    color:
        #0f9d58;

}


.out-stock {

    color:
        #e53935;

}


/* =========================================================
   BUTTONS
   ========================================================= */

.card-actions {

    display:
        flex;

    gap:
        8px;

    margin-top:
        15px;

}


.btn {

    flex:
        1;

    display:
        inline-flex;

    justify-content:
        center;

    align-items:
        center;

    gap:
        6px;

    padding:
        10px;

    border-radius:
        8px;

    text-decoration:
        none;

    font-size:
        13px;

    font-weight:
        600;

    transition:
        .3s;

}


.view-btn {

    background:
        #f1f3f5;

    color:
        #333;

}


.view-btn:hover {

    background:
        #e2e6ea;

}


.cart-btn {

    background:
        #0f9d58;

    color:
        white;

    border:
        none;

    cursor:
        pointer;

}


.cart-btn:hover {

    background:
        #0b8043;

}


.cart-btn:disabled {

    background:
        #aaa;

    cursor:
        not-allowed;

}


/* =========================================================
   SUCCESS MESSAGE
   ========================================================= */

.message {

    background:
        #d4edda;

    color:
        #155724;

    padding:
        12px 18px;

    border-radius:
        10px;

    margin-bottom:
        20px;

}


/* =========================================================
   EMPTY WISHLIST
   ========================================================= */

.empty-wishlist {

    background:
        white;

    padding:
        80px 20px;

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


.empty-wishlist i {

    font-size:
        65px;

    color:
        #ddd;

    margin-bottom:
        20px;

}


.empty-wishlist h2 {

    margin-bottom:
        8px;

}


.empty-wishlist p {

    color:
        #777;

    margin-bottom:
        22px;

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
        11px 22px;

    border-radius:
        8px;

}


.shop-btn:hover {

    background:
        #0b8043;

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


    .wishlist-header {

        flex-direction:
            column;

        align-items:
            flex-start;

        gap:
            10px;

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
        >

            <i class="fas fa-box"></i>

            My Orders

        </a>


        <a
            href="wishlist.php"
            class="active"
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

My Wishlist ❤️

</h1>


<p>

Save your favorite farm products for later.

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
     SUCCESS MESSAGE
     ===================================================== -->

<?php if (
    isset($_GET['removed'])
): ?>


<div
    class="message"
>


<i
    class="fas fa-check-circle"
></i>


Product removed from wishlist successfully.


</div>


<?php endif; ?>



<!-- =====================================================
     WISHLIST HEADER
     ===================================================== -->

<div
    class="wishlist-header"
>


<h2>

Wishlist Products

</h2>


<div
    class="wishlist-count"
>


<i
    class="fas fa-heart"
></i>


<?= $wishlist_count ?>

Items


</div>


</div>



<!-- =====================================================
     WISHLIST PRODUCTS
     ===================================================== -->

<?php if (
    !empty($wishlist)
): ?>


<div
    class="wishlist-grid"
>


<?php foreach (
    $wishlist
    as $product
): ?>


<?php

$product_id =
    (int)
    $product['product_id'];


$wishlist_id =
    (int)
    $product['wishlist_id'];


$image =

    !empty(
        $product['image']
    )

    ? trim(
        $product['image']
    )

    : 'https://via.placeholder.com/600x400?text=No+Image';


$stock =
    (int)
    $product['stock'];


?>


<div
    class="product-card"
>


<!-- PRODUCT IMAGE -->

<div
    class="product-image"
>


<img
    src="<?= htmlspecialchars(
        $image
    ) ?>"
    alt="<?= htmlspecialchars(
        $product['name']
    ) ?>"
>


<a
    href="wishlist.php?remove=<?= $wishlist_id ?>"
    class="remove-btn"
    title="Remove from Wishlist"
    onclick="return confirm('আপনি কি এই product টি wishlist থেকে remove করতে চান?');"
>


<i
    class="fas fa-heart"
></i>


</a>


</div>



<!-- PRODUCT INFO -->

<div
    class="product-info"
>


<span
    class="product-category"
>

<?= htmlspecialchars(
    $product['category']
    ??
    'Farm Product'
) ?>


</span>


<h3>

<?= htmlspecialchars(
    $product['name']
) ?>

</h3>


<div
    class="farmer"
>


<i
    class="fas fa-user"
></i>


<?= htmlspecialchars(
    $product['farmer_name']
    ??
    'Unknown Farmer'
) ?>


</div>



<div
    class="price"
>

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


</div>



<div
    class="stock
    <?= $stock > 0
        ? 'in-stock'
        : 'out-stock'
    ?>"
>


<?php if (
    $stock > 0
): ?>


<i
    class="fas fa-check-circle"
></i>


Available


(<?= $stock ?>)


<?php else: ?>


<i
    class="fas fa-times-circle"
></i>


Out of Stock


<?php endif; ?>


</div>



<!-- ACTIONS -->

<div
    class="card-actions"
>


<a
    href="product-details.php?id=<?= $product_id ?>"
    class="btn view-btn"
>


<i
    class="fas fa-eye"
></i>


View


</a>


<?php if (
    $stock > 0
): ?>


<button
    type="button"
    class="btn cart-btn"
    onclick="addToCart(<?= $product_id ?>)"
>


<i
    class="fas fa-cart-plus"
></i>


Cart


</button>


<?php else: ?>


<button
    type="button"
    class="btn cart-btn"
    disabled
>


<i
    class="fas fa-ban"
></i>


Unavailable


</button>


<?php endif; ?>


</div>


</div>


</div>


<?php endforeach; ?>


</div>


<?php else: ?>


<!-- =====================================================
     EMPTY WISHLIST
     ===================================================== -->

<div
    class="empty-wishlist"
>


<i
    class="far fa-heart"
></i>


<h2>

Your Wishlist is Empty

</h2>


<p>

You haven't added any products to your wishlist yet.

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



<!-- =====================================================
     ADD TO CART
     ===================================================== -->

<script>

function addToCart(
    productId
) {


    const formData =
        new FormData();


    formData.append(
        'product_id',
        productId
    );


    formData.append(
        'quantity',
        1
    );


    fetch(
        'add_to_cart.php',
        {

            method:
                'POST',

            body:
                formData

        }
    )


    .then(
        response =>
            response.text()
    )


    .then(
        data => {


            alert(
                data
            );


        }
    )


    .catch(
        error => {


            alert(
                'Cart এ product যোগ করা সম্ভব হয়নি।'
            );


            console.error(
                error
            );


        }
    );

}

</script>


</body>

</html>


<?php

mysqli_close(
    $conn
);

?>
```
