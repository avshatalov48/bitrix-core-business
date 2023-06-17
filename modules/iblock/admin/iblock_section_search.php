<?php
/** @global CMain $APPLICATION */
/** @global CUser $USER */
/** @global CDatabase $DB */
/** @global CUserTypeManager $USER_FIELD_MANAGER */

use Bitrix\Main\Context;
use Bitrix\Main\Loader;
use Bitrix\Catalog;

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
CModule::IncludeModule("iblock");
IncludeModuleLangFile(__FILE__);

$request = Context::getCurrent()->getRequest();

//Init variables
$reloadParams = array();

$tableId = '';
if (isset($_GET['tableId']))
	$tableId = preg_replace("/[^a-zA-Z0-9_:\\[\\]]/", "", $_GET['tableId']);
if ($tableId != '')
	$reloadParams['tableId'] = $tableId;

$n = '';
if (isset($_GET['n']))
	$n = preg_replace("/[^a-zA-Z0-9_:\\[\\]]/", "", $_GET['n']);
if ($n != '')
	$reloadParams['n'] = $n;

$k = '';
if (isset($_GET['k']))
	$k = preg_replace("/[^a-zA-Z0-9_:]/", "", $_GET['k']);
if ($k != '')
	$reloadParams['k'] = $k;

$lookup = '';
if (isset($_GET['lookup']))
	$lookup = preg_replace("/[^a-zA-Z0-9_:]/", "", $_GET["lookup"]);
if ($lookup != '')
	$reloadParams['lookup'] = $lookup;

$m = (isset($_GET["m"]) && $_GET["m"] === "y");
if ($m)
	$reloadParams['m'] = 'y';

$get_xml_id = (isset($_GET["get_xml_id"]) && $_GET["get_xml_id"] === "Y");
if ($get_xml_id)
	$reloadParams['get_xml_id'] = 'Y';

$hideIblockId = 0;
if (isset($_GET['hideiblock']) && is_string($_GET['hideiblock']))
	$hideIblockId = (int)$_GET['hideiblock'];
if ($hideIblockId < 0)
	$hideIblockId = 0;

$showIblockList = true;
$iblockFix = isset($_GET['iblockfix']) && $_GET['iblockfix'] === 'y';
$IBLOCK_ID = 0;
if ($iblockFix)
{
	if (isset($_GET['IBLOCK_ID']))
		$IBLOCK_ID = (int)$_GET['IBLOCK_ID'];
	if ($IBLOCK_ID <= 0)
	{
		$IBLOCK_ID = 0;
		$iblockFix = false;
	}
}
if ($iblockFix)
{
	$reloadParams['iblockfix'] = 'y';
	$showIblockList = false;
	$hideIblockId = 0;
}
if ($hideIblockId > 0)
	$reloadParams['hideiblock'] = $hideIblockId;

$boolDiscount = (isset($_REQUEST['discount']) && $_REQUEST['discount'] === 'Y');
if ($boolDiscount)
	$reloadParams['discount'] = 'Y';

$simpleName = (isset($_REQUEST['simplename']) && $_REQUEST['simplename'] === 'Y');
if ($simpleName)
	$reloadParams['simplename'] = 'Y';

$reloadUrl = $APPLICATION->GetCurPage().'?lang='.LANGUAGE_ID;
foreach ($reloadParams as $key => $value)
	$reloadUrl .= '&'.$key.'='.$value;
unset($key, $value);
$extReloadUrl = $reloadUrl;
if ($iblockFix)
	$extReloadUrl .= '&IBLOCK_ID='.$IBLOCK_ID;

if ($tableId != '')
	$sTableID = 'tbl_iblock_section_search_'.md5($tableId);
elseif ($boolDiscount)
	$sTableID = 'tbl_iblock_section_search_'.md5('discount');
else
	$sTableID = 'tbl_iblock_section_search_'.md5($n);

