<?php

require_once("TeamSpeak3/TeamSpeak3.php");
TeamSpeak3::init();

$serverIP = "127.0.0.1";
$serverPort = 9987;
$serverQueryPort = 10011;
$maxQuery = 200;

try
{
    $connect = true;
    while($connect == true)
    {
        $serverfull = true;
        $nickname = substr(md5(microtime()),rand(10,20),20);
        $ts3 = TeamSpeak3::factory("serverquery://@{$serverIP}:{$serverQueryPort}/?server_port={$serverPort}&blocking=0&nickname={$nickname}");

        $serverInfo = $ts3->getInfo();
        $maxSlots = $serverInfo["virtualserver_maxclients"];
        $clientsOnline = $serverInfo["virtualserver_clientsonline"];
        $queryOnline = $serverInfo["virtualserver_queryclientsonline"];
        $slotsReserved = $serverInfo["virtualserver_reserved_slots"];
        $slotsAvailable = $maxSlots - $slotsReserved;

        $unixTime = time();
        $realTime = date('[Y-m-d] [H:i:s]',$unixTime);
        echo $realTime."\t[INFO] Connected {$nickname} Slots: {$slotsAvailable} Online: {$clientsOnline} Query: {$queryOnline}\n";

        while($serverfull == true)
        {
            if(($slotsAvailable > $clientsOnline) && ($queryOnline <= $maxQuery))
            {
                $serverfull = false;
            }
            else
            {
                $serverInfo = $ts3->getInfo();
                $maxSlots = $serverInfo["virtualserver_maxclients"];
                $clientsOnline = $serverInfo["virtualserver_clientsonline"];
                $queryOnline = $serverInfo["virtualserver_queryclientsonline"];
                $slotsReserved = $serverInfo["virtualserver_reserved_slots"];
                $slotsAvailable = $maxSlots - $slotsReserved;
                $unixTime = time();
                $realTime = date('[Y-m-d] [H:i:s]',$unixTime);
                echo $realTime."\t[INFO] Server is full. Slots: {$slotsAvailable} Online: {$clientsOnline} Query: {$queryOnline}\n";
                sleep(1);
            }
        }

        sleep(1);
    }
}
catch(Exception $e)
{
    $unixTime = time();
    $realTime = date('[Y-m-d] [H:i:s]',$unixTime);
    die($realTime."\t[ERROR]  " . $e->getMessage() . "\n". $e->getTraceAsString() ."\n");
}