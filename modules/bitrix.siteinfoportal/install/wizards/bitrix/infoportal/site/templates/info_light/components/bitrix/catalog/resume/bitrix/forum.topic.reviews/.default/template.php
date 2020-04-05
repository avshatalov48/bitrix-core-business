<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
$APPLICATION->AddHeadScript("/bitrix/js/main/utils.js");
$APPLICATION->AddHeadScript("/bitrix/components/bitrix/forum.interface/templates/popup/script.js");
$APPLICATION->AddHeadScript("/bitrix/components/bitrix/forum.interface/templates/.default/script.js");
// ************************* Input params***************************************************************
$arParams["SHOW_LINK_TO_FORUM"] = ($arParams["SHOW_LINK_TO_FORUM"] == "N" ? "N" : "Y");
$arParams["FILES_COUNT"] = intVal(intVal($arParams["FILES_COUNT"]) > 0 ? $arParams["FILES_COUNT"] : 1);
$arParams["IMAGE_SIZE"] = (intVal($arParams["IMAGE_SIZE"]) > 0 ? $arParams["IMAGE_SIZE"] : 100);
if (LANGUAGE_ID == 'ru'):
	$path = str_replace(array("\\", "//"), "/", dirname(__FILE__)."/ru/script.php");
	include($path);
endif;

// *************************/Input params***************************************************************
if (!empty($arResult["MESSAGES"])):
if ($arResult["NAV_RESULT"] && $arResult["NAV_RESULT"]->NavPageCount > 1):
?>
<div class="reviews-navigation-box reviews-navigation-top">
	<div class="reviews-page-navigation">
		<?=$arResult["NAV_STRING"]?>
	</div>
	<div class="reviews-clear-float"></div>
</div>
<?
endif;

?>
<div class="reviews-block-container reviews-reviews-block-container">
	<div class="reviews-block-outer">
		<div class="reviews-block-inner">
<?
$iCount = 0;
foreach ($arResult["MESSAGES"] as $res):
	$iCount++;
?>
	<table cellspacing="0" border="0" class="reviews-post-table <?=($iCount == 1 ? "reviews-post-first " : "")?><?
		?><?=($iCount == count($arResult["MESSAGES"]) ? "reviews-post-last " : "")?><?
		?><?=($iCount%2 == 1 ? "reviews-post-odd " : "reviews-post-even ")?>" id="message<?=$res["ID"]?>">
		<thead><tr><td>
<?
	if ($arResult["SHOW_POST_FORM"] == "Y"):
?>
		<div class="reviews-post-reply-buttons">
<?
		if ($arResult["FORUM"]["ALLOW_QUOTE"] == "Y"):
?>
	<a href="#review_anchor" title="<?=GetMessage("F_QUOTE_HINT")?>" class="reviews-button-small" <?
			?>onMouseDown="quoteMessageEx('<?=$res["FOR_JS"]["AUTHOR_NAME"]?>', 'message_text_<?=$res["ID"]?>')"><?=GetMessage("F_QUOTE_FULL")?></a>
<?
		endif;
?>
		<a href="#review_anchor"  title="<?=GetMessage("F_NAME")?>"  class="reviews-button-small" <?
			?>onMouseDown="reply2author('<?=$res["FOR_JS"]["AUTHOR_NAME"]?>,')"><?=GetMessage("F_NAME")?></a></div>
<?
	
?>
		</div>
<?
	endif;
?>
		<a name="message<?=$res["ID"]?>"></a><b>
<?
		if (intVal($res["AUTHOR_ID"]) > 0 && !empty($res["AUTHOR_URL"])):
			?><a href="<?=$res["AUTHOR_URL"]?>"><?=$res["AUTHOR_NAME"]?></a><?
		else:
			?><?=$res["AUTHOR_NAME"]?><?
		endif;
		?></b>, <?=$res["POST_DATE"]?>
	</th></tr>
	</thead>
	<tbody>
	<tr><td>
		<div class="reviews-text" id="message_text_<?=$res["ID"]?>"><?=$res["POST_MESSAGE_TEXT"]?></div>
<?
	foreach ($res["FILES"] as $arFile): 
	?><div class="reviews-message-img"><?
		?><?$GLOBALS["APPLICATION"]->IncludeComponent(
			"bitrix:forum.interface", "show_file",
			Array(
				"FILE" => $arFile,
				"WIDTH" => $arResult["PARSER"]->image_params["width"],
				"HEIGHT" => $arResult["PARSER"]->image_params["height"],
				"CONVERT" => "N",
				"FAMILY" => "FORUM",
				"SINGLE" => "Y",
				"RETURN" => "N",
				"SHOW_LINK" => "Y"),
			null,
			array("HIDE_ICONS" => "Y"));
	?></div><?
	endforeach;
