<?
use Bitrix\Main,
	Bitrix\Main\Loader,
	Bitrix\Main\Localization\Loc,
	Bitrix\Catalog;

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/catalog/prolog.php");
global $APPLICATION;
global $DB;
global $USER;
global $USER_FIELD_MANAGER;

if(!($USER->CanDoOperation('catalog_read') || $USER->CanDoOperation('catalog_store')))
	$APPLICATION->AuthForm('');
Loader::includeModule('catalog');
$bReadOnly = !$USER->CanDoOperation('catalog_store');

Loc::loadMessages(__FILE__);

$bCanAdd = true;
$bExport = false;
if ($_REQUEST["mode"] == "excel")
	$bExport = true;

if (!CBXFeatures::IsFeatureEnabled('CatMultiStore'))
{
	$dbResultList = CCatalogStore::GetList(array());
	if($arResult = $dbResultList->Fetch())
		$bCanAdd = false;
}

if($ex = $APPLICATION->GetException())
{
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

	$strError = $ex->GetString();
	ShowError($strError);

	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
	die();
}

/** For a given site ID, issues generated site title.
 * @param $siteId
 * @return string
 */

function getSiteTitle($siteId)
{
	static $rsSites = '';
	static $arSitesShop = array();
	$siteTitle = $siteId;

	if($rsSites === '')
	{
		$rsSites = CSite::GetList($b="id", $o="asc", Array("ACTIVE" => "Y"));
		while($arSite = $rsSites->GetNext())
			$arSitesShop[] = array("ID" => $arSite["ID"], "NAME" => $arSite["NAME"]);
	}

	foreach($arSitesShop as $arSite)
	{
		if($arSite["ID"] == $siteId)
		{
			$siteTitle = $arSite["NAME"]." (".$arSite["ID"].")";
		}
	}
	return $siteTitle;
}

$sTableID = "b_catalog_store";
$entityId = Catalog\StoreTable::getUfId();

$oSort = new CAdminSorting($sTableID, "SORT", "ASC");
$lAdmin = new CAdminList($sTableID, $oSort);

$filter = array();
$filterFields = array(
	'filter_id_from',
	'filter_id_to',
	'filter_site_id',
	'filter_active',
	'filter_title',
	'filter_code',
	'filter_xml_id',
	'filter_issuing_center',
	'filter_shipping_center',
	'filter_address',
	'filter_phone',
	'filter_email'
);
$USER_FIELD_MANAGER->AdminListAddFilterFields($entityId, $filterFields);
$filterValues = array_fill_keys($filterFields, null);

$lAdmin->InitFilter($filterFields);

if (isset($filter_id_from) && is_string($filter_id_from))
{
	$filter_id_from = trim($filter_id_from);
	if ($filter_id_from !== '')
		$filterValues['filter_id_from'] = (int)$filter_id_from;
}
if (isset($filter_id_to) && is_string($filter_id_to))
{
	$filter_id_to = trim($filter_id_to);
	if ($filter_id_to !== '')
		$filterValues['filter_id_to'] = (int)$filter_id_to;
}

if (isset($filter_site_id) && is_string($filter_site_id))
{
	if ($filter_site_id != 'NOT_REF' && $filter_site_id !== '')
		$filterValues['filter_site_id'] = $filter_site_id;
}

if (isset($filter_active) && is_string($filter_active))
{
	if ($filter_active === 'Y' || $filter_active === 'N')
		$filterValues['filter_active'] = $filter_active;
}

if (isset($filter_title) && is_string($filter_title))
{
	$filter_title = trim($filter_title);
	if ($filter_title !== '')
		$filterValues['filter_title'] = $filter_title;
}

if (isset($filter_code) && is_string($filter_code))
{
	$filter_code = trim($filter_code);
	if ($filter_code !== '')
		$filterValues['filter_code'] = $filter_code;
}

if (isset($filter_xml_id) && is_string($filter_xml_id))
{
	$filter_xml_id = trim($filter_xml_id);
	if ($filter_xml_id !== '')
		$filterValues['filter_xml_id'] = $filter_xml_id;
}

