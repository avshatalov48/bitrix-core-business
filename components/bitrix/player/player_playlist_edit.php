<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_js.php");

$bFileMan = CModule::IncludeModule('fileman');
if(!$bFileMan)
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

CComponentUtil::__IncludeLang(BX_PERSONAL_ROOT."/components/bitrix/player", "player_playlist_edit.php");

$strWarning = "";
$menufilename = "";
$path = Rel2Abs("/", $path);
$arPath = Array($site, $path);
$DOC_ROOT = CSite::GetSiteDocRoot($site);
$abs_path = $DOC_ROOT.$path;
$bCreate = !file_exists($abs_path);

if (($bCreate && (!$USER->CanDoFileOperation('fm_create_new_file', $arPath) || !$USER->CanDoOperation('fileman_edit_existent_files'))) ||
(!$bCreate && (!$USER->CanDoFileOperation('fm_edit_existent_file', $arPath) || !$USER->CanDoOperation('fileman_admin_files'))))
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

$arTracks = Array();
/* * * * * * * * * * * * * * POST * * * * * * * * * * * * * */
if($REQUEST_METHOD=="POST" && $_REQUEST['save'] == 'Y')
{
	$objXML = new CDataXML();

	$xmlsrc = '<?xml version="1.0" encoding="UTF-8"?>
<playlist version="1" xmlns="http://xspf.org/ns/0/">
<trackList>';
	for ($i = 0, $l = count($ids); $i < $l; $i++)
	{
		$id = $ids[$i];
		$arTrack = Array(
			'title' => getPostVal('title', $id),
			//'author' => getPostVal('author', $id),
			'location' => getPostVal('location', $id),
			'image' => getPostVal('image', $id),
			//'duration' => getPostVal('duration', $id)
		);

		$xmlsrc .= "\n\t<track>\n";
		$xmlsrc .= getXMLNode('title', $arTrack['title']);
		//$xmlsrc .= getXMLNode('creator', $arTrack['author']);
		$xmlsrc .= getXMLNode('location', $arTrack['location']);
		$xmlsrc .= getXMLNode('image', $arTrack['image']);
		//$xmlsrc .= getXMLNode('duration', $arTrack['duration']);
		$xmlsrc .= "\t</track>";

		$arTracks[] = $arTrack;
	}
	$xmlsrc .= "\n</trackList>\n</playlist>";

	if (!defined("BX_UTF"))
		$xmlsrc = $GLOBALS["APPLICATION"]->ConvertCharset($xmlsrc, 'Windows-1251', 'UTF-8');

	if (!check_bitrix_sessid())
	{
		$strWarning = GetMessage('PLAYLIST_EDIT_SESSION_EXPIRED');
	}
	else
	{
		$APPLICATION->SaveFileContent($abs_path, $xmlsrc);
	?>
	<script>
	oPlaylistDialog.CloseDialog();

	<?if (isset($target) && $target == 'editor') die('</script>');?>
	ShowWaitWindow();

	<?if ($back_url <> ''):?>
	window.location.href = '<?=CUtil::JSEscape($back_url);?>';
	<?else:?>
	var new_href = top.location.href;
	var hashpos = new_href.indexOf('#');
	if (hashpos != -1)
		new_href = new_href.substr(0, hashpos);
	new_href += (new_href.indexOf('?') == -1 ? '?' : '&') + 'clear_cache=Y';
	top.location.href = new_href;
	<?endif;?>
	</script>
	<?
		die();
	}
}

