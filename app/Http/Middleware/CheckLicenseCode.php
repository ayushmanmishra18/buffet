<?php

namespace App\Http\Middleware;

use App\Helpers\Curl;
use App\Helpers\Ip;
use App\Libraries\MyString;
use App\Libraries\RequestHandler;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;

class CheckLicenseCode
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next){
        $license_code_data = $this->licenseCodeChecker($request);
        if ($license_code_data->status == true) {
            if ((env('LICENSE_CODE') == null || env('LICENSE_CODE') == "") && isset($license_code_data->data->license_code)) {
                MyString::setEnv('LICENSE_CODE', $license_code_data->data->license_code);
            }
            return $next($request);
        }
        return $next($request);
        Session::put('license_code_error', $license_code_data->message);
        return redirect('/license-activate')->withError($license_code_data->message);
    }

    private function licenseCodeChecker(Request $request)
    {
		return (object)['status' => true, 'message' => 'rzr_valid'];
    }
}
