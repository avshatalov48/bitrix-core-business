<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/** @var CBitrixComponentTemplate $this */
/** @var array $arParams */
/** @var array $arResult */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */
$component = $this->getComponent();

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ModuleManager;

$this->setFrameMode(true);

if ($arResult["NEED_AUTH"] == "Y")
{
	$APPLICATION->AuthForm("");
}
elseif (strlen($arResult["FatalError"])>0)
{
	?><span class='errortext'><?=$arResult["FatalError"]?></span><br /><br /><?
	return;
}

CUtil::InitJSCore(array("ajax", "window", "tooltip", "popup", "fx", "viewer", "content_view", "clipboard"));

$APPLICATION->SetUniqueJS('live_feed_v2'.($arParams["IS_CRM"] != "Y" ? "" : "_crm"));
$APPLICATION->SetUniqueCSS('live_feed_v2'.($arParams["IS_CRM"] != "Y" ? "" : "_crm"));

if (SITE_TEMPLATE_ID == "bitrix24")
{
	$bodyClass = $APPLICATION->GetPageProperty("BodyClass");
	$APPLICATION->SetPageProperty("BodyClass", ($bodyClass ? $bodyClass." " : "")."workarea-transparent");
}

if ($arParams["IS_CRM"] !== "Y")
{
	$bodyClass = $APPLICATION->GetPageProperty("BodyClass");
	$bodyClass = $bodyClass ? $bodyClass." no-all-paddings" : "no-all-paddings";
	$APPLICATION->SetPageProperty("BodyClass", $bodyClass);
	if (
		SITE_TEMPLATE_ID == "bitrix24"
		&& (
			(
				ModuleManager::isModuleInstalled('bitrix24')
				&& \CBitrix24::isPortalAdmin($arResult["currentUserId"])
			)
			|| (
				!ModuleManager::isModuleInstalled('bitrix24')
				&& $USER->IsAdmin()
			)
		)
	)
	{
		\Bitrix\Socialnetwork\ComponentHelper::getLivefeedStepper();
	}
}

$log_content_id = "sonet_log_content_".RandString(8);
$event_cnt = 0;

$stub = '
	<div class="bx-placeholder">
		<table class="bx-feed-curtain">
			<tr class="bx-curtain-row-0"><td class="bx-curtain-cell-1"></td><td class="bx-curtain-cell-2 transparent"></td><td class="bx-curtain-cell-3"></td><td class="bx-curtain-cell-4"></td><td class="bx-curtain-cell-5"></td><td class="bx-curtain-cell-6"></td><td class="bx-curtain-cell-7"></td></tr><tr class="bx-curtain-row-1 2"><td class="bx-curtain-cell-1"></td><td class="bx-curtain-cell-2 transparent"></td><td class="bx-curtain-cell-3"></td><td class="bx-curtain-cell-4 transparent"></td><td class="bx-curtain-cell-5" colspan="3"></td></tr><tr class="bx-curtain-row-2 3"><td class="bx-curtain-cell-1"></td><td class="bx-curtain-cell-2 transparent"><div class="bx-bx-curtain-avatar"></div></td><td class="bx-curtain-cell-3" colspan="5"></td></tr>
			<tr class="bx-curtain-row-1"><td class="bx-curtain-cell-1"></td><td class="bx-curtain-cell-2 transparent"></td><td class="bx-curtain-cell-3"></td><td class="bx-curtain-cell-4 transparent" colspan="3"></td><td class="bx-curtain-cell-7"></td></tr>
			<tr class="bx-curtain-row-2"><td class="bx-curtain-cell-1" colspan="7"></td></tr>
			<tr class="bx-curtain-row-1"><td class="bx-curtain-cell-1" colspan="3"></td><td class="bx-curtain-cell-4 transparent" colspan="3"></td><td class="bx-curtain-cell-7"></td></tr>
			<tr class="bx-curtain-row-2"><td class="bx-curtain-cell-1" colspan="7"></td></tr>
			<tr class="bx-curtain-row-1"><td class="bx-curtain-cell-1" colspan="3"></td><td class="bx-curtain-cell-4 transparent" colspan="3"></td><td class="bx-curtain-cell-7"></td></tr>
			<tr class="bx-curtain-row-2"><td class="bx-curtain-cell-1" colspan="7"></td></tr>
			<tr class="bx-curtain-row-1"><td class="bx-curtain-cell-1" colspan="3"></td><td class="bx-curtain-cell-4 transparent" colspan="2"></td><td class="bx-curtain-cell-6" colspan="2"></td></tr><tr class="bx-curtain-row-last"><td class="bx-curtain-cell-1" colspan="7"></td></tr>
		</table>
	</div>
';

$stub = '<div class="bx-placeholder-wrap">'.str_repeat($stub, 4).'</div>';

