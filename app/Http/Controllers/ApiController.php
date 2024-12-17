<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;
use App\Services\FlickerService;

class ApiController extends Controller
{

    protected $flickerService;

    public function __construct(FlickerService $flickerService)
    {
        $this->flickerService = $flickerService;
    }

    
    public function photos(Request $request)
    {

        $type = $request->input('type');
        $tags = $request->input('tag');
        $page = $request->input('page');  
        $perPage = $request->input('per_page');  
        $photo_id = $request->input('photo_id');  
        switch ($type) {
            case 'grid':
                
                try {
                    $photosResponse = $this->flickerService->recentPhotos($page, $perPage);
            
                    
                    $photos = collect($photosResponse['photos']['photo'])->map(function ($photo) {
                        
                        $photoInfo = $this->flickerService->photoInfo($photo['id']);
        
            
                        return [
                            'id' => $photo['id'],
                            'title' => $photo['title'],
                            'owner' => [
                                'id' => $photoInfo['photo']['owner']['nsid'],
                                'username' => $photoInfo['photo']['owner']['username'],
                                'realname' => $photoInfo['photo']['owner']['realname'],
                            ],
                            'description' => $photoInfo['photo']['description']['_content'],
                            'url_p' => $this->flickerService->PhotoUrl($photo, 't'), // Preview pequeña
                            'url_m' => $this->flickerService->PhotoUrl($photo, 'w'), // Preview mediana
                            'url_g' => $this->flickerService->PhotoUrl($photo, 'b'), // Preview grande
                        ];
                    });
            
                    
                    return response()->json([
                        'status' => 'success',
                        'message' => 'Fotos recientes obtenidas exitosamente.',
                        'data' => $photos,
                        'pagination' => [
                            'page' => $page,
                            'per_page' => $perPage,
                            'total_pages' => $photosResponse['photos']['pages'] ?? 1,
                            'total_photos' => $photosResponse['photos']['total'] ?? 0,
                        ],
                    ], 200);
                } catch (\Exception $e) {
                    
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Ocurrió un error al obtener las fotos recientes: ' . $e->getMessage(),
                        'data' => [],
                    ], 500);
                }

                break;
            case 'search':

                try {
                    if (is_null($tags) || empty($tags)) {
                        return response()->json([
                            'status' => 'error',
                            'message' => 'El parámetro "tags" es obligatorio para la búsqueda.',
                            'data' => [],
                        ], 400);
                    }
                        $photosResponse = $this->flickerService->searchPhotos($tags, 'es-us', $perPage, $page);

                        if (isset($photosResponse['photos'])) {
                            $photos = collect($photosResponse['photos']['photo'])->map(function ($photo) {

                                $photoInfo = $this->flickerService->photoInfo($photo['id']);

                                return [
                                    'id' => $photo['id'],
                                    'title' => $photo['title'],
                                    'owner' => [
                                        'id' => $photoInfo['photo']['owner']['nsid'],
                                        'username' => $photoInfo['photo']['owner']['username'],
                                        'realname' => $photoInfo['photo']['owner']['realname'],
                                    ],
                                    'description' => $photoInfo['photo']['description']['_content'],
                                    'tags' => [],
                                    'server' => $photo['server'],
                                    'url_p' => $this->flickerService->PhotoUrl($photo, 't'), // Preview pequeña
                                    'url_m' => $this->flickerService->PhotoUrl($photo, 'w'), // Preview mediana
                                    'url_g' => $this->flickerService->PhotoUrl($photo, 'b'), // Preview grande
                                ];
                            });
                
                            return response()->json([
                                'status' => 'success',
                                'message' => 'Fotos encontradas exitosamente.',
                                'data' => $photos,
                                'pagination' => [
                                    'page' => $page,
                                    'per_page' => $perPage,
                                    'total_pages' => $photosResponse['photos']['pages'] ?? 1,
                                    'total_photos' => $photosResponse['photos']['total'] ?? 0,
                                ],
                            ], 200);
                        } else {
                            // Respuesta si no se encontraron fotos
                            return response()->json([
                                'status' => 'error',
                                'message' => 'No se encontraron fotos para la consulta proporcionada.',
                                'data' => [],
                            ], 404);
                        }
                } catch (\Exception $e) {
                    // Manejo de errores
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Ocurrió un error: ' . $e->getMessage(),
                        'data' => [],
                    ], 404);
                }
                
                break;
            case 'info':

                try{
                if (is_null($photo_id) || empty($photo_id)) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'El parámetro "photo_id" es obligatorio para obtener la información',
                        'data' => [],
                    ], 400);
                }

                $photoInfo = $this->flickerService->photoInfo($photo_id);
                $photoComments = $this->flickerService->photoComments($photo_id);

                $tags = collect($photoInfo['photo']['tags']['tag'])->map(function ($tag) {
                    return $tag['_content'];
                });

                    if (isset($photoInfo['photo'])) {
                        $photo = $photoInfo['photo'];
                        $data = [
                            'id' => $photo['id'],
                            'title' => $photo['title']['_content'],
                            'description' => $photo['description']['_content'],
                            'owner' => [
                                'username' => $photo['owner']['username'],
                                'realname' => $photo['owner']['realname'],
                                'location' => $photo['owner']['location'],
                            ],
                            'comments' => $photoComments['comments']['comment'] ?? [],
                            'dates' => $photo['dates'],
                            'tags' => $tags,
                            'url_p' => $this->flickerService->PhotoUrl($photo, 't'), // Preview pequeña
                            'url_m' => $this->flickerService->PhotoUrl($photo, 'w'), // Preview mediana
                            'url_g' => $this->flickerService->PhotoUrl($photo, 'b'), // Preview grande
                        ];

                        // Respuesta exitosa
                        return response()->json([
                            'status' => 'success',
                            'message' => 'Información de la foto obtenida exitosamente.',
                            'data' => $data,
                        ], 200);
                    } else {
                        // Respuesta si no se encontró información
                        return response()->json([
                            'status' => 'error',
                            'message' => 'No se encontró información para el photo_id proporcionado.',
                            'data' => [],
                        ], 404);
                    }
                } catch (\Exception $e) {
                    // Manejo de errores
                    return response()->json([
                        'status' => 'error',
                        'message' => 'No se encontro ninguna coincidencia para esta foto.',
                        'data' => [],
                    ], 404);
                }


                break;
            default:
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Debes enviar el tipo de solicitud',
                        'data' => [],
                    ], 500);
                break;
        }
    }

    private function filter(Request $data){
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
