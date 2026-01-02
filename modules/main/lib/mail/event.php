<?php

/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2025 Bitrix
 */

namespace Bitrix\Main\Mail;

use Bitrix\Main;
use Bitrix\Main\Config;
use Bitrix\Main\Application;
use Bitrix\Main\ORM\Data\AddResult;

class Event
{
	const SEND_RESULT_NONE = 'N';
	const SEND_RESULT_SUCCESS = 'Y';
	const SEND_RESULT_ERROR = 'F';
	const SEND_RESULT_PARTLY = 'P';
	const SEND_RESULT_TEMPLATE_NOT_FOUND = '0';

	/**
	 * @param array $data
	 * @return string
	 * @throws Main\ArgumentTypeException
	 */
	public static function sendImmediate(array $data)
	{
		if (!static::onBeforeEventAdd($data))
		{
			return static::SEND_RESULT_NONE;
		}

		$data["ID"] = 0;

		return static::handleEvent($data);
	}

	/**
	 * Send mail event
	 *
	 * @param array $data Params of event
	 * @return AddResult
	 */
	public static function send(array $data)
	{
		if (!static::onBeforeEventAdd($data))
		{
			return (new AddResult())->addError(new Main\Error('OnBeforeEventAdd'));
		}

		$manageCache = Application::getInstance()->getManagedCache();
		if (CACHED_b_event !== false && $manageCache->read(CACHED_b_event, "events"))
		{
			$manageCache->clean('events');
		}

		$fileList = [];
		if (isset($data['FILE']))
		{
			if (is_array($data['FILE']))
			{
				$fileList = $data['FILE'];
			}

			unset($data['FILE']);
		}

		$attachments = [];
		foreach ($fileList as $file)
		{
			$attachment = [];
			if (is_numeric($file) && \CFile::GetFileArray($file))
			{
				$attachment['FILE_ID'] = $file;
				$attachment['IS_FILE_COPIED'] = 'N';
			}
			else
			{
				$fileArray = \CFile::MakeFileArray($file);
				$fileArray['MODULE_ID'] = 'main';

				$attachment['FILE'] = $fileArray;
				$attachment['IS_FILE_COPIED'] = 'Y';
			}
			$attachments[] = $attachment;
		}

		$connection = Application::getConnection();

		$connection->startTransaction();

		$result = Internal\EventTable::add($data);

		if ($result->isSuccess())
		{
			$id = $result->getId();

			foreach ($attachments as $file)
			{
				$attachment = [
					'EVENT_ID' => $id,
					'IS_FILE_COPIED' => $file['IS_FILE_COPIED'],
					'FILE_ID' => $file['FILE_ID'] ?? null,
				];

				if ($attachment['IS_FILE_COPIED'] == 'Y')
				{
					$attachment['FILE_ID'] = \CFile::SaveFile($file['FILE'], 'main');
				}

				Internal\EventAttachmentTable::add($attachment);
			}
		}

		$connection->commitTransaction();

		return $result;
	}

	protected static function onBeforeEventAdd(array &$data): bool
	{
		foreach (GetModuleEvents("main", "OnBeforeEventAdd", true) as $arEvent)
		{
			if (ExecuteModuleEventEx($arEvent, [&$data["EVENT_NAME"], &$data["LID"], &$data["C_FIELDS"], &$data["MESSAGE_ID"], &$data["FILE"], &$data["LANGUAGE_ID"]]) === false)
			{
				return false;
			}
		}

		if (isset($data['LID']) && is_array($data['LID']))
		{
			$data['LID'] = implode(",", $data['LID']);
		}

		if (!is_array($data["C_FIELDS"] ?? null))
		{
			$data["C_FIELDS"] = [];
		}

		if (isset($data["MESSAGE_ID"]) && (int)$data["MESSAGE_ID"] > 0)
		{
			$data["MESSAGE_ID"] = (int)$data["MESSAGE_ID"];
		}
		else
		{
			unset($data["MESSAGE_ID"]);
		}

		return true;
	}

