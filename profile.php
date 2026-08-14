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

$user_id = (int) $_SESSION['user_id'];

$message = "";
$message_type = "";


/* =========================================================
   UPDATE PROFILE
   ========================================================= */

if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['update_profile'])
) {

    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');

    if ($name === '' || $email === '') {

        $message = "Please enter your name and email.";
        $message_type = "error";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $message = "Please enter a valid email address.";
        $message_type = "error";

    } else {

        /* Check duplicate email */

        $check_sql = "
            SELECT id
            FROM users
            WHERE email = ?
            AND id != ?
            LIMIT 1
        ";

        $check_stmt = $conn->prepare($check_sql);

        if ($check_stmt) {

            $check_stmt->bind_param(
                "si",
                $email,
                $user_id
            );

            $check_stmt->execute();

            $check_result = $check_stmt->get_result();

            if ($check_result->num_rows > 0) {

                $message = "This email is already used by another account.";
                $message_type = "error";

            } else {

                $update_sql = "
                    UPDATE users
                    SET
                        name = ?,
                        email = ?,
                        phone = ?,
                        address = ?
                    WHERE id = ?
                ";

                $update_stmt = $conn->prepare($update_sql);

                if ($update_stmt) {

                    $update_stmt->bind_param(
                        "ssssi",
                        $name,
                        $email,
                        $phone,
                        $address,
                        $user_id
                    );

                    if ($update_stmt->execute()) {

                        /* Update session name */

                        $_SESSION['user_name'] = $name;

                        $message = "Profile updated successfully.";
                        $message_type = "success";

                    } else {

                        $message = "Failed to update profile.";
                        $message_type = "error";

                    }

                    $update_stmt->close();

                } else {

                    $message = "Database error.";
                    $message_type = "error";

                }

            }

            $check_stmt->close();

        }

    }

}


/* =========================================================
   CHANGE PASSWORD
   ========================================================= */

if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['change_password'])
) {

    $current_password =
        $_POST['current_password'] ?? '';

    $new_password =
        $_POST['new_password'] ?? '';

    $confirm_password =
        $_POST['confirm_password'] ?? '';


    if (
        $current_password === '' ||
        $new_password === '' ||
        $confirm_password === ''
    ) {

        $message = "Please fill in all password fields.";
        $message_type = "error";

    } elseif (
        strlen($new_password) < 6
    ) {

        $message = "New password must be at least 6 characters.";
        $message_type = "error";

    } elseif (
        $new_password !== $confirm_password
    ) {

        $message = "New passwords do not match.";
        $message_type = "error";

    } else {

        /* Get current password */

        $password_sql = "
            SELECT password
            FROM users
            WHERE id = ?
            LIMIT 1
        ";

        $password_stmt =
            $conn->prepare($password_sql);

        if ($password_stmt) {

            $password_stmt->bind_param(
                "i",
                $user_id
            );

            $password_stmt->execute();

            $password_result =
                $password_stmt->get_result();

            $user_password =
                $password_result->fetch_assoc();

            $password_stmt->close();


            if (
                !$user_password ||
                !password_verify(
                    $current_password,
                    $user_password['password']
                )
            ) {

                $message =
                    "Current password is incorrect.";

                $message_type =
                    "error";

            } else {

                $hashed_password =
                    password_hash(
                        $new_password,
                        PASSWORD_DEFAULT
                    );


                $update_password_sql = "
                    UPDATE users
                    SET password = ?
                    WHERE id = ?
                ";


                $update_password_stmt =
                    $conn->prepare(
                        $update_password_sql
                    );


                if ($update_password_stmt) {

                    $update_password_stmt->bind_param(
                        "si",
                        $hashed_password,
                        $user_id
                    );


                    if (
                        $update_password_stmt->execute()
                    ) {

                        $message =
                            "Password changed successfully.";

                        $message_type =
                            "success";

                    } else {

                        $message =
                            "Failed to change password.";

                        $message_type =
                            "error";

                    }


                    $update_password_stmt->close();

                }

            }

        }

    }

}


/* =========================================================
   GET USER PROFILE
   ========================================================= */

$user_sql = "
    SELECT
        id,
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

if (!$user_stmt) {
    die("Database Query Error: " . $conn->error);
}

$user_stmt->bind_param(
    "i",
    $user_id
);

$user_stmt->execute();

$user_result =
    $user_stmt->get_result();

$user =
    $user_result->fetch_assoc();

$user_stmt->close();


if (!$user) {

    session_destroy();

    header("Location: login.php");

    exit();

}


/* =========================================================
   USER DATA
   ========================================================= */

$user_name =
    $user['name'] ?? 'Buyer';

