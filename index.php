<?php
require_once 'SCBEasyAPI.php';

// รับข้อมูล Webhook จาก Line Messaging API
$input = file_get_contents('php://input');
$event = json_decode($input, true);
$channelAccessToken = '0gVWNByoHhgA5SrA/JcGElLcU3HtL2TKrMzccn7RgRziyvtSycLqLTEpHqiSOXAC7BawM+vjrBeFXhYDVPJ1XoX12ckzq5ARiIN5nvTVUQYfQShHzd6CY3hpPqen0SNJCFiKtLyFicsAgFMEoFWFHQdB04t89/1O/w1cDnyilFU=';
$channelSecret = 'cff2987561066dd826af14f0c2eb0337';

global $channelAccessToken,$channelSecret;
// ตรวจสอบถึงข้อมูลรูปภาพ
if ($event['events'][0]['type'] == 'message' && $event['events'][0]['message']['type'] == 'image') {
    $imageMessageId = $event['events'][0]['message']['id'];
    
    // เรียกใช้งาน Line Messaging API เพื่อรับรูปภาพ
    $responses = getImageContent($imageMessageId,$channelAccessToken);
    $name = time();
$localFilePath = 'images/' . $name . '.jpg'; 
file_put_contents($localFilePath, $responses);
move_uploaded_file($name, $localFilePath);
$output = shell_exec('"C:\Program Files (x86)\ZBar\bin\zbarimg" --quiet -q --raw '."\"$localFilePath\"".'2>&1');
$output = trim($output);
// $output = exec("zbarimg --quiet -q --raw \"$localFilePath\"");

if (!empty($output)) {

    $scb = new SCBEasyAPI();
   
    if ($scb->login()) {
        
         $response = $scb->getcheckslip($output);

        if ($response['status']['code'] == 1000) {
            unlink($localFilePath);

            $logosender = $response['data']['pullSlip']['sender']['bankLogo'];
            $sender = strrpos($logosender, '/');
            $sender = substr($logosender, $sender + 1);

            $logoreceiver = $response['data']['pullSlip']['receiver']['bankLogo'];
            $receiver = strrpos($logoreceiver, '/');
            $receiver = substr($logoreceiver, $receiver + 1);

            $timestamp = strtotime($response['data']['pullSlip']['dateTime']);
            $formattedDateAndTime = date("d/m/Y H:i", $timestamp);

            $messageArray = [
                'type' => 'bubble',
                'body' => [
                    'type' => 'box',
                    'layout' => 'vertical',
                    'contents' => [
                        [
                            'type' => 'text',
                            'text' => '✅ สลีปถูกต้อง',
                            'weight' => 'bold',
                            'color' => '#1DB446',
                            'size' => 'md',
                        ],
                        [
                            'type' => 'text',
                            'text' => '฿ '.$response['data']['amount'],
                            'weight' => 'bold',
                            'size' => 'xxl',
                            'margin' => 'md',
                        ],
                        [
                            'type' => 'text',
                            'text' => 'วันเวลา : '.$formattedDateAndTime,
                            'size' => 'xs',
                            'color' => '#aaaaaa',
                            'wrap' => true,
                        ],
                        [
                            'type' => 'separator',
                            'margin' => 'xl',
                        ],
                        [
                            'type' => 'box',
                            'layout' => 'horizontal',
                            'contents' => [
                                [
                                    'type' => 'box',
                                    'layout' => 'horizontal',
                                    'contents' => [
                                        [
                                            'type' => 'box',
                                            'layout' => 'horizontal',
                                            'contents' => [
                                                [
                                                    'type' => 'image',
                                                    'url' => 'https://adsfree.dev/banksimg_code/'.$sender,
                                                    'size' => '60px',
                                                ],
                                            ],
                                            'cornerRadius' => '50px',
                                            'width' => '50px',
                                            'height' => '50px',
                                            'margin' => 'sm',
                                        ],
                                    ],
                                    'position' => 'relative',
                                    'width' => '70px',
                                ],
                                [
                                    'type' => 'box',
                                    'layout' => 'vertical',
                                    'contents' => [
                                        [
                                            'type' => 'text',
                                            'text' => $response['data']['pullSlip']['sender']['name'],
                                            'size' => 'xs',
                                            'wrap' => false,
                                            'align' => 'start',
                                            'position' => 'relative',
                                            'margin' => '10px',
                                            'weight' => 'bold',
                                        ],
                                        [
                                            'type' => 'text',
                                            'text' => $response['data']['pullSlip']['sender']['accountNumber'],
                                        ],
                                    ],
                                    'offsetBottom' => '10px',
                                ],
                            ],
                            'margin' => 'xl',
                        ],
                        [
                            'type' => 'box',
                            'layout' => 'vertical',
                            'contents' => [
                                [
                                    'type' => 'box',
                                    'layout' => 'vertical',
                                    'contents' => [
                                        [
                                            'type' => 'text',
                                            'text' => '↓',
                                            'size' => '40px',
                                            'offsetStart' => '15px',
                                        ],
                                    ],
                                    'alignItems' => 'flex-start',
                                ],
                            ],
                        ],
                        [
                            'type' => 'box',
                            'layout' => 'horizontal',
                            'contents' => [
                                [
                                    'type' => 'box',
                                    'layout' => 'horizontal',
                                    'contents' => [
                                        [
                                            'type' => 'box',
                                            'layout' => 'horizontal',
                                            'contents' => [
                                                [
                                                    'type' => 'image',
                                                    'url' => 'https://adsfree.dev/banksimg_code/'.$receiver,
                                                    'size' => '60px',
                                                ],
                                            ],
                                            'cornerRadius' => '50px',
                                            'width' => '50px',
                                            'height' => '50px',
                                            'margin' => 'xs',
                                        ],
                                    ],
                                    'position' => 'relative',
                                    'width' => '70px',
                                ],
                                [
                                    'type' => 'box',
                                    'layout' => 'vertical',
                                    'contents' => [
                                        [
                                            'type' => 'text',
                                            'text' => $response['data']['pullSlip']['receiver']['name'],
                                            'size' => 'xs',
                                            'wrap' => false,
                                            'align' => 'start',
                                            'position' => 'relative',
                                            'margin' => '10px',
                                            'weight' => 'bold',
                                        ],
                                        [
                                            'type' => 'text',
                                            'text' => isset($response['data']['pullSlip']['receiver']['proxyNumber'])?$response['data']['pullSlip']['receiver']['proxyNumber']:$response['data']['pullSlip']['receiver']['accountNumber'],
                                        ],
                                    ],
                                    'offsetBottom' => '10px',
                                ],
                            ],
                            'margin' => 'lg',
                        ],
                    ],
                ],
                'styles' => [
                    'footer' => [
                        'separator' => true,
                    ],
                ],
            ];
            


            $flexMessageArray = [
                "type" => "flex",
                "altText" => "เช็คสลีป",
                "contents" => $messageArray
            ];


         replyFlexToGroup($channelAccessToken,$flexMessageArray,$event['events'][0]['replyToken']);


    }else{
        replyToUser($event['events'][0]['replyToken'],"เกิดข้อผิดพลาดทางธนาคาร\n หรือสลีปไม่ถูกต้อง",$channelAccessToken);
    }

        
}else{
    replyToUser($event['events'][0]['replyToken'],"api เกิดข้อผิดพลาด",$channelAccessToken);
}
        
    
} else {
    echo json_encode(["error" => "No QR code found in the image."]);
}





}

