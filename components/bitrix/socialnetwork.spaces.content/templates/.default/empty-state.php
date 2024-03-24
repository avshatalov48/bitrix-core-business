<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Text\HtmlFilter;

/** @var array $arResult */
$spaceName = $arResult['spaceName'];

?>

<div class="sn-spaces__empty-state">
	<div class="sn-spaces__empty-state-icon"></div>
	<div class="sn-spaces__empty-state-title">
		<?= Loc::getMessage('SN_SPACES_NEW_SPACE_EMPTY_STATE_TITLE', [
			'#SPACE_NAME#' => HtmlFilter::encode($spaceName),
		])?>
	</div>
	<div class="sn-spaces__empty-state-text"><?= Loc::getMessage('SN_SPACES_NEW_SPACE_EMPTY_STATE_TEXT')?></div>
</div>
