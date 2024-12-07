<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}
/**
 * @var array $arParams
 * @var array $arResult
 * @var $APPLICATION
 * @var $component
 * @var $templateFolder
 */

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\UI\Extension;
use Bitrix\Main\Web\Json;

Extension::load([
	'socialnetwork.group-privacy',
]);

$messages = Loc::loadLanguageFile(__FILE__);
?>

<div class="sn-spaces__list-anchor" id="socialnetwork-spaces-list-anchor">

</div>

<script>
	BX.ready(function() {
		BX.message(<?= Json::encode($messages) ?>);

		const userId = <?= \Bitrix\Socialnetwork\Helper\User::getCurrentUserId() ?>;
		const spaceId = parseInt('<?= CUtil::JSEscape($arResult['SELECTED_SPACE_ID']) ?>', 10);

		const spaceList = new BX.Socialnetwork.Spaces.List({
			recentSpaceIds: <?= CUtil::PhpToJSObject($arResult['RECENT_SPACE_IDS']) ?>,
			spaces: <?= CUtil::PhpToJSObject($arResult['SPACES']) ?>,
			invitationSpaceIds: <?= CUtil::PhpToJSObject($arResult['INVITATION_SPACE_IDS']) ?>,
			invitations: <?= CUtil::PhpToJSObject($arResult['INVITATIONS']) ?>,
			avatarColors: <?= CUtil::PhpToJSObject($arResult['AVATAR_COLORS']) ?>,

			selectedSpaceId: spaceId,
			filterMode: <?= CUtil::PhpToJSObject($arResult['FILTER_MODE']) ?>,
			spacesListMode: <?= CUtil::PhpToJSObject($arResult['SPACES_LIST_MODE']) ?>,
			canCreateGroup: <?= CUtil::PhpToJSObject($arResult['CAN_CREATE_GROUP']) ?>,

			pathToUserSpace: <?= CUtil::PhpToJSObject($arResult['PATH_TO_USER_SPACE']) ?>,
			pathToGroupSpace: <?= CUtil::PhpToJSObject($arResult['PATH_TO_GROUP_SPACE']) ?>,
			currentUserId: userId,

			doShowCollapseMenuAhaMoment: <?= ($arResult['doShowCollapseMenuAhaMoment'] ?? false) ? 'true' : 'false' ?>,
		});

		spaceList.create(document.getElementById('socialnetwork-spaces-list-anchor'));
	});
</script>