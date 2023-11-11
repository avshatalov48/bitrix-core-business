<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");

\Bitrix\Main\Page\Asset::getInstance()->setJsToBody(false);

if (!CModule::IncludeModule('im'))
{
	return;
}

if (intval($USER->GetID()) <= 0 || !isset($_GET['IM_HISTORY']))
{
	?>
<script type="text/javascript">
	location.href = '/';
</script><?php
	return true;
}

$isDesktop = isset($_GET['BXD_API_VERSION']) || mb_strpos($_SERVER['HTTP_USER_AGENT'], 'BitrixDesktop') !== false;

$APPLICATION->IncludeComponent("bitrix:im.messenger", "iframe", Array(
	"CONTEXT" => "HISTORY-FULLSCREEN",
	"WITH_DESKTOP" => $isDesktop,
), false, Array("HIDE_ICONS" => "Y"));

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");
?>
