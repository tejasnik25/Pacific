<?php
header('Content-Type: text/plain; charset=UTF-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  echo 'Only POST requests are accepted.';
  exit;
}

$name = trim((string) ($_POST['name'] ?? ''));
$email = trim((string) ($_POST['email'] ?? ''));
$subject = trim((string) ($_POST['subject'] ?? ''));
$message = trim((string) ($_POST['message'] ?? ''));

if ($name === '' || $subject === '' || $message === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
  http_response_code(422);
  echo 'Please complete all required fields with a valid email address.';
  exit;
}

$safeName = preg_replace('/[\r\n]+/', ' ', $name);
$safeSubject = preg_replace('/[\r\n]+/', ' ', $subject);
$safeEmail = preg_replace('/[\r\n]+/', '', $email);
$recipient = 'pacificcorporation20@gmail.com';
$mailSubject = 'Website enquiry: ' . $safeSubject;
$mailBody = "Name: {$safeName}\nEmail: {$safeEmail}\n\nMessage:\n{$message}";
$headers = "From: Pacific Corporation Website <office@pacificcorporation.com>\r\n";
$headers .= "Reply-To: {$safeEmail}\r\n";
$headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

if (mail($recipient, $mailSubject, $mailBody, $headers)) {
  echo 'OK';
  exit;
}

http_response_code(500);
echo 'Your request could not be submitted right now. Please email us directly.';
?>
