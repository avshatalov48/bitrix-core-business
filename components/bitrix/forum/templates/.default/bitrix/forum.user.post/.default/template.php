<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
$filter_value_fid = array(
	"0" => GetMessage("F_ALL_FORUMS"));
if (is_array($arResult["GROUPS_FORUMS"])):
	foreach ($arResult["GROUPS_FORUMS"] as $key => $res):
		if ($res["TYPE"] == "GROUP"):
			$filter_value_fid["GROUP_".$res["ID"]] = array(
				"NAME" => ($res["DEPTH"] > 0 ? str_pad("", ($res["DEPTH"] - 1)*4, " ") : "").$res["~NAME"],
				"CLASS" => "forums-selector-optgroup level".$res["DEPTH"],
				"TYPE" => "OPTGROUP");
		else:
			$filter_value_fid[$res["ID"]] = array(
				"NAME" => ($res["DEPTH"] > 0 ? str_pad("", ($res["DEPTH"] + 1)*4, " ") : "").$res["~NAME"],
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
		"FIELDS" => array_merge(array(
			array(
				"NAME" => "PAGE_NAME",
				"TYPE" => "HIDDEN",
				"VALUE" => "user_post"),
			array(
				"NAME" => "UID",
				"TYPE" => "HIDDEN",
				"VALUE" => $arParams["UID"]),
			array(
				"NAME" => "mode",
				"TYPE" => "HIDDEN",
				"VALUE" => $arParams["mode"]),
			array(
				"TITLE" => GetMessage("LU_FORUM"),
				"NAME" => "fid",
				"TYPE" => "SELECT",
				"CLASS" => "forums-selector-single",
				"VALUE" => $filter_value_fid,
				"ACTIVE" => isset($_REQUEST["fid"]) ? $_REQUEST["fid"] : null),
			array(
				"TITLE" => GetMessage("LU_DATE_CREATE"),
				"NAME" => "date_create",
				"NAME_TO" => "date_create1",
				"TYPE" => "PERIOD",
				"VALUE" => isset($_REQUEST["date_create"]) ? $_REQUEST["date_create"] : null,
				"VALUE_TO" => isset($_REQUEST["date_create1"]) ? $_REQUEST["date_create1"] : null),
			array(
				"TITLE" => GetMessage("LU_TOPIC"),
				"NAME" => "topic",
				"TYPE" => "TEXT",
				"VALUE" => isset($_REQUEST["topic"]) ? $_REQUEST["topic"] : null),
			array(
				"TITLE" => GetMessage("LU_MESSAGE"),
				"NAME" => "message",
				"TYPE" => "TEXT",
				"VALUE" => isset($_REQUEST["message"]) ? $_REQUEST["message"] : null)),
			($arParams["mode"] == "all" ? array(
			array(
				"TITLE" => GetMessage("LU_SORT"),
				"NAME" => "sort",
				"TYPE" => "SELECT",
				"VALUE" => array(
					"topic" => array("NAME" => GetMessage("LU_BY_TOPIC")),
					"message" => array("NAME" => GetMessage("LU_BY_MESSAGE"))),
				"ACTIVE" => isset($_REQUEST["sort"]) ? $_REQUEST["sort"] : null)) : array())
			)),

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
if (!empty($arResult["OK_MESSAGE"])):
?>
<div class="forum-note-box forum-note-success">
	<div class="forum-note-box-text"><?=ShowNote($arResult["OK_MESSAGE"], "forum-note-success")?></div>
</div>
<?
endif;

if ($arResult["NAV_RESULT"] && $arResult["NAV_RESULT"]->NavPageCount > 0):
?><div class="forum-navigation-box forum-navigation-top">
	<div class="forum-page-navigation">
		<?=$arResult["NAV_STRING"]?>
	</div>
	<div class="forum-clear-float"></div>
</div>
<?
endif;

if (empty($arResult["FORUMS"])):
?>
<div class="forum-info-box forum-user-posts">
	<div class="forum-info-box-inner">
		<?=GetMessage("FR_EMPTY")?>
	</div>
</div>
<?
	return false;
endif;

