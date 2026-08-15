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

/* =========================
   SETTINGS
========================= */

$products_per_page = 12;

$page = isset($_GET['page']) && is_numeric($_GET['page'])
    ? max(1, (int)$_GET['page'])
    : 1;

$offset = ($page - 1) * $products_per_page;

$search = isset($_GET['search'])
    ? trim($_GET['search'])
    : '';

$category = isset($_GET['category'])
    ? trim($_GET['category'])
    : 'All';

$min_price = isset($_GET['min_price']) && $_GET['min_price'] !== ''
    ? (int)$_GET['min_price']
    : '';

$max_price = isset($_GET['max_price']) && $_GET['max_price'] !== ''
    ? (int)$_GET['max_price']
    : '';

$sort = isset($_GET['sort'])
    ? $_GET['sort']
    : 'newest';


/* =========================
   SORTING
========================= */

$order_by = "p.created_at DESC, p.id DESC";

if ($sort === 'price_low') {
    $order_by = "p.price ASC";
}

if ($sort === 'price_high') {
    $order_by = "p.price DESC";
}

if ($sort === 'name_asc') {
    $order_by = "p.name ASC";
}


/* =========================
   COUNT QUERY
========================= */

$count_sql = "
    SELECT COUNT(*) AS total
    FROM products p
    WHERE 1=1
";

$params = [];
$types = "";

if ($search !== '') {

    $count_sql .= "
        AND (
            p.name LIKE ?
            OR p.description LIKE ?
        )
    ";

    $search_param = "%{$search}%";

    $params[] = $search_param;
    $params[] = $search_param;

    $types .= "ss";
}

if ($category !== 'All') {

    $count_sql .= " AND p.category = ?";

    $params[] = $category;

    $types .= "s";
}

if ($min_price !== '') {

    $count_sql .= " AND p.price >= ?";

    $params[] = $min_price;

    $types .= "i";
}

if ($max_price !== '') {

    $count_sql .= " AND p.price <= ?";

    $params[] = $max_price;

    $types .= "i";
}


$stmt_count = $conn->prepare($count_sql);

if (!$stmt_count) {
    die("Count Query Error: " . $conn->error);
}

if (!empty($params)) {
    $stmt_count->bind_param($types, ...$params);
}

$stmt_count->execute();

$count_result = $stmt_count->get_result();

$total = (int)$count_result->fetch_assoc()['total'];

$total_pages = max(1, ceil($total / $products_per_page));

$stmt_count->close();


/* =========================
   MAIN PRODUCT QUERY
========================= */

$sql = "
    SELECT
        p.*,
        f.name AS farmer_name
    FROM products p
    LEFT JOIN farmers f
        ON p.farmer_id = f.id
    WHERE 1=1
";

if ($search !== '') {
    $sql .= "
        AND (
            p.name LIKE ?
            OR p.description LIKE ?
        )
    ";
}

if ($category !== 'All') {
    $sql .= " AND p.category = ?";
}

if ($min_price !== '') {
    $sql .= " AND p.price >= ?";
}

if ($max_price !== '') {
    $sql .= " AND p.price <= ?";
}

$sql .= "
    ORDER BY $order_by
    LIMIT ? OFFSET ?
";


$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("Product Query Error: " . $conn->error);
}


$full_params = $params;

$full_params[] = $products_per_page;
$full_params[] = $offset;

$full_types = $types . "ii";

$stmt->bind_param(
    $full_types,
    ...$full_params
);

$stmt->execute();

$result = $stmt->get_result();

?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
content="width=device-width, initial-scale=1.0">

<title>
Fresh Harvest | Farmer Direct Market
</title>

<link
href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Playfair+Display:wght@600;700&display=swap"
rel="stylesheet">

<link
rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">


<style>

/* =========================
   ROOT
========================= */