if (isset($filter_issuing_center) && is_string($filter_issuing_center))
{
	if ($filter_issuing_center === 'Y' || $filter_issuing_center === 'N')
		$filterValues['filter_issuing_center'] = $filter_issuing_center;
}

if (isset($filter_shipping_center) && is_string($filter_shipping_center))
{
	if ($filter_shipping_center === 'Y' || $filter_shipping_center === 'N')
		$filterValues['filter_shipping_center'] = $filter_shipping_center;
}

if (isset($filter_address) && is_string($filter_address))
{
	$filter_address = trim($filter_address);
	if ($filter_address !== '')
		$filterValues['filter_address'] = $filter_address;
}

if (isset($filter_phone) && is_string($filter_phone))
{
	$filter_phone = trim($filter_phone);
	if ($filter_phone !== '')
		$filterValues['filter_phone'] = $filter_phone;
}

if (isset($filter_email) && is_string($filter_email))
{
	$filter_email = trim($filter_email);
	if ($filter_email !== '')
		$filterValues['filter_email'] = $filter_email;
}

if ($filterValues['filter_id_from'] !== null || $filterValues['filter_id_to'] !==  null)
{
	if ($filterValues['filter_id_from'] === $filterValues['filter_id_to'])
	{
		$filter['=ID'] = $filterValues['filter_id_from'];
	}
	else
	{
		if ($filterValues['filter_id_from'] !== null)
			$filter['>=ID'] = $filterValues['filter_id_from'];
		if ($filterValues['filter_id_to'] !== null)
			$filter['<=ID'] = $filterValues['filter_id_to'];
	}
}

if ($filterValues['filter_site_id'] !== null)
	$filter['=SITE_ID'] = ($filterValues['filter_site_id'] == '-' ? null : $filterValues['filter_site_id']);

if ($filterValues['filter_active'] !== null)
	$filter['=ACTIVE'] = $filterValues['filter_active'];

if ($filterValues['filter_title'] !== null)
	$filter['%TITLE'] = $filterValues['filter_title'];

if ($filterValues['filter_code'] !== null)
	$filter['=CODE'] = $filterValues['filter_code'];

if ($filterValues['filter_xml_id'] !== null)
	$filter['=XML_ID'] = $filterValues['filter_xml_id'];

if ($filterValues['filter_issuing_center'] !== null)
	$filter['=ISSUING_CENTER'] = $filterValues['filter_issuing_center'];

if ($filterValues['filter_shipping_center'] !== null)
	$filter['=ACTIVE'] = $filterValues['filter_shipping_center'];

if ($filterValues['filter_address'] !== null)
	$filter['%ADDRESS'] = $filterValues['filter_address'];

if ($filterValues['filter_phone'] !== null)
	$filter['%PHONE'] = $filterValues['filter_phone'];

if ($filterValues['filter_email'] !== null)
	$filter['%EMAIL'] = $filterValues['filter_email'];

$USER_FIELD_MANAGER->AdminListAddFilter($entityId, $filter);

if($lAdmin->EditAction() && !$bReadOnly)
{
	foreach ($_POST['FIELDS'] as $ID => $arFields)
	{
		$ID = (int)$ID;

		if ($ID <= 0 || !$lAdmin->IsUpdated($ID))
			continue;
		if(isset($arFields["IMAGE_ID"]))
			unset($arFields["IMAGE_ID"]);
		if (isset($arFields['GPS_N']))
			$arFields['GPS_N'] = str_replace(',', '.', $arFields['GPS_N']);
		if (isset($arFields['GPS_S']))
			$arFields['GPS_S'] = str_replace(',', '.', $arFields['GPS_S']);

		$DB->StartTransaction();
		if(!CCatalogStore::Update($ID, $arFields))
		{
			if($ex = $APPLICATION->GetException())
				$lAdmin->AddUpdateError($ex->GetString(), $ID);
			else
				$lAdmin->AddUpdateError(Loc::getMessage("ERROR_UPDATING_REC")." (".$arFields["ID"].", ".$arFields["TITLE"].", ".$arFields["SORT"].")", $ID);

			$DB->Rollback();
		}
		else
		{
			$ufUpdated = $USER_FIELD_MANAGER->Update($entityId, $ID, $arFields);
			$DB->Commit();
		}
	}
}

