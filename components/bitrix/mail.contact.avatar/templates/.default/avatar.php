<?php

use Bitrix\Main\Web\Uri;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();
\Bitrix\Main\UI\Extension::load(['ui.icons.b24']);
?>
<span class="ui-icon ui-icon-common-user mail-contact-avatar" style="min-width: <?=$arResult['avatarSize'] ?>px; width: <?=$arResult['avatarSize'] ?>px; min-height: <?=$arResult['avatarSize'] ?>px; height: <?=$arResult['avatarSize'] ?>px; font-size: <?=$arResult['initialsFontSize'] ?>px; line-height: <?=$arResult['avatarSize'] ?>px;">
	<i style="background: url('<?= Uri::urnEncode(htmlspecialcharsbx($arResult['image']['src'])) ?>'); background-size: <?=$arResult['avatarSize'] ?>px <?=$arResult['avatarSize'] ?>px;"></i>
</span>
