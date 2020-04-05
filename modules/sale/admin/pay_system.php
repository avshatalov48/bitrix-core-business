<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/include.php");

use Bitrix\Main\Localization\Loc;

$saleModulePermissions = $APPLICATION->GetGroupRight("sale");
if ($saleModulePermissions < "W")
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

IncludeModuleLangFile(__FILE__);
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/prolog.php");

$sTableID = "tbl_sale_pay_system";
$instance = \Bitrix\Main\Application::getInstance();
$context = $instance->getContext();
$request = $context->getRequest();

$oSort = new CAdminSorting($sTableID, "ID", "asc");
$lAdmin = new CAdminList($sTableID, $oSort);

$arFilterFields = array(
	"filter_active",
	"filter_person_type",
);

$lAdmin->InitFilter($arFilterFields);

$filter = array();

if (strlen($filter_active) > 0 && $filter_active != "NOT_REF")
	$filter["ACTIVE"] = trim($filter_active);

if (empty($filter_person_type) || in_array("NOT_REF", $filter_person_type))
	$filter_person_type = array();

if (($ids = $lAdmin->GroupAction()) && $saleModulePermissions >= "W")
{
	if ($request->get('action_target')=='selected')
	{
		$ids = array();
		$dbRes = \Bitrix\Sale\Internals\PaySystemActionTable::getList(
			array(
				'select' => array('ID'),
				'filter' => $filter,
				'order' => array(ToUpper($by) => ToUpper($order))
			)
		);

		while ($arResult = $dbRes->fetch())
			$ids[] = $arResult['ID'];
	}

	foreach ($ids as $id)
	{
		if ((int)$id <= 0)
			continue;

		switch ($_REQUEST['action'])
		{
			case "delete":
				$dbRes = \Bitrix\Sale\Internals\PaymentTable::getList(array('filter' => array('PAY_SYSTEM_ID' => $id)));
				if ($dbRes->fetch())
				{
					$lAdmin->AddGroupError(Loc::getMessage("SALE_DELETE_ERROR"), $id);
					continue;
				}

				$result = \Bitrix\Sale\PaySystem\Manager::delete($id);
				if (!$result->isSuccess())
				{
					if ($result->getErrorMessages())
						$lAdmin->AddGroupError(join(', ', $result->getErrorMessages()), $id);
					else
						$lAdmin->AddGroupError(GetMessage("SPSAN_ERROR_DELETE"), $id);
				}

				break;

			case "activate":
			case "deactivate":

				$arFields = array(
					"ACTIVE" => (($_REQUEST['action'] == 'activate') ? 'Y' : 'N')
				);

				$result = \Bitrix\Sale\Internals\PaySystemActionTable::update($id, $arFields);
				if (!$result->isSuccess())
				{
					if ($result->getErrorMessages())
						$lAdmin->AddGroupError(join(', ', $result->getErrorMessages()), $id);
					else
						$lAdmin->AddGroupError(GetMessage("SPSAN_ERROR_UPDATE"), $id);
				}

				break;
		}
	}
}

$params = array(
	'select' => array('ID', 'NAME', 'SORT', 'DESCRIPTION', 'ACTIVE', 'ACTION_FILE', 'LOGOTIP'),
	'filter' => $filter
);

if (ToUpper($by) != 'LID' && ToUpper($by) != 'CURRENCY')
	$params['order'] = array(ToUpper($by) => ToUpper($order));

$dbRes = \Bitrix\Sale\Internals\PaySystemActionTable::getList($params);

$result = array();

while ($paySystem = $dbRes->fetch())
{
	if (!empty($filter_person_type) && !in_array("NOT_REF", $filter_person_type))
	{
		$filter_person_type['PERSON_TYPE_ID'] = $filter_person_type;
		$dbRestriction = \Bitrix\Sale\Internals\ServiceRestrictionTable::getList(array(
			'filter' => array(
				'SERVICE_ID' => $paySystem['ID'],
				'SERVICE_TYPE' => \Bitrix\Sale\Services\PaySystem\Restrictions\Manager::SERVICE_TYPE_PAYMENT,
				'=CLASS_NAME' => '\Bitrix\Sale\Services\PaySystem\Restrictions\PersonType'
			)
		));

		while ($restriction = $dbRestriction->fetch())
		{
			if (!CSalePaySystemAction::checkRestriction($restriction, $filter_person_type))
				continue(2);
		}
	}

	$result[] = $paySystem;
}

