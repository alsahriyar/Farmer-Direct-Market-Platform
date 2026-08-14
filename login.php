<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "farm_db";

$conn = mysqli_connect($servername, $username, $password, $dbname);
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
mysqli_set_charset($conn, "utf8mb4");

session_start();
$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_type = trim($_POST['userType']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $sql = "SELECT id, name, user_type, password FROM users WHERE email = ? AND user_type = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ss", $email, $user_type);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($row = mysqli_fetch_assoc($result)) {
        if (password_verify($password, $row['password'])) {
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['user_name'] = $row['name'];
            $_SESSION['user_type'] = $row['user_type'];

            if ($row['user_type'] == 'admin') {
                header("Location: admin-dashboard.php");
            } elseif ($row['user_type'] == 'farmer') {
                header("Location: farmer-dashboard.php");
            } else {
                header("Location: buyer-dashboard.php");
            }
            exit();
        } else {
            $error = "Invalid password!";
        }
    } else {
        $error = "No account found with this email and user type!";
    }
    mysqli_stmt_close($stmt);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Farmer Direct Market</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <style>
        *{
            margin:0;
            padding:0;
            box-sizing:border-box;
            font-family:'Poppins',sans-serif;
        }
        body{
            min-height:100vh;
            display:flex;
            justify-content:center;
            align-items:center;
            padding:20px;
            background: linear-gradient(rgba(0,60,20,.55),rgba(0,60,20,.55)),
                        url('https://images.pexels.com/photos/325944/pexels-photo-325944.jpeg');
            background-size:cover;
            background-position:center;
            background-repeat:no-repeat;
        }
        .login-container{
            width:100%;
            max-width:430px;
            background:#ffffff;
            border-radius:18px;
            padding:38px;
            box-shadow:0 18px 50px rgba(0,0,0,.25);
        }
        .logo{
            text-align:center;
            margin-bottom:22px;
        }
        .logo h1{
            color:#0f9d58;
            font-size:28px;
            font-weight:700;
            letter-spacing:.3px;
        }
        .logo p{
            color:#6b7280;
            font-size:13px;
            margin-top:6px;
        }
        .form-group{
            margin-bottom:15px;
        }
        label{
            display:block;
            margin-bottom:6px;
            font-size:13px;
            font-weight:600;
            color:#374151;
        }
        .input-box{
            position:relative;
        }
        .input-box i{
            position:absolute;
            left:14px;
            top:50%;
            transform:translateY(-50%);
            color:#9ca3af;
            font-size:14px;
        }
        .input-box input,
        .input-box select{
            width:100%;
            padding:12px 14px 12px 42px;
            border:1px solid #e5e7eb;
            border-radius:10px;
            font-size:14px;
            outline:none;
            transition:.25s;
            background:#fff;
        }
        .input-box input:focus,
        .input-box select:focus{
            border-color:#0f9d58;
            box-shadow:0 0 0 3px rgba(15,157,88,.18);
        }
        .options{
            display:flex;
            justify-content:space-between;
            align-items:center;
            font-size:12.5px;
            margin:14px 0 18px;
            color:#6b7280;
        }
        .options a{
            color:#0f9d58;
            text-decoration:none;
            font-weight:600;
        }
        .login-btn{
            width:100%;
            padding:13px;
            border:none;
            border-radius:10px;
            background:#0f9d58;
            color:#fff;
            font-size:15px;
            font-weight:600;
            cursor:pointer;
            transition:.25s;
        }
        .login-btn:hover{
            background:#0c7d45;
            transform:translateY(-2px);
        }
        .divider{
            text-align:center;
            margin:18px 0;
            font-size:12px;
            color:#9ca3af;
            position:relative;
        }
        .divider::before,
        .divider::after{
            content:'';
            position:absolute;
            top:50%;
            width:40%;
            height:1px;
            background:#e5e7eb;
        }
        .divider::before{ left:0; }
        .divider::after{ right:0; }
        .register-link{
            text-align:center;
            font-size:13px;
            color:#4b5563;
        }
        .register-link a{
            color:#0f9d58;
            font-weight:600;
            text-decoration:none;
        }
        .error{
            color:#dc2626;
            text-align:center;
            margin-bottom:15px;
            font-size:14px;
        }
        @media(max-width:480px){
            .login-container{
                padding:25px;
            }
            .logo h1{
                font-size:24px;
            }
        }
    </style>
</head>
<body>

<div class="login-container">

    <div class="logo">
        <h1>🌾 Farmer Direct</h1>
        <p>Login to your account</p>
    </div>

    <?php if($error): ?>
    <div class="error"><?= $error ?></div>
    <?php endif; ?>

    <form method="POST" action="login.php">

        <div class="form-group">
            <label>User Type</label>
            <div class="input-box">
                <i class="fas fa-user-tag"></i>
                <select name="userType" required>
                    <option value="">Select User Type</option>
                    <option value="farmer">Farmer</option>
                    <option value="buyer">Buyer</option>
                    <option value="admin">Admin</option>
                </select>
            </div>
        </div>

        <div class="form-group">
            <label>Email Address</label>
            <div class="input-box">
                <i class="fas fa-envelope"></i>
                <input type="email" name="email" placeholder="Enter your email" required>
            </div>
        </div>

        <div class="form-group">
            <label>Password</label>
            <div class="input-box">
                <i class="fas fa-lock"></i>
                <input type="password" name="password" placeholder="Enter your password" required>
            </div>
        </div>

        <div class="options">
            <label>
                <input type="checkbox" name="remember">
                Remember Me
            </label>
            <a href="#">Forgot Password?</a>
        </div>

        <button type="submit" class="login-btn">
            Login
        </button>

    </form>

    <div class="divider">OR</div>

    <div class="register-link">
        Don't have an account?
        <a href="register.php">Register Now</a>
    </div>

</div>

</body>
</html>

<?php mysqli_close($conn); ?>