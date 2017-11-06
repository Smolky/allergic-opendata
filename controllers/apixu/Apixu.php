<?php

/**
 * Apixu
 *
 * @package AllergyLESS
 */
class Apixu extends BaseController {

    /** @var $client GuzzleHttp */
    protected $client;

    /**
     * handleRequest
     *
     * @package UCC
     */
    public function handleRequest () {
    
        // Get database
        $db = $this->_container['connection'];
        

        // Create and configure HTTP client to fetch data
        $this->client = new \GuzzleHttp\Client();
        $this->client->setDefaultOption ('verify', false);
        

        // Get response
        // Weather
        $response = [
            'murcia' => [
                'weather' => $this->getResponse ('current.json?q=Murcia'),
                'weather-history' => $this->getResponse ('history.json?q=Murcia&dt=2017-01-01'),
            ]
        ];
        
        
        // Set response
        $this->_response->setContent (['ok' => true, 'response' => $response]);
        
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
    
        /** @var $apixu_api Array */
        global $apixu_api;    

    
        try {
            
            // Fetch data
            $res = $this->client->get ($apixu_api['base_url'] . $request_url, [
                'query' => [
                    'key' => $apixu_api['key']
                ]
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
