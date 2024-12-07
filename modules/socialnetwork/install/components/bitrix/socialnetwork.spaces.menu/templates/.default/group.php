<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

/** @var $APPLICATION \CMain */
/** @var array $arResult */
/** @var array $arParams */
/** @var \CBitrixComponent $component */

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\UI\Extension;
use Bitrix\Main\Web\Json;

Extension::load([
	'im.public',
	'ui.buttons',
	'ui.buttons.icons',
	'ui.icon-set.main',
	'ui.entity-selector',
	'sidepanel',
	'tasks.scrum.meetings',
	'tasks.scrum.methodology',
	'socialnetwork.group-privacy',
	'socialnetwork.logo',
	'socialnetwork.controller',
]);

$messages = Loc::loadLanguageFile(__FILE__);

if ($arResult['isScrum'])
{
	Extension::load([
		'tasks.scrum.meetings',
		'tasks.scrum.methodology',
	]);
}
?>

<div class="sn-spaces__menu">

	<div id="sn-spaces__menu-logo" class="sn-spaces__space-logo"></div>

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

		const menu = new BX.Socialnetwork.Spaces.Menu({
			type: 'group',
			entityId: '<?= (int) $arResult['groupId'] ?>',
			currentUserId: '<?= (int) $arResult['userId'] ?>',
			groupMembersList: <?= CUtil::PhpToJSObject($arResult['groupMembersList'] ?? []) ?>,
			logo: <?= Json::encode($arResult['logo']) ?>,
			availableFeatures: <?= CUtil::PhpToJSObject($arResult['availableFeatures']) ?>,
			pathToFeatures: '<?= CUtil::JSescape($arResult['pathToGroupFeatures']) ?>',
			pathToDiscussions: '<?= CUtil::JSescape($arResult['pathToDiscussions']) ?>',
			pathToUsers: '<?= CUtil::JSescape($arResult['pathToGroupUsers']) ?>',
			pathToInvite: '<?= CUtil::JSescape($arResult['pathToGroupInvite']) ?>',
			canInvite: <?= ($arResult['canInvite'] ?? false) ? 'true' : 'false' ?>,
			isNew: <?= ($arResult['isNew'] ?? false) ? 'true' : 'false' ?>,
			isMember: <?= ($arResult['isMember'] ?? false) ? 'true' : 'false' ?>,
			pathToScrumTeamSpeed: '<?= CUtil::JSescape($arResult['pathToScrumTeamSpeed']) ?>',
			pathToScrumBurnDown: '<?= CUtil::JSescape($arResult['pathToScrumBurnDown']) ?>',
			pathToGroupTasksTask: '<?= CUtil::JSescape($arResult['pathToGroupTasksTask']) ?>',
		});

		const paths = {
			pathToUsers: '<?= $arResult['pathToGroupUsers'] ?>',
			pathToCommonSpace: '<?= $arResult['pathToCommonSpace'] ?>',
			pathToFeatures: '<?= $arResult['pathToGroupFeatures'] ?>',
			pathToInvite: '<?= $arResult['pathToGroupInvite'] ?>',
		};

		BX.Socialnetwork.Controller.paths = paths;
		top.BX.Runtime.loadExtension('socialnetwork.controller').then((exports) => {
			const { Controller } = exports;

			Controller.paths = paths;
		});

		menu.renderLogoTo(document.getElementById('sn-spaces__menu-logo'));
		<?php if ($arResult['isScrum']): ?>
			menu.renderScrumToolbarTo(document.getElementById('sn-spaces__menu-toolbar'));
		<?php else: ?>
			menu.renderToolbarTo(document.getElementById('sn-spaces__menu-toolbar'));
		<?php endif; ?>

	});
</script>
