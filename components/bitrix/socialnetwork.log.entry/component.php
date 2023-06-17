<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

/** @var CBitrixComponent $this */
/** @var array $arParams */
/** @var array $arResult */
/** @var string $componentPath */
/** @var string $componentName */
/** @var string $componentTemplate */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */
/** @global CCacheManager $CACHE_MANAGER */

global $CACHE_MANAGER;

use Bitrix\Main\Engine\ActionFilter\Service\Token;
use Bitrix\Socialnetwork\Livefeed;
use Bitrix\Main\Loader;
use Bitrix\Main\ModuleManager;

require_once($_SERVER['DOCUMENT_ROOT'] . $this->getPath() . '/include.php');

if (!Loader::includeModule("socialnetwork"))
{
	ShowError(GetMessage("SONET_MODULE_NOT_INSTALL"));
	return;
}

if (
	!isset($arParams['LOG_ID'])
	|| (int)$arParams['LOG_ID'] <= 0
)
{
	return;
}

if (
	!isset($arParams['IND'])
	|| (string)$arParams['IND'] === ''
)
{
	$arParams["IND"] = \Bitrix\Main\Security\Random::getString(8);
}

if (empty($arParams["LOG_PROPERTY"]))
{
	$arParams["LOG_PROPERTY"] = array("UF_SONET_LOG_FILE");
	if (IsModuleInstalled("webdav")  || IsModuleInstalled("disk"))
	{
		$arParams["LOG_PROPERTY"][] = "UF_SONET_LOG_DOC";
	}
}

if (empty($arParams["COMMENT_PROPERTY"]))
{
	$arParams["COMMENT_PROPERTY"] = array("UF_SONET_COM_FILE");
	if (
		ModuleManager::isModuleInstalled('webdav')
		|| ModuleManager::isModuleInstalled('disk')
	)
	{
		$arParams["COMMENT_PROPERTY"][] = "UF_SONET_COM_DOC";
	}

	$arParams["COMMENT_PROPERTY"][] = "UF_SONET_COM_URL_PRV";
}

if (empty($arParams["PATH_TO_LOG_TAG"]))
{
	$folderUsers = COption::GetOptionString("socialnetwork", "user_page", false, SITE_ID);
	$arParams["PATH_TO_LOG_TAG"] = $folderUsers."log/?TAG=#tag#";
	if (SITE_TEMPLATE_ID === 'bitrix24')
	{
		$arParams["PATH_TO_LOG_TAG"] .= "&apply_filter=Y";
	}
}

CSocNetLogComponent::processDateTimeFormatParams($arParams);

$bCurrentUserIsAdmin = CSocNetUser::IsCurrentUserModuleAdmin();

$arParams["COMMENT_ID"] = (int)$arParams["COMMENT_ID"];

$arResult["TZ_OFFSET"] = CTimeZone::GetOffset();
$arResult['LAST_LOG_TS'] = (int) ($arParams['LAST_LOG_TS'] ?? null);
$arResult["COUNTER_TYPE"] = $arParams["COUNTER_TYPE"] ?? null;
$arResult["AJAX_CALL"] = $arParams["AJAX_CALL"] ?? null;
$arResult["bReload"] = $arParams["bReload"] ?? null;
$arResult["bGetComments"] = $arParams["bGetComments"] ?? null;
$arResult["bIntranetInstalled"] = ModuleManager::isModuleInstalled("intranet");

$currentUserId = (int)$USER->getId();

$arResult['bPublicPage'] = (isset($arParams['PUB']) && $arParams['PUB'] === 'Y');

$arResult["bTasksInstalled"] = Loader::includeModule("tasks");
$arResult["bTasksAvailable"] = (
	!$arResult["bPublicPage"]
	&& $arResult["bTasksInstalled"]
	&& (
		!Loader::includeModule('bitrix24')
		|| CBitrix24BusinessTools::isToolAvailable($currentUserId, "tasks")
	)
	&& \Bitrix\Tasks\Access\TaskAccessController::can($currentUserId, \Bitrix\Tasks\Access\ActionDictionary::ACTION_TASK_CREATE)
);

$arResult["Event"] = false;
$arCurrentUserSubscribe = array("TRANSPORT" => array());

$arEvent = __SLEGetLogRecord($arParams["LOG_ID"], $arParams, $arCurrentUserSubscribe);
if (!$arEvent)
{
	return;
}

$contentId = Livefeed\Provider::getContentId($arEvent['EVENT']);

$arResult["canGetCommentContent"] = false;
$arResult["POST_CONTENT_TYPE_ID"] = false;
$arResult["COMMENT_CONTENT_TYPE_ID"] = false;

