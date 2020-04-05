<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

/**
 * @var array $arParams
 * @var array $arResult
 * @global \CMain $APPLICATION
 * @global \CUser $USER
 * @global \CDatabase $DB
 * @var \CBitrixComponentTemplate $this
 * @var string $templateName
 * @var string $templateFile
 * @var string $templateFolder
 * @var string $componentPath
 * @var \TranslateListComponent|\CBitrixComponent $component
 */

?>
	<div class="ui-alert ui-alert-danger ui-alert-icon-danger">
		<span class="ui-alert-message"><?= $arResult['ERROR_MESSAGE'] ?></span>
	</div>
<?