<?
define("ADMIN_MODULE_NAME", "clouds");

/*.require_module 'standard';.*/
/*.require_module 'pcre';.*/
/*.require_module 'bitrix_main_include_prolog_admin_before';.*/
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

if(!$USER->CanDoOperation("clouds_browse"))
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

/*.require_module 'bitrix_clouds_include';.*/
if(!CModule::IncludeModule('clouds'))
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

IncludeModuleLangFile(__FILE__);

$bucket_id = 0;
$arBuckets = array();
foreach(CCloudStorageBucket::GetAllBuckets() as $arBucket)
{
	if($arBucket["ACTIVE"] == "Y")
	{
		$bucket_id = $arBucket["ID"];
		$arBuckets[$bucket_id] = $arBucket["BUCKET"];
	}
}

$message = /*.(CAdminMessage).*/null;
$sTableID = "tbl_clouds_file_search";
$lAdmin = new CAdminList($sTableID);

$lAdmin->InitFilter(array("bucket", "path"));
$path = isset($_GET["path"])? $_GET["path"]: $path;
$path = preg_replace("#[\\\\\\/]+#", "/", "/".$path."/");
$n = preg_replace("/[^a-zA-Z0-9_:\\[\\]]/", "", $_GET["n"]);
if(intval($bucket) <= 0 && count($arBuckets) == 1)
	$bucket = $bucket_id;

//TODO when there is only one cloud storage there is no need for filter or at least we can preset it
$arHeaders = array(
	array(
		"id" => "FILE_NAME",
		"content" => GetMessage("CLO_STORAGE_SEARCH_NAME"),
		"default" => true,
	),
	array(
		"id" => "FILE_SIZE",
		"content" => GetMessage("CLO_STORAGE_SEARCH_SIZE"),
		"align" => "right",
		"default" => true,
	),
);

$lAdmin->AddHeaders($arHeaders);

$arData = /*.(array[int][string]string).*/array();

$obBucket = new CCloudStorageBucket($bucket);
if($obBucket->Init() && $_GET["file"]!=="y")
{
	$arFiles = $obBucket->ListFiles($path);

	if($path != "/")
		$arData[] = array("ID" => "D..", "TYPE" => "dir", "NAME" => "..", "SIZE" => "");
	if(is_array($arFiles))
	{
		foreach($arFiles["dir"] as $i => $dir)
			$arData[] = array("ID" => "D".$dir, "TYPE" => "dir", "NAME" => $dir, "SIZE" => '');
		foreach($arFiles["file"] as $i => $file)
			$arData[] = array("ID" => "F".$file, "TYPE" => "file", "NAME" => $file, "SIZE" => $arFiles["file_size"][$i]);
	}
	else
	{
		$e = $APPLICATION->GetException();
		if(is_object($e))
			$message = new CAdminMessage(GetMessage("CLO_STORAGE_SEARCH_LIST_ERROR"), $e);
	}
}

$rsData = new CDBResult;
$rsData->InitFromArray($arData);
$rsData = new CAdminResult($rsData, $sTableID);
$rsData->NavStart();
$lAdmin->NavText($rsData->GetNavPrint(''));

