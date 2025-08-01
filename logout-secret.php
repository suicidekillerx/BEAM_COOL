<?php
session_start();
require_once 'includes/functions.php';

// Deactivate the current password session
if (isset($_SESSION['secret_collection_password_id'])) {
    deactivatePasswordSession(session_id());
}

// Clear session variables
unset($_SESSION['secret_collection_access']);
unset($_SESSION['secret_collection_password_id']);

// Redirect to home page
header('Location: index.php');
exit();
?> 