<?php

/**
 * Aemed
 *
 * @package AllergyLESS
 */
class Aemed extends BaseController {

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
        
        
        $final_response = array ();
        
        
        // Truncation of the previous data, for debug purposes
        $db->prepare ("TRUNCATE TABLE location");
        $db->execute ();

        $db->prepare ("TRUNCATE TABLE stats");
        $db->execute ();
        
        
        // Extracted from
        // https://opendata.aemet.es/centrodedescargas/productosAEMET?
        $aemed_stations_ids = [
            '7178I' => 'murcia',
            '7012D' => 'cartagena',
            '7023X' => 'fuente-alamo',
            '7227X' => 'alama',
            '7031X' => 'san-javier'
        ];
        
        
        // Create period of time of the data we are looking for
        $dates = new \DatePeriod (
            new \DateTime ('2017-07-04'),
            new \DateInterval ('P1D'),
            new \DateTime ('2017-07-08')
        );
        
        
        // Create and configure HTTP client to fetch data
        $this->client = new \GuzzleHttp\Client();
        $this->client->setDefaultOption ('verify', false);
        
        
        // Create stations
        foreach ($aemed_stations_ids as $aemed_station_id => $aemed_station_name) {
        
            // Get response
            $response = $this->getResponse ('api/observacion/convencional/datos/estacion/' . $aemed_station_id);
            
            
            // Check response
            if ( ! $response || ! is_array ($response)) {
                $this->_response->setContent (['ok' => false, 'message' => $response]);
                die ();
            }
            
            
            // Get the responses
            foreach ($response as $index => $medition) {
                
                // Store the location
                if (0 != $index) {
                    continue;
                }
                
                // Store the station
                $station = new Item (array (), 'location');
                $station->set ('name', $aemed_station_name);
                $station->set ('latitude', $medition['lat']);
                $station->set ('longitude', $medition['lon']);
                $station->set ('altitude', $medition['alt']);
                $station->store ();
                
                $station_id = $station->get ('id');
                
            }
            
            
            // For each date
            foreach ($dates as $init_date) {
            
                // Get dates
                $end_date = clone $init_date;
                
                
                // Create date end (the end of the day we are requesting for...)
                $end_date->modify ('+23hours +59minutes +59seconds'); 
                
                
                // Prepare Request URL
                $request_url = 
                    'api/valores/climatologicos/diarios/datos' 
                    . '/fechaini/' . $init_date->format ('Y-m-d\TH:i:s') . 'UTC' 
                    . '/fechafin/' . $end_date->format ('Y-m-d\TH:i:s') . 'UTC'
                    . '/estacion/' . $aemed_station_id
                ;
                
                
                // Fetch the response
                $response = $this->getResponse ($request_url);
                if ( ! $response) {
                    continue;
                }
                
                
                // Get only the data we want!
                $response = reset ($response);
                

                
                // Insert into the database
                $stats_data = [
                    'date' => $init_date->format ('Y-m-d H:i:s'),
                    'location_id' => $station_id,
                    
                    'precipitation' => $this->getFormatedValue ($response, 'prec'),
                    
                    'temperature_avg' => $this->getFormatedValue ($response, 'tmed'),
                    'temperature_min' => $this->getFormatedValue ($response, 'tmin'),
                    'temperature_max' => $this->getFormatedValue ($response, 'tmax'),
                    
                    'temperature_min_time' => $this->getDateTimeFromField ($init_date, $response, 'horatmin'),
                    'temperature_max_time' => $this->getDateTimeFromField ($init_date, $response, 'horatmax'),
                    
                    'wind_direction' => $this->getFormatedValue ($response, 'dir'),
                    'wind_velocity_avg' => $this->getFormatedValue ($response, 'velmedia'),
                    
                    'wind_velocity_max' => $this->getFormatedValue ($response, 'racha'),
                    'wind_velocity_max_time' => $this-> getDateTimeFromField ($init_date, $response, 'horaracha'),
                    
                    'pressure_max' => $this->getFormatedValue ($response, 'presMax'),
                    'pressure_max_time' => $this->getDateTimeFromField ($init_date, $response, 'horaPresMax'),
                    
                    'pressure_min' => $this->getFormatedValue ($response, 'presMin'),
                    'pressure_min_time' => $this->getDateTimeFromField ($init_date, $response, 'horaPresMin')
                    
                ];
                
                $stats = new Item ($stats_data, 'stats');
                try {
                    $stats->store ();
                    $final_response[$aemed_station_name][$init_date->format ('Y-m-d H:i:s')][] = $stats_data;
                    
                } catch (Exception $e) {
                    $final_response[$aemed_station_name][$init_date->format ('Y-m-d H:i:s')][] = 'error';
                }

                
            }
        }
    
        
        
        // Set response
        $this->_response->setContent (['ok' => true, 'data' => $final_response]);
        
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
    
        /** @var $aemed_api Array */
        global $aemed_api;    

    
        try {
            
            // Fetch data
            $res = $this->client->get ($aemed_api['base_url'] . $request_url, [
                'query' => ['api_key' => $aemed_api['key']]
            ]);
        
        // Handle client errors
        } catch (\Exception $e) {
            
            // Retorn an empty response in 404 cases
            if ('404' == $e->getResponse ()->getStatusCode ()) {
                return array ();
            }
            
            
            // Wait and retry
            sleep (10);
            return $this->getResponse ($request_url);
            
        }
    
    
        // Convert to JSON
        $dumb_response = json_decode ((string) $res->getBody (), true);
        
        
        // if the main answer is wrong, abort!
        if ('200' != $dumb_response['estado']) {
            return 'error in the main response';
            die ();
        }
        
        
        // Fetch real data
        $res = $this->client->get ($dumb_response['datos'], ['query' => ['api_key' => $aemed_api['key']]]);
        $response = json_decode ($res->getBody(), true);
        
        
        // Return an empty response if there is no data to handle
        if (isset ($response['estado']) && '404' == $response['estado']) {
            return array ();
        }
        
        
        // Return response
        return $response;
        
    }
    
    
    /**
     * getFormatedValue
     *
     * @param $response
     * @param $field
     *
     * @package AllergyLESS
     */    
    private function getFormatedValue ($response, $field) {
    
        if (isset ($response[$field]) && $response[$field] == 'Ip') {
            return null;
        }
    
        return isset ($response[$field]) ? str_replace (',', '.', $response[$field]) : null;
    }
    
    
    /**
     * getDateTimeFromField
     *
     * @param $datetime
     * @param $response
     * @param $field
     *
     * @package AllergyLESS
     */
    private function getDateTimeFromField ($datetime, $response, $field) {
        
        $date = clone $datetime; 
        
        // For cases when hour is "..:.." format
        if (isset ($response[$field]) && strpos ($response[$field], ":")) {
            $parts = explode (":", $response[$field]);
            $date->setTime ($parts[0], $parts[1]); 
            return $date->format ('Y-m-d H:i:s');
        }
        
        
        // For cases when hour is ".."
        if (isset ($response[$field]) && is_numeric ($response[$field])) {
            $date->setTime ($response[$field], 0); 
            return $date->format ('Y-m-d H:i:s');
        }
        
        return null;
        
    }
}