if (
	!empty($contentId['ENTITY_TYPE'])
	&& ($postProvider = \Bitrix\Socialnetwork\Livefeed\Provider::getProvider($contentId['ENTITY_TYPE']))
)
{
	$postProviderClassName = get_class($postProvider);
	$reflectionClass = new ReflectionClass($postProviderClassName);
	$arResult["canGetPostContent"] = ($reflectionClass->getMethod('initSourceFields')->class === $postProviderClassName);
	if ($arResult["canGetPostContent"])
	{
		$arResult["POST_CONTENT_TYPE_ID"] = $postProvider->getContentTypeId();
		$arResult["POST_CONTENT_ID"] = $contentId['ENTITY_ID'];
	}

	if ($commentProvider = $postProvider->getCommentProvider())
	{
		$commentProviderClassName = get_class($commentProvider);
		$reflectionClass = new ReflectionClass($commentProviderClassName);

		$arResult["canGetCommentContent"] = ($reflectionClass->getMethod('initSourceFields')->class === $commentProviderClassName);
		if ($arResult["canGetCommentContent"])
		{
			$arResult["COMMENT_CONTENT_TYPE_ID"] = $commentProvider->getContentTypeId();
		}

		$commentProvider->setLogEventId($arEvent['EVENT']['EVENT_ID']);
		$suffix = $commentProvider->getSuffix();
		if (!empty($suffix))
		{
			$arParams['COMMENT_ENTITY_SUFFIX'] = $suffix;
		}
	}
}

if (
	isset($arEvent['HAS_COMMENTS'])
	&& $arEvent['HAS_COMMENTS'] === 'Y'
)
{
	$commentEvent = CSocNetLogTools::FindLogCommentEventByLogEventID($arEvent["EVENT"]["EVENT_ID"]);
	if (
		!empty($commentEvent)
		&& isset($commentEvent["METHOD_GET_URL"])
		&& is_callable($commentEvent["METHOD_GET_URL"])
	)
	{
		$arResult["COMMENT_URL"] = call_user_func($commentEvent["METHOD_GET_URL"], [
			"ENTRY_ID" => $arEvent["EVENT"]["SOURCE_ID"],
			"ENTRY_USER_ID" => $arEvent["EVENT"]["USER_ID"],
		]);
	}
	else
	{
		$arResult["COMMENT_URL"] = false;
	}

	$nTopCount = 20;

	$arCommentsFullList = \Bitrix\Socialnetwork\Component\LogEntry::getCommentsFullList($arEvent, $arParams, [
		'nTopCount' => $nTopCount,
		'timeZoneOffzet' => $arResult['TZ_OFFSET'],
		'commentEvent' => $commentEvent,
		'commentProvider' => $commentProvider ?? null
	], $arResult);

	$arCommentsFullListCut = array();
	$arCommentID = array();

	$handlerManager = new Bitrix\Socialnetwork\CommentAux\HandlerManager();

	$arResult['NEW_COMMENTS_COUNT'] = 0;
	$arResult['ALL_COMMENTS_COUNT'] = (int)$arEvent["COMMENTS_COUNT"];

	foreach ($arCommentsFullList as $key => $arCommentTmp)
	{
		if ($key === 0)
		{
			$rating_entity_type = $arCommentTmp["EVENT"]["RATING_TYPE_ID"];
		}

		if (
			isset($arCommentTmp['EVENT_FORMATTED']['MESSAGE'])
			&& ($handler = $handlerManager->getHandlerByPostText($arCommentTmp['EVENT_FORMATTED']['MESSAGE'])
			)
		)
		{
			$arCommentTmp["AUX"] = $handler->getType();
			$arCommentTmp["CAN_DELETE"] = ($handler->canDelete() ? 'Y' : 'N');

			if ($handler->checkRecalcNeeded($arCommentTmp['EVENT'], array(
				'bPublicPage' => $arResult['bPublicPage']
			)))
			{
				$commentAuxFields = $arCommentTmp['EVENT'];
				$params = $handler->getParamsFromFields($commentAuxFields);
				if (!empty($params))
				{
					$handler->setParams($params);
				}

				$handler->setOptions(array(
					'mobile' => false,
					'bPublicPage' => (isset($arParams["bPublicPage"]) && $arParams["bPublicPage"]),
					'cache' => false,
					'suffix' => (!empty($arParams['COMMENT_ENTITY_SUFFIX']) ? $arParams['COMMENT_ENTITY_SUFFIX'] : ''),
					'logId' => $arParams["LOG_ID"],
					'entityType' => $contentId['ENTITY_TYPE'],
					'entityId' => $contentId['ENTITY_ID'],
				));
				$arCommentTmp['EVENT_FORMATTED']['FULL_MESSAGE_CUT']  = nl2br($handler->getText());
			}
		}

		if (
			$arResult["bGetComments"]
			&& (int)$arParams["CREATED_BY_ID"] > 0
		)
		{
			if ((int)$arCommentTmp["EVENT"]["USER_ID"] === (int)$arParams["CREATED_BY_ID"])
			{
				$arCommentsFullListCut[] = $arCommentTmp;
			}
		}
		else
		{
			$event_date_log_ts = ($arCommentTmp["EVENT"]["LOG_DATE_TS"] ?? (MakeTimeStamp($arCommentTmp["EVENT"]["LOG_DATE"]) - (int)$arResult["TZ_OFFSET"]));

			if (
				$arResult["COUNTER_TYPE"] === '**'
				&& $arResult['LAST_LOG_TS'] > 0
				&& $event_date_log_ts >= $arResult['LAST_LOG_TS']
				&& (int)$arCommentTmp['EVENT']['USER_ID'] !== $currentUserId
				&& (
					!is_array($arParams['UNREAD_COMMENTS_ID_LIST'] ?? null)
					|| in_array($arCommentTmp['EVENT']['ID'], $arParams['UNREAD_COMMENTS_ID_LIST'])
				)
			)
			{
				$arResult['NEW_COMMENTS_COUNT']++;
			}

			if (!(
				$arParams["COMMENT_ID"] <= 0
				&& (
					(
						$event_date_log_ts > $arResult["LAST_LOG_TS"]
						&& $key >= $nTopCount
					) // new comments, no more than 20
					|| (
						(
							$event_date_log_ts <= $arResult["LAST_LOG_TS"]
							|| $arResult["LAST_LOG_TS"] <= 0
						)
						&& $key >= $arParams["COMMENTS_IN_EVENT"]
					) // old comments, no more than 3
				)
			))
			{
				$arCommentsFullListCut[] = $arCommentTmp;
			}
		}

		$arCommentID[] = $arCommentTmp["EVENT"]["RATING_ENTITY_ID"];
	}

	$arCommentRights = CSocNetLogComponent::getCommentRights(array(
		"EVENT_ID" => $arEvent["EVENT"]["EVENT_ID"],
		"SOURCE_ID" => $arEvent["EVENT"]["SOURCE_ID"],
		"USER_ID" => $currentUserId,
	));
	$arResult["COMMENT_RIGHTS_EDIT"] = $arCommentRights["COMMENT_RIGHTS_EDIT"];
	$arResult["COMMENT_RIGHTS_DELETE"] = $arCommentRights["COMMENT_RIGHTS_DELETE"];

	$arEvent["COMMENTS"] = array_reverse($arCommentsFullListCut);
	$arResult["RATING_COMMENTS"] = array();
	if (
		!empty($arCommentID)
		&& $arParams['SHOW_RATING'] === 'Y'
		&& $rating_entity_type <> ''
	)
	{
		$arResult["RATING_COMMENTS"] = CRatings::GetRatingVoteResult($rating_entity_type, $arCommentID);
	}
}

$liveFeedEntity = Livefeed\Provider::init(array(
	'ENTITY_TYPE' => $contentId['ENTITY_TYPE'] ?? null,
	'ENTITY_ID' => $contentId['ENTITY_ID'] ?? null,
	'LOG_ID' => $arEvent["EVENT"]["ID"]
));

if (
	(
		isset($arParams['FROM_LOG'])
		&& $arParams['FROM_LOG'] === 'N'
	)
	&& !empty($arEvent["EVENT"])
	&& $contentId
	&& $liveFeedEntity
)
{
	$liveFeedEntity->setContentView();
}

$arResult['CONTENT_ID'] = '';
if (
	$liveFeedEntity
	&& $contentId
)
{
	$arResult['CONTENT_ID'] = (string)(
		!empty($arParams['CONTENT_ID'])
			? $arParams['CONTENT_ID']
			: $contentId['ENTITY_TYPE'] . '-' . (int)$contentId['ENTITY_ID']
	);

	$arResult['CONTENT_VIEW_KEY_SIGNED'] = (string)($arParams['CONTENT_VIEW_KEY_SIGNED'] ?? (
		(is_object($USER) && $USER->isAuthorized())
		&& $arResult['CONTENT_ID'] !== ''
			? (new Token($USER->getId()))->generate($arResult['CONTENT_ID'])
			: ''
	));

	if (isset($arParams["CONTENT_VIEW_CNT"]))
	{
		$arResult["CONTENT_VIEW_CNT"] = (int)$arParams["CONTENT_VIEW_CNT"];
	}
	elseif (
		($contentViewData = \Bitrix\Socialnetwork\Item\UserContentView::getViewData(array(
			'contentId' => array($arResult["CONTENT_ID"])
		)))
		&& !empty($contentViewData[$arResult["CONTENT_ID"]])
	)
	{
		$arResult["CONTENT_VIEW_CNT"] = (int)$contentViewData[$arResult["CONTENT_ID"]]["CNT"];
	}
	else
	{
		$arResult["CONTENT_VIEW_CNT"] = 0;
	}
}

$arResult["Event"] = $arEvent;
$arResult["WORKGROUPS_PAGE"] = COption::GetOptionString("socialnetwork", "workgroups_page", "/workgroups/", SITE_ID);

$arResult['isCurrentUserEventOwner'] = (
	((int)$arEvent['EVENT']['USER_ID'] === $currentUserId)
	|| CSocNetUser::isCurrentUserModuleAdmin(SITE_ID, false)
);

$arResult['LOG_ID_TOKEN'] = (
	$currentUserId > 0
		? (new Token($currentUserId))->generate($arParams['LOG_ID'])
		: ''
);

$this->IncludeComponentTemplate();