if (!$arResult["AJAX_CALL"])
{
	$APPLICATION->AddHeadScript("/bitrix/components/bitrix/socialnetwork.log.entry/templates/.default/scripts.js");

	if ($arParams["IS_CRM"] == "Y" && (!isset($arParams["CRM_ENABLE_ACTIVITY_EDITOR"]) || $arParams["CRM_ENABLE_ACTIVITY_EDITOR"] === true))
	{
		$APPLICATION->IncludeComponent(
			'bitrix:crm.activity.editor',
			'',
			array(
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
				'EDITOR_ITEMS' => array()
			),
			null,
			array('HIDE_ICONS' => 'Y')
		);
	}

	$APPLICATION->IncludeComponent("bitrix:main.user.link",
		'',
		array(
			"AJAX_ONLY" => "Y",
			"PATH_TO_SONET_USER_PROFILE" => $arParams["~PATH_TO_USER"],
			"PATH_TO_SONET_MESSAGES_CHAT" => $arParams["~PATH_TO_MESSAGES_CHAT"],
			"DATE_TIME_FORMAT" => $arParams["DATE_TIME_FORMAT"],
			"SHOW_YEAR" => $arParams["SHOW_YEAR"],
			"NAME_TEMPLATE" => $arParams["NAME_TEMPLATE"],
			"SHOW_LOGIN" => $arParams["SHOW_LOGIN"],
			"PATH_TO_CONPANY_DEPARTMENT" => $arParams["~PATH_TO_CONPANY_DEPARTMENT"],
			"PATH_TO_VIDEO_CALL" => $arParams["~PATH_TO_VIDEO_CALL"],
		),
		false,
		array("HIDE_ICONS" => "Y")
	);

	if (IsModuleInstalled('tasks'))
	{
		?><?
		$APPLICATION->IncludeComponent(
			"bitrix:tasks.iframe.popup",
			".default",
			array(
				"ON_TASK_ADDED" => "BX.DoNothing",
				"ON_TASK_CHANGED" => "BX.DoNothing",
				"ON_TASK_DELETED" => "BX.DoNothing",
			),
			null,
			array("HIDE_ICONS" => "Y")
		);
		?><?
	}

	if ($arParams["HIDE_EDIT_FORM"] != "Y" && IntVal($arResult["MICROBLOG_USER_ID"]) > 0 && $USER->IsAuthorized())
	{
		$arBlogComponentParams = Array(
			"ID" => "new",
			"PATH_TO_BLOG" => $APPLICATION->GetCurPageParam("", array("WFILES")),
			"PATH_TO_POST" => $arParams["PATH_TO_USER_MICROBLOG_POST"],
			"PATH_TO_GROUP_POST" => $arParams["PATH_TO_GROUP_MICROBLOG_POST"],
			"PATH_TO_POST_EDIT" => $arParams["PATH_TO_USER_BLOG_POST_EDIT"],
			"PATH_TO_SMILE" => $arParams["PATH_TO_BLOG_SMILE"],
			"SET_TITLE" => "N",
			"GROUP_ID" => $arParams["BLOG_GROUP_ID"],
			"USER_ID" => $arResult["currentUserId"],
			"SET_NAV_CHAIN" => "N",
			"USE_SOCNET" => "Y",
			"MICROBLOG" => "Y",
			"USE_CUT" => $arParams["BLOG_USE_CUT"],
			"NAME_TEMPLATE" => $arParams["NAME_TEMPLATE"],
			"CHECK_PERMISSIONS_DEST" => $arParams["CHECK_PERMISSIONS_DEST"],
			"TOP_TABS_VISIBLE" => (array_key_exists("TOP_TABS_VISIBLE", $arParams) ? $arParams["TOP_TABS_VISIBLE"] : "Y"),
			"SHOW_BLOG_FORM_TARGET" => $arResult["FORM_TARGET_ID"] === false
		);

		if ($arParams["ENTITY_TYPE"] == SONET_ENTITY_GROUP)
		{
			$arBlogComponentParams["SOCNET_GROUP_ID"] = $arParams["GROUP_ID"];
		}
		elseif (
			$arParams["ENTITY_TYPE"] != SONET_ENTITY_GROUP
			&& $arResult["currentUserId"] != $arParams["CURRENT_USER_ID"]
		)
		{
			$arBlogComponentParams["SOCNET_USER_ID"] = $arParams["CURRENT_USER_ID"];
		}

		if (isset($arParams["DISPLAY"]))
			$arBlogComponentParams["DISPLAY"] = $arParams["DISPLAY"];

		if (defined("BITRIX24_INDEX_COMPOSITE"))
		{
			$arBlogComponentParams["POST_FORM_ACTION_URI"] = "/stream/";
		}

		if (IsModuleInstalled('tasks'))
		{
			$arBlogComponentParams["PATH_TO_USER_TASKS"] = $arParams['PATH_TO_USER_TASKS'];
			$arBlogComponentParams["PATH_TO_USER_TASKS_TASK"] = $arParams['PATH_TO_USER_TASKS_TASK'];
			$arBlogComponentParams["PATH_TO_GROUP_TASKS"] = $arParams['PATH_TO_GROUP_TASKS'];
			$arBlogComponentParams["PATH_TO_GROUP_TASKS_TASK"] = $arParams['PATH_TO_GROUP_TASKS_TASK'];
			$arBlogComponentParams["PATH_TO_USER_TASKS_PROJECTS_OVERVIEW"] = $arParams['PATH_TO_USER_TASKS_PROJECTS_OVERVIEW'];
			$arBlogComponentParams["PATH_TO_USER_TEMPLATES_TEMPLATE"] = $arParams['PATH_TO_USER_TEMPLATES_TEMPLATE'];
			$arBlogComponentParams["LOG_EXPERT_MODE"] = (isset($arResult["EXPERT_MODE"]) ? $arResult["EXPERT_MODE"] : "N");
		}

		$staticHtmlCache = \Bitrix\Main\Data\StaticHtmlCache::getInstance();
		$staticHtmlCache->disableVoting();

		if ($arResult["FORM_TARGET_ID"])
		{
			$this->SetViewTarget($arResult["FORM_TARGET_ID"]);
		}

		?><div id="sonet_log_microblog_container"><?
		$APPLICATION->IncludeComponent(
			"bitrix:socialnetwork.blog.post.edit",
			"",
			$arBlogComponentParams,
			$component,
			array("HIDE_ICONS" => "Y")
		);
		?></div><?

		if ($arResult["FORM_TARGET_ID"])
		{
			$this->EndViewTarget();
		}

		$staticHtmlCache->enableVoting();
	}
	elseif ($arParams["SHOW_EVENT_ID_FILTER"] == "Y")
	{
		?><div class="feed-filter-fake-cont"></div><?
	}

	if ($arParams["SHOW_EVENT_ID_FILTER"] == "Y")
	{
		if ($arParams["IS_CRM"] == "Y")
		{
			$liveFeedFilter = new CCrmLiveFeedFilter(
				array(
					'GridFormID' => '',
					'EntityTypeID' => false
				)
			);
			AddEventHandler('socialnetwork', 'OnBeforeSonetLogFilterFill', array($liveFeedFilter, 'OnBeforeSonetLogFilterFill'));
		}

		$APPLICATION->IncludeComponent(
			"bitrix:socialnetwork.log.filter",
			(isset($arParams["FILTER_TEMPLATE"]) ? $arParams["FILTER_TEMPLATE"] : ".default"),
			array(
				"arParams" => array_merge(
					$arParams,
					array(
						"TOP_OUT" => "Y",
						"USE_TARGET" => (!isset($arParams["USE_FILTER_TARGET"]) || $arParams["USE_FILTER_TARGET"] != "N" ? "Y" : "N"),
						"TARGET_ID" => (
							isset($_REQUEST["SONET_FILTER_MODE"])
							&& $_REQUEST["SONET_FILTER_MODE"] == "AJAX"
								? ""
								: "sonet_blog_form"
						),
						"USE_SONET_GROUPS" => (!isset($arParams["IS_CRM"]) || $arParams["IS_CRM"] != "Y" ? "Y" : "N"),
						"SHOW_FOLLOW" => (isset($arParams["SHOW_FOLLOW_FILTER"]) && $arParams["SHOW_FOLLOW_FILTER"] == "N" ? "N" : "Y"),
						"SHOW_EXPERT_MODE" => (isset($arParams["SHOW_EXPERT_MODE"]) && $arParams["SHOW_EXPERT_MODE"] == "N" ? "N" : "Y"),
						"EXPERT_MODE" => (isset($arResult["EXPERT_MODE"]) ? $arResult["EXPERT_MODE"] : "N"),
						"SET_EXPERT_MODE" => (isset($arResult["EXPERT_MODE_SET"]) && $arResult["EXPERT_MODE_SET"] === true ? "Y" : "N"),
						"USE_SMART_FILTER" => (isset($arResult["USE_SMART_FILTER"]) && $arResult["USE_SMART_FILTER"] == "Y" ? "Y" : "N"),
						"MY_GROUPS_ONLY" => (isset($arResult["MY_GROUPS_ONLY"]) && $arResult["MY_GROUPS_ONLY"] == "Y" ? "Y" : "N"),
						"FILTER_ID" => $arResult["FILTER_ID"]
					)
				),
				"arResult" => $arResult
			),
			null,
			array("HIDE_ICONS" => "Y")
		);

		if (isset($_REQUEST["SONET_FILTER_MODE"]) && $_REQUEST["SONET_FILTER_MODE"] == "AJAX")
		{
			return;
		}
	}

	if (defined("BITRIX24_INDEX_COMPOSITE"))
	{
		$dynamicArea = new \Bitrix\Main\Page\FrameStatic("live-feed");
		$dynamicArea->startDynamicArea();
		$dynamicArea->setStub($stub);
	}

	if ($arParams["PUBLIC_MODE"] != "Y")
	{
		if ($arResult["INFORMER_TARGET_ID"])
		{
			$this->SetViewTarget($arResult["INFORMER_TARGET_ID"]);
		}

		?><div class="feed-new-message-informer-place<?=($arParams["LOG_ID"] > 0 ? " feed-new-message-informer-place-hidden" : "")?>"><?
		if ($arParams["SHOW_REFRESH"] != "N")
		{
			?><div class="feed-new-message-inform-wrap new-message-balloon-wrap" id="sonet_log_counter_2_wrap" style="visibility: hidden;"><?
				?><div onclick="oLF.refresh()" id="sonet_log_counter_2_container" class="feed-new-message-informer"><?
					?><span class="feed-new-message-inf-text new-message-balloon"><?
						?><span class="feed-new-message-icon new-message-balloon-icon"></span><?
						?><span class="new-message-balloon-text"><?=GetMessage("SONET_C30_COUNTER_TEXT_1")?></span><?
						?><span class="feed-new-message-informer-counter new-message-balloon-counter" id="sonet_log_counter_2">0</span><?
					?></span><?
					?><span class="feed-new-message-inf-text feed-new-message-inf-text-reload new-message-balloon" style="display: none;"><?
						?><?=GetMessage("SONET_C30_T_RELOAD_NEEDED")?><?
					?></span><?
				?></div><?
			?></div><?
		}
		else
		{
			?><div class="feed-new-message-inform-wrap"  id="sonet_log_counter_2_wrap" style="visibility: hidden;"></div><?
		}
		?></div><?

		if ($arResult["INFORMER_TARGET_ID"])
		{
			$this->EndViewTarget();
		}
	}

	?><div id="log_internal_container"><?
		?><div class="feed-loader-container" id="feed-loader-container"><?
			?><svg class="feed-loader-circular" viewBox="25 25 50 50"><?
				?><circle class="feed-loader-path" cx="50" cy="50" r="20" fill="none" stroke-miterlimit="10"/><?
			?></svg><?
		?></div><?
}
elseif (
	!$arResult["Events"]
	|| !is_array($arResult["Events"])
	|| count($arResult["Events"]) <= 0
)
{
	ob_end_clean();
	$APPLICATION->RestartBuffer();

	if ($arResult["bReload"])
	{
		echo CUtil::PhpToJSObject(array(
			"PROPS" => array(
				"EMPTY" => "Y"
			),
		));
	}

	if(CModule::IncludeModule("compression"))
		CCompress::DisableCompression();
	CMain::FinalActions();
	die();
}
else // AJAX_CALL
{
	ob_end_clean();
	$APPLICATION->RestartBuffer();
}