if (!$iblockFix)
{
	$lAdmin = new CAdminList($sTableID);
	$lAdmin->InitFilter(array('find_iblock_id'));
	/* this code - for delete filter */
	/** @var string $find_iblock_id */
	$IBLOCK_ID = (int)(isset($_GET['IBLOCK_ID']) && (int)$_GET['IBLOCK_ID'] > 0 ? $_GET['IBLOCK_ID'] : $find_iblock_id);
	unset($lAdmin);
}

$arIBTYPE = false;
if($IBLOCK_ID > 0)
{
	$arIBlock = CIBlock::GetArrayByID($IBLOCK_ID);
	if($arIBlock)
	{
		$arIBTYPE = CIBlockType::GetByIDLang($arIBlock["IBLOCK_TYPE_ID"], LANGUAGE_ID);
		if(!$arIBTYPE)
			$APPLICATION->AuthForm(GetMessage("IBLOCK_BAD_BLOCK_TYPE_ID"));

		$bBadBlock = !CIBlockRights::UserHasRightTo($IBLOCK_ID, $IBLOCK_ID, "iblock_admin_display");
	}
	else
	{
		$bBadBlock = true;
	}
	if($bBadBlock)
		$APPLICATION->AuthForm(GetMessage("IBLOCK_BAD_IBLOCK"));
}
else
{
	$arIBlock = array(
		"ID" => 0,
		"NAME" => "",
		"SECTIONS_NAME" => GetMessage("IBLOCK_SECSEARCH_SECTIONS"),
	);
}

$useParentFilter = ($iblockFix || $IBLOCK_ID > 0);

$APPLICATION->SetTitle(GetMessage("IBLOCK_SECSEARCH_TITLE"));

$entity_id = ($IBLOCK_ID > 0 ? "IBLOCK_".$IBLOCK_ID."_SECTION" : false);

$oSort = new CAdminSorting($sTableID, "NAME", "ASC");
$by = mb_strtoupper($oSort->getField());
$order = mb_strtoupper($oSort->getOrder());
$arOrder = [
	$by => $order,
];
if ($by !== 'ID')
{
	$arOrder['ID'] = 'ASC';
}
$lAdmin = new CAdminList($sTableID, $oSort);

$userFilter = [
	'?NAME' => 'find_section_name',
	'SECTION_ID' => 'find_section_section',
	'ID' => 'find_section_id',
	'>=TIMESTAMP_X' => 'find_section_timestamp_1',
	'<=TIMESTAMP_X' => 'find_section_timestamp_2',
	'MODIFIED_BY' => 'find_section_modified_user_id',
	'>=DATE_CREATE' => 'find_section_date_create_1',
	'<=DATE_CREATE' => 'find_section_date_create_2',
	'CREATED_BY' => 'find_section_created_user_id',
	'ACTIVE' => 'find_section_active',
	'CODE' => 'find_section_code',
	'EXTERNAL_ID' => 'find_section_external_id',
];

$arFilterFields = [
	'find_iblock_id',
	'find_section_id',
	'find_section_timestamp_1',
	'find_section_timestamp_2',
	'find_section_modified_user_id',
	'find_section_date_create_1',
	'find_section_date_create_2',
	'find_section_created_user_id',
	'find_section_name',
	'find_section_active',
	'find_section_section',
	'find_section_code',
	'find_section_external_id',
];

if ($entity_id)
{
	$USER_FIELD_MANAGER->AdminListAddFilterFields($entity_id, $arFilterFields);
}
$currentFilter = $lAdmin->InitFilter($arFilterFields);
foreach ($arFilterFields as $fieldName)
{
	$currentFilter[$fieldName] = (string)($currentFilter[$fieldName] ?? '');
}

$directSectionId = $request->get('find_section_section');
if (is_string($directSectionId))
{
	$currentFilter['find_section_section'] = $directSectionId;
}
unset($directSectionId);

$temporarySectionId = $currentFilter['find_section_section'] !== '' ? (int)$currentFilter['find_section_section'] : '';
$currentFilter['find_section_section'] = $temporarySectionId;
unset($temporarySectionId);

