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
    $full_name = trim($_POST['fullName']);
    $user_type = trim($_POST['userType']);
    $email     = trim($_POST['email']);
    $phone     = trim($_POST['phone']);
    $address   = trim($_POST['address']);
    $password  = $_POST['password'];
    $confirmPassword = $_POST['confirmPassword'];

    $farm_name  = isset($_POST['farmName']) ? trim($_POST['farmName']) : NULL;
    $farm_type  = isset($_POST['farmType']) ? trim($_POST['farmType']) : NULL;
    $admin_code = isset($_POST['adminCode']) ? trim($_POST['adminCode']) : NULL;

    // Basic validation
    if ($password !== $confirmPassword) {
        $message = "Passwords do not match!";
    } elseif (strlen($password) < 8) {
        $message = "Password must be at least 8 characters long!";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Please enter a valid email address!";
    } else {
        // Admin code check
        if ($user_type === 'admin') {
            $result = mysqli_query($conn, "SELECT admin_code FROM admin_settings WHERE id=1");
            $row = mysqli_fetch_assoc($result);
            if (!$row || $admin_code !== $row['admin_code']) {
                $message = "Invalid Admin Security Code!";
            }
        }

        if (empty($message)) {
            // Check if email already exists
            $check_stmt = mysqli_prepare($conn, "SELECT id FROM users WHERE email = ?");
            mysqli_stmt_bind_param($check_stmt, "s", $email);
            mysqli_stmt_execute($check_stmt);
            mysqli_stmt_store_result($check_stmt);

            if (mysqli_stmt_num_rows($check_stmt) > 0) {
                $message = "Email already registered!";
            } else {
                $hashed = password_hash($password, PASSWORD_DEFAULT);

                // Main Users Table Insert
                $sql = "INSERT INTO users 
                        (name, full_name, user_type, email, phone, address, password, farm_name, farm_type, admin_code) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                
                $stmt = mysqli_prepare($conn, $sql);
                mysqli_stmt_bind_param($stmt, "ssssssssss", 
                    $full_name, $full_name, $user_type, $email, $phone, $address, 
                    $hashed, $farm_name, $farm_type, $admin_code
                );

                if (mysqli_stmt_execute($stmt)) {
                    $user_id = mysqli_insert_id($conn);

                    // Insert into farmers table (with duplicate protection)
                    if ($user_type === 'farmer') {
                        $farmer_sql = "INSERT IGNORE INTO farmers (id, name, phone, location) 
                                       VALUES (?, ?, ?, ?)";
                        $farmer_stmt = mysqli_prepare($conn, $farmer_sql);
                        mysqli_stmt_bind_param($farmer_stmt, "isss", 
                            $user_id, $full_name, $phone, $address);
                        mysqli_stmt_execute($farmer_stmt);
                        mysqli_stmt_close($farmer_stmt);
                    }

                    // Auto login after registration
                    $_SESSION['user_id']   = $user_id;
                    $_SESSION['full_name'] = $full_name;
                    $_SESSION['email']     = $email;
                    $_SESSION['user_type'] = $user_type;
                    $_SESSION['phone']     = $phone;

                    // Redirect according to user type
                    if ($user_type === 'admin') {
                        $redirect = "admin-dashboard.php";
                    } elseif ($user_type === 'farmer') {
                        $redirect = "farmer-dashboard.php";
                    } else {
                        $redirect = "buyer-dashboard.php";
                    }

                    mysqli_stmt_close($stmt);
                    mysqli_stmt_close($check_stmt);
                    mysqli_close($conn);
                    header("Location: " . $redirect);
                    exit();
                } else {
                    $message = "Registration failed! Please try again.";
                }
                mysqli_stmt_close($stmt);
            }
            mysqli_stmt_close($check_stmt);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Register - Farmer Direct Market</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

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
    padding:40px 20px;
    background: linear-gradient(rgba(7,25,17,.55),rgba(7,25,17,.55)),
                url('https://images.unsplash.com/photo-1500937386664-56d1dfef3854?auto=format&fit=crop&w=1600&q=80');
    background-size:cover;
    background-position:center;
}
.register-container{
    width:100%;
    max-width:950px;
    backdrop-filter:blur(18px);
    background:rgba(255,255,255,.12);
    border:1px solid rgba(255,255,255,.2);
    border-radius:30px;
    padding:45px;
    box-shadow:0 25px 60px rgba(0,0,0,.35), inset 0 1px 0 rgba(255,255,255,.15);
}
.header{
    text-align:center;
    margin-bottom:35px;
}
.header h1{
    color:#fff;
    font-size:38px;
    font-weight:700;
}
.header p{
    color:rgba(255,255,255,.85);
    margin-top:8px;
}
.form-grid{
    display:grid;
    grid-template-columns:repeat(2,1fr);
    gap:22px;
}
.full{ grid-column:1/-1; }
label{
    display:block;
    margin-bottom:8px;
    color:#fff;
    font-size:14px;
    font-weight:500;
}
.input-box{
    position:relative;
}
.input-box i{
    position:absolute;
    left:18px;
    top:50%;
    transform:translateY(-50%);
    color:#6bff9f;
    font-size:15px;
}
.input-box input,
.input-box select,
.input-box textarea{
    width:100%;
    border:none;
    outline:none;
    padding:16px 18px 16px 50px;
    border-radius:16px;
    background:rgba(255,255,255,.08);
    color:#fff;
    font-size:14px;
    border:1px solid rgba(255,255,255,.15);
}
.input-box textarea{
    height:120px;
    resize:none;
}
.input-box input:focus,
.input-box select:focus,
.input-box textarea:focus{
    border-color:#38ef7d;
    box-shadow:0 0 0 4px rgba(56,239,125,.15);
}

/* ===== Select Account Type color fix ===== */
.input-box select {
    appearance: none;
    -webkit-appearance: none;
    -moz-appearance: none;
    cursor: pointer;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%236bff9f' d='M6 8L1 3h10z'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 18px center;
    padding-right: 45px;
}
.input-box select option {
    background: #0d2818;
    color: #ffffff;
    padding: 12px;
}
.input-box select option:checked,
.input-box select option:hover {
    background: #00c853 !important;
    color: #ffffff;
}
/* ======================================== */

#farmerFields,
#adminFields{
    background:rgba(255,255,255,.06);
    border:1px solid rgba(255,255,255,.12);
    border-radius:22px;
    padding:25px;
    margin-top:5px;
}
.register-btn{
    width:100%;
    margin-top:28px;
    padding:18px;
    border:none;
    border-radius:18px;
    background:linear-gradient(135deg,#00c853,#00e676);
    color:#fff;
    font-size:16px;
    font-weight:700;
    cursor:pointer;
    transition: transform 0.2s ease;
}
.register-btn:hover{
    transform:translateY(-4px);
}
.login-link{
    text-align:center;
    margin-top:22px;
    color:rgba(255,255,255,.9);
}
.login-link a{
    color:#6bff9f;
    text-decoration:none;
    font-weight:600;
}
.alert{
    padding:15px;
    margin-bottom:20px;
    border-radius:12px;
    text-align:center;
}
.success { background:rgba(0,200,83,.9); color:white; }
.error { background:rgba(220,50,50,.9); color:white; }
</style>
</head>
<body>

<div class="register-container">
    <div class="header">
        <h1>🌾 Farmer Direct Market</h1>
        <p>Create Your Account</p>
    </div>

    <?php if($message): ?>
        <div class="alert <?= $success ? 'success' : 'error' ?>">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="register.php">
        <div class="form-grid">
            <div class="form-group">
                <label for="fullName">Full Name</label>
                <div class="input-box">
                    <i class="fas fa-user"></i>
                    <input type="text" name="fullName" id="fullName" placeholder="Enter Full Name" required>
                </div>
            </div>
            <div class="form-group">
                <label for="userType">Account Type</label>
                <div class="input-box">
                    <i class="fas fa-user-tag"></i>
                    <select name="userType" id="userType" required>
                        <option value="">Select Account Type</option>
                        <option value="buyer">Buyer</option>
                        <option value="farmer">Farmer</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label for="email">Email Address</label>
                <div class="input-box">
                    <i class="fas fa-envelope"></i>
                    <input type="email" name="email" id="email" placeholder="Enter Email Address" required>
                </div>
            </div>
            <div class="form-group">
                <label for="phone">Phone Number</label>
                <div class="input-box">
                    <i class="fas fa-phone"></i>
                    <input type="tel" name="phone" id="phone" placeholder="Enter Phone Number" required>
                </div>
            </div>
            <div class="form-group full">
                <label for="address">Address</label>
                <div class="input-box">
                    <i class="fas fa-location-dot"></i>
                    <textarea name="address" id="address" placeholder="Enter Full Address" required></textarea>
                </div>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <div class="input-box">
                    <i class="fas fa-lock"></i>
                    <input type="password" name="password" id="password" placeholder="Create Password (min 8 characters)" required minlength="8">
                </div>
            </div>
            <div class="form-group">
                <label for="confirmPassword">Confirm Password</label>
                <div class="input-box">
                    <i class="fas fa-lock"></i>
                    <input type="password" name="confirmPassword" id="confirmPassword" placeholder="Confirm Password" required minlength="8">
                </div>
            </div>

            <div id="farmerFields" class="full">
                <div class="form-group">
                    <label for="farmName">Farm Name</label>
                    <div class="input-box">
                        <i class="fas fa-seedling"></i>
                        <input type="text" name="farmName" id="farmName" placeholder="Enter Farm Name">
                    </div>
                </div>
                <div class="form-group">
                    <label for="farmType">Farm Type</label>
                    <div class="input-box">
                        <i class="fas fa-leaf"></i>
                        <select name="farmType" id="farmType">
                            <option value="">Select Farm Type</option>
                            <option>Vegetables</option>
                            <option>Fruits</option>
                            <option>Dairy</option>
                            <option>Fishery</option>
                            <option>Poultry</option>
                        </select>
                    </div>
                </div>
            </div>

            <div id="adminFields" class="full">
                <div class="form-group">
                    <label for="adminCode">Admin Security Code</label>
                    <div class="input-box">
                        <i class="fas fa-shield-halved"></i>
                        <input type="password" name="adminCode" id="adminCode" placeholder="Enter Admin Code">
                    </div>
                </div>
            </div>
        </div>

        <button type="submit" class="register-btn">Create Account</button>
    </form>

    <div class="login-link">
        Already have an account? 
        <a href="login.php">Login Here</a>
    </div>
</div>

<script>
const userType = document.getElementById('userType');
const farmerFields = document.getElementById('farmerFields');
const adminFields = document.getElementById('adminFields');

farmerFields.style.display = 'none';
adminFields.style.display = 'none';

userType.addEventListener('change', function() {
    farmerFields.style.display = 'none';
    adminFields.style.display = 'none';
    if (this.value === 'farmer') farmerFields.style.display = 'block';
    if (this.value === 'admin') adminFields.style.display = 'block';
});
</script>

</body>
</html>

<?php mysqli_close($conn); ?>