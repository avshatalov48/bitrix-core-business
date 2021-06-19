<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?><?
if (!$this->__component->__parent || empty($this->__component->__parent->__name)):
	$GLOBALS['APPLICATION']->SetAdditionalCSS('/bitrix/components/bitrix/forum/templates/.default/style.css');
	$GLOBALS['APPLICATION']->SetAdditionalCSS('/bitrix/components/bitrix/forum/templates/.default/themes/blue/style.css');
	$GLOBALS['APPLICATION']->SetAdditionalCSS('/bitrix/components/bitrix/forum/templates/.default/styles/additional.css');
endif;
$GLOBALS['APPLICATION']->AddHeadScript("/bitrix/js/main/utils.js");
$arParams["SHOW_MAIL"] = (($arParams["SEND_MAIL"] <= "A" || ($arParams["SEND_MAIL"] <= "E" && !$GLOBALS['USER']->IsAuthorized())) ? "N" : "Y");

/************** User options **************************************/
require_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/classes/".mb_strtolower($GLOBALS["DB"]->type)."/favorites.php");
$arUserOptions = CUserOptions::GetOption("forum", "profile", "", $arParams["UID"]);
$arUserOptions = (CheckSerializedData($arUserOptions) ? @unserialize($arUserOptions, ["allowed_classes" => false]) : array());

$arUserOptions = (is_array($arUserOptions) ? $arUserOptions : array());
if (!is_array($arUserOptions["hide"])):
	$arUserOptions["hide"] = array();
endif;
$arResult["USER"]["SHOW_PANELS"] = array();
foreach (array("photo", "private", "work", "user_prop") as $key):
	$arResult["USER"]["SHOW_PANELS"][$key] = (in_array($key, $arUserOptions["hide"]) ? "N" : "Y");
endforeach;

if ($arResult["SHOW_EDIT_PROFILE"] == "Y" && check_bitrix_sessid() && !empty($_REQUEST["option"]) && 
	in_array($_REQUEST["option"], array("photo", "private", "work", "user_prop"))):
	$bUpdate = false;
	if ($_REQUEST["value"] == "hide" && !in_array($_REQUEST["option"], $arUserOptions["hide"])):
		$arUserOptions["hide"][] = $_REQUEST["option"];
		$bUpdate = true;
	elseif ($_REQUEST["value"] != "hide" && in_array($_REQUEST["option"], $arUserOptions["hide"])):
		$key = intval(array_search($_REQUEST["option"], $arUserOptions["hide"]));
		unset($arUserOptions["hide"][$key]);
		$bUpdate = true;
	endif;
	if ($bUpdate):
		CUserOptions::SetOption("forum", "profile", serialize($arUserOptions), false, $arParams["UID"]);
	endif;
	$arResult["USER"]["SHOW_PANELS"][$_REQUEST["option"]] = ($_REQUEST["value"] == "hide" ? "N" : "Y");
endif;

if (COption::GetOptionString("forum", "show_avatar_photo", "N") == 'Y')
{
	$arResult["USER"]["SHOW_PANELS"]["photo"] = 'Y';
}

if ($arParams["SHOW_RATING"] == 'Y')
{
	$authorId = intval($arParams["UID"]);
	$arRatingResult = CRatings::GetRatingResult($arParams["RATING_ID"], $authorId);
	$arRatingVote	= CRatings::GetRatingVoteResult('USER', $authorId);
}

$bShowedInfo = false;
?><script type="text/javascript">
function forumAddSessid(el)
{
	if (!!el || !!el.href) { el.href += (el.href.indexOf("?") > 0 ? "&" : "?") + 'sessid=' + BX.bitrix_sessid(); }
	return true;
}
</script>
<?
/*******************************************************************/
if (!empty($arResult["ERROR_MESSAGE"])): 
?>
<div class="forum-note-box forum-note-error">
	<div class="forum-note-box-text"><?=ShowError($arResult["ERROR_MESSAGE"], "forum-note-error");?></div>
