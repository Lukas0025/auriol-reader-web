<?php
    include 'php/weather-icons.php';

    class example_api {

        private db;

        public function conscruct() {
            $this->db = new SQLite3('/var/local/auriol-db.sl3', SQLITE3_OPEN_READONLY);
        }

        public function lastUpdateStr() {
            $statement = $this->db->prepare('SELECT "created" FROM "temperature" ORDER BY "created" DESC LIMIT 1');
            $result = $statement->execute();
          
            return $result->fetchArray(SQLITE3_ASSOC)['created'];  
        }

        public function current() {

            /**
             * get current values
             */

            //temp
            $statement = $this->db->prepare('SELECT * FROM "temperature" ORDER BY "created" DESC LIMIT 1');
            $result = $statement->execute();
            $temp_res = $result->fetchArray(SQLITE3_ASSOC);

            //now get min and max
            $statement = $this->db->prepare('SELECT max("amount") as "high", min("amount") as "low" FROM "temperature" WHERE date("created") = ?');
            $statement->bindValue(1, explode(" ", $temp_res['created'])[0]);
            $result = $statement->execute();
            $temp_extrems = $result->fetchArray(SQLITE3_ASSOC);


            //wind
            $statement = $this->db->prepare('SELECT "speed", "gust", "direction" FROM "wind" ORDER BY "created" DESC LIMIT 1');
            $result = $statement->execute();
            $wind = $result->fetchArray(SQLITE3_ASSOC);

            //humidity
            $statement = $this->db->prepare('SELECT "amount" FROM "humidity" ORDER BY "created" DESC LIMIT 1');
            $result = $statement->execute();
            $humidity = $result->fetchArray(SQLITE3_ASSOC)['amount'];

            //rain
            $statement = $this->db->prepare('SELECT * FROM "pluviometer" ORDER BY "created" DESC LIMIT 1');
            $result = $statement->execute();
            $rain_end = $result->fetchArray(SQLITE3_ASSOC);

            //now get amount on day start
            $statement = $this->db->prepare('SELECT "amount" FROM "pluviometer" WHERE "created" < ? ORDER BY "created" DESC LIMIT 1');
            $statement->bindValue(1, explode(" ", $rain_end['created'])[0]);
            $result = $statement->execute();
            $rain_start = $result->fetchArray(SQLITE3_ASSOC);

            //now clac today rain
            $rain = floatval($rain_end["amount"]) - floatval($rain_start["amount"]);

            return [
                'weather'      => 'clear', //todo
                'weather_icon' => $GLOBALS['weather_icons']->clear->day, //todo
                'temp'         => $temp_res['amount'] . '&deg;',
                'stats' => [
                    [
                        'label' => "low",
                        'value' => $temp_extrems['low'] . '&deg;'
                    ],

                    [
                        'label' => 'high',
                        'value' => $temp_extrems['high'] . '&deg;'
                    ],

                    [
                        'label' => 'wind',
                        'value' => $wind['speed'] . 'm/s'
                    ],

                    [
                        'label' => 'rain',
                        'value' => $rain . 'mm'
                    ],

                    [
                        'label' => 'direction',
                        'value' => $wind['direction'] . '&deg;'
                    ],

                    [
                        'label' => 'humidity',
                        'value' => $humidity . '%'
                    ],

                ]
            ];
        }

        public function hours() {
            //TODO prediction
        }

        public function days() {
            //TODO prediction
        }

    }
?>