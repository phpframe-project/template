<?php

namespace App\Controllers\Default;

class DefaultController extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function testAction()
    {
        return $this->render("/default/test.twig", ["name" => "test"]);
    }
}