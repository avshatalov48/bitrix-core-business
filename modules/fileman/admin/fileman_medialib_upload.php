<?require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/fileman/prolog.php");

IncludeModuleLangFile(__FILE__);
if (!CModule::IncludeModule("fileman"))
	return false;
else if (!CMedialib::CanDoOperation('medialib_view_collection', 0))
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
/**
* Bitrix vars
* @var CMain $APPLICATION
* @var CUser $USER
*/
class filemanMedialibUpload
{
	var $post = array();
	var $collectionId = 0;
	var $ext = array();

	function onBeforeUpload(&$package, $upload, $post, $files, &$error)
	{
		$post["collectionId"] = intval($post['collection_id']);
		$this->post = $post;

		if ($post["collectionId"] <= 0)
		{
			$error = "Bad collection";
			return false;
		}
		else if (!CMedialib::CanDoOperation("medialib_new_item", $post["collectionId"])) // Check access
		{
			$error = "Access denied";
			return false;
		}
		$package["collectionId"] = $post["collectionId"];
		$package["ml_type"] = $post["ml_type"];
		return true;
	}

	function handleFile($hash, $file, &$package, &$upload)
	{
		global $APPLICATION;
		$name = $file["name"];
		$pattern = defined('BX_UTF')
			? "/[^\p{L}L0-9!\p{Z}\$&\(\)\[\]\{\}\-\.;=@\^_\~]/uis"
			: "/[^A-Za-zÀ-ß¨à-ÿ¸0-9!\s\$&\(\)\[\]\{\}\-\.;=@\^_\~]/is";
		$name = trim(preg_replace($pattern, "", $name));
		if (trim(mb_substr($name, 0, mb_strpos($name, '.'))) == '')
			$name = mb_substr(md5(uniqid(rand(), true)), 0, 8).trim($name);
		$res = CMedialibItem::Edit(array(
			'file' => (array_key_exists("files", $file) ? $file["files"]["default"] : $file),
			'arFields' => array(
				'NAME' => $name,
				'DESCRIPTION' => $file['description'],
				'KEYWORDS' => ''
			),
			'arCollections' => array($package["collectionId"])
		));
		if (!isset($upload["redirectUrl"]) && $res && $res['ID'] > 0)
		{
			$upload["redirectUrlPart"] = "action=redirect&".bitrix_sessid_get()."&first_id=".$res["ID"].
				"&col_id=".$package["collectionId"]."&ml_type=".htmlspecialcharsEx($package["ml_type"]);
			$upload["redirectUrl"] = $APPLICATION->GetCurPageParam($upload["redirectUrlPart"], array("action", "ml_type", "first_id", "col_id", "sessid"));
		}
		return $res;
	}
}
$obj = new filemanMedialibUpload();
$params = array(
	"allowUpload" => "F",
	"allowUploadExt" => CMedialib::GetMediaExtentions(true),
	"events" => array(
		"onUploadIsStarted" => array($obj, "onBeforeUpload"),
		"onFileIsUploaded" => array($obj, "handleFile")
	)
);
$uploader = (class_exists("CFileUploader") ? new CFileUploader($params, "get") : false);
// **************************** Add items to medialibrary  ****************************
$action = (array_key_exists("action", $_REQUEST) && check_bitrix_sessid() ? $_REQUEST["action"] : "");
if ($action == 'uploadhtml5' && is_object($uploader))
{
	$uploader->checkPost();
}
else if ($action == 'upload')
{
	$collectionId = intval($_POST['collection_id']);
	$fileCount = intval($_POST['FileCount']);
	$firstId = false;

	if (!CMedialib::CanDoOperation("medialib_new_item", $collectionId)) // Check access
		die();

	// Save elements
	if ($fileCount > 0 && $collectionId > 0)
	{
		CMedialib::Init();
		$arExt = CMedialib::GetMediaExtentions(false);

		for ($i = 1; $i <= $fileCount; $i++)
		{
			$name = $_FILES['SourceFile_'.$i]['name'];
			if (!CMedialib::CheckFileExtention($name, $arExt))
				continue;

			$name = trim(preg_replace("/[^a-zA-Z0-9!\$&\(\)\[\]\{\}\-\.;=@\^_\~]/is", "", $name));
			if (trim(mb_substr($name, 0, mb_strpos($name, '.'))) == '')
				$name = mb_substr(md5(uniqid(rand(), true)), 0, 8).trim($name);

			$res = CMedialibItem::Edit(array(
				'file' => $_FILES['SourceFile_'.$i],
				'arFields' => array(
					'NAME' => $name,
					'DESCRIPTION' => '',
					'KEYWORDS' => ''
				),
				'arCollections' => array($collectionId)
			));

			if ($i == 1 && $res && $res['ID'] > 0)
				$firstId = $res['ID'];
		}
	}
	die('#JS#&first_id='.$firstId.'&col_id='.$collectionId.'&ml_type='.htmlspecialcharsEx($_GET['ml_type']).'#JS#');
}
elseif($action == 'redirect')  //Redirect after files uploading
{
	$APPLICATION->SetTitle(GetMessage('FM_ML_UPL_TITLE2'));
	$APPLICATION->SetAdditionalCSS('/bitrix/js/fileman/medialib/medialib_admin.css');
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

	$aContext = Array();
	$aContext[] = Array(
		"TEXT" => GetMessage("FM_ML"),
		"ICON" => "btn_list",
		"LINK" => "/bitrix/admin/fileman_medialib_admin.php?lang=".LANGUAGE_ID."&".bitrix_sessid_get()
	);
	$menu = new CAdminContextMenu($aContext);
	$menu->Show();

	$firstId = intval($_GET['first_id']);
	$colId = intval($_GET['col_id']);


	// Get all items with id > $firstId
	$arItems = CMedialibItem::GetList(array(
		'arCollections' => array($colId),
		'minId' => $firstId
	));

	$len = count($arItems);

	if ($len > 0)
	{
		$res = CMedialib::GetCollectionTree(array('checkByType' => true, 'typeId' => intval($_GET['ml_type'])));
		$strSel = '<option value="0">'.GetMessage('ML_COL_SELECT').'</option>'.CMedialib::_BuildCollectionsSelectOptions($res['Collections'], $res['arColTree']);
		$module_id="fileman";
		$thumbWidth = COption::GetOptionInt($module_id, "ml_thumb_width", 140);
		$thumbHeight = COption::GetOptionInt($module_id, "ml_thumb_height", 105);
		$tmbWidth = ($thumbWidth + 10).'px';

		?>
		<script>window.arKeywords = {};</script>

		<form name="ml_mu_form" action="/bitrix/admin/fileman_medialib_upload.php?action=postsave" method="POST">
		<table class="mu-items-list" border="0"><?
		for($i = 0; $i < $len; $i++)
		{
			$Item = $arItems[$i];
			?>
			<tr>
				<td class="mu-label" style="width: 150px;"><label for="item_name_<?=$i?>" ><b><?= GetMessage('ML_NAME')?>:</b></label></td>
				<td><input class="mu-text-inp" id="item_name_<?=$i?>" name="item_name_<?=$i?>" type="text" value="<?= htmlspecialcharsbx($Item['NAME'])?>" size="52"></td>
				<td rowSpan="3" class="mu-thumb-cell" style="width: <?= $tmbWidth?>">
				<?if ($Item['TYPE'] == 'image'):?>
					<img src="<?= $Item['THUMB_PATH']?>"/>
				<?else:?>
					<img src="/bitrix/images/1.gif" class="ml-item-thumb ml-item-no-thumb" />
				<?endif;?>
				</td>
			</tr>
			<tr>
				<td class="mu-label" style="vertical-align: top; padding-top: 2px;"><label for="item_desc_<?=$i?>" ><?= GetMessage('ML_DESC')?>:</label></td>
				<td style="vertical-align: top;"><textarea id="item_desc_<?=$i?>" name="item_desc_<?=$i?>" rows="3" cols="40"><?=htmlspecialcharsbx($Item["DESCRIPTION"])?></textarea></td>
			</tr>
			<tr>
				<td class="mu-label"><label for="item_keys_<?=$i?>" ><?= GetMessage('ML_KEYWORDS')?>:</label></td>
				<td><input class="mu-text-inp" id="item_keys_<?=$i?>" name="item_keys_<?=$i?>" type="text" value="" size="52"></td>
			</tr>
			<tr>
				<td class="mu-label" style="vertical-align: top; padding-top: 2px;"><label for="item_cols_sel_<?=$i?>" ><b><?= GetMessage('ML_COLLECTIONS')?>:</b></label></td>
				<td style="padding-bottom: 8px;"><div class="mu-col-sel"><select id="item_cols_sel_<?=$i?>" onchange="itemColsSelChange(this);"  style="margin-top: 2px"><?= $strSel?></select></div>
				<input  id="item_colls_<?=$i?>" name="item_colls_<?=$i?>" type="hidden" value="" />
				<input  name="item_id_<?=$i?>" type="hidden" value="<?=$Item['ID']?>" />
				<script>
				BX.ready(function(){
					setTimeout(function(){addCollToItem(<?=$i?>, <?= $colId?>, BX("item_cols_sel_<?=$i?>"));}, 200);
					arKeywords[<?=$i?>] = {pKeys: BX('item_keys_<?=$i?>'), bFocusKeywords: false};
					arKeywords[<?=$i?>].pKeys.onchange = arKeywords[<?=$i?>].pKeys.onblur = function(){arKeywords[<?=$i?>].bFocusKeywords = true;};
				});
				</script>
				</td>
				<td><input type="checkbox" name="item_del_<?=$i?>" id="item_del_<?=$i?>"/><label for="item_del_<?=$i?>" ><?= GetMessage('ML_DELETE')?></label></td>
			</tr>
			<tr class="mu-separator"><td colSpan="3"></td></tr>
			<?
		}
		?></table>
		<br />
		<input type="hidden" value="<?= LANGUAGE_ID?>" name="lang" />
		<input type="hidden" value="<?= $len?>" name="items_count" />
		<input type="hidden" value="<?= $firstId?>" name="first_id" />
		<input type="hidden" value="<?= $colId?>" name="col_id" />
		<?=bitrix_sessid_post()?>
		<input type="submit" title="<?= GetMessage('admin_lib_edit_save_title')?>" value="<?= GetMessage('admin_lib_edit_save')?>" name="save" />
		<input type="button" title="<?= GetMessage('admin_lib_edit_cancel_title')?>" onclick="window.location='/bitrix/admin/fileman_medialib_admin.php?lang=<?= LANGUAGE_ID?>';" name="cancel" value="<?= GetMessage('admin_lib_edit_cancel')?>" />
		</form>
		<script>
		document.forms['ml_mu_form'].onsubmit = function(e)
		{
			var res = true, pName, pColS, pColV, i, itemsCount = <?= $len?>;

			for (i = 0; i < itemsCount; i++)
			{
				pName = BX('item_name_' + i);
				if (pName.value == '')
				{
					alert('<?= GetMessage('FM_ML_UPL_NO_NAME_WARN')?>');
					pName.focus();
					res = false;
					break;
				}

				pColV = BX('item_colls_' + i);

				if (pColV.value == '' || pColV.value == ',')
				{
					pColS = BX('item_cols_sel_' + i);
					alert('<?= GetMessage('FM_ML_UPL_NO_COLS_WARN')?>');
					pColS.focus();
					res = false;
					break;
				}
			}

			return res ? true : jsUtils.PreventDefault(e);
		};

		window.oCollections = <?= CUtil::PhpToJSObject($res['Collections'])?>;

		function itemColsSelChange(pEl)
		{
			var ItemId = pEl.id.substr('item_cols_sel_'.length);
			addCollToItem(ItemId, pEl.value, pEl);
			pEl.value = 0;
		}

		function addCollToItem(ItemId, id, pSel)
		{
			var
				i,
				pEl = BX('item_colls_' + ItemId),
				curArCols = pEl.value == '' ? [] : pEl.value.split(','),
				curL = curArCols.length;

			for (i = 0; i < curL; i++)
				if (parseInt(curArCols[i], 10) == id)
					return;

			var
				oCol = oCollections[id],
				pDiv = BX.create("DIV", {props: {className: 'mu-check-col', title: oCol.NAME}}),
				pDel = pDiv.appendChild(jsUtils.CreateElement("IMG", {src: '/bitrix/images/1.gif', className: 'mu-col-del', id: 'mu_it_' + id, title: '<?= GetMessage('ML_DEL_COL2ITEM')?>'}));

			curArCols.push(id);
			pDiv.appendChild(BX.create("SPAN", {text: oCol.NAME}));
			pDiv.onmouseover = function(){BX.addClass(this, 'mu-col-over');}
			pDiv.onmouseout = function(){BX.removeClass(this, 'mu-col-over');}

			if (oCol && oCol.KEYWORDS && !arKeywords[ItemId].bFocusKeywords)
				AppendKeywords(arKeywords[ItemId].pKeys, oCol.KEYWORDS);

			pDel.onclick = function(e)
			{
				var
					cid = parseInt(this.id.substr('mu_it_'.length)),
					pCont = this.parentNode.parentNode,
					itemInd = parseInt(pCont.lastChild.id.substr('item_cols_sel_'.length)),
					pEl = BX('item_colls_' + ItemId),
					curArCols = pEl.value.split(','),
					i, l = curArCols.length, newArCols = [], cid_;

				SelectOptionInColList(BX('item_cols_sel_' + itemInd), cid, false);
				pCont.removeChild(this.parentNode);

				for (i = 0; i < l; i++)
				{
					cid_ = parseInt(curArCols[i], 10);
					if (cid_ != cid && cid_ > 0)
						newArCols.push(cid_);
				}

				pEl.value = newArCols.join(',');
			};

			pEl.value = curArCols.join(',');
			pSel.parentNode.insertBefore(pDiv, pSel);
			SelectOptionInColList(pSel, id);
		}

		function SelectOptionInColList(pSel, val, bSel)
		{
			for (var i = 0, l = pSel.options.length; i < l; i++)
			{
				if (pSel.options[i].value == val)
				{
					pSel.options[i].className = (bSel !== false) ? 'mu-opt-checked' : '';
					pSel.options[i].title = (bSel !== false) ? '<?= GetMessage('ML_CHECKED_COL_TITLE')?>' : '';
					return;
				}
			}
		}

		function AppendKeywords(pInput, value)
		{
			if (!pInput || !value)
				return;

			var
				arKeys = [],
				arKeysR = pInput.value.split(',').concat(value.split(',')),
				kw, i, l = arKeysR.length;

			for (i = 0; i < l; i++)
			{
				kw = jsUtils.trim(arKeysR[i]);
				if (kw && !jsUtils.in_array(kw, arKeys))
					arKeys.push(kw);
			}

			pInput.value = arKeys.join(', ');
		}
		</script>
		<?
	}
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
}
elseif($action == 'postsave')
{
	$itemsCount = intval($_POST['items_count']);

	if ($itemsCount > 0)
	{
		for($i = 0; $i < $itemsCount; $i++)
		{
			if (isset($_POST['item_del_'.$i]) && $_POST['item_del_'.$i])
			{
				CMedialib::DelItem(intval($_POST['item_id_'.$i]));
				continue;
			}

			$arCols_ = explode(',', trim($_POST['item_colls_'.$i], ' ,'));
			$arCols = array();
			for ($j = 0, $n = count($arCols_); $j < $n; $j++)
			{
				if (intval($arCols_[$j]) > 0 && CMedialib::CanDoOperation("medialib_edit_item", $arCols_[$j])) // Check access
					$arCols[] = intval($arCols_[$j]);
			}

			if (count($arCols) > 0)
			{
				$res = CMedialibItem::Edit(array(
					'arFields' => array(
						'ID' => intval($_POST['item_id_'.$i]),
						'NAME' => $_POST['item_name_'.$i],
						'DESCRIPTION' => $_POST['item_desc_'.$i],
						'KEYWORDS' => $_POST['item_keys_'.$i]
					),
					'arCollections' => $arCols
				));
			}
		}
	}

	LocalRedirect("/bitrix/admin/fileman_medialib_admin.php?lang=".LANGUAGE_ID."&".bitrix_sessid_get());
}