$user_email =
    $user['email'] ?? '';

$user_phone =
    $user['phone'] ?? '';

$user_address =
    $user['address'] ?? '';


$avatar =
    strtoupper(
        mb_substr(
            $user_name,
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
    My Profile | Farmer Direct Market
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
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: 'Poppins', sans-serif;
}


/* =========================================================
   BODY
   ========================================================= */

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

.profile-mini {
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
   PROFILE GRID
   ========================================================= */

.profile-grid {
    display: grid;
    grid-template-columns: 1.4fr 1fr;
    gap: 25px;
    align-items: start;
}


/* =========================================================
   CARD
   ========================================================= */

.card {
    background: white;
    padding: 25px;
    border-radius: 15px;
    box-shadow: 0 4px 15px rgba(0,0,0,.08);
}

.card h2 {
    margin-bottom: 20px;
}

.card-header {
    display: flex;
    align-items: center;
    gap: 15px;
    margin-bottom: 25px;
}

.big-avatar {
    width: 75px;
    height: 75px;
    border-radius: 50%;
    background: #0f9d58;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 28px;
    font-weight: 700;
}

.card-header p {
    color: #777;
    font-size: 13px;
}


/* =========================================================
   FORM
   ========================================================= */

.form-group {
    margin-bottom: 18px;
}

.form-group label {
    display: block;
    font-size: 13px;
    font-weight: 600;
    color: #555;
    margin-bottom: 7px;
}

.form-group input,
.form-group textarea {
    width: 100%;
    padding: 12px 15px;
    border: 1px solid #ddd;
    border-radius: 9px;
    outline: none;
    background: #fafafa;
    transition: .3s;
}

.form-group input:focus,
.form-group textarea:focus {
    border-color: #0f9d58;
    box-shadow: 0 0 0 3px rgba(15,157,88,.1);
}

.form-group textarea {
    resize: vertical;
}


/* =========================================================
   BUTTON
   ========================================================= */

.btn {
    width: 100%;
    border: none;
    background: #0f9d58;
    color: white;
    padding: 13px;
    border-radius: 9px;
    font-size: 15px;
    font-weight: 600;
    cursor: pointer;
    transition: .3s;
}

.btn:hover {
    background: #0b8043;
    transform: translateY(-2px);
}


/* =========================================================
   PASSWORD CARD
   ========================================================= */

.password-card {
    margin-top: 25px;
}

.password-title {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 20px;
}

.password-title i {
    color: #0f9d58;
}


/* =========================================================
   MESSAGE
   ========================================================= */

.message {
    padding: 14px 18px;
    border-radius: 10px;
    margin-bottom: 20px;
}

.message.success {
    background: #d4edda;
    color: #155724;
}

.message.error {
    background: #f8d7da;
    color: #842029;
}


/* =========================================================
   ACCOUNT INFO
   ========================================================= */

.info-item {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 15px 0;
    border-bottom: 1px solid #eee;
}

.info-item:last-child {
    border-bottom: none;
}

.info-icon {
    width: 42px;
    height: 42px;
    border-radius: 10px;
    background: #e9f8f0;
    color: #0f9d58;
    display: flex;
    align-items: center;
    justify-content: center;
}

.info-item small {
    color: #888;
    display: block;
    margin-bottom: 3px;
}

.info-item strong {
    word-break: break-word;
}


/* =========================================================
   LOGOUT
   ========================================================= */

.logout-btn {
    display: block;
    text-align: center;
    margin-top: 20px;
    padding: 12px;
    border-radius: 9px;
    color: #dc3545;
    border: 1px solid #dc3545;
    text-decoration: none;
    transition: .3s;
}

.logout-btn:hover {
    background: #dc3545;
    color: white;
}


/* =========================================================
   RESPONSIVE
   ========================================================= */

@media (max-width: 900px) {

    .profile-grid {
        grid-template-columns: 1fr;
    }

}

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

        <a href="buyer-dashboard.php">
            <i class="fas fa-chart-line"></i>
            Dashboard
        </a>

        <a href="products.php">
            <i class="fas fa-store"></i>
            Browse Products
        </a>

        <a href="cart.php">
            <i class="fas fa-shopping-cart"></i>
            My Cart
        </a>

        <a href="orders.php">
            <i class="fas fa-box"></i>
            My Orders
        </a>

        <a href="wishlist.php">
            <i class="fas fa-heart"></i>
            Wishlist
        </a>

        <a href="profile.php" class="active">
            <i class="fas fa-user"></i>
            Profile
        </a>

        <a href="logout.php">
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
            My Profile 👤
        </h1>

        <p>
            Manage your account information and security.
        </p>

    </div>


    <div class="profile-mini">

        <div class="avatar">
            <?= htmlspecialchars($avatar) ?>
        </div>

        <div>

            <strong>
                <?= htmlspecialchars($user_name) ?>
            </strong>

            <p>
                Buyer Account
            </p>

        </div>

    </div>

</div>


<?php if ($message !== ""): ?>

    <div class="message <?= htmlspecialchars($message_type) ?>">

        <?= htmlspecialchars($message) ?>

    </div>

<?php endif; ?>


<div class="profile-grid">


<!-- =====================================================
     LEFT COLUMN
     ===================================================== -->

<div>


<!-- PROFILE EDIT -->

<div class="card">

    <div class="card-header">

        <div class="big-avatar">
            <?= htmlspecialchars($avatar) ?>
        </div>

        <div>

            <h2>
                Personal Information
            </h2>

            <p>
                Update your personal details.
            </p>

        </div>

    </div>


    <form method="POST">

        <div class="form-group">

            <label>
                Full Name
            </label>

            <input
                type="text"
                name="name"
                value="<?= htmlspecialchars($user_name) ?>"
                required
            >

        </div>


        <div class="form-group">

            <label>
                Email Address
            </label>

            <input
                type="email"
                name="email"
                value="<?= htmlspecialchars($user_email) ?>"
                required
            >

        </div>


        <div class="form-group">

            <label>
                Phone Number
            </label>

            <input
                type="text"
                name="phone"
                value="<?= htmlspecialchars($user_phone) ?>"
                placeholder="Enter your phone number"
            >

        </div>


        <div class="form-group">

            <label>
                Delivery Address
            </label>

            <textarea
                name="address"
                rows="4"
                placeholder="Enter your delivery address"
            ><?= htmlspecialchars($user_address) ?></textarea>

        </div>


        <button
            type="submit"
            name="update_profile"
            class="btn"
        >

            <i class="fas fa-save"></i>

            Save Changes

        </button>

    </form>

</div>


<!-- CHANGE PASSWORD -->

<div class="card password-card">

    <div class="password-title">

        <i class="fas fa-lock"></i>

        <h2>
            Change Password
        </h2>

    </div>


    <form method="POST">

        <div class="form-group">

            <label>
                Current Password
            </label>

            <input
                type="password"
                name="current_password"
                required
            >

        </div>


        <div class="form-group">

            <label>
                New Password
            </label>

            <input
                type="password"
                name="new_password"
                minlength="6"
                required
            >

        </div>


        <div class="form-group">

            <label>
                Confirm New Password
            </label>

            <input
                type="password"
                name="confirm_password"
                minlength="6"
                required
            >

        </div>


        <button
            type="submit"
            name="change_password"
            class="btn"
        >

            <i class="fas fa-key"></i>

            Change Password

        </button>

    </form>

</div>


</div>


<!-- =====================================================
     RIGHT COLUMN
     ===================================================== -->

<div>


<div class="card">

    <h2>
        Account Information
    </h2>


    <div class="info-item">

        <div class="info-icon">
            <i class="fas fa-user"></i>
        </div>

        <div>

            <small>
                Account Name
            </small>

            <strong>
                <?= htmlspecialchars($user_name) ?>
            </strong>

        </div>

    </div>


    <div class="info-item">

        <div class="info-icon">
            <i class="fas fa-envelope"></i>
        </div>

        <div>

            <small>
                Email Address
            </small>

            <strong>
                <?= htmlspecialchars($user_email ?: 'Not added') ?>
            </strong>

        </div>

    </div>


    <div class="info-item">

        <div class="info-icon">
            <i class="fas fa-phone"></i>
        </div>

        <div>

            <small>
                Phone Number
            </small>

            <strong>
                <?= htmlspecialchars($user_phone ?: 'Not added') ?>
            </strong>

        </div>

    </div>


    <div class="info-item">

        <div class="info-icon">
            <i class="fas fa-location-dot"></i>
        </div>

        <div>

            <small>
                Delivery Address
            </small>

            <strong>
                <?= htmlspecialchars($user_address ?: 'Not added') ?>
            </strong>

        </div>

    </div>


    <div class="info-item">

        <div class="info-icon">
            <i class="fas fa-user-tag"></i>
        </div>

        <div>

            <small>
                Account Type
            </small>

            <strong>
                Buyer
            </strong>

        </div>

    </div>


    <a
        href="logout.php"
        class="logout-btn"
        onclick="return confirm('Are you sure you want to logout?');"
    >

        <i class="fas fa-sign-out-alt"></i>

        Logout from Account

    </a>


</div>


</div>


</div>


</div>


</body>

</html>

<?php

$conn->close();

?>