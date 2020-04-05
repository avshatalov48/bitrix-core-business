<?
use Bitrix\Main;
use Bitrix\Main\Config;
use Bitrix\Main\Localization\Loc;

use Bitrix\Sale\Location\Admin\SiteLocationHelper as Helper;
use Bitrix\Sale\Location\Admin\SearchHelper;

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/include.php");
require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/sale/prolog.php');

Loc::loadMessages(__FILE__);

if($APPLICATION->GetGroupRight("sale") < "W")
	$APPLICATION->AuthForm(Loc::getMessage("SALE_MODULE_ACCES_DENIED"));

#####################################
#### Data prepare
#####################################

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

	$tabControl = new CAdminForm(
		"tabcntrl_zone_edit",
		array(
			array(
				"DIV" => "main",
				"TAB" => Loc::getMessage('SALE_LOCATION_E_MAIN_TAB'),
				"TITLE" =>  Loc::getMessage('SALE_LOCATION_E_MAIN_TAB_TITLE')
			)
		),
		true,
		true
	);
	$tabControl->SetShowSettings(false);
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
	if($id)
		$args['id'] = $id;

	$tabControl->Begin(array(
		"FORM_ACTION" => Helper::getEditUrl($args) // generally, it is not safe to leave action empty
	));
	$tabControl->BeginNextFormTab();
	?>

	<?$tabControl->BeginCustomField('LOCATIONS', Loc::getMessage('SALE_LOCATION_E_HEADING_LOCATIONS'));?>
		<tr>

			<tr class="heading">
				<td colspan="2">
					<?=GetMessage('SALE_LOCATION_E_FLD_LOCATIONS')?>
				</td>
			</tr>

			<td>
				<input type="hidden" name="element[ID]" value="<?=$id?>" />
				<?$APPLICATION->IncludeComponent("bitrix:sale.location.selector.system", "", array(
						"ENTITY_PRIMARY" => $id,
						"LINK_ENTITY_NAME" => "Bitrix\Sale\Location\SiteLocation",
						"INPUT_NAME" => 'element[LOC]',
						"SELECTED_IN_REQUEST" => array(
							'L' => isset($_REQUEST['element']['LOC']['L']) ? explode(':', $_REQUEST['element']['LOC']['L']) : false,
							'G' => isset($_REQUEST['element']['LOC']['G']) ? explode(':', $_REQUEST['element']['LOC']['G']) : false
						)
					),
					false
				);?>

			</td>
		</tr>
	<?$tabControl->EndCustomField('LOCATIONS', '');?>

	<?
	$tabControl->Buttons(array(
		"disabled" => !$userIsAdmin,
		"btnApply" => true,
		"btnCancel" => true,
		"back_url" => $returnUrl,
	));

	$tabControl->Show();
	?>

<?endif?>

<?require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/epilog_admin.php");?>