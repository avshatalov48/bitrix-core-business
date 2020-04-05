<?
use \Bitrix\Main\Localization\Loc as Loc;

/**
 * @var array $arResult
 * @var array $arParams
 * @var CAllMain $APPLICATION
 */
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
$this->addExternalCss('/bitrix/css/main/table/style.css');

?>
<form method="POST">
	<?echo bitrix_sessid_post()?>
	<div class="bx-sender-letter-field" style="">
		<div class="bx-sender-caption">
			<?=Loc::getMessage('SENDER_CONFIG_ROLE_EDIT_NAME')?>:
		</div>
		<div class="bx-sender-value">
			<input type="text" name="NAME"
				value="<?=htmlspecialcharsbx($arResult['NAME'])?>"
				class="bx-sender-form-control bx-sender-letter-field-input"
			>
		</div>
	</div>

	<br>
	<br>
	<table class="table-blue-wrapper">
		<tr>
			<td>
				<table class="table-blue">
					<tr>
						<th class="table-blue-td-title">
							<?=Loc::getMessage('SENDER_CONFIG_ROLE_EDIT_ENTITY')?>
						</th>
						<th class="table-blue-td-title">
							<?=Loc::getMessage('SENDER_CONFIG_ROLE_EDIT_ACTION')?>
						</th>
						<th class="table-blue-td-title">
							<?=Loc::getMessage('SENDER_CONFIG_ROLE_EDIT_PERMISSION')?>
						</th>
					</tr>
					<?foreach ($arResult['LIST'] as $entity)
					{
						$firstAction = true;
						foreach ($entity['ACTIONS'] as $action)
						{
							?>
								<tr class="<?=($firstAction ? 'tr-first' : '')?>">
									<td class="table-blue-td-name">
										<?=($firstAction ? htmlspecialcharsbx($entity['NAME']) : '&nbsp;')?>
									</td>
									<td class="table-blue-td-param">
										<?=htmlspecialcharsbx($action['NAME'])?>
									</td>
									<td class="table-blue-td-select">
										<select
											class="table-blue-select"
											name="PERMISSIONS[<?=htmlspecialcharsbx($entity['CODE'])?>][<?=htmlspecialcharsbx($action['CODE'])?>]"
										>
											<?foreach ($action['PERMS'] as $permission):?>
												<option
													value="<?=htmlspecialcharsbx($permission['CODE'])?>"
													<?=($permission['SELECTED'] ? 'selected' : '')?>
												>
													<?=htmlspecialcharsbx($permission['NAME'])?>
												</option>
											<?endforeach;?>
										</select>
									</td>

								</tr>
							<?
							$firstAction = false;
						}
					}
					?>
				</table>
			</td>
		</tr>
	</table>

	<?
	$APPLICATION->IncludeComponent(
		"bitrix:sender.ui.button.panel",
		"",
		array(
			'SAVE' => array(),
			'CANCEL' => array(
				'URL' => $arParams['PATH_TO_LIST']
			),
		),
		false
	);
	?>
</form>