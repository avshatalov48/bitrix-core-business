<?
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */
use Bitrix\Main\Loader;
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/catalog/prolog.php");

if (!($USER->CanDoOperation('catalog_read') || $USER->CanDoOperation('catalog_store')))
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
Loader::includeModule('catalog');
$bReadOnly = !$USER->CanDoOperation('catalog_store');

IncludeModuleLangFile(__FILE__);

$bExport = (isset($_REQUEST['mode']) && $_REQUEST['mode'] == 'excel');

$typeList = array(
	CONTRACTOR_INDIVIDUAL => GetMessage('CONTRACTOR_INDIVIDUAL'),
	CONTRACTOR_JURIDICAL => GetMessage('CONTRACTOR_JURIDICAL')
);

if ($ex = $APPLICATION->GetException())
{
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
	ShowError($ex->GetString());
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
	die();
}

$sTableID = "b_catalog_contractor";
$oSort = new CAdminSorting($sTableID, "ID", "ASC");
$lAdmin = new CAdminList($sTableID, $oSort);
$arFilterFields = array(
	"filter_contractor_type",
	"filter_person_name",
	"filter_company",
	"filter_phone",
	"filter_email",
	"filter_inn",
	"filter_kpp",
);
$lAdmin->InitFilter($arFilterFields);
$arFilter = array();

if (strlen($filter_contractor_type) > 0) $arFilter["PERSON_TYPE"] = $filter_contractor_type;
if (strlen($filter_person_name) > 0) $arFilter["%PERSON_NAME"] = $filter_person_name;
if (strlen($filter_company) > 0) $arFilter["%COMPANY"] = $filter_company;
if (strlen($filter_phone) > 0) $arFilter["PHONE"] = $filter_phone;
if (strlen($filter_email) > 0) $arFilter["EMAIL"] = $filter_email;
if (strlen($filter_inn) > 0) $arFilter["INN"] = $filter_inn;
if (strlen($filter_kpp) > 0) $arFilter["KPP"] = $filter_kpp;

if ($lAdmin->EditAction() && !$bReadOnly)
{
	foreach ($_POST['FIELDS'] as $ID => $arFields)
	{
		$ID = (int)$ID;
		if ($ID <= 0 || !$lAdmin->IsUpdated($ID))
			continue;

		$DB->StartTransaction();
		if (!CCatalogContractor::Update($ID, $arFields))
		{
			if ($ex = $APPLICATION->GetException())
				$lAdmin->AddUpdateError($ex->GetString(), $ID);
			else
				$lAdmin->AddUpdateError(GetMessage("ERROR_UPDATING_REC")." (".$arFields["ID"].", ".$arFields["TITLE"].", ".$arFields["SORT"].")", $ID);

			$DB->Rollback();
		}
		else
		{
			$DB->Commit();
		}
	}
}

if (($arID = $lAdmin->GroupAction()) && !$bReadOnly)
{
	if ($_REQUEST['action_target']=='selected')
	{
		$arID = array();
		$dbResultList = CCatalogContractor::GetList(array(), $arFilter, false, false, array('ID'));
		while ($arResult = $dbResultList->Fetch())
			$arID[] = $arResult['ID'];
	}

	foreach ($arID as $ID)
	{
		if (strlen($ID) <= 0)
			continue;

		switch ($_REQUEST['action'])
		{
			case "delete":
				@set_time_limit(0);
				$DB->StartTransaction();
				if (!CCatalogContractor::Delete($ID))
				{
					$DB->Rollback();

					if ($ex = $APPLICATION->GetException())
						$lAdmin->AddGroupError($ex->GetString(), $ID);
					else
						$lAdmin->AddGroupError(GetMessage("ERROR_DELETING_TYPE"), $ID);
				}
				else
				{
					$DB->Commit();
				}
				break;
		}
	}
}

