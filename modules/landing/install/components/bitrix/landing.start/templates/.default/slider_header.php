<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}
?><!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?= LANGUAGE_ID?>" lang="<?= LANGUAGE_ID?>">
<head>
	<script type="text/javascript">
		// Prevent loading page without header and footer
		if (window === window.top)
		{
			window.location = "<?= \CUtil::JSEscape($APPLICATION->GetCurPageParam('', array('IFRAME')));?>";
		}
	</script>
	<?$APPLICATION->ShowHead();?>
</head>
<body class="template-<?= defined('SITE_TEMPLATE_ID') ? SITE_TEMPLATE_ID : '';?> <?$APPLICATION->ShowProperty('BodyClass');?>">
	<div class="landing-slider-pagetitle-wrap">
		<div class="landing-slider-pagetitle-inner-container">
			<div class="landing-slider-pagetitle-menu" id="pagetitle-menu">
				<?$APPLICATION->ShowViewContent('pagetitle');?>
			</div>
			<div class="landing-slider-pagetitle">
				<span id="pagetitle" class="landing-slider-pagetitle-item"><?$APPLICATION->ShowTitle(false);?></span>
			</div>
		</div>
	</div>

	<div class="landing-slider-content">
		<div id="sidebar"><?$APPLICATION->ShowViewContent('sidebar');?></div>
		<div id="workarea-content">
			<div class="workarea-content-paddings">