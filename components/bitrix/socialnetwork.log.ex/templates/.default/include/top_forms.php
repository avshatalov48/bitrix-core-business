<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

/** @var CBitrixComponentTemplate $this */
/** @var array $arParams */
/** @var array $arResult */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */

$component = $this->getComponent();

if (
	$arParams["IS_CRM"] === "Y"
	&& (
		!isset($arParams["CRM_ENABLE_ACTIVITY_EDITOR"])
		|| $arParams["CRM_ENABLE_ACTIVITY_EDITOR"] === true
	)
)
{
	$APPLICATION->IncludeComponent(
		'bitrix:crm.activity.editor',
		'',
		[
			'CONTAINER_ID' => '',
			'EDITOR_ID' => 'livefeed',
			'EDITOR_TYPE' => 'MIXED',
			'PREFIX' => 'crm_activity_livefeed',
			'OWNER_TYPE' => '',
			'OWNER_ID' => 0,
			'READ_ONLY' => false,
			'ENABLE_UI' => false,
			'ENABLE_TASK_TRACING' => false,
			'ENABLE_TASK_ADD' => true,
			'ENABLE_CALENDAR_EVENT_ADD' => true,
			'ENABLE_EMAIL_ADD' => true,
			'ENABLE_TOOLBAR' => false,
			'EDITOR_ITEMS' => array(),
			'SKIP_VISUAL_COMPONENTS' => 'Y'
		],
		null,
		[ "HIDE_ICONS" => "Y" ]
	);
}

if ($arParams['USE_TASKS'] === 'Y')
{
	?><?php
	$APPLICATION->IncludeComponent(
		"bitrix:tasks.iframe.popup",
		".default",
		[
			"ON_TASK_ADDED" => "BX.DoNothing",
			"ON_TASK_CHANGED" => "BX.DoNothing",
			"ON_TASK_DELETED" => "BX.DoNothing",
		],
		null,
		[ 'HIDE_ICONS' => 'Y' ]
	);
	?><?php
}

if (
	$arParams["HIDE_EDIT_FORM"] !== "Y"
	&& (int)$arResult["MICROBLOG_USER_ID"] > 0
	&& $USER->IsAuthorized()
)
{
	$arBlogComponentParams = [
		"ID" => "new",
		"PATH_TO_BLOG" => $APPLICATION->GetCurPageParam("", [ "WFILES" ]),
		"PATH_TO_POST" => $arParams["PATH_TO_USER_MICROBLOG_POST"],
		"PATH_TO_GROUP_POST" => $arParams["PATH_TO_GROUP_MICROBLOG_POST"],
		"PATH_TO_POST_EDIT" => $arParams["PATH_TO_USER_BLOG_POST_EDIT"],
		"PATH_TO_SMILE" => $arParams["PATH_TO_BLOG_SMILE"] ?? '',
		"SET_TITLE" => "N",
		"GROUP_ID" => $arParams["BLOG_GROUP_ID"],
		"USER_ID" => $arResult["currentUserId"],
		"SET_NAV_CHAIN" => "N",
		"USE_SOCNET" => "Y",
		"MICROBLOG" => "Y",
		"USE_CUT" => $arParams["BLOG_USE_CUT"] ?? '',
		"NAME_TEMPLATE" => $arParams["NAME_TEMPLATE"],
		"CHECK_PERMISSIONS_DEST" => $arParams["CHECK_PERMISSIONS_DEST"],
		"TOP_TABS_VISIBLE" => (array_key_exists("TOP_TABS_VISIBLE", $arParams) ? $arParams["TOP_TABS_VISIBLE"] : "Y"),
		"SHOW_BLOG_FORM_TARGET" => $arResult["FORM_TARGET_ID"] === false,
		"SELECTOR_VERSION" => 3,
	];

	$currentUserId = $arParams["CURRENT_USER_ID"] ?? 0;
	if ($arParams["ENTITY_TYPE"] === SONET_ENTITY_GROUP)
	{
		$arBlogComponentParams["SOCNET_GROUP_ID"] = $arParams["GROUP_ID"];
	}
	elseif ((int) $arResult["currentUserId"] !== (int) $currentUserId)
	{
		$arBlogComponentParams["SOCNET_USER_ID"] = $currentUserId;
	}

	if (isset($arParams["DISPLAY"]))
	{
		$arBlogComponentParams["DISPLAY"] = $arParams["DISPLAY"];
	}
	if (isset($arParams["PAGE_ID"]))
	{
		$arBlogComponentParams["PAGE_ID"] = $arParams["PAGE_ID"];
	}

	if (defined("BITRIX24_INDEX_COMPOSITE"))
	{
		$arBlogComponentParams["POST_FORM_ACTION_URI"] = "/stream/";
	}

	if ($arParams['USE_TASKS'] === 'Y')
	{
		$arBlogComponentParams["PATH_TO_USER_TASKS"] = $arParams['PATH_TO_USER_TASKS'];
		$arBlogComponentParams["PATH_TO_USER_TASKS_TASK"] = $arParams['PATH_TO_USER_TASKS_TASK'];
		$arBlogComponentParams["PATH_TO_GROUP_TASKS"] = $arParams['PATH_TO_GROUP_TASKS'];
		$arBlogComponentParams["PATH_TO_GROUP_TASKS_TASK"] = $arParams['PATH_TO_GROUP_TASKS_TASK'];
		$arBlogComponentParams["PATH_TO_USER_TASKS_PROJECTS_OVERVIEW"] = $arParams['PATH_TO_USER_TASKS_PROJECTS_OVERVIEW'];
		$arBlogComponentParams["PATH_TO_USER_TEMPLATES_TEMPLATE"] = $arParams['PATH_TO_USER_TEMPLATES_TEMPLATE'];
		$arBlogComponentParams["LOG_EXPERT_MODE"] = ($arResult["EXPERT_MODE"] ?? "N");
	}

	$staticHtmlCache = \Bitrix\Main\Data\StaticHtmlCache::getInstance();
	$staticHtmlCache->disableVoting();

	if ($arResult["FORM_TARGET_ID"])
	{
		$this->SetViewTarget($arResult["FORM_TARGET_ID"]);
	}

	?><div id="sonet_log_microblog_container"><?php
		$APPLICATION->IncludeComponent(
			"bitrix:socialnetwork.blog.post.edit",
			"",
			$arBlogComponentParams,
			$component,
			[ 'HIDE_ICONS' => 'Y' ]
		);
	?></div><?php

	if ($arResult["FORM_TARGET_ID"])
	{
		$this->EndViewTarget();
	}

	$staticHtmlCache->enableVoting();
}
elseif ($arParams["SHOW_EVENT_ID_FILTER"] === "Y")
{
	?><div class="feed-filter-fake-cont"></div><?php
}