// ***************************** Show upploader  **************************
$APPLICATION->SetAdditionalCSS('/bitrix/js/fileman/medialib/medialib_admin.css');
$APPLICATION->AddHeadScript('/bitrix/js/fileman/medialib/medialib_admin.js');
$APPLICATION->SetTitle(GetMessage('FM_ML_UPL_TITLE1'));
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

$trees = CMedialib::GetCollectionTree(array('checkByType' => true, 'typeId' => intval($_GET['type'])));
$select = CMedialib::_BuildCollectionsSelectOptions($trees['Collections'], $trees['arColTree'], 0, intval($_GET['col_id']));

$menu = new CAdminContextMenu(array(
	array(
		"TEXT" => GetMessage("FM_ML"),
		"LINK" => "/bitrix/admin/fileman_medialib_admin.php?lang=".LANGUAGE_ID,
		"ICON" => "btn_list",
	)
));
$menu->Show();

CJSCore::Init(array("core", "ajax", "uploader", "canvas"));
$uploaderID = "medialib";
$options = CUserOptions::GetOption("fileman", "uploader_html5", array());
$options = (is_array($options) ? $options : array());
?>
<div class="upl-main-wrap">
<form id="<?=$uploaderID?>_form" name="<?=$uploaderID?>_form" action="<?=$APPLICATION->GetCurPageParam("type_ml=".urlencode($_GET["type"])."&".bitrix_sessid_get(), array("type_ml", "sessid"))?>" method="POST" enctype="multipart/form-data" class="bxiu-photo-form">
	<input type="hidden" name="action" id="id" value="uploadhtml5" />
	<input type="hidden" name="sessid" id="sessid" value="<?= bitrix_sessid()?>" />
