<?php
session_start();

$servername = "localhost";
$username   = "root";
$password   = "";
$dbname     = "farm_db";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Database Connection Failed: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");
?>

<!DOCTYPE html>
<html lang="bn">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>About Us | Farmer Direct Market</title>

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Poppins:wght@400;500;600;700;800&display=swap"
          rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <style>

        /* =====================================================
           GLOBAL
        ===================================================== */

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: #f7faf8;
            color: #1f2937;
            line-height: 1.7;
        }

        a {
            text-decoration: none;
        }

        img {
            max-width: 100%;
            display: block;
        }


        /* =====================================================
           NAVBAR
        ===================================================== */

        .navbar {
            position: sticky;
            top: 0;
            z-index: 1000;

            display: flex;
            align-items: center;
            justify-content: space-between;

            padding: 16px 7%;

            background: rgba(255, 255, 255, 0.96);
            backdrop-filter: blur(15px);

            border-bottom: 1px solid #e5e7eb;

            box-shadow: 0 5px 25px rgba(0, 0, 0, 0.06);
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 9px;

            color: #15803d;

            font-family: 'Poppins', sans-serif;
            font-size: 25px;
            font-weight: 800;

            white-space: nowrap;
        }

        .logo-icon {
            width: 42px;
            height: 42px;

            display: flex;
            align-items: center;
            justify-content: center;

            background: linear-gradient(
                135deg,
                #16a34a,
                #15803d
            );

            color: white;

            border-radius: 12px;

            box-shadow: 0 8px 20px rgba(21, 128, 61, 0.25);
        }

        .nav-links {
            display: flex;
            align-items: center;
            gap: 30px;

            list-style: none;
        }

        .nav-links a {
            position: relative;

            color: #374151;

            font-size: 14px;
            font-weight: 600;

            transition: 0.3s;
        }

        .nav-links a::after {
            content: "";

            position: absolute;
            left: 0;
            bottom: -8px;

            width: 0;
            height: 2px;

            background: #16a34a;

            transition: 0.3s;
        }

        .nav-links a:hover,
        .nav-links a.active {
            color: #15803d;
        }

        .nav-links a:hover::after,
        .nav-links a.active::after {
            width: 100%;
        }


        /* =====================================================
           NAV BUTTONS
        ===================================================== */

        .nav-buttons {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .login-btn,
        .register-btn {
            padding: 10px 18px;

            border-radius: 10px;

            font-size: 14px;
            font-weight: 600;

            transition: 0.3s;
        }

        .login-btn {
            color: #15803d;
            border: 1.5px solid #16a34a;
            background: white;
        }

        .login-btn:hover {
            background: #15803d;
            color: white;
        }

        .register-btn {
            color: white;

            background: linear-gradient(
                135deg,
                #16a34a,
                #15803d
            );

            box-shadow: 0 7px 18px rgba(22, 163, 74, 0.25);
        }

        .register-btn:hover {
            transform: translateY(-2px);

            box-shadow:
                0 10px 25px rgba(22, 163, 74, 0.35);
        }


        /* =====================================================
           GOOGLE TRANSLATE
        ===================================================== */

        .translate-box {
            display: flex;
            align-items: center;
            gap: 8px;

            padding: 7px 10px;

            background: #f0fdf4;

            border: 1px solid #bbf7d0;

            border-radius: 10px;
        }

        .translate-box i {
            color: #15803d;
        }

        #google_translate_element {
            font-size: 12px;
        }

        .goog-te-gadget {
            font-family: 'Inter', sans-serif !important;
            font-size: 0 !important;
        }

        .goog-te-gadget select {
            padding: 5px 8px;

            border: none;
            outline: none;

            background: transparent;

            color: #166534;

            font-size: 12px;
            font-weight: 600;

            cursor: pointer;
        }


        /* =====================================================
           HERO
        ===================================================== */

        .about-hero {
            position: relative;

            min-height: 580px;

            display: flex;
            align-items: center;
            justify-content: center;

            text-align: center;

            color: white;

            background:
                linear-gradient(
                    135deg,
                    rgba(6, 78, 59, 0.90),
                    rgba(21, 128, 61, 0.72)
                ),
                url('https://images.pexels.com/photos/325944/pexels-photo-325944.jpeg')
                center / cover no-repeat;
        }

        .hero-content {
            width: 90%;
            max-width: 900px;

            padding: 40px 20px;
        }

        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;

            padding: 9px 18px;

            margin-bottom: 25px;

            border-radius: 50px;

            background: rgba(255, 255, 255, 0.14);

            border: 1px solid rgba(255, 255, 255, 0.3);

            backdrop-filter: blur(10px);

            font-size: 13px;
            font-weight: 600;
        }

        .about-hero h1 {
            margin-bottom: 20px;

            font-family: 'Poppins', sans-serif;

            font-size: clamp(40px, 6vw, 68px);

            font-weight: 800;

            line-height: 1.15;
        }

        .about-hero p {
            max-width: 700px;

            margin: auto;

            font-size: 19px;

            color: rgba(255, 255, 255, 0.92);
        }

        .hero-buttons {
            display: flex;
            justify-content: center;
            gap: 14px;

            margin-top: 30px;

            flex-wrap: wrap;
        }

        .hero-btn {
            padding: 13px 25px;

            border-radius: 10px;

            font-size: 14px;
            font-weight: 600;

            transition: 0.3s;
        }

        .hero-btn.primary {
            background: white;
            color: #15803d;
        }

        .hero-btn.secondary {
            color: white;

            border: 1px solid rgba(255, 255, 255, 0.5);

            background: rgba(255, 255, 255, 0.1);
        }

        .hero-btn:hover {
            transform: translateY(-3px);
        }


        /* =====================================================
           MAIN CONTAINER
        ===================================================== */

        .container {
            width: 86%;
            max-width: 1250px;

            margin: 80px auto;
        }


        /* =====================================================
           SECTION COMMON
        ===================================================== */

        .section {
            margin-bottom: 70px;
        }

        .section-header {
            text-align: center;

            max-width: 750px;

            margin: 0 auto 45px;
        }

        .section-tag {
            display: inline-block;

            margin-bottom: 10px;

            color: #15803d;

            font-size: 13px;
            font-weight: 700;

            text-transform: uppercase;

            letter-spacing: 1.5px;
        }

        .section-header h2 {
            margin-bottom: 15px;

            font-family: 'Poppins', sans-serif;

            color: #111827;

            font-size: 38px;
            font-weight: 800;
        }

        .section-header p {
            color: #6b7280;

            font-size: 16px;
        }


        /* =====================================================
           STORY SECTION
        ===================================================== */

        .story-grid {
            display: grid;

            grid-template-columns: 1.1fr 1fr;

            gap: 55px;

            align-items: center;
        }

        .story-image {
            position: relative;
        }

        .story-image img {
            width: 100%;
            height: 480px;

            object-fit: cover;

            border-radius: 24px;

            box-shadow:
                0 25px 60px rgba(0, 0, 0, 0.12);
        }

        .story-content h2 {
            margin-bottom: 20px;

            color: #111827;

            font-family: 'Poppins', sans-serif;

            font-size: 38px;
            font-weight: 800;
        }

        .story-content h2 span {
            color: #16a34a;
        }

        .story-content p {
            margin-bottom: 18px;

            color: #5b6472;

            font-size: 15px;
        }

        .story-highlight {
            display: flex;
            align-items: flex-start;
            gap: 15px;

            margin-top: 25px;
            padding: 20px;

            background: #f0fdf4;

            border-left: 4px solid #16a34a;

            border-radius: 12px;
        }

        .story-highlight i {
            color: #16a34a;

            font-size: 22px;
        }

        .story-highlight p {
            margin: 0;

            color: #166534;

            font-weight: 600;
        }


        /* =====================================================
           MISSION VISION VALUES
        ===================================================== */

        .mv-grid {
            display: grid;

            grid-template-columns:
                repeat(3, 1fr);

            gap: 25px;
        }

        .mv-card {
            padding: 35px 30px;

            background: white;

            border: 1px solid #e5e7eb;

            border-radius: 20px;

            text-align: center;

            transition: 0.4s;

            box-shadow:
                0 10px 35px rgba(0, 0, 0, 0.04);
        }

        .mv-card:hover {
            transform: translateY(-10px);

            border-color: #86efac;

            box-shadow:
                0 20px 50px rgba(22, 163, 74, 0.12);
        }

        .mv-icon {
            width: 70px;
            height: 70px;

            margin: 0 auto 20px;

            display: flex;
            align-items: center;
            justify-content: center;

            background: #f0fdf4;

            color: #16a34a;

            border-radius: 20px;

            font-size: 28px;
        }

        .mv-card h3 {
            margin-bottom: 12px;

            color: #111827;

            font-size: 21px;
        }

        .mv-card p {
            color: #6b7280;

            font-size: 14px;
        }


        /* =====================================================
           IMPACT
        ===================================================== */

        .impact-section {
            padding: 60px 40px;

            background:
                linear-gradient(
                    135deg,
                    #064e3b,
                    #15803d
                );

            border-radius: 25px;

            color: white;
        }

        .impact-title {
            text-align: center;

            margin-bottom: 45px;
        }

        .impact-title h2 {
            font-family: 'Poppins', sans-serif;

            font-size: 36px;
        }

        .impact-title p {
            margin-top: 8px;

            opacity: 0.85;
        }

        .stats {
            display: grid;

            grid-template-columns:
                repeat(4, 1fr);

            gap: 20px;
        }

        .stat-item {
            padding: 25px;

            text-align: center;

            background: rgba(255, 255, 255, 0.08);

            border: 1px solid rgba(255, 255, 255, 0.15);

            border-radius: 15px;

            backdrop-filter: blur(10px);
        }

        .stat-item h3 {
            margin-bottom: 5px;

            font-size: 34px;

            font-weight: 800;
        }

        .stat-item p {
            font-size: 13px;

            opacity: 0.85;
        }


        /* =====================================================
           WHY CHOOSE US
        ===================================================== */

        .features {
            display: grid;

            grid-template-columns:
                repeat(3, 1fr);

            gap: 25px;
        }

        .feature {
            padding: 30px;

            background: white;

            border: 1px solid #e5e7eb;

            border-radius: 18px;

            transition: 0.3s;
        }

        .feature:hover {
            transform: translateY(-7px);

            box-shadow:
                0 15px 40px rgba(0, 0, 0, 0.07);
        }

        .feature-icon {
            width: 55px;
            height: 55px;

            display: flex;
            align-items: center;
            justify-content: center;

            margin-bottom: 20px;

            background: #f0fdf4;

            color: #16a34a;

            border-radius: 14px;

            font-size: 22px;
        }

        .feature h3 {
            margin-bottom: 10px;

            color: #111827;

            font-size: 18px;
        }

        .feature p {
            color: #6b7280;

            font-size: 14px;
        }


        /* =====================================================
           HOW IT WORKS
        ===================================================== */

        .steps {
            display: grid;

            grid-template-columns:
                repeat(4, 1fr);

            gap: 25px;
        }

        .step {
            position: relative;

            padding: 30px;

            background: white;

            border-radius: 18px;

            border: 1px solid #e5e7eb;

            text-align: center;
        }

        .step-number {
            width: 48px;
            height: 48px;

            display: flex;
            align-items: center;
            justify-content: center;

            margin: 0 auto 18px;

            background: #15803d;

            color: white;

            border-radius: 50%;

            font-size: 18px;
            font-weight: 700;
        }

        .step h3 {
            margin-bottom: 10px;

            font-size: 17px;
        }

        .step p {
            color: #6b7280;

            font-size: 13px;
        }


        /* =====================================================
           TESTIMONIALS
        ===================================================== */

        .testimonials {
            display: grid;

            grid-template-columns:
                repeat(2, 1fr);

            gap: 25px;
        }

        .testimonial {
            padding: 30px;

            background: white;

            border-radius: 18px;

            border: 1px solid #e5e7eb;

            box-shadow:
                0 10px 30px rgba(0, 0, 0, 0.04);
        }

        .stars {
            color: #f59e0b;

            margin-bottom: 15px;
        }

        .testimonial p {
            color: #4b5563;

            font-size: 14px;

            font-style: italic;

            margin-bottom: 20px;
        }

        .testimonial-author {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .author-avatar {
            width: 45px;
            height: 45px;

            display: flex;
            align-items: center;
            justify-content: center;

            background: #dcfce7;

            color: #15803d;

            border-radius: 50%;

            font-weight: 700;
        }

        .testimonial-author strong {
            display: block;

            color: #111827;

            font-size: 14px;
        }

        .testimonial-author span {
            color: #6b7280;

            font-size: 12px;
        }


        /* =====================================================
           CTA
        ===================================================== */

        .cta {
            padding: 65px 30px;

            text-align: center;

            background:
                linear-gradient(
                    135deg,
                    #15803d,
                    #16a34a
                );

            color: white;

            border-radius: 25px;

            box-shadow:
                0 20px 50px rgba(22, 163, 74, 0.2);
        }

        .cta h2 {
            margin-bottom: 12px;

            font-family: 'Poppins', sans-serif;

            font-size: 34px;
        }

        .cta p {
            max-width: 650px;

            margin: 0 auto 25px;

            opacity: 0.9;
        }

        .cta-btn {
            display: inline-block;

            padding: 13px 25px;

            background: white;

            color: #15803d;

            border-radius: 10px;

            font-weight: 700;

            transition: 0.3s;
        }

        .cta-btn:hover {
            transform: translateY(-3px);

            box-shadow:
                0 10px 25px rgba(0, 0, 0, 0.15);
        }


        /* =====================================================
           FOOTER
        ===================================================== */

        footer {
            margin-top: 80px;

            padding: 60px 7% 25px;

            background: #111827;

            color: white;
        }

        .footer-grid {
            display: grid;

            grid-template-columns:
                1.5fr 1fr 1fr 1.2fr;

            gap: 40px;

            margin-bottom: 45px;
        }

        .footer-brand h3 {
            margin-bottom: 15px;

            font-size: 23px;

            color: #4ade80;
        }

        .footer-brand p {
            color: #9ca3af;

            font-size: 14px;

            max-width: 320px;
        }

        .footer-column h4 {
            margin-bottom: 18px;

            font-size: 15px;
        }

        .footer-column a {
            display: block;

            margin-bottom: 10px;

            color: #9ca3af;

            font-size: 13px;

            transition: 0.3s;
        }

        .footer-column a:hover {
            color: #4ade80;
        }

        .footer-bottom {
            padding-top: 25px;

            border-top: 1px solid #374151;

            text-align: center;

            color: #9ca3af;

            font-size: 13px;
        }


        /* =====================================================
           RESPONSIVE
        ===================================================== */

        @media (max-width: 1100px) {

            .nav-links {
                gap: 18px;
            }

            .story-grid {
                grid-template-columns: 1fr;
            }

            .story-image img {
                height: 400px;
            }

            .mv-grid,
            .features {
                grid-template-columns: repeat(2, 1fr);
            }

            .stats,
            .steps {
                grid-template-columns: repeat(2, 1fr);
            }

            .footer-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }


        @media (max-width: 850px) {

            .navbar {
                flex-wrap: wrap;

                gap: 15px;

                justify-content: center;
            }

            .logo {
                width: 100%;

                justify-content: center;
            }

            .nav-links {
                flex-wrap: wrap;

                justify-content: center;
            }

            .translate-box {
                display: none;
            }

            .testimonials {
                grid-template-columns: 1fr;
            }
        }


        @media (max-width: 600px) {

            .navbar {
                padding: 15px 5%;
            }

            .nav-links {
                gap: 15px;
            }

            .nav-links a {
                font-size: 12px;
            }

            .nav-buttons {
                width: 100%;

                justify-content: center;
            }

            .about-hero {
                min-height: 500px;
            }

            .about-hero p {
                font-size: 16px;
            }

            .container {
                width: 92%;

                margin: 55px auto;
            }

            .section-header h2,
            .story-content h2 {
                font-size: 30px;
            }

            .mv-grid,
            .features,
            .stats,
            .steps {
                grid-template-columns: 1fr;
            }

            .impact-section {
                padding: 40px 20px;
            }

            .footer-grid {
                grid-template-columns: 1fr;

                text-align: center;
            }

            .footer-brand p {
                margin: auto;
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
            <i class="fas fa-seedling"></i>
        </span>

        FarmerDirect

    </a>


    <ul class="nav-links">

        <li>
            <a href="index.php">
                হোম
            </a>
        </li>

        <li>
            <a href="products.php">
                পণ্যসমূহ
            </a>
        </li>

        <li>
            <a href="product_price.php">
                ফসলের দাম
            </a>
        </li>

        <li>
            <a href="about.php" class="active">
                আমাদের সম্পর্কে
            </a>
        </li>

        <li>
            <a href="contact.php">
                যোগাযোগ
            </a>
        </li>

    </ul>


    <div class="nav-buttons">

        <div class="translate-box">

            <i class="fas fa-language"></i>

            <div id="google_translate_element"></div>

        </div>

        <?php if (isset($_SESSION['user_id'])): ?>

            <a href="logout.php" class="login-btn">
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


<!-- =====================================================
     HERO
===================================================== -->

<section class="about-hero">

    <div class="hero-content">

        <div class="hero-badge">
            <i class="fas fa-leaf"></i>
            Bangladesh's Digital Agriculture Marketplace
        </div>

        <h1>
            আমরা কৃষক ও ভোক্তার
            মাঝে সেতুবন্ধন
        </h1>

        <p>
            Farmer Direct Market একটি আধুনিক ডিজিটাল কৃষি প্ল্যাটফর্ম,
            যেখানে কৃষকরা সরাসরি ক্রেতার কাছে তাদের পণ্য পৌঁছে দিতে পারেন।
            আমাদের লক্ষ্য হলো ন্যায্য মূল্য, স্বচ্ছ বাজার এবং নিরাপদ খাদ্য নিশ্চিত করা।
        </p>

        <div class="hero-buttons">

            <a href="products.php" class="hero-btn primary">
                <i class="fas fa-shopping-basket"></i>
                পণ্য দেখুন
            </a>

            <a href="contact.php" class="hero-btn secondary">
                <i class="fas fa-envelope"></i>
                যোগাযোগ করুন
            </a>

        </div>

    </div>

</section>


<!-- =====================================================
     MAIN CONTENT
===================================================== -->

<main class="container">


    <!-- STORY -->

    <section class="section">

        <div class="story-grid">

            <div class="story-image">

                <img
                    src="https://images.pexels.com/photos/1595104/pexels-photo-1595104.jpeg"
                    alt="Farmer Direct Market"
                >

            </div>


            <div class="story-content">

                <span class="section-tag">
                    Our Story
                </span>

                <h2>
                    কৃষকের পরিশ্রম থেকে
                    <span>আপনার টেবিলে</span>
                </h2>

                <p>
                    Farmer Direct Market-এর যাত্রা শুরু হয়েছে বাংলাদেশের
                    কৃষক ও সাধারণ মানুষের মধ্যে একটি শক্তিশালী ও সরাসরি
                    সংযোগ তৈরির উদ্দেশ্যে।
                </p>

                <p>
                    আমাদের দেশের কৃষকরা প্রতিদিন কঠোর পরিশ্রম করে
                    শাকসবজি, ফলমূল, ধান, গম, মসলা এবং বিভিন্ন ধরনের
                    কৃষিপণ্য উৎপাদন করেন। কিন্তু অনেক সময় মধ্যস্বত্বভোগী
                    ও জটিল সরবরাহ ব্যবস্থার কারণে কৃষকরা তাদের পণ্যের
                    প্রকৃত মূল্য পান না।
                </p>

                <p>
                    অন্যদিকে, ভোক্তাদের একই পণ্য কিনতে গিয়ে অতিরিক্ত মূল্য
                    দিতে হয়। এই সমস্যার একটি প্রযুক্তিনির্ভর সমাধান হিসেবে
                    Farmer Direct Market কৃষকদের সরাসরি ক্রেতাদের সাথে
                    যুক্ত করার একটি সহজ ও স্বচ্ছ প্ল্যাটফর্ম তৈরি করেছে।
                </p>

                <div class="story-highlight">

                    <i class="fas fa-quote-left"></i>

                    <p>
                        "কৃষকের ন্যায্য মূল্য এবং ভোক্তার ন্যায্য দাম —
                        একটি স্বচ্ছ বাজার ব্যবস্থাই আমাদের লক্ষ্য।"
                    </p>

                </div>

            </div>

        </div>

    </section>


    <!-- MISSION VISION VALUES -->

    <section class="section">

        <div class="section-header">

            <span class="section-tag">
                Our Purpose
            </span>

            <h2>
                আমাদের লক্ষ্য ও মূল্যবোধ
            </h2>

            <p>
                প্রযুক্তি, স্বচ্ছতা ও বিশ্বাসের মাধ্যমে
                বাংলাদেশের কৃষি বাজারকে আরও সহজ ও শক্তিশালী করা।
            </p>

        </div>


        <div class="mv-grid">


            <div class="mv-card">

                <div class="mv-icon">
                    <i class="fas fa-bullseye"></i>
                </div>

                <h3>
                    আমাদের Mission
                </h3>

                <p>
                    কৃষকদের সরাসরি ক্রেতাদের সাথে যুক্ত করা,
                    ন্যায্য মূল্য নিশ্চিত করা এবং মধ্যস্বত্বভোগীর
                    ওপর নির্ভরতা কমানো।
                </p>

            </div>


            <div class="mv-card">

                <div class="mv-icon">
                    <i class="fas fa-eye"></i>
                </div>

                <h3>
                    আমাদের Vision
                </h3>

                <p>
                    বাংলাদেশের জন্য একটি আধুনিক, স্বচ্ছ ও
                    টেকসই ডিজিটাল কৃষি ইকোসিস্টেম তৈরি করা,
                    যেখানে কৃষক ও ভোক্তা উভয়েই উপকৃত হবে।
                </p>

            </div>


            <div class="mv-card">

                <div class="mv-icon">
                    <i class="fas fa-heart"></i>
                </div>

                <h3>
                    আমাদের Values
                </h3>

                <p>
                    সততা, স্বচ্ছতা, বিশ্বাস, কৃষকের প্রতি সম্মান,
                    গ্রাহকের সন্তুষ্টি এবং নিরাপদ খাদ্য—
                    আমাদের প্রতিটি কার্যক্রমের মূল ভিত্তি।
                </p>

            </div>


        </div>

    </section>


    <!-- IMPACT -->

    <section class="section">

        <div class="impact-section">

            <div class="impact-title">

                <h2>
                    আমাদের Impact
                </h2>

                <p>
                    কৃষক ও ভোক্তাদের জন্য একটি শক্তিশালী
                    ডিজিটাল কৃষি নেটওয়ার্ক গড়ে তোলার পথে।
                </p>

            </div>


            <div class="stats">

                <div class="stat-item">

                    <h3>
                        150+
                    </h3>

                    <p>
                        Verified Farmers
                    </p>

                </div>


                <div class="stat-item">

                    <h3>
                        12K+
                    </h3>

                    <p>
                        Successful Orders
                    </p>

                </div>


                <div class="stat-item">

                    <h3>
                        28+
                    </h3>

                    <p>
                        District Network
                    </p>

                </div>


                <div class="stat-item">

                    <h3>
                        95%
                    </h3>

                    <p>
                        Customer Satisfaction
                    </p>

                </div>

            </div>

        </div>

    </section>


    <!-- WHY CHOOSE US -->

    <section class="section">

        <div class="section-header">

            <span class="section-tag">
                Why FarmerDirect
            </span>

            <h2>
                কেন আমাদের বেছে নেবেন?
            </h2>

            <p>
                কৃষক ও ক্রেতা উভয়ের জন্য একটি সহজ,
                নিরাপদ এবং স্বচ্ছ কৃষি বাজার তৈরি করাই আমাদের লক্ষ্য।
            </p>

        </div>


        <div class="features">


            <div class="feature">

                <div class="feature-icon">
                    <i class="fas fa-handshake"></i>
                </div>

                <h3>
                    সরাসরি কৃষকের কাছ থেকে
                </h3>

                <p>
                    মধ্যস্বত্বভোগীর জটিলতা কমিয়ে কৃষক ও ক্রেতার
                    মধ্যে সরাসরি যোগাযোগ তৈরি করা হয়।
                </p>

            </div>


            <div class="feature">

                <div class="feature-icon">
                    <i class="fas fa-tags"></i>
                </div>

                <h3>
                    স্বচ্ছ মূল্য
                </h3>

                <p>
                    পণ্যের মূল্য সম্পর্কে স্বচ্ছ তথ্য প্রদান করা হয়,
                    যাতে কৃষক ও ক্রেতা উভয়েই সঠিক সিদ্ধান্ত নিতে পারেন।
                </p>

            </div>


            <div class="feature">

                <div class="feature-icon">
                    <i class="fas fa-seedling"></i>
                </div>

                <h3>
                    তাজা কৃষিপণ্য
                </h3>

                <p>
                    মাঠ থেকে সংগ্রহ করা তাজা কৃষিপণ্য
                    দ্রুততম সময়ে ক্রেতার কাছে পৌঁছে দেওয়ার চেষ্টা করা হয়।
                </p>

            </div>


            <div class="feature">

                <div class="feature-icon">
                    <i class="fas fa-shield-halved"></i>
                </div>

                <h3>
                    নিরাপদ কেনাকাটা
                </h3>

                <p>
                    একটি নির্ভরযোগ্য ও নিরাপদ ডিজিটাল পরিবেশে
                    পণ্য দেখা এবং কেনাকাটার সুবিধা প্রদান করা হয়।
                </p>

            </div>


            <div class="feature">

                <div class="feature-icon">
                    <i class="fas fa-chart-line"></i>
                </div>

                <h3>
                    বাজার তথ্য
                </h3>

                <p>
                    কৃষিপণ্যের বর্তমান মূল্য ও বাজারের পরিবর্তন সম্পর্কে
                    প্রয়োজনীয় তথ্য সহজভাবে দেখার সুযোগ রয়েছে।
                </p>

            </div>


            <div class="feature">

                <div class="feature-icon">
                    <i class="fas fa-users"></i>
                </div>

                <h3>
                    কৃষক কমিউনিটি
                </h3>

                <p>
                    কৃষকদের ডিজিটাল বাজারের সাথে যুক্ত করে
                    তাদের ব্যবসায়িক সুযোগ আরও বিস্তৃত করতে সাহায্য করা।
                </p>

            </div>


        </div>

    </section>


    <!-- HOW IT WORKS -->

    <section class="section">

        <div class="section-header">

            <span class="section-tag">
                Simple Process
            </span>

            <h2>
                Farmer Direct কীভাবে কাজ করে?
            </h2>

            <p>
                কৃষক থেকে ক্রেতা পর্যন্ত একটি সহজ ও স্বচ্ছ প্রক্রিয়া।
            </p>

        </div>


        <div class="steps">


            <div class="step">

                <div class="step-number">
                    01
                </div>

                <h3>
                    কৃষক পণ্য যুক্ত করেন
                </h3>

                <p>
                    কৃষক তার উৎপাদিত পণ্যের তথ্য,
                    মূল্য ও স্টক প্ল্যাটফর্মে যুক্ত করেন।
                </p>

            </div>


            <div class="step">

                <div class="step-number">
                    02
                </div>

                <h3>
                    ক্রেতা পণ্য নির্বাচন করেন
                </h3>

                <p>
                    ক্রেতা পণ্যের তালিকা থেকে
                    প্রয়োজনীয় পণ্য নির্বাচন করেন।
                </p>

            </div>


            <div class="step">

                <div class="step-number">
                    03
                </div>

                <h3>
                    অর্ডার সম্পন্ন হয়
                </h3>

                <p>
                    প্রয়োজনীয় তথ্য দিয়ে ক্রেতা
                    সহজেই অর্ডার সম্পন্ন করেন।
                </p>

            </div>


            <div class="step">

                <div class="step-number">
                    04
                </div>

                <h3>
                    পণ্য পৌঁছে যায়
                </h3>

                <p>
                    কৃষিপণ্য সংগ্রহ ও ডেলিভারির মাধ্যমে
                    ক্রেতার কাছে পৌঁছে দেওয়া হয়।
                </p>

            </div>


        </div>

    </section>


    <!-- TESTIMONIALS -->

    <section class="section">

        <div class="section-header">

            <span class="section-tag">
                Community Voices
            </span>

            <h2>
                আমাদের সাথে যারা আছেন
            </h2>

            <p>
                কৃষক ও ক্রেতাদের অভিজ্ঞতাই আমাদের এগিয়ে যাওয়ার অনুপ্রেরণা।
            </p>

        </div>


        <div class="testimonials">


            <div class="testimonial">

                <div class="stars">
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                </div>

                <p>
                    "Farmer Direct-এর মাধ্যমে আমার উৎপাদিত পণ্য
                    সরাসরি ক্রেতাদের কাছে পৌঁছানোর সুযোগ পাচ্ছি।
                    এতে বাজার খুঁজতে সময় কম লাগে এবং নিজের পণ্যের
                    মূল্য সম্পর্কে ভালো ধারণা পাওয়া যায়।"
                </p>

                <div class="testimonial-author">

                    <div class="author-avatar">
                        AK
                    </div>

                    <div>

                        <strong>
                            Abdul Karim
                        </strong>

                        <span>
                            Farmer, Gazipur
                        </span>

                    </div>

                </div>

            </div>


            <div class="testimonial">

                <div class="stars">
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                </div>

                <p>
                    "অনলাইনে কৃষিপণ্য খুঁজে পাওয়া এবং সরাসরি
                    কৃষকের কাছ থেকে কেনার ধারণাটি খুবই ভালো।
                    তাজা পণ্য ও স্বচ্ছ মূল্য—দুই দিক থেকেই
                    এটি একটি কার্যকর উদ্যোগ।"
                </p>

                <div class="testimonial-author">

                    <div class="author-avatar">
                        RB
                    </div>

                    <div>

                        <strong>
                            Rahima Begum
                        </strong>

                        <span>
                            Customer, Dhaka
                        </span>

                    </div>

                </div>

            </div>


        </div>

    </section>


    <!-- CTA -->

    <section class="section">

        <div class="cta">

            <h2>
                কৃষির ভবিষ্যৎ গড়ি একসাথে
            </h2>

            <p>
                আপনি একজন কৃষক হন অথবা একজন ক্রেতা—
                Farmer Direct Market-এর সাথে যুক্ত হয়ে
                একটি স্বচ্ছ ও শক্তিশালী কৃষি বাজার গড়ে তুলতে
                আমাদের সাথে এগিয়ে আসুন।
            </p>

            <a href="products.php" class="cta-btn">
                <i class="fas fa-arrow-right"></i>
                এখনই পণ্য দেখুন
            </a>

        </div>

    </section>


</main>


<!-- =====================================================
     FOOTER
===================================================== -->

<footer>

    <div class="footer-grid">


        <div class="footer-brand">

            <h3>
                🌾 FarmerDirect
            </h3>

            <p>
                কৃষক ও ক্রেতার মধ্যে সরাসরি সংযোগ তৈরি করে
                একটি স্বচ্ছ, ন্যায্য এবং আধুনিক কৃষি বাজার
                গড়ে তোলাই আমাদের লক্ষ্য।
            </p>

        </div>


        <div class="footer-column">

            <h4>
                Quick Links
            </h4>

            <a href="index.php">
                Home
            </a>

            <a href="products.php">
                Products
            </a>

            <a href="product_price.php">
                Crop Prices
            </a>

            <a href="about.php">
                About Us
            </a>

        </div>


        <div class="footer-column">

            <h4>
                Support
            </h4>

            <a href="contact.php">
                Contact Us
            </a>

            <a href="login.php">
                Login
            </a>

            <a href="register.php">
                Register
            </a>

        </div>


        <div class="footer-column">

            <h4>
                Contact
            </h4>

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

        <p>
            © <?php echo date("Y"); ?>
            Farmer Direct Market Platform.
            All Rights Reserved.
        </p>

    </div>

</footer>


<!-- =====================================================
     GOOGLE TRANSLATE SCRIPT
===================================================== -->

<script type="text/javascript">

    function googleTranslateElementInit() {

        new google.translate.TranslateElement(
            {
                pageLanguage: 'bn',

                includedLanguages:
                    'bn,en,hi,ar,es,fr,de,zh-CN,ja,ko',

                layout:
                    google.translate.TranslateElement.InlineLayout.SIMPLE

            },
            'google_translate_element'
        );

    }

</script>


<script
    type="text/javascript"
    src="//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit">
</script>


</body>
</html>

<?php
$conn->close();
?>