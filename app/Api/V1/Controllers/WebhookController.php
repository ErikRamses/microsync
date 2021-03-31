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

    public function webhook(Request $request, $id)
    {
        $hmac_header = $request->header('X-Shopify-Hmac-Sha256');
        $data = file_get_contents('php://input');
        $secret_key = Shop::find($id)->secret_key;
        $verified = $this->verify_webhook($data, $hmac_header, $secret_key);

        if (var_export($verified, true)) {
            $new_log = new Log();
            $new_log->shop_id = $id;
            $new_log->event = $request->event;
            $module = explode('-', $request->event);
            $new_log->module = $module[1];
            $new_log->save();
            TestMessages::log('info', $request->all());

            return response()->json(['status' => 'ok', 'event' => $request->event, 'data' => $request->all()], 200);
        } else {
            TestMessages::log('info', 'Origin not verified');

            return response()->json(['status' => 'error', 'message' => 'Origin not verified'], 400);
        }
    }
}
