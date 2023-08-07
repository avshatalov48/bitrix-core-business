<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

/**
 * @var array $arParams
 * @var array $arResult
 * @var CMain $APPLICATION
 * @var CBitrixComponent $component
 * @var CBitrixComponentTemplate $this
 */

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Text\HtmlFilter;
use Bitrix\Main\UI\Extension;

Extension::load('ui.buttons');

?>
<div class="ui-admin-page-stub-wrap">
	<div class="ui-admin-page-stub">
		<div class="ui-admin-page-stub-title">
			<?= $arResult['TITLE'] ?>
		</div>
		<div class="ui-admin-page-stub-link">
			<?php if (isset($arResult['LINK_TO_NEW_PAGE'])): ?>
				<a class="ui-btn ui-btn-lg ui-btn-primary" href="<?= HtmlFilter::encode($arResult['LINK_TO_NEW_PAGE']) ?>">
					<?= Loc::getMessage("UI_APS_LINK_BUTTON") ?>
				</a>
			<?php endif; ?>

			<?php if (isset($arResult['LINK_TO_SKIP_STUB'])): ?>
				<br>
				<a class="ui-link ui-link-secondary ui-admin-page-stub-link" href="<?= HtmlFilter::encode($arResult['LINK_TO_SKIP_STUB']) ?>">
					<?= Loc::getMessage("UI_APS_LINK_BUTTON_OPEN_ADMIN_PAGE") ?>
				</a>
			<?php endif; ?>
		</div>
	</div>
</div>
