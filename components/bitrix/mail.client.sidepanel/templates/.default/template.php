<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

global $APPLICATION;

$APPLICATION->restartBuffer();

\Bitrix\Main\UI\Extension::load(['sidepanel', 'ui.fonts.opensans']);

?><!DOCTYPE html>
<html>
	<head>
		<script>
			if (window === window.top)
			{
				window.location = '<?=CUtil::jsEscape($APPLICATION->getCurPageParam('', array('IFRAME'))) ?>';
			}
		</script>
		<? $APPLICATION->showHead(); ?>
	</head>
	<body>
		<div style="padding: 0 20px 20px 20px; ">
			<div class="mail-msg-sidepanel-header">
				<div class="mail-msg-sidepanel-title-container">
					<div class="mail-msg-sidepanel-title">
						<? $APPLICATION->showViewContent('pagetitle_icon'); ?>
						<span class="mail-msg-sidepanel-title-text"><? $APPLICATION->showTitle(); ?></span>
					</div>
					<? $APPLICATION->showViewContent('inside_pagetitle'); ?>
				</div>
				<div class="mail-msg-sidepanel-title-below">
					<? $APPLICATION->showViewContent('below_pagetitle'); ?>
				</div>
			</div>

			<div style="position: relative; ">
				<? call_user_func_array(array($APPLICATION, 'includeComponent'), $arParams['~COMPONENT_ARGUMENTS']); ?>
			</div>

		</div>
	</body>
</html><?

require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/epilog_after.php');
exit;
