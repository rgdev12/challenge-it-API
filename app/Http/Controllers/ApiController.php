<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;

class ApiController extends Controller
{
    public function filter(Request $data){
        $response['preview'] = $this->getPreview($data->photoId, $data->size);
        $response['comments'] = $this->getPreview($data->photoId);

        return $response;
    }

    private function getPhotos(){

    }

    private function getComments($photoId){
        
    }

    private function getPreview($photoId, $size){
        $apiKey = getenv("FLICKR_API_KEY");
        
        $curl = curl_init();
        
        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://www.flickr.com/services/rest/?method=flickr.photos.getSizes&api_key={$apiKey}&photo_id=54198922103&format=json&nojsoncallback=1",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
        ));
        
        $response = curl_exec($curl);
        
        curl_close($curl);
        $data = json_decode($response, true);

        foreach ($data['sizes']['size'] as $key => $item) {
            if($item['label'] == $size){
                return $item['source'];
            }
        }        
    }
}
