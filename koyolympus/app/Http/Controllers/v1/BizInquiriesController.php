<?php

declare(strict_types=1);

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\BizInquiriesRequest;
use App\Mails\BizInquiriesMail;
use Illuminate\Support\Facades\Mail;

class BizInquiriesController extends Controller
{
    public function __construct()
    {
    }

    /**
     * お問い合わせメール送信処理
     *
     * @param  BizInquiriesRequest  $request
     */
    public function sendBizInquiries(BizInquiriesRequest $request): void
    {
        $params = [
            'name'    => $request->input('name'),
            'email'   => $request->input('email'),
            'opinion' => $request->input('opinion'),
        ];

        Mail::to(config('const.MAIL'))->send(new BizInquiriesMail($params));
    }
}
