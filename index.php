<?php
session_start();
include 'db_connect.php';





$_SESSION['dealer_id'] = 0;
$_SESSION['dealer_otp'] = 0;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $emailid = mysqli_real_escape_string($conn, $_POST['emailid']);
    $otp = $_POST['otp'];

    $query = "SELECT * FROM DealerMaster WHERE EmailId = '$emailid' AND OTP = '$otp'";
    $result = $conn->query($query);

    if (!$result) {
        die("Error executing query: " . $conn->error);
    }

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        if ($otp == $user['OTP']) {
            $dId = $user['DealerId'];

            // $queryDealerEvenrs = "SELECT * FROM `DealerEventDetails` DE INNER JOIN  EventMaster EM ON EM.EventId=DE.EventId where DE.DealerId =$dId and EM.EventStatus =1;";
            // $resultEvent = $conn->query($queryDealerEvenrs);

            // if ($resultEvent->num_rows > 0) {
                $_SESSION['dealer_id'] = $user['DealerId'];
                $_SESSION['dealer_otp'] = $user['OTP'];
                
                $query1 = "UPDATE `DealerMaster` SET `LastLoggedIn` = NOW(), `ActiveUser` = 1 WHERE DealerId='$dId'";
                $conn->query($query1);
    
            
                header("Location: home.php");
                exit();
            // } else {
            //     echo "<div class='alert alert-danger'>Event Not Started.</div>";
            // }
        } else {
            $_SESSION['error_message'] = "Invalid OTP1.";
            //echo "<div class='alert alert-danger'>Invalid OTP.</div>";
        }
    } else {
         $_SESSION['error_message'] = "No user found with the provided email1.";
        // echo "<div class='alert alert-danger'>No user found with the provided email.</div>";
    }
     header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Spin a Wheel | Log in</title>
  <!--<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">-->
  <!--<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">-->
  <link rel="stylesheet" href="css/all.min.css">
  <link rel="stylesheet" href="css/bootstrap.min.css">
  <link rel="stylesheet" href="css/loginstyle.css?v=1">
  <link rel="icon" href="favicon.webp" type="image/x-icon">

<style>
   
</style>	
 
</head>
<body>
 

<div class="login-box">
    <h1>Namaste Participants!</h1>
    <p>Sign in to test your luck!</p>
    <form action="" method="post">
         <label for="emailid">Email</label>
        <input type="email" id="emailid" name="emailid"  placeholder="Enter your email" required>
        <button type="button" id="getOtpButton" name="getOtpButton" >Get OTP</button>
        <input type="text" id="otp" " name="otp" placeholder="4 digit login pin" required maxlength="4" style="display:none" >
        <button type="submit" id="submit" name="submit"  style="display:none">Verify OTP</button>
        
        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="alert alert-danger">
                <?php
                echo $_SESSION['error_message'];
                unset($_SESSION['error_message']); // Clear the message after displaying it
                ?>
            </div>
        <?php endif; ?>
    </form>
    <p style="display:none">
        If you haven't received the OTP, <a href="#">click here</a> to resend it.
    </p>
</div>

<!--<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>-->
<!--<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>-->
<!--<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>-->

<script src="js/jquery-3.5.1.slim.min.js"></script>
<script src="js/popper.min.js"></script>
<script src="js/bootstrap.min.js"></script>

<script>
    document.getElementById('getOtpButton').addEventListener('click', function () {
        const emailid = document.getElementById('emailid').value;

        if (!emailid) {
            alert("Please enter your email ID.");
            return;
        }

        // Send AJAX request to get OTP
        fetch('get_otp.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: `emailid=${encodeURIComponent(emailid)}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message); // OTP sent successfully
                document.getElementById('otp').style.display = 'block';
                document.getElementById('submit').style.display = 'block';
                document.getElementById('getOtpButton').style.display = 'none';
            } else {
                alert(data.message); // Error messages
            }
        })
        .catch(error => console.error('Error:', error));
    });
</script>
</body>
</html>
