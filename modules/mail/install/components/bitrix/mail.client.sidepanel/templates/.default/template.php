<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

global $APPLICATION;

$APPLICATION->restartBuffer();

?><!DOCTYPE html>
<html>
	<head>
		<script type="text/javascript">
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
				<div class="mail-msg-sidepanel-header-ext">
					<? $APPLICATION->showViewContent('pagetitle'); ?>
				</div>
				<div class="mail-msg-sidepanel-title">
					<? $APPLICATION->showViewContent('pagetitle_icon'); ?>
					<span><? $APPLICATION->showTitle() ?></span>
				</div>
			</div>

			<?

			call_user_func_array(array($APPLICATION, 'includeComponent'), $arParams['~COMPONENT_ARGUMENTS']);

			?>

		</div>
	</body>
</html><?

require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/epilog_after.php');
exit;