:root{

    --primary:#0f9d58;

    --primary-dark:#087f46;

    --primary-light:#e9f8f0;

    --dark:#10231a;

    --text:#25332c;

    --muted:#718078;

    --border:#e7ece9;

    --white:#ffffff;

    --bg:#f5f8f6;

    --shadow:
        0 15px 45px rgba(16,35,26,.08);

    --shadow-hover:
        0 25px 65px rgba(15,157,88,.18);

}


/* =========================
   RESET
========================= */

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
            rgba(15,157,88,.06),
            transparent 35%
        ),

        var(--bg);

    color:var(--text);

    line-height:1.6;

}


/* =========================
   TOP NAVBAR
========================= */

.navbar{

    height:76px;

    background:rgba(255,255,255,.94);

    backdrop-filter:blur(18px);

    border-bottom:1px solid rgba(15,157,88,.08);

    display:flex;

    align-items:center;

    justify-content:space-between;

    padding:0 6%;

    position:sticky;

    top:0;

    z-index:100;

}


.brand{

    display:flex;

    align-items:center;

    gap:12px;

    text-decoration:none;

    color:var(--dark);

}


.brand-icon{

    width:46px;

    height:46px;

    border-radius:14px;

    background:

        linear-gradient(
            135deg,
            var(--primary),
            var(--primary-dark)
        );

    display:flex;

    align-items:center;

    justify-content:center;

    color:white;

    font-size:21px;

    box-shadow:
        0 8px 22px rgba(15,157,88,.25);

}


.brand-text strong{

    display:block;

    font-size:17px;

    font-weight:800;

}


.brand-text span{

    display:block;

    font-size:11px;

    color:var(--muted);

    margin-top:-3px;

}


.nav-actions{

    display:flex;

    align-items:center;

    gap:12px;

}


.nav-btn{

    width:44px;

    height:44px;

    border-radius:13px;

    display:flex;

    align-items:center;

    justify-content:center;

    color:var(--dark);

    background:#f7faf8;

    border:1px solid var(--border);

    text-decoration:none;

    transition:.3s;

}


.nav-btn:hover{

    color:white;

    background:var(--primary);

    border-color:var(--primary);

    transform:translateY(-2px);

}


/* =========================
   HERO
========================= */

.hero{

    min-height:500px;

    padding:100px 6% 150px;

    position:relative;

    overflow:hidden;

    color:white;

    text-align:center;

    background:

        linear-gradient(
            135deg,
            rgba(7,70,40,.94),
            rgba(15,157,88,.78)
        ),

        url('https://images.unsplash.com/photo-1500937386664-56d1dfef3854?auto=format&fit=crop&w=2000&q=85')
        center/cover;

}


.hero::before{

    content:"";

    position:absolute;

    width:500px;

    height:500px;

    border-radius:50%;

    background:rgba(255,255,255,.06);

    top:-250px;

    right:-100px;

}


.hero-content{

    position:relative;

    z-index:2;

    max-width:850px;

    margin:auto;

}


.hero-badge{

    display:inline-flex;

    align-items:center;

    gap:8px;

    padding:8px 17px;

    border-radius:50px;

    background:rgba(255,255,255,.13);

    border:1px solid rgba(255,255,255,.2);

    backdrop-filter:blur(10px);

    font-size:13px;

    font-weight:600;

    margin-bottom:22px;

}


.hero h1{

    font-family:'Playfair Display',serif;

    font-size:clamp(42px,6vw,76px);

    line-height:1.1;

    margin-bottom:20px;

    letter-spacing:-1px;

}


.hero h1 span{

    color:#b8f5cf;

}


.hero p{

    max-width:680px;

    margin:auto;

    font-size:18px;

    color:rgba(255,255,255,.88);

}


/* =========================
   SEARCH PANEL
========================= */

.search-section{

    max-width:1400px;

    margin:-75px auto 70px;

    padding:0 25px;

    position:relative;

    z-index:20;

}


