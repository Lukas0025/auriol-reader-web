<?php
    /**
     * Auriol wetaher station web reader
     * auriol API
     * @author Lukáš Plevač <lukasplevac@gmail.com>
     * @date 26.7.2020
     */

    include 'php/weather-icons.php';

    class auriol_api {

        private $db;

        /**
         * Api conscructor
         * open database file
         * @return null
         */
        function __construct($lang) {
            $this->db = new SQLite3('/var/local/auriol-db.sl3', SQLITE3_OPEN_READONLY);
            $this->lang = $lang;
        }

        /**
         * API interface function
         * get date of last update
         * @return str date-time
         */
        public function lastUpdateStr() {
            $statement = $this->db->prepare('SELECT "created" FROM "temperature" ORDER BY "created" DESC LIMIT 1');
            $result = $statement->execute();
          
            return $result->fetchArray(SQLITE3_ASSOC)['created'];  
        }

        /**
         * inner function
         * get rain amount today in mm
         * @return float
         */
        private function getRainToday() {
            //rain now
            $statement = $this->db->prepare('SELECT * FROM "pluviometer" ORDER BY "created" DESC LIMIT 1');
            $result = $statement->execute();
            $rain_end = $result->fetchArray(SQLITE3_ASSOC);
            
            //now get amount on day start
            $statement = $this->db->prepare('SELECT "amount" FROM "pluviometer" WHERE "created" < ? ORDER BY "created" DESC LIMIT 1');
            $statement->bindValue(1, explode(" ", $rain_end['created'])[0]);
            $result = $statement->execute();
            $rain_start = $result->fetchArray(SQLITE3_ASSOC);
            
            //now clac today rain
            return floatval($rain_end["amount"]) - floatval($rain_start["amount"]);
        }

        /**
         * inner function
         * get current temperature in C
         * @return float
         */
        private function getCurrentTemp() {
            $statement = $this->db->prepare('SELECT * FROM "temperature" ORDER BY "created" DESC LIMIT 1');
            $result = $statement->execute();
            return $result->fetchArray(SQLITE3_ASSOC)['amount'];
        }

        /**
         * inner function
         * get current wind status
         * @return array of float with keys: speed, gust, direction
         */
        private function getCurrentWind() {
            $statement = $this->db->prepare('SELECT "speed", "gust", "direction" FROM "wind" ORDER BY "created" DESC LIMIT 1');
            $result = $statement->execute();
            return $result->fetchArray(SQLITE3_ASSOC);
        }

        /**
         * inner function
         * Get todays temperature extrems
         * @return array of floats with keys: low, high
         */
        private function getTempExtremsToday() {
            //get last update date
            $update = explode(" ", $this->lastUpdateStr())[0];

            $statement = $this->db->prepare('SELECT max("amount") as "high", min("amount") as "low" FROM "temperature" WHERE date("created") = ?');
            $statement->bindValue(1, $update);
            $result = $statement->execute();
            return $result->fetchArray(SQLITE3_ASSOC);
        }

        /**
         * inner function
         * get rain amount in min iterval in mm
         * @param int min - interval for search in minutes
         * @return float
         */
        private function getRainInLast($min) {
            //rain now
            $statement = $this->db->prepare('SELECT * FROM "pluviometer" ORDER BY "created" DESC LIMIT 1');
            $result = $statement->execute();
            $rain_end = $result->fetchArray(SQLITE3_ASSOC);
                        
            //now get amount on start
            $date = new DateTime($rain_end['created']);
            $date->modify("-$min minutes");

            $statement = $this->db->prepare('SELECT "amount" FROM "pluviometer" WHERE "created" > ? ORDER BY "created" ASC LIMIT 1');
            $statement->bindValue(1, $date->format('Y-m-d H:i:s'));
            $result = $statement->execute();
            $rain_start = $result->fetchArray(SQLITE3_ASSOC);
                        
            //now clac today rain
            return floatval($rain_end["amount"]) - floatval($rain_start["amount"]);
        }

        /**
         * inner function
         * Get current wetaher status
         * @return obj array of str with keys: name, icon
         */
        private function decodeCurrentWeather() {
            //if delte between two rains with time distance 10m > 0.1 - raining
            if ($this->getRainInLast(10) > 0.1) {
                return (object)[
                    'name' => $this->lang->weathers->rain,
                    'icon' => $GLOBALS['weather_icons']->rain
                ];
            } else if ($this->getCurrentWind()['speed'] > 35) {
                return (object)[
                    'name' => $this->lang->weathers->wind,
                    'icon' => $GLOBALS['weather_icons']->wind
                ];
            } else {
                $hour = date("H");

                //others I cant detect easy just say its partly_cloudy
                return (object)[
                    'name' => $this->lang->weathers->partly_cloudy,
                    'icon' => ($hour > 6 && $hour < 20) ? $GLOBALS['weather_icons']->partly_cloudy->day : $GLOBALS['weather_icons']->partly_cloudy->night
                ];
            }
            
        }

        /**
         * inner function
         * Get current humidity
         * @return float
         */
        private function getCurrentHumidity() {
            $statement = $this->db->prepare('SELECT "amount" FROM "humidity" ORDER BY "created" DESC LIMIT 1');
            $result = $statement->execute();
            return $result->fetchArray(SQLITE3_ASSOC)['amount'];
        }

        /**
         * API iterface function
         * get current weather
         * @return array
         */
        public function current() {

            $wind         = $this->getCurrentWind();
            $temp_extrems = $this->getTempExtremsToday();
            $weather      = $this->decodeCurrentWeather();

            return [
                'weather'      => $weather->name,
                'weather_icon' => $weather->icon,
                'temp'         => round($this->getCurrentTemp()) . '&deg;',
                'stats' => [
                    [
                        'label' => $this->lang->low,
                        'value' => $temp_extrems['low'] . '&deg;'
                    ],

                    [
                        'label' => $this->lang->high,
                        'value' => $temp_extrems['high'] . '&deg;'
                    ],

                    [
                        'label' => $this->lang->wind,
                        'value' => $wind['speed'] . 'm/s'
                    ],

                    [
                        'label' => $this->lang->rain,
                        'value' => $this->getRainToday() . 'mm'
                    ],

                    [
                        'label' => $this->lang->direction,
                        'value' => $wind['direction'] . '&deg;'
                    ],

                    [
                        'label' => $this->lang->humidity,
                        'value' => $this->getCurrentHumidity() . '%'
                    ],

                ]
            ];
        }

        /**
         * API iterface function
         * get weather prediction for hours
         * @return array
         */
        public function hours() {
            //TODO prediction
        }

        /**
         * API iterface function
         * get weather prediction for days
         * @return array
         */
        public function days() {
            //TODO prediction
        }

    }
?>