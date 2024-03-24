<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

/** @var CMain $APPLICATION */
/** @var array $arParams */
/** @var array $arResult */

use Bitrix\Main\Localization\Loc;
if($arResult['IS_IFRAME'])
{
	$bodyClass = $APPLICATION->GetPageProperty("BodyClass");
	$APPLICATION->SetPageProperty("BodyClass", ($bodyClass ? $bodyClass." " : "") . "no-all-paddings no-background");
}

\Bitrix\Main\UI\Extension::load('ui.design-tokens');

if(!$arResult['ERROR']):
?>
	<? if (isset($arResult['JSON_RESULT'])):?>
		<div class="integration-container">
			<div class="ui-title-4"><?= Loc::getMessage("REST_INTEGRATION_IFRAME_QUERY_RESULT_TITLE") ?></div>
			<hr>
			<p class="integration-print"><?=htmlspecialcharsbx($arResult['RESPONSE']);?></p>
		</div>
		<div class="integration-container">
			<div class="ui-title-4"><?= Loc::getMessage("REST_INTEGRATION_IFRAME_QUERY_RESULT_JSON_TITLE") ?></div>
			<hr>
			<pre><?=htmlspecialcharsbx(print_r($arResult['JSON_RESULT'], true))?></pre>
		</div>
	<?elseif($arResult['XML_RESULT']):?>
		<div class="integration-container">
			<div class="ui-title-4"><?= Loc::getMessage("REST_INTEGRATION_IFRAME_QUERY_RESULT_XML_RESULT") ?></div>
			<hr>
			<pre class="integration-print-xml"><code><?=htmlspecialcharsbx($arResult['XML_RESULT']);?></code></pre>
		</div>
	<? endif;?>
<? else:?>
	<div class="integration-container">
		<?=htmlspecialcharsbx($arResult['ERROR_TEXT']);?>
	</div>
<? endif; ?>