.search-panel{

    background:rgba(255,255,255,.96);

    backdrop-filter:blur(20px);

    border:1px solid rgba(255,255,255,.8);

    border-radius:26px;

    padding:30px;

    box-shadow:

        0 25px 70px rgba(15,40,25,.13);

}


.search-heading{

    display:flex;

    align-items:center;

    gap:12px;

    margin-bottom:22px;

}


.search-heading-icon{

    width:42px;

    height:42px;

    border-radius:12px;

    background:var(--primary-light);

    color:var(--primary);

    display:flex;

    align-items:center;

    justify-content:center;

}


.search-heading h3{

    font-size:17px;

    font-weight:700;

}


.search-heading p{

    font-size:12px;

    color:var(--muted);

}


.search-form{

    display:grid;

    grid-template-columns:
        2fr
        1.1fr
        1.1fr
        1.5fr
        auto;

    gap:13px;

}


.input-wrap{

    position:relative;

}


.input-wrap i{

    position:absolute;

    left:16px;

    top:50%;

    transform:translateY(-50%);

    color:#94a39b;

    font-size:14px;

}


.search-form input,

.search-form select{

    width:100%;

    height:54px;

    border:1px solid var(--border);

    background:#fbfcfb;

    border-radius:14px;

    padding:0 16px;

    font-family:inherit;

    color:var(--text);

    outline:none;

    transition:.3s;

}


.input-wrap input{

    padding-left:44px;

}


.search-form input:focus,

.search-form select:focus{

    background:white;

    border-color:var(--primary);

    box-shadow:
        0 0 0 4px rgba(15,157,88,.1);

}


.price-group{

    display:grid;

    grid-template-columns:1fr 1fr;

    gap:10px;

}


.search-btn{

    height:54px;

    border:none;

    border-radius:14px;

    padding:0 28px;

    color:white;

    background:

        linear-gradient(
            135deg,
            var(--primary),
            var(--primary-dark)
        );

    font-family:inherit;

    font-weight:700;

    cursor:pointer;

    display:flex;

    align-items:center;

    justify-content:center;

    gap:9px;

    transition:.3s;

}


.search-btn:hover{

    transform:translateY(-3px);

    box-shadow:
        0 12px 25px rgba(15,157,88,.25);

}


/* =========================
   PRODUCTS AREA
========================= */

.products{

    max-width:1480px;

    margin:auto;

    padding:0 30px 100px;

}


.section-header{

    display:flex;

    justify-content:space-between;

    align-items:flex-end;

    margin-bottom:35px;

}


.section-title small{

    color:var(--primary);

    text-transform:uppercase;

    letter-spacing:2px;

    font-size:11px;

    font-weight:800;

}


.section-title h2{

    font-family:'Playfair Display',serif;

    font-size:42px;

    color:var(--dark);

    line-height:1.2;

    margin-top:5px;

}


.result-count{

    background:white;

    border:1px solid var(--border);

    padding:10px 16px;

    border-radius:12px;

    color:var(--muted);

    font-size:13px;

    font-weight:600;

}


/* =========================
   PRODUCT GRID
========================= */

.products-grid{

    display:grid;

    grid-template-columns:
        repeat(4,1fr);

    gap:24px;

}


.product-card{

    background:white;

    border:1px solid var(--border);

    border-radius:22px;

    overflow:hidden;

    position:relative;

    display:flex;

    flex-direction:column;

    transition:.4s cubic-bezier(.2,.8,.2,1);

}


.product-card:hover{

    transform:translateY(-9px);

    border-color:rgba(15,157,88,.2);

    box-shadow:var(--shadow-hover);

}


.image-container{

    height:245px;

    overflow:hidden;

    position:relative;

    background:#edf3ef;

}


.product-card img{

    width:100%;

    height:100%;

    object-fit:cover;

    transition:.6s;

}


.product-card:hover img{

    transform:scale(1.07);

}


.image-overlay{

    position:absolute;

    inset:0;

    background:
        linear-gradient(
            to top,
            rgba(0,0,0,.25),
            transparent 45%
        );

    pointer-events:none;

}


