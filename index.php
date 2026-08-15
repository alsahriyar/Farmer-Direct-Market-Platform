<?php
session_start();

$host = "localhost";
$username = "root";
$password = "";
$dbname = "farm_db";

$conn = new mysqli($host, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Database Connection Failed: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");

// Today's Crop Prices
$prices_query = "
    SELECT id, name AS crop_name, price, image 
    FROM products 
    ORDER BY price DESC 
    LIMIT 6
";
$prices_result = $conn->query($prices_query);

// Featured Products
$products_query = "
    SELECT id, name AS product_name, price AS product_price, image AS product_image 
    FROM products 
    ORDER BY stock DESC 
    LIMIT 6
";
$products_result = $conn->query($products_query);

// Special Products
$special_query = "
    SELECT id, name AS product_name, price AS product_price, image AS product_image 
    FROM products 
    WHERE category = 'Grain' 
    ORDER BY price DESC 
    LIMIT 6
";
$special_result = $conn->query($special_query);
?>

<!DOCTYPE html>
<html lang="bn">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Farmer Direct Market | Fresh From Farm</title>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">

<link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<style>

/* =========================================================
   ROOT
========================================================= */

:root{
    --primary:#138a4b;
    --primary-dark:#096b39;
    --primary-light:#eaf8ef;

    --dark:#102018;
    --text:#26352d;
    --muted:#718078;

    --white:#ffffff;
    --bg:#f6faf7;

    --border:#e5eee8;

    --shadow-sm:0 8px 25px rgba(16,32,24,.06);
    --shadow-md:0 18px 45px rgba(16,32,24,.10);
    --shadow-lg:0 30px 80px rgba(16,32,24,.16);

    --radius:22px;
}


/* =========================================================
   RESET
========================================================= */

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
    background:var(--bg);
    color:var(--text);
    line-height:1.7;
    overflow-x:hidden;
}

a{
    text-decoration:none;
}

img{
    display:block;
    max-width:100%;
}

button{
    font-family:inherit;
}


/* =========================================================
   NAVBAR
========================================================= */

.navbar{
    position:sticky;
    top:0;
    z-index:1000;

    width:100%;

    display:flex;
    align-items:center;
    justify-content:space-between;

    padding:16px 6%;

    background:rgba(255,255,255,.94);

    backdrop-filter:blur(18px);
    -webkit-backdrop-filter:blur(18px);

    border-bottom:1px solid rgba(19,138,75,.08);

    box-shadow:0 5px 25px rgba(0,0,0,.04);
}


/* Logo */

.logo{
    display:flex;
    align-items:center;
    gap:11px;

    color:var(--dark);

    font-size:23px;
    font-weight:800;

    white-space:nowrap;
}

.logo-icon{
    width:43px;
    height:43px;

    display:flex;
    align-items:center;
    justify-content:center;

    border-radius:13px;

    color:white;

    background:linear-gradient(
        135deg,
        var(--primary),
        var(--primary-dark)
    );

    box-shadow:0 8px 20px rgba(19,138,75,.25);
}

.logo span{
    color:var(--primary);
}


/* Navigation */

.nav-links{
    display:flex;
    align-items:center;
    gap:8px;

    list-style:none;
}

.nav-links a{
    position:relative;

    display:flex;
    align-items:center;
    gap:7px;

    padding:10px 14px;

    color:#435149;

    font-size:14px;
    font-weight:600;

    border-radius:10px;

    transition:.3s;
}

.nav-links a:hover{
    color:var(--primary);
    background:var(--primary-light);
}

.nav-links a.active{
    color:var(--primary);
    background:var(--primary-light);
}


/* Navbar buttons */

.nav-buttons{
    display:flex;
    align-items:center;
    gap:10px;
}

.login-btn,
.register-btn{

    display:flex;
    align-items:center;
    gap:7px;

    padding:11px 18px;

    border-radius:11px;

    font-size:14px;
    font-weight:700;

    transition:.3s;
}

.login-btn{
    color:var(--primary);
    border:1px solid rgba(19,138,75,.3);
    background:white;
}

