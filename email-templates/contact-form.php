<?php
// Try to locate project root (where index.html lives) by searching parent dirs
$startDir = __DIR__;
$root = null;
$search = $startDir;
for ($i = 0; $i < 6; $i++) {
	if (file_exists($search . '/.env') || file_exists($search . '/index.html') || file_exists($search . '/vendor/autoload.php')) {
		$root = $search;
		break;
	}
	$parent = dirname($search);
	if ($parent === $search) break;
	$search = $parent;
}

if ($root) {
	// load composer autoload if available
	$autoload = $root . '/vendor/autoload.php';
	if (file_exists($autoload)) {
		require_once $autoload;
	}
	// try vlucas/phpdotenv if present
	try {
		if (class_exists('\\Dotenv\\Dotenv')) {
			$dotenv = \Dotenv\Dotenv::createImmutable($root);
			$dotenv->safeLoad();
		}
	} catch (\Throwable $e) {
		error_log('Dotenv load failed in contact-form.php: ' . $e->getMessage());
	}

	// Fallback: if Dotenv not available or didn't populate envs, parse .env manually
	$envFile = $root . '/.env';
	if (file_exists($envFile) && (!getenv('GRECAPTCHA_SECRET_KEY') && !isset($_ENV['GRECAPTCHA_SECRET_KEY']))) {
		$lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
		foreach ($lines as $line) {
			$line = trim($line);
			if ($line === '' || strpos($line, '#') === 0) continue;
			$parts = explode('=', $line, 2);
			if (count($parts) !== 2) continue;
			$k = trim($parts[0]);
			$v = trim($parts[1]);
			if ((substr($v,0,1) === '"' && substr($v,-1) === '"') || (substr($v,0,1) === "'" && substr($v,-1) === "'")) {
				$v = substr($v,1,-1);
			}
			putenv(sprintf('%s=%s', $k, $v));
			$_ENV[$k] = $v;
			$_SERVER[$k] = $v;
		}
	}
} else {
	error_log('Unable to locate project root for .env in contact-form.php: ' . __DIR__);
}

// Ensure GRECAPTCHA_SECRET_KEY is available in $_ENV from getenv fallback
if ( empty( $_ENV['GRECAPTCHA_SECRET_KEY'] ) ) {
	$_ENV['GRECAPTCHA_SECRET_KEY'] = getenv('GRECAPTCHA_SECRET_KEY');
}


