<?PHP
    /*
        File description:
            This file is responsible for sending an email to the user that confirms their registration.

        Status codes for the email sending process:
            0 - Email sent successfully
            1 - Email failed to send

        Email options:
            1 - Registration notification
            2 - Password reset notification
    */

    $Email = isset($_POST['Email']) ? $_POST['Email'] : '';
    $UName = isset($_POST['UName']) ? $_POST['UName'] : '';
    $EmailOption = isset($_POST['EmailOption']) ? $_POST['EmailOption'] : '';

    define('MAILHOST', 'smtp.gmail.com');
    define('USERNAME', "crystalosdev@gmail.com");
    define('PASSWORD', "");
    define('SEND_FROM', "info@movieflix.com");
    define('SEND_FROM_NAME', "MovieFlix");
    define('REPLY_TO', "info@movieflix.com");
    define('REPLY_TO_NAME', "info@movieflix.com");

    use PHPMailer\PHPMailer\Exception;
    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\SMTP;

    require '../vendor/PHPMailer/PHPMailer/src/Exception.php';
    require '../vendor/PHPMailer/PHPMailer/src/PHPMailer.php';
    require '../vendor/PHPMailer/PHPMailer/src/SMTP.php';

    function sendMail($email, $subject, $message, $name) {
        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->SMTPAuth = true;
            $mail->Host = MAILHOST;
            $mail->Username = USERNAME;
            $mail->Password = PASSWORD;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            $mail->CharSet = 'UTF-8';

            $mail->setFrom(SEND_FROM, SEND_FROM_NAME);
            $mail->addAddress($email, $name);
            $mail->addReplyTo(REPLY_TO, REPLY_TO_NAME);

            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $message;
            $mail->AltBody = strip_tags($message);

            $mail->send();
            die("0");
        } catch (Exception $e) {
            echo "1";
            die("1");
        }
    }

    $MSGContent = "";
    $Title = "";

    switch($EmailOption){
        case '1':
            // Registration notification
            $Title = "Regisztrációs visszaigazolás";
            $MSGContent = "
                <html>
                    <head>
                        <style>
                            .MainBody{
                                border: 1px solid #333;
                                border-radius: 15px;
                                padding: 20px;
                                max-width: 600px;
                                margin-left: auto;
                                margin-right: auto;
                                background-color: black;
                            }
                        </style>
                    </head>
                    <body style='margin: 0; padding: 0; font-family: Arial, sans-serif; text-align: center;'>
                        <div class=\"MainBody\">
                            <h1 style='color: red; margin-top: 20px; margin-bottom: 0px;'>MovieFlix (Official)</h1>
                            <p style='color: #666; margin-top: 5px;'>
                                Köszönjük, hogy minket választott!
                            </p>
                            <p style='color: white'>
                                Regisztrációját sikeresen rögzítettük adatbázisunkban! Fiókja tehát máris a rendelkezésére áll. További teendője nincs.
                            </p>
                            <p style='color: white'>
                                Weboldalunkat ezen a linken: <a href='movies.movieflix.nhely.hu' style='color: #007BFF;'>MovieFix</a>,
                                vagy az alábbi gombra kattintva érheti el.
                            </p>
                            <a href='movies.movieflix.nhely.hu' style='display: inline-block; text-decoration: none; color: white; background-color: #007BFF; padding: 10px 20px; border-radius: 5px; font-size: 16px;'>
                                Visit MovieFix
                            </a>
                        </div>
                    </body>
                </html>
            ";
            break;
        case '2':
            // Password reset notification
            $Title = "Jelszó visszaállítás";
            $MSGContent = "
                <html>
                    <head>
                        <style>
                            .MainBody{
                                border: 1px solid #333;
                                border-radius: 15px;
                                padding: 20px;
                                max-width: 600px;
                                margin-left: auto;
                                margin-right: auto;
                                background-color: black;
                            }
                        </style>
                    </head>
                    <body style='margin: 0; padding: 0; font-family: Arial, sans-serif; text-align: center;'>
                        <div class=\"MainBody\">
                            <h1 style='color: red; margin-top: 20px; margin-bottom: 0px;'>MovieFlix (Official)</h1>
                            <p style='color: #666; margin-top: 5px;'>
                                Jelszó visszaállítás
                            </p>
                            <p style='color: white'>
                                Kérjük, hogy a jelszó visszaállításához kattintson az alábbi gombra.
                            </p>
                            <a href='movies.movieflix.nhely.hu' style='display: inline-block; text-decoration: none; color: white; background-color: #007BFF; padding: 10px 20px; border-radius: 5px; font-size: 16px;'>
                                Jelszó visszaállítása
                            </a>
                        </div>
                    </body>
                </html>";
            break;
    }

    if($MSGContent != "" && $Email != ""){
        sendMail($Email, $Title, $MSGContent, $UName);
    }

?>