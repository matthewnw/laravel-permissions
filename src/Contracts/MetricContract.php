<?php

namespace Matthewnw\Permissions\Contracts;

use Illuminate\Http\Request;

Interface MetricContract
{
    public function calculate(Request $request);

    public function cacheFor();
}
