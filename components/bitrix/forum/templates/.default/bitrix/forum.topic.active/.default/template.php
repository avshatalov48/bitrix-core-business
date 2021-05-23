<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
if (!$this->__component->__parent || empty($this->__component->__parent->__name)):
	$GLOBALS['APPLICATION']->SetAdditionalCSS('/bitrix/components/bitrix/forum/templates/.default/style.css');
	$GLOBALS['APPLICATION']->SetAdditionalCSS('/bitrix/components/bitrix/forum/templates/.default/themes/blue/style.css');
	$GLOBALS['APPLICATION']->SetAdditionalCSS('/bitrix/components/bitrix/forum/templates/.default/styles/additional.css');
endif;
/********************************************************************
				Input params
********************************************************************/
/***************** BASE ********************************************/
global $find_forum, $find_date1, $find_date2;
/********************************************************************
				/Input params
********************************************************************/
// For filter only
$filter_value_fid = array(
	"0" => GetMessage("F_ALL_FORUMS"), 
	"separator" => array("NAME" => " ", "TYPE" => "OPTGROUP"));
if (is_array($arResult["GROUPS_FORUMS"])):
	foreach ($arResult["GROUPS_FORUMS"] as $key => $res):
		if ($res["TYPE"] == "GROUP"):
			$filter_value_fid["GROUP_".$res["ID"]] = array(
				"NAME" => str_pad("", ($res["DEPTH"] - 1)*6, "&nbsp;").$res["~NAME"], 
				"CLASS" => "forums-selector-optgroup level".$res["DEPTH"], 
				"TYPE" => "OPTGROUP");
		else:
			$filter_value_fid[$res["ID"]] = array(
				"NAME" => ($res["DEPTH"] > 0 ? str_pad("", $res["DEPTH"]*6, "&nbsp;")."&nbsp;" : "").$res["~NAME"], 
				"CLASS" => "forums-selector-option level".$res["DEPTH"], 
				"TYPE" => "OPTION");
		endif;
	endforeach;
endif;
?>
<div class="forum-info-box forum-filter">
	<div class="forum-info-box-inner">
<?
$APPLICATION->IncludeComponent("bitrix:forum.interface", "filter_simple",
	array(
		"FORM_METHOD_GET" => 'Y',
		"HEADER" => array(
			"TITLE" => GetMessage("F_TITLE")),
		"FIELDS" => array(
			array(
				"NAME" => "PAGE_NAME",
				"TYPE" => "HIDDEN",
				"VALUE" => "active"),
			array(
				"TITLE" => GetMessage("F_FILTER_FORUM"),
				"NAME" => "find_forum",
				"TYPE" => "SELECT",
				"CLASS" => "forums-selector-single",
				"VALUE" => $filter_value_fid,
				"ACTIVE" => $find_forum),
			array(
				"TITLE" => GetMessage("F_FILTER_LAST_MESSAGE_DATE"),
				"NAME" => "find_date1",
				"NAME_TO" => "find_date2",
				"TYPE" => "PERIOD",
				"VALUE" => $find_date1,
				"VALUE_TO" => $find_date2)
		)),
		$component,
		array(
			"HIDE_ICONS" => "Y"));?><?
?>
	</div>
</div>

<br/>
<?
if (!empty($arResult["ERROR_MESSAGE"])): 
?>
<div class="forum-note-box forum-note-error">
	<div class="forum-note-box-text"><?=ShowError($arResult["ERROR_MESSAGE"], "forum-note-error");?></div>
</div>
<?
endif;

if ($arResult["NAV_RESULT"]->NavPageCount > 0):
?><div class="forum-navigation-box forum-navigation-top">
	<div class="forum-page-navigation">
		<?=$arResult["NAV_STRING"]?>
	</div>
	<div class="forum-clear-float"></div>
</div>
<?
endif;
?>
<div class="forum-header-box">
	<div class="forum-header-title"><span><?=GetMessage("F_TITLE")?></span></div>
</div>

<div class="forum-block-container">
	<div class="forum-block-outer">
		<div class="forum-block-inner">
			<table cellspacing="0" class="forum-table forum-topic-list">
			<thead>
				<tr>
					<th class="forum-column-title" colspan="2"><div class="forum-head-title"><span><?=GetMessage("F_HEAD_TOPICS")?></span></div></th>
					<th class="forum-column-replies"><span><?=GetMessage("F_HEAD_POSTS")?><?/*?><?=$arResult["SortingEx"]["POSTS"]?><?*/?></span></th>
					<th class="forum-column-views"><span><?=GetMessage("F_HEAD_VIEWS")?></span><?/*?><?=$arResult["SortingEx"]["VIEWS"]?><?*/?></th>
					<th class="forum-column-lastpost"><span><?=GetMessage("F_HEAD_LAST_POST")?></span><?/*?><?=$arResult["SortingEx"]["LAST_POST_DATE"]?><?*/?></th>
				</tr>
			</thead>
			<tbody>
