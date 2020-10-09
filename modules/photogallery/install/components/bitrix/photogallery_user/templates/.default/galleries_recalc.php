<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
{
	if ($_REQUEST["AJAX"] == "Y")
	{
		define("STOP_STATISTICS", true);
		require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
	}
	else
	{
		die();
	}
}
\Bitrix\Main\Localization\Loc::loadMessages(__FILE__);
$time = getmicrotime();

if (!CModule::IncludeModule("iblock"))
{
	ShowError(GetMessage("IBLOCK_MODULE_NOT_INSTALLED"));
	return false;
}
else if (!CModule::IncludeModule("photogallery"))
{
	ShowError(GetMessage("P_MODULE_IS_NOT_INSTALLED"));
	return false;
}

$arParams = (is_array($arParams) ? $arParams : array());
if (empty($arParams))
{
	$arParams["IBLOCK_ID"] = intval($_REQUEST["IBLOCK_ID"]);
	$arParams["PERMISSION"] = CIBlock::GetPermission($arParams["IBLOCK_ID"]);
}
if ($arParams["PERMISSION"] < "W")
{
	ShowError(GetMessage("P_DENIED_ACCESS"));
	return false;
}
elseif ($arParams["IBLOCK_ID"] <= 0)
{
	ShowError(GetMessage("P_BAD_IBLOCK_ID"));
	return false;
}

$arGalleries = unserialize(COption::GetOptionString("photogallery", "UF_GALLERY_SIZE"));
$arGalleries = (is_array($arGalleries) ? $arGalleries : array());
$arGallery = $arGalleries[$arParams["IBLOCK_ID"]];

if ($_REQUEST["AJAX"] == "Y" && check_bitrix_sessid())
{
	$result = array();
	$iCount = 300;
	$arFilter = array(
		"IBLOCK_ID" => $arParams["IBLOCK_ID"]);
	if ($arGallery["status"] != "inprogress" || $_REQUEST["ID"]."" != $arGallery["id"]."")
	{
		$arGallery = array(
			"status" => "done",
			"step" => 0,
			"elements_cnt" => CIBlock::GetElementCount($arParams["IBLOCK_ID"]),
			"element_number" => 0,
			"element_id" => 0,
			"id" => $_REQUEST["ID"],
			"date" => ConvertTimeStamp());
	}
	else
	{
		$arFilter[">ID"] = $arGallery["element_id"];
	}
	$db_res = CIBlockElement::GetList(array("ID" => "ASC"), $arFilter, false, array("nTopCount" => $iCount), array("ID", "IBLOCK_ID", "IBLOCK_SECTION_ID"));
	$iCnt = 0;
	$bBreaked = false;
	while ($res = $db_res->Fetch())
	{
		$iCnt++;
		CPhotogalleryElement::OnRecalcGalleries($res["ID"], $arGallery["id"]);
		$arGallery["element_id"] = $res["ID"];
		$arGallery["element_number"]++;

		if (getmicrotime() - $time > 10)
		{
			$bBreaked = true;
			break;
		}
	}
	$arGallery["status"] = (($iCnt < $iCount && !$bBreaked) ? "done" : "inprogress");
	if ($arGallery["status"] == "done")
	{
		if (getmicrotime() - $time > 10)
		{
			$arGallery["status"] = "inprogress";
		}
		else
		{
			$arGallery["status"] = "inprogress";
			$arGallery["step"]++;
			$arGalleries[$arParams["IBLOCK_ID"]] = $arGallery;
			COption::SetOptionString("photogallery", "UF_GALLERY_SIZE", serialize($arGalleries));
			CPhotogalleryElement::OnAfterRecalcGalleries($arParams["IBLOCK_ID"], $arGallery["id"]);
			$arGallery["status"] = "done";
			$arGallery["step"]--;
		}
	}

	$arGallery["step"]++;
	$arGalleries[$arParams["IBLOCK_ID"]] = $arGallery;
	COption::SetOptionString("photogallery", "UF_GALLERY_SIZE", serialize($arGalleries));
	$arGallery["text"] = str_replace(
		array("#ELEMENT_NUMBER#", "#ELEMENTS_CNT#"),
		array($arGallery["element_number"], $arGallery["elements_cnt"]),
		GetMessage("P_RECALC_1"));
	$APPLICATION->RestartBuffer();
	echo CUtil::PhpToJSObject($arGallery);
	die();
}
	CAjax::Init();
