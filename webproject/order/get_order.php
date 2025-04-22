<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $food = array(
        "BAsoup"=>149, "TBsoup"=>129, "MTsoup"=>149, "LPsoup"=>129,
        "TRmain"=>499, "PSSmain"=>599, "GCmain"=>459, "CEmain"=>499,
        "Tdessert"=>249, "LCdessert"=>299, "BBFmock"=>129, "TSmock"=>129,
        "CMCmock"=>129
    );

    $order = [];
    $total = 0;

    foreach ($food as $key => $price) {
        if (isset($_POST[$key]) && $_POST[$key] > 0) {
            $quantity = intval($_POST[$key]);
            $itemTotal = $quantity * $price;
            $order[] = [
                "name" => $key,
                "quantity" => $quantity,
                "unit_price" => $price,
                "subtotal" => $itemTotal
            ];
            $total += $itemTotal;
        }
    }

    $_SESSION['order'] = $order;
    $_SESSION['total'] = $total;

    header("Location: ../bill/bill.php");
    exit();
}
?>
