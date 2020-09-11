<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?><?
if (!$this->__component->__parent || empty($this->__component->__parent->__name)):
	$GLOBALS['APPLICATION']->SetAdditionalCSS('/bitrix/components/bitrix/forum/templates/.default/style.css');
	$GLOBALS['APPLICATION']->SetAdditionalCSS('/bitrix/components/bitrix/forum/templates/.default/themes/blue/style.css');
	$GLOBALS['APPLICATION']->SetAdditionalCSS('/bitrix/components/bitrix/forum/templates/.default/styles/additional.css');
endif;
IncludeAJAX();
$GLOBALS['APPLICATION']->AddHeadScript("/bitrix/js/main/utils.js");
$GLOBALS['APPLICATION']->AddHeadScript("/bitrix/components/bitrix/forum.interface/templates/.default/script.js");
$GLOBALS['APPLICATION']->AddHeadScript("/bitrix/components/bitrix/forum.interface/templates/popup/script.js");
/********************************************************************
				Input params
********************************************************************/
/***************** BASE ********************************************/
$arParams["SHOW_AUTHOR_COLUMN"] = ($arParams["SHOW_AUTHOR_COLUMN"] == "Y" ? "Y" : "N");
$arParams["SHOW_RSS"] = ($arParams["SHOW_RSS"] == "N" ? "N" : "Y");
if ($arParams["SHOW_RSS"] == "Y"):
	$arParams["SHOW_RSS"] = (!$USER->IsAuthorized() ? "Y" : (CForumNew::GetUserPermission($arParams["FID"], array(2)) > "A"? "Y" : "N"));
	if ($arParams["SHOW_RSS"] == "Y"):
		$APPLICATION->AddHeadString('<link rel="alternate" type="application/rss+xml" href="'.$arResult["URL"]["RSS_DEFAULT"].'" />');
	endif;
endif;
$arParams["~TMPLT_SHOW_ADDITIONAL_MARKER"] = trim($arParams["~TMPLT_SHOW_ADDITIONAL_MARKER"]);
$arParams["SEO_USER"] = (in_array($arParams["SEO_USER"], array("Y", "N", "TEXT")) ? $arParams["SEO_USER"] : "Y");
$arParams["USER_TMPL"] = '<noindex><a rel="nofollow" href="#URL#" title="'.GetMessage("F_USER_PROFILE").'">#NAME#</a></noindex>';
if ($arParams["SEO_USER"] == "N") $arParams["USER_TMPL"] = '<a href="#URL#" title="'.GetMessage("F_USER_PROFILE").'">#NAME#</a>';
elseif ($arParams["SEO_USER"] == "TEXT") $arParams["USER_TMPL"] = '#NAME#';
$iIndex = rand();
/********************************************************************
				/Input params
********************************************************************/
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

// *****************************************************************************************
?>
<div class="forum-navigation-box forum-navigation-top">
	<div class="forum-page-navigation">
		<?=$arResult["NAV_STRING"]?>
	</div>
<?
if ($arResult["USER"]["RIGHTS"]["CAN_ADD_TOPIC"] == "Y"):
?>
	<div class="forum-new-post">
		<noindex><a href="<?=$arResult["URL"]["TOPIC_NEW"]?>" title="<?=GetMessage("F_NEW_TOPIC_TITLE")?>" rel="nofollow"><span><?=GetMessage("F_NEW_TOPIC")?></span></a></noindex>
	</div>
<?
endif;
?>
	<div class="forum-clear-float"></div>
</div>

<div class="forum-header-box">
	<div class="forum-header-options"><?
if ($arParams["SHOW_RSS"] == "Y"):
?>
		<span class="forum-option-feed"><noindex><a rel="nofollow" href="<?=$arResult["URL"]["RSS_DEFAULT"]?>" onclick="window.location='<?=addslashes(htmlspecialcharsbx($arResult["URL"]["~RSS"]))?>'; return false;">RSS</a></noindex></span>
