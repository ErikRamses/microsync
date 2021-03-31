<?php

namespace App\Api\V1\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Shop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ShopController extends Controller
{
    /**
     * Create a new AuthController instance.
     */
    public function __construct()
    {
        $this->middleware('jwt.auth', []);
    }

    public function storeShop(Request $request)
    {
        // $client = new \GuzzleHttp\Client();

        $hostname = $request->input('shop');
        $username = $request->input('user');
        $password = $request->input('password');
        // $version = '2021-01';
        // $resource = 'shop.json';
        // $response = $client->request('GET', 'https://'.$username.':'.$password.'@'.$shop.'.myshopify.com/admin/api/'.$version.'/'.$resource);

        $config = [
            'ShopUrl' => $hostname.'.myshopify.com',
            'ApiKey' => $username,
            'Password' => $password,
        ];

        $shopify = new \PHPShopify\ShopifySDK($config);

        $shop = $shopify->Shop->get();

        Log::log('info', $shop);

        if (count($shop)) {
            $current = Shop::where('hostname', $hostname)->first();
            if (isset($current)) {
                $new_shop = $current;
            } else {
                $new_shop = new Shop();
            }

            $new_shop->hostname = $hostname;
            $new_shop->apikey = $username;
            $new_shop->email = $shop['email'];
            $new_shop->password = $password;
            $new_shop->secret_key = $request->input('secret_key');

            if (isset($current)) {
                $status = $new_shop->update();
            } else {
                $status = $new_shop->save();
            }

            if ($status) {
                return response()->json(['status' => 'ok', 'event' => 'Shop registered', 'data' => $shop], 200);
            } else {
                return response()->json(['status' => 'error', 'message' => 'Shop is not registered'], 400);
            }
        } else {
            return response()->json(['status' => 'error', 'message' => 'Shop is not on platform'], 400);
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $shop = Shop::find($id);
            if (! $shop) {
                return response()->json(
                  ['error' => 'Shop doesnt exists'], 400);
            }
            $shop->delete();
        } catch (Exception $e) {
            DB::rollBack();

            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 400);
        }
        DB::commit();

        return response()->json(['status' => 'ok', 'message' => 'Shop deleted correctly'], 200);
    }
}
