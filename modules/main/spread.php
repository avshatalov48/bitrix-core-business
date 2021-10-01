<?php

// Should work only on HTTPS requests

use Bitrix\Main;
use Bitrix\Main\Web;

header("P3P: policyref=\"/bitrix/p3p.xml\", CP=\"NON DSP COR CUR ADM DEV PSA PSD OUR UNR BUS UNI COM NAV INT DEM STA\"");
header("Content-type: image/png");

if (isset($_GET['k']) && isset($_GET['s']) && is_string($_GET['k']) && is_string($_GET['s']) && $_GET['k'] != '')
{
	// "SameSite: None" requires "secure"
	ini_set('session.cookie_secure', 1);
	ini_set('session.cookie_samesite', 'None');

	require_once(__DIR__.'/include.php');

	$application = Main\Application::getInstance();

	$cookieString = base64_decode($_GET['s']);
	$salt = $_SERVER['REMOTE_ADDR'] . '|' . @filemtime($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/classes/general/version.php') . '|' . LICENSE_KEY;

	if (md5($cookieString . $salt) === $_GET['k'])
	{
		$arr = explode(chr(2), $cookieString);

		if (is_array($arr))
		{
			$context = Main\Context::getCurrent();
			$request = $context->getRequest();
			$response = $context->getResponse();

			$host = $request->getHttpHost();

			foreach ($arr as $str)
			{
				if ($str != '')
				{
					$ar = explode(chr(1), $str);

					// "SameSite: None" requires "secure"
					$cookie = (new Web\Cookie($ar[0], $ar[1], $ar[2], false))
						->setPath($ar[3])
						->setDomain($host)
						->setSecure(true)
						->setHttpOnly($ar[6])
						->setSameSite(Web\Cookie::SAME_SITE_NONE)
					;

					$response->addCookie($cookie);

					//logout
					if(substr($ar[0], -5) == '_UIDH' && $ar[1] == '')
					{
						$kernelSession = $application->getKernelSession();
						$kernelSession["SESS_AUTH"] = [];
						unset($kernelSession["SESS_AUTH"]);
						unset($kernelSession["SESS_OPERATIONS"]);
						unset($kernelSession["MODULE_PERMISSIONS"]);
						unset($kernelSession["SESS_PWD_HASH_TESTED"]);
					}
				}
			}
		}
	}

	$application->end();
}