<?
endif;
if ($USER->IsAuthorized() && empty($arResult["USER"]["SUBSCRIBE"])):
	if ($arParams["SHOW_RSS"] == "Y"):
		?>&nbsp;&nbsp;<?
	endif;
	
?>
	<span class="forum-option-subscribe">
		<noindex><a rel="nofollow" title="<?=GetMessage("F_SUBSCRIBE_TO_NEW_POSTS")?>" href="<?=$APPLICATION->GetCurPageParam("ACTION=FORUM_SUBSCRIBE", 
			array("ACTION", "sessid"))?>"><?=GetMessage("F_SUBSCRIBE")?></a></noindex>
	</span>
<?
endif;
?>
	</div>
	<div class="forum-header-title"><span><?=$arResult["FORUM"]["NAME"]?></span></div>
</div>
<?
if ($arResult["PERMISSION"] >= "Q"):
?>
<form class="forum-form" action="<?=POST_FORM_ACTION_URI?>" method="POST" onsubmit="return Validate(this)" name="TOPICS_<?=$iIndex?>" id="TOPICS_<?=$iIndex?>">
	<?=bitrix_sessid_post()?>
	<input type="hidden" name="PAGE_NAME" value="list" />
	<input type="hidden" name="NAV_PAGE" value="<?=$arResult['NAV_PAGE']?>" />
	<input type="hidden" name="FID" value="<?=$arParams["FID"]?>" />
<?
endif;
?>
<div class="forum-block-container">
	<div class="forum-block-outer">
		<div class="forum-block-inner">
			<table cellspacing="0" class="forum-table forum-topic-list">

<?
if (empty($arResult["TOPICS"])):
?>
			<tbody>
				<tr class="forum-row-first forum-row-last forum-row-odd">
					<td class="forum-column-alone">
						<div class="forum-empty-message"><?=GetMessage("F_NO_TOPICS_HERE")?><br />
<?
if ($arResult["USER"]["RIGHTS"]["CAN_ADD_TOPIC"] == "Y"):
?>
						<?=str_replace("#HREF#", $arResult["URL"]["TOPIC_NEW"], GetMessage("F_CREATE_NEW_TOPIC"))?></div>
<?
endif;
?>
					</td>
				</tr>
			</tbody>
			<tfoot>
				<tr>
					<td class="forum-column-footer">
						<div class="forum-footer-inner">&nbsp;</div>
					</td>
				</tr>
			</tfoot> 
<?
else:
?>
			<thead>
				<tr>
					<th class="forum-column-title" colspan="2"><div class="forum-head-title"><span><?=GetMessage("F_HEAD_TOPICS")?></span></div></th>
<?
if ($arParams["SHOW_AUTHOR_COLUMN"] == "Y"):
?>
	<th class="forum-column-replies"><span><?=GetMessage("F_HEAD_AUTHOR")?></span></th>
<?
endif;
?>
					<th class="forum-column-replies"><span><?=GetMessage("F_HEAD_POSTS")?></span></th>
					<th class="forum-column-views"><span><?=GetMessage("F_HEAD_VIEWS")?></span></th>
					<th class="forum-column-lastpost"><span><?=GetMessage("F_HEAD_LAST_POST")?></span></th>
				</tr>
			</thead>
			<tbody>

<?
$iCount = 0;
foreach ($arResult["TOPICS"] as $res):
	$iCount++;
