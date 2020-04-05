<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use \Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

\Bitrix\Main\UI\Extension::load("ui.buttons");
?>
<div class="sale-admin-page-wrap">
	<div class="sale-admin-page">
		<div class="sale-admin-page-title"><?=$arResult["crm"]["title"]?></div>
		<div class="sale-admin-page-link">
			<div class="sale-admin-page-text"><?=Loc::getMessage("SAPS_LINK_TEXT")?></div>
			<a class="ui-btn ui-btn-lg ui-btn-primary" href="<?=$arResult["crm_link"]?>"><?=Loc::getMessage("SAPS_LINK_BUTTON")?></a>
			<br>
			<a class="ui-btn ui-btn-lg ui-btn-link" href="<?=$arResult["current_page"]?>"><?=Loc::getMessage("SAPS_LINK_BUTTON_OPEN_ADMIN_PAGE")?></a>
		</div>
	</div>
</div>