$currentSectionId = (int)$currentFilter['find_section_section'];

############################################

$arFilter = [];
foreach ($userFilter as $index => $fieldName)
{
	if ($currentFilter[$fieldName] !== '')
	{
		$arFilter[$index] = $currentFilter[$fieldName];
	}
}
if ($entity_id)
{
	$USER_FIELD_MANAGER->AdminListAddFilter($entity_id, $arFilter);
}

$arFilter['IBLOCK_ID'] = ($IBLOCK_ID > 0 ? $IBLOCK_ID : -1);

$arFilter['CHECK_PERMISSIONS'] = 'Y';
$arFilter['MIN_PERMISSION'] = 'S';

// list header
$arHeaders = array(
	array(
		"id" => "ID",
		"content" => GetMessage("IBLOCK_SECSEARCH_ID"),
		"sort" => "id",
		"default" => true,
		"align" => "right",
	),
	array(
		"id" => "NAME",
		"content" => GetMessage("IBLOCK_SECSEARCH_NAME"),
		"sort" => "name",
		"default" => true,
	),
	array(
		"id" => "ACTIVE",
		"content" => GetMessage("IBLOCK_SECSEARCH_ACTIVE"),
		"sort" => "active",
		"default" => true,
		"align" => "center",
	),
	array(
		"id" => "SORT",
		"content" => GetMessage("IBLOCK_SECSEARCH_SORT"),
		"sort" => "sort",
		"default" => true,
		"align" => "right",
	),
	array(
		"id" => "CODE",
		"content" => GetMessage("IBLOCK_SECSEARCH_CODE"),
		"sort" => "code",
	),
	array(
		"id" => "XML_ID",
		"content" => GetMessage("IBLOCK_SECSEARCH_XML_ID"),
	),
	array(
		"id" => "ELEMENT_CNT",
		"content" => GetMessage("IBLOCK_SECSEARCH_ELEMENT_CNT"),
		"sort" => "element_cnt",
		"align" => "right",
	),
	array(
		"id" => "SECTION_CNT",
		"content" => GetMessage("IBLOCK_SECSEARCH_SECTION_CNT"),
		"default" => true,
		"align" => "right",
	),
	array(
		"id" => "TIMESTAMP_X",
		"content" => GetMessage("IBLOCK_SECSEARCH_TIMESTAMP"),
		"sort" => "timestamp_x",
	),
	array(
		"id" => "MODIFIED_BY",
		"content" => GetMessage("IBLOCK_SECSEARCH_MODIFIED_BY"),
		"sort" => "modified_by",
	),
	array(
		"id" => "DATE_CREATE",
		"content" => GetMessage("IBLOCK_SECSEARCH_DATE_CREATE"),
		"sort" => "date_create",
	),
	array(
		"id" => "CREATED_BY",
		"content" => GetMessage("IBLOCK_SECSEARCH_CREATED_BY"),
		"sort" => "created_by",
	),
);
if($entity_id)
	$USER_FIELD_MANAGER->AdminListAddHeaders($entity_id, $arHeaders);
$lAdmin->AddHeaders($arHeaders);

$arVisibleColumns = $lAdmin->GetVisibleHeaderColumns();
$arVisibleColumnsMap = array();
foreach($arVisibleColumns as $value)
	$arVisibleColumnsMap[$value] = true;
if (!in_array('ID', $arVisibleColumns))
{
	$arVisibleColumns[] = 'ID';
}
if ($get_xml_id && !in_array('XML_ID', $arVisibleColumns))
{
	$arVisibleColumns[] = 'XML_ID';
}

if(array_key_exists("ELEMENT_CNT", $arVisibleColumnsMap))
{
	$arFilter["CNT_ALL"] = "Y";
	$arFilter["ELEMENT_SUBSECTIONS"] = "N";
	$rsData = CIBlockSection::GetList($arOrder, $arFilter, true, $arVisibleColumns);
}
else
{
	$rsData = CIBlockSection::GetList($arOrder, $arFilter, false, $arVisibleColumns);
}

