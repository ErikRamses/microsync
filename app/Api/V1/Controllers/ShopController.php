<?php

namespace App\Api\V1\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Shop;
use App\Models\Webhook;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log as TestMessages;

class ShopController extends Controller
{
    /**
     * Create a new AuthController instance.
     */
    public function __construct()
    {
        $this->middleware('jwt.auth', []);
    }

    public function install(Request $request)
    {
        $shop = $request->input('shop');
        $api_key = env('SHOPIFY_API_KEY', 'f1af0caeb6b619676cc5b231645e465c');
        $scopes = 'read_orders,write_products';
        $public_url = env('APP_PUBLIC_URL', '/');
        $redirect_uri = $public_url.'/api/webhooks/shopify/get-token';
        $nonce = uniqid();

        // Build install/approval URL to redirect to
        $install_url = 'https://'.$shop.'/admin/oauth/authorize?client_id='.$api_key.'&scope='.$scopes.'&redirect_uri='.urlencode($redirect_uri).'&state='.$nonce.'&grant_options[]=';

        TestMessages::log('info', $install_url);

        $current = Shop::where('hostname', $shop)->first();
        if (isset($current)) {
            $new_shop = $current;
        } else {
            $new_shop = new Shop();
        }

        $new_shop->hostname = $shop;
        $new_shop->apikey = $api_key;
        $new_shop->nonce = $nonce;

        if (isset($current)) {
            $status = $new_shop->update();
        } else {
            $status = $new_shop->save();
        }

        if ($status) {
            return response()->json(['status' => 'ok', 'event' => 'Shop install requested', 'url' => $install_url], 200);
        } else {
            return response()->json(['status' => 'error', 'message' => 'Shop is not registered'], 400);
        }
    }

