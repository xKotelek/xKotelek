<?php

session_start();

if (!isset($_GET['code'])) {
    header('Location: https://discord.com/api/oauth2/authorize?client_id=1204965592147296256&response_type=code&redirect_uri=http%3A%2F%2Flocalhost%2Fquickpay%2Fauth%2Fflow%2Fcallback&scope=identify+guilds+email+guilds.members.read');
    exit();
}

$token_url = 'https://discord.com/api/oauth2/token';
$client_id = '1204965592147296256';
$client_secret = '6bl6fgRRbt1c4SvD5vDzcB1PBmvLvP4A';
$redirect_uri = 'http://localhost/quickpay/auth/flow/callback';
$code = $_GET['code'];

$data = [
    'client_id' => $client_id,
    'client_secret' => $client_secret,
    'grant_type' => 'authorization_code',
    'code' => $code,
    'redirect_uri' => $redirect_uri,
    'scope' => 'identify email guilds guilds.members.read',
];

$options = [
    'http' => [
        'header' => "Content-type: application/x-www-form-urlencoded\r\n",
        'method' => 'POST',
        'content' => http_build_query($data),
    ],
];

$context = stream_context_create($options);
$response = file_get_contents($token_url, false, $context);

if ($response === FALSE) {
    die('Failed to request token');
}

$token_data = json_decode($response, true);

if (!isset($token_data['access_token'])) {
    die('Access token not found in response');
}

$access_token = $token_data['access_token'];

$api_url = 'https://discord.com/api/users/@me';

$options = [
    'http' => [
        'header' => "Authorization: Bearer $access_token\r\n",
    ],
];

$context = stream_context_create($options);
$user_data = file_get_contents($api_url, false, $context);

if ($user_data === FALSE) {
    die('Failed to request user data');
}

$user_data = json_decode($user_data, true);

$guilds_api_url = 'https://discord.com/api/users/@me/guilds';
$guilds_response = file_get_contents($guilds_api_url, false, $context);

if ($guilds_response === FALSE) {
    die('Failed to request user guilds');
}

$user_guilds = json_decode($guilds_response, true);

$filtered_guilds = array_filter($user_guilds, function($guild) {
    return ($guild['permissions'] & 0x20) || ($guild['permissions'] & 0x8);
});

$formatted_guilds = [];

foreach ($filtered_guilds as $guild) {
    $is_added = false;

    if(isset($guild['icon']) && $guild['icon'] !== '') {
        $guild_icon = 'https://cdn.discordapp.com/icons/' . $guild['id'] . '/' . $guild['icon'] . '.png';
    } else {
        $guild_icon = '../assets/default.webp';
    }

    $guild_data = [
        'name' => $guild['name'],
        'id' => $guild['id'],
        'icon_url' => $guild_icon
    ];

    $formatted_guilds[] = $guild_data;
}

$_SESSION['member_guilds'] = $formatted_guilds;

$url = "http://localhost/quickpay/api/check_user.php?name=".$user_data['username'];

$curl = curl_init($url);
curl_setopt($curl, CURLOPT_URL, $url);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

$resp = curl_exec($curl);
curl_close($curl);

$result_array = json_decode($resp, true);

if ($result_array["user_exists"]) {
    $_SESSION['user_email'] = $result_array['email'];
    $_SESSION['user_name'] = $result_array['name'];
    $_SESSION['user_avatar'] = $result_array['avatar'];
    $_SESSION['logged'] = true;
    $member_guilds_json = json_encode($_SESSION['member_guilds'], JSON_UNESCAPED_UNICODE); // Używamy flagi JSON_UNESCAPED_UNICODE, aby zapobiec zamianie polskich znaków na kody Unicode

    $url = "http://localhost/quickpay/api/update_user_guilds.php?name=".$_SESSION['user_name']."&member_guilds=".$member_guilds_json;

    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

    $resp = curl_exec($curl);
    curl_close($curl);
} else {
    if(isset($user_data['avatar']) && $user_data['avatar'] !== '') {
        $avatar = 'https://cdn.discordapp.com/avatars/' . $user_data['id'] . '/' . $user_data['avatar'] . '.png';
    } else {
        $avatar = '../assets/default.webp';
    }
    $user_email = $user_data['email'];
    $member_guilds_json = json_encode($_SESSION['member_guilds'], JSON_UNESCAPED_UNICODE); // Używamy flagi JSON_UNESCAPED_UNICODE, aby zapobiec zamianie polskich znaków na kody Unicode

    $url = "http://localhost/quickpay/api/init_user.php?name=$user_username&email=$user_email&avatar=$avatar&member_guilds=$member_guilds_json";

    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

    $resp = curl_exec($curl);
    curl_close($curl);

    $_SESSION['user_email'] = $user_email;
    $_SESSION['user_name'] = $user_username;
    $_SESSION['user_avatar'] = $avatar;
}

header('Location: http://localhost/quickpay/dash/?name=' . $_SESSION['user_name'] . '&email=' . $_SESSION['user_email'] . '&avatar=' . $_SESSION['user_avatar']);
exit();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>quickpay auth</title>
    <link rel="icon" href="https://cdn.discordapp.com/emojis/1204978647941779516.webp?size=512">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;700&display=swap');

        *,
        html,
        body {
            transition-timing-function: ease-in-out;
        }

        body {
            scroll-behavior: smooth;
            font-family: 'Roboto', sans-serif;
            margin: 0;
            background: black;
            color: white;
            overflow-x: hidden;
            overflow-y: hidden;
            user-select: none;
            font-weight: 500;
        }

        .container {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            transition: .5s;
            text-align: center;
        }

        .inner {
            background: rgb(19, 19, 19);
            border: 1px solid rgb(41, 41, 41);
            z-index: 99;
            border-radius: 15px;
            transition: .5s;
        }

        .container .inner {
            padding: 25px 40px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="inner">
            <img src="https://cdn.discordapp.com/emojis/1204866149184053338.gif?size=96&quality=lossless" width="96" height="96">
            <h1>Processing authorization...</h1>
        </div>
    </div>
</body>
</html>