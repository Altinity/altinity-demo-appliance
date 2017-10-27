<?php

$pidFile     = '/tmp/logs/cron.pid';
$statusFile  = '/tmp/logs/demo-dataset.id';
$minProgress = 0;

$statuses = array(
    'start',
    'installing',
    'd0','d1','d2','d3','d4','d5','d6','d7','d8','d9','d10',
    'schema',
    'i0','i1','i2','i3','i4','i5','i6','i7','i8','i9','i10',
    'finished'
);

function updateStatus($status, $message)
{
    global $statusFile;

    $status = array(
        'status'  => $status,
        'message' => $message,
    );

    if (!json_encode($status)) {
        echo "Error status: ";
        print_r($status);
    }
    file_put_contents($statusFile, json_encode($status));
    chmod($statusFile, 0777);
}

function getStatus()
{
    global $statusFile, $statuses, $minProgress;

    if (!file_exists($statusFile)) {
        $chc = new mysqli('127.0.0.1', 'clicku', 'clickp', 'default', 6090);
        if ($chc) {
            $r = $chc->query("SELECT count() c FROM ontime");
            if ($r) {
                $row = $r->fetch_assoc();
                if ($row['c']) {
                    return array(
                        'status'   => 'finished',
                        'message'  => 'Demo dataset installed.',
                        'progress' => 100,
                        'records' => $row['c'],
                    );
                }
            }
        }

        return array(
            'status'   => '',
            'message'  => '',
            'progress' => 0,
            'records' => 0,
        );
    }

    $status = @file_get_contents($statusFile);
    if (empty($status)) {
        return array(
            'status'   => '',
            'message'  => '',
            'progress' => 0,
            'records' => 0,
        );
    }

    $status = json_decode($status, true);

    $status['progress'] = $minProgress + ceil((array_search($status['status'], $statuses) + 1)/count($statuses)*(100 - $minProgress));
    $status['records']  = 0;

    if ($status['progress'] >= 100) {
        $chc = new mysqli('127.0.0.1', 'clicku', 'clickp', 'default', 6090);
        if ($chc) {
            $r = $chc->query("SELECT count() c FROM ontime");
            if ($r) {
                $row = $r->fetch_assoc();
                $status['records'] = $row['c'];
            }
        }
    }

    return $status;
}

