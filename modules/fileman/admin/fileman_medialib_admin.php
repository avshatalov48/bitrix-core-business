<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/fileman/prolog.php");

$APPLICATION->SetAdditionalCSS('/bitrix/js/fileman/medialib/medialib_admin.css');
$APPLICATION->AddHeadScript('/bitrix/js/fileman/medialib/core_admin.js');

IncludeModuleLangFile(__FILE__);
CModule::IncludeModule("fileman");

if (!CMedialib::CanDoOperation('medialib_view_collection', 0))
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

/***********  MAIN PAGE **********/
$APPLICATION->SetTitle(GetMessage("ML_MEDIALIB"));
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

$exParams = array(
	'bCountPermissions' => true,
	'types' => false
);

//curTypeId
CUtil::InitJSCore(array('ajax'));
$arMLTypes = CMedialib::GetTypes();
$curTypeInd = 0;
$curType = false;

if (isset($_REQUEST['type']) && intval($_REQUEST['type']) > 0 ) // && check_bitrix_sessid() http://jabber.bx/view.php?id=28997 commit
{
	for ($i = 0, $l = count($arMLTypes); $i < $l; $i++)
	{
		if ($arMLTypes[$i]['id'] == $_REQUEST['type'])
		{
			$curTypeInd = $i;
			$curType = $arMLTypes[$i];
			CUserOptions::SetOption("fileman", "medialib_def_type", $arMLTypes[$i]['id']);
			break;
		}
	}
}

if ($curType === false)
{
	$id = CUserOptions::GetOption("fileman", "medialib_def_type", 0);
	if ($id > 0)
	{
		for ($i = 0, $l = count($arMLTypes); $i < $l; $i++)
		{
			if ($arMLTypes[$i]['id'] == $id)
			{
				$curTypeInd = $i;
				$curType = $arMLTypes[$i];
				break;
			}
		}
	}
}

if ($curType === false)
{
	$curTypeInd = 0;
	$curType = $arMLTypes[0];
}

?><script><?$arCols = CMedialib::GetCollections($exParams);?></script><?

$arTypeCols = array();
for ($i = 0, $l = count($arCols); $i < $l; $i++)
{
	$type = $arCols[$i]['ML_TYPE'];

	if ($curType === false || $curType['id'] == $type || ($curType['code'] == "image" && $curType['system'] && !$type))
		$arTypeCols[] = $arCols[$i];
}

$aContext = Array();
$bCols = count($arTypeCols) > 0;
if (($bCols && $exParams['arCountPerm']['new_col'] > 0) || CMedialib::CanDoOperation('medialib_new_collection', 0))
	$aContext[] = Array(
		"TEXT" => GetMessage("FM_ML_NEW_COLLECTION"),
		"ICON" => "btn_new_collection",
		"LINK" => "javascript: void(0);",
		"TITLE" => GetMessage("FM_ML_NEW_COLLECTION_TITLE")
	);

if ($bCols && $exParams['arCountPerm']['new_item'] > 0)
{
	$aContext[] = Array(
		"TEXT" => GetMessage("FM_ML_NEW_ITEM"),
		"ICON" => "btn_new_item",
		"LINK" => "javascript: void(0);",
		"TITLE" => GetMessage("FM_ML_NEW_ITEM_TITLE")
	);

	$aContext[] = Array(
		"TEXT" => GetMessage("FM_ML_MASS_UPLOAD"),
		"ICON" => "btn_mass_upl",
		"LINK" => "fileman_medialib_upload.php?lang=".LANGUAGE_ID."&type=".$curType['id']."&".bitrix_sessid_get(),
		"TITLE" => GetMessage("FM_ML_MASS_UPLOAD_TITLE")
	);
}

$aContext[] = array(
	"HTML" => '<div class="bxml-search-controll"><span>'.GetMessage('FM_ML_SEARCH').':</span>'.
		'<input type="text" id="ml_search_input" size="25" />'.
		'<input type="button"  style="margin-left:3px;" id="ml_search_button" value="'.GetMessage('FM_ML_SEARCH_BUT').'" title="'.GetMessage('FM_ML_SEARCH_BUT_TITLE').'" />'.
		'</div>'
);