<div class="bxu-thumbnails bxu-thumbnails-start<?=($options["template"]=="full" ? "" : " bxu-main-block-reduced-size")?>" id="bxuMain<?=$uploaderID?>"> <!-- bxu-thumbnails-loading bxu-thumbnails-start-->
	<div class="bxu-top-block">
		<label class="upl-top-bar-text" for="collection_id<?=$uploaderID?>"><?=GetMessage("MEDIALIB_UPLOADER_INTO")?>:</label><?
		?><select class="upl-select" name="collection_id" id="collection_id<?=$uploaderID?>" onchange="itemColsSelChange2(this, arguments[0] || window.event);">
			<?=$select?>
		</select>
		<span class="bxu-loading-block">
			<span class="bxu-loading-block-bar"><span class="bxu-loading-block-bar-inner" id="bxuUploadBar<?=$uploaderID?>"></span></span>
			<span class="bxu-loading-block-text"><?=GetMessage("MEDIALIB_UPLOADER_HAS_BEEN_UPLOADED")?> <span id="bxuUploaded<?=$uploaderID?>"></span> <?=GetMessage("MEDIALIB_UPLOADER_UPLOAD_FROM")?> <span id="bxuForUpload<?=$uploaderID?>"></span></span>
			<span class="bxu-loading-block-cancel-btn" id="bxuCancel<?=$uploaderID?>"><?=GetMessage("MEDIALIB_UPLOADER_CANCEL")?></span>
		</span>
		<div class="bxu-settings-block">
			<span class="bxu-settings-block-templates">
				<span class="bxu-templates-btn bxu-templates-btn-small<?=($options["template"]=="full" ? "" : " bxu-templates-btn-active")?>" id="bxuReduced<?=$uploaderID?>" title="<?=GetMessage("MEDIALIB_UPLOADER_SIMPLIFIED")?>"></span><?
				?><span id="bxuEnlarge<?=$uploaderID?>" class="bxu-templates-btn bxu-templates-btn-big<?=($options["template"]=="full" ? " bxu-templates-btn-active" : "")?>" title="<?=GetMessage("MEDIALIB_UPLOADER_NORMAL")?>"></span>
			</span>
		</div>
	</div>
	<div class="bxu-main-block" id="bxuDropzone<?=$uploaderID?>">
		<div class="bxu-start-block">
			<div class="bxu-start-block-spacer-div">
				<img class="bxu-start-block-spacer-img" src="/bitrix/images/fileman/medialib/uploader/upl-start-spacer.png"/>
				<input type="file" id="bxuUploaderStartField<?=$uploaderID?>" multiple="multiple" />
			</div>
			<div class="bxu-start-block-cont">
				<img src="/bitrix/images/fileman/medialib/uploader/start-img.png" class="bxu-start-block-img" alt=""/>
				<div class="bxu-start-block-text">
					<?=GetMessage("MEDIALIB_UPLOADER_UPLOAD1")?>
					<span class="bxu-start-block-description bxu-dnd"><?=GetMessage("MEDIALIB_UPLOADER_DND")?></span>
				</div>
			</div>
			<div class="bxu-start-block-btn">
				<a class="webform-button">
					<span class="webform-button-left"></span><span class="webform-button-text"><?=GetMessage("MEDIALIB_UPLOADER_UPLOAD")?><?
						?><input type="file" id="bxuUploaderStart<?=$uploaderID?>" class="bxu-file-input" multiple="multiple" /><?
					?></span><span class="webform-button-right"></span>
				</a>
			</div>
		</div>
		<ul class="bxu-items" id="bxuItems<?=$uploaderID?>"></ul>
		<div class="bxu-bottom-block">
			<div class="bxu-bottom-block-shadow-wrap">
				<div class="bxu-bottom-block-shadow"></div>
			</div>
			<div class="bxu-bottom-block-btns">
				<a class="webform-button webform-button-accept" id="bxuStartUploading<?=$uploaderID?>">
					<span class="webform-button-left"></span><span class="webform-button-text"><?=GetMessage("MEDIALIB_UPLOADER_UPLOAD")?></span><span class="webform-button-right"></span>
				</a>
				<a class="webform-button webform-button-add">
					<span class="webform-button-left"></span><span class="webform-button-text"><?=GetMessage("MEDIALIB_UPLOADER_ADD")?>
						<input type="file" id="bxuUploader<?=$uploaderID?>" name="FILE" class="bxu-file-input" multiple="multiple" />
					</span><span class="webform-button-right"></span>
				</a>
			</div>
			<div class="bxu-bottom-block-text"><?=GetMessage("MEDIALIB_UPLOADER_COUNT2")?>: <span id="bxuImagesCount<?=$uploaderID?>">0</span></div>
		</div>
	</div>
