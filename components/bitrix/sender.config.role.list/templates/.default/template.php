<?
/**
 * @var array $arParams
 * @var array $arResult
 * @var CAllMain $APPLICATION
 */
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

\Bitrix\Main\UI\Extension::load('ui.design-tokens');
\Bitrix\Main\Page\Asset::getInstance()->addCss('/bitrix/css/main/table/style.css');

use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

\Bitrix\Main\UI\Extension::load([
	'access',
	'ui.design-tokens'
]);

if (!$arResult['CAN_EDIT'])
{
	?>
	<div style="margin: 20px;">
		<?=$arResult['TRIAL_TEXT'];?>
		<br><br>
		<?\CBitrix24::showTariffRestrictionButtons('sender_security');?>
	</div>
	<?
	return;
}
?>

<div id="vi-permissions-edit">
<form method="POST">
	<input type="hidden" id="act" value="save" name="act">
	<?echo bitrix_sessid_post()?>
	<table class="table-blue-wrapper">
		<tr>
			<td>
				<table class="table-blue bx-vi-js-role-access-table">
					<tr>
						<td class="table-blue-td-title">&nbsp;</td>
						<td class="table-blue-td-title">&nbsp;</td>
						<td class="table-blue-td-title"><?=Loc::getMessage('SENDER_CONFIG_ROLE_LIST_ROLE')?></td>
						<td class="table-blue-td-title"><?=Loc::getMessage('SENDER_CONFIG_ROLE_LIST_AVAILABLE_CATEGORY')?></td>
						<td class="table-blue-td-title"></td>
					</tr>
					<?foreach ($arResult['ROLE_ACCESS_CODES'] as $roleAccessCode):?>
						<tr data-access-code="<?=htmlspecialcharsbx($roleAccessCode['ACCESS_CODE'])?>" data-role-id="<?=htmlspecialcharsbx($roleAccessCode['ROLE_ID'])?>">
							<td class="table-blue-td-name"><?=htmlspecialcharsbx($roleAccessCode['ACCESS_PROVIDER'])?></td>
							<td class="table-blue-td-param"><?=htmlspecialcharsbx($roleAccessCode['ACCESS_NAME'])?></td>
							<td class="table-blue-td-select">
									<select class="bx-vi-js-select-role table-blue-select" name="PERMS[<?=htmlspecialcharsbx($roleAccessCode['ACCESS_CODE'])?>]" data-access-code="<?=htmlspecialcharsbx($roleAccessCode['ACCESS_CODE'])?>">
										<?foreach ($arResult['ROLES'] as $role):?>
											<option title="<?=htmlspecialcharsbx($role['NAME'])?>" value="<?=htmlspecialcharsbx($role['ID'])?>" <?=($role['ID'] == $roleAccessCode['ROLE_ID'] ? 'selected' : '')?>>
												<?=htmlspecialcharsbx($role['NAME'])?>
											</option>
										<?endforeach;?>
									</select>
							</td>
							<td class="table-blue-td-action">
								<span class="bx-vi-js-delete-access table-blue-delete" data-access-code="<?=htmlspecialcharsbx($roleAccessCode['ACCESS_CODE'])?>"></span>
							</td>
						</tr>
					<?endforeach;?>
					<tr class="bx-vi-js-access-table-last-row">
						<td colspan="4" class="table-blue-td-link">
								<a class="bx-vi-js-add-access table-blue-link" href="javascript:void(0);"><?=Loc::getMessage('SENDER_CONFIG_ROLE_LIST_ADD_ACCESS_CODE')?></a>
						</td>
					</tr>
				</table>
			</td>
			<td>
				<table class="table-blue">
					<tr>
						<td colspan="2" class="table-blue-td-title"><?=Loc::getMessage('SENDER_CONFIG_ROLE_LIST_ROLE_LIST')?>:</td>
					</tr>
					<?foreach ($arResult['ROLES'] as $role):?>
						<tr data-role-id="<?=htmlspecialcharsbx($role['ID'])?>">
							<td class="table-blue-td-name">
								<?=htmlspecialcharsbx($role['NAME'])?>
							</td>
							<td class="table-blue-td-action">
								<a class="table-blue-edit" title="<?=Loc::getMessage('SENDER_CONFIG_ROLE_LIST_EDIT')?>" href="<?=$role['EDIT_URL']?>"></a>
								<?if($arResult['CAN_EDIT']):?>
									<span class="table-blue-delete bx-vi-js-delete-role" title="<?=Loc::getMessage('SENDER_CONFIG_ROLE_LIST_DELETE')?>" data-role-id="<?=htmlspecialcharsbx($role['ID'])?>"></span>
								<?endif?>
							</td>
						</tr>
					<?endforeach;?>
					<tr>
						<td colspan="2" class="table-blue-td-link">
							<a href="<?=$arParams['PATH_TO_ADD']?>" class="table-blue-link"><?=Loc::getMessage('SENDER_CONFIG_ROLE_LIST_ADD')?></a>
						</td>
					</tr>
				</table>
			</td>
		</tr>
	</table>
	<?
	$APPLICATION->IncludeComponent(
		"bitrix:sender.ui.button.panel",
		"",
		['SAVE' => []], false
	);
	?>

</form>
</div>
<script>
	BX.ready(function () {
		var parameters = <?=\Bitrix\Main\Web\Json::encode([
			'elementId' => 'vi-permissions-edit',
			'signedParameters' => $this->getComponent()->getSignedParameters(),
			'componentName' => $this->getComponent()->getName(),
			'mess' => [
				'error' => Loc::getMessage('SENDER_CONFIG_ROLE_LIST_ERROR'),
				'errorDelete' => Loc::getMessage('SENDER_CONFIG_ROLE_LIST_ROLE_DELETE_ERROR'),
				'delete' => Loc::getMessage('SENDER_CONFIG_ROLE_LIST_ROLE_DELETE'),
				'deleteConfirm' => Loc::getMessage('SENDER_CONFIG_ROLE_LIST_ROLE_DELETE_CONFIRM'),
				'apply' => Loc::getMessage('SENDER_CONFIG_ROLE_LIST_ROLE_OK'),
				'cancel' => Loc::getMessage('SENDER_CONFIG_ROLE_LIST_ROLE_CANCEL'),
			]
		])?>;
		BX.Sender.Role.List.init(parameters);
	});
</script>

<script type="text/template" id="bx-vi-new-access-row">
	<td class="table-blue-td-name">#PROVIDER#</td>
	<td class="table-blue-td-param">#NAME#</td>
	<td class="table-blue-td-select">
		<select class="bx-vi-js-select-role table-blue-select" name="PERMS[#ACCESS_CODE#]" data-access-code="#ACCESS_CODE#">
			<?foreach ($arResult['ROLES'] as $role):?>
				<option title="<?=htmlspecialcharsbx($role['NAME'])?>" value="<?=htmlspecialcharsbx($role['ID'])?>">
					<?=htmlspecialcharsbx($role['NAME'])?>
				</option>
			<?endforeach;?>
		</select>
	</td>
	<td class="table-blue-td-action">
		<span class="bx-vi-js-delete-access table-blue-delete" data-access-code="#ACCESS_CODE#"></span>
	</td>
</script>

