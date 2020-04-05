<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/catalog/prolog.php");
global $APPLICATION;
global $DB;
global $USER;

if (!($USER->CanDoOperation('catalog_read') || $USER->CanDoOperation('catalog_store')))
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
CModule::IncludeModule("catalog");
$bReadOnly = !$USER->CanDoOperation('catalog_store');

if ($ex = $APPLICATION->GetException())
{
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

	$strError = $ex->GetString();
	ShowError($strError);

	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
	die();
}

IncludeModuleLangFile(__FILE__);
ClearVars();

$errorMessage = "";
$bVarsFromForm = false;
$ID = (isset($_REQUEST["ID"]) ? (int)$_REQUEST["ID"] : 0);
$typeReadOnly = false;
$userId = (int)$USER->GetID();

if ($_SERVER["REQUEST_METHOD"] == "POST" && check_bitrix_sessid() && strlen($_REQUEST["Update"]) > 0 && !$bReadOnly)
{
	if($PERSON_TYPE == CONTRACTOR_INDIVIDUAL)
		$INN = $KPP = $COMPANY = '';
	$PERSON_NAME = ($_REQUEST["PERSON_NAME"] == GetMessage("CONTRACTOR_NAME")) ? '' : $_REQUEST["PERSON_NAME"];
	$PERSON_LASTNAME = ($_REQUEST["PERSON_LASTNAME"] == GetMessage("CONTRACTOR_LAST_NAME")) ? '' : $_REQUEST["PERSON_LASTNAME"];
	$PERSON_MIDDLENAME = ($_REQUEST["PERSON_MIDDLENAME"] == GetMessage("CONTRACTOR_SECOND_NAME")) ? '' : $_REQUEST["PERSON_MIDDLENAME"];
	$arFields = Array(
		"PERSON_TYPE" => $PERSON_TYPE,
		"SITE_ID" => SITE_ID,
		"PERSON_NAME" => $PERSON_NAME,
		"PERSON_LASTNAME" => $PERSON_LASTNAME,
		"PERSON_MIDDLENAME" => $PERSON_MIDDLENAME,
		"EMAIL" => $_REQUEST["EMAIL"],
		"PHONE" => $_REQUEST["PHONE"],
		"POST_INDEX" => $_REQUEST["POST_INDEX"],
		"COUNTRY" => $_REQUEST["COUNTRY"],
		"CITY" => $XML_ID,
		"INN" => $INN,
		"KPP" => $KPP,
		"COMPANY" => $COMPANY,
		"ADDRESS" => $ADDRESS,
		"CREATED_BY" => $userId,
		"MODIFIED_BY" => $userId,
	);
	$DB->StartTransaction();
	if (strlen($errorMessage) == 0 && $ID > 0 && $res = CCatalogContractor::update($ID, $arFields))
	{
		$ID = $res;
		$DB->Commit();

		if (strlen($_REQUEST["apply"])<=0)
			LocalRedirect("/bitrix/admin/cat_contractor_list.php?lang=".LANG."&".GetFilterParams("filter_", false));
		else
			LocalRedirect("/bitrix/admin/cat_contractor_edit.php?lang=".LANG."&ID=".$ID."&".GetFilterParams("filter_", false));
	}
	elseif (strlen($errorMessage) == 0 && $ID == 0 && $res = CCatalogContractor::Add($arFields))
	{
		$ID = $res;
		$DB->Commit();
		if (strlen($_REQUEST["apply"])<=0)
			LocalRedirect("/bitrix/admin/cat_contractor_list.php?lang=".LANG."&".GetFilterParams("filter_", false));
		else
			LocalRedirect("/bitrix/admin/cat_contractor_edit.php?lang=".LANG."&ID=".$ID."&".GetFilterParams("filter_", false));
	}
	else
	{
		$bVarsFromForm = true;
		$errorMessage = $APPLICATION->GetException()->GetString();
		$DB->Rollback();
	}
}

if ($ID > 0)
	$APPLICATION->SetTitle(str_replace("#ID#", $ID, GetMessage("CONTRACTOR_TITLE_UPDATE")));
else
	$APPLICATION->SetTitle(GetMessage("CONTRACTOR_TITLE_ADD"));

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
$str_ACTIVE = "Y";

