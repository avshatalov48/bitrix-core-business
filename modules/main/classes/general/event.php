<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2020 Bitrix
 */

use Bitrix\Main\Mail;

/**
 * @deprecated
 */
class CAllEvent
{
	public static $EVENT_SITE_PARAMS = [];

	public static function CheckEvents()
	{
		return Mail\EventManager::checkEvents();
	}

	public static function ExecuteEvents()
	{
		return Mail\EventManager::executeEvents();
	}

	public static function CleanUpAgent()
	{
		return Mail\EventManager::cleanUpAgent();
	}

	public static function SendImmediate($event, $lid, $arFields, $Duplicate = "Y", $message_id = "", $files = array(), $languageId = '', array $filesContent = [])
	{
		foreach(GetModuleEvents("main", "OnBeforeEventAdd", true) as $arEvent)
			if(ExecuteModuleEventEx($arEvent, array(&$event, &$lid, &$arFields, &$message_id, &$files, &$languageId)) === false)
				return false;

		if(!is_array($arFields))
		{
			$arFields = array();
		}

		$arLocalFields = array(
			"EVENT_NAME" => $event,
			"C_FIELDS" => $arFields,
			"LID" => (is_array($lid)? implode(",", $lid) : $lid),
			"DUPLICATE" => ($Duplicate != "N"? "Y" : "N"),
			"MESSAGE_ID" => (intval($message_id) > 0? intval($message_id): ""),
			"DATE_INSERT" => GetTime(time(), "FULL"),
			"FILE" => $files,
			"LANGUAGE_ID" => ($languageId == ''? LANGUAGE_ID : $languageId),
			"ID" => "0",
			"FILES_CONTENT" => $filesContent,
		);

		return Mail\Event::sendImmediate($arLocalFields);
	}

	public static function Send($event, $lid, $arFields, $Duplicate = "Y", $message_id="", $files=array(), $languageId = '')
	{
		foreach(GetModuleEvents("main", "OnBeforeEventAdd", true) as $arEvent)
			if(ExecuteModuleEventEx($arEvent, array(&$event, &$lid, &$arFields, &$message_id, &$files, &$languageId)) === false)
				return false;

		$arLocalFields = array(
			"EVENT_NAME" => $event,
			"C_FIELDS" => $arFields,
			"LID" => (is_array($lid)? implode(",", $lid) : $lid),
			"DUPLICATE" => ($Duplicate != "N"? "Y" : "N"),
			"FILE" => $files,
			"LANGUAGE_ID" => ($languageId == ''? LANGUAGE_ID : $languageId),
		);
		if(intval($message_id) > 0)
			$arLocalFields["MESSAGE_ID"] = intval($message_id);

		$result = Mail\Event::send($arLocalFields);

		$id = false;
		if ($result->isSuccess())
		{
			$id = $result->getId();
		}
		return $id;
	}

	public static function fieldencode($s)
	{
		if(is_array($s))
		{
			$ret_val = '';
			foreach($s as $v)
				$ret_val .= ($ret_val <> ''? ', ':'').CEvent::fieldencode($v);
		}
		else
		{
			$ret_val = str_replace("%", "%2", $s);
			$ret_val = str_replace("&","%1", $ret_val);
			$ret_val = str_replace("=", "%3", $ret_val);
		}
		return $ret_val;
	}

	public static function ExtractMailFields($str)
	{
		$ar = explode("&", $str);
		$newar = array();
		foreach($ar as $val)
		{
			$val = str_replace("%1", "&", $val);
			$tar = explode("=", $val);
			$key = $tar[0];
			$val = $tar[1];
			$key = str_replace("%3", "=", $key);
			$val = str_replace("%3", "=", $val);
			$key = str_replace("%2", "%", $key);
			$val = str_replace("%2", "%", $val);
			if($key != "")
				$newar[$key] = $val;
		}
		return $newar;
	}