if (!$bCreate && !isset($_REQUEST['save']))
{
	$bIncorrectFormat = false;
	$handle = fopen($abs_path, "r");
	$size = filesize($abs_path);

	if ($size > 20)
	{
		$contents = fread($handle, 20);
		if (mb_strtolower(mb_substr($contents, 0, 5)) != "<?xml")
			$bIncorrectFormat = true;
	}

	if (!$bIncorrectFormat)
	{
		$objXML = new CDataXML();
		$objXML->Load($abs_path);
		$arTree = $objXML->GetTree();
		$arTracks = Array();
		$bIncorrectFormat = true;

		$ch = $arTree->children;
		if (count($ch) > 0 && mb_strtolower($ch[0]->name) == 'playlist')
		{
			$bIncorrectFormat = false;
			$pl = $ch[0];
			$tls = $pl->children;
			for ($i_ = 0, $l_ = count($tls); $i_ < $l_; $i_++)
			{
				if (mb_strtolower($tls[$i_]->name) != 'tracklist')
					continue;
				$tracks = $tls[$i_]->children;
				for ($i = 0, $l = count($tracks); $i < $l; $i++)
				{
					$track = $tracks[$i];
					if (mb_strtolower($track->name) == 'track')
					{
						$arTrack = Array('title' => '', 'author' => '', 'location' => '', 'image' => '', 'duration' => '');
						for ($j = 0, $n = count($track->children); $j < $n; $j++)
						{
							$prop = $track->children[$j];
							if (mb_strtolower($prop->name) == 'title')
							// TODO: Maybe using xmlspecialcharsback - is bogus
								$arTrack['title'] = $objXML->xmlspecialcharsback($prop->content);
							//if (strtolower($prop->name) == 'creator')
								//$arTrack['author'] = $objXML->xmlspecialcharsback($prop->content);
							if (mb_strtolower($prop->name) == 'location')
								$arTrack['location'] = $objXML->xmlspecialcharsback($prop->content);
							if (mb_strtolower($prop->name) == 'image')
								$arTrack['image'] = $objXML->xmlspecialcharsback($prop->content);
							//if (strtolower($prop->name) == 'duration')
								//$arTrack['duration'] = $objXML->xmlspecialcharsback($prop->content);
						}
						$arTracks[] = $arTrack;
					}
				}
				break;
			}
		}
	}
	if ($bIncorrectFormat):
	?><script>
	if (!confirm("<?=GetMessage('CONFIRM_INCORRECT_XML_FORMAT')?>"))
		setTimeout(oPlaylistDialog.CloseDialog, 100);
	</script><?
	endif;
}

function getXMLNode($name, $value)
{
	return "\t\t<".$name.">".$GLOBALS['objXML']->xmlspecialchars($value)."</".$name.">\n";
}

function getPostVal($param, $ind)
{
	$k = $param.'_'.$ind;
	return isset($_POST[$k]) ? $_POST[$k] : '';
}

function getListVal($val)
{
	return $val != '' ? htmlspecialcharsex($val) : '-';
}

function displayInputRow($id, $val, $i, $width, $fd = false)
{
	$width = intval($width);
	$js_fd_par = $fd ? ', true' : '';
	?>
<td valign="top">
	<div onmouseout="rowMouseOut(this)" onmouseover="rowMouseOver(this<?=$js_fd_par?>)" class="edit-field view-area va_playlist" id="view_area_<?=$id?>_<?=$i?>" style="width: <?=$width?>px; zoom:1;" onclick="editArea('<?=$id?>_<?=$i?>')" title="<?=GetMessage('PLAYLIST_EDIT_CLICK_TO_EDIT')?>"><div class="playlist_text"><?=getListVal($val)?></div>
	<?if ($fd):?>
	<span onclick="BXOpenFD('<?=$i?>', '<?=$fd?>');" class="rowcontrol folder fd_icon" title="<?=GetMessage('OPEN_FD_TITLE')?>"></span>
	<?endif;?>
	</div>
	<div class="edit-area" id="edit_area_<?=$id?>_<?=$i?>" style="display: none;"><input type="text" style="width: <?=$width?>px;" name="<?=$id?>_<?echo $i?>" value="<?=getListVal($val)?>" onblur="viewArea('<?=$id?>_<?=$i?>')" /></div>
</td>
<?
}
?>
<script>
var jsMess = {
	//noname: '<?=CUtil::JSEscape(GetMessage('PLAYLIST_EDIT_NO_DATA'))?>',
	noname: '-',
	clickToEdit: '<?=CUtil::JSEscape(GetMessage('PLAYLIST_EDIT_CLICK_TO_EDIT'))?>',
	openFDTitle: '<?=CUtil::JSEscape(GetMessage('OPEN_FD_TITLE'))?>',
	itemUp: '<?=CUtil::JSEscape(GetMessage('PLAYLIST_ITEM_UP'))?>',
	itemDown: '<?=CUtil::JSEscape(GetMessage('PLAYLIST_ITEM_DOWN'))?>',
	itemDel: '<?=CUtil::JSEscape(GetMessage('PLAYLIST_ITEM_DELETE'))?>',
	itemDrag: '<?=CUtil::JSEscape(GetMessage('PLAYLIST_ITEM_DRAG'))?>'
};
if (!window.style_2 || !window.style_2.parentNode)
	window.style_2 = jsUtils.loadCSSFile("/bitrix/components/bitrix/player/js/playlist_edit.css");