if(($arID = $lAdmin->GroupAction()) && !$bReadOnly)
{
	if($_REQUEST['action_target']=='selected')
	{
		$arID = array();
		$dbResultList = CCatalogStore::GetList(array(), $filter, false, false, array('ID'));
		while ($arResult = $dbResultList->Fetch())
			$arID[] = $arResult['ID'];
	}

	foreach ($arID as $ID)
	{
		if(strlen($ID) <= 0)
			continue;

		switch ($_REQUEST['action'])
		{
			case "delete":
				@set_time_limit(0);

				$DB->StartTransaction();

				if(!CCatalogStore::Delete($ID))
				{
					$DB->Rollback();

					if($ex = $APPLICATION->GetException())
						$lAdmin->AddGroupError($ex->GetString(), $ID);
					else
						$lAdmin->AddGroupError(Loc::getMessage("ERROR_DELETING_TYPE"), $ID);
				}
				$DB->Commit();
				break;
		}
	}
}

$filterSiteList = array();
$siteList = array();
$siteIterator = Main\SiteTable::getList(array(
	'select' => array('LID', 'NAME', 'ACTIVE', 'SORT'),
	'order' => array('SORT' => 'ASC')
));
while ($site = $siteIterator->fetch())
{
	$filterSiteList[] = $site;
	$siteList[$site['LID']] = $site['LID'];
}
unset($site, $siteIterator);

$arSelect = array(
	"ID",
	"ACTIVE",
	"TITLE",
	"ADDRESS",
	"DESCRIPTION",
	"GPS_N",
	"GPS_S",
	"IMAGE_ID",
	"PHONE",
	"SCHEDULE",
	"XML_ID",
	"DATE_MODIFY",
	"DATE_CREATE",
	"USER_ID",
	"MODIFIED_BY",
	"SORT",
	"EMAIL",
	"ISSUING_CENTER",
	"SHIPPING_CENTER",
	"SITE_ID",
	"CODE",
	"UF_*"
);

if (!isset($by))
	$by = 'ID';
if (!isset($order))
	$order = 'ASC';

if(array_key_exists("mode", $_REQUEST) && $_REQUEST["mode"] == "excel")
	$arNavParams = false;
else
	$arNavParams = array("nPageSize"=>CAdminResult::GetNavSize($sTableID));

$dbResultList = CCatalogStore::GetList(
	array($by => $order),
	$filter,
	false,
	$arNavParams,
	$arSelect
);

$dbResultList = new CAdminResult($dbResultList, $sTableID);
$dbResultList->NavStart();
$lAdmin->NavText($dbResultList->GetNavPrint(Loc::getMessage("group_admin_nav")));