?>
				<tr class="<?=($iCount == 1 ? "forum-row-first " : "")?><?
					?><?=($iCount == count($arResult["TOPICS"]) ? "forum-row-last " : "")?><?
					?><?=($iCount%2 == 1 ? "forum-row-odd " : "forum-row-even ")?><?
					?><?=(intval($res["SORT"]) != 150 ? "forum-row-sticky " : "")?><?
					?><?=($res["STATE"] != "Y" && $res["STATE"] != "L" ? "forum-row-closed " : "")?><?
					?><?=($res["TopicStatus"] == "MOVED" ? "forum-row-moved " : "")?><?
					?><?=($res["APPROVED"] != "Y" ? " forum-row-hidden ": "")?><?
					?>">
					<td class="forum-column-icon">
						<div class="forum-icon-container">
							<div class="forum-icon <?
							$title = ""; $class = "";
							if (intval($res["SORT"]) != 150):
								$title = GetMessage("F_PINNED_TOPIC");
								if ($res["TopicStatus"] == "NEW"):
									$title .= " (".GetMessage("F_HAVE_NEW_MESS").")";
									?> forum-icon-sticky-newposts <?
								else:
									?> forum-icon-sticky <?
								endif;
							elseif ($res["TopicStatus"] == "MOVED"):
								$title = GetMessage("F_MOVED_TOPIC");
								?> forum-icon-moved <?
							elseif ($res["STATE"] != "Y" && $res["STATE"] != "L"):
								$title = (intval($res["SORT"]) != 150 ? GetMessage("F_PINNED_CLOSED_TOPIC") : GetMessage("F_CLOSED_TOPIC"));
								if ($res["TopicStatus"] == "NEW"):
									$title .= " (".GetMessage("F_HAVE_NEW_MESS").")";
									?> forum-icon-closed-newposts <?
								else:
									?> forum-icon-closed <?
								endif;
							elseif ($res["TopicStatus"] == "NEW"):
								$title .= (empty($title) ? GetMessage("F_HAVE_NEW_MESS") : " (".GetMessage("F_HAVE_NEW_MESS").")");
								?> forum-icon-newposts <?
							else:
								$title .= (empty($title) ? GetMessage("F_NO_NEW_MESS") : "");
								?> forum-icon-default <?
							endif;
							
							?>" title="<?=$title?>"><!-- ie --></div>
						</div>
					</td>
					<td class="forum-column-title">
						<div class="forum-item-info">
							<div class="forum-item-name"><?
						if (intval($res["SORT"]) != 150 && $res["STATE"]!="Y"):
								?><span class="forum-status-sticky-block"><span class="forum-status-sticky"><?=GetMessage("F_PINNED")?></span>, </span><?
							if ($res["STATE"] != "L"):
								?><span class="forum-status-closed-block"><span class="forum-status-closed"><?=GetMessage("F_CLOSED")?></span>:&nbsp;</span><?
							else:
								?><span class="forum-status-moved-block"><span class="forum-status-moved"><?=GetMessage("F_MOVED")?></span>:&nbsp;</span><?
							endif;
						elseif ($res["TopicStatus"] == "MOVED" || $res["STATE"]=="L"):
								?><span class="forum-status-moved-block"><span class="forum-status-moved"><?=GetMessage("F_MOVED")?></span>:&nbsp;</span><?
						elseif (intval($res["SORT"]) != 150):
								?><span class="forum-status-sticky-block"><span class="forum-status-sticky"><?=GetMessage("F_PINNED")?></span>:&nbsp;</span><?
						elseif (($res["STATE"]!="Y") && ($res["STATE"]!="L")):
								?><span class="forum-status-closed-block"><span class="forum-status-closed"><?=GetMessage("F_CLOSED")?></span>:&nbsp;</span><?
						endif;
								?><span class="forum-item-title"><?
						if (false && $res["IMAGE"] <> ''):
								?><img src="<?=$res["IMAGE"];?>" alt="<?=$res["IMAGE_DESCR"];?>" border="0" width="15" height="15"/><?
						endif;
								?><a href="<?=$res["URL"]["TOPIC"]?>" title="<?=GetMessage("F_TOPIC_START")?> <?=$res["START_DATE"]?>"><?=$res["TITLE"]?></a><?
						if ($res["TopicStatus"] == "NEW" && $arParams["~TMPLT_SHOW_ADDITIONAL_MARKER"] <> ''):
								?><noindex><a href="<?=$res["URL"]["MESSAGE_UNREAD"]?>" rel="nofollow" class="forum-new-message-marker"><?=$arParams["~TMPLT_SHOW_ADDITIONAL_MARKER"]?></a></noindex><?
						endif;
								?></span><?
						if ($res["PAGES_COUNT"] > 1):
								?> <span class="forum-item-pages">(<?
							$iCountPages = intval($res["PAGES_COUNT"] > 5 ? 3 : $res["PAGES_COUNT"]);
							for ($ii = 1; $ii <= $iCountPages; $ii++):
								?><noindex><a rel="nofollow" href="<?=ForumAddPageParams($res["URL"]["~TOPIC"], ($ii > 1 ? array("PAGEN_".$arParams["PAGEN"] => $ii) : array()))?>"><?
									?><?=$ii?></a></noindex><?=($ii < $iCountPages ? ",&nbsp;" : "")?><?
							endfor;
							if ($iCountPages < $res["PAGES_COUNT"]):
								?>&nbsp;...&nbsp;<noindex><a rel="nofollow" href="<?=ForumAddPageParams($res["URL"]["~TOPIC"], 
									array("PAGEN_".$arParams["PAGEN"] => $res["PAGES_COUNT"]))?>"><?=$res["PAGES_COUNT"]?></a></noindex><?
							endif;
								?>)</span><?
						endif;
							?></div>