    public function listWebhooks(Request $request, $id)
    {
        $shop = Shop::find($id);

        $base_url = 'https://'.$shop->hostname.'/admin/api/2021-04/webhooks.json';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'X-Shopify-Access-Token:'.$shop->token,
            'X-Shopify-Shop-Domain'.$shop->hostname,
            'X-Shopify-API-Version:2021-04', ]
        );
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, $base_url);
        // curl_setopt($ch, CURLOPT_POST, count($query));
        // curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($query));
        $result = curl_exec($ch);
        curl_close($ch);

        TestMessages::log('info', $result);
        // Store the access token
        $response = json_decode($result, true);

        if ($response) {
            return response()->json(['status' => 'ok', 'event' => 'Shop listed webhooks', 'data' => $response], 200);
        } else {
            return response()->json(['status' => 'error', 'message' => 'Shop is not registered'], 400);
        }
    }

    public function defaultWebhooks(Request $request, $id)
    {
        $shop_default = Shop::find(1);
        $shop = Shop::find($id);
        $base_url = 'https://'.$shop->hostname.'/admin/api/2021-04/webhooks.json';

        $client = new \GuzzleHttp\Client([
            'base_uri' => $base_url,
        ]);

        foreach ($shop_default->webhooks as $webhook) {
            $topic = $webhook->topic;
            $event = str_replace('/', '-', $topic);
            $webhook_url = env('APP_PUBLIC_URL', '/').'/api/webhooks/shopify/shop/'.$id.'?event='.$event;

            $webhook_params = ['webhook' => ['topic' => $topic, 'address' => $webhook_url, 'format' => 'json']];

            $response = $client->request('POST', $base_url, [
                'query' => $webhook_params,
                // $bodyType => $hasFile ? $multipart : $formParams,
                'headers' => [
                    'Content-Type' => 'application/json; charset=utf-8',
                    'X-Shopify-Access-Token' => $shop->token,
                    'X-Shopify-Shop-Domain' => $shop->hostname,
                    'X-Shopify-API-Version' => '2021-04',
                ],
            ]);

            $response = $response->getBody()->getContents();

            if ($response) {
                $new_webhook = new Webhook();

                $return = json_decode($response);

                $new_webhook->webhook_id = $return->webhook->id ?? null;
                $new_webhook->shop_id = $id;
                $new_webhook->topic = $topic;
                $new_webhook->address = $webhook_url;
                $new_webhook->save();

                TestMessages::log('info', json_encode(['status' => 'ok', 'event' => 'Webhook: '.$topic.' created']));
            } else {
                TestMessages::log('info', json_encode(['status' => 'error', 'event' => 'Webhook: '.$topic.' not created']));
            }
        }

        if ($base_url) {
            return response()->json(['status' => 'ok', 'event' => 'Shop default webhooks registered'], 200);
        } else {
            return response()->json(['status' => 'error', 'message' => 'Shop webhooks not registered'], 400);
        }
    }

    public function createWebhook(Request $request, $id)
    {
        $shop = Shop::find($id);

        $topic = $request->input('topic');
        $event = str_replace('/', '-', $topic);
        $webhook_url = env('APP_PUBLIC_URL', '/').'/api/webhooks/shopify/shop/'.$id.'?event='.$event;

        $webhook = ['webhook' => ['topic' => $topic, 'address' => $webhook_url, 'format' => 'json']];

        $base_url = 'https://'.$shop->hostname.'/admin/api/2021-04/webhooks.json';

        $client = new \GuzzleHttp\Client([
            'base_uri' => $base_url,
        ]);

        $response = $client->request('POST', $base_url, [
            'query' => $webhook,
            // $bodyType => $hasFile ? $multipart : $formParams,
            'headers' => [
                'Content-Type' => 'application/json; charset=utf-8',
                'X-Shopify-Access-Token' => $shop->token,
                'X-Shopify-Shop-Domain' => $shop->hostname,
                'X-Shopify-API-Version' => '2021-04',
            ],
        ]);

        $response = $response->getBody()->getContents();

        if ($response) {
            $new_webhook = new Webhook();

            $return = json_decode($response);

            TestMessages::log('info', $response);

            $new_webhook->webhook_id = $return->webhook->id ?? null;
            $new_webhook->shop_id = $id;
            $new_webhook->topic = $topic;
            $new_webhook->address = $webhook_url;
            $new_webhook->save();

            return response()->json(['status' => 'ok', 'event' => 'Webhook: '.$topic.' created', 'data' => $return], 200);
        } else {
            return response()->json(['status' => 'error', 'message' => 'Shop is not registered'], 400);
        }
    }

    public function updateWebhook(Request $request, $id)
    {
        $shop = Shop::find($id);

        $topic = $request->input('topic');
        $event = str_replace('/', '-', $topic);
        $webhook_url = env('APP_PUBLIC_URL', '/').'/api/webhooks/shopify/shop/'.$id.'?event='.$event;

        $wk = Webhook::where('topic', $topic)->where('shop_id', $id)->first();
        $webhook = ['webhook' => ['id' => $wk->webhook_id, 'address' => $webhook_url]];

        $base_url = 'https://'.$shop->hostname.'/admin/api/2021-04/webhooks/'.$wk->webhook_id.'.json';

        $client = new \GuzzleHttp\Client([
            'base_uri' => $base_url,
        ]);

        $response = $client->request('PUT', $base_url, [
            'query' => $webhook,
            // $bodyType => $hasFile ? $multipart : $formParams,
            'headers' => [
                'Content-Type' => 'application/json; charset=utf-8',
                'X-Shopify-Access-Token' => $shop->token,
                'X-Shopify-Shop-Domain' => $shop->hostname,
                'X-Shopify-API-Version' => '2021-04',
            ],
        ]);

        $response = $response->getBody()->getContents();

        if ($response) {
            $return = json_decode($response);

            TestMessages::log('info', json_encode($return));

            $wk->webhook_id = $return->webhook->id ?? null;
            $wk->address = $webhook_url;
            $wk->update();

            return response()->json(['status' => 'ok', 'event' => 'Webhook: '.$topic.' updated', 'data' => $return], 200);
        } else {
            return response()->json(['status' => 'error', 'message' => 'Shop is not registered'], 400);
        }
    }

    public function deleteWebhook($id)
    {
        $wk = Webhook::where('webhook_id', $id)->first();
        $shop = $wk->shop;
        $base_url = 'https://'.$shop->hostname.'/admin/api/2021-04/webhooks/'.$wk->webhook_id.'.json';

        $client = new \GuzzleHttp\Client([
            'base_uri' => $base_url,
        ]);

        $response = $client->request('DELETE', $base_url, [
            'headers' => [
                'Content-Type' => 'application/json; charset=utf-8',
                'X-Shopify-Access-Token' => $shop->token,
                'X-Shopify-Shop-Domain' => $shop->hostname,
                'X-Shopify-API-Version' => '2021-04',
            ],
        ]);

        $response = $response->getBody()->getContents();

        if ($response) {
            $return = json_decode($response);
            $wk->delete();

            return response()->json(['status' => 'ok', 'message' => 'Webhook: '.$wk->topic.' deleted correctly'], 200);
        } else {
            return response()->json(['status' => 'error', 'message' => 'Webhook id is not registered'], 400);
        }
    }
}
