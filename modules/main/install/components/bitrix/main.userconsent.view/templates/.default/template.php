<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
{
	die();
}

/**
 * @var array $arParams
 * @var array $arResult
 */
?>
<div class="intranet-user-consent-view-wrapper">
	<?php
	if ($arResult['IS_HTML'])
	{
		echo $arResult['HTML'];
	}
	else
	{
		echo nl2br(htmlspecialcharsbx($arResult['TEXT']));
	}
	?>
</div>