<?
						if (!empty($res["DESCRIPTION"])):
?>
							<span class="forum-item-desc"><?=$res["DESCRIPTION"]?></span><span class="forum-item-desc-sep"><?
							?><?=($arParams["SHOW_AUTHOR_COLUMN"] != "Y" ? "&nbsp;&middot; " : "")?></span>
<?
						endif;
						if ($arParams["SHOW_AUTHOR_COLUMN"] != "Y"):
?>
							<span class="forum-item-author"><span><?=GetMessage("F_AUTHOR")?></span>&nbsp;<?=$res["USER_START_NAME"]?></span>
<?
						endif;
?>
						</div>
					</td>
<?
						if ($arParams["SHOW_AUTHOR_COLUMN"] == "Y"):
?>
					<td class="forum-column-author"><span><?
						if ($res["USER_START_ID"] > 0):
							?><?=str_replace(array("#URL#", "#NAME#"), array($res["URL"]["USER_START"], $res["USER_START_NAME"]), $arParams["USER_TMPL"]);
						else:
							?><?=$res["USER_START_NAME"]?><?
						endif;
					?></span></td>
<?
						endif;

						if ($arResult["PERMISSION"] >= "Q" && $res["mCnt"] > 0):
?>
					<td class="forum-column-replies forum-cell-hidden"><span><?=$res["POSTS"]?> <?
						?>(<noindex><a rel="nofollow" href="<?=$res["URL"]["MODERATE_MESSAGE"]?>" title="<?=GetMessage("F_MESSAGE_NOT_APPROVED")?>"><?=$res["mCnt"]?></a></noindex>)</span></td>
<?
						else:
?>
					<td class="forum-column-replies"><span><?=$res["POSTS"]?></span></td>
<?
						endif;
?>
					<td class="forum-column-views"><span><?=$res["VIEWS"]?></span></td>
					<td class="forum-column-lastpost"><?
						if ($arResult["PERMISSION"] >= "Q"):
?>
						<div class="forum-select-box"><input type="checkbox" name="TID[]" value="<?=$res["ID"]?>" onclick="SelectRow(this.parentNode.parentNode.parentNode)" /></div>
<?
						endif;
						if ($res["LAST_MESSAGE_ID"] > 0):
?>
						<div class="forum-lastpost-box">
							<span class="forum-lastpost-date"><noindex><a rel="nofollow" href="<?=$res["URL"]["LAST_MESSAGE"]?>"><?=$res["LAST_POST_DATE"]?></a></noindex></span>
							<span class="forum-lastpost-title"><span class="forum-lastpost-author"><?=$res["LAST_POSTER_NAME"]?></span></span>
						</div>