?>
	</td></tr></tbody>
	</table>
<?
endforeach;
?>
		</div>
	</div>
</div>
<?

if (strlen($arResult["NAV_STRING"]) > 0 && $arResult["NAV_RESULT"]->NavPageCount > 1):
?>
<div class="reviews-navigation-box reviews-navigation-bottom">
	<div class="reviews-page-navigation">
		<?=$arResult["NAV_STRING"]?>
	</div>
	<div class="reviews-clear-float"></div>
</div>
<?
endif;


if (!empty($arResult["read"]) && $arParams["SHOW_LINK_TO_FORUM"] != "N"):
?>
<div class="reviews-link-box">
	<div class="reviews-link-box-text">
		<a href="<?=$arResult["read"]?>"><?=GetMessage("F_C_GOTO_FORUM");?></a>
	</div>
</div>
	
<?
endif;

endif;

if (empty($arResult["ERROR_MESSAGE"]) && !empty($arResult["OK_MESSAGE"])):
?>
<div class="reviews-note-box reviews-note-note">
	<div class="reviews-note-box-text"><?=ShowNote($arResult["OK_MESSAGE"]);?></div>
</div>
<?
endif;

if ($arResult["SHOW_POST_FORM"] != "Y"):
	return false;
endif;


if (!empty($arResult["MESSAGE_VIEW"])):
?>
<div class="reviews-header-box">
	<div class="reviews-header-title"><span><?=GetMessage("F_PREVIEW")?></span></div>
</div>

<div class="reviews-info-box reviews-post-preview">
	<div class="reviews-info-box-inner">
		<div class="reviews-post-entry">
			<div class="reviews-post-text"><?=$arResult["MESSAGE_VIEW"]["POST_MESSAGE_TEXT"]?></div>
<?
		if (!empty($arResult["REVIEW_FILES"])):
?>								
			<div class="reviews-post-attachments">
				<label><?=GetMessage("F_ATTACH_FILES")?></label>
<?
			foreach ($arResult["REVIEW_FILES"] as $arFile): 
?>								
				<div class="reviews-post-attachment"><?
				?><?$GLOBALS["APPLICATION"]->IncludeComponent(
					"bitrix:forum.interface", "show_file",
					Array(
						"FILE" => $arFile,
						"WIDTH" => $arResult["PARSER"]->image_params["width"],
						"HEIGHT" => $arResult["PARSER"]->image_params["height"],
						"CONVERT" => "N",
						"FAMILY" => "FORUM",
						"SINGLE" => "Y",
						"RETURN" => "N",
						"SHOW_LINK" => "Y"),
					null,
					array("HIDE_ICONS" => "Y"));
				?></div>
<?
			endforeach;
?>
			</div>
<?
		endif;
?>
		</div>
	</div>
</div>
<div class="reviews-br"></div>
<?
endif;

?>
<div class="reviews-reply-form">
<a name="review_anchor"></a>
<?
if (!empty($arResult["ERROR_MESSAGE"])): 
?>
<div class="reviews-note-box reviews-note-error">
	<div class="reviews-note-box-text"><?=ShowError($arResult["ERROR_MESSAGE"], "reviews-note-error");?></div>
</div>
<?
endif;
?>

<form name="REPLIER<?=$arParams["form_index"]?>" id="REPLIER<?=$arParams["form_index"]?>" action="<?=POST_FORM_ACTION_URI?>#postform"<?
	?> method="POST" enctype="multipart/form-data" onsubmit="return ValidateForm(this, '<?=$arParams["AJAX_TYPE"]?>');"<?
	?> onkeydown="if(null != init_form){init_form(this)}" onmouseover="if(init_form){init_form(this)}" class="reviews-form">
	<input type="hidden" name="back_page" value="<?=$arResult["CURRENT_PAGE"]?>" />
	<input type="hidden" name="ELEMENT_ID" value="<?=$arParams["ELEMENT_ID"]?>" />
	<input type="hidden" name="SECTION_ID" value="<?=$arResult["ELEMENT"]["IBLOCK_SECTION_ID"]?>" />
	<input type="hidden" name="save_product_review" value="Y" />
	<input type="hidden" name="preview_comment" value="N" />
	<?=bitrix_sessid_post()?>
<?
/* GUEST PANEL */
if (!$arResult["IS_AUTHORIZED"]):
?>
	<div class="reviews-reply-fields">
		<div class="reviews-reply-field-user">
			<div class="reviews-reply-field reviews-reply-field-author"><label for="REVIEW_AUTHOR<?=$arParams["form_index"]?>"><?=GetMessage("OPINIONS_NAME")?><?
				?><span class="reviews-required-field">*</span></label>
				<span><input name="REVIEW_AUTHOR" id="REVIEW_AUTHOR<?=$arParams["form_index"]?>" size="30" type="text" value="<?=$arResult["REVIEW_AUTHOR"]?>" tabindex="<?=$tabIndex++;?>" /></span></div>