</div>
<script type="text/javascript">
<?
	$cols = array();
	foreach ($trees['Collections'] as $col)
		$cols[$col['ID']] = CMedialib::CanDoOperation('medialib_new_item', $col['ID']);
?>
function itemColsSelChange2(pEl, e)
{
	if (window.oColAccess[pEl.value] != '1') alert("<?= GetMessage('FM_ML_UPL_ACCESS_DENIED')?>");
}
window.oColAccess = <?=CUtil::PhpToJSObject($cols)?>;
<?
$edit = GetMessage("MEDIALIB_UPLOADER_EDIT");
$turn = GetMessage("MEDIALIB_UPLOADER_TURN");
$del = GetMessage("MEDIALIB_UPLOADER_DEL");
$thumb = <<<HTML
<span class="bxu-item-block">
	<span class="bxu-item-block-top">
		<img src="/bitrix/images/fileman/medialib/uploader/upl-spacer-img.png" class="bxu-spacer"/>
		<span class="bxu-item-block-preview">#preview#</span>
		<span class="bxu-item-load-bar" id="bxu#id#Progress"><span class="bxu-item-load-bar-inner" id="bxu#id#ProgressBar"></span></span>
	</span>
	<span class="bxu-item-block-bottom" onmousedown="BX.eventCancelBubble(event); return true;">
		<span class="bxu-item-block-setting">
			<span class="bxu-item-btn bxu-item-btn-edit" id="#id#Edit" title="$edit"></span>
			<span class="bxu-item-btn bxu-item-btn-turn" id="#id#Turn" title="$turn"></span>
			<span class="bxu-item-btn bxu-item-btn-del" id="#id#Del" title="$del"></span></span>
		<span class="bxu-item-block-desc">#description#</span>
	</span>
