<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$this->setFramemode(false);

if (!CModule::IncludeModule("sale"))
{
	ShowError(GetMessage("SALE_MODULE_NOT_INSTALL"));
	return;
}

if (!array_key_exists('PAY_SYSTEM_ID_NEW', $arParams))
{
	$newPsId = CSalePaySystem::getNewIdsFromOld($arParams["PAY_SYSTEM_ID"], $arParams["PERSON_TYPE_ID"]);
	$newPsId = current($newPsId);
}
else
{
	$newPsId = $arParams["PAY_SYSTEM_ID_NEW"];
}

$dbPaySysAction = CSalePaySystemAction::GetList(
	array(),
	array('ID' => $newPsId),
	false,
	false,
	array("ACTION_FILE", "PARAMS", "ENCODING")
);

if ($arPaySysAction = $dbPaySysAction->Fetch())
{
	if ($arPaySysAction["ACTION_FILE"] <> '')
	{
		$GLOBALS["SALE_CORRESPONDENCE"] = CSalePaySystemAction::UnSerializeParams($arPaySysAction["PARAMS"]);
		$pathToAction = $_SERVER["DOCUMENT_ROOT"].$arPaySysAction["ACTION_FILE"];

		if(!isset($GLOBALS["SALE_INPUT_PARAMS"]))
			$GLOBALS["SALE_INPUT_PARAMS"] = array();

		$pathToAction = str_replace("\\", "/", $pathToAction);
		while (mb_substr($pathToAction, mb_strlen($pathToAction) - 1, 1) == "/")
			$pathToAction = mb_substr($pathToAction, 0, mb_strlen($pathToAction) - 1);

		if (file_exists($pathToAction))
		{
			if (is_dir($pathToAction))
			{
				if (file_exists($pathToAction."/result_rec.php"))
					include($pathToAction."/result_rec.php");
			}
		}

		if($arPaySysAction["ENCODING"] <> '')
		{
			define("BX_SALE_ENCODING", $arPaySysAction["ENCODING"]);
			AddEventHandler("main", "OnEndBufferContent", "ChangeEncoding");
			function ChangeEncoding($content)
			{
				global $APPLICATION;
				header("Content-Type: text/html; charset=".BX_SALE_ENCODING);
				$content = $APPLICATION->ConvertCharset($content, SITE_CHARSET, BX_SALE_ENCODING);
				$content = str_replace("charset=".SITE_CHARSET, "charset=".BX_SALE_ENCODING, $content);
			}
		}

	}
}