<?		
	if ($arResult["FORUM"]["ASK_GUEST_EMAIL"]=="Y"):
?>
			<div class="reviews-reply-field-user-sep">&nbsp;</div>
			<div class="reviews-reply-field reviews-reply-field-email"><label for="REVIEW_EMAIL<?=$arParams["form_index"]?>"><?=GetMessage("OPINIONS_EMAIL")?></label>
				<span><input type="text" name="REVIEW_EMAIL" id="REVIEW_EMAIL<?=$arParams["form_index"]?>" size="30" value="<?=$arResult["REVIEW_EMAIL"]?>" tabindex="<?=$tabIndex++;?>" /></span></div>
<?
	endif;
?>
			<div class="reviews-clear-float"></div>
		</div>
	</div>
<?
endif;
?>
	<div class="reviews-reply-header"><?=GetMessage("F_MESSAGE_TEXT")?><span class="reviews-required-field">*</span></div>
	<div class="reviews-reply-fields">

		<div class="reviews-reply-field reviews-reply-field-bbcode">

			<div class="reviews-bbcode-line" id="forum_bbcode_line<?=$arParams["form_index"]?>">
<?
if ($arResult["FORUM"]["ALLOW_BIU"] == "Y"):
?>
				<a href="#postform" class="reviews-bbcode-button reviews-bbcode-bold" id="form_b" title="<?=GetMessage("F_BOLD")?>">
					<img src="/bitrix/components/bitrix/forum.post_form/templates/.default/images/bbcode/empty_for_ie.gif" /></a>
				<a href="#postform" class="reviews-bbcode-button reviews-bbcode-italic" id="form_i" title="<?=GetMessage("F_ITAL")?>">
					<img src="/bitrix/components/bitrix/forum.post_form/templates/.default/images/bbcode/empty_for_ie.gif" /></a>
				<a href="#postform" class="reviews-bbcode-button reviews-bbcode-underline" id="form_u" title="<?=GetMessage("F_UNDER")?>">
					<img src="/bitrix/components/bitrix/forum.post_form/templates/.default/images/bbcode/empty_for_ie.gif" /></a>
				<a href="#postform" class="reviews-bbcode-button reviews-bbcode-strike" id="form_s" title="<?=GetMessage("F_STRIKE")?>">
					<img src="/bitrix/components/bitrix/forum.post_form/templates/.default/images/bbcode/empty_for_ie.gif" /></a>
<?
endif;
if ($arResult["FORUM"]["ALLOW_QUOTE"] == "Y"):
?>
				<a href="#postform" class="reviews-bbcode-button reviews-bbcode-quote" id="form_quote" title="<?=GetMessage("F_QUOTE_TITLE")?>">
					<img src="/bitrix/components/bitrix/forum.post_form/templates/.default/images/bbcode/empty_for_ie.gif" /></a>
<?
endif;
if ($arResult["FORUM"]["ALLOW_CODE"] == "Y"):
?>
				<a href="#postform" class="reviews-bbcode-button reviews-bbcode-code" id="form_code" title="<?=GetMessage("F_CODE_TITLE")?>">
					<img src="/bitrix/components/bitrix/forum.post_form/templates/.default/images/bbcode/empty_for_ie.gif" /></a>
<?
endif;
if ($arResult["FORUM"]["ALLOW_ANCHOR"] == "Y"):
?>
				<a href="#postform" class="reviews-bbcode-button reviews-bbcode-url" id="form_url" title="<?=GetMessage("F_HYPERLINK_TITLE")?>">
					<img src="/bitrix/components/bitrix/forum.post_form/templates/.default/images/bbcode/empty_for_ie.gif" /></a>
<?
endif;
if ($arResult["FORUM"]["ALLOW_IMG"] == "Y"):
?>
				<a href="#postform" class="reviews-bbcode-button reviews-bbcode-img" id="form_img" title="<?=GetMessage("F_IMAGE_TITLE")?>">
					<img src="/bitrix/components/bitrix/forum.post_form/templates/.default/images/bbcode/empty_for_ie.gif" /></a>
<?
endif;
if ($arResult["FORUM"]["ALLOW_VIDEO"] == "Y"):
?>
				<a href="#postform" class="reviews-bbcode-button reviews-bbcode-video" id="form_video" title="<?=GetMessage("F_VIDEO_TITLE")?>">
					<img src="/bitrix/components/bitrix/forum.post_form/templates/.default/images/bbcode/empty_for_ie.gif" /></a>
