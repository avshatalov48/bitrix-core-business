<?
use Bitrix\Main;
use Bitrix\Main\Config;
use Bitrix\Main\Localization\Loc;

use Bitrix\Sale\Location\Admin\LocationHelper as Helper;
use Bitrix\Sale\Location\Admin\ExternalServiceHelper;
use Bitrix\Sale\Location\Admin\SearchHelper;

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/include.php");
require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/sale/prolog.php');

Loc::loadMessages(__FILE__);

if($APPLICATION->GetGroupRight("sale") < "W")
	$APPLICATION->AuthForm(Loc::getMessage("SALE_MODULE_ACCES_DENIED"));

$userIsAdmin = $APPLICATION->GetGroupRight("sale") >= "W";
CSaleLocation::locationProCheckEnabled(); // temporal

#####################################
#### Data prepare
#####################################

CJSCore::Init();
$APPLICATION->AddHeadScript('/bitrix/js/sale/core_ui_widget.js');
$APPLICATION->AddHeadScript('/bitrix/js/sale/core_ui_etc.js');
$APPLICATION->AddHeadScript('/bitrix/js/sale/core_ui_dynamiclist.js');

try
{
	$fatalFailure = false;

	#####################################
	#### ACTIONS
	#####################################

	$actionFailure = false;

	$id = intval($_REQUEST['id']) ? intval($_REQUEST['id']) : false;
	$copyId = intval($_REQUEST['copy_id']) ? intval($_REQUEST['copy_id']) : false;

	// the following parameter will be present visibly only when copying or creating blank with the same parent
	$parentId = intval($_REQUEST['parent_id']) ? intval($_REQUEST['parent_id']) : '0';
	if(!$parentId && $id)
		$parentId = Helper::getParentId($id);

	$actionSave = isset($_REQUEST['save']);
	$actionApply = isset($_REQUEST['apply']);
	$actionSaveAndAdd = isset($_REQUEST['save_and_add']);

	$formSubmitted = ($actionSave || $actionApply || $actionSaveAndAdd) && check_bitrix_sessid();

	$returnUrl = strlen($_REQUEST['return_url']) ? $_REQUEST['return_url'] : '0';

	if($userIsAdmin && !empty($_REQUEST['element']) && $formSubmitted) // form submitted, handling it
	{
		$saveAsId = intval($_REQUEST['element']['ID']);

		global $DB;
		$redirectUrl = false;

		// parent id might be updated, so re-read it from request
		if(intval($_REQUEST['PARENT_ID']))
			$parentId = intval($_REQUEST['PARENT_ID']);

		try
		{
			$DB->StartTransaction();

			if($saveAsId) // existed, updating
			{
				$res = Helper::update($saveAsId, $_REQUEST['element']);

				if($res['success']) // on successfull update ...
				{
					if($actionSave)
						$redirectUrl = $returnUrl ? $returnUrl : Helper::getListUrl($parentId); // go to the parent page

					// $actionApply : do nothing
				}
			}
			else // new or copyed item
			{
				$res = Helper::add($_REQUEST['element']);
				if($res['success']) // on successfull add ...
				{
					if($actionSave)
						$redirectUrl = $returnUrl ? $returnUrl : Helper::getListUrl($parentId); // go to the parent list page

					if($actionApply)
						$redirectUrl = $returnUrl ? $returnUrl : Helper::getEditUrl($res['id']); // go to the page of just created item
				}
			}

			// no matter we updated or added a new item - we go to blank page on $actionSaveAndAdd
			if($res['success'] && $actionSaveAndAdd)
				$redirectUrl = Helper::getEditUrl(false, array('parent_id' => $parentId)); // go to the blank page with correct parent_id to create

			// on failure just show sad message
			if(!$res['success'])
				throw new Main\SystemException(implode('<br />', $res['errors']));

			$DB->Commit();

			if($redirectUrl)
				LocalRedirect($redirectUrl);
		}
		catch(Main\SystemException $e)
		{
			$actionFailure = true;

			$code = $e->getCode();
			$message = $e->getMessage().(!empty($code) ? ' ('.$code.')' : '');

			$actionFailureMessage = Loc::getMessage('SALE_LOCATION_E_CANNOT_'.($saveAsId ? 'UPDATE' : 'SAVE').'_ITEM').(strlen($message) ? ': <br /><br />'.$message : '');

			$DB->Rollback();
		}
	}

	if(!$returnUrl)
		$returnUrl = Helper::getListUrl($parentId); // default return page for "cancel" action

	#####################################
	#### READ FORM DATA
	#####################################

	$readAsId = $id ? $id : $copyId;

	if($formSubmitted && $actionFailure) // if form were submitted, but form action (add or update) failed
	{
		// load from request
		$formData = $_REQUEST['element'];

		if($readAsId)
			$nameToDisplay = Helper::getNameToDisplay($readAsId);

		// cleaning up empty external data
		if(is_array($formData['EXTERNAL']) && !empty($formData['EXTERNAL']))
		{
			foreach($formData['EXTERNAL'] as $eId => $external)
			{
				if(!strlen($external['XML_ID']))
					unset($formData['EXTERNAL'][$eId]);
			}
		}
	}
	else
	{
		if($readAsId)
		{
			// load from database
			$formData = Helper::getFormData($readAsId);

			if($readAsId)
			{
				$langU = ToUpper(LANGUAGE_ID);
				$nameToDisplay = strlen($formData['NAME_'.$langU]) ? $formData['NAME_'.$langU] : $formData['CODE'];
			}
		}
		else
		{
			// load blank form, optionally with parent id filled up
			$formData = array();
			if($parentId)
				$formData['PARENT_ID'] = $parentId;
		}
	}
}
catch(Main\SystemException $e)
{
	$fatalFailure = true;

	$code = $e->getCode();
	$fatalFailureMessage = $e->getMessage().(!empty($code) ? ' ('.$code.')' : '');
}

