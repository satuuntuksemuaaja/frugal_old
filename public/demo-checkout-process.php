<?php
if( isset($_POST['name']) )
{

  $to = 'mail@mail.com'; // Replace with your email	
	$subject = 'Checkout from SkyForm!'; // Replace with your $subject
	$headers = 'From: ' . $_POST['email'] . "\r\n" . 'Reply-To: ' . $_POST['email'];
  
	$message = 'First Name: ' . $_POST['fname'] . "\n" .
						 'Last Name: ' . $_POST['lname']. "\n" .
						 'E-mail: ' . $_POST['email']. "\n" .
						 'Phone: ' . $_POST['phone']. "\n\n" .
						 'County ' . $_POST['country']. "\n" .
						 'City: ' . $_POST['city']. "\n" .
						 'Post code: ' . $_POST['code']. "\n" .
						 'Address: ' . $_POST['address']. "\n" .
						 'Additional info: ' . $_POST['info']. "\n" .
                         'Name on card ' . $_POST['name']. "\n" .
						 'Card: ' . $_POST['card']. "\n" .
						 'CVV: ' . $_POST['cvv']. "\n" .
						 'Expires: ' . $_POST['year']. ' ' . $_POST['month'] . "\n" ."\n\n\n";
		
	// Send email
	mail($to, $subject, $message, $headers);
}
?>