$headers = array(
	array(
		"id" => "ID",
		"content" => "ID",
		"sort" => "ID",
		"default" => true
	),
	array(
		"id" => "SORT",
		"content" => Loc::getMessage("CSTORE_SORT"),
		"sort" => "SORT",
		"default" => true
	),
	array(
		"id" => "TITLE",
		"content" => Loc::getMessage("TITLE"),
		"sort" => "TITLE",
		"default" => true
	),
	array(
		"id" => "ACTIVE",
		"content" => Loc::getMessage("STORE_ACTIVE"),
		"sort" => "ACTIVE",
		"default" => true
	),
	array(
		"id" => "ADDRESS",
		"content" => Loc::getMessage("ADDRESS"),
		"sort" => "",
		"default" => true
	),
	array(
		"id" => "IMAGE_ID",
		"content" => Loc::getMessage("STORE_IMAGE"),
		"sort" => "",
		"default" => false
	),
	array(
		"id" => "DESCRIPTION",
		"content" => Loc::getMessage("DESCRIPTION"),
		"sort" => "",
		"default" => true
	),
	array(
		"id" => "GPS_N",
		"content" => Loc::getMessage("GPS_N"),
		"sort" => "GPS_N",
		"default" => false
	),
	array(
		"id" => "GPS_S",
		"content" => Loc::getMessage("GPS_S"),
		"sort" => "GPS_S",
		"default" => false
	),
	array(
		"id" => "PHONE",
		"content" => Loc::getMessage("PHONE"),
		"sort" => "",
		"default" => true
	),
	array(
		"id" => "SCHEDULE",
		"content" => Loc::getMessage("SCHEDULE"),
		"sort" => "",
		"default" => true
	),
	array(
		"id" => "DATE_MODIFY",
		"content" => Loc::getMessage("DATE_MODIFY"),
		"sort" => "DATE_MODIFY",
		"default" => true
	),
	array(
		"id" => "MODIFIED_BY",
		"content" => Loc::getMessage("MODIFIED_BY"),
		"sort" => "MODIFIED_BY",
		"default" => true
	),
	array(
		"id" => "DATE_CREATE",
		"content" => Loc::getMessage("DATE_CREATE"),
		"sort" => "DATE_CREATE",
		"default" => false
	),
	array(
		"id" => "USER_ID",
		"content" => Loc::getMessage("USER_ID"),
		"sort" => "USER_ID",
		"default" => false
	),
	array(
		"id" => "EMAIL",
		"content" => "E-mail",
		"sort" => "EMAIL",
		"default" => false
	),
	array(
		"id" => "ISSUING_CENTER",
		"content" => Loc::getMessage("ISSUING_CENTER"),
		"sort" => "ISSUING_CENTER",
		"default" => false
	),
	array(
		"id" => "SHIPPING_CENTER",
		"content" => Loc::getMessage("SHIPPING_CENTER"),
		"sort" => "SHIPPING_CENTER",
		"default" => false
	),
	array(
		"id" => "SITE_ID",
		"content" => Loc::getMessage("STORE_SITE_ID"),
		"sort" => "SITE_ID",
		"default" => true
	),
	array(
		"id" => "CODE",
		"content" => Loc::getMessage("STORE_CODE"),
		"sort" => "CODE",
		"default" => false
	),
	array(
		"id" => "XML_ID",
		"content" => Loc::getMessage("STORE_XML_ID"),
		"sort" => "XML_ID",
		"default" => false
	)
);

$USER_FIELD_MANAGER->AdminListAddHeaders($entityId, $headers);

$arSelectFieldsMap = array(
	"ID" => false,
	"TITLE" => false,
	"ACTIVE" => false,
	"ADDRESS" => false,
	"IMAGE_ID" => false,
	"DESCRIPTION" => false,
	"GPS_N" => false,
	"GPS_S" => false,
	"PHONE" => false,
	"SCHEDULE" => false,
	"DATE_MODIFY" => false,
	"MODIFIED_BY" => false,
	"DATE_CREATE" => false,
	"USER_ID" => false,
	"EMAIL" => false,
	"ISSUING_CENTER" => false,
	"SHIPPING_CENTER" => false,
	"SITE_ID" => false,
	"CODE" => false,
	"XML_ID" => false
);

$lAdmin->AddHeaders($headers);

$arSelectFields = $lAdmin->GetVisibleHeaderColumns();
if(!in_array('ID', $arSelectFields))
	$arSelectFields[] = 'ID';

$arSelectFieldsMap = array_merge($arSelectFieldsMap, array_fill_keys($arSelectFields, true));

$arUserList = array();
$arUserID = array();
$strNameFormat = CSite::GetNameFormat(true);

$arRows = array();

