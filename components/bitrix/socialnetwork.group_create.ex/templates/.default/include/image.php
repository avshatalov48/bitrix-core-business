<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Web\Uri;

/** @var CBitrixComponentTemplate $this */
/** @var array $arParams */
/** @var array $arResult */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */
/** @var boolean $isProject */
/** @var boolean $isScrumProject */

Loc::loadMessages(__FILE__);

?>
	<script>
		BX.message({
			SONET_GCE_T_IMAGE_DELETE_CONFIRM: '<?= CUtil::JSEscape(Loc::getMessage('SONET_GCE_T_IMAGE_DELETE_CONFIRM')) ?>',
			SONET_GCE_T_IMAGE_DELETE_CONFIRM_YES: '<?= CUtil::JSEscape(Loc::getMessage('SONET_GCE_T_IMAGE_DELETE_CONFIRM_YES')) ?>',
			SONET_GCE_T_IMAGE_DELETE_CONFIRM_NO: '<?= CUtil::JSEscape(Loc::getMessage('SONET_GCE_T_IMAGE_DELETE_CONFIRM_NO')) ?>',
		});
	</script>

<?php

?>
<div class="socialnetwork-group-create-ex__project-logo" data-role="group-avatar-cont">
	<div class="socialnetwork-group-create-ex__project-logo--title socialnetwork-group-create-ex__create--switch-nonproject <?= ($isProject ? '--project' : '') ?> <?= ($isScrumProject ? '--scrum' : '') ?>"><?= htmlspecialcharsEx(Loc::getMessage('SONET_GCE_T_IMAGE3')) ?></div>
	<div class="socialnetwork-group-create-ex__project-logo--title socialnetwork-group-create-ex__create--switch-project socialnetwork-group-create-ex__create--switch-nonscrum <?= ($isScrumProject ? '--scrum' : '') ?> <?= ($isProject ? '--project' : '') ?>"><?= htmlspecialcharsEx(Loc::getMessage('SONET_GCE_T_IMAGE3_PROJECT')) ?></div>
	<div class="socialnetwork-group-create-ex__project-logo--title socialnetwork-group-create-ex__create--switch-scrum <?= ($isScrumProject ? '--scrum' : '') ?> <?= ($isProject ? '--project' : '') ?>"><?= htmlspecialcharsEx(Loc::getMessage('SONET_GCE_T_IMAGE3_SCRUM')) ?></div>
	<div class="socialnetwork-group-create-ex__project-logo--area" data-role="group-avatar-selector">
		<?php

		if (
			empty($arResult['POST']['IMAGE_SRC'])
			&& !empty($arResult['POST']['AVATAR_TYPE'])
		)
		{
			$avatarType = $arResult['POST']['AVATAR_TYPE'];
		}
		else
		{
			$avatarType = '';
		}

		foreach ($arResult['avatarTypesList'] as $code => $value)
		{
			$classList = [
				'socialnetwork-group-create-ex__project-logo--item',
				'sonet-common-workgroup-avatar',
				'--' . htmlspecialcharsbx(\Bitrix\Socialnetwork\Helper\Workgroup::getAvatarTypeWebCssClass($code)),
			];
			if (
				!empty($avatarType)
				&& $code === $avatarType
			)
			{
				$classList[] = '--selected';
			}

			?>
			<div class="<?= implode(' ', $classList) ?>" data-role="group-avatar-type" data-avatar-type="<?= htmlspecialcharsbx($code) ?>"></div>
			<?php
		}

		$style = '';
		$classList = [
			'socialnetwork-group-create-ex__project-logo--item',
			'--icon--no-image',
		];
		$removeClassList = [
			'socialnetwork-group-create-ex__project-logo--remove'
		];
		if (!empty($arResult['POST']['IMAGE_SRC']))
		{
			$classList[] = '--selected';
			$style = "background-image: url('" . Uri::urnEncode(htmlspecialcharsbx($arResult['POST']['IMAGE_SRC'])) . "'); background-size: cover;";
		}
		else
		{
			$removeClassList[] = '--hidden';
		}

		?>
		<div class="<?= implode(' ', $classList) ?>" style="<?= $style ?>" data-role="group-avatar-image">
			<div class="<?= implode(' ', $removeClassList) ?>" data-role="group-avatar-remove"></div>
		</div>
		<input
			type="hidden"
			name="GROUP_IMAGE_ID"
			value="<?= (int) ($arResult['POST']['IMAGE_ID'] ?? 0) ?>"
			data-role="group-avatar-input"
		>
		<input type="hidden" name="GROUP_AVATAR_TYPE" value="<?= htmlspecialcharsbx($arResult['POST']['AVATAR_TYPE']) ?>" data-role="group-avatar-type-input">
	</div>
</div>
<?php
