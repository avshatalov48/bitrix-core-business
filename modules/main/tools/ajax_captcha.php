<?php

use Bitrix\Main\Web\Json;

define("NO_KEEP_STATISTIC", true);
define("NOT_CHECK_PERMISSIONS", true);

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

echo Json::encode([
	'captcha_sid' => $GLOBALS['APPLICATION']->CaptchaGetCode(),
]);