$rsData = new CAdminResult($rsData, $sTableID);
$rsData->NavStart();
$lAdmin->NavText($rsData->GetNavPrint($arIBlock["SECTIONS_NAME"]));

$strPath = "";
$jsPath  = "";
$nameSeparator = "";
if (!$simpleName && $currentSectionId > 0)
{
	$nameSeparator = "&nbsp;/&nbsp;";
	$nav = CIBlockSection::GetNavChain(
		$IBLOCK_ID,
		$find_section_section,
		[
			'ID',
			'NAME',
		],
		true
	);
	foreach ($nav as $ar_nav)
	{
		$strPath .= htmlspecialcharsbx($ar_nav["NAME"], ENT_QUOTES).$nameSeparator;
		$jsPath .= htmlspecialcharsbx(CUtil::JSEscape($ar_nav["NAME"]), ENT_QUOTES).$nameSeparator;
	}
}

$arUsersCache = [];

while ($arRes = $rsData->Fetch())
{
	$sec_list_url = $extReloadUrl . '&find_section_section=' . $arRes['ID'];

	$row =& $lAdmin->AddRow($arRes['ID'], $arRes);

	if ($entity_id)
	{
		$USER_FIELD_MANAGER->AddUserFields($entity_id, $arRes, $row);
	}

	$row->AddViewField("NAME", '<a href="'.$sec_list_url.'" onclick="'.$lAdmin->ActionRedirect($sec_list_url).'; return false;" title="'.GetMessage("IBLOCK_SECSEARCH_LIST").'">'.htmlspecialcharsbx($arRes['NAME'], ENT_QUOTES).'</a><div style="display:none" id="name_'.$arRes['ID'].'">'.$strPath.htmlspecialcharsbx($arRes['NAME'], ENT_QUOTES).$nameSeparator.'</div>');

	$row->AddCheckField("ACTIVE", false);

	if(array_key_exists("ELEMENT_CNT", $arVisibleColumnsMap))
	{
		$row->AddViewField(
			"ELEMENT_CNT",
			$arRes['ELEMENT_CNT']
				. '('
				. CIBlockSection::GetSectionElementsCount($arRes['ID'], ["CNT_ALL" => "Y"])
				. ')'
		);
	}

	if (array_key_exists("SECTION_CNT", $arVisibleColumnsMap))
	{
		$row->AddViewField(
			"SECTION_CNT",
			'<a href="'.$sec_list_url.'" onclick="'.$lAdmin->ActionRedirect($sec_list_url).'; return false;" title="'.GetMessage("IBLOCK_SECSEARCH_LIST").'">'
				. CIBlockSection::GetCount([
					'IBLOCK_ID' => $IBLOCK_ID,
					'SECTION_ID' => $arRes['ID'],
				])
				. '</a>'
		);
	}

	if (array_key_exists("MODIFIED_BY", $arVisibleColumnsMap) && (int)$arRes['MODIFIED_BY'] > 0)
	{
		$userId = (int)$arRes['MODIFIED_BY'];
		if (!array_key_exists($userId, $arUsersCache))
		{
			$rsUser = CUser::GetByID($userId);
			$arUsersCache[$userId] = $rsUser->Fetch();
		}
		if($arUser = $arUsersCache[$userId])
			$row->AddViewField("MODIFIED_BY", '[<a href="user_edit.php?lang='.LANGUAGE_ID.'&ID='.$userId.'" title="'.GetMessage("IBLOCK_SECSEARCH_USERINFO").'">'.$userId."</a>]&nbsp;(".htmlspecialcharsEx($arUser["LOGIN"]).") ".htmlspecialcharsEx($arUser["NAME"]." ".$arUser["LAST_NAME"]));
	}

	if(array_key_exists("CREATED_BY", $arVisibleColumnsMap) && (int)$arRes['CREATED_BY'] > 0)
	{
		$userId = (int)$arRes['CREATED_BY'];
		if(!array_key_exists($userId, $arUsersCache))
		{
			$rsUser = CUser::GetByID($userId);
			$arUsersCache[$userId] = $rsUser->Fetch();
		}
		if($arUser = $arUsersCache[$userId])
			$row->AddViewField("CREATED_BY", '[<a href="user_edit.php?lang='.LANGUAGE_ID.'&ID='.$userId.'" title="'.GetMessage("IBLOCK_SECSEARCH_USERINFO").'">'.$userId."</a>]&nbsp;(".htmlspecialcharsEx($arUser["LOGIN"]).") ".htmlspecialcharsEx($arUser["NAME"]." ".$arUser["LAST_NAME"]));
	}

	$row->AddActions([
		[
			"DEFAULT" => "Y",
			"TEXT" => GetMessage("IBLOCK_SECSEARCH_SELECT"),
			"ACTION"=>"javascript:SelEl('".($get_xml_id? $arRes['XML_ID']: $arRes['ID'])."', '".htmlspecialcharsbx($jsPath.htmlspecialcharsbx(CUtil::JSEscape($arRes["NAME"]), ENT_QUOTES)).$nameSeparator."')",
		],
	]);
	unset($row);
}

