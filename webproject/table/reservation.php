<?php
session_start();
include("../db.php");

if (isset($_POST["submit"])) {
    $customer_name = $_POST["name"];
    $contact = $_POST["contact"];
    $count = (int)$_POST["count"];

    // Search for an available table
    $sql = "SELECT tablenum FROM restaurant_tables WHERE status = 0 AND capacity >= ? ORDER BY capacity ASC LIMIT 1";
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        die("Error preparing statement: " . $conn->error);
    }

    $stmt->bind_param("i", $count);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $assigned_table = $row['tablenum'];

        // Mark the table as reserved
        $update = $conn->prepare("UPDATE restaurant_tables SET status = 1 WHERE tablenum = ?");
        if (!$update) {
            die("Error preparing table update: " . $conn->error);
        }

        $update->bind_param("s", $assigned_table);
        if ($update->execute()) {

            // Insert customer data into 'user' table
            $insert = $conn->prepare("INSERT INTO user (name, contact, tablenum, bill) VALUES (?, ?, ?, 0)");
            if (!$insert) {
                die("Error preparing reservation insert: " . $conn->error);
            }

            $insert->bind_param("sss", $customer_name, $contact, $assigned_table);
            if ($insert->execute()) {

                // Store reservation data in session
                $_SESSION['reservation'] = [
                    'name' => $customer_name,
                    'contact' => $contact,
                    'table_no' => $assigned_table
                ];

                echo "<h2>Table Reserved!</h2>";
                echo "<p>Thank you, <b>" . htmlspecialchars($customer_name) . "</b>.</p>";
                echo "<p>Your table number is: <b>" . htmlspecialchars($assigned_table) . "</b>.</p>";
                echo "<p>You will be redirected to the order page shortly...</p>";

                // Automatic redirection after 2 seconds
                header("Refresh:2; URL=../order/order.html");

            } else {
                echo "Error saving reservation: " . $insert->error;
            }

        } else {
            echo "Error updating table status: " . $update->error;
        }

    } else {
        echo "<h2>No Table Available</h2>";
        echo "<p>Sorry, no table can accommodate <b>$count</b> guests right now.</p>";
    }

    $stmt->close();
    $conn->close();
}
?>
