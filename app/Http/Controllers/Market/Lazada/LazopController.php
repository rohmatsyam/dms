<?php

namespace App\Http\Controllers\Market\Lazada;

use App\Http\Controllers\Controller;
use App\Models\SellerLazada;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Lazada\LazopClient;
use Lazada\LazopRequest;


class LazopController extends Controller
{
    private $lazadaUrl = "https://api.lazada.com.my/rest";    
    public function lazadaAuth(){
        $user_id = Auth::user()->id;        
        $seller = SellerLazada::where('user_id',$user_id)->first();
        if($seller){
            if($seller->token_expires_at <= Carbon::now()){
                return response()->json([
                    'code' => 200,
                    'status' => true,
                    'message' => "Access token is available",
                    'data' => $seller->access_token
                ], 200);
            }else{
                if($seller->refresh_expires_at <= Carbon::now()){
                    // refresh access token
                    $lazOp = new LazopClient($this->lazadaUrl, env('LAZADA_KEY'), env('LAZADA_SECRET'));
                    $lazRequest = new LazopRequest('/auth/token/refresh');
                    $lazRequest->addApiParam('refresh_token',$seller->refresh_token);
                    $response = $lazOp->execute($lazRequest);
                    $hasil = json_decode($response);                                                
                    $access_token = $hasil->access_token;
                    $token_expires_at = Carbon::now()->addDays(7)->format('Y-m-d');
                    $refresh_token = $hasil->refresh_token;                    
                    $seller->update([                
                        'access_token' => $access_token,
                        'token_expires_at' => $token_expires_at,
                        'refresh_token' => $refresh_token,                
                    ], 200);
                    return response()->json([
                        'code' => 200,
                        'status' => true,
                        'message' => "Success update access token",
                        'data' => $seller
                    ], 200);
                }else{                    
                    return Http::get("https://auth.lazada.com/oauth/authorize?response_type=code&force_auth=true&redirect_uri=".env('LAZADA_REDIRECT_URI')."&client_id=".env('LAZADA_KEY')."&redirect_auth=true");                    
                }
            }
        }else{                        
            return Http::get("https://auth.lazada.com/oauth/authorize?response_type=code&force_auth=true&redirect_uri=".env('LAZADA_REDIRECT_URI')."&client_id=".env('LAZADA_KEY')."&redirect_auth=true");            
        }
    }    
    
    public function callbackAuth(Request $request){
        dd(Auth::user()->id);
        $code = $request->code;        
        $lazOp = new LazopClient($this->lazadaUrl, env('LAZADA_KEY'), env('LAZADA_SECRET'));
        $lazRequest = new LazopRequest('/auth/token/create');
        // Request Params
        $lazRequest->addApiParam('code', $code);        
        // Process API 
        $response = $lazOp->execute($lazRequest); // JSON response

        // save token
        $hasil = json_decode($response);        
        $seller_id = $hasil->country_user_info[0]->seller_id;
        // $user_id = Auth::user()->id;
        $country = $hasil->country_user_info[0]->country;
        $access_token = $hasil->access_token;
        $token_expires_at = Carbon::now()->addDays(7)->format('Y-m-d');
        $refresh_token = $hasil->refresh_token;
        $refresh_expires_at = Carbon::now()->addDays(30)->format('Y-m-d');
        $seller = SellerLazada::create([                
            'seller_id' => $seller_id,
            'user_id' => 1,
            'country' => $country,
            'access_token' => $access_token,
            'token_expires_at' => $token_expires_at,
            'refresh_token' => $refresh_token,
            'refresh_expires_at' => $refresh_expires_at,
        ], 200);
        return response()->json([
            'code' => 200,
            'status' => true,
            'message' => "Success stored access token",
            'data' => $seller
        ], 200);
    }
}
