<?
/** @global CMain $APPLICATION */
/** @global CUser $USER */
/** @global CDatabase $DB */
/** @global CUserTypeManager $USER_FIELD_MANAGER */
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
CModule::IncludeModule("iblock");
IncludeModuleLangFile(__FILE__);

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
	/** @var string $filter_iblock_id */
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

$useParentFilter = $iblockFix;

$APPLICATION->SetTitle(GetMessage("IBLOCK_SECSEARCH_TITLE"));

$entity_id = ($IBLOCK_ID > 0 ? "IBLOCK_".$IBLOCK_ID."_SECTION" : false);

$oSort = new CAdminSorting($sTableID, "NAME", "ASC");
if (!isset($by))
	$by = 'NAME';
if (!isset($order))
	$order = 'ASC';
$arOrder = (strtoupper($by) === "ID"? array($by => $order): array($by => $order, "ID" => "ASC"));
$lAdmin = new CAdminList($sTableID, $oSort);

$arFilterFields = array(
	"find_iblock_id",
	"find_section_id",
	"find_section_timestamp_1",
	"find_section_timestamp_2",
	"find_section_modified_by",
	"find_section_date_create_1",
	"find_section_date_create_2",
	"find_section_created_by",
	"find_section_name",
	"find_section_active",
	"find_section_section",
	"find_section_code",
	"find_section_external_id"
);

if($entity_id)
	$USER_FIELD_MANAGER->AdminListAddFilterFields($entity_id, $arFilterFields);

$section_id = strlen($find_section_section) > 0? intval($find_section_section): "";
$lAdmin->InitFilter($arFilterFields);
$find_section_section = $section_id;

############################################

$arFilter = array(
	"?NAME"		=> $find_section_name,
	"SECTION_ID"	=> $find_section_section,
	"ID"		=> $find_section_id,
	">=TIMESTAMP_X"	=> $find_section_timestamp_1,
	"<=TIMESTAMP_X"	=> $find_section_timestamp_2,
	"MODIFIED_BY"	=> $find_section_modified_user_id? $find_section_modified_user_id: $find_section_modified_by,
	">=DATE_CREATE"	=> $find_section_date_create_1,
	"<=DATE_CREATE"	=> $find_section_date_create_2,
	"CREATED_BY"	=> $find_section_created_user_id? $find_section_created_user_id: $find_section_created_by,
	"ACTIVE"	=> $find_section_active,
	"CODE"		=> $find_section_code,
	"EXTERNAL_ID"	=> $find_section_external_id,
);
if($entity_id)
	$USER_FIELD_MANAGER->AdminListAddFilter($entity_id, $arFilter);

if($find_section_section === "" || !$useParentFilter)
	unset($arFilter["SECTION_ID"]);

if($IBLOCK_ID > 0)
	$arFilter["IBLOCK_ID"] = $IBLOCK_ID;
else
	$arFilter["IBLOCK_ID"] = -1;

$arFilter["CHECK_PERMISSIONS"]="Y";

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
if (!$simpleName && intval($find_section_section) > 0)
{
	$nameSeparator = "&nbsp;/&nbsp;";
	$nav = CIBlockSection::GetNavChain($IBLOCK_ID, $find_section_section);
	while($ar_nav = $nav->GetNext())
	{
		$strPath .= htmlspecialcharsbx($ar_nav["~NAME"], ENT_QUOTES).$nameSeparator;
		$jsPath .= htmlspecialcharsbx(CUtil::JSEscape($ar_nav["~NAME"]), ENT_QUOTES).$nameSeparator;
	}
}

$arUsersCache = array();

