
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


/* ================= CONNECTION CHECK ================= */

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

    http_response_code(401);

    echo "Please login as a buyer first.";

    exit();
}


$user_id = (int) $_SESSION['user_id'];


/* =========================================================
   CART COUNT REQUEST
   Example:
   add_to_cart.php?count=1
   ========================================================= */

if (isset($_GET['count'])) {

    $query = "
        SELECT COALESCE(
            SUM(quantity),
            0
        ) AS total_items

        FROM cart

        WHERE user_id = ?
    ";


    $stmt = $conn->prepare($query);


    if (!$stmt) {

        echo "0";

        exit();

    }


    $stmt->bind_param(
        "i",
        $user_id
    );


    $stmt->execute();


    $result =
        $stmt->get_result();


    $row =
        $result->fetch_assoc();


    echo (int) (
        $row['total_items']
        ?? 0
    );


    $stmt->close();

    $conn->close();

    exit();
}


/* =========================================================
   ONLY POST REQUEST ALLOWED
   ========================================================= */

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    http_response_code(405);

    echo "Invalid request method.";

    exit();
}


/* =========================================================
   GET PRODUCT ID AND QUANTITY
   ========================================================= */

$product_id =
    isset($_POST['product_id'])
    ? (int) $_POST['product_id']
    : 0;


$quantity =
    isset($_POST['quantity'])
    ? (int) $_POST['quantity']
    : 0;


/* =========================================================
   BASIC VALIDATION
   ========================================================= */

if (
    $product_id <= 0 ||
    $quantity <= 0
) {

    http_response_code(400);

    echo "Invalid product or quantity.";

    exit();
}


/* =========================================================
   CHECK PRODUCT
   ========================================================= */

$query = "
    SELECT
        id,
        name,
        price,
        stock,
        status

    FROM products

    WHERE id = ?

    LIMIT 1
";


$stmt = $conn->prepare($query);


if (!$stmt) {

    echo "Database error.";

    exit();

}


$stmt->bind_param(
    "i",
    $product_id
);


$stmt->execute();


$result =
    $stmt->get_result();


$product =
    $result->fetch_assoc();


$stmt->close();


/* =========================================================
   PRODUCT NOT FOUND
   ========================================================= */

if (!$product) {

    http_response_code(404);

    echo "Product not found.";

    exit();
}


/* =========================================================
   PRODUCT STATUS CHECK
   ========================================================= */

if (
    $product['status'] !== 'active'
) {

    echo "This product is currently unavailable.";

    exit();
}


/* =========================================================
   STOCK CHECK
   ========================================================= */

$stock =
    (int) $product['stock'];


if ($stock <= 0) {

    echo "This product is out of stock.";

    exit();
}


if ($quantity > $stock) {

    echo "Only "
        . $stock
        . " items are available in stock.";

    exit();
}


/* =========================================================
   CHECK IF PRODUCT ALREADY EXISTS
   IN BUYER'S CART
   ========================================================= */

$query = "
    SELECT
        id,
        quantity

    FROM cart

    WHERE user_id = ?
    AND product_id = ?

    LIMIT 1
";


$stmt = $conn->prepare($query);


$stmt->bind_param(
    "ii",
    $user_id,
    $product_id
);


$stmt->execute();


$result =
    $stmt->get_result();


$existing_cart =
    $result->fetch_assoc();


$stmt->close();


/* =========================================================
   IF PRODUCT ALREADY IN CART
   UPDATE QUANTITY
   ========================================================= */

if ($existing_cart) {


    $cart_id =
        (int) $existing_cart['id'];


    $old_quantity =
        (int) $existing_cart['quantity'];


    $new_quantity =
        $old_quantity + $quantity;


    /* ================= STOCK LIMIT ================= */

    if ($new_quantity > $stock) {

        echo "You can add maximum "
            . $stock
            . " items of this product.";

        exit();

    }


    /* ================= UPDATE CART ================= */

    $query = "
        UPDATE cart

        SET quantity = ?

        WHERE id = ?
        AND user_id = ?
    ";


    $stmt =
        $conn->prepare($query);


    $stmt->bind_param(
        "iii",
        $new_quantity,
        $cart_id,
        $user_id
    );


    if ($stmt->execute()) {

        echo "Product quantity updated in cart.";

    } else {

        echo "Failed to update cart.";

    }


    $stmt->close();


}


/* =========================================================
   NEW PRODUCT
   INSERT INTO CART
   ========================================================= */

else {


    $query = "
        INSERT INTO cart
        (
            user_id,
            product_id,
            quantity
        )

        VALUES
        (
            ?,
            ?,
            ?
        )
    ";


    $stmt =
        $conn->prepare($query);


    $stmt->bind_param(
        "iii",
        $user_id,
        $product_id,
        $quantity
    );


    if ($stmt->execute()) {

        echo "Product added to cart successfully!";

    } else {

        echo "Failed to add product to cart.";

    }


    $stmt->close();

}


/* ================= CLOSE CONNECTION ================= */

$conn->close();

?>