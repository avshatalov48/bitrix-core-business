<?php
/**
 * @global \CUser $USER
 * @global \CMain $APPLICATION
 */

function sendResponse(array $answer, string $httpCode = '200 OK')
{
	\CHTTP::SetStatus($httpCode);

	if (
		defined('RESPONSE_JSON')
		|| isset($_REQUEST['json']) && $_REQUEST['json'] == 'y'
	)
	{
		header('Content-Type: application/json');
		echo \Bitrix\Main\Web\Json::encode($answer);

		\Bitrix\Main\Application::getInstance()->end();
	}

	$answerParts = array();
	foreach($answer as $attr => $value)
	{
		switch(gettype($value))
		{
			case 'string':
				$value = "'".CUtil::JSEscape($value)."'";
				break;
			case 'boolean':
				$value = ($value === true? 'true': 'false');
				break;
			case 'array':
				$value = toJsObject($value);
				break;
		}

		$answerParts[] = $attr.": ".$value;
	}

	echo "{".implode(", ", $answerParts)."}";

	\Bitrix\Main\Application::getInstance()->end();
}

function isAccessAllowed()
{
	global $USER;

	if ($USER->IsAdmin())
	{
		return true;
	}

	if (!\Bitrix\Main\Loader::includeModule('intranet'))
	{
		return true;
	}

	if (\Bitrix\Intranet\Util::isIntranetUser())
	{
		return true;
	}

	if (\Bitrix\Intranet\Util::isExtranetUser())
	{
		return true;
	}

	return false;
}