while($arRes = $rsData->NavNext(true, "f_"))
{
	$sec_list_url = $extReloadUrl.'&find_section_section='.$f_ID;

	$row =& $lAdmin->AddRow($f_ID, $arRes);

	if($entity_id)
		$USER_FIELD_MANAGER->AddUserFields($entity_id, $arRes, $row);

	$row->AddViewField("NAME", '<a href="'.$sec_list_url.'" onclick="'.$lAdmin->ActionRedirect($sec_list_url).'; return false;" title="'.GetMessage("IBLOCK_SECSEARCH_LIST").'">'.$f_NAME.'</a><div style="display:none" id="name_'.$f_ID.'">'.$strPath.$f_NAME.$nameSeparator.'</div>');

	$row->AddCheckField("ACTIVE", false);

	if(array_key_exists("ELEMENT_CNT", $arVisibleColumnsMap))
		$row->AddViewField("ELEMENT_CNT", $f_ELEMENT_CNT.'('.IntVal(CIBlockSection::GetSectionElementsCount($f_ID, Array("CNT_ALL"=>"Y"))).')');

	if(array_key_exists("SECTION_CNT", $arVisibleColumnsMap))
	{
		$arFilter = Array("IBLOCK_ID"=>$IBLOCK_ID, "SECTION_ID"=>$f_ID);
		$row->AddViewField("SECTION_CNT", '<a href="'.$sec_list_url.'" onclick="'.$lAdmin->ActionRedirect($sec_list_url).'; return false;" title="'.GetMessage("IBLOCK_SECSEARCH_LIST").'">'.IntVal(CIBlockSection::GetCount($arFilter)).'</a>');
	}

	if(array_key_exists("MODIFIED_BY", $arVisibleColumnsMap) && intval($f_MODIFIED_BY) > 0)
	{
		if(!array_key_exists($f_MODIFIED_BY, $arUsersCache))
		{
			$rsUser = CUser::GetByID($f_MODIFIED_BY);
			$arUsersCache[$f_MODIFIED_BY] = $rsUser->Fetch();
		}
		if($arUser = $arUsersCache[$f_MODIFIED_BY])
			$row->AddViewField("MODIFIED_BY", '[<a href="user_edit.php?lang='.LANGUAGE_ID.'&ID='.$f_MODIFIED_BY.'" title="'.GetMessage("IBLOCK_SECSEARCH_USERINFO").'">'.$f_MODIFIED_BY."</a>]&nbsp;(".htmlspecialcharsEx($arUser["LOGIN"]).") ".htmlspecialcharsEx($arUser["NAME"]." ".$arUser["LAST_NAME"]));
	}

	if(array_key_exists("CREATED_BY", $arVisibleColumnsMap) && intval($f_CREATED_BY) > 0)
	{
		if(!array_key_exists($f_CREATED_BY, $arUsersCache))
		{
			$rsUser = CUser::GetByID($f_CREATED_BY);
			$arUsersCache[$f_CREATED_BY] = $rsUser->Fetch();
		}
		if($arUser = $arUsersCache[$f_MODIFIED_BY])
			$row->AddViewField("CREATED_BY", '[<a href="user_edit.php?lang='.LANGUAGE_ID.'&ID='.$f_CREATED_BY.'" title="'.GetMessage("IBLOCK_SECSEARCH_USERINFO").'">'.$f_CREATED_BY."</a>]&nbsp;(".htmlspecialcharsEx($arUser["LOGIN"]).") ".htmlspecialcharsEx($arUser["NAME"]." ".$arUser["LAST_NAME"]));
	}

	$row->AddActions(array(
		array(
			"DEFAULT" => "Y",
			"TEXT" => GetMessage("IBLOCK_SECSEARCH_SELECT"),
			"ACTION"=>"javascript:SelEl('".($get_xml_id? $f_XML_ID: $f_ID)."', '".htmlspecialcharsbx($jsPath.htmlspecialcharsbx(CUtil::JSEscape($arRes["NAME"]), ENT_QUOTES)).$nameSeparator."')",
		),
	));
}

$lAdmin->AddFooter(
	array(
		array("title"=>GetMessage("MAIN_ADMIN_LIST_SELECTED"), "value"=>$rsData->SelectedRowsCount()),
		array("counter"=>true, "title"=>GetMessage("MAIN_ADMIN_LIST_CHECKED"), "value"=>"0"),
	)
);

if($m)
{
	$lAdmin->AddGroupActionTable(array(
		array(
			"action" => "SelAll()",
			"value" => "select",
			"type" => "button",
			"name" => GetMessage("IBLOCK_SECSEARCH_SELECT"),
			)
	), array("disable_action_target"=>true));
}

