<?php
/**
 * Static Website Header
 * Contains security headers, CSP, preloads, CSS, and fonts
 */

// Security Headers
header("Strict-Transport-Security: max-age=31536000; includeSubDomains");
header("X-Content-Type-Options: nosniff");
header("X-Frame-Options: DENY");
header("X-XSS-Protection: 1; mode=block");
header("Referrer-Policy: strict-origin-when-cross-origin");
header("Permissions-Policy: geolocation=(), microphone=(), camera=()");

// Content Security Policy
// $csp = "default-src 'self';" .
//        "script-src 'self' 'unsafe-inline' https://bunny.net;" .
//        "style-src 'self' 'unsafe-inline' https://fonts.bunny.net;" .
//        "font-src 'self' https://fonts.bunny.net;" .
//        "img-src 'self' data: blob:;" .
//        "connect-src 'self';" .
//        "frame-ancestors 'none';" .
//        "base-uri 'self';" .
//        "form-action 'self';" .
//        "upgrade-insecure-requests;";
// header("Content-Security-Policy: " . $csp);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="PenPalace - Your Premium Korean and Japanese Stationery Store">
    <title>PenPalace<?= isset($page_title)
        ? " - " . htmlspecialchars($page_title)
        : "" ?></title>

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="assets/images/favicon.ico">

    <!-- Preload Critical Assets -->
    <link rel="preload" href="assets/css/styles.css" as="style">
    <link rel="preload" href="assets/js/main.js" as="script">
    <link rel="preload" href="assets/images/logo.png" as="image">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- CSS -->
    <link rel="stylesheet" href="assets/css/styles.css">

    <!-- Tabler Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
</head>
<body>
    <main>
