<?php
if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

/** @var $this \CBitrixComponentTemplate */
/** @var CMain $APPLICATION */
/** @var array $arResult*/
/** @var array $arParams*/

\CJSCore::Init(['sidepanel']);
$this->addExternalCss($this->GetFolder() . '/loader.css');
$urlToRedirect = array_key_exists('~PAGE_MODE_OFF_BACK_URL', $arParams) && $arParams['~PAGE_MODE_OFF_BACK_URL'] === null
	? null : $arParams['PAGE_MODE_OFF_BACK_URL'];
?>

<div class="ui-sidepanel-wrapper-loader-container">
	<svg id="ui-sidepanel-wrapper-loader" class="ui-sidepanel-wrapper-loader" viewBox="25 25 50 50">
		<circle class="ui-sidepanel-wrapper-loader-path" cx="50" cy="50" r="20" fill="none" stroke-width="1" stroke-miterlimit="10"></circle>
	</svg>
</div>

<script>
	BX.ready(function () {
		var loader = BX('ui-sidepanel-wrapper-loader');
		var link = window.location.href;
		var rule = BX.SidePanel.Instance.getUrlRule(link);
		var options = (rule && BX.type.isPlainObject(rule.options)) ? rule.options : {};
		BX.SidePanel.Instance.open(link, options);
<?php if ($urlToRedirect !== null): ?>
		BX.addCustomEvent(
			BX.SidePanel.Instance.getTopSlider(),
			"SidePanel.Slider:onCloseComplete",
			function ()
			{
				if (loader)
				{
					loader.style.display = '';
				}
				window.location.href = '<?=CUtil::JSEscape(htmlspecialcharsbx($urlToRedirect))?>';
			}
		);
<?php endif;?>
		BX.addCustomEvent(
			BX.SidePanel.Instance.getTopSlider(),
			"SidePanel.Slider:onLoad",
			function ()
			{
				if (loader)
				{
					loader.style.display = 'none';
				}
			}
		);
	})
</script>
