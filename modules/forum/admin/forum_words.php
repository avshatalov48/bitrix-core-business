<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
use \Bitrix\Forum;
use \Bitrix\Main;

Main\Loader::includeModule("forum");
Main\Localization\Loc::loadMessages(__FILE__);

$forumPermissions = $APPLICATION->GetGroupRight("forum");
if ($forumPermissions == "D")
{
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
}

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/forum/prolog.php");
$request = Main\Context::getCurrent()->getRequest();
$sTableID = "tbl_filter";
$oSort = new CAdminSorting($sTableID, "ID", "asc");
$lAdmin = new CAdminList($sTableID, $oSort);
$filter = $lAdmin->InitFilter(array("ID", "find_pattern", "find_type", "USE_IT", "PATTERN_CREATE"));

/*******************************************************************/
$DICTIONARY_ID = intval($request->get("DICTIONARY_ID"));
if ($DICTIONARY_ID <= 0)
{
	$lAdmin->AddFilterError(GetMessage("FLT_NOT_DICTIONARY"));
}
$arFilter = array(
	"DICTIONARY_ID" => $DICTIONARY_ID
);
if (is_array($filter))
{
	if ($filter["find_pattern"] <> '' && $filter["find_type"] <> '')
	{
		$arFilter[$filter["find_type"]] = "%".$filter["find_pattern"]."%";
	}
	if (in_array($filter["USE_IT"], ["Y", "N"]))
	{
		$arFilter["USE_IT"] = $filter["USE_IT"] == "Y" ? "Y" : "N";
	}
	if (in_array($filter["PATTERN_CREATE"], ["WORDS", "TRNSL", "PTTRN"]))
	{
		$arFilter["PATTERN_CREATE"] = $filter["PATTERN_CREATE"];
	}
}
/*******************************************************************/
if ($lAdmin->EditAction() && $forumPermissions >= "W")
{
	foreach ($request->getPost("FIELDS") as $ID => $fields)
	{
		$ID = intval($ID);
		if (!$lAdmin->IsUpdated($ID))
		{
			continue;
		}
		try
		{
			$result = Forum\BadWords\Filter::getById($ID)->update($fields);
		}
		catch (Exception $e)
		{
			$result = new Main\Result();
			$result->addError(new Main\Error($e->getMessage()));
		}

		if (!$result->isSuccess())
		{
			$lAdmin->AddUpdateError(implode("", $result->getErrorMessages()));
		}
	}
}
/*******************************************************************/
if (($arID = $lAdmin->GroupAction()) && check_bitrix_sessid() && $forumPermissions >= "W")
{
	if ($request->getPost("action_target") == "selected")
	{
		$dbRes = Forum\BadWords\FilterTable::getList([
			"select" => ["ID"],
			"filter" => $arFilter,
			"order" => [
				$by => $order
			]
		]);

		$arID = [];
		while ($arRes = $dbRes->Fetch())
		{
			$arID[] = $arRes["ID"];
		}
	}

	foreach($arID as $ID)
	{
		try
		{
			$word = Forum\BadWords\Filter::getById($ID);
			if ($request->get("action_button") === "delete")
			{
				$word->delete();
			}
			else
			{
				$word->generatePattern();
			}
		}
		catch(Exception $e)
		{

		}
	}
	if ($request->get("sessid") || $request->get("action_button"))
	{
		LocalRedirect($APPLICATION->GetCurPageParam("", ["sessid", "action_button", "ID"]));
	}
}
$dbRes = Forum\BadWords\FilterTable::getList([
	"select" => ["*"],
	"filter" => $arFilter,
	"order" => [
		$by => $order
	]
]);
$rsData = new CAdminResult($dbRes, $sTableID);
$rsData->NavStart();
$lAdmin->NavText($rsData->GetNavPrint(GetMessage("FLT_TITLE_NAV")));
$lAdmin->AddHeaders([
	["id" => "ID", "content" => "ID", "sort" => "ID", "default" => true],
	["id" => "USE_IT", "content" => GetMessage("FLT_USE_IT"), "sort" => "USE_IT", "default" => true],
	["id" => "PATTERN_CREATE", "content" => GetMessage("FLT_PATTERN_CREATE"), "sort" => "PATTERN_CREATE", "default" => true],
	["id" => "WORDS", "content" => GetMessage("FLT_WORDS"), "sort" => "WORDS", "default" => true],
	["id" => "PATTERN", "content" => GetMessage("FLT_PATTERN"), "sort" => "PATTERN", "default" => false],
	["id" => "REPLACEMENT", "content" => GetMessage("FLT_REPLACEMENT"), "sort" => "REPLACEMENT", "default" => true],
	["id" => "DESCRIPTION", "content" => GetMessage("FLT_DESCRIPTION"), "sort" => "DESCRIPTION", "default" => true]
]);
/*******************************************************************/
while ($res = $rsData->NavNext(true, "t_"))
{
	$row =& $lAdmin->AddRow($res["ID"], $res);
	$row->bReadOnly = $forumPermissions < "W";
	$row->AddViewField("ID", '<a title="'.GetMessage("FLT_ACT_EDIT").'" href="'."forum_words_edit.php?DICTIONARY_ID=".$res["DICTIONARY_ID"]."&ID=".$res["ID"]."&amp;lang=".LANG.'">'.$res["ID"].'</a>');
	$row->AddInputField("WORDS", array("size"=>"20"));
	$row->AddViewField("PATTERN", htmlspecialcharsbx($res["PATTERN"]));
	$row->AddInputField("REPLACEMENT", array("maxlength"=>"255", "size"=>"10%"));
	$row->AddInputField("DESCRIPTION", array("size"=>"80%"));
	$row->AddCheckField("USE_IT", array("Y"=>GetMessage("FLT_ACT_USE_IT_Y"), "N"=>GetMessage("FLT_ACT_USE_IT_N")));
	$row->AddSelectField("PATTERN_CREATE", array("WORDS"=>GetMessage("FLT_FLT_WORDS"), "TRNSL"=>GetMessage("FLT_FLT_TRNSL"), "PTTRN"=>GetMessage("FLT_FLT_PTTRN")));
	$row->AddActions([
		[
			"ICON" => "edit",
			"TEXT" => GetMessage("FLT_ACT_EDIT"),
			"ACTION" => $lAdmin->ActionRedirect("forum_words_edit.php?DICTIONARY_ID=" . $res["DICTIONARY_ID"] . "&lang=" . LANG . "&ID=" . $res["ID"] . GetFilterParams("filter_", false) . ""),
			"DEFAULT" => true
		],
		["SEPARATOR" => true],
		[
			"ICON" => "delete",
			"TEXT" => GetMessage("FLT_ACT_DEL"),
			"ACTION" => "if(confirm('" . GetMessage("FLT_ACT_DEL_CONFIRM") . "')) " . $lAdmin->ActionDoGroup($res["ID"], "delete", "DICTIONARY_ID=" . $res["DICTIONARY_ID"] . "&lang=" . LANG)
		]
	]);
}
/*******************************************************************/
$lAdmin->AddFooter([
	["title" => GetMessage("MAIN_ADMIN_LIST_SELECTED"), "value" => $rsData->SelectedRowsCount()],
	["counter" => true, "title" => GetMessage("MAIN_ADMIN_LIST_CHECKED"), "value" => "0"]
]);

