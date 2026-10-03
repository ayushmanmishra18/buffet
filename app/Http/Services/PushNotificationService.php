<?php

namespace App\Http\Services;

use Google\Client;
use App\Models\User;
use App\Enums\NotificationType;
use GuzzleHttp\Client as guzzle;
use App\Notifications\OrderUpdated;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Exception\RequestException;


class PushNotificationService
{

    public function sendPushNotification($notification, $topicName = null)
    {
        try {
            $url = 'https://fcm.googleapis.com/v1/projects/' . setting('projectId') . '/messages:send';
            $headers = [
                'Authorization: Bearer ' . $this->getAccessToken(),
                'Content-Type: application/json'
            ];

            $messageData = [
                'notification' => [
                    'title' => $notification->title,
                    'body' => $notification->description,
                ],
                'data' => [
                    'title' => $notification->title,
                    'body' => $notification->description,
                    'sound' => 'default',
                    'image' => $notification->image ?? null,
                    'topicName' => $topicName,
                ],
                'webpush' => [
                    'headers' => [
                        'Urgency' => 'high'
                    ]
                ]
            ];

            if ($notification->type == NotificationType::SINGLE) {
                $webToken = User::where('id', $notification->customer_id)
                    ->whereNotNull('web_token')
                    ->value('web_token');

                if (!$webToken) {
                    throw new \Exception('Web token not found for customer ID ' . $notification->customer_id);
                }

                $messageData['token'] = $webToken;
                $message = ['message' => $messageData];
            } else {
                $webTokens = User::whereNotNull('web_token')
                    ->pluck('web_token')
                    ->toArray();

                if (empty($webTokens)) {
                    throw new \Exception('No web tokens found for sending notifications.');
                }

                foreach ($webTokens as $webToken) {
                    $messageData['token'] = $webToken;
                    $message = ['message' => $messageData];

                    $result = $this->sendRequest($url, $headers, $message);
                    if ($result === false) {
                        throw new \Exception('Failed to send notification to token: ' . $webToken);
                    }
                }
            }
            return $this->sendRequest($url, $headers, $message);
        } catch (\Exception $exception) {
            Log::error('Failed to send notification: ' . $exception->getMessage());
        }
    }