function installOntime()
{
    updateStatus('installing', 'Installation in progress...');

    $startTime = time();

    // download dataset starting from 2007

    $minYear   = 1987;
    $maxYear   = 2017;
    $datadir   = '/tmp/ontimedata';
    $sourcecnt = 0;

    if (!file_exists($datadir)) {
        mkdir($datadir, 0777);
    }

    chdir($datadir);
    for ($y = $minYear; $y <= $maxYear; $y++) {
        for ($m = 1; $m <= 12; $m++) {
            if (file_exists($datadir . "/On_Time_On_Time_Performance_{$y}_{$m}.zip")) {
                //unlink($datadir . "/On_Time_On_Time_Performance_{$y}_{$m}.zip");
            } else {
                exec("wget -q http://repo.altinity.com/ontime/On_Time_On_Time_Performance_{$y}_{$m}.zip");
            }

            $downloadProgress = (($y - $minYear)*12 + $m)/(($maxYear - $minYear + 1)*12);

            if ($downloadProgress <= 0 || $downloadProgress > 1) {
                echo $y, ", ", $m, ", ", $downloadProgress, "\n";
            }
            $newStatus = 'd' . ceil($downloadProgress * 10);

            if ($downloadProgress) {
                $estTime = ceil((1 - $downloadProgress) * (time() - $startTime) / $downloadProgress / 60);
            } else {
                $estTime = 30;
            }

            $sourcecnt++;

            updateStatus($newStatus, 'Downloading Data Set... Estimated time left ' . $estTime . ' min.');
        }
    }


    // prepare schema for CH
    updateStatus('schema', 'Setting up ClickHouse schema...');

    /*$chc = new mysqli('127.0.0.1', 'clicku', 'clickp', 'default', 6090);
    if (!$chc) {
        die("could not connect to DB via proxysql");
    }*/

    exec('clickhouse-client --query="DROP Table if exists ontime"');
    exec('clickhouse-client < '.__DIR__ . '/schema.sql');
    /*$schema = file_get_contents(__DIR__ . '/schema.sql');
    $chc->query("DROP TABLE IF EXISTS ontime");
    $chc->query($schema);*/

    // import the data into CH
    updateStatus('i0', 'Importing Data Set...');

    $startTime = time();
    $dir = dir($datadir);
    $c   = 0;
    while ($entry = $dir->read()) {
        if (stripos($entry, '.zip') !== false) {
            exec('unzip -o ' . $datadir . '/' . $entry);
            //exec('unlink ' . $datadir . '/' .$entry);
            $entry = str_replace('.zip', '.csv', $entry);
        } else {
            continue;
        }

        if (stripos($entry, '.csv') === false) {
            continue;
        }

        $c++;
        $importProgress = $c/$sourcecnt;
        $newStatus = ceil($importProgress * 10);

        if ($importProgress) {
            $estTime = ceil((1 - $importProgress) * (time() - $startTime) / $importProgress / 60);
        } else {
            $estTime = 30;
        }

        updateStatus('i' . $newStatus, "Importing Data Set... Estimated time left $estTime min.");

        $entry = $datadir . '/' .$entry;
        exec("tail -n +2 $entry | clickhouse-client --query=\"INSERT INTO ontime (  Year,  Quarter,  Month,  DayofMonth,  DayOfWeek,  FlightDate,  UniqueCarrier,  AirlineID,  Carrier,  TailNum,  FlightNum,  OriginAirportID,  OriginAirportSeqID,  OriginCityMarketID,  Origin,  OriginCityName,  OriginState,  OriginStateFips,  OriginStateName,  OriginWac,  DestAirportID,  DestAirportSeqID,  DestCityMarketID,  Dest,  DestCityName,  DestState,  DestStateFips,  DestStateName,  DestWac,  CRSDepTime,  DepTime,  DepDelay,  DepDelayMinutes,  DepDel15,  DepartureDelayGroups,  DepTimeBlk,  TaxiOut,  WheelsOff,  WheelsOn,  TaxiIn,  CRSArrTime,  ArrTime,  ArrDelay,  ArrDelayMinutes,  ArrDel15,  ArrivalDelayGroups,  ArrTimeBlk,  Cancelled,  CancellationCode,  Diverted,  CRSElapsedTime,  ActualElapsedTime,  AirTime,  Flights,  Distance,  DistanceGroup,  CarrierDelay,  WeatherDelay,  NASDelay,  SecurityDelay,  LateAircraftDelay,  FirstDepTime,  TotalAddGTime,  LongestAddGTime,  DivAirportLandings,  DivReachedDest,  DivActualElapsedTime,  DivArrDelay,  DivDistance,  Div1Airport,  Div1AirportID,  Div1AirportSeqID,  Div1WheelsOn,  Div1TotalGTime,  Div1LongestGTime,  Div1WheelsOff,  Div1TailNum,  Div2Airport,  Div2AirportID,  Div2AirportSeqID,  Div2WheelsOn,  Div2TotalGTime,  Div2LongestGTime,  Div2WheelsOff,  Div2TailNum,  Div3Airport,  Div3AirportID,  Div3AirportSeqID,  Div3WheelsOn,  Div3TotalGTime,  Div3LongestGTime,  Div3WheelsOff,  Div3TailNum,  Div4Airport,  Div4AirportID,  Div4AirportSeqID,  Div4WheelsOn,  Div4TotalGTime,  Div4LongestGTime,  Div4WheelsOff,  Div4TailNum,  Div5Airport,  Div5AirportID,  Div5AirportSeqID,  Div5WheelsOn,  Div5TotalGTime,  Div5LongestGTime,  Div5WheelsOff,  Div5TailNum) FORMAT CSV\"");
        unlink($entry);
    }

    // import grafana dashboard
    $dashboard = file_get_contents(__DIR__ . '/grafana/Ontime.json');
    $dashboard =  '{"dashboard":' . $dashboard . ',"overwrite":true}';

    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => 'http://admin:admin@localhost:3000/api/dashboards/db',
        CURLOPT_POST => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => array(
            'Accept: application/json',
            'Content-Type: application/json'
        ),
        CURLOPT_POSTFIELDS => $dashboard
    ));
    $response = curl_exec($curl);

    updateStatus('finished', 'Installation complete');
}
