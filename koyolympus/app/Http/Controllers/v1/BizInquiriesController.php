<?php

declare(strict_types=1);

namespace App\Http\Controllers\v1;

use App\Mail\BizInquiriesMail;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Mail;
use App\Http\Requests\BizInquiriesRequest;

class BizInquiriesController extends Controller
{
    public function __construct()
    {
    }

    /**
     * @param BizInquiriesRequest $request
     */
    public function sendBizInquiries(BizInquiriesRequest $request)
    {
        $params = [
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'opinion' => $request->input('opinion'),
        ];

        Mail::to(config('const.MAIL'))->send(new BizInquiriesMail($params));
    }
}