if ($ID > 0)
{
	$arSelect = array(
		"ID",
		"PERSON_TYPE",
		"SITE_ID",
		"PERSON_NAME",
		"EMAIL",
		"PHONE",
		"POST_INDEX",
		"COUNTRY",
		"CITY",
		"COMPANY",
		"INN",
		"KPP",
		"ADDRESS",
	);

	$dbResult = CCatalogContractor::GetList(array(),array('ID' => $ID),false,false,$arSelect);
	if (!$dbResult->ExtractFields("str_"))
		$ID = 0;
}

if ($bVarsFromForm)
	$DB->InitTableVarsForEdit("b_catalog_contractor", "", "str_");

if(isset($str_ADDRESS))
	$str_ADDRESS = (trim($str_ADDRESS) != '') ? $str_ADDRESS : '';

$str_PERSON_TYPE = (isset($str_PERSON_TYPE)) ? $str_PERSON_TYPE : CONTRACTOR_INDIVIDUAL;

$aMenu = array(
	array(
		"TEXT" => GetMessage("CONTRACTOR_LIST"),
		"ICON" => "btn_list",
		"LINK" => "/bitrix/admin/cat_contractor_list.php?lang=".LANG."&".GetFilterParams("filter_", false)
	)
);

if ($ID > 0 && !$bReadOnly)
{
	$aMenu[] = array("SEPARATOR" => "Y");

	$aMenu[] = array(
		"TEXT" => GetMessage("CONTRACTOR_NEW"),
		"ICON" => "btn_new",
		"LINK" => "/bitrix/admin/cat_contractor_edit.php?lang=".LANG."&".GetFilterParams("filter_", false)
	);

	$aMenu[] = array(
		"TEXT" => GetMessage("CONTRACTOR_DELETE"),
		"ICON" => "btn_delete",
		"LINK" => "javascript:if(confirm('".GetMessage("CONTRACTOR_DELETE_CONFIRM")."')) window.location='/bitrix/admin/cat_contractor_list.php?action=delete&ID[]=".$ID."&lang=".LANG."&".bitrix_sessid_get()."#tb';",
		"WARNING" => "Y"
	);
}
$context = new CAdminContextMenu($aMenu);
$context->Show();
?>

