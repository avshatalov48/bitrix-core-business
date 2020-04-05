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

\CJSCore::Init(array("sidepanel"));
?>
<!DOCTYPE html>
<html>
<head>
<script type="text/javascript">
	// Prevent loading page without header and footer
	if(window === window.top)
	{
		window.location = "<?=CUtil::JSEscape($APPLICATION->GetCurPageParam('', array('IFRAME'))); ?>";
	}
</script>
<?$APPLICATION->ShowHead();?>
<style>
	body {
		background-color: #fff;
	}
	.mp-slider-wrap
	{
		padding: 10px 10px 30px 10px;
	}
</style>
</head>
<body>
	<div class="mp-slider-wrap">
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