<?
endif;

if ($arResult["FORUM"]["ALLOW_LIST"] == "Y"):
?>
				<a href="#postform" class="reviews-bbcode-button reviews-bbcode-list" id="form_list" title="<?=GetMessage("F_LIST_TITLE")?>">
					<img src="/bitrix/components/bitrix/forum.post_form/templates/.default/images/bbcode/empty_for_ie.gif" /></a>
<?
endif;
if ($arResult["FORUM"]["ALLOW_FONT"] == "Y"):
?>
				<a href="#postform" class="reviews-bbcode-button reviews-bbcode-color" id="form_palette" title="<?=GetMessage("F_COLOR_TITLE")?>">
					<img src="/bitrix/components/bitrix/forum.post_form/templates/.default/images/bbcode/empty_for_ie.gif" /></a>
<?
endif;
if (LANGUAGE_ID == 'ru'):
?>
				<a href="#postform" class="reviews-bbcode-button reviews-bbcode-translit" id="form_translit" title="<?=GetMessage("F_TRANSLIT_TITLE")?>">
					<img src="/bitrix/components/bitrix/forum.post_form/templates/.default/images/bbcode/empty_for_ie.gif" /></a>
<?
endif;
if ($arResult["FORUM"]["ALLOW_FONT"] == "Y"):
?>
				<select name='FONT' class="reviews-bbcode-font" id='form_font' title="<?=GetMessage("F_FONT_TITLE")?>">
					<option value='none'><?=GetMessage("F_FONT")?></option>
					<option value='Arial' style='font-family:Arial'>Arial</option>
					<option value='Times' style='font-family:Times'>Times</option>
					<option value='Courier' style='font-family:Courier'>Courier</option>
					<option value='Impact' style='font-family:Impact'>Impact</option>
					<option value='Geneva' style='font-family:Geneva'>Geneva</option>
					<option value='Optima' style='font-family:Optima'>Optima</option>
					<option value='Verdana' style='font-family:Verdana'>Verdana</option>
				</select>
<?
endif;
?>
			</div>
<?
if ($arResult["FORUM"]["ALLOW_SMILES"]=="Y"):
	$iMaxH = 0;
	foreach ($arResult["SMILES"] as $res):
		$iMaxH = ($iMaxH > intVal($res['IMAGE_HEIGHT']) ? $iMaxH : intVal($res['IMAGE_HEIGHT']));
	endforeach;
	$iPaddingTop = round(($iMaxH-16)/2);
	$iPaddingTop = ($iPaddingTop > 0 ? $iPaddingTop : 0);
if ($arParams["SMILES_COUNT"] <= 0):
?>
<script>
document.write('<style>div.reviews-smiles-corrected{height:<?=$iMaxH?>px; overflow:hidden;}</style>');
document.write('<style>div.reviews-smiles-none{visibility:hidden;}</style>');
</script>
			<div class="reviews-smiles-line reviews-smiles-corrected reviews-smiles-none" id="forum_smiles_line<?=$arParams["form_index"]?>">
<?
	foreach ($arResult["SMILES"] as $res):
		$TYPING = strtok($res['TYPING'], " ");
?>
				<span class="reviews-smiles-item" style="height:<?=$iMaxH?>px;"><?
					?><a href="#postform" name="smiles" style="margin-top:<?=(round(($iMaxH - $res['IMAGE_HEIGHT'])/2))?>px;"><?
						?><img src="<?=$arParams["PATH_TO_SMILE"].$res['IMAGE']?>" class="smiles"<?
		if (intVal($res['IMAGE_WIDTH']) > 0):
							?> width="<?=$res['IMAGE_WIDTH']?>" <?
		endif;
		if (intVal($res['IMAGE_HEIGHT']) > 0):
							?> height="<?=$res['IMAGE_HEIGHT']?>" <?
		endif;
						?> alt="<?=$TYPING?>" title="<?=$res['NAME']?>" border="0" /><?
					?></a></span>
<?
	endforeach;
?>
			</div>
