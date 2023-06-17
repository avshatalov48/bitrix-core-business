<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
	die();

CPageTemplate::IncludeLangFile(__FILE__);

class CForumPageTemplate
{
	function GetDescription()
	{
		return array(
			"name"=>GetMessage("forum_template_name"), 
			"description"=>GetMessage("forum_template_desc"),
			"icon"=>"/bitrix/themes/.default/start_menu/forum/forum.gif",
			"modules"=>array("forum"),
			"type"=>"section",
		);
	}
	
	function GetFormHtml()
	{
		if(!CModule::IncludeModule('forum'))
			return '';

		$s = '
<tr class="section">
	<td colspan="2">'.GetMessage("forum_template_settings").'</td>
</tr>
<tr>
	<td class="bx-popup-label">'.GetMessage("forum_template_forums").'</td>
	<td><select name="forum_FID[]" size="4" multiple>
		<option value="" selected>'.GetMessage("forum_template_forums_all").'</option>';
		$arForums = CForumParameters::GetForumsList();
		foreach($arForums as $key=>$val)
			$s .= '<option value="'.$key.'">'.$val.'</option>';
	$s .= '
	</select></td>
</tr>
';		
		$arThemesMessages = array(
			"beige" => GetMessage("F_THEME_BEIGE"), 
			"blue" => GetMessage("F_THEME_BLUE"), 
			"fluxbb" => GetMessage("F_THEME_FLUXBB"), 
			"gray" => GetMessage("F_THEME_GRAY"), 
			"green" => GetMessage("F_THEME_GREEN"), 
			"orange" => GetMessage("F_THEME_ORANGE"), 
			"red" => GetMessage("F_THEME_RED"), 
			"white" => GetMessage("F_THEME_WHITE"));
		$arThemes = array();
		$dir = $_SERVER["DOCUMENT_ROOT"].BX_ROOT."/components/bitrix/forum/templates/.default/themes/";
		if (is_dir($dir) && $directory = opendir($dir))
		{
			while (($file = readdir($directory)) !== false)
			{
				if ($file != "." && $file != ".." && is_dir($dir.$file))
					$arThemes[$file] = (!empty($arThemesMessages[$file]) ? $arThemesMessages[$file] : mb_strtoupper(mb_substr($file, 0, 1)).mb_strtolower(mb_substr($file, 1)));
			}
			closedir($directory);
		}
		
		if(!empty($arThemes))
		{
			$s .= '
<tr>
<td class="bx-popup-label">'.GetMessage("forum_template_theme").'</td>
<td><select name="forum_THEME">';
		foreach($arThemes as $key=>$val)
			$s .= '<option value="'.$key.'">'.$val.'</option>';
		$s .= '
	</select></td>
</tr>
';
		}

		if (IsModuleInstalled("vote"))
		{
			$right = $GLOBALS["APPLICATION"]->GetGroupRight("vote");
			if ($right >= "W")
			{
				$s .= '
<tr class="section">
	<td colspan="2">'.GetMessage("forum_template_vote").'</td>
</tr>
<tr>
	<td class="bx-popup-label"><label for="forum_SHOW_VOTE">'.GetMessage("forum_template_vote_enable").'</label></td>
	<td>
<script>
window.ForumVoteClick = function(el)
{
	document.getElementById("forum_vote_group").style.display = (el.checked? "":"none");
	document.getElementById("forum_user_group").style.display = (el.checked? "":"none");
}

window.ForumVoteChannelClick = function(el)
{
	if(el.form.forum_VOTE_CHANNEL_ID)
		el.form.forum_VOTE_CHANNEL_ID.disabled = (el.value == "Y");
}
</script>
		<input type="checkbox" name="forum_SHOW_VOTE" id="forum_SHOW_VOTE" value="Y" onclick="ForumVoteClick(this);">
	</td>
</tr>
<tr id="forum_vote_group" style="display:none;">
	<td class="bx-popup-label">'.GetMessage("forum_template_vote_channel").'</td>
	<td>
		<input type="radio" name="forum_NEW_VOTE_CHANNEL" value="Y" id="forum_NEW_VOTE_CHANNEL_Y" checked onclick="ForumVoteChannelClick(this);"><label for="forum_NEW_VOTE_CHANNEL_Y">'.GetMessage("forum_template_vote_channel_new").'</label><br>
';
				$arVoteChannels = array();
				CModule::IncludeModule("vote");
				$db_res = CVoteChannel::GetList("s_title", "asc", array("ACTIVE" => "Y"));
				if($db_res && $res=$db_res->Fetch())
				{
					$s .= '
		<input type="radio" name="forum_NEW_VOTE_CHANNEL" value="N" id="forum_NEW_VOTE_CHANNEL_N" onclick="ForumVoteChannelClick(this);"><label for="forum_NEW_VOTE_CHANNEL_N">'.GetMessage("forum_template_vote_channel_select").':</label><br>
		<select name="forum_VOTE_CHANNEL_ID" style="width:100%" disabled>';
					do 
						$s .= '<option value="'.$res["ID"].'">'.htmlspecialcharsbx($res["TITLE"])." [".$res["ID"]."]".'</option>';
					while ($res = $db_res->Fetch());
					$s .= '</select>';
				}
				else
				{
					$s .= '
		<input type="radio" name="forum_NEW_VOTE_CHANNEL" value="N" id="forum_NEW_VOTE_CHANNEL_N" disabled><label for="forum_NEW_VOTE_CHANNEL_N" disabled>'.GetMessage("forum_template_vote_channel_select").'</label><br>
';
				}
				$s .= '
	</td>
</tr>
';
				$s .= '
<tr id="forum_user_group" style="display:none;">
	<td class="bx-popup-label">'.GetMessage("forum_template_vote_groups").'</td>
	<td><select name="forum_VOTE_GROUP_ID[]" size="4" multiple>
';
				$db_res = CGroup::GetList();
				while($res = $db_res->Fetch())
					$s .= '<option value="'.$res["ID"].'">'.htmlspecialcharsbx($res["NAME"])." [".$res["ID"]."]".'</option>';
				$s .= '
		</select>
	</td>
</tr>
';					
			}
		}
		return $s;
	}

	function CheckArray($array)
	{
		$ar = array();
		if(is_array($array))
		{
			foreach($array as $val)
				$ar[] = intval($val);
			$ar = array_unique($ar);
		}
		return $ar;
	}

	function GetContent($arParams)
	{
		//check theme
		$dir = $_SERVER["DOCUMENT_ROOT"].BX_ROOT."/components/bitrix/forum/templates/.default/themes/";
		$theme = '';
		if(is_dir($dir) && $directory = opendir($dir))
		{
			while (($file = readdir($directory)) !== false)
			{
				if ($file != "." && $file != ".." && is_dir($dir.$file) && $file == $_POST['forum_THEME'])
				{
					$theme = $_POST['forum_THEME'];
					break;
				}
			}
			closedir($directory);
		}
		
		//check forums
		$arForums = $this->CheckArray($_POST['forum_FID']);
		
		//check voting user groups
		$arGroups = $this->CheckArray($_POST['forum_VOTE_GROUP_ID']);
		
		$vote_channel = 0;
		if(\CModule::IncludeModule("vote") && $GLOBALS["APPLICATION"]->GetGroupRight("vote") >= "W" && $_POST['forum_SHOW_VOTE'] == 'Y')
		{
			if($_POST['forum_NEW_VOTE_CHANNEL'] == 'Y')
			{
				//new voting channel for forum
				//total bullshit - need vote module API 
				$arFields = array(
					"TIMESTAMP_X"		=> $GLOBALS['DB']->GetNowFunction(),
					"C_SORT"			=> "'100'",
					"FIRST_SITE_ID"		=> "'".$GLOBALS['DB']->ForSql($arParams['site'], 2)."'",
					"ACTIVE"			=> "'Y'",
					"VOTE_SINGLE"		=> "'N'",
					"TITLE"				=> "'".$GLOBALS['DB']->ForSql(GetMessage("forum_template_vote_name"), 255)."'",
					"SYMBOLIC_NAME"		=> "'FORUM_POLLS'",
				);
				$vote_channel = $GLOBALS['DB']->Insert("b_vote_channel", $arFields);
	
				if(VOTE_CACHE_TIME !== false) 
					$GLOBALS['CACHE_MANAGER']->CleanDir("b_vote_channel");
				
				if($vote_channel > 0)
				{
					$GLOBALS['DB']->Query("INSERT INTO b_vote_channel_2_site (CHANNEL_ID, SITE_ID) VALUES ('".$vote_channel."', '".$GLOBALS['DB']->ForSql($arParams['site'], 2)."')");
	
					if(VOTE_CACHE_TIME !== false) 
						$GLOBALS['CACHE_MANAGER']->Clean("b_vote_channel_2_site");
	
					foreach($arGroups as $group)
					{
						$arFields = array(
							"CHANNEL_ID"	=> "'".$vote_channel."'",
							"GROUP_ID"		=> "'".$group."'",
							"PERMISSION"	=> "'2'"
						);
						$GLOBALS['DB']->Insert("b_vote_channel_2_group", $arFields);
					}
	
					if(VOTE_CACHE_TIME!==false) 
						$GLOBALS['CACHE_MANAGER']->CleanDir("b_vote_perm");
				}
			}
			else
			{
				$vote_channel = intval($_POST['forum_VOTE_CHANNEL_ID']);
			}
		}

		$s = '<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
?><?$APPLICATION->IncludeComponent("bitrix:forum", ".default", array(
	"THEME" => "'.EscapePHPString($theme).'",
	"SHOW_TAGS" => "Y",
	"SHOW_AUTH_FORM" => "Y",
	"SHOW_NAVIGATION" => "Y",
	"TMPLT_SHOW_ADDITIONAL_MARKER" => "",
	"SMILES_COUNT" => "100",
	"USE_LIGHT_VIEW" => "Y",
	"FID" => array('.implode(',', $arForums).'),
	"FILES_COUNT" => "5",
	"SEF_MODE" => "Y",
	"SEF_FOLDER" => "'.EscapePHPString($arParams["path"]).'",
	"CACHE_TYPE" => "A",
	"CACHE_TIME" => "3600",
	"CACHE_TIME_USER_STAT" => "60",
	"FORUMS_PER_PAGE" => "15",
	"TOPICS_PER_PAGE" => "10",
	"MESSAGES_PER_PAGE" => "3",
	"TIME_INTERVAL_FOR_USER_STAT" => "10",
	"IMAGE_SIZE" => "500",
	"SET_TITLE" => "Y",
	"USE_RSS" => "Y",
	"RSS_COUNT" => "30",
	"SHOW_VOTE" => "'.($_POST['forum_SHOW_VOTE'] == 'Y' && $vote_channel > 0? 'Y':'N').'",
	"VOTE_CHANNEL_ID" => "'.$vote_channel.'",
	"VOTE_GROUP_ID" => array('.implode(',', $arGroups).'),
	"VOTE_TEMPLATE" => "light",
	"SHOW_SUBSCRIBE_LINK" => "N",
	"SHOW_LEGEND" => "Y",
	"SHOW_STATISTIC_BLOCK" => array(
		0 => "STATISTIC",
		1 => "BIRTHDAY",
		2 => "USERS_ONLINE"),
	"SHOW_NAME_LINK" => "Y",
	"SHOW_FORUMS" => "Y",
	"SHOW_FIRST_POST" => "N",
	"SHOW_AUTHOR_COLUMN" => "N",
	"PAGE_NAVIGATION_TEMPLATE" => "forum",
	"PAGE_NAVIGATION_WINDOW" => "5",
	"WORD_WRAP_CUT" => "23",
	"WORD_LENGTH" => "50",
	"SEO_USER" => "N",
	"USER_PROPERTY" => array(),
	"HELP_CONTENT" => "",
	"RULES_CONTENT" => "",
	"CHECK_CORRECT_PATH_TEMPLATES" => "Y",
	"RSS_CACHE" => "1800",
	"PATH_TO_AUTH_FORM" => "",
	"DATE_FORMAT" => "d.m.Y",
	"DATE_TIME_FORMAT" => "d.m.Y H:i:s",
	"SEND_MAIL" => "E",
	"SEND_ICQ" => "A",
	"SET_NAVIGATION" => "Y",
	"SET_PAGE_PROPERTY" => "Y",
	"DISPLAY_PANEL" => "N",
	"SHOW_FORUM_ANOTHER_SITE" => "Y",
	"RSS_TYPE_RANGE" => array("RSS2"),
	"RSS_TN_TITLE" => "",
	"RSS_TN_DESCRIPTION" => "",
	"VOTE_COUNT_QUESTIONS" => "10",
	"VOTE_COUNT_ANSWERS" => "20",
	"SEF_URL_TEMPLATES" => array(
		"index" => "'.EscapePHPString($arParams["file"]).'",
		"list" => "forum#FID#/",
		"read" => "forum#FID#/topic#TID#/",
		"message" => "messages/forum#FID#/topic#TID#/message#MID#/",
		"help" => "help/",
		"rules" => "rules/",
		"message_appr" => "messages/approve/forum#FID#/topic#TID#/",
		"message_move" => "messages/move/forum#FID#/topic#TID#/message#MID#/",
		"pm_list" => "pm/folder#FID#/",
		"pm_edit" => "pm/folder#FID#/message#MID#/user#UID#/#mode#/",
		"pm_read" => "pm/folder#FID#/message#MID#/",
		"pm_search" => "pm/search/",
		"pm_folder" => "pm/folders/",
		"rss" => "rss/#TYPE#/#MODE#/#IID#/",
		"search" => "search/",
		"subscr_list" => "subscribe/",
		"active" => "topic/new/",
		"topic_move" => "topic/move/forum#FID#/topic#TID#/",
		"topic_new" => "topic/add/forum#FID#/",
		"topic_search" => "topic/search/",
		"user_list" => "users/",
		"profile" => "user/#UID#/edit/",
		"profile_view" => "user/#UID#/",
		"user_post" => "user/#UID#/post/#mode#/",
		"message_send" => "user/#UID#/send/#TYPE#/",
	)
	),
	false
);?><?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>';
		return $s;
	}
}
$pageTemplate = new CForumPageTemplate;
?>