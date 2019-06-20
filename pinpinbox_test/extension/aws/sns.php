<?php

namespace Extension\aws;

class sns
{
    private $client;

    function __construct()
    {
        $this->client = \Aws\Sns\SnsClient::factory([
            'credentials' => [
                'key' => 'AKIAIBIPL2YJTMBPG5UA',
                'secret' => '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',
            ],
            'region' => 'ap-northeast-1',
            'version' => '2010-03-31',
        ]);
    }

    function createPlatformEndpoint($identifier, $os, $token)
    {
        switch (SITE_EVN) {
            case 'production':
                $android = 'arn:aws:sns:ap-northeast-1:487425152686:app/GCM/pinpinbox';
                $ios = 'arn:aws:sns:ap-northeast-1:487425152686:app/APNS/pinpinbox_sns_IOS';
                break;

            default:
                $android = 'arn:aws:sns:ap-northeast-1:487425152686:app/GCM/pinpindemo';
                $ios = 'arn:aws:sns:ap-northeast-1:487425152686:app/APNS_SANDBOX/pinpinbox_apns';
                break;
        }

        $a_platform_application_arn = [
            'android' => $android,
            'ios' => $ios,
        ];

        try {
            return $this->client->createPlatformEndpoint([
                'CustomUserData' => $identifier,
                'PlatformApplicationArn' => $a_platform_application_arn[$os],
                'Token' => $token,
            ]);
        } catch (\Exception $e) {
            (new \userlogModel)->setException($e);
        }
    }

    //參考 http://docs.aws.amazon.com/aws-sdk-php/v3/api/api-sns-2010-03-31.html#createtopic
    function createTopic($type, $type_id)
    {
        try {
            return $this->client->createTopic([
                'Name' => implode('-', [SITE_EVN, $type, $type_id]),//僅能由大小寫字母, 數字, 下劃線和連字符組成, 長度在 1 ~ 256 之間
            ]);
        } catch (\Exception $e) {
            (new \userlogModel)->setException($e);
        }
    }

    //參考 http://docs.aws.amazon.com/aws-sdk-php/v3/api/api-sns-2010-03-31.html#deleteendpoint
    function deleteEndpoint($endpointarn)
    {
        $this->client->deleteEndpoint([
            'EndpointArn' => $endpointarn,
        ]);
    }

    //參考 http://docs.aws.amazon.com/aws-sdk-php/v3/api/api-sns-2010-03-31.html#deletetopic
    function deleteTopic($topicarn)
    {
        try {
            $this->client->deleteTopic([
                'TopicArn' => $topicarn,
            ]);
        } catch (\Exception $e) {
            (new \userlogModel)->setException($e);
        }
    }

    function getEndpointAttributes($endpointarn)
    {
        return $this->client->getEndpointAttributes([
            'EndpointArn' => $endpointarn,
        ]);
    }

    //參考 http://docs.aws.amazon.com/aws-sdk-php/v3/api/api-sns-2010-03-31.html#unsubscribe
    function unsubscribe($subscriptionarn)
    {
        try {
            $this->client->unsubscribe([
                'SubscriptionArn' => $subscriptionarn,
            ]);
        } catch (\Exception $e) {
            (new \userlogModel)->setException($e);
        }
    }

    //參考 http://docs.aws.amazon.com/aws-sdk-php/v3/api/api-sns-2010-03-31.html#publish
    function publish($targetarn, $message)
    {
        //自定義
        $data = ['message' => $message];

        $apns = json_encode([
            'aps' => [//APNS 制定，參照 https://developer.apple.com/library/prerelease/content/documentation/NetworkingInternet/Conceptual/RemoteNotificationsPG/PayloadKeyReference.html#//apple_ref/doc/uid/TP40008194-CH17-SW1
                'alert' => [
                    'title' => $data['message'],
                    'body' => $data['message']
                ],
                'sound' => 'default',
                "content-available" => 1
            ],
            'data' => $data,
        ]);

        return $this->client->publish([
            'Message' => json_encode([//Message is required
                'default' => $message,//如果  MessageStructure 為 json，則 Message 需含有至少一個頂層的 key 為 default，value 為 string 型態的數組
                'APNS' => $apns,
                'APNS_SANDBOX' => $apns,
                'GCM' => json_encode([
                    'data' => $data,
                    // 									//GCM 制定，參照 https://developers.google.com/cloud-messaging/http-server-ref#send-downstream
                    // 									'time_to_live',
                    // 									'collapse_key',
                ]),
            ]),

            // 					'MessageAttributes'=>[//參照 http://docs.aws.amazon.com/zh_cn/sns/latest/dg/SNSMessageAttributes.html
            // 							// Associative array of custom 'String' key names
            // 							'String'=>[
            // 									'DataType'=>'string',//DataType is required
            // 									'StringValue'=>'string',
            // 									'BinaryValue'=>'string',
            // 							],
            // 							// ... repeated
            // 					],

            'MessageStructure' => 'json',
            'Subject' => 'pinpinbox',
            'TargetArn' => $targetarn,
        ]);
    }