?>
<div class="photo-page-galleries-recalc">
	<div class="photo-info-box photo-page-galleries-recalc">
		<div class="photo-info-box-inner" id="photogallery_result">
<?
	if (empty($arGallery))
	{
?>
		<?=GetMessage("P_RECALC_2")?>
<?
	}
	elseif ($arGallery["status"] == "inprogress")
	{
?>
		<?=str_replace("#DATE#", $arGallery["date"], GetMessage("P_RECALC_3"))?>
<?
	}
	else
	{
?>
		<?=str_replace("#DATE#", $arGallery["date"], GetMessage("P_RECALC_4"))?>
<?
	}

?>
		</div>
	</div>

	<div class="photo-info-box photo-page-galleries-recalc-bar" id="photogallery_bar" <?
	if ($arGallery["status"] != "inprogress")
	{
		?>style="display:none;"<?
	}
	?>>
		<div class="photo-info-box-inner">
		<table cellpadding="0" cellspacing="0">
		<tr valign="top">
		<td>
			<div class="pbar-outer" style="width: 400px;">
				<div id="pb_photos" class="pbar-inner-green" style="display:block!important; width:<?
				if ($arGallery['elements_cnt'] > 0):
					echo intval(doubleval($arGallery['element_number']) * 100 / doubleval($arGallery['elements_cnt']));
				else:
					echo "1";
				endif;
					?>%;">&nbsp;</div>
			</div>
			<div class="pbar-title-outer" style="width: 400px;">
				<div class="pbar-title-inner" id="photogallery_recalc"><?
				if ($arGallery['elements_cnt'] > 0):
					echo str_replace(
						array("#ELEMENT_NUMBER#", "#ELEMENTS_CNT#"),
						array($arGallery["element_number"], $arGallery["elements_cnt"]),
						GetMessage("P_RECALC_1"));
				endif;
				?></div>
			</div>
		</td>
		<td>
			<div id="photo_window_edit"></div>
		</td>
		</tr>
		</table>
		</div>
	</div>
	<div class="photo-info-box photo-page-galleries-recalc-buttons">
		<div class="photo-info-box-inner">
			<button onclick="PhotoGalleryRecalcStart(this);" id="ButtonPhotoGalleryRecalcStart"><?=GetMessage("P_START")?></button><?
			?><button onclick="PhotoGalleryRecalcContinue(this);" id="ButtonPhotoGalleryRecalcContinue"<?=($arGallery["status"] != "inprogress" ? ' disabled="disabled"' : '')?>><?=GetMessage("P_CONTINUE")?></button><?
			?><button onclick="PhotoGalleryRecalcStop(this);" id="ButtonPhotoGalleryRecalcStop" disabled="disabled"><?=GetMessage("P_STOP")?></button>
		</div>
	</div>

	<div id="photogallery_error" style="display: none;" class="errortext">
	</div>
</div>
<script type="text/javascript">
var phpVars;
if (typeof(phpVars) != "object")
	var phpVars = {};