<?
						else:
?>
						&nbsp;
<?
						endif;
?>
					</td>
				</tr>
<?
		endforeach;
?>
			</tbody>
			<tfoot>
				<tr>
					<td colspan="<?=($arParams["SHOW_AUTHOR_COLUMN"] == "Y" ? "6" : "5")?>" class="forum-column-footer">
						<div class="forum-footer-inner">
<?
						if ($arResult["PERMISSION"] >= "Q"):
?>
							<div class="forum-topics-moderate">
								<select name="ACTION">
									<option value=""><?=GetMessage("F_MANAGE_TOPICS")?></option>
									<option value="SET_TOP"><?=GetMessage("F_MANAGE_PIN")?></option>
									<option value="SET_ORDINARY"><?=GetMessage("F_MANAGE_UNPIN")?></option>
									<option value="STATE_Y"><?=GetMessage("F_MANAGE_OPEN")?></option>
									<option value="STATE_N"><?=GetMessage("F_MANAGE_CLOSE")?></option>
									<option value="MOVE_TOPIC"><?=GetMessage("F_MANAGE_MOVE")?></option>
<?
						if ($arResult["PERMISSION"] >= "U"):
?>
									<option value="DEL_TOPIC"><?=GetMessage("F_MANAGE_DELETE")?></option>
<?
						endif;
?>
								</select>&nbsp;<input type="submit" value="OK" />
							</div>
<?
						endif;
						if ($USER->IsAuthorized()):
?>
							<span class="forum-footer-option forum-footer-markread forum-footer-option-first"><?
								?><noindex><a rel="nofollow" <?
									?>href="<?=$APPLICATION->GetCurPageParam("ACTION=SET_BE_READ", array("ACTION", "sessid"))?>" <?
									?>onclick="return this.href+=('&sessid='+BX.bitrix_sessid());"><?=GetMessage("F_SET_FORUM_READ")?></a></noindex></span>
<?
						endif;
						if ($arResult["PERMISSION"] >= "Q"):
?>
							<span class="forum-footer-option forum-footer-selectall"><?
								?><noindex><a rel="nofollow" href="javascript:void(0);" onclick="SelectRows('<?=$iIndex?>');" name=""><?=GetMessage("F_SELECT_ALL")?></a></noindex></span>
<?
						elseif (!$USER->IsAuthorized()):
?>
							&nbsp;
<?
						endif;
						
?>
						</div>
					</td>
				</tr>
			</tfoot>
<?
endif;
?>
			</table>
		</div>
	</div>
</div>
<?
if ($arResult["PERMISSION"] >= "Q"):
?>
</form>
<?
endif;
?>
<div class="forum-navigation-box forum-navigation-bottom">
	<div class="forum-page-navigation">
		<?=$arResult["NAV_STRING"]?>
	</div>
<?
if ($arResult["USER"]["RIGHTS"]["CAN_ADD_TOPIC"] == "Y"):
?>
	<div class="forum-new-post">
		<noindex><a rel="nofollow" href="<?=$arResult["URL"]["TOPIC_NEW"]?>" title="<?=GetMessage("F_NEW_TOPIC_TITLE")?>"><span><?=GetMessage("F_NEW_TOPIC")?></span></a></noindex>
	</div>
<?
endif;
?>
	<div class="forum-clear-float"></div>
</div>
<?

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

?>
<script>
if (typeof oText != "object")
		var oText = {};
oText['empty_action'] = '<?=CUtil::addslashes(GetMessage("JS_NO_ACTION"))?>';
oText['empty_topics'] = '<?=CUtil::addslashes(GetMessage("JS_NO_TOPICS"))?>';
oText['del_topics'] = '<?=CUtil::addslashes(GetMessage("JS_DEL_TOPICS"))?>';
</script>
