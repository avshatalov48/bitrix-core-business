<?
use Bitrix\Main;
use Bitrix\Main\Config;
use Bitrix\Main\Localization\Loc;

use Bitrix\Sale\Location\Admin\GroupHelper as Helper;
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

try
{
	$fatalFailure = false;

	#####################################
	#### ACTIONS
	#####################################

	$actionFailure = false;

	$id = intval($_REQUEST['id']) ? intval($_REQUEST['id']) : false;

	$copyId = intval($_REQUEST['copy_id']) ? intval($_REQUEST['copy_id']) : false;

	$actionSave = isset($_REQUEST['save']);
	$actionApply = isset($_REQUEST['apply']);
	$actionSaveAndAdd = isset($_REQUEST['save_and_add']);
	$formSubmitted = ($actionSave || $actionApply || $actionSaveAndAdd) && check_bitrix_sessid();

	$returnUrl = strlen($_REQUEST['return_url']) ? $_REQUEST['return_url'] : false;

	if($userIsAdmin && !empty($_REQUEST['element']) && $formSubmitted) // form submitted, handling it
	{
		$saveAsId = intval($_REQUEST['element']['ID']);

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
			else // new or copyed item
			{
				$res = Helper::add($_REQUEST['element']);
				if($res['success']) // on successfull add ...
				{
					if($actionSave)
						$redirectUrl = $returnUrl ? $returnUrl : Helper::getListUrl(); // go to the list page

					if($actionApply)
						$redirectUrl = $returnUrl ? $returnUrl : Helper::getEditUrl(array('id' => $res['id'])); // go to the page of just created item
				}
			}

			// no matter we updated or added a new item - we go to blank page on $actionSaveAndAdd
			if($res['success'] && $actionSaveAndAdd)
				$redirectUrl = Helper::getEditUrl(); // go to the blank page

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
		$returnUrl = Helper::getListUrl(); // default return page for "cancel" action

	// read data to display
	$readAsId = $id ? $id : $copyId;

	if($formSubmitted && $actionFailure) // if form were submitted, but form action (add or update) failed
	{
		// load from request
		$formData = $_REQUEST['element'];

		if($readAsId)
			$nameToDisplay = Helper::getNameToDisplay($readAsId);
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
			// load blank form
			$formData = array();
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
			"LINK" => Helper::getListUrl(array('id' => $parentId)),
			"ICON" => "btn_list",
		)
	));

	$tabControl = new CAdminForm("tabcntrl_location_group_edit", array(
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
	if(intval($_REQUEST['id']))
		$args['id'] = intval($_REQUEST['id']);

	$tabControl->Begin(array(
		"FORM_ACTION" => Helper::getEditUrl($args) // generally, it is not safe to leave action empty
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

			<tr<?=($field['required'] || $code == 'ID' ? $requiredFld : '')?>>
				<td width="40%"><?=$field['title']?>:</td>
				<td width="60%">

					<?if($code == 'ID'):?>

						<?=$id?>
						<input type="hidden" name="element[<?=$code?>]" value="<?=$id?>" />

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

	<?//todo: fix this?>
	<?//$tabControl->AddSection("LOCATION", GetMessage("SALE_LOCATION_E_HEADING_LOCATION"));?>

	<?$tabControl->BeginCustomField('LOCATION', Loc::getMessage('SALE_LOCATION_E_HEADING_LOCATION'));?>

		<tr class="heading">
			<td colspan="2"><?=Loc::getMessage('SALE_LOCATION_E_HEADING_LOCATION')?></td>
		</tr>

		<tr<?=($field['required'] || $code == 'ID' ? $requiredFld : '')?>>
			<td colspan="2">

				<?$APPLICATION->IncludeComponent("bitrix:sale.location.selector.system", "", array(
						"ENTITY_PRIMARY" => $id,
						"LINK_ENTITY_NAME" => "Bitrix\Sale\Location\GroupLocation",
						"INPUT_NAME" => 'element[LOC]',
						"SELECTED_IN_REQUEST" => array(
							'L' => isset($_REQUEST['element']['LOC']['L']) ? explode(':', $_REQUEST['element']['LOC']['L']) : false
						)
					),
					false
				);?>

			</td>
		</tr>

	<?$tabControl->EndCustomField('LOCATION', '');?>

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