<?php
    include 'php/templates.php';
    include 'php/components.php';
    include 'api.php';
    include 'config.php';

    $comp = new components();
    $temp = new template('index');

    $api =  new example_api();

    $current = $api->current();

    $temp->create([
        'header'          => $comp->header($config->location, $api->lastUpdateStr()),
        'current_weather' => $comp->current($current['temp'], $current['weather'], $current['weather_icon'], $current['stats']),
        'hour_weather'    => $comp->hour($api->hours()),
        'days_weather'    => $comp->days($api->days())
    ]);