while(is_array($arRes = $rsData->NavNext()))
{
	if($arRes["TYPE"] === "dir")
	{
		if($arRes["NAME"] === "..")
			$link = 'clouds_file_search.php?lang='.LANGUAGE_ID.'&n='.urlencode($n).'&bucket='.$obBucket->ID.'&path='.urlencode(preg_replace('#([^/]+)/$#', '', $path));
		else
			$link = 'clouds_file_search.php?lang='.LANGUAGE_ID.'&n='.urlencode($n).'&bucket='.$obBucket->ID.'&path='.urlencode($path.$arRes["NAME"].'/');
	}
	else
	{
		$link = 'clouds_file_search.php?lang='.LANGUAGE_ID.'&n='.urlencode($n).'&file=y&bucket='.$obBucket->ID.'&path='.urlencode($path.$arRes["NAME"]);
	}

	$row =& $lAdmin->AddRow($arRes["ID"], $arRes, $link);

	$showFieldIcon = "";
	$showFieldText = "";
	if($arRes["TYPE"] === "dir")
	{
		$showFieldIcon = '<a href="'.htmlspecialcharsbx($link).'"><span id="fileman_menu_icon_sections" class="adm-submenu-item-link-icon"></span></a>';
		$showFieldText = '<a href="'.htmlspecialcharsbx($link).'">'.htmlspecialcharsex($arRes["NAME"]).'</a>';
	}
	else
	{
		$showFieldIcon = "";
		$showFieldText = '<a href="'.htmlspecialcharsbx($link).'">'.htmlspecialcharsex($arRes["NAME"]).'</a>';
	}

	$showField = '<table cellpadding="0" cellspacing="0" border="0"><tr><td align="left">'.$showFieldIcon.'</td><td align="left">&nbsp;'.$showFieldText.'</td></tr></table>';

	if($arRes["TYPE"] === "dir")
	{
		$row->AddViewField("FILE_NAME", $showField);
		$row->AddViewField("FILE_SIZE", '&nbsp;');
	}
	else
	{
		$row->AddViewField("FILE_NAME", $showField);
		$row->AddViewField("FILE_SIZE", CFile::FormatSize((float)$arRes["SIZE"]));
	}
}

$lAdmin->BeginPrologContent();

if(is_object($message))
	echo $message->Show();

if($obBucket->Init() && $_GET["file"] === "y")
	echo "<script>SelFile('".CUtil::JSEscape(urldecode($obBucket->GetFileSRC(rtrim($path, "/"))))."');</script>";

$lAdmin->EndPrologContent();

$lAdmin->CheckListMode();

$APPLICATION->SetTitle(GetMessage("CLO_STORAGE_SEARCH_TITLE"));

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_popup_admin.php");
?>
<form name="form1" method="GET" action="<?echo $APPLICATION->GetCurPage()?>">
<?
$arFindFields = Array(
	"bucket"=>GetMessage("CLO_STORAGE_SEARCH_BUCKET"),
	"path"=>GetMessage("CLO_STORAGE_SEARCH_PATH"),
);
$oFilter = new CAdminFilter($sTableID."_filter", $arFindFields);
$oFilter->Begin();
?>
<script language="JavaScript">
function SelFile(name)
{
	if (window.opener && window.opener.BX)
	{
		window.opener.BX.onCustomEvent('onCloudFileIsChosen', [name]);
	}
	el = window.opener.document.getElementById('<?echo CUtil::JSEscape($n)?>');
	if(el)
	{
		el.value = name;
		if (window.opener.BX)
			window.opener.BX.fireEvent(el, 'change');
	}
	window.close();
}
</script>
	<tr>
		<td><b><?echo GetMessage("CLO_STORAGE_SEARCH_BUCKET")?></b></td>
		<td><select name="bucket">
			<option value=""><?echo GetMessage("CLO_STORAGE_SEARCH_CHOOSE_BUCKET")?></option>
			<?foreach($arBuckets as $id => $name):?>
					<option value="<?echo htmlspecialcharsbx($id)?>" <?if($id == $bucket) echo "selected"?>><?echo htmlspecialcharsex($name)?></option>
			<?endforeach?>
		</select></td>
	</tr>

	<tr>
		<td><?echo GetMessage("CLO_STORAGE_SEARCH_PATH")?></td>
		<td><input type="text" name="path" size="45" value="<?echo htmlspecialcharsbx($path)?>"></td>
	</tr>
<?
$oFilter->Buttons(array(
	"url" => "/bitrix/admin/clouds_file_search.php?lang=".LANGUAGE_ID."&n=".urlencode($n),
	"table_id" => $sTableID,
));
$oFilter->End();
?>
</form>
<?
$lAdmin->DisplayList();

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_popup_admin.php");
?>