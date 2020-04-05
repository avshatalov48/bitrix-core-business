<?
define("ADMIN_MODULE_NAME", "perfmon");
define("PERFMON_STOP", true);
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
/** @global CMain $APPLICATION */
/** @global CDatabase $DB */
/** @global CUser $USER */
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/perfmon/include.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/perfmon/prolog.php");

IncludeModuleLangFile(__FILE__);

$RIGHT = $APPLICATION->GetGroupRight("perfmon");
if ($RIGHT == "D")
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

$bFileMan = CModule::IncludeModule('fileman');

$arErrorCodes = array(
	1 => "E_ERROR",
	2 => "E_WARNING",
	4 => "E_PARSE",
	8 => "E_NOTICE",
	16 => "E_CORE_ERROR",
	32 => "E_CORE_WARNING",
	64 => "E_COMPILE_ERROR",
	128 => "E_COMPILE_WARNING",
	256 => "E_USER_ERROR",
	512 => "E_USER_WARNING",
	1024 => "E_USER_NOTICE",
	2048 => "E_STRICT",
	4096 => "E_RECOVERABLE_ERROR",
	8192 => "E_DEPRECATED",
	16384 => "E_USER_DEPRECATED",
	6143 => "E_ALL",
);

$sTableID = "tbl_perfmon_error_list";
$oSort = new CAdminSorting($sTableID, "ID", "desc");
$lAdmin = new CAdminList($sTableID, $oSort);

if (($arID = $lAdmin->GroupAction()) && $RIGHT >= "W")
{
	switch ($_REQUEST['action'])
	{
		case "delete":
			CPerfomanceError::Delete(array("=ERRFILE" => $_REQUEST["file"], "=ERRLINE" => $_REQUEST["line"]));
	}
}

$FilterArr = array(
	"find",
	"find_type",
	"find_hit_id",
	"find_errno",
	"find_errfile",
	"find_errstr",
);

$lAdmin->InitFilter($FilterArr);

$arFilter = array(
	"=HIT_ID" => ($find != "" && $find_type == "hit_id"? $find: $find_hit_id),
	"=ERRNO" => $find_errno,
	"%ERRFILE" => ($find != "" && $find_type == "file"? $find: $find_errfile),
	"%ERRSTR" => ($find != "" && $find_type == "file"? $find: $find_errstr),
);
foreach ($arFilter as $key => $value)
{
	if (!$value)
		unset($arFilter[$key]);
}

$arHeaders = array();
if ($group !== "Y")
{
	$arHeaders[] = array(
		"id" => "ID",
		"content" => GetMessage("PERFMON_ERR_ID"),
		"align" => "right",
		"sort" => "ID",
		"default" => true,
	);
	$arHeaders[] = array(
		"id" => "HIT_ID",
		"content" => GetMessage("PERFMON_ERR_HIT_ID"),
		"align" => "right",
		"sort" => "HIT_ID",
		"default" => true,
	);
}
$arHeaders[] = array(
	"id" => "ERRNO",
	"content" => GetMessage("PERFMON_ERR_NO"),
	"align" => "right",
	"sort" => "ERRNO",
	"default" => true,
);
$arHeaders[] = array(
	"id" => "ERRFILE",
	"content" => GetMessage("PERFMON_ERR_FILE"),
	"sort" => "ERRFILE",
	"default" => true,
);
$arHeaders[] = array(
	"id" => "ERRLINE",
	"content" => GetMessage("PERFMON_ERR_LINE"),
	"sort" => "ERRLINE",
	"default" => true,
);
$arHeaders[] = array(
	"id" => "ERRSTR",
	"content" => GetMessage("PERFMON_ERR_TEXT"),
	"sort" => "ERRSTR",
	"default" => true,
);
if ($group === "Y")
{
	$arHeaders[] = array(
		"id" => "COUNT",
		"content" => GetMessage("PERFMON_ERR_COUNT"),
		"align" => "right",
		"sort" => "COUNT",
		"default" => true,
	);
}

$lAdmin->AddHeaders($arHeaders);

$arSelectedFields = $lAdmin->GetVisibleHeaderColumns();
if (!is_array($arSelectedFields) || (count($arSelectedFields) < 1))
	$arSelectedFields = array(
		"ID",
		"HIT_ID",
		"ERRNO",
		"ERRFILE",
		"ERRLINE",
		"ERRSTR",
	);

$cData = new CPerfomanceError;
$rsData = $cData->GetList($arSelectedFields, $arFilter, array($by => $order), $group === "Y");

$rsData = new CAdminResult($rsData, $sTableID);
$rsData->NavStart();
$lAdmin->NavText($rsData->GetNavPrint(GetMessage("PERFMON_ERR_PAGE")));