$dbRes = new CDBResult();
$dbRes->InitFromArray($result);

$dbRes = new CAdminResult($dbRes, $sTableID);
$dbRes->NavStart();

$lAdmin->NavText($dbRes->GetNavPrint(GetMessage("SALE_PRLIST")));

$lAdmin->AddHeaders(array(
	array("id"=>"SORT", "content"=>GetMessage("SALE_SORT"),  "sort"=>"SORT", "default"=>true),
	array("id"=>"NAME","content"=>GetMessage("SALE_NAME"), "sort"=>"NAME", "default"=>true),
	array("id"=>"DESCRIPTION", "content"=>GetMessage("SALE_H_DESCRIPTION"), "default"=>true),
	array("id"=>"LOGOTIP", "content"=>GetMessage("SALE_LOGOTIP"),  "default"=>true),
	array("id"=>"ACTIVE", "content"=>GetMessage("SALE_ACTIVE"),  "sort"=>"ACTIVE", "default"=>true),
	array("id"=>"PERSON_TYPES", "content"=>GetMessage("SALE_H_PERSON_TYPES"), "default"=>false),
	array("id"=>"LID", "content"=>GetMessage('SALE_LID'), "default"=>false),
	array("id"=>"ACTION_FILES", "content"=>GetMessage("SALE_H_ACTION_FILES"), "default"=>false),
	array("id"=>"ID", "content"=>"ID", 	"sort"=>"ID", "default"=>true),
));

$arVisibleColumns = $lAdmin->GetVisibleHeaderColumns();

while ($arCCard = $dbRes->NavNext(true, "f_"))
{
	$row =& $lAdmin->AddRow($f_ID, $arCCard, "sale_pay_system_edit.php?ID=".$f_ID."&lang=".LANG, GetMessage("SALE_EDIT_DESCR"));

	$row->AddField("ID", "<a href=\"sale_pay_system_edit.php?ID=".$f_ID."&lang=".LANG."\">".$f_ID."</a>");
	$row->AddField("NAME", $f_NAME);
	$row->AddField("ACTIVE", (($f_ACTIVE=="Y") ? GetMessage("SPS_YES") : GetMessage("SPS_NO")));
	$row->AddField("SORT", $f_SORT);

	if ($f_LOGOTIP > 0)
	{
		$logoFileArray = CFile::GetFileArray($f_LOGOTIP);
		$f_LOGOTIP = CFile::ShowImage($logoFileArray, 100, 100, "border=0", "", false);
	}

	$row->AddField("LOGOTIP", $f_LOGOTIP);
	$row->AddField("DESCRIPTION", $f_DESCRIPTION);

	$pTypes = '';
	$aFiles = '';

	$dbRestriction = \Bitrix\Sale\Internals\ServiceRestrictionTable::getList(array(
		'select' => array('PARAMS'),
		'filter' => array(
			'SERVICE_ID' => $f_ID,
			'SERVICE_TYPE' => \Bitrix\Sale\Services\PaySystem\Restrictions\Manager::SERVICE_TYPE_PAYMENT,
			'=CLASS_NAME' => '\Bitrix\Sale\Services\PaySystem\Restrictions\PersonType'
		)
	));

	 if ($restriction = $dbRestriction->fetch())
	 {
		 $ptRes = \Bitrix\Sale\PersonTypeTable::getList(array('select' => array('NAME'), 'filter' => array('ID' => $restriction['PARAMS']['PERSON_TYPE_ID'])));
		 while ($personType = $ptRes->fetch())
		    $pTypes .= "<div>".$personType['NAME']."</div>";
	 }

	$row->AddField("PERSON_TYPES", $pTypes);

	$dbRestriction = \Bitrix\Sale\Internals\ServiceRestrictionTable::getList(array(
		'select' => array('PARAMS'),
		'filter' => array(
			'SERVICE_ID' => $f_ID,
			'SERVICE_TYPE' => \Bitrix\Sale\Services\PaySystem\Restrictions\Manager::SERVICE_TYPE_PAYMENT,
			'=CLASS_NAME' => '\Bitrix\Sale\Services\PaySystem\Restrictions\Site'
		)
	));

	$pSite = '';
	if ($restriction = $dbRestriction->fetch())
	{
		$siteRes = \Bitrix\Main\SiteTable::getList(array('select' => array('NAME', 'LID'), 'filter' => array('LID' => $restriction['PARAMS']['SITE_ID'], 'LANGUAGE_ID' => $context->getLanguage())));
		while ($site = $siteRes->fetch())
			$pSite .= "<div>".$site['NAME']." (".$site['LID'].")</div>";
	}

	$row->AddField("LID", $pSite);

	$description = \Bitrix\Sale\PaySystem\Manager::getHandlerDescription($f_ACTION_FILE);
	$row->AddField("ACTION_FILES", $description['NAME']);

	$arActions = array(
		array(
			"ICON" => "edit",
			"TEXT" => GetMessage("SALE_EDIT"),
			"TITLE" => GetMessage("SALE_EDIT_DESCR"),
			"ACTION" => $lAdmin->ActionRedirect("sale_pay_system_edit.php?ID=".$f_ID."&lang=".$context->getLanguage()),
			"DEFAULT" => true,
		),
	);
	if ($saleModulePermissions >= "W")
	{
		$arActions[] = array("SEPARATOR" => true);
		$arActions[] = array(
			"ICON" => "delete",
			"TEXT" => GetMessage("SALE_DELETE"),
			"TITLE" => GetMessage("SALE_DELETE_DESCR"),
			"ACTION" => "if(confirm('".GetMessage('SALE_CONFIRM_DEL_MESSAGE')."')) ".$lAdmin->ActionDoGroup($f_ID, "delete"),
		);
	}

	$row->AddActions($arActions);
}