#####################################
#### PAGE INTERFACE GENERATION
#####################################

if(!$fatalFailure) // no fatals like "module not installed, etc."
{
	$topMenu = new CAdminContextMenu(array(
		array(
			"TEXT" => GetMessage("SALE_LOCATION_E_GO_BACK"),
			"LINK" => Helper::getListUrl($parentId),
			"ICON" => "btn_list",
		)
	));

	$tabControl = new CAdminForm("tabcntrl_location_node_edit", array(
		array(
			"DIV" => "main",
			"TAB" => Loc::getMessage('SALE_LOCATION_E_MAIN_TAB'),
			"TITLE" =>  Loc::getMessage('SALE_LOCATION_E_MAIN_TAB_TITLE')
		),
		array(
			"DIV" => "external",
			"TAB" => Loc::getMessage('SALE_LOCATION_E_EXTERNAL_TAB'),
			"TITLE" => Loc::getMessage('SALE_LOCATION_E_EXTERNAL_TAB_TITLE')
		)
	));
	$tabControl->BeginPrologContent();
	$tabControl->EndPrologContent();
	$tabControl->BeginEpilogContent();

	?>
	<?if(strlen($_REQUEST['return_url'])):?>
		<input type="hidden" name="return_url" value="<?=htmlspecialcharsbx($returnUrl)?>">
	<?endif?>
	<?=bitrix_sessid_post()?>
	<?
	$tabControl->EndEpilogContent();
}

$APPLICATION->SetTitle(strlen($nameToDisplay) ? Loc::getMessage('SALE_LOCATION_E_ITEM_EDIT', array('#ITEM_NAME#' => $nameToDisplay)) : Loc::getMessage('SALE_LOCATION_E_ITEM_NEW'));
?>

<?require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");?>

<?
#####################################
#### Data output
#####################################
?>

<?//temporal code?>
<?if(!CSaleLocation::locationProCheckEnabled())require($DOCUMENT_ROOT."/bitrix/modules/main/include/epilog_admin.php");?>

<?SearchHelper::checkIndexesValid();?>

<?if($fatalFailure):?>

	<?CAdminMessage::ShowMessage(array('MESSAGE' => $fatalFailureMessage, 'type' => 'ERROR'))?>

