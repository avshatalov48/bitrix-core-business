<?php

/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage socialnetwork
 * @copyright 2001-2015 Bitrix
 */

use Bitrix\Main;
use Bitrix\Main\Config;
use Bitrix\Main\Localization;
use Bitrix\Main\Loader;
use Bitrix\Socialnetwork\ComponentHelper;
use Bitrix\Socialnetwork\Helper\Mention;
use Bitrix\Socialnetwork\Helper\Path;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/components/bitrix/socialnetwork.log.entry/include.php");

class CBitrixSocialnetworkLogEntryMailComponent extends CBitrixComponent
{
	const E_SOCIALNETWORK_MODULE_NOT_INSTALLED 		= 10001;
	const E_COMMENT_NOT_FOUND 						= 10002;
	const E_LOG_ENTRY_NOT_FOUND						= 10003;

	/**
	 * Variable contains comments data
	 *
	 * @var array[] array
	 */

	protected $logEntryId = false;
	protected $authorIdList = array();

	/**
	 * Function implements all the life cycle of the component
	 * @return void
	 */
	public function executeComponent()
	{
		$this->checkRequiredModules();

		$this->arResult = array(
			"SITE" => false,
			"AUTHORS" => array(),
			"COMMENTS" => array(),
			"LOG_ENTRY" => array(),
			"LOG_ENTRY_URL" => ""
		);

		try
		{
			$this->obtainDataLogEntry();
			$this->obtainDataDestinations();
			$this->obtainDataComments();
			$this->obtainLogEntryUrl();
		}
		catch(Exception $e)
		{
		}

		Loader::includeModule('mail');

		$this->includeComponentTemplate();
	}

	public function __construct($component = null)
	{
		parent::__construct($component);

		Localization\Loc::loadMessages(__FILE__);
	}

	/**
	 * Function checks if required modules installed. If not, throws an exception
	 * @throws Main\SystemException
	 * @return void
	 */
	protected function checkRequiredModules()
	{
		if (!Loader::includeModule('socialnetwork'))
		{
			throw new Main\SystemException(Localization\Loc::getMessage("SLEM_SOCIALNETWORK_MODULE_NOT_INSTALLED"), self::E_SOCIALNETWORK_MODULE_NOT_INSTALLED);
		}
	}

	public function onPrepareComponentParams($arParams): array
	{
		$arParams["RECIPIENT_ID"] = (isset($arParams["RECIPIENT_ID"]) ? intval($arParams["RECIPIENT_ID"]) : 0);
		$arParams["COMMENT_ID"] = (isset($arParams["COMMENT_ID"]) ? intval($arParams["COMMENT_ID"]) : 0);
		$arParams["LOG_ENTRY_ID"] = (int)($arParams["LOG_ENTRY_ID"] ?? 0);
		$arParams["AVATAR_SIZE"] = (
			isset($arParams["AVATAR_SIZE"])
			&& intval($arParams["AVATAR_SIZE"]) > 0
				? intval($arParams["AVATAR_SIZE"])
				: 100
		);
		$arParams["AVATAR_SIZE_COMMENT"] = (
			isset($arParams["AVATAR_SIZE_COMMENT"])
			&& intval($arParams["AVATAR_SIZE_COMMENT"]) > 0
				? intval($arParams["AVATAR_SIZE_COMMENT"])
				: 100
		);
		$arParams["COMMENTS_COUNT"] = (
			isset($arParams["COMMENTS_COUNT"])
			&& intval($arParams["COMMENTS_COUNT"]) > 0
				? intval($arParams["COMMENTS_COUNT"])
				: 3
		);
		$arParams["URL"] = (
			isset($arParams["URL"])
			&& $arParams["URL"] <> ''
				? $arParams["URL"]
				: CComponentEngine::MakePathFromTemplate(
					'/pub/log_entry.php?log_id=#log_id#',
					array(
						"log_id"=> $arParams["LOG_ENTRY_ID"]
					)
				)
		);

		$this->logEntryId = $arParams["LOG_ENTRY_ID"];

		return $arParams;
	}

