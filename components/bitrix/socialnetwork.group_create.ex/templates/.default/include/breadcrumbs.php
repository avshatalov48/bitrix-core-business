<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}
/** @var array $arResult */

use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

?>
<div class="socialnetwork-group-create-ex__breadcrumbs">
	<?php

	$classListCommon = [
		'socialnetwork-group-create-ex__breadcrumbs-item',
	];

	if (count($arResult['ProjectTypes']) > 1)
	{
		$classList = array_merge($classListCommon, [
			'--step-1',
		]);

		$classList[] = '--active';
		?>
		<div class="<?= implode(' ', $classList) ?>"><?= Loc::getMessage('SONET_GCE_T_BREADCRUMBS_TYPE') ?></div>
		<?php
	}

	$classList = array_merge($classListCommon, [
		'--step-2',
	]);
	if (count($arResult['ProjectTypes']) <= 1)
	{
		$classList[] = '--active';
	}

	?>
	<div class="<?= implode(' ', $classList) ?>"><?= Loc::getMessage('SONET_GCE_T_BREADCRUMBS_FEATURES') ?></div>
	<?php

	if (count($arResult['ConfidentialityTypes']) > 1)
	{
		$classList = array_merge($classListCommon, [
			'--step-3',
		]);
		?>
		<div class="<?= implode(' ', $classList) ?>"><?= Loc::getMessage('SONET_GCE_T_BREADCRUMBS_CONFIDENTIALITY') ?></div>
		<?php
	}

	$classList = array_merge($classListCommon, [
		'--step-4',
	]);
	?>
	<div class="<?= implode(' ', $classList) ?>"><?= Loc::getMessage('SONET_GCE_T_BREADCRUMBS_TEAM') ?></div>
</div>
<?php