while ($arRes = $dbResultList->Fetch())
{
	$arRes['ID'] = (int)$arRes['ID'];
	$arRes['SORT'] = (int)$arRes['SORT'];
	if($arSelectFieldsMap['USER_ID'])
	{
		$arRes['USER_ID'] = (int)$arRes['USER_ID'];
		if(0 < $arRes['USER_ID'])
			$arUserID[$arRes['USER_ID']] = true;
	}
	if($arSelectFieldsMap['MODIFIED_BY'])
	{
		$arRes['MODIFIED_BY'] = (int)$arRes['MODIFIED_BY'];
		if(0 < $arRes['MODIFIED_BY'])
			$arUserID[$arRes['MODIFIED_BY']] = true;
	}

	$arRows[$arRes['ID']] = $row =& $lAdmin->AddRow($arRes['ID'], $arRes);
	$row->AddField("ID", "<a href=\""."cat_store_edit.php?ID=".$arRes['ID']."&lang=".LANGUAGE_ID."&".GetFilterParams("filter_")."\">".$arRes['ID']."</a>");
	if($bReadOnly)
	{
		$row->AddViewField("SORT", $arRes['SORT']);
		if($arSelectFieldsMap['CODE'])
			$row->AddInputField("CODE", false);
		if($arSelectFieldsMap['TITLE'])
			$row->AddInputField("TITLE", false);
		if($arSelectFieldsMap['ADDRESS'])
			$row->AddInputField("ADDRESS", false);
		if($arSelectFieldsMap['DESCRIPTION'])
			$row->AddInputField("DESCRIPTION", false);
		if($arSelectFieldsMap['ACTIVE'])
			$row->AddCheckField("ACTIVE", false);
		if($arSelectFieldsMap['ISSUING_CENTER'])
			$row->AddCheckField("ISSUING_CENTER", false);
		if($arSelectFieldsMap['SHIPPING_CENTER'])
			$row->AddCheckField("SHIPPING_CENTER", false);
		if($arSelectFieldsMap['PHONE'])
			$row->AddInputField("PHONE", false);
		if($arSelectFieldsMap['SCHEDULE'])
			$row->AddInputField("SCHEDULE", false);
		if($arSelectFieldsMap['EMAIL'])
			$row->AddInputField("EMAIL", false);
		if($arSelectFieldsMap['IMAGE_ID'] && !$bExport)
			$row->AddField("IMAGE_ID", CFile::ShowImage($arRes['IMAGE_ID'], 100, 100, "border=0", "", true));
		if($arSelectFieldsMap['GPS_N'])
			$row->AddInputField('GPS_N', false);
		if($arSelectFieldsMap['GPS_S'])
			$row->AddInputField('GPS_S', false);
		if($arSelectFieldsMap['XML_ID'])
			$row->AddInputField("XML_ID", false);
	}
	else
	{
		$row->AddInputField("SORT", array("size" => "3"));
		if($arSelectFieldsMap['CODE'])
			$row->AddInputField("CODE");
		if($arSelectFieldsMap['TITLE'])
			$row->AddInputField("TITLE");
		if($arSelectFieldsMap['ACTIVE'])
			$row->AddCheckField("ACTIVE");
		if($arSelectFieldsMap['ISSUING_CENTER'])
			$row->AddCheckField("ISSUING_CENTER");
		if($arSelectFieldsMap['SHIPPING_CENTER'])
			$row->AddCheckField("SHIPPING_CENTER");
		if($arSelectFieldsMap['ADDRESS'])
			$row->AddInputField("ADDRESS", array("size" => 30));
		if($arSelectFieldsMap['DESCRIPTION'])
			$row->AddInputField("DESCRIPTION", array("size" => 50));
		if($arSelectFieldsMap['PHONE'])
			$row->AddInputField("PHONE", array("size" => 25));
		if($arSelectFieldsMap['SCHEDULE'])
			$row->AddInputField("SCHEDULE", array("size" => 35));
		if($arSelectFieldsMap['EMAIL'])
			$row->AddInputField("EMAIL", array("size" => 35));
		if($arSelectFieldsMap['IMAGE_ID'] && !$bExport)
			$row->AddField("IMAGE_ID", CFile::ShowImage($arRes['IMAGE_ID'], 100, 100, "border=0", "", true));
		if($arSelectFieldsMap['GPS_N'])
			$row->AddInputField('GPS_N', array('size' => 35));
		if($arSelectFieldsMap['GPS_S'])
			$row->AddInputField('GPS_S', array('size' => 35));
		if($arSelectFieldsMap['XML_ID'])
			$row->AddInputField("XML_ID");
	}

	if($arSelectFieldsMap['SITE_ID'])
		$row->AddViewField("SITE_ID", htmlspecialcharsbx(getSiteTitle($arRes['SITE_ID'])));
	if($arSelectFieldsMap['DATE_CREATE'])
		$row->AddCalendarField("DATE_CREATE", false);
	if($arSelectFieldsMap['DATE_MODIFY'])
		$row->AddCalendarField("DATE_MODIFY", false);

	$arActions = array();
	$arActions[] = array("ICON"=>"edit", "TEXT"=>Loc::getMessage("EDIT_STORE_ALT"), "ACTION"=>$lAdmin->ActionRedirect("cat_store_edit.php?ID=".$arRes['ID']."&lang=".LANGUAGE_ID."&".GetFilterParams("filter_").""), "DEFAULT"=>true);

	if(!$bReadOnly)
	{
		$arActions[] = array("SEPARATOR" => true);
		$arActions[] = array("ICON"=>"delete", "TEXT"=>Loc::getMessage("DELETE_STORE_ALT"), "ACTION"=>"if(confirm('".CUtil::JSEscape(Loc::getMessage('DELETE_STORE_CONFIRM'))."')) ".$lAdmin->ActionDoGroup($arRes['ID'], "delete"));
	}

	$row->AddActions($arActions);
}
if(isset($row))
	unset($row);