	private function obtainDataLogEntry()
	{
		static $logEntriesCache = array();
		static $sitesCache = array();

		$arResult =& $this->arResult;

		if ($this->logEntryId > 0)
		{
			if (isset($sitesCache[$this->logEntryId]))
			{
				$arResult["SITE"] = $sitesCache[$this->logEntryId];
			}
			else
			{
				$siteId = \Bitrix\Socialnetwork\Util::getSiteIdByLogId($this->logEntryId);
				$res = CSite::GetByID($siteId);
				$arResult["SITE"] = $sitesCache[$this->logEntryId] = $res->fetch();
			}

			$arResult["PATH_TO_USER"] = Config\Option::get("main", "TOOLTIP_PATH_TO_USER", false, $arResult["SITE"]["ID"]);
			$arResult["PATH_TO_GROUP"] = Config\Option::get("socialnetwork", "workgroups_page", false, $arResult["SITE"]["ID"])."group/#group_id#/";
			$arResult["PATH_TO_CONPANY_DEPARTMENT"] = Path::get('department_path_template', $arResult['SITE']['ID']);

			if (isset($logEntriesCache[$this->logEntryId]))
			{
				$arResult["LOG_ENTRY"] = $logEntriesCache[$this->logEntryId];
			}
			else
			{
				$arResult["LOG_ENTRY"] = __SLEGetLogRecord(
					$this->logEntryId,
					array(
						"AVATAR_SIZE" => $this->arParams["AVATAR_SIZE"],
						"NAME_TEMPLATE" => CSite::getNameFormat(false, $arResult["SITE"]["ID"]),
						"SHOW_LOGIN" => "Y",
						"DATE_TIME_FORMAT" => CSite::GetDateFormat("FULL", $arResult["SITE"]["ID"]),
						"PATH_TO_USER" => Config\Option::get("main", "TOOLTIP_PATH_TO_USER", false, $arResult["SITE"]["ID"]),
						"PATH_TO_GROUP" => Config\Option::get("socialnetwork", "workgroups_page", false, $arResult["SITE"]["ID"])."group/#group_id#/",
						"PATH_TO_CONPANY_DEPARTMENT" => Path::get('department_path_template', $arResult['SITE']['ID']),
					),
					false
				);

				$arResult["LOG_ENTRY"]["ATTACHMENTS"] = array();

				if (
					!empty($arResult["LOG_ENTRY"]["EVENT_FORMATTED"])
					&& !empty($arResult["LOG_ENTRY"]["EVENT_FORMATTED"]["UF"])
					&& !empty($arResult["LOG_ENTRY"]["EVENT_FORMATTED"]["UF"]["UF_SONET_LOG_DOC"])
					&& !empty($arResult["LOG_ENTRY"]["EVENT_FORMATTED"]["UF"]["UF_SONET_LOG_DOC"]["VALUE"])

				)
				{
					$arResult["LOG_ENTRY"]["ATTACHMENTS"] = ComponentHelper::getAttachmentsData($arResult["LOG_ENTRY"]["EVENT_FORMATTED"]["UF"]["UF_SONET_LOG_DOC"]["VALUE"], $arResult["SITE"]["ID"]);
				}

				$arResult["LOG_ENTRY"]["EVENT"]["MESSAGE"] = ComponentHelper::convertDiskFileBBCode(
					$arResult["LOG_ENTRY"]["EVENT"]["MESSAGE"],
					'SONET_LOG',
					$this->logEntryId,
					$arResult["LOG_ENTRY"]["EVENT"]["USER_ID"],
					$arResult["LOG_ENTRY"]["ATTACHMENTS"]
				);

				$arResult["LOG_ENTRY"]["EVENT"]["MESSAGE_FORMATTED"] = preg_replace(
					array(
						'|\[DISK\sFILE\sID=[n]*\d+\]|',
						'|\[DOCUMENT\sID=[n]*\d+\]|'
					),
					'',
					$arResult["LOG_ENTRY"]["EVENT"]["MESSAGE"]
				);

				$arResult['LOG_ENTRY']['EVENT']['MESSAGE_FORMATTED'] = Mention::clear($arResult['LOG_ENTRY']['EVENT']['MESSAGE_FORMATTED']);
				$arResult["LOG_ENTRY"]["EVENT"]["MESSAGE_FORMATTED"] = $this->parseText($arResult["LOG_ENTRY"]["EVENT"]["MESSAGE_FORMATTED"]);
			}
		}

		if (empty($arResult["LOG_ENTRY"]))
		{
			throw new Main\SystemException(str_replace("#ID#", $this->logEntryId, Localization\Loc::getMessage("SLEM_NO_LOG_ENTRY")), self::E_LOG_ENTRY_NOT_FOUND);
		}
	}

