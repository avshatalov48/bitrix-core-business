<?
use Bitrix\Main;
use Bitrix\Main\Config;
use Bitrix\Main\Localization\Loc;

use Bitrix\Sale\Location\Admin\DefaultSiteHelper as Helper;
use Bitrix\Sale\Location\Admin\SearchHelper;

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/include.php");
require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/sale/prolog.php');

Loc::loadMessages(__FILE__);

if($APPLICATION->GetGroupRight("sale") < "W")
	$APPLICATION->AuthForm(Loc::getMessage("SALE_MODULE_ACCES_DENIED"));

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
	// todo: real condition here!
	$userIsAdmin = true; //$USER->CanDoOperation('edit_other_settings');

	#####################################
	#### ACTIONS: update
	#####################################

	$actionFailure = false;

	$id = strlen($_REQUEST['id']) ? Helper::tryParseSiteId($_REQUEST['id']) : false;

	$actionSave = isset($_REQUEST['save']);
	$actionApply = isset($_REQUEST['apply']);
	$formSubmitted = ($actionSave || $actionApply) && check_bitrix_sessid();

	$returnUrl = strlen($_REQUEST['return_url']) ? $_REQUEST['return_url'] : false;

	if($userIsAdmin && !empty($_REQUEST['element']) && $formSubmitted) // form submitted, handling it
	{
		$saveAsId = Helper::tryParseSiteId($_REQUEST['element']['ID']);

		global $DB;
		$redirectUrl = false;

		try
		{
			$DB->StartTransaction();

			if($saveAsId) // existed, updating
			{
				$res = Helper::update($saveAsId, $_REQUEST['element']);
				if($res['success']) // on successfull update ...
				{
					if($actionSave)
						$redirectUrl = $returnUrl ? $returnUrl : Helper::getListUrl(); // go to the page of just created item

					// $actionApply : do nothing
				}
			}
			else
				throw new Main\SystemException(Loc::getMessage('SALE_LOCATION_E_ITEM_NOT_FOUND'));

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

			$actionFailureMessage = Loc::getMessage('SALE_LOCATION_E_CANNOT_UPDATE_ITEM').(strlen($message) ? ': <br /><br />'.$message : '');

			$DB->Rollback();
		}
	}

	if(!$returnUrl)
		$returnUrl = Helper::getListUrl(); // default return page for "cancel" action

	#####################################
	#### READ FORM DATA
	#####################################

	if($formSubmitted && $actionFailure) // if form were submitted, but form action (add or update) failed
	{
		// load from request
		$formData = $_REQUEST['element'];

		if($id)
			$nameToDisplay = Helper::getNameToDisplay($id);

		// cleaning up empty external data
		if(is_array($formData['LOCATION']) && !empty($formData['LOCATION']))
		{
			foreach($formData['LOCATION'] as $lId => $external)
			{
				if(!intval($external['LOCATION_ID']))
					unset($formData['LOCATION'][$lId]);
			}
		}
	}
	else
	{
		if($id)
		{
			// load from database
			$formData = Helper::getFormData($id);
			$nameToDisplay = $formData['SITE_NAME'];
		}
		else
		{
			// blank page is not allowed here
			throw new Main\SystemException(Loc::getMessage('SALE_LOCATION_E_ITEM_NOT_FOUND'));
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
			"LINK" => Helper::getListUrl(),
			"ICON" => "btn_list",
		)
	));

	$tabControl = new CAdminForm("tabcntrl_location_default_edit", array(
		array(
			"DIV" => "main",
			"TAB" => Loc::getMessage('SALE_LOCATION_E_MAIN_TAB'),
			"TITLE" =>  Loc::getMessage('SALE_LOCATION_E_MAIN_TAB_TITLE')
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

$APPLICATION->SetTitle(strlen($nameToDisplay) ? Loc::getMessage('SALE_LOCATION_E_ITEM_EDIT', array('#ITEM_NAME#' => htmlspecialcharsbx($nameToDisplay))) : Loc::getMessage('SALE_LOCATION_E_ITEM_NEW'));

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

#####################################
#### Data output
#####################################

//temporal code
if(!CSaleLocation::locationProCheckEnabled())
	require($_SERVER['DOCUMENT_ROOT']."/bitrix/modules/main/include/epilog_admin.php");

SearchHelper::checkIndexesValid();

if($fatalFailure):
	CAdminMessage::ShowMessage(array('MESSAGE' => $fatalFailureMessage, 'type' => 'ERROR'));
else:

	if($actionFailure):
		CAdminMessage::ShowMessage(array('MESSAGE' => $actionFailureMessage, 'type' => 'ERROR'));
	endif;

	$topMenu->Show();

	$args = array();
	if($id)
		$args['id'] = $id;

	$tabControl->Begin(array(
		"FORM_ACTION" => Helper::getEditUrl($args) // generally, it is not safe to leave action empty
	));
	$tabControl->BeginNextFormTab();

	$tabControl->BeginCustomField('LOCATIONS', Loc::getMessage('SALE_LOCATION_E_HEADING_LOCATIONS'));?>

		<tr>
			<td>

				<?$randTag = rand(99, 999);?>
				<?$appearance = Helper::getWidgetAppearance();?>

				<input type="hidden" name="element[ID]" value="<?=$id?>" />
				<div id="ib_external_values_<?=$randTag?>">

					<table class="internal" style="margin: 0 auto; min-width: 600px;">
						<tbody class="bx-ui-dynamiclist-container">
							<tr class="heading">
								<td width="70%"><?=Loc::getMessage('SALE_LOCATION_E_HEADER_LOC_LOCATION')?></td>
								<td width="20%"><?=Loc::getMessage('SALE_LOCATION_E_HEADER_LOC_SORT')?></td>
								<td width="10%"><?=Loc::getMessage('SALE_LOCATION_E_HEADER_LOC_REMOVE')?></td>
							</tr>

							<?if(is_array($formData['LOCATION'])):?>

								<?$i = 0;?>
								<?foreach($formData['LOCATION'] as $location):?>
									<tr>
										<td>
											<?$APPLICATION->IncludeComponent("bitrix:sale.location.selector.".Helper::getWidgetAppearance(), "", array(
												"ID" => "",
												"CODE" => $location['LOCATION_CODE'],
												"INPUT_NAME" => "element[LOCATION][".$i."][LOCATION_CODE]",
												"PROVIDE_LINK_BY" => "code",
												"SHOW_ADMIN_CONTROLS" => 'Y',
												"SELECT_WHEN_SINGLE" => 'N',
												"FILTER_BY_SITE" => 'Y',
												"FILTER_SITE_ID" => $id,
												"SHOW_DEFAULT_LOCATIONS" => 'N',
												"SEARCH_BY_PRIMARY" => 'Y'
												),
												false
											);?>
										</td>
										<td>
											<input type="text" name="element[LOCATION][<?=$i?>][SORT]" value="<?=intval($location['SORT'])?>" size="7" />
										</td>

										<td style="text-align: center">
											<?if(strlen($location['LOCATION_CODE'])):?>
												<input type="checkbox" name="element[LOCATION][<?=$i?>][REMOVE]" value="1" <?=($location['REMOVE'] == 1 ? 'checked' : '')?> />
											<?endif?>
										</td>
									</tr>
									<?$i++;?>
								<?endforeach?>

							<?endif?>

							<script type="text/html" data-template-id="bx-ui-dynamiclist-row">
								<tr>
									<td></td>
									<td>
										<input type="text" name="element[LOCATION][{{column_id}}][SORT]" value="100" size="7" />
									</td>
									<td style="text-align: center"></td>
								</tr>
							</script>
						</tbody>
					</table>

					<div style="width: 100%; text-align: center; margin: 10px 0;">
						<input class="adm-btn-big bx-ui-dynamiclist-addmore" type="button" value="<?=Loc::getMessage('SALE_LOCATION_E_HEADER_LOC_MORE')?>" title="<?=Loc::getMessage('SALE_LOCATION_E_HEADER_LOC_MORE')?>">
					</div>

				</div>

				<div style="display: none">
					<?$APPLICATION->IncludeComponent("bitrix:sale.location.selector.".Helper::getWidgetAppearance(), "", array(
						"ID" => "",
						"CODE" => "",
						"INPUT_NAME" => "",
						"PROVIDE_LINK_BY" => "code",
						"SHOW_ADMIN_CONTROLS" => 'Y',
						"SELECT_WHEN_SINGLE" => 'N',
						"FILTER_BY_SITE" => 'Y',
						"FILTER_SITE_ID" => $id,
						"SHOW_DEFAULT_LOCATIONS" => 'N',
						"SEARCH_BY_PRIMARY" => 'Y',
						"JS_CONTROL_GLOBAL_ID" => 'defaultLocationSelector',
						"USE_JS_SPAWN" => 'Y'
						),
						false
					);?>
				</div>

				<script>
					new BX.ui.dynamicList({
						scope: 'ib_external_values_<?=$randTag?>',
						initiallyAdd: 3,
						startFrom: <?=intval($i)?>,
						bindEvents: {
							'after-row-built': function(row){

								var children = row.childNodes;
								for(var c in children){ // find first dom node (non-text node)
									if(BX.type.isElementNode(children[c])){

										var clone = window.BX.locationSelectors['defaultLocationSelector'].spawn(
											children[c],
											function(opts, node){
												node.childNodes[0].removeAttribute('id'); // drop scope id
											}
										);

										clone.setTargetInputName('element[LOCATION]['+this.vars.idOffset+'][LOCATION_CODE]');

										break;
									}
								}
							}
						}
					});
				</script>

			</td>
		</tr>
	<?$tabControl->EndCustomField('LOCATIONS', '');
	$tabControl->Buttons(array(
		"disabled" => !$userIsAdmin,
		"btnApply" => true,
		"btnCancel" => true,
		"back_url" => $returnUrl,
	));

	$tabControl->Show();
endif;
require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/epilog_admin.php");?>