$dbRes = Forum\BadWords\DictionaryTable::getList(["select" => ["*"], "filter" => ["TYPE"=>"T"]]);
$option = "";
$active = Main\Config\Option::get("forum", "FILTER_DICT_T", '', SITE_ID);
while ($res = $dbRes->fetch())
{
	$option .= "<option value=\"{$res["ID"]}\" ".($res["ID"] == $active ? " selected " : "").">{$res["TITLE"]}</option>";
}

$lAdmin->AddGroupActionTable(
	array(
		"delete" => GetMessage("MAIN_ADMIN_LIST_DELETE"),
		"generate" => GetMessage("FLT_ACT_GEN"),
		"copy2" => array(
			"type" => "html",
			"value" => GetMessage("FLT_ACT_GEN_CONFIRM")
		),
		"copy1" => array(
			"type" => "html",
			"value" => "<select name='DICTIONARY_ID_T'>".$option."</select>"
		),
		)
	);
if (($forumPermissions >= "W") && $arFilter["DICTIONARY_ID"] > 0)
{
	$aContext = array(
		array(
			"TEXT" => GetMessage("FLT_ACT_ADD"),
			"LINK" => "forum_words_edit.php?DICTIONARY_ID=".$arFilter["DICTIONARY_ID"]."&lang=".LANG,
			"TITLE" => GetMessage("FLT_ACT_ADD"),
			"ICON" => "btn_new")
	);
	$lAdmin->AddAdminContextMenu($aContext);
}
/*******************************************************************/
	$lAdmin->CheckListMode();
