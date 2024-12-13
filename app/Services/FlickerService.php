<?php

namespace App\Services;

use GuzzleHttp\Client;

class FlickerService
{
    protected $client;
    protected $Key;

    public function __construct()
    {
        $this->client = new Client(['base_uri' => 'https://api.flickr.com/services/rest/']);
        $this->Key = config('services.flickr.api_key');
    }

    public function searchPhotos(string $tags = null, $lang = 'es-us', $perPage, $page )
    {
        $params = [
            'method' => 'flickr.photos.search',
            'api_key' => $this->Key,
            'format' => 'json',
            'nojsoncallback' => 1,
            'tags' => $tags,
            'tag_mode' => 'all',
            'lang' => $lang,
            'per_page' => $perPage,
            'page' => $page,
        ];

        $response = $this->client->get('', ['query' => $params]);

        return json_decode($response->getBody(), true);
    }

    public function PhotoUrl($photo, $size = 'm')
    {
        return "https://live.staticflickr.com/{$photo['server']}/{$photo['id']}_{$photo['secret']}_{$size}.jpg";
    }


    public function recentPhotos($Page, $perPage )
    {
        $params = [
            'method' => 'flickr.photos.getRecent',
            'api_key' => $this->Key,
            'format' => 'json',
            'nojsoncallback' => 1,
            'lang' => 'es-us',
            'per_page' => $perPage,
            'page' => $Page,
        ];

        $response = $this->client->get('', ['query' => $params]);

        return json_decode($response->getBody(), true);
    }
    
    public function photoInfo($photoId)
    {
        $params = [
            'method' => 'flickr.photos.getInfo',
            'api_key' => $this->Key,
            'format' => 'json',
            'lang' => 'es-us',
            'nojsoncallback' => 1,
            'photo_id' => $photoId,
        ];
    
        $response = $this->client->get('', ['query' => $params]);
        return json_decode($response->getBody(), true);
    }
}
