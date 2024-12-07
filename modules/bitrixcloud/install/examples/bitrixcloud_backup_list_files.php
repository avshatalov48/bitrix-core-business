<?php

$LICENSE_KEY = '';
include $_SERVER['DOCUMENT_ROOT'] . '/bitrix/license_key.php';

$host = 'util.bitrixsoft.com';

$path = '/backup.php';
$path .= '?license=' . md5($LICENSE_KEY);
$path .= '&lang=ru';
$path .= '&region=ru';
$path .= '&action=get_info';

$proto = 'ssl';
$port = 443;
$http_timeout = 10;

$result = '';
$fp = fsockopen($proto . $host, $port, $errno, $errstr, $http_timeout);
if ($fp)
{
	$strRequest = "GET " . $path . " HTTP/1.0\r\n";
	$strRequest .= "Connection: close\r\n";
	$strRequest .= "Accept: */*\r\n";
	$strRequest .= "Host: " . $host . "\r\n";
	$strRequest .= "Accept-Language: en\r\n";
	$strRequest .= "\r\n";

	fwrite($fp, $strRequest);

	$headers = '';
	while (!feof($fp))
	{
		$line = fgets($fp, 4096);
		if ($line == "\r\n")
		{
			break;
		}
		$headers .= $line;
	}

	while (!feof($fp))
	{
		$result .= fread($fp, 4096);
	}

	fclose($fp);
}

if (preg_match_all('/<file name="([^"]+)" size="([^"]+)".*?\\/>/', $result, $match))
{
	foreach ($match[0] as $i => $wholeMatch)
	{
		echo 'file: ', htmlspecialchars($match[1][$i]), ' (', htmlspecialchars($match[2][$i]), ' bytes)', "<br>\n";
	}
}
