<?
use \Bitrix\Sale\Internals\CompanyTable;
use \Bitrix\Main\Application;
use Bitrix\Main\Localization\Loc;

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

\Bitrix\Main\Loader::includeModule('sale');

$saleModulePermissions = $APPLICATION->GetGroupRight("sale");
if ($saleModulePermissions < "U")
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

IncludeModuleLangFile(__FILE__);
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/prolog.php");

global $USER_FIELD_MANAGER, $USER;
$conn = \Bitrix\Main\Application::getConnection();
$lang = \Bitrix\Main\Application::getInstance()->getContext()->getLanguage();
$request = Application::getInstance()->getContext()->getRequest();

$sTableID = "tbl_sale_company";

$oSort = new CAdminSorting($sTableID, "ID", "asc");
$lAdmin = new CAdminList($sTableID, $oSort);

$filterFields = array(
	"filter_name",
	"filter_location_id",
	"filter_active"
);
$USER_FIELD_MANAGER->AdminListAddFilterFields($entity_id, $filterFields);

$lAdmin->InitFilter($filterFields);
$filter = array();

if ($filter_name <> '')
	$filter["?NAME"] = $filter_name;

if ($filter_location_id <> '')
	$filter["LOCATION_ID"] = $filter_location_id;

if ($filter_active <> '' && $filter_active != 'NOT_REF')
	$filter["ACTIVE"] = $filter_active;

$USER_FIELD_MANAGER->AdminListAddFilter(CompanyTable::getUfId(), $filter);

if ($lAdmin->EditAction() && $saleModulePermissions >= 'W')
{
	foreach ($request->getPost('FIELDS') as $id => $arFields)
	{
		$error = false;
		$id = intval($id);

		if ($id <= 0 || !$lAdmin->IsUpdated($id))
			continue;

		$reqFields = array('NAME'); // , 'LOCATION_ID'
		foreach ($reqFields as $reqField)
		{
			if (empty($arFields[$reqField]))
			{
				$error = true;
				$lAdmin->AddUpdateError('#'.$id.' : '.Loc::getMessage('SALE_COMPANY_ERROR_NO_'.$reqField), $id);
			}
		}

		if (!$error)
		{
			$arFields['DATE_MODIFY'] = new \Bitrix\Main\Type\DateTime();
			$arFields['MODIFIED_BY'] = $USER->GetID();

			$conn->startTransaction();
			$res = CompanyTable::update($id, $arFields);
			if (!$res->isSuccess())
			{
				$conn->rollbackTransaction();
				$lAdmin->AddUpdateError(join("\n", $res->getErrorMessages()), $id);
				continue;
			}
			$conn->commitTransaction();
		}
	}
}

if (($ids = $lAdmin->GroupAction()) && $saleModulePermissions >= "W")
{
	if ($_REQUEST['action_target'] == 'selected')
	{
		$ids = array();
		$params = array(
			'select' => array('ID'),
			'filter' => $filter
		);
		$dbResultList = CompanyTable::getList($params);

		while ($result = $dbResultList->Fetch())
			$ids[] = $result['ID'];
	}

	foreach ($ids as $id)
	{
		if (empty($id))
			continue;

		switch ($_REQUEST['action'])
		{
			case "delete":
				@set_time_limit(0);

				$dbRes = \Bitrix\Sale\Internals\OrderTable::getList(array(
					'select' => array('ID'),
					'filter' => array(
						'LOGIC' => 'OR',
						'SHIPMENT.COMPANY_ID' => $id,
						'PAYMENT.COMPANY_ID' => $id
					)
				));

				if ($dbRes->fetch())
				{
					$lAdmin->AddGroupError(Loc::getMessage("SALE_COMPANY_ERROR_DELETE_LINK"), $id);
					continue 2;
				}

				$result = CompanyTable::delete($id);
				if (!$result->isSuccess())
				{
					if ($error = $result->getErrorMessages())
						$lAdmin->AddGroupError(join("\n", $error), $id);
					else
						$lAdmin->AddGroupError(Loc::getMessage("SALE_COMPANY_ERROR_DELETE"), $id);
				}
				break;
		}
	}
}

