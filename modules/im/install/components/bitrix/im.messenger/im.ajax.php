<?
if (!defined('IM_AJAX_INIT'))
{
	define("IM_AJAX_INIT", true);
	define("PUBLIC_AJAX_MODE", true);
	define("NO_KEEP_STATISTIC", "Y");
	define("NO_AGENT_STATISTIC","Y");
	define("NOT_CHECK_PERMISSIONS", true);
	require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
}
header('Content-Type: application/x-javascript; charset='.LANG_CHARSET);

IncludeModuleLangFile(__FILE__);

global $APPLICATION;
$APPLICATION->RestartBuffer();

// NOTICE
// Before execute next code, execute file /module/im/ajax_hit.php
// for skip onProlog events

if (!CModule::IncludeModule("im"))
{
	echo \Bitrix\Im\Common::objectEncode(Array('ERROR' => GetMessage('IM_MODULE_NOT_INSTALLED')));
	CMain::FinalActions();
	die();
}

if(!$USER->IsAuthorized())
{
	$USER->LoginByCookies();
}

if (intval($USER->GetID()) <= 0)
{
	// TODO need change AUTHORIZE ERROR callbacks
	//header("HTTP/1.0 401 Not Authorized");
	//header("Content-Type: application/x-javascript");
	//header("BX-Authorize: ".bitrix_sessid());

	echo \Bitrix\Im\Common::objectEncode(Array(
		'ERROR' => 'AUTHORIZE_ERROR',
		'BITRIX_SESSID' => bitrix_sessid()
	));
	CMain::FinalActions();
	die();
}
if (!$USER->IsJustAuthorized() && !check_bitrix_sessid())
{
	if (\Bitrix\Im\User::getInstance($USER->GetID())->isConnector())
	{
		echo \Bitrix\Im\Common::objectEncode(Array(
			'ERROR' => '',
			'REAL_ERROR' => 'SESSION_ERROR',
		));
	}
	else
	{
		echo \Bitrix\Im\Common::objectEncode(Array(
			'ERROR' => 'SESSION_ERROR',
			'BITRIX_SESSID' => bitrix_sessid(),
		));
	}
	CMain::FinalActions();
	die();
}

if (\Bitrix\Im\User::getInstance($USER->GetID())->isConnector())
{
	if (!(
		$_POST['IM_START_WRITING'] == 'Y' ||
		$_POST['IM_SEND_MESSAGE'] == 'Y' ||
		$_POST['IM_EDIT_MESSAGE'] == 'Y' ||
		$_POST['IM_LIKE_MESSAGE'] == 'Y' ||
		$_POST['IM_DELETE_MESSAGE'] == 'Y' ||
		$_POST['IM_READ_MESSAGE'] == 'Y' ||
		$_POST['IM_UNREAD_MESSAGE'] == 'Y' ||
		$_POST['IM_FILE_REGISTER'] == 'Y' ||
		$_POST['IM_FILE_UPLOAD'] == 'Y' ||
		$_POST['IM_FILE_UNREGISTER'] == 'Y' ||
		$_POST['IM_FILE_DELETE'] == 'Y' ||
		$_POST['IM_UPDATE_STATE'] == 'Y' ||
		$_POST['IM_LOAD_LAST_MESSAGE'] == 'Y' ||
		$_POST['IM_HISTORY_LOAD_MORE'] == 'Y' ||
		$_POST['IM_LOAD_CONTEXT_MESSAGE'] == 'Y' ||
		$_POST['IM_URL_ATTACH_DELETE'] == 'Y' ||
		$_POST['IM_LOAD_MESSAGE_BY_DATE'] == 'Y' ||
		$_POST['IM_LINES_VOTE_SEND'] == 'Y' ||
		$_POST['IM_OPEN_LINES_CLIENT'] == 'Y'
	))
	{
		echo \Bitrix\Im\Common::objectEncode(Array(
			'ERROR' => 'SCOPE_ERROR'
		));
		CMain::FinalActions();
		die();
	}
}

CIMContactList::SetOnline();

if (isset($_REQUEST["mobile_action"]) && $_POST['FOCUS'] == 'Y' && CModule::IncludeModule('mobile'))
{
	Bitrix\Mobile\User::setOnline();
}

if (isset($_POST['desktop_action']) && $_POST['desktop_action'] == 'Y')
{
	CIMMessenger::SetDesktopStatusOnline();
}

if (!function_exists('isImPostRequest'))
{
	function isImPostRequest(string $method): bool
	{
		if (!$_POST || !isset($_POST[$method]) || $_POST[$method] !== 'Y')
		{
			return false;
		}

		return true;
	}
}

