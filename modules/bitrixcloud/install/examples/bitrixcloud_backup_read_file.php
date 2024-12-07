<?php

$LICENSE_KEY = '';
include $_SERVER['DOCUMENT_ROOT'] . '/bitrix/license_key.php';

$host = 'util.bitrixsoft.com';

$path = '/backup.php';
$path .= '?license=' . md5($LICENSE_KEY);
$path .= '&lang=ru';
$path .= '&region=ru';
$path .= '&action=read_file';
$path .= '&file_name=20120911_123440_79.enc.gz';
$path .= '&check_word=batman2';

$proto = 'ssl';
$port = 443;
$http_timeout = 10;

echo '<pre>', htmlspecialchars($host . $path), '<pre>';

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

if ($result)
{
	echo '<pre>', htmlspecialchars($result), '<pre>';

	$host = preg_match('/<host>(.*?)<\\/host>/', $result, $match) ? $match[1] : '';
	$proto = preg_match('/<proto>(.*?)<\\/proto>/', $result, $match) ? $match[1] : '';
	$port = preg_match('/<port>(.*?)<\\/port>/', $result, $match) ? intval($match[1]) : 0;
	$path = preg_match('/<path>(.*?)<\\/path>/', $result, $match) ? $match[1] : '';
	$headers = preg_match_all('#<header name="(.*)" value="(.*)"/>#', $result, $match) ? $match : '';

	$result = '';
	$fp = fsockopen($proto . $host, $port, $errno, $errstr, $http_timeout);
	var_dump($fp);
	if ($fp)
	{
		$strRequest = "GET " . $path . " HTTP/1.0\r\n";
		$strRequest .= "Connection: close\r\n";
		$strRequest .= "Accept: */*\r\n";
		$strRequest .= "Host: " . $host. "\r\n";
		$strRequest .= "Accept-Language: en\r\n";

		foreach ($headers[0] as $i => $tmp)
		{
			$strRequest .= $headers[1][$i] . ': ' . $headers[2][$i] . "\r\n";
		}

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
		echo '<pre>', htmlspecialchars($headers), '</pre>';

		while (!feof($fp))
		{
			$result .= fread($fp, 4096);
		}

		fclose($fp);
	}

	echo '<pre>', htmlspecialchars($result), '</pre>';
}
else
{
	var_dump($result);
}
