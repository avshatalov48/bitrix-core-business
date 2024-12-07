<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
$GLOBALS["APPLICATION"]->AddHeadScript("/bitrix/js/main/utils.js");
?>
<div class="socnet-chat-body" id="socnet_chat_body">
<form name="sonet_chat_form" action="" onsubmit="sonet_chat_msg_add(); return false;" onmouseover="if(null != init_form){init_form(this)}" onkeydown="if(null != init_form){init_form(this)}">
<?
if ($arResult["NEED_AUTH"] == "Y")
{
?>
	<div class="socnet-chat-warning">
	<span class="errortext"><?= GetMessage("SONET_C50_T_NEED_AUTH") ?></span>
	</div>
<?
}
elseif (!empty($arResult["FatalError"]))
{
	?>
	<div class="socnet-chat-warning">
	<span class="errortext"><?= $arResult["FatalError"] ?></span><br /><br />
	</div>
	<?
}
else
{
	if (!empty($arResult["ErrorMessage"]))
	{
		?>
		<div class="socnet-chat-warning">
		<span class="errortext"><?= $arResult["ErrorMessage"] ?></span><br /><br />
		</div>
		<?
	}
	?>
<script>

var bSendForm = false;

if (typeof oErrors != "object")
	var oErrors = {};

oErrors['no_topic_name'] = "<?=CUtil::addslashes(GetMessage("JERROR_NO_TOPIC_NAME"))?>";
oErrors['no_topic_recip'] = "<?=CUtil::addslashes(GetMessage("JERROR_NO_RECIPIENT"))?>";
oErrors['no_message'] = "<?=CUtil::addslashes(GetMessage("JERROR_NO_MESSAGE"))?>";
oErrors['max_len1'] = "<?=CUtil::addslashes(GetMessage("JERROR_MAX_LEN1"))?>";
oErrors['max_len2'] = "<?=CUtil::addslashes(GetMessage("JERROR_MAX_LEN2"))?>";
oErrors['no_url'] = "<?=CUtil::addslashes(GetMessage("FORUM_ERROR_NO_URL"))?>";
oErrors['no_title'] = "<?=CUtil::addslashes(GetMessage("FORUM_ERROR_NO_TITLE"))?>";

if (typeof oText != "object")
	var oText = {};

oText['author'] = " <?=CUtil::addslashes(GetMessage("JQOUTE_AUTHOR_WRITES"))?>:\n";
oText['translit_en'] = "<?=CUtil::addslashes(GetMessage("FORUM_TRANSLIT_EN"))?>";
oText['enter_url'] = "<?=CUtil::addslashes(GetMessage("FORUM_TEXT_ENTER_URL"))?>";
oText['enter_url_name'] = "<?=CUtil::addslashes(GetMessage("FORUM_TEXT_ENTER_URL_NAME"))?>";
oText['enter_image'] = "<?=CUtil::addslashes(GetMessage("FORUM_TEXT_ENTER_IMAGE"))?>";
oText['list_prompt'] = "<?=CUtil::addslashes(GetMessage("FORUM_LIST_PROMPT"))?>";

if (typeof oHelp != "object")
	var oHelp = {};

oHelp['B'] = "<?=CUtil::addslashes(GetMessage("FORUM_HELP_BOLD"))?>";
oHelp['I'] = "<?=CUtil::addslashes(GetMessage("FORUM_HELP_ITALIC"))?>";
oHelp['U'] = "<?=CUtil::addslashes(GetMessage("FORUM_HELP_UNDER"))?>";
oHelp['FONT'] = "<?=CUtil::addslashes(GetMessage("FORUM_HELP_FONT"))?>";
oHelp['COLOR'] = "<?=CUtil::addslashes(GetMessage("FORUM_HELP_COLOR"))?>";
oHelp['CLOSE'] = "<?=CUtil::addslashes(GetMessage("FORUM_HELP_CLOSE"))?>";
oHelp['URL'] = "<?=CUtil::addslashes(GetMessage("FORUM_HELP_URL"))?>";
oHelp['IMG'] = "<?=CUtil::addslashes(GetMessage("FORUM_HELP_IMG"))?>";
oHelp['QUOTE'] = "<?=CUtil::addslashes(GetMessage("FORUM_HELP_QUOTE"))?>";
oHelp['LIST'] = "<?=CUtil::addslashes(GetMessage("FORUM_HELP_LIST"))?>";
oHelp['CODE'] = "<?=CUtil::addslashes(GetMessage("FORUM_HELP_CODE"))?>";
oHelp['CLOSE_CLICK'] = "<?=CUtil::addslashes(GetMessage("FORUM_HELP_CLICK_CLOSE"))?>";
oHelp['TRANSLIT'] = "<?=CUtil::addslashes(GetMessage("FORUM_HELP_TRANSLIT"))?>";

var messageTrSystem = "<?=CUtil::addslashes(GetMessage("SONET_CT94_SYSTEM"))?>";
var messageTrTalkOnline = "<?=CUtil::addslashes(GetMessage("SONET_CT94_ONLINE"))?>";
var messageTrTalkOutline = "<?=CUtil::addslashes(GetMessage("SONET_CT94_OFFLINE"))?>";
var messageTrError = "<?=CUtil::addslashes(GetMessage("SONET_CT94_ERROR"))?>";
var messageNewMessage = "<?=CUtil::addslashes(GetMessage("SONET_NEW_MESSAGE"))?>";
var messageNetworkError = "<?=CUtil::addslashes(GetMessage("SONET_NET_ERROR"))?>";
var messSoundOn = "<?=CUtil::addslashes(GetMessage("SONET_SOUND_ON"))?>";
var messSoundOff = "<?=CUtil::addslashes(GetMessage("SONET_SOUND_OFF"))?>";
var messSeachSuggest = "<?=CUtil::addslashes(GetMessage("SONET_CHAT_SEARCH"))?>";
var messUserOnline = "<?=CUtil::addslashes(GetMessage("SONET_C50_T_ONLINE"))?>";
var messUserOffline = "<?=CUtil::addslashes(GetMessage("SONET_CHAT_OFFLINE"))?>";

var mmTrMonth = new Array('<?=CUtil::addslashes(GetMessage("SONET_CT94_1"))?>', '<?=CUtil::addslashes(GetMessage("SONET_CT94_2"))?>', '<?=CUtil::addslashes(GetMessage("SONET_CT94_3"))?>', '<?=CUtil::addslashes(GetMessage("SONET_CT94_4"))?>', '<?=CUtil::addslashes(GetMessage("SONET_CT94_5"))?>', '<?=CUtil::addslashes(GetMessage("SONET_CT94_6"))?>', '<?=CUtil::addslashes(GetMessage("SONET_CT94_7"))?>', '<?=CUtil::addslashes(GetMessage("SONET_CT94_8"))?>', '<?=CUtil::addslashes(GetMessage("SONET_CT94_9"))?>', '<?=CUtil::addslashes(GetMessage("SONET_CT94_10"))?>', '<?=CUtil::addslashes(GetMessage("SONET_CT94_11"))?>', '<?=CUtil::addslashes(GetMessage("SONET_CT94_12"))?>');

var sonetChatMsgAddPath = '<?= $arResult["MsgAddPath"] ?>';
var sonetChatMsgGetPath = '<?= $arResult["MsgGetPath"] ?>';
var sonetChatSessid = '<?= bitrix_sessid_get() ?>';
var sonetChatUserId = '<?= $arResult["User"]["ID"] ?>';
var sonetChatTimeout = <?= 10.00 ?>;
var sonetChatUser = '<?= CUtil::JSEscape($arResult["User"]["NAME_FORMATTED"]) ?>';
var sonetSelfUser = '<?= CUtil::JSEscape($arResult["UserSelf"]["NAME_FORMATTED"]) ?>';
var sonetChatLastDate = '<?= $arResult["ChatLastDate"] ?>';
var sonetChatNowDate = '<?= $arResult["Now"]; ?>';
var sonetChatReplyMesageId = '<?= $arResult["REPLY_MESSAGE_ID"]; ?>';
var sonetSoundOn = <?= ($arResult["USER_OPTIONS"]["sound"] == "Y"? "true":"false")?>;
var sonetReplyPathTemplate = '<?= CUtil::JSEscape($arParams["PATH_TO_MESSAGE_FORM_MESS"])?>';
</script>
<script src="/bitrix/components/bitrix/player/mediaplayer/flvscript.js<?echo (mb_strpos($_SERVER["HTTP_USER_AGENT"], "Opera") !== false? '':'?v='.filemtime($_SERVER['DOCUMENT_ROOT'].'/bitrix/components/bitrix/player/mediaplayer/flvscript.js'))?>"></script>

<div style="position:absolute; top:-1000px; left:-1000px;">
<div id="bx_flv_player_incoming_div" style="display:none">
<object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000"
codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=8"
width="1" height="1"
id="socnet_player">
	<param name=movie value="/bitrix/components/bitrix/player/mediaplayer/player">
	<param name=quality value="high">
	<param name=swLiveConnect value="true">
	<param name=allowScriptAccess value="always">
	<param name="FlashVars" value="file=/bitrix/sounds/socialnetwork/incoming_message.mp3&controlbar=none&autostart=false&bufferlength=10">

	<embed type="application/x-shockwave-flash"
	pluginspage="http://www.macromedia.com/go/getflashplayer"
	width="1" height="1"
	id="socnet_player_embed"
	src="/bitrix/components/bitrix/player/mediaplayer/player"
	flashvars="file=/bitrix/sounds/socialnetwork/incoming_message.mp3&controlbar=none&autostart=false&bufferlength=10">
	</embed>
</object>
</div>
</div>
<script>
showFLVPlayer("bx_flv_player_incoming", "");
</script>

<table cellpadding="0" cellspacing="0" border="0" width="100%">
	<tr valign="top">
		<td>

	<div class="socnet-chat-info" id="socnet_chat_info">

	<table cellspacing="0" cellpadding="0">
		<tr valign="top">
<?if($arParams["GROUP_ID"] > 0):?>
			<td class="bx-photo"><?= $arResult["Group"]["IMAGE_ID_IMG"] ?></td>
			<td>
				<b><?= $arResult["Group"]["NAME"] ?></b>
			</td>
<?else:?>
			<td class="bx-photo"><?= $arResult["User"]["PersonalPhotoImg"] ?></td>
			<td>
				<div class="socnet-user-name"><?= $arResult["User"]["NAME_FORMATTED"] ?></div>
				<div id="socnet_user_online" class="bx-icon <?= ($arResult["IS_ONLINE"]? "bx-icon-online":"bx-icon-offline")?>" title="<?= ($arResult["IS_ONLINE"]? GetMessage("SONET_C50_T_ONLINE"):GetMessage("SONET_CHAT_OFFLINE")) ?>"></div>
				<div id="socnet_user_online_text"><?= ($arResult["IS_ONLINE"]? GetMessage("SONET_C50_T_ONLINE"):GetMessage("SONET_CHAT_OFFLINE")) ?></div>
			</td>
<?
if($arResult['IS_BIRTHDAY'] || $arResult['IS_ABSENT'] || $arResult['IS_FEATURED']):
?>
			<td class="bx-border">
				<?if($arResult['IS_BIRTHDAY']):?><div class="bx-icon bx-icon-birth" title="<?= GetMessage("SONET_C50_T_BIRTHDAY") ?>"></div><?endif;?>
				<?if($arResult['IS_FEATURED']):?><div class="bx-icon bx-icon-featured" title="<?= GetMessage("SONET_C50_T_FEATURED") ?>"></div><?endif;?>
				<?if($arResult['IS_ABSENT']):?><div class="bx-icon bx-icon-away" title="<?= GetMessage("SONET_C50_T_AWAY") ?>"></div><?endif;?>
			</td>
<?
endif;
?>
<?
endif;
?>
			<td class="bx-border">
			<?if($arParams["GROUP_ID"] > 0):?>
				<a class="socnet-button socnet-button-group-profile" href="<?= $arResult["Urls"]["Group"] ?>" target="_blank" title="<?echo GetMessage("SONET_GROUP_PROFILE")?>"></a>
			<?else:?>
				<?if ($arResult["CurrentUserPerms"]["Operations"]["viewprofile"]):?>
					<a class="socnet-button socnet-button-profile" href="<?= $arResult["Urls"]["User"] ?>" target="_blank" title="<?= GetMessage("SONET_C50_T_TO_PROFILE_ALT") ?>"></a>
				<?endif;?>
				<a class="socnet-button socnet-button-history" href="<?= $arResult["Urls"]["UserMessages"] ?>" target="_blank" title="<?= GetMessage("SONET_C50_T_TO_HISTORY_ALT") ?>"></a>
				<?if ($arResult["CurrentUserPerms"]["Operations"]["videocall"]):?>
					<a class="socnet-button socnet-button-videocall" href="<?= $arResult["Urls"]["VideoCall"] ?>" target="_blank" onclick="window.open('<?= $arResult["Urls"]["VideoCall"] ?>', '', 'location=yes,status=no,scrollbars=yes,resizable=yes,width=1000,height=600,top='+Math.floor((screen.height - 600)/2-14)+',left='+Math.floor((screen.width - 1000)/2-5)); return false;" title="<?= GetMessage("SONET_C50_T_TO_VIDEO_CALL_ALT") ?>"></a>
				<?endif;?>
				<a class="socnet-button socnet-button-messages" href="javascript:sonet_load_last_char();" title="<?= GetMessage("SONET_C50_T_LAST_CHAT_ALT") ?>"></a>
			<?endif;?>
			<a href="javascript:void(0);" class="socnet-button <?=($arResult["USER_OPTIONS"]["sound"] == "Y"? "socnet-sound-on":"socnet-sound-off")?>" title="<?=($arResult["USER_OPTIONS"]["sound"] == "Y"? GetMessage("PM_SOUND_ON"):GetMessage("PM_SOUND_OFF"))?>" onclick="sonet_switch_sound(this)"></a>
			</td>
		</tr>
	</table>
	</div>

	<div id="sonet_chat_messages"></div>

	<div class="socnet-chat-form" id="socnet_chat_form">
		<div class="socnet-chat-buttons">

			<div class="form-button button-bold" id="form_b" title="<?=GetMessage("PM_BOLD")?>"></div>
			<div class="form-button button-italic" id="form_i" title="<?=GetMessage("PM_ITAL")?>"></div>
			<div class="form-button button-underline" id="form_u" title="<?=GetMessage("PM_UNDER")?>"></div>
			<div class="form-button button-font">
				<select name="FONT" id="form-font" title="<?=GetMessage("PM_FONT")?>">
					<option value="0"><?=GetMessage("PM_FONT")?></option>
					<option value="Arial" style="font-family:Arial">Arial</option>
					<option value="Times" style="font-family:Times">Times</option>
					<option value="Courier" style="font-family:Courier">Courier</option>
					<option value="Impact" style="font-family:Impact">Impact</option>
					<option value="Geneva" style="font-family:Geneva">Geneva</option>
					<option value="Optima" style="font-family:Optima">Optima</option>
					<option value="Verdana" style="font-family:Verdana">Verdana</option>
				</select>
			</div>
			<div class="form-button button-color" id="form_palette" title="<?=GetMessage("PM_COLOR")?>"></div>
			<div class="form-button button-url" id="form_url" title="<?=GetMessage("PM_HYPERLINK_TITLE")?>"></div>
			<div class="form-button button-img" id="form_img" title="<?=GetMessage("PM_IMAGE_TITLE")?>"></div>
			<div class="form-button button-quote" id="form_quote" title="<?=GetMessage("PM_QUOTE_TITLE")?>"></div>
			<br style="clear:both;" />
		</div>
		<div class="socnet-chat-smiles">
<?
$res_str = '';
foreach($arResult["PrintSmilesList"] as $res)
{
	$strTYPING = strtok($res['TYPING'], " ");
	$res_str .= "<img src=\"".$arParams["PATH_TO_SMILE"].$res['IMAGE']."\" alt=\"".$res['NAME']."\" title=\"".$res['NAME']."\" border=\"0\"";
	if (intval($res['IMAGE_WIDTH'])>0) {$res_str .= " width=\"".$res['IMAGE_WIDTH']."\"";}
	if (intval($res['IMAGE_HEIGHT'])>0) {$res_str .= " height=\"".$res['IMAGE_HEIGHT']."\"";}
	$res_str .= " class=\"chat-smile\"  id=\"".$strTYPING."\" ";
	$res_str .= "/>";
}
echo $res_str;
?>
			<br style="clear:both;" />
		</div>

		<div class="socnet-chat-input">
			<?=bitrix_sessid_post()?>
			<textarea id="post_message_id" name="POST_MESSAGE" rows="5"></textarea><br>
			<input id="post_message_button" type="submit" name="save" value="<?= GetMessage("SONET_C50_T_SEND_MESSAGE") ?>">
			Ctrl+Enter<br>
		</div>
	</div>

</td>

<?
//contact list
?>
<td class="socnet-user-group-divider" valign="middle" onmousedown="sonet_start_drag(arguments[0]);">
	<a class="socnet-divider<?=(is_array($arResult["Users"]["List"]) || $arResult["USER_OPTIONS"]["contacts"] == "Y"? ' socnet-divider-right':'')?>" href="javascript:void(0)" onclick="sonet_group_resize(this)" title="<?echo GetMessage("SOCNET_CHAT_RESIZE")?>"></a>
</td>

<td class="socnet-user-group" style="display:<?=(is_array($arResult["Users"]["List"]) || $arResult["USER_OPTIONS"]["contacts"] == "Y"? '':'none')?>;<?if($arResult["USER_OPTIONS"]["contacts_width"] > 0) echo ' width:'.$arResult["USER_OPTIONS"]["contacts_width"].'px;'?>" id="socnet_user_group_cell">
<div id="socnet_user_group"<?if($arResult["USER_OPTIONS"]["contacts_width"] > 0) echo ' style="width:'.$arResult["USER_OPTIONS"]["contacts_width"].'px"'?>>

<div id="socnet_chat_selectors">

<div class="socnet-group-search">
	<input type="text" name="search" value="<?echo GetMessage("SONET_CHAT_SEARCH")?>" onfocus="sonet_search_focus(this)" onblur="sonet_search_blur(this)" onkeyup="sonet_search_keypress(this)" title="<?echo GetMessage("SONET_CHAT_SEARCH_TITLE")?>">
</div>

<div>
<?
//socialnetwork group
if(is_array($arResult["Users"]["List"])):
?>
<div class="socnet-selector socnet-selector-active" id="socnet_selector_group" onclick="sonet_set_selector(this)"><?echo GetMessage("SOCNET_CHAT_GROUP")?></div>
<?endif?>
<?
//recent users
if(is_array($arResult["RecentUsers"])):
?>
<div class="socnet-selector<?if(!is_array($arResult["Users"]["List"])) echo " socnet-selector-active"?>" id="socnet_selector_recent" onclick="sonet_set_selector(this)"><?echo GetMessage("SOCNET_CHAT_RECENT")?></div>
<?endif?>
<?
//friends
if(is_array($arResult["Friends"])):
?>
<div class="socnet-selector" id="socnet_selector_friends" onclick="sonet_set_selector(this)"><?echo (CModule::IncludeModule('extranet') && CExtranet::IsExtranetSite() ?  GetMessage("SOCNET_CHAT_MY_CONTACTS") : GetMessage("SOCNET_CHAT_FRIENDS"))?></div>
<?endif?>
<?
//intranet structure
if(is_array($arResult["Structure"])):
?>
<div class="socnet-selector" id="socnet_selector_structure" onclick="sonet_set_selector(this)"><?echo GetMessage("SOCNET_CHAT_STRUCTURE")?></div>
<?endif?>
</div>

<div class="socnet-group-separator"></div>

</div>

<div id="socnet_user_list"<?if($arResult["USER_OPTIONS"]["contacts_width"] > 0) echo ' style="width:'.($arResult["USER_OPTIONS"]["contacts_width"]-2).'px"'?>>
<table cellpadding="0" cellspacing="0" border="0" width="100%">
	<tr>
		<td>
<?
//socialnetwork group
if(is_array($arResult["Users"]["List"])):
?>
<div id="socnet_selector_group_div" style="display:block">
<div class="socnet-user-section">
<table cellspacing="0">
<tr>
	<td><div id="group_arrow" class="socnet-arrow socnet-arrow-down" title="<?echo GetMessage("SONET_SECT_TITLE")?>" onclick="sonet_switch_section(this, 'group_<?=$arResult["Group"]["ID"]?>_block');"></div></td>
	<td><input type="checkbox" name="" value="" id="group_<?=$arResult["Group"]["ID"]?>" checked onclick="sonet_check_group(this);"></td>
	<td class="socnet-contact-section" ondblclick="sonet_switch_section(document.getElementById('group_arrow'), 'group_<?=$arResult["Group"]["ID"]?>_block');"><?= $arResult["Group"]["NAME"]?></td>
</tr>
</table>
</div>
<div style="display:block;" id="group_<?=$arResult["Group"]["ID"]?>_block" class="socnet-user-contact-block">

<?foreach($arResult["Users"]["List"] as $gr_user):?>
<div class="socnet-user-contact">
<table cellspacing="0">
<tr>
	<td><div class="socnet-indent"></div></td>
	<td><input type="checkbox" name="USER_ID[]" value="<?=$gr_user["USER_ID"]?>" id="user_<?=$gr_user["USER_ID"]?>" checked></td>
	<td>
		<?if($gr_user["SHOW_PROFILE_LINK"]):?>
			<a class="socnet-status <?=($gr_user["IS_ONLINE"]? 'socnet-online':'socnet-offline')?>" href="<?=$gr_user["USER_PROFILE_URL"]?>" title="<?echo GetMessage("SONET_PROFILE")?>" target="_blank"></a>
		<?else:?>
			<div class="socnet-status <?=($gr_user["IS_ONLINE"]? 'socnet-online':'socnet-offline')?>"></div>
		<?endif;?>
	</td>
	<td class="socnet-contact-user"><a href="<?= $gr_user['PATH_TO_MESSAGES_CHAT'] ?>" onclick="sonet_open_chat(this); return false;" title="<?= GetMessage("SONET_C39_SEND_MESSAGE") ?>"><?=$gr_user["USER_NAME_FORMATTED"]?></a></td>
</tr>
</table>
</div>

<?endforeach;?>
</div>
</div>
<?
//end of socialnetwork group
endif;
?>

<?
//friends
if(is_array($arResult["Friends"])):
?>
<div id="socnet_selector_friends_div" style="display:none">
<div class="socnet-user-section">
<table cellspacing="0">
<tr>
	<td><div id="friends_arrow" class="socnet-arrow socnet-arrow-down" title="<?echo GetMessage("SONET_SECT_TITLE")?>" onclick="sonet_switch_section(this, 'friends_block');"></div></td>
	<td><input type="checkbox" name="" value="" id="friends" onclick="sonet_check_group(this);"></td>
	<td class="socnet-contact-section" ondblclick="sonet_switch_section(document.getElementById('friends_arrow'), 'friends_block');"><?echo (CModule::IncludeModule('extranet') && CExtranet::IsExtranetSite() ? GetMessage("SOCNET_CHAT_MY_CONTACTS_ALL") : GetMessage("SOCNET_CHAT_ALL_FRIENDS"))?></td>
</tr>
</table>
</div>
<div style="display:block;" id="friends_block" class="socnet-user-contact-block">

<?foreach($arResult["Friends"] as $friend):?>
<div class="socnet-user-contact">
<table cellspacing="0">
<tr>
	<td><div class="socnet-indent"></div></td>
	<td><input type="checkbox" name="USER_ID[]" value="<?=$friend["USER_ID"]?>" id="friend_<?=$friend["USER_ID"]?>"></td>
	<td>
		<?if($friend["SHOW_PROFILE_LINK"]):?>
			<a class="socnet-status <?=($friend["IS_ONLINE"]? 'socnet-online':'socnet-offline')?>" href="<?=$friend["USER_PROFILE_URL"]?>" title="<?echo GetMessage("SONET_PROFILE")?>" target="_blank"></a>
		<?else:?>
			<div class="socnet-status <?=($friend["IS_ONLINE"]? 'socnet-online':'socnet-offline')?>"></div>
		<?endif;?>
	</td>
	<td class="socnet-contact-user"><a href="<?= $friend['PATH_TO_MESSAGES_CHAT'] ?>" onclick="sonet_open_chat(this); return false;" title="<?= GetMessage("SONET_C39_SEND_MESSAGE") ?>"><?=$friend["USER_NAME_FORMATTED"]?></a></td>
</tr>
</table>
</div>

<?endforeach;?>
</div>
</div>
<?
//end of friends
endif;
?>

<?
//recent
if(is_array($arResult["RecentUsers"])):
?>
<div id="socnet_selector_recent_div" style="display:<?=(is_array($arResult["Users"]["List"])? "none":"block")?>">
<div class="socnet-user-section">
<table cellspacing="0">
<tr>
	<td><div id="recent_arrow" class="socnet-arrow socnet-arrow-down" title="<?echo GetMessage("SONET_SECT_TITLE")?>" onclick="sonet_switch_section(this, 'recent_block');"></div></td>
	<td><input type="checkbox" name="" value="" id="recent" onclick="sonet_check_group(this);"></td>
	<td class="socnet-contact-section" ondblclick="sonet_switch_section(document.getElementById('recent_arrow'), 'recent_block');"><?echo GetMessage("SOCNET_CHAT_RECENT")?></td>
</tr>
</table>
</div>
<div style="display:block;" id="recent_block" class="socnet-user-contact-block">

<?foreach($arResult["RecentUsers"] as $recent):?>
<div class="socnet-user-contact">
<table cellspacing="0">
<tr>
	<td><div class="socnet-indent"></div></td>
	<td><input type="checkbox" name="USER_ID[]" value="<?=$recent["USER_ID"]?>" id="recent_<?=$recent["USER_ID"]?>"></td>
	<td>
		<?if($recent["SHOW_PROFILE_LINK"]):?>
			<a class="socnet-status <?=($recent["IS_ONLINE"]? 'socnet-online':'socnet-offline')?>" href="<?=$recent["USER_PROFILE_URL"]?>" title="<?echo GetMessage("SONET_PROFILE")?>" target="_blank"></a>
		<?else:?>
			<div class="socnet-status <?=($recent["IS_ONLINE"]? 'socnet-online':'socnet-offline')?>"></div>
		<?endif;?>
	</td>
	<td class="socnet-contact-user"><a href="<?= $recent['PATH_TO_MESSAGES_CHAT'] ?>" onclick="sonet_open_chat(this); return false;" title="<?= GetMessage("SONET_C39_SEND_MESSAGE") ?>"><?=$recent["USER_NAME_FORMATTED"]?></a></td>
</tr>
</table>
</div>

<?endforeach;?>
</div>
</div>
<?
//end of recent
endif;
?>


<?
//intranet structure
if(is_array($arResult["Structure"])):

function socnet_show_section(&$arStructure, &$arResult, $CUR_LEVEL = 1)
{
	while(list($key, $department) = current($arStructure)):
		next($arStructure);
		if($department["DEPTH_LEVEL"]==$CUR_LEVEL):
			?>
			<div class="socnet-user-section">
				<table cellspacing="0">
				<tr>
					<?echo str_repeat('<td><div class="socnet-indent"></div></td>', $department["DEPTH_LEVEL"]-1)?>
					<td><div id="dep_<?=$department["ID"]?>_arrow" class="socnet-arrow socnet-arrow-right" title="<?echo GetMessage("SONET_SECT_TITLE")?>" onclick="sonet_switch_section(this, 'dep_<?=$department["ID"]?>_block');"></div></td>
					<td><input type="checkbox" name="" value="" id="dep_<?=$department["ID"]?>" onclick="sonet_check_group(this);"></td>
					<td class="socnet-contact-section" ondblclick="sonet_switch_section(document.getElementById('dep_<?=$department["ID"]?>_arrow'), 'dep_<?=$department["ID"]?>_block');"><?= $department["NAME"]?></td>
				</tr>
				</table>
			</div>
			<div style="display:none;" id="dep_<?=$department["ID"]?>_block" class="socnet-user-contact-block">
				<?
				$bExit = false;
				if(list($key, $subdepartment) = current($arStructure))
				{
					if($subdepartment["DEPTH_LEVEL"] > $department["DEPTH_LEVEL"])
						socnet_show_section($arStructure, $arResult, $CUR_LEVEL+1);
					if($subdepartment["DEPTH_LEVEL"] < $department["DEPTH_LEVEL"])
						$bExit = true;
				}
				?>
				<?
				if(is_array($arResult["UsersInStructure"][$department["ID"]])):
					foreach($arResult["UsersInStructure"][$department["ID"]] as $dep_user):
						?><div class="socnet-user-contact">
							<table cellspacing="0">
							<tr>
								<?echo str_repeat('<td><div class="socnet-indent"></div></td>', $department["DEPTH_LEVEL"])?>
								<td><input type="checkbox" name="USER_ID[]" value="<?=$dep_user["USER_ID"]?>" id="dep_user_<?=$dep_user["USER_ID"]?>"></td>
								<td>
								<?if($dep_user["SHOW_PROFILE_LINK"]):?>
									<a class="socnet-status <?=($dep_user["IS_ONLINE"]? 'socnet-online':'socnet-offline')?>" href="<?=$dep_user["USER_PROFILE_URL"]?>" title="<?echo GetMessage("SONET_PROFILE")?>" target="_blank"></a>
								<?else:?>
									<div class="socnet-status <?=($dep_user["IS_ONLINE"]? 'socnet-online':'socnet-offline')?>"></div>
								<?endif;?>
								</td>
								<td class="socnet-contact-user"><a href="<?= $dep_user['PATH_TO_MESSAGES_CHAT'] ?>" onclick="sonet_open_chat(this); return false;" title="<?= GetMessage("SONET_C39_SEND_MESSAGE") ?>"><?=$dep_user["USER_NAME_FORMATTED"]?></a></td>
							</tr>
							</table>
						</div><?
					endforeach;
				endif;
				?>
			</div>
			<?
			if($bExit):
				return;
			endif;

		else:
			prev($arStructure);
			return;
		endif;
	endwhile;
}
?>
<div id="socnet_selector_structure_div" style="display:none">
<?
socnet_show_section($arResult["Structure"], $arResult);
?>
</div>
<?
//end of intranet structure
endif;
?>
</td>
	</tr>
</table>
</div>
</div>
</td>
<?
//end of contact list
?>
	</tr>
</table>
<?
}
?>
</form>
</div>