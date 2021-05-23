<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;
use \Bitrix\Landing\Manager;

Loc::loadMessages(__FILE__);
Manager::setPageTitle(Loc::getMessage('LANDING_TPL_TITLE'));
\Bitrix\Main\Page\Asset::getInstance()->addCss(
	'/bitrix/css/main/table/style.css'
);

if ($arResult['ERRORS'])
{
	\showError(implode("\n", $arResult['ERRORS']));
}
if ($arResult['FATAL'])
{
	return;
}

// access
\CJSCore::init(['access']);
$drawSelect = function($position = '#inc#', $selectedId = null) use($arResult)
{
	$select = '<select class="table-blue-select" name="rights[ROLE_ID][' . $position . ']">';
	foreach ($arResult['ROLES'] as $role)
	{
		$selected = ($role['ID'] == $selectedId) ? ' selected="selected"' : '';
		$role['TITLE'] = \htmlspecialcharsbx($role['TITLE']);
		$select .= '<option title="'. $role['TITLE'] . '" value="'. $role['ID'] .'"' . $selected . '>';
		$select .= $role['TITLE'];
		$select .= '</option>';
	}
	$select .= '</select>';

	return $select;
};
if (isset($arResult['ACCESS_CODES']))
{
	$accessCodes = array_keys($arResult['ACCESS_CODES']);
}
?>

<?if ($arResult['EXTENDED']):?>
<form action="<?=POST_FORM_ACTION_URI;?>" method="post">
	<input type="hidden" name="action" value="saveExtended" />
	<?= bitrix_sessid_post();?>
	<table class="table-blue landing-additional-rights-table" id="landing-additional-rights-table">
		<tbody>
		<?foreach ($arResult['ADDITIONAL'] as $code => $title):
			$checked = !is_array($row['ADDITIONAL_RIGHTS']['CURRENT']) ||
					   in_array($code, $row['ADDITIONAL_RIGHTS']['CURRENT']);
			$accessCodes = \htmlspecialcharsbx(
				implode(',', array_keys($arResult['ACCESS_CODES'][$code]))
			);
			?>
			<tr class="tr-first">
				<td class="table-blue-td-name">
					<label for="landing-operation-additional-<?= $code;?>">
						<?= $title;?>
					</label>
				</td>
				<td class="table-blue-td-select" id="landing-additional-rights-fields-<?= $code;?>">
					<?foreach ($arResult['ACCESS_CODES'][$code] as $codeKey => $accessItem):?>
						<div>
							<input type="hidden" name="rights[<?= $code;?>][]" value="<?= \htmlspecialcharsbx($codeKey);?>">
							<?= \htmlspecialcharsbx($accessItem['PROVIDER'])?>: <?= \htmlspecialcharsbx($accessItem['NAME']);?>
							<span class="table-blue-delete table-blue-delete-landing-role" <?
							?>data-code="<?= $code;?>" <?
							?>data-id="<?= $accessItem['CODE'];?>" <?
							?>onclick="deleteAccessRowExtended(this);" <?
							?>title="<?= Loc::getMessage('LANDING_TPL_ACTION_DEL');?>"></span>
						</div>
					<?endforeach;?>
				</td>
				<td>
					<a href="javascript:void(0);" class="landing-additional-rights-form" <?
					?>data-codes="<?= $accessCodes;?>" <?
					   ?>data-id="<?= $code;?>">
						<?= Loc::getMessage('LANDING_TPL_ACTION_RIGHT');?>
					</a>
				</td>
			</tr>
		<?endforeach;?>
		</tbody>
	</table>
	<div class="pinable-block">
		<div class="landing-form-footer-container">
			<button id="landing-rights-save" type="submit" class="ui-btn ui-btn-success" name="submit" value="<?= Loc::getMessage('LANDING_TPL_BUTTON_SAVE');?>">
				<?= Loc::getMessage('LANDING_TPL_BUTTON_SAVE');?>
			</button>
			<a class="ui-btn ui-btn-md ui-btn-link" href="<?= $arParams['PAGE_URL_ROLES'];?>">
				<?= Loc::getMessage('LANDING_TPL_BUTTON_CANCEL');?>
			</a>
		</div>
	</div>
</form>

<form action="<?=POST_FORM_ACTION_URI;?>" method="post">
	<?= bitrix_sessid_post();?>
	<input type="hidden" name="action" value="mode"/>
	<p><?=Loc::getMessage('LANDING_TPL_EXTENDED_MODE');?></p>
	<button type="submit" class="ui-btn ui-btn-success" value="<?=Loc::getMessage(
		'LANDING_TPL_BUTTON_MODE_TO_ROLE'
	);?>">
		<?=Loc::getMessage('LANDING_TPL_BUTTON_MODE_TO_ROLE');?>
	</button>