.login-btn:hover{
    background:var(--primary-light);
    transform:translateY(-2px);
}

.register-btn{
    color:white;
    background:linear-gradient(
        135deg,
        var(--primary),
        var(--primary-dark)
    );

    box-shadow:0 8px 20px rgba(19,138,75,.20);
}

.register-btn:hover{
    transform:translateY(-2px);
    box-shadow:0 12px 28px rgba(19,138,75,.30);
}


/* =========================================================
   HERO
========================================================= */

.hero{

    min-height:calc(100vh - 75px);

    display:flex;
    align-items:center;
    justify-content:center;

    position:relative;

    text-align:center;

    color:white;

    padding:100px 6%;

    background:
    linear-gradient(
        120deg,
        rgba(4,39,22,.88),
        rgba(10,87,46,.72),
        rgba(0,0,0,.45)
    ),
    url('https://images.unsplash.com/photo-1500937386664-56d1dfef3854?auto=format&fit=crop&w=1800&q=85')
    center/cover no-repeat;

    overflow:hidden;
}


/* Hero decorative circle */

.hero::before{
    content:"";

    position:absolute;

    width:500px;
    height:500px;

    right:-180px;
    top:-180px;

    border:1px solid rgba(255,255,255,.12);

    border-radius:50%;
}

.hero::after{
    content:"";

    position:absolute;

    width:350px;
    height:350px;

    left:-150px;
    bottom:-160px;

    border:1px solid rgba(255,255,255,.1);

    border-radius:50%;
}


.hero-content{
    position:relative;
    z-index:2;

    max-width:950px;
}


.hero-badge{

    display:inline-flex;
    align-items:center;
    gap:8px;

    padding:8px 17px;

    margin-bottom:25px;

    border-radius:50px;

    background:rgba(255,255,255,.13);

    border:1px solid rgba(255,255,255,.25);

    backdrop-filter:blur(10px);

    font-size:14px;
    font-weight:600;

    color:#f2fff6;
}


.hero h1{

    font-family:'Playfair Display',serif;

    font-size:clamp(42px,6vw,78px);

    line-height:1.12;

    margin-bottom:25px;

    letter-spacing:-1px;

    text-shadow:0 10px 40px rgba(0,0,0,.25);
}


.hero h1 span{
    color:#8df0b2;
}


.hero p{

    max-width:760px;

    margin:0 auto 38px;

    color:rgba(255,255,255,.9);

    font-size:18px;

    line-height:1.9;
}


.hero-buttons{

    display:flex;

    justify-content:center;

    gap:14px;

    flex-wrap:wrap;
}


.btn-primary,
.btn-secondary{

    display:inline-flex;

    align-items:center;
    justify-content:center;

    gap:9px;

    min-width:190px;

    padding:15px 27px;

    border-radius:12px;

    font-size:15px;

    font-weight:700;

    transition:.35s;
}


.btn-primary{

    color:white;

    background:linear-gradient(
        135deg,
        #1ca45b,
        #0b733c
    );

    box-shadow:0 12px 30px rgba(0,0,0,.2);
}

.btn-primary:hover{
    transform:translateY(-4px);
    box-shadow:0 18px 40px rgba(0,0,0,.28);
}


.btn-secondary{

    color:white;

    background:rgba(255,255,255,.12);

    border:1px solid rgba(255,255,255,.35);

    backdrop-filter:blur(10px);
}

.btn-secondary:hover{
    background:white;
    color:var(--primary);
    transform:translateY(-4px);
}


/* =========================================================
   FEATURES
========================================================= */

.features{

    position:relative;

    display:grid;

    grid-template-columns:repeat(3,1fr);

    gap:25px;

    max-width:1250px;

    margin:-70px auto 0;

    padding:0 25px;

    z-index:10;
}


.feature-card{

    background:rgba(255,255,255,.96);

    padding:35px 30px;

    border:1px solid var(--border);

    border-radius:var(--radius);

    box-shadow:var(--shadow-md);

    transition:.4s;
}