<?
if ($arResult["SHOW_RESULT"] == "Y"):
	$iCount = 0;
	$arTopics = array();
	foreach ($arResult["TOPICS"] as $res):
		$arTopics[] = $res["ID"];
		$iCount++;
?>
				<tr class="<?=($iCount == 1 ? "forum-row-first " : (
					$iCount == count($arResult["TOPICS"]) ? "forum-row-last " : ""))?><?
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
								?> forum-icon-sticky <?
								$title = GetMessage("F_PINNED_TOPIC");
							endif;
							if ($res["TopicStatus"] == "MOVED"):
								$title = GetMessage("F_MOVED_TOPIC");
								?> forum-icon-moved <?
							elseif ($res["STATE"] != "Y" && $res["STATE"] != "L"):
								$title = (intval($res["SORT"]) != 150 ? GetMessage("F_PINNED_CLOSED_TOPIC") : GetMessage("F_CLOSED_TOPIC")).
									" (".GetMessage("F_HAVE_NEW_MESS").")";
									?> forum-icon-closed-newposts <?
							else:
								$title .= (empty($title) ? GetMessage("F_HAVE_NEW_MESS") : " (".GetMessage("F_HAVE_NEW_MESS").")");
								?> forum-icon-newposts <?
							endif;
							
							?>" title="<?=$title?>"><!-- ie --></div>
						</div>
					</td>
					<td class="forum-column-title">
						<div class="forum-item-info">
							<div class="forum-item-name"><?
						if ($res["TopicStatus"] == "MOVED"):
								?><span class="forum-status-moved-block"><span class="forum-status-moved"><?=GetMessage("F_MOVED")?></span>:&nbsp;</span><?
						elseif (intval($res["SORT"]) != 150 && ($res["STATE"]!="Y") && ($res["STATE"]!="L")):
								?><span class="forum-status-sticky-block"><span class="forum-status-sticky"><?=GetMessage("F_PINNED")?></span>, </span><?
								?><span class="forum-status-closed-block"><span class="forum-status-closed"><?=GetMessage("F_CLOSED")?></span>:&nbsp;</span><?
						elseif (intval($res["SORT"]) != 150):
								?><span class="forum-status-sticky-block"><span class="forum-status-sticky"><?=GetMessage("F_PINNED")?></span>:&nbsp;</span><?
						elseif (($res["STATE"]!="Y") && ($res["STATE"]!="L")):
								?><span class="forum-status-closed-block"><span class="forum-status-closed"><?=GetMessage("F_CLOSED")?></span>:&nbsp;</span><?
						endif;
								?><span class="forum-item-title"><?
						if (false && $res["IMAGE"] <> ''):
								?><img src="<?=$res["IMAGE"];?>" alt="<?=$res["IMAGE_DESCR"];?>" border="0" width="15" height="15"/><?
						endif;
								?><a href="<?=$res["URL"]["TOPIC"]?>" title="<?=GetMessage("F_TOPIC_START")?> <?=$res["START_DATE"]?>"><?=$res["TITLE"]?></a></span><?
						if ($res["PAGES_COUNT"] > 1):
								?> <span class="forum-item-pages">(<?
							$iCount = intval($res["PAGES_COUNT"] > 5 ? 3 : $res["PAGES_COUNT"]);
							for ($ii = 1; $ii <= $iCount; $ii++):
								?><noindex><a rel="nofollow" href="<?=ForumAddPageParams($res["URL"]["~TOPIC"], array("PAGEN_".$arParams["PAGEN"] => $ii))?>"><?
									?><?=$ii?></a></noindex><?=($ii < $iCount ? ",&nbsp;" : "")?><?
							endfor;
							if ($iCount < $res["PAGES_COUNT"]):
								?>&nbsp;...&nbsp;<noindex><a rel="nofollow" href="<?=ForumAddPageParams($res["URL"]["~TOPIC"], 
									array("PAGEN_".$arParams["PAGEN"] => $res["PAGES_COUNT"]))?>"><?=$res["PAGES_COUNT"]?></a></noindex><?
							endif;
								?>)</span><?
						endif;
							?></div>
<?
						if (!empty($res["DESCRIPTION"])):
