<?php
session_start();

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "farm_db";

$conn = mysqli_connect($servername, $username, $password, $dbname);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

mysqli_set_charset($conn, "utf8mb4");

$message = "";
$success = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $name = trim($_POST['fullName'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message_text = trim($_POST['message'] ?? '');

    if (!empty($name) && !empty($email) && !empty($subject) && !empty($message_text)) {

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

            $message = "Please enter a valid email address.";

        } else {

            $sql = "INSERT INTO contact_messages 
                    (name, email, subject, message) 
                    VALUES (?, ?, ?, ?)";

            $stmt = mysqli_prepare($conn, $sql);

            if ($stmt) {

                mysqli_stmt_bind_param(
                    $stmt,
                    "ssss",
                    $name,
                    $email,
                    $subject,
                    $message_text
                );

                if (mysqli_stmt_execute($stmt)) {

                    $success = true;
                    $message = "Thank you! Your message has been received successfully.";

                } else {

                    $message = "Failed to send message. Please try again.";

                }

                mysqli_stmt_close($stmt);

            } else {

                $message = "Something went wrong. Please try again later.";

            }
        }

    } else {

        $message = "Please fill in all required fields.";

    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Contact Us | Farmer Direct Market</title>

<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

<link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<style>

/* =========================================================
   GLOBAL
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
    background:#f6f9f7;
    color:#17221b;
    line-height:1.7;
}

a{
    text-decoration:none;
}

button,
input,
textarea{
    font-family:inherit;
}


/* =========================================================
   NAVBAR
========================================================= */

.navbar{

    position:sticky;
    top:0;
    z-index:1000;

    display:flex;
    align-items:center;
    justify-content:space-between;

    padding:18px 6%;

    background:rgba(255,255,255,.94);

    backdrop-filter:blur(16px);

    border-bottom:1px solid rgba(15,157,88,.08);

    box-shadow:
        0 8px 30px rgba(0,0,0,.06);
}

.logo{

    display:flex;
    align-items:center;
    gap:10px;

    font-size:25px;
    font-weight:800;

    color:#118548;

    letter-spacing:-.5px;
}

.logo-icon{

    width:44px;
    height:44px;

    display:flex;
    align-items:center;
    justify-content:center;

    border-radius:13px;

    background:
        linear-gradient(
            135deg,
            #0f9d58,
            #22c55e
        );

    color:white;

    box-shadow:
        0 8px 20px rgba(15,157,88,.25);
}

.nav-links{

    display:flex;
    align-items:center;

    list-style:none;

    gap:8px;
}

.nav-links a{

    display:flex;
    align-items:center;
    gap:7px;

    padding:10px 15px;

    color:#374151;

    font-size:14px;
    font-weight:600;

    border-radius:10px;

    transition:.3s;
}

.nav-links a:hover,
.nav-links a.active{

    background:#ecfdf3;

    color:#0f9d58;
}

.nav-buttons{

    display:flex;
    align-items:center;

    gap:10px;
}

.login-btn,
.register-btn{

    padding:11px 20px;

    border-radius:10px;

    font-size:14px;
    font-weight:700;

    transition:.3s;
}

.login-btn{

    color:#0f9d58;

    border:1.5px solid #0f9d58;

    background:white;
}

.login-btn:hover{

    color:white;

    background:#0f9d58;

    transform:translateY(-2px);
}

.register-btn{

    color:white;

    background:
        linear-gradient(
            135deg,
            #0f9d58,
            #16a34a
        );

    box-shadow:
        0 8px 18px rgba(15,157,88,.2);
}

.register-btn:hover{

    transform:translateY(-2px);

    box-shadow:
        0 12px 25px rgba(15,157,88,.3);
}


/* =========================================================
   HERO
========================================================= */

.hero{

    position:relative;

    min-height:480px;

    display:flex;
    align-items:center;
    justify-content:center;

    text-align:center;

    color:white;

    overflow:hidden;

    background:

    linear-gradient(
        135deg,
        rgba(6,78,59,.88),
        rgba(15,157,88,.76)
    ),

    url('https://images.unsplash.com/photo-1497435334941-8c899ee9e8e9?auto=format&fit=crop&w=1800&q=85');

    background-size:cover;
    background-position:center;
}

.hero::after{

    content:"";

    position:absolute;

    width:500px;
    height:500px;

    right:-150px;
    top:-200px;

    border-radius:50%;

    border:1px solid rgba(255,255,255,.15);

    box-shadow:
        0 0 0 50px rgba(255,255,255,.03),
        0 0 0 100px rgba(255,255,255,.02);
}

.hero-content{

    position:relative;

    z-index:2;

    max-width:850px;

    padding:60px 20px;
}

.hero-badge{

    display:inline-flex;

    align-items:center;

    gap:8px;

    padding:9px 17px;

    margin-bottom:22px;

    border-radius:50px;

    background:rgba(255,255,255,.13);

    border:1px solid rgba(255,255,255,.25);

    backdrop-filter:blur(10px);

    font-size:13px;
    font-weight:600;

    letter-spacing:.5px;
}

.hero h1{

    font-size:56px;

    line-height:1.15;

    font-weight:800;

    letter-spacing:-1.5px;

    margin-bottom:20px;
}

.hero p{

    max-width:700px;

    margin:auto;

    font-size:18px;

    line-height:1.8;

    color:rgba(255,255,255,.92);
}


/* =========================================================
   MAIN CONTAINER
========================================================= */

.container{

    width:88%;

    max-width:1250px;

    margin:80px auto;
}


/* =========================================================
   CONTACT GRID
========================================================= */

.contact-wrapper{

    display:grid;

    grid-template-columns:
        minmax(0,1.35fr)
        minmax(320px,.65fr);

    gap:35px;

    align-items:start;
}


/* =========================================================
   CONTACT FORM
========================================================= */

.contact-form{

    background:white;

    padding:42px;

    border-radius:24px;

    border:1px solid #e8eee9;

    box-shadow:
        0 20px 60px rgba(16,54,31,.08);
}

.form-heading{

    margin-bottom:30px;
}

.form-heading .small-title{

    display:inline-block;

    color:#0f9d58;

    font-size:13px;

    font-weight:700;

    text-transform:uppercase;

    letter-spacing:1.5px;

    margin-bottom:8px;
}

.form-heading h2{

    font-size:30px;

    font-weight:800;

    color:#14231a;

    margin-bottom:8px;
}

.form-heading p{

    color:#6b7280;

    font-size:14px;
}

.form-row{

    display:grid;

    grid-template-columns:1fr 1fr;

    gap:18px;
}

.form-group{

    margin-bottom:20px;
}

.form-group label{

    display:block;

    margin-bottom:8px;

    font-size:13px;

    font-weight:700;

    color:#374151;
}

.input-wrapper{

    position:relative;
}

.input-wrapper i{

    position:absolute;

    left:16px;
    top:16px;

    color:#9ca3af;

    font-size:15px;
}

.form-group input,
.form-group textarea{

    width:100%;

    padding:14px 16px 14px 45px;

    border:1.5px solid #e2e8e4;

    border-radius:12px;

    outline:none;

    background:#fbfdfb;

    color:#1f2937;

    font-size:14px;

    transition:.3s;
}

.form-group textarea{

    height:150px;

    resize:none;

    padding-top:14px;
}

.form-group input:focus,
.form-group textarea:focus{

    background:white;

    border-color:#0f9d58;

    box-shadow:
        0 0 0 4px rgba(15,157,88,.1);
}

.send-btn{

    width:100%;

    display:flex;

    align-items:center;
    justify-content:center;

    gap:10px;

    padding:15px 20px;

    border:none;

    border-radius:12px;

    color:white;

    background:
        linear-gradient(
            135deg,
            #0f9d58,
            #15803d
        );

    font-size:15px;

    font-weight:700;

    cursor:pointer;

    box-shadow:
        0 10px 25px rgba(15,157,88,.2);

    transition:.3s;
}

.send-btn:hover{

    transform:translateY(-3px);

    box-shadow:
        0 15px 30px rgba(15,157,88,.3);
}


/* =========================================================
   ALERT
========================================================= */

.alert{

    padding:14px 18px;

    margin-bottom:22px;

    border-radius:12px;

    font-size:14px;

    font-weight:600;
}

.success{

    background:#ecfdf5;

    color:#047857;

    border:1px solid #a7f3d0;
}

.error{

    background:#fef2f2;

    color:#b91c1c;

    border:1px solid #fecaca;
}


/* =========================================================
   CONTACT INFO
========================================================= */

.contact-info{

    display:flex;

    flex-direction:column;

    gap:16px;
}

.info-card{

    display:flex;

    align-items:flex-start;

    gap:18px;

    padding:23px;

    background:white;

    border:1px solid #e8eee9;

    border-radius:18px;

    box-shadow:
        0 12px 35px rgba(16,54,31,.06);

    transition:.3s;
}

.info-card:hover{

    transform:translateY(-5px);

    box-shadow:
        0 20px 45px rgba(15,157,88,.12);
}

.info-icon{

    min-width:48px;
    height:48px;

    display:flex;

    align-items:center;
    justify-content:center;

    border-radius:14px;

    background:#ecfdf3;

    color:#0f9d58;

    font-size:19px;
}

.info-card h3{

    font-size:15px;

    margin-bottom:3px;

    color:#17221b;
}

.info-card p{

    color:#6b7280;

    font-size:13px;
}


/* =========================================================
   SOCIAL CARD
========================================================= */

.social-card{

    padding:25px;

    border-radius:18px;

    color:white;

    background:

    linear-gradient(
        135deg,
        #064e3b,
        #0f9d58
    );

    box-shadow:
        0 15px 35px rgba(15,157,88,.2);
}

.social-card h3{

    font-size:18px;

    margin-bottom:7px;
}

.social-card p{

    font-size:13px;

    opacity:.85;

    margin-bottom:18px;
}

.social-icons{

    display:flex;

    gap:10px;
}

.social-icons a{

    width:40px;
    height:40px;

    display:flex;

    align-items:center;
    justify-content:center;

    border-radius:10px;

    background:rgba(255,255,255,.12);

    color:white;

    transition:.3s;
}

.social-icons a:hover{

    background:white;

    color:#0f9d58;

    transform:translateY(-3px);
}


/* =========================================================
   MAP SECTION
========================================================= */

.map-section{

    margin-top:35px;

    background:white;

    padding:30px;

    border-radius:24px;

    border:1px solid #e8eee9;

    box-shadow:
        0 15px 45px rgba(16,54,31,.06);
}

.map-header{

    display:flex;

    justify-content:space-between;

    align-items:center;

    margin-bottom:20px;
}

.map-header h2{

    font-size:24px;

    font-weight:800;
}

.map-header span{

    color:#0f9d58;

    font-size:13px;

    font-weight:600;
}

.map-placeholder{

    height:300px;

    border-radius:18px;

    overflow:hidden;

    position:relative;

    display:flex;

    align-items:center;

    justify-content:center;

    background:

    linear-gradient(
        rgba(15,157,88,.08),
        rgba(15,157,88,.08)
    ),

    url('https://images.unsplash.com/photo-1524666041070-9b8766b8d1da?auto=format&fit=crop&w=1400&q=80');

    background-size:cover;

    background-position:center;
}

.map-overlay{

    padding:25px 35px;

    text-align:center;

    background:rgba(255,255,255,.94);

    backdrop-filter:blur(12px);

    border-radius:16px;

    box-shadow:
        0 15px 35px rgba(0,0,0,.12);
}

.map-overlay i{

    font-size:30px;

    color:#0f9d58;

    margin-bottom:8px;
}

.map-overlay h3{

    font-size:18px;

    margin-bottom:3px;
}

.map-overlay p{

    color:#6b7280;

    font-size:13px;
}


/* =========================================================
   CTA
========================================================= */

.cta{

    margin-top:70px;

    padding:55px 30px;

    text-align:center;

    border-radius:24px;

    color:white;

    background:

    linear-gradient(
        135deg,
        #0f9d58,
        #047857
    );

    box-shadow:
        0 20px 50px rgba(15,157,88,.2);
}

.cta h2{

    font-size:30px;

    margin-bottom:10px;
}

.cta p{

    max-width:650px;

    margin:auto;

    opacity:.9;
}


/* =========================================================
   FOOTER
========================================================= */

footer{

    margin-top:0;

    padding:55px 6% 25px;

    background:#101815;

    color:white;
}

.footer-grid{

    max-width:1250px;

    margin:auto;

    display:grid;

    grid-template-columns:
        1.5fr 1fr 1fr 1fr;

    gap:40px;

    padding-bottom:40px;
}

.footer-brand .logo{

    color:white;

    margin-bottom:15px;
}

.footer-brand p{

    max-width:350px;

    color:#9ca3af;

    font-size:13px;

    line-height:1.8;
}

.footer-column h4{

    font-size:15px;

    margin-bottom:18px;
}

.footer-column a{

    display:block;

    color:#9ca3af;

    font-size:13px;

    margin-bottom:10px;

    transition:.3s;
}

.footer-column a:hover{

    color:#22c55e;

    padding-left:4px;
}

.footer-bottom{

    max-width:1250px;

    margin:auto;

    padding-top:22px;

    border-top:1px solid rgba(255,255,255,.08);

    text-align:center;

    color:#6b7280;

    font-size:12px;
}


/* =========================================================
   RESPONSIVE
========================================================= */

@media(max-width:1050px){

    .nav-links{

        gap:2px;
    }

    .nav-links a{

        padding:9px 10px;

        font-size:13px;
    }

    .contact-wrapper{

        grid-template-columns:1fr;
    }

    .contact-info{

        display:grid;

        grid-template-columns:1fr 1fr;
    }

    .social-card{

        grid-column:1/-1;
    }

    .footer-grid{

        grid-template-columns:1fr 1fr;
    }
}


@media(max-width:800px){

    .navbar{

        flex-wrap:wrap;

        gap:15px;

        padding:15px 5%;
    }

    .nav-links{

        order:3;

        width:100%;

        justify-content:center;

        flex-wrap:wrap;
    }

    .hero h1{

        font-size:42px;
    }

    .hero p{

        font-size:16px;
    }

    .container{

        width:92%;

        margin:55px auto;
    }
}


@media(max-width:600px){

    .logo{

        font-size:21px;
    }

    .logo-icon{

        width:38px;
        height:38px;
    }

    .nav-buttons{

        gap:6px;
    }

    .login-btn,
    .register-btn{

        padding:9px 12px;

        font-size:12px;
    }

    .nav-links a{

        font-size:12px;

        padding:7px 9px;
    }

    .hero{

        min-height:420px;
    }

    .hero h1{

        font-size:34px;
    }

    .hero p{

        font-size:14px;
    }

    .contact-form{

        padding:25px 20px;
    }

    .form-row{

        grid-template-columns:1fr;
    }

    .contact-info{

        grid-template-columns:1fr;
    }

    .map-section{

        padding:20px;
    }

    .map-header{

        flex-direction:column;

        align-items:flex-start;

        gap:5px;
    }

    .footer-grid{

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
            <i class="fas fa-leaf"></i>
        </span>

        FarmerDirect

    </a>


    <ul class="nav-links">

        <li>
            <a href="index.php">
                <i class="fas fa-house"></i>
                হোম
            </a>
        </li>

        <li>
            <a href="products.php">
                <i class="fas fa-basket-shopping"></i>
                পণ্যসমূহ
            </a>
        </li>

        <li>
            <a href="product_price.php">
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
            <a href="contact.php" class="active">
                <i class="fas fa-envelope"></i>
                যোগাযোগ
            </a>
        </li>

    </ul>


    <div class="nav-buttons">

        <?php if(isset($_SESSION['user_id'])): ?>

            <a href="profile.php" class="login-btn">
                <i class="fas fa-user"></i>
                Profile
            </a>

            <a href="logout.php" class="register-btn">
                Logout
            </a>

        <?php else: ?>

            <a href="login.php" class="login-btn">
                লগইন
            </a>

            <a href="register.php" class="register-btn">
                রেজিস্টার
            </a>

        <?php endif; ?>

    </div>

</nav>


<!-- =========================================================
     HERO
========================================================= -->

<section class="hero">

    <div class="hero-content">

        <div class="hero-badge">

            <i class="fas fa-headset"></i>

            We're Here to Help

        </div>

        <h1>যোগাযোগ করুন</h1>

        <p>
            আপনার কোনো প্রশ্ন, পরামর্শ বা সহায়তার প্রয়োজন হলে
            আমাদের সাথে যোগাযোগ করুন। FarmerDirect কৃষক ও ক্রেতার
            মধ্যে একটি সহজ, স্বচ্ছ ও বিশ্বস্ত যোগাযোগ ব্যবস্থা তৈরি করতে প্রতিশ্রুতিবদ্ধ।
        </p>

    </div>

</section>


<!-- =========================================================
     MAIN
========================================================= -->

<div class="container">


    <div class="contact-wrapper">


        <!-- CONTACT FORM -->

        <div class="contact-form">

            <div class="form-heading">

                <span class="small-title">
                    Get In Touch
                </span>

                <h2>Send Us a Message</h2>

                <p>
                    নিচের ফর্মটি পূরণ করুন। আমাদের টিম যত দ্রুত সম্ভব আপনার সাথে যোগাযোগ করবে।
                </p>

            </div>


            <?php if($message): ?>

                <div class="alert <?= $success ? 'success' : 'error' ?>">

                    <i class="fas <?= $success ? 'fa-circle-check' : 'fa-circle-exclamation' ?>"></i>

                    <?= htmlspecialchars($message) ?>

                </div>

            <?php endif; ?>


            <form method="POST" action="contact.php">


                <div class="form-row">


                    <div class="form-group">

                        <label>
                            Full Name *
                        </label>

                        <div class="input-wrapper">

                            <i class="fas fa-user"></i>

                            <input
                                type="text"
                                name="fullName"
                                placeholder="Enter your full name"
                                required
                            >

                        </div>

                    </div>


                    <div class="form-group">

                        <label>
                            Email Address *
                        </label>

                        <div class="input-wrapper">

                            <i class="fas fa-envelope"></i>

                            <input
                                type="email"
                                name="email"
                                placeholder="Enter your email"
                                required
                            >

                        </div>

                    </div>

                </div>


                <div class="form-group">

                    <label>
                        Subject *
                    </label>

                    <div class="input-wrapper">

                        <i class="fas fa-pen"></i>

                        <input
                            type="text"
                            name="subject"
                            placeholder="What is your message about?"
                            required
                        >

                    </div>

                </div>


                <div class="form-group">

                    <label>
                        Your Message *
                    </label>

                    <div class="input-wrapper">

                        <i class="fas fa-message"></i>

                        <textarea
                            name="message"
                            placeholder="Write your message here..."
                            required
                        ></textarea>

                    </div>

                </div>


                <button type="submit" class="send-btn">

                    <i class="fas fa-paper-plane"></i>

                    Send Message

                </button>


            </form>

        </div>


        <!-- CONTACT INFORMATION -->

        <div class="contact-info">


            <div class="info-card">

                <div class="info-icon">

                    <i class="fas fa-location-dot"></i>

                </div>

                <div>

                    <h3>Office Address</h3>

                    <p>
                        Dhaka, Bangladesh
                    </p>

                </div>

            </div>


            <div class="info-card">

                <div class="info-icon">

                    <i class="fas fa-phone"></i>

                </div>

                <div>

                    <h3>Phone Number</h3>

                    <p>
                        +880 1XXXXXXXXX
                    </p>

                </div>

            </div>


            <div class="info-card">

                <div class="info-icon">

                    <i class="fas fa-envelope"></i>

                </div>

                <div>

                    <h3>Email Address</h3>

                    <p>
                        support@farmerdirect.com
                    </p>

                </div>

            </div>


            <div class="info-card">

                <div class="info-icon">

                    <i class="fas fa-clock"></i>

                </div>

                <div>

                    <h3>Business Hours</h3>

                    <p>
                        Saturday – Thursday<br>
                        9:00 AM – 6:00 PM
                    </p>

                </div>

            </div>


            <div class="social-card">

                <h3>Connect With Us</h3>

                <p>
                    আমাদের সাথে সোশ্যাল মিডিয়াতেও যুক্ত থাকুন।
                </p>

                <div class="social-icons">

                    <a href="#">
                        <i class="fab fa-facebook-f"></i>
                    </a>

                    <a href="#">
                        <i class="fab fa-instagram"></i>
                    </a>

                    <a href="#">
                        <i class="fab fa-linkedin-in"></i>
                    </a>

                    <a href="#">
                        <i class="fab fa-youtube"></i>
                    </a>

                </div>

            </div>


        </div>

    </div>


    <!-- MAP -->

    <div class="map-section">

        <div class="map-header">

            <h2>
                <i class="fas fa-map-marker-alt" style="color:#0f9d58;"></i>
                আমাদের অবস্থান
            </h2>

            <span>
                Dhaka, Bangladesh
            </span>

        </div>


        <div class="map-placeholder">

            <div class="map-overlay">

                <i class="fas fa-location-dot"></i>

                <h3>FarmerDirect Market</h3>

                <p>
                    Dhaka, Bangladesh
                </p>

            </div>

        </div>

    </div>


    <!-- CTA -->

    <div class="cta">

        <h2>
            কৃষক ও ক্রেতার মধ্যে সরাসরি সংযোগ
        </h2>

        <p>
            FarmerDirect-এর সাথে যুক্ত হয়ে একটি স্বচ্ছ,
            ন্যায্য ও আধুনিক কৃষি বাজার ব্যবস্থার অংশীদার হোন।
        </p>

    </div>


</div>


<!-- =========================================================
     FOOTER
========================================================= -->

<footer>

    <div class="footer-grid">


        <div class="footer-brand">

            <a href="index.php" class="logo">

                <span class="logo-icon">
                    <i class="fas fa-leaf"></i>
                </span>

                FarmerDirect

            </a>

            <p>
                কৃষকের কাছ থেকে সরাসরি ক্রেতার কাছে।
                তাজা পণ্য, ন্যায্য মূল্য এবং একটি
                স্বচ্ছ ডিজিটাল কৃষি বাজার তৈরিই আমাদের লক্ষ্য।
            </p>

        </div>


        <div class="footer-column">

            <h4>Quick Links</h4>

            <a href="index.php">হোম</a>

            <a href="products.php">পণ্যসমূহ</a>

            <a href="product_price.php">ফসলের দাম</a>

            <a href="about.php">আমাদের সম্পর্কে</a>

        </div>


        <div class="footer-column">

            <h4>Support</h4>

            <a href="contact.php">যোগাযোগ</a>

            <a href="login.php">লগইন</a>

            <a href="register.php">রেজিস্টার</a>

            <a href="#">Privacy Policy</a>

        </div>


        <div class="footer-column">

            <h4>Contact</h4>

            <a href="#">
                <i class="fas fa-location-dot"></i>
                Dhaka, Bangladesh
            </a>

            <a href="#">
                <i class="fas fa-envelope"></i>
                support@farmerdirect.com
            </a>

            <a href="#">
                <i class="fas fa-phone"></i>
                +880 1XXXXXXXXX
            </a>

        </div>


    </div>


    <div class="footer-bottom">

        © <?= date("Y") ?> Farmer Direct Market Platform.
        All Rights Reserved.

    </div>

</footer>


</body>

</html>


<?php

mysqli_close($conn);

?>
