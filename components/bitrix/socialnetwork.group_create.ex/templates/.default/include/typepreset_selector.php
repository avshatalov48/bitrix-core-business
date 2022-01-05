<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Security\Random;
use Bitrix\Main\Loader;

/** @var CBitrixComponentTemplate $this */
/** @var array $arParams */
/** @var array $arResult */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */

if (
	$arResult['USE_PRESETS'] === 'Y'
	&& (empty($arResult['TAB']))
)
{
	?>
	<div class="socialnetwork-group-create-ex__content-title"><?= htmlspecialcharsbx(Loc::getMessage('SONET_GCE_T_TYPEPRESET_TITLE')) ?></div>
	<div class="socialnetwork-group-create-ex__content-wrapper socialnetwork-group-create-ex__type-preset-wrapper">
		<?php

		$projectTypeCode = \Bitrix\Socialnetwork\Helper\Workgroup::getProjectTypeCodeByParams([
			'typesList' => $arResult['ProjectTypes'],
			'fields' => $arResult['POST'],
		]);

		foreach ($arResult['ProjectTypes'] as $code => $type)
		{
			$classList = [
				'socialnetwork-group-create-ex__group-selector',
				'socialnetwork-group-create-ex__type-preset-selector',
			];
			if ($projectTypeCode === $code)
			{
				$classList[] = '--active';
			}

			$new = ($code === 'scrum')
			?>
			<div class="<?= implode(' ', $classList) ?>" data-bx-project-type="<?= htmlspecialcharsbx($code) ?>">
				<div class="socialnetwork-group-create-ex__group-selector--logo --<?= htmlspecialcharsbx($code) ?>"></div>
				<div class="socialnetwork-group-create-ex__group-selector--container">
					<div class="socialnetwork-group-create-ex__group-selector--title">
						<?= htmlspecialcharsex($type['NAME']) ?>
						<?php
						if ($new)
						{
							?><div class="socialnetwork-group-create-ex__group-selector--label --new"><?= htmlspecialcharsbx(Loc::getMessage('SONET_GCE_T_TYPEPRESET_NEW')) ?></div><?php
						}
						?>
					</div>
					<div class="socialnetwork-group-create-ex__group-selector--description"><?= htmlspecialcharsex($type['DESCRIPTION']) ?></div>
				</div>
			</div>
			<?php
		}
		?>
	</div>
	<?php

/*
	?><div id="sonet_group_create_form_step_1" style="display: <?=($arResult['step1Display'] ? 'block' : 'none')?>;">
	<div id="sonet_group_create_step_1_content">
		<div class="social-group-create-container first-step"><?php

			$typeCode = \Bitrix\Socialnetwork\Helper\Workgroup::getTypeCodeByParams([
				'typesList' => $arResult['Types'],
				'fields' => $arResult['POST'],
			]);
			foreach ($arResult['TypeRowList'] as $rowCode)
			{
				?><div class="social-group-create-inner">
				<div class="social-group-create-title"><?=$arResult["TypeRowNameList"][$rowCode]?></div>
				<div class="social-group-tile-container"><?php
					foreach ($arResult[$rowCode] as $code => $type)
					{
						$selected = ($typeCode == $code);
						?><div class="social-group-tile-item" bx-type="<?=htmlspecialcharsbx($code)?>">
						<a href="#" class="social-group-tile-item-inner">
							<span class="social-group-tile-item-title"><?=htmlspecialcharsex($type["NAME"])?></span>
							<span class="social-group-tile-item-cover social-group-tile-item-cover-back<?=(!empty($type["TILE_CLASS"]) ? " ".htmlspecialcharsbx($type["TILE_CLASS"]) : "")?>"></span>
							<span class="social-group-tile-item-description"><?=htmlspecialcharsex($type["DESCRIPTION"])?></span>
						</a>
						</div><?php
					}
					?></div>
				</div><?php
			}

			?></div>
	</div>
	</div><?php
*/
}