.feature-card:hover{

    transform:translateY(-10px);

    box-shadow:var(--shadow-lg);
}


.feature-icon{

    width:58px;
    height:58px;

    display:flex;
    align-items:center;
    justify-content:center;

    margin-bottom:22px;

    border-radius:16px;

    color:var(--primary);

    background:var(--primary-light);

    font-size:24px;
}


.feature-card h3{

    margin-bottom:10px;

    font-size:20px;

    color:var(--dark);
}


.feature-card p{

    color:var(--muted);

    font-size:14px;

    line-height:1.8;
}


/* =========================================================
   SECTION COMMON
========================================================= */

section{
    scroll-margin-top:90px;
}

.section{

    padding:105px 6%;
}


.section-header{

    max-width:760px;

    margin:0 auto 55px;

    text-align:center;
}


.section-tag{

    display:inline-flex;

    align-items:center;

    gap:7px;

    margin-bottom:12px;

    color:var(--primary);

    font-size:13px;

    font-weight:800;

    text-transform:uppercase;

    letter-spacing:1.5px;
}


.section-header h2{

    font-family:'Playfair Display',serif;

    color:var(--dark);

    font-size:clamp(32px,4vw,48px);

    line-height:1.25;

    margin-bottom:15px;
}


.section-header p{

    color:var(--muted);

    font-size:15px;

    line-height:1.8;
}


/* =========================================================
   PRICE SECTION
========================================================= */

.prices{

    background:

    radial-gradient(
        circle at top right,
        rgba(19,138,75,.07),
        transparent 30%
    ),

    #f3f9f5;
}


.price-grid{

    max-width:1250px;

    margin:auto;

    display:grid;

    grid-template-columns:repeat(3,1fr);

    gap:22px;
}


.price-card{

    position:relative;

    display:flex;

    align-items:center;

    gap:18px;

    padding:25px;

    background:white;

    border:1px solid var(--border);

    border-radius:18px;

    cursor:pointer;

    overflow:hidden;

    box-shadow:var(--shadow-sm);

    transition:.4s;
}


.price-card::after{

    content:"\f35d";

    font-family:"Font Awesome 6 Free";

    font-weight:900;

    position:absolute;

    right:22px;

    top:22px;

    color:#b9c9bf;

    transition:.3s;
}


.price-card:hover{

    transform:translateY(-7px);

    border-color:rgba(19,138,75,.25);

    box-shadow:var(--shadow-md);
}


.price-card:hover::after{
    color:var(--primary);
    transform:translate(3px,-3px);
}


.price-image{

    width:70px;
    height:70px;

    flex-shrink:0;

    border-radius:15px;

    overflow:hidden;

    background:var(--primary-light);
}


.price-image img{

    width:100%;
    height:100%;

    object-fit:cover;

    transition:.5s;
}


.price-card:hover .price-image img{
    transform:scale(1.1);
}


.price-info h3{

    color:var(--dark);

    font-size:17px;

    margin-bottom:4px;
}


.price-info p{

    color:var(--primary);

    font-size:20px;

    font-weight:800;
}


.price-info span{

    color:var(--muted);

    font-size:12px;

    font-weight:500;
}


/* =========================================================
   PRODUCT SECTION
========================================================= */

.products{
    background:white;
}


.product-grid,
.special-grid{

    max-width:1250px;

    margin:auto;

    display:grid;

    grid-template-columns:repeat(3,1fr);

    gap:25px;
}


.product-card,
.special-card{

    position:relative;

    background:white;

    border:1px solid var(--border);

    border-radius:20px;

    overflow:hidden;

    box-shadow:var(--shadow-sm);

    transition:.45s;
}


.product-card:hover,
.special-card:hover{

    transform:translateY(-10px);

    box-shadow:var(--shadow-lg);
}


.product-image{

    position:relative;

    height:250px;

    overflow:hidden;
}


.product-image img{

    width:100%;
    height:100%;

    object-fit:cover;

    transition:.6s;
}


.product-card:hover img,
.special-card:hover img{

    transform:scale(1.08);
}


