<?php

namespace App\Http\Controllers\App;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;


class AppController extends BaseController
{
    use DispatchesJobs, ValidatesRequests;

    protected $view;

    public function __construct()
    {
        $this->view = 'app';
    }

}
