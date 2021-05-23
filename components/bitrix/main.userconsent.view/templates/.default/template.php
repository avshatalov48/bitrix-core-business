<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

use Bitrix\Main\Localization\Loc;

/** @var array $arParams */
/** @var array $arResult */
?>
<div class="intranet-user-consent-view-wrapper">
	<?=nl2br(htmlspecialcharsbx($arResult['TEXT']))?>
</div>
