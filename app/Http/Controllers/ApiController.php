<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ApiController extends Controller
{
    protected $apiKey;
    protected $secret;

    public function __construct() {
        $this->$apiKey = get("FLICKR_API_KEY");
        $this->$secret = get("FLICKR_SECRET");
    }

    public function filter(array $data){

    }

    private function getPhotos(){

    }

    private function getComments(){

    }

    private function getPreview(){

    }
}