phpVars.bitrix_sessid = '<?=bitrix_sessid()?>';
var iPhotoGalleryRecalcIndex = <?=($arGallery["status"] != "inprogress" ? "Math.random()" : "'".$arGallery["id"]."'")?>;
function PhotoGalleryRecalc()
{
	this.bReady = false;
}
PhotoGalleryRecalc.prototype.Start = function()
{

	this.bReady = true;
	this.Step(false);
}
PhotoGalleryRecalc.prototype.Stop = function()
{
	this.bReady = false;
}
PhotoGalleryRecalc.prototype.Step = function(bContinue)
{
	if (this.bReady == false)
	{
		return false;
	}
	__this_source = this;
	var TID = jsAjax.InitThread();
	jsAjax.AddAction(TID, function(data){
		try {
			jsAjaxUtil.CloseLocalWaitWindow(TID, 'photo_window_edit');
			var result = {};
			if (data) { eval("var result = " + data + "; "); }
			if (result['status'] == 'inprogress')
			{
				document.getElementById('photogallery_recalc').innerHTML = result['text'];
				if (__this_source.bReady == false)
				{
					document.getElementById('ButtonPhotoGalleryRecalcStart').disabled = false;
					document.getElementById('ButtonPhotoGalleryRecalcContinue').disabled = false;
					document.getElementById('ButtonPhotoGalleryRecalcStop').disabled = true;
				}
				else
				{
					document.getElementById('ButtonPhotoGalleryRecalcStart').disabled = true;
					document.getElementById('ButtonPhotoGalleryRecalcContinue').disabled = true;
					document.getElementById('ButtonPhotoGalleryRecalcStop').disabled = false;
				}
				document.getElementById('pb_photos').style.width = (parseInt(parseInt(result['element_number']) * 100 / parseInt(result['elements_cnt']))) + '%';
				__this_source.Step();
			}
			else
			{
				__this_source.Stop();
				if (result['status'] != 'done')
				{
					document.getElementById('photogallery_error').innerHTML = '<?=CUtil::JSEscape(GetMessage("P_RECALC_5"))?><br />' + data;
					document.getElementById('ButtonPhotoGalleryRecalcStart').disabled = false;
					document.getElementById('ButtonPhotoGalleryRecalcContinue').disabled = false;
					document.getElementById('ButtonPhotoGalleryRecalcStop').disabled = true;
				}
				else
				{
					document.getElementById('photogallery_recalc').innerHTML = result['text'];
					var res_tmp = '<div class="photo-note-box"><div class="photo-note-box-text"><?=CUtil::JSEscape(GetMessage("P_RECALC_6"))?></div></div>';
					document.getElementById('photogallery_result').innerHTML = res_tmp.replace("#DATE#", result["date"]);
					document.getElementById('ButtonPhotoGalleryRecalcStart').disabled = false;
					document.getElementById('ButtonPhotoGalleryRecalcContinue').disabled = true;
					document.getElementById('ButtonPhotoGalleryRecalcStop').disabled = true;
					document.getElementById('pb_photos').style.width = '100%';
				}
			}
		} catch (e) {
			__this_source.Stop();
			for (var ii in e) { document.getElementById('photogallery_error').innerHTML += '<br />' + ii + ': ' + e[ii]; }}
		});

	var url = '/bitrix/components/bitrix/photogallery_user/templates/.default/galleries_recalc.php';
	var res = {'IBLOCK_ID' : '<?=$arParams["IBLOCK_ID"]?>', 'AJAX' : 'Y', 'sessid' : phpVars.bitrix_sessid, 'ID' : iPhotoGalleryRecalcIndex};
	if (bContinue === false)
	{
		res['start'] = 'Y';
	}
	jsAjaxUtil.ShowLocalWaitWindow(TID, 'photo_window_edit', false);
	jsAjax.Send(TID, url, res);
}
var PhotoRecalcObject = new PhotoGalleryRecalc();

function PhotoGalleryRecalcStart(button)
{
	document.getElementById('photogallery_bar').style.display = 'block';
	iPhotoGalleryRecalcIndex = Math.random();
	PhotoRecalcObject.Start();
	button.disabled = true;
	button.nextSibling.disabled = true;
	button.nextSibling.nextSibling.disabled = true;
}
function PhotoGalleryRecalcContinue(button)
{
	document.getElementById('photogallery_bar').style.display = 'block';
	PhotoRecalcObject.Start();
	button.disabled = true;
	button.previousSibling.disabled = true;
	button.nextSibling.disabled = true;
}
function PhotoGalleryRecalcStop(button)
{
	button.disabled = true;
	button.previousSibling.disabled = true;
	button.previousSibling.previousSibling.disabled = true;
	__this_source.Stop();
}
</script>
<?
$APPLICATION->SetTitle(GetMessage("P_TITLE"));
?>