	private function obtainDataComments()
	{
		global $USER_FIELD_MANAGER;

		static $commentListsCache = array();
		static $commentsAllCountCache = array();

		$arResult =& $this->arResult;

		if (
			$this->logEntryId > 0
			&& !empty($this->arParams["COMMENT_ID"])
			&& intval($this->arParams["COMMENT_ID"]) > 0
		)
		{
			if (isset($commentListsCache[$this->logEntryId]))
			{
				$arResult["COMMENTS"] = $commentListsCache[$this->logEntryId];
			}
			else
			{
				$arUFMeta = $USER_FIELD_MANAGER->GetUserFields("SONET_COMMENT", 0, $arResult["SITE"]["LANGUAGE_ID"]);
				$arAssets = array();
				$arResult["COMMENTS"] = array();

				$dbComments = CSocNetLogComments::GetList(
					array("LOG_DATE" => "DESC"), // revert then
					array(
						"LOG_ID" => $this->logEntryId
					),
					false,
					array(
						"nTopCount" => $this->arParams["COMMENTS_COUNT"]
					),
					array(
						"ID", "LOG_ID", "SOURCE_ID", "ENTITY_TYPE", "ENTITY_ID", "USER_ID", "EVENT_ID", "LOG_DATE", "MESSAGE", "TEXT_MESSAGE", "URL", "MODULE_ID",
						"CREATED_BY_NAME", "CREATED_BY_LAST_NAME", "CREATED_BY_SECOND_NAME", "CREATED_BY_LOGIN", "CREATED_BY_PERSONAL_PHOTO", "CREATED_BY_PERSONAL_GENDER",
						"LOG_SITE_ID", "LOG_SOURCE_ID",
						"UF_*"
					),
					array(
						"USE_SUBSCRIBE" => "N",
						"CHECK_RIGHTS" => "N"
					)
				);

				while($comment = $dbComments->getNext())
				{
					$comment["UF"] = $arUFMeta;
					foreach($arUFMeta as $field_name => $arUF)
					{
						if (array_key_exists($field_name, $comment))
						{
							$comment["UF"][$field_name]["VALUE"] = $comment[$field_name];
							$comment["UF"][$field_name]["ENTITY_VALUE_ID"] = $comment["ID"];
						}
					}

					if (isset($comment["UF"]["UF_SONET_COM_URL_PRV"]))
					{
						unset($comment["UF"]["UF_SONET_COM_URL_PRV"]);
					}

					$commentFormatted = \Bitrix\Socialnetwork\Component\LogEntry::getLogCommentRecord($comment, array(
						"MAIL" => "Y",
						"AVATAR_SIZE" => $this->arParams["AVATAR_SIZE_COMMENT"],
						"NAME_TEMPLATE" => CSite::getNameFormat(false, $arResult["SITE"]["ID"]),
						"SHOW_LOGIN" => "Y",
						"DATE_TIME_FORMAT" => CSite::GetDateFormat("FULL", $arResult["SITE"]["ID"]),
						"PATH_TO_USER" => $arResult["PATH_TO_USER"],
						"PATH_TO_GROUP" => $arResult["PATH_TO_GROUP"],
						"PATH_TO_CONPANY_DEPARTMENT" => $arResult["PATH_TO_CONPANY_DEPARTMENT"],
					), $arAssets);

					$commentFormatted["ATTACHMENTS"] = array();

					if (
						!empty($commentFormatted)
						&& !empty($commentFormatted["UF"])
						&& !empty($commentFormatted["UF"]["UF_SONET_COM_DOC"])
						&& !empty($commentFormatted["UF"]["UF_SONET_COM_DOC"]["VALUE"])
					)
					{
						$commentFormatted["ATTACHMENTS"] = ComponentHelper::getAttachmentsData($commentFormatted["UF"]["UF_SONET_COM_DOC"]["VALUE"], $arResult["SITE"]["ID"]);
					}

					$commentFormatted["EVENT"]["MESSAGE"] = ComponentHelper::convertDiskFileBBCode(
						$commentFormatted["EVENT"]["MESSAGE"],
						'SONET_COMMENT',
						$commentFormatted["EVENT"]["ID"],
						$commentFormatted["EVENT"]["USER_ID"],
						$commentFormatted["ATTACHMENTS"]
					);

					$commentFormatted["EVENT"]["MESSAGE_FORMATTED"] = preg_replace(
						array(
							'|\[DISK\sFILE\sID=[n]*\d+\]|',
							'|\[DOCUMENT\sID=[n]*\d+\]|'
						),
						'',
						$commentFormatted["EVENT"]["MESSAGE"]
					);

					$commentFormatted['EVENT']['MESSAGE_FORMATTED'] = Mention::clear($commentFormatted['EVENT']['MESSAGE_FORMATTED']);
					$commentFormatted["EVENT"]["MESSAGE_FORMATTED"] = $this->parseText($commentFormatted["EVENT"]["MESSAGE_FORMATTED"]);

					$arResult["COMMENTS"][$comment["ID"]] = $commentFormatted;
				}

				$commentListsCache[$this->logEntryId] = $arResult["COMMENTS"];
			}

			if (isset($commentsAllCountCache[$this->logEntryId]))
			{
				$arResult["COMMENTS_ALL_COUNT"] = $commentsAllCountCache[$this->logEntryId];
			}
			else
			{
				$arResult["COMMENTS_ALL_COUNT"] = $commentsAllCountCache[$this->logEntryId] = intval(CSocNetLogComments::GetList(
					array("LOG_DATE" => "DESC"),
					array(
						"LOG_ID" => $this->logEntryId
					),
					array(),
					false,
					array("ID"),
					array(
						"USE_SUBSCRIBE" => "N",
						"CHECK_RIGHTS" => "N"
					)
				));
			}
		}
	}