</form>

<script type="text/javascript">
	BX.ready(function(){
		new BX.Landing.AccessExtended({
		});
	});
</script>

<?else:?>

<form action="<?= POST_FORM_ACTION_URI;?>" method="post">
<input type="hidden" name="action" value="save" />
<?= bitrix_sessid_post();?>
<table class="table-blue-wrapper">
	<tbody>
	<tr>
		<td>
			<table class="table-blue" id="landing-rights-table">
				<tbody>
					<tr>
						<td class="table-blue-td-title">&nbsp;</td>
						<td class="table-blue-td-title">&nbsp;</td>
						<td class="table-blue-td-title"><?= Loc::getMessage('LANDING_TPL_COL_ROLE');?></td>
						<td class="table-blue-td-title"></td>
					</tr>
					<?foreach (array_values($arResult['ACCESS_CODES']) as $i => $code):?>
					<tr>
						<td class="table-blue-td-name"><?= \htmlspecialcharsbx($code['PROVIDER'])?></td>
						<td class="table-blue-td-param"><?= \htmlspecialcharsbx($code['NAME']);?></td>
						<td class="table-blue-td-select">
							<?= $drawSelect($i, $code['ROLE_ID']);?>
							<input type="hidden" name="rights[ACCESS_CODE][<?= $i;?>]" value="<?= $code['CODE']?>">
						</td>
						<td class="table-blue-td-action">
							<span class="table-blue-delete table-blue-delete-landing-role bitrix24-metrika" data-metrika24="permission_delete" data-id="<?= $code['CODE'];?>" onclick="deleteAccessRow(this);" title="<?= Loc::getMessage('LANDING_TPL_ACTION_DEL');?>"></span>
						</td>
					</tr>
					<?endforeach;?>
					<tr>
						<td colspan="4" class="table-blue-td-link">
							<a class="table-blue-link bitrix24-metrika" data-metrika24="permission_add" href="javascript:void(0);" id="landing-rights-form">
								<?= Loc::getMessage('LANDING_TPL_ACTION_RIGHT');?>
							</a>
						</td>
					</tr>
				</tbody>
			</table>
		</td>
		<td>
			<table class="table-blue" id="landing-roles">
				<tbody>
				<tr>
					<td colspan="2" class="table-blue-td-title">
						<?= Loc::getMessage('LANDING_TPL_COL_ROLES');?>
					</td>
				</tr>
				<?foreach ($arResult['ROLES'] as $item):
					$urlEdit = str_replace(
						'#role_edit#',
						$item['ID'],
						$arParams['PAGE_URL_ROLE_EDIT']
					);
					?>
				<tr data-role-id="1">
					<td class="table-blue-td-name">
						<?= \htmlspecialcharsbx($item['TITLE']);?>
					</td>
					<td class="table-blue-td-action">
						<input type="hidden" name="roles[]" value="<?= $item['ID'];?>" />
						<a class="table-blue-edit bitrix24-metrika" data-metrika24="role_edit" title="<?= Loc::getMessage('LANDING_TPL_ACTION_EDIT');?>" href="<?= $urlEdit;?>"></a>
						<span class="table-blue-delete landing-role-delete bitrix24-metrika" data-metrika24="role_delete" title="<?= Loc::getMessage('LANDING_TPL_ACTION_DEL');?>"></span>
					</td>
				</tr>
				<?endforeach;?>
				<tr>
					<td colspan="2" class="table-blue-td-link">
						<a href="<?= str_replace('#role_edit#', 0, $arParams['PAGE_URL_ROLE_EDIT']);?>" class="table-blue-link bitrix24-metrika" data-metrika24="role_add">
							<?= Loc::getMessage('LANDING_TPL_ACTION_ADD');?>
						</a>
					</td>
				</tr>
				</tbody>
			</table>
		</td>
	</tr>
	</tbody>
</table>
<button type="submit" class="ui-btn ui-btn-success bitrix24-metrika" data-metrika24="rights_edit" id="landing-rights-save" name="submit" value="<?= Loc::getMessage('LANDING_TPL_ACTION_SAVE');?>">
	<?= Loc::getMessage('LANDING_TPL_ACTION_SAVE');?>
</button>
</form>

<form action="<?= POST_FORM_ACTION_URI;?>" method="post" id="landing-mode-form">
	<?= bitrix_sessid_post();?>
	<input type="hidden" name="action" value="mode" />
</form>

<script type="text/javascript">
	var landingAccessSelected = <?= json_encode(array_fill_keys($accessCodes, true));?>;
	BX.ready(function(){
		new BX.Landing.Access({
			select: '<?= \CUtil::jsEscape($drawSelect());?>',
			inc: <?= count($arResult['ACCESS_CODES']);?>
		});
	});
</script>

<?endif;?>