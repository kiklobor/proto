<?php
//session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST["paymentOnline"])) {
        $_SESSION["paymentsOn"] = ($_POST["paymentOnline"] === "true");
        echo "Session variable updated successfully.";
    } else {
        echo "Invalid request.";
    }
}
?>