</script>
<script type="text/javascript" src="/bitrix/js/main/dd.js?v=<?=filemtime($_SERVER['DOCUMENT_ROOT'].'/bitrix/js/main/dd.js')?>"></script>
<script type="text/javascript" src="/bitrix/components/bitrix/player/js/playlist_edit.js?v=<?=filemtime($_SERVER['DOCUMENT_ROOT'].'/bitrix/components/bitrix/player/js/playlist_edit.js')?>"></script>
<?
$TITLE = GetMessage("PLAYLIST_TITLE_".($bCreate ? "CREATE" : "EDIT"));
$DESCRIPTION = GetMessage('PLAYLIST_TITLE_DESCRIPTION');
$back_url = $_GET["back_url"];

// Clear all pathes which not begining from '/'
if ($back_url != '' && (mb_substr($back_url, 0, 1) != '/' || mb_strpos($back_url, ':') !== false))
	$back_url = '';

$obJSPopup = new CJSPopup('',
	array(
		'TITLE' => $TITLE,
		'ARGS' => "lang=".urlencode($_GET["lang"])."&site=".urlencode($_GET["site"])."&back_url=".urlencode($back_url)."&path=".urlencode($_GET["path"])."&name=".urlencode($_GET["name"])
	)
);
$obJSPopup->ShowTitlebar();
?>
<?$obJSPopup->StartDescription('bx-edit-menu');?>
<p><b><?=$DESCRIPTION?></b></p>
</p>
<?
if($strWarning != "")
	$obJSPopup->ShowValidationError($strWarning);

$obJSPopup->StartContent();
if(!is_array($arTracks))
	$arTracks = Array();
?>
	<input type="hidden" name="save" value="Y" />
	<table border="0" cellpadding="2" cellspacing="0" class="bx-width100 internal">
	<thead>
		<tr class="heading">
			<td><div style="width: 25px">&nbsp;</div></td>
			<td><div style="width: 160px"><b><?=GetMessage("PLAYLIST_EDIT_TITLE")?></b><div></td>
			<!--td><div style="width: 140px"><b><?=GetMessage("PLAYLIST_EDIT_AUTHOR")?></b></div></td-->
			<!--td><div style="width: 50px"><b><?=GetMessage("PLAYLIST_EDIT_DURATION")?></b></div></td-->
			<td><div style="width: 150px"><b><?=GetMessage("PLAYLIST_EDIT_LOCATION")?></b></div></td>
			<td><div style="width: 140px"><b><?=GetMessage("PLAYLIST_EDIT_IMAGE")?></b></div></td>
			<td><div style="width: 25px">&nbsp;</div></td>
			<td><div style="width: 25px">&nbsp;</div></td>
			<td><div style="width: 25px">&nbsp;</div></td>
		</tr>
	</thead>
	</table>
	<?
	?><div id="bx_playlist_layout" class="bx-menu-layout"><?
	$itemcnt = 0;
	for($i = 1, $l = count($arTracks); $i <= $l; $i++):
		$itemcnt++;
		$track = $arTracks[$i - 1];
	?><div class="bx-menu-placement" id="bx_item_placement_<?=$i?>"><div class="bx-edit-menu-item" id="bx_item_row_<?=$i?>"><table  id="bx_playlist_layout_tbl_<?=$i?>" class="bx-width100 internal playlist-table">
	<tr>
		<td>
		<input type="hidden" name="ids[]" value="<?=$i?>" />
		<span class="rowcontrol drag" title="<?=GetMessage('PLAYLIST_ITEM_DRAG')?>"></span>
		</td>
		<?displayInputRow('title', $track['title'], $i, 160)?>
		<?//displayInputRow('author', $track['author'], $i, 140)?>
		<?//displayInputRow('duration', $track['duration'], $i, 50)?>
		<?displayInputRow('location', $track['location'], $i, 150, 'VIDEO')?>
		<?displayInputRow('image', $track['image'], $i, 140, 'IMAGE')?>
		<td><span onclick="itemMoveUp(<?=$i?>)" class="rowcontrol up" title="<?=GetMessage('PLAYLIST_ITEM_UP')?>"></span></td>
		<td><span onclick="itemMoveDown(<?=$i?>)" class="rowcontrol down" title="<?=GetMessage('PLAYLIST_ITEM_DOWN')?>"></span></td>
		<td><span onclick="itemDelete(<?=$i?>)" class="rowcontrol delete" title="<?=GetMessage('PLAYLIST_ITEM_DELETE')?>"></span></td>
	</tr>
	</table></div></div><?
	endfor;
