<?php
declare(strict_types=1);

/* Keep server details out of browser responses. */
ini_set('display_errors', '0');
error_reporting(E_ALL);
header('Content-Type: text/plain; charset=UTF-8');
header('X-Content-Type-Options: nosniff');

const CONTACT_RECIPIENT = 'pacificcorporation20@gmail.com';
const CONTACT_SENDER = 'office@pacificcorporation.com';
const MAX_NAME_LENGTH = 120;
const MAX_SUBJECT_LENGTH = 180;
const MAX_MESSAGE_LENGTH = 5000;

function contact_response(string $message, int $status = 200): never
{
    http_response_code($status);
    echo $message;
    exit;
}

function contact_length(string $value): int
{
    return function_exists('mb_strlen') ? mb_strlen($value, 'UTF-8') : strlen($value);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    contact_response('Only POST requests are accepted.', 405);
}

/* A hidden field catches basic automated submissions. */
if (trim((string) ($_POST['website'] ?? '')) !== '') {
    contact_response('OK');
}

$name = trim((string) ($_POST['name'] ?? ''));
$email = trim((string) ($_POST['email'] ?? ''));
$subject = trim((string) ($_POST['subject'] ?? ''));
$message = trim((string) ($_POST['message'] ?? ''));

if ($name === '' || $email === '' || $subject === '' || $message === '') {
    contact_response('Please complete all required fields.', 422);
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    contact_response('Please enter a valid email address.', 422);
}

if (contact_length($name) > MAX_NAME_LENGTH || contact_length($subject) > MAX_SUBJECT_LENGTH || contact_length($message) > MAX_MESSAGE_LENGTH) {
    contact_response('Please shorten the submitted information and try again.', 422);
}

/* Prevent header injection while preserving the message body line breaks. */
$safeName = preg_replace('/[\r\n]+/', ' ', $name) ?? '';
$safeEmail = preg_replace('/[\r\n]+/', '', $email) ?? '';
$safeSubject = preg_replace('/[\r\n]+/', ' ', $subject) ?? '';
$mailSubject = 'Website enquiry: ' . $safeSubject;
$mailBody = "Name: {$safeName}\nEmail: {$safeEmail}\n\nMessage:\n{$message}";
$headers = [
    'From: Pacific Corporation Website <' . CONTACT_SENDER . '>',
    'Reply-To: ' . $safeEmail,
    'MIME-Version: 1.0',
    'Content-Type: text/plain; charset=UTF-8',
];

try {
    if (!function_exists('mail')) {
        throw new RuntimeException('PHP mail() is unavailable.');
    }

    $sent = mail(
        CONTACT_RECIPIENT,
        $mailSubject,
        $mailBody,
        implode("\r\n", $headers)
    );

    if ($sent) {
        contact_response('OK');
    }

    throw new RuntimeException('PHP mail() rejected the message.');
} catch (Throwable $error) {
    error_log('Pacific contact form error: ' . $error->getMessage());
    contact_response('The email service is not configured on this server. Please email pacificcorporation20@gmail.com directly.', 503);
}
