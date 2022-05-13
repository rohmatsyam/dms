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
    private $code;

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
    public function createToken(){                        
        $lazOp = new LazopClient($this->lazadaUrl, $this->apiKey, $this->apiSecret);
        $lazRequest = new LazopRequest('/auth/token/create');
        // Request Params
        $lazRequest->addApiParam('code', $this->code);
        // Process API 
        $response = $lazOp->execute($lazRequest); // JSON response
        return $response;
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

    public function callbackAuth(Request $request){
        $this->code = $request->code;
        return response()->json([
            'code' => 200,
            'data' => $request->code,            
            'message' => 'Succes add code to system'
        ], 200);
    }
}