    private function sendRequest($url, $headers, $message)
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($message));

        $result = curl_exec($ch);
        curl_close($ch);

        return $result ?: false;
    }


    public function fcmSubscribe($request)
    {

        $deviceToken = $request->device_token;
        $topic = env('FCM_TOPIC') . '_' . str_replace(['@', '.', '+'], ['_', '_', ''], $request->topic);


        $headers = array(
            'Authorization: key=' . env('FCM_SECRET_KEY'),
            'Content-Type: application/json'
        );
        $this->fcmGlobalSubscribe($request);
        try {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, "https://iid.googleapis.com/iid/v1/$deviceToken/rel/topics/$topic");
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_POSTFIELDS, array());
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $result = curl_exec($ch);
            return response()->json([
                'status' => 200,
                'message' => 'Subscribed',
            ], 200);
        } catch (\Exception $exception) {
            return response()->json([
                'status'  => 401,
                'message' => $exception,
            ], 401);
        }
    }


    public function fcmGlobalSubscribe($request)
    {
        $deviceToken = $request->device_token;
        $topic = env('FCM_TOPIC');

        $headers = array(
            'Authorization: key=' . env('FCM_SECRET_KEY'),
            'Content-Type: application/json'
        );

        try {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, "https://iid.googleapis.com/iid/v1/$deviceToken/rel/topics/$topic");
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_POSTFIELDS, array());
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $result = curl_exec($ch);
            return response()->json([
                'status' => 200,
                'message' => 'Global Subscription',
            ], 200);
        } catch (\Exception $exception) {
            return response()->json([
                'status'  => 401,
                'message' => $exception,
            ], 401);
        }
    }


    public function fcmUnsubscribe($request)
    {
        $request->validate([
            'device_token' => 'required',
            'topic' => 'nullable',
        ]);

        $deviceToken = $request->token;

        $headers = array(
            'Authorization: key=' . env('FCM_SECRET_KEY'),
            'Content-Type: application/json'
        );

        try {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, "https://iid.googleapis.com/v1/web/iid/$deviceToken");
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $result = curl_exec($ch);

            return response()->json([
                'status' => 200,
                'message' => 'Unsubscribed',
            ], 200);
        } catch (\Exception $exception) {
            return response()->json([
                'status'  => 401,
                'message' => $exception,
            ], 401);
        }
    }

    public function sendWebNotification($order)
    {
        $url = 'https://fcm.googleapis.com/v1/projects/' . setting('projectId') . '/messages:send';
        $FcmWabToken = User::where(['id' => auth()->user()->id])->whereNotNull('web_token')->pluck('web_token')->toArray();
        $message = [
            "message" => [
                'token' => $FcmWabToken[0],
                "notification" => [
                    "body" => 'A new order has been placed at ' . ucfirst($order->restaurant->name) . ' The order amount is ' . $order->total,
                    "title" => "New Order #" . $order->id,
                    'sound'     => 'default', // Optional
                    'icon'      => public_path('images/fav.png'),
                ]
            ]
        ];

        $encodedData = json_encode($message);
        $headers = [
            'Authorization: Bearer ' . $this->getAccessToken(),
            'Content-Type: application/json'
        ];


        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        // Disabling SSL Certificate support temporarly
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $encodedData);
        // Execute post
        $result = curl_exec($ch);
        if ($result === FALSE) {
            die('Curl failed: ' . curl_error($ch));
        }
        // Close connection
        curl_close($ch);
        // FCM response
        return true;
    }

    public  function NotificationReservationRestaurant($reservation, $user, $type)
    {
        try {

            $FcmWabToken = User::where(['id' => $user->id])->whereNotNull('web_token')->pluck('web_token')->toArray();

            $message = [
                "message" => [
                    "token" => $FcmWabToken[0],
                    "notification" => [
                        'title' => 'Hello, ' . $user->name,
                        'body'  => "A New Reservation #" . $reservation->id . " has been created by " . $reservation->user->name,
                    ]
                ]
            ];

            $url = 'https://fcm.googleapis.com/v1/projects/' . setting('projectId') . '/messages:send';

            $headers = [
                'Authorization: Bearer ' . $this->getAccessToken(),
                'Content-Type: application/json'
            ];

            $ch = curl_init();

            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($message));
            $result = curl_exec($ch);
            if ($result === FALSE) {
                die('Curl failed: ' . curl_error($ch));
            }
            curl_close($ch);
            return $result;
        } catch (\Exception $exception) {
        }
    }

    public  function NotificationReservationCustomer($reservation, $user, $type)
    {
        try {

            $FcmWabToken = User::where(['id' => $user->id])->whereNotNull('web_token')->pluck('web_token')->toArray();

            $message = [
                "message" => [
                    "token" => $FcmWabToken[0],
                    "notification" => [
                        'title' => 'Hello ' . $user->name,
                        'body'  => "Reservation  #" . $reservation->id . " has been created By " . $reservation->restaurant->name,
                    ]
                ]
            ];

            $url = 'https://fcm.googleapis.com/v1/projects/' . setting('projectId') . '/messages:send';

            $headers = [
                'Authorization: Bearer ' . $this->getAccessToken(),
                'Content-Type: application/json'
            ];

            $ch = curl_init();

            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($message));
            $result = curl_exec($ch);
            if ($result === FALSE) {
                die('Curl failed: ' . curl_error($ch));
            }
            curl_close($ch);
            return $result;
        } catch (\Exception $exception) {
        }
    }

    public  function NotificationForRestaurant($order, $user, $type)
    {
        try {
            $FcmWabToken = User::where(['id' => $user->id])->whereNotNull('web_token')->pluck('web_token')->toArray();
            $message = [
                "message" => [
                    "token" => $FcmWabToken[0],
                    "notification" => [
                        'title' => 'Hello ' . $user->name,
                        'body'  => "A new order #" . $order->id . " has been created by " . $order->user->name,
                    ]
                ]
            ];

            $url = 'https://fcm.googleapis.com/v1/projects/' . setting('projectId') . '/messages:send';

            $headers = [
                'Authorization: Bearer ' . $this->getAccessToken(),
                'Content-Type: application/json'
            ];

            $ch = curl_init();

            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($message));
            $result = curl_exec($ch);
            if ($result === FALSE) {
                die('Curl failed: ' . curl_error($ch));
            }
            curl_close($ch);
            return $result;
        } catch (\Exception $exception) {
            Log::error("FCM Error: " . $exception->getMessage());
        }
    }

    public  function NotificationForCustomer($order, $user, $type)
    {
        try {
            $FcmWabToken = User::where(['id' => $user->id])->whereNotNull('web_token')->pluck('web_token')->toArray();
            $message = [
                "message" => [
                    "token" => $FcmWabToken[0],
                    "notification" => [
                        'title' => 'Hello ' . $user->name,
                        'body'  => "Order #" . $order->order_code . " has been Successfully Created.",
                    ]
                ]
            ];

            $url = 'https://fcm.googleapis.com/v1/projects/' . setting('projectId') . '/messages:send';

            $headers = [
                'Authorization: Bearer ' . $this->getAccessToken(),
                'Content-Type: application/json'
            ];

            $ch = curl_init();

            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($message));
            $result = curl_exec($ch);
            if ($result === FALSE) {
                die('Curl failed: ' . curl_error($ch));
            }
            curl_close($ch);
            return $result;
        } catch (\Exception $exception) {
            Log::error("FCM Error: " . $exception->getMessage());
        }
    }

    public  function NotificationForAppRestaurant($order, $user, $type)
    {
        try {
            $FcmWabToken = User::where(['id' => $user->id])->whereNotNull('device_token')->pluck('device_token')->toArray();

            $message = [
                "data" => [
                    "token" => $FcmWabToken[0],
                    "data" => [
                        'title' => 'Hello ' . $user->name,
                        'body'  => "A new order #" . $order->id . " has been created by " . $order->user->name,
                    ],
                ]
            ];
            

            $url = 'https://fcm.googleapis.com/v1/projects/' . setting('projectId') . '/messages:send';

            $headers = [
                'Authorization: Bearer ' . $this->getAccessToken(),
                'Content-Type: application/json'
            ];

            $ch = curl_init();

            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($message));
            $result = curl_exec($ch);
            if ($result === FALSE) {
                die('Curl failed: ' . curl_error($ch));
            }
            curl_close($ch);
            return $result;
        } catch (\Exception $exception) {
            Log::error("FCM Error: " . $exception->getMessage());
        }
    }

    public function NotificationForAppCustomer($order, $user, $type)
{
    try {
        $FcmWabToken = User::where('id', $user->id)->whereNotNull('device_token')->value('device_token');

        $message = [
            "data" => [
                "token" => $FcmWabToken[0],
                "data" => [
                    'title' => 'Hello ' . $user->name,
                    'body'  => "Order #" . $order->order_code . " has been Successfully Created.",
                ],
            ]
        ];

        $url = 'https://fcm.googleapis.com/v1/projects/' . setting('projectId') . '/messages:send';

        $headers = [
            'Authorization: Bearer ' . $this->getAccessToken(),
            'Content-Type: application/json'
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($message));
        
        $result = curl_exec($ch);
        if ($result === FALSE) {
            Log::error('Curl failed: ' . curl_error($ch));
        }
        curl_close($ch);
        
        Log::info("FCM Response: " . $result);
        return $result;

    } catch (\Exception $exception) {
        Log::error("FCM Error: " . $exception->getMessage());
    }
}


    public  function sendNotificationOrderUpdate($order, $user, $type)
    {
        try {

            $fcmTokens = User::where(['id' => $user->id])->whereNotNull('device_token')->pluck('device_token')->toArray();
            $FcmWabToken = User::where(['id' => $user->id])->whereNotNull('web_token')->pluck('web_token')->toArray();
            if (!blank($fcmTokens)) {
                if ($type == 'customer') {
                    $user->notify(new OrderUpdated($order, $fcmTokens));
                } else if ($type == 'deliveryboy') {
                    $user->notify(new OrderUpdated($order, $fcmTokens));
                }
            }

            if (!blank($FcmWabToken)) {
                if ($type == 'customer') {
                    $user->notify(new OrderUpdated($order, $FcmWabToken));
                } else if ($type == 'deliveryboy') {
                    $user->notify(new OrderUpdated($order, $FcmWabToken));
                }
            }
        } catch (\Exception $exception) {
        }
    }

    public function getAccessToken()
    {
        $client = new Client();
        $client->setAuthConfig(storage_path('app/firebase/service-account-file.json'));
        $client->addScope('https://www.googleapis.com/auth/firebase.messaging');
        $client->useApplicationDefaultCredentials();

        $token = $client->fetchAccessTokenWithAssertion()['access_token'];

        return $token;
    }

    public function sendWebNotificationn($order)
    {
        $projectId = 'new-project-3e70e';
        $url = 'https://fcm.googleapis.com/v1/projects/' . $projectId . '/messages:send';
        $FcmWebToken = User::where(['id' => auth()->user()->id])->whereNotNull('web_token')->pluck('web_token')->first();
        if (!$FcmWebToken) {
            Log::error('No web token found for user.');
            return false;
        }

        $message = [
            "message" => [
                'token' => $FcmWebToken,
                "notification" => [
                    "body" => 'A new order has been placed at ' . ucfirst($order->restaurant->name) . '. The order amount is ' . $order->total,
                    "title" => "New Order #" . $order->id,
                ]
            ]
        ];

        $encodedData = json_encode($message);
        $headers = [
            'Authorization' => 'Bearer ' . $this->getAccessToken(),
            'Content-Type' => 'application/json'
        ];

        try {
            $client = new guzzle();
            $response = $client->post($url, [
                'headers' => $headers,
                'body' => $encodedData,
            ]);

            if ($response->getStatusCode() != 200) {
                Log::error('Failed to send notification. Response: ' . $response->getBody());
                return false;
            }
        } catch (RequestException $e) {
            Log::error('Failed to send notification: ' . $e->getMessage());
            return false;
        }

        Log::info('Notification sent successfully.');
        return true;
    }
}