$lAdmin->AddAdminContextMenu(array(), false);

if($IBLOCK_ID > 0)
{
	$chain = $lAdmin->CreateChain();
	if(intval($find_section_section)>0)
	{
		$nav = CIBlockSection::GetNavChain($IBLOCK_ID, $find_section_section);
		while($ar_nav = $nav->GetNext())
		{
			if($find_section_section==$ar_nav["ID"])
			{
				$chain->AddItem(array(
					"TEXT" => $ar_nav["NAME"],
				));
			}
			else
			{
				$chain->AddItem(array(
					"TEXT" => $ar_nav["NAME"],
					"LINK" => $extReloadUrl.'&find_section_section='.$ar_nav["ID"],
					"ONCLICK" => $lAdmin->ActionRedirect($extReloadUrl.'&find_section_section='.$ar_nav["ID"]).';return false;',
				));
			}
		}
	}
	$lAdmin->ShowChain($chain);
}
else
{
	$lAdmin->BeginPrologContent();
	$message = new CAdminMessage(array("MESSAGE"=>GetMessage("IBLOCK_SECSEARCH_CHOOSE_IBLOCK"), "TYPE"=>"OK"));
	echo $message->Show();
	$lAdmin->EndPrologContent();
}

$lAdmin->CheckListMode();

/***************************************************************************
				HTML form
****************************************************************************/
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_popup_admin.php");

$chain = new CAdminChain("main_navchain");
$chain->AddItem(array(
	"TEXT" => htmlspecialcharsbx($arIBlock["NAME"]),
	"LINK" => $extReloadUrl.'&find_section_section=0',
	"ONCLICK" => $lAdmin->ActionRedirect($extReloadUrl.'&find_section_section=0').';return false;',
));
$chain->Show();
?>
<form method="GET" name="find_section_form" action="<?echo $APPLICATION->GetCurPage()?>">
<?
$arFindFields = array();
if (!$iblockFix)
	$arFindFields['IBLOCK_ID'] = GetMessage('IBLOCK_SECSEARCH_IBLOCK');
if ($useParentFilter)
	$arFilterFields['parent'] = GetMessage('IBLOCK_SECSEARCH_PARENT_ID');
$arFindFields = array_merge(
	$arFindFields,
	array(
		"name" => GetMessage("IBLOCK_SECSEARCH_NAME"),
		"id" => GetMessage("IBLOCK_SECSEARCH_ID"),
		"timestamp_x" => GetMessage("IBLOCK_SECSEARCH_TIMESTAMP"),
		"modified_by" => GetMessage("IBLOCK_SECSEARCH_MODIFIED_BY"),
		"date_create" => GetMessage("IBLOCK_SECSEARCH_DATE_CREATE"),
		"created_by" => GetMessage("IBLOCK_SECSEARCH_CREATED_BY"),
		"code" => GetMessage("IBLOCK_SECSEARCH_CODE"),
		"xml_id" => GetMessage("IBLOCK_SECSEARCH_XML_ID"),
		"active" => GetMessage("IBLOCK_SECSEARCH_ACTIVE"),
	)
);
$USER_FIELD_MANAGER->AddFindFields($entity_id, $arFindFields);

$oFilter = new CAdminFilter($sTableID."_filter", $arFindFields);

?>
<script type="text/javascript">
var blockedFilter = false;

function applyFilter(el)
{
	if (blockedFilter)
		return false;
	<?=$sTableID."_filter";?>.OnSet('<?=CUtil::JSEscape($sTableID); ?>', '<?=CUtil::JSEscape($reloadUrl); ?>');
	return false;
}
function deleteFilter(el)
{
	if (blockedFilter)
		return false;
	<?=$sTableID."_filter"?>.OnClear('<?=CUtil::JSEscape($sTableID); ?>', '<?=CUtil::JSEscape($reloadUrl)?>');
	return false;
}