<?CAdminMessage::ShowMessage($errorMessage);?>

	<script>
		function fContractorChangeType(el)
		{
			var type = el.value;
			var companyName = document.getElementById('company-name-tr');
			var companyInn = document.getElementById('company-inn-tr');
			var companyKpp = document.getElementById('company-kpp-tr');
			var titleContractor = document.getElementById('title_span');
			var addressContractor = document.getElementById('address_span');
			if(type == 1)
			{
				companyName.style.display = 'none';
				companyInn.style.display = 'none';
				companyKpp.style.display = 'none';
				titleContractor.innerHTML = "<?=GetMessage("CONTRACTOR_TITLE");?>:";
				addressContractor.innerHTML = "<?= GetMessage("CONTRACTOR_ADDRESS")?>:";
			}
			else if(type == 2)
			{
				companyName.style.display = 'table-row';
				companyInn.style.display = 'table-row';
				companyKpp.style.display = 'table-row';
				titleContractor.innerHTML = "<?=GetMessage("CONTRACTOR_TITLE_JURIDICAL");?>:";
				addressContractor.innerHTML = "<?= GetMessage("CONTRACTOR_ADDRESS_JURIDICAL") ?>:";
			}
		}

	</script>

	<form enctype="multipart/form-data" method="POST" action="<?echo $APPLICATION->GetCurPage()?>?" name="contractor_edit">
		<?echo GetFilterHiddens("filter_");?>
		<input type="hidden" name="Update" value="Y">
		<input type="hidden" name="lang" value="<?echo LANG ?>">
		<input type="hidden" name="ID" value="<?echo $ID ?>">
		<?=bitrix_sessid_post()?>

		<?
		$aTabs = array(
			array("DIV" => "edit1", "TAB" => GetMessage("CONTRACTOR_TAB"), "ICON" => "catalog", "TITLE" => GetMessage("CONTRACTOR_TAB_DESCR")),
		);

		$tabControl = new CAdminTabControl("tabControl", $aTabs);
		$tabControl->Begin();
		?>

		<?
		$tabControl->BeginNextTab();
		?>
		<style>
			.fio.newo_break_active input {
				color: #CCC;
			}
		</style>
		<?if ($ID > 0):
			$typeReadOnly = true;
			?>

			<tr>
				<td>ID:</td>
				<td><?= $ID ?></td>
			</tr>
		<?endif;?>

		<tr class="adm-detail-required-field">
			<td width="40%"><?= GetMessage("CONTRACTOR_TYPE") ?>:</td>
			<td width="60%">
				<input type="hidden" name="PERSON_TYPE" value="<?=$str_PERSON_TYPE?>">
				<select <?if($typeReadOnly) echo " disabled";?> name="PERSON_TYPE" onchange="fContractorChangeType(this);">
					<option <? if(intval($str_PERSON_TYPE) == CONTRACTOR_INDIVIDUAL) echo" selected";?> value="1"><?= GetMessage("CONTRACTOR_INDIVIDUAL") ?></option>
					<option <? if(intval($str_PERSON_TYPE) == CONTRACTOR_JURIDICAL) echo" selected";?> value="2"><?= GetMessage("CONTRACTOR_JURIDICAL") ?></option>
				</select>
			</td>
		</tr>

		<tr class="adm-detail-required-field" id="company-name-tr" <? if($str_PERSON_TYPE == 1) echo "style=\"display: none\"";?>>
			<td width="40%"><?= GetMessage("CONTRACTOR_COMPANY") ?>:</td>
			<td width="60%">
				<input type="text" name="COMPANY" value="<?=$str_COMPANY?>" size="30" />
			</td>

		</tr>
		<tr id="company-inn-tr"<? if($str_PERSON_TYPE == CONTRACTOR_INDIVIDUAL) echo "style=\"display: none\"";?>>
			<td><?= GetMessage("CONTRACTOR_INN") ?>:</td>
			<td>
				<input type="text" name="INN" value="<?=$str_INN?>" size="30" />
			</td>
		</tr>
		<?if(trim(GetMessage("CONTRACTOR_KPP")) != ''):?>
			<tr id="company-kpp-tr" <? if($str_PERSON_TYPE == CONTRACTOR_INDIVIDUAL) echo "style=\"display: none\"";?>>
				<td><?= GetMessage("CONTRACTOR_KPP") ?>:</td>
				<td>
					<input type="text" name="KPP" value="<?=$str_KPP?>" size="30" />
				</td>
			</tr>
		<?endif;?>
		<tr class="adm-detail-required-field">
			<td> <span id="title_span">
			<?
					if($str_PERSON_TYPE == CONTRACTOR_JURIDICAL)
						echo GetMessage("CONTRACTOR_TITLE_JURIDICAL");
					else
						echo GetMessage("CONTRACTOR_TITLE");
					?>:</span></td>
			<td>
				<input type="text" name="PERSON_NAME" id="BREAK_LAST_NAME" size="50" value="<?=$str_PERSON_NAME?>" />
			</td>
		</tr>
		<tr>
			<td><?= GetMessage("CONTRACTOR_PHONE") ?>:</td>
			<td>
				<input type="text" name="PHONE" value="<?=$str_PHONE?>" size="30" />
			</td>
		</tr>
		<tr>
			<td><?= GetMessage("CONTRACTOR_EMAIL") ?>:</td>
			<td>
				<input type="text" name="EMAIL" value="<?=$str_EMAIL?>" size="30" />
			</td>
		</tr>
		<tr>
			<td><?= GetMessage("CONTRACTOR_POSTINDEX") ?>:</td>
			<td><input type="text" name="POST_INDEX" value="<?=$str_POST_INDEX?>" size="30" />
			</td>
		</tr>
		<tr>
			<td  class="adm-detail-valign-top"><span id="address_span"><? 	if($str_PERSON_TYPE == CONTRACTOR_JURIDICAL) echo GetMessage("CONTRACTOR_ADDRESS_JURIDICAL"); else echo GetMessage("CONTRACTOR_ADDRESS"); ?>:</span></td>
			<td>
				<textarea cols="35" rows="3" class="typearea" name="ADDRESS" wrap="virtual"><?= $str_ADDRESS ?></textarea>
			</td>
		</tr>

		</tr>

		<?echo
		$tabControl->EndTab();

		$tabControl->Buttons(
			array(
				"disabled" => $bReadOnly,
				"back_url" => "/bitrix/admin/cat_contractor_list.php?lang=".LANG."&".GetFilterParams("filter_", false)
			)
		);
		$tabControl->End();
		?>
	</form>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");?>