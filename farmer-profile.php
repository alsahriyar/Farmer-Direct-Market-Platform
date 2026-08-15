
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

$message = "";
$message_type = "";


/* =========================================================
   UPDATE PROFILE
   ========================================================= */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name = trim(
        $_POST['name'] ?? ''
    );

    $email = trim(
        $_POST['email'] ?? ''
    );

    $phone = trim(
        $_POST['phone'] ?? ''
    );

    $address = trim(
        $_POST['address'] ?? ''
    );


    /* -----------------------------------------
       BASIC VALIDATION
       ----------------------------------------- */

    if (
        empty($name) ||
        empty($email)
    ) {

        $message =
            "Name and Email are required.";

        $message_type =
            "error";

    } elseif (
        !filter_var(
            $email,
            FILTER_VALIDATE_EMAIL
        )
    ) {

        $message =
            "Please enter a valid email address.";

        $message_type =
            "error";

    } else {


        /* -----------------------------------------
           CHECK EMAIL USED BY ANOTHER USER
           ----------------------------------------- */

        $email_check = $conn->prepare("

            SELECT id

            FROM users

            WHERE email = ?

            AND id != ?

            LIMIT 1

        ");

        $email_check->bind_param(
            "si",
            $email,
            $farmer_id
        );

        $email_check->execute();

        $email_result =
            $email_check->get_result();


        if (
            $email_result->num_rows > 0
        ) {

            $message =
                "This email is already used by another account.";

            $message_type =
                "error";

        } else {


            /* -----------------------------------------
               CURRENT PROFILE IMAGE
               ----------------------------------------- */

            $image_sql = "";

            $image_path = "";


            if (
                isset(
                    $_FILES['profile_image']
                ) &&
                $_FILES[
                    'profile_image'
                ]['error'] === UPLOAD_ERR_OK
            ) {


                $file =
                    $_FILES[
                        'profile_image'
                    ];


                $allowed_types = [

                    'image/jpeg',
                    'image/png',
                    'image/jpg',
                    'image/webp'

                ];


                if (
                    !in_array(
                        $file['type'],
                        $allowed_types
                    )
                ) {

                    $message =
                        "Only JPG, PNG and WEBP images are allowed.";

                    $message_type =
                        "error";

                } elseif (
                    $file['size'] >
                    5 * 1024 * 1024
                ) {

                    $message =
                        "Profile image must be less than 5MB.";

                    $message_type =
                        "error";

                } else {


                    $upload_dir =
                        "uploads/profiles/";


                    if (
                        !is_dir(
                            $upload_dir
                        )
                    ) {

                        mkdir(
                            $upload_dir,
                            0777,
                            true
                        );

                    }


                    $extension =
                        strtolower(
                            pathinfo(
                                $file['name'],
                                PATHINFO_EXTENSION
                            )
                        );


                    $new_name =
                        "farmer_" .
                        $farmer_id .
                        "_" .
                        time() .
                        "." .
                        $extension;


                    $image_path =
                        $upload_dir .
                        $new_name;


                    if (
                        move_uploaded_file(
                            $file['tmp_name'],
                            $image_path
                        )
                    ) {

                        $image_sql = ",
                            profile_image = ?
                        ";

                    } else {

                        $message =
                            "Failed to upload profile image.";

                        $message_type =
                            "error";

                    }

                }

            }


            /* -----------------------------------------
               UPDATE USER
               ----------------------------------------- */

            if (
                empty($message)
            ) {


                if (
                    !empty(
                        $image_path
                    )
                ) {


                    $update_sql = "

                        UPDATE users

                        SET
                            name = ?,
                            email = ?,
                            phone = ?,
                            address = ?,
                            profile_image = ?

                        WHERE id = ?

                        AND user_type = 'farmer'

                    ";


                    $update_stmt =
                        $conn->prepare(
                            $update_sql
                        );


                    $update_stmt->bind_param(
                        "sssssi",
                        $name,
                        $email,
                        $phone,
                        $address,
                        $image_path,
                        $farmer_id
                    );


                } else {


                    $update_sql = "

                        UPDATE users

                        SET
                            name = ?,
                            email = ?,
                            phone = ?,
                            address = ?

                        WHERE id = ?

                        AND user_type = 'farmer'

                    ";


                    $update_stmt =
                        $conn->prepare(
                            $update_sql
                        );


                    $update_stmt->bind_param(
                        "ssssi",
                        $name,
                        $email,
                        $phone,
                        $address,
                        $farmer_id
                    );

                }


                if (
                    $update_stmt->execute()
                ) {

                    $_SESSION[
                        'user_name'
                    ] =
                        $name;


                    $message =
                        "Profile updated successfully!";

                    $message_type =
                        "success";

                } else {

                    $message =
                        "Failed to update profile: " .
                        $update_stmt->error;

                    $message_type =
                        "error";

                }


                $update_stmt->close();

            }

        }


        $email_check->close();

    }

}