/*******************************************************************/

$APPLICATION->SetTitle(GetMessage("FLT_TITLE"));
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
$oFilter = new CAdminFilter(
	$sTableID."_filter",
	array(
		GetMessage("FLT_USE_IT"),
		GetMessage("FLT_PATTERN_CREATE"),
	)
);
?><form name="form1" method="get" action="<?=$APPLICATION->GetCurPage()?>?">
	<input type="hidden" name="DICTIONARY_ID" value="<?=$arFilter["DICTIONARY_ID"]?>">
	<?$oFilter->Begin();?>
	<tr>
		<td><b><?=GetMessage("MAIN_FIND")?>:</b></td>
		<td>
			<input type="text" size="47" name="find_pattern" value="<?=htmlspecialcharsbx(isset($filter["find_pattern"]) ? $filter["find_pattern"] : "")?>" title="<?=GetMessage("MAIN_FIND_TITLE")?>">
		<?
		$arr = array(
			"reference" => array(
				GetMessage("FLT_WORDS"),
				GetMessage("FLT_PATTERN"),
				GetMessage("FLT_REPLACEMENT"),
				GetMessage("FLT_DESCRIPTION"),
			),
			"reference_id" => array(
				"WORDS",
				"PATTERN",
				"REPLACEMENT",
				"DESCRIPTION",
			)
		);
		echo SelectBoxFromArray("find_type", $arr, (isset($filter["find_type"]) ? $filter["find_type"] : ""), "", "");?>
		</td>
	</tr>
	<tr>
		<td><?=GetMessage("FLT_USE_IT")?>:</td>
		<td><?=SelectBoxFromArray("USE_IT",
				[
					"reference" => ["", GetMessage("FLT_ACT_USE_IT_Y"), GetMessage("FLT_ACT_USE_IT_N")],
				    "reference_id" => ["all", "Y", "N"]
				], isset($filter["USE_IT"]) ? $filter["USE_IT"] : "all")?>
		</td>
	</tr>
	<tr>
		<td><?=GetMessage("FLT_PATTERN_CREATE")?>:</td>
		<td><?=SelectBoxFromArray(
				"PATTERN_CREATE",
				["REFERENCE" => ["", GetMessage("FLT_FLT_WORDS"), GetMessage("FLT_FLT_TRNSL"), GetMessage("FLT_FLT_PTTRN")], "REFERENCE_ID" => ["all", "WORDS", "TRNSL", "PTTRN"]],
				isset($filter["PATTERN_CREATE"]) ? $filter["PATTERN_CREATE"] : "all"
			)?>
		</td>
	</tr>
	<?
	$oFilter->Buttons([
		"table_id" => $sTableID,
		"url" => $APPLICATION->GetCurPage()."?DICTIONARY_ID=".$arFilter["DICTIONARY_ID"]."&lang=".LANG,
		"form" => "find_form"
	]);
	$oFilter->End();
?></form><?
$lAdmin->DisplayList();
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
?>