<?php
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

/**
 * Bitrix vars
 *
 * @var array $arParams
 * @var array $arResult
 * @var CBitrixComponent $component
 * @var CBitrixComponentTemplate $this
 * @global CMain $APPLICATION
 * @global CUser $USER
 */

\CJSCore::Init(array("sidepanel", "ui.fonts.opensans"));
?>
<!DOCTYPE html>
<html>
<head>
<script>
	// Prevent loading page without header and footer
	if(window === window.top)
	{
		window.location = "<?=CUtil::JSEscape($APPLICATION->GetCurPageParam('', array('IFRAME'))); ?>";
	}
</script>
<?$APPLICATION->ShowHead();?>
</head>
<body class="rest-mp-slider-body">
<div class="rest-mp-slider-wrap">
	<?php
	$APPLICATION->IncludeComponent(
		$arParams['COMPONENT_NAME'],
		$arParams['COMPONENT_TEMPLATE_NAME'],
		$arParams['COMPONENT_PARAMS'],
		$component,
		array('HIDE_ICONS' => 'Y')
	);
	?>
</div>
</body>
</html>