<script>
document.write('<div class="reviews-reply-field reviews-reply-field-showsmiles" id="forum_smile_switcher<?=$arParams["form_index"]?>" style="visibility:hidden;">');
document.write('<a href="#postform" id="form_smiles_dinamic"><?=CUtil::JSEscape(GetMessage("F_SHOW_SMILE"))?></a></div>');
jsUtils.addEvent(window, "load", function(e){ForumShowSmile(e)});
function ForumShowSmile(e)
{
	var sIndex = '<?=$arParams["form_index"]?>';
	var form = document.forms["REPLIER<?=$arParams["form_index"]?>"];
	var forum_bbcode_line = document.getElementById('forum_bbcode_line<?=$arParams["form_index"]?>');
	var forum_smiles_line = document.getElementById('forum_smiles_line<?=$arParams["form_index"]?>');
	var forum_smile_switcher = document.getElementById('forum_smile_switcher<?=$arParams["form_index"]?>');
	var params = {'width' : form.REVIEW_TEXT.offsetWidth, 'width_real' : 0};
	var res = forum_smiles_line.getElementsByTagName('A');
	if (res && res.length > 0)
	{
		for (var ii = 0; ii < res.length; ii++)
		{
			params['width_real'] += parseInt(res[ii].parentNode.offsetWidth);
		}
	}
	if (params['width'] >= params['width_real'])
	{
		var res = document.createElement('DIV');
		res.className = "reviews-clear-float";
		forum_smiles_line.parentNode.insertBefore(res, forum_smiles_line);
	}
	if (((params['width'] - forum_bbcode_line.offsetWidth) >= params['width_real']) || 
		params['width'] >= params['width_real'])
	{
		forum_smile_switcher.style.display = 'none';
		forum_smiles_line.className = forum_smiles_line.className.replace('reviews-smiles-none', '');
		return true;
	}
	init_form(form);
	oForumForm[form.id].show_smiles_dinamic(forum_smile_switcher.firstChild, '<?=$arUserSettings["smiles"]?>', 'N');
	forum_smiles_line.className = forum_smiles_line.className.replace(/forum\-smiles\-none/gi, '');
	forum_smile_switcher.style.visibility = 'visible';
}
</script>
<?
elseif ($arParams["SMILES_COUNT"] < count($arResult["SMILES"])):
?>
			<div class="reviews-clear-float"></div>
			<div class="reviews-smiles-line" id="forum_smiles_showed" style="display:<?=($arUserSettings["smiles"] == "show" ? "none" : "block")?>;">
<?
	$ii = $arParams["SMILES_COUNT"];
	foreach ($arResult["SMILES"] as $res):
		$TYPING = strtok($res['TYPING'], " ");
		$ii--;
		if ($ii < 0){break;}
?>
				<span class="reviews-smiles-item" style="height:<?=$iMaxH?>px;"><?
					?><a href="#postform" name="smiles" style="margin-top:<?=(round(($iMaxH - $res['IMAGE_HEIGHT'])/2))?>px;"><?
						?><img src="<?=$arParams["PATH_TO_SMILE"].$res['IMAGE']?>" class="smiles"<?
		if (intVal($res['IMAGE_WIDTH']) > 0):
							?> width="<?=$res['IMAGE_WIDTH']?>" <?
		endif;
		if (intVal($res['IMAGE_HEIGHT']) > 0):
							?> height="<?=$res['IMAGE_HEIGHT']?>" <?
		endif;
						?> alt="<?=$TYPING?>" title="<?=$res['NAME']?>" border="0" /><?
					?></a></span>
<?
	endforeach;
?>
				<div class="reviews-smiles-item" style="height:<?=$iMaxH?>px;padding-top:<?=$iPaddingTop?>px;">
					<a href="#postform" id="form_smiles_static" name="smile_show">
						<?=GetMessage("F_SHOW_SMILE")?></a>
				</div>
			</div><?
			?><div class="reviews-smiles-line" id="forum_smiles_hidden" style="display:<?=($arUserSettings["smiles"] == "show" ? "block" : "none")?>;">
<?
	foreach ($arResult["SMILES"] as $res):
		$TYPING = strtok($res['TYPING'], " ");
?>
				<span class="reviews-smiles-item" style="height:<?=$iMaxH?>px;"><?
					?><a href="#postform" name="smiles" style="margin-top:<?=(round(($iMaxH - $res['IMAGE_HEIGHT'])/2))?>px;"><?
						?><img src="<?=$arParams["PATH_TO_SMILE"].$res['IMAGE']?>" class="smiles"<?
		if (intVal($res['IMAGE_WIDTH']) > 0):
							?> width="<?=$res['IMAGE_WIDTH']?>" <?
		endif;
		if (intVal($res['IMAGE_HEIGHT']) > 0):
							?> height="<?=$res['IMAGE_HEIGHT']?>" <?
		endif;
						?> alt="<?=$TYPING?>" title="<?=$res['NAME']?>" border="0" /><?
					?></a></span>
<?
	endforeach;
	
?>
				<div class="reviews-smiles-item" style="height:<?=$iMaxH?>px;padding-top:<?=$iPaddingTop?>px;">
					<a href="#postform" id="form_smiles_static" name="smile_hide"><?=GetMessage("F_HIDE_SMILE")?></a>
				</div>
			</div>
