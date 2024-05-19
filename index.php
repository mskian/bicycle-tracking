<?php

ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(0);

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1);

header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');
header('X-Content-Type-Options: nosniff');
header('Strict-Transport-Security: max-age=63072000');
header('X-Robots-Tag: noindex, nofollow', true);

$error = "";
$apiKey = "";

define('API_KEY', '<REPLACE WITH AUTH KEY>');

if (!isset($_SESSION['form_enabled'])) {
    $_SESSION['form_enabled'] = false;
}
$isFormSubmitted = isset($_POST['submit_button']); 

function sanitize_input($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES | ENT_HTML5);
}

function generate_csrf_token() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}
$_SESSION['csrf_token'] = generate_csrf_token();

function verify_csrf_token($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

if ($isFormSubmitted) {
    if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
        $error = "CSRF token verification failed. Action aborted.";
    } else {
        $apiKey = sanitize_input($_POST['api_key']);

        if (empty($apiKey)) {
            $error = "API Key is required";
        } else {
            $validApiKey = API_KEY;
            if ($apiKey === $validApiKey) {
                $_SESSION['form_enabled'] = true;
            } else {
                $error = "Invalid API Key";
            }
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="HandheldFriendly" content="True" />
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="theme-color" content="#c7ecee">
<link rel="shortcut icon" href="data:image/x-icon;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAACXBIWXMAAA7EAAAOxAGVKw4bAAABqklEQVQ4jZ2Tv0scURDHP7P7SGWh14mkuXJZEH8cgqUWcklAsLBbCEEJSprkD7hD/4BUISHEkMBBiivs5LhCwRQBuWgQji2vT7NeYeF7GxwLd7nl4knMwMDMfL8z876P94TMLt+8D0U0EggQSsAjwMvga8ChJAqxqjTG3m53AQTg4tXHDRH9ABj+zf6oytbEu5d78nvzcyiivx7QXBwy46XOi5z1jbM+Be+nqVfP8yzuD3FM6rzIs9YE1hqGvDf15cVunmdx7w5eYJw1pcGptC9CD4gBUuef5Ujq/BhAlTLIeFYuyfmTZgeYv+2nPt1a371P+Hm1WUPYydKf0lnePwVmh3hnlcO1uc7yvgJUDtdG8oy98kduK2KjeHI0fzCQINSXOk/vlXBUOaihAwnGWd8V5r1uhe1VIK52V6JW2D4FqHZX5lphuwEE7ooyaN7gjLMmKSwYL+pMnV+MA/6+g8RYa2Lg2RBQbj4+rll7uymLy3coiuXb5PdQVf7rKYvojAB8Lf3YUJUHfSYR3XqeLO5JXvk0dhKqSqQQoCO+s5AIxCLa2Lxc6ALcAPwS26XFskWbAAAAAElFTkSuQmCC" />
<?php $current_page = "https://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]"; echo '<link rel="canonical" href="'.$current_page.'" />'; ?>


    <title>Bicycle Ride Tracker 🚴</title>
    <meta name="description" content="Bicycle Ride Tracker - Add Ride Name, Distance and Km 🚴."/>

    <link rel="preconnect" href="https://cdnjs.cloudflare.com">
    <link rel="preconnect" href="https://cdn.jsdelivr.net">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bulma/0.9.3/css/bulma.min.css" integrity="sha512-IgmDkwzs96t4SrChW29No3NXBIBv8baW490zk5aXvhCD8vuZM3yUSkbyTBcXohkySecyzIrUwiF/qV0cuPcL3Q==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto+Mono:ital,wght@0,100..700;1,100..700&display=swap" rel="stylesheet">

<style>
    html, body {
        background-color: #FDA7DF;
        padding-bottom: 20px;
    }
    body {
        font-family: "Roboto Mono", monospace;
        font-weight: 600;
        line-height: 1.6;
        word-wrap: break-word;
        -moz-osx-font-smoothing: grayscale;
        -webkit-font-smoothing: antialiased !important;
        -moz-font-smoothing: antialiased !important;
        text-rendering: optimizelegibility !important;
    }
    #quote-container {
        margin: 10px auto;
        border-radius: 10px;
        padding: 20px;
        background-color: #fff;
        font-family: "Roboto Mono", monospace;
    }
    #quote {
        font-family: "Roboto Mono", monospace;
        font-size: 20px;
        margin-bottom: 20px;
        color: #333;
    }
    #author {
        font-family: "Roboto Mono", monospace;
        font-style: italic;
        color: #777;
    }
    #image-container {
        margin-top: 20px;
    }
    #quote-card {
        max-width: 800px;
        margin: 10px auto;
        font-family: "Roboto Mono", monospace;
    }
    .error {
        font-family: "Roboto Mono", monospace;
        display: none;
    }
    input, button {
        font-family: "Roboto Mono", monospace;
    }
    .pagination-previous, .pagination-next {
        border-radius: 25px;
        background-color: #b71540;
        color: #ffffff;
        margin-right: 10px;
        transition: background-color 0.3s ease, color 0.3s ease;
    }
    .pagination-previous:hover, .pagination-next:hover {
        background-color: #b71540;
        color: #ffffff;
    }
    .pagination-previous:active, .pagination-next:active {
        background-color: #b71540;
        color: #ffffff;
    }
    .btn-box {
        font-weight: 600;
        font-size: 14px;
        font-family: "Roboto Mono", monospace;
        text-transform: uppercase;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
        border-radius: 32px;
        padding: 10px 20px;
        -moz-osx-font-smoothing: grayscale;
        -webkit-font-smoothing: antialiased !important;
        -moz-font-smoothing: antialiased !important;
        text-rendering: optimizelegibility !important;
    }