	public static function GetSiteFieldsArray($site_id)
	{
		if($site_id !== false && isset(static::$EVENT_SITE_PARAMS[$site_id]))
			return static::$EVENT_SITE_PARAMS[$site_id];

		$SITE_NAME = COption::GetOptionString("main", "site_name", $GLOBALS["SERVER_NAME"]);
		$SERVER_NAME = COption::GetOptionString("main", "server_name", $GLOBALS["SERVER_NAME"]);
		$DEFAULT_EMAIL_FROM = COption::GetOptionString("main", "email_from", "admin@".$GLOBALS["SERVER_NAME"]);

		if($site_id <> '')
		{
			$dbSite = CSite::GetByID($site_id);
			if($arSite = $dbSite->Fetch())
			{
				static::$EVENT_SITE_PARAMS[$site_id] = array(
					"SITE_NAME" => ($arSite["SITE_NAME"]<>''? $arSite["SITE_NAME"] : $SITE_NAME),
					"SERVER_NAME" => ($arSite["SERVER_NAME"]<>''? $arSite["SERVER_NAME"] : $SERVER_NAME),
					"DEFAULT_EMAIL_FROM" => ($arSite["EMAIL"]<>''? $arSite["EMAIL"] : $DEFAULT_EMAIL_FROM),
					"SITE_ID" => $arSite['ID'],
					"SITE_DIR" => $arSite['DIR'],
				);
				return static::$EVENT_SITE_PARAMS[$site_id];
			}
		}

		return array(
			"SITE_NAME" => $SITE_NAME,
			"SERVER_NAME" => $SERVER_NAME,
			"DEFAULT_EMAIL_FROM" => $DEFAULT_EMAIL_FROM
		);
	}

	public static function ReplaceTemplate($str, $ar, $bNewLineToBreak=false)
	{
		$str = str_replace("%", "%2", $str);
		foreach($ar as $key=>$val)
		{
			if($bNewLineToBreak && mb_strpos($val, "<") === false)
				$val = nl2br($val);
			$val = str_replace("%", "%2", $val);
			$val = str_replace("#", "%1", $val);
			$str = str_replace("#".$key."#", $val, $str);
		}
		$str = str_replace("%1", "#", $str);
		$str = str_replace("%2", "%", $str);

		return $str;
	}

	/**
	 * @deprecated See \Bitrix\Main\Mail\Mail::is8Bit()
	 */
	public static function Is8Bit($str)
	{
		return Mail\Mail::is8Bit($str);
	}

	/**
	 * @deprecated See \Bitrix\Main\Mail\Mail::encodeMimeString()
	 */
	public static function EncodeMimeString($text, $charset)
	{
		return Mail\Mail::encodeMimeString($text, $charset);
	}

	/**
	 * @deprecated See \Bitrix\Mail\Mail::encodeSubject()
	 */
	public static function EncodeSubject($text, $charset)
	{
		return Mail\Mail::encodeSubject($text, $charset);
	}

	/**
	 * @deprecated See \Bitrix\Main\Mail\Mail::encodeHeaderFrom()
	 */
	public static function EncodeHeaderFrom($text, $charset)
	{
		return Mail\Mail::encodeHeaderFrom($text, $charset);
	}

	/**
	 * @deprecated See \Bitrix\Main\Mail\Mail::getMailEol()
	 */
	public static function GetMailEOL()
	{
		return Mail\Mail::getMailEol();
	}

	/**
	 * @deprecated See \Bitrix\Main\Mail\Event::handleEvent()
	 */
	public static function HandleEvent($arEvent)
	{
		if(isset($arEvent['C_FIELDS']))
		{
			$arEvent['FIELDS'] = $arEvent['C_FIELDS'];
			unset($arEvent['C_FIELDS']);
		}

		return Mail\Event::handleEvent($arEvent);
	}
}

class CEvent extends CAllEvent
{
}
