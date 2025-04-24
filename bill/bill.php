<?php
session_start();
include("../db.php");

$tableBill = null;
$total = 0;
$order = [];

if (isset($_SESSION['order']) && !empty($_SESSION['order'])) {
    // If there is a new order (from ordering page)
    $order = $_SESSION['order'];
    $total = $_SESSION['total'];
} elseif (isset($_POST['table_lookup'])) {
    // When user submits table number to view existing bill
    $tableNum = $_POST['tablenum'];

    $tableNum = mysqli_real_escape_string($conn, $tableNum);

    $sql = "SELECT bill FROM user WHERE tablenum = '$tableNum'";
    $result = mysqli_query($conn, $sql);

    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $tableBill = $row['bill'];
        $total = $tableBill;
    } else {
        $tableBill = "NOT_FOUND";
    }
}


// When Finalizing and Closing Table
if (isset($_POST['complete'])) {
    $tableNum = $_POST['tablenum'];

    $tableNum = mysqli_real_escape_string($conn, $tableNum);

    // Simple SQL query
    $sql = "UPDATE restaurant_tables SET status = 0 WHERE tablenum = '$tableNum'";
    
    if (mysqli_query($conn, $sql)) {
        echo "<h3>✅ Table '$tableNum' is now free! </h3>";
        session_destroy();
        header("Refresh:3;URL=../index.html");
        exit();
    } else {
        echo "<h3>❌ Error updating table status!</h3>";
    }
}


// When Adding More Items to Existing Bill
if (isset($_POST['add_more'])) {
    $tableNum = $_POST['tablenum'];

    $tableNum = mysqli_real_escape_string($conn, $tableNum);

    // Step 1: Get the current bill
    $sql = "SELECT bill FROM user WHERE tablenum = '$tableNum'";
    $result = mysqli_query($conn, $sql);

    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $currentBill = $row['bill'];

        // Assuming $total is already defined earlier in your session
        $newTotal = $currentBill + $total;

        // Step 2: Update the bill
        $updateSql = "UPDATE user SET bill = $newTotal WHERE tablenum = '$tableNum'";
        if (mysqli_query($conn, $updateSql)) {
            echo "<h3>✅ Added ₹$total to Table '$tableNum'. New total: ₹$newTotal</h3>";
            $_SESSION['order'] = [];
            $_SESSION['total'] = 0;
            header("Refresh:3;URL=../index.html");
            exit();
        } else {
            echo "<h3>❌ Error updating the bill!</h3>";
        }
    } else {
        echo "<h3>❌ Table '$tableNum' not found in records.</h3>";
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Salt & Sizzle</title>
    <link rel="stylesheet" href="bill.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&display=swap" rel="stylesheet">
</head>
<body>
<header>
    <p>Salt & Sizzle</p>
    <div class="links">
        <a href="../index.html">HOME</a><br>
        <a href="../order/order.html">ORDER</a><br>
        <a href="bill.php">BILLING</a><br>
        <a href="../table/reservation.html">BOOK A TABLE</a><br>
    </div>
</header>

<h2 align="center">Bill</h2>

<?php if (!empty($order)) : ?>

    <!-- Show Ordered Items -->
    <table cellpadding="3" align="center" border=1> 
        <tr>
            <th>Item Name</th>
            <th>Quantity</th>
            <th>Unit Price (₹)</th>
            <th>Subtotal (₹)</th>
        </tr>
        <?php foreach ($order as $item): ?>
            <tr>
                <td><?php echo htmlspecialchars($item['name']); ?></td>
                <td align="right"><?php echo $item['quantity']; ?></td>
                <td align="right"><?php echo $item['unit_price']; ?></td>
                <td align="right"><?php echo $item['subtotal']; ?></td>
            </tr>
        <?php endforeach; ?>
        <tr>
            <td colspan="3"><strong>Total Bill</strong></td>
            <td align="right"><strong>₹<?php echo $total; ?></strong></td>
        </tr>
    </table>

<?php elseif ($tableBill !== null): ?>

    <?php if ($tableBill === "NOT_FOUND"): ?>
        <h3 align="center">❌ Table not found in records!</h3>
    <?php else: ?>
        <!-- Show only Total -->
        <table cellpadding="3" align="center" border=1> 
            <tr>
                <td><strong>Total Bill</strong></td>
                <td align="right"><strong>₹<?php echo $total; ?></strong></td>
            </tr>
        </table>
    <?php endif; ?>

<?php else: ?>

    <!-- No active order, ask for Table Number -->
    <div align="center" class="tasks">
        <form method="POST" action="">
            <label for="tablenum"><b>Enter Table Number to View Bill:</b></label><br>
            <input type="text" name="tablenum" autocomplete="off" required><br><br>
            <button type="submit" name="table_lookup">Show Existing Bill</button>
        </form>
    </div>

<?php endif; ?>

<?php if (!empty($order) || ($tableBill !== null && $tableBill !== "NOT_FOUND")): ?>

    <!-- Show Table Close and Add More options -->
    <div align="center" class="tasks">
        <form method="POST" action="">
            <label for="tablenum"><b>Enter Table Number to Close:</b></label><br>
            <input type="text" name="tablenum" autocomplete="off" required><br><br>
            <button type="submit" name="complete"> Finalize & Close Table</button>
        </form>

        <form method="POST" action="">
            <label for="tablenum"><b>Enter Table Number to Add Bill:</b></label><br>
            <input type="text" name="tablenum" autocomplete="off" required><br><br>
            <button type="submit" name="add_more"> Add Items To Table Bill</button>
        </form>
    </div>

<?php endif; ?>

</body>
</html>
