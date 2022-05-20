<?php

namespace App\Http\Controllers\Market\Lazada;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Lazada\LazopClient;
use Lazada\LazopRequest;

class LazopController extends Controller
{
    private $lazadaUrl = "https://api.lazada.com.my/rest";
    private $apiKey = "108666";
    private $apiSecret = "4A7V4bhW9XBrZuCWrq8nOl540ibyxJms";

    public function lazadaAuth(Request $request){        
        //https://auth.lazada.com/oauth/authorize?response_type=code&force_auth=true&redirect_uri=${app call back url}&client_id=${appkey}
        $validator = Validator::make($request->all(), [
            'redirect_uri' => 'required',
            'client_id' => 'required',            
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors());
        }
        $redirect_uri = $request->redirect_uri;
        $client_id = $request->client_id;
        $response = Http::get("https://auth.lazada.com/oauth/authorize?response_type=code&force_auth=true&redirect_uri=${redirect_uri}&client_id=${client_id}&redirect_auth=true");
        return $response;
    }    
    
    public function callbackAuth(Request $request){
        $code = $request->code;

        $lazOp = new LazopClient($this->lazadaUrl, $this->apiKey, $this->apiSecret);
        $lazRequest = new LazopRequest('/auth/token/create');
        // Request Params
        $lazRequest->addApiParam('code', $code);
        // Process API 
        $response = $lazOp->execute($lazRequest); // JSON response

        // handling access token
        $hasil = json_decode($response);        
        $user_id = $hasil->country_user_info[0]->user_id;
        $country = $hasil->country_user_info[0]->country;
        $access_token = $hasil->access_token;

        $data = array(
            'user_id' => $user_id,
            'country' => $country,
            'access_token' => $access_token,
        );
        return json_encode($data);
    }

    public function refreshToken(Request $request){
        $validator = Validator::make($request->all(), [
            'token' => 'required',            
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors());
        }

        $lazOp = new LazopClient($this->lazadaUrl, $this->apiKey, $this->apiSecret);
        $lazRequest = new LazopRequest('/auth/token/refresh');
        $lazRequest->addApiParam('refresh_token',$request->token);
        var_dump($lazOp->execute($lazRequest));
    }
}
