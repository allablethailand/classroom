<?php
    $userId = 'Uf88f87d469cbb80d8115e8411e9d5c50'; // เปลี่ยน
    $channel_access_token = '70xV47C8nMHwqTtGE9WVkrv3LAHBHiGaR3IZkOEMUkQ+9nvFPThHJwRJK0iXfjU9rmGmB3ISAlA6zvz1kxwr3fRmLy1FXicv7mMy/I8X6WWcSdi7dairgsIHBZzSJVI0ch+9zyCqljdE46LOwFrt6AdB04t89/1O/w1cDnyilFU='; // เปลี่ยน
    $messageData = [
        "to" => $userId,
        "messages" => [
            [
                "type" => "text",
                "text" => "สวัสดีครับ! ขอบคุณที่ใช้บริการของเรา"
            ]
        ]
    ];
    $ch = curl_init('https://api.line.me/v2/bot/message/push');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($messageData));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $channel_access_token
    ]);
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    echo "HTTP Status: $http_code<br>";
    echo "Response:<br>";
    echo "<pre>" . htmlspecialchars($response) . "</pre>";
?>
