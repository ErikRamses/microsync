<?php

namespace App\Api\V1\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Log;
use App\Models\Shop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log as TestMessages;

class WebhookController extends Controller
{
    public function verify_webhook($data, $hmac_header, $secret_key)
    {
        $calculated_hmac = base64_encode(hash_hmac('sha256', $data, $secret_key, true));

        return hash_equals($hmac_header, $calculated_hmac);
    }

    public function installTest(Request $request)
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
            header('Location: '.$install_url);
        // return response()->json(['status' => 'ok', 'event' => 'Shop install requested', 'url' => $install_url], 200);
        } else {
            return response()->json(['status' => 'error', 'message' => 'Shop is not registered'], 400);
        }
    }

    public function getToken(Request $request)
    {
        $shop = $request->input('shop');
        $api_key = env('SHOPIFY_API_KEY', 'f1af0caeb6b619676cc5b231645e465c');
        $shared_secret = env('SHOPIFY_SECRET_KEY', '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');
        $data = file_get_contents('php://input');
        $hmac = $request->hmac;

        $verified = $this->verify_webhook($data, $hmac, $shared_secret);

        if (var_export($verified, true)) {
            // Set variables for our request
            $query = [
                'client_id' => $api_key, // Your API key
                'client_secret' => $shared_secret, // Your app credentials (secret key)
                'code' => $request->input('code'), // Grab the access key from the URL
            ];

            // Generate access token URL
            $access_token_url = 'https://'.$shop.'/admin/oauth/access_token';

            // Configure curl client and execute request
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_URL, $access_token_url);
            curl_setopt($ch, CURLOPT_POST, count($query));
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($query));
            $result = curl_exec($ch);
            curl_close($ch);

            TestMessages::log('info', $result);
            // Store the access token
            $result = json_decode($result, true);
            $access_token = $result['access_token'];

            // TestMessages::log('info', $result);

            $store = Shop::where('hostname', $shop)->first();
            $store->token = $access_token;
            $store->secret_key = $shared_secret;
            $store->update();

            return response()->json(['status' => 'ok', 'message' => 'Shop token already registered'], 200);
        } else {
            // Someone is trying to be shady!
            return response()->json(['status' => 'error', 'message' => 'Shop is not on platform'], 400);
        }
    }

    public function webhook(Request $request, $id)
    {
        $hmac_header = $request->header('X-Shopify-Hmac-Sha256');
        $data = file_get_contents('php://input');
        $secret_key = Shop::find($id)->secret_key;
        $verified = $this->verify_webhook($data, $hmac_header, $secret_key);

        if (var_export($verified, true)) {
            TestMessages::log('info', $request->all());
            $new_log = new Log();
            $new_log->shop_id = $id;
            $new_log->event = $request->event;
            $module = explode('-', $request->event);
            $new_log->module = $module[1];
            $new_log->save();

            return response()->json(['status' => 'ok', 'event' => $request->event, 'data' => $request->all()], 200);
        } else {
            TestMessages::log('info', 'Origin not verified');

            return response()->json(['status' => 'error', 'message' => 'Origin not verified'], 400);
        }
    }
}
