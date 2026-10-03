<?php

namespace App\Http\Controllers;

use App\Helpers\Curl;
use App\Helpers\Ip;
use App\Http\Requests\PurchaseCodeRequest;
use App\Libraries\RequestHandler;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;

class PurchaseCodeController extends Controller
{

    public function index()
    {
        return view('vendor.installer.purchase-code');
    }


    public function action(PurchaseCodeRequest $request)
    {
        // Check purchase code
        $purchase_code_data = $this->purchaseCodeChecker($request);

        if ($purchase_code_data->status == false) {
            return redirect()->back()->withInput($request->all())->withErrors($purchase_code_data->message);
        } else {
            Session::put('license_code', $request->get('purchase_code'));

        }

        return redirect()->route('LaravelInstaller::environment');
    }

    /**
     * @param Request $request
     * @return false|mixed|string
     */
    private function purchaseCodeChecker(Request $request)
    {
		return (object)['status' => true, 'message' => 'valid'];
    }

    public function licenseCodeActivate(Request $request)
    {

        $message = Session::get('license_code_error');
        return view('activate-license',compact('message'));
    }
}
