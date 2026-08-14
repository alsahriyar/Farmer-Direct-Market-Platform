<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "farm_db";

$conn = mysqli_connect($servername, $username, $password, $dbname);
if (!$conn) die("Connection failed: " . mysqli_connect_error());

session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$message = "";

// Fetch Product
$product = null;
if ($product_id > 0) {
    $sql = "SELECT * FROM products WHERE id = $product_id";
    $result = mysqli_query($conn, $sql);
    $product = mysqli_fetch_assoc($result);
}

// Fetch Farmers
$farmers = [];
$result = mysqli_query($conn, "SELECT id, name, phone, location FROM farmers ORDER BY name ASC");
while ($row = mysqli_fetch_assoc($result)) {
    $farmers[] = $row;
}
?>

<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit - <?= htmlspecialchars($product['name'] ?? 'প্রোডাক্ট') ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&family=Playfair+Display:wght@700&display=swap');
        
        body {
            background: linear-gradient(135deg, #0f172a 0%, #1e2937 100%);
            font-family: 'Poppins', sans-serif;
        }
        .hero-bg {
            background: linear-gradient(135deg, #10b981, #34d399);
        }
        .glass {
            background: rgba(255, 255, 255, 0.08);
            backdrop-filter: blur(24px);
            border: 1px solid rgba(255,255,255,0.15);
        }
        .input-field {
            background: rgba(255,255,255,0.9);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .input-field:focus {
            background: white;
            transform: scale(1.02);
            box-shadow: 0 0 0 5px rgba(16, 185, 129, 0.3);
        }
        .label {
            font-size: 0.95rem;
            letter-spacing: 0.5px;
        }
    </style>
</head>
<body class="min-h-screen py-12">
<div class="max-w-5xl mx-auto px-6">
    <div class="glass rounded-3xl overflow-hidden shadow-2xl">
        
        <!-- Beautiful Header -->
        <div class="hero-bg px-10 py-12 text-white">
            <div class="flex items-center gap-5">
                <div class="w-20 h-20 bg-white/20 backdrop-blur-md rounded-3xl flex items-center justify-center text-5xl border border-white/30">
                    🌱
                </div>
                <div>
                    <h1 class="text-5xl font-bold tracking-tight">প্রোডাক্ট এডিট করুন</h1>
                    <p class="text-emerald-100 mt-2 text-lg"><?= htmlspecialchars($product['name'] ?? '') ?></p>
                </div>
            </div>
        </div>

        <div class="p-10">
            <?php if($message): ?>
                <div class="mb-8 p-5 rounded-2xl <?= strpos($message,'✅') !== false ? 'bg-emerald-500/10 border border-emerald-500 text-emerald-400' : 'bg-red-500/10 text-red-400' ?>">
                    <?= $message ?>
                </div>
            <?php endif; ?>

            <?php if($product): ?>
            <form method="POST" enctype="multipart/form-data" class="space-y-10">
                
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-10">
                    
                    <!-- Left Side: Image -->
                    <div class="lg:col-span-5">
                        <div class="sticky top-8">
                            <label class="label text-emerald-100 block mb-3 font-medium">প্রোডাক্ট ছবি</label>
                            <div class="relative rounded-3xl overflow-hidden shadow-xl border border-white/10">
                                <?php if(!empty($product['image'])): ?>
                                    <img id="currentImage" src="<?= $product['image'] ?>" 
                                         class="w-full h-[420px] object-cover" alt="">
                                <?php else: ?>
                                    <div class="h-[420px] bg-gray-800 flex items-center justify-center">
                                        <i class="fas fa-leaf text-8xl text-emerald-700"></i>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/70 to-transparent p-6">
                                    <label for="imageInput" 
                                           class="cursor-pointer inline-flex items-center gap-3 bg-white text-gray-800 px-6 py-3.5 rounded-2xl font-semibold hover:bg-emerald-100 transition">
                                        <i class="fas fa-camera"></i>
                                        ছবি পরিবর্তন করুন
                                    </label>
                                    <input type="file" name="image" id="imageInput" accept="image/*" class="hidden" onchange="previewImage(event)">
                                </div>
                            </div>
                            
                            <div id="newPreview" class="hidden mt-6">
                                <p class="text-emerald-100 text-sm mb-2">নতুন ছবি প্রিভিউ</p>
                                <img id="previewImg" class="w-full h-80 object-cover rounded-3xl shadow-xl">
                            </div>
                        </div>
                    </div>

                    <!-- Right Side: Form -->
                    <div class="lg:col-span-7 space-y-8">
                        
                        <div>
                            <label class="label text-emerald-100">প্রোডাক্টের নাম</label>
                            <input type="text" name="name" value="<?= htmlspecialchars($product['name']) ?>" required
                                   class="input-field w-full px-7 py-6 rounded-3xl text-xl font-medium focus:outline-none">
                        </div>

                        <div class="grid grid-cols-2 gap-6">
                            <div>
                                <label class="label text-emerald-100">ক্যাটেগরি</label>
                                <input type="text" name="category" value="<?= htmlspecialchars($product['category']) ?>" required
                                       class="input-field w-full px-6 py-5 rounded-3xl">
                            </div>
                            <div>
                                <label class="label text-emerald-100">ইউনিট</label>
                                <input type="text" name="unit" value="<?= htmlspecialchars($product['unit']) ?>" required
                                       class="input-field w-full px-6 py-5 rounded-3xl">
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-6">
                            <div>
                                <label class="label text-emerald-100">দাম (৳)</label>
                                <input type="number" name="price" step="0.01" value="<?= $product['price'] ?>" required
                                       class="input-field w-full px-6 py-5 rounded-3xl">
                            </div>
                            <div>
                                <label class="label text-emerald-100">স্টক</label>
                                <input type="number" name="stock" value="<?= $product['stock'] ?>" required
                                       class="input-field w-full px-6 py-5 rounded-3xl">
                            </div>
                        </div>

                        <div>
                            <label class="label text-emerald-100">বিস্তারিত বর্ণনা</label>
                            <textarea name="description" rows="4" 
                                      class="input-field w-full px-6 py-5 rounded-3xl"><?= htmlspecialchars($product['description']) ?></textarea>
                        </div>

                        <!-- Farmer -->
                        <div>
                            <label class="label text-emerald-100">ফার্মার</label>
                            <select name="farmer_id" required class="input-field w-full px-6 py-5 rounded-3xl">
                                <option value="">ফার্মার নির্বাচন করুন</option>
                                <?php foreach($farmers as $f): ?>
                                <option value="<?= $f['id'] ?>" <?= $product['farmer_id'] == $f['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($f['name']) ?> — <?= htmlspecialchars($f['phone']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="grid grid-cols-2 gap-6">
                            <div>
                                <label class="label text-emerald-100">হার্ভেস্ট ডেট</label>
                                <input type="date" name="harvest_date" value="<?= $product['harvest_date'] ?? '' ?>"
                                       class="input-field w-full px-6 py-5 rounded-3xl">
                            </div>
                            <div>
                                <label class="label text-emerald-100">লোকেশন</label>
                                <input type="text" name="location" value="<?= htmlspecialchars($product['location'] ?? '') ?>"
                                       class="input-field w-full px-6 py-5 rounded-3xl">
                            </div>
                        </div>

                        <button type="submit" 
                                class="w-full mt-8 bg-white text-emerald-700 hover:bg-emerald-50 font-bold py-7 rounded-3xl text-xl shadow-xl flex items-center justify-center gap-3 transition-all hover:scale-[1.02]">
                            <i class="fas fa-check-circle"></i>
                            আপডেট সেভ করুন
                        </button>
                    </div>
                </div>
            </form>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function previewImage(event) {
    const previewContainer = document.getElementById('newPreview');
    const previewImg = document.getElementById('previewImg');
    if (event.target.files[0]) {
        previewImg.src = URL.createObjectURL(event.target.files[0]);
        previewContainer.classList.remove('hidden');
    }
}
</script>
</body>
</html>

<?php mysqli_close($conn); ?>