<?
else:
?>
<script>
jsUtils.addEvent(window, "load", function(e){ForumShowSmile(e)});
function ForumShowSmile(e)
{
	var sIndex = '<?=$arParams["form_index"]?>';
	var form = document.forms["REPLIER<?=$arParams["form_index"]?>"];
	var forum_bbcode_line = document.getElementById('forum_bbcode_line<?=$arParams["form_index"]?>');
	var forum_smiles_line = document.getElementById('forum_smiles_line<?=$arParams["form_index"]?>');
	var params = {'width' : form.REVIEW_TEXT.offsetWidth, 'width_real' : 0};
	var res = forum_smiles_line.getElementsByTagName('A');
	if (res && res.length > 0)
	{
		for (var ii = 0; ii < res.length; ii++)
		{
			params['width_real'] += parseInt(res[ii].parentNode.offsetWidth);
		}
	}
	if ((form.REVIEW_TEXT.offsetWidth - forum_bbcode_line.offsetWidth) < params['width_real'])
	{
		var res = document.createElement('DIV');
		res.className = "reviews-clear-float";
		forum_smiles_line.parentNode.insertBefore(res, forum_smiles_line);
		forum_smiles_line.style.width = form.REVIEW_TEXT.offsetWidth + 'px';
	}
}
</script>
			<div class="reviews-smiles-line" id="forum_smiles_line<?=$arParams["form_index"]?>">
<?
	foreach ($arResult["SMILES"] as $res):
		$TYPING = strtok($res['TYPING'], " ");
?>
				<span class="reviews-smiles-item" style="height:<?=$iMaxH?>px;"><?
					?><a href="#postform" name="smiles" style="margin-top:<?=(round(($iMaxH - $res['IMAGE_HEIGHT'])/2))?>px;"><?
						?><img src="<?=$arParams["PATH_TO_SMILE"].$res['IMAGE']?>" class="smiles"<?
		if (intVal($res['IMAGE_WIDTH']) > 0):
							?> width="<?=$res['IMAGE_WIDTH']?>" <?
		endif;
		if (intVal($res['IMAGE_HEIGHT']) > 0):
							?> height="<?=$res['IMAGE_HEIGHT']?>" <?
		endif;
						?> alt="<?=$TYPING?>" title="<?=$res['NAME']?>" border="0" /><?
					?></a></span>
<?
	endforeach;
?>
			</div>
<?
endif;
?>
			<div class="reviews-clear-float"></div>
<?
endif;
?>
		</div>
		
		<div class="reviews-reply-field reviews-reply-field-text">
			<textarea class="post_message" cols="55" rows="14" name="REVIEW_TEXT" id="REVIEW_TEXT" tabindex="<?=$tabIndex++;?>"><?=$arResult["REVIEW_TEXT"];?></textarea>
		</div>
		
<?

/* CAPTHCA */
if (strLen($arResult["CAPTCHA_CODE"]) > 0):
?>
		<div class="reviews-reply-field reviews-reply-field-captcha">
			<input type="hidden" name="captcha_code" value="<?=$arResult["CAPTCHA_CODE"]?>"/>
			<div class="reviews-reply-field-captcha-label">
				<label for="captcha_word"><?=GetMessage("F_CAPTCHA_PROMT")?><span class="reviews-required-field">*</span></label>
				<input type="text" size="30" name="captcha_word" tabindex="<?=$tabIndex++;?>" autocomplete="off" />
			</div>
			<div class="reviews-reply-field-captcha-image">
				<img src="/bitrix/tools/captcha.php?captcha_code=<?=$arResult["CAPTCHA_CODE"]?>" alt="<?=GetMessage("F_CAPTCHA_TITLE")?>" />
			</div>
		</div>
<?
endif;
/* ATTACH FILES */
if ($arResult["SHOW_PANEL_ATTACH_IMG"] == "Y"):
?>
		<div class="reviews-reply-field reviews-reply-field-upload">
<?
$iCount = 0;
if (!empty($arResult["REVIEW_FILES"])):
	foreach ($arResult["REVIEW_FILES"] as $key => $val):
	$iCount++;
	$iFileSize = intVal($val["FILE_SIZE"]);
	$size = array(
		"B" => $iFileSize, 
		"KB" => round($iFileSize/1024, 2), 
		"MB" => round($iFileSize/1048576, 2));
	$sFileSize = $size["KB"].GetMessage("F_KB");
	if ($size["KB"] < 1)
		$sFileSize = $size["B"].GetMessage("F_B");
	elseif ($size["MB"] >= 1 )
		$sFileSize = $size["MB"].GetMessage("F_MB");