	/**
	 * @param array $arEvent
	 * @return string
	 * @throws Main\ArgumentTypeException
	 */
	public static function handleEvent(array $arEvent)
	{
		if (!isset($arEvent['FIELDS']) && isset($arEvent['C_FIELDS']))
		{
			$arEvent['FIELDS'] = $arEvent['C_FIELDS'];
		}

		if (!is_array($arEvent['FIELDS']))
		{
			throw new Main\ArgumentTypeException("FIELDS");
		}

		$flag = static::SEND_RESULT_TEMPLATE_NOT_FOUND; // no templates
		$arResult = [
			"Success" => false,
			"Fail" => false,
			"Was" => false,
			"Skip" => false,
		];

		$trackRead = null;
		$trackClick = null;
		if (array_key_exists('TRACK_READ', $arEvent))
		{
			$trackRead = $arEvent['TRACK_READ'];
		}
		if (array_key_exists('TRACK_CLICK', $arEvent))
		{
			$trackClick = $arEvent['TRACK_CLICK'];
		}

		$arSites = explode(",", $arEvent["LID"]);
		if (empty($arSites))
		{
			return $flag;
		}

		// get charset and server name for languages of event
		// actually it's one of the sites (let it be the first one)
		$charset = false;
		$serverName = null;

		static $sites = [];
		$infoSite = reset($arSites);

		if (!isset($sites[$infoSite]))
		{
			$siteDb = Main\SiteTable::getList([
				'select' => ['SERVER_NAME', 'CULTURE_CHARSET' => 'CULTURE.CHARSET'],
				'filter' => ['=LID' => $infoSite],
			]);
			$sites[$infoSite] = $siteDb->fetch();
		}

		if (is_array($sites[$infoSite]))
		{
			$charset = $sites[$infoSite]['CULTURE_CHARSET'];
			$serverName = $sites[$infoSite]['SERVER_NAME'];
		}

		if (!$charset)
		{
			return $flag;
		}

		// get filter for list of message templates
		$arEventMessageFilter = [];
		$MESSAGE_ID = intval($arEvent["MESSAGE_ID"] ?? 0);
		if ($MESSAGE_ID > 0)
		{
			$eventMessageDb = Internal\EventMessageTable::getById($MESSAGE_ID);
			if ($eventMessageDb->Fetch())
			{
				$arEventMessageFilter['=ID'] = $MESSAGE_ID;
				$arEventMessageFilter['=ACTIVE'] = 'Y';
			}
		}
		if (empty($arEventMessageFilter))
		{
			$arEventMessageFilter = [
				'=ACTIVE' => 'Y',
				'=EVENT_NAME' => $arEvent["EVENT_NAME"],
				'=EVENT_MESSAGE_SITE.SITE_ID' => $arSites,
			];

			if ($arEvent["LANGUAGE_ID"] <> '')
			{
				$arEventMessageFilter[] = [
					"LOGIC" => "OR",
					["=LANGUAGE_ID" => $arEvent["LANGUAGE_ID"]],
					["=LANGUAGE_ID" => null],
				];
			}
		}

		// get list of message templates of event
		$messageDb = Internal\EventMessageTable::getList([
			'select' => ['ID'],
			'filter' => $arEventMessageFilter,
			'group' => ['ID'],
		]);

		while ($arMessage = $messageDb->fetch())
		{
			$eventMessage = Internal\EventMessageTable::getRowById($arMessage['ID']);

			$eventMessage['FILE'] = [];
			$attachmentDb = Internal\EventMessageAttachmentTable::getList([
				'select' => ['FILE_ID'],
				'filter' => ['=EVENT_MESSAGE_ID' => $arMessage['ID']],
			]);
			while ($arAttachmentDb = $attachmentDb->fetch())
			{
				$eventMessage['FILE'][] = $arAttachmentDb['FILE_ID'];
			}

			$context = new Context();
			$arFields = $arEvent['FIELDS'];

			foreach (GetModuleEvents("main", "OnBeforeEventSend", true) as $event)
			{
				if (ExecuteModuleEventEx($event, [&$arFields, &$eventMessage, $context, &$arResult]) === false)
				{
					continue 2;
				}
			}

			// get message object for send mail
			$arMessageParams = [
				'EVENT' => $arEvent,
				'FIELDS' => $arFields,
				'MESSAGE' => $eventMessage,
				'SITE' => $arSites,
				'CHARSET' => $charset,
			];
			$message = EventMessageCompiler::createInstance($arMessageParams);
			try
			{
				$message->compile();
			}
			catch (StopException)
			{
				$arResult["Was"] = true;
				$arResult["Fail"] = true;
				continue;
			}

			// send mail
			$result = Main\Mail\Mail::send([
				'TO' => $message->getMailTo(),
				'SUBJECT' => $message->getMailSubject(),
				'BODY' => $message->getMailBody(),
				'HEADER' => $message->getMailHeaders(),
				'CHARSET' => $message->getMailCharset(),
				'CONTENT_TYPE' => $message->getMailContentType(),
				'MESSAGE_ID' => $message->getMailId(),
				'ATTACHMENT' => $message->getMailAttachment(),
				'TRACK_READ' => $trackRead,
				'TRACK_CLICK' => $trackClick,
				'LINK_PROTOCOL' => Config\Option::get("main", "mail_link_protocol"),
				'LINK_DOMAIN' => $serverName,
				'CONTEXT' => $context,
			]);
			if ($result)
			{
				$arResult["Success"] = true;
			}
			else
			{
				$arResult["Fail"] = true;
			}

			$arResult["Was"] = true;
		}

		if ($arResult["Was"])
		{
			if ($arResult["Success"])
			{
				if ($arResult["Fail"])
				{
					// partly sent
					$flag = static::SEND_RESULT_PARTLY;
				}
				else
				{
					// all sent
					$flag = static::SEND_RESULT_SUCCESS;
				}
			}
			else
			{
				if ($arResult["Fail"])
				{
					// all templates failed
					$flag = static::SEND_RESULT_ERROR;
				}
			}
		}
		elseif ($arResult["Skip"])
		{
			// skip this event
			$flag = static::SEND_RESULT_NONE;
		}

		return $flag;
	}
}
