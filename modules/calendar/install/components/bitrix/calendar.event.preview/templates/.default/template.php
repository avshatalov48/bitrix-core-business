<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true){
	die();
}

/**
 * @var $arParams
 * @var $arResult
 */

use Bitrix\Main\UI;
use Bitrix\Main\Web\Uri;

UI\Extension::load([
	'ui.link',
	'ui.icons.b24',
	'ui.urlpreview',
]);

?><div class="event-preview">
	<div class="event-preview-info">
		<a href="<?= $arParams['URL'] ?>" target="_blank" class="ui-link ui-link-dashed"><?= htmlspecialcharsbx($arResult['EVENT']['NAME']) ?></a><br>
		<?= htmlspecialcharsbx($arResult['EVENT']['~FROM_TO_HTML'])?>
	</div>
</div>
<div class="event-participants">
	<div class="event-participants-inner"><?php
		foreach ($arResult['EVENT']['ACCEPTED_ATTENDEES'] as $attendee)
		{
			$style = (!empty($attendee['AVATAR']) ? 'background-image: url('. Uri::urnEncode($attendee['AVATAR']) .');' : '');
			?><span class="ui-icon ui-icon-common-user event-participants-user" title="<?= htmlspecialcharsbx($attendee['DISPLAY_NAME']) ?>">
				<i style="<?= $style ?>"></i>
			</span><?php
		}
	?></div><?php

	$moreCount = (int)$arResult['EVENT']['ACCEPTED_ATTENDEES_COUNT'] - (int)$arResult['EVENT']['ACCEPTED_ATTENDEES_LIMIT'];

	if ($moreCount > 0)
	{
		if (
			($moreCount % 100) > 10
			&& ($moreCount % 100) < 20
		)
		{
			$suffix = 5;
		}
		else
		{
			$suffix = $moreCount % 10;
		}

		?><div class="event-participants-more"><?= \Bitrix\Main\Localization\Loc::getMessage('CALENDAR_EVENT_PREVIEW_MORE_' .$suffix, [
			'#COUNT#' => $moreCount,
	] )?></div><?php
	}
?></div>