$arSelect = array(
	"ID",
	"PERSON_TYPE",
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

$arNavParams = (
	isset($_REQUEST['mode']) && $_REQUEST['mode'] == 'excel'
	? false
	: array("nPageSize" => CAdminResult::GetNavSize($sTableID))
);
if (!isset($by))
	$by = 'ID';
if (!isset($order))
	$order = 'ASC';

$dbResultList = CCatalogContractor::GetList(
	array($by => $order),
	$arFilter,
	false,
	$arNavParams,
	$arSelect
);

$dbResultList = new CAdminResult($dbResultList, $sTableID);
$dbResultList->NavStart();
$lAdmin->NavText($dbResultList->GetNavPrint(GetMessage("group_admin_nav")));

$arHeaders = array(
	array("id" => "ID", "content" => "ID", "sort" => "ID", "default" => true),
	array('id' => 'PERSON_TYPE', 'content' => GetMessage('CONTRACTOR_TYPE'), 'sort' => 'PERSON_TYPE', 'default' => true),
	array('id' => 'PERSON_NAME', 'content' => GetMessage('CONTRACTOR_PERSON_TITLE'), 'sort' => 'PERSON_NAME', 'default' => true),
	array("id" => "COMPANY", "content" => GetMessage("CONTRACTOR_COMPANY"),  "sort" => "COMPANY", "default" => true),
	array("id" => "EMAIL", "content" => GetMessage("CONTRACTOR_EMAIL"),  "sort" => "EMAIL", "default" => true),
	array("id" => "PHONE", "content" => GetMessage("CONTRACTOR_PHONE"),  "sort" => "PHONE", "default" => false),
	array("id" => "POST_INDEX", "content" => GetMessage("CONTRACTOR_POST_INDEX"),  "sort" => "POST_INDEX", "default" => false),
	array("id" => "INN", "content" => GetMessage("CONTRACTOR_INN"),  "sort" => "INN", "default" => false),
);
if(trim(GetMessage("CONTRACTOR_KPP")) != '')
	$arHeaders[] = array("id" => "KPP", "content" => GetMessage("CONTRACTOR_KPP"),  "sort" => "KPP", "default" => false);

$arHeaders[] = 	array("id" => "ADDRESS", "content" => GetMessage("CONTRACTOR_ADDRESS"),  "sort" => "ADDRESS", "default" => true);

$lAdmin->AddHeaders($arHeaders);

$arVisibleColumns = $lAdmin->GetVisibleHeaderColumns();
while ($arResultContractor = $dbResultList->Fetch())
{
	$row =& $lAdmin->AddRow($arResultContractor['ID'], $arResultContractor);
	$row->AddField('ID', $arResultContractor['ID']);
	$row->AddViewField('PERSON_TYPE', $typeList[$arResultContractor['PERSON_TYPE']]);
	$row->AddInputField('PERSON_NAME', false);
	$row->AddInputField('COMPANY', false);
	if($bReadOnly)
	{
		$row->AddInputField('EMAIL', false);
		$row->AddInputField('PHONE', false);
		$row->AddInputField('ADDRESS', false);
	}
	else
	{
		$row->AddInputField('EMAIL', array('size' => 30));
		$row->AddInputField('PHONE', array('size' => 25));
		$row->AddInputField('ADDRESS', array('size' => 40));
	}

	$arActions = array();
	$arActions[] = array(
		"ICON" => "edit",
		"TEXT" => GetMessage("EDIT_CONTRACTOR_ALT"),
		"ACTION" => $lAdmin->ActionRedirect("cat_contractor_edit.php?ID=".$arResultContractor['ID']."&lang=".LANGUAGE_ID."&".GetFilterParams("filter_").""),
		"DEFAULT" => true
	);

	if (!$bReadOnly)
	{
		$arActions[] = array("SEPARATOR" => true);
		$arActions[] = array(
			"ICON" => "delete",
			"TEXT" => GetMessage("DELETE_CONTRACTOR_ALT"),
			"ACTION" => "if(confirm('".GetMessage('DELETE_CONTRACTOR_CONFIRM')."')) ".$lAdmin->ActionDoGroup($arResultContractor['ID'], "delete")
		);
	}

	$row->AddActions($arActions);
}

$lAdmin->AddFooter(
	array(
		array(
			"title" => GetMessage("MAIN_ADMIN_LIST_SELECTED"),
			"value" => $dbResultList->SelectedRowsCount()
		),
		array(
			"counter" => true,
			"title" => GetMessage("MAIN_ADMIN_LIST_CHECKED"),
			"value" => "0"
		),
	)
);

if(!$bReadOnly)
{
	$lAdmin->AddGroupActionTable(
		array(
			"delete" => GetMessage("MAIN_ADMIN_LIST_DELETE"),
		)
	);
}

if (!$bReadOnly)
{
	$aContext = array(
		array(
			"TEXT" => GetMessage("CONTRACTOR_ADD_NEW"),
			"ICON" => "btn_new",
			"LINK" => "cat_contractor_edit.php?lang=".LANG,
			"TITLE" => GetMessage("CONTRACTOR_ADD_NEW_ALT")
		),
	);
	$lAdmin->AddAdminContextMenu($aContext);
}

$lAdmin->CheckListMode();

$APPLICATION->SetTitle(GetMessage("CONTRACTOR_PAGE_TITLE"));
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
?>
	<form name="find_form" method="GET" action="<?echo $APPLICATION->GetCurPage()?>?">
		<?
		$arFilterPopup = array(
			GetMessage("CONTRACTOR_TYPE"),
			GetMessage("CONTRACTOR_PERSON_NAME"),
			GetMessage("CONTRACTOR_COMPANY"),
			GetMessage("CONTRACTOR_EMAIL"),
			GetMessage("CONTRACTOR_PHONE"),
			GetMessage("CONTRACTOR_INN"),
		);
		if(trim(GetMessage("CONTRACTOR_KPP")) != '')
			$arFilterPopup[] = GetMessage("CONTRACTOR_KPP");

		$oFilter = new CAdminFilter($sTableID."_filter", $arFilterPopup);

		$oFilter->Begin();
		?>
		<tr>
			<td><? echo GetMessage("CONTRACTOR_TYPE") ?>:</td>
			<td>
				<select name="filter_contractor_type">
					<option value=""><?=htmlspecialcharsbx(GetMessage("CONTRACTOR_FIELD_EMPTY")); ?></option>
					<?
					foreach ($typeList as $typeId => $typeTitle)
					{
						?><option value="<? echo $typeId; ?>"<?if($filter_contractor_type == $typeId) echo " selected"?>><?=htmlspecialcharsbx($typeTitle); ?></option><?
					}
					?>
				</select>
			</td>
		</tr>
		<tr>
			<td><?= GetMessage("CONTRACTOR_PERSON_NAME") ?>:</td>
			<td>
				<input type="text" name="filter_person_name" value="<?echo htmlspecialcharsbx($filter_person_name)?>">
			</td>
		</tr>
		<tr>
			<td><?= GetMessage("CONTRACTOR_COMPANY") ?>:</td>
			<td>
				<input type="text" name="filter_company" value="<?echo htmlspecialcharsbx($filter_company)?>">
			</td>
		</tr>
		<tr>
			<td><? echo GetMessage("CONTRACTOR_EMAIL") ?>:</td>
			<td>
				<input type="text" name="filter_email" value="<?echo htmlspecialcharsbx($filter_email)?>" />
			</td>
		</tr>
		<tr>
			<td><? echo GetMessage("CONTRACTOR_PHONE") ?>:</td>
			<td>
				<input type="text" name="filter_phone" value="<?echo htmlspecialcharsbx($filter_phone)?>" />
			</td>
		</tr>
		<tr>
			<td><? echo GetMessage("CONTRACTOR_INN") ?>:</td>
			<td>
				<input type="text" name="filter_inn" value="<?echo htmlspecialcharsbx($filter_inn)?>" />
			</td>
		</tr>
		<?if(trim(GetMessage("CONTRACTOR_KPP")) != ''):?>
		<tr>
			<td><? echo GetMessage("CONTRACTOR_KPP") ?>:</td>
			<td>
				<input type="text" name="filter_kpp" value="<?echo htmlspecialcharsbx($filter_kpp)?>" />
			</td>
		</tr>
		<?endif;

		$oFilter->Buttons(
			array(
				"table_id" => $sTableID,
				"url" => $APPLICATION->GetCurPage(),
				"form" => "find_form"
			)
		);
		$oFilter->End();
		?>
	</form>
<?
$lAdmin->DisplayList();

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");