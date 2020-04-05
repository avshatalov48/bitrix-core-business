<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

if (!\Bitrix\Main\Loader::includeModule('landing'))
{
	return;
}

\Bitrix\Landing\Manager::setTheme();
\Bitrix\Main\Page\Asset::getInstance()->addJS(
	SITE_TEMPLATE_PATH . '/assets/vendor/vendors_base.js'
);
?>

<?$APPLICATION->ShowProperty('FooterJS');?>

<script type="text/javascript">
	BX.ready(function() {
		var elements = [].slice.call(
			document.querySelectorAll("h1, h2, h3, h4, h5")
		);
		new BX.Landing.UI.Tool.autoFontScale(elements);
	});
</script>
</main>
<?$APPLICATION->ShowProperty('BeforeBodyClose');?>
</body>
</html>