?>
			<div class="reviews-uploaded-file">
				<input type="hidden" name="FILES[<?=$key?>]" value="<?=$key?>" />
				<input type="checkbox" name="FILES_TO_UPLOAD[<?=$key?>]" id="FILES_TO_UPLOAD_<?=$key?>" value="<?=$key?>" checked="checked" />
				<label for="FILES_TO_UPLOAD_<?=$key?>"><?=$val["ORIGINAL_NAME"]?> (<?=$val["CONTENT_TYPE"]?>) <?=$sFileSize?>
					( <a href="/bitrix/components/bitrix/forum.interface/show_file.php?action=download&amp;fid=<?=$key?>"><?=GetMessage("F_DOWNLOAD")?></a> )
				</label>
			</div>
<?
	endforeach;
endif;

if ($iCount < $arParams["FILES_COUNT"]):
$iFileSize = intVal(COption::GetOptionString("forum", "file_max_size", 50000));
$size = array(
	"B" => $iFileSize, 
	"KB" => round($iFileSize/1024, 2), 
	"MB" => round($iFileSize/1048576, 2));
$sFileSize = $size["KB"].GetMessage("F_KB");
if ($size["KB"] < 1)
	$sFileSize = $size["B"].GetMessage("F_B");
elseif ($size["MB"] >= 1 )
	$sFileSize = $size["MB"].GetMessage("F_MB");
?>
			<div class="reviews-upload-info" style="display:none;" id="upload_files_info_<?=$arParams["form_index"]?>">
<?
if ($arParams["FORUM"]["ALLOW_UPLOAD"] == "F"):
?>
				<span><?=str_replace("#EXTENSION#", $arParams["FORUM"]["ALLOW_UPLOAD_EXT"], GetMessage("F_FILE_EXTENSION"))?></span>
<?
endif;
?>
				<span><?=str_replace("#SIZE#", $sFileSize, GetMessage("F_FILE_SIZE"))?></span>
			</div>
<?
			
	for ($ii = $iCount; $ii < $arParams["FILES_COUNT"]; $ii++):
?>

			<div class="reviews-upload-file" style="display:none;" id="upload_files_<?=$ii?>_<?=$arParams["form_index"]?>">
				<input name="FILE_NEW_<?=$ii?>" type="file" value="" size="30" />
			</div>
<?
	endfor;
?>
			<a href="javascript:void(0);" onclick="AttachFile('<?=$iCount?>', '<?=($ii - $iCount)?>', '<?=$arParams["form_index"]?>', this); return false;">
				<span><?=($arResult["FORUM"]["ALLOW_UPLOAD"]=="Y") ? GetMessage("F_LOAD_IMAGE") : GetMessage("F_LOAD_FILE") ?></span>
			</a>
<?
endif;
?>
		</div>
<?
endif;
?>
		<div class="reviews-reply-field reviews-reply-field-settings">
<?
/* SMILES */
if ($arResult["FORUM"]["ALLOW_SMILES"] == "Y"):
?>
			<div class="reviews-reply-field-setting">
				<input type="checkbox" name="REVIEW_USE_SMILES" id="REVIEW_USE_SMILES<?=$arParams["form_index"]?>" <?
				?>value="Y" <?=($arResult["REVIEW_USE_SMILES"]=="Y") ? "checked=\"checked\"" : "";?> <?
				?>tabindex="<?=$tabIndex++;?>" /><?
			?>&nbsp;<label for="REVIEW_USE_SMILES<?=$arParams["form_index"]?>"><?=GetMessage("F_WANT_ALLOW_SMILES")?></label></div>
<?
endif;
/* SUBSCRIBE */
if ($arResult["SHOW_SUBSCRIBE"] == "Y"):
?>
			<div class="reviews-reply-field-setting">
				<input type="checkbox" name="TOPIC_SUBSCRIBE" id="TOPIC_SUBSCRIBE<?=$arParams["form_index"]?>" value="Y" <?
					?><?=($arResult["TOPIC_SUBSCRIBE"] == "Y")? "checked disabled " : "";?> tabindex="<?=$tabIndex++;?>" /><?
				?>&nbsp;<label for="TOPIC_SUBSCRIBE<?=$arParams["form_index"]?>"><?=GetMessage("F_WANT_SUBSCRIBE_TOPIC")?></label></div>
<?
	if ($arResult["FORUM_SUBSCRIBE"] == "Y"):