</div>
<?
endif;
if (!empty($arResult["OK_MESSAGE"])): 
?>
<div class="forum-note-box forum-note-success">
	<div class="forum-note-box-text"><?=ShowNote($arResult["OK_MESSAGE"], "forum-note-success")?></div>
</div>
<?
endif;
/*******************************************************************/
?>
<div class="forum-header-box">
<?
if ($arResult["SHOW_EDIT_PROFILE"] == "Y"):
?>
	<div class="forum-header-options">
		
<?
	if ($USER->GetID() == $arParams["UID"]):
?>
		<span class="forum-option-subscribe">
			<noindex><a rel="nofollow" href="<?=$arResult["URL"]["SUBSCRIBE"]?>" title="<?=GetMessage('F_SUBSCRIBE_TITLE')?>"><?=GetMessage("F_SUBSCRIBE")?></a></noindex>
		</span>&nbsp;&nbsp;
<?
	endif;
?>
		<span class="forum-option-profile">
			<noindex><a rel="nofollow" href="<?=$arResult["URL"]["PROFILE"]?>" title="<?=$arResult["SHOW_EDIT_PROFILE_TITLE"]?>"><?=GetMessage("F_EDIT_PROFILE")?></a></noindex>
		</span>
	</div>
<?
endif;
?>
	<div class="forum-header-title"><span><?=GetMessage("F_PROFILE")?></span></div>
</div>

<table cellspacing="0" border="0" class="forum-post-table forum-post-first forum-post-last forum-post-odd">
	<tbody>
		<tr>
			<td class="forum-cell-user">
				<div class="forum-user-info">
					<div class="forum-user-name"><span><?=$arResult["SHOW_NAME"]?></span></div>
<?
	if (!empty($arResult["FORUM_USER"]["AVATAR"])):
?>
					<div class="forum-user-avatar"><?=$arResult["FORUM_USER"]["AVATAR"]?></div>
<?
	else:
?>
					<div class="forum-user-register-avatar"><span><!-- ie --></span></div>
<?
	endif;

if ($arResult["SHOW_RANK"] != "Y"):
?>
					<div class="forum-user-status <?=(!empty($arResult["USER_RANK_CODE"]) ? "forum-user-".$arResult["USER_RANK_CODE"]."-status" : "")?>"><?
						?><span><?=htmlspecialcharsbx($arResult["USER_RANK"])?></span></div>
<?
else:
?>
					<div class="forum-user-status <?=(!empty($arResult["USER_RANK_CODE"]) ? "forum-user-".$arResult["USER_RANK_CODE"]."-status" : "")?>"><?
						?><span><?=htmlspecialcharsbx($arResult["arRank"]["NAME"])?></span></div>
			
<?
endif;
?>
					<div class="forum-user-additional">
<?
if (intval($arResult["FORUM_USER"]["NUM_POSTS"]) > 0):
?>
						<span><?=GetMessage("F_NUM_MESS")?> <span><?=$arResult["FORUM_USER"]["NUM_POSTS"]?></span></span>
<?
endif;

if ($arResult["SHOW_RANK"] == "Y"):
?>
						<span><?=GetMessage("F_NUM_POINTS")?> <span><?=$arResult["FORUM_USER"]["POINTS"]?></span><?
	if ($arResult["SHOW_VOTES"] == "Y"):
						?>&nbsp;(<span class="forum-vote-user"><?
							?><noindex><a rel="nofollow" onclick="return forumAddSessid(this);" href="<?=$arResult["URL"]["~VOTE"]?>" title="<?
								?><?=($arResult["VOTE_ACTION"] == "VOTE" ? GetMessage("F_NO_VOTE_DO") : GetMessage("F_NO_VOTE_UNDO"));?>"><?
								?><?=($arResult["VOTE_ACTION"] == "VOTE" ? "+" : "-");?></a></noindex></span>)
<?
	endif;
?>
						</span>
<?
	if ($arResult["SHOW_POINTS"] == "Y"):