function SelEl(id, name)
{
	var el;
	<?
	if ('' != $lookup)
	{
		if ('' != $m)
		{
			?>window.opener.<? echo $lookup; ?>.AddValue(id);<?
		}
		else
		{
			?>
			window.opener.<? echo $lookup; ?>.AddValue(id);
			window.close();<?
		}
	}
	else
	{
		if($m)
		{
			?>window.opener.InS<?echo md5($n)?>(id, name);<?
		}
		else
		{
			?>el = window.opener.document.getElementById('<?echo $n?>[<?echo $k?>]');
		if(!el)
			el = window.opener.document.getElementById('<?echo $n?>');
		if(el)
			el.value = id;
		el = window.opener.document.getElementById('sp_<?echo md5($n)?>_<?echo $k?>');
		if(!el)
			el = window.opener.document.getElementById('sp_<?echo $n?>');
		if (!el)
			el = window.opener.document.getElementById('<?echo md5($n)?>_<?echo $k?>_link');
		if(!el)
			el = window.opener.document.getElementById('<?echo $n?>_link');
		if(el)
			el.innerHTML = name;
		window.close();
		<?
		}
	}
	?>
}

function SelAll()
{
	var frm = BX('form_<?echo $sTableID?>'),
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
	var newUrl = '<? echo CUtil::JSEscape($reloadUrl); ?>',
		iblockID = 0;

	if (!el)
		return;
	if (el.selectedIndex > 0)
	{
		iblockID = parseInt(el.value, 10);
		if (isNaN(iblockID))
			iblockID = 0;
		if (iblockID > 0 && iblockID != <? echo $IBLOCK_ID; ?>)
		{
			blockedFilter = true;
			newUrl += ('&IBLOCK_ID=' + iblockID) + ('&find_iblock_id=' + iblockID) + '&set_filter=y';
			location.href = newUrl;
		}
	}
}

