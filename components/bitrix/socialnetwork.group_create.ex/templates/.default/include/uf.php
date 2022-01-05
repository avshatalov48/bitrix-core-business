<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\UserField;

/** @var CBitrixComponentTemplate $this */
/** @var array $arParams */
/** @var array $arResult */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */

Loc::loadMessages(__FILE__);

if (
	!empty($arResult['GROUP_PROPERTIES_MANDATORY'])
	|| !empty($arResult['GROUP_PROPERTIES_NON_MANDATORY'])
)
{
	?>
	<div class="socialnetwork-group-create-ex__content-title"><?= htmlspecialcharsEx(Loc::getMessage('SONET_GCE_T_UF_TITLE')) ?></div>
	<?php
}

if (!empty($arResult['GROUP_PROPERTIES_MANDATORY']))
{
	$mandatoryUFList = [];
	?><div class="social-group-create-options"><?php
		foreach ($arResult['GROUP_PROPERTIES_MANDATORY'] as $FIELD_NAME => $arUserField)
		{
			$fieldWrapperId = $FIELD_NAME . '_wrapper';
			?>
			<div class="socialnetwork-group-create-ex__content-block">
				<div class="socialnetwork-group-create-ex__text --s ui-ctl-label-text"><?= htmlspecialcharsex($arUserField['EDIT_FORM_LABEL']) ?></div>
				<div class="socialnetwork-group-create-ex__content-block socialnetwork-group-create-ex__content--user-fields --space-bottom" id="<?= htmlspecialcharsbx($fieldWrapperId) ?>">
					<?php

					echo (new UserField\Renderer($arUserField, [
						'mode' => UserField\Types\BaseType::MODE_EDIT,
					]))->render();

					$mandatoryUFList[] = [
						'id' => $fieldWrapperId,
						'type' => $arUserField['USER_TYPE_ID'],
					];

					?>
				</div>
			</div>
			<?php
		}

		?>
		<script>

			BX.ready(
				function()
				{
					new BX.Socialnetwork.WorkgroupFormUFManager({
						mandatoryUFList: <?= CUtil::phpToJSObject($mandatoryUFList) ?>,
					});
				}
			);

		</script>
	</div>
	<?php
}

if (!empty($arResult['GROUP_PROPERTIES_NON_MANDATORY']))
{
	foreach ($arResult['GROUP_PROPERTIES_NON_MANDATORY'] as $FIELD_NAME => $arUserField)
	{
		?>
		<div class="socialnetwork-group-create-ex__content-block">
			<div class="socialnetwork-group-create-ex__text --s ui-ctl-label-text"><?= htmlspecialcharsex($arUserField['EDIT_FORM_LABEL']) ?></div>
			<div class="socialnetwork-group-create-ex__content-block --space-bottom">
				<?php

				echo (new UserField\Renderer($arUserField, [
					'mode' => UserField\Types\BaseType::MODE_EDIT,
				]))->render();

				?>
			</div>
		</div>
		<?php
	}
}
