<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "farm_db";

$conn = mysqli_connect($servername, $username, $password, $dbname);
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$message = "";
$image_path = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = mysqli_real_escape_string($conn, trim($_POST['name']));
    $category = mysqli_real_escape_string($conn, trim($_POST['category']));
    $price = floatval($_POST['price']);
    $unit = mysqli_real_escape_string($conn, trim($_POST['unit']));
    $stock = intval($_POST['stock']);
    $description = mysqli_real_escape_string($conn, trim($_POST['description']));
    $location = mysqli_real_escape_string($conn, trim($_POST['location']));
    $harvest_date = $_POST['harvest_date'];
    $farmer_id = intval($_POST['farmer_id']);

    // Image Upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $target_dir = "uploads/products/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        
        $file_ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $new_file_name = time() . '_' . uniqid() . '.' . $file_ext;
        $image_path = $target_dir . $new_file_name;

        if (move_uploaded_file($_FILES['image']['tmp_name'], $image_path)) {
            $image = $image_path;
        } else {
            $image = "";
        }
    } else {
        $image = "";
    }

    $sql = "INSERT INTO products (name, category, price, unit, stock, description, image, harvest_date, location, farmer_id)
            VALUES ('$name', '$category', $price, '$unit', $stock, '$description', '$image', '$harvest_date', '$location', $farmer_id)";

    if (mysqli_query($conn, $sql)) {
        $message = "✅ প্রোডাক্ট সফলভাবে যোগ করা হয়েছে!";
    } else {
        $message = "❌ এরর: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>নতুন প্রোডাক্ট যোগ করুন</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #f0fdf4 0%, #ecfdf5 100%);
            font-family: 'Poppins', sans-serif;
        }
        .glass {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(16px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
        }
        .form-input {
            transition: all 0.3s ease;
        }
        .form-input:focus {
            border-color: #10b981;
            box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.2);
            transform: translateY(-2px);
        }
        .preview-img {
            transition: all 0.4s ease;
        }
        button {
            transition: all 0.3s ease;
        }
        button:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(16, 185, 129, 0.3);
        }
    </style>
</head>
<body class="min-h-screen py-12">
<div class="max-w-2xl mx-auto px-4">
    <div class="glass rounded-3xl p-8 md:p-10">
        <!-- Header -->
        <div class="flex items-center gap-4 mb-8">
            <div class="w-14 h-14 bg-emerald-100 text-emerald-600 rounded-2xl flex items-center justify-center text-3xl">
                🌱
            </div>
            <div>
                <h1 class="text-3xl font-bold text-gray-800">নতুন প্রোডাক্ট যোগ করুন</h1>
                <p class="text-gray-600">ফার্মের তাজা পণ্য যোগ করুন</p>
            </div>
        </div>

        <?php if($message): ?>
            <div class="mb-6 p-4 rounded-2xl <?php echo strpos($message, '✅') !== false ? 'bg-emerald-50 text-emerald-700' : 'bg-red-50 text-red-700'; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data" class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Product Name -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">প্রোডাক্টের নাম</label>
                    <input type="text" name="name" required
                           class="form-input w-full px-5 py-4 border border-gray-200 rounded-2xl focus:outline-none">
                </div>

                <!-- Category & Unit -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">ক্যাটেগরি</label>
                    <input type="text" name="category" required placeholder="যেমন: সবজি, ফল, ধান"
                           class="form-input w-full px-5 py-4 border border-gray-200 rounded-2xl focus:outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">ইউনিট</label>
                    <input type="text" name="unit" required placeholder="kg / piece / dozen"
                           class="form-input w-full px-5 py-4 border border-gray-200 rounded-2xl focus:outline-none">
                </div>

                <!-- Price & Stock -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">দাম (৳)</label>
                    <input type="number" name="price" step="0.01" required
                           class="form-input w-full px-5 py-4 border border-gray-200 rounded-2xl focus:outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">স্টক</label>
                    <input type="number" name="stock" required
                           class="form-input w-full px-5 py-4 border border-gray-200 rounded-2xl focus:outline-none">
                </div>
            </div>

            <!-- Description -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">বিস্তারিত বর্ণনা</label>
                <textarea name="description" rows="4" 
                          class="form-input w-full px-5 py-4 border border-gray-200 rounded-3xl focus:outline-none resize-y"></textarea>
            </div>

            <!-- Image Upload with Preview -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">প্রোডাক্টের ছবি</label>
                <div class="border-2 border-dashed border-gray-300 rounded-3xl p-6 text-center hover:border-emerald-400 transition-colors">
                    <input type="file" name="image" id="imageInput" accept="image/*" 
                           class="hidden" onchange="previewImage(event)">
                    <label for="imageInput" class="cursor-pointer flex flex-col items-center">
                        <i class="fas fa-cloud-upload-alt text-4xl text-emerald-500 mb-3"></i>
                        <p class="text-gray-600">ছবি আপলোড করুন</p>
                        <p class="text-xs text-gray-500 mt-1">PNG, JPG, JPEG (max 5MB)</p>
                    </label>
                </div>
                <div id="imagePreview" class="hidden mt-4 rounded-2xl overflow-hidden shadow-md">
                    <img id="previewImg" class="w-full h-64 object-cover preview-img" alt="Preview">
                </div>
            </div>

            <!-- Other Fields -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">হার্ভেস্টের তারিখ</label>
                    <input type="date" name="harvest_date"
                           class="form-input w-full px-5 py-4 border border-gray-200 rounded-2xl focus:outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">লোকেশন</label>
                    <input type="text" name="location" placeholder="যেমন: রাজশাহী, বাংলাদেশ"
                           class="form-input w-full px-5 py-4 border border-gray-200 rounded-2xl focus:outline-none">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">ফার্মার ID</label>
                <input type="number" name="farmer_id" required
                       class="form-input w-full px-5 py-4 border border-gray-200 rounded-2xl focus:outline-none">
            </div>

            <button type="submit" 
                    class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-semibold py-5 rounded-2xl text-lg flex items-center justify-center gap-3">
                <i class="fas fa-plus"></i>
                প্রোডাক্ট যোগ করুন
            </button>
        </form>

        <div class="mt-8 text-center">
            <a href="admin-dashboard.php?section=products" 
               class="inline-flex items-center gap-2 text-emerald-600 hover:text-emerald-700 font-medium">
                ← ফিরে যান ড্যাশবোর্ডে
            </a>
        </div>
    </div>
</div>

<script>
function previewImage(event) {
    const preview = document.getElementById('imagePreview');
    const img = document.getElementById('previewImg');
    const file = event.target.files[0];
    
    if (file) {
        img.src = URL.createObjectURL(file);
        preview.classList.remove('hidden');
    }
}
</script>
</body>
</html>

<?php mysqli_close($conn); ?>