.organic-badge{

    position:absolute;

    top:15px;

    left:15px;

    background:rgba(255,255,255,.94);

    color:var(--primary-dark);

    padding:7px 12px;

    border-radius:50px;

    font-size:11px;

    font-weight:800;

    display:flex;

    align-items:center;

    gap:5px;

    box-shadow:
        0 5px 15px rgba(0,0,0,.1);

}


.product-category{

    color:var(--primary);

    font-size:11px;

    text-transform:uppercase;

    letter-spacing:1.2px;

    font-weight:800;

    margin-bottom:8px;

}


.product-info{

    padding:22px;

    display:flex;

    flex-direction:column;

    flex:1;

}


.product-title{

    font-size:18px;

    font-weight:700;

    color:var(--dark);

    margin-bottom:8px;

}


.price-row{

    display:flex;

    align-items:baseline;

    gap:6px;

    margin-bottom:17px;

}


.price{

    font-size:25px;

    font-weight:800;

    color:var(--dark);

}


.unit{

    color:var(--muted);

    font-size:12px;

}


.farmer-info{

    display:flex;

    align-items:center;

    gap:9px;

    padding-top:15px;

    border-top:1px solid #eef2ef;

    margin-top:auto;

    color:var(--muted);

    font-size:12px;

}


.farmer-avatar{

    width:30px;

    height:30px;

    border-radius:50%;

    display:flex;

    align-items:center;

    justify-content:center;

    background:var(--primary-light);

    color:var(--primary);

    font-size:12px;

}


.btn-group{

    display:grid;

    grid-template-columns:1fr 1.3fr;

    gap:9px;

    margin-top:18px;

}


.btn{

    height:45px;

    border-radius:12px;

    display:flex;

    align-items:center;

    justify-content:center;

    gap:7px;

    font-family:inherit;

    font-size:12px;

    font-weight:700;

    cursor:pointer;

    text-decoration:none;

    transition:.3s;

}


.details-btn{

    background:#f7faf8;

    color:var(--text);

    border:1px solid var(--border);

}


.details-btn:hover{

    border-color:var(--primary);

    color:var(--primary);

}


.cart-btn{

    border:none;

    color:white;

    background:

        linear-gradient(
            135deg,
            var(--primary),
            var(--primary-dark)
        );

}


.cart-btn:hover{

    transform:translateY(-2px);

    box-shadow:
        0 9px 20px rgba(15,157,88,.25);

}


/* =========================
   PAGINATION
========================= */

.pagination{

    display:flex;

    justify-content:center;

    align-items:center;

    gap:8px;

    margin-top:60px;

}


.pagination a,

.pagination span{

    width:44px;

    height:44px;

    display:flex;

    align-items:center;

    justify-content:center;

    border-radius:12px;

    border:1px solid var(--border);

    background:white;

    color:var(--text);

    text-decoration:none;

    font-size:13px;

    font-weight:700;

    transition:.3s;

}


.pagination a:hover{

    background:var(--primary);

    color:white;

    border-color:var(--primary);

    transform:translateY(-2px);

}


.pagination .active{

    background:var(--primary);

    color:white;

    border-color:var(--primary);

    box-shadow:
        0 8px 18px rgba(15,157,88,.22);

}


/* =========================
   EMPTY STATE
========================= */

.no-products{

    grid-column:1/-1;

    text-align:center;

    padding:100px 20px;

    background:white;

    border-radius:22px;

    border:1px solid var(--border);

}


.no-products i{

    font-size:50px;

    color:#b8c5bd;

    margin-bottom:20px;

}


.no-products h3{

    font-size:22px;

    margin-bottom:7px;

}


.no-products p{

    color:var(--muted);

}


/* =========================
   TOAST
========================= */

