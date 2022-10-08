<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

\Bitrix\Main\UI\Extension::load(['ui.design-tokens']);

\Bitrix\Main\Page\Asset::getInstance()->addJs($this->GetFolder() . "/../.default/script.js");
?>
<div class="adm-numerator-form">
	<? include($_SERVER["DOCUMENT_ROOT"] . $this->GetFolder() . "/../.default/template.php");; ?>
</div>