<?else:?>

	<?if($actionFailure):?>
		<?CAdminMessage::ShowMessage(array('MESSAGE' => $actionFailureMessage, 'type' => 'ERROR'))?>
	<?endif?>

	<?
	$topMenu->Show();

	$args = array();
	if(intval($_REQUEST['parent_id']))
		$args['parent_id'] = intval($_REQUEST['parent_id']);

	$tabControl->Begin(array(
		"FORM_ACTION" => Helper::getEditUrl(intval($_REQUEST[Helper::URL_PARAM_ID]) ? intval($_REQUEST[Helper::URL_PARAM_ID]) : false, $args) // generally, it is not safe to leave action empty
	));
	$tabControl->BeginNextFormTab();
	?>

	<?$requiredFld = ' class="adm-detail-required-field"';?>

	<?$columns = Helper::getColumns('detail');?>
	<?foreach($columns as $code => $field):?>

		<?if($code == 'ID' && !$id) continue; // new node or copied ?>
		<?if(Helper::checkIsNameField($code)) continue; // we`ll output names in a different manner ?>

		<?$value = Helper::makeSafeDisplay($formData[$code], $code);?>

		<?$tabControl->BeginCustomField($code, $field['title']);?>

			<?if(!$geoHeadingShown && ($code == 'LATITUDE' || $code == 'LONGITUDE')):?>
				<tr class="heading">
					<td colspan="2"><?=Loc::getMessage('SALE_LOCATION_E_GEODATA')?></td>
				</tr>
				<?$geoHeadingShown = true;?>
			<?endif?>

			<tr<?=($field['required'] || $code == 'ID' ? $requiredFld : '')?>>
				<td width="40%"><?=$field['title']?>:</td>
				<td width="60%">

					<?if($code == 'ID'):?>

						<?=$id?>
						<input type="hidden" name="element[ID]" value="<?=$id?>" />

					<?elseif($code == 'TYPE_ID'):?>

						<select name="element[TYPE_ID]">
							<?foreach(Helper::getTypeList() as $tId => $type):?>
								<option value="<?=$tId?>"<?=($tId == $value ? ' selected' : '')?>><?=htmlspecialcharsbx($type)?></option>
							<?endforeach?>
						</select>

					<?elseif($code == 'PARENT_ID'):?>

						<div style="max-width: 500px">

							<?/*$APPLICATION->IncludeComponent(
								"bitrix:map.yandex.view",
								"",
							Array(),
							false
							);*/?>

							<?$APPLICATION->IncludeComponent("bitrix:sale.location.selector.".Helper::getWidgetAppearance(), "", array(
								"ID" => $value,
								"CODE" => "",
								"INPUT_NAME" => "element[PARENT_ID]",
								"PROVIDE_LINK_BY" => "id",
								"SHOW_ADMIN_CONTROLS" => 'Y',
								"SELECT_WHEN_SINGLE" => 'N',
								"FILTER_BY_SITE" => 'N',
								"SHOW_DEFAULT_LOCATIONS" => 'N',
								"SEARCH_BY_PRIMARY" => 'Y',

								"EXCLUDE_SUBTREE" => $nodeId,
								),
								false
							);?>

						</div>

					<?else:?>

						<input type="text" name="element[<?=$code?>]" value="<?=$value?>" <?if($code == 'SORT'):?>size="7"<?endif?> />

					<?endif?>

				</td>
			</tr>
		<?$tabControl->EndCustomField($code, '');?>

	<?endforeach?>

	<?
	$languages = Helper::getLanguageList();
	$nameMap = Helper::getNameMap();
	?>
	<?$tabControl->BeginCustomField('NAME', Loc::getMessage('SALE_LOCATION_E_HEADING_NAME_ALL'));?>
	<?foreach($languages as $lang):?>

		<tr class="heading">
			<td colspan="2"><?=Loc::getMessage('SALE_LOCATION_E_HEADING_NAME', array('#LANGUAGE_ID#' => htmlspecialcharsbx($lang)))?></td>
		</tr>

		<?$lang = ToUpper($lang);?>

		<?foreach($nameMap as $code => $field):?>
			<?$value = Helper::makeSafeDisplay($formData[$code.'_'.$lang], $code);?>
			<tr<?=($field['required'] || $code == 'ID' ? $requiredFld : '')?>>
				<td width="40%"><?=$field['title']?></td>
				<td width="60%">
					<input type="text" name="element[<?=$code?>_<?=$lang?>]" value="<?=$value?>" size="20" maxlength="255" />
				</td>
			</tr>
		<?endforeach?>

	<?endforeach?>
	<?$tabControl->EndCustomField('NAME', '');?>

	<?$tabControl->BeginNextFormTab();?>
	<?$tabControl->BeginCustomField('EXTERNAL', Loc::getMessage('SALE_LOCATION_E_HEADING_EXTERNAL'));?>

		<?$services = Helper::getExternalServicesList();?>
		<tr>
			<td>

				<?if(empty($services)):?>

					<?=Loc::getMessage('SALE_LOCATION_E_NO_SERVICES')?> <a href="<?=ExternalServiceHelper::getListUrl()?>" target="blank_"><?=Loc::getMessage('SALE_LOCATION_E_NO_SERVICES_LIST_PAGE')?></a>

				<?else:?>

					<?$randTag = rand(99, 999);?>
					<?$externalMap = Helper::getExternalMap();?>
					<div id="ib_external_values_<?=$randTag?>">

						<table class="internal" style="margin: 0 auto">
							<tbody class="bx-ui-dynamiclist-container">
								<tr class="heading">
									<?foreach($externalMap as $code => $field):?>
										<td><?=$field['title']?></td>
									<?endforeach?>
									<td><?=Loc::getMessage('SALE_LOCATION_E_HEADER_EXT_REMOVE')?></td>
								</tr>

								<?if(is_array($formData['EXTERNAL']) && !empty($formData['EXTERNAL'])):?>

									<?foreach($formData['EXTERNAL'] as $id => $ext):?>
										<tr>
											<?foreach($externalMap as $code => $field):?>
												<?$value = Helper::makeSafeDisplay($ext[$code], $code);?>
												<td>
													<?if($code == 'SERVICE_ID'):?>
														<select name="element[EXTERNAL][<?=$ext['ID']?>][<?=$code?>]">
															<?foreach($services as $sId => $serv):?>
																<option value="<?=intval($serv['ID'])?>"<?=($serv['ID'] == $value ? ' selected' : '')?>><?=htmlspecialcharsbx($serv['CODE'])?></option>
															<?endforeach?>
														</select>
													<?elseif($code == 'ID'):?>
														<?if(intval($value)):?>
															<?=$value?>
														<?endif?>
													<?else:?>
														<input type="text" name="element[EXTERNAL][<?=$ext['ID']?>][<?=$code?>]" value="<?=$value?>" size="20" />
													<?endif?>
												</td>
											<?endforeach?>

											<td style="text-align: center">
												<?if($ext['ID']):?>
													<input type="checkbox" name="element[EXTERNAL][<?=$ext['ID']?>][REMOVE]" value="1" />
												<?endif?>
											</td>
										</tr>
									<?endforeach?>

								<?endif?>

								<script type="text/html" data-template-id="bx-ui-dynamiclist-row">
									<tr>
										<td></td>
										<td>
											<select name="element[EXTERNAL][n{{column_id}}][SERVICE_ID]">
												<?foreach($services as $sId => $serv):?>
													<option value="<?=intval($serv['ID'])?>"><?=htmlspecialcharsbx($serv['CODE'])?></option>
												<?endforeach?>
											</select>
										</td>
										<td>
											<input type="text" name="element[EXTERNAL][n{{column_id}}][XML_ID]" value="" size="20" />
										</td>
										<td></td>
									</tr>
								</script>
							</tbody>
						</table>

						<div style="width: 100%; text-align: center; margin: 10px 0;">
							<input class="adm-btn-big bx-ui-dynamiclist-addmore" type="button" value="<?=Loc::getMessage('SALE_LOCATION_E_HEADER_EXT_MORE')?>" title="<?=Loc::getMessage('SALE_LOCATION_E_HEADER_EXT_MORE')?>">
						</div>

					</div>

					<script>
						new BX.ui.dynamicList({
							scope: 'ib_external_values_<?=$randTag?>',
							initiallyAdd: 3
						});
					</script>

				<?endif?>

			</td>
		</tr>
	<?$tabControl->EndCustomField('EXTERNAL', '');?>

	<?
	$tabControl->Buttons(array(
		"disabled" => !$userIsAdmin,
		"btnSaveAndAdd" => true,
		"btnApply" => true,
		"btnCancel" => true,
		"back_url" => $returnUrl,
	));

	$tabControl->Show();
	?>

<?endif?>

<?require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/epilog_admin.php");?>