.product-label{

    position:absolute;

    top:15px;
    left:15px;

    padding:6px 12px;

    color:white;

    background:rgba(19,138,75,.9);

    border-radius:30px;

    font-size:11px;

    font-weight:700;
}


.product-content{

    padding:23px;
}


.product-content h3{

    color:var(--dark);

    font-size:18px;

    margin-bottom:8px;
}


.product-price{

    display:flex;

    justify-content:space-between;

    align-items:center;
}


.product-price strong{

    color:var(--primary);

    font-size:21px;
}


.view-product{

    display:inline-flex;

    align-items:center;

    gap:6px;

    color:var(--muted);

    font-size:13px;

    font-weight:600;

    transition:.3s;
}


.product-card:hover .view-product{
    color:var(--primary);
}


/* =========================================================
   SPECIAL PRODUCTS
========================================================= */

.special-products{

    background:

    linear-gradient(
        180deg,
        #f7fbf8,
        #eef7f0
    );
}


/* =========================================================
   ABOUT
========================================================= */

.about{

    position:relative;

    padding:110px 8%;

    color:white;

    text-align:center;

    overflow:hidden;

    background:

    linear-gradient(
        135deg,
        #0b713c,
        #064d2a
    );
}


.about::before{

    content:"";

    position:absolute;

    width:450px;
    height:450px;

    right:-180px;
    top:-180px;

    border:1px solid rgba(255,255,255,.12);

    border-radius:50%;
}


.about-content{

    position:relative;

    max-width:850px;

    margin:auto;

    z-index:2;
}


.about h2{

    font-family:'Playfair Display',serif;

    font-size:clamp(32px,4vw,50px);

    margin-bottom:22px;
}


.about p{

    color:rgba(255,255,255,.86);

    font-size:16px;

    line-height:1.9;
}


/* =========================================================
   FOOTER
========================================================= */

footer{

    padding:55px 6% 25px;

    color:#b5c1ba;

    background:#0c1510;
}


.footer-content{

    max-width:1250px;

    margin:auto;

    display:grid;

    grid-template-columns:2fr 1fr 1fr;

    gap:50px;

    padding-bottom:40px;

    border-bottom:1px solid rgba(255,255,255,.08);
}


.footer-brand h3{

    color:white;

    font-size:22px;

    margin-bottom:12px;
}


.footer-brand p{

    max-width:450px;

    font-size:14px;

    line-height:1.8;
}


.footer-column h4{

    color:white;

    margin-bottom:15px;
}


.footer-column a{

    display:block;

    margin-bottom:8px;

    color:#aebbb3;

    font-size:14px;

    transition:.3s;
}


.footer-column a:hover{
    color:#7de5a5;
    transform:translateX(4px);
}


.footer-bottom{

    padding-top:25px;

    text-align:center;

    font-size:13px;

    color:#7f8c84;
}


/* =========================================================
   RESPONSIVE
========================================================= */

@media(max-width:1050px){

    .nav-links{
        gap:2px;
    }

    .nav-links a{
        padding:9px;
    }

    .features{
        grid-template-columns:1fr;
        max-width:700px;
    }

    .price-grid,
    .product-grid,
    .special-grid{
        grid-template-columns:repeat(2,1fr);
    }

    .footer-content{
        grid-template-columns:1fr 1fr;
    }
}


@media(max-width:850px){

    .navbar{
        flex-wrap:wrap;
        gap:15px;
    }

    .nav-links{
        order:3;
        width:100%;

        justify-content:center;

        overflow-x:auto;
    }

    .nav-links a{
        white-space:nowrap;
    }

    .hero{
        min-height:80vh;
    }
}