	private function obtainDataDestinations()
	{
		$arResult =& $this->arResult;

		$arResult["DESTINATIONS"] = array();

		if (
			isset($arResult["LOG_ENTRY"]["EVENT_FORMATTED"])
			&& isset($arResult["LOG_ENTRY"]["EVENT_FORMATTED"]["DESTINATION"])
			&& !empty($arResult["LOG_ENTRY"]["EVENT_FORMATTED"]["DESTINATION"])
		)
		{
			foreach ($arResult["LOG_ENTRY"]["EVENT_FORMATTED"]["DESTINATION"] as $destination)
			{
				if (mb_strpos($destination["TYPE"], "CRM") !== 0)
				{
					$arResult["DESTINATIONS"][] = $destination;
				}
			}
		}
	}

	private function obtainLogEntryUrl()
	{
		$arResult =& $this->arResult;
		$arResult["LOG_ENTRY_URL"] = $this->arParams["URL"];

		if (
			isset($this->arParams["RECIPIENT_ID"])
			&& intval($this->arParams["RECIPIENT_ID"]) > 0
		)
		{
			$backUrl = ComponentHelper::getReplyToUrl($arResult["LOG_ENTRY_URL"], intval($this->arParams["RECIPIENT_ID"]), 'LOG_ENTRY', $arResult["LOG_ENTRY"]["EVENT"]["ID"], $arResult["SITE"]["ID"]);
			if ($backUrl)
			{
				$arResult["LOG_ENTRY_URL"] = $backUrl;
			}
		}
	}

	private function parseText($text)
	{
		static $parser = false;

		$arResult =& $this->arResult;

		if (!$parser)
		{
			if (Loader::includeModule('forum'))
			{
				$parser = new forumTextParser($arResult["SITE"]["LANGUAGE_ID"]);
			}
			else
			{
				$parser = new logTextParser($arResult["SITE"]["LANGUAGE_ID"]);
			}
		}

		if (Loader::includeModule('forum'))
		{
			$result = $parser->convert(
				$text,
				array(
					"HTML" => "N",
					"ALIGN" => "Y",
					"ANCHOR" => "Y", "BIU" => "Y",
					"IMG" => "Y", "QUOTE" => "Y",
					"CODE" => "Y", "FONT" => "Y",
					"LIST" => "Y", "SMILES" => "Y",
					"NL2BR" => "Y", "MULTIPLE_BR" => "N",
					"VIDEO" => "Y", "LOG_VIDEO" => "N",
					"SHORT_ANCHOR" => "Y",
				),
				"html"
			);
		}
		else
		{
			$result = $parser->convert(
				$text,
				array(),
				array(
					"HTML" => "N",
					"ALIGN" => "Y",
					"ANCHOR" => "Y", "BIU" => "Y",
					"IMG" => "Y", "QUOTE" => "Y",
					"CODE" => "Y", "FONT" => "Y",
					"LIST" => "Y", "SMILES" => "Y",
					"NL2BR" => "Y", "MULTIPLE_BR" => "N",
					"VIDEO" => "Y", "LOG_VIDEO" => "N",
					"SHORT_ANCHOR" => "Y",
				)
			);
		}

		return $result;
	}
}