$lAdmin->AddFooter([
	[
		"title" => GetMessage("MAIN_ADMIN_LIST_SELECTED"),
		"value" => $rsData->SelectedRowsCount(),
	],
	[
		"counter" => true,
		"title" => GetMessage("MAIN_ADMIN_LIST_CHECKED"),
		"value" => "0",
	],
]);

if ($m)
{
	$lAdmin->AddGroupActionTable(
		[
			[
				"action" => "SelAll()",
				"value" => "select",
				"type" => "button",
				"name" => GetMessage("IBLOCK_SECSEARCH_SELECT"),
			],
		],
		["disable_action_target" => true]
	);
}

$lAdmin->AddAdminContextMenu([], false);

if($IBLOCK_ID > 0)
{
	$chain = $lAdmin->CreateChain();
	if ($currentSectionId > 0)
	{
		$nav = CIBlockSection::GetNavChain(
			$IBLOCK_ID,
			$currentSectionId,
			[
				'ID',
				'NAME',
			],
			true
		);
		foreach ($nav as $ar_nav)
		{
			if ((int)$ar_nav["ID"] === $currentSectionId)
			{
				$chain->AddItem([
					"TEXT" => $ar_nav["NAME"],
				]);
			}
			else
			{
				$chain->AddItem([
					"TEXT" => $ar_nav["NAME"],
					"LINK" => $extReloadUrl.'&find_section_section='.$ar_nav["ID"],
					"ONCLICK" => $lAdmin->ActionRedirect($extReloadUrl.'&find_section_section='.$ar_nav["ID"]).';return false;',
				]);
			}
		}
	}
	$lAdmin->ShowChain($chain);
}
else
{
	$lAdmin->BeginPrologContent();
	$message = new CAdminMessage([
		"MESSAGE" => GetMessage("IBLOCK_SECSEARCH_CHOOSE_IBLOCK"),
		"TYPE"=>"OK",
	]);
	echo $message->Show();
	$lAdmin->EndPrologContent();
}

$lAdmin->CheckListMode();

/***************************************************************************
				HTML form
****************************************************************************/
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_popup_admin.php");