$fields = $USER_FIELD_MANAGER->GetUserFields(CompanyTable::getUfId());
$select = array('*');
foreach ($fields as $field)
	$select[] = $field['FIELD_NAME'];

$params = array(
	'select' => $select,
	'filter' => $filter,
	'order'  => array($by => $order)
);

$company = CompanyTable::getList($params);
$dbResultList = new CAdminResult($company, $sTableID);
$dbResultList->NavStart();

$lAdmin->NavText($dbResultList->GetNavPrint(Loc::getMessage("SALE_COMPANY")));

$headers = array(
	array(
		"id"=>"ID",
		"content"=>"ID",
		"sort"=>"ID",
		"default"=>true
	),
	array(
		"id"=>"ACTIVE",
		"content"=>Loc::getMessage("SALE_COMPANY_ACTIVE"),
		"sort"=>"ACTIVE",
		"default"=>true
	),
	array(
		"id"=>"NAME",
		"content"=>Loc::getMessage("SALE_COMPANY_NAME"),
		"sort"=>"NAME",
		"default"=>true
	),
	array(
		"id"=>"LOCATION_ID",
		"content"=>Loc::getMessage("SALE_COMPANY_LOCATION_ID"),
		"sort"=>"LOCATION_ID",
		"default"=>true
	),
	array(
		"id"=>"CODE",
		"content"=>Loc::getMessage("SALE_COMPANY_CODE"),
		"sort"=>"CODE",
		"default"=>true
	),
	array(
		"id"=>"SORT",
		"content"=>Loc::getMessage("SALE_COMPANY_SORT"),
		"sort"=>"SORT",
		"default"=>true
	)
);
$USER_FIELD_MANAGER->AdminListAddHeaders(CompanyTable::getUfId(), $headers);
$lAdmin->AddHeaders($headers);

$allSelectedFields = array(
	"ID" => false,
	"ACTIVE" => false,
	"NAME" => false,
	"LOCATION_ID" => false,
	"CODE" => false
);

$selectedFields = $lAdmin->GetVisibleHeaderColumns();
$allSelectedFields = array_merge($allSelectedFields, array_fill_keys($selectedFields, true));

