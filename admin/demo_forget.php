<?php
include 'config.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

function sendMail($email, $reset_token)
{
    require('PHPMailer/PHPMailer.php');
    require('PHPMailer/SMTP.php');
    require('PHPMailer/Exception.php');
    $mail = new PHPMailer(true);
    try {
        // Server settings
        $mail->isSMTP();                                          // Send using SMTP
        $mail->Host       = 'smtp.gmail.com';                     // Set the SMTP server to send through
        $mail->SMTPAuth   = true;                                 // Enable SMTP authentication
        $mail->Username   = 'vegdakrunal21@gmail.com';            // SMTP username
        $mail->Password   = 'cgkoeyjnsxemgxks';                   // SMTP password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;          // Enable implicit TLS encryption
        $mail->Port       = 465;                                  // TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`

        // Recipients
        $mail->setFrom('vegdakrunal21@gmail.com', 'News Site');
        $mail->addAddress($email);                                // Add a recipient

        // Content
        $mail->isHTML(true);                                      // Set email format to HTML
        $mail->Subject = 'Password reset link from News Site';
        $body = "We received a request from you to reset your password!<br>
                Click the link below to reset your password:<br>
                <a href='http://localhost/news-site/admin/reset_pass.php?username=$email&reset_token=$reset_token'>Reset Password</a>";
        
        $mail->MsgHTML($body);
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log('Mailer Error: ' . $mail->ErrorInfo);  // Log the error for debugging
        return false;
    }
}

if (isset($_POST['sendlink'])) {
    // Sanitize the email input to avoid SQL injection
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    
    // Check if the username (email) exists
    $sql = "SELECT * FROM user WHERE username = '$username'";
    $result = mysqli_query($conn, $sql);
    
    if ($result) {
        if (mysqli_num_rows($result) == 1) {
            // Generate reset token
            $reset_token = bin2hex(random_bytes(16));
            date_default_timezone_set('Asia/Kolkata');
            $date = date('Y-m-d');
            
            // Update the user table with reset token and expiration date
            $query = "UPDATE user SET resettoken = '$reset_token', resettokenexpired = '$date' WHERE username = '$username'";
            if (mysqli_query($conn, $query)) {
                // Send email with reset token
                if (sendMail($username, $reset_token)) {
                    echo "<script>alert('Password reset link has been sent to your email. Please check your inbox.');</script>";
                } else {
                    echo "<script>alert('Failed to send reset link. Please try again later.');</script>";
                }
            } else {
                echo "<script>alert('Error updating token in database. Please try again.');</script>";
            }
        } else {
            echo "<script>alert('Email not found in our records. Please check and try again.');</script>";
        }
    } else {
        echo "<script>alert('Database connection issue. Please try again later.');</script>";
    }
}
?>







<!doctype html>
<html>

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <title>Forget Password</title>
  <link rel="stylesheet" href="../css/bootstrap.min.css" />
  <link rel="stylesheet" href="../css/style.css">
</head>

<body>
  <div id="wrapper-admin" class="body-content">
    <div class="container">
      <div class="row">
        <div class="col-md-offset-4 col-md-4">
          <img class="logo" src="images/_logo.jpeg">
          <h3 class="heading">Forget Password</h3><br><br>
          <form action="" method="POST">
            <div class="form-group">
              <label>Username</label>
              <input type="email" name="username" class="form-control" placeholder="Email Address" required>
            </div>
            <input type="submit" name="sendlink" class="btn btn-primary" value="Send Link"/>
          </form>
        </div>
      </div>
    </div>
  </div>
</body>

</html>

<?php
// Include database configuration file
include 'config.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

function sendMail($email, $reset_token)
{
    require('PHPMailer/PHPMailer.php');
    require('PHPMailer/SMTP.php');
    require('PHPMailer/Exception.php');
    
    $mail = new PHPMailer(true);
    try {
        //Server settings
        $mail->isSMTP();                                        //Send using SMTP
        $mail->Host       = 'smtp.gmail.com';                   //Set the SMTP server to send through
        $mail->SMTPAuth   = true;                               //Enable SMTP authentication
        $mail->Username   = 'vegdakrunal21@gmail.com';             //SMTP username (replace with your Gmail)
        $mail->Password   = 'cgkoeyjnsxemgxks';                    //SMTP password (replace with your Gmail app password)
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;        //Enable TLS encryption
        $mail->Port       = 465;                                //TCP port to connect to

        //Recipients
        $mail->setFrom('vegdakrunal21@gmail.com', 'News Site');
        $mail->addAddress($email);                              //Add the recipient

        //Content
        $mail->isHTML(true);                                    //Set email format to HTML
        $mail->Subject = 'Password reset link from News Site';
        $body = "We received a request to reset your password!<br>
                Click the link below to reset your password:<br>
                <a href='http://localhost/news-site/admin/reset_pass.php?username=$email&reset_token=$reset_token'>Reset Password</a>";
        $mail->Body = $body;

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log('Mail error: ' . $mail->ErrorInfo);
        return false;
    }
}

if (isset($_POST['sendlink'])) {
    // Escape input to avoid SQL injection
    $username = mysqli_real_escape_string($conn, $_POST['username']);

    // Check if the email exists in the database
    $sql = "SELECT * FROM user WHERE username='$username'";
    $result = mysqli_query($conn, $sql);

    if ($result && mysqli_num_rows($result) == 1) {
        // Generate a reset token and current date
        $reset_token = bin2hex(random_bytes(16));
        date_default_timezone_set('Asia/Kolkata');
        $date = date('Y-m-d');

        // Update the user with the reset token and expiry date
        $query = "UPDATE user SET resettoken='$reset_token', resettokenexpired='$date' WHERE username='$username'";
        if (mysqli_query($conn, $query)) {
            // Send the reset link to the user's email
            if (sendMail($username, $reset_token)) {
                echo "<script>alert('Password reset link sent to your email. Please check your inbox.');</script>";
            } else {
                echo "<script>alert('Failed to send the email. Please try again later.');</script>";
            }
        } else {
            echo "<script>alert('Database error. Please try again later.');</script>";
        }
    } else {
        echo "<script>alert('Email address not found. Please try again.');</script>";
    }
}
?>