.toast{

    position:fixed;

    right:25px;

    bottom:25px;

    min-width:280px;

    max-width:360px;

    padding:16px 20px;

    border-radius:15px;

    background:#10231a;

    color:white;

    display:flex;

    align-items:center;

    gap:12px;

    box-shadow:
        0 20px 45px rgba(0,0,0,.2);

    transform:translateY(130px);

    opacity:0;

    transition:.4s;

    z-index:999;

}


.toast.show{

    transform:translateY(0);

    opacity:1;

}


.toast-icon{

    width:35px;

    height:35px;

    border-radius:10px;

    background:var(--primary);

    display:flex;

    align-items:center;

    justify-content:center;

}


/* =========================
   RESPONSIVE
========================= */

@media(max-width:1200px){

    .products-grid{

        grid-template-columns:
            repeat(3,1fr);

    }

    .search-form{

        grid-template-columns:
            1fr 1fr 1fr;

    }

    .search-btn{

        width:100%;

    }

}


@media(max-width:900px){

    .products-grid{

        grid-template-columns:
            repeat(2,1fr);

    }

    .hero{

        padding-top:80px;

    }

    .search-form{

        grid-template-columns:
            1fr 1fr;

    }

}


@media(max-width:600px){

    .navbar{

        padding:0 20px;

    }

    .brand-text{

        display:none;

    }

    .hero{

        min-height:440px;

        padding:
            75px 20px 130px;

    }

    .hero p{

        font-size:15px;

    }

    .search-section{

        margin-top:-65px;

        padding:0 15px;

    }

    .search-panel{

        padding:22px;

    }

    .search-form{

        grid-template-columns:1fr;

    }

    .price-group{

        grid-template-columns:1fr 1fr;

    }

    .products{

        padding:
            0 15px 70px;

    }

    .section-header{

        align-items:flex-start;

        flex-direction:column;

        gap:15px;

    }

    .section-title h2{

        font-size:32px;

    }

    .products-grid{

        grid-template-columns:1fr;

    }

    .image-container{

        height:240px;

    }

}

</style>

</head>


<body>


<!-- =========================
     NAVBAR
========================= -->

<nav class="navbar">

    <a href="buyer-dashboard.php" class="brand">

        <div class="brand-icon">
            <i class="fas fa-leaf"></i>
        </div>

        <div class="brand-text">

            <strong>Farmer Direct Market</strong>

            <span>Fresh • Fair • Direct</span>

        </div>

    </a>


    <div class="nav-actions">

        <a href="cart.php"
           class="nav-btn"
           title="Shopping Cart">

            <i class="fas fa-shopping-bag"></i>

        </a>

        <a href="wishlist.php"
           class="nav-btn"
           title="Wishlist">

            <i class="far fa-heart"></i>

        </a>

    </div>

</nav>


<!-- =========================
     HERO
========================= -->

<section class="hero">

    <div class="hero-content">

        <div class="hero-badge">

            <i class="fas fa-leaf"></i>

            Farm Fresh Marketplace

        </div>


        <h1>

            Fresh From

            <span>Local Farmers</span>

        </h1>


        <p>

            Discover quality farm products directly from trusted farmers.
            Fresh produce, fair prices and a better way to shop.

        </p>

    </div>

</section>


<!-- =========================
     SEARCH PANEL
========================= -->

<section class="search-section">