//$aContext[] = Array(
//	"TEXT" => GetMessage("FM_ML_TAGS_CLOUD"),
//	"ICON" => "",
//	"LINK" => "javascript: void(0);",
//	"TITLE" => GetMessage("FM_ML_TAGS_CLOUD_TITLE")
//);

if(count($aContext) > 0)
	$aContext[] = Array("NEWBAR" => true);

if (($bCols && $exParams['arCountPerm']['access'] > 0) || CMedialib::CanDoOperation('medialib_access', 0))
{
	$aContext[] = Array(
		"TEXT" => GetMessage("FM_ML_ACCESS"),
		//"ICON" => "btn_access",
		"LINK" => "fileman_medialib_access.php?lang=".LANGUAGE_ID."&".bitrix_sessid_get(),
		"TITLE" => GetMessage("FM_ML_ACCESS_TITLE")
	);
}

if ($USER->CanDoOperation('fileman_view_all_settings'))
{
	$aContext[] = Array(
		"TEXT" => GetMessage("FM_ML_MANAGE_TYPES"),
		//"ICON" => "btn_type_config",
		"LINK" => "/bitrix/admin/settings.php?mid=fileman&tabControl_active_tab=edit5&lang=".LANGUAGE_ID."&".bitrix_sessid_get(),
		"TITLE" => GetMessage("FM_ML_MANAGE_TYPES_TITLE")
	);
}

if (count($aContext) > 0)
{
	$menu = new CAdminContextMenuList($aContext);
	$menu->Show();
}

?>

<script>
<?CMedialib::AppendLangMessages();?>
<?CMedialib::AppendLangMessagesEx();?>

BX.ready(function()
	{
		BX.loadScript([
			"/bitrix/js/fileman/medialib/common.js?v=<?=@filemtime($_SERVER['DOCUMENT_ROOT'].'/bitrix/js/fileman/medialib/common.js')?>",
			"/bitrix/js/fileman/medialib/core_admin.js?v=<?=@filemtime($_SERVER['DOCUMENT_ROOT'].'/bitrix/js/fileman/medialib/core_admin.js')?>"
		],
		function(){setTimeout(function(){
			window.oBXMLAdmin = new window.BXMedialibAdmin(
			{
				sessid: "<?= bitrix_sessid()?>",
				thumbWidth : <?= COption::GetOptionInt('fileman', "ml_thumb_width", 140)?>,
				thumbHeight : <?= COption::GetOptionInt('fileman', "ml_thumb_height", 105) ?>,
				rootAccess: {
					new_col: '<?= CMedialib::CanDoOperation('medialib_new_collection', 0)?>',
					edit: '<?= CMedialib::CanDoOperation('medialib_edit_collection', 0)?>',
					del: '<?= CMedialib::CanDoOperation('medialib_del_collection', 0)?>',
					new_item: '<?= CMedialib::CanDoOperation('medialib_new_item', 0)?>',
					edit_item: '<?= CMedialib::CanDoOperation('medialib_edit_item', 0)?>',
					del_item: '<?= CMedialib::CanDoOperation('medialib_del_item', 0)?>',
					access: '<?= CMedialib::CanDoOperation('medialib_access', 0)?>'
				},
				curColl: <?= isset($_REQUEST['cur_col']) ? intval($_REQUEST['cur_col']) : 0?>,
				bCanUpload: <?= $USER->CanDoOperation('fileman_upload_files') ? 'true' : 'false'?>,
				bCanViewStructure: <?= $USER->CanDoOperation('fileman_view_file_structure') ? 'true' : 'false'?>,
				strExt : "<?= htmlspecialcharsEx(CMedialib::GetMediaExtentions())?>",
				lang : "<?= LANGUAGE_ID?>",
				Types : <?= CUtil::PhpToJSObject($arMLTypes)?>,
				curTypeInd : <?= $curTypeInd?>
			});

			window.oBXMLAdmin.OnStart();
			var
				btn_new_collection = BX('btn_new_collection'),
				btn_new_item = BX('btn_new_item'),
				btn_mass_upload = BX('btn_mass_upl');

			if (btn_new_collection)
				btn_new_collection.onclick = function()
				{
					window.oBXMLAdmin.OpenEditCollDialog({bGetSelCol: true});
					return false;
				};

			if (btn_new_item)
				btn_new_item.onclick = function()
				{
					window.oBXMLAdmin.OpenEditItemDialog({bGetSelCol: true});
					return false;
				};

			if (btn_mass_upload)
				btn_mass_upload.onclick = function()
				{
					var col_id = window.oBXMLAdmin.SelectedColId;
					if (!col_id || !window.oBXMLAdmin.oCollections[col_id])
						col_id = '';
					window.location = "fileman_medialib_upload.php?lang=<?= LANGUAGE_ID ?>&type=<?= $curType['id'] ?>&<?= bitrix_sessid_get() ?>&col_id=" + col_id;
					return false;
				};
		}, 50);}
		);
	}
);
</script><?
?>

