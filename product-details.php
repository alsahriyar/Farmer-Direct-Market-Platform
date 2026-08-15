<?php
session_start();

$servername = "localhost";
$username   = "root";
$password   = "";
$dbname     = "farm_db";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");

$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($product_id <= 0) {
    header("Location: products.php");
    exit;
}

/* ================= PRODUCT QUERY ================= */

$stmt = $conn->prepare("
    SELECT 
        p.*,
        f.name AS farmer_name,
        f.rating,
        f.verified,
        f.location AS farmer_location
    FROM products p
    LEFT JOIN farmers f ON p.farmer_id = f.id
    WHERE p.id = ?
    LIMIT 1
");

$stmt->bind_param("i", $product_id);
$stmt->execute();

$result = $stmt->get_result();
$product = $result->fetch_assoc();

$stmt->close();

if (!$product) {
    echo "
    <!DOCTYPE html>
    <html>
    <head>
        <title>Product Not Found</title>
        <style>
            body{
                margin:0;
                min-height:100vh;
                display:flex;
                align-items:center;
                justify-content:center;
                font-family:Arial;
                background:#f5f7fa;
            }
            .error-box{
                background:#fff;
                padding:50px;
                border-radius:20px;
                text-align:center;
                box-shadow:0 20px 50px rgba(0,0,0,.1);
            }
            a{
                display:inline-block;
                margin-top:20px;
                padding:12px 25px;
                background:#0f9d58;
                color:#fff;
                text-decoration:none;
                border-radius:10px;
            }
        </style>
    </head>
    <body>
        <div class='error-box'>
            <h2>Product Not Found!</h2>
            <p>The product you are looking for does not exist.</p>
            <a href='products.php'>Back to Products</a>
        </div>
    </body>
    </html>
    ";
    exit;
}

$product_image = !empty($product['image'])
    ? $product['image']
    : "https://via.placeholder.com/900x700?text=No+Image";

$farmer_name = !empty($product['farmer_name'])
    ? $product['farmer_name']
    : "Local Farmer";

$farmer_initial = strtoupper(substr($farmer_name, 0, 1));

$rating = isset($product['rating'])
    ? number_format((float)$product['rating'], 1)
    : "0.0";

$stock = (int)$product['stock'];

$is_verified = !empty($product['verified']);

$harvest_date = !empty($product['harvest_date'])
    ? date('d F Y', strtotime($product['harvest_date']))
    : "Not specified";

$location = !empty($product['location'])
    ? $product['location']
    : "Not specified";

$farmer_location = !empty($product['farmer_location'])
    ? $product['farmer_location']
    : "Not specified";

$description = !empty($product['description'])
    ? $product['description']
    : "Fresh quality product directly sourced from the farmer.";

$benefits = !empty($product['benefits'])
    ? $product['benefits']
    : "High nutritional value and fresh from farm.";

$shelf_life = !empty($product['shelf_life'])
    ? $product['shelf_life']
    : "3-5 days after delivery";
?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>
<?php echo htmlspecialchars($product['name']); ?>
| Farmer Direct Market
</title>

<link rel="preconnect" href="https://fonts.googleapis.com">

<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">

<link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<style>

/* =====================================================
   GLOBAL
===================================================== */

:root{
    --primary:#0f9d58;
    --primary-dark:#087f45;
    --primary-light:#e9f8f0;

    --dark:#111827;
    --text:#374151;
    --muted:#6b7280;

    --border:#e5e7eb;
    --bg:#f7faf8;

    --white:#ffffff;

    --shadow-sm:0 5px 20px rgba(15,23,42,.06);
    --shadow-md:0 15px 40px rgba(15,23,42,.09);
    --shadow-lg:0 30px 80px rgba(15,23,42,.12);
}

*{
    margin:0;
    padding:0;
    box-sizing:border-box;
}

html{
    scroll-behavior:smooth;
}

body{
    font-family:'Inter',sans-serif;
    background:
        radial-gradient(
            circle at top left,
            rgba(15,157,88,.05),
            transparent 35%
        ),
        var(--bg);
    color:var(--text);
    line-height:1.6;
}

button,
input{
    font-family:inherit;
}

a{
    text-decoration:none;
}


/* =====================================================
   NAVBAR
===================================================== */

.navbar{
    position:sticky;
    top:0;
    z-index:999;

    height:76px;

    display:flex;
    align-items:center;
    justify-content:space-between;

    padding:0 6%;

    background:rgba(255,255,255,.92);

    backdrop-filter:blur(18px);
    -webkit-backdrop-filter:blur(18px);

    border-bottom:1px solid rgba(229,231,235,.8);

    box-shadow:0 5px 25px rgba(15,23,42,.04);
}

.logo{
    display:flex;
    align-items:center;
    gap:10px;

    font-size:22px;
    font-weight:800;

    color:var(--primary);

    letter-spacing:-.5px;
}

.logo-icon{
    width:42px;
    height:42px;

    display:flex;
    align-items:center;
    justify-content:center;

    border-radius:13px;

    background:linear-gradient(
        135deg,
        var(--primary),
        var(--primary-dark)
    );

    color:white;

    box-shadow:
        0 8px 20px rgba(15,157,88,.25);
}

.nav-links{
    display:flex;
    align-items:center;
    gap:8px;
}

.nav-links a{
    color:#4b5563;

    font-size:14px;
    font-weight:600;

    padding:10px 15px;

    border-radius:10px;

    transition:.3s;
}

.nav-links a:hover{
    color:var(--primary);
    background:var(--primary-light);
}

.nav-right{
    display:flex;
    align-items:center;
    gap:14px;
}

.cart-icon{
    position:relative;

    width:44px;
    height:44px;

    display:flex;
    align-items:center;
    justify-content:center;

    color:#374151;

    border:1px solid var(--border);
    border-radius:12px;

    transition:.3s;
}

.cart-icon:hover{
    color:var(--primary);
    border-color:#a7e2c3;
    background:var(--primary-light);
    transform:translateY(-2px);
}

.user-info{
    display:flex;
    align-items:center;
    gap:9px;

    padding:8px 14px 8px 8px;

    border:1px solid var(--border);

    border-radius:30px;

    background:#fff;

    font-size:14px;
    font-weight:600;
}

.user-avatar{
    width:34px;
    height:34px;

    display:flex;
    align-items:center;
    justify-content:center;

    border-radius:50%;

    color:#fff;

    background:linear-gradient(
        135deg,
        var(--primary),
        var(--primary-dark)
    );
}

.login-btn{
    color:var(--primary);

    padding:10px 18px;

    border:1px solid #b9e7ce;

    border-radius:10px;

    font-weight:700;

    transition:.3s;
}

.login-btn:hover{
    background:var(--primary);
    color:white;
}


/* =====================================================
   BREADCRUMB
===================================================== */

.breadcrumb-wrapper{
    max-width:1280px;
    margin:0 auto;

    padding:28px 25px 0;
}

.breadcrumb{
    display:flex;
    align-items:center;
    gap:10px;

    color:#8a94a6;

    font-size:13px;
    font-weight:500;
}

.breadcrumb a{
    color:var(--primary);
    font-weight:600;
}

.breadcrumb i{
    font-size:10px;
}


/* =====================================================
   MAIN CONTAINER
===================================================== */

.container{
    width:90%;
    max-width:1280px;

    margin:25px auto 80px;
}


/* =====================================================
   PRODUCT MAIN CARD
===================================================== */

.product-wrapper{
    display:grid;

    grid-template-columns:
        minmax(0,1.05fr)
        minmax(0,.95fr);

    gap:65px;

    background:#fff;

    padding:45px;

    border-radius:30px;

    border:1px solid rgba(229,231,235,.8);

    box-shadow:var(--shadow-lg);
}


/* =====================================================
   IMAGE
===================================================== */

.product-image{
    position:relative;

    overflow:hidden;

    border-radius:24px;

    background:#f1f5f3;

    min-height:520px;
}

.product-image img{
    width:100%;
    height:520px;

    display:block;

    object-fit:cover;

    transition:
        transform .7s ease;
}

.product-image:hover img{
    transform:scale(1.04);
}

.image-overlay{
    position:absolute;

    left:20px;
    bottom:20px;

    display:flex;
    align-items:center;
    gap:8px;

    padding:10px 16px;

    border-radius:30px;

    color:#fff;

    font-size:13px;
    font-weight:700;

    background:rgba(15,23,42,.72);

    backdrop-filter:blur(10px);
}

.image-overlay i{
    color:#4ade80;
}


/* =====================================================
   PRODUCT CONTENT
===================================================== */

.product-details{
    display:flex;
    flex-direction:column;

    justify-content:center;
}

.category{
    display:inline-flex;
    align-items:center;
    gap:7px;

    width:max-content;

    padding:8px 14px;

    border-radius:30px;

    color:var(--primary);

    background:var(--primary-light);

    font-size:12px;

    font-weight:800;

    text-transform:uppercase;

    letter-spacing:1px;
}

.product-title{
    margin-top:20px;

    color:var(--dark);

    font-family:'Playfair Display',serif;

    font-size:48px;

    line-height:1.15;

    letter-spacing:-1px;
}

.price{
    display:flex;
    align-items:baseline;
    gap:8px;

    margin-top:18px;

    color:var(--primary);

    font-size:36px;

    font-weight:800;
}

.price-unit{
    color:#8b95a3;

    font-size:15px;

    font-weight:500;
}

.description{
    margin-top:22px;

    color:#667085;

    font-size:15px;

    line-height:1.9;
}


/* =====================================================
   QUICK INFO
===================================================== */

.info-grid{
    display:grid;

    grid-template-columns:
        repeat(2,1fr);

    gap:12px;

    margin-top:28px;
}

.info-box{
    display:flex;
    align-items:center;

    gap:13px;

    padding:16px;

    border:1px solid #edf0f2;

    border-radius:16px;

    background:#fbfcfc;

    transition:.3s;
}

.info-box:hover{
    border-color:#bfe7ce;

    background:#f7fcf9;

    transform:translateY(-2px);
}

.info-icon{
    width:42px;
    height:42px;

    flex-shrink:0;

    display:flex;
    align-items:center;
    justify-content:center;

    color:var(--primary);

    background:var(--primary-light);

    border-radius:12px;
}

.info-text span{
    display:block;

    color:#98a2b3;

    font-size:11px;

    font-weight:600;

    text-transform:uppercase;

    letter-spacing:.5px;
}

.info-text strong{
    display:block;

    margin-top:2px;

    color:#344054;

    font-size:13px;
}


/* =====================================================
   STOCK
===================================================== */

.stock-status{
    display:flex;
    align-items:center;
    gap:8px;

    margin-top:22px;

    font-size:13px;
    font-weight:700;
}

.stock-dot{
    width:9px;
    height:9px;

    border-radius:50%;

    background:#22c55e;

    box-shadow:
        0 0 0 5px rgba(34,197,94,.12);
}


/* =====================================================
   QUANTITY
===================================================== */

.quantity-section{
    display:flex;
    align-items:center;
    justify-content:space-between;

    margin-top:28px;

    padding:18px 20px;

    border:1px solid #edf0f2;

    border-radius:16px;
}

.quantity-label{
    font-size:14px;
    font-weight:700;

    color:#344054;
}

.quantity-control{
    display:flex;
    align-items:center;

    overflow:hidden;

    border:1px solid #dfe4e8;

    border-radius:11px;
}

.qty-btn{
    width:38px;
    height:38px;

    border:none;

    background:#f8faf9;

    color:var(--primary);

    cursor:pointer;

    font-size:16px;

    transition:.2s;
}

.qty-btn:hover{
    background:var(--primary-light);
}

.quantity-control input{
    width:52px;
    height:38px;

    text-align:center;

    border:none;

    outline:none;

    font-size:14px;
    font-weight:700;

    color:#344054;
}


/* =====================================================
   BUTTONS
===================================================== */

.btn-group{
    display:grid;

    grid-template-columns:
        1.3fr 1fr;

    gap:13px;

    margin-top:18px;
}

.btn{
    min-height:56px;

    display:flex;
    align-items:center;
    justify-content:center;

    gap:10px;

    border:none;

    border-radius:15px;

    cursor:pointer;

    font-size:14px;

    font-weight:700;

    transition:
        transform .3s ease,
        box-shadow .3s ease,
        background .3s ease;
}

.cart-btn{
    color:#fff;

    background:
        linear-gradient(
            135deg,
            #12a862,
            #087f45
        );

    box-shadow:
        0 12px 25px rgba(15,157,88,.22);
}

.cart-btn:hover{
    transform:translateY(-3px);

    box-shadow:
        0 18px 35px rgba(15,157,88,.32);
}

.buy-btn{
    color:#fff;

    background:#111827;

    box-shadow:
        0 10px 20px rgba(17,24,39,.12);
}

.buy-btn:hover{
    transform:translateY(-3px);

    background:#000;

    box-shadow:
        0 18px 30px rgba(17,24,39,.2);
}


/* =====================================================
   FARMER CARD
===================================================== */

.farmer-card,
.details-box{
    margin-top:25px;

    padding:30px;

    background:#fff;

    border-radius:24px;

    border:1px solid rgba(229,231,235,.8);

    box-shadow:var(--shadow-md);
}

.card-heading{
    display:flex;
    align-items:center;
    justify-content:space-between;

    padding-bottom:20px;

    border-bottom:1px solid #edf0f2;
}

.card-heading h3{
    color:#111827;

    font-size:19px;
}

.card-heading span{
    color:#8a94a6;

    font-size:12px;
}

.farmer-content{
    display:flex;

    align-items:center;

    gap:20px;

    padding-top:25px;
}

.farmer-avatar{
    width:72px;
    height:72px;

    flex-shrink:0;

    display:flex;
    align-items:center;
    justify-content:center;

    border-radius:22px;

    color:white;

    font-size:26px;
    font-weight:800;

    background:
        linear-gradient(
            135deg,
            #18b96c,
            #087f45
        );

    box-shadow:
        0 12px 25px rgba(15,157,88,.2);
}

.farmer-main{
    flex:1;
}

.farmer-main h4{
    color:#111827;

    font-size:18px;

    margin-bottom:6px;
}

.verified{
    display:inline-flex;
    align-items:center;
    gap:6px;

    color:var(--primary);

    font-size:12px;

    font-weight:700;
}

.farmer-meta{
    display:flex;

    flex-wrap:wrap;

    gap:10px 20px;

    margin-top:10px;

    color:#667085;

    font-size:13px;
}

.farmer-meta span{
    display:flex;
    align-items:center;
    gap:6px;
}

.farmer-meta i{
    color:var(--primary);
}


/* =====================================================
   DETAILS BOX
===================================================== */

.details-content{
    display:grid;

    grid-template-columns:
        repeat(2,1fr);

    gap:20px;

    margin-top:25px;
}

.detail-item{
    padding:20px;

    border-radius:16px;

    background:#f8faf9;

    border:1px solid #edf2ef;
}

.detail-item h4{
    display:flex;
    align-items:center;
    gap:9px;

    color:#344054;

    font-size:14px;

    margin-bottom:8px;
}

.detail-item h4 i{
    color:var(--primary);
}

.detail-item p{
    color:#667085;

    font-size:13px;

    line-height:1.8;
}


/* =====================================================
   TOAST
===================================================== */

.toast{
    position:fixed;

    right:25px;
    bottom:25px;

    z-index:9999;

    display:flex;
    align-items:center;
    gap:12px;

    min-width:280px;

    padding:16px 20px;

    color:#fff;

    background:#111827;

    border-radius:14px;

    box-shadow:0 20px 40px rgba(0,0,0,.2);

    transform:
        translateY(120px);

    opacity:0;

    transition:.4s;
}

.toast.show{
    transform:
        translateY(0);

    opacity:1;
}

.toast i{
    color:#4ade80;
}


/* =====================================================
   MOBILE
===================================================== */

@media(max-width:1000px){

    .nav-links{
        display:none;
    }

    .product-wrapper{
        grid-template-columns:1fr;

        gap:35px;

        padding:25px;
    }

    .product-image,
    .product-image img{
        min-height:450px;
        height:450px;
    }

    .product-title{
        font-size:40px;
    }
}

@media(max-width:650px){

    .navbar{
        padding:0 20px;
    }

    .logo{
        font-size:18px;
    }

    .user-info{
        display:none;
    }

    .breadcrumb-wrapper{
        padding:20px 15px 0;
    }

    .container{
        width:94%;

        margin-top:15px;
    }

    .product-wrapper{
        padding:16px;

        border-radius:22px;

        gap:25px;
    }

    .product-image,
    .product-image img{
        min-height:320px;
        height:320px;

        border-radius:18px;
    }

    .product-title{
        font-size:34px;
    }

    .price{
        font-size:29px;
    }

    .info-grid{
        grid-template-columns:1fr;
    }

    .quantity-section{
        align-items:flex-start;

        flex-direction:column;

        gap:15px;
    }

    .btn-group{
        grid-template-columns:1fr;
    }

    .farmer-content{
        align-items:flex-start;
    }

    .details-content{
        grid-template-columns:1fr;
    }

    .farmer-card,
    .details-box{
        padding:22px;
    }
}

</style>

</head>

<body>


<!-- =====================================================
     NAVBAR
===================================================== -->

<nav class="navbar">

    <a href="index.php" class="logo">

        <span class="logo-icon">
            <i class="fas fa-leaf"></i>
        </span>

        FarmerDirect

    </a>


    <div class="nav-links">

        <a href="index.php">
            Home
        </a>

        <a href="products.php">
            Products
        </a>

        <a href="price-trends.php">
            Price Trends
        </a>

        <a href="about.php">
            About
        </a>

    </div>


    <div class="nav-right">

        <a href="cart.php" class="cart-icon">

            <i class="fas fa-shopping-bag"></i>

        </a>


        <?php if(isset($_SESSION['user_id'])): ?>

            <div class="user-info">

                <div class="user-avatar">

                    <?php
                    echo strtoupper(
                        substr(
                            $_SESSION['user_name'] ?? 'A',
                            0,
                            1
                        )
                    );
                    ?>

                </div>

                <span>
                    <?php
                    echo htmlspecialchars(
                        $_SESSION['user_name'] ?? 'Account'
                    );
                    ?>
                </span>

            </div>

        <?php else: ?>

            <a href="login.php" class="login-btn">
                Login
            </a>

        <?php endif; ?>

    </div>

</nav>


<!-- =====================================================
     BREADCRUMB
===================================================== -->

<div class="breadcrumb-wrapper">

    <div class="breadcrumb">

        <a href="products.php">
            Products
        </a>

        <i class="fas fa-chevron-right"></i>

        <span>
            <?php
            echo htmlspecialchars(
                $product['name']
            );
            ?>
        </span>

    </div>

</div>


<!-- =====================================================
     MAIN
===================================================== -->

<div class="container">


    <!-- PRODUCT MAIN -->

    <div class="product-wrapper">


        <!-- IMAGE -->

        <div class="product-image">

            <img
                src="<?php echo htmlspecialchars($product_image); ?>"
                alt="<?php echo htmlspecialchars($product['name']); ?>"
            >

            <div class="image-overlay">

                <i class="fas fa-check-circle"></i>

                Fresh from Farm

            </div>

        </div>


        <!-- PRODUCT DETAILS -->

        <div class="product-details">


            <div class="category">

                <i class="fas fa-leaf"></i>

                <?php
                echo htmlspecialchars(
                    $product['category']
                );
                ?>

            </div>


            <h1 class="product-title">

                <?php
                echo htmlspecialchars(
                    $product['name']
                );
                ?>

            </h1>


            <div class="price">

                ৳ <?php
                echo number_format(
                    $product['price'],
                    0
                );
                ?>

                <span class="price-unit">

                    / <?php
                    echo htmlspecialchars(
                        $product['unit']
                    );
                    ?>

                </span>

            </div>


            <p class="description">

                <?php
                echo nl2br(
                    htmlspecialchars(
                        $description
                    )
                );
                ?>

            </p>


            <!-- INFO -->

            <div class="info-grid">


                <div class="info-box">

                    <div class="info-icon">
                        <i class="fas fa-box"></i>
                    </div>

                    <div class="info-text">

                        <span>
                            Available Stock
                        </span>

                        <strong>
                            <?php
                            echo number_format($stock);
                            ?>

                            <?php
                            echo htmlspecialchars(
                                $product['unit']
                            );
                            ?>
                        </strong>

                    </div>

                </div>


                <div class="info-box">

                    <div class="info-icon">
                        <i class="fas fa-calendar-check"></i>
                    </div>

                    <div class="info-text">

                        <span>
                            Harvest Date
                        </span>

                        <strong>
                            <?php
                            echo $harvest_date;
                            ?>
                        </strong>

                    </div>

                </div>


                <div class="info-box">

                    <div class="info-icon">
                        <i class="fas fa-location-dot"></i>
                    </div>

                    <div class="info-text">

                        <span>
                            Product Location
                        </span>

                        <strong>
                            <?php
                            echo htmlspecialchars(
                                $location
                            );
                            ?>
                        </strong>

                    </div>

                </div>


                <?php if(!empty($product['weight'])): ?>

                <div class="info-box">

                    <div class="info-icon">
                        <i class="fas fa-weight-hanging"></i>
                    </div>

                    <div class="info-text">

                        <span>
                            Weight
                        </span>

                        <strong>
                            <?php
                            echo htmlspecialchars(
                                $product['weight']
                            );
                            ?>
                        </strong>

                    </div>

                </div>

                <?php endif; ?>


            </div>


            <!-- STOCK STATUS -->

            <div class="stock-status">

                <?php if($stock > 0): ?>

                    <span class="stock-dot"></span>

                    <span>
                        In Stock — Ready to Order
                    </span>

                <?php else: ?>

                    <span style="color:#ef4444;">
                        <i class="fas fa-circle"></i>
                        Out of Stock
                    </span>

                <?php endif; ?>

            </div>


            <!-- QUANTITY -->

            <div class="quantity-section">

                <span class="quantity-label">

                    Select Quantity

                </span>


                <div class="quantity-control">

                    <button
                        type="button"
                        class="qty-btn"
                        onclick="changeQty(-1)"
                    >
                        <i class="fas fa-minus"></i>
                    </button>


                    <input
                        type="number"
                        id="quantity"
                        value="1"
                        min="1"
                        max="<?php echo $stock; ?>"
                    >


                    <button
                        type="button"
                        class="qty-btn"
                        onclick="changeQty(1)"
                    >
                        <i class="fas fa-plus"></i>
                    </button>

                </div>

            </div>


            <!-- BUTTONS -->

            <div class="btn-group">


                <button
                    onclick="addToCart(<?php echo $product['id']; ?>)"
                    class="btn cart-btn"
                    <?php echo $stock <= 0 ? 'disabled' : ''; ?>
                >

                    <i class="fas fa-cart-plus"></i>

                    Add to Cart

                </button>


                <button
                    onclick="buyNow(<?php echo $product['id']; ?>)"
                    class="btn buy-btn"
                    <?php echo $stock <= 0 ? 'disabled' : ''; ?>
                >

                    <i class="fas fa-bolt"></i>

                    Buy Now

                </button>


            </div>


        </div>

    </div>


    <!-- FARMER CARD -->

    <div class="farmer-card">


        <div class="card-heading">

            <h3>

                <i
                    class="fas fa-user-check"
                    style="color:#0f9d58;margin-right:8px;"
                ></i>

                Farmer Information

            </h3>

            <span>
                Direct from Farmer
            </span>

        </div>


        <div class="farmer-content">


            <div class="farmer-avatar">

                <?php
                echo $farmer_initial;
                ?>

            </div>


            <div class="farmer-main">

                <h4>

                    <?php
                    echo htmlspecialchars(
                        $farmer_name
                    );
                    ?>

                </h4>


                <?php if($is_verified): ?>

                    <span class="verified">

                        <i class="fas fa-circle-check"></i>

                        Verified Farmer

                    </span>

                <?php else: ?>

                    <span class="verified">

                        <i class="fas fa-user"></i>

                        Farmer

                    </span>

                <?php endif; ?>


                <div class="farmer-meta">


                    <span>

                        <i class="fas fa-star"></i>

                        <?php
                        echo $rating;
                        ?>
                        Rating

                    </span>


                    <span>

                        <i class="fas fa-location-dot"></i>

                        <?php
                        echo htmlspecialchars(
                            $farmer_location
                        );
                        ?>

                    </span>


                </div>

            </div>

        </div>

    </div>


    <!-- PRODUCT DETAILS -->

    <div class="details-box">


        <div class="card-heading">

            <h3>

                <i
                    class="fas fa-circle-info"
                    style="color:#0f9d58;margin-right:8px;"
                ></i>

                Product Information

            </h3>

            <span>
                Quality & Freshness
            </span>

        </div>


        <div class="details-content">


            <div class="detail-item">

                <h4>

                    <i class="fas fa-heart"></i>

                    Benefits

                </h4>

                <p>

                    <?php
                    echo nl2br(
                        htmlspecialchars(
                            $benefits
                        )
                    );
                    ?>

                </p>

            </div>


            <div class="detail-item">

                <h4>

                    <i class="fas fa-clock"></i>

                    Shelf Life

                </h4>

                <p>

                    <?php
                    echo htmlspecialchars(
                        $shelf_life
                    );
                    ?>

                </p>

            </div>


        </div>

    </div>


</div>


<!-- TOAST -->

<div id="toast" class="toast">

    <i class="fas fa-circle-check"></i>

    <span id="toastMessage">
        Product added successfully!
    </span>

</div>


<script>

/* =====================================================
   QUANTITY
===================================================== */

function changeQty(amount){

    const input =
        document.getElementById('quantity');

    let value =
        parseInt(input.value) || 1;

    const max =
        parseInt(input.max);

    value += amount;

    if(value < 1){
        value = 1;
    }

    if(max > 0 && value > max){
        value = max;
    }

    input.value = value;
}


/* =====================================================
   TOAST
===================================================== */

function showToast(message){

    const toast =
        document.getElementById('toast');

    const toastMessage =
        document.getElementById('toastMessage');

    toastMessage.innerText =
        message;

    toast.classList.add('show');

    setTimeout(function(){

        toast.classList.remove('show');

    },3000);
}


/* =====================================================
   ADD TO CART
===================================================== */

function addToCart(productId){

    const qty =
        document.getElementById('quantity').value;

    if(qty <= 0){

        showToast(
            'Please select a valid quantity.'
        );

        return;
    }


    fetch('add_to_cart.php',{

        method:'POST',

        headers:{
            'Content-Type':
            'application/x-www-form-urlencoded'
        },

        body:
        `product_id=${productId}&quantity=${qty}`

    })

    .then(response => response.text())

    .then(data => {

        showToast(data);

    })

    .catch(error => {

        showToast(
            'Something went wrong. Please try again.'
        );

    });

}


/* =====================================================
   BUY NOW
===================================================== */

function buyNow(productId){

    const qty =
        document.getElementById('quantity').value;

    if(qty <= 0){

        showToast(
            'Please select a valid quantity.'
        );

        return;
    }


    window.location.href =
        `checkout.php?product_id=${productId}&qty=${qty}`;

}

</script>


</body>

</html>

<?php

$conn->close();

?>