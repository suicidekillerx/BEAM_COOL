<?php
session_start();

// Include functions
require_once 'includes/functions.php';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $productId = (int)$_POST['product_id'];
    $size = $_POST['size'];
    $quantity = (int)$_POST['quantity'];
    
    try {
        if (addToCart($productId, $size, $quantity)) {
            $_SESSION['cart_message'] = 'Item added to cart successfully!';
        } else {
            $_SESSION['cart_message'] = 'Failed to add item to cart.';
        }
    } catch (Exception $e) {
        $_SESSION['cart_message'] = 'Error: ' . $e->getMessage();
    }
    
    // Redirect back to the product page
    $redirect_url = isset($_POST['redirect_url']) ? $_POST['redirect_url'] : 'index.php';
    header('Location: ' . $redirect_url);
    exit;
}

// If accessed directly without POST, redirect to home
header('Location: index.php');
exit;
?> 