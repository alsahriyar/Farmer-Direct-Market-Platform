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
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Handle Actions
if (isset($_POST['action']) && isset($_POST['id'])) {
    $id = (int)$_POST['id'];
    $action = $_POST['action'];

    if ($action == 'approve_user') {
        mysqli_query($conn, "UPDATE users SET status='active' WHERE id=$id");
    } elseif ($action == 'block_user') {
        mysqli_query($conn, "UPDATE users SET status='blocked' WHERE id=$id");
    } elseif ($action == 'delete_user') {
        mysqli_query($conn, "DELETE FROM users WHERE id=$id");
    } elseif ($action == 'delete_product') {
        mysqli_query($conn, "DELETE FROM products WHERE id=$id");
    }
    
    header("Location: admin-dashboard.php?section=" . ($_GET['section'] ?? 'dashboard'));
    exit();
}

// Get current section
$section = $_GET['section'] ?? 'dashboard';
?>

<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | Farmer Direct Market</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        :root {
            --primary: #10b981;
            --primary-dark: #059669;
            --sidebar-bg: #1f2937;
            --card-bg: #ffffff;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }
        
        body {
            background: linear-gradient(135deg, #f8fafc 0%, #e0f2fe 100%);
            color: #1e2937;
        }
        
        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            width: 280px;
            height: 100vh;
            background: var(--sidebar-bg);
            color: #fff;
            padding: 30px 20px;
            overflow-y: auto;
            box-shadow: 4px 0 20px rgba(0,0,0,0.1);
            z-index: 100;
        }
        
        .logo {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 50px;
            color: #fff;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .menu a {
            display: flex;
            align-items: center;
            gap: 12px;
            color: #cbd5e1;
            text-decoration: none;
            padding: 16px 20px;
            margin-bottom: 8px;
            border-radius: 12px;
            transition: all 0.3s ease;
            font-weight: 500;
        }
        
        .menu a:hover,
        .menu a.active {
            background: rgba(16, 185, 129, 0.2);
            color: #fff;
            transform: translateX(8px);
        }
        
        .main {
            margin-left: 280px;
            padding: 30px;
            min-height: 100vh;
        }
        
        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 35px;
            background: white;
            padding: 22px 30px;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
        }
        
        .admin-profile {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .avatar {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            background: linear-gradient(135deg, #10b981, #34d399);
            color: #fff;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 22px;
            font-weight: 700;
            box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3);
        }
        
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 25px;
            margin-bottom: 40px;
        }
        
        .card {
            background: var(--card-bg);
            padding: 32px 28px;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
            transition: all 0.4s ease;
        }
        
        .card:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 40px rgba(16, 185, 129, 0.15);
        }
        
        .card i {
            font-size: 42px;
            color: var(--primary);
            margin-bottom: 15px;
        }
        
        .card h2 {
            font-size: 36px;
            font-weight: 700;
            margin: 8px 0;
        }
        
        .section {
            margin-top: 40px;
        }
        
        .section h2 {
            margin-bottom: 20px;
            font-size: 26px;
            color: #1e2937;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .table-box {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th {
            background: linear-gradient(90deg, #10b981, #34d399);
            color: white;
            padding: 20px 18px;
            text-align: left;
            font-weight: 600;
        }
        
        td {
            padding: 18px;
            border-bottom: 1px solid #f1f5f9;
        }
        
        .status {
            padding: 8px 18px;
            border-radius: 50px;
            font-size: 13px;
            font-weight: 600;
        }
        
        .active { background: #d1fae5; color: #065f46; }
        .pending { background: #fef3c7; color: #92400e; }
        .blocked { background: #fee2e2; color: #991b1b; }
        
        .btn {
            border: none;
            padding: 10px 16px;
            border-radius: 10px;
            cursor: pointer;
            color: white;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.3s;
        }
        
        .approve { background: #10b981; }
        .approve:hover { background: #059669; }
        .block { background: #f59e0b; }
        .block:hover { background: #d97706; }
        .delete { background: #ef4444; }
        .delete:hover { background: #dc2626; }
        
        .action-form {
            display: inline-block;
        }
        
        .nav-link {
            text-decoration: none;
            color: inherit;
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="logo">
            <i class="fas fa-leaf"></i> FDM Admin
        </div>
        <div class="menu">
            <a href="admin-dashboard.php" class="<?= $section === 'dashboard' ? 'active' : '' ?>">
                <i class="fas fa-chart-pie"></i> ড্যাশবোর্ড
            </a>
            <a href="admin-dashboard.php?section=users" class="<?= $section === 'users' ? 'active' : '' ?>">
                <i class="fas fa-users"></i> সকল ইউজার
            </a>
            <a href="admin-dashboard.php?section=farmers" class="<?= $section === 'farmers' ? 'active' : '' ?>">
                <i class="fas fa-tractor"></i> কৃষক
            </a>
            <a href="admin-dashboard.php?section=buyers" class="<?= $section === 'buyers' ? 'active' : '' ?>">
                <i class="fas fa-store"></i> ক্রেতা
            </a>
            <a href="admin-dashboard.php?section=products" class="<?= $section === 'products' ? 'active' : '' ?>">
                <i class="fas fa-seedling"></i> প্রোডাক্ট
            </a>
            <a href="admin-dashboard.php?section=orders" class="<?= $section === 'orders' ? 'active' : '' ?>">
                <i class="fas fa-shopping-cart"></i> অর্ডার
            </a>
            <a href="admin-dashboard.php?section=reports" class="<?= $section === 'reports' ? 'active' : '' ?>">
                <i class="fas fa-chart-line"></i> রিপোর্ট
            </a>
            <a href="#"><i class="fas fa-cog"></i> সেটিংস</a>
            <a href="logout.php"><i class="fas fa-sign-out-alt"></i> লগআউট</a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main">
        <div class="topbar">
            <div>
                <h1 style="font-size: 28px;"><?= $section === 'dashboard' ? 'ড্যাশবোর্ড' : ucfirst($section) ?></h1>
                <p style="color: #64748b;">ফার্মার ডাইরেক্ট মার্কেট অ্যাডমিন প্যানেল</p>
            </div>
            <div class="admin-profile">
                <div class="avatar">A</div>
                <div>
                    <strong>সুপার অ্যাডমিন</strong>
                    <p style="color: #10b981; font-size: 14px;">পূর্ণ অ্যাক্সেস</p>
                </div>
            </div>
        </div>

        <?php if ($section === 'dashboard'): ?>
            <!-- Stats -->
            <div class="stats">
                <?php
                $total_users = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM users"))['c'];
                $total_farmers = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM users WHERE user_type='farmer'"))['c'];
                $total_buyers = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM users WHERE user_type='buyer'"))['c'];
                $total_products = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM products"))['c'] ?? 142;
                ?>
                <div class="card">
                    <i class="fas fa-users"></i>
                    <h2><?= $total_users ?></h2>
                    <p>Total Users</p>
                </div>
                <div class="card">
                    <i class="fas fa-tractor"></i>
                    <h2><?= $total_farmers ?></h2>
                    <p>Total Farmers</p>
                </div>
                <div class="card">
                    <i class="fas fa-store"></i>
                    <h2><?= $total_buyers ?></h2>
                    <p>Total Buyers</p>
                </div>
                <div class="card">
                    <i class="fas fa-seedling"></i>
                    <h2><?= $total_products ?></h2>
                    <p>Active Products</p>
                </div>
            </div>

            <!-- Recent Users -->
            <div class="section">
                <h2><i class="fas fa-user-plus"></i> সাম্প্রতিক ইউজার রেজিস্ট্রেশন</h2>
                <div class="table-box">
                    <table>
                        <tr>
                            <th>নাম</th>
                            <th>ইমেইল</th>
                            <th>রোল</th>
                            <th>স্ট্যাটাস</th>
                            <th>অ্যাকশন</th>
                        </tr>
                        <?php
                        $result = mysqli_query($conn, "SELECT id, name, email, user_type, status FROM users ORDER BY created_at DESC LIMIT 8");
                        while($row = mysqli_fetch_assoc($result)):
                        ?>
                        <tr>
                            <td><?= htmlspecialchars($row['name']) ?></td>
                            <td><?= htmlspecialchars($row['email']) ?></td>
                            <td><?= ucfirst($row['user_type']) ?></td>
                            <td><span class="status <?= $row['status'] ?>"><?= ucfirst($row['status'] ?? 'pending') ?></span></td>
                            <td>
                                <form method="POST" class="action-form">
                                    <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                    <?php if($row['status'] !== 'active'): ?>
                                        <button type="submit" name="action" value="approve_user" class="btn approve">Approve</button>
                                    <?php endif; ?>
                                    <button type="submit" name="action" value="block_user" class="btn block">Block</button>
                                    <button type="submit" name="action" value="delete_user" class="btn delete" onclick="return confirm('আপনি কি নিশ্চিত যে এই ইউজারকে ডিলিট করতে চান?')">Delete</button>
                                </form>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </table>
                </div>
            </div>

        <?php elseif ($section === 'users'): ?>
            <!-- All Users -->
            <div class="section">
                <h2><i class="fas fa-users"></i> সকল ইউজার</h2>
                <div class="table-box">
                    <table>
                        <tr>
                            <th>ID</th>
                            <th>নাম</th>
                            <th>ইমেইল</th>
                            <th>রোল</th>
                            <th>স্ট্যাটাস</th>
                            <th>অ্যাকশন</th>
                        </tr>
                        <?php
                        $result = mysqli_query($conn, "SELECT id, name, email, user_type, status FROM users ORDER BY id DESC");
                        while($row = mysqli_fetch_assoc($result)):
                        ?>
                        <tr>
                            <td><?= $row['id'] ?></td>
                            <td><?= htmlspecialchars($row['name']) ?></td>
                            <td><?= htmlspecialchars($row['email']) ?></td>
                            <td><?= ucfirst($row['user_type']) ?></td>
                            <td><span class="status <?= $row['status'] ?>"><?= ucfirst($row['status'] ?? 'pending') ?></span></td>
                            <td>
                                <form method="POST" class="action-form">
                                    <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                    <?php if($row['status'] !== 'active'): ?>
                                        <button type="submit" name="action" value="approve_user" class="btn approve">Approve</button>
                                    <?php endif; ?>
                                    <button type="submit" name="action" value="block_user" class="btn block">Block</button>
                                    <button type="submit" name="action" value="delete_user" class="btn delete" onclick="return confirm('আপনি কি নিশ্চিত?')">Delete</button>
                                </form>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </table>
                </div>
            </div>

        <?php elseif ($section === 'farmers'): ?>
            <!-- Farmers -->
            <div class="section">
                <h2><i class="fas fa-tractor"></i> সকল কৃষক</h2>
                <div class="table-box">
                    <table>
                        <tr>
                            <th>নাম</th>
                            <th>ইমেইল</th>
                            <th>স্ট্যাটাস</th>
                            <th>অ্যাকশন</th>
                        </tr>
                        <?php
                        $result = mysqli_query($conn, "SELECT id, name, email, status FROM users WHERE user_type='farmer' ORDER BY id DESC");
                        while($row = mysqli_fetch_assoc($result)):
                        ?>
                        <tr>
                            <td><?= htmlspecialchars($row['name']) ?></td>
                            <td><?= htmlspecialchars($row['email']) ?></td>
                            <td><span class="status <?= $row['status'] ?>"><?= ucfirst($row['status'] ?? 'pending') ?></span></td>
                            <td>
                                <form method="POST" class="action-form">
                                    <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                    <?php if($row['status'] !== 'active'): ?>
                                        <button type="submit" name="action" value="approve_user" class="btn approve">Approve</button>
                                    <?php endif; ?>
                                    <button type="submit" name="action" value="block_user" class="btn block">Block</button>
                                    <button type="submit" name="action" value="delete_user" class="btn delete" onclick="return confirm('আপনি কি নিশ্চিত?')">Delete</button>
                                </form>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </table>
                </div>
            </div>

        <?php elseif ($section === 'buyers'): ?>
            <!-- Buyers -->
            <div class="section">
                <h2><i class="fas fa-store"></i> সকল ক্রেতা</h2>
                <div class="table-box">
                    <table>
                        <tr>
                            <th>নাম</th>
                            <th>ইমেইল</th>
                            <th>স্ট্যাটাস</th>
                            <th>অ্যাকশন</th>
                        </tr>
                        <?php
                        $result = mysqli_query($conn, "SELECT id, name, email, status FROM users WHERE user_type='buyer' ORDER BY id DESC");
                        while($row = mysqli_fetch_assoc($result)):
                        ?>
                        <tr>
                            <td><?= htmlspecialchars($row['name']) ?></td>
                            <td><?= htmlspecialchars($row['email']) ?></td>
                            <td><span class="status <?= $row['status'] ?>"><?= ucfirst($row['status'] ?? 'pending') ?></span></td>
                            <td>
                                <form method="POST" class="action-form">
                                    <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                    <?php if($row['status'] !== 'active'): ?>
                                        <button type="submit" name="action" value="approve_user" class="btn approve">Approve</button>
                                    <?php endif; ?>
                                    <button type="submit" name="action" value="block_user" class="btn block">Block</button>
                                    <button type="submit" name="action" value="delete_user" class="btn delete" onclick="return confirm('আপনি কি নিশ্চিত?')">Delete</button>
                                </form>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </table>
                </div>
            </div>

     <?php elseif ($section === 'products'): ?>
<!-- ==================== প্রোডাক্ট ম্যানেজমেন্ট ==================== -->

<div class="section">

    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:25px;">
        <h2><i class="fas fa-seedling"></i> প্রোডাক্ট ম্যানেজমেন্ট</h2>

        <a href="add-product.php"
           class="btn"
           style="background:#10b981; padding:12px 22px; text-decoration:none; border-radius:10px;">
            <i class="fas fa-plus"></i> নতুন প্রোডাক্ট যোগ করুন
        </a>
    </div>

    <div class="table-box">

        <table>

            <thead>
                <tr>
                    <th>ID</th>
                    <th>ছবি</th>
                    <th>প্রোডাক্ট নাম</th>
                    <th>ক্যাটেগরি</th>
                    <th>দাম (৳)</th>
                    <th>ইউনিট</th>
                    <th>স্টক</th>
                    <th>লোকেশন</th>
                    <th>হার্ভেস্ট ডেট</th>
                    <th>অ্যাকশন</th>
                </tr>
            </thead>

            <tbody>

            <?php

            $prod_query = "SELECT
                                id,
                                name,
                                category,
                                price,
                                unit,
                                stock,
                                image,
                                harvest_date,
                                location,
                                farmer_id,
                                created_at
                           FROM products
                           ORDER BY id ASC";

            $prod_result = mysqli_query($conn, $prod_query);

            if($prod_result && mysqli_num_rows($prod_result) > 0):

                while($product = mysqli_fetch_assoc($prod_result)):
            ?>

                <tr>

                    <td><?= $product['id']; ?></td>

                    <td>

                        <?php if(!empty($product['image'])): ?>

                            <img src="<?= htmlspecialchars($product['image']); ?>"
                                 width="50"
                                 height="50"
                                 style="width:50px;height:50px;object-fit:cover;border-radius:8px;">

                        <?php else: ?>

                            <i class="fas fa-image"
                               style="font-size:28px;color:#cbd5e1;"></i>

                        <?php endif; ?>

                    </td>

                    <td><?= htmlspecialchars($product['name']); ?></td>

                    <td><?= htmlspecialchars($product['category'] ?? '-'); ?></td>

                    <td>
                        <strong>৳<?= number_format($product['price'] ?? 0,2); ?></strong>
                    </td>

                    <td><?= htmlspecialchars($product['unit'] ?? '-'); ?></td>

                    <td><?= $product['stock'] ?? 0; ?></td>

                    <td><?= htmlspecialchars($product['location'] ?? '-'); ?></td>

                    <td>
                        <?=
                        !empty($product['harvest_date'])
                        ? date("d M, Y", strtotime($product['harvest_date']))
                        : "-";
                        ?>
                    </td>

                    <td>

                        <a href="edit-product.php?id=<?= $product['id']; ?>"
                           class="btn"
                           style="background:#3b82f6;padding:8px 14px;text-decoration:none;margin-right:5px;">
                            ✏️ Edit
                        </a>

                        <form method="POST"
                              style="display:inline;"
                              onsubmit="return confirm('আপনি কি এই প্রোডাক্টটি ডিলিট করতে চান?');">

                            <input type="hidden" name="action" value="delete_product">

                            <input type="hidden" name="id" value="<?= $product['id']; ?>">

                            <button type="submit"
                                    class="btn delete"
                                    style="padding:8px 14px;">
                                🗑️
                            </button>

                        </form>

                    </td>

                </tr>

            <?php
                endwhile;

            else:
            ?>

                <tr>

                    <td colspan="10"
                        style="text-align:center;padding:60px;color:#64748b;">

                        <i class="fas fa-box-open"
                           style="font-size:45px;margin-bottom:15px;"></i>

                        <br><br>

                        এখনো কোনো প্রোডাক্ট যোগ করা হয়নি।

                    </td>

                </tr>

            <?php endif; ?>

            </tbody>

        </table>

    </div>

</div>


        <?php elseif ($section === 'orders'): ?>
            <div class="section">
                <h2><i class="fas fa-shopping-cart"></i> সকল অর্ডার</h2>
                <div class="table-box">
                    <p style="padding: 30px; text-align: center; color: #64748b;">অর্ডার ম্যানেজমেন্ট শীঘ্রই আসছে...</p>
                </div>
            </div>

        <?php elseif ($section === 'reports'): ?>
            <div class="section">
                <h2><i class="fas fa-chart-line"></i> রিপোর্টস</h2>
                <div class="table-box">
                    <p style="padding: 30px; text-align: center; color: #64748b;">রিপোর্ট জেনারেশন শীঘ্রই আসছে...</p>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script>
        // Simple confirmation enhancement
        document.querySelectorAll('.delete').forEach(btn => {
            btn.addEventListener('click', function(e) {
                if (!confirm('আপনি কি সত্যিই এটি ডিলিট করতে চান? এটি অপরিবর্তনীয়।')) {
                    e.preventDefault();
                }
            });
        });
    </script>
</body>
</html>
<?php mysqli_close($conn); ?>