$lAdmin->AddFooter(
	array(
		array(
			"title" => GetMessage("MAIN_ADMIN_LIST_SELECTED"),
			"value" => $dbRes->SelectedRowsCount()
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
		"activate" => GetMessage("MAIN_ADMIN_LIST_ACTIVATE"),
		"deactivate" => GetMessage("MAIN_ADMIN_LIST_DEACTIVATE"),
	)
);

	$aContext = array(
		array(
			"TEXT" => GetMessage("SPSAN_ADD_NEW"),
			"TITLE" => GetMessage("SPSAN_ADD_NEW_ALT"),
			"LINK" => "sale_pay_system_edit.php?lang=".LANG,
			"ICON" => "btn_new"
		),
	);
	/** @global CUser $USER */
	global $USER;
	if($USER->CanDoOperation("install_updates"))
	{
		$aContext[] = array(
			"TEXT" => GetMessage("SPSAN_MARKETPLACE_ADD_NEW"),
			"TITLE" => GetMessage("SPSAN_MARKETPLACE_ADD_NEW_ALT"),
			"LINK" => "update_system_market.php?category=35&lang=".LANG,
			"ICON" => "btn"
		);
	}
	$lAdmin->AddAdminContextMenu($aContext);
}

$lAdmin->CheckListMode();

$APPLICATION->SetTitle(GetMessage("SALE_SECTION_TITLE"));
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
?>

<form name="find_form" method="GET" action="<?echo $APPLICATION->GetCurPage()?>?">
<?
$oFilter = new CAdminFilter(
	$sTableID."_filter",
	array(
		GetMessage("SALE_F_PERSON_TYPE"),
	)
);

$oFilter->Begin();
?>
	<tr>
		<td><?echo GetMessage("SALE_F_ACTIVE")?>:</td>
		<td>
			<select name="filter_active">
				<option value="NOT_REF">(<?echo GetMessage("SALE_ALL")?>)</option>
				<option value="Y"<?if ($filter_active=="Y") echo " selected"?>><?echo GetMessage("SALE_YES")?></option>
				<option value="N"<?if ($filter_active=="N") echo " selected"?>><?echo GetMessage("SALE_NO")?></option>
			</select>
		</td>
	</tr>

	<tr>
		<td><?echo GetMessage("SALE_F_PERSON_TYPE")?>:</td>
		<td>
			<select name="filter_person_type[]" multiple size=5>
				<option value="NOT_REF">(<?echo GetMessage("SALE_ALL")?>)</option>
				<?$dbPersonType = CSalePersonType::GetList(array("SORT" => "ASC", "NAME" => "ASC"), array());
				while ($arPersonType = $dbPersonType->GetNext())
				{
					?><option value="<?=$arPersonType["ID"]?>"<?if (in_array($arPersonType["ID"], $filter_person_type)) echo " selected"?>><?=$arPersonType["NAME"]." (".implode(", ", $arPersonType["LIDS"]).")"?></option><?
				}
				?>
			</select>
		</td>
	</tr>
<?
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