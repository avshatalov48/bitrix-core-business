<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
} ?>

<?php
	if (\Bitrix\Main\Loader::includeModule('landing'))
	{
		\Bitrix\Landing\Manager::setTheme();
	}
?>

<? $APPLICATION->ShowProperty('FooterJS'); ?>


<script>
	BX.ready(function() {
		var elements = [].slice.call(document.querySelectorAll("h1, h2, h3, h4, h5"));
		new BX.Landing.UI.Tool.autoFontScale(elements);
	});
</script>
</main>
</body>
</html>