</style>

</head>
<body>

<section class="section">
<div class="container">
<div id="quote-card" class="card">
<div class="card-content">
<div id="quote-container">
<?php if (!$_SESSION['form_enabled']): ?>
<form method="POST" action="/">
<input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
<div class="field">
<label for="api_key" class="label">Auth Key:</label>
<div class="control">
<input type="password" class="input" id="api_key" name="api_key" value="<?= htmlspecialchars($apiKey); ?>" autocomplete="current-password">
</div>
</div>
<?php if (!empty($error)): ?>
<div class="notification is-danger"><button class="delete" onclick="this.parentNode.remove();"></button><P><?= $error; ?></P></span></div>
<?php endif; ?>
<div class="field">
<div class="control">
<input type="submit" class="button is-warning" name="submit_button" value="Submit">
</div>
</div>
</form>
<?php if (!empty($errors)): ?>
<div class="notification is-danger">
<button class="delete" onclick="this.parentNode.remove();"></button>
<?php foreach ($errors as $error): ?>
<p><?php echo $error; ?></p>
<?php endforeach; ?>
</div>
<?php endif; ?>
</div>
</div>
</div>
</div>
</section>
<?php else: ?>
<hr>
<h1 class="title is-size-5">🚴 Ride Tracker</h1>
<br>
<form id="rideForm">
<div class="field">
<label class="label">Ride Name</label>
<div class="control">
<input class="input is-rounded" type="text" id="rideName" placeholder="Enter ride name">
</div>
<p class="help is-danger error" id="rideNameError">Ride name is required</p>
</div>
<div class="field">
<label class="label">Distance (km)</label>
<div class="control">
<input class="input is-rounded" type="number" step="any" id="rideDistance" placeholder="Enter distance in km" min="0" step="0.01">
</div>
<p class="help is-danger error" id="rideDistanceError">Distance must be a number EX: 7 or 7.25</p>
</div>
<div class="field">
<label class="label">Date</label>
<div class="control">
<input class="input is-rounded" type="date" id="rideDate">
</div>
<p class="help is-danger error" id="rideDateError">Date is required</p>
</div>
<div class="control">
<button class="button is-link is-rounded btn-box" type="submit">Add Ride</button>
</div>
</form>
<hr>
<div class="notification is-success" id="successMessage" style="display: none;">
</div>
</div>
<div class="notification is-danger" id="emptydata" style="display: none;">
</div>
<div id="ridesList" style="display: none;">
<h2 class="title is-size-5">🚴 Rides</h2>
<br>
<ul id="rides">
</ul>
<nav class="pagination" role="navigation" aria-label="pagination">
<button class="button pagination-previous" id="prevPage">Previous</button>
<button class="button pagination-next" id="nextPage">Next</button>
</nav>
<br>
<div id="totalRideDistance"></div>
<br>
<canvas id="myChart" width="400" height="400"></canvas>
<hr>
</div>
</div>
</div>
</div>
</section>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js" integrity="sha512-NqRhTU0DQNHNUO0pTx6zPLJ11YhOqj4MRcvv0+amxJk+re07ykGhFuhMmrQpfTRAUx8nQ4EcMuX/m8cz2K8vIQ==" crossorigin="anonymous"></script>
<script src="script.js"></script>
<?php endif; ?>

</body>
</html>