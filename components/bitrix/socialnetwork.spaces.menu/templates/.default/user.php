<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

/** @var $APPLICATION \CMain */
/** @var array $arResult */
/** @var array $arParams */
/** @var \CBitrixComponent $component */

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\UI\Extension;
use Bitrix\Main\Web\Json;

Extension::load('ui.buttons');

$messages = Loc::loadLanguageFile(__FILE__);
?>

<div class="sn-spaces__menu">

	<div id="sn-spaces__menu-logo" class="sn-spaces__list-item_icon"></div>

	<div class="sn-spaces__menu-list">
		<div class="sn-spaces__menu-buttons">
			<?php
				$APPLICATION->IncludeComponent(
					'bitrix:main.interface.buttons',
					'',
					[
						'ID' => $arResult['menuId'],
						'ITEMS' => $arResult['menuItems'],
					],
				);
			?>
		</div>
	</div>

	<div id="sn-spaces__menu-toolbar" class="sn-spaces__menu-toolbar"></div>
</div>

<script>
	BX.ready(function() {
		BX.message(<?= Json::encode($messages) ?>);

		const menu = new BX.Socialnetwork.Spaces.Menu({});

		menu.renderUserLogoTo(
			document.getElementById('sn-spaces__menu-logo')
		);

		// menu.renderUserToolbarTo(
		// 	document.getElementById('sn-spaces__menu-toolbar')
		// );
	});
</script>