@media(max-width:650px){

    .navbar{
        padding:13px 5%;
    }

    .logo{
        font-size:20px;
    }

    .nav-buttons{
        gap:5px;
    }

    .login-btn,
    .register-btn{
        padding:9px 12px;
        font-size:12px;
    }

    .nav-links{
        justify-content:flex-start;
    }

    .hero{
        padding:80px 5%;
    }

    .hero p{
        font-size:15px;
    }

    .hero-buttons{
        flex-direction:column;
    }

    .btn-primary,
    .btn-secondary{
        width:100%;
    }

    .section{
        padding:75px 5%;
    }

    .price-grid,
    .product-grid,
    .special-grid{
        grid-template-columns:1fr;
    }

    .footer-content{
        grid-template-columns:1fr;
        gap:30px;
    }
}

</style>

</head>

<body>


<!-- =========================================================
     NAVBAR
========================================================= -->

<nav class="navbar">

    <a href="index.php" class="logo">

        <span class="logo-icon">
            <i class="fas fa-seedling"></i>
        </span>

        Farmer<span>Direct</span>

    </a>


    <ul class="nav-links">

        <li>
            <a href="index.php" class="active">
                <i class="fas fa-house"></i>
                হোম
            </a>
        </li>

        <li>
            <a href="products.php">
                <i class="fas fa-store"></i>
                পণ্যসমূহ
            </a>
        </li>

        <li>
            <a href="crop-price-trends.php">
                <i class="fas fa-chart-line"></i>
                ফসলের দাম
            </a>
        </li>

        <li>
            <a href="about.php">
                <i class="fas fa-circle-info"></i>
                আমাদের সম্পর্কে
            </a>
        </li>

        <li>
            <a href="contact.php">
                <i class="fas fa-envelope"></i>
                যোগাযোগ
            </a>
        </li>

    </ul>


    <div class="nav-buttons">

        <a href="login.php" class="login-btn">
            <i class="fas fa-right-to-bracket"></i>
            লগইন
        </a>

        <a href="register.php" class="register-btn">
            <i class="fas fa-user-plus"></i>
            রেজিস্টার
        </a>

    </div>

</nav>


<!-- =========================================================
     HERO
========================================================= -->

<section class="hero">

    <div class="hero-content">

        <div class="hero-badge">
            <i class="fas fa-leaf"></i>
            Fresh From Farm • Direct To You
        </div>

        <h1>
            কৃষকের কাছ থেকে
            <span>সরাসরি আপনার ঘরে</span>
        </h1>

        <p>
            Farmer Direct Market-এর মাধ্যমে সরাসরি কৃষকের কাছ থেকে
            তাজা কৃষিজাত পণ্য কিনুন। কৃষক পান ন্যায্য মূল্য,
            আর ক্রেতা পান মানসম্মত ও সতেজ পণ্য।
        </p>

        <div class="hero-buttons">

            <a href="products.php" class="btn-primary">
                <i class="fas fa-basket-shopping"></i>
                পণ্য দেখুন
            </a>

            <a href="crop-price-trends.php" class="btn-secondary">
                <i class="fas fa-chart-line"></i>
                দামের প্রবণতা দেখুন
            </a>

        </div>

    </div>

</section>


<!-- =========================================================
     FEATURES
========================================================= -->

<section class="features">

    <div class="feature-card">

        <div class="feature-icon">
            <i class="fas fa-handshake"></i>
        </div>

        <h3>মধ্যস্থতাকারী ছাড়াই</h3>

        <p>
            কৃষক ও ক্রেতার মধ্যে সরাসরি বাণিজ্যের মাধ্যমে
            কৃষক পান ন্যায্য মূল্য এবং ক্রেতা পান সঠিক দামে
            তাজা কৃষিপণ্য।
        </p>

    </div>


    <div class="feature-card">

        <div class="feature-icon">
            <i class="fas fa-chart-line"></i>
        </div>

        <h3>রিয়েল-টাইম বাজার তথ্য</h3>

        <p>
            ফসলের বর্তমান মূল্য এবং দামের পরিবর্তনের তথ্য
            দেখে সহজেই বাজার পরিস্থিতি সম্পর্কে ধারণা নিন।
        </p>

    </div>


    <div class="feature-card">

        <div class="feature-icon">
            <i class="fas fa-seedling"></i>
        </div>

        <h3>তাজা ও মানসম্মত পণ্য</h3>

        <p>
            সরাসরি কৃষকের খামার থেকে সংগৃহীত তাজা,
            মানসম্মত এবং স্বাস্থ্যকর কৃষিপণ্য আপনার কাছে পৌঁছে দিন।
        </p>

    </div>