$chain = new CAdminChain("main_navchain");
$chain->AddItem([
	"TEXT" => htmlspecialcharsbx($arIBlock["NAME"]),
	"LINK" => $extReloadUrl.'&find_section_section=0',
	"ONCLICK" => $lAdmin->ActionRedirect($extReloadUrl.'&find_section_section=0').';return false;',
]);
$chain->Show();
?>
<form method="GET" name="find_section_form" action="<?= $APPLICATION->GetCurPage(); ?>">
<?php
$arFindFields = [];
$defaultFilterFields = [];
if (!$iblockFix)
{
	$arFindFields['IBLOCK_ID'] = GetMessage('IBLOCK_SECSEARCH_IBLOCK');
	$defaultFilterFields[] = 'IBLOCK_ID';
}
if ($useParentFilter)
{
	$arFilterFields['parent'] = GetMessage('IBLOCK_SECSEARCH_PARENT_ID');
}
$arFindFields = array_merge(
	$arFindFields,
	[
		"name" => GetMessage("IBLOCK_SECSEARCH_NAME"),
		"id" => GetMessage("IBLOCK_SECSEARCH_ID"),
		"timestamp_x" => GetMessage("IBLOCK_SECSEARCH_TIMESTAMP"),
		"modified_by" => GetMessage("IBLOCK_SECSEARCH_MODIFIED_BY"),
		"date_create" => GetMessage("IBLOCK_SECSEARCH_DATE_CREATE"),
		"created_by" => GetMessage("IBLOCK_SECSEARCH_CREATED_BY"),
		"code" => GetMessage("IBLOCK_SECSEARCH_CODE"),
		"xml_id" => GetMessage("IBLOCK_SECSEARCH_XML_ID"),
		"active" => GetMessage("IBLOCK_SECSEARCH_ACTIVE"),
	]
);
$defaultFilterFields[] = 'NAME';
$USER_FIELD_MANAGER->AddFindFields($entity_id, $arFindFields);

$oFilter = new CAdminFilter($sTableID."_filter", $arFindFields);
$oFilter->SetDefaultRows($defaultFilterFields);

?>
<script type="text/javascript">
var blockedFilter = false;

function applyFilter(el)
{
	if (blockedFilter)
		return false;
	<?= $sTableID."_filter"; ?>.OnSet('<?= CUtil::JSEscape($sTableID); ?>', '<?= CUtil::JSEscape($reloadUrl); ?>');
	return false;
}
function deleteFilter(el)
{
	if (blockedFilter)
		return false;
	<?= $sTableID."_filter"?>.OnClear('<?= CUtil::JSEscape($sTableID); ?>', '<?= CUtil::JSEscape($reloadUrl); ?>');
	return false;
}


function SelEl(id, name)
{
	var el;
	<?php
	if ('' != $lookup)
	{
		if ('' != $m)
		{
			?>window.opener.<?= $lookup; ?>.AddValue(id);<?php
		}
		else
		{
			?>
			window.opener.<?= $lookup; ?>.AddValue(id);
			window.close();<?php
		}
	}
	else
	{
		if($m)
		{
			?>window.opener.InS<?= md5($n); ?>(id, name);<?php
		}
		else
		{
			?>el = window.opener.document.getElementById('<?= $n; ?>[<?= $k?>]');
		if(!el)
			el = window.opener.document.getElementById('<?= $n; ?>');
		if(el)
			el.value = id;
		el = window.opener.document.getElementById('sp_<?= md5($n); ?>_<?= $k; ?>');
		if(!el)
			el = window.opener.document.getElementById('sp_<?= $n; ?>');
		if (!el)
			el = window.opener.document.getElementById('<?= md5($n); ?>_<?= $k; ?>_link');
		if(!el)
			el = window.opener.document.getElementById('<?= $n; ?>_link');
		if(el)
			el.innerHTML = name;
		window.close();
		<?php
		}
	}
	?>
}

function SelAll()
{
	var frm = BX('form_<?= $sTableID; ?>'),
		e,
		v,
		n,
		i;

	if(frm)
	{
		e = frm.elements['ID[]'];
		if(e && e.nodeName)
		{
			v = e.value;
			n = BX('name_'+v).innerHTML;
			SelEl(v, n);
		}
		else if(e)
		{
			for(i=0;i<e.length;i++)
			{
				if (e[i].checked)
				{
					v = e[i].value;
					n = BX('name_'+v).innerHTML;
					SelEl(v, n);
				}
			}
		}
		window.close();
	}
}