?>
							<span class="forum-item-desc"><?=$res["DESCRIPTION"]?></span><span class="forum-item-desc-sep">&nbsp;&middot; </span>
<?
						endif;
							?><span class="forum-item-author"><span><?=GetMessage("F_AUTHOR")?></span>&nbsp;<?=$res["USER_START_NAME"]?></span>
						</div>
					</td>
<?
						if ($res["PERMISSION"] >= "Q" && $res["mCnt"] > 0):
?>
					<td class="forum-column-replies forum-cell-hidden"><span><?=$res["POSTS"]?> <?
						?>(<noindex><a rel="nofollow" href="<?=$res["URL"]["MODERATE_MESSAGE"]?>" title="<?=GetMessage("F_MESSAGE_NOT_APPROVED")?>"><?
							?><?=$res["mCnt"]?></a></noindex>)</span></td>
<?
						else:
?>
					<td class="forum-column-replies"><span><?=$res["POSTS"]?></span></td>
<?
						endif;
?>
					<td class="forum-column-views"><span><?=$res["VIEWS"]?></span></td>
					<td class="forum-column-lastpost"><?
						if ($res["LAST_MESSAGE_ID"] > 0):
?>
							<div class="forum-lastpost-box">
							<span class="forum-lastpost-date"><noindex><a rel="nofollow" href="<?=$res["URL"]["LAST_MESSAGE"]?>"><?=$res["LAST_POST_DATE"]?></a></noindex></span>
							<span class="forum-lastpost-title"><span class="forum-lastpost-author"><?=$res["LAST_POSTER_NAME"]?></span></span>
						</div>
<?
						endif;
?>
					</td>
				</tr>
<?
	endforeach;
else:
?>
				<tr class="forum-row-first forum-row-odd">
					<td class="forum-column-icon" colspan="5">
						<div class="forum-item-info">
							<?=GetMessage("F_TOPICS_LIST_IS_EMPTY")?>
						</div>
					</td>
				</tr>
<?
endif;
?>
				</tbody>
			<tfoot>
				<tr>
					<td colspan="<?=($arParams["SHOW_AUTHOR_COLUMN"] == "Y" ? "6" : "5")?>" class="forum-column-footer">
						<div class="forum-footer-inner">
<?
						if ($USER->IsAuthorized() && $arResult["SHOW_RESULT"] == "Y"):
							$arParamKill = array("ACTION", "sessid", "TID", "FID", "find_forum", "find_date1", "find_date1_DAYS_TO_BACK", "find_date2", 
									"set_filter", "del_filter");
?>
							<span class="forum-footer-option  forum-footer-markread  forum-footer-markread-topics forum-footer-option-first">
								<noindex><a rel="nofollow" href="<?=$APPLICATION->GetCurPageParam("ACTION=SET_BE_READ&".bitrix_sessid_get()."&TID=".implode(",", $arTopics), $arParamKill)
								?>" title="<?=GetMessage("F_SET_READ_ON_THIS_PAGE_TITLE")?>"><?=GetMessage("F_SET_READ_ON_THIS_PAGE")?></a></noindex></span>
									
<?
							if ($GLOBALS["find_forum"] > 0):
?>
							<span class="forum-footer-option forum-footer-markread  forum-footer-markread-forums">
								<noindex><a rel="nofollow" href="<?=$APPLICATION->GetCurPageParam("ACTION=SET_BE_READ&".bitrix_sessid_get()."&FID=".$GLOBALS["find_forum"], $arParamKill)
								?>" title="<?=GetMessage("F_SET_READ_THIS_FORUM_TITLE")?>"><?=GetMessage("F_SET_READ_THIS_FORUM")?></a></noindex></span>
<?
							else:
?>
							<span class="forum-footer-option  forum-footer-markread  forum-footer-markread-topics">
								<noindex><a rel="nofollow" href="<?=$APPLICATION->GetCurPageParam("ACTION=SET_BE_READ&".bitrix_sessid_get()."&FID=all", $arParamKill)
								?>" title="<?=GetMessage("F_SET_READ_TITLE")?>"><?=GetMessage("F_SET_READ")?></a></noindex></span>
<?
							endif;
						else:
?>
							&nbsp;
<?
						endif;
						
?>
						</div>
					</td>
				</tr>
			</tfoot>
			</table>
		</div>
	</div>
</div>
<?
if ($arResult["NAV_RESULT"]->NavPageCount > 0):
?>
<div class="forum-navigation-box forum-navigation-bottom">
	<div class="forum-page-navigation">
		<?=$arResult["NAV_STRING"]?>
	</div>
	<div class="forum-clear-float"></div>
</div>
<?
endif;
?>