while ($company = $dbResultList->NavNext(true, "f_"))
{
	try
	{
		$res = \Bitrix\Sale\Location\LocationTable::getPathToNodeByCode(
				$company['LOCATION_ID'],
				array(
						'select' => array('CHAIN' => 'NAME.NAME'),
						'filter' => array('NAME.LANGUAGE_ID' => Application::getInstance()->getContext()->getLanguage())
				)
		);

		$path = array();
		while ($item = $res->fetch())
			$path[] = $item['CHAIN'];

		$company['LOCATION_ID'] = implode(', ', array_reverse($path));
	}
	catch (\Bitrix\Main\SystemException $e)
	{
		$company['LOCATION_ID'] = '';
	}

	$row = &$lAdmin->AddRow($f_ID, $company, "sale_company_edit.php?ID=".$f_ID."&lang=".$lang, Loc::getMessage("SALE_COMPANY_EDIT_DESCR"));

	$row->AddField("ID", "<a href=\"sale_company_edit.php?ID=".$f_ID."&lang=".$lang.GetFilterParams("filter_")."\">".$f_ID."</a>");
	$row->AddCheckField("ACTIVE");
	$row->AddInputField("NAME");
	$row->AddField("LOCATION_ID", $company['LOCATION_ID']);
	$row->AddInputField("CODE");

	$USER_FIELD_MANAGER->AddUserFields(CompanyTable::getUfId(), $company, $row);

	$arActions = array(
		array(
			"ICON" => "edit",
			"TEXT" => Loc::getMessage("SALE_COMPANY_EDIT"),
			"TITLE" => Loc::getMessage("SALE_COMPANY_EDIT_DESCR"),
			"ACTION" => $lAdmin->ActionRedirect("sale_company_edit.php?ID=".$f_ID."&lang=".$lang),
			"DEFAULT" => true,
		),
	);
	if ($saleModulePermissions >= "W")
	{
		$arActions[] = array("SEPARATOR" => true);
		$arActions[] = array(
			"ICON" => "delete",
			"TEXT" => Loc::getMessage("SALE_COMPANY_DELETE"),
			"TITLE" => Loc::getMessage("SALE_COMPANY_DELETE_DESCR"),
			"ACTION" => "if(confirm('".Loc::getMessage('SALE_COMPANY_CONFIRM_DEL')."')) ".$lAdmin->ActionDoGroup($f_ID, "delete"),
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

if ($saleModulePermissions == "W")
{
	$lAdmin->AddGroupActionTable(
		array(
			"delete" => GetMessage("MAIN_ADMIN_LIST_DELETE"),
		)
	);

	$lAdmin->AddAdminContextMenu(array(
		array(
			"TEXT" => Loc::getMessage("SALE_COMPANY_ADD_NEW"),
			"TITLE" => Loc::getMessage("SALE_COMPANY_ADD_NEW_ALT"),
			"LINK" => "sale_company_edit.php?lang=" . $lang,
			"ICON" => "btn_new"
		),
	));
}

$lAdmin->CheckListMode();

$APPLICATION->SetTitle(Loc::getMessage("SALE_COMPANY_SECTION_TITLE"));
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
?>

<form name="find_form" method="GET" action="<?echo $APPLICATION->GetCurPage()?>?">
<?
$arFindFields = array(Loc::getMessage("SALE_COMPANY_F_PERSON_TYPE"));
$USER_FIELD_MANAGER->AddFindFields(CompanyTable::getUfId(), $arFindFields);
$oFilter = new CAdminFilter(
	$sTableID."_filter",
	$arFindFields
);

$oFilter->Begin();
?>
	<style type="text/css">
		.adm-filter-content {
			overflow: visible;
		}

		.adm-filter-item-center {
			overflow: visible;
		}
	</style>
	<tr>
		<td><?=Loc::getMessage("SALE_COMPANY_NAME");?>:</td>
		<td>
			<input type="text" name="filter_name" value="<?echo htmlspecialcharsbx($filter_name)?>" />
		</td>
	</tr>
	<tr>
		<td><?=Loc::getMessage("SALE_COMPANY_ACTIVE")?>:</td>
		<td>
			<select name="filter_active">
				<option value="NOT_REF">(<?=Loc::getMessage("SALE_COMPANY_ALL");?>)</option>
				<option value="Y"<?if ($filter_active=="Y") echo " selected"?>><?=Loc::getMessage("SALE_COMPANY_YES");?></option>
				<option value="N"<?if ($filter_active=="N") echo " selected"?>><?=Loc::getMessage("SALE_COMPANY_NO");?></option>
			</select>
		</td>
	</tr>
	<tr>
		<td><?=Loc::getMessage('SALE_COMPANY_LOCATION');?>:</td>
		<td>
			<div style="width: 100%; margin-left: 12px">
				<?$APPLICATION->IncludeComponent("bitrix:sale.location.selector.search"/*.\Bitrix\Sale\Location\Admin\LocationHelper::getWidgetAppearance()*/, "", array(
						"ID" => "",
						"CODE" => $fields['LOCATION_ID'],
						"INPUT_NAME" => "filter_location_id",
						"PROVIDE_LINK_BY" => "code",
						"SHOW_ADMIN_CONTROLS" => 'Y',
						"SELECT_WHEN_SINGLE" => 'N',
						"FILTER_BY_SITE" => 'N',
						"FILTER_SITE_ID" => '',
						"SHOW_DEFAULT_LOCATIONS" => 'N',
						"SEARCH_BY_PRIMARY" => 'Y',

						"INITIALIZE_BY_GLOBAL_EVENT" => 'onAdminFilterInited', // this allows js logic to be initialized after admin filter
						"GLOBAL_EVENT_SCOPE" => 'window'
					),
					false
				);?>
			</div>
		</td>
	</tr>
	<?
$USER_FIELD_MANAGER->AdminListShowFilter(CompanyTable::getUfId());
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

?>