while ($arRes = $rsData->NavNext(true, "f_"))
{
	if ($group == "Y")
		$ID = md5($f_ERRFILE."|".$f_ERRLINE);
	else
		$ID = $f_ID;

	$row = $lAdmin->AddRow($ID, $arRes);

	$row->AddViewField("ERRNO", $arErrorCodes[$f_ERRNO]);

	if ($bFileMan)
		$row->AddViewField("ERRFILE", '<a href="fileman_file_edit.php?lang='.LANGUAGE_ID.'&amp;full_src=Y&amp;site=&amp;set_filter=Y&amp;filter=&amp;path='.urlencode(substr($arRes["ERRFILE"], strlen($_SERVER["DOCUMENT_ROOT"]))).'">'.$f_ERRFILE.'</a>');

	$row->AddViewField("HIT_ID", '<a href="perfmon_hit_list.php?lang='.LANGUAGE_ID.'&amp;set_filter=Y&amp;find_id='.$f_HIT_ID.'">'.$f_HIT_ID.'</a>');

	if ($group == "Y")
	{
		$arActions = array();
		$arActions[] = array(
			"ICON" => "delete",
			"DEFAULT" => false,
			"TEXT" => GetMessage("PERFMON_ERR_ACTION_DELETE"),
			"ACTION" => $lAdmin->ActionDoGroup($ID, "delete", "group=Y&file=".$f_ERRFILE."&line=".$f_ERRLINE),
		);
		$row->AddActions($arActions);
	}
}

$lAdmin->AddFooter(
	array(
		array(
			"title" => GetMessage("MAIN_ADMIN_LIST_SELECTED"),
			"value" => $rsData->SelectedRowsCount(),
		),
	)
);

$aContext = array(
	array(
		"TEXT" => GetMessage("PERFMON_ERR_GROUP"),
		"MENU" => array(
			array(
				"TEXT" => GetMessage("PERFMON_ERR_GROUP_ON"),
				"ACTION" => $lAdmin->ActionDoGroup(0, "", "group=Y&by=COUNT&order=DESC"),
				"ICON" => ($group === "Y"? "checked": ""),
			),
			array(
				"TEXT" => GetMessage("PERFMON_ERR_GROUP_OFF"),
				"ACTION" => $lAdmin->ActionDoGroup(0, "", "group=N"),
				"ICON" => ($group !== "Y"? "checked": ""),
			),
		),
	),
);

$lAdmin->AddAdminContextMenu($aContext);

$lAdmin->CheckListMode();

$APPLICATION->SetTitle(GetMessage("PERFMON_ERR_TITLE"));

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
$oFilter = new CAdminFilter(
	$sTableID."_filter",
	array(
		"find_hit_id" => GetMessage("PERFMON_ERR_HIT_ID"),
		"find_errno" => GetMessage("PERFMON_ERR_NO"),
		"find_errfile" => GetMessage("PERFMON_ERR_FILE"),
		"find_errstr" => GetMessage("PERFMON_ERR_TEXT"),
	)
);
?>

<form name="find_form" method="get" action="<? echo $APPLICATION->GetCurPage(); ?>">
	<? $oFilter->Begin(); ?>
	<tr>
		<td><b><?=GetMessage("PERFMON_ERR_FIND")?>:</b></td>
		<td>
			<input type="text" size="25" name="find" value="<? echo htmlspecialcharsbx($find) ?>"
				title="<?=GetMessage("PERFMON_ERR_FIND")?>">
			<?
			$arr = array(
				"reference" => array(
					GetMessage("PERFMON_ERR_HIT_ID"),
					GetMessage("PERFMON_ERR_FILE"),
					GetMessage("PERFMON_ERR_TEXT"),
				),
				"reference_id" => array(
					"hit_id",
					"file",
					"text",
				)
			);
			echo SelectBoxFromArray("find_type", $arr, $find_type, "", "");
			?>
		</td>
	</tr>
	<tr>
		<td><?=GetMessage("PERFMON_ERR_HIT_ID")?></td>
		<td><input type="text" name="find_hit_id" size="47" value="<? echo htmlspecialcharsbx($find_hit_id) ?>">
		</td>
	</tr>
	<tr>
		<td><?=GetMessage("PERFMON_ERR_NO")?></td>
		<td>
			<div class="adm-list">
			<? foreach ($arErrorCodes as $key => $value): ?>
				<div class="adm-list-item">
					<div class="adm-list-control">
						<input
							type="checkbox"
							id="ck_<? echo $key ?>"
							value="<? echo $key ?>"
							name="find_errno[]" <? if (is_array($find_errno) && in_array($key, $find_errno))
							echo "checked" ?>
						/>
					</div>
					<div class="adm-list-label">
						<label for="ck_<? echo $key ?>"><? echo $value ?></label>
					</div>
				</div>
			<? endforeach ?>
			</div>
		</td>
	</tr>
	<tr>
		<td><?=GetMessage("PERFMON_ERR_FILE")?></td>
		<td><input type="text" name="find_errfile" size="47"
			value="<? echo htmlspecialcharsbx($find_errfile) ?>"></td>
	</tr>
	<tr>
		<td><?=GetMessage("PERFMON_ERR_TEXT")?></td>
		<td><input type="text" name="find_errstr" size="47" value="<? echo htmlspecialcharsbx($find_errstr) ?>">
		</td>
	</tr>
	<?
	$oFilter->Buttons(array(
		"table_id" => $sTableID,
		"url" => $APPLICATION->GetCurPage(),
		"form" => "find_form",
	));
	$oFilter->End();
	?>
</form>

<? $lAdmin->DisplayList(); ?>

<? require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php"); ?>