<div class="search-panel">

    <div class="search-heading">

        <div class="search-heading-icon">

            <i class="fas fa-sliders"></i>

        </div>

        <div>

            <h3>Find Your Fresh Produce</h3>

            <p>Search and filter products according to your needs</p>

        </div>

    </div>


    <form method="GET" class="search-form">


        <!-- SEARCH -->

        <div class="input-wrap">

            <i class="fas fa-search"></i>

            <input
                type="text"
                name="search"
                placeholder="Search vegetables, fruits, grains..."
                value="<?php echo htmlspecialchars($search); ?>"
            >

        </div>


        <!-- CATEGORY -->

        <select name="category">

            <option value="All"
                <?php echo $category === 'All' ? 'selected' : ''; ?>>

                All Categories

            </option>

            <option value="Vegetable"
                <?php echo $category === 'Vegetable' ? 'selected' : ''; ?>>

                Vegetables

            </option>

            <option value="Fruit"
                <?php echo $category === 'Fruit' ? 'selected' : ''; ?>>

                Fruits

            </option>

            <option value="Grain"
                <?php echo $category === 'Grain' ? 'selected' : ''; ?>>

                Rice & Grains

            </option>

            <option value="Spices"
                <?php echo $category === 'Spices' ? 'selected' : ''; ?>>

                Spices

            </option>

        </select>


        <!-- SORT -->

        <select name="sort">

            <option value="newest"
                <?php echo $sort === 'newest' ? 'selected' : ''; ?>>

                Newest First

            </option>

            <option value="price_low"
                <?php echo $sort === 'price_low' ? 'selected' : ''; ?>>

                Price: Low to High

            </option>

            <option value="price_high"
                <?php echo $sort === 'price_high' ? 'selected' : ''; ?>>

                Price: High to Low

            </option>

            <option value="name_asc"
                <?php echo $sort === 'name_asc' ? 'selected' : ''; ?>>

                Name A-Z

            </option>

        </select>


        <!-- PRICE -->

        <div class="price-group">

            <input
                type="number"
                name="min_price"
                placeholder="Min ৳"
                value="<?php echo htmlspecialchars($min_price); ?>"
            >

            <input
                type="number"
                name="max_price"
                placeholder="Max ৳"
                value="<?php echo htmlspecialchars($max_price); ?>"
            >

        </div>


        <!-- SEARCH BUTTON -->

        <button
            type="submit"
            class="search-btn"
        >

            <i class="fas fa-search"></i>

            Search

        </button>


    </form>

</div>

</section>


<!-- =========================
     PRODUCTS
========================= -->

