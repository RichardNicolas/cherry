<!doctype html>
<html>
    
<head>
    <meta charset="utf-8">
    <title>formHandler </title>
    <?php
        require '../vendor/autoload.php';
        
        use Aws\DynamoDb\DynamoDbClient;
        use Aws\DynamoDb\Exception\DynamoDbException;
        ?>
</head>

<body>
    <?php
        function randomPassword() {
            $alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
            $pass = array(); //remember to declare $pass as an array
            $alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
            for ($i = 0; $i < 8; $i++) {
                $n = rand(0, $alphaLength);
                $pass[] = $alphabet[$n];
            }
            return implode($pass); //turn the array into a string
        }
    
        try {
            $client = DynamoDbClient::factory(array(
                'credentials' => array(
                    'key'    => 'AKIAILB7YAAEVCHDD2KQ',
                    'secret' => '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',
                ),
                'region' => 'eu-west-1'
            ));

            $email = $_POST['email'];

            $response = $client->getItem(array(
            'TableName' => 'Users',
                'Key' => array(
                    'email' => array('S' => $email)
                )
            ));
            
            if ($response['Item'] != null) {
                $password = randomPassword();
                $client->putItem(array(
                    'TableName' => 'Users',
                    'Item' => array(
                        'email' => array('S' => $email),
                        'password' => array('S' => $password),
                        'lastname' => array('S' => $response['Item']['lastname']['S']),
                        'firstname' => array('S' => $response['Item']['firstname']['S'])
                    )
                ));
                $to      = $email;
                $subject = 'Cherry suppert - New password!';
                $headers = 'From: nrichard@neosoft-sas.com' . "\r\n" .
                    'Reply-To: nrichard@neosoft-sas.com' . "\r\n" .
                    'X-Mailer: PHP/' . phpversion();
                $msg = "Here is your new password : " . $password . "\nKeep it secret!";
                $msg = wordwrap($msg, 70);
                if (mail($to, $subject, $msg, $headers)) {
                    echo "Votre nouveau mot de passe vous a été envoyé par email à l'adresse : " . $to;
                } else {
                    echo "L'envoie de l'email a échoué pour une raison inconnue.";
                }
            } else {
                echo "Cette email ne correspond à aucun utlisateur.";
            }
            
        } catch (DynamoDbException $e) {
            echo '<p>Exception dynamoDB reçue : ',  $e->getMessage(), "\n</p>";
        } catch (Exception $e) {
            echo '<p>Exception reçue : ',  $e->getMessage(), "\n</p>";
        }
        ?>
</body>
</html>





