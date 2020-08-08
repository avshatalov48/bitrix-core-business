<?php

if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Localization\Loc;

/**
 * @var array $arResult
 * @var array $additionalParameters
 */
$additionalParameters = $arResult['additionalParameters'];

\Bitrix\Main\UI\Extension::load("ui.hint");

?>
<script>
    BX.ready(function() {
        var table = document.querySelector('[data-role="main-user-field-settings-table"]');
        if(table && BX.UI && BX.UI.Hint)
        {
            BX.UI.Hint.init(table);
        }
    })
</script>
	<tr>
		<td>
			<?= Loc::getMessage('USER_TYPE_FILE_SIZE') ?>:
		</td>
		<td>
			<input
				type="text"
				name="<?= $additionalParameters['NAME'] ?>[SIZE]"
				size="20"
				maxlength="20"
				value="<?= $arResult['values']['size'] ?>"
			>
		</td>
	</tr>

	<tr>
		<td>
			<?= Loc::getMessage('USER_TYPE_FILE_WIDTH_AND_HEIGHT') ?>:
		</td>
		<td>
			<input
				type="text"
				name="<?= $additionalParameters['NAME'] ?>[LIST_WIDTH]"
				size="7"
				maxlength="20"
				value="<?= $arResult['values']['width'] ?>"
			>
			&nbsp;x&nbsp;
			<input
				type="text"
				name="<?= $additionalParameters['NAME'] ?>[LIST_HEIGHT]"
				size="7"
				maxlength="20"
				value="<?= $arResult['values']['height'] ?>"
			>
		</td>
	</tr>

	<tr>
		<td>
			<?= Loc::getMessage('USER_TYPE_FILE_MAX_SHOW_SIZE') ?>:
		</td>
		<td>
			<input
				type="text"
				name="<?= $additionalParameters['NAME'] ?>[MAX_SHOW_SIZE]"
				size="20"
				maxlength="20"
				value="<?= $arResult['values']['max_show_size'] ?>"
			>
		</td>
	</tr>

	<tr>
		<td>
			<?= Loc::getMessage('USER_TYPE_FILE_MAX_ALLOWED_SIZE') ?>:
		</td>
		<td>
			<input
				type="text"
				name="<?= $additionalParameters['NAME'] ?>[MAX_ALLOWED_SIZE]"
				size="20"
				maxlength="20"
				value="<?= $arResult['values']['max_allowed_size'] ?>"
			>
		</td>
	</tr>

<?php
if($arResult['additionalParameters']['bVarsFromForm'])
{
	?>
	<tr>
		<td>
			<?= Loc::getMessage('USER_TYPE_FILE_EXTENSIONS') ?>:
		</td>
		<td>
			<input
				type="text"
				size="20"
				name="<?= $additionalParameters['NAME'] ?>[EXTENSIONS]"
				value="<?= $arResult['values']['extensions'] ?>"
			>
		</td>
	</tr>
	<?php
}
else
{
	?>
	<tr>
		<td>
			<?= Loc::getMessage('USER_TYPE_FILE_EXTENSIONS') ?>:<span data-hint="<?=Loc::getMessage('USER_TYPE_FILE_EXTENSIONS_HINT');?>"></span>
		</td>
		<td>
			<input
				type="text"
				size="20"
				name="<?= $additionalParameters['NAME'] ?>[EXTENSIONS]"
				value="<?= $arResult['values']['extensions'] ?>"
			>
		</td>
	</tr>
	<?php
}
?>

<tr>
	<td>
		<?= Loc::getMessage('USER_TYPE_FILE_TARGET_BLANK') ?>:
	</td>
	<td>
		<input
			type="hidden"
			name="<?= $additionalParameters['NAME'] ?>[TARGET_BLANK]"
			value="N"
		>
		<label class="adm-detail-label">
			<input
				type="checkbox"
				name="<?= $additionalParameters['NAME'] ?>[TARGET_BLANK]"
				value="Y"
				<?= ($arResult['values']['targetBlank'] === 'N' ? '' : '	checked="checked"') ?>
			>
			<span class="adm-detail-label-text"><?= Loc::getMessage('MAIN_YES') ?></span>
		</label>
	</td>
</tr>