if($arSelectFieldsMap['USER_ID'] || $arSelectFieldsMap['MODIFIED_BY'])
{
	if(!empty($arUserID))
	{
		$byUser = 'ID';
		$byOrder = 'ASC';
		$rsUsers = CUser::GetList(
			$byUser,
			$byOrder,
			array('ID' => implode(' | ', array_keys($arUserID))),
			array('FIELDS' => array('ID', 'LOGIN', 'NAME', 'LAST_NAME', 'SECOND_NAME', 'EMAIL'))
		);
		while ($arOneUser = $rsUsers->Fetch())
		{
			$arOneUser['ID'] = (int)$arOneUser['ID'];
			$arUserList[$arOneUser['ID']] = '<a href="/bitrix/admin/user_edit.php?lang='.LANGUAGE_ID.'&ID='.$arOneUser['ID'].'">'.CUser::FormatName($strNameFormat, $arOneUser).'</a>';
		}
	}

	foreach ($arRows as &$row)
	{
		if($arSelectFieldsMap['USER_ID'])
		{
			$strCreatedBy = '';
			if (0 < $row->arRes['USER_ID'] && isset($arUserList[$row->arRes['USER_ID']]))
			{
				$strCreatedBy = $arUserList[$row->arRes['USER_ID']];
			}
			$row->AddViewField("USER_ID", $strCreatedBy);
		}
		if($arSelectFieldsMap['MODIFIED_BY'])
		{
			$strModifiedBy = '';
			if (0 < $row->arRes['MODIFIED_BY'] && isset($arUserList[$row->arRes['USER_ID']]))
			{
				$strModifiedBy = $arUserList[$row->arRes['MODIFIED_BY']];
			}
			$row->AddViewField("MODIFIED_BY", $strModifiedBy);
		}
	}
	if(isset($row))
		unset($row);
}

$lAdmin->AddFooter(
	array(
		array(
			"title" => Loc::getMessage("MAIN_ADMIN_LIST_SELECTED"),
			"value" => $dbResultList->SelectedRowsCount()
		),
		array(
			"counter" => true,
			"title" => Loc::getMessage("MAIN_ADMIN_LIST_CHECKED"),
			"value" => "0"
		),
	)
);