</section>


<!-- =========================================================
     TODAY'S PRICES
========================================================= -->

<section class="section prices" id="prices">

    <div class="section-header">

        <div class="section-tag">
            <i class="fas fa-tags"></i>
            Market Update
        </div>

        <h2>আজকের ফসলের দাম</h2>

        <p>
            সর্বশেষ আপডেট করা কৃষিপণ্যের বাজারমূল্য দেখুন।
            যেকোনো পণ্যের দামের বিস্তারিত তথ্য দেখতে
            কার্ডে ক্লিক করুন।
        </p>

    </div>


    <div class="price-grid">

        <?php

        if ($prices_result && $prices_result->num_rows > 0):

            while($row = $prices_result->fetch_assoc()):

                $crop_name = htmlspecialchars($row['crop_name']);
                $crop_id = (int)$row['id'];
                $crop_image = htmlspecialchars($row['image']);

        ?>

        <!-- PRICE CARD CLICK -> product_price.php?id= -->
        <div
            class="price-card"
            onclick="window.location.href='crop-price-trends.php?id=<?php echo $crop_id; ?>'"
        >

            <div class="price-image">

                <?php if(!empty($crop_image)): ?>

                    <img
                        src="<?php echo $crop_image; ?>"
                        alt="<?php echo $crop_name; ?>"
                    >

                <?php else: ?>

                    <img
                        src="https://images.unsplash.com/photo-1542838132-92c53300491e?auto=format&fit=crop&w=500&q=80"
                        alt="<?php echo $crop_name; ?>"
                    >

                <?php endif; ?>

            </div>


            <div class="price-info">

                <h3>
                    <?php echo $crop_name; ?>
                </h3>

                <p>
                    ৳ <?php echo number_format($row['price'], 0); ?>

                    <span>/ কেজি</span>
                </p>

            </div>

        </div>

        <?php

            endwhile;

        else:

        ?>

            <p style="grid-column:1/-1;text-align:center;color:#718078;">
                আজকে কোনো দামের আপডেট নেই।
            </p>

        <?php endif; ?>

    </div>

</section>


<!-- =========================================================
     FEATURED PRODUCTS
========================================================= -->

<section class="section products">

    <div class="section-header">

        <div class="section-tag">
            <i class="fas fa-star"></i>
            Featured Collection
        </div>

        <h2>ফিচার্ড পণ্যসমূহ</h2>

        <p>
            আমাদের প্ল্যাটফর্মের জনপ্রিয় ও মানসম্মত কৃষিপণ্য।
            সরাসরি কৃষকের কাছ থেকে সংগ্রহ করা হয়েছে।
        </p>

    </div>


    <div class="product-grid">

        <?php

        if ($products_result && $products_result->num_rows > 0):

            while($row = $products_result->fetch_assoc()):

        ?>

        <div class="product-card">

            <div class="product-image">

                <img
                    src="<?php echo htmlspecialchars($row['product_image']); ?>"
                    alt="<?php echo htmlspecialchars($row['product_name']); ?>"
                >

                <span class="product-label">
                    <i class="fas fa-leaf"></i>
                    Fresh
                </span>

            </div>


            <div class="product-content">

                <h3>
                    <?php echo htmlspecialchars($row['product_name']); ?>
                </h3>

                <div class="product-price">

                    <strong>
                        ৳ <?php echo number_format($row['product_price'], 0); ?>
                    </strong>

                    <a
                        href="product-details.php?id=<?php echo (int)$row['id']; ?>"
                        class="view-product"
                    >
                        Details
                        <i class="fas fa-arrow-right"></i>
                    </a>

                </div>

            </div>

        </div>

        <?php

            endwhile;

        else:

        ?>

            <p style="grid-column:1/-1;text-align:center;">
                কোনো ফিচার্ড পণ্য পাওয়া যায়নি।
            </p>

        <?php endif; ?>

    </div>

