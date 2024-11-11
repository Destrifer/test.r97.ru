<?php
header('Content-Type: text/html; charset=utf-8');
$url = 'https://geocode-maps.yandex.ru/1.x/?format=json&results=1&geocode=Беларусь,%20Минск?%20ул.%20   ул. Шрадера 3/1';
        $ar = json_decode(file_get_contents($url),true);
        $coords = $ar['response']['GeoObjectCollection']['featureMember'][0]['GeoObject']['Point']['pos'];
        list($lon_adress,$lat_adress) = explode(' ',$coords);
        $service = $lon_adress.','.$lat_adress;
        echo $service;
?>