<section class="products">


    <div class="section-header">

        <div class="section-title">

            <small>Explore Our Marketplace</small>

            <h2>Fresh Products</h2>

        </div>


        <div class="result-count">

            <i class="fas fa-box-open"></i>

            <?php echo $total; ?> Products Found

        </div>

    </div>


    <div class="products-grid">


    <?php if ($result->num_rows > 0): ?>


        <?php while($row = $result->fetch_assoc()): ?>


            <div class="product-card">


                <!-- IMAGE -->

                <div class="image-container">


                    <?php if (!empty($row['is_organic'])): ?>

                        <div class="organic-badge">

                            <i class="fas fa-seedling"></i>

                            Organic

                        </div>

                    <?php endif; ?>


                    <?php

                    $product_image = !empty($row['image'])
                        ? $row['image']
                        : 'https://images.unsplash.com/photo-1542838132-92c53300491e?auto=format&fit=crop&w=800&q=80';

                    ?>


                    <img
                        src="<?php echo htmlspecialchars($product_image); ?>"
                        alt="<?php echo htmlspecialchars($row['name']); ?>"
                        onerror="this.src='https://images.unsplash.com/photo-1542838132-92c53300491e?auto=format&fit=crop&w=800&q=80';"
                    >


                    <div class="image-overlay"></div>


                </div>


                <!-- INFO -->

                <div class="product-info">


                    <div class="product-category">

                        <?php

                        echo htmlspecialchars(
                            $row['category']
                        );

                        ?>

                    </div>


                    <h3 class="product-title">

                        <?php

                        echo htmlspecialchars(
                            $row['name']
                        );

                        ?>

                    </h3>


                    <div class="price-row">

                        <span class="price">

                            ৳ <?php

                            echo number_format(
                                $row['price'],
                                0
                            );

                            ?>

                        </span>


                        <span class="unit">

                            /

                            <?php

                            echo htmlspecialchars(
                                $row['unit']
                            );

                            ?>

                        </span>

                    </div>


                    <!-- FARMER -->

                    <div class="farmer-info">

                        <div class="farmer-avatar">

                            <i class="fas fa-user"></i>

                        </div>


                        <span>

                            By

                            <strong>

                                <?php

                                echo htmlspecialchars(
                                    $row['farmer_name']
                                    ?: 'Local Farmer'
                                );

                                ?>

                            </strong>

                        </span>

                    </div>


                    <!-- BUTTONS -->

                    <div class="btn-group">


                        <a
                            href="product-details.php?id=<?php echo $row['id']; ?>"
                            class="btn details-btn"
                        >

                            <i class="far fa-eye"></i>

                            Details

                        </a>


                        <button
                            onclick="addToCart(<?php echo $row['id']; ?>)"
                            class="btn cart-btn"
                        >

                            <i class="fas fa-shopping-bag"></i>

                            Add to Cart

                        </button>


                    </div>


                </div>


            </div>


        <?php endwhile; ?>


    <?php else: ?>


        <div class="no-products">

            <i class="fas fa-box-open"></i>

            <h3>No Products Found</h3>

            <p>

                Try changing your search or filter options.

            </p>

        </div>


    <?php endif; ?>


    </div>


    <!-- =========================
         PAGINATION
    ========================= -->

    <?php if ($total_pages > 1): ?>


    <div class="pagination">


        <?php

        $query_string =
            "&search=" .
            urlencode($search) .

            "&category=" .
            urlencode($category) .

            "&min_price=" .
            urlencode($min_price) .

            "&max_price=" .
            urlencode($max_price) .

            "&sort=" .
            urlencode($sort);


        if ($page > 1):

        ?>

            <a
                href="?page=<?php echo $page - 1 . $query_string; ?>"
            >

                <i class="fas fa-chevron-left"></i>

            </a>

        <?php endif; ?>


        <?php

        for (
            $i = 1;
            $i <= $total_pages;
            $i++
        ):

        ?>

            <?php if ($i == $page): ?>

                <span class="active">

                    <?php echo $i; ?>

                </span>

            <?php else: ?>

                <a
                    href="?page=<?php echo $i . $query_string; ?>"
                >

                    <?php echo $i; ?>

                </a>

            <?php endif; ?>


        <?php endfor; ?>


        <?php if ($page < $total_pages): ?>

            <a
                href="?page=<?php echo $page + 1 . $query_string; ?>"
            >

                <i class="fas fa-chevron-right"></i>

            </a>

        <?php endif; ?>


    </div>

    <?php endif; ?>


</section>


<!-- =========================
     TOAST
========================= -->

<div id="toast" class="toast">

    <div class="toast-icon">

        <i class="fas fa-check"></i>

    </div>

    <span id="toastMessage">

        Product added to cart!

    </span>

</div>


<!-- =========================
     JAVASCRIPT
========================= -->

<script>

function showToast(message, success = true){

    const toast =
        document.getElementById('toast');

    const messageBox =
        document.getElementById('toastMessage');

    const icon =
        toast.querySelector('.toast-icon i');


    messageBox.textContent = message;


    if(success){

        icon.className =
            'fas fa-check';

    }else{

        icon.className =
            'fas fa-exclamation';

    }


    toast.classList.add('show');


    setTimeout(function(){

        toast.classList.remove('show');

    },3000);

}


function addToCart(productId){

    fetch(
        'add_to_cart.php',
        {

            method:'POST',

            headers:{
                'Content-Type':
                'application/x-www-form-urlencoded'
            },

            body:
                'product_id=' +
                encodeURIComponent(productId) +
                '&quantity=1'

        }
    )

    .then(function(response){

        return response.text();

    })

    .then(function(data){

        showToast(
            data,
            !data.toLowerCase().includes('invalid')
        );

    })

    .catch(function(error){

        showToast(
            'Unable to add product to cart.',
            false
        );

        console.error(error);

    });

}

</script>


</body>

</html>


<?php

$stmt->close();

$conn->close();

?>