<div class="ml-cont">
	<table><tr>
		<td><div id="ml_type_cont" class="ml-type-cont"></div></td>
		<td><div class="ml-breadcrumbs" id="ml_breadcrumbs"></div></td>
	</tr></table>
	<br />
	<div class="ml-search-res-cont" id="ml_search_res_cont_par">
		<div class="ml-coll-title mlcolllevel-0" title="<?= GetMessage('ML_SEARCH_RESULT')?>">
			<img class="ml-col-icon ml-col-icon-closed" src="/bitrix/images/1.gif" id="ml_srch_res_flip"/>
			<input type="checkbox" id="ml_srch_res_check" />
			<span><span id="ml_srch_res_title" class="ml-search-res-title"><?= GetMessage('ML_SEARCH_RESULT')?></span>
			<a id="ml_srch_res_hide" class="" title="<?= GetMessage('FM_ML_HIDE_TITLE')?>" hidefocus="true" href="javascript:void(0);">(<?= GetMessage('FM_ML_HIDE')?>)</a>
			</span>
		</div>
		<div class="ml-items-cont" id="ml_s_res_cnt_div">
			<table><tr><td id="ml_search_res_cont"></td></tr></table>
		</div>
	</div>
	<div class="ml-coll-cont" id="ml_coll_cont"></div>
</div>

<div id="ml_no_colection_notice" style="display: none;">
<?= BeginNote().GetMessage('ML_NO_COLS_EX').EndNote();?>
</div>

<? if ($bCols): ?>
<br />
<table class="multiaction" style = "display:<?= (CMedialib::CanDoOperation('medialib_del_item', 0)||CMedialib::CanDoOperation('medialib_del_collection', 0)) ? 'block' : 'none'?>">
	<tr class="top">
		<td class="left"><div class="empty"/></td><td><div class="empty"/></td><td class="right"><div class="empty"/></td>
	</tr>
	<tr>
		<td class="left"><div class="empty"/></td>
		<td class="content multi-dis" id="ml_multiaction_cnt">
			<table>
				<tr>
				<td>
					<input type="checkbox" id="ml_action_target" name="ml_action_target" title="<?= GetMessage('ML_FOR_ALL_TITLE')?>"/>
				</td>
				<td>
					<label for="ml_action_target" title="<?= GetMessage('ML_FOR_ALL_TITLE')?>"><?= GetMessage('ML_FOR_ALL')?></label>
				</td>
				<td><div class="separator"/></td>
				<td>
					<a id="action_delete_button" class="context-button icon ma-but-delete" title="<?= GetMessage('ML_DELETE')?>" onclick="" hidefocus="true" href="javascript:void(0);"><?= GetMessage('ML_DELETE')?></a>
				</td>
				</tr>
			</table>
		</td>
		<td class="right"><div class="empty"/></td>
	</tr>
	<tr class="bottom"><td class="left"><div class="empty"/></td><td><div class="empty"/></td><td class="right"><div class="empty"/></td></tr>
</table>
<?endif;?>

<div id="bxml-subdialog-cont" class="bxml-subdialog-cont">
<?
CMedialib::BuildAddCollectionDialog($Params);
CMedialib::BuildAddItemDialog($Params);
CMedialib::BuildConfirmDialog($Params);
CMedialib::BuildViewItemDialog($Params);
CMedialib::BuildChangeType($Params);
?>
</div>
<?

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
?>
