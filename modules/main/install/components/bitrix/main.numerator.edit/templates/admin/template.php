<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

/** @var array $arParams */
/** @var array $arResult */

$arResult['WITHOUT_FORM'] = true;
$arResult['IS_SLIDER'] = false;
$arResult['IS_HIDE_PAGE_TITLE'] = true;
$arResult['isEmbedMode'] = true;

\Bitrix\Main\UI\Extension::load(['ui.design-tokens']);

\Bitrix\Main\Page\Asset::getInstance()->addJs($this->GetFolder() . "/../.default/script.js");
?>
<div class="adm-numerator-form">
	<?php
	include($_SERVER["DOCUMENT_ROOT"] . $this->GetFolder() . "/../.default/template.php");
	?>
</div>