<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

/** @var array $arResult */

use Bitrix\Main\Text\HtmlFilter;
use Bitrix\Main\UI\Extension;

Extension::load('ui.alerts');

?>

<div class='sn-spaces__error'>
	<div class="ui-alert ui-alert-danger">
		<span class="ui-alert-message">
			<?= $arResult['errorMessage'] . ' ' . $arResult['errorCode']; ?>
		</span>
	</div>
</div>