<?php

use App\Http\Controllers\SmilePayPayin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;



Route::post('/payment/gateway/smilepay/payin',[SmilePayPayin::class, 'payIn']);