    function publishTopic($topicarn, $message, array $param = null)
    {
        try {
            //自定義
            $data = [
                'message' => $message,
                'title' => 'pinpinbox',
            ];

            if ($param) $data = array_merge($data, $param);

            $apns = json_encode([
                'aps' => [//APNS 制定，參照 https://developer.apple.com/library/prerelease/content/documentation/NetworkingInternet/Conceptual/RemoteNotificationsPG/PayloadKeyReference.html#//apple_ref/doc/uid/TP40008194-CH17-SW1
                    'alert' => [
                        'title' => $data['title'],
                        'body' => $message
                    ],
                    'sound' => 'default',
                    "content-available" => 1
                ],
                'data' => $data,
            ]);

            return $this->client->publish([
                'Message' => json_encode([//Message is required
                    'default' => $message,//如果  MessageStructure 為 json，則 Message 需含有至少一個頂層的 key 為 default，value 為 string 型態的數組
                    'APNS' => $apns,
                    'APNS_SANDBOX' => $apns,
                    'GCM' => json_encode([
                        'data' => $data,

                        //GCM 制定，參照 https://developers.google.com/cloud-messaging/http-server-ref#send-downstream
                        //'time_to_live',
                        //'collapse_key',
                    ]),
                ]),

                // 					'MessageAttributes'=>[//參照 http://docs.aws.amazon.com/zh_cn/sns/latest/dg/SNSMessageAttributes.html
                // 							// Associative array of custom 'String' key names
                // 							'String'=>[
                // 									'DataType'=>'string',//DataType is required
                // 									'StringValue'=>'string',
                // 									'BinaryValue'=>'string',
                // 							],
                // 							// ... repeated
                // 					],

                'MessageStructure' => 'json',
                'Subject' => 'pinpinbox',
                'TopicArn' => $topicarn,
            ]);
        } catch (\Exception $e) {
            (new \userlogModel)->setException($e);
        }
    }

    //參考 http://docs.aws.amazon.com/aws-sdk-php/v3/api/api-sns-2010-03-31.html#setendpointattributes
    function setEndpointAttributes(array $attributes, $endpointarn)
    {
        try {
            $this->client->setEndpointAttributes([
                'Attributes' => $attributes,
                'EndpointArn' => $endpointarn,
            ]);
        } catch (\Exception $e) {
            (new \userlogModel)->setException($e);
        }
    }

    //參考 http://docs.aws.amazon.com/aws-sdk-php/v3/api/api-sns-2010-03-31.html#subscribe
    function subscribe($topicarn, $protocol = 'application', $endpoint = null)
    {
        try {
            return $this->client->subscribe([
                'Endpoint' => $endpoint,
                'Protocol' => $protocol,
                'TopicArn' => $topicarn,
            ]);
        } catch (\Exception $e) {
            switch (PHP_SAPI) {
                case 'cli':
                    (new \cronjobModel)->setException($e);
                    break;

                default:
                    \userlogModel::setExceptionV2(\Lib\Exception::LEVEL_NOTICE, $e->getMessage());
                    break;
            }
        }
    }

    function subscriptionConfirmation()
    {
        if (is_post()) {
            try {
                $Message = \Aws\Sns\Message::fromRawPostData();

                (new \Aws\Sns\MessageValidator())->validate($Message);

                (new \userlogModel)->setReturn($Message->toArray());

                switch ($Message->offsetGet('Type')) {
                    case 'SubscriptionConfirmation':
                        file_get_contents($Message->offsetGet('SubscribeURL'));
                        break;
                }

                return $Message->toArray();
            } catch (\Exception $e) {
                (new \userlogModel)->setException($e);
            }
        }
    }
} 