$arMessages = $arResult["MESSAGE_LIST"];
if (isset($_REQUEST["sort"]) && $_REQUEST["sort"] == "topic")
{
	$arTopic = reset($arResult["TOPICS"]);
	$arMessages = $arTopic["MESSAGES"];
}
while (!empty($arMessages))
{
	$cntrMessages = 0;
	$cntMessages = count($arMessages);
	foreach ($arMessages as $res)
	{
		$arTopic = $arResult["TOPICS"][$res["TOPIC_ID"]];
		$arForum = $arResult["FORUMS"][$arTopic["FORUM_ID"]];
		$cntrMessages++;
		if (isset($_REQUEST["sort"]) && $_REQUEST["sort"] == "topic")
		{
			if ($cntrMessages == 1)
			{
?>
<div class="forum-header-box">
	<div class="forum-header-options">
		<span class="forum-option-messages"><a href="<?=$arTopic["URL"]["TOPIC"]?>"><?
				?><?=GetMessage("LU_USER_POSTS_ON_TOPIC")?>: <span><?=$arTopic["COUNT_MESSAGE"]?></span><?
				?></a></span>
	</div>
	<div class="forum-header-title"><span><?
			if ($arTopic["STATE"] != "Y"):
			?><span class="forum-header-title-closed">[ <span><?=GetMessage("F_CLOSED")?></span> ]</span> <?
			endif;
			?><?=trim($arTopic["TITLE"])?><?
			if ($arTopic["DESCRIPTION"] <> ''):
			?>, <?=trim($arTopic["DESCRIPTION"])?><?
			endif;
			?></span></div>
</div>
<div class="forum-block-container">
	<div class="forum-block-outer">
		<div class="forum-block-inner">
			<?
			}
		}
		else
		{
?>
<div class="forum-header-box">
	<div class="forum-header-title">
		<span><?if ($arTopic["STATE"] != "Y"): ?><span class="forum-header-title-closed">[ <span><?=GetMessage("F_CLOSED")?></span>]</span> <? endif;
		?><?=trim($arTopic["TITLE"])?><?if ($arTopic["DESCRIPTION"] <> ''): ?>, <?=trim($arTopic["DESCRIPTION"])?><? endif; ?></span>
	</div>
</div>
<div class="forum-block-container">
	<div class="forum-block-outer">
		<div class="forum-block-inner">
<?
		}
			?><?$GLOBALS["APPLICATION"]->IncludeComponent(
				"bitrix:forum.message.template", "",
				Array(
					"MESSAGE" => array_merge($res,
						array(
						"AUTHOR_STATUS" => $arForum["AUTHOR_STATUS"] ?? null,
						"AUTHOR_STATUS_CODE" => $arForum["AUTHOR_STATUS_CODE"] ?? null,
						"AVATAR" => $arResult["USER"]["~AVATAR"] ?? null,
						"NEW_TOPIC" => "N",
						"SHOW_CONTROL" => "N",
						"PANELS" => array("GOTO" => "Y"))),
					"ATTACH_MODE" => $arParams["ATTACH_MODE"] ?? null,
					"ATTACH_SIZE" => $arParams["ATTACH_SIZE"] ?? null,
					"COUNT" => 0,
					"NUMBER" => 1,
					"SEO_USER" => $arParams["SEO_USER"] ?? null,
					"SHOW_RATING" => "N",
					"RATING_ID" => "",
					"RATING_TYPE" => "",
					"arRatingVote" => "",
					"arRatingResult" => "",
					"arResult" => $arResult,
					"arParams" => $arParams
				),
				$component->__parent,
				array("HIDE_ICONS" => "Y")
				);?><?
		if (isset($_REQUEST["sort"]) && $_REQUEST["sort"] == "topic")
		{
			if ($cntMessages == $cntrMessages)
			{
?>
		</div>
	</div>
</div>
		<?
			}
		}
		else
		{
?>
		</div>
	</div>
</div>
<?
		}
	}

	$arMessages = ((isset($_REQUEST["sort"]) && $_REQUEST["sort"] == "topic" && ($arTopic = next($arResult["TOPICS"])) && !!$arTopic) ? $arTopic["MESSAGES"] : null);
}

if ($arResult["NAV_RESULT"] && $arResult["NAV_RESULT"]->NavPageCount > 0):
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