// ฟังก์ชันเรียกใช้งาน Line Messaging API เพื่อรับรูปภาพ
function getImageContent($messageId,$channelAccessToken) {
    $curl = curl_init();

    curl_setopt_array($curl, array(
      CURLOPT_URL => 'https://api-data.line.me/v2/bot/message/'.$messageId.'/content',
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => 'GET',
      CURLOPT_HTTPHEADER => array(
        "Authorization: Bearer $channelAccessToken"
      ),
    ));
    
    $response = curl_exec($curl);
    
    curl_close($curl);

    return   $response;
}

// ฟังก์ชันบันทึกรูปภาพในเซิร์ฟเวอร์

// ฟังก์ชันส่งข้อความตอบกลับไปยังผู้ใช้ผ่าน Line Messaging API
function replyToUser($replyToken, $messageText,$channelAccessToken) {
    $url = 'https://api.line.me/v2/bot/message/reply';
    
    $data = [
        'replyToken' => $replyToken,
        'messages' => [
            [
                'type' => 'text',
                'text' => $messageText
            ]
        ]
    ];
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer '.$channelAccessToken // แทน YOUR_CHANNEL_ACCESS_TOKEN
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    
    $response = curl_exec($ch);
    curl_close($ch);
}
function replyFlexToGroup($channelAccessToken, $flexMessageJson, $replyToken)
{

    $url = 'https://api.line.me/v2/bot/message/reply';
    $headers = ['Content-Type: application/json', 'Authorization: Bearer ' . $channelAccessToken];

    $data = [
        'replyToken' => $replyToken,
        'messages' => [$flexMessageJson],
    ];

    $options = [
        'http' => [
            'header' => $headers,
            'method' => 'POST',
            'content' => json_encode($data),
        ],
    ];

    $context = stream_context_create($options);
    $result = file_get_contents($url, false, $context);

    if ($result === false) {
        // Handle errors here
        error_log('Error sending Flex message: ' . error_get_last()['message']);
    }
}
?>