?>
</div>
	<br />
	<input type="button" onClick="itemAdd()" value="<?=GetMessage("PLAYLIST_ITEM_ADD")?>" />
	<input type="hidden" id="bx_item_cnt" value="<?= $itemcnt?>" />

<?
CAdminFileDialog::ShowScript(
	Array
	(
		"event" => "OpenFD_playlist_video",
		"arResultDest" => Array("FUNCTION_NAME" => 'BXSaveVideoPath'),
		"arPath" => Array("SITE" => $site, 'PATH' => $aMenuLinksItem[1]),
		"select" => 'F',// F - file only, D - folder only
		"operation" => 'O',// O - open, S - save
		"showUploadTab" => true,
		"showAddToMenuTab" => false,
		"fileFilter" => 'wmv,wma,flv,vp6,mp3,mp4,aac',
		"allowAllFiles" => true,
		"SaveConfig" => true,
		"zIndex" => 3200
	)
);

CAdminFileDialog::ShowScript(
	Array
	(
		"event" => "OpenFD_playlist_image",
		"arResultDest" => Array("FUNCTION_NAME" => 'BXSaveImagePath'),
		"arPath" => Array("SITE" => $site, 'PATH' => $aMenuLinksItem[1]),
		"select" => 'F',// F - file only, D - folder only
		"operation" => 'O',// O - open, S - save
		"showUploadTab" => true,
		"showAddToMenuTab" => false,
		"fileFilter" => 'jpg,jpeg,gif,png',
		"allowAllFiles" => true,
		"SaveConfig" => true,
		"zIndex" => 3200
	)
);
?>

<script type="text/javascript">
window.onload = function ()
{
	if (!window.oPlaylistDialog)
	{
		window.oPlaylistDialog = BX.WindowManager.Get();
		BX.addClass(window.oPlaylistDialog.PARTS.CONTENT, "bx-playlist-edit");
	}

	jsDD.Reset();
	var player_wind = BX('<?= CUtil::JSEscape($contID)?>');
	if (player_wind)
	{
		player_wind.style.visibility = 'hidden';
		if (oPlaylistDialog._CloseDialog)
			oPlaylistDialog.CloseDialog = oPlaylistDialog._CloseDialog;
		oPlaylistDialog._CloseDialog = oPlaylistDialog.CloseDialog;
		oPlaylistDialog.CloseDialog = function()
		{
			if (window.style_2 && window.style_2.parentNode)
				window.style_2.parentNode.removeChild(window.style_2);
			player_wind.style.visibility = 'visible';
			oPlaylistDialog._CloseDialog();
		};
	}
<?for($i = 1, $l = count($arTracks); $i <= $l; $i++):?>
	jsDD.registerDest(BX('bx_item_placement_<?=$i?>'));
	var obEl = BX('bx_item_row_<?=$i?>');
	obEl.onbxdragstart = BXDD_DragStart;
	obEl.onbxdragstop = BXDD_DragStop;
	obEl.onbxdraghover = BXDD_DragHover;
	jsDD.registerObject(obEl);
<?endfor;?>
	l = BX('bx_playlist_layout');
	l.ondrag = l.onselectstart = jsUtils.False;
	l.style.MozUserSelect = 'none';
	l.className = l.className; // hack for ie

	menuCheckIcons();
}
</script>
<?$obJSPopup->ShowStandardButtons(); ?>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin_js.php");?>