?>			<div class="reviews-reply-field-setting">
				<input type="checkbox" name="FORUM_SUBSCRIBE" id="FORUM_SUBSCRIBE<?=$arParams["form_index"]?>" value="Y" <?
				?><?=($arResult["FORUM_SUBSCRIBE"] == "Y")? "checked disabled " : "";?> tabindex="<?=$tabIndex++;?>"/><?
				?>&nbsp;<label for="FORUM_SUBSCRIBE<?=$arParams["form_index"]?>"><?=GetMessage("F_WANT_SUBSCRIBE_FORUM")?></label></div>
<?
	endif;
endif;
?>
		</div>
<?

?>
		<div class="reviews-reply-buttons">
			<input name="send_button" type="submit" value="<?=GetMessage("OPINIONS_SEND")?>" tabindex="<?=$tabIndex++;?>" <?
				?>onclick="this.form.preview_comment.value = 'N';" />
			<input name="view_button" type="submit" value="<?=GetMessage("OPINIONS_PREVIEW")?>" tabindex="<?=$tabIndex++;?>" <?
				?>onclick="this.form.preview_comment.value = 'VIEW';" />
		</div>

	</div>
</form>
</div>
<script type="text/javascript">
function AttachFile(iNumber, iCount, sIndex, oObj)
{
	var element = null;
	var bFined = false;
	iNumber = parseInt(iNumber);
	iCount = parseInt(iCount);
	
	document.getElementById('upload_files_info_' + sIndex).style.display = 'block';
	for (var ii = iNumber; ii < (iNumber + iCount); ii++)
	{
		element = document.getElementById('upload_files_' + ii + '_' + sIndex);
		if (!element || typeof(element) == null)
			break;
		if (element.style.display == 'none')
		{
			bFined = true;
			element.style.display = 'block';
			break;
		}
	}
	var bHide = (!bFined ? true : (ii >= (iNumber + iCount - 1)));
	if (bHide == true)
		oObj.style.display = 'none';
}

if (typeof oErrors != "object")
	var oErrors = {};
oErrors['no_topic_name'] = "<?=CUtil::addslashes(GetMessage("JERROR_NO_TOPIC_NAME"))?>";
oErrors['no_message'] = "<?=CUtil::addslashes(GetMessage("JERROR_NO_MESSAGE"))?>";
oErrors['max_len'] = "<?=CUtil::addslashes(GetMessage("JERROR_MAX_LEN"))?>";
oErrors['no_url'] = "<?=CUtil::addslashes(GetMessage("FORUM_ERROR_NO_URL"))?>";
oErrors['no_title'] = "<?=CUtil::addslashes(GetMessage("FORUM_ERROR_NO_TITLE"))?>";
oErrors['no_path'] = "<?=CUtil::addslashes(GetMessage("FORUM_ERROR_NO_PATH_TO_VIDEO"))?>";
if (typeof oText != "object")
	var oText = {};
oText['author'] = " <?=CUtil::addslashes(GetMessage("JQOUTE_AUTHOR_WRITES"))?>:\n";
oText['enter_url'] = "<?=CUtil::addslashes(GetMessage("FORUM_TEXT_ENTER_URL"))?>";
oText['enter_url_name'] = "<?=CUtil::addslashes(GetMessage("FORUM_TEXT_ENTER_URL_NAME"))?>";
oText['enter_image'] = "<?=CUtil::addslashes(GetMessage("FORUM_TEXT_ENTER_IMAGE"))?>";
oText['list_prompt'] = "<?=CUtil::addslashes(GetMessage("FORUM_LIST_PROMPT"))?>";
oText['video'] = "<?=CUtil::addslashes(GetMessage("FORUM_VIDEO"))?>";
oText['path'] = "<?=CUtil::addslashes(GetMessage("FORUM_PATH"))?>:";
oText['preview'] = "<?=CUtil::addslashes(GetMessage("FORUM_PREVIEW"))?>:";
oText['width'] = "<?=CUtil::addslashes(GetMessage("FORUM_WIDTH"))?>:";
oText['height'] = "<?=CUtil::addslashes(GetMessage("FORUM_HEIGHT"))?>:";

oText['BUTTON_OK'] = "<?=CUtil::addslashes(GetMessage("FORUM_BUTTON_OK"))?>";
oText['BUTTON_CANCEL'] = "<?=CUtil::addslashes(GetMessage("FORUM_BUTTON_CANCEL"))?>";
oText['smile_hide'] = "<?=CUtil::addslashes(GetMessage("F_HIDE_SMILE"))?>";

if (typeof oHelp != "object")
	var oHelp = {};

function reply2author(name)
{
	<?if ($arResult["FORUM"]["ALLOW_BIU"] == "Y"):?>
	document.REPLIER.REVIEW_TEXT.value += "[B]"+name+"[/B] \n";
	<?else:?>
	document.REPLIER.REVIEW_TEXT.value += name+" \n";
	<?endif;?>
	return false;
}
</script>