if(!$bReadOnly)
{
	$lAdmin->AddGroupActionTable(
		array(
			"delete" => Loc::getMessage("MAIN_ADMIN_LIST_DELETE"),
		)
	);
}

if(!$bReadOnly && $bCanAdd)
{
	$aContext = array(
		array(
			"TEXT" => Loc::getMessage("STORE_ADD_NEW"),
			"ICON" => "btn_new",
			"LINK" => "cat_store_edit.php?lang=".LANGUAGE_ID,
			"TITLE" => Loc::getMessage("STORE_ADD_NEW_ALT")
		),
	);
	$lAdmin->AddAdminContextMenu($aContext);
}

$lAdmin->CheckListMode();

$APPLICATION->SetTitle(Loc::getMessage("STORE_TITLE"));
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
?>
<form name="find_form" method="GET" action="<?echo $APPLICATION->GetCurPage()?>?">
<?
$findFields = array(
	'ID',
	Loc::getMessage('STORE_SITE_ID'),
	Loc::getMessage('STORE_ACTIVE'),
	Loc::getMessage('TITLE'),
	Loc::getMessage('STORE_CODE'),
	Loc::getMessage('STORE_XML_ID'),
	Loc::getMessage('ISSUING_CENTER'),
	Loc::getMessage('SHIPPING_CENTER'),
	Loc::getMessage('ADDRESS'),
	Loc::getMessage('PHONE'),
	'E-mail'
);
$USER_FIELD_MANAGER->AddFindFields($entityId, $arFindFields);
$oFilter = new CAdminFilter(
	$sTableID."_filter",
	$findFields
);
$oFilter->Begin();
?>
	<tr>
		<td>ID:</td>
		<td>
			<?=Loc::getMessage('BX_CATALOG_STORE_LIST_MESS_RANGE_FROM'); ?>
			<input id="filter_id_from" type="text" name="filter_id_from" value="<?=htmlspecialcharsbx($filterValues['filter_id_from']); ?>" size="6">
			<?echo Loc::getMessage("BX_CATALOG_STORE_LIST_MESS_RANGE_TO"); ?>
			<input id="filter_id_to" type="text" name="filter_id_to" value="<?=htmlspecialcharsbx($filterValues['filter_id_to']); ?>" size="6">
		</td>
	</tr>
	<tr>
		<td><?=Loc::getMessage("STORE_SITE_ID"); ?>:</td>
		<td>
			<select name="filter_site_id">
				<option value="NOT_REF"<?=($filterValues['filter_site_id'] == 'NOT_REF' ? ' selected' : ''); ?>><?=Loc::getMessage('BX_CATALOG_STORE_LIST_EMPTY_FILTER'); ?></option>
				<option value="-"<?=($filterValues['filter_site_id'] == '-' ? ' selected' : ''); ?>><?=Loc::getMessage('BX_CATALOG_STORE_LIST_FILTER_EMPTY_SITE_ID'); ?></option>
				<?
				foreach ($filterSiteList as $row)
				{
					?><option value="<?=$row['LID']; ?>"<?=($row['LID'] == $filterValues['filter_site_id'] ? ' selected' : ''); ?>>[<?=$row['LID']; ?>]&nbsp;<?=htmlspecialcharsEx($row['NAME']); ?></option><?
				}
				unset($row);
				?>
			</select>
		</td>
	</tr>
	<tr>
		<td><?=Loc::getMessage('STORE_ACTIVE'); ?>:</td>
		<td>
			<select name="filter_active">
				<option value=""><?=Loc::getMessage('BX_CATALOG_STORE_LIST_FILTER_ANY_VALUE'); ?></option>
				<option value="Y"<?=($filterValues['filter_active'] === 'Y' ? ' selected' : ''); ?>><?= htmlspecialcharsEx(Loc::getMessage('BX_CATALOG_STORE_LIST_FILTER_YES_VALUE')) ?></option>
				<option value="N"<?=($filterValues['filter_active'] === 'N' ? ' selected' : ''); ?>><?= htmlspecialcharsEx(Loc::getMessage('BX_CATALOG_STORE_LIST_FILTER_NO_VALUE')) ?></option>
			</select>
		</td>
	</tr>
	<tr>
		<td><?=Loc::getMessage('TITLE'); ?>:</td>
		<td><input type="text" name="filter_title" value="<?=htmlspecialcharsbx($filterValues['filter_title']); ?>"></td>
	</tr>
	<tr>
		<td><?=Loc::getMessage('STORE_CODE'); ?>:</td>
		<td><input type="text" name="filter_code" value="<?=htmlspecialcharsbx($filterValues['filter_code']); ?>"></td>
	</tr>
	<tr>
		<td><?=Loc::getMessage('STORE_XML_ID'); ?>:</td>
		<td><input type="text" name="filter_xml_id" value="<?=htmlspecialcharsbx($filterValues['filter_xml_id']); ?>"></td>
	</tr>
	<tr>
		<td><?=Loc::getMessage('ISSUING_CENTER'); ?>:</td>
		<td>
			<select name="filter_issuing_center">
				<option value=""><?=Loc::getMessage('BX_CATALOG_STORE_LIST_FILTER_ANY_VALUE'); ?></option>
				<option value="Y"<?=($filterValues['filter_issuing_center'] === 'Y' ? ' selected' : ''); ?>><?= htmlspecialcharsEx(Loc::getMessage('BX_CATALOG_STORE_LIST_FILTER_YES_VALUE')) ?></option>
				<option value="N"<?=($filterValues['filter_issuing_center'] === 'N' ? ' selected' : ''); ?>><?= htmlspecialcharsEx(Loc::getMessage('BX_CATALOG_STORE_LIST_FILTER_NO_VALUE')) ?></option>
			</select>
		</td>
	</tr>
	<tr>
		<td><?=Loc::getMessage('SHIPPING_CENTER'); ?>:</td>
		<td>
			<select name="filter_shipping_center">
				<option value=""><?=Loc::getMessage('BX_CATALOG_STORE_LIST_FILTER_ANY_VALUE'); ?></option>
				<option value="Y"<?=($filterValues['filter_shipping_center'] === 'Y' ? ' selected' : ''); ?>><?= htmlspecialcharsEx(Loc::getMessage('BX_CATALOG_STORE_LIST_FILTER_YES_VALUE')) ?></option>
				<option value="N"<?=($filterValues['filter_shipping_center'] === 'N' ? ' selected' : ''); ?>><?= htmlspecialcharsEx(Loc::getMessage('BX_CATALOG_STORE_LIST_FILTER_NO_VALUE')) ?></option>
			</select>
		</td>
	</tr>
	<tr>
		<td><?=Loc::getMessage('ADDRESS'); ?>:</td>
		<td><input type="text" name="filter_address" value="<?=htmlspecialcharsbx($filterValues['filter_address']); ?>"></td>
	</tr>
	<tr>
		<td><?=Loc::getMessage('PHONE'); ?>:</td>
		<td><input type="text" name="filter_phone" value="<?=htmlspecialcharsbx($filterValues['filter_phone']); ?>"></td>
	</tr>
	<tr>
		<td>E-mail:</td>
		<td><input type="text" name="filter_email" value="<?=htmlspecialcharsbx($filterValues['filter_email']); ?>"></td>
	</tr>
	<?
	$USER_FIELD_MANAGER->AdminListShowFilter($entityId);

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
<script type="text/javascript">
	function changeIdTo()
	{
		var destination = BX('filter_id_to');

		if (this.value === '')
			return;
		if (!BX.type.isElementNode(destination))
			return;
		if (destination.value !== '')
			return;
		destination.value = this.value;
		destination = null;
	}
	BX.ready(function(){
		var control = BX('filter_id_from');
		if (!BX.type.isElementNode(control))
			return;
		BX.bind(control, 'change', changeIdTo);
	});
</script>
<?
$lAdmin->DisplayList();

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");