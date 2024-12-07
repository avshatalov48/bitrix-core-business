<?php

use Bitrix\Main\Web;
use Bitrix\Main\Context;

if (defined("BX_URLREWRITE"))
{
	return;
}

define("BX_URLREWRITE", true);

error_reporting(E_COMPILE_ERROR | E_ERROR | E_CORE_ERROR | E_PARSE);

require_once __DIR__ . "/../bx_root.php";
require_once __DIR__ . "/../lib/loader.php";
require_once __DIR__ . "/autoload.php";
require_once __DIR__ . "/../tools.php";

// try to fix REQUEST_URI under IIS
$aProtocols = ['http', 'https'];
foreach ($aProtocols as $prot)
{
	$marker = "404;" . $prot . "://";
	if (($p = strpos($_SERVER["QUERY_STRING"], $marker)) !== false)
	{
		$uri = $_SERVER["QUERY_STRING"];
		if (($p = strpos($uri, "/", $p + strlen($marker))) !== false)
		{
			if ($_SERVER["REQUEST_URI"] == '' || $_SERVER["REQUEST_URI"] == '/404.php' || str_contains($_SERVER["REQUEST_URI"], $marker))
			{
				$_SERVER["REQUEST_URI"] = substr($uri, $p);
			}
			$_SERVER["REDIRECT_STATUS"] = '404';
			$_SERVER["QUERY_STRING"] = "";
			$_GET = [];
			break;
		}
	}
}

require_once $_SERVER["DOCUMENT_ROOT"] . getLocalPath('php_interface/dbconn.php', BX_PERSONAL_ROOT);

$foundQMark = strpos($_SERVER["REQUEST_URI"], "?");
$requestUriWithoutParams = ($foundQMark !== false ? substr($_SERVER["REQUEST_URI"], 0, $foundQMark) : $_SERVER["REQUEST_URI"]);
$requestParams = ($foundQMark !== false ? substr($_SERVER["REQUEST_URI"], $foundQMark) : "");

//decode only filename, not parameters
$requestPage = urldecode($requestUriWithoutParams);

$requestUri = $requestPage . $requestParams;

$io = CBXVirtualIo::GetInstance();

$arUrlRewrite = [];
if (file_exists($_SERVER['DOCUMENT_ROOT'] . "/urlrewrite.php"))
{
	include $_SERVER['DOCUMENT_ROOT'] . "/urlrewrite.php";
}

$uri = new Web\Uri($_SERVER["REQUEST_URI"]);
if (!$uri->isPathTraversal())
{
	foreach ($arUrlRewrite as $val)
	{
		if (preg_match($val["CONDITION"], $requestUri))
		{
			if (!empty($val["RULE"]))
			{
				$url = preg_replace($val["CONDITION"], ($val["PATH"] <> '' ? $val["PATH"] . "?" : "") . $val["RULE"], $requestUri);
			}
			else
			{
				$url = $val["PATH"];
			}

			if (($pos = strpos($url, "?")) !== false)
			{
				$params = substr($url, $pos + 1);
				parse_str($params, $vars);

				$_GET += $vars;
				$_REQUEST += $vars;
				$_SERVER["QUERY_STRING"] = Web\Uri::urnEncode($params, false);
				$url = substr($url, 0, $pos);

				// actualize context if it is initialized already
				Context::getCurrent()?->getRequest()->modifyByQueryString($_SERVER["QUERY_STRING"]);
			}

			$url = _normalizePath($url);

			if (!$io->FileExists($_SERVER['DOCUMENT_ROOT'] . $url))
			{
				continue;
			}

			if (!$io->ValidatePathString($url))
			{
				continue;
			}

			$urlTmp = strtolower(ltrim($url, "/\\"));
			$urlTmp = str_replace(".", "", $urlTmp);

			if ((str_starts_with($urlTmp, "upload/") || (str_starts_with($urlTmp, "bitrix/") && !str_starts_with($urlTmp, "bitrix/services/") && !str_starts_with($urlTmp, "bitrix/groupdavphp"))))
			{
				continue;
			}

			$ext = strtolower(GetFileExtension($url));
			if ($ext != "php")
			{
				continue;
			}

			// D7 response is not available here
			if (stristr(php_sapi_name(), "cgi") !== false && (!defined("BX_HTTP_STATUS") || !BX_HTTP_STATUS))
			{
				header("Status: 200 OK");
			}
			else
			{
				header($_SERVER["SERVER_PROTOCOL"] . " 200 OK");
			}

			$_SERVER["REAL_FILE_PATH"] = $url;
			include_once $io->GetPhysicalName($_SERVER['DOCUMENT_ROOT'] . $url);
			die();
		}
	}
}

//admin section 404
if (str_starts_with($requestUri, "/bitrix/admin/"))
{
	$_SERVER["REAL_FILE_PATH"] = "/bitrix/admin/404.php";
	include $_SERVER["DOCUMENT_ROOT"] . "/bitrix/admin/404.php";
	die();
}

define("BX_CHECK_SHORT_URI", true);
