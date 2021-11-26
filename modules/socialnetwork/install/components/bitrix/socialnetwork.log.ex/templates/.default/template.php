<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

/** @var CBitrixComponentTemplate $this */
/** @var string $templateFolder */
/** @var array $arParams */
/** @var array $arResult */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */

$component = $this->getComponent();

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ModuleManager;
use Bitrix\Main\UI;
use Bitrix\Main\Page\Asset;

$targetHtml = '';
$error = false;

$this->setFrameMode(true);

if ($arResult["NEED_AUTH"] === "Y")
{
	$APPLICATION->AuthForm("");
}
elseif (
	isset($arResult["FatalError"])
	&& $arResult["FatalError"] <> ''
)
{
	if (in_array($arResult['PAGE_MODE'], ['refresh', 'next' ]))
	{
		$targetHtml .= '<span class="livefeed-empty-block"></span>';
		$error = true;
	}
	else
	{
		?><span class='errortext'><?=$arResult["FatalError"]?></span><br /><br /><?php
		return;
	}
}

CUtil::InitJSCore(array("ajax", "window", "tooltip", "popup", "fx", "viewer", "content_view", "clipboard"));
UI\Extension::load([
	'socialnetwork.livefeed',
	'socialnetwork.commentaux',
	'tasks.comment-action-controller',
]);

Asset::getInstance()->setUnique('PAGE', 'live_feed_v2'.($arParams["IS_CRM"] !== "Y" ? "" : "_crm"));
Asset::getInstance()->addJs("/bitrix/js/main/rating_like.js");

if (
	defined('SITE_TEMPLATE_ID')
	&& SITE_TEMPLATE_ID === "bitrix24"
	&& $arResult['PAGE_MODE'] === 'first'
)
{
	$bodyClass = $APPLICATION->GetPageProperty("BodyClass");
	$APPLICATION->SetPageProperty("BodyClass", ($bodyClass ? $bodyClass." " : "")."workarea-transparent no-background");
}