/* =========================================================
   GET FARMER PROFILE
   ========================================================= */

$profile_stmt =
    $conn->prepare("

        SELECT

            id,
            name,
            email,
            phone,
            address,
            profile_image,
            user_type

        FROM users

        WHERE id = ?

        AND user_type = 'farmer'

        LIMIT 1

    ");


$profile_stmt->bind_param(
    "i",
    $farmer_id
);


$profile_stmt->execute();


$profile_result =
    $profile_stmt->get_result();


$farmer =
    $profile_result->fetch_assoc();


$profile_stmt->close();


if (!$farmer) {

    session_destroy();

    header(
        "Location: login.php"
    );

    exit();

}


/* =========================================================
   FARMER STATISTICS
   ========================================================= */

$product_count = 0;

$stmt = $conn->prepare("

    SELECT COUNT(*) AS total

    FROM products

    WHERE farmer_id = ?

");

$stmt->bind_param(
    "i",
    $farmer_id
);

$stmt->execute();

$result =
    $stmt->get_result();

$row =
    $result->fetch_assoc();

$product_count =
    (int) (
        $row['total']
        ?? 0
    );

$stmt->close();


/* =========================================================
   ORDER COUNT
   ========================================================= */

$total_orders = 0;

$stmt = $conn->prepare("

    SELECT
        COUNT(DISTINCT o.id) AS total

    FROM orders o

    INNER JOIN order_items oi
        ON o.id = oi.order_id

    INNER JOIN products p
        ON oi.product_id = p.id

    WHERE p.farmer_id = ?

");

$stmt->bind_param(
    "i",
    $farmer_id
);

$stmt->execute();

$result =
    $stmt->get_result();

$row =
    $result->fetch_assoc();

$total_orders =
    (int) (
        $row['total']
        ?? 0
    );

$stmt->close();


/* =========================================================
   TOTAL EARNINGS
   ========================================================= */

$total_earnings = 0;

$stmt = $conn->prepare("

    SELECT

        COALESCE(
            SUM(
                oi.quantity *
                oi.price
            ),
            0
        ) AS total

    FROM orders o

    INNER JOIN order_items oi
        ON o.id = oi.order_id

    INNER JOIN products p
        ON oi.product_id = p.id

    WHERE p.farmer_id = ?

    AND o.status = 'Delivered'

");

$stmt->bind_param(
    "i",
    $farmer_id
);

$stmt->execute();

$result =
    $stmt->get_result();

$row =
    $result->fetch_assoc();

$total_earnings =
    (float) (
        $row['total']
        ?? 0
    );

$stmt->close();


/* =========================================================
   PROFILE IMAGE
   ========================================================= */

$profile_image =
    $farmer[
        'profile_image'
    ]
    ?? '';

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
    Farmer Profile | Farmer Direct Market
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

    background:
        linear-gradient(
            135deg,
            #f5f7fa,
            #eef8f2
        );

    color: #1f2937;

    min-height: 100vh;

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

    background:
        linear-gradient(
            180deg,
            #0f9d58,
            #087f45
        );

    color: white;

    padding: 25px 18px;

    box-shadow:
        5px 0 25px
        rgba(0,0,0,.08);

    z-index: 1000;

}


.logo {

    font-size: 23px;

    font-weight: 700;

    padding:
        5px 10px 25px;

    margin-bottom: 20px;

    border-bottom:
        1px solid
        rgba(255,255,255,.18);

}


.menu a {

    display: flex;

    align-items: center;

    gap: 12px;

    color: white;

    text-decoration: none;

    padding:
        13px 15px;

    margin-bottom: 7px;

    border-radius: 12px;

    transition:
        all .3s ease;

}


.menu a i {

    width: 22px;

    text-align: center;

}


.menu a:hover,
.menu a.active {

    background:
        rgba(255,255,255,.16);

    transform:
        translateX(4px);

}


.menu a:last-child {

    margin-top: 25px;

    background:
        rgba(255,255,255,.08);

}


/* =========================================================
   MAIN
   ========================================================= */

.main {

    margin-left: 260px;

    padding: 30px 35px;

    min-height: 100vh;

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


.topbar h1 {

    font-size: 28px;

}


.topbar p {

    color: #7b8794;

    margin-top: 5px;

}


.top-profile {

    display: flex;

    align-items: center;

    gap: 12px;

    background: white;

    padding:
        8px 15px 8px 8px;

    border-radius: 50px;

    box-shadow:
        0 5px 20px
        rgba(0,0,0,.06);

}


.top-avatar {

    width: 45px;

    height: 45px;

    border-radius: 50%;

    object-fit: cover;

    background:
        linear-gradient(
            135deg,
            #0f9d58,
            #34c77b
        );

    color: white;

    display: flex;

    justify-content: center;

    align-items: center;

    font-weight: 700;

}


/* =========================================================
   PROFILE HERO
   ========================================================= */

.profile-hero {

    background:
        linear-gradient(
            135deg,
            #0f9d58,
            #087f45
        );

    border-radius: 22px;

    padding: 35px;

    color: white;

    display: flex;

    justify-content:
        space-between;

    align-items: center;

    gap: 25px;

    box-shadow:
        0 15px 35px
        rgba(15,157,88,.22);

    position: relative;

    overflow: hidden;

}


.profile-hero::after {

    content: "";

    position: absolute;

    width: 250px;

    height: 250px;

    border-radius: 50%;

    background:
        rgba(255,255,255,.07);

    right: -80px;

    top: -100px;

}


.hero-left {

    display: flex;

    align-items: center;

    gap: 25px;

    position: relative;

    z-index: 2;

}


.big-avatar {

    width: 110px;

    height: 110px;

    border-radius: 50%;

    object-fit: cover;

    border:
        5px solid
        rgba(255,255,255,.75);

    background:
        rgba(255,255,255,.15);

    display: flex;

    justify-content: center;

    align-items: center;

    font-size: 40px;

    font-weight: 700;

}


.hero-info h2 {

    font-size: 26px;

    margin-bottom: 5px;

}


.hero-info p {

    color:
        rgba(255,255,255,.8);

    font-size: 14px;

}


.verified {

    display: inline-flex;

    align-items: center;

    gap: 6px;

    margin-top: 10px;

    background:
        rgba(255,255,255,.15);

    padding:
        6px 12px;

    border-radius: 20px;

    font-size: 12px;

}


.hero-badge {

    position: relative;

    z-index: 2;

    background:
        rgba(255,255,255,.12);

    padding:
        15px 20px;

    border-radius: 15px;

    text-align: center;

}


.hero-badge i {

    font-size: 24px;

    margin-bottom: 5px;

}


.hero-badge strong {

    display: block;

    font-size: 14px;

}


/* =========================================================
   CONTENT GRID
   ========================================================= */

.content-grid {

    display: grid;

    grid-template-columns:
        1.7fr
        1fr;

    gap: 25px;

    margin-top: 25px;

}


/* =========================================================
   CARD
   ========================================================= */

.card {

    background: white;

    border-radius: 20px;

    padding: 28px;

    box-shadow:
        0 8px 25px
        rgba(0,0,0,.06);

    border:
        1px solid
        rgba(0,0,0,.03);

}


.card-header {

    display: flex;

    justify-content:
        space-between;

    align-items: center;

    margin-bottom: 25px;

}


.card-header h2 {

    font-size: 19px;

}


.card-header i {

    color:
        #0f9d58;

    font-size: 20px;

}


/* =========================================================
   FORM
   ========================================================= */

.form-group {

    margin-bottom: 20px;

}


.form-group label {

    display: block;

    margin-bottom: 8px;

    font-size: 13px;

    font-weight: 600;

    color: #555;

}


.form-input {

    width: 100%;

    padding:
        13px 15px;

    border:
        1px solid
        #e3e7eb;

    border-radius:
        11px;

    outline: none;

    font-size: 13px;

    transition:
        all .3s ease;

    background:
        #fafcfd;

}


.form-input:focus {

    border-color:
        #0f9d58;

    background: white;

    box-shadow:
        0 0 0 4px
        rgba(15,157,88,.08);

}


textarea.form-input {

    resize: vertical;

    min-height: 100px;

}


.form-row {

    display: grid;

    grid-template-columns:
        1fr 1fr;

    gap: 18px;

}


.email-note {

    font-size: 11px;

    color:
        #999;

    margin-top: 5px;

}


/* =========================================================
   PROFILE UPLOAD
   ========================================================= */

.upload-area {

    text-align: center;

    padding:
        10px 0 20px;

}


.upload-preview {

    width: 120px;

    height: 120px;

    margin:
        0 auto 15px;

    border-radius: 50%;

    overflow: hidden;

    background:
        #e8f7ef;

    border:
        4px solid
        #e8f7ef;

    display: flex;

    align-items: center;

    justify-content: center;

    color:
        #0f9d58;

    font-size: 40px;

}


.upload-preview img {

    width: 100%;

    height: 100%;

    object-fit: cover;

}


.file-label {

    display: inline-flex;

    align-items: center;

    gap: 8px;

    padding:
        10px 15px;

    border-radius: 9px;

    background:
        #e9f8f0;

    color:
        #0f9d58;

    cursor: pointer;

    font-size: 12px;

    font-weight: 600;

}


.file-label:hover {

    background:
        #d9f2e4;

}


#profile_image {

    display: none;

}


.file-info {

    margin-top: 8px;

    font-size: 10px;

    color:
        #999;

}


/* =========================================================
   BUTTON
   ========================================================= */

.save-btn {

    width: 100%;

    border: none;

    padding:
        14px;

    border-radius:
        11px;

    background:
        linear-gradient(
            135deg,
            #0f9d58,
            #087f45
        );

    color: white;

    font-size: 14px;

    font-weight: 600;

    cursor: pointer;

    transition:
        all .3s ease;

}


.save-btn:hover {

    transform:
        translateY(-2px);

    box-shadow:
        0 10px 25px
        rgba(15,157,88,.25);

}


/* =========================================================
   STATS CARD
   ========================================================= */

.stat-list {

    display: grid;

    gap: 15px;

}


.stat-item {

    display: flex;

    align-items: center;

    gap: 15px;

    padding:
        15px;

    border-radius:
        13px;

    background:
        #f8fbf9;

}


.stat-item-icon {

    width: 45px;

    height: 45px;

    border-radius:
        12px;

    display: flex;

    justify-content: center;

    align-items: center;

    background:
        #e7f7ee;

    color:
        #0f9d58;

}


.stat-item-content {

    flex: 1;

}


.stat-item-content p {

    font-size:
        11px;

    color:
        #888;

}


.stat-item-content strong {

    display: block;

    font-size:
        17px;

    margin-top:
        2px;

}


/* =========================================================
   ACCOUNT INFO
   ========================================================= */

.info-list {

    margin-top:
        25px;

}


.info-row {

    display: flex;

    align-items: center;

    gap: 12px;

    padding:
        13px 0;

    border-bottom:
        1px solid
        #f0f0f0;

}


.info-row:last-child {

    border-bottom:
        none;

}


.info-icon {

    width: 35px;

    height: 35px;

    border-radius:
        9px;

    background:
        #e9f8f0;

    color:
        #0f9d58;

    display: flex;

    align-items: center;

    justify-content: center;

}


.info-text {

    flex: 1;

}


.info-text small {

    display: block;

    color:
        #999;

    font-size:
        10px;

}


.info-text span {

    display: block;

    margin-top:
        2px;

    font-size:
        12px;

    word-break:
        break-word;

}


/* =========================================================
   MESSAGE
   ========================================================= */

.message {

    padding:
        13px 16px;

    border-radius:
        11px;

    margin-bottom:
        20px;

    font-size:
        13px;

    font-weight:
        500;

}


.message.success {

    background:
        #dff6e8;

    color:
        #16733d;

}


.message.error {

    background:
        #fde2e2;

    color:
        #a32626;

}


/* =========================================================
   RESPONSIVE
   ========================================================= */

@media (
    max-width: 1000px
) {

    .content-grid {

        grid-template-columns:
            1fr;

    }

}


@media (
    max-width: 800px
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


    .top-profile {

        width:
            100%;

    }


    .profile-hero {

        flex-direction:
            column;

        align-items:
            flex-start;

    }


    .hero-left {

        flex-direction:
            column;

        align-items:
            flex-start;

    }

}


@media (
    max-width: 600px
) {

    .form-row {

        grid-template-columns:
            1fr;

    }


    .profile-hero {

        padding:
            25px;

    }


    .hero-info h2 {

        font-size:
            22px;

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


        <a href="earnings.php">

            <i class="fas fa-wallet"></i>

            Earnings

        </a>


        <a href="crop-price-trends.php">

            <i class="fas fa-chart-bar"></i>

            Price Trends

        </a>


        <a
            href="farmer-profile.php"
            class="active"
        >

            <i class="fas fa-user"></i>

            Profile

        </a>


        <a
            href="logout.php"
            onclick="
                return confirm(
                    'Are you sure you want to logout?'
                );
            "
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

                My Profile 👨‍🌾

            </h1>


            <p>

                Manage your farmer account
                and personal information.

            </p>

        </div>


        <div class="top-profile">


            <?php if (
                !empty(
                    $profile_image
                )
            ): ?>


                <img
                    src="<?= htmlspecialchars(
                        $profile_image
                    ) ?>"
                    class="top-avatar"
                    alt="Profile"
                >


            <?php else: ?>


                <div class="top-avatar">

                    <?= htmlspecialchars(
                        strtoupper(
                            substr(
                                $farmer[
                                    'name'
                                ],
                                0,
                                1
                            )
                        )
                    ) ?>

                </div>


            <?php endif; ?>


            <div>


                <strong>

                    <?= htmlspecialchars(
                        $farmer[
                            'name'
                        ]
                    ) ?>

                </strong>


                <p>

                    Verified Farmer

                </p>


            </div>


        </div>


    </div>


    <!-- =================================================
         PROFILE HERO
         ================================================= -->

    <div class="profile-hero">


        <div class="hero-left">


            <?php if (
                !empty(
                    $profile_image
                )
            ): ?>


                <img
                    src="<?= htmlspecialchars(
                        $profile_image
                    ) ?>"
                    class="big-avatar"
                    alt="Farmer Profile"
                >


            <?php else: ?>


                <div class="big-avatar">

                    <?= htmlspecialchars(
                        strtoupper(
                            substr(
                                $farmer[
                                    'name'
                                ],
                                0,
                                1
                            )
                        )
                    ) ?>

                </div>


            <?php endif; ?>


            <div class="hero-info">


                <h2>

                    <?= htmlspecialchars(
                        $farmer[
                            'name'
                        ]
                    ) ?>

                </h2>


                <p>

                    <?= htmlspecialchars(
                        $farmer[
                            'email'
                        ]
                    ) ?>

                </p>


                <div class="verified">

                    <i class="
                        fas
                        fa-circle-check
                    "></i>

                    Verified Farmer Account

                </div>


            </div>


        </div>


        <div class="hero-badge">


            <i class="
                fas
                fa-leaf
            "></i>


            <strong>

                Farmer Direct Market

            </strong>


        </div>


    </div>


    <!-- =================================================
         MESSAGE
         ================================================= -->

    <?php if (
        !empty(
            $message
        )
    ): ?>


        <div class="
            message
            <?= $message_type ?>
        ">


            <?php if (
                $message_type ===
                'success'
            ): ?>


                <i class="
                    fas
                    fa-circle-check
                "></i>


            <?php else: ?>


                <i class="
                    fas
                    fa-circle-exclamation
                "></i>


            <?php endif; ?>


            <?= htmlspecialchars(
                $message
            ) ?>


        </div>


    <?php endif; ?>


    <!-- =================================================
         CONTENT
         ================================================= -->

    <div class="content-grid">


        <!-- LEFT: EDIT PROFILE -->

        <div class="card">


            <div class="card-header">


                <h2>

                    Edit Profile

                </h2>


                <i class="
                    fas
                    fa-user-pen
                "></i>


            </div>


            <form
                method="POST"
                enctype="multipart/form-data"
            >


                <!-- PROFILE IMAGE -->

                <div class="upload-area">


                    <div
                        class="upload-preview"
                        id="previewContainer"
                    >


                        <?php if (
                            !empty(
                                $profile_image
                            )
                        ): ?>


                            <img
                                id="previewImage"
                                src="<?= htmlspecialchars(
                                    $profile_image
                                ) ?>"
                                alt="Profile Preview"
                            >


                        <?php else: ?>


                            <i
                                id="defaultIcon"
                                class="
                                    fas
                                    fa-user
                                "
                            ></i>


                            <img
                                id="previewImage"
                                style="
                                    display:none;
                                "
                                alt="Preview"
                            >


                        <?php endif; ?>


                    </div>


                    <label
                        for="profile_image"
                        class="file-label"
                    >

                        <i class="
                            fas
                            fa-camera
                        "></i>

                        Change Profile Photo

                    </label>


                    <input
                        type="file"
                        name="profile_image"
                        id="profile_image"
                        accept="
                            image/jpeg,
                            image/png,
                            image/jpg,
                            image/webp
                        "
                        onchange="
                            previewProfile(
                                this
                            )
                        "
                    >


                    <div class="file-info">

                        JPG, PNG or WEBP
                        • Maximum 5MB

                    </div>


                </div>


                <!-- NAME -->

                <div class="form-group">


                    <label>

                        Full Name

                    </label>


                    <input
                        type="text"
                        name="name"
                        class="form-input"
                        value="<?= htmlspecialchars(
                            $farmer[
                                'name'
                            ]
                        ) ?>"
                        required
                    >


                </div>


                <!-- EMAIL -->

                <div class="form-group">


                    <label>

                        Email Address

                    </label>


                    <input
                        type="email"
                        name="email"
                        class="form-input"
                        value="<?= htmlspecialchars(
                            $farmer[
                                'email'
                            ]
                        ) ?>"
                        required
                    >


                    <div class="email-note">

                        Your email is used
                        for account communication.

                    </div>


                </div>


                <!-- PHONE + ADDRESS -->

                <div class="form-row">


                    <div class="form-group">


                        <label>

                            Phone Number

                        </label>


                        <input
                            type="text"
                            name="phone"
                            class="form-input"
                            placeholder="
                                Enter phone number
                            "
                            value="<?= htmlspecialchars(
                                $farmer[
                                    'phone'
                                ]
                                ??
                                ''
                            ) ?>"
                        >


                    </div>


                    <div class="form-group">


                        <label>

                            Account Type

                        </label>


                        <input
                            type="text"
                            class="form-input"
                            value="Verified Farmer"
                            readonly
                        >


                    </div>


                </div>


                <!-- ADDRESS -->

                <div class="form-group">


                    <label>

                        Address

                    </label>


                    <textarea
                        name="address"
                        class="form-input"
                        placeholder="
                            Enter your farm or home address
                        "
                    ><?= htmlspecialchars(
                        $farmer[
                            'address'
                        ]
                        ??
                        ''
                    ) ?></textarea>


                </div>


                <!-- BUTTON -->

                <button
                    type="submit"
                    class="save-btn"
                >

                    <i class="
                        fas
                        fa-save
                    "></i>

                    Save Profile Changes

                </button>


            </form>


        </div>


        <!-- RIGHT SIDE -->

        <div>


            <!-- FARMER STATISTICS -->

            <div class="card">


                <div class="card-header">


                    <h2>

                        Farmer Overview

                    </h2>


                    <i class="
                        fas
                        fa-chart-pie
                    "></i>


                </div>


                <div class="stat-list">


                    <div class="stat-item">


                        <div class="
                            stat-item-icon
                        ">

                            <i class="
                                fas
                                fa-seedling
                            "></i>

                        </div>


                        <div class="
                            stat-item-content
                        ">


                            <p>

                                Total Products

                            </p>


                            <strong>

                                <?= number_format(
                                    $product_count
                                ) ?>

                            </strong>


                        </div>


                    </div>


                    <div class="stat-item">


                        <div class="
                            stat-item-icon
                        ">

                            <i class="
                                fas
                                fa-shopping-bag
                            "></i>

                        </div>


                        <div class="
                            stat-item-content
                        ">


                            <p>

                                Total Orders

                            </p>


                            <strong>

                                <?= number_format(
                                    $total_orders
                                ) ?>

                            </strong>


                        </div>


                    </div>


                    <div class="stat-item">


                        <div class="
                            stat-item-icon
                        ">

                            <i class="
                                fas
                                fa-money-bill-wave
                            "></i>

                        </div>


                        <div class="
                            stat-item-content
                        ">


                            <p>

                                Total Earnings

                            </p>


                            <strong>

                                ৳ <?= number_format(
                                    $total_earnings,
                                    2
                                ) ?>

                            </strong>


                        </div>


                    </div>


                </div>


            </div>


            <!-- ACCOUNT INFORMATION -->

            <div
                class="card"
                style="
                    margin-top:25px;
                "
            >


                <div class="card-header">


                    <h2>

                        Account Information

                    </h2>


                    <i class="
                        fas
                        fa-shield-halved
                    "></i>


                </div>


                <div class="info-list">


                    <div class="info-row">


                        <div class="info-icon">

                            <i class="
                                fas
                                fa-envelope
                            "></i>

                        </div>


                        <div class="info-text">


                            <small>

                                Email

                            </small>


                            <span>

                                <?= htmlspecialchars(
                                    $farmer[
                                        'email'
                                    ]
                                ) ?>

                            </span>


                        </div>


                    </div>


                    <div class="info-row">


                        <div class="info-icon">

                            <i class="
                                fas
                                fa-phone
                            "></i>

                        </div>


                        <div class="info-text">


                            <small>

                                Phone

                            </small>


                            <span>

                                <?= !empty(
                                    $farmer[
                                        'phone'
                                    ]
                                )
                                ?
                                htmlspecialchars(
                                    $farmer[
                                        'phone'
                                    ]
                                )
                                :
                                'Not added yet'
                                ?>

                            </span>


                        </div>


                    </div>


                    <div class="info-row">


                        <div class="info-icon">

                            <i class="
                                fas
                                fa-location-dot
                            "></i>

                        </div>


                        <div class="info-text">


                            <small>

                                Address

                            </small>


                            <span>

                                <?= !empty(
                                    $farmer[
                                        'address'
                                    ]
                                )
                                ?
                                htmlspecialchars(
                                    $farmer[
                                        'address'
                                    ]
                                )
                                :
                                'Not added yet'
                                ?>

                            </span>


                        </div>


                    </div>


                    <div class="info-row">


                        <div class="info-icon">

                            <i class="
                                fas
                                fa-user-check
                            "></i>

                        </div>


                        <div class="info-text">


                            <small>

                                Account Status

                            </small>


                            <span>

                                Verified Farmer

                            </span>


                        </div>


                    </div>


                </div>


            </div>


        </div>


    </div>


</div>


<script>

/* =========================================================
   PROFILE IMAGE PREVIEW
   ========================================================= */

function previewProfile(
    input
) {

    const file =
        input.files[0];

    if (!file) {

        return;

    }


    const preview =
        document.getElementById(
            'previewImage'
        );


    const defaultIcon =
        document.getElementById(
            'defaultIcon'
        );


    preview.src =
        URL.createObjectURL(
            file
        );


    preview.style.display =
        'block';


    if (
        defaultIcon
    ) {

        defaultIcon.style.display =
            'none';

    }

}

</script>


</body>

</html>


<?php

$conn->close();

?>