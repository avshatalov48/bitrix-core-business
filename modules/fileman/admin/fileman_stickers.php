<?
/*
##############################################
# Bitrix: SiteManager                        #
# Copyright (c) 2002-2006 Bitrix             #
# http://www.bitrixsoft.com                  #
# mailto:admin@bitrixsoft.com                #
##############################################
*/
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/fileman/prolog.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/fileman/classes/general/sticker.php");

if (!$USER->CanDoOperation('fileman_view_file_structure') || !$USER->CanDoOperation('fileman_edit_existent_files') || !CSticker::CanDoOperation('sticker_view'))
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/fileman/include.php");

if(CModule::IncludeModule("compression"))
	CCompress::Disable2048Spaces();

$action = isset($_REQUEST['sticker_action']) ? $_REQUEST['sticker_action'] : false;

if (!check_bitrix_sessid())
	die('<!--BX_STICKER_DUBLICATE_ACTION_REQUEST'.bitrix_sessid().'-->');

CUtil::JSPostUnEscape();

if($action == 'show_stickers' || $action == 'hide_stickers')
{
	// Save user choise
	CSticker::SetBShowStickers($action == 'show_stickers');
	if ($_REQUEST['b_inited'] == "N")
	{
		$Stickers = CSticker::GetList(array(
			'arFilter' => array(
				'USER_ID' => $USER->GetId(),
				'PAGE_URL' => $_POST['pageUrl'],
				'CLOSED' => 'N',
				'DELETED' => 'N',
				'SITE_ID' => $_REQUEST['site_id']
			)
		));
	}
	?>
	<script>
	<? if ($_REQUEST['b_inited'] == "N"):?>
		window.__bxst_result.stickers = <?= CUtil::PhpToJSObject($Stickers)?>;
	<?endif;?>
	window.__bxst_result.show = <?= (CSticker::GetBShowStickers() ? 'true' : 'false')?>;
	</script>
	<?
}
elseif ($action == 'load_lhe') // Load light editor
{
	$LHE = new CLightHTMLEditor;
	$LHE->Show(array(
		'id' => 'LHEBxStickers',
		'width' => '230',
		'height' => '100',
		'inputId' => 'stickers_ed',
		'content' => 'Text',
		'bUseFileDialogs' => false,
		'bUseMedialib' => false,
		'toolbarConfig' => array(
			'Bold', 'Italic', 'Underline', 'Strike', 'RemoveFormat',
			'ForeColor',
			'InsertOrderedList', 'InsertUnorderedList',
			'CreateLink'
			//,'Source'
		),
		'jsObjName' => 'oLHESticker',
		'bInitByJS' => true,
		'BBCode' => true,
		'bSaveOnBlur' => false,
		'documentCSS' => "
body{padding:0 5px 0 20px !important; font-family:Verdana !important; font-size:12px !important;}
.bxst-title{font-family:Verdana!important; font-size:11px !important; margin:0 0 0 -7px !important;line-height:18px!important; color: #727272!important;}
body.bxst-yellow{background: #FFFCB3!important;}
body.bxst-green{background: #DBFCCD!important;}
body.bxst-blue{background: #DCE7F7!important;}
body.bxst-red{background: #FCDFDF!important;}
body.bxst-purple{background: #F6DAF8!important;}
body.bxst-gray{background: #F5F5F5!important;}
body.bxst-yellow .bxst-title{color: #7F7E59!important;}
body.bxst-green .bxst-title{color: #6D7E66!important;}
body.bxst-blue .bxst-title{color: #6E737B!important;}
body.bxst-red .bxst-title{color: #7E6F6F!important;}
body.bxst-purple .bxst-title{color: #7B6D7C!important;}
body.bxst-gray .bxst-title{color: #7A7A7A!important;}"
	));
}
elseif($action == 'get_cur_date')
{
	echo FormatDate("j F G:i", time()+CTimeZone::GetOffset());
}
elseif($action == 'save_sticker')
{
	if (isset($_POST['marker']['adjust']))
		$markerAdjust = serialize($_POST['marker']['adjust']);
	else
		$markerAdjust = "";

	$ID = CSticker::Edit(array(
		'arFields' => array(
			'ID' => intVal($_POST['id']),
			'PAGE_URL' => $_POST['page_url'],
			'PAGE_TITLE' => $_POST['page_title'],
			'SITE_ID' => $_REQUEST['site_id'],

			'PERSONAL' => $_POST['personal'] == 'Y' ? 'Y' : 'N',
			'CONTENT' => $_POST['content'],
			'POS_TOP' => intVal($_POST['top']),
			'POS_LEFT' => intVal($_POST['left']),
			'WIDTH' => intVal($_POST['width']),
			'HEIGHT' => intVal($_POST['height']),

			'COLOR' => intVal($_POST['color']),
			'COLLAPSED' => $_POST['collapsed'] == 'Y' ? 'Y' : 'N',
			'COMPLETED' => $_POST['completed'] == 'Y' ? 'Y' : 'N',
			'CLOSED' => $_POST['closed'] == 'Y' ? 'Y' : 'N',
			'DELETED' => $_POST['deleted'] == 'Y' ? 'Y' : 'N',

			'MARKER_TOP' => isset($_POST['marker']['top']) ? intVal($_POST['marker']['top']) : 0,
			'MARKER_LEFT' => isset($_POST['marker']['left']) ? intVal($_POST['marker']['left']) : 0,
			'MARKER_WIDTH' => isset($_POST['marker']['width']) ? intVal($_POST['marker']['width']) : 0,
			'MARKER_HEIGHT' => isset($_POST['marker']['height']) ? intVal($_POST['marker']['height']) : 0,

			'MARKER_ADJUST' => $markerAdjust
		)
	));

	CUserOptions::SetOption('fileman', "stickers_last_color", intVal($_POST['color']));

	if ($ID > 0)
	{
?>
<script>
window.__bxst_result['<?= intVal($_POST['reqid'])?>'] = <?= CUtil::PhpToJSObject(CSticker::GetById($ID))?>;
</script>
<?
	}
	else
	{

	}
}
elseif ($action == 'show_list')
{
	if (isset($_REQUEST['list_action']) && in_array($_REQUEST['list_action'], array('del', 'restore', 'hide')))
	{
		$arIds = array();
		for ($i = 0; $i < count($_REQUEST['list_ids']); $i++)
		{
			if (intVal($_REQUEST['list_ids'][$i]) > 0)
				$arIds[] = intVal($_REQUEST['list_ids'][$i]);
		}

		if ($_REQUEST['list_action'] == 'del')
			$res = CSticker::Delete($arIds);
		elseif($_REQUEST['list_action'] == 'restore')
			$res = CSticker::SetHiden($arIds, false);
		elseif($_REQUEST['list_action'] == 'hide')
			$res = CSticker::SetHiden($arIds, true);

		if ($res !== true)
		{
			?><script>alert("<?= CUtil::JSEscape($res)?>");</script><?
		}
	}

	$bJustResult = $_REQUEST['sticker_just_res'] == "Y";
	$colorSchemes = array('bxst-yellow', 'bxst-green', 'bxst-blue', 'bxst-red', 'bxst-purple', 'bxst-gray');
	$curPage = urldecode($_REQUEST['cur_page']);

	$arFilter = array(
		'USER_ID' => $USER->GetId(),
		'SITE_ID' => $_REQUEST['site_id']
	);

	if (!$bJustResult) // First open: we get filter params from saved for user
	{
		$arFilterParams = CSticker::GetFilterParams();
		$arFilter['ONLY_OWNER'] = $arFilterParams['type'] == 'my' ? "Y" : "N";

		if ($_REQUEST['type'] == 'current')
		{
			//$arFilter['CLOSED'] = $arFilterParams['status'] == 'closed' ? 'Y' : 'N';
			$arFilter['PAGE_URL'] = $curPage;
		}
		else if ($_REQUEST['type'] == 'all')
		{
			$arFilter['CLOSED'] = 'N';
		}

		if ($arFilterParams['colors'] != 'all')
			$arFilter['COLORS'] = $arFilterParams['colors'];
	}
	else // We get filter params from request
	{
		$arFilterParams = array();
		$arFilter['ONLY_OWNER'] = (isset($_REQUEST['sticker_type']) && $_REQUEST['sticker_type'] == 'my') ? "Y" : "N";
		$arFilterParams['type'] = $_REQUEST['sticker_type'] == 'my' ? 'my' : 'all';

		if (isset($_REQUEST['sticker_status']) && $_REQUEST['sticker_status'] != "all")
		{
			$arFilter['CLOSED'] = $_REQUEST['sticker_status'] == 'closed' ? 'Y' : 'N';
			$arFilterParams['status'] = $_REQUEST['sticker_status'] == 'closed' ? 'closed' : 'opened';
		}

		if (isset($_REQUEST['sticker_page']))
		{
			if ($_REQUEST['sticker_page'] == "all")
			{
				$arFilterParams['page'] = 'all';
			}
			else
			{
				$arFilter['PAGE_URL'] = $_REQUEST['sticker_page'];
				$arFilterParams['page'] = $arFilter['PAGE_URL'] == $curPage ? 'current' : $arFilter['PAGE_URL'];
			}
		}

		if (isset($_REQUEST['colors']) && is_array($_REQUEST['colors']))
		{
			if ($_REQUEST['colors'] == array('99', '0', '1', '2', '3', '4', '5'))
			{
				$arFilterParams['colors'] = 'all';
			}
			else
			{
				$arFilter['COLORS'] = $_REQUEST['colors'];
				$arFilterParams['colors'] = $arFilter['COLORS'];
			}
		}

		CSticker::SetFilterParams($arFilterParams);
	}


	// Get stickers list
	$dbStickers = CSticker::GetList(
		array(
			'arFilter' => $arFilter,
			'bDBResult' => true,
			'arOrder' => array(
				'CLOSED' => 'ASC',
				'DATE_UPDATE' => 'DESC'
			)
		));

	$naviSize = intVal($_REQUEST['navi_size']);
	if (!$naviSize)
	{
		$naviSize = CUserOptions::GetOption('fileman', "stickers_navi_size", 5);
	}
	else
	{
		if ($naviSize < 5)
			$naviSize = 5;
		if ($naviSize > 30)
			$naviSize = 30;
		CUserOptions::SetOption('fileman', "stickers_navi_size", $naviSize);
	}

	CPageOption::SetOptionString("main", "nav_page_in_session", "N");
	$dbStickers->NavStart($naviSize);

	$curPageIds = array();
	$count = intVal($dbStickers->SelectedRowsCount());

	$arPages = CSticker::GetPagesList($_REQUEST['site_id']);

	$bReadonly = !CSticker::CanDoOperation('sticker_edit');
?>

<? if (!$bJustResult): /* Display whole dialog*/?>
<div class="bxst-list">
	<div class="bxst-list-filter">
		<div class="bxst-list-filter-hr"> </div> <?/* space in div - is special for IE without Doctype. Don't del it*/?>
		<table class="bxst-list-filter-tbl">
			<tr class="bxst-list-filter-titles">
				<td><div><?= GetMessage('FMST_LIST_STICKERS')?></div></td>
				<td><div><?= GetMessage('FMST_LIST_COLOR')?></div></td>
				<td><div><?= GetMessage('FMST_LIST_STATUS')?></div></td>
				<td><div><?= GetMessage('FMST_LIST_PAGE')?></div></td>
			</tr>
			<tr class="bxst-list-filter-controls">
				<td>
					<div class="bxstl-fil-cont-c">
						<table><tr>
								<td><div id="bxstl_fil_all_but" class="bxstl-but"><div class="bxstl-but-l"></div><div class="bxstl-but-c"><span><?= GetMessage('FMST_LIST_ALL')?></span></div><div class="bxstl-but-r"></div></div></td>
								<td><div id="bxstl_fil_my_but" class="bxstl-but"><div class="bxstl-but-l"></div><div class="bxstl-but-c"><span><?= GetMessage('FMST_LIST_MY')?></span></div><div class="bxstl-but-r"></div></div></td>
						</tr></table>
					</div>
				</td>
				<td><div id="bxstl_col_cont" class="bxst-list-color-cont bxstl-fil-cont-c"></div></td>
				<td>
					<div class="bxstl-fil-cont-c">
						<table><tr>
								<td><div id="bxstl_fil_opened_but" class="bxstl-but"><div class="bxstl-but-l"></div><div class="bxstl-but-c"><span><?= GetMessage('FMST_LIST_OPENED')?></span></div><div class="bxstl-but-r"></div></div></td>
								<td><div id="bxstl_fil_closed_but" class="bxstl-but"><div class="bxstl-but-l"></div><div class="bxstl-but-c"><span><?= GetMessage('FMST_LIST_CLOSED')?></span></div><div class="bxstl-but-r"></div></div></td>
								<td><div id="bxstl_fil_all_p_but" class="bxstl-but"><div class="bxstl-but-l"></div><div class="bxstl-but-c"><span><?= GetMessage('FMST_LIST_ALL_PAGES')?></span></div><div class="bxstl-but-r"></div></div></td>
						</tr></table>
					</div>
				</td>
				<td>
					<div class="bxstl-fil-cont">
						<select id="bxstl_fil_page_sel"  style="width: 200px;">
							<option value="<?= htmlspecialcharsbx($curPage)?>"> <?= GetMessage('FMST_LIST_CURRENT')?> </option>
							<option value="all"> <?= GetMessage('FMST_LIST_ALL_PAGES')?> </option>
							<? for ($i = 0, $l = count($arPages); $i < $l; $i++):
								if ($arPages[$i]['PAGE_URL'] == $curPage)
									continue;
								?>
								<option value="<?= str_replace('%20', ' ', $arPages[$i]['PAGE_URL'])?>" title="<?= htmlspecialcharsex($arPages[$i]['PAGE_TITLE']." - ".str_replace('%20', ' ', $arPages[$i]['PAGE_URL']))?>"><?= htmlspecialcharsex($arPages[$i]['PAGE_TITLE']." - ".str_replace('%20', ' ', $arPages[$i]['PAGE_URL']))?></option>
							<?endfor;?>
						</select>
					</div>
				</td>
			</tr>
		</table>
	</div>
	<div class="bxst-list-items" id="bxstl_items_table_cnt">
<?endif; /* if (!$bJustResult) */?>

		<table id="bxstl_items_table">
			<tr class="bxst-list-header">
				<td style="width: 4%;" class="bxst-id-cell"><div class="bxstl-h-div">#</div><div class="bxstl-sep"></div></td>
				<td style="width: <?if ($bReadonly):?>31%<?else:?>27%<?endif;?>;"><div class="bxstl-h-div"><?= GetMessage('FMST_LIST_TEXT')?></div><div class="bxstl-sep"></div></td>
				<td style="width: 15%;"><div class="bxstl-h-div"><?= GetMessage('FMST_LIST_DATA')?></div><div class="bxstl-sep"></div></td>
				<td style="width: 15%;"><div class="bxstl-h-div"><?= GetMessage('FMST_LIST_AUTOR')?></div><div class="bxstl-sep"></div></td>
				<td style="width: 22%;"><div class="bxstl-h-div"><?= GetMessage('FMST_LIST_PAGE')?></div><div class="bxstl-sep"></div></td>
				<td style="width: 8%;"><div class="bxstl-h-div"><?= GetMessage('FMST_LIST_COLOR')?></div><div class="bxstl-sep"></div></td>
				<?if (!$bReadonly):?>
				<td style="width: 4%;"><div class="bxstl-h-div"><input type="checkbox" onclick="window.oBXSticker.List.CheckAll(this.checked);"/></div></td>
				<?endif;?>
			</tr>
			<? if ($count > 0):?>
			<? while($arRes = $dbStickers->Fetch()): ?>
			<?
			$arRes['PAGE_URL'] = str_replace('%20', ' ', $arRes['PAGE_URL']);
			$html = strip_tags($arRes['CONTENT']);
			$colorClass = isset($colorSchemes[$arRes['COLOR']]) ? $colorSchemes[$arRes['COLOR']] : $colorSchemes[0];
			$date = CSticker::GetUsableDate($arRes['DATE_UPDATE2']);
			$url = $arRes['PAGE_URL']."?show_sticker=".intVal($arRes['ID']);
			$bCompleted = $arRes['COMPLETED'] == 'Y';
			if ($arRes['PAGE_URL'] == $curPage)
				$curPageIds[] = $arRes['ID'];
			?>
			<tr class="bxst-list-item<? if ($arRes['CLOSED'] == "Y") {echo " bxst-list-item-closed";}?>">
				<td class="bxst-id-cell"><a href="<?= $url?>"><?= intVal($arRes['ID'])?></a></td>
				<td><?= $html?></td>
				<td><?= htmlspecialcharsex($date)?></td>
				<td><nobr><?= htmlspecialcharsex(CSticker::GetUserName($arRes['CREATED_BY']))?></nobr></td>
				<td class="bxst-list-it-link<? if ($bCompleted) {echo ' bxstl-completed';}?>">
					<? if (strlen($arRes['PAGE_TITLE']) > 0):?>
					<a href="<?= $url?>" title="<?= htmlspecialcharsex($arRes['PAGE_TITLE'])?>"><?= htmlspecialcharsex($arRes['PAGE_TITLE'])?></a>
					<?endif;?>
					<a href="<?= $url?>" class="bxst-list-it-path" title="<?= htmlspecialcharsex($arRes['PAGE_URL'])?>"><?= htmlspecialcharsex($arRes['PAGE_URL'])?></a>
					<div class="bxst-sprite bxstl-compl-icon" title="<?= GetMessage('FMST_COMPLETE_LABEL')?>"></div>
				</td>
				<td><div class="bxstl-color-ind <?= $colorClass?>" /></td>
				<?if (!$bReadonly):?>
				<td><input type="checkbox" name="bxstl_item" value="<?= intVal($arRes['ID'])?>" onclick="window.oBXSticker.List.EnableActionBut(this.checked ? true : 'check');" /></td>
				<?endif;?>
			</tr>
			<?endwhile;?>
			<?else:?>
			<tr class="bxst-list-item"><td colSpan="7">
				<div class="bxstl-no-stickers"><?= GetMessage("FMST_NO_STICKERS");?></div>
			</td></tr>
			<? endif; /* if ($count > 0) */ ?>
		</table>
<? if (!$bJustResult): /* Display whole dialog*/?>
	</div>
	<div class="bxst-list-footer">
	<div class="bxst-list-navi"  id="bxstl_navi_cont">
<?else: /* if (!$bJustResult) */?>
	#BX_STICKER_SPLITER#
<?endif; /* if (!$bJustResult) */?>
		<? $dbStickers->NavPrint("", false, "", "/bitrix/modules/fileman/admin/fileman_stickers_nav.php"); ?>
<? if (!$bJustResult): /* Display whole dialog*/?>
	</div>
	<?if (!$bReadonly):?>
	<div class="bxst-list-action" >
		<select id="bxstl_action_sel">
			<option value=""><?= GetMessage('FMST_LIST_SEL_ACTION')?></option>
			<option value="restore"><?= GetMessage('FMST_LIST_RESTORE')?></option>
			<option value="hide"><?= GetMessage('FMST_LIST_HIDE')?></option>
			<option value="del"><?= GetMessage('FMST_LIST_DEL')?></option>
		</select>
		<input id="bxstl_action_ok" type="button" value="OK" />
	</div>
	<?endif;?>
	</div>
</div>

<script>
	window.__bxst_result.cur_page_ids = <?= CUtil::PhpToJSObject($curPageIds)?>;
	if (window.oBXSticker)
		window.oBXSticker.List.OnLoad(<?= $count?>);
</script>
<?else: /* if (!$bJustResult) */?>
<script>
	window.__bxst_result.cur_page_ids = <?= CUtil::PhpToJSObject($curPageIds)?>;
	window.__bxst_result.list_rows_count = <?= $count?>;
</script>
<?endif; /* if (!$bJustResult) */?>


<?
}


define("ADMIN_AJAX_MODE", true);
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin_after.php");
?>
