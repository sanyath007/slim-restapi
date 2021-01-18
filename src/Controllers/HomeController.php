<?php

namespace App\Controllers;

use App\Controllers\Controller;

class HomeController extends Controller
{
    public function home($request, $response, $args)
    {
        // your code here
        // use $this->view to render the HTML
        return $response;
    }
}