if( ! empty( $_POST['email'] ) ) {

	// Load SMTP settings from environment (set these in your .env on the host)
	$smtpHost = ! empty( $_ENV['SMTP_HOST'] ) ? $_ENV['SMTP_HOST'] : getenv('SMTP_HOST');
	$smtpUser = ! empty( $_ENV['SMTP_USERNAME'] ) ? $_ENV['SMTP_USERNAME'] : getenv('SMTP_USERNAME');
	$smtpPass = ! empty( $_ENV['SMTP_PASSWORD'] ) ? $_ENV['SMTP_PASSWORD'] : getenv('SMTP_PASSWORD');
	$smtpPort = ! empty( $_ENV['SMTP_PORT'] ) ? $_ENV['SMTP_PORT'] : getenv('SMTP_PORT');
	$smtpSecure = ! empty( $_ENV['SMTP_SECURE'] ) ? $_ENV['SMTP_SECURE'] : getenv('SMTP_SECURE');
	$mailFrom = ! empty( $_ENV['MAIL_FROM'] ) ? $_ENV['MAIL_FROM'] : getenv('MAIL_FROM');
	$mailFromName = ! empty( $_ENV['MAIL_FROM_NAME'] ) ? $_ENV['MAIL_FROM_NAME'] : getenv('MAIL_FROM_NAME');

	// Optional relay host (useful for Exchange connector): host that accepts mail for your domain (e.g. your-domain-com-hr.mail.protection.outlook.com)
	$smtpRelayHost = ! empty( $_ENV['SMTP_RELAY_HOST'] ) ? $_ENV['SMTP_RELAY_HOST'] : getenv('SMTP_RELAY_HOST');
	// If set to 'no', do not use authentication when connecting to relay host
	$smtpRelayAuth = ! empty( $_ENV['SMTP_RELAY_AUTH'] ) ? strtolower($_ENV['SMTP_RELAY_AUTH']) : strtolower(getenv('SMTP_RELAY_AUTH'));

	// Enable SMTP automatically if explicit SMTP host+creds provided, or if relay host is configured
	$enable_smtp = 'no';
	if ( ! empty( $smtpHost ) && ! empty( $smtpUser ) && ! empty( $smtpPass ) ) {
		$enable_smtp = 'yes';
	} elseif ( ! empty( $smtpRelayHost ) ) {
		$enable_smtp = 'yes';
	}

	// Email Receiver Address
	$receiver_email = 'info@k-tech.com.hr';

	// Email Receiver Name for SMTP Email
	$receiver_name 	= 'Your Name';

	// Email Subk-Tech - Kontatk forma
	$subject = 'k-Tech - Kontatk forma';

	// Google reCaptcha secret Key
	$grecaptcha_secret_key = $_ENV['GRECAPTCHA_SECRET_KEY'];	

	$from 	= $_POST['email'];
	$name 	= isset( $_POST['name'] ) ? $_POST['name'] : '';

	if( ! empty( $grecaptcha_secret_key ) && ! empty( $_POST['g-recaptcha-response'] ) ) {

		$token = $_POST['g-recaptcha-response'];

		// call curl to POST request
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,"https://www.google.com/recaptcha/api/siteverify");
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query( array( 'secret' => $grecaptcha_secret_key, 'response' => $token ) ) );
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$response = curl_exec($ch);
		curl_close($ch);
		$arrResponse = json_decode($response, true);

		// verify the response (supports reCAPTCHA v2 and v3)
		if ( empty( $arrResponse ) || ! isset( $arrResponse['success'] ) || $arrResponse['success'] != true ) {
			echo '{ "alert": "alert-danger", "message": "Your message could not been sent due to invalid reCaptcha!" }';
			die;
		}

		// If v3 response contains a score, enforce a minimum threshold (0.5) and optional action match
		if ( isset( $arrResponse['score'] ) ) {
			$score = floatval( $arrResponse['score'] );
			if ( $score < 0.5 ) {
				echo '{ "alert": "alert-danger", "message": "Your message could not been sent due to low reCaptcha score!" }';
				die;
			}
			if ( isset( $_POST['action'] ) && isset( $arrResponse['action'] ) && $arrResponse['action'] !== $_POST['action'] ) {
				echo '{ "alert": "alert-danger", "message": "reCaptcha action mismatch." }';
				die;
			}
		}
	}

	if( $_SERVER['REQUEST_METHOD'] == 'POST' ) {

		$prefix		= !empty( $_POST['prefix'] ) ? $_POST['prefix'] : '';
		$submits	= $_POST;
		$botpassed	= false;

		$fields = array();
		foreach( $submits as $name => $value ) {
			if( empty( $value ) ) {
				continue;
			}

			$name = str_replace( $prefix , '', $name );
			$name = function_exists('mb_convert_case') ? mb_convert_case( $name, MB_CASE_TITLE, "UTF-8" ) : ucwords($name);

			if( is_array( $value ) ) {
				$value = implode( ', ', $value );
			}

			$fields[$name] = nl2br( filter_var( $value, FILTER_SANITIZE_SPECIAL_CHARS ) );
		}

		$response = array();
		foreach( $fields as $fieldname => $fieldvalue ) {
			
                    $fieldname = '<tr>
                                                            <td align="right" valign="top" style="border-top:1px solid #dfdfdf; font-family:Arial, Helvetica, sans-serif; font-size:13px; color:#000; padding:7px 5px 7px 0;">' . $fieldname . ': </td>';
                    $fieldvalue = '<td align="left" valign="top" style="border-top:1px solid #dfdfdf; font-family:Arial, Helvetica, sans-serif; font-size:13px; color:#000; padding:7px 0 7px 5px;">' . $fieldvalue . '</td>
                                                    </tr>';
                    $response[] = $fieldname . $fieldvalue;

		}

		$message = '<html>
			<head>
				<title>HTML email</title>
			</head>
			<body>
				<table width="50%" border="0" align="center" cellpadding="0" cellspacing="0">
				<tr>
				<td colspan="2" align="center" valign="top"><img style="margin-top: 15px;" src="http://k-tech.com.hr/images/kTech_logo.png" ></td>
				</tr>
				<tr>
				<td width="50%" align="right">&nbsp;</td>
				<td align="left">&nbsp;</td>
				</tr>
				' . implode( '', $response ) . '
				</table>
			</body>
			</html>';
		if( $enable_smtp == 'no' ) { // Simple Email

			// Always set content-type when sending HTML email
			$headers = "MIME-Version: 1.0" . "\r\n";
			$headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
			// More headers
			$headers .= 'From: ' . $fields['Name'] . ' <' . $fields['Email'] . '>' . "\r\n";
			if( mail( $receiver_email, $subject, $message, $headers ) ) {

				// Redirect to success page
				$redirect_page_url = ! empty( $_POST['redirect'] ) ? $_POST['redirect'] : '';
				if( ! empty( $redirect_page_url ) ) {
					header( "Location: " . $redirect_page_url );
					exit();
				}

			   	//Success Message
			  	echo '{ "alert": "alert alert-success alert-dismissable", "message": "Vaša poruka je uspješno poslana!" }';
			} else {
				//Fail Message
			  	echo '{ "alert": "alert alert-danger alert-dismissable", "message": "Vaša poruka ne može biti poslana!" }';
			}
			
		} else { // SMTP
			// Email Receiver Addresses
			$toemailaddresses = array();
			$toemailaddresses[] = array(
				'email' => $receiver_email, // Your Email Address
				'name' 	=> $receiver_name // Your Name
			);

			require 'phpmailer/Exception.php';
			require 'phpmailer/PHPMailer.php';
			require 'phpmailer/SMTP.php';

					$mail = new PHPMailer\PHPMailer\PHPMailer();

					$mail->isSMTP();
					// If a relay host is configured, prefer it
					if ( ! empty( $smtpRelayHost ) ) {
						$mail->Host = $smtpRelayHost;
						// For relay we commonly use port 25 and no auth
						$mail->SMTPAuth = ( $smtpRelayAuth === 'no' ) ? false : true;
						if ( $mail->SMTPAuth ) {
							// If relay auth requested, try provided creds
							$mail->Username = $smtpUser;
							$mail->Password = $smtpPass;
						}
						$mail->SMTPSecure = '';
						$mail->Port = ! empty( $smtpPort ) ? intval( $smtpPort ) : 25;
					} else {
						$mail->Host     = $smtpHost;
						$mail->SMTPAuth = true;
						$mail->Username = $smtpUser;
						$mail->Password = $smtpPass;
						// SMTPSecure can be 'ssl' or 'tls' or empty. Default to ssl for 465
						if ( empty( $smtpSecure ) ) {
							$smtpSecure = ($smtpPort == 587) ? 'tls' : 'ssl';
						}
						if ( ! empty( $smtpSecure ) ) {
							$mail->SMTPSecure = $smtpSecure;
						}
						$mail->Port     = ! empty( $smtpPort ) ? intval( $smtpPort ) : 465;
					}
					// From address should be the authenticated mailbox or configured MAIL_FROM
					$fromAddress = ! empty( $mailFrom ) ? $mailFrom : ( isset($fields['Email']) ? $fields['Email'] : ( ! empty($smtpUser) ? $smtpUser : '' ) );
					$fromName = ! empty( $mailFromName ) ? $mailFromName : ( isset($fields['Name']) ? $fields['Name'] : 'Website' );
					if ( ! empty( $fromAddress ) ) {
						$mail->setFrom( $fromAddress, $fromName );
					}
					if ( isset( $fields['Email'] ) ) {
						$mail->addReplyTo( $fields['Email'], isset($fields['Name']) ? $fields['Name'] : '' );
					}
			
			foreach( $toemailaddresses as $toemailaddress ) {
				$mail->AddAddress( $toemailaddress['email'], $toemailaddress['name'] );
			}

			$mail->Subject = $subject;
			$mail->isHTML( true );

			$mail->Body = $message;

			if( $mail->send() ) {
				
				// Redirect to success page
				$redirect_page_url = ! empty( $_POST['redirect'] ) ? $_POST['redirect'] : '';
				if( ! empty( $redirect_page_url ) ) {
					header( "Location: " . $redirect_page_url );
					exit();
				}

			   	//Success Message
			  	echo '{ "alert": "alert alert-success alert-dismissable", "message": "Vaša poruka je uspješno poslana!" }';
			} else {
				//Fail Message
			  	echo '{ "alert": "alert alert-danger alert-dismissable", "message": "Vaša poruka ne može biti poslana!" }';
			}
		}
	}
} else {
	//Empty Email Message
	echo '{ "alert": "alert alert-danger alert-dismissable", "message": "Molimo Vas da unesete e-mail adresu!" }';
}