if (
	$arParams["IS_CRM"] !== "Y"
	&& $arResult['PAGE_MODE'] === 'first'
)
{
	$bodyClass = $APPLICATION->GetPageProperty("BodyClass");
	$bodyClass = $bodyClass ? $bodyClass." no-all-paddings" : "no-all-paddings";
	$APPLICATION->SetPageProperty("BodyClass", $bodyClass);

	if (
		defined('SITE_TEMPLATE_ID')
		&& SITE_TEMPLATE_ID === "bitrix24"
		&& (
			(
				ModuleManager::isModuleInstalled('bitrix24')
				&& CBitrix24::isPortalAdmin($arResult["currentUserId"])
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

if (
	!$error
	&& $arResult['PAGE_MODE'] === 'first'
)
{
	Asset::getInstance()->addJs('/bitrix/components/bitrix/socialnetwork.log.entry/templates/.default/scripts.js');

	$APPLICATION->IncludeComponent("bitrix:main.user.link",
		'',
		[
			"AJAX_ONLY" => "Y",
			"PATH_TO_SONET_USER_PROFILE" => $arParams["~PATH_TO_USER"],
			"PATH_TO_SONET_MESSAGES_CHAT" => $arParams["~PATH_TO_MESSAGES_CHAT"],
			"DATE_TIME_FORMAT" => $arParams["DATE_TIME_FORMAT"],
			"SHOW_YEAR" => $arParams["SHOW_YEAR"],
			"NAME_TEMPLATE" => $arParams["NAME_TEMPLATE"],
			"SHOW_LOGIN" => $arParams["SHOW_LOGIN"],
			"PATH_TO_CONPANY_DEPARTMENT" => $arParams["~PATH_TO_CONPANY_DEPARTMENT"],
			"PATH_TO_VIDEO_CALL" => $arParams["~PATH_TO_VIDEO_CALL"],
		],
		false,
		[ "HIDE_ICONS" => "Y" ]
	);

	require_once($_SERVER["DOCUMENT_ROOT"].$templateFolder."/include/top_forms.php");
	require_once($_SERVER["DOCUMENT_ROOT"].$templateFolder."/include/filter_area.php");

	if (defined("BITRIX24_INDEX_COMPOSITE"))
	{
		$dynamicArea = new \Bitrix\Main\Page\FrameStatic("live-feed");
		$dynamicArea->startDynamicArea();
		$dynamicArea->setStub($stub);
	}

	require_once($_SERVER["DOCUMENT_ROOT"].$templateFolder."/include/informer.php");

	if ($arResult["SHOW_NOTIFICATION_NOTASKS"])
	{
		?><div class="feed-notification-container">
			<div class="feed-notification-block-icon">
				<div class="feed-notification-icon"></div>
			</div>
			<div class="feed-notification-block-content">
				<div class="feed-notification-title"><?=GetMessage("SONET_C30_FEED_NOTIFICATION_NOTASKS_TITLE")?></div>
				<div class="feed-notification-description"><?=GetMessage("SONET_C30_FEED_NOTIFICATION_NOTASKS_DESC2")?></div>
				<div class="feed-notification-buttons">
					<a href="javascript:void(0);" class="ui-btn ui-btn-sm ui-btn-primary ui-btn-round" id="feed-notification-notasks-read-btn"><?=Loc::getMessage('SONET_C30_FEED_NOTIFICATION_NOTASKS_BUTTON_OK')?></a>
					<a onclick="top.BX.Helper.show('redirect=detail&code=11182736');" style="margin-left: 12px;" class="ui-link ui-link-dashed ui-link-secondary"><?=Loc::getMessage('SONET_C30_FEED_NOTIFICATION_NOTASKS_BUTTON_MORE')?></a>
				</div>
			</div>
			<div id="feed-notification-notasks-close-btn" class="feed-notification-close-btn" onclick=""></div>
		</div><?php
	}

	?><div id="log_internal_container"><?php
		?><div class="feed-loader-container" id="feed-loader-container"><?php
			?><svg class="feed-loader-circular" viewBox="25 25 50 50"><?php
				?><circle class="feed-loader-path" cx="50" cy="50" r="20" fill="none" stroke-miterlimit="10"/><?php
			?></svg><?php
		?></div><?php
}
elseif (
	!$error
	&& $arResult["EMPTY_AJAX_FEED"]
)
{
	$targetHtml .= '<span class="livefeed-empty-block"></span>';
}

require($_SERVER["DOCUMENT_ROOT"].$templateFolder."/include/pinned.php");

if (
	!$error
	&& !$arResult["EMPTY_AJAX_FEED"]
)
{

	if ($arResult['PAGE_MODE'] === 'first')
	{
		?><div class="feed-wrap"><?php
	}

	/*
	* inline JS start
	*/
	ob_start();

	?><script>
			<?php
		if ($arResult['PAGE_MODE'] === 'first')
		{
			?>
			BX.message({
				sonetLGetPath: '<?=CUtil::JSEscape('/bitrix/components/bitrix/socialnetwork.log.ex/ajax.php')?>',
				sonetLSetPath: '<?=CUtil::JSEscape('/bitrix/components/bitrix/socialnetwork.log.ex/ajax.php')?>',
				sonetLESetPath: '<?=CUtil::JSEscape('/bitrix/components/bitrix/socialnetwork.log.entry/ajax.php')?>',
				sonetLEPath: '<?=CUtil::JSEscape($arParams["PATH_TO_LOG_ENTRY"])?>',
				sonetLSessid: '<?=bitrix_sessid_get()?>',
				sonetLAssetsCheckSum: '<?=(!empty($arResult['ASSETS_CHECKSUM']) ? CUtil::JSEscape($arResult['ASSETS_CHECKSUM']) : '')?>',
				sonetLNoSubscriptions: '<?=GetMessageJS("SONET_C30_NO_SUBSCRIPTIONS")?>',
				sonetLInherited: '<?=GetMessageJS("SONET_C30_INHERITED")?>',
				sonetLDialogClose: '<?=GetMessageJS("SONET_C30_DIALOG_CLOSE_BUTTON")?>',
				sonetLDialogSubmit: '<?=GetMessageJS("SONET_C30_DIALOG_SUBMIT_BUTTON")?>',
				sonetLDialogCancel: '<?=GetMessageJS("SONET_C30_DIALOG_CANCEL_BUTTON")?>',
				sonetLbUseFavorites: '<?=(!isset($arParams["USE_FAVORITES"]) || $arParams["USE_FAVORITES"] !== "N" ? "Y" : "N")?>',
				sonetLMenuLink: '<?=GetMessageJS("SONET_C30_MENU_TITLE_LINK2")?>',
				sonetLMenuHref: '<?=GetMessageJS("SONET_C30_MENU_TITLE_HREF")?>',
				sonetLMenuDelete: '<?=GetMessageJS(ModuleManager::isModuleInstalled('intranet') ? "SONET_C30_MENU_TITLE_DELETE2" : "SONET_C30_MENU_TITLE_DELETE")?>',
				sonetLMenuDeleteConfirm: '<?=GetMessageJS("SONET_C30_MENU_TITLE_DELETE_CONFIRM")?>',
				sonetLMenuDeleteFailure: '<?=GetMessageJS("SONET_C30_MENU_TITLE_DELETE_FAILURE")?>',
				sonetLMenuCreateTask: '<?=GetMessageJS("SONET_C30_MENU_TITLE_CREATETASK")?>',
				sonetLMenuCreateSubTask: '<?=GetMessageJS("SONET_C30_MENU_TITLE_CREATESUBTASK")?>',
				sonetLCounterType: '<?=CUtil::JSEscape($arResult["COUNTER_TYPE"])?>',
				sonetLIsB24: '<?=(defined('SITE_TEMPLATE_ID') && SITE_TEMPLATE_ID === "bitrix24" ? "Y" : "N")?>',
				sonetRatingType : '<?=CUtil::JSEscape($arParams["RATING_TYPE"])?>',
				sonetLIsCRM : '<?=CUtil::JSEscape($arParams["IS_CRM"])?>',
				sonetLCanDelete : '<?=($arResult["CAN_DELETE"] ? 'Y' : 'N')?>',
				sonetLForumID : <?=intval($arParams["FORUM_ID"])?>,
				SONET_C30_T_LINK_COPIED: '<?=GetMessageJS("SONET_C30_T_LINK_COPIED")?>',
				SONET_C30_T_EMPTY: '<?=GetMessageJS("SONET_C30_T_EMPTY")?>',
				SONET_C30_T_EMPTY_SEARCH: '<?=GetMessageJS("SONET_C30_T_EMPTY_SEARCH")?>'
			});

			BX.ready(function(){

				BX.Livefeed.FeedInstance.init();

				BX.addCustomEvent('onFrameDataProcessed', function() {
					BX.Livefeed.FeedInstance.init();
				});

				BX.Livefeed.FeedInstance.initOnce({
					crmEntityTypeName: '<?= (!empty($arResult['CRM_ENTITY_TYPE_NAME']) ? CUtil::JSEscape($arResult['CRM_ENTITY_TYPE_NAME']) : '') ?>',
					crmEntityId: <?= (!empty($arResult['CRM_ENTITY_ID']) ? (int)$arResult['CRM_ENTITY_ID'] : 0) ?>,
					filterId: '<?= (!empty($arResult['FILTER_ID']) ? CUtil::JSEscape($arResult["FILTER_ID"]) : '') ?>',
					signedParameters: '<?= $this->getComponent()->getSignedParameters() ?>',
					componentName: '<?= $this->getComponent()->getName() ?>'
				});
			});
			<?php
		}

		if (in_array($arResult['PAGE_MODE'], [ 'first', 'refresh' ]))
		{
			?>
			BX.ready(function(){
				oLF.init({
					firstPageLastTS : <?=intval($arResult["dateLastPageTS"])?>,
					firstPageLastId : <?=intval($arResult["dateLastPageId"])?>,
					useBXMainFilter: '<?=(isset($arParams["useBXMainFilter"]) && $arParams["useBXMainFilter"] === 'Y' ? 'Y' : 'N')?>'
				});
			});
			<?php
		}

		if (
			in_array($arResult['PAGE_MODE'], [ 'refresh', 'next' ])
			&& $arParams["SHOW_RATING"] === "Y"
		)
		{
			$likeTemplate = (
				ModuleManager::isModuleInstalled('intranet')
					? 'like_react'
					: 'like'
			);

			if ($arParams["RATING_TYPE"] === "like")
			{
				Asset::getInstance()->addCss('/bitrix/components/bitrix/rating.vote/templates/'.$likeTemplate.'/popup.css');
			}
			Asset::getInstance()->addCss('/bitrix/components/bitrix/rating.vote/templates/'.($arParams["RATING_TYPE"] === "like" ? $likeTemplate : $arParams["RATING_TYPE"]).'/style.css');
		}

		if ($arResult['PAGE_MODE'] === 'refresh')
		{
			?>
			if (typeof __logOnReload === 'function')
			{
				BX.ready(function(){
					window.bRefreshed = true;
					__logOnReload(<?=intval($arResult["LOG_COUNTER"])?>);
				});
			}
			<?php
		}
		elseif (
			$arParams["IS_CRM"] === "Y"
			&& $arResult['PAGE_MODE'] === 'first'
		)
		{
			?>
			if (typeof __logOnReload === 'function')
			{
				BX.ready(function(){
					__logOnReload(<?=(int)$arResult["LOG_COUNTER"]?>);
				});
			}
			<?php
		}

		if (in_array($arResult['PAGE_MODE'], ['first', 'refresh' ]))
		{
			?>
			BX.ready(function() {
				<?php
				if (
					$arParams["SET_LOG_COUNTER"] !== "N"
					&& !(isset($arResult["EXPERT_MODE_SET"]) && $arResult["EXPERT_MODE_SET"])
				)
				{
					?>
					BX.onCustomEvent(window, 'onSonetLogCounterClear', [BX.message('sonetLCounterType')]);
				<?php
				}

				if ($arResult['PAGE_MODE'] === 'first')
				{
					?>
					BX.addCustomEvent('onAjaxFailure', function(status){
						if (status == 'auth')
						{
							top.location = top.location.href;
						}
					});
				<?php
				}
				?>
			});

			<?php
			if ($arResult['PAGE_MODE'] === 'first')
			{
				if(\Bitrix\Main\Page\Frame::isAjaxRequest())
				{
					?>
					setTimeout(function() {
						BX.Livefeed.MoreButton.recalcPostsList()
						BX.Livefeed.ContentView.registerAreaList();
					}, 1000);
			<?php
				}
				elseif (!empty($arParams["CRM_ENTITY_ID"]))
				{
					?>
					BX.bind(window, 'load', function() {
						setTimeout(function() {
							BX.Livefeed.MoreButton.recalcPostsList();
						}, 1000);
						BX.Livefeed.ContentView.registerAreaList();
					});
			<?php
				}
				else
				{
				?>
					BX.ready(function() {
						setTimeout(function() {
							BX.Livefeed.MoreButton.recalcPostsList();
						}, 1);
					});
					BX.bind(window, 'load', function() {
						BX.Livefeed.ContentView.registerAreaList();
					});
			<?php
				}
			}
		}
		?>
		BX.ready(function()
		{
			BX.Livefeed.FeedInstance.clearMoreButtons();
			BX.bind(BX('sonet_log_counter_2_container'), 'click', BX.Livefeed.PageInstance.clearContainerExternalNew);

			if (BX('sonet_log_comment_text'))
			{
				BX('sonet_log_comment_text').onkeydown = BX.eventCancelBubble;
			}
		});

	</script><?php

	$blockContent = ob_get_clean();

	if (in_array($arResult['PAGE_MODE'],  ['refresh', 'next' ]))
	{
		$targetHtml .= $blockContent;
	}
	else
	{
		echo $blockContent;
	}
	/*
	* inline JS end
	*/

	if(
		$arResult['PAGE_MODE'] === 'first'
		&& $arResult["ErrorMessage"] <> ''
	)
	{
		?><span class='errortext'><?=$arResult["ErrorMessage"]?></span><br /><br /><?php
	}

	$hasBlogEvent = (
		in_array($arResult['PAGE_MODE'], [ 'first', 'refresh'])
		|| $_REQUEST['noblog'] === 'Y'
			? false
			: true
	);

	ob_start();

	if (
		is_array($arResult["Events"])
		&& !empty($arResult["Events"])
	)
	{
		$blogPostLivefeedProvider = new \Bitrix\Socialnetwork\Livefeed\BlogPost;
		$blogPostEventIdList = $blogPostLivefeedProvider->getEventId();

		foreach ($arResult["Events"] as $arEvent)
		{
			if (empty($arEvent))
			{
				continue;
			}

			$ind = randString(8);

			$event_date_log_ts = (
				isset($arEvent["LOG_DATE_TS"])
					? $arEvent["LOG_DATE_TS"]
					: (MakeTimeStamp($arEvent["LOG_DATE"]) - intval($arResult["TZ_OFFSET"]))
			);

			$is_unread = (
				$arResult["SHOW_UNREAD"] === "Y"
				&& in_array($arResult["COUNTER_TYPE"], [ '**', 'CRM_**', 'blog_post' ])
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
				require($_SERVER["DOCUMENT_ROOT"].$templateFolder."/include/blog_post.php");
			}
			else
			{
				require($_SERVER["DOCUMENT_ROOT"].$templateFolder."/include/log_entry.php");
			}
		}
	}

	/*
	* empty stub start
	*/
	$emptyMessage = (
		$arResult["IS_FILTERED"]
		&& (
			empty($arParams["GROUP_ID"])
			|| (int)$arParams["GROUP_ID"] <= 0
		)
			? Loc::getMessage('SONET_C30_T_EMPTY_SEARCH')
			: Loc::getMessage('SONET_C30_T_EMPTY')
	);

	?><div class="feed-wrap-empty-wrap" id="feed-empty-wrap" style="display: <?=(!is_array($arResult["Events"]) || empty($arResult["Events"]) ? 'block' : 'none')?>"><?php
		?><div class="feed-wrap-empty"><?=$emptyMessage?></div><?php
	?></div><?php

	$blockContent = ob_get_clean();

		if (in_array($arResult['PAGE_MODE'], ['refresh', 'next' ]))
	{
		$targetHtml .= $blockContent;
	}
	else
	{
		echo $blockContent;
	}

	/*
	* empty stub end
	*/

	if (
		$arParams["SHOW_NAV_STRING"] !== "N"
		&& is_array($arResult["Events"])
	)
	{
		$uri = new \Bitrix\Main\Web\Uri(htmlspecialcharsback(POST_FORM_ACTION_URI));

		$uri->deleteParams([
			"PAGEN_".$arResult["PAGE_NAVNUM"],
			"RELOAD",
			"logajax",
			"pplogid",
			"pagesize",
			"startVideoRecorder"
		]);

		$uriParams = [
			'logajax' => 'Y',
			'PAGEN_' . $arResult['PAGE_NAVNUM'] => ($arResult['PAGE_NUMBER'] + 1),
			'ts' => $arResult['LAST_LOG_TS'],
		];

		if (
			is_array($arResult["arLogTmpID"])
			&& count($arResult["arLogTmpID"]) > 0
		)
		{
			$uriParams['pplogid'] = implode("|", $arResult["arLogTmpID"]);
		}
		if ((int)$arResult["NEXT_PAGE_SIZE"] > 0)
		{
			$uriParams['pagesize'] = (int)$arResult["NEXT_PAGE_SIZE"];
		}
		if (!$hasBlogEvent)
		{
			$uriParams['noblog'] = 'Y';
		}

		$uriParams['preset_filter_top_id'] = $arResult['presetFilterTopIdValue'];
		$uriParams['preset_filter_id'] = $arResult['presetFilterIdValue'];

		if (
			isset($arParams['CREATED_BY_ID'])
			&& (int)$arParams['CREATED_BY_ID'] > 0
		)
		{
			$uriParams['CREATED_BY_ID'] = (int)$arParams['CREATED_BY_ID'];
		}

		$request = \Bitrix\Main\Context::getCurrent()->getRequest();

		if ($request->get('flt_date_datesel'))
		{
			$uriParams['flt_date_datesel'] = preg_replace('/[^a-z0-9_]/i', '', $request->get('flt_date_datesel'));
		}

		if ($request->get('flt_date_from'))
		{
			$uriParams['flt_date_from'] = preg_replace('/[^0-9\/]/i', '', $request->get('flt_date_from'));
		}

		if ($request->get('flt_date_to'))
		{
			$uriParams['flt_date_to'] = preg_replace('/[^0-9\/]/i', '', $request->get('flt_date_to'));
		}

		$uri->addParams($uriParams);

		ob_start();
		?>
		<script>
			BX.ready(function() {
				BX.Livefeed.PageInstance.setNextPageUrl('<?= CUtil::JSEscape(htmlspecialcharsEx($uri->getUri())) ?>');

				<?php
				if (
					$arResult["PAGE_NUMBER"] == 1
					&& in_array($arResult['PAGE_MODE'], [ 'first', 'refresh' ])
				)
				{
					?>
					BX.Livefeed.PageInstance.initScroll();
					<?php
				}
				?>
			});
		</script><?php

		$blockContent = ob_get_clean();

		if (in_array($arResult['PAGE_MODE'], ['refresh', 'next' ]))
		{
			$targetHtml .= $blockContent;
		}
		else
		{
			echo $blockContent;
		}

		/*
		* next page loader block start
		*/
		if (in_array($arResult['PAGE_MODE'], [ 'first', 'refresh' ]))
		{
			ob_start();

			?><div class="feed-new-message-inf-wrap-first" id="feed-new-message-inf-wrap-first"><?php
				?><a href="javascript:void(0);" id="sonet_log_more_container_first" class="feed-new-message-inf-bottom"><?php
				?><span class="feed-new-message-inf-text" id="feed-new-message-inf-text-first" style="display: none;"><?php
					?><?=GetMessage("SONET_C30_MORE")?><?php
					?><span class="feed-new-message-icon"></span><?php
				?></span><?php
				?><span class="feed-new-message-inf-loader-first-cont" id="feed-new-message-inf-loader-first"><?php
					?><svg class="feed-new-message-inf-loader-first-loader" viewBox="25 25 50 50"><circle class="feed-new-message-inf-loader-first-path" cx="50" cy="50" r="20" fill="none" stroke-miterlimit="10"></circle><circle class="feed-new-message-inf-loader-first-inner-path" cx="50" cy="50" r="20" fill="none" stroke-miterlimit="10"></circle></svg><?php
				?></span><?php
				?></a><?php
			?></div><?php

			$blockContent = ob_get_clean();

			if ($arResult['PAGE_MODE'] === 'refresh')
			{
				$targetHtml .= $blockContent;
			}
			else
			{
				echo $blockContent;
			}
		}
		/*
		* next page loader block end
		*/

		?><div class="feed-new-message-inf-wrap feed-new-message-active" id="feed-new-message-inf-wrap" style="display: none;"><?=$stub?></div><?php
	}

	if ($arResult['PAGE_MODE'] === 'first')
	{
			?></div><?php // feed-wrap
		?></div><?php // log_internal_container

		CUtil::InitJSCore(array("ajax"));

		require_once($_SERVER["DOCUMENT_ROOT"].$templateFolder."/include/comment_form.php");
	}
	else
	{
		$inlineJs = '<script bxrunfirst="true">'."\n";
		$inlineJs .= 'window.__logGetNextPageLinkEntities('.
			CUtil::PhpToJSObject($component->arResult["ENTITIES_XML_ID"]).', '.
			CUtil::PhpToJSObject($component->arResult["ENTITIES_CORRESPONDENCE"]).');';
		$inlineJs .= '</script>';
		$inlineJs .= Asset::getInstance()->getJs();

		$strText = ob_get_clean();

		ob_start();
		echo  $inlineJs.$strText;
		$targetHtml .= ob_get_clean();
	}
}

if ($targetHtml <> '')
{
	$APPLICATION->RestartBuffer();
	echo $targetHtml;
}

if (defined("BITRIX24_INDEX_COMPOSITE"))
{
	$dynamicArea->finishDynamicArea();
}
