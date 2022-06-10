<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <title>General</title>
    <style>
        body {
            margin: 0 auto;
            font-size: 15px;
        }

        .form-container {
            width: 600px;
            margin: 0 auto;
            text-align: center;
            overflow: hidden;
        }

        form h2 {
            margin: 0;
            font-family: GENISO;
        }

        input {
            display: block;
            margin: 0px auto 5px auto;
            border-color: black;
            border-width: 1px;
            border-radius: 5px;
        }

        .text-head {
            width: 530px;
            height: 25px;
            padding: 0 10px;
        }

        .text-message {
            width: 550px;
            height: 300px;
            align-content: start;
        }

        .send-button {
            width: 200px;
        }

        .send-button:hover {
            background: #CD214F;
            border-color: #CD214F; border-style: solid;
            cursor: pointer;
        }
        h2 {
            text-align: center;
        }

        .red-span{
            color: #CD214F;
        }
        .blue-span{
            color: dodgerblue;
        }
    </style>
</head>
<body>
<form class="form-container" method="get">
    <h2>CITY</h2>
    <input class="text-head" type="text" name="City">

    <input class="send-button" type="submit" value="Get weather!">
</form>
</body>
</html>

<?php

$openweatherKey = "c11bf8a889f32038bc21c7ff481268ba";
$visualcrossingKey = "84WBXPZNHXRD8CNL9RBYTSTWP";
$weatherKey = "9829c1f8519941169f5190438221605";

function GetOpenweatherTemp(string $cityName): float
{
    $jsonWeather = file_get_contents("https://api.openweathermap.org/data/2.5/weather?q=".$cityName."&appid=".$GLOBALS["openweatherKey"]);
    if(!$jsonWeather)
        die("Not valid city");

    $weatherInfo = json_decode($jsonWeather, true);
    $celsiumTemp = $weatherInfo['main']['temp'] - 273.15;

    return $celsiumTemp;
}

function GetVisualcrossingTemp(string $cityName): float
{
    $jsonWeather = file_get_contents("https://weather.visualcrossing.com/VisualCrossingWebServices/rest/services/timeline/".$cityName."/".time()."/".time()."?key=".$GLOBALS["visualcrossingKey"]."&include=elements=temp");
    if(!$jsonWeather)
        die("Not valid city");

    $weatherInfo = json_decode($jsonWeather, true);
    $celsiumTemp = ($weatherInfo['days'][0]['temp'] - 32) * 5 / 9;

    return $celsiumTemp;
}

function GetWeatherTemp($city)
{
    try {
        $url = "http://api.weatherapi.com/v1/current.json?key=".$GLOBALS["weatherKey"]."&q=$city&aqi=no";

        $context = stream_context_create(array(
            'http' => array(
                'method' => 'GET',
                'header' => 'Content-type: application/json'.PHP_EOL
            ),
        ));

        $jsonWeather = @file_get_contents($url, false, $context);
        if(!$jsonWeather)
            die("Not valid city");

        $weatherInfo = json_decode($jsonWeather);
        return $weatherInfo->{'current'}->{'temp_c'};
    }
    catch (Exception $e)
    {
        die("Not valid city");
    }
}

function GetYandexTemp($city)
{
    $coord = GetLonLat($city);
    if (count($coord) != 0)
    {
        try {
            $lat = $coord["lat"];

            $lon = $coord['lon'];
            $url = "https://api.weather.yandex.ru/v2/forecast?lat=$lat&lon=$lon";
            $context = stream_context_create(array(
                'http' => array(
                    'method' => 'GET',
                    'header' => 'Content-type: application/json'.PHP_EOL.'X-Yandex-API-Key: 621e0fb0-3102-41c9-948f-9d3008471e92'.PHP_EOL
                ),
            ));

            $jsonWeather = @file_get_contents($url, false, $context);
            if(!$jsonWeather)
                die("Not valid city");

            $weatherInfo = json_decode($jsonWeather);
            return $weatherInfo->{'fact'}->{'temp'};
        }
        catch (Exception $e)
        {
            die("Not valid city");
        }
    }
    else
        die("Not valid city");
}

function GetLonLat($city): array
{
    try {
        $url = "http://open.mapquestapi.com/geocoding/v1/address?key=04hbA8ljjHUnpKWwZO6R4ZoQTGJUTJLy&location=$city";

        $context = stream_context_create(array(
            'http' => array(
                'method' => 'GET',
                'header' => 'Content-type: application/json'.PHP_EOL
            ),
        ));

        $result = @file_get_contents($url, false, $context);
        if ($result === FALSE) {
            return [];
        }

        $obj = json_decode($result, null, 8000);

        $resArr['lon'] = $obj->{'results'}[0]->{'locations'}[0]->{'latLng'}->{'lng'};
        $resArr['lat'] = $obj->{'results'}[0]->{'locations'}[0]->{'latLng'}->{'lat'};
        return  $resArr;
    }
    catch (Exception $e)
    {
        return [];
    }
}

function GetAmbeeWeatherTemp($city)
{
    $coord = GetLonLat($city);
    if (count($coord) != 0)
    {
        try {
            $lat = $coord["lat"];
            $lon = $coord['lon'];
            $url = "https://api.ambeedata.com/weather/latest/by-lat-lng?lat=$lat&lng=$lon&units=SI";
            $context = stream_context_create(array(
                'http' => array(
                    'method' => 'GET',
                    'header' => 'Content-type: application/json'.PHP_EOL.'x-api-key: 417f3d0454c52e335e57bf1c6d69e6ecbf9edaca0830cd59a7ab553b8d45fd91'.PHP_EOL
                ),
            ));

            $jsonWeather = @file_get_contents($url, false, $context);
            if(!$jsonWeather)
                die("Not valid city");

            $weatherInfo = json_decode($jsonWeather);
            return $weatherInfo->{'data'}->{'temperature'};
        }
        catch (Exception $e)
        {
            die("Not valid city");
        }
    }
    else
        die("Not valid city");
}

function GetAverageTemp($cityName):float
{
    $temps = array();
    $temps[] = GetOpenweatherTemp($cityName);
    $temps[] = GetVisualcrossingTemp($cityName);
    $temps[] = GetYandexTemp($cityName);
    $temps[] = GetWeatherTemp($cityName);
    $temps[] = GetAmbeeWeatherTemp($cityName);

    $totalTemp = 0;
    foreach ($temps as $currTemp)
        $totalTemp += $currTemp;

    return $totalTemp / count($temps);
}

if (isset($_GET['City']))
{
    $cityName = $_GET['City'];
    $currTemp = round(GetAverageTemp($cityName),2);
    $spanClass = $currTemp >= 0 ? "red-span" : "blue-span";
    echo "<h2> City: ".$cityName."</h2>";
    echo "<h2> Current temperature: <span class='".$spanClass."'>".$currTemp."</span> Celsium</h2>";
}