</script><?
if ($iblockFix)
{
	?><input type="hidden" name="IBLOCK_ID" value="<?=$IBLOCK_ID; ?>">
	<input type="hidden" name="find_iblock_id" value="<?=$IBLOCK_ID; ?>"><?
}
$oFilter->Begin();
if (!$iblockFix)
{
	$iblockFilter = array(
		'MIN_PERMISSION' => 'S'
	);
	if ($hideIblockId > 0)
		$iblockFilter['!ID'] = $hideIblockId;
	?><tr>
		<td><b><?echo GetMessage("IBLOCK_SECSEARCH_IBLOCK")?></b></td>
		<td><?echo GetIBlockDropDownListEx(
				$IBLOCK_ID,
				"find_type",
				"find_iblock_id",
				$iblockFilter,
				'',
				'reloadFilter(this)'
			);?></td>
	</tr>
	<?
}
if ($useParentFilter)
{
?><tr>
	<td><?echo GetMessage("IBLOCK_SECSEARCH_PARENT_ID")?>:</td>
	<td>
		<select name="find_section_section" >
			<option value=""><?echo GetMessage("IBLOCK_SECSEARCH_ALL_PARENTS")?></option>
			<option value="0"<?if($find_section_section=="0")echo" selected"?>><?echo GetMessage("IBLOCK_SECSEARCH_ROOT_PARENT_ID")?></option>
			<?
			$bsections = CIBlockSection::GetTreeList(array("IBLOCK_ID"=>$IBLOCK_ID), array("ID", "NAME", "DEPTH_LEVEL"));
			while($arSection = $bsections->GetNext()):
				?><option value="<?echo $arSection["ID"]?>"<?if($arSection["ID"]==$find_section_section)echo " selected"?>><?echo str_repeat("&nbsp;.&nbsp;", $arSection["DEPTH_LEVEL"])?><?echo $arSection["NAME"]?></option><?
			endwhile;
			?>
		</select>
	</td>
	</tr><?
}
?>
	<tr>
		<td><b><?echo GetMessage("IBLOCK_SECSEARCH_NAME")?>:</b></td>
		<td><input type="text" name="find_section_name" value="<?echo htmlspecialcharsbx($find_section_name)?>" size="47">&nbsp;<?=ShowFilterLogicHelp()?></td>
	</tr>

	<tr>
		<td><?echo GetMessage("IBLOCK_SECSEARCH_ID")?>:</td>
		<td><input type="text" name="find_section_id" size="47" value="<?echo htmlspecialcharsbx($find_section_id)?>"></td>
	</tr>
	<tr>
		<td><?echo GetMessage("IBLOCK_SECSEARCH_TIMESTAMP").":"?></td>
		<td><?echo CalendarPeriod("find_section_timestamp_1", htmlspecialcharsbx($find_section_timestamp_1), "find_section_timestamp_2", htmlspecialcharsbx($find_section_timestamp_2), "find_section_form","Y")?></td>
	</tr>
	<tr>
		<td><?echo GetMessage("IBLOCK_SECSEARCH_MODIFIED_BY")?>:</td>
		<td>
			<?echo FindUserID(
				/*$tag_name=*/"find_section_modified_user_id",
				/*$tag_value=*/$find_section_modified_by,
				/*$user_name=*/"",
				/*$form_name=*/"find_section_form",
				/*$tag_size=*/"5",
				/*$tag_maxlength=*/"",
				/*$button_value=*/" ... ",
				/*$tag_class=*/"",
				/*$button_class=*/""
			);?>
		</td>
	</tr>
	<tr>
		<td><?echo GetMessage("IBLOCK_SECSEARCH_DATE_CREATE").":"?></td>
		<td><?echo CalendarPeriod("find_section_date_create_1", htmlspecialcharsbx($find_section_date_create_1), "find_section_date_create_2", htmlspecialcharsbx($find_section_date_create_2), "find_section_form")?></td>
	</tr>
	<tr>
		<td><?echo GetMessage("IBLOCK_SECSEARCH_CREATED_BY")?>:</td>
		<td>
			<?echo FindUserID(
				/*$tag_name=*/"find_section_created_user_id",
				/*$tag_value=*/$find_section_created_by,
				/*$user_name=*/"",
				/*$form_name=*/"find_section_form",
				/*$tag_size=*/"5",
				/*$tag_maxlength=*/"",
				/*$button_value=*/" ... ",
				/*$tag_class=*/"",
				/*$button_class=*/""
			);?>
		</td>
	</tr>
	<tr>
		<td><?echo GetMessage("IBLOCK_SECSEARCH_CODE")?>:</td>
		<td><input type="text" name="find_section_code" size="47" value="<?echo htmlspecialcharsbx($find_section_code)?>"></td>
	</tr>
	<tr>
		<td><?echo GetMessage("IBLOCK_SECSEARCH_XML_ID")?>:</td>
		<td><input type="text" name="find_section_external_id" size="47" value="<?echo htmlspecialcharsbx($find_section_external_id)?>"></td>
	</tr>
	<tr>
		<td><?echo GetMessage("IBLOCK_SECSEARCH_ACTIVE")?>:</td>
		<td>
			<select name="find_section_active" >
				<option value=""><?=htmlspecialcharsbx(GetMessage('IBLOCK_ALL'))?></option>
				<option value="Y"<?if($find_section_active=="Y")echo " selected"?>><?=htmlspecialcharsbx(GetMessage("IBLOCK_YES"))?></option>
				<option value="N"<?if($find_section_active=="N")echo " selected"?>><?=htmlspecialcharsbx(GetMessage("IBLOCK_NO"))?></option>
			</select>
		</td>
	</tr>
<?
$USER_FIELD_MANAGER->AdminListShowFilter($entity_id);
$oFilter->Buttons();
?>
<span class="adm-btn-wrap"><input type="submit"  class="adm-btn" name="set_filter" value="<? echo GetMessage("admin_lib_filter_set_butt"); ?>" title="<? echo GetMessage("admin_lib_filter_set_butt_title"); ?>" onclick="return applyFilter(this);"></span>
<span class="adm-btn-wrap"><input type="submit"  class="adm-btn" name="del_filter" value="<? echo GetMessage("admin_lib_filter_clear_butt"); ?>" title="<? echo GetMessage("admin_lib_filter_clear_butt_title"); ?>" onclick="return deleteFilter(this);"></span>
<?
$oFilter->End();
?>
</form>
<?
$lAdmin->DisplayList();

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_popup_admin.php");