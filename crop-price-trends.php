<?php
session_start();
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "farm_db";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed");
}

// Handle AJAX request for price history
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'get_history') {
    $product_id = (int)$_POST['product_id'];
    
    $stmt = $conn->prepare("SELECT DATE_FORMAT(date, '%d %b') as date, price 
                            FROM price_history 
                            WHERE product_id = ? 
                            ORDER BY date ASC LIMIT 7");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    
    // If no data, return sample
    if (empty($data)) {
        $data = [
            ['date' => '24 Jun', 'price' => 38],
            ['date' => '25 Jun', 'price' => 39],
            ['date' => '26 Jun', 'price' => 37],
            ['date' => '27 Jun', 'price' => 40],
            ['date' => '28 Jun', 'price' => 42],
            ['date' => '29 Jun', 'price' => 41],
            ['date' => '30 Jun', 'price' => 40]
        ];
    }
    
    echo json_encode($data);
    exit;
}

// Fetch products for cards
$products = $conn->query("SELECT id, name, price, category FROM products ORDER BY name");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Crop Price Trends | Farmer Direct Market</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
* { margin:0; padding:0; box-sizing:border-box; font-family:'Poppins',sans-serif; }
body { background:#f5f7fa; }
.header { background:#0f9d58; color:white; padding:35px 8%; }
.header h1 { font-size:34px; }

.container { width:90%; max-width:1200px; margin:30px auto; }

.search-box {
    background:white;
    padding:20px;
    border-radius:15px;
    margin-bottom:25px;
    box-shadow:0 5px 15px rgba(0,0,0,.08);
    display:flex;
    gap:15px;
    flex-wrap:wrap;
}
.search-box input, .search-box select {
    padding:12px 15px;
    border:1px solid #ddd;
    border-radius:8px;
    outline:none;
}
.search-box input { flex:1; min-width:280px; }

.price-cards {
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(230px,1fr));
    gap:20px;
    margin-bottom:35px;
}
.card {
    background:white;
    padding:20px;
    border-radius:15px;
    box-shadow:0 5px 15px rgba(0,0,0,.08);
    cursor:pointer;
    transition:.3s;
}
.card:hover { transform:translateY(-8px); box-shadow:0 10px 25px rgba(0,0,0,.12); }

.chart-container {
    background:white;
    padding:25px;
    border-radius:16px;
    box-shadow:0 5px 20px rgba(0,0,0,.08);
    margin-top:20px;
    height: 420px;
}
.chart-container h2 {
    margin-bottom:20px;
    color:#222;
    font-size:22px;
}

.history-table {
    background:white;
    padding:25px;
    border-radius:16px;
    box-shadow:0 5px 20px rgba(0,0,0,.08);
    margin-top:25px;
}
.history-table table {
    width:100%;
    border-collapse:collapse;
}
.history-table th, .history-table td {
    padding:14px;
    text-align:left;
    border-bottom:1px solid #eee;
}
.history-table th {
    background:#0f9d58;
    color:white;
}
</style>
</head>
<body>

<section class="header">
    <h1>📈 Live Crop Price Trends</h1>
    <p>Real-time market prices & 7 days history</p>
</section>

<div class="container">

    <div class="search-box">
        <input type="text" id="searchInput" placeholder="Search crop (e.g. Tomato, Rice...)" onkeyup="filterProducts()">
        <select id="categoryFilter" onchange="filterProducts()">
            <option value="">All Categories</option>
            <option value="Vegetable">Vegetables</option>
            <option value="Fruit">Fruits</option>
            <option value="Grain">Rice & Grains</option>
            <option value="Spices">Spices</option>
        </select>
    </div>

    <div class="price-cards" id="priceCards">
        <?php while($row = $products->fetch_assoc()): ?>
            <div class="card" onclick="showProductChart(<?php echo $row['id']; ?>, '<?php echo addslashes($row['name']); ?>')">
                <h3><?php echo htmlspecialchars($row['name']); ?></h3>
                <h2>৳<?php echo number_format($row['price'], 0); ?>/kg</h2>
                <small><?php echo htmlspecialchars($row['category']); ?></small>
            </div>
        <?php endwhile; ?>
    </div>

    <!-- Chart -->
    <div class="chart-container">
        <h2 id="chartTitle">Select a crop to view 7 days price trend</h2>
        <canvas id="priceChart"></canvas>
    </div>

    <!-- 7 Days History Table -->
    <div class="history-table">
        <h2>7 Days Price History</h2>
        <table id="historyData">
            <tr><th>Date</th><th>Price (৳/kg)</th></tr>
        </table>
    </div>

</div>

<script>
let currentChart = null;

function filterProducts() {
    const search = document.getElementById('searchInput').value.toLowerCase();
    const category = document.getElementById('categoryFilter').value;
    const cards = document.querySelectorAll('.card');

    cards.forEach(card => {
        const name = card.querySelector('h3').textContent.toLowerCase();
        const cat = card.querySelector('small').textContent;
        const matchSearch = name.includes(search);
        const matchCat = !category || cat === category;
        card.style.display = (matchSearch && matchCat) ? 'block' : 'none';
    });
}

function showProductChart(productId, name) {
    document.getElementById('chartTitle').textContent = `${name} - Last 7 Days Price Trend`;

    fetch(window.location.href, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `action=get_history&product_id=${productId}`
    })
    .then(res => res.json())
    .then(data => {
        const dates = data.map(item => item.date);
        const prices = data.map(item => parseFloat(item.price));

        // Update Chart
        const ctx = document.getElementById('priceChart');
        if (currentChart) currentChart.destroy();

        currentChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: dates,
                datasets: [{
                    label: name,
                    data: prices,
                    borderColor: '#0f9d58',
                    backgroundColor: 'rgba(15, 157, 88, 0.1)',
                    borderWidth: 4,
                    tension: 0.4,
                    pointRadius: 6,
                    pointBackgroundColor: '#0f9d58'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: true, position: 'top' }
                }
            }
        });

        // Update History Table
        let html = `<tr><th>Date</th><th>Price (৳/kg)</th></tr>`;
        data.forEach(item => {
            html += `<tr><td>${item.date}</td><td>৳${parseFloat(item.price).toFixed(0)}</td></tr>`;
        });
        document.getElementById('historyData').innerHTML = html;
    })
    .catch(err => console.error(err));
}

// Default Load
window.onload = () => {
    showProductChart(1, 'Fresh Organic Tomato');
};
</script>

</body>
</html>