</section>


<!-- =========================================================
     SPECIAL PRODUCTS
========================================================= -->

<section class="section special-products">

    <div class="section-header">

        <div class="section-tag">
            <i class="fas fa-fire"></i>
            Special Collection
        </div>

        <h2>বিশেষ অফার পণ্য</h2>

        <p>
            সীমিত সময়ের জন্য বিশেষ দামে উপলব্ধ
            উন্নতমানের কৃষিপণ্যসমূহ।
        </p>

    </div>


    <div class="special-grid">

        <?php

        if ($special_result && $special_result->num_rows > 0):

            while($row = $special_result->fetch_assoc()):

        ?>

        <div class="special-card">

            <div class="product-image">

                <img
                    src="<?php echo htmlspecialchars($row['product_image']); ?>"
                    alt="<?php echo htmlspecialchars($row['product_name']); ?>"
                >

                <span class="product-label">
                    <i class="fas fa-bolt"></i>
                    Special
                </span>

            </div>


            <div class="product-content">

                <h3>
                    <?php echo htmlspecialchars($row['product_name']); ?>
                </h3>

                <div class="product-price">

                    <strong>
                        ৳ <?php echo number_format($row['product_price'], 0); ?>
                    </strong>

                    <a
                        href="product-details.php?id=<?php echo (int)$row['id']; ?>"
                        class="view-product"
                    >
                        View
                        <i class="fas fa-arrow-right"></i>
                    </a>

                </div>

            </div>

        </div>

        <?php

            endwhile;

        else:

        ?>

            <p style="grid-column:1/-1;text-align:center;">
                বিশেষ পণ্য এখনো যোগ করা হয়নি।
            </p>

        <?php endif; ?>

    </div>

</section>


<!-- =========================================================
     ABOUT
========================================================= -->

<section class="about">

    <div class="about-content">

        <div class="section-tag" style="color:#9af0ba;">
            <i class="fas fa-heart"></i>
            Why Farmer Direct
        </div>

        <h2>
            কেন Farmer Direct বেছে নেবেন?
        </h2>

        <p>
            Farmer Direct Market কৃষকদের সরাসরি ক্রেতাদের সাথে যুক্ত করে।
            এর মাধ্যমে কৃষকরা পান ন্যায্য মূল্য এবং ক্রেতারা পান
            খেত থেকে সরাসরি আসা তাজা ও মানসম্মত কৃষিপণ্য।
            স্বচ্ছ মূল্য নির্ধারণ, বাজারদরের তথ্য এবং সহজ অনলাইন
            কেনাকাটার মাধ্যমে আমরা একটি শক্তিশালী ও আধুনিক
            কৃষি ইকোসিস্টেম গড়ে তুলতে কাজ করছি।
        </p>

    </div>

</section>


<!-- =========================================================
     FOOTER
========================================================= -->

<footer>

    <div class="footer-content">

        <div class="footer-brand">

            <h3>
                🌾 FarmerDirect
            </h3>

            <p>
                কৃষক ও ক্রেতার মধ্যে সরাসরি সংযোগ তৈরি করে
                বাংলাদেশের কৃষি বাজারকে আরও স্বচ্ছ,
                সহজ ও আধুনিক করে তোলাই আমাদের লক্ষ্য।
            </p>

        </div>


        <div class="footer-column">

            <h4>Quick Links</h4>

            <a href="index.php">হোম</a>

            <a href="products.php">পণ্যসমূহ</a>

            <a href="crop-price-trends.php">ফসলের দাম</a>

        </div>


        <div class="footer-column">

            <h4>Support</h4>

            <a href="about.php">আমাদের সম্পর্কে</a>

            <a href="contact.php">যোগাযোগ</a>

            <a href="login.php">লগইন</a>

        </div>

    </div>


    <div class="footer-bottom">

        © 2026 Farmer Direct Market Platform
        <br>
        All Rights Reserved.

    </div>

</footer>


</body>

</html>

<?php
$conn->close();
?>