function reloadFilter(el)
{
	var newUrl = '<?= CUtil::JSEscape($reloadUrl); ?>',
		iblockID = 0;

	if (!el)
		return;
	if (el.selectedIndex > 0)
	{
		iblockID = parseInt(el.value, 10);
		if (isNaN(iblockID))
			iblockID = 0;
		if (iblockID > 0 && iblockID !== <?= $IBLOCK_ID; ?>)
		{
			blockedFilter = true;
			newUrl += ('&IBLOCK_ID=' + iblockID) + ('&find_iblock_id=' + iblockID) + '&set_filter=y';
			location.href = newUrl;
		}
	}
}

</script><?php
if ($iblockFix)
{
	?><input type="hidden" name="IBLOCK_ID" value="<?= $IBLOCK_ID; ?>">
	<input type="hidden" name="find_iblock_id" value="<?= $IBLOCK_ID; ?>"><?php
}
$oFilter->Begin();
if (!$iblockFix)
{
	$iblockFilter = array(
		'MIN_PERMISSION' => 'S'
	);

	$hideIblockList = [];
	if ($hideIblockId > 0)
	{
		$hideIblockList[] = $hideIblockId;
	}
	if ($boolDiscount && Loader::includeModule('catalog'))
	{
		$iterator = Catalog\CatalogIblockTable::getList([
			'select'=> [
				'IBLOCK_ID',
			],
			'filter' => [
				'!=PRODUCT_IBLOCK_ID' => 0,
			]
		]);
		while ($row = $iterator->fetch())
		{
			$hideIblockList[] = (int)$row['IBLOCK_ID'];
		}
		unset($row, $iterator);
	}

	if (!empty($hideIblockList))
	{
		$iblockFilter['!ID'] = $hideIblockList;
	}
	unset($hideIblockList);
	?><tr>
		<td><b><?= GetMessage("IBLOCK_SECSEARCH_IBLOCK"); ?></b></td>
		<td><?= GetIBlockDropDownListEx(
				$IBLOCK_ID,
				"find_type",
				"find_iblock_id",
				$iblockFilter,
				'',
				'reloadFilter(this)'
			);
		?></td>
	</tr>
	<?php
}
if ($useParentFilter)
{
?><tr>
	<td><?= GetMessage("IBLOCK_SECSEARCH_PARENT_ID")?>:</td>
	<td>
		<select name="find_section_section" >
			<option value=""><?= GetMessage("IBLOCK_SECSEARCH_ALL_PARENTS"); ?></option>
			<option value="0"<?= ($currentFilter['find_section_section'] === '0'  ? ' selected' : ''); ?>><?= GetMessage("IBLOCK_SECSEARCH_ROOT_PARENT_ID"); ?></option>
			<?php
			$bsections = CIBlockSection::GetTreeList(array("IBLOCK_ID"=>$IBLOCK_ID), array("ID", "NAME", "DEPTH_LEVEL"));
			while($arSection = $bsections->GetNext()):
				?><option value="<?= $arSection["ID"]?>"<?= ($arSection['ID'] === $currentFilter['find_section_section'] ? ' selected' : ''); ?>><?= str_repeat("&nbsp;.&nbsp;", $arSection["DEPTH_LEVEL"]) .  $arSection["NAME"]; ?></option><?php
			endwhile;
			?>
		</select>
	</td>
	</tr><?php
}
?>
	<tr>
		<td><b><?= GetMessage("IBLOCK_SECSEARCH_NAME")?>:</b></td>
		<td><input type="text" name="find_section_name" value="<?= htmlspecialcharsbx($currentFilter['find_section_name']); ?>" size="47">&nbsp;<?=ShowFilterLogicHelp()?></td>
	</tr>

	<tr>
		<td><?= GetMessage("IBLOCK_SECSEARCH_ID")?>:</td>
		<td><input type="text" name="find_section_id" size="47" value="<?= htmlspecialcharsbx($currentFilter['find_section_id']); ?>"></td>
	</tr>
	<tr>
		<td><?= GetMessage("IBLOCK_SECSEARCH_TIMESTAMP").":"?></td>
		<td><?= CalendarPeriod(
			"find_section_timestamp_1",
			htmlspecialcharsbx($currentFilter['find_section_timestamp_1']),
			"find_section_timestamp_2",
			htmlspecialcharsbx($currentFilter['find_section_timestamp_2']),
			"find_section_form",
			"Y"
			);
		?></td>
	</tr>
	<tr>
		<td><?= GetMessage("IBLOCK_SECSEARCH_MODIFIED_BY")?>:</td>
		<td>
			<?= FindUserID(
				"find_section_modified_user_id",
				$currentFilter['find_section_modified_user_id'],
				"",
				"find_section_form",
				"5",
				"",
				" ... ",
				"",
				""
			);?>
		</td>
	</tr>
	<tr>
		<td><?= GetMessage("IBLOCK_SECSEARCH_DATE_CREATE").":"?></td>
		<td><?= CalendarPeriod(
			"find_section_date_create_1",
			htmlspecialcharsbx($currentFilter['find_section_date_create_1']),
			"find_section_date_create_2",
			htmlspecialcharsbx($currentFilter['find_section_date_create_2']),
			"find_section_form",
			'Y'
			);
		?></td>
	</tr>
	<tr>
		<td><?= GetMessage("IBLOCK_SECSEARCH_CREATED_BY")?>:</td>
		<td>
			<?= FindUserID(
				"find_section_created_user_id",
				$currentFilter['find_section_created_user_id'],
				"",
				"find_section_form",
				"5",
				"",
				" ... ",
				"",
				""
			);?>
		</td>
	</tr>
	<tr>
		<td><?= GetMessage("IBLOCK_SECSEARCH_CODE")?>:</td>
		<td><input type="text" name="find_section_code" size="47" value="<?= htmlspecialcharsbx($currentFilter['find_section_code']); ?>"></td>
	</tr>
	<tr>
		<td><?= GetMessage("IBLOCK_SECSEARCH_XML_ID")?>:</td>
		<td><input type="text" name="find_section_external_id" size="47" value="<?= htmlspecialcharsbx($currentFilter['find_section_external_id']); ?>"></td>
	</tr>
	<tr>
		<td><?= GetMessage("IBLOCK_SECSEARCH_ACTIVE")?>:</td>
		<td>
			<select name="find_section_active" >
				<option value=""><?= htmlspecialcharsbx(GetMessage('IBLOCK_ALL')); ?></option>
				<option value="Y"<?= ($currentFilter['find_section_active'] === 'Y' ? ' selected' : ''); ?>><?=htmlspecialcharsbx(GetMessage("IBLOCK_YES"))?></option>
				<option value="N"<?= ($currentFilter['find_section_active'] === 'N' ? ' selected' : ''); ?>><?=htmlspecialcharsbx(GetMessage("IBLOCK_NO"))?></option>
			</select>
		</td>
	</tr>
<?php
$USER_FIELD_MANAGER->AdminListShowFilter($entity_id);
$oFilter->Buttons();
?>
<span class="adm-btn-wrap"><input type="submit"  class="adm-btn" name="set_filter" value="<?= GetMessage("admin_lib_filter_set_butt"); ?>" title="<?= GetMessage("admin_lib_filter_set_butt_title"); ?>" onclick="return applyFilter(this);"></span>
<span class="adm-btn-wrap"><input type="submit"  class="adm-btn" name="del_filter" value="<?= GetMessage("admin_lib_filter_clear_butt"); ?>" title="<?= GetMessage("admin_lib_filter_clear_butt_title"); ?>" onclick="return deleteFilter(this);"></span>
<?php
$oFilter->End();
?>
</form>
<?php
$lAdmin->DisplayList();

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_popup_admin.php");