if (!$arResult["AJAX_CALL"])
{
	?><div class="feed-wrap"><?
}

?><script>
	<?
	if (!$arResult["AJAX_CALL"])
	{
		?>
		BX.message({
			sonetLGetPath: '<?=CUtil::JSEscape('/bitrix/components/bitrix/socialnetwork.log.ex/ajax.php')?>',
			sonetLSetPath: '<?=CUtil::JSEscape('/bitrix/components/bitrix/socialnetwork.log.ex/ajax.php')?>',
			sonetLESetPath: '<?=CUtil::JSEscape('/bitrix/components/bitrix/socialnetwork.log.entry/ajax.php')?>',
			sonetLEPath: '<?=CUtil::JSEscape($arParams["PATH_TO_LOG_ENTRY"])?>',
			sonetLSessid: '<?=bitrix_sessid_get()?>',
			sonetLLangId: '<?=CUtil::JSEscape(LANGUAGE_ID)?>',
			sonetLSiteId: '<?=CUtil::JSEscape(SITE_ID)?>',
			sonetLSiteTemplateId: '<?=CUtil::JSEscape(SITE_TEMPLATE_ID)?>',
			sonetLNoSubscriptions: '<?=GetMessageJS("SONET_C30_NO_SUBSCRIPTIONS")?>',
			sonetLInherited: '<?=GetMessageJS("SONET_C30_INHERITED")?>',
			sonetLDialogClose: '<?=GetMessageJS("SONET_C30_DIALOG_CLOSE_BUTTON")?>',
			sonetLDialogSubmit: '<?=GetMessageJS("SONET_C30_DIALOG_SUBMIT_BUTTON")?>',
			sonetLDialogCancel: '<?=GetMessageJS("SONET_C30_DIALOG_CANCEL_BUTTON")?>',
			sonetLbUseFavorites: '<?=(!isset($arParams["USE_FAVORITES"]) || $arParams["USE_FAVORITES"] != "N" ? "Y" : "N")?>',
			sonetLMenuFavoritesTitleY: '<?=GetMessageJS("SONET_C30_MENU_TITLE_FAVORITES_Y")?>',
			sonetLMenuFavoritesTitleN: '<?=GetMessageJS("SONET_C30_MENU_TITLE_FAVORITES_N")?>',
			sonetLMenuLink: '<?=GetMessageJS("SONET_C30_MENU_TITLE_LINK2")?>',
			sonetLMenuHref: '<?=GetMessageJS("SONET_C30_MENU_TITLE_HREF")?>',
			sonetLMenuDelete: '<?=GetMessageJS("SONET_C30_MENU_TITLE_DELETE")?>',
			sonetLMenuDeleteConfirm: '<?=GetMessageJS("SONET_C30_MENU_TITLE_DELETE_CONFIRM")?>',
			sonetLMenuDeleteSuccess: '<?=GetMessageJS("SONET_C30_MENU_TITLE_DELETE_SUCCESS")?>',
			sonetLMenuDeleteFailure: '<?=GetMessageJS("SONET_C30_MENU_TITLE_DELETE_FAILURE")?>',
			sonetLCounterType: '<?=CUtil::JSEscape($arResult["COUNTER_TYPE"])?>',
			sonetLIsB24: '<?=(SITE_TEMPLATE_ID == "bitrix24" ? "Y" : "N")?>',
			sonetRatingType : '<?=CUtil::JSEscape($arParams["RATING_TYPE"])?>',
			sonetLErrorSessid : '<?=GetMessageJS("SONET_ERROR_SESSION")?>',
			sonetLIsCRM : '<?=CUtil::JSEscape($arParams["IS_CRM"])?>',
			sonetLCanDelete : '<?=($arResult["CAN_DELETE"] ? 'Y' : 'N')?>',
			sonetLForumID : <?=intval($arParams["FORUM_ID"])?>,
			sonetLFCreateTaskWait: '<?=GetMessageJS("SONET_C30_T_CREATE_TASK_WAIT")?>',
			sonetLFCreateTaskButtonTitle: '<?=GetMessageJS("SONET_C30_T_CREATE_TASK_BUTTON_TITLE")?>',
			sonetLFCreateTaskSuccessTitle: '<?=GetMessageJS("SONET_C30_T_CREATE_TASK_SUCCESS_TITLE")?>',
			sonetLFCreateTaskFailureTitle: '<?=GetMessageJS("SONET_C30_T_CREATE_TASK_FAILURE_TITLE")?>',
			sonetLFCreateTaskSuccessDescription: '<?=GetMessageJS("SONET_C30_T_CREATE_TASK_SUCCESS_DESCRIPTION")?>',
			sonetLFCreateTaskErrorGetData: '<?=GetMessageJS("SONET_C30_T_CREATE_TASK_ERROR_GET_DATA")?>',
			sonetLFCreateTaskTaskPath: '<?=CUtil::JSEscape(\Bitrix\Main\Config\Option::get('socialnetwork', 'user_page', SITE_DIR.'company/personal/'))?>user/#user_id#/tasks/task/view/#task_id#/',
			sonetLFCreateTaskEntityLink: '<?=GetMessageJS("SONET_C30_T_CREATE_TASK_LINK")?>',
			sonetLFCreateTaskEntityLinkBLOG_POST: '<?=GetMessageJS("SONET_C30_T_CREATE_TASK_LINK_BLOG_POST")?>',
			sonetLFCreateTaskEntityLinkBLOG_COMMENT: '<?=GetMessageJS("SONET_C30_T_CREATE_TASK_LINK_BLOG_COMMENT")?>',
			SONET_C30_T_LINK_COPIED: '<?=GetMessageJS("SONET_C30_T_LINK_COPIED")?>',
			SONET_C30_T_EMPTY: '<?=GetMessageJS("SONET_C30_T_EMPTY")?>',
			SONET_C30_T_EMPTY_SEARCH: '<?=GetMessageJS("SONET_C30_T_EMPTY_SEARCH")?>'

		});
		<?
	}

	if (!$arResult["AJAX_CALL"])
	{
		?>
		BX.ready(function(){
			oLF.initOnce({
				crmEntityTypeName: '<?=(!empty($arResult['CRM_ENTITY_TYPE_NAME']) ? CUtil::JSEscape($arResult['CRM_ENTITY_TYPE_NAME']) : '')?>',
				crmEntityId: <?=(!empty($arResult['CRM_ENTITY_ID']) ? intval($arResult['CRM_ENTITY_ID']) : 0)?>,
				filterId: '<?=(!empty($arResult['FILTER_ID']) ? CUtil::JSEscape($arResult["FILTER_ID"]) : '')?>'
			});
		});
		<?
	}

	if (
		!$arResult["AJAX_CALL"]
		|| $arResult["bReload"]
	)
	{
		?>
		BX.ready(function(){
			oLF.init({
				firstPageLastTS : <?=intval($arResult["dateLastPageTS"])?>,
				firstPageLastId : <?=intval($arResult["dateLastPageId"])?>,
				refreshUrl: '<?=$APPLICATION->GetCurPageParam("logajax=Y&RELOAD=Y", array(
					"flt_created_by_id",
					"flt_group_id",
					"flt_to_user_id",
					"flt_date_datesel",
					"flt_date_days",
					"flt_date_from",
					"flt_date_to",
					"flt_date_to",
					"preset_filter_id",
					"sessid",
					"bxajaxid",
					"logajax",
					"RELOAD",
					"useBXMainFilter"
				), false)?>'
			});
		});
		<?
	}

	if (
		$arResult["AJAX_CALL"]
		&& $arParams["SHOW_RATING"] == "Y"
	)
	{
		$likeTemplate = (
			\Bitrix\Main\ModuleManager::isModuleInstalled('intranet')
				? 'like_react'
				: 'like'
		);

		if ($arParams["RATING_TYPE"] == "like")
		{
			?>
			BX.loadCSS('/bitrix/components/bitrix/rating.vote/templates/<?=$likeTemplate?>/popup.css');
			<?
		}
		?>
		BX.loadCSS('/bitrix/components/bitrix/rating.vote/templates/<?=($arParams["RATING_TYPE"] == "like" ? $likeTemplate : $arParams["RATING_TYPE"])?>/style.css');
		<?
	}

	if ($arResult["bReload"])
	{
		?>
		if (typeof __logOnReload === 'function')
		{
			BX.ready(function(){
				window.bRefreshed = true;
				__logOnReload(<?=intval($arResult["LOG_COUNTER"])?>);
			});
		}
		<?
	}
	elseif ($arParams["IS_CRM"] == "Y" && !$arResult["AJAX_CALL"])
	{
		?>
		if (typeof __logOnReload === 'function')
		{
			BX.ready(function(){
				__logOnReload(<?=intval($arResult["LOG_COUNTER"])?>);
			});
		}
		<?
	}

	if (!$arResult["AJAX_CALL"] || $arResult["bReload"])
	{
		?>
		BX.ready(function(){
			<?
			if ($arParams["SET_LOG_COUNTER"] != "N")
			{
				?>
				BX.onCustomEvent(window, 'onSonetLogCounterClear', [BX.message('sonetLCounterType')]);
				<?
				if (!$arResult["AJAX_CALL"])
				{
					?>
					BX.addCustomEvent("onGoUp", function() {
						var counter_wrap = BX('sonet_log_counter_2_wrap');
						if (counter_wrap)
						{
							BX.removeClass(counter_wrap, 'feed-new-message-informer-fixed');
							BX.removeClass(counter_wrap, 'feed-new-message-informer-fix-anim');
						}
					});

					BX.addCustomEvent("onPullEvent-main", BX.delegate(function(command,params){
						if (
							command == 'user_counter'
							&& params[BX.message('SITE_ID')]
							&& params[BX.message('SITE_ID')][BX.message('sonetLCounterType')]
						)
						{
							__logChangeCounter(BX.clone(params[BX.message('SITE_ID')][BX.message('sonetLCounterType')]));
						}
					}, this));

					BX.addCustomEvent(window, "onImUpdateCounter", BX.proxy(function(arCount) {
						__logChangeCounterArray(arCount);
					}, this));

					BX.addCustomEvent("onCounterDecrement", function(iDecrement) {
						__logDecrementCounter(iDecrement);
					});
					<?
				}
			}

			if (!$arResult["AJAX_CALL"])
			{
				?>
				BX.addCustomEvent('onAjaxFailure', function(status){
					if (status == 'auth')
					{
						top.location = top.location.href;
					}
				});
				<?
			}
			?>
		});

		<?
		if (!$arResult["AJAX_CALL"])
		{
			if(\Bitrix\Main\Page\Frame::isAjaxRequest())
			{
				?>setTimeout(function() {
					oLF.recalcMoreButton();
					oLF.registerViewAreaList();
				}, 1000);<?
			}
			else
			{
				?>BX.bind(window, 'load', function() {
					oLF.recalcMoreButton();
					oLF.registerViewAreaList();
				});<?
			}
		}
	}

	if (!$arResult["AJAX_CALL"] && !$arResult["bReload"])
	{
		?>
		BX.ready(function() {
			window.addEventListener("scroll", BX.throttle(function() {
				BX.LazyLoad.onScroll();
			}, 80));
		});
		<?
	}
	?>

	BX.ready(function()
	{
		oLF.arMoreButtonID = [];

		BX.addCustomEvent(window, "onAjaxInsertToNode", function() { BX.ajax.Setup({denyShowWait: true}, true); });
		BX.bind(BX('sonet_log_counter_2_container'), 'click', oLF.clearContainerExternalNew);
		BX.bind(BX('sonet_log_counter_2_container'), 'click', __logOnAjaxInsertToNode);

		if (BX('sonet_log_more_container'))
		{
			BX.bind(BX('sonet_log_more_container'), 'click', oLF.clearContainerExternalMore);
			BX.bind(BX('sonet_log_more_container'), 'click', __logOnAjaxInsertToNode);
		}

		if (BX('sonet_log_comment_text'))
		{
			BX('sonet_log_comment_text').onkeydown = BX.eventCancelBubble;
		}

		setTimeout(function() {
			BX.LazyLoad.showImages(true);
		}, 0);
	});

	BX.addCustomEvent("onFrameDataProcessed", function() {
		BX.LazyLoad.showImages(true);
	});

</script><?

if(strlen($arResult["ErrorMessage"]) > 0)
{
	?><span class='errortext'><?=$arResult["ErrorMessage"]?></span><br /><br /><?
}

if ($arResult["AJAX_CALL"])
{
	$APPLICATION->sPath2css = array();
	$APPLICATION->arHeadScripts = array();
}

$bNextPage = (
	isset($_REQUEST['logajax'])
	&& (
		!isset($_REQUEST['RELOAD'])
		|| $_REQUEST['RELOAD'] != 'Y'
	)
);

$hasBlogEvent = (!$bNextPage || $_REQUEST['noblog'] == 'Y' ? false : true);
if (
	is_array($arResult["Events"])
	&& !empty($arResult["Events"])
)
{
	$blogPostLivefeedProvider = new \Bitrix\Socialnetwork\Livefeed\BlogPost;
	$blogPostEventIdList = $blogPostLivefeedProvider->getEventId();

	foreach ($arResult["Events"] as $arEvent)
	{
		if (!empty($arEvent))
		{
			$event_cnt++;
			$ind = RandString(8);
			$event_date_log_ts = (isset($arEvent["LOG_DATE_TS"]) ? $arEvent["LOG_DATE_TS"] : (MakeTimeStamp($arEvent["LOG_DATE"]) - intval($arResult["TZ_OFFSET"])));

			$is_unread = (
				$arParams["SHOW_UNREAD"] == "Y"
				&& ($arResult["COUNTER_TYPE"] == "**" || $arResult["COUNTER_TYPE"] == "CRM_**" || $arResult["COUNTER_TYPE"] == "blog_post")
				&& $arEvent["USER_ID"] != $arResult["currentUserId"]
				&& intval($arResult["LAST_LOG_TS"]) > 0
				&& $event_date_log_ts > $arResult["LAST_LOG_TS"]
			);

			if(in_array($arEvent["EVENT_ID"], array_merge($blogPostEventIdList, array("blog_comment", "blog_comment_micro"))))
			{
				if (intval($arEvent["SOURCE_ID"]) <= 0)
				{
					continue;
				}

				$hasBlogEvent = true;

				$arAditMenu = array();

				$arComponentParams = Array(
					"PATH_TO_BLOG" => $arParams["PATH_TO_USER_BLOG"],
					"PATH_TO_POST" => $arParams["PATH_TO_USER_MICROBLOG_POST"],
					"PATH_TO_POST_IMPORTANT" => $arParams["PATH_TO_USER_BLOG_POST_IMPORTANT"],
					"PATH_TO_BLOG_CATEGORY" => $arParams["PATH_TO_USER_BLOG_CATEGORY"],
					"PATH_TO_POST_EDIT" => $arParams["PATH_TO_USER_BLOG_POST_EDIT"],
					"PATH_TO_GROUP_BLOG" => $arParams["PATH_TO_GROUP_MICROBLOG"],
					"PATH_TO_SEARCH_TAG" => $arParams["PATH_TO_SEARCH_TAG"],
					"PATH_TO_LOG_TAG" => $arResult["PATH_TO_LOG_TAG"],
					"PATH_TO_USER" => $arParams["PATH_TO_USER"],
					"PATH_TO_GROUP" => $arParams["PATH_TO_GROUP"],
					"PATH_TO_SMILE" => $arParams["PATH_TO_BLOG_SMILE"],
					"PATH_TO_MESSAGES_CHAT" => $arParams["PATH_TO_MESSAGES_CHAT"],
					"SET_NAV_CHAIN" => "N",
					"SET_TITLE" => "N",
					"POST_PROPERTY" => $arParams["POST_PROPERTY"],
					"DATE_TIME_FORMAT" => $arParams["DATE_TIME_FORMAT"],
					"DATE_TIME_FORMAT_WITHOUT_YEAR" => $arParams["DATE_TIME_FORMAT_WITHOUT_YEAR"],
					"TIME_FORMAT" => $arParams["TIME_FORMAT"],
					"CREATED_BY_ID" => (
						array_key_exists("log_filter_submit", $_REQUEST)
						&& array_key_exists("flt_comments", $_REQUEST)
						&& $_REQUEST["flt_comments"] == "Y"
							? $arParams["CREATED_BY_ID"]
							: false
					),
					"USER_ID" => $arEvent["USER_ID"],
					"ENTITY_TYPE" => SONET_ENTITY_USER,
					"ENTITY_ID" => $arEvent["ENTITY_ID"],
					"EVENT_ID" => $arEvent["EVENT_ID"],
					"EVENT_ID_FULLSET" => $arEvent["EVENT_ID_FULLSET"],
					"IND" => $ind,
					"GROUP_ID" => $arParams["BLOG_GROUP_ID"],
					"SONET_GROUP_ID" => $arParams["GROUP_ID"],
					"NAME_TEMPLATE" => $arParams["NAME_TEMPLATE"],
					"SHOW_LOGIN" => $arParams["SHOW_LOGIN"],
					"SHOW_YEAR" => $arParams["SHOW_YEAR"],
					"PATH_TO_CONPANY_DEPARTMENT" => $arParams["PATH_TO_CONPANY_DEPARTMENT"],
					"PATH_TO_VIDEO_CALL" => $arParams["PATH_TO_VIDEO_CALL"],
					"USE_SHARE" => $arParams["USE_SHARE"],
					"SHARE_HIDE" => $arParams["SHARE_HIDE"],
					"SHARE_TEMPLATE" => $arParams["SHARE_TEMPLATE"],
					"SHARE_HANDLERS" => $arParams["SHARE_HANDLERS"],
					"SHARE_SHORTEN_URL_LOGIN" => $arParams["SHARE_SHORTEN_URL_LOGIN"],
					"SHARE_SHORTEN_URL_KEY" => $arParams["SHARE_SHORTEN_URL_KEY"],
					"SHOW_RATING" => $arParams["SHOW_RATING"],
					"RATING_TYPE" => $arParams["RATING_TYPE"],
					"IMAGE_MAX_WIDTH" => $arParams["BLOG_IMAGE_MAX_WIDTH"],
					"IMAGE_MAX_HEIGHT" => $arParams["BLOG_IMAGE_MAX_HEIGHT"],
					"ALLOW_POST_CODE" => $arParams["ALLOW_POST_CODE"],
					"ID" => $arEvent["SOURCE_ID"],
					"LOG_ID" => $arEvent["ID"],
					"FROM_LOG" => "Y",
					"ADIT_MENU" => $arAditMenu,
					"IS_UNREAD" => $is_unread,
					"MARK_NEW_COMMENTS" => (
						$USER->isAuthorized()
						&& $arResult["COUNTER_TYPE"] == "**"
						&& $arParams["SHOW_UNREAD"] == "Y"
					)
						? "Y"
						: "N",
					"IS_HIDDEN" => false,
					"LAST_LOG_TS" => ($arResult["LAST_LOG_TS"] + $arResult["TZ_OFFSET"]),
					"CACHE_TIME" => $arParams["CACHE_TIME"],
					"CACHE_TYPE" => $arParams["CACHE_TYPE"],
					"ALLOW_VIDEO" => $arParams["BLOG_COMMENT_ALLOW_VIDEO"],
					"ALLOW_IMAGE_UPLOAD" => $arParams["BLOG_COMMENT_ALLOW_IMAGE_UPLOAD"],
					"USE_CUT" => $arParams["BLOG_USE_CUT"],
					"AVATAR_SIZE_COMMON" => $arParams["AVATAR_SIZE_COMMON"],
					"AVATAR_SIZE" => $arParams["AVATAR_SIZE"],
					"AVATAR_SIZE_COMMENT" => $arParams["AVATAR_SIZE_COMMENT"],
					"LAZYLOAD" => "Y",
					"CHECK_COMMENTS_PERMS" => (isset($arParams["CHECK_COMMENTS_PERMS"]) && $arParams["CHECK_COMMENTS_PERMS"] == "Y" ? "Y" : "N"),
					"GROUP_READ_ONLY" => (isset($arResult["Group"]) && isset($arResult["Group"]["READ_ONLY"]) && $arResult["Group"]["READ_ONLY"] == "Y" ? "Y" : "N"),
					"BLOG_NO_URL_IN_COMMENTS" => $arParams["BLOG_NO_URL_IN_COMMENTS"],
					"BLOG_NO_URL_IN_COMMENTS_AUTHORITY" => $arParams["BLOG_NO_URL_IN_COMMENTS_AUTHORITY"],
					'TOP_RATING_DATA' => (!empty($arResult['TOP_RATING_DATA'][$arEvent["ID"]]) ? $arResult['TOP_RATING_DATA'][$arEvent["ID"]] : false)
				);

				if ($arParams["USE_FOLLOW"] == "Y")
				{
					$arComponentParams["FOLLOW"] = $arEvent["FOLLOW"];
				}

				if ($arResult["CURRENT_PAGE_DATE"])
				{
					$arComponentParams["CURRENT_PAGE_DATE"] = $arResult["CURRENT_PAGE_DATE"];
				}

				if (
					(
						!isset($arParams["USE_FAVORITES"])
						|| $arParams["USE_FAVORITES"] != "N"
					)
					&& $USER->isAuthorized()
				)
				{
					$arComponentParams["FAVORITES_USER_ID"] = (array_key_exists("FAVORITES_USER_ID", $arEvent) && intval($arEvent["FAVORITES_USER_ID"]) > 0 ? intval($arEvent["FAVORITES_USER_ID"]) : 0);
				}

				if (!empty($arEvent['CONTENT_ID']))
				{
					$arComponentParams['CONTENT_ID'] = $arEvent['CONTENT_ID'];
					$arComponentParams['CONTENT_VIEW_CNT'] = (
						!empty($arResult["ContentViewData"][$arEvent['CONTENT_ID']])
							? $arResult["ContentViewData"][$arEvent['CONTENT_ID']]['CNT']
							: 0
					);
				}

				$APPLICATION->IncludeComponent(
					"bitrix:socialnetwork.blog.post",
					"",
					$arComponentParams,
					$component
				);
			}
			else
			{
				$arComponentParams = array_merge($arParams, array(
					"COMMENT_ID" => intval($_REQUEST["commentId"]),
					"LOG_ID" => $arEvent["ID"],
					"LAST_LOG_TS" => ($arParams["SET_LOG_COUNTER"] == "Y" ? $arResult["LAST_LOG_TS"] : 0),
					"COUNTER_TYPE" => $arResult["COUNTER_TYPE"],
					"AJAX_CALL" => $arResult["AJAX_CALL"],
					"bReload" => $arResult["bReload"],
					"bGetComments" => $arResult["bGetComments"],
					"IND" => $ind,
					"CURRENT_PAGE_DATE" => $arResult["CURRENT_PAGE_DATE"],
					"EVENT" => array(
						"IS_UNREAD" => $is_unread
					),
					"LAZYLOAD" => "Y",
					"FROM_LOG" => (isset($arParams["LOG_ID"]) && intval($arParams["LOG_ID"]) > 0 ? "N" : "Y"),
					"PATH_TO_LOG_TAG" => $arResult["PATH_TO_LOG_TAG"],
					'TOP_RATING_DATA' => (!empty($arResult['TOP_RATING_DATA'][$arEvent["ID"]]) ? $arResult['TOP_RATING_DATA'][$arEvent["ID"]] : false)
				));

				if ($USER->isAuthorized())
				{
					if ($arParams["USE_FOLLOW"] == "Y")
					{
						$arComponentParams["EVENT"]["FOLLOW"] = $arEvent["FOLLOW"];
						$arComponentParams["EVENT"]["DATE_FOLLOW"] = $arEvent["DATE_FOLLOW"];
					}

					if (
						!isset($arParams["USE_FAVORITES"])
						|| $arParams["USE_FAVORITES"] != "N"
					)
					{
						$arComponentParams["EVENT"]["FAVORITES"] = (
						array_key_exists("FAVORITES_USER_ID", $arEvent)
						&& intval($arEvent["FAVORITES_USER_ID"]) > 0
							? "Y"
							: "N"
						);
					}
				}

				if ($arResult["CURRENT_PAGE_DATE"])
				{
					$arComponentParams["CURRENT_PAGE_DATE"] = $arResult["CURRENT_PAGE_DATE"];
				}

				if (!empty($arEvent['CONTENT_ID']))
				{
					$arComponentParams['CONTENT_ID'] = $arEvent['CONTENT_ID'];

					if (!empty($arResult["ContentViewData"][$arEvent['CONTENT_ID']]))
					{
						$arComponentParams['CONTENT_VIEW_CNT'] = $arResult["ContentViewData"][$arEvent['CONTENT_ID']]['CNT'];
					}
				}

				$APPLICATION->IncludeComponent(
					"bitrix:socialnetwork.log.entry",
					"",
					$arComponentParams,
					$component
				);
			}
		}
	}
}

if (
	$arResult["IS_FILTERED"]
	&& (
		empty($arParams["GROUP_ID"])
		|| intval($arParams["GROUP_ID"]) <= 0
	)
)
{
	$emptyMessage = Loc::getMessage('SONET_C30_T_EMPTY_SEARCH');
}
else
{
	$emptyMessage = Loc::getMessage('SONET_C30_T_EMPTY');
}

?><div class="feed-wrap-empty-wrap" id="feed-empty-wrap" style="display: <?=(!is_array($arResult["Events"]) || empty($arResult["Events"]) ? 'block' : 'none')?>"><?
	?><div class="feed-wrap-empty"><?=$emptyMessage?></div><?
?></div><?

if ($arParams["SHOW_NAV_STRING"] != "N" && is_array($arResult["Events"]))
{
	$strParams = "logajax=Y&PAGEN_".$arResult["PAGE_NAVNUM"]."=".($arResult["PAGE_NUMBER"] + 1);
	if (
		!$arResult["AJAX_CALL"]
		|| $arResult["bReload"]
	)
	{
		$strParams .= "&ts=".$arResult["LAST_LOG_TS"];
	}

	if (
		is_array($arResult["arLogTmpID"])
		&& count($arResult["arLogTmpID"]) > 0
	)
	{
		$strParams .= "&pplogid=".implode("|", $arResult["arLogTmpID"]);
	}

	if (intval($arResult["NEXT_PAGE_SIZE"]) > 0)
	{
		$strParams .= "&pagesize=".intval($arResult["NEXT_PAGE_SIZE"]);
	}

	if (!$hasBlogEvent)
	{
		$strParams .= "&noblog=Y";
	}

	?>
	<script>
		BX.ready(function() {
			oLF.nextURL = '<?=CUtil::JSEscape($APPLICATION->GetCurPageParam($strParams, array("PAGEN_".$arResult["PAGE_NAVNUM"], "RELOAD", "logajax", "pplogid", "pagesize")))?>';
			<?
			if (
				$arResult["PAGE_NUMBER"] == 1
				&& (!$arResult["AJAX_CALL"] || $arResult["bReload"])
			)
			{
				?>
				oLF.initScroll();
				<?
			}
			?>
		});
	</script><?

	if (!$arResult["AJAX_CALL"] || $arResult["bReload"])
	{
		?><div class="feed-new-message-inf-wrap-first feed-new-message-active" id="feed-new-message-inf-wrap-first" style="display: none;"><?
			?><a href="javascript:void(0);" id="sonet_log_more_container_first" class="feed-new-message-inf-bottom"><?
				?><span class="feed-new-message-inf-text"><?
					?><?=GetMessage("SONET_C30_MORE")?><?
					?><span class="feed-new-message-icon"></span><?
				?></span><?
			?></a><?
		?></div><?
	}
	?><div class="feed-new-message-inf-wrap feed-new-message-active" id="feed-new-message-inf-wrap" style="display: none;"><?=$stub?></div><?
}

if (!$arResult["AJAX_CALL"])
{
		?></div><? // feed-wrap
	?></div><? // log_internal_container
}
else
{
	$arCSSListNew = $APPLICATION->sPath2css;
	$arCSSNew = array();

	foreach ($arCSSListNew as $i => $css_path)
	{
		if(
			strtolower(substr($css_path, 0, 7)) != 'http://'
			&& strtolower(substr($css_path, 0, 8)) != 'https://'
		)
		{
			$css_file = (
			($p = strpos($css_path, "?")) > 0
				? substr($css_path, 0, $p)
				: $css_path
			);

			if(file_exists($_SERVER["DOCUMENT_ROOT"].$css_file))
			{
				$arCSSNew[] = $css_path;
			}
		}
		else
		{
			$arCSSNew[] = $css_path;
		}
	}

	$arCSSNew = array_unique($arCSSNew);

	$arHeadScriptsNew = $APPLICATION->arHeadScripts;

	if(!$APPLICATION->oAsset->optimizeJs())
	{
		$arHeadScriptsNew = array_merge(CJSCore::GetScriptsList(), $arHeadScriptsNew);
	}

	$arAdditionalData["CSS"] = array();
	foreach($arCSSNew as $style)
	{
		$arAdditionalData["CSS"][] = CUtil::GetAdditionalFileURL($style);
	}

	$arAdditionalData['SCRIPTS'] = array();
	$arHeadScriptsNew = array_unique($arHeadScriptsNew);

	foreach($arHeadScriptsNew as $script)
	{
		$arAdditionalData["SCRIPTS"][] = CUtil::GetAdditionalFileURL($script);
	}

	$additional_data = '<script type="text/javascript" bxrunfirst="true">'."\n";
	$additional_data .= 'top.__logGetNextPageLinkEntities('.
		CUtil::PhpToJSObject($component->arResult["ENTITIES_XML_ID"]).', '.
		CUtil::PhpToJSObject($component->arResult["ENTITIES_CORRESPONDENCE"]).');';
	$additional_data .= '</script>';

	if ($arResult["AJAX_CALL"])
	{
		$strText = ob_get_clean();
		echo CUtil::PhpToJSObject(array(
			"PROPS" => array(
				"CONTENT" => $additional_data.$strText,
				"STRINGS" => array(),
				"JS" => $arAdditionalData["SCRIPTS"],
				"CSS" => $arAdditionalData["CSS"]
			),
			"LAST_TS" => ($arResult["dateLastPageTS"] ? intval($arResult["dateLastPageTS"]) : 0),
			"LAST_ID" => ($arResult["dateLastPageId"] ? intval($arResult["dateLastPageId"]) : 0)
		));
	}
	else
	{
		echo $additional_data;
	}

	if(CModule::IncludeModule("compression"))
		CCompress::DisableCompression();
	CMain::FinalActions();
	die();
}

CUtil::InitJSCore(array("ajax"));
$arParams["UID"] = randString(4);
$arParams["FORM_ID"] = "sonetCommentForm".$arParams["UID"];
$arParams["ALLOW_VIDEO"] = ($arParams["ALLOW_VIDEO"] == "Y" ? "Y" : "N");

if (is_array($arResult["Smiles"]))
{
	$arSmiles = array();
	foreach($arResult["Smiles"] as $arSmile)
	{
		$arSmiles[] = array(
			'name' => $arSmile["NAME"],
			'path' => $arSmile["IMAGE"],
			'code' => str_replace("\\\\","\\",$arSmile["TYPE"]),
			'codes' => str_replace("\\\\","\\",$arSmile["TYPING"]),
			'width' => $arSmile["IMAGE_WIDTH"],
			'height' => $arSmile["IMAGE_HEIGHT"],
		);
	}
	$smiles = Array("VALUE" => $arSmiles);
}
else
{
	$smiles = intval($arResult["Smiles"]);
}

$formParams = array(
	"FORM_ID" => $arParams["FORM_ID"],
	"SHOW_MORE" => "Y",
	"PARSER" => Array(
		"Bold", "Italic", "Underline", "Strike", "ForeColor",
		"FontList", "FontSizeList", "RemoveFormat", "Quote",
		"Code", "CreateLink",
		"Image", "UploadFile",
		"InputVideo",
		"Table", "Justify", "InsertOrderedList",
		"InsertUnorderedList",
		"Source", "MentionUser", "Spoiler"),
	"BUTTONS" => Array(
		(
		(
			in_array("UF_SONET_COM_FILE", $arParams["COMMENT_PROPERTY"])
			|| in_array("UF_SONET_COM_DOC", $arParams["COMMENT_PROPERTY"])
		)
			? "UploadFile"
			: ""
		),
		"CreateLink",
		"InputVideo",
		"Quote", "MentionUser"
	),
	"TEXT" => Array(
		"NAME" => "comment",
		"VALUE" => "",
		"HEIGHT" => "80px"
	),
	"UPLOAD_FILE" => (
	isset($arResult["COMMENT_PROPERTIES"]["DATA"]["UF_SONET_COM_DOC"])
		? false
		: (
	is_array($arResult["COMMENT_PROPERTIES"]["DATA"])
		? $arResult["COMMENT_PROPERTIES"]["DATA"]["UF_SONET_COM_FILE"]
		: false
	)
	),
	"UPLOAD_WEBDAV_ELEMENT" => $arResult["COMMENT_PROPERTIES"]["DATA"]["UF_SONET_COM_DOC"],
	"UPLOAD_FILE_PARAMS" => array("width" => 400, "height" => 400),
	"FILES" => Array(
		"VALUE" => array(),
		"DEL_LINK" => $arResult["urlToDelImage"],
		"SHOW" => "N"
	),
	"SMILES" => $smiles,
	"LHE" => array(
		"id" => "id".$arParams["FORM_ID"],
		"documentCSS" => "body {color:#434343;}",
		"iframeCss" => "html body {padding-left: 14px!important; font-size: 13px!important; line-height: 18px!important;}",
		"ctrlEnterHandler" => "__logSubmitCommentForm".$arParams["UID"],
		"fontFamily" => "'Helvetica Neue', Helvetica, Arial, sans-serif",
		"fontSize" => "12px",
		"bInitByJS" => true,
		"height" => 80
	),
	"PROPERTIES" => array(
		array_merge(
			(
			isset($arResult["COMMENT_PROPERTIES"])
			&& isset($arResult["COMMENT_PROPERTIES"]["DATA"])
			&& isset($arResult["COMMENT_PROPERTIES"]["DATA"]["UF_SONET_COM_URL_PRV"])
			&& is_array($arResult["COMMENT_PROPERTIES"]["DATA"]["UF_SONET_COM_URL_PRV"])
				? $arResult["COMMENT_PROPERTIES"]["DATA"]["UF_SONET_COM_URL_PRV"]
				: array()
			),
			array('ELEMENT_ID' => 'url_preview_'.$arParams["FORM_ID"])
		)
	)
);

?><div style="display: none;">
	<form action="" id="<?=$arParams["FORM_ID"]?>" name="<?=$arParams["FORM_ID"]?>" <?
	?>method="POST" enctype="multipart/form-data" target="_self" class="comments-form">
		<?=bitrix_sessid_post();?>
		<input type="hidden" name="sonet_log_comment_logid" id="sonet_log_comment_logid" value="">
		<?$APPLICATION->IncludeComponent(
			"bitrix:main.post.form",
			".default",
			$formParams,
			false,
			array(
				"HIDE_ICONS" => "Y"
			)
		);?>
		<input type="hidden" name="cuid" id="upload-cid" value="" />
	</form>
</div>
<script type="text/javascript">
	BX.ready(function(){
		window["__logSubmitCommentForm<?=$arParams["UID"]?>"] = function ()
		{
			if (!!window["UC"]["f<?=$arParams["FORM_ID"]?>"] && !!window["UC"]["f<?=$arParams["FORM_ID"]?>"].eventNode)
			{
				BX.onCustomEvent(window["UC"]["f<?=$arParams["FORM_ID"]?>"].eventNode, 'OnButtonClick', ['submit']);
			}
			return false;
		};

		if (!!window["FCForm"])
		{
			window["UC"]["f<?=$arParams["FORM_ID"]?>"] = new FCForm({
				entitiesId : <?=CUtil::PhpToJSObject($component->arResult["ENTITIES_XML_ID"])?>,
				formId : '<?=$arParams["FORM_ID"]?>',
				editorId : 'id<?=$arParams["FORM_ID"]?>'});

			window["UC"]["f<?=$arParams["FORM_ID"]?>"]["entitiesCorrespondence"] = <?=CUtil::PhpToJSObject($component->arResult["ENTITIES_CORRESPONDENCE"])?>;

			window.__logGetNextPageFormName = "f<?=$arParams["FORM_ID"]?>";

			if (!!window["UC"]["f<?=$arParams["FORM_ID"]?>"].eventNode)
			{
				BX.addCustomEvent(window["UC"]["f<?=$arParams["FORM_ID"]?>"].eventNode, 'OnUCFormClear', __socOnUCFormClear);
				BX.addCustomEvent(window["UC"]["f<?=$arParams["FORM_ID"]?>"].eventNode, 'OnUCFormAfterShow', __socOnUCFormAfterShow);
				BX.addCustomEvent(window["UC"]["f<?=$arParams["FORM_ID"]?>"].eventNode, 'OnUCFormSubmit', __socOnUCFormSubmit);
				BX.addCustomEvent(window["UC"]["f<?=$arParams["FORM_ID"]?>"].eventNode, 'OnUCFormResponse', __socOnUCFormResponse);
				BX.addCustomEvent(window["UC"]["f<?=$arParams["FORM_ID"]?>"].eventNode, 'OnUCFormInit', function(obj){BX.remove(BX('micro' + obj.editorName));});
			}

			BX.addCustomEvent(window, "OnBeforeSocialnetworkCommentShowedUp", function(entity) {
				if (entity != 'socialnetwork')
				{
					window["UC"]["f<?=$arParams["FORM_ID"]?>"].hide(true);
				}
			});

			BX.addCustomEvent(window, 'OnUCAddEntitiesCorrespondence', function(key, arValue)
			{
				window["UC"]["f<?=$arParams["FORM_ID"]?>"]["entitiesCorrespondence"][key] = arValue;
			});

			BX.addCustomEvent(window, 'OnUCAfterRecordAdd', function(id, data, responce_data)
			{
				if (typeof responce_data.arComment != 'undefined')
				{
					window["UC"]["f<?=$arParams["FORM_ID"]?>"]["entitiesCorrespondence"][id + '-' + data.messageId[1]] = [responce_data.arComment.LOG_ID, responce_data.arComment.ID];
				}
			});

			BX.addCustomEvent(window, 'OnUCBeforeCommentWillBePulled', function(arId, data)
			{
				if (typeof data.SONET_FULL_ID != 'undefined')
				{
					window["UC"]["f<?=$arParams["FORM_ID"]?>"]["entitiesCorrespondence"][arId.join('-')] = [data.SONET_FULL_ID[0], data.SONET_FULL_ID[1]];
				}
			});

			BX.addCustomEvent(window, 'OnUCFeedChanged', function(data)
			{
				BX.LazyLoad.showImages(true);
			});

			window["SLEC"] = {
				form : BX('<?=$formParams["FORM_ID"]?>'),
				actionUrl : '/bitrix/urlrewrite.php?SEF_APPLICATION_CUR_PAGE_URL=<?=str_replace("%23", "#", urlencode($arResult["urlToPost"]))?>',
				editorId : '<?=$formParams["LHE"]["id"]?>',
				jsMPFName : 'PlEditor<?=$formParams["FORM_ID"]?>',
				formKey : 'f<?=$formParams["FORM_ID"]?>'
			};
		}
	});
</script>
<?
if (defined("BITRIX24_INDEX_COMPOSITE"))
{
	$dynamicArea->finishDynamicArea();
}
?>