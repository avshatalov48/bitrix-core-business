<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

/** @var array $arResult */

use Bitrix\Main\Text\HtmlFilter;
use Bitrix\Main\UI\Extension;

Extension::load("ui.alerts");

?>

<div class='sonet-counters-container'>
	<div class="ui-alert ui-alert-danger">
		<span class="ui-alert-message">
			<?= HtmlFilter::encode($arResult['ERROR'] . ' '.$arResult['ERROR_CODE']);?>
		</span>
	</div>
</div>