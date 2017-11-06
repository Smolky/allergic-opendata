<?php

/**
 * OpenWeather
 *
 * @package AllergyLESS
 */
class OpenWeather extends BaseController {

    /** @var $client GuzzleHttp */
    protected $client;

    /**
     * handleRequest
     *
     * @package UCC
     */
    public function handleRequest () {
    
        /** @var String $url_preffix */
        $url_preffix = 'http://api.openweathermap.org/';
        
        
        /** @var String $start the Start date for UV */
        $start = '-60 days';
        
        
        /** @var String $end the end date for UV */
        $end = '-1 days';
        
        
        /** @var String */
        $uv_url_request = 
            'data/2.5/uvi/history?lat=38&lon=-1'
            . '&start=' . strtotime ($start, time ()) 
            . '&end=' . strtotime ($end, time ())
        ;
        
    
        // Create and configure HTTP client to fetch data
        $this->client = new \GuzzleHttp\Client();
        $this->client->setDefaultOption ('verify', false);
        
        
        // Generate response
        $response = [
            'murcia' => [
                'weather' => $this->getResponse ($url_preffix . 'data/2.5/weather?q=Murcia,es'),
                'co2'     => $this->getResponse ($url_preffix . 'pollution/v1/co/38,-1/2017Z.json'),
                'o3'      => $this->getResponse ($url_preffix . 'pollution/v1/o3/38,-1/2017Z.json'),
                'uv'      => $this->getResponse ($url_preffix . $uv_url_request),
            ]
        ];

        
        // Set response
        $this->_response->setContent (['ok' => true, 'data' => $response]);
        
    }
        
    
    /**
     * getResponse
     *
     * Private method to communicate with the API
     *
     * @param $request_url
     *
     * @package AllergyLESS
     */
    private function getResponse ($request_url) {
    
        /** @var $open_weather_api_key String */
        global $open_weather_api_key;    
    
        try {
            
            // Fetch data
            $res = $this->client->get ($request_url, [
                'query' => ['appid' => $open_weather_api_key]
            ]);
        
        // Handle client errors
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            
            // Retorn an empty response in 404 cases
            if ('404' == $e->getResponse ()->getStatusCode ()) {
                return array ();
            }
            
            
            // @todo
            return array ();
            
        // Undefined error
        } catch (\Exception $e) {
        
            // In case of error, it can be a too many requests error
            // in this case we will wait a period of time until
            // retry
            // @todo
            
        }
    
    
        // Convert to JSON
        return json_decode ($res->getBody (), true);
        
        
    }

}
