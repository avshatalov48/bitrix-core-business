<?php
if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

/** @var CMain $APPLICATION */
/** @var array $arResult*/
/** @var array $arParams*/

global $APPLICATION;

?><!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?=LANGUAGE_ID ?>" lang="<?=LANGUAGE_ID ?>">
<head>
	<script type="text/javascript">
		// Prevent loading page without header and footer
		if(window == window.top)
		{
			window.location = "<?=CUtil::JSEscape($APPLICATION->GetCurPageParam('', array('IFRAME'))); ?>";
		}
	</script>
	<?$APPLICATION->ShowHead();?>
</head>
<body class="sonet-slider-frame-popup template-<?= SITE_TEMPLATE_ID ?> <? $APPLICATION->ShowProperty('BodyClass'); ?>">
	<div class="pagetitle-wrap">
		<div class="pagetitle-inner-container">
			<div class="slider-pagetitle-menu"><?$APPLICATION->ShowViewContent("sonet-slider-pagetitle")?></div>
			<div class="sonet-slider-pagetitle">
				<span id="pagetitle" class="sonet-pagetitle-item"><? $APPLICATION->ShowTitle(); ?></span>
				<span id="pagesubtitle" class="sonet-pagesubtitle-item"><? $APPLICATION->ShowProperty('PageSubtitle'); ?></span>
			</div>
		</div>
	</div>

	<div id="crm-frame-popup-workarea">
		<div id="workarea-content">
			<div class="workarea-content-paddings">

				<?
				$APPLICATION->IncludeComponent(
					$arParams['POPUP_COMPONENT_NAME'],
					$arParams['POPUP_COMPONENT_TEMPLATE_NAME'],
					$arParams['POPUP_COMPONENT_PARAMS']
				);
				?>

			</div>
		</div>
	</div>
</body>
</html>