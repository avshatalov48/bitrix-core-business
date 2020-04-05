<?php

namespace Bitrix\Socialnetwork;

use Bitrix\Main\Loader;
use Bitrix\Main\ModuleManager;
use Bitrix\Main;
use Bitrix\Main\Localization\Loc;

class Util
{
	public static function getPermissionsByExternalAuthId($authId)
	{
		$result = array(
			'message' => true
		);

		if ($authId == 'email')
		{
			$result['message'] = (!ModuleManager::isModuleInstalled('mail'));
		}

		return $result;
	}

	public static function getSiteIdByLogId($logId)
	{
		$extranetSiteId = false;
		if (Loader::includeModule('extranet'))
		{
			$extranetSiteId = \CExtranet::getExtranetSiteId();
		}

		$logSiteId = array();
		$res = \CSocNetLog::getSite($logId);
		while ($logSite = $res->fetch())
		{
			$logSiteId[] = $logSite["LID"];
		}

		return  (
			$extranetSiteId
			&& count($logSiteId) == 1
			&& $logSiteId[0] == $extranetSiteId
				? $extranetSiteId
				: $logSiteId[0]
		);
	}

	public static function notifyMail($fields)
	{
		if (!Loader::includeModule('mail'))
		{
			return false;
		}

		if (
			!isset($fields["logEntryId"])
			|| intval($fields["logEntryId"]) <= 0
			|| !isset($fields["userId"])
			|| !isset($fields["logEntryUrl"])
			|| strlen($fields["logEntryUrl"]) <= 0
		)
		{
			return false;
		}

		if (!is_array($fields["userId"]))
		{
			$fields["userId"] = array($fields["userId"]);
		}

		if (!isset($fields["siteId"]))
		{
			$fields["siteId"] = SITE_ID;
		}

		$nameTemplate = \CSite::getNameFormat("", $fields["siteId"]);
		$authorName = "";

		if (!empty($fields["authorId"]))
		{
			$res = \CUser::getById($fields["authorId"]);
			if ($author = $res->fetch())
			{
				$authorName = \CUser::formatName(
					$nameTemplate,
					$author,
					true,
					false
				);
			}
			else
			{
				$authorName = '';
			}

			if (check_email($authorName))
			{
				$authorName = '"'.$authorName.'"';
			}

			foreach($fields["userId"] as $key => $val)
			{
				if (intval($val) == intval($fields["authorId"]))
				{
					unset($fields["userId"][$key]);
				}
			}
		}

		if (empty($fields["userId"]))
		{
			return false;
		}

		if (
			!isset($fields["type"])
			|| !in_array(strtoupper($fields["type"]), array("LOG_ENTRY", "LOG_COMMENT"))
		)
		{
			$fields["type"] = "LOG_COMMENT";
		}

		$arEmail = \Bitrix\Mail\User::getUserData($fields["userId"], $nameTemplate);
		if (empty($arEmail))
		{
			return false;
		}

		$arLogEntry = \CSocNetLog::getByID(intval($fields["logEntryId"]));
		if (!$arLogEntry)
		{
			return false;
		}

		$logEntryTitle = str_replace(array("\r\n", "\n"), " ", ($arLogEntry["TITLE"] != '__EMPTY__' ? $arLogEntry["TITLE"] : $arLogEntry["MESSAGE"]));
		$logEntryTitle = truncateText($logEntryTitle, 100);

		switch (strtoupper($fields["type"]))
		{
			case "LOG_COMMENT":
				$mailMessageId = "<LOG_COMMENT_".$fields["logCommentId"]."@".$GLOBALS["SERVER_NAME"].">";
				$mailTemplateType = "SONET_LOG_NEW_COMMENT";
				break;
			default:
				$mailMessageId = "<LOG_ENTRY_".$fields["logEntryId"]."@".$GLOBALS["SERVER_NAME"].">";
				$mailTemplateType = "SONET_LOG_NEW_ENTRY";
		}

		$mailMessageInReplyTo = "<LOG_ENTRY_".$fields["logEntryId"]."@".$GLOBALS["SERVER_NAME"].">";
		$defaultEmailFrom = \Bitrix\Mail\User::getDefaultEmailFrom();

		foreach ($arEmail as $userId => $user)
		{
			$email = $user["EMAIL"];
			$nameFormatted = str_replace(array('<', '>', '"'), '', $user["NAME_FORMATTED"]);

			if (
				intval($userId) <= 0
				&& strlen($email) <= 0
			)
			{
				continue;
			}

			$res = \Bitrix\Mail\User::getReplyTo(
				$fields["siteId"],
				$userId,
				'LOG_ENTRY',
				$fields["logEntryId"],
				$fields["logEntryUrl"]
			);
			if (is_array($res))
			{
				list($replyTo, $backUrl) = $res;

				if (
					$replyTo
					&& $backUrl
				)
				{
					$authorName = str_replace(array('<', '>', '"'), '', $authorName);
					\CEvent::send(
						$mailTemplateType,
						$fields["siteId"],
						array(
							"=Reply-To" => $authorName.' <'.$replyTo.'>',
							"=Message-Id" => $mailMessageId,
							"=In-Reply-To" => $mailMessageInReplyTo,
							"EMAIL_FROM" => $authorName.' <'.$defaultEmailFrom.'>',
							"EMAIL_TO" => (!empty($nameFormatted) ? ''.$nameFormatted.' <'.$email.'>' : $email),
							"RECIPIENT_ID" => $userId,
							"COMMENT_ID" => (isset($fields["logCommentId"]) ? intval($fields["logCommentId"]) : false),
							"LOG_ENTRY_ID" => intval($fields["logEntryId"]),
							"LOG_ENTRY_TITLE" => $logEntryTitle,
							"URL" => $fields["logEntryUrl"]
						)
					);
				}
			}
		}

		return true;
	}

	public static function getEqualityFields(&$fields)
	{
		$fields1 = array();
		foreach ($fields as $key => $value)
		{
			if (substr($key, 0, 1) == "=")
			{
				$fields1[substr($key, 1)] = $value;
				unset($fields[$key]);
			}
		}

		return $fields1;
	}

	public static function processEqualityFieldsToInsert($fields1, &$insert)
	{
		foreach ($fields1 as $key => $value)
		{
			if (strlen($insert[0]) > 0)
			{
				$insert[0] .= ", ";
			}
			$insert[0] .= $key;
			if (strlen($insert[1]) > 0)
			{
				$insert[1] .= ", ";
			}
			$insert[1] .= $value;
		}
	}

	public static function processEqualityFieldsToUpdate($fields1, &$update)
	{
		foreach ($fields1 as $key => $value)
		{
			if (strlen($update) > 0)
			{
				$update .= ", ";
			}
			$update .= $key."=".$value." ";
		}
	}

	public static function detectTags($fieldList, $codeList = array())
	{
		static $parser = null;

		$result = array();

		if (
			!is_array($fieldList)
			|| !is_array($codeList)
		)
		{
			return false;
		}

		foreach($codeList as $code)
		{
			if (
				empty($code)
				|| empty($fieldList[$code])
			)
			{
				continue;
			}

			if ($parser === null)
			{
				$parser = new \CTextParser();
			}

			$result = array_merge($result, $parser->detectTags($fieldList[$code]));
		}

		return array_unique($result);
	}
}
?>