if (isImPostRequest('IM_AVATAR_UPDATE'))
{
	$userId = $USER->GetId();
	$chatId = intval($_POST['CHAT_ID']);
	if (!\Bitrix\Im\Chat::isActionAllowed('chat' . $chatId, 'AVATAR'))
	{
		echo \Bitrix\Im\Common::objectEncode(Array(
			'ERROR' => 'UPLOAD_ERROR'
		));
	}
	else
	{
		$uploader = new \Bitrix\Main\UI\Uploader\Uploader(array(
			"allowUpload" => "I",
			"events" => array("onFileIsUploaded" => array("CIMDisk", "UploadAvatar")),
			"storage" => array("moduleId" => "im")
		));
		if (!$uploader->checkPost())
		{
			echo \Bitrix\Im\Common::objectEncode(Array(
				'ERROR' => 'UPLOAD_ERROR'
			));
		}
	}
}
else if (isImPostRequest('IM_FILE_UPLOAD'))
{
	CUtil::decodeURIComponent($_POST);
	$uploader = new \Bitrix\Main\UI\Uploader\Uploader(array(
		"allowUpload" => "A",
		"events" => array(
			"onFileIsUploaded" => array("CIMDisk", "UploadFile")
		),
		"storage" => array(
			"moduleId" => "im"
		)
	));
	if (!$uploader->checkPost())
	{
		echo \Bitrix\Im\Common::objectEncode(Array(
			'ERROR' => 'UPLOAD_ERROR'
		));
	}
}
else if (isImPostRequest('IM_FILE_REGISTER'))
{
	$errorMessage = '';
	CUtil::decodeURIComponent($_POST);
	$_POST['FILES'] = CUtil::JsObjectToPhp($_POST['FILES']);

	$result = CIMDisk::UploadFileRegister($_POST['CHAT_ID'], $_POST['FILES'], $_POST['TEXT'], $_POST['OL_SILENT'] == 'Y');
	if (!$result)
	{
		$errorMessage = 'ERROR';
	}

	if ($_POST['TEXT'])
	{
		$ar['MESSAGE'] = trim(str_replace(Array('[BR]', '[br]'), "\n", $_POST['TEXT']));
		$ar['MESSAGE'] = preg_replace("/\[DISK\=([0-9]+)\]/i", "", $ar['MESSAGE']);
		$ar['MESSAGE'] = \Bitrix\Im\Text::parse($ar['MESSAGE']);
	}
	else
	{
		$ar['MESSAGE'] = '';
	}

	echo \Bitrix\Im\Common::objectEncode(Array(
		'FILE_ID' => $result['FILE_ID'],
		'CHAT_ID' => $_POST['CHAT_ID'],
		'RECIPIENT_ID' => $_POST['RECIPIENT_ID'],
		'MESSAGE_TEXT' => $ar['MESSAGE'],
		'MESSAGE_ID' => $result['MESSAGE_ID'],
		'MESSAGE_TMP_ID' => $_POST['MESSAGE_TMP_ID'],
		'ERROR' => $errorMessage
	));
}
else if (isImPostRequest('IM_FILE_UNREGISTER'))
{
	$_POST['FILES'] = CUtil::JsObjectToPhp($_POST['FILES']);
	$_POST['MESSAGES'] = CUtil::JsObjectToPhp($_POST['MESSAGES']);

	$result = CIMDisk::UploadFileUnRegister($_POST['CHAT_ID'], $_POST['FILES'], $_POST['MESSAGES']);

	echo \Bitrix\Im\Common::objectEncode(Array(
		'ERROR' => !$result? 'ERROR': ''
	));
}
else if (isImPostRequest('IM_FILE_DELETE'))
{
	$result = CIMDisk::DeleteFile($_POST['CHAT_ID'], $_POST['FILE_ID']);

	echo \Bitrix\Im\Common::objectEncode(Array(
		'CHAT_ID' => $_POST['CHAT_ID'],
		'FILE_ID' => $_POST['FILE_ID'],
		'ERROR' => !$result? 'ERROR': ''
	));
}
else if (isImPostRequest('IM_FILE_SAVE_TO_DISK'))
{
	$result = CIMDisk::SaveToLocalDisk($_POST['FILE_ID']);

	echo \Bitrix\Im\Common::objectEncode(Array(
		'CHAT_ID' => $_POST['CHAT_ID'],
		'FILE_ID' => $_POST['FILE_ID'],
		'NEW_FILE_ID' => $result? $result['FILE']->getId(): 0,
		'ERROR' => !$result? 'ERROR': ''
	));
}
else if (isImPostRequest('IM_FILE_UPLOAD_FROM_DISK'))
{
	$errorMessage = '';

	CUtil::decodeURIComponent($_POST);
	$_POST['FILES'] = CUtil::JsObjectToPhp($_POST['FILES']);

	$result = CIMDisk::UploadFileFromDisk($_POST['CHAT_ID'], $_POST['FILES'], $_POST['MESSAGE'], [
		'LINES_SILENT_MODE' => $_POST['OL_SILENT'] == 'Y',
		//'SYMLINK' => true
	]);
	if (!$result)
	{
		$errorMessage = 'ERROR';
	}

	echo \Bitrix\Im\Common::objectEncode(Array(
		'FILES' => $result['FILES'],
		'CHAT_ID' => $_POST['CHAT_ID'],
		'RECIPIENT_ID' => $_POST['RECIPIENT_ID'],
		'MESSAGE_ID' => $result['MESSAGE_ID'],
		'MESSAGE_TMP_ID' => $_POST['MESSAGE_TMP_ID'],
		'ERROR' => $errorMessage
	));
}
else if (isImPostRequest('IM_HISTORY_FILES_LOAD'))
{
	$chatId = intval($_POST['CHAT_ID']);
	$historyPage = isset($_POST['PAGE_ID']) ? intval($_POST['PAGE_ID']) : 0;
	$historyPage = $historyPage>0? $historyPage: 1;

	$arFiles = CIMDisk::GetHistoryFiles($chatId, $historyPage);

	echo \Bitrix\Im\Common::objectEncode(Array(
		'CHAT_ID' => $chatId,
		'FILES' => $arFiles, // TODO remove this in next year 2022
		'FILE_LIST' => array_values($arFiles),
		'ERROR' => ''
	));
}
else if (isImPostRequest('IM_HISTORY_FILES_SEARCH'))
{
	CUtil::decodeURIComponent($_POST);

	$chatId = intval($_POST['CHAT_ID']);
	$arFiles = CIMDisk::GetHistoryFilesByName($chatId, $_POST['SEARCH']);

	echo \Bitrix\Im\Common::objectEncode(Array(
		'CHAT_ID' => $chatId,
		'FILES' => $arFiles,
		'ERROR' => ''
	));
}
else if (isImPostRequest('IM_UPDATE_STATE') || isImPostRequest('IM_UPDATE_STATE_LIGHT'))
{
	$arResult['LAST_UPDATE'] = (new \Bitrix\Main\Type\DateTime())->format(DateTimeInterface::RFC3339);

	$arResult["REVISION"] = \Bitrix\Im\Revision::getWeb();
	$arResult["MOBILE_REVISION"] = \Bitrix\Im\Revision::getMobile();
	$arResult["DISK_REVISION"] = COption::GetOptionString("disk", "disk_revision_api", -1);

	$arResult['SERVER_TIME'] = time();

	// Online
	$arOnline = CIMStatus::GetList();

	// Counters
	$arResult["COUNTERS"] = CUserCounter::GetValues($USER->GetID(), $_POST['SITE_ID']);

	if (CIMMail::IsExternalMailAvailable())
	{
		$arResult["MAIL_COUNTER"] = intval($arResult["COUNTERS"]["mail_unseen"]);
	}
	else if (CModule::IncludeModule("dav"))
	{
		// Exchange
		$ar = CDavExchangeMail::GetTicker($GLOBALS["USER"]);
		if ($ar !== null)
		{
			$arResult["MAIL_COUNTER"] = intval($ar["numberOfUnreadMessages"]);
		}
	}

	$arResult["INTRANET_USTAT_ONLINE_DATA"] = [];
	if (
		$_POST["IS_DESKTOP"] !== "Y"
		&& CModule::IncludeModule("intranet")
	)
	{
		$ustatOnline = new \Bitrix\Intranet\Component\UstatOnline;
		if (!$ustatOnline->isFullAnimationMode())
		{
			$arResult["INTRANET_USTAT_ONLINE_DATA"] = $ustatOnline->getCurrentOnlineUserData();
		}
	}

	$counters = \Bitrix\Im\Counter::get(null, ['JSON' => 'Y']);
	$counters['type']['mail'] = (int)$arResult["MAIL_COUNTER"];

	$isOperator = $_POST["IS_OPERATOR"] === 'Y';

	$recent = [];
	$getOriginalTextOption = isImPostRequest('IM_UPDATE_STATE') ? 'Y' : 'N';
	if (isset($_POST['RECENT_LAST_UPDATE']) && $_POST['RECENT_LAST_UPDATE'] !== 'N')
	{
		try
		{
			$lastUpdate = new \Bitrix\Main\Type\DateTime($_POST['RECENT_LAST_UPDATE'], DateTimeInterface::RFC3339);
			$recent = \Bitrix\Im\Recent::get(null, [
				'LAST_SYNC_DATE' => $lastUpdate,
				'SKIP_NOTIFICATION' => 'Y',
				'SKIP_OPENLINES' => ($isOperator? 'Y': 'N'),
				'GET_ORIGINAL_TEXT' => $getOriginalTextOption,
				'JSON' => 'Y'
			]);
		}
		catch (Exception $e){}
	}

	$linesList = [];
	if (isset($_POST['LINES_LAST_UPDATE']) && $_POST['LINES_LAST_UPDATE'] !== 'N')
	{
		try
		{
			$lastUpdate = new \Bitrix\Main\Type\DateTime($_POST['LINES_LAST_UPDATE'], DateTimeInterface::RFC3339);
			$linesList = \Bitrix\Im\Recent::get(null, [
				'LAST_SYNC_DATE' => $lastUpdate,
				'ONLY_OPENLINES' => 'Y',
				'GET_ORIGINAL_TEXT' => $getOriginalTextOption,
				'JSON' => 'Y'
			]);
		}
		catch (Exception $e){}
	}

	$arSend = [
		'REVISION' => $arResult["REVISION"],
		'MOBILE_REVISION' => $arResult["MOBILE_REVISION"],
		'DISK_REVISION' => $arResult["DISK_REVISION"],
		'RECENT' => $recent,
		'LINES_LIST' => $linesList,
		'COUNTERS' => $arResult["COUNTERS"],
		'CHAT_COUNTERS' => $counters,
		'NOTIFY_LAST_ID' => (new \Bitrix\Im\Notify())->getLastId(),
		'ONLINE' => !empty($arOnline)? $arOnline['users']: array(),
		'XMPP_STATUS' => CIMMessenger::CheckXmppStatusOnline()? 'Y':'N',
		'DESKTOP_STATUS' => CIMMessenger::CheckDesktopStatusOnline()? 'Y':'N',
		'INTRANET_USTAT_ONLINE_DATA' => $arResult["INTRANET_USTAT_ONLINE_DATA"],
		'SERVER_TIME' => time(),
		'LAST_UPDATE' => $arResult['LAST_UPDATE'],
		'ERROR' => ""
	];
	echo \Bitrix\Im\Common::objectEncode($arSend, true);
}
else if (isImPostRequest('IM_NOTIFY_LOAD'))
{
	$CIMNotify = new CIMNotify();
	$arNotify = $CIMNotify->GetUnreadNotify(Array('SPEED_CHECK' => 'N', 'USE_TIME_ZONE' => 'N'));
	if ($arNotify['result'])
	{
		$arSend['NOTIFY'] = $arNotify['notify'];
		$arSend['UNREAD_NOTIFY'] = $arNotify['unreadNotify'];
		$arSend['ERROR'] = '';

		if (count($arNotify['notify']))
		{
			$minNotify = min(array_keys($arNotify['notify']));
			if (
				$minNotify > 0
				&& (!isset($_POST['IM_AUTO_READ']) || $_POST['IM_AUTO_READ'] == 'Y')
			)
			{
				$CIMNotify->MarkNotifyRead($minNotify, true);
			}
		}
	}
	echo \Bitrix\Im\Common::objectEncode($arSend);
}
else if (isImPostRequest('IM_NOTIFY_HISTORY_LOAD_MORE'))
{
	if (CIMMessenger::IsBitrix24UserRestricted())
	{
		echo \Bitrix\Im\Common::objectEncode(Array(
			'ERROR' => GetMessage('IM_ACCESS_ERROR')
		));
		CMain::FinalActions();
		die();
	}

	$errorMessage = "";

	$CIMNotify = new CIMNotify();
	$arNotify = $CIMNotify->GetNotifyList(Array('PAGE' => $_POST['PAGE'], 'USE_TIME_ZONE' => 'N'));

	echo \Bitrix\Im\Common::objectEncode(Array(
		'NOTIFY' => $arNotify,
		'ERROR' => $errorMessage
	));
}
else if (isImPostRequest('IM_SEND_MESSAGE'))
{
	if (
		CIMMessenger::IsBitrix24UserRestricted()
		|| !\Bitrix\Im\Chat::isActionAllowed($_POST['RECIPIENT_ID'], 'SEND')
	)
	{
		echo \Bitrix\Im\Common::objectEncode(Array(
			'ERROR' => GetMessage('IM_ACCESS_ERROR')
		));
		CMain::FinalActions();
		die();
	}

	CUtil::decodeURIComponent($_POST);

	$insertID = 0;
	$errorMessage = "";
	if ($_POST['CHAT'] == 'Y' && mb_substr($_POST['RECIPIENT_ID'], 0, 4) == 'chat')
	{
		$userId = $USER->GetId();
		$chatId = intval(mb_substr($_POST['RECIPIENT_ID'], 4));
		if (CIMChat::GetGeneralChatId() == $chatId && !CIMChat::CanSendMessageToGeneralChat($userId))
		{
			$errorMessage = GetMessage('IM_ERROR_GROUP_CANCELED');
		}
		else
		{
			$ar = Array(
				"FROM_USER_ID" => $userId,
				"TO_CHAT_ID" => $chatId,
				"MESSAGE" 	 => $_POST['MESSAGE'],
				"SILENT_CONNECTOR" => $_POST['OL_SILENT'] == 'Y'?'Y':'N',
				"TEMPLATE_ID" => $_POST['ID'],
			);
			$insertID = CIMChat::AddMessage($ar);
		}
	}
	else if (mb_substr($_POST['RECIPIENT_ID'], 0, 4) != 'chat' && !\Bitrix\Im\User::getInstance($USER->GetID())->isConnector())
	{
		$ar = Array(
			"FROM_USER_ID" => intval($USER->GetID()),
			"TO_USER_ID" => intval($_POST['RECIPIENT_ID']),
			"MESSAGE" 	 => $_POST['MESSAGE'],
			"TEMPLATE_ID" => $_POST['ID'],
		);
		$insertID = CIMMessage::Add($ar);
	}
	else
	{
		$errorMessage = GetMessage('IM_ACCESS_ERROR');
	}

	if (!$insertID && !$errorMessage)
	{
		if ($e = $GLOBALS["APPLICATION"]->GetException())
			$errorMessage = $e->GetString();
		if ($errorMessage == '')
			$errorMessage = GetMessage('IM_UNKNOWN_ERROR');
	}

	if (!\CIMMessenger::IsMobileRequest())
	{
		CIMStatus::Set($USER->GetId(), Array('IDLE' => null));
	}

	$message = CIMMessenger::GetById($insertID, Array('WITH_FILES' => 'Y'));
	$arMessages[$insertID]['params'] = $message['PARAMS'] ?? null;

	$arMessages = CIMMessageLink::prepareShow($arMessages, Array($insertID => $message['PARAMS'] ?? null));

	$ar['MESSAGE'] = trim(str_replace(Array('[BR]', '[br]'), "\n", $_POST['MESSAGE']));
	$ar['MESSAGE'] = preg_replace("/\[DISK\=([0-9]+)\]/i", "", $ar['MESSAGE']);

	$userTzOffset = isset($_POST['USER_TZ_OFFSET'])? intval($_POST['USER_TZ_OFFSET']): CTimeZone::GetOffset();
	$arResult = Array(
		'TMP_ID' => $_POST['ID'],
		'ID' => $insertID,
		'CHAT_ID' => $message['CHAT_ID'] ?? null,
		'SEND_DATE' => new \Bitrix\Main\Type\DateTime(),
		'SEND_MESSAGE' => \Bitrix\Im\Text::parse($ar['MESSAGE']),
		'SEND_MESSAGE_PARAMS' => $arMessages[$insertID]['params'],
		'SEND_MESSAGE_FILES' => $message['FILES'] ?? null,
		'SENDER_ID' => intval($USER->GetID()),
		'RECIPIENT_ID' => $_POST['CHAT'] == 'Y'? htmlspecialcharsbx($_POST['RECIPIENT_ID']): intval($_POST['RECIPIENT_ID']),
		'OL_SILENT' => $_POST['OL_SILENT'],
		'ERROR' => $errorMessage
	);
	if (isset($_POST['MOBILE']))
	{
		$arFormat = Array(
			"today" => "today, ".GetMessage('IM_MESSAGE_FORMAT_TIME'),
			"" => GetMessage('IM_MESSAGE_FORMAT_DATE')
		);
		$arResult['SEND_DATE_FORMAT'] = FormatDate($arFormat, time()+$userTzOffset);
	}

	echo \Bitrix\Im\Common::objectEncode($arResult);
}
else if (isImPostRequest('IM_BOT_COMMAND'))
{
	CUtil::decodeURIComponent($_POST);

	$messageId = intval($_POST['MESSAGE_ID']);
	$userId = $USER->GetId();

	$errorMessage = 'ACCESS_DENIED';

	$orm = \Bitrix\Im\Model\MessageTable::getById($messageId);
	if($message = $orm->fetch())
	{
		$orm = \Bitrix\Im\Model\ChatTable::getById($message['CHAT_ID']);
		$chat = $orm->fetch();
		$relations = \CIMChat::GetRelationById($message['CHAT_ID'], false, true, false);
		if (isset($relations[$userId]))
		{
			if (mb_substr($_POST['DIALOG_ID'], 0, 4) == 'chat')
			{
				$messageFields = Array(
					"FROM_USER_ID" => $userId,
					"TO_CHAT_ID" => $message['CHAT_ID'],
					"MESSAGE"  => '/'.$_POST['COMMAND'].' '.$_POST['COMMAND_PARAMS'],
				);
			}
			else
			{
				$messageFields = Array(
					"FROM_USER_ID" => $userId,
					"TO_USER_ID" => intval($_POST['BOT_ID']),
					"MESSAGE"  => '/'.$_POST['COMMAND'].' '.$_POST['COMMAND_PARAMS'],
				);
			}
			$messageFields['MESSAGE_TYPE'] = $relations[$userId]['MESSAGE_TYPE'];
			$messageFields['AUTHOR_ID'] = $userId;

			$messageFields['COMMAND_CONTEXT'] = $_POST['COMMAND_CONTEXT'] === 'MENU'? 'MENU': 'KEYBOARD';
			$messageFields['CHAT_ENTITY_TYPE'] = $chat['ENTITY_TYPE'];
			$messageFields['CHAT_ENTITY_ID'] = $chat['ENTITY_ID'];

			$result = \Bitrix\Im\Command::onCommandAdd($messageId, $messageFields);
			$errorMessage = '';
		}
	}

	echo \Bitrix\Im\Common::objectEncode(Array(
		'ERROR' => $errorMessage
	));
}
else if (isImPostRequest('IM_EDIT_MESSAGE'))
{
	CUtil::decodeURIComponent($_POST);

	if(!CIMMessenger::Update($_POST['ID'], $_POST['MESSAGE']))
	{
		$arResult = Array(
			'ERROR' => 'CANT_EDIT_MESSAGE'
		);
	}
	else
	{
		$userTzOffset = isset($_POST['USER_TZ_OFFSET'])? intval($_POST['USER_TZ_OFFSET']): CTimeZone::GetOffset();

		$arResult = Array(
			'ID' => $insertID ?? 0,
			'MESSAGE' => \Bitrix\Im\Text::parse($_POST['MESSAGE']),
			'DATE' => new \Bitrix\Main\Type\DateTime(),
			'ERROR' => ''
		);
	}

	echo \Bitrix\Im\Common::objectEncode($arResult);
}
else if (isImPostRequest('IM_DELETE_MESSAGE'))
{
	$errorMessage = '';
	if(!CIMMessenger::Delete($_POST['ID']))
	{
		$errorMessage = 'CANT_EDIT_MESSAGE';
	}

	$arResult = Array(
		'ERROR' => $errorMessage
	);
	echo \Bitrix\Im\Common::objectEncode($arResult);
}
else if (isImPostRequest('IM_SHARE_MESSAGE'))
{
	$errorMessage = '';
	if(!CIMMessenger::Share($_POST['ID'], $_POST['TYPE'], $_POST['DATE']))
	{
		$errorMessage = 'CANT_SHARE_MESSAGE';
	}

	echo \Bitrix\Im\Common::objectEncode(Array(
		'ERROR' => $errorMessage
	));
}
else if (isImPostRequest('IM_URL_ATTACH_DELETE'))
{
	$errorMessage = '';

	$result = CIMMessenger::UrlAttachDelete($_POST['ID'], $_POST['ATTACH_ID']);

	$arResult = Array(
		'ERROR' => $errorMessage
	);
	echo \Bitrix\Im\Common::objectEncode($arResult);
}
else if (isImPostRequest('IM_LINES_VOTE_SEND'))
{
	CIMMessenger::LinesSessionVote($_POST['DIALOG_ID'], $_POST['MESSAGE_ID'], $_POST['RATING']);

	$arResult = Array(
		'ERROR' => ''
	);
	echo \Bitrix\Im\Common::objectEncode($arResult);
}
else if (isImPostRequest('IM_LIKE_MESSAGE'))
{
	$errorMessage = '';
	$result = CIMMessenger::Like($_POST['ID'], $_POST['ACTION']);
	if ($result === false)
		$errorMessage = 'WITHOUT_CHANGES';

	$arResult = Array(
		'LIKE' => $result,
		'ERROR' => $errorMessage
	);
	echo \Bitrix\Im\Common::objectEncode($arResult);
}
else if (isImPostRequest('IM_READ_MESSAGE'))
{
	if (CIMMessenger::IsBitrix24UserRestricted())
	{
		echo \Bitrix\Im\Common::objectEncode(Array(
			'ERROR' => GetMessage('IM_ACCESS_ERROR')
		));
		CMain::FinalActions();
		die();
	}

	$errorMessage = "";

	if (mb_substr($_POST['USER_ID'], 0, 4) == 'chat')
	{
		$CIMChat = new CIMChat();
		$CIMChat->SetReadMessage(intval(mb_substr($_POST['USER_ID'], 4)), (isset($_POST['LAST_ID']) && intval($_POST['LAST_ID'])>0 ? $_POST['LAST_ID']: null));
	}
	else
	{
		$CIMMessage = new CIMMessage();
		$CIMMessage->SetReadMessage($_POST['USER_ID'], (isset($_POST['LAST_ID']) && intval($_POST['LAST_ID'])>0 ? $_POST['LAST_ID']: null));
	}

	echo \Bitrix\Im\Common::objectEncode(Array(
		'USER_ID' => htmlspecialcharsbx($_POST['USER_ID']),
		'ERROR' => $errorMessage
	));
}
else if (isImPostRequest('IM_UNREAD_MESSAGE'))
{
	$errorMessage = "";
	if (mb_substr($_POST['USER_ID'], 0, 4) == 'chat')
	{
		$CIMChat = new CIMChat();
		$CIMChat->SetUnReadMessage(intval(mb_substr($_POST['USER_ID'], 4)), (isset($_POST['LAST_ID']) && intval($_POST['LAST_ID'])>0 ? $_POST['LAST_ID']: null));
	}
	else
	{
		$CIMMessage = new CIMMessage();
		$CIMMessage->SetUnReadMessage($_POST['USER_ID'], (isset($_POST['LAST_ID']) && intval($_POST['LAST_ID'])>0 ? $_POST['LAST_ID']: null));
	}
	echo \Bitrix\Im\Common::objectEncode(Array(
		'USER_ID' => htmlspecialcharsbx($_POST['USER_ID']),
		'ERROR' => $errorMessage
	));
}
else if (isImPostRequest('IM_LOAD_LAST_MESSAGE'))
{
	if (CIMMessenger::IsBitrix24UserRestricted())
	{
		echo \Bitrix\Im\Common::objectEncode(Array(
			'ERROR' => 'ACCESS_DENIED'
		));
		CMain::FinalActions();
		die();
	}

	$error = '';
	$arMessage = Array();

	$entityType = '';
	$entityId = '';
	if ($_POST['CHAT'] == 'Y')
	{
		if (mb_substr($_POST['USER_ID'], 0, 3) == 'crm')
		{
			$chatId = CIMChat::GetCrmChatId(mb_substr($_POST['USER_ID'], 4));
		}
		else if (mb_substr($_POST['USER_ID'], 0, 2) == 'sg')
		{
			$chatId = CIMChat::GetSonetGroupChatId(mb_substr($_POST['USER_ID'], 2));
		}
		else
		{
			$chatId = intval(mb_substr($_POST['USER_ID'], 4));
		}

		if ($chatId > 0)
		{
			$CIMChat = new CIMChat();
			$arMessage = $CIMChat->GetLastMessage($chatId, false, ($_POST['USER_LOAD'] == 'Y'? true: false), false);
		}
		else
		{
			$arMessage = false;
		}

		if (!$arMessage || $_POST['USER_LOAD'] == 'Y' && empty($arMessage['chat']) || isset($arMessage['chat'][$chatId]) && !in_array($arMessage['chat'][$chatId]['message_type'], Array(IM_MESSAGE_OPEN, IM_MESSAGE_CHAT, IM_MESSAGE_OPEN_LINE)))
		{
			$arMessage = Array();
			$error = 'ACCESS_DENIED';
		}
		else if (isset($arMessage['message']))
		{
			foreach ($arMessage['message'] as $id => $ar)
				$arMessage['message'][$id]['recipientId'] = 'chat'.$ar['recipientId'];

			$arMessage['usersMessage']['chat'.$chatId] = $arMessage['usersMessage'][$chatId];
			unset($arMessage['usersMessage'][$chatId]);
			if (isset($_POST['READ']) && $_POST['READ'] == 'Y')
				$CIMChat->SetReadMessage($chatId);

			$orm = \Bitrix\Im\Model\ChatTable::getById($chatId);
			$chatData = $orm->fetch();

			$diskFolderId = (int)$chatData['DISK_FOLDER_ID'];
			$entityType = $chatData['ENTITY_TYPE'];
			$entityId = $chatData['ENTITY_ID'];
		}
	}
	else
	{
		$networkUserId = 0;
		if (
			mb_substr($_POST['USER_ID'], 0, 12) == 'networkLines'
			&& CModule::IncludeModule('imbot')
		)
		{
			$userId = \Bitrix\ImBot\Bot\Network::join(mb_substr($_POST['USER_ID'], 12));
			if ($userId > 0)
			{
				$networkUserId = $_POST['USER_ID'];
				$_POST['USER_ID'] = $userId;
			}
		}
		else if (mb_substr($_POST['USER_ID'], 0, 7) == 'network')
		{
			$userId = \CIMContactList::PrepareUserId($_POST['USER_ID'], $_POST['SEARCH_MARK']);
			if ($userId > 0)
			{
				$networkUserId = $_POST['USER_ID'];
				$_POST['USER_ID'] = $userId;
			}
		}

		$chatId = 0;
		if (CIMContactList::AllowToSend(Array('TO_USER_ID' => $_POST['USER_ID'])))
		{
			$CIMMessage = new CIMMessage();
			$arMessage = $CIMMessage->GetLastMessage(intval($_POST['USER_ID']), false, ($_POST['USER_LOAD'] == 'Y'? true: false), false);
			if (isset($_POST['READ']) && $_POST['READ'] == 'Y')
				$CIMMessage->SetReadMessage(intval($_POST['USER_ID']));

			if (
				$_POST['USER_LOAD'] == 'Y'
				&& $_POST['USER_ID'] != $USER->GetId()
				&& $arMessage && isset($arMessage['users']) && count($arMessage['users']) <= 1
			)
			{
				$arMessage = Array();
				$error = 'ACCESS_DENIED';
			}
			else
			{
				$chatId = $arMessage['chatId'];
				if ($chatId <= 0)
				{
					$chatId = CIMMessage::GetChatId($USER->GetId(), $_POST['USER_ID']);
				}

				$orm = \Bitrix\Im\Model\ChatTable::getById($chatId);
				$chatData = $orm->fetch();

				if ($chatData === false)
				{
					$chatData = [
						'DISK_FOLDER_ID' => null,
						'ENTITY_TYPE' => null,
						'ENTITY_ID' => null,
					];
				}
				$diskFolderId = (int)($chatData['DISK_FOLDER_ID']);
				$entityType = $chatData['ENTITY_TYPE'];
				$entityId = $chatData['ENTITY_ID'];
			}
		}
		else
		{
			$arMessage = Array();
			$error = 'ACCESS_DENIED';
		}
	}

	if ($error == '')
	{
		$relation = \CIMChat::GetRelationById($chatId, false, true, false);

		$dialogId = $_POST['USER_ID'];
		$userId = $USER->GetId();
		foreach(GetModuleEvents("im", "OnLoadLastMessage", true) as $arEvent)
		{
			ExecuteModuleEventEx($arEvent, array($chatId, $dialogId, $entityType, $entityId, $userId));
		}
	}

	if (!\CIMMessenger::IsMobileRequest())
	{
		CIMStatus::Set($USER->GetId(), Array('IDLE' => null));
	}

	echo \Bitrix\Im\Common::objectEncode(Array(
		'REVISION' => \Bitrix\Im\Revision::getWeb(),
		'MOBILE_REVISION' => \Bitrix\Im\Revision::getMobile(),
		'CHAT_ID' => $chatId,
		'DISK_FOLDER_ID' => $diskFolderId,
		'USER_ID' => $_POST['CHAT'] == 'Y'? htmlspecialcharsbx($_POST['USER_ID']): intval($_POST['USER_ID']),
		'MESSAGE' => isset($arMessage['message'])? $arMessage['message']: Array(),
		'USERS_MESSAGE' => isset($arMessage['usersMessage'])? $arMessage['usersMessage']: Array(),
		'UNREAD_MESSAGE' => isset($arMessage['unreadMessage'])? $arMessage['unreadMessage']: Array(),
		'USERS' => isset($arMessage['users'])? $arMessage['users']: Array(),
		'USER_IN_GROUP' => isset($arMessage['userInGroup'])? $arMessage['userInGroup']: Array(),
		'CHAT' => isset($arMessage['chat'])? $arMessage['chat']: Array(),
		'USER_BLOCK_CHAT' => isset($arMessage['userChatBlockStatus'])? $arMessage['userChatBlockStatus']: Array(),
		'USER_IN_CHAT' => isset($arMessage['userInChat'])? $arMessage['userInChat']: Array(),
		'USER_LOAD' => $_POST['USER_LOAD'] == 'Y'? 'Y': 'N',
		'READED_LIST' => isset($arMessage['readedList'])? $arMessage['readedList']: Array(),
		'PHONES' => isset($arMessage['phones'])? $arMessage['phones']: Array(),
		'FILES' => isset($arMessage['files'])? $arMessage['files']: Array(),
		'LINES' => isset($arMessage['lines'])? $arMessage['lines']: Array(),
		'OPENLINES' => isset($arMessage['openlines'])? $arMessage['openlines']: Array(),
		'NETWORK_ID' => isset($networkUserId) && $networkUserId ? $networkUserId : '',
		'ERROR' => $error
	));
}
else if (isImPostRequest('IM_USER_DATA_LOAD'))
{
	$error = '';
	$arMessage = Array();
	$chatId = 0;
	if (CIMContactList::AllowToSend(Array('TO_USER_ID' => $_POST['USER_ID'])))
	{
		$ar = CIMContactList::GetUserData(array(
				'ID' => Array($_POST['USER_ID'], $USER->GetID()),
				'DEPARTMENT' => 'Y',
				'USE_CACHE' => 'N',
				'PHONES' => IsModuleInstalled('voximplant')? 'Y': 'N'
			)
		);
		$arMessage['users'] = $ar['users'];
		$arMessage['userInGroup']  = $ar['userInGroup'];
		$arMessage['phones']  = $ar['phones'];
		$chatId = CIMMessage::GetChatId($USER->GetId(), $_POST['USER_ID']);
	}
	else
	{
		$error = 'ACCESS_DENIED';
	}

	echo \Bitrix\Im\Common::objectEncode(Array(
		'CHAT_ID' => $chatId,
		'USER_ID' => intval($_POST['USER_ID']),
		'USERS' => isset($arMessage['users'])? $arMessage['users']: Array(),
		'USER_IN_GROUP' => isset($arMessage['userInGroup'])? $arMessage['userInGroup']: Array(),
		'PHONES' => isset($arMessage['phones'])? $arMessage['phones']: Array(),
		'ERROR' => $error
	));
}
else if (isImPostRequest('IM_HISTORY_LOAD'))
{
	$arMessage = Array();
	$chatId = 0;
	if (mb_substr($_POST['USER_ID'], 0, 4) == 'chat')
	{
		$chatId = intval(mb_substr($_POST['USER_ID'], 4));

		$CIMChat = new CIMChat();
		$arMessage = $CIMChat->GetLastMessage($chatId, false, ($_POST['USER_LOAD'] == 'Y'? true: false), false, false);
		if ($arMessage && isset($arMessage['message']))
		{
			foreach ($arMessage['message'] as $id => $ar)
				$arMessage['message'][$id]['recipientId'] = 'chat'.$ar['recipientId'];

			$arMessage['usersMessage']['chat'.$chatId] = $arMessage['usersMessage'][$chatId];
			unset($arMessage['usersMessage'][$chatId]);
		}
		$dialogId = 'chat'.$chatId;
	}
	else
	{
		$dialogId = intval($_POST['USER_ID']);
		if (CIMContactList::AllowToSend(Array('TO_USER_ID' => $dialogId)))
		{
			$CIMMessage = new CIMMessage();
			$arMessage = $CIMMessage->GetLastMessage($dialogId, false, ($_POST['USER_LOAD'] == 'Y'? true: false), false, false);

			$chatId = $arMessage['chatId'];
			if ($chatId <= 0)
			{
				$chatId = CIMMessage::GetChatId($USER->GetId(), $dialogId);
			}
		}
	}
	echo \Bitrix\Im\Common::objectEncode(Array(
		'CHAT_ID' => $chatId,
		'USER_ID' => $dialogId,
		'MESSAGE' => isset($arMessage['message'])? $arMessage['message']: Array(),
		'USERS_MESSAGE' => isset($arMessage['message'])? $arMessage['usersMessage']: Array(),
		'USERS' => isset($arMessage['users'])? $arMessage['users']: Array(),
		'USER_IN_GROUP' => isset($arMessage['userInGroup'])? $arMessage['userInGroup']: Array(),
		'CHAT' => isset($arMessage['chat'])? $arMessage['chat']: Array(),
		'USER_BLOCK_CHAT' => isset($arMessage['userChatBlockStatus'])? $arMessage['userChatBlockStatus']: Array(),
		'USER_IN_CHAT' => isset($arMessage['userInChat'])? $arMessage['userInChat']: Array(),
		'FILES' => isset($arMessage['files'])? $arMessage['files']: Array(),
		'ERROR' => ''
	));
}
else if (isImPostRequest('IM_HISTORY_LOAD_MORE'))
{
	$arMessage = Array();

	$CIMHistory = new CIMHistory(false, Array());
	if (mb_substr($_POST['USER_ID'], 0, 4) == 'chat')
	{
		$chatId = mb_substr($_POST['USER_ID'], 4);
		$arMessage = $CIMHistory->GetMoreChatMessage(intval($_POST['PAGE_ID']), $chatId, false);
		if (!empty($arMessage['message']))
		{
			foreach ($arMessage['message'] as $id => $ar)
				$arMessage['message'][$id]['recipientId'] = 'chat'.$ar['recipientId'];

			$arMessage['usersMessage']['chat'.$chatId] = $arMessage['usersMessage'][$chatId];
			unset($arMessage['usersMessage'][$chatId]);
		}
	}
	else
	{
		$allowToSend = Array('TO_USER_ID' => $_POST['USER_ID']);

		if (CIMContactList::AllowToSend($allowToSend))
		{
			$arMessage = $CIMHistory->GetMoreMessage(intval($_POST['PAGE_ID']), intval($_POST['USER_ID']), false, false);
		}
	}

	echo \Bitrix\Im\Common::objectEncode(Array(
		'CHAT_ID' => isset($arMessage['chatId'])? $arMessage['chatId']: 0,
		'MESSAGE' => isset($arMessage['message'])? $arMessage['message']: Array(),
		'USERS' => isset($arMessage['users'])? $arMessage['users']: Array(),
		'USER_IN_GROUP' => isset($arMessage['userInGroup'])? $arMessage['userInGroup']: Array(),
		'PHONES' => isset($arMessage['phones'])? $arMessage['phones']: Array(),
		'USERS_MESSAGE' => isset($arMessage['usersMessage'])? $arMessage['usersMessage']: Array(),
		'FILES' => isset($arMessage['files'])? $arMessage['files']: Array(),
		'ERROR' => ''
	));
}
else if (isImPostRequest('IM_LOAD_MESSAGE_BY_DATE'))
{
	$history = new \CIMHistory();
	$arMessage = $history->GetMessagesByDate($_POST['CHAT_ID'], $_POST['LAST_LOAD'], $_POST['FIRST_MESSAGE_ID'], false);

	echo \Bitrix\Im\Common::objectEncode(Array(
		'CHAT_ID' => isset($arMessage['chatId'])? $arMessage['chatId']: 0,
		'DIALOG_ID' => isset($arMessage['dialogId'])? $arMessage['dialogId']: 0,
		'MESSAGE' => isset($arMessage['message'])? $arMessage['message']: Array(),
		'USERS_MESSAGE' => isset($arMessage['usersMessage'])? $arMessage['usersMessage']: Array(),
		'UNREAD_MESSAGE' => isset($arMessage['unreadMessage'])? $arMessage['unreadMessage']: Array(),
		'DELETE_MESSAGE' => isset($arMessage['messageDelete'])? $arMessage['messageDelete']: Array(),
		'FILES' => isset($arMessage['files'])? $arMessage['files']: Array(),
		'USERS' => isset($arMessage['users'])? $arMessage['users']: Array(),
		'USER_IN_GROUP' => isset($arMessage['userInGroup'])? $arMessage['userInGroup']: Array(),
		'PHONES' => isset($arMessage['phones'])? $arMessage['phones']: Array(),
		'CHAT' => $arChat['chat'],
		'USER_IN_CHAT' => $arChat['userInChat'],
		'USER_BLOCK_CHAT' => $arChat['userChatBlockStatus'],
		'ERROR' => ''
	));
}
else if (isImPostRequest('IM_LOAD_CONTEXT_MESSAGE'))
{
	if (isset($_POST['PREVIOUS']))
	{
		$previous = 20;
		$next = 0;
	}
	else if (isset($_POST['NEXT']))
	{
		$previous = 0;
		$next = 20;
	}
	else
	{
		$previous = 10;
		$next = 10;
	}

	$CIMHistory = new CIMHistory();
	$arMessage = $CIMHistory->GetRelatedMessages(intval($_POST['MESSAGE_ID']), $previous, $next, false);

	echo \Bitrix\Im\Common::objectEncode(Array(
		'CHAT_ID' => isset($arMessage['chatId'])? $arMessage['chatId']: 0,
		'DIALOG_ID' => isset($arMessage['dialogId'])? $arMessage['dialogId']: 0,
		'MESSAGE' => isset($arMessage['message'])? $arMessage['message']: Array(),
		'USERS_MESSAGE' => isset($arMessage['usersMessage'])? $arMessage['usersMessage']: Array(),
		'FILES' => isset($arMessage['files'])? $arMessage['files']: Array(),
		'USERS' => isset($arMessage['users'])? $arMessage['users']: Array(),
		'USER_IN_GROUP' => isset($arMessage['userInGroup'])? $arMessage['userInGroup']: Array(),
		'PHONES' => isset($arMessage['phones'])? $arMessage['phones']: Array(),
		'ERROR' => ''
	));
}
else if (isImPostRequest('IM_HISTORY_REMOVE_ALL'))
{
	$errorMessage = "";

	$CIMHistory = new CIMHistory();
	if (mb_substr($_POST['USER_ID'], 0, 4) == 'chat')
		$CIMHistory->HideAllChatMessage(mb_substr($_POST['USER_ID'], 4));
	else
		$CIMHistory->RemoveAllMessage($_POST['USER_ID']);

	echo \Bitrix\Im\Common::objectEncode(Array(
		'USER_ID' => htmlspecialcharsbx($_POST['USER_ID']),
		'ERROR' => $errorMessage
	));
}
else if (isImPostRequest('IM_HISTORY_REMOVE_MESSAGE'))
{
	$errorMessage = "";

	$CIMHistory = new CIMHistory();
	$CIMHistory->RemoveMessage($_POST['MESSAGE_ID']);

	echo \Bitrix\Im\Common::objectEncode(Array(
		'MESSAGE_ID' => intval($_POST['MESSAGE_ID']),
		'ERROR' => $errorMessage
	));
}
else if (isImPostRequest('IM_HISTORY_SEARCH'))
{
	CUtil::decodeURIComponent($_POST);

	$CIMHistory = new CIMHistory();
	if (mb_substr($_POST['USER_ID'], 0, 4) == 'chat')
	{
		$chatId = mb_substr($_POST['USER_ID'], 4);
		$arMessage = $CIMHistory->SearchChatMessage($_POST['SEARCH'], $chatId, false);
		if (!empty($arMessage['message']))
		{
			foreach ($arMessage['message'] as $id => $ar)
				$arMessage['message'][$id]['recipientId'] = 'chat'.$ar['recipientId'];

			$arMessage['usersMessage']['chat'.$chatId] = $arMessage['usersMessage'][$chatId];
			unset($arMessage['usersMessage'][$chatId]);
		}
	}
	else
	{
		$arMessage = $CIMHistory->SearchMessage($_POST['SEARCH'], intval($_POST['USER_ID']), false, false);
	}

	echo \Bitrix\Im\Common::objectEncode(Array(
		'CHAT_ID' => $arMessage['chatId'],
		'MESSAGE' => $arMessage['message'],
		'FILES' => $arMessage['files'],
		'USERS_MESSAGE' => $arMessage['usersMessage'],
		'USER_ID' => htmlspecialcharsbx($_POST['USER_ID']),
		'ERROR' => ''
	));
}
else if (isImPostRequest('IM_HISTORY_DATE_SEARCH'))
{
	$CIMHistory = new CIMHistory();
	if (mb_substr($_POST['USER_ID'], 0, 4) == 'chat')
	{
		$chatId = mb_substr($_POST['USER_ID'], 4);
		$arMessage = $CIMHistory->SearchDateChatMessage($_POST['DATE'], $chatId, false);
		if (!empty($arMessage['message']))
		{
			foreach ($arMessage['message'] as $id => $ar)
				$arMessage['message'][$id]['recipientId'] = 'chat'.$ar['recipientId'];

			$arMessage['usersMessage']['chat'.$chatId] = $arMessage['usersMessage'][$chatId];
			unset($arMessage['usersMessage'][$chatId]);
		}
	}
	else
	{
		$arMessage = $CIMHistory->SearchDateMessage($_POST['DATE'], intval($_POST['USER_ID']), false, false);
	}

	echo \Bitrix\Im\Common::objectEncode(Array(
		'CHAT_ID' => $arMessage['chatId'],
		'MESSAGE' => $arMessage['message'],
		'FILES' => $arMessage['files'],
		'USERS_MESSAGE' => $arMessage['usersMessage'],
		'USER_ID' => htmlspecialcharsbx($_POST['USER_ID']),
		'ERROR' => ''
	));
}
else if (isImPostRequest('IM_CONTACT_LIST_SEARCH'))
{
	$enabled = false;
	if (!IsModuleInstalled('b24network'))
	{
		$enabled = true;
	}
	else if (!Bitrix\Im\User::getInstance()->isExtranet() && CModule::IncludeModule('socialservices'))
	{
		$network = new \Bitrix\Socialservices\Network();
		$enabled = $network->isEnabled();

		if ($enabled)
		{
			$query = CBitrix24NetTransport::init();
			if(!$query)
			{
				$enabled = false;
			}
		}
	}

	if ($enabled)
	{
		CUtil::decodeURIComponent($_POST);

		$CIMContactList = new CIMContactList();
		$arContactList = $CIMContactList->SearchUsers($_POST['SEARCH']);

		echo \Bitrix\Im\Common::objectEncode(Array(
			'USERS' => $arContactList['users'],
			'USER_ID' => htmlspecialcharsbx($_POST['USER_ID']),
			'ERROR' => ''
		));
	}
	else
	{
		echo \Bitrix\Im\Common::objectEncode(Array('ERROR' => 'DISABLED_FUNCTION'));
	}
}
else if (isImPostRequest('IM_CONTACT_LIST'))
{
	$CIMContactList = new CIMContactList();
	$arContactList = $CIMContactList->GetList([
		'LOAD_USERS' => COption::GetOptionString("im", 'contact_list_load')? 'Y': 'N'
	]);

	echo \Bitrix\Im\Common::objectEncode(Array(
		'USER_ID' => $USER->GetId(),
		'USERS' => $arContactList['users'],
		'GROUPS' => $arContactList['groups'],
		'CHATS' => $arContactList['chats'],
		'PHONES' => $arContactList['phones'],
		'USER_IN_GROUP' => $arContactList['userInGroup'],
		'ERROR' => ''
	));
}
else if (isImPostRequest('IM_RECENT_LIST'))
{
	$ar = CIMContactList::GetRecentList(Array(
		'USE_TIME_ZONE' => 'N',
		'USE_SMILES' => 'N'
	));
	$arRecent = Array();
	$arUsers = Array();
	$arChat = Array();
	foreach ($ar as $userId => $value)
	{
		if ($value['TYPE'] == IM_MESSAGE_CHAT || $value['TYPE'] == IM_MESSAGE_OPEN || $value['TYPE'] == IM_MESSAGE_OPEN_LINE)
		{
			$arChat[$value['CHAT']['id']] = $value['CHAT'];
			$value['MESSAGE']['userId'] = $userId;
			$value['MESSAGE']['recipientId'] = $userId;
		}
		else
		{
			$value['MESSAGE']['userId'] = $userId;
			$value['MESSAGE']['recipientId'] = $userId;
			$arUsers[$value['USER']['id']] = $value['USER'];
		}
		$arRecent[] = $value['MESSAGE'];
	}

	$arSmile = CIMMessenger::PrepareSmiles();

	$arResult['SMILE'] = $arSmile['SMILE'];
	$arResult['SMILE_SET'] = $arSmile['SMILE_SET'];

	$arResult['NOTIFY_BLOCKED'] = CIMSettings::GetSimpleNotifyBlocked();

	echo \Bitrix\Im\Common::objectEncode(Array(
		'USER_ID' => $USER->GetId(),
		'RECENT' => $arRecent,
		'USERS' => $arUsers,
		'CHAT' => $arChat,
		'NOTIFY_BLOCKED' => $arResult['NOTIFY_BLOCKED'],
		'SMILE' => !empty($arSmile['SMILE'])? $arSmile['SMILE']: false,
		'SMILE_SET' => !empty($arSmile['SMILE_SET'])? $arSmile['SMILE_SET']: false,
		'ERROR' => ''
	));

}
else if (isImPostRequest('IM_NOTIFY_READ'))
{
	$errorMessage = "";

	$CIMNotify = new CIMNotify();
	$CIMNotify->MarkNotifyRead($_POST['ID'], true);

	echo \Bitrix\Im\Common::objectEncode(Array(
		'ERROR' => $errorMessage
	));
}
else if (isImPostRequest('IM_NOTIFY_VIEW'))
{
	$errorMessage = "";

	$CIMNotify = new CIMNotify();
	if ($_POST['READ'] == 'N')
	{
		$CIMNotify->MarkNotifyUnRead($_POST['ID']);
	}
	else
	{
		$CIMNotify->MarkNotifyRead($_POST['ID']);
	}

	echo \Bitrix\Im\Common::objectEncode(Array(
		'ERROR' => $errorMessage
	));
}
else if (isImPostRequest('IM_NOTIFY_CONFIRM'))
{
	$errorMessage = "";

	$CIMNotify = new CIMNotify();
	$result = $CIMNotify->Confirm($_POST['NOTIFY_ID'], $_POST['NOTIFY_VALUE']);

	echo \Bitrix\Im\Common::objectEncode(Array(
		'NOTIFY_ID' => intval($_POST['NOTIFY_ID']),
		'NOTIFY_VALUE' => $_POST['NOTIFY_VALUE'],
		'MESSAGES' => $result,
		'ERROR' => $errorMessage
	));
}
else if (isImPostRequest('IM_NOTIFY_ANSWER'))
{
	CUtil::decodeURIComponent($_POST);

	$errorMessage = "";

	$CIMNotify = new CIMNotify();
	$result = $CIMNotify->Answer($_POST['NOTIFY_ID'], $_POST['NOTIFY_ANSWER']);

	echo \Bitrix\Im\Common::objectEncode(Array(
		'MESSAGES' => $result,
		'ERROR' => $errorMessage
	));
}
else if (isImPostRequest('IM_NOTIFY_BLOCK_TYPE'))
{
	$errorMessage = "";

	$arSettings = Array(
		'site|'.$_POST['BLOCK_TYPE'] => $_POST['BLOCK_RESULT'] == 'Y'? false: true,
		'xmpp|'.$_POST['BLOCK_TYPE'] => $_POST['BLOCK_RESULT'] == 'Y'? false: true,
		'email|'.$_POST['BLOCK_TYPE'] => $_POST['BLOCK_RESULT'] == 'Y'? false: true,
	);
	CIMSettings::SetSetting(CIMSettings::NOTIFY, $arSettings);

	echo \Bitrix\Im\Common::objectEncode(Array(
		'ERROR' => $errorMessage
	));
}
else if (isImPostRequest('IM_NOTIFY_REMOVE'))
{
	$errorMessage = "";

	$CIMNotify = new CIMNotify();
	$CIMNotify->DeleteWithCheck($_POST['NOTIFY_ID']);

	echo \Bitrix\Im\Common::objectEncode(Array(
		'NOTIFY_ID' => intval($_POST['NOTIFY_ID']),
		'ERROR' => $errorMessage
	));
}
else if (isImPostRequest('IM_NOTIFY_GROUP_REMOVE'))
{
	$errorMessage = "";

	$CIMNotify = new CIMNotify();
	if ($arNotify = $CIMNotify->GetNotify($_POST['NOTIFY_ID']))
		CIMNotify::DeleteByTag($arNotify['NOTIFY_TAG']);

	echo \Bitrix\Im\Common::objectEncode(Array(
		'NOTIFY_ID' => intval($_POST['NOTIFY_ID']),
		'ERROR' => $errorMessage
	));
}
else if (isImPostRequest('IM_RECENT_HIDE'))
{
	\CIMContactList::DialogHide($_POST['DIALOG_ID']);

	echo \Bitrix\Im\Common::objectEncode(Array(
		'USER_ID' => $_POST['DIALOG_ID'],
		'ERROR' => ''
	));
}
else if (isImPostRequest('IM_CHAT_ADD'))
{
	$_POST['USERS'] = CUtil::JsObjectToPhp($_POST['USERS']);

	$errorMessage = "";
	$chatId = 0;
	$alias = null;
	if ($_POST['TYPE'] != 'open' && !is_array($_POST['USERS']))
	{
		$errorMessage = GetMessage('IM_UNKNOWN_ERROR');
	}
	else
	{
		CUtil::decodeURIComponent($_POST);

		$entityType = '';

		$type = IM_MESSAGE_CHAT;
		if ($_POST['TYPE'] == 'open')
		{
			$type = IM_MESSAGE_OPEN;
		}
		else if ($_POST['TYPE'] == 'videoconf')
		{
			$entityType = 'VIDEOCONF';
		}

		if (\Bitrix\Im\User::getInstance()->isExtranet())
		{
			$_POST['USERS'] = \Bitrix\Im\Integration\Socialnetwork\Extranet::filterUserList($_POST['USERS']);
		}

		$CIMChat = new CIMChat();
		$chatId = $CIMChat->Add(Array(
			'TYPE' => $type,
			'USERS' => $_POST['USERS'],
			'TITLE' => $_POST['TITLE'],
			'MESSAGE' => $_POST['MESSAGE'],
			'ENTITY_TYPE' => $entityType,
			'SEARCH_MARK' => $_POST['SEARCH_MARK'] ?? null,
			'AVATAR' => $_POST['AVATAR']
		));
		if ($chatId)
		{
			if ($entityType === 'VIDEOCONF')
			{
				$alias = \Bitrix\Im\Alias::getByEntity('VIDEOCONF', $chatId);
			}
		}
		else
		{
			if ($e = $GLOBALS["APPLICATION"]->GetException())
				$errorMessage = $e->GetString();
		}
	}

	echo \Bitrix\Im\Common::objectEncode([
		'CHAT_ID' => intval($chatId),
		'PUBLIC_LINK' => $alias? $alias['LINK']: '',
		'ERROR' => $errorMessage
	]);
}
else if (isImPostRequest('IM_CHAT_EXTEND'))
{
	$_POST['USERS'] = CUtil::JsObjectToPhp($_POST['USERS']);

	$errorMessage = "";
	$userId = $USER->GetId();
	$chatId = intval($_POST['CHAT_ID']);
	if (!\Bitrix\Im\Chat::isActionAllowed('chat' . $chatId, 'EXTEND'))
	{
		$errorMessage = GetMessage('IM_ACCESS_ERROR');
	}
	else
	{
		if (\Bitrix\Im\User::getInstance()->isExtranet())
		{
			$_POST['USERS'] = \Bitrix\Im\Integration\Socialnetwork\Extranet::filterUserList($_POST['USERS']);
		}

		$CIMChat = new CIMChat();
		$result = $CIMChat->AddUser($_POST['CHAT_ID'], $_POST['USERS'], $_POST['HISTORY'] != 'Y');
		if (!$result)
		{
			if ($e = $GLOBALS["APPLICATION"]->GetException())
				$errorMessage = $e->GetString();
		}
	}
	echo \Bitrix\Im\Common::objectEncode(Array(
		'ERROR' => $errorMessage
	));
}
else if (isImPostRequest('IM_CHAT_JOIN'))
{
	$CIMChat = new CIMChat();
	$result = $CIMChat->Join($_POST['CHAT_ID']);
}
else if (isImPostRequest('IM_PARENT_CHAT_JOIN'))
{
	$CIMChat = new CIMChat();
	$result = $CIMChat->JoinParent($_POST['CHAT_ID'], $_POST['MESSAGE_ID']);
}
else if (isImPostRequest('IM_CHAT_LEAVE'))
{
	$userId = $USER->GetId();
	$chatId = intval($_POST['CHAT_ID']);
	if (!\Bitrix\Im\Chat::isActionAllowed('chat' . $chatId, 'LEAVE'))
	{
		$result = false;
	}
	else
	{
		$CIMChat = new CIMChat();
		$result = $CIMChat->DeleteUser($chatId, intval($_POST['USER_ID']) > 0? intval($_POST['USER_ID']): $USER->GetID());
	}

	echo \Bitrix\Im\Common::objectEncode(Array(
		'CHAT_ID' => intval($_POST['CHAT_ID']),
		'USER_ID' => intval($_POST['USER_ID']),
		'ERROR' => $result? '': 'ACCESS_ERROR'
	));
}
else if (isImPostRequest('IM_CHAT_MUTE'))
{
	$result = false;

	if (\Bitrix\Im\Chat::isActionAllowed('chat' . $_POST['CHAT_ID'], 'MUTE'))
	{
		$CIMChat = new CIMChat();
		$result = $CIMChat->MuteNotify($_POST['CHAT_ID'], $_POST['MUTE'] == 'Y');
	}

	echo \Bitrix\Im\Common::objectEncode(Array(
		'CHAT_ID' => intval($_POST['CHAT_ID']),
		'ERROR' => $result? '': 'ACCESS_ERROR'
	));
}
else if (isImPostRequest('IM_CHAT_RENAME'))
{
	$userId = $USER->GetId();
	$chatId = intval($_POST['CHAT_ID']);
	$error = '';
	if (!\Bitrix\Im\Chat::isActionAllowed('chat' . $chatId, 'RENAME'))
	{
		$error = 'ACTION_DISABLED';
	}
	else
	{
		CUtil::decodeURIComponent($_POST);

		$CIMChat = new CIMChat();
		$CIMChat->Rename($chatId, $_POST['CHAT_TITLE']);
	}

	echo \Bitrix\Im\Common::objectEncode(Array(
		'CHAT_ID' => intval($_POST['CHAT_ID']),
		'CHAT_TITLE' => $_POST['CHAT_TITLE'],
		'ERROR' => $error
	));
}
else if (isImPostRequest('IM_CRM_SELECTOR'))
{
	if (CModule::IncludeModule('crm'))
	{
		ob_start();
		$APPLICATION->IncludeComponent(
			'bitrix:crm.entity.selector.ajax',
			'.default',
			array(
				"MULTIPLE" => $_REQUEST['multiple'] == 'Y' ? 'Y' : 'N',
				'VALUE' => $_REQUEST['value'],
				'ENTITY_TYPE' => $_REQUEST['entityType'],
				'NAME' => 'olCrmSelector',
			),
			null,
			array('HIDE_ICONS' => 'Y')
		);
		$arResult['HTML'] = ob_get_contents();
		ob_end_clean();
	}
	else
	{
		$arResult['ERROR'] = 'ACCESS_DENIED';
	}

	echo \Bitrix\Im\Common::objectEncode($arResult);

}
else if (isImPostRequest('IM_CHAT_DATA_LOAD'))
{
	CUtil::decodeURIComponent($_POST);

	$chatId = $_POST['CHAT_ID'];

	$arChat = CIMChat::GetChatData(array(
		'ID' => $chatId,
		'USE_CACHE' => 'Y',
		'USER_ID' => $USER->GetId()
	));

	$arUser = CIMContactList::GetUserData(Array(
		'ID' => $arChat['userInChat'][$chatId]
	));

	if (!in_array($USER->GetId(), $arChat['userInChat'][$chatId]))
	{
		if (($arChat['chat'][$chatId]['message_type'] == IM_MESSAGE_OPEN || $arChat['chat'][$chatId]['messageType'] == IM_MESSAGE_OPEN_LINE) && CModule::IncludeModule("pull"))
		{
			CPullWatch::Add($USER->GetId(), 'IM_PUBLIC_'.$chatId, true);
		}
	}

	echo \Bitrix\Im\Common::objectEncode(Array(
		'CHAT' => $arChat['chat'],
		'CHAT_ID' => $_POST['CHAT_ID'],
		'LINES' => $arChat['lines'],
		'USER_IN_CHAT' => $arChat['userInChat'],
		'USER_BLOCK_CHAT' => $arChat['userChatBlockStatus'],
		'USERS' => isset($arUser['users'])? $arUser['users']: Array(),
		'USER_IN_GROUP' => isset($arUser['userInGroup'])? $arUser['userInGroup']: Array(),
		'ERROR' => ''
	));
}
else if (isImPostRequest('IM_GET_EXTERNAL_DATA'))
{
	$error = '';
	$arResult = Array(
		'TS' => $_POST['TS'],
		'TYPE' => $_POST['TYPE'],
		'ERROR' => ''
	);
	$arMessage = Array();

	if ($_POST['TYPE'] == 'user')
	{
		$arResult['USER_ID'] = intval($_POST['USER_ID']);
		if (CIMContactList::AllowToSend(Array('TO_USER_ID' => $_POST['USER_ID'])))
		{
			$ar = CIMContactList::GetUserData(array(
				'ID' => Array($_POST['USER_ID']),
				'DEPARTMENT' => 'Y',
				'USE_CACHE' => 'N',
				'PHONES' => IsModuleInstalled('voximplant')? 'Y': 'N'
			));
			$arResult['USERS'] = isset($ar['users'])? $ar['users']: Array();
			$arResult['USER_IN_GROUP'] = isset($ar['userInGroup'])? $ar['userInGroup']: Array();
			$arResult['PHONES'] = isset($ar['phones'])? $ar['phones']: Array();
		}
		else
		{
			$arResult['ERROR'] = 'ACCESS_DENIED';
		}
	}
	else if ($_POST['TYPE'] == 'chat')
	{
		$chatId = intval($_POST['CHAT_ID']);

		$arChat = CIMChat::GetChatData(array(
			'ID' => $chatId,
			'USE_CACHE' => 'Y',
			'USER_ID' => $USER->GetId()
		));

		if ($arChat['chat'][$chatId])
		{
			$arResult['CHAT_ID'] = $chatId;
			$arResult['CHAT'] = $arChat['chat'];
			$arResult['LINES'] = $arChat['lines'];
			$arResult['USER_IN_CHAT'] = $arChat['userInChat'];
			$arResult['USER_BLOCK_CHAT'] = $arChat['userChatBlockStatus'];
		}
		else
		{
			$arResult['ERROR'] = 'ACCESS_DENIED';
		}
	}
	else if ($_POST['TYPE'] == 'phoneCallHistory')
	{
		if (CModule::IncludeModule('voximplant'))
		{
			$arResult['HISTORY_ID'] = intval($_POST['HISTORY_ID']);
			$history = CVoxImplantHistory::GetForPopup($arResult['HISTORY_ID']);
			if ($history && $history['PORTAL_USER_ID'] == $USER->GetId())
			{
				if ($history['CALL_RECORD_HREF'] <> '')
				{
					ob_start();
					$APPLICATION->IncludeComponent(
						"bitrix:player",
						"",
						Array(
							"PROVIDER" => "sound",
							"PLAYER_TYPE" => "flv",
							"CHECK_FILE" => "N",
							"USE_PLAYLIST" => "N",
							"PATH" => $history["CALL_RECORD_HREF"],
							"WIDTH" => 233,
							"HEIGHT" => 24,
							"PREVIEW" => false,
							"LOGO" => false,
							"FULLSCREEN" => "N",
							"SKIN_PATH" => "/bitrix/components/bitrix/player/mediaplayer/skins",
							"SKIN" => "",
							"CONTROLBAR" => "bottom",
							"WMODE" => "transparent",
							"WMODE_WMV" => "windowless",
							"HIDE_MENU" => "N",
							"SHOW_CONTROLS" => "N",
							"SHOW_STOP" => "Y",
							"SHOW_DIGITS" => "Y",
							"CONTROLS_BGCOLOR" => "FFFFFF",
							"CONTROLS_COLOR" => "000000",
							"CONTROLS_OVER_COLOR" => "000000",
							"SCREEN_COLOR" => "000000",
							//"FILE_DURATION" => "30",
							"AUTOSTART" => "N",
							"REPEAT" => "N",
							"VOLUME" => "90",
							"DISPLAY_CLICK" => "play",
							"MUTE" => "N",
							"HIGH_QUALITY" => "N",
							"ADVANCED_MODE_SETTINGS" => "Y",
							"BUFFER_LENGTH" => "10",
							"DOWNLOAD_LINK" => false,
							"DOWNLOAD_LINK_TARGET" => "_self",
							"ALLOW_SWF" => "N",
							"ADDITIONAL_PARAMS" => array(
								'LOGO' => false,
								'NUM' => false,
								'HEIGHT_CORRECT' => false,
							),
							"PLAYER_ID" => "bitrix_vi_record_".$arResult['HISTORY_ID']
						),
						false,
						Array("HIDE_ICONS" => "Y")
					);
					$history['CALL_RECORD_HTML'] = ob_get_contents();
					ob_end_clean();
					unset($history['CALL_RECORD_HREF']);

				}
				foreach ($history as $key => $value)
				{
					$arResult[$key] = $value;
				}
			}
			else
			{
				$arResult['ERROR'] = 'ACCESS_DENIED';
			}
		}
		else
		{
			$arResult['ERROR'] = 'ACCESS_DENIED';
		}
	}

	echo \Bitrix\Im\Common::objectEncode($arResult);
}
else if (isImPostRequest('IM_CALL'))
{
	$userId = intval($USER->GetId());
	$chatId = intval($_POST['CHAT_ID']) ?: CIMMessage::GetChatId($userId, intval($_POST['USER_ID']));

	$errorMessage = "";
	if ($_POST['COMMAND'] == 'invite')
	{
		if ($_POST['CHAT'] != 'Y')
			$chatId = CIMMessage::GetChatId($userId, intval($_POST['CHAT_ID']));

		$arCallData = CIMCall::Invite(Array(
			'CHAT_ID' => $chatId,
			'USER_ID' => $userId,
			'RECIPIENT_ID' => $_POST['CHAT'] != 'Y'? intval($_POST['CHAT_ID']): 0,
			'VIDEO' => $_POST['VIDEO'],
			'MOBILE' => $_POST['MOBILE'],
		));
		if (!$arCallData)
		{
			if ($e = $GLOBALS["APPLICATION"]->GetException())
				$errorMessage = $e->GetString();

			echo \Bitrix\Im\Common::objectEncode(Array(
				'ERROR' => $errorMessage
			));
		}
		else
		{
			echo \Bitrix\Im\Common::objectEncode(Array(
				'CHAT_ID' => $arCallData['CHAT_ID'],
				'USERS' => $arCallData['USER_DATA']['USERS'],
				'USERS_CONNECT' => isset($arCallData['USERS_CONNECT'])? $arCallData['USERS_CONNECT']: array(),
				'HR_PHOTO' => $arCallData['USER_DATA']['HR_PHOTO'],
				'CALL_VIDEO' => $arCallData['STATUS_TYPE'] == IM_CALL_VIDEO,
				'CALL_TO_GROUP' => $arCallData['CALL_TO_GROUP'],
				'CALL_ENABLED' => $arCallData['STATUS_TYPE'] != IM_CALL_NONE,
				'ERROR' => $errorMessage
			));
		}
	}
	else if ($_POST['COMMAND'] == 'wait')
	{
		CIMCall::Wait(Array(
			'CHAT_ID' => $chatId,
			'USER_ID' => $userId,
		));
	}
	else if ($_POST['COMMAND'] == 'reconnect')
	{
		CIMCall::Command($chatId, $_POST['RECIPIENT_ID'], 'reconnect', Array());
	}
	else if ($_POST['COMMAND'] == 'answer')
	{
		CIMCall::Answer(Array(
			'CHAT_ID' => $chatId,
			'USER_ID' => $userId,
			'CALL_TO_GROUP' => $_POST['CALL_TO_GROUP'] == 'Y',
			'MOBILE' => $_POST['MOBILE'],
		));
	}
	else if ($_POST['COMMAND'] == 'start')
	{
		CIMCall::Start(Array(
			'CHAT_ID' => $chatId,
			'USER_ID' => $userId,
			'RECIPIENT_ID' => intval($_POST['RECIPIENT_ID']),
			'CALL_TO_GROUP' => $_POST['CALL_TO_GROUP'] == 'Y',
		));
	}
	else if (in_array($_POST['COMMAND'], Array(IM_CALL_END_DECLINE, IM_CALL_END_TIMEOUT, IM_CALL_END_BUSY, IM_CALL_END_OFFLINE, IM_CALL_END_ACCESS)))
	{
		$arParams = Array(
			'CHAT_ID' => $chatId,
			'USER_ID' => $userId,
			'RECIPIENT_ID' => intval($_POST['RECIPIENT_ID']),
			'REASON' => $_POST['COMMAND'],
		);
		$_POST['PARAMS'] = CUtil::JsObjectToPhp($_POST['PARAMS']);
		if (isset($_POST['VIDEO']))
			$arParams['VIDEO'] = $_POST['VIDEO'];
		if (isset($_POST['PARAMS']['ACTIVE']))
			$arParams['ACTIVE'] = $_POST['PARAMS']['ACTIVE'];
		if (isset($_POST['PARAMS']['INITIATOR']))
			$arParams['INITIATOR'] = $_POST['PARAMS']['INITIATOR'];

		CIMCall::End($arParams);
	}
	else if ($_POST['COMMAND'] == 'signaling')
	{
		CIMCall::Command($chatId, $_POST['RECIPIENT_ID'], 'signaling', Array('peer' => $_POST['PEER']));
	}
	else if ($_POST['COMMAND'] == 'invite_user')
	{
		$arCallData = CIMCall::AddUser(Array(
			'CHAT_ID' => $chatId,
			'USER_ID' => $userId,
			'USERS' => CUtil::JsObjectToPhp($_POST['USERS']),
		));
		if ($e = $GLOBALS["APPLICATION"]->GetException())
			$errorMessage = $e->GetString();

		if ($errorMessage == '')
		{
			echo \Bitrix\Im\Common::objectEncode(Array(
				'CHAT_ID' => $arCallData['CHAT_ID'],
				'USERS' => $arCallData['USER_DATA']['USERS'],
				'HR_PHOTO' => $arCallData['USER_DATA']['HR_PHOTO'],
				'ERROR' => $errorMessage
			));
		}
		else
		{
			echo \Bitrix\Im\Common::objectEncode(Array(
				'CHAT_ID' => $arCallData['CHAT_ID'],
				'ERROR' => $e->GetString()
			));
		}
	}
	else
	{
		CIMCall::Signaling(Array(
			'CHAT_ID' => $chatId,
			'USER_ID' => $userId,
			'COMMAND' => $_POST['COMMAND'],
		));
	}
	if ($_POST['COMMAND'] != 'invite' && $_POST['COMMAND'] != 'invite_user')
	{
		echo \Bitrix\Im\Common::objectEncode(Array(
			'CHAT_ID' => $chatId,
			'ERROR' => $errorMessage
		));
	}
}
else if (isImPostRequest('IM_SHARING') && intval($_POST['USER_ID']) > 0)
{
	if (!CModule::IncludeModule("pull"))
		return false;

	if ($_POST['COMMAND'] == 'signaling')
	{
		\Bitrix\Pull\Event::add(intval($_POST['USER_ID']), Array(
			'module_id' => 'im',
			'command' => 'screenSharing',
			'expiry' => 3600,
			'params' => Array(
				'senderId' => $USER->GetID(),
				'command' => 'signaling',
				'peer' => $_POST['PEER'],
			),
			'extra' => \Bitrix\Im\Common::getPullExtra()
		));
	}
	else
	{
		\Bitrix\Pull\Event::add(intval($_POST['USER_ID']), Array(
			'module_id' => 'im',
			'command' => 'screenSharing',
			'expiry' => 3600,
			'params' => Array(
				'senderId' => $USER->GetID(),
				'command' => $_POST['COMMAND']
			),
			'extra' => \Bitrix\Im\Common::getPullExtra()
		));
	}
}
else if (isImPostRequest('IM_PHONE') && CModule::IncludeModule('voximplant'))
{
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/voximplant/ajax_hit.php");
}
else if ((isImPostRequest('IM_OPEN_LINES') || isImPostRequest('IM_OPEN_LINES_CLIENT')) && CModule::IncludeModule('imopenlines'))
{
	$_POST['IM_OPEN_LINES_CLIENT'] = $_POST['IM_OPEN_LINES'] == 'Y'? 'N': 'Y';
	$_POST['IM_OPEN_LINES'] = $_POST['IM_OPEN_LINES_CLIENT'] == 'Y'? 'N': 'Y';

	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/imopenlines/handlers/ajax.php");
}
else if (isImPostRequest('IM_IDLE'))
{
	$errorMessage = "";
	CIMStatus::SetIdle($USER->GetId(), $_POST['IDLE'] == 'Y', $_POST['MANUAL'] == 'Y'? 1: 10);

	echo \Bitrix\Im\Common::objectEncode(Array(
		'ERROR' => $errorMessage
	));
}
else if (isImPostRequest('IM_OPEN_REST_TOKEN'))
{
	$errorMessage = "";

	\Bitrix\Im\App::addToken(Array(
		'BOT_ID' => intval($_POST['BOT_ID']),
		'DIALOG_ID' => $_POST['DIALOG_ID'],
		'USER_ID' => $USER->GetId(),
	));

	echo \Bitrix\Im\Common::objectEncode(Array(
		'ERROR' => $errorMessage
	));
}
else if (isImPostRequest('IM_GET_TEXTAREA_ICONS'))
{
	$errorMessage = "";

	echo \Bitrix\Im\Common::objectEncode(Array(
		'TEXTAREA_ICON' => \Bitrix\Im\App::getListForJs(),
		'ERROR' => ''
	));
}
else if (isImPostRequest('IM_START_WRITING'))
{
	$errorMessage = "";
	CIMMessenger::StartWriting($_POST['DIALOG_ID'], false, "", false, $_POST['OL_SILENT'] == 'Y');

	echo \Bitrix\Im\Common::objectEncode(Array(
		'ERROR' => $errorMessage
	));
}
else if (isImPostRequest('IM_DESKTOP_LOGOUT'))
{
	$errorMessage = "";

	CIMMessenger::SetDesktopStatusOffline();
	CIMContactList::SetOffline();

	echo \Bitrix\Im\Common::objectEncode(Array(
		'ERROR' => $errorMessage
	));
}
else if (isImPostRequest('IM_SET_COLOR'))
{
	$errorMessage = "";

	$_POST['CHAT_ID'] = intval($_POST['CHAT_ID']);

	if ($_POST['CHAT_ID'] > 0)
	{
		$userId = $USER->GetId();
		if (CIMChat::GetGeneralChatId() == intval($_POST['CHAT_ID']) && !CIMChat::CanSendMessageToGeneralChat($userId))
		{
			$errorMessage = GetMessage('IM_ACCESS_ERROR');
		}
		else
		{
			$chat = new CIMChat();
			$chat->SetColor($_POST['CHAT_ID'], $_POST['COLOR']);
		}
	}
	else
	{
		CIMStatus::SetColor($USER->GetId(), $_POST['COLOR']);
	}

	echo \Bitrix\Im\Common::objectEncode(Array(
		'COLOR' => $_POST['COLOR'],
		'CHAT_ID' => $_POST['CHAT_ID'],
		'ERROR' => $errorMessage
	));
}
else if (isImPostRequest('IM_GET_MOBILE_CHAT_AVATAR'))
{
	$avatar = "";
	$errorMessage = "";

	if ($_POST['CHAT_ID'] > 0)
	{
		$chat = new CIMChat();
		$arChat = CIMChat::GetChatData(array(
			'ID' => $_POST['CHAT_ID'],
			'USE_CACHE' => 'N',
			'PHOTO_SIZE' => '500',
			'USER_ID' => intval($USER->GetId())
		));
		$arResult['CHAT'] = $arChat['chat'][$_POST['CHAT_ID']];
		if ($arResult['CHAT'])
		{
			$avatar = $arResult['CHAT']['avatar'] == '/bitrix/js/im/images/blank.gif'? '': $arResult['CHAT']['avatar'];
		}
	}

	echo \Bitrix\Im\Common::objectEncode(Array(
		'AVATAR' => $avatar,
		'ERROR' => $errorMessage
	));
}
else if (isImPostRequest('IM_SETTING_SAVE'))
{
	$errorMessage = "";

	$arSettings = CUtil::JsObjectToPhp($_POST['SETTINGS']);

	CIMSettings::SetSetting(CIMSettings::SETTINGS, $arSettings);

	echo \Bitrix\Im\Common::objectEncode(Array(
		'ERROR' => $errorMessage
	));
}
else if (isImPostRequest('IM_SETTINGS_SAVE'))
{
	$errorMessage = "";

	$arSettings = CUtil::JsObjectToPhp($_POST['SETTINGS']);

	$oldSettings = CIMSettings::Get()[CIMSettings::SETTINGS];
	if ($oldSettings['notifyScheme'] == 'expert' && $arSettings['notifyScheme'] == 'simple')
	{
		$arNotifyValues = CIMSettings::GetSimpleNotifyBlocked();
		$arSettings['notify'] = Array();
		foreach ($arNotifyValues as $settingName => $value)
		{
			$arSettings['notify'][CIMSettings::CLIENT_SITE.'|'.$settingName] = false;
			$arSettings['notify'][CIMSettings::CLIENT_XMPP.'|'.$settingName] = false;
			$arSettings['notify'][CIMSettings::CLIENT_MAIL.'|'.$settingName] = false;
		}
	}

	if (array_key_exists('notify', $arSettings))
	{
		CIMSettings::Set(CIMSettings::NOTIFY, $arSettings['notify']);
		unset($arSettings['notify']);
	}
	CIMSettings::Set(CIMSettings::SETTINGS, $arSettings);

	echo \Bitrix\Im\Common::objectEncode(Array(
		'ERROR' => $errorMessage
	));
}
else if (isImPostRequest('IM_SETTINGS_NOTIFY_LOAD'))
{
	$errorMessage = "";

	$arSettings = CIMSettings::Get();
	$arNotifyNames = CIMSettings::GetNotifyNames();

	echo \Bitrix\Im\Common::objectEncode(Array(
		'NAMES' => $arNotifyNames,
		'VALUES' => $arSettings['notify'],
		'ERROR' => $errorMessage
	));
}
else if (isImPostRequest('IM_SETTINGS_SIMPLE_NOTIFY_LOAD'))
{
	$errorMessage = "";

	$arNotifyNames = CIMSettings::GetNotifyNames();
	$arNotifyValues = CIMSettings::GetSimpleNotifyBlocked(true);

	echo \Bitrix\Im\Common::objectEncode(Array(
		'NAMES' => $arNotifyNames,
		'VALUES' => $arNotifyValues,
		'ERROR' => $errorMessage
	));
}
else if (isImPostRequest('IM_DISK_ACTIVATE_PUBLIC_LINK'))
{
	CIMDisk::SetEnabledExternalLink($_POST['STATUS'] == 'Y');

	echo \Bitrix\Im\Common::objectEncode(Array(
		'ERROR' => $errorMessage
	));
}
else if (isImPostRequest('IM_CREATE_ZOOM_CONF'))
{
	$chatId = \Bitrix\Im\Dialog::getChatId($_POST['CHAT_ID']);
	$userId = $USER->GetId();

	if (!\Bitrix\Im\Call\Integration\Zoom::isActive())
	{
		echo \Bitrix\Im\Common::objectEncode(Array(
			'ERROR' => 'NOT_ACTIVE'
		));
	}
	elseif (!\Bitrix\Im\Call\Integration\Zoom::isAvailable())
	{
		echo \Bitrix\Im\Common::objectEncode(Array(
			'ERROR' => 'NOT_AVAILABLE'
		));
	}
	elseif (!\Bitrix\Im\Call\Integration\Zoom::isConnected($userId))
	{
		echo \Bitrix\Im\Common::objectEncode(Array(
			'ERROR' => 'NOT_CONNECTED',
		));
	}
	elseif (CIMChat::GetGeneralChatId() == $chatId && !CIMChat::CanSendMessageToGeneralChat($userId))
	{
		echo \Bitrix\Im\Common::objectEncode(Array(
			'ERROR' => GetMessage('IM_ERROR_GROUP_CANCELED'),
		));
	}
	else
	{
		$zoom = new \Bitrix\Im\Call\Integration\Zoom($userId, $_POST['CHAT_ID']);
		$link = $zoom->getImChatConferenceUrl();
		if (empty($link))
		{
			echo \Bitrix\Im\Common::objectEncode(Array(
				'ERROR' => 'COULD_NOT_CREATE',
			));
		}
		else
		{
			$messageFields = $zoom->getRichMessageFields($_POST['CHAT_ID'], $link, $userId);
			$messageId = \CIMMessenger::Add($messageFields);
			if ($messageId)
			{
				echo \Bitrix\Im\Common::objectEncode(Array(
					'LINK' => $link,
					'ADD_RESULT' => $messageId,
				));
			}
			else
			{
				echo \Bitrix\Im\Common::objectEncode(Array(
					'ERROR' => 'COULD_NOT_ADD_MESSAGE',
				));
			}
		}
	}
}
else
{
	echo \Bitrix\Im\Common::objectEncode(Array('ERROR' => GetMessage('IM_UNKNOWN_ERROR')));
}

CMain::FinalActions();
die();