</span>
HTML;
$errorThumb = <<<HTML
<span class="bxu-item-block">
	<span class="bxu-item-block-top">
		<img src="/bitrix/images/fileman/medialib/uploader/upl-spacer-img.png" class="bxu-spacer" />
		<span class="bxu-item-error-cont">
			<span class="bxu-error-icon"></span>
			<span class="bxu-error-text">#error#</span>
		</span>
	</span>
</span>
HTML;

$params = array_merge($uploader->params, array(
	"id" => $uploaderID,
	"streams" => 1,
	"allowUpload" => "A",
	"uploadFormData" => "Y",
	"uploadMethod" => "deferred",
	"input" => "bxuUploader".$uploaderID,
	"dropZone" => "bxuDropzone".$uploaderID,
	"placeHolder" => "bxuItems".$uploaderID,
	"errorThumb" => $errorThumb,
	"thumb" => array("className" => "bxu-item"),
	"fields" => array(
		"thumb" => array(
			"template" => $thumb,
			"editorTemplate" => "#description#"
		),
		"description" => array(
			"template" => '<input class="bxu-item-thumb-description-inp" name="description" placeholder="'.GetMessage("MEDIALIB_UPLOADER_DESCRIPTION").'" value="#description#" type="text" />',
			"editorTemplate" => '<input name="description" placeholder="'.GetMessage("MEDIALIB_UPLOADER_DESCRIPTION").'" value="#description#" type="text" />',
			"className" => "bx-bxu-thumb-description"
		)
	)
));

?>
	BX.ready(function(){
		new BX.UploaderTemplateThumbnails(<?=CUtil::PhpToJSObject($params)?>);
	});
</script>
</form>
</div>

<div style="clear:both"></div>
<?=BeginNote().GetMessage('FM_ML_UPL_NOTICE').EndNote();?>
<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
?>