?>
						<span><?=GetMessage("F_NUM_VOTES")?> <span><?=intval($arResult["USER_POINTS"])?></span></span>
<?
	endif;
endif;
$sRatingTemplate = COption::GetOptionString("main", "rating_vote_type", "standart");
if ($sRatingTemplate == 'standart' && $arParams["SHOW_RATING"] == 'Y' && $authorId > 0):
?>
	<span>
		<?
		$GLOBALS["APPLICATION"]->IncludeComponent(
			"bitrix:rating.result", "",
			Array(
				"RATING_ID" => $arParams["RATING_ID"],
				"ENTITY_ID" => $arRatingResult['ENTITY_ID'],
				"CURRENT_VALUE" => $arRatingResult['CURRENT_VALUE'],
				"PREVIOUS_VALUE" => $arRatingResult['PREVIOUS_VALUE'],
			),
			null,
			array("HIDE_ICONS" => "Y")
		);
		?>
	</span>
	<span><?=GetMessage('F_VOTE_AUTH')?>:
		<?
		$GLOBALS["APPLICATION"]->IncludeComponent(
			"bitrix:rating.vote", $arParams["RATING_TYPE"],
			Array(
				"ENTITY_TYPE_ID" => 'USER',
				"ENTITY_ID" => $authorId,
				"OWNER_ID" => $authorId,
				"USER_VOTE" => $arRatingVote['USER_VOTE'],
				"USER_HAS_VOTED" => $arRatingVote['USER_HAS_VOTED'],
				"TOTAL_VOTES" => $arRatingVote['TOTAL_VOTES'],
				"TOTAL_POSITIVE_VOTES" => $arRatingVote['TOTAL_POSITIVE_VOTES'],
				"TOTAL_NEGATIVE_VOTES" => $arRatingVote['TOTAL_NEGATIVE_VOTES'],
				"TOTAL_VALUE" => $arRatingVote['TOTAL_VALUE'],
				"PATH_TO_USER_PROFILE" => $arParams["~URL_TEMPLATES_PROFILE_VIEW"]
			),
			null,
			array("HIDE_ICONS" => "Y")
		);
		?>
		</span>	
<?
endif;


if (!empty($arResult["FORUM_USER"]["DATE_REG_FORMATED"])):
?>
						<span><?=GetMessage("F_DATE_REGISTER")?> <span><?=$arResult["FORUM_USER"]["DATE_REG_FORMATED"]?></span></span>
<?
endif;
if (!empty($arResult["FORUM_USER"]["LAST_VISIT_FORMATED"])):
?>
						<span><?=GetMessage("F_DATE_VISIT")?> <span><?=$arResult["FORUM_USER"]["LAST_VISIT_FORMATED"]?></span></span>
<?
endif;

?>
					</div>
<?
		if ($arResult["FORUM_USER"]["DESCRIPTION"] <> ''):
?>
					<div class="forum-user-description"><span><?=$arResult["FORUM_USER"]["DESCRIPTION"]?></span></div>
<?
		endif;

?>
				</div>
			</td>
			<td class="forum-cell-post">
				<div class="forum-post-entry forum-user-information">
<?
	if ($arResult["SHOW_EDIT_PROFILE"] == "Y"):
?>
				<div style="float:right;" class="forum-user-hide-action">
<?
		if (!empty($arResult["USER"]["PERSONAL_PHOTO"]) && COption::GetOptionString("forum", "show_avatar_photo", "N") == 'N'):
?>
					<noindex><a rel="nofollow" onclick="return forumAddSessid(this);" href="<?=$APPLICATION->GetCurPageParam("option=photo&value=".
						($arResult["USER"]["SHOW_PANELS"]["photo"] == "Y" ? "hide" : "show"), array("option", "value", "sessid"))?>"><?
					?><?=($arResult["USER"]["SHOW_PANELS"]["photo"] == "Y" ? GetMessage("F_HIDE") : GetMessage("F_SHOW")).GetMessage("F_PHOTO")?>
					</a></noindex><br />
<?
		endif;

		if (!empty($arResult["USER"]["PERSONAL_BIRTHDAY_FORMATED"]) || !empty($arResult["USER"]["PERSONAL_GENDER"]) || 
		!empty($arResult["USER"]["PERSONAL_PROFESSION"]) || !empty($arResult["USER"]["PERSONAL_LOCATION"])):
?>
					<noindex><a rel="nofollow" onclick="return forumAddSessid(this);" href="<?=$APPLICATION->GetCurPageParam("option=private&value=".
						($arResult["USER"]["SHOW_PANELS"]["private"] == "Y" ? "hide" : "show"), array("option", "value", "sessid"))?>"><?
					?><?=($arResult["USER"]["SHOW_PANELS"]["private"] == "Y" ? GetMessage("F_HIDE") : GetMessage("F_SHOW")).GetMessage("F_PRIVATE_DATA")?>
					</a></noindex><br />
<?
		endif;

		if (!empty($arResult["USER"]["WORK_COMPANY"]) || !empty($arResult["USER"]["WORK_POSITION"]) || !empty($arResult["USER"]["WORK_DEPARTMENT"]) ||
	!empty($arResult["USER"]["WORK_LOCATION"]) || !empty($arResult["USER"]["WORK_PROFILE"]) || !empty($arResult["USER"]["WORK_WWW"])):
?>
					<noindex><a rel="nofollow" onclick="return forumAddSessid(this);" href="<?=$APPLICATION->GetCurPageParam("option=work&value=".
						($arResult["USER"]["SHOW_PANELS"]["work"] == "Y" ? "hide" : "show"), array("option", "value", "sessid"))?>"><?
					?><?=($arResult["USER"]["SHOW_PANELS"]["work"] == "Y" ? GetMessage("F_HIDE") : GetMessage("F_SHOW")).GetMessage("F_WORK_DATA")?>
					</a></noindex><br />
<?
		endif;

		if ($arResult["USER_PROPERTIES"]["SHOW"] == "Y"):
?>
					<noindex><a rel="nofollow" onclick="return forumAddSessid(this);" href="<?=$APPLICATION->GetCurPageParam("option=user_prop&value=".
						($arResult["USER"]["SHOW_PANELS"]["user_prop"] == "Y" ? "hide" : "show"), array("option", "value", "sessid"))?>"><?
					?><?=($arResult["USER"]["SHOW_PANELS"]["user_prop"] == "Y" ? GetMessage("F_HIDE") : GetMessage("F_SHOW")).GetMessage("USER_TYPE_EDIT_TAB")?>
					</a></noindex>
<?
		endif;
?>
				</div>
<?
	endif;

	if ($arResult["USER"]["SHOW_PANELS"]["photo"] != "N" && !empty($arResult["USER"]["PERSONAL_PHOTO"])):
?>
				<div class="forum-user-photo"><?=$arResult["USER"]["PERSONAL_PHOTO"]?></div>
<?
	endif;
	

	if ($arResult["USER"]["SHOW_PANELS"]["private"] == "N" || (empty($arResult["USER"]["PERSONAL_BIRTHDAY_FORMATED"]) && empty($arResult["USER"]["PERSONAL_GENDER"]) &&  
		empty($arResult["USER"]["PERSONAL_PROFESSION"]) && empty($arResult["USER"]["PERSONAL_LOCATION"]))):
		if (!empty($arResult["FORUM_USER"]["INTERESTS"])):
			$bShowedInfo = true;
?>
		<div class="forum-info-box forum-user-interests">
			<div class="forum-info-box-inner">
				<?=$arResult["FORUM_USER"]["INTERESTS"]?>
			</div>
		</div>
<?
		endif;
	elseif (!empty($arResult["USER"]["PERSONAL_BIRTHDAY_FORMATED"]) || !empty($arResult["USER"]["PERSONAL_GENDER"]) || 
		!empty($arResult["USER"]["PERSONAL_PROFESSION"]) || !empty($arResult["USER"]["PERSONAL_LOCATION"]) || !empty($arResult["FORUM_USER"]["INTERESTS"])):
?>
<table cellspacing="0" class="forum-table forum-user-private-info">
	<thead>
		<tr><th class="forum-first-column forum-last-column" colspan="2"><span><?=GetMessage("F_PRIVATE_DATA")?></span></th></tr>
	</thead>
	<tbody>
<?
	$bShowedInfo = true;
	$iCount = 0;
	if (!empty($arResult["USER"]["PERSONAL_BIRTHDAY_FORMATED"])):
		$iCount++;
?>
			<tr class="<?=($iCount == 1 ? "forum-row-first " : "")?> <?=($iCount%2 == 1 ? "forum-row-odd" : "forum-row-even")?>">
			<td class="forum-first-column"><?=GetMessage("F_BIRTHDATE")?>: </td>
			<td class="forum-last-column"><?=$arResult["USER"]["PERSONAL_BIRTHDAY_FORMATED"]?></td></tr>
<?
	endif;
	
	if (!empty($arResult["USER"]["PERSONAL_GENDER"])):
		$iCount++;
?>
			<tr class="<?=($iCount == 1 ? "forum-row-first " : "")?> <?=($iCount%2 == 1 ? "forum-row-odd" : "forum-row-even")?>">
			<td class="forum-first-column"><?=GetMessage("F_SEX")?>: </td>
			<td class="forum-last-column"><?=$arResult["USER"]["PERSONAL_GENDER"]?></td></tr>
<?
	endif;
	
	if (!empty($arResult["USER"]["PERSONAL_PROFESSION"])):
		$iCount++;
?>
			<tr class="<?=($iCount == 1 ? "forum-row-first " : "")?> <?=($iCount%2 == 1 ? "forum-row-odd" : "forum-row-even")?>">
			<td class="forum-first-column"><?=GetMessage("F_PROFESSION")?>: </td>
			<td class="forum-last-column"><?=$arResult["USER"]["PERSONAL_PROFESSION"]?></td></tr>
<?
	endif;
	
	if (!empty($arResult["USER"]["PERSONAL_LOCATION"])):
		$iCount++;
?>
			<tr class="<?=($iCount == 1 ? "forum-row-first " : "")?> <?=($iCount%2 == 1 ? "forum-row-odd" : "forum-row-even")?>">
			<td class="forum-first-column"><?=GetMessage("F_LOCATION_PERS")?>: </td>
			<td class="forum-last-column"><?=$arResult["USER"]["PERSONAL_LOCATION"]?></td></tr>
<?
	endif;
	
	if (!empty($arResult["FORUM_USER"]["INTERESTS"])):
		$iCount++;
?>
			<tr class="<?=($iCount == 1 ? "forum-row-first " : "")?> <?=($iCount%2 == 1 ? "forum-row-odd" : "forum-row-even")?>">
			<td class="forum-first-column"><?=GetMessage("F_INTERESTS")?>: </td>
			<td class="forum-last-column"><?=$arResult["FORUM_USER"]["INTERESTS"]?></td></tr>
<?
	endif;
?>
	</tbody>
</table><br />
<?
endif;
	
if ($arResult["USER"]["SHOW_PANELS"]["work"] != "N" && 
	(!empty($arResult["USER"]["WORK_COMPANY"]) || !empty($arResult["USER"]["WORK_POSITION"]) || !empty($arResult["USER"]["WORK_DEPARTMENT"]) ||
	!empty($arResult["USER"]["WORK_LOCATION"]) || !empty($arResult["USER"]["WORK_PROFILE"]) || !empty($arResult["USER"]["WORK_WWW"]))):
?>
<table cellspacing="0" class="forum-table">
	<thead>
		<tr><th class="forum-first-column forum-last-column" colspan="2"><span><?=GetMessage("F_WORK_DATA")?></span></th></tr>
	</thead>
	<tbody>
<?
	$bShowedInfo = true;
	$iCount = 0;	
	if (!empty($arResult["USER"]["WORK_COMPANY"]) || !empty($arResult["USER"]["WORK_WWW"])):
		$iCount++;
?>
			<tr class="forum-row-first <?=($iCount%2 == 1 ? "forum-row-odd" : "forum-row-even")?>">
			<td class="forum-first-column"><?=GetMessage("F_COMPANY")?>: </td>
			<td class="forum-last-column"><?
		if (!empty($arResult["USER"]["WORK_WWW_FORMATED"]) && !empty($arResult["USER"]["WORK_COMPANY"])):
?>
					<noindex><a rel="nofollow" href="<?=$arResult["USER"]["WORK_WWW_FORMATED"]?>" target="_blank"><?=$arResult["USER"]["WORK_COMPANY"]?></a></noindex>
<?
		elseif (!empty($arResult["USER"]["WORK_COMPANY"])):
?>
					<?=$arResult["USER"]["WORK_COMPANY"]?>
<?
		else:
?>
			<noindex><a rel="nofollow" href="<?=$arResult["USER"]["WORK_WWW_FORMATED"]?>" target="_blank"><?=$arResult["USER"]["WORK_WWW_FORMATED"]?></a></noindex>
<?
		endif;
				?></td>
			</tr>
<?
	endif;
	
	if (!empty($arResult["USER"]["WORK_POSITION"]) || !empty($arResult["USER"]["WORK_DEPARTMENT"])):
		$iCount++;
?>
			<tr class="<?=($iCount == 1 ? "forum-row-first " : "")?> <?=($iCount%2 == 1 ? "forum-row-odd" : "forum-row-even")?>">
			<td class="forum-first-column"><?
				if (!empty($arResult["USER"]["WORK_POSITION"])):
					?><?=GetMessage("F_POST")?><?
					if (!empty($arResult["USER"]["WORK_DEPARTMENT"])):
						?>, <?
					endif;
				endif;
				if (!empty($arResult["USER"]["WORK_DEPARTMENT"])):
					?><?=GetMessage("F_SEX_DEPARTMENT")?><?
				endif;
			?>:</td>
			<td class="forum-last-column"><?
				if (!empty($arResult["USER"]["WORK_POSITION"])):
					?><?=$arResult["USER"]["WORK_POSITION"]?><?
					if (!empty($arResult["USER"]["WORK_DEPARTMENT"])):
						?>, <?
					endif;
				endif;
				if (!empty($arResult["USER"]["WORK_DEPARTMENT"])):
					?><?=$arResult["USER"]["WORK_DEPARTMENT"]?><?
				endif;
			?></td></tr>
<?
	endif;
	
	if (!empty($arResult["USER"]["WORK_LOCATION"])):
		$iCount++;
?>
			<tr class="<?=($iCount == 1 ? "forum-row-first " : "")?> <?=($iCount%2 == 1 ? "forum-row-odd" : "forum-row-even")?>">
			<td class="forum-first-column"><?=GetMessage("F_LOCATION")?>: </td>
			<td class="forum-last-column"><?=$arResult["USER"]["WORK_LOCATION"]?></td></tr>
<?
	endif;
	
	if (!empty($arResult["USER"]["WORK_PROFILE"])):
		$iCount++;
?>
			<tr class="<?=($iCount == 1 ? "forum-row-first " : "")?> <?=($iCount%2 == 1 ? "forum-row-odd" : "forum-row-even")?>">
			<td class="forum-first-column"><?=GetMessage("F_ACTIVITY")?>: </td>
			<td class="forum-last-column"><?=$arResult["USER"]["WORK_PROFILE"]?></td></tr>
<?
	endif;
?>
	</tbody>
</table>
<br />
<?
endif;
/************** User proprties *************************************/
if ($arResult["USER"]["SHOW_PANELS"]["user_prop"] != "N" && $arResult["USER_PROPERTIES"]["SHOW"] == "Y"):
	$iCount = 0;
	foreach ($arResult["USER_PROPERTIES"]["DATA"] as $FIELD_NAME => $arUserField):
		$res = $arUserField["VALUE"];
		if($arUserField["ENTITY_VALUE_ID"] < 1 && $arUserField["SETTINGS"]["DEFAULT_VALUE"] <> '')
			$res = $arUserField["SETTINGS"]["DEFAULT_VALUE"];
		if (empty($res))
			continue;
		$iCount++;
		$bShowedInfo = true;
		if ($iCount == 1):
?>
		<table cellspacing="0" class="forum-table">
		<thead>
			<tr><th class="forum-first-column forum-last-column" colspan="2"><span><?=GetMessage("USER_TYPE_EDIT_TAB")?></span></th></tr>
		</thead>
		<tbody>
<?
		endif;
?>
			<tr class="<?=($iCount == 1 ? "forum-row-first " : "")?> <?=($iCount%2 == 1 ? "forum-row-odd" : "forum-row-even")?>">
			<td class="forum-first-column"><?=$arUserField["EDIT_FORM_LABEL"]?>: </td>
			<td class="forum-last-column"><?$APPLICATION->IncludeComponent(
					"bitrix:system.field.view", 
					$arUserField["USER_TYPE"]["USER_TYPE_ID"], 
					array("arUserField" => $arUserField), null, array("HIDE_ICONS"=>"Y"))?></td></tr>
<?
	endforeach;
	if ($iCount > 0):
?>
		</tbody>
	</table>
<?
	endif;
endif;
/************** User proprties/*************************************/

if (!$bShowedInfo):
	?><i><?=GetMessage("F_NO_DATA")?>.</i><?
endif;
?>
				</div>
			</td>
		</tr>
		<tr>
			<td class="forum-cell-contact">
				<div class="forum-contact-links">
<?
	$bEmptyCell = true;
		if (intval(COption::GetOptionString("forum", "UsePMVersion", "2")) > 0 && $USER->IsAuthorized()):
			$bEmptyCell = false;
?>
					<span class="forum-contact-message"><noindex><a rel="nofollow" href="<?=$arResult["URL"]["USER_PM"]?>" title="<?=GetMessage("F_SEND_PM_ALT")?>"><?
						?><?=GetMessage("F_SEND_PM")?></a></noindex></span>&nbsp;&nbsp;
<?
		endif;
		if ($arParams["SHOW_MAIL"] == "Y" && $arResult["USER"]["EMAIL"] <> ''):
			$bEmptyCell = false;
?>
					<span class="forum-contact-email"><?
						?><noindex><a rel="nofollow" href="<?=$arResult["URL"]["USER_EMAIL"]?>" title="<?=GetMessage("F_SEND_EMAIL_ALT")?>">E-mail</a></noindex></span>&nbsp;&nbsp;
<?
		endif;
		if ($arResult["SHOW_ICQ"] == "Y" && $arResult["USER"]["PERSONAL_ICQ"] <> ''):
			$bEmptyCell = false;
?>
					<span class="forum-contact-icq">
						<noindex><a rel="nofollow" href="javascript:void(0);" onclick="prompt('ICQ', '<?=CUtil::JSEscape($arResult["USER"]["PERSONAL_ICQ"])?>')">ICQ</a></noindex></span>
<?
		endif;
		
		if (!empty($arResult["USER"]["PERSONAL_WWW_FORMATED"])):
?>
					<span class="forum-contact-url"><noindex><a rel="nofollow" href="<?=$arResult["USER"]["PERSONAL_WWW_FORMATED"]?>" target="_blank"><?
						?><?=GetMessage("F_SITE")?></a></noindex></span>&nbsp;&nbsp;
<?
		elseif ($bEmptyCell):
?>
					&nbsp;
<?
		endif;
?>
				</div>
			</td>
			<td class="forum-cell-actions">
				<div class="forum-action-links">
<?
	if (!empty($arResult["FORUM_USER"]["NUM_POSTS"])):
?>
								<span class="forum-user-messages">
									<noindex><a rel="nofollow" href="<?=$arResult["user_post_lta"]?>" title="<?=GetMessage("F_ALL_TOPICS_AUTHOR_TITLE")?>"><?
										?><?=GetMessage("F_ALL_TOPICS_AUTHOR")?></a></noindex></span>
<?/*?>								&nbsp;&nbsp;<span class="forum-user-messages">
									<noindex><a rel="nofollow" href="<?=$arResult["user_post_lt"]?>" title="<?=GetMessage("F_ALL_TOPICS_TITLE")?>"><?
										?><?=GetMessage("F_ALL_TOPICS")?></a></noindex></span>
<?*/?>
								&nbsp;&nbsp;<span class="forum-user-messages">
									<noindex><a rel="nofollow" href="<?=$arResult["user_post_all"]?>" title="<?=GetMessage("F_ALL_MESSAGES_TITLE")?>"><?
										?><?=GetMessage("F_ALL_MESSAGES")?></a></noindex></span>
<?
		if (!empty($arResult["arTopic"]) && $arResult["arTopic"] != "N"):
?>
								&nbsp;&nbsp;<span class="forum-user-messages">
									<noindex><a rel="nofollow" href="<?=$arResult["arTopic"]["read"]?>" title="<?=htmlspecialcharsbx($arResult["arTopic"]["~TITLE"])?><?
										if ($arResult["arTopic"]["~DESCRIPTION"] <> ''):
											?>, <?=htmlspecialcharsbx($arResult["arTopic"]["~DESCRIPTION"])?><?
										endif;?>"><?=GetMessage("F_LAST_MESSAGE")?></a></noindex></span>
<?
		endif;
	else:
?>
								&nbsp;
<?
	endif;
?>				</div>
			</td>
		</tr>
	</tbody>
	<tfoot>
		<tr>
			<td colspan="5" class="forum-column-footer">
				<div class="forum-footer-inner">
<?
	if ($arResult["SHOW_VOTES"] == "Y" && $arResult["IsAdmin"] == "Y"):?>
				<div class="forum-vote-block"><div><?=$arResult["titleVote"]?></div>
<?
		if ($arResult["bCanVote"] || $arResult["bCanUnVote"]):
?>
				<form method="get" action="<?=$APPLICATION->GetCurPageParam()?>" class="forum-form">
				<?if ($arResult["bCanVote"]):?>
					<input type="text" name="VOTES" size="5" value="<?=intval($arResult["VOTES"])?>" id="votes" />
				<?endif;?>
				<input type="hidden" name="UID" value="<?=$arParams["UID"]?>"/>
				<input type="hidden" name="FID" value="<?=$arResult["FID"]?>"/>
				<input type="hidden" name="TID" value="<?=$arResult["TID"]?>"/>
				<input type="hidden" name="MID" value="<?=$arResult["MID"]?>"/>
				<input type="hidden" name="VOTE_USER" value="Y"/>
				<input type="hidden" name="PAGE_NAME" value="profile_view"/>
				<?=bitrix_sessid_post()?>
<?
			if ($arResult["bCanVote"]):
?>
				<input type="submit" name="VOTE_BUTTON" value="<?=GetMessage("F_DO_VOTE")?>" title="<?=GetMessage("F_DO_VOTE_ALT")?>" id="vote" />
<?
			endif;
			if ($arResult["bCanUnVote"]):
?>
				<input type="submit" name="CANCEL_VOTE" value="<?=GetMessage("F_UNDO_VOTE")?>" title="<?=GetMessage("F_UNDO_VOTE_ALT")?>" id="unvote" />
<?
			endif;
?>
				</form>
<?
		endif;
?>
				</div>
<?
	else:
?>
		&nbsp;
<?
	endif;
?>				</div>
			</td>
		</tr>
	</tfoot>
</table>