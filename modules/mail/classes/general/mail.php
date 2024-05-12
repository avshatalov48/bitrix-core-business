<?php

use Bitrix\Mail\Helper\MailContact;
use Bitrix\Mail\MailMessageTable;
use Bitrix\Main\Application;
use Bitrix\Main\Text\BinaryString;
use Bitrix\Main\Text\Emoji;
use Bitrix\Main\Config\Ini;
use Bitrix\Mail\Internals\MessageClosureTable;

IncludeModuleLangFile(__FILE__);

global $BX_MAIL_ERRORs, $B_MAIL_MAX_ALLOWED;
$BX_MAIL_ERRORs = Array();
$B_MAIL_MAX_ALLOWED = false;

class CMail
{
	const ERR_DEFAULT = 1;
	const ERR_DB      = 2;

	const ERR_API_DEFAULT            = 101;
	const ERR_API_DENIED             = 102;
	const ERR_API_DOMAINLIST_EMPTY   = 103;
	const ERR_API_NAME_OCCUPIED      = 104;
	const ERR_API_USER_NOTFOUND      = 105;
	const ERR_API_EMPTY_DOMAIN       = 106;
	const ERR_API_EMPTY_NAME         = 107;
	const ERR_API_EMPTY_PASSWORD     = 108;
	const ERR_API_SHORT_PASSWORD     = 109;
	const ERR_API_BAD_NAME           = 110;
	const ERR_API_BAD_PASSWORD       = 111;
	const ERR_API_PASSWORD_LIKELOGIN = 112;
	const ERR_API_LONG_NAME          = 113;
	const ERR_API_LONG_PASSWORD      = 114;
	const ERR_API_OP_DENIED          = 115;
	const ERR_API_OLD_TOKEN          = 116;

	const ERR_API_DOMAIN_OCCUPIED    = 201;
	const ERR_API_BAD_DOMAIN         = 202;
	const ERR_API_PROHIBITED_DOMAIN  = 203;

	const ERR_ENTRY_NOT_FOUND        = 301;

	const F_DOMAIN_LOGO = 1;
	const F_DOMAIN_REG  = 2;

	public static function getErrorMessage($code)
	{
		switch ($code)
		{
			case self::ERR_DB:
				return GetMessage('MAIL_ERR_DB');
			case self::ERR_API_DEFAULT:
				return GetMessage('MAIL_ERR_API_DEFAULT');
			case self::ERR_API_DENIED:
				return GetMessage('MAIL_ERR_API_DENIED');
			case self::ERR_API_NAME_OCCUPIED:
				return GetMessage('MAIL_ERR_API_NAME_OCCUPIED');
			case self::ERR_API_USER_NOTFOUND:
				return GetMessage('MAIL_ERR_API_USER_NOTFOUND');
			case self::ERR_API_EMPTY_DOMAIN:
				return GetMessage('MAIL_ERR_API_EMPTY_DOMAIN');
			case self::ERR_API_EMPTY_NAME:
				return GetMessage('MAIL_ERR_API_EMPTY_NAME');
			case self::ERR_API_EMPTY_PASSWORD:
				return GetMessage('MAIL_ERR_API_EMPTY_PASSWORD');
			case self::ERR_API_SHORT_PASSWORD:
				return GetMessage('MAIL_ERR_API_SHORT_PASSWORD');
			case self::ERR_API_BAD_NAME:
				return GetMessage('MAIL_ERR_API_BAD_NAME');
			case self::ERR_API_BAD_PASSWORD:
				return GetMessage('MAIL_ERR_API_BAD_PASSWORD');
			case self::ERR_API_PASSWORD_LIKELOGIN:
				return GetMessage('MAIL_ERR_API_PASSWORD_LIKELOGIN');
			case self::ERR_API_LONG_NAME:
				return GetMessage('MAIL_ERR_API_LONG_NAME');
			case self::ERR_API_LONG_PASSWORD:
				return GetMessage('MAIL_ERR_API_LONG_PASSWORD');
			case self::ERR_API_OP_DENIED:
				return GetMessage('MAIL_ERR_API_OP_DENIED');
			case self::ERR_API_OLD_TOKEN:
				return getMessage('MAIL_ERR_API_OLD_TOKEN');
			case self::ERR_API_DOMAIN_OCCUPIED:
				return GetMessage('MAIL_ERR_API_DOMAIN_OCCUPIED');
			case self::ERR_API_BAD_DOMAIN:
				return GetMessage('MAIL_ERR_API_BAD_DOMAIN');
			case self::ERR_API_PROHIBITED_DOMAIN:
				return GetMessage('MAIL_ERR_API_PROHIBITED_DOMAIN');
			case self::ERR_ENTRY_NOT_FOUND:
				return GetMessage('MAIL_ERR_ENTRY_NOT_FOUND');
			default:
				return GetMessage('MAIL_ERR_DEFAULT');
		}
	}

	public static function onUserUpdate($arFields)
	{
		if ($arFields['RESULT'] && isset($arFields['ACTIVE']) && $arFields['ACTIVE'] == 'N')
		{
			$selectResult = CMailbox::getList(array(), array('USER_ID' => intval($arFields['ID']), 'ACTIVE' => 'Y'));
			while ($mailbox = $selectResult->fetch())
				CMailbox::update($mailbox['ID'], array('ACTIVE' => 'N'));
		}
	}

	public static function onUserDelete($id)
	{
		$selectResult = CMailbox::getList(array(), array('USER_ID' => intval($id)));
		while ($mailbox = $selectResult->fetch())
			CMailbox::delete($mailbox['ID']);
	}

	public static function option($name, $value = null)
	{
		static $options;

		if (!is_scalar($name))
			throw new \Bitrix\Main\ArgumentTypeException('name');

		if (is_null($options))
			$options = array();

		if (is_null($value))
		{
			return array_key_exists($name, $options) ? $options[$name] : null;
		}
		else
		{
			$options[$name] = $value;
			return $value;
		}
	}

}

class CMailError
{
	public static function ResetErrors()
	{
		global $BX_MAIL_ERRORs;
		$BX_MAIL_ERRORs = Array();
	}

	public static function SetError($ID, $TITLE="", $DESC="")
	{
		global $BX_MAIL_ERRORs;
		$BX_MAIL_ERRORs[] = array("ID"=>$ID, "TITLE"=>$TITLE, "DESCRIPTION"=>$DESC);
		return false;
	}

	public static function GetLastError($type=false)
	{
		global $BX_MAIL_ERRORs;
		if($type===false)
			return $BX_MAIL_ERRORs[count($BX_MAIL_ERRORs)-1];
		return $BX_MAIL_ERRORs[count($BX_MAIL_ERRORs)-1][$type];
	}

	public static function GetErrors()
	{
		global $BX_MAIL_ERRORs;
		return $BX_MAIL_ERRORs;
	}

	public static function GetErrorsText($delim="<br>")
	{
		global $BX_MAIL_ERRORs;
		$str = "";
		foreach($BX_MAIL_ERRORs as $err)
		{
			if ($str!="")
				$str .= $delim;
			$str.=$err["TITLE"];
		}
		return $str;
	}

	public static function ErrCount()
	{
		global $BX_MAIL_ERRORs;
		if(!is_array($BX_MAIL_ERRORs))
			return 0;
		return count($BX_MAIL_ERRORs);
	}
}


class _CMailBoxDBRes extends CDBResult
{
	function __construct($res)
	{
		parent::__construct($res);
	}

	function Fetch()
	{
		if($res = parent::Fetch())
		{
			if(!Bitrix\Main\Loader::includeModule('mail'))
			{
				return false;
			}

			$entity = \Bitrix\Mail\MailboxTable::getEntity();

			foreach ($res as $alias => $value)
			{
				if (!$entity->hasField($alias))
				{
					continue;
				}

				foreach ($entity->getField($alias)->getFetchDataModifiers() as $modifier)
				{
					$res[$alias] = call_user_func_array($modifier, array($res[$alias], $this, $res, $alias));
				}
			}
		}
		return $res;
	}
}
///////////////////////////////////////////////////////////////////////////////////
// class CMailBox
///////////////////////////////////////////////////////////////////////////////////
class CAllMailBox
{
	var $pop3_conn = false;
	var $mess_count = 0;
	var $mess_size = 0;
	var $resp = true;
	var $last_result = true;
	var $response = "";
	var $response_body = "";
	public $mailbox_id = 0;
	public $new_mess_count = 0;
	public $deleted_mess_count = 0;

	public static function GetList($arOrder=[], $arFilter=[])
	{
		global $DB;
		$strSql =
				"SELECT MB.*, C.CHARSET as LANG_CHARSET, ".
				"	".$DB->DateToCharFunction("MB.TIMESTAMP_X")."	as TIMESTAMP_X ".
				"FROM b_mail_mailbox MB, b_lang L, b_culture C ".
				"WHERE MB.LID=L.LID AND C.ID=L.CULTURE_ID";

		if(!is_array($arFilter))
		{
			$arFilter = [];
		}

		$arSqlSearch = [];
		$filter_keys = array_keys($arFilter);
		for($i = 0, $n = count($filter_keys); $i < $n; $i++)
		{
			$val = $arFilter[$filter_keys[$i]];

			if (is_null($val) || $val === '')
			{
				continue;
			}

			$key = mb_strtoupper($filter_keys[$i]);

			$strNegative = false;
			if (mb_substr($key, 0, 1) == '!')
			{
				$key = mb_substr($key, 1);
				$strNegative = 'Y';
			}

			$strExact = false;
			if (mb_substr($key, 0, 1) == '=')
			{
				$key = mb_substr($key, 1);
				$strExact = 'Y';
			}

			switch ($key)
			{
				case 'ID':
				case 'PORT':
				case 'DELETE_MESSAGES':
				case 'ACTIVE':
				case 'USE_MD5':
				case 'RELAY':
				case 'AUTH_RELAY':
					$arSqlSearch[] = GetFilterQuery('MB.'.$key, ($strNegative == 'Y' ? '~' : '').$val, 'N');
					break;
				case 'LID':
				case 'LOGIN':
				case 'SERVER':
				case 'NAME':
				case 'DESCRIPTION':
				case 'DOMAINS':
				case 'SERVER_TYPE':
					$arSqlSearch[] = GetFilterQuery('MB.'.$key, ($strNegative == 'Y' ? '~' : '').$val, $strExact == 'Y' ? 'N' : 'Y');
					break;
				case 'SERVICE_ID':
				case 'USER_ID':
					$arSqlSearch[] = 'MB.' . $key . ($strNegative == 'Y' ? ' != ' : ' = ') . intval($val);
					break;
			}
		}

		$is_filtered = false;
		$strSqlSearch = "";
		for($i = 0, $n = count($arSqlSearch); $i < $n; $i++)
		{
			if($arSqlSearch[$i] <> '')
			{
				$is_filtered = true;
				$strSqlSearch .= " AND  (".$arSqlSearch[$i].") ";
			}
		}

		$arSqlOrder = Array();
		foreach($arOrder as $by=>$order)
		{
			$order = mb_strtolower($order);
			if ($order!="asc")
				$order = "desc".($DB->type == "ORACLE"?" NULLS LAST":"");
			else
				$order = "asc".($DB->type == "ORACLE"?" NULLS FIRST":"");

			switch(mb_strtoupper($by))
			{
				case "TIMESTAMP_X":
				case "LID":
				case "ACTIVE":
				case "NAME":
				case "SERVER":
				case "PORT":
				case "LOGIN":
				case "USE_MD5":
				case "DELETE_MESSAGES":
				case "RELAY":
				case "AUTH_RELAY":
				case "SERVER_TYPE":
				case "PERIOD_CHECK":
					$arSqlOrder[] = " MB.".$by." ".$order." ";
					break;
				default:
					$arSqlOrder[] = " MB.ID ".$order." ";
			}
		}

		$strSqlOrder = "";
		$arSqlOrder = array_unique($arSqlOrder);
		DelDuplicateSort($arSqlOrder);

		for ($i = 0, $n = count($arSqlOrder); $i < $n; $i++)
		{
			if($i==0)
				$strSqlOrder = " ORDER BY ";
			else
				$strSqlOrder .= ",";

			$strSqlOrder .= $arSqlOrder[$i];
		}

		$strSql .= $strSqlSearch.$strSqlOrder;

		$res = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		$res = new _CMailBoxDBRes($res);
		$res->is_filtered = $is_filtered;
		return $res;
	}

	public static function GetByID($ID)
	{
		return CMailBox::GetList(Array(), Array("ID"=>$ID));
	}

	function CheckMail($mailbox_id = false)
	{
		global $DB;
		$mbx = Array();
		if($mailbox_id===false)
		{
			$strSql =
					"SELECT MB.ID ".
					"FROM b_mail_mailbox MB ".
					"WHERE ACTIVE='Y' ";

			$dbr = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
			while($ar = $dbr->Fetch())
				$mbx[] = $ar["ID"];
		}
		else
		{
			$mbx[] = $mailbox_id;
		}

		$bNoErrors = true;
		foreach($mbx as $mailboxId)
		{
			$mb = new CMailbox();
			if(!$mb->Connect($mailboxId))
			{
				$bNoErrors = false;
				CMailError::SetError("ERR_CHECK_MAIL", GetMessage("MAIL_CL_ERR_CHECK_MAIL")." (mailbox id: ".$mailboxId.").", "");
			}
		}

		return $bNoErrors;
	}

	public static function CheckMailAgent($ID)
	{
		global $DB, $USER;
		$bUserCreated = false;
		if (!isset($USER) || !is_object($USER))
		{
			$USER = new CUser();
			$bUserCreated = true;
		}
		$ID = intval($ID);
		$strSql =
				"SELECT MB.ID, MB.PERIOD_CHECK ".
				"FROM b_mail_mailbox MB ".
				"WHERE ACTIVE='Y' ".
				"	AND ID=".$ID.
				"	AND USER_ID = 0";

		$strReturn = '';
		$dbr = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		if($ar = $dbr->Fetch())
		{
			$mb = new CMailbox();
			$mb->Connect($ID);
			if(intval($ar["PERIOD_CHECK"])>0)
				$strReturn = "CMailbox::CheckMailAgent(".$ID.");";
		}
		if ($bUserCreated)
		{
			unset($USER);
		}
		return $strReturn;
	}

	public static function CheckFields($arFields, $ID=false)
	{
		global $APPLICATION;
		$arMsg = array();

		if (is_set($arFields, 'NAME') && mb_strlen($arFields['NAME']) < 1)
		{
			CMailError::SetError('B_MAIL_ERR_NAME', GetMessage('MAIL_CL_ERR_NAME').' "'.GetMessage('MAIL_CL_NAME').'"');
			$arMsg[] = array('id' => 'NAME', 'text' => GetMessage('MAIL_CL_ERR_NAME').' "'.GetMessage('MAIL_CL_NAME').'"');
		}

		if (in_array(mb_strtolower($arFields['SERVER_TYPE']), array('pop3', 'imap', 'controller', 'domain', 'crdomain')) && is_set($arFields, 'LOGIN') && mb_strlen($arFields['LOGIN']) < 1)
		{
			CMailError::SetError('B_MAIL_ERR_LOGIN', GetMessage('MAIL_CL_ERR_NAME').' "'.GetMessage('MAIL_CL_LOGIN').'"');
			$arMsg[] = array('id' => 'LOGIN', 'text' => GetMessage('MAIL_CL_ERR_NAME').' "'.GetMessage('MAIL_CL_LOGIN').'"');
		}

		if (in_array(mb_strtolower($arFields['SERVER_TYPE']), array('pop3', 'imap')) && is_set($arFields, 'PASSWORD') && mb_strlen($arFields['PASSWORD']) < 1)
		{
			CMailError::SetError('B_MAIL_ERR_PASSWORD', GetMessage('MAIL_CL_ERR_NAME').' "'.GetMessage('MAIL_CL_PASSWORD').'"');
			$arMsg[] = array('id' => 'PASSWORD', 'text' => GetMessage('MAIL_CL_ERR_NAME').' "'.GetMessage('MAIL_CL_PASSWORD').'"');
		}

		if (in_array(mb_strtolower($arFields['SERVER_TYPE']), array('controller', 'domain', 'crdomain')) && is_set($arFields, 'USER_ID') && $arFields['USER_ID'] < 1)
		{
			CMailError::SetError('B_MAIL_ERR_USER_ID', GetMessage('MAIL_CL_ERR_NAME').' "'.GetMessage('MAIL_CL_USER_ID').'"');
			$arMsg[] = array('id' => 'USER_ID', 'text' => GetMessage('MAIL_CL_ERR_NAME').' "'.GetMessage('MAIL_CL_USER_ID').'"');
		}

		if (in_array(mb_strtolower($arFields['SERVER_TYPE']), array('pop3', 'smtp', 'imap')) && is_set($arFields, 'SERVER') && mb_strlen($arFields['SERVER']) < 1)
		{
			CMailError::SetError('B_MAIL_ERR_SERVER_NAME', GetMessage('MAIL_CL_ERR_NAME').' "'.GetMessage('MAIL_CL_SERVER').'"');
			$arMsg[] = array('id' => 'SERVER', 'text' => GetMessage('MAIL_CL_ERR_NAME').' "'.GetMessage('MAIL_CL_SERVER').'"');
		}
		elseif (mb_strtolower($arFields['SERVER_TYPE']) == 'smtp')
		{
			$dbres = CMailBox::GetList(array(), array('ACTIVE' => 'Y', 'SERVER_TYPE' => 'smtp', 'SERVER' => $arFields['SERVER'], 'PORT' => $arFields['PORT']));
			while($arres = $dbres->Fetch())
			{
				if ($ID === false || $arres['ID'] != $ID)
				{
					CMailError::SetError('B_MAIL_ERR_SERVER_NAME',  GetMessage('B_MAIL_ERR_SN').' "'.GetMessage('MAIL_CL_SERVER').'"');
					$arMsg[] = array('id' => 'SERVER', 'text' => GetMessage('B_MAIL_ERR_SN').' "'.GetMessage('MAIL_CL_SERVER').'"');
					break;
				}
			}
		}

		if (is_set($arFields, 'LID'))
		{
			$r = CLang::GetByID($arFields['LID']);
			if (!$r->Fetch())
			{
				CMailError::SetError('B_MAIL_ERR_BAD_LANG', GetMessage('MAIL_CL_ERR_BAD_LANG'));
				$arMsg[] = array('id' => 'LID', 'text' => GetMessage('MAIL_CL_ERR_BAD_LANG'));
			}
		}
		elseif ($ID === false)
		{
			CMailError::SetError('B_MAIL_ERR_BAD_LANG_NA', GetMessage('MAIL_CL_ERR_BAD_LANG_NX'));
			$arMsg[] = array('id' => 'LID', 'text' => GetMessage('MAIL_CL_ERR_BAD_LANG_NX'));
		}

		if (in_array(mb_strtolower($arFields['SERVER_TYPE']), array('imap', 'controller', 'domain', 'crdomain')))
		{
			if (is_set($arFields, 'SERVICE_ID'))
			{
				if (!empty($arFields['LID']) || $ID)
				{
					$LID_tmp = $arFields['LID'];
					if (empty($arFields['LID']))
					{
						$arMb_tmp = CMailBox::GetList(array(), array('ID' => $ID))->fetch();
						$LID_tmp = $arMb_tmp['LID'];
					}
					$result = Bitrix\Mail\MailServicesTable::getList(array(
						'filter' => array('=SITE_ID' => $LID_tmp, '=ID' => $arFields['SERVICE_ID'])
					));
					if (!$result->fetch())
					{
						CMailError::SetError('B_MAIL_ERR_BAD_SERVICE_ID', GetMessage('MAIL_CL_ERR_BAD_SERVICE_ID'));
						$arMsg[] = array('id' => 'SERVICE_ID', 'text' => GetMessage('MAIL_CL_ERR_BAD_SERVICE_ID'));
					}
				}
			}
			else if ($ID === false)
			{
				CMailError::SetError('B_MAIL_ERR_BAD_SERVICE_ID_NA', GetMessage('MAIL_CL_ERR_BAD_SERVICE_ID_NX'));
				$arMsg[] = array('id' => 'SERVICE_ID', 'text' => GetMessage('MAIL_CL_ERR_BAD_SERVICE_ID_NX'));
			}
		}

		if (!empty($arMsg))
		{
			$e = new CAdminException($arMsg);
			$APPLICATION->ThrowException($e);
			return false;
		}

		return true;
	}

	public static function Add($arFields)
	{
		global $DB;
		CMailError::ResetErrors();

		$arFields = array_filter($arFields, 'is_set');

		if($arFields["ACTIVE"] != "Y")
			$arFields["ACTIVE"] = "N";

		if($arFields["DELETE_MESSAGES"] != "Y")
			$arFields["DELETE_MESSAGES"] = "N";

		if($arFields["USE_MD5"] != "Y")
			$arFields["USE_MD5"] = "N";

		if ($arFields['USE_TLS'] != 'Y' && $arFields['USE_TLS'] != 'S')
			$arFields["USE_TLS"] = "N";

		if (!in_array($arFields["SERVER_TYPE"], array("pop3", "smtp", "imap", "controller", "domain", "crdomain")))
			$arFields["SERVER_TYPE"] = "pop3";

		if (!CMailBox::CheckFields($arFields))
			return false;

		$ID = \Bitrix\Mail\MailboxTable::add($arFields)->getId();
		if ($arFields['ACTIVE'] == 'Y' && $arFields['USER_ID'] != 0)
		{
			CUserCounter::Clear($arFields['USER_ID'], 'mail_unseen', $arFields['LID']);
			$mailboxSyncManager = new \Bitrix\Mail\Helper\Mailbox\MailboxSyncManager($arFields['USER_ID']);
			$mailboxSyncManager->setDefaultSyncData($ID);
		}
		if (in_array($arFields['SERVER_TYPE'], array('imap', 'controller', 'domain', 'crdomain')))
		{
			\CAgent::addAgent(sprintf('Bitrix\Mail\Helper::syncMailboxAgent(%u);', $ID), 'mail', 'N', (int) $arFields['PERIOD_CHECK'] * 60);
			\CAgent::addAgent(sprintf('Bitrix\Mail\Helper::cleanupMailboxAgent(%u);', $ID), 'mail', 'N', 3600 * 24);
		}

		if ($arFields['SERVER_TYPE'] == 'pop3' && (int) $arFields['PERIOD_CHECK'] > 0)
			CAgent::addAgent(sprintf('CMailbox::CheckMailAgent(%u);', $ID), 'mail', 'N', (int) $arFields['PERIOD_CHECK']*60);

		CMailbox::SMTPReload();
		return $ID;
	}

	public static function Update($ID, $arFields)
	{
		$ID = intval($ID);

		CMailError::ResetErrors();

		$arFields = array_filter($arFields, 'is_set');

		if(is_set($arFields, "ACTIVE") && $arFields["ACTIVE"]!="Y")
			$arFields["ACTIVE"]="N";

		if(is_set($arFields, "DELETE_MESSAGES") && $arFields["DELETE_MESSAGES"]!="Y")
			$arFields["DELETE_MESSAGES"]="N";

		if(is_set($arFields, "USE_MD5") && $arFields["USE_MD5"]!="Y")
			$arFields["USE_MD5"]="N";

		if(is_set($arFields, 'USE_TLS') && $arFields['USE_TLS'] != 'Y' && $arFields['USE_TLS'] != 'S')
			$arFields["USE_TLS"]="N";

		if (is_set($arFields, "SERVER_TYPE") && !in_array($arFields["SERVER_TYPE"], array("pop3", "smtp", "imap", "controller", "domain", "crdomain")))
			$arFields["SERVER_TYPE"] = "pop3";

		if(!CMailBox::CheckFields($arFields, $ID))
			return false;

		$mbox = \Bitrix\Mail\MailboxTable::getRowById($ID);

		$serverType  = is_set($arFields, 'SERVER_TYPE') ? $arFields['SERVER_TYPE'] : $mbox['SERVER_TYPE'];
		$periodCheck = is_set($arFields, 'PERIOD_CHECK') ? $arFields['PERIOD_CHECK'] : $mbox['PERIOD_CHECK'];

		if (!empty($mbox))
		{
			$userChanged = isset($arFields['USER_ID']) && $mbox['USER_ID'] != $arFields['USER_ID'];
			$siteChanged = isset($arFields['LID']) && $mbox['LID'] != $arFields['LID'];

			if ($userChanged || $siteChanged)
			{
				if ($mbox['ACTIVE'] == 'Y')
				{
					if ($mbox['USER_ID'] > 0)
					{
						$mailboxSyncManager = new \Bitrix\Mail\Helper\Mailbox\MailboxSyncManager($mbox['USER_ID']);
						$mailboxSyncManager->deleteSyncData($mbox['ID']);
					}
				}

				$newActive = isset($arFields['ACTIVE']) ? $arFields['ACTIVE'] : $mbox['ACTIVE'];
				if ($newActive == 'Y')
				{
					$newUserId = isset($arFields['USER_ID']) ? $arFields['USER_ID'] : $mbox['USER_ID'];
					$newSiteId = isset($arFields['LID']) ? $arFields['LID'] : $mbox['LID'];

					if ($newUserId > 0)
					{
						$mailboxSyncManager = new \Bitrix\Mail\Helper\Mailbox\MailboxSyncManager($newUserId);
						$mailboxSyncManager->setDefaultSyncData($mbox['ID']);
					}
				}
			}

			if ($mbox['USER_ID'] != 0 || isset($arFields['USER_ID']) && $arFields['USER_ID'] != 0)
			{
				CUserCounter::Clear($mbox['USER_ID'], 'mail_unseen', $mbox['LID']);
				if ($siteChanged)
					CUserCounter::Clear($mbox['USER_ID'], 'mail_unseen', $arFields['LID']);

				if ($userChanged)
				{
					CUserCounter::Clear($arFields['USER_ID'], 'mail_unseen', $mbox['LID']);
					if (isset($arFields['LID']) && $mbox['LID'] != $arFields['LID'])
						CUserCounter::Clear($arFields['USER_ID'], 'mail_unseen', $arFields['LID']);
				}
			}
		}

		\CAgent::removeAgent(sprintf('CMailbox::CheckMailAgent(%u);', $ID), 'mail');
		\CAgent::removeAgent(sprintf('Bitrix\Mail\Helper::syncMailboxAgent(%u);', $ID), 'mail');
		\CAgent::removeAgent(sprintf('Bitrix\Mail\Helper::cleanupMailboxAgent(%u);', $ID), 'mail');

		\Bitrix\Mail\MailboxTable::update($ID, $arFields);

		if (in_array($serverType, array('imap', 'controller', 'domain', 'crdomain')))
		{
			\CAgent::addAgent(sprintf('Bitrix\Mail\Helper::syncMailboxAgent(%u);', $ID), 'mail', 'N', (int) $periodCheck*60);
			\CAgent::addAgent(sprintf('Bitrix\Mail\Helper::cleanupMailboxAgent(%u);', $ID), 'mail', 'N', 3600 * 24);
		}

		if ($serverType == 'pop3' && (int) $periodCheck > 0)
			CAgent::addAgent(sprintf('CMailbox::CheckMailAgent(%u);', $ID), 'mail', 'N', (int) $periodCheck*60);

		CMailbox::SMTPReload();
		return true;
	}

	/**
	 * Clears all database entries associated with the mailbox.
	 *
	 * @param string $ID mailbox id.
	 *
	 * @return CDBResult|false
	 */
	public static function Delete($ID)
	{
		global $DB;
		$ID = intval($ID);

		Bitrix\Main\Loader::includeModule('mail');
		$db_msg = Bitrix\Mail\MailMessageTable::getList(array(
			'select' => array('ID'),
			'filter' => array('MAILBOX_ID' => $ID)
		));
		while($msg = $db_msg->Fetch())
		{
			if(!CMailMessage::Delete($msg["ID"]))
				return false;
		}

		$db_flt = CMailFilter::GetList(Array(), Array("MAILBOX_ID"=>$ID));
		while($flt = $db_flt->Fetch())
		{
			if(!CMailFilter::Delete($flt["ID"]))
				return false;
		}

		$db_mbox = \CMailbox::getList(array('ID' => $ID, 'ACTIVE' => 'Y'));
		if ($mbox = $db_mbox->fetch())
		{
			if ($mbox['USER_ID'] > 0)
			{
				\CUserCounter::clear($mbox['USER_ID'], 'mail_unseen', $mbox['LID']);
				$mailboxSyncManager = new \Bitrix\Mail\Helper\Mailbox\MailboxSyncManager($mbox['USER_ID']);
				$mailboxSyncManager->deleteSyncData($ID);
			}
		}

		\CAgent::removeAgent(sprintf('CMailbox::CheckMailAgent(%u);', $ID), 'mail');
		\CAgent::removeAgent(sprintf('Bitrix\Mail\Helper::syncMailboxAgent(%u);', $ID), 'mail');
		\CAgent::removeAgent(sprintf('Bitrix\Mail\Helper::cleanupMailboxAgent(%u);', $ID), 'mail');

		$strSql = "DELETE FROM b_mail_log WHERE MAILBOX_ID=".$ID;
		if(!$DB->Query($strSql, true))
			return false;

		$strSql = "DELETE FROM b_mail_message_uid WHERE MAILBOX_ID=".$ID;
		if(!$DB->Query($strSql, true))
			return false;

		// @TODO: make a log optional
		//AddMessage2Log("The mailbox $ID was deleted");

		$strSql = "DELETE FROM b_mail_blacklist WHERE MAILBOX_ID=".$ID;
		if(!$DB->Query($strSql, true))
			return false;

		$DB->query(sprintf('DELETE FROM b_mail_mailbox_access WHERE MAILBOX_ID = %u', $ID));
		$DB->query(sprintf('DELETE FROM b_mail_mailbox_dir WHERE MAILBOX_ID = %u', $ID));
		$DB->query(sprintf('DELETE FROM b_mail_counter WHERE MAILBOX_ID = %u', $ID));
		$DB->query(sprintf('DELETE FROM b_mail_entity_options WHERE MAILBOX_ID = %u', $ID));

		CMailbox::SMTPReload();
		$strSql = "DELETE FROM b_mail_mailbox WHERE ID=".$ID;
		return $DB->Query($strSql, true);
	}

	public static function SMTPReload()
	{
		global $CACHE_MANAGER;
		$CACHE_MANAGER->Read(3600000, $cache_id = "smtpd_reload");
		$CACHE_MANAGER->Set($cache_id, true);
	}

	function SendCommand($command)
	{
		//SSRF "filter"
		$command = preg_replace("/[\\n\\r]/", "", $command);

		fputs($this->pop3_conn, $command."\r\n");

		if($this->mailbox_id>0)
		{
			CMailLog::AddMessage(
				Array(
					"MAILBOX_ID"=>$this->mailbox_id,
					"STATUS_GOOD"=>"Y",
					"MESSAGE"=>"> ".nl2br(preg_replace("'PASS .*'", "PASS ******", $command))
					)
				);
		}
		$this->resp = true;
	}

	function GetResponse($bMultiline = false, $bSkipFirst = true)
	{
		if(!$this->resp) return false;
		$this->resp = false;

		socket_set_timeout($this->pop3_conn, 20);
		$res = rtrim(fgets($this->pop3_conn, 1024), "\r\n");
//		socket_set_blocking($this->pop3_conn, false);
//		socket_set_blocking($this->pop3_conn, true);

		$this->last_result = ($res[0]=="+");
		$this->response = $res;

		if($this->mailbox_id>0)
		{
			CMailLog::AddMessage(
				Array(
					"MAILBOX_ID"=>$this->mailbox_id,
					"STATUS_GOOD"=>($this->last_result?"Y":"N"),
					"MESSAGE"=>"< ".$res
					)
				);
		}

		if($bMultiline && $res[0]=="+")
		{
			if($bSkipFirst)
				$res = "";
			else
				$res .= "\r\n";

			$s = fgets($this->pop3_conn, 1024);
			while($s <> '' && $s!=".\r\n")
			{
				if(mb_substr($s, 0, 2) == "..")
					$s = mb_substr($s, 1);
				$res .= $s;
				$s = fgets($this->pop3_conn, 1024);
			}
		}
		$this->response_body = $res;
		return $this->last_result;
	}

	function GetResponseBody()
	{
		return $this->response_body;
	}

	function GetResponseString()
	{
		return $this->response_body;
	}

	function GetPassword($p)
	{
	}

	function Check($server, $port, $use_tls, $login, $passw)
	{
		if (($use_tls == 'Y' || $use_tls == 'S') && !preg_match('#^(tls|ssl)://#', $server))
			$server = 'ssl://' . $server;

		$skip_cert = $use_tls != 'Y';

		$pop3_conn = &$this->pop3_conn;
		$pop3_conn = stream_socket_client(
			sprintf('%s:%s', $server, $port),
			$errno, $errstr,
			COption::getOptionInt('mail', 'connect_timeout', B_MAIL_TIMEOUT),
			STREAM_CLIENT_CONNECT,
			stream_context_create(array('ssl' => array('verify_peer' => !$skip_cert, 'verify_peer_name' => !$skip_cert)))
		);
		if(!$pop3_conn)
			return array(false, GetMessage("MAIL_CL_TIMEOUT")." $errstr ($errno)");

		$this->GetResponse();
		$greeting = $this->GetResponseString();

		$this->SendCommand("USER ".$login);
		if(!$this->GetResponse())
			return array(false, GetMessage("MAIL_CL_ERR_USER").' ('.$this->GetResponseString().')');
		$this->SendCommand("PASS ".$passw);
		if(!$this->GetResponse())
			return array(false, GetMessage("MAIL_CL_ERR_PASSWORD").' ('.$this->GetResponseString().')');

		$this->SendCommand("STAT");

		if(!$this->GetResponse())
			return array(false, GetMessage("MAIL_CL_ERR_STAT").' ('.$this->GetResponseString().')');

		$stat = trim($this->GetResponseBody());
		$arStat = explode(" ", $stat);
		return array(true, $arStat[1]);
	}

	function Connect($mailbox_id)
	{
		global $DB;
		$mailbox_id = intval($mailbox_id);
		$strSql =
				"SELECT MB.*, C.CHARSET as LANG_CHARSET ".
				"FROM b_mail_mailbox MB, b_lang L, b_culture C ".
				"WHERE MB.LID=L.LID AND C.ID=L.CULTURE_ID ".
				"	AND MB.ID=".$mailbox_id;
		$dbr = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		$dbr = new _CMailBoxDBRes($dbr);
		if(!$arMAILBOX_PARAMS = $dbr->Fetch())
			return CMailError::SetError("ERR_MAILBOX_NOT_FOUND", GetMessage("MAIL_CL_ERR_MAILBOX_NOT_FOUND"), GetMessage("MAIL_CL_ERR_MAILBOX_NOT_FOUND"));

		if ($arMAILBOX_PARAMS['SYNC_LOCK'] > time()-600)
			return;

		$DB->query('UPDATE b_mail_mailbox SET SYNC_LOCK = '.time().' WHERE ID = '.$mailbox_id);

		$result = $this->_connect($mailbox_id, $arMAILBOX_PARAMS);

		$DB->query('UPDATE b_mail_mailbox SET SYNC_LOCK = 0 WHERE ID = '.$mailbox_id);

		return $result;
	}

	private function _connect($mailbox_id, $arMAILBOX_PARAMS)
	{
		global $DB;

		@set_time_limit(0);

		// https://support.google.com/mail/answer/47948
		if ($arMAILBOX_PARAMS["SERVER"] == 'pop.gmail.com')
			$arMAILBOX_PARAMS["LOGIN"] = 'recent:' . $arMAILBOX_PARAMS["LOGIN"];

		$server = $arMAILBOX_PARAMS["SERVER"];
		if (($arMAILBOX_PARAMS['USE_TLS'] == 'Y' || $arMAILBOX_PARAMS['USE_TLS'] == 'S') && !preg_match('#^(tls|ssl)://#', $server))
			$server = 'ssl://' . $server;

		$skip_cert = $arMAILBOX_PARAMS['USE_TLS'] != 'Y';

		$pop3_conn = &$this->pop3_conn;
		$pop3_conn = stream_socket_client(
			sprintf('%s:%s', $server, $arMAILBOX_PARAMS["PORT"]),
			$errno, $errstr,
			COption::getOptionInt('mail', 'connect_timeout', B_MAIL_TIMEOUT),
			STREAM_CLIENT_CONNECT,
			stream_context_create(array('ssl' => array('verify_peer' => !$skip_cert, 'verify_peer_name' => !$skip_cert)))
		);

		CMailLog::AddMessage(
			Array(
				"MAILBOX_ID"=>$mailbox_id,
				"STATUS_GOOD"=>"Y",
				"MESSAGE"=>GetMessage("MAIL_CL_CONNECT_TO")." ".$arMAILBOX_PARAMS["SERVER"]
				)
			);

		if(!$pop3_conn || !is_resource($pop3_conn))
		{
			CMailLog::AddMessage(
				Array(
					"MAILBOX_ID"=>$mailbox_id,
					"STATUS_GOOD"=>"N",
					"MESSAGE"=>GetMessage("MAIL_CL_TIMEOUT")
					)
				);
			return CMailError::SetError("ERR_CONNECT_TIMEOUT", GetMessage("MAIL_CL_TIMEOUT"), "$errstr ($errno)");
		}

		$this->mailbox_id = $mailbox_id;
		if($arMAILBOX_PARAMS["CHARSET"]!='')
			$this->charset = $arMAILBOX_PARAMS["CHARSET"];
		else
			$this->charset = $arMAILBOX_PARAMS["LANG_CHARSET"];
		$this->use_md5 = $arMAILBOX_PARAMS["USE_MD5"];

		$session_id = md5(uniqid(""));
		$this->GetResponse();
		$greeting = $this->GetResponseString();

		if($this->use_md5=="Y" && preg_match("'(<.+>)'", $greeting, $reg))
		{
			$this->SendCommand("APOP ".$arMAILBOX_PARAMS["LOGIN"]." ".md5($reg[1].$arMAILBOX_PARAMS["PASSWORD"]));
			if(!$this->GetResponse())
				return CMailError::SetError("ERR_AFTER_USER", GetMessage("MAIL_CL_ERR_APOP"), $this->GetResponseString());
		}
		else
		{
			$this->SendCommand("USER ".$arMAILBOX_PARAMS["LOGIN"]);
			if(!$this->GetResponse())
				return CMailError::SetError("ERR_AFTER_USER", GetMessage("MAIL_CL_ERR_USER"), $this->GetResponseString());
			$this->SendCommand("PASS ".$arMAILBOX_PARAMS["PASSWORD"]);
			if(!$this->GetResponse())
				return CMailError::SetError("ERR_AFTER_PASS", GetMessage("MAIL_CL_ERR_PASSWORD"), $this->GetResponseString());
		}

		$this->SendCommand("STAT");
		if(!$this->GetResponse())
			return CMailError::SetError("ERR_AFTER_STAT", GetMessage("MAIL_CL_ERR_STAT"), $this->GetResponseString());

		$stat = trim($this->GetResponseBody());
		$arStat = explode(" ", $stat);
		$this->mess_count = $arStat[1];
		if($this->mess_count>0)
		{
			$this->mess_size = $arStat[2];
			$arLIST = array();

			if($arMAILBOX_PARAMS["MAX_MSG_SIZE"]>0)
			{
				$this->SendCommand("LIST");
				if(!$this->GetResponse(true))
					return CMailError::SetError("ERR_AFTER_LIST", "LIST command error", $this->GetResponseString());
				$list = $this->GetResponseBody();
				preg_match_all("'([0-9]+)[ ]+?(.+)'", $list, $arLIST_temp, PREG_SET_ORDER);

				for($i = 0, $n = count($arLIST_temp); $i < $n; $i++)
					$arLIST[intval($arLIST_temp[$i][1])] = intval($arLIST_temp[$i][2]);
			}

			$this->SendCommand("UIDL");
			if(!$this->GetResponse(true))
				return CMailError::SetError("ERR_AFTER_UIDL", GetMessage("MAIL_CL_ERR_UIDL"), $this->GetResponseString());

			$uidl = $this->GetResponseBody();
			preg_match_all("'([0-9]+)[ ]+?(.+)'", $uidl, $arUIDL_temp, PREG_SET_ORDER);

			$arUIDL = array();
			$cnt = count($arUIDL_temp);
			for ($i = 0; $i < $cnt; $i++)
				$arUIDL[md5($arUIDL_temp[$i][2])] = $arUIDL_temp[$i][1];

			$skipOldUIDL = $cnt < $this->mess_count;
			if ($skipOldUIDL)
			{
				AddMessage2Log(sprintf(
					"%s\n%s of %s",
					$this->response, $cnt, $this->mess_count
				), 'mail');
			}

			$arOldUIDL = array();
			if (count($arUIDL) > 0)
			{
				$strSql = 'SELECT ID FROM b_mail_message_uid WHERE MAILBOX_ID = ' . $mailbox_id;
				$db_res = $DB->query($strSql, false, 'File: '.__FILE__.'<br>Line: '.__LINE__);
				while ($ar_res = $db_res->fetch())
				{
					if (isset($arUIDL[$ar_res['ID']]))
						unset($arUIDL[$ar_res['ID']]);
					else if (!$skipOldUIDL)
						$arOldUIDL[] = $ar_res['ID'];
				}
			}

			while (count($arOldUIDL) > 0)
			{
				$ids = "'" . join("','", array_splice($arOldUIDL, 0, 1000)) . "'";

				// @TODO: make a log optional
				/*$toLog = [
					'filter'=>'\CAllMailBox::_connect',
					'removedMessages'=>$ids,
				];
				AddMessage2Log($toLog);*/

				$strSql = 'DELETE FROM b_mail_message_uid WHERE MAILBOX_ID = ' . $mailbox_id . ' AND ID IN (' . $ids . ')';
				$DB->query($strSql, false, 'File: '.__FILE__.'<br>Line: '.__LINE__);
			}

			$this->new_mess_count = 0;
			$this->deleted_mess_count = 0;
			$session_id = md5(uniqid(""));

			foreach($arUIDL as $msguid=>$msgnum)
			{
				if($arMAILBOX_PARAMS["MAX_MSG_SIZE"]<=0 || $arLIST[$msgnum]<=$arMAILBOX_PARAMS["MAX_MSG_SIZE"])
					$this->GetMessage($mailbox_id, $msgnum, $msguid, $session_id);

				if($arMAILBOX_PARAMS["DELETE_MESSAGES"]=="Y")
				{
					$this->DeleteMessage($msgnum);
					$this->deleted_mess_count++;
				}

				$this->new_mess_count++;
				if($arMAILBOX_PARAMS["MAX_MSG_COUNT"]>0 && $arMAILBOX_PARAMS["MAX_MSG_COUNT"]<=$this->new_mess_count)
					break;
			}
		}

		$this->SendCommand("QUIT");
		if(!$this->GetResponse())
			return CMailError::SetError("ERR_AFTER_QUIT", GetMessage("MAIL_CL_ERR_DISCONNECT"), $this->GetResponseString());

		fclose($pop3_conn);
		return true;
	}

	function GetMessage($mailbox_id, $msgnum, $msguid, $session_id)
	{
		global $DB;

		$this->SendCommand("RETR ".$msgnum);
		if(!$this->GetResponse(true))
			return CMailError::SetError("ERR_AFTER_RETR", GetMessage("MAIL_CL_ERR_RETR"), $this->GetResponseString());

		$message = $this->GetResponseBody();

		$strSql = "INSERT INTO b_mail_message_uid(ID, MAILBOX_ID, SESSION_ID, DATE_INSERT, MESSAGE_ID) VALUES('".$DB->ForSql($msguid)."', ".intval($mailbox_id).", '".$DB->ForSql($session_id)."', ".$DB->GetNowFunction().", 0)";
		$DB->Query($strSql);

		$message_id = CMailMessage::AddMessage($mailbox_id, $message, $this->charset);
		if($message_id>0)
		{
			$strSql = "UPDATE b_mail_message_uid SET MESSAGE_ID = " . intval($message_id) . " WHERE ID = '" . $DB->forSql($msguid) . "' AND MAILBOX_ID = " . intval($mailbox_id);
			$DB->Query($strSql);
		}
		return $message_id;
	} // function GetMessage(...

	/*********************************************************************
	*********************************************************************/
	function DeleteMessage($msgnum)
	{
		$this->SendCommand("DELE ".$msgnum);
		if(!$this->GetResponse())
			return CMailError::SetError("ERR_AFTER_DELE", GetMessage("MAIL_CL_ERR_DELE"), $this->GetResponseString());
	}
}

///////////////////////////////////////////////////////////////////////////////////
// class CMailHeader
///////////////////////////////////////////////////////////////////////////////////
class CMailHeader
{
	var $arHeader = Array();
	var $arHeaderLines = Array();
	var $strHeader = "";
	var $bMultipart = false;
	var $content_type, $boundary, $charset, $filename, $MultipartType="mixed";
	public $content_id = '';

	public static function ConvertHeader($encoding, $type, $str, $charset)
	{
		if(mb_strtoupper($type) == "B")
			$str = base64_decode($str);
		else
			$str = quoted_printable_decode(str_replace("_", " ", $str));

		$str = Emoji::encode($str);
		$str = CMailUtil::ConvertCharset($str, $encoding, $charset);

		return $str;
	}

	function DecodeHeader($str, $charset_to, $charset_document)
	{
		do
		{
			$n = 0;
			$str = preg_replace('/(=\?.*?\?(?:B|Q)\?.*?\?=)\s+((?1))/i', '\1\2', $str, -1, $n);
		}
		while ($n > 0);

		$handler = function ($m) use ($charset_to)
		{
			return \CMailHeader::convertHeader($m[1], $m[2], $m[3], $charset_to);
		};

		$n = 0;
		$str = preg_replace_callback('/=\?(.*?)\?(B|Q)\?(.*?)\?=/i', $handler, $str, -1, $n);

		if ($n == 0 && $charset_document <> '')
		{
			$str = \CMailUtil::convertCharset($str, $charset_document, $charset_to);
		}

		return $str;
	}

	function Parse($message_header, $charset)
	{
		$this->charset = defined('BX_MAIL_DEFAULT_CHARSET') && BX_MAIL_DEFAULT_CHARSET != '' ? BX_MAIL_DEFAULT_CHARSET : $charset;
		if(preg_match("'content-type:.*?charset\s*=\s*([^\r\n;]+)'is", $message_header, $res))
			$this->charset = mb_strtolower(trim($res[1], ' "'));

		$message_header = preg_replace('/\r\n([\x20\t])/i', '\1', $message_header);

		$ar_message_header_tmp = explode("\r\n", $message_header);

		for ($i = 0, $num = count($ar_message_header_tmp); $i < $num; $i++)
		{
			$this->arHeaderLines[] = \CMailHeader::decodeHeader($ar_message_header_tmp[$i], $charset, $this->charset);
		}

		$this->arHeader = Array();
		for($i = 0, $num = count($this->arHeaderLines); $i < $num; $i++)
		{
			$p = mb_strpos($this->arHeaderLines[$i], ":");
			if($p>0)
			{
				$header_name = mb_strtoupper(trim(mb_substr($this->arHeaderLines[$i], 0, $p)));
				$header_value = trim(mb_substr($this->arHeaderLines[$i], $p + 1));
				$this->arHeader[$header_name] = $header_value;
			}
		}

		$full_content_type = $this->arHeader["CONTENT-TYPE"];
		if($full_content_type == '')
			$full_content_type = "text/plain";

		if(!($p = mb_strpos($full_content_type, ";")))
			$p = mb_strlen($full_content_type);

		$this->content_type = trim(mb_substr($full_content_type, 0, $p));
		if(mb_strpos(mb_strtolower($this->content_type), "multipart/") === 0)
		{
			$this->bMultipart = true;
			if (!preg_match("'boundary\s*=\s*(.+?);'i", $full_content_type, $res))
				preg_match("'boundary\s*=\s*(.+)'i", $full_content_type, $res);

			$this->boundary = trim($res[1], '"');
			if($p = mb_strpos($this->content_type, "/"))
				$this->MultipartType = mb_substr($this->content_type, $p + 1);
		}

		if($p < mb_strlen($full_content_type))
		{
			$add = mb_substr($full_content_type, $p + 1);
			if(preg_match("'name=([^;]+)'i", $full_content_type, $res))
				$this->filename = trim($res[1], '"');
		}

		$cd = isset($this->arHeader["CONTENT-DISPOSITION"]) ? $this->arHeader["CONTENT-DISPOSITION"] : '';
		if ($cd <> '')
		{
			if (preg_match("'filename=([^;]+)'i", $cd, $res))
			{
				$this->filename = trim($res[1], '"');
			}
			else if (preg_match("'filename\*=([^;]+)'i", $cd, $res))
			{
				[$fncharset, $fnstr] = preg_split("/'[^']*'/", trim($res[1], '"'));
				$this->filename = CMailUtil::ConvertCharset(rawurldecode($fnstr), $fncharset, $charset);
			}
			else if (preg_match("'filename\*0=([^;]+)'i", $cd, $res))
			{
				$this->filename = trim($res[1], '"');

				$i = 0;
				while (preg_match("'filename\*".(++$i)."=([^;]+)'i", $cd, $res))
					$this->filename .= trim($res[1], '"');
			}
			else if (preg_match("'filename\*0\*=([^;]+)'i", $cd, $res))
			{
				$fnstr = trim($res[1], '"');

				$i = 0;
				while (preg_match("'filename\*".(++$i)."\*?=([^;]+)'i", $cd, $res))
					$fnstr .= trim($res[1], '"');

				[$fncharset, $fnstr] = preg_split("/'[^']*'/", $fnstr);
				if (!empty($fnstr))
				{
					$fnstr = rawurldecode($fnstr);
					$this->filename = $fncharset ? CMailUtil::convertCharset($fnstr, $fncharset, $charset) : $fnstr;
				}
			}
		}

		if(isset($this->arHeader["CONTENT-ID"]) && $this->arHeader["CONTENT-ID"]!='')
			$this->content_id = trim($this->arHeader["CONTENT-ID"], '"<>');

		$this->strHeader = implode("\r\n", $this->arHeaderLines);

		return true;
	}

	function IsMultipart()
	{
		return $this->bMultipart;
	}

	function MultipartType()
	{
		return mb_strtolower($this->MultipartType);
	}

	function GetBoundary()
	{
		return $this->boundary;
	}

	function GetHeader($type)
	{
		return isset($this->arHeader[mb_strtoupper($type)]) ? $this->arHeader[mb_strtoupper($type)] : '';
	}
}


class CMailMessageDBResult extends CDBResult
{

	function fetch()
	{
		if ($item = parent::fetch())
		{
			$item['OPTIONS'] = (array) @unserialize($item['OPTIONS'], ['allowed_classes' => false]);
			$item['FOR_SPAM_TEST'] = sprintf('%s %s', $item['HEADER'], $item['BODY_HTML'] ?: $item['BODY']);
		}

		return $item;
	}

}

///////////////////////////////////////////////////////////////////////////////////
// class CMailMessage
///////////////////////////////////////////////////////////////////////////////////
class CAllMailMessage
{
	public const MAX_LENGTH_MESSAGE_BODY = 3000000;

	public static function GetList($arOrder = Array(), $arFilter = Array(), $bCnt = false)
	{
		global $DB;
		$sum = "case when NEW_MESSAGE='Y' then 1 else 0 end";

		$strSql =
				"SELECT ".
				($bCnt?
					"COUNT('x') as CNT, SUM(".$sum.") as CNT_NEW, COUNT('x')-SUM(".$sum.") as CNT_OLD "
				:
					"MS.*, MB.NAME as MAILBOX_NAME, MB.LID, ".
					"	".$DB->DateToCharFunction("MS.DATE_INSERT")."	as DATE_INSERT, ".
					"	".$DB->DateToCharFunction("MS.FIELD_DATE")."	as FIELD_DATE "
				).
				"FROM b_mail_message MS ".
				($bCnt? "":" INNER JOIN b_mail_mailbox MB ON MS.MAILBOX_ID=MB.ID ");

		$arSqlSearch = Array();
		$filter_keys = array_keys($arFilter);
		for($i = 0, $n = count($filter_keys); $i < $n; $i++)
		{
			$key = $filter_keys[$i];
			$val = $arFilter[$key];
			$res = CMailUtil::MkOperationFilter($key);
			$key = mb_strtoupper($res["FIELD"]);
			$cOperationType = $res["OPERATION"];

			if($cOperationType == "?")
			{
				if ($val == '') continue;
				switch($key)
				{
				case "ID":
				case "MAILBOX_ID":
				case "MSGUID":
					$arSqlSearch[] = GetFilterQuery("MS.".$key, $val, "N");
					break;
				case "FIELD_FROM":
				case "FIELD_TO":
				case "FIELD_CC":
				case "FIELD_BCC":
					$arSqlSearch[] = GetFilterQuery("MS.".$key, $val, "Y", Array("@", "_", ".", "-"));
					break;
				case "NEW_MESSAGE":
				case "SUBJECT":
				case "HEADER":
				case "MSG_ID":
				case "IN_REPLY_TO":
				case "BODY":
					$arSqlSearch[] = GetFilterQuery("MS.".$key, $val);
					break;
				case "SENDER":
					$arSqlSearch[] = GetFilterQuery("MS.FIELD_FROM", $val, "Y", array("@","_",".","-"));
					break;
				case "RECIPIENT":
					$arSqlSearch[] = GetFilterQuery("MS.FIELD_TO, MS.FIELD_CC, MS.FIELD_BCC", $val, "Y", array("@","_",".","-"));
					break;
				case "SPAM_RATING":
					CMailFilter::RecalcSpamRating();
					$arSqlSearch[] = GetFilterQuery("MS.SPAM_RATING", $val, "N");
					break;
				case "SPAM":
					$arSqlSearch[] = GetFilterQuery("MS.SPAM", $val, "Y", array("?"));
					break;
				case "ALL":
					$arSqlSearch[] = GetFilterQuery("MS.HEADER, MS.BODY", $val);
					break;
				}
			}
			else
			{
				switch($key)
				{
				case "SPAM":
				case "NEW_MESSAGE":
					$arSqlSearch[] = CMailUtil::FilterCreate("MS.".$key, $val, "string_equal", $cOperationType);
					break;
				case "ID":
				case "MAILBOX_ID":
					$arSqlSearch[] = CMailUtil::FilterCreate("MS.".$key, $val, "number", $cOperationType);
					break;
				case "SUBJECT":
				case "HEADER":
				case "BODY":
				case "MSGUID":
				case "FIELD_FROM":
				case "FIELD_TO":
				case "FIELD_CC":
				case "MSG_ID":
				case "IN_REPLY_TO":
				case "FIELD_BCC":
					$arSqlSearch[] = CMailUtil::FilterCreate("MS.".$key, $val, "string", $cOperationType);
					break;
				case "SPAM_RATING":
					$arSqlSearch[] = CMailUtil::FilterCreate("MS.".$key, $val, "number", $cOperationType);
					CMailFilter::RecalcSpamRating();
					break;
				/*
				case "TIMESTAMP_X":
					$arSqlSearch[] = CIBlock::FilterCreate("BE.TIMESTAMP_X", $val, "date", $cOperationType);
					break;
				*/
				}
			}
		}

		$is_filtered = false;
		$strSqlSearch = "";
		for($i = 0, $n = count($arSqlSearch); $i < $n; $i++)
		{
			if($arSqlSearch[$i] <> '')
			{
				$strSqlSearch .= " AND  (".$arSqlSearch[$i].") ";
				$is_filtered = true;
			}
		}
		$arSqlOrder = Array();
		foreach($arOrder as $by=>$order)
		{
			$by = mb_strtolower($by);
			$order = mb_strtolower($order);

			if ($order!="asc")
				$order = "desc".($DB->type == "ORACLE"?" NULLS LAST":"");
			else
				$order = "asc".($DB->type == "ORACLE"?" NULLS FIRST":"");

			if ($by == "field_date")		$arSqlOrder[] = " MS.FIELD_DATE ".$order." ";
			elseif ($by == "field_from")	$arSqlOrder[] = " MS.FIELD_FROM ".$order." ";
			elseif ($by == "field_reply_to")$arSqlOrder[] = " MS.FIELD_REPLY_TO ".$order." ";
			elseif ($by == "field_to")		$arSqlOrder[] = " MS.FIELD_TO ".$order." ";
			elseif ($by == "field_cc")		$arSqlOrder[] = " MS.FIELD_CC ".$order." ";
			elseif ($by == "field_bcc")		$arSqlOrder[] = " MS.FIELD_BCC ".$order." ";
			elseif ($by == "subject")		$arSqlOrder[] = " MS.SUBJECT ".$order." ";
			elseif ($by == "attachments")	$arSqlOrder[] = " MS.ATTACHMENTS ".$order." ";
			elseif ($by == "date_insert")	$arSqlOrder[] = " MS.DATE_INSERT ".$order." ";
			elseif ($by == "msguid")		$arSqlOrder[] = " MS.MSGUID ".$order." ";
			elseif ($by == "mailbox_id")	$arSqlOrder[] = " MS.MAILBOX_ID ".$order." ";
			elseif ($by == "new_message")	$arSqlOrder[] = " MS.NEW_MESSAGE ".$order." ";
			elseif ($by == "mailbox_name" && !$bCnt)	$arSqlOrder[] = " MB.NAME ".$order." ";
			elseif ($by == "spam_rating")
			{
				$arSqlOrder[] = " MS.SPAM_RATING ".$order." "; CMailFilter::RecalcSpamRating();
			}
			else $arSqlOrder[] = " MS.ID ".$order." ";
		}

		$strSqlOrder = "";
		$arSqlOrder = array_unique($arSqlOrder);
		DelDuplicateSort($arSqlOrder);

		for ($i = 0, $n = count($arSqlOrder); $i < $n; $i++)
		{
			if($i==0)
				$strSqlOrder = " ORDER BY ";
			else
				$strSqlOrder .= ",";

			$strSqlOrder .= $arSqlOrder[$i];
		}

		$strSql .= " WHERE 1=1 ".$strSqlSearch.$strSqlOrder;

		$dbr = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		$dbr = new \CMailMessageDBResult($dbr);
		$dbr->is_filtered = $is_filtered;
		return $dbr;
	}

	public static function GetByID($ID)
	{
		return CMailMessage::GetList(Array(), Array("=ID"=>$ID));
	}

	public static function GetSpamRating($msgid, $arRow=false)
	{
		global $DB;
		$res = null;

		if(!is_array($arRow))
			$res = $DB->Query("SELECT SPAM_RATING, SPAM_LAST_RESULT, HEADER, BODY_HTML, BODY FROM b_mail_message WHERE ID=".intval($msgid));
		else
			$ar = $arRow;

		if(is_array($arRow) || $res && ($ar = $res->Fetch()))
		{
			if (empty($ar['FOR_SPAM_TEST']))
			{
				$ar['FOR_SPAM_TEST'] = sprintf('%s %s', $ar['HEADER'], $ar['BODY_HTML'] ?: $ar['BODY'] );
			}

			if($ar["SPAM_LAST_RESULT"]=="Y")
				return $ar["SPAM_RATING"];
			$arSpam = CMailFilter::GetSpamRating($ar["FOR_SPAM_TEST"]);
			$num = Round($arSpam["RATING"], 4);
			$DB->Query("UPDATE b_mail_message SET SPAM_RATING=".$num.", SPAM_LAST_RESULT='Y', SPAM_WORDS='".$DB->ForSql($arSpam["WORDS"], 255)."' WHERE ID=".intval($msgid));
			return $num;
		}
	}


	public static function parseHeader($header, $charset)
	{
		$h = new CMailHeader();
		$h->parse($header, $charset);
		return $h;
	}

	public static function decodeMessageBody($header, $body, $charset)
	{
		$encoding = mb_strtolower($header->GetHeader('CONTENT-TRANSFER-ENCODING'));

		if ($encoding == 'base64')
			$body = base64_decode($body);
		elseif ($encoding == 'quoted-printable')
			$body = quoted_printable_decode($body);
		elseif ($encoding == 'x-uue')
			$body = CMailUtil::uue_decode($body);

		$content_type = mb_strtolower($header->content_type);
		if (empty($header->filename) && !empty($header->charset))
		{
			if (preg_match('/plain|html|text/', $content_type) && !preg_match('/x-vcard|csv/', $content_type))
			{
				$body = Emoji::encode($body);
				$body = CMailUtil::convertCharset($body, $header->charset, $charset);
			}
		}

		return array(
			'CONTENT-TYPE' => $content_type,
			'CONTENT-ID'   => $header->content_id,
			'BODY'         => $body,
			'FILENAME'     => $header->filename
		);
	}

	public static function parseMessage($message, $charset)
	{
		$headerP = strpos($message, "\r\n\r\n");

		if (false === $headerP)
		{
			$rawHeader = '';
			$body      = $message;
		}
		else
		{
			$rawHeader = substr($message, 0, $headerP);
			$body      = substr($message, $headerP+4);
		}

		$header = \CMailMessage::parseHeader($rawHeader, $charset);

		$htmlBody = '';
		$textBody = '';

		$parts = array();

		if ($header->isMultipart())
		{
			$startP = 0;
			$startRegex = sprintf('/(^|\r\n)--%s\r\n/', preg_quote($header->getBoundary(), '/'));
			if (preg_match($startRegex, $body, $matches, PREG_OFFSET_CAPTURE))
			{
				$startP = $matches[0][1] + strlen($matches[0][0]);
			}

			$endP = strlen($body);
			$endRegex = sprintf('/\r\n--%s--(\r\n|$)/', preg_quote($header->getBoundary(), '/'));
			if (preg_match($endRegex, $body, $matches, PREG_OFFSET_CAPTURE))
			{
				$endP = $matches[0][1];
			}

			if (!($startP < $endP))
			{
				$startP = 0;
			}

			$data = substr($body, $startP, $endP-$startP);

			$isHtml = false;
			$rawParts = preg_split(sprintf('/\r\n--%s\r\n/', preg_quote($header->getBoundary(), '/')), $data);
			$tmpParts = array();
			foreach ($rawParts as $part)
			{
				if (substr($part, 0, 2) == "\r\n")
					$part = "\r\n" . $part;

				[, $subHtml, $subText, $subParts] = CMailMessage::parseMessage($part, $charset);

				if ($subHtml)
					$isHtml = true;

				if ($subText)
					$tmpParts[] = array($subHtml, $subText);

				$parts = array_merge($parts, $subParts);
			}

			if (mb_strtolower($header->MultipartType()) == 'alternative')
			{
				$candidate = '';

				foreach ($tmpParts as $part)
				{
					if ($part[0])
					{
						if (!$htmlBody || (mb_strlen($htmlBody) < mb_strlen($part[0])))
						{
							$htmlBody  = $part[0];
							$candidate = $part[1];
						}
					}
					else
					{
						if (!$textBody || mb_strlen($textBody) < mb_strlen($part[1]))
							$textBody = $part[1];
					}
				}

				if (!trim($textBody))
					$textBody = $candidate;
			}
			else
			{
				foreach ($tmpParts as $part)
				{
					if ($textBody)
						$textBody .= "\r\n\r\n";
					$textBody .= $part[1];

					if ($isHtml)
					{
						if ($htmlBody)
							$htmlBody .= "\r\n\r\n";

						$htmlBody .= $part[0] ?: $part[1];
					}
				}
			}
		}
		else
		{
			$bodyPart = CMailMessage::decodeMessageBody($header, $body, $charset);
			$contentType = mb_strtolower($bodyPart['CONTENT-TYPE']);

			if (
				!$bodyPart['FILENAME']
				&& (mb_strpos($contentType, 'text/') === 0)
				&& ($contentType !== 'text/calendar')
			)
			{
				if ($contentType == 'text/html')
				{
					$htmlBody = $bodyPart['BODY'];
					$textBody = html_entity_decode(htmlToTxt($bodyPart['BODY']), ENT_QUOTES | ENT_HTML401, $charset);
				}
				else
				{
					$textBody = $bodyPart['BODY'];
				}
			}
			else
			{
				$parts[] = $bodyPart;
			}
		}

		return array($header, $htmlBody, $textBody, $parts);
	}

	public static function addMessage($mailboxId, $message, $charset, $params = array())
	{
		[$header, $html, $text, $attachments] = CMailMessage::parseMessage($message, $charset);

		return static::saveMessage($mailboxId, $message, $header, $html, $text, $attachments, $params);
	}

	public static function saveMessage($mailboxId, &$message, &$header, &$bodyHtml, &$bodyText, &$attachments, $params = array())
	{
		global $DB;

		$mailbox_id = $mailboxId;
		$obHeader = &$header;
		$message_body_html = &$bodyHtml;
		$message_body = &$bodyText;
		$arMessageParts = &$attachments;
		$initialHtmlLen = mb_strlen($message_body_html);

		$isStrippedTagsToBody = false;
		$isOriginalEmptyBody = empty(trim(strip_tags($message_body_html)));

		if (self::isLongMessageBody($message_body))
		{
			[$message_body, $message_body_html] = self::prepareLongMessage($message_body, $message_body_html);
		}

		if (
			(mb_strlen($message_body_html) > 0)
			&& empty(trim(strip_tags($message_body_html)))
		)
		{
			$message_body_html = '';
			$isStrippedTagsToBody = true;
		}

		$arFields = array(
			"MAILBOX_ID" => $mailbox_id,
			"HEADER" => $obHeader->strHeader,
			"FIELD_DATE_ORIGINAL" => $obHeader->GetHeader("DATE"),
			"NEW_MESSAGE"	=> "Y",
			"FIELD_FROM" => $obHeader->GetHeader("FROM"),
			"FIELD_REPLY_TO" => $obHeader->GetHeader("REPLY-TO"),
			"FIELD_TO" => $obHeader->GetHeader("TO"),
			"FIELD_CC" => $obHeader->GetHeader("CC"),
			"FIELD_BCC" => ($obHeader->GetHeader('X-Original-Rcpt-to')!=''?$obHeader->GetHeader('X-Original-Rcpt-to').($obHeader->GetHeader("BCC")!=''?', ':''):'').$obHeader->GetHeader("BCC"),
			"MSG_ID" => trim($obHeader->GetHeader("MESSAGE-ID"), " <>"),
			"FIELD_PRIORITY" => intval($obHeader->GetHeader("X-PRIORITY")),
			"MESSAGE_SIZE" => $params['size']?: mb_strlen($message),
			"SUBJECT" => $obHeader->GetHeader("SUBJECT"),
			"BODY" => rtrim($message_body),
			'OPTIONS' => array(
				'attachments' => count($arMessageParts),
				'isStrippedTags' => $isStrippedTagsToBody,
				'isOriginalEmptyBody' => $isOriginalEmptyBody,
			),
			MailMessageTable::FIELD_SANITIZE_ON_VIEW => (int)($params[MailMessageTable::FIELD_SANITIZE_ON_VIEW] ?? 0)
		);

		if (
			($arFields['OPTIONS']['attachments'] <= 0)
			&& (empty($message_body) || empty($message_body_html))
		)
		{
			$arFields['OPTIONS']['isEmptyBody'] = 'Y';
		}

		$inReplyTo = trim($obHeader->GetHeader("IN-REPLY-TO"), " <>");

		if($inReplyTo !== '')
		{
			$arFields['IN_REPLY_TO'] = $inReplyTo;
		}

		$datetime = preg_replace('/(?<=[\s\d])UT$/i', '+0000', $arFields['FIELD_DATE_ORIGINAL']);
		if (!(isset($params['replaces']) && $params['replaces'] > 0) || strtotime($datetime) || $params['timestamp'])
		{
			$timestamp = strtotime($datetime) ?: $params['timestamp'] ?: time();
			$arFields['FIELD_DATE'] = convertTimeStamp($timestamp + \CTimeZone::getOffset(), 'FULL');
		}

		if (!empty($message) && \Bitrix\Main\Config\Option::get('mail', 'save_src', B_MAIL_SAVE_SRC) == 'Y')
		{
			$arFields['FULL_TEXT'] = $message;
		}

		$forSpamTest = sprintf('%s %s', $arFields['HEADER'], $message_body_html ?: $message_body);

		$arFields["SPAM"] = "?";
		if(COption::GetOptionString("mail", "spam_check", B_MAIL_CHECK_SPAM)=="Y")
		{
			$arSpam = \CMailFilter::getSpamRating($forSpamTest);
			$arFields["SPAM_RATING"] = $arSpam["RATING"];
			$arFields["SPAM_WORDS"] = $arSpam["WORDS"];
			$arFields["SPAM_LAST_RESULT"] = "Y";
		}

		// @TODO: MAX_ALLOWED_PACKET
		$arFields['INDEX_VERSION'] = \Bitrix\Mail\Helper\MessageIndexStepper::INDEX_VERSION;
		$arFields['SEARCH_CONTENT'] = \Bitrix\Mail\Helper\Message::prepareSearchContent($arFields);

		if (isset($params['replaces']) && $params['replaces'] > 0)
		{
			\CMailMessage::update($message_id = $params['replaces'], $arFields, $mailbox_id);
		}
		else
		{
			if (isset($params['trackable']) && $params['trackable'])
			{
				$arFields['OPTIONS']['trackable'] = \Bitrix\Main\Config\Option::get('main', 'track_outgoing_emails_read', 'Y') == 'Y';
			}

			$message_id = \CMailMessage::add($arFields, $mailbox_id);
		}

		if ($message_id > 0)
		{
			$arFields['ID'] = $message_id;
			$arFields['FOR_SPAM_TEST'] = $forSpamTest;

			\CMailLog::addMessage(array(
				'MAILBOX_ID'  => $mailbox_id,
				'MESSAGE_ID'  => $message_id,
				'STATUS_GOOD' => 'Y',
				'LOG_TYPE'    => isset($params['replaces']) && $params['replaces'] > 0 ? 'RENEW_MESSAGE' : 'NEW_MESSAGE',
				'MESSAGE'     => sprintf(
					'%s (%s)%s', $arFields['SUBJECT'], $arFields['MESSAGE_SIZE'],
					\Bitrix\Main\Config\Option::get('mail', 'spam_check', B_MAIL_CHECK_SPAM) == 'Y'
						? sprintf(' [%.3f]', $arFields['SPAM_RATING']) : ''
				),
			));

			//If the message is new. Not resynchronization
			if (!(isset($params['replaces']) && $params['replaces'] > 0))
			{
				/**
				 * By default, a chain is created for each new message that links the message to itself.
				 * If the parents are not found for the message in the future, then the chain will remain like this.
				 * */
				MessageClosureTable::insertIgnoreFromSql(sprintf('VALUES (%1$u, %1$u)', $message_id));

				/**
				 * We find the parents(in the standard case there should be one) of this message and create a chain.
				 * If the id of the parent (IN_REPLY_TO) matches the id of the message itself(MSG_ID),
				 * then nothing will happen(INSERT IGNORE), since such a chain was created in the step above.
				 * */
				if ($arFields['IN_REPLY_TO'])
				{
					self::makeMessageClosureChain($message_id, $mailbox_id, (string)$arFields['IN_REPLY_TO']);
				}

				$mailbox = Bitrix\Mail\MailboxTable::getList(array(
					'select' => array('ID', 'USER_ID', 'OPTIONS'),
					'filter' => array('=ID' => $mailbox_id, '=ACTIVE' => 'Y'),
				))->fetch();

				if ($mailbox['USER_ID'] > 0)
				{
					\Bitrix\Mail\Internals\MailContactTable::addContactsBatch(array_merge(
						MailContact::getContactsData($arFields['FIELD_TO'], $mailbox['USER_ID'], \Bitrix\Mail\Internals\MailContactTable::ADDED_TYPE_TO),
						MailContact::getContactsData($arFields['FIELD_FROM'], $mailbox['USER_ID'], \Bitrix\Mail\Internals\MailContactTable::ADDED_TYPE_FROM),
						MailContact::getContactsData($arFields['FIELD_CC'], $mailbox['USER_ID'], \Bitrix\Mail\Internals\MailContactTable::ADDED_TYPE_CC),
						MailContact::getContactsData($arFields['FIELD_REPLY_TO'], $mailbox['USER_ID'], \Bitrix\Mail\Internals\MailContactTable::ADDED_TYPE_REPLY_TO),
						MailContact::getContactsData($arFields['FIELD_BCC'], $mailbox['USER_ID'], \Bitrix\Mail\Internals\MailContactTable::ADDED_TYPE_BCC)
					));
				}
			}

			$atchCnt = 0;
			if (empty($params['lazy_attachments']) && \Bitrix\Main\Config\Option::get('mail', 'save_attachments', B_MAIL_SAVE_ATTACHMENTS) == 'Y')
			{
				foreach ($arMessageParts as $i => $part)
				{
					$attachFields = array(
						'MESSAGE_ID'   => $message_id,
						'FILE_NAME'    => $part['FILENAME'],
						'CONTENT_TYPE' => $part['CONTENT-TYPE'],
						'FILE_DATA'    => $part['BODY'],
						'CONTENT_ID'   => $part['CONTENT-ID'],
					);

					$arMessageParts[$i]['ATTACHMENT-ID'] = \CMailMessage::addAttachment($attachFields);
					if (!$arMessageParts[$i]['ATTACHMENT-ID'])
					{
						\CMailMessage::delete($message_id);
						return false;
					}

					$atchCnt++;
				}
			}

			$arFields['ATTACHMENTS'] = $atchCnt;

			if ($message_body_html)
			{
				if (isset($params[MailMessageTable::FIELD_SANITIZE_ON_VIEW])
					&& $params[MailMessageTable::FIELD_SANITIZE_ON_VIEW])
				{
					$arFields['BODY_HTML'] = $message_body_html;
				}
				else
				{
					Ini::adjustPcreBacktrackLimit(strlen($message_body_html)*2);

					$msg = array(
						'html'        => $message_body_html,
						'attachments' => array(),
					);
					foreach ($arMessageParts as $part)
					{
						if (!(is_array($part) && $part['ATTACHMENT-ID'] > 0))
						{
							continue;
						}

						$msg['attachments'][] = array(
							'contentId' => $part['CONTENT-ID'],
							'uniqueId'  => sprintf('attachment_%u', $part['ATTACHMENT-ID']),
						);
					}

					$arFields['BODY_BB'] = \Bitrix\Mail\Message::parseMessage($msg);

					$arFields['BODY_HTML'] = \Bitrix\Mail\Helper\Message::sanitizeHtml($message_body_html, true);
				}

				foreach ($arMessageParts as $part)
				{
					if (!(is_array($part) && $part['ATTACHMENT-ID'] > 0))
					{
						continue;
					}

					$arFields['BODY_HTML'] = \Bitrix\Mail\Helper\Message::replaceBodyInlineImgContentId(
						(string)$arFields['BODY_HTML'],
						(string)$part['CONTENT-ID'],
						$part['ATTACHMENT-ID'],
					);
				}

				\CMailMessage::update($message_id, array('BODY_HTML' => $arFields['BODY_HTML']), $mailbox_id);
			}
			else
			{
				self::logEmptyHtml($message_id, $initialHtmlLen, $params);
				self::addDefferedDownload($mailboxId, $message_id);
			}

			if (!(isset($params['replaces']) && $params['replaces'] > 0))
			{
				$arFields['IS_OUTCOME'] = !empty($params['outcome']);
				$arFields['IS_DRAFT'] = !empty($params['draft']);
				$arFields['IS_TRASH'] = !empty($params['trash']);
				$arFields['IS_SPAM'] = !empty($params['spam']);
				$arFields['IS_SEEN'] = !empty($params['seen']);
				$arFields['MSG_HASH'] = $params['hash'];

				if (!empty($params['excerpt']) && is_array($params['excerpt']))
				{
					$arFields = $arFields + $params['excerpt'];
				}

				$messageBindings = array();

				$eventKey = \Bitrix\Main\EventManager::getInstance()->addEventHandler(
					'mail',
					'onBeforeUserFieldSave',
					function (\Bitrix\Main\Event $event) use (&$messageBindings)
					{
						$params = $event->getParameters();
						$messageBindings[] = $params['entity_type'];
					}
				);

				$arFieldsForFilter = $arFields;

				foreach (['BODY','BODY_BB','BODY_HTML','SUBJECT'] as $key)
				{
					if(!empty($arFieldsForFilter[$key]))
					{
						$arFieldsForFilter[$key] = Emoji::decode($arFieldsForFilter[$key]);
					}
				}

				\CMailFilter::filter($arFieldsForFilter, 'R');

				\Bitrix\Main\EventManager::getInstance()->removeEventHandler('mail', 'onBeforeUserFieldSave', $eventKey);

				$icalAccess = isset($mailbox['OPTIONS']['ical_access']) && ($mailbox['OPTIONS']['ical_access'] === 'Y');
				$event = new \Bitrix\Main\Event('mail', 'onMailMessageNew', [
					'message' => $arFields,
					'attachments' => $arMessageParts,
					'userId' => isset($mailbox['USER_ID']) ? $mailbox['USER_ID'] : null,
					'icalAccess' => $icalAccess
				]);
				$event->send();

				addEventToStatFile(
					'mail',
					sprintf(
						'add_%s_%s',
						(empty($arFields['IN_REPLY_TO']) ? 'message' : 'reply'),
						(empty($params['outcome']) ? 'incoming' : 'outgoing')
					),
					join(',', array_unique(array_filter($messageBindings))),
					$arFields['MSG_ID']
				);
			}
		}

		return $message_id;
	}

	private static function logEmptyHtml($messageId, $initialHtmlLen, $params): void
	{
		if (isset($params['log_parts']))
		{
			if (is_array($params['log_parts']))
			{
				$logParts = count($params['log_parts']);
			}
			else
			{
				$logParts = -2;
			}
		}
		else
		{
			$logParts = -1;
		}
		addMessage2Log(
			sprintf('MAIL_EMPTY_BODY id: %s initalLen: %s parts: %s', $messageId, $initialHtmlLen, $logParts),
			'mail');
	}

	/**
	 * We find the parents(in the standard case there should be one) of this message and create a chain.
	 * If the id of the parent (IN_REPLY_TO) matches the id of the message itself(MSG_ID),
	 * then nothing will happen(INSERT IGNORE), since such a chain was created in the step above.
	 *
	 * @param int $messageId Mail message ID
	 * @param int $mailboxId Mailbox ID
	 * @param string $inReply In Replay To mail header value
	 *
	 * @return void
	 */
	private static function makeMessageClosureChain(int $messageId, int $mailboxId, string $inReply): void
	{
		$helper = \Bitrix\Main\Application::getConnection()->getSqlHelper();
		MessageClosureTable::insertIgnoreFromSelect(sprintf("SELECT DISTINCT %u, C.PARENT_ID
			FROM b_mail_message M 
			INNER JOIN b_mail_message_closure C ON M.ID = C.MESSAGE_ID
			WHERE M.MAILBOX_ID = %u AND M.MSG_ID = '%s'",
			$messageId,
			$mailboxId,
			$helper->forSql($inReply)));
	}

	/**
	 * Add a message to the database in the table b_mail_message.
	 *
	 * @param array $arFields
	 *
	 * @return int(message id in the table b_mail_message).
	 */
	public static function Add($arFields, $mailboxID = false)
	{
		global $DB;

		if (is_set($arFields, "NEW_MESSAGE") && $arFields["NEW_MESSAGE"] != "N")
			$arFields["NEW_MESSAGE"]="Y";

		if (is_set($arFields, "FULL_TEXT") && !is_set($arFields, "MESSAGE_SIZE"))
			$arFields["MESSAGE_SIZE"] = mb_strlen($arFields["FULL_TEXT"]);

		if (!is_set($arFields, "DATE_INSERT"))
			$arFields["~DATE_INSERT"] = $DB->GetNowFunction();

		if (is_set($arFields, "FIELD_DATE_ORIGINAL") && !is_set($arFields, "FIELD_DATE"))
		{
			$datetime = preg_replace('/(?<=[\s\d])UT$/i', '+0000', $arFields['FIELD_DATE_ORIGINAL']);
			$timestamp = strtotime($datetime) ?: time();
			$arFields['FIELD_DATE'] = convertTimeStamp($timestamp + \CTimeZone::getOffset(), 'FULL');
		}

		if (array_key_exists('SUBJECT', $arFields))
		{
			$arFields['SUBJECT'] = strval(mb_substr($arFields['SUBJECT'], 0, 255));
		}

		if (array_key_exists('OPTIONS', $arFields))
		{
			$arFields['OPTIONS'] = serialize($arFields['OPTIONS']);
		}

		$params = $DB->PrepareInsert("b_mail_message", $arFields);
		$sql = sprintf("INSERT INTO b_mail_message (%s) VALUES (%s)", $params[0], $params[1]);
		$length = BinaryString::getLength($sql);

		if (!\CMailUtil::IsSizeAllowed($length))
		{
			$limit =  \Bitrix\Main\Application::getConnection()->getMaxAllowedPacket() - 1;
			$trimLength = $length - $limit;
			self::trimContent($arFields, $trimLength, [['BODY_HTML', 'BODY'], 'SEARCH_CONTENT', 'HEADER']);

			$params = $DB->PrepareInsert("b_mail_message", $arFields);
			$sql = sprintf("INSERT INTO b_mail_message (%s) VALUES (%s)", $params[0], $params[1]);
		}

		$DB->Query($sql, false, "File: " . __FILE__ . "<br>Line: " . __LINE__);

		$ID = intval($DB->LastID());

		static::saveForDeferredDownload($ID, $arFields, $mailboxID);

		return $ID;
	}

	private static function saveForDeferredDownload($ID, $arFields, $mailboxID)
	{
		if (
			$mailboxID !== false
			&& is_set($arFields, 'BODY_HTML')
			&& $arFields['BODY_HTML'] === ''
			&& (!is_set($arFields, 'BODY') || $arFields['BODY'] === '')
			&& !$arFields['OPTIONS']['isStrippedTags']
		)
		{
			self::addDefferedDownload($mailboxID, $ID);
		}
	}

	private static function addDefferedDownload($mailboxID, $ID): void
	{
		\Bitrix\Mail\Internals\MailEntityOptionsTable::add([
			'MAILBOX_ID' => $mailboxID,
			'ENTITY_TYPE' => 'MESSAGE',
			'ENTITY_ID' => $ID,
			'PROPERTY_NAME' => 'UNSYNC_BODY',
			'DATE_INSERT' => new \Bitrix\Main\Type\DateTime(),
			'VALUE' => 'Y',
		]);
	}

	public static function Update($ID, $arFields, $mailboxID = false)
	{
		global $DB;
		$ID = intval($ID);

		if (is_set($arFields, "FIELD_DATE_ORIGINAL") && !is_set($arFields, "FIELD_DATE"))
		{
			$datetime = preg_replace('/(?<=[\s\d])UT$/i', '+0000', $arFields['FIELD_DATE_ORIGINAL']);
			$timestamp = strtotime($datetime) ?: time();
			$arFields['FIELD_DATE'] = convertTimeStamp($timestamp + \CTimeZone::getOffset(), 'FULL');
		}

		if (array_key_exists('SUBJECT', $arFields))
		{
			$arFields['SUBJECT'] = strval(mb_substr($arFields['SUBJECT'], 0, 255));
		}

		if (array_key_exists('OPTIONS', $arFields))
		{
			$arFields['OPTIONS'] = serialize($arFields['OPTIONS']);
		}

		$params = $DB->PrepareUpdate("b_mail_message", $arFields);
		$sql = sprintf("UPDATE b_mail_message SET %s WHERE ID=%s", $params, $ID);
		$length = BinaryString::getLength($sql);

		if (!\CMailUtil::IsSizeAllowed($length))
		{
			$limit =  \Bitrix\Main\Application::getConnection()->getMaxAllowedPacket() - 1;
			$trimLength = $length - $limit;
			self::trimContent($arFields, $trimLength, [['BODY_HTML', 'BODY'], 'SEARCH_CONTENT', 'HEADER']);

			$params = $DB->PrepareUpdate("b_mail_message", $arFields);
			$sql = sprintf("UPDATE b_mail_message SET %s WHERE ID=%s", $params, $ID);
		}

		$DB->Query($sql, false, "File: " . __FILE__ . "<br>Line: " . __LINE__);

		static::saveForDeferredDownload($ID, $arFields, $mailboxID);

		return true;
	}

	private static function trimContent(array &$fields, $trimLength, $filters)
	{
		foreach ($filters as $filter)
		{
			if (is_array($filter))
			{
				$filter = array_filter(
					$filter,
					function ($name) use ($fields)
					{
						return isset($fields[$name]);
					}
				);

				$totalLength = array_reduce(
					$filter,
					function ($total, $name) use ($fields)
					{
						$length = BinaryString::getLength($fields[$name]);
						return $total + $length;
					},
					0
				);

				if ($totalLength === 0)
				{
					continue;
				}

				$overLength = 0;

				foreach ($filter as $subFilter)
				{
					$length = BinaryString::getLength($fields[$subFilter]);
					$ratio = $length / $totalLength;
					$over = ceil($trimLength * $ratio);
					$newLength = $length - $over;
					$fields[$subFilter] = $newLength > 0 ? BinaryString::getSubstring($fields[$subFilter], 0, $newLength) : '';
					$overLength += $newLength > 0 ? $over : $length;
				}

				$trimLength -= $overLength;
			}
			else
			{
				if (isset($fields[$filter]))
				{
					$length = BinaryString::getLength($fields[$filter]);
					$newLength = $length - $trimLength;
					$fields[$filter] = $newLength > 0 ? BinaryString::getSubstring($fields[$filter], 0, $newLength) : '';
					$trimLength -= $newLength > 0 ? $trimLength : $length;
				}
			}

			if ($trimLength <= 0)
			{
				break;
			}
		}

		return $fields;
	}

	public static function Delete($id)
	{
		global $DB;
		$id = intval($id);

		$res = $DB->query('SELECT FILE_ID FROM b_mail_msg_attachment WHERE MESSAGE_ID = '.$id);
		while ($file = $res->fetch())
		{
			if ($file['FILE_ID'])
			{
				CFile::delete($file['FILE_ID']);
				\Bitrix\Mail\Helper\Attachment\Storage::unregisterAttachment($file['FILE_ID']);
			}
		}

		$strSql = "DELETE FROM b_mail_msg_attachment WHERE MESSAGE_ID=".$id;
		$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);

		$DB->query(sprintf('DELETE FROM b_mail_message_access WHERE MESSAGE_ID = %u', $id));

		$DB->query(sprintf('DELETE FROM b_mail_message_closure WHERE MESSAGE_ID = %1$u OR PARENT_ID = %1$u', $id));

		$strSql = "DELETE FROM b_mail_message WHERE ID=".$id;
		$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);

		return true;
	}

	public static function MarkAsSpam($ID, $bIsSPAM = true, $arRow = false)
	{
		global $DB;
		$res = null;

		if(!is_array($arRow))
			$res = $DB->Query("SELECT SPAM, HEADER, BODY_HTML, BODY, MAILBOX_ID FROM b_mail_message WHERE ID=".intval($ID));
		else
			$ar = $arRow;

		if(is_array($arRow) || $res && ($ar = $res->Fetch()))
		{
			if (empty($ar['FOR_SPAM_TEST']))
			{
				$ar['FOR_SPAM_TEST'] = sprintf('%s %s', $ar['HEADER'], $ar['BODY_HTML'] ?: $ar['BODY'] );
			}

			if($bIsSPAM)
			{
				if($ar["SPAM"]!="Y")
				{
					if($ar["SPAM"]=="N")
						CMailFilter::DeleteFromSpamBase($ar["FOR_SPAM_TEST"], false);
					CMailFilter::MarkAsSpam($ar["FOR_SPAM_TEST"], true);
					CMailMessage::Update($ID, Array("SPAM"=>"Y"));

					CMailLog::AddMessage(
						Array(
							"MAILBOX_ID"=>$ar["MAILBOX_ID"],
							"MESSAGE_ID"=>$ID,
							"LOG_TYPE"=>"SPAM"
							)
					);
				}
			}
			else
			{
				if($ar["SPAM"]!="N")
				{
					if($ar["SPAM"]=="Y")
						CMailFilter::DeleteFromSpamBase($ar["FOR_SPAM_TEST"], true);
					CMailFilter::MarkAsSpam($ar["FOR_SPAM_TEST"], false);
					CMailMessage::Update($ID, Array("SPAM"=>"N"));

					CMailLog::AddMessage(
						Array(
							"MAILBOX_ID"=>$ar["MAILBOX_ID"],
							"MESSAGE_ID"=>$ID,
							"LOG_TYPE"=>"NOTSPAM"
							)
					);
				}
			}
			$DB->Query("UPDATE b_mail_message SET SPAM_LAST_RESULT='N' WHERE ID=".intval($ID));
		}
	}

	public static function addAttachment($arFields)
	{
		global $DB;

		$arFields['FILE_NAME'] = trim($arFields['FILE_NAME']);

		$strSql = "SELECT ID, MAILBOX_ID, ATTACHMENTS FROM b_mail_message WHERE ID=".intval($arFields["MESSAGE_ID"]);
		$dbr = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		if(!($dbr_arr = $dbr->Fetch()))
			return false;

		$n = intval($dbr_arr["ATTACHMENTS"])+1;
		if (empty($arFields['FILE_NAME']))
		{
			$arFields['FILE_NAME'] = sprintf(
				'%u-%u-%u.%s',
				$dbr_arr['MAILBOX_ID'], $dbr_arr['ID'], $n,
				mb_strpos($arFields['CONTENT_TYPE'], 'message/') === 0 ? 'msg' : 'file'
			);
		}

		if(is_set($arFields, "CONTENT_TYPE"))
			$arFields["CONTENT_TYPE"] = mb_strtolower($arFields["CONTENT_TYPE"]);

		if(mb_strpos($arFields["CONTENT_TYPE"], "image/") === 0 && (!is_set($arFields, "IMAGE_WIDTH") || !is_set($arFields, "IMAGE_HEIGHT")) && is_set($arFields, "FILE_DATA"))
		{
			$filename = CTempFile::GetFileName(md5(uniqid("")).'.tmp');
			CheckDirPath($filename);
			if(file_put_contents($filename, $arFields["FILE_DATA"]) !== false)
			{
				$img_arr = CFile::GetImageSize($filename);
				$arFields["IMAGE_WIDTH"] = $img_arr? $img_arr[0]: 0;
				$arFields["IMAGE_HEIGHT"] = $img_arr? $img_arr[1]: 0;
			}
		}

		if(is_set($arFields, "FILE_DATA") && !is_set($arFields, "FILE_SIZE"))
			$arFields["FILE_SIZE"] = strlen($arFields["FILE_DATA"]);

		$file = array(
			'name'      => md5($arFields['FILE_NAME']),
			'size'      => $arFields['FILE_SIZE'],
			'type'      => $arFields['CONTENT_TYPE'],
			'content'   => $arFields['FILE_DATA'],
			'MODULE_ID' => 'mail'
		);

		if (!($file_id = CFile::saveFile($file, 'mail/attachment')))
		{
			\CMail::option('attachment_failure', true);
			return false;
		}

		\CMail::option('attachment_failure', false);

		unset($arFields['FILE_DATA']);
		$arFields['FILE_ID'] = $file_id;

		$ID = $DB->add('b_mail_msg_attachment', $arFields);

		if ($ID > 0)
		{
			$strSql = 'UPDATE b_mail_message SET ATTACHMENTS = ' . $n . ' WHERE ID = ' . intval($arFields['MESSAGE_ID']);
			$DB->query($strSql, false, 'File: '.__FILE__.'<br>Line: '.__LINE__);

			try
			{
				\Bitrix\Mail\Helper\Attachment\Storage::registerAttachment(array(
					'FILE_ID' => $arFields['FILE_ID'],
					'FILE_NAME' => $arFields['FILE_NAME'],
					'FILE_SIZE' => $arFields['FILE_SIZE'],
				));
			}
			catch (\Exception $e)
			{
				Application::getInstance()->getExceptionHandler()->writeToLog($e);
			}
		}

		return $ID;
	}

	/**
	 * Is message body to long
	 *
	 * @param string|null $messageBody Body string
	 *
	 * @return bool
	 */
	public static function isLongMessageBody(?string &$messageBody): bool
	{
		if (!$messageBody)
		{
			return false;
		}

		return mb_strlen($messageBody) > self::getBodyMaxLength();
	}

	/**
	 * Get max allowed email body length in symbols
	 *
	 * @return int
	 */
	public static function getBodyMaxLength(): int
	{
		$limit = (int)\Bitrix\Main\Config\Option::get('mail', '~max_email_body_length', false);
		return ($limit > 0) ? $limit : self::MAX_LENGTH_MESSAGE_BODY;
	}

	/**
	 * @param string|null $messageBodyHtml
	 * @param string|null $messageBody
	 * @return string
	 */
	private static function getClearBody(string $body): string
	{
		//todo merge with \Bitrix\Main\Mail\Mail::convertBodyHtmlToText
		// get <body> inner html if exists
		$innerBody = trim(preg_replace('/(.*?<body[^>]*>)(.*?)(<\/body>.*)/is', '$2', $body));
		$body = $innerBody ?: $body;

		// modify links to text version
		$body = preg_replace_callback(
			"%<a[^>]*?href=(['\"])(?<href>[^\1]*?)(?1)[^>]*?>(?<text>.*?)<\/a>%ims",
			function ($matches)
			{
				$href = $matches['href'];
				$text = trim($matches['text']);
				if (!$href)
				{
					return $matches[0];
				}
				$text = strip_tags($text);
				return ($text ? "$text:" : '') ."\n$href\n";
			},
			$body
		);

		// change <br> to new line
		$body = preg_replace('/\<br(\s*)?\/?\>/i', "\n", $body);

		$body = preg_replace('|(<style[^>]*>)(.*?)(<\/style>)|isU', '', $body);
		$body = preg_replace('|(<script[^>]*>)(.*?)(<\/script>)|isU', '', $body);

		// remove tags
		$body = strip_tags($body);

		// format text to the left side
		$lines = [];
		foreach (explode("\n", trim($body)) as $line)
		{
			$lines[] = trim($line);
		}

		// remove redundant new lines
		$body = preg_replace("/[\\n]{2,}/", "\n\n", implode("\n", $lines));

		// remove redundant spaces
		$body = preg_replace("/[ \\t]{2,}/", "  ", $body);

		// decode html-entities
		return html_entity_decode($body);
	}

	/**
	 * @param string $body
	 * @return string[]
	 */
	protected static function getTextHtmlBlock(string &$body): array
	{
		$messageBody = '';
		$messageBodyHtml = '';

		$boundaryMatches = [];
		preg_match('/content-type: multipart\/mixed; [\s\n\t]*boundary=\"(?<boundary>[\w+-]+)\"/i', $body, $boundaryMatches);
		if (is_string($boundaryMatches['boundary']))
		{
			$parts = explode('--'. $boundaryMatches['boundary'][0], $body);
			if (count($parts) > 1)
			{
				foreach ($parts as $part)
				{
					preg_match('/content-type: (?<contentType>text\/[html|plain]+); *charset="(?<charset>\w+-\d)"/i', $part, $contentTypeMatches);
					if (isset($contentTypeMatches['contentType']))
					{
						preg_match('/content-transfer-encoding: (?<encode>[\w]+)/i', $part, $encodeMatches);

						$partBody = self::getPartBody($part);
						if ($partBody === null)
						{
							continue;
						}

						if ($encodeMatches['encode'] === 'base64')
						{
							$partBody = base64_decode($partBody);
						}

						if ($contentTypeMatches['contentType'] === 'text/plain')
						{
							$messageBody = $partBody;
						}

						if ($contentTypeMatches['contentType'] === 'text/html')
						{
							$messageBodyHtml = $partBody;
						}
					}
				}
			}
		}

		return [$messageBody, $messageBodyHtml];
	}

	/**
	 * @param $messageBody
	 * @param $messageBodyHtml
	 * @return array
	 */
	public static function prepareLongMessage(&$messageBody, &$messageBodyHtml): array
	{
		if (mb_stripos($messageBody, '--- Below this line is a copy of the message'))
		{
			$mainBodyHtml = $mainBody = explode('--- Below this line is a copy of the message', $messageBody)[0];
		}
		elseif (mb_stripos($messageBodyHtml, '<blockquote'))
		{
			$mainBody = $mainBodyHtml = self::cutBlockQuote($messageBodyHtml);
		}
		elseif (mb_stripos($messageBodyHtml, htmlspecialcharsbx('<blockquote')))
		{
			$mainBody = $mainBodyHtml = self::cutBlockHtmlQuote($messageBodyHtml);
		}
		elseif (mb_stripos($messageBody, '<blockquote'))
		{
			$mainBody = $mainBodyHtml = self::cutBlockQuote($messageBody);
		}
		else
		{
			[$mainBody, $mainBodyHtml] = self::getTextHtmlBlock($messageBody);
			if ($mainBody === '' && $mainBodyHtml === '')
			{
				$limit = self::getBodyMaxLength();
				$mainBody = mb_substr($messageBody, 0, $limit);
				$mainBodyHtml = mb_substr($messageBodyHtml, 0, $limit);
			}
		}

		$mainBody = (string) $mainBody;
		$mainBodyHtml = (string) $mainBodyHtml;

		$mainBody = self::getClearBody($mainBody);

		return [$mainBody, $mainBodyHtml];
	}

	/**
	 * @param $part
	 * @return string
	 */
	protected static function getPartBody(&$part): ?string
	{
		$itemParts = explode("\r\n\r\n", $part);
		if (!is_array($itemParts) && (count($itemParts) < 2))
		{
			$itemParts = explode("\n\n", $part);
		}

		if (!is_array($itemParts) && (count($itemParts) < 2))
		{
			$itemParts = explode("\r\n\n\r\n", $part);
		}

		if (!is_array($itemParts) && (count($itemParts) < 2))
		{
			$itemParts = explode("\n\n\n", $part);
		}

		if (is_array($itemParts) && (count($itemParts) >= 2))
		{
			return $itemParts[1];
		}

		return null;
	}

	/**
	 * @param $messageBodyHtml
	 * @return array|string|string[]|null
	 */
	private static function cutBlockQuote(&$messageBodyHtml): array|string|null
	{
		return preg_replace('|(<blockquote([^>]*)>)(.*?)(<\/blockquote>)|isU', '', $messageBodyHtml);
	}

	/**
	 * @param $messageBodyHtml
	 * @return string
	 */
	private static function cutBlockHtmlQuote(&$messageBodyHtml): string
	{
		$messageBody = htmlspecialcharsback($messageBodyHtml);

		return htmlspecialcharsbx(self::cutBlockQuote($messageBody));
	}
}


class _CMailAttachmentDBRes extends CDBResult
{
	function __construct($res)
	{
		parent::__construct($res);
	}

	function fetch()
	{
		if (($res = parent::fetch()) && $res['FILE_ID'] > 0)
		{
			if ($file = \CFile::makeFileArray($res['FILE_ID']))
			{
				if (!empty($file['tmp_name']) && \Bitrix\Main\IO\File::isFileExists($file['tmp_name']))
				{
					$res['FILE_DATA'] = \Bitrix\Main\IO\File::getFileContents($file['tmp_name']);
				}
				else
				{
					$res['FILE_DATA'] = false;
				}
			}
		}

		return $res;
	}
}

class CMailAttachment
{
	public static function GetList($arOrder=Array(), $arFilter=Array())
	{
		global $DB;

		$strSql =
				"SELECT * ".
				"FROM b_mail_msg_attachment MA ";

		$arSqlSearch = Array();
		foreach ($arFilter as $key => $val)
		{
			$res = CMailUtil::MkOperationFilter($key);
			$key = mb_strtoupper($res["FIELD"]);
			$cOperationType = $res["OPERATION"];

			if($cOperationType == "?")
			{
				if ($val == '') continue;
				switch($key)
				{
				case "ID":
				case "MESSAGE_ID":
				case "FILE_SIZE":
				case "IMAGE_WIDTH":
				case "IMAGE_HEIGHT":
					$arSqlSearch[] = GetFilterQuery("MA.".$key, $val, "N");
					break;
				case "FILE_NAME":
				case "FILE_DATA":
					$arSqlSearch[] = GetFilterQuery("MA.".$key, $val);
					break;
				case "CONTENT_TYPE":
					$arSqlSearch[] = GetFilterQuery("MA.".$key, $val, "Y", array("/"));
					break;
				}
			}
			else
			{
				switch($key)
				{
				case "ID":
				case "MESSAGE_ID":
				case "FILE_SIZE":
				case "IMAGE_WIDTH":
				case "IMAGE_HEIGHT":
					$arSqlSearch[] = CMailUtil::FilterCreate("MA.".$key, $val, "number", $cOperationType);
					break;
				case "FILE_NAME":
				case "CONTENT_TYPE":
				case "FILE_DATA":
					$arSqlSearch[] = CMailUtil::FilterCreate("MA.".$key, $val, "string", $cOperationType);
					break;
				}
			}
		}

		$is_filtered = false;
		$strSqlSearch = "";
		for($i = 0, $n = count($arSqlSearch); $i < $n; $i++)
		{
			if($arSqlSearch[$i] <> '')
			{
				$strSqlSearch .= " AND  (".$arSqlSearch[$i].") ";
				$is_filtered = true;
			}
		}
		$arSqlOrder = Array();
		foreach($arOrder as $by=>$order)
		{
			$by = mb_strtolower($by);
			$order = mb_strtolower($order);

			if ($order!="asc")
				$order = "desc".($DB->type == "ORACLE"?" NULLS LAST":"");
			else
				$order = "asc".($DB->type == "ORACLE"?" NULLS FIRST":"");

			if ($by == "message_id")		$arSqlOrder[] = " MA.MESSAGE_ID ".$order." ";
			elseif ($by == "file_name")		$arSqlOrder[] = " MA.FILE_NAME ".$order." ";
			elseif ($by == "file_size")		$arSqlOrder[] = " MA.FILE_SIZE ".$order." ";
			elseif ($by == "content_type")	$arSqlOrder[] = " MA.CONTENT_TYPE ".$order." ";
			elseif ($by == "image_width")	$arSqlOrder[] = " MA.IMAGE_WIDTH ".$order." ";
			elseif ($by == "image_height")	$arSqlOrder[] = " MA.IMAGE_HEIGHT ".$order." ";
			else $arSqlOrder[] = " MA.ID ".$order." ";
		}

		$strSqlOrder = "";
		$arSqlOrder = array_unique($arSqlOrder);
		DelDuplicateSort($arSqlOrder);

		for ($i = 0, $n = count($arSqlOrder); $i < $n; $i++)
		{
			if($i==0)
				$strSqlOrder = " ORDER BY ";
			else
				$strSqlOrder .= ",";

			$strSqlOrder .= $arSqlOrder[$i];
		}

		$strSql .= " WHERE 1=1 ".$strSqlSearch.$strSqlOrder;
		//echo "<pre>".$strSql."</pre>";
		$dbr = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		$dbr = new _CMailAttachmentDBRes($dbr);
		$dbr->is_filtered = $is_filtered;
		return $dbr;
	}

	public static function GetByID($ID)
	{
		return CMailAttachment::GetList(Array(), Array("=ID"=>$ID));
	}

	function Delete($id)
	{
		global $DB;
		$id = intval($id);

		$res = $DB->query('SELECT FILE_ID FROM b_mail_msg_attachment WHERE MESSAGE_ID = '.$id);
		while ($file = $res->fetch())
		{
			if ($file['FILE_ID'])
			{
				CFile::delete($file['FILE_ID']);
				\Bitrix\Mail\Helper\Attachment\Storage::unregisterAttachment($file['FILE_ID']);
			}
		}

		$strSql = "DELETE FROM b_mail_msg_attachment WHERE ID=".$id;
		$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
	}

	public static function getContents($attachment)
	{
		if (!is_array($attachment))
		{
			if ($res = CMailAttachment::getByID($attachment))
				$attachment = $res->fetch();
		}

		if (is_array($attachment))
		{
			if (!empty($attachment['FILE_DATA']))
				return $attachment['FILE_DATA'];

			if ($attachment['FILE_ID'] > 0)
			{
				if ($file = \CFile::makeFileArray($attachment['FILE_ID']))
				{
					return (!empty($file['tmp_name'])
						&& \Bitrix\Main\IO\File::isFileExists($file['tmp_name']))
							? \Bitrix\Main\IO\File::getFileContents($file['tmp_name'])
							: false;
				}
			}
		}

		return false;
	}
}

class CAllMailUtil
{
	public static function convertCharset($str, $from, $to)
	{
		if (!trim($str))
			return $str;

		$from = trim(mb_strtolower($from));
		$to   = trim(mb_strtolower($to));

		$escape = function ($matches)
		{
			return isset($matches[2]) ? '?' : $matches[1];
		};

		if ($from != $to)
		{
			if (in_array($from, array('utf-8', 'utf8')))
			{
				// escape all invalid (rfc-3629) utf-8 characters
				$str = preg_replace_callback('/
					([\x00-\x7F]+
						|[\xC2-\xDF][\x80-\xBF]
						|\xE0[\xA0-\xBF][\x80-\xBF]|[\xE1-\xEC\xEE\xEF][\x80-\xBF]{2}|\xED[\x80-\x9F][\x80-\xBF]
						|\xF0[\x90-\xBF][\x80-\xBF]{2}|[\xF1-\xF3][\x80-\xBF]{3}|\xF4[\x80-\x8F][\x80-\xBF]{2})
					|([\x80-\xFF])
				/x', $escape, $str);
			}

			if ($result = Bitrix\Main\Text\Encoding::convertEncoding($str, $from, $to))
				$str = $result;
			else
				addMessage2Log(sprintf('Failed to convert email part. (%s -> %s : %s)', $from, $to, $error));
		}

		if (in_array($to, array('utf-8', 'utf8')))
		{
			// escape invalid (rfc-3629) and 4-bytes utf-8 characters
			$str = preg_replace_callback('/
				([\x00-\x7F]+
					|[\xC2-\xDF][\x80-\xBF]
					|\xE0[\xA0-\xBF][\x80-\xBF]|[\xE1-\xEC\xEE\xEF][\x80-\xBF]{2}|\xED[\x80-\x9F][\x80-\xBF])
				|([\x80-\xFF])
			/x', $escape, $str);
		}

		return $str;
	}

	public static function uue_decode($str)
	{
		preg_match("/begin [0-7]{3} .+?\r?\n(.+)?\r?\nend/is", $str, $reg);

		$str = $reg[1];
		$res = '';
		$str = preg_split("/\r?\n/", trim($str));
		$strlen = count($str);
		$spaceCharOrd = 0;

		for ($i = 0; $i < $strlen; $i++)
		{
			$pos = 1;
			$d = 0;
			$len= (((ord(mb_substr($str[$i], 0, 1)) -32) - $spaceCharOrd) & 077);

			while (($d + 3 <= $len) AND ($pos + 4 <= mb_strlen($str[$i])))
			{
				$c0 = (ord(mb_substr($str[$i], $pos, 1)) ^ 0x20);
				$c1 = (ord(mb_substr($str[$i], $pos + 1, 1)) ^ 0x20);
				$c2 = (ord(mb_substr($str[$i], $pos + 2, 1)) ^ 0x20);
				$c3 = (ord(mb_substr($str[$i], $pos + 3, 1)) ^ 0x20);
				$res .= chr(((($c0 - $spaceCharOrd) & 077) << 2) | ((($c1 - $spaceCharOrd) & 077) >> 4)).
						chr(((($c1 - $spaceCharOrd) & 077) << 4) | ((($c2 - $spaceCharOrd) & 077) >> 2)).
						chr(((($c2 - $spaceCharOrd) & 077) << 6) |  (($c3 - $spaceCharOrd) & 077));

				$pos += 4;
				$d += 3;
			}

			if (($d + 2 <= $len) && ($pos + 3 <= mb_strlen($str[$i])))
			{
				$c0 = (ord(mb_substr($str[$i], $pos, 1)) ^ 0x20);
				$c1 = (ord(mb_substr($str[$i], $pos + 1, 1)) ^ 0x20);
				$c2 = (ord(mb_substr($str[$i], $pos + 2, 1)) ^ 0x20);
				$res .= chr(((($c0 - $spaceCharOrd) & 077) << 2) | ((($c1 - $spaceCharOrd) & 077) >> 4)).
						chr(((($c1 - $spaceCharOrd) & 077) << 4) | ((($c2 - $spaceCharOrd) & 077) >> 2));

				$pos += 3;
				$d += 2;
			}

			if (($d + 1 <= $len) && ($pos + 2 <= mb_strlen($str[$i])))
			{
				$c0 = (ord(mb_substr($str[$i], $pos, 1)) ^ 0x20);
				$c1 = (ord(mb_substr($str[$i], $pos + 1, 1)) ^ 0x20);
				$res .= chr(((($c0 - $spaceCharOrd) & 077) << 2) | ((($c1 - $spaceCharOrd) & 077) >> 4));
			}
		}

		return $res;
	}

	public static function MkOperationFilter($key)
	{
		if(mb_substr($key, 0, 1) == "!")
		{
			$key = mb_substr($key, 1);
			$cOperationType = "N";
		}
		elseif(mb_substr($key, 0, 2) == ">=")
		{
			$key = mb_substr($key, 2);
			$cOperationType = "GE";
		}
		elseif(mb_substr($key, 0, 1) == ">")
		{
			$key = mb_substr($key, 1);
			$cOperationType = "G";
		}
		elseif(mb_substr($key, 0, 2) == "<=")
		{
			$key = mb_substr($key, 2);
			$cOperationType = "LE";
		}
		elseif(mb_substr($key, 0, 1) == "<")
		{
			$key = mb_substr($key, 1);
			$cOperationType = "L";
		}
		elseif(mb_substr($key, 0, 1) == "=")
		{
			$key = mb_substr($key, 1);
			$cOperationType = "E";
		}
		else
			$cOperationType = "?";

		return Array("FIELD"=>$key, "OPERATION"=>$cOperationType);
	}

	public static function FilterCreate($fname, $vals, $type, $cOperationType=false, $bSkipEmpty = true)
	{
		return CMailUtil::FilterCreateEx($fname, $vals, $type, $bFullJoin, $cOperationType, $bSkipEmpty);
	}

	public static function FilterCreateEx($fname, $vals, $type, &$bFullJoin, $cOperationType=false, $bSkipEmpty = true)
	{
		global $DB;
		if(!is_array($vals))
			$vals=Array($vals);

		if(count($vals)<1)
			return "";

		if(is_bool($cOperationType))
		{
			if($cOperationType===true)
				$cOperationType = "N";
			else
				$cOperationType = "E";
		}

		if($cOperationType=="G")
			$strOperation = ">";
		elseif($cOperationType=="GE")
			$strOperation = ">=";
		elseif($cOperationType=="LE")
			$strOperation = "<=";
		elseif($cOperationType=="L")
			$strOperation = "<";
		else
			$strOperation = "=";

		$bFullJoin = false;
		$bWasLeftJoin = false;

		$res = Array();
		for($i = 0, $n = count($vals); $i < $n; $i++)
		{
			$val = $vals[$i];
			if(!$bSkipEmpty || $val <> '' || (is_bool($val) && $val===false))
			{
				switch ($type)
				{
				case "string_equal":
					if($val == '')
						$res[] = ($cOperationType=="N"?"NOT":"")."(".$fname." IS NULL OR ".$DB->Length($fname)."<=0)";
					else
						$res[] = ($cOperationType=="N"?" ".$fname." IS NULL OR NOT ":"")."(".CIBlock::_Upper($fname).$strOperation.CIBlock::_Upper("'".$DB->ForSql($val)."'").")";
					break;
				case "string":
					if($val == '')
						$res[] = ($cOperationType=="N"?"NOT":"")."(".$fname." IS NULL OR ".$DB->Length($fname)."<=0)";
					else
						if($strOperation=="=")
							$res[] = ($cOperationType=="N"?" ".$fname." IS NULL OR NOT ":"")."(".($DB->type == "ORACLE"?CIBlock::_Upper($fname)." LIKE ".CIBlock::_Upper("'".$DB->ForSqlLike($val)."'")." ESCAPE '\\'" : $fname." ".($strOperation=="="?"LIKE":$strOperation)." '".$DB->ForSqlLike($val)."'").")";
						else
							$res[] = ($cOperationType=="N"?" ".$fname." IS NULL OR NOT ":"")."(".($DB->type == "ORACLE"?CIBlock::_Upper($fname)." ".$strOperation." ".CIBlock::_Upper("'".$DB->ForSql($val)."'")." " : $fname." ".$strOperation." '".$DB->ForSql($val)."'").")";
					break;
				case "date":
					if($val == '')
						$res[] = ($cOperationType=="N"?"NOT":"")."(".$fname." IS NULL)";
					else
						$res[] = ($cOperationType=="N"?" ".$fname." IS NULL OR NOT ":"")."(".$fname." ".$strOperation." ".$DB->CharToDateFunction($DB->ForSql($val), "FULL").")";
					break;
				case "number":
					if($val == '')
						$res[] = ($cOperationType=="N"?"NOT":"")."(".$fname." IS NULL)";
					else
						$res[] = ($cOperationType=="N"?" ".$fname." IS NULL OR NOT ":"")."(".$fname." ".$strOperation." '".DoubleVal($val)."')";
					break;
				case "number_above":
					if($val == '')
						$res[] = ($cOperationType=="N"?"NOT":"")."(".$fname." IS NULL)";
					else
						$res[] = ($cOperationType=="N"?" ".$fname." IS NULL OR NOT ":"")."(".$fname." ".$strOperation." '".$DB->ForSql($val)."')";
					break;
				}

				// INNER JOIN on such conditions
				if($val <> '' && $cOperationType!="N")
					$bFullJoin = true;
				else
					$bWasLeftJoin = true;
			}
		}

		$strResult = "";
		for($i = 0, $n = count($res); $i < $n; $i++)
		{
			if($i>0)
				$strResult .= ($cOperationType=="N"?" AND ":" OR ");
			$strResult .= "(".$res[$i].")";
		}
		if($strResult!="")
			$strResult = "(".$strResult.")";

		if($bFullJoin && $bWasLeftJoin && $cOperationType!="N")
			$bFullJoin = false;

		return $strResult;
	}

	public static function ByteXOR($a, $b, $l)
	{
		$c = "";
		for ($i = 0; $i < $l; $i++)
		{
			if (isset($a[$i]) && isset($b[$i]))
				$c .= $a[$i] ^ $b[$i];
		}

		return $c;
	}

	public static function BinMD5($val)
	{
		return(pack("H*",md5($val)));
	}

	public static function Decrypt($str, $key=false)
	{
		$res = '';
		if($key===false)
			$key = COption::GetOptionString("main", "pwdhashadd", "");
		$key1 = CMailUtil::BinMD5($key);
		$str = base64_decode($str);
		while (strlen($str) > 0)
		{
			$m = substr($str, 0, 16);
			$str = substr($str, 16);
			$m = CMailUtil::ByteXOR($m, $key1, 16);
			$res .= $m;
			$key1 = CMailUtil::BinMD5($key.$key1.$m);
		}
		return $res;
	}

	public static function Crypt($str, $key=false)
	{
		$res = '';
		if($key===false)
			$key = COption::GetOptionString("main", "pwdhashadd", "");
		$key1 = CMailUtil::BinMD5($key);
		while (strlen($str) > 0)
		{
			$m = substr($str, 0, 16);
			$str = substr($str, 16);
			$res .= CMailUtil::ByteXOR($m, $key1, 16);
			$key1 = CMailUtil::BinMD5($key.$key1.$m);
		}
		return(base64_encode($res));
	}

	public static function extractAllMailAddresses($emails)
	{
		$result = array();
		$arEMails = explode(",", $emails);
		foreach($arEMails as $mail)
		{
			$result[] = CMailUtil::ExtractMailAddress($mail);
		}
		return $result;
	}


	public static function ExtractMailAddress($email)
	{
		$email = trim($email);
		if(($pos = mb_strpos($email, "<"))!==false)
			$email = mb_substr($email, $pos + 1);
		if(($pos = mb_strpos($email, ">"))!==false)
			$email = mb_substr($email, 0, $pos);
		return mb_strtolower($email);
	}

	public static function checkImapMailbox($server, $port, $use_tls, $login, $password, &$error)
	{
		$use_tls = is_string($use_tls) ? $use_tls : ($use_tls ? 'Y' : 'N');

		$imap = new \Bitrix\Mail\Imap(
			$server, $port,
			$use_tls == 'Y' || $use_tls == 'S',
			$use_tls == 'Y',
			$login, $password,
			'UTF-8'
		);

		if (($unseen = $imap->getUnseen('INBOX', $error)) === false)
			return (-1);

		$error = false;
		return $unseen;
	}
}


global $BX_MAIL_FILTER_CACHE, $BX_MAIL_SPAM_CNT;
$BX_MAIL_FILTER_CACHE = Array();
$BX_MAIL_SPAM_CNT = Array();

class CMailFilter
{
	public static function GetList($arOrder=Array(), $arFilter=Array(), $bCnt=false)
	{
		global $DB;
		$strSql =
				"SELECT ".
				($bCnt
				?
				"	COUNT('x') as CNT "
				:
				"	MF.*, MB.NAME as MAILBOX_NAME, MB.ID as MAILBOX_ID, MB.SERVER_TYPE as MAILBOX_TYPE, MB.DOMAINS as DOMAINS, ".
				"	".$DB->DateToCharFunction("MF.TIMESTAMP_X")."	as TIMESTAMP_X "
				).
				"	".
				"FROM b_mail_mailbox MB ".($arFilter["EMPTY"]=="Y"?"LEFT":"INNER")." JOIN b_mail_filter MF ON MB.ID=MF.MAILBOX_ID ";

		if(!is_array($arFilter))
			$arFilter = Array();
		$arSqlSearch = Array();
		$filter_keys = array_keys($arFilter);

		for($i = 0, $n = count($filter_keys); $i < $n; $i++)
		{
			$val = $arFilter[$filter_keys[$i]];
			if ($val == '') continue;
			$key = mb_strtoupper($filter_keys[$i]);
			switch($key)
			{
			case "NAME":
			case "PHP_CONDITION":
			case "ACTION_PHP":
				$arSqlSearch[] = GetFilterQuery("MF.".$key, $val);
				break;
			case "SERVER_TYPE":
				$arSqlSearch[] = GetFilterQuery("MB.".$key, $val, "N");
				break;
			case "ID":
			case "ACTION_TYPE":
			case "MAILBOX_ID":
			case "PARENT_FILTER_ID":
			case "SORT":
			case "WHEN_MAIL_RECEIVED":
			case "WHEN_MANUALLY_RUN":
			case "ACTION_STOP_EXEC":
			case "ACTION_DELETE_MESSAGE":
			case "ACTION_READ":
			case "ACTIVE":
				$arSqlSearch[] = GetFilterQuery("MF.".$key, $val, "N");
				break;
			}
		}

		$is_filtered = false;
		$strSqlSearch = "";
		for($i = 0, $n = count($arSqlSearch); $i < $n; $i++)
		{
			if($arSqlSearch[$i] <> '')
			{
				$strSqlSearch .= " AND  (".$arSqlSearch[$i].") ";
				$is_filtered = true;
			}
		}

		$arSqlOrder = Array();
		foreach($arOrder as $by=>$order)
		{
			$order = mb_strtolower($order);
			if ($order!="asc")
				$order = "desc".($DB->type == "ORACLE"?" NULLS LAST":"");
			else
				$order = "asc".($DB->type == "ORACLE"?" NULLS FIRST":"");

			switch(mb_strtoupper($by))
			{
				case "TIMESTAMP_X":
				case "MAILBOX_ID":
				case "ACTIVE":
				case "NAME":
				case "SORT":
				case "PARENT_FILTER_ID":
				case "WHEN_MAIL_RECEIVED":
				case "WHEN_MANUALLY_RUN":
				case "ACTION_STOP_EXEC":
				case "ACTION_DELETE_MESSAGE":
				case "ACTION_READ":
					$arSqlOrder[] = " MF.".$by." ".$order." ";
					break;
				case "MAILBOX_NAME":
					$arSqlOrder[] = " MB.NAME ".$order." ";
					$arSqlOrder[] = " MF.ID ".$order." ";
					break;
				default:
					$arSqlOrder[] = " MF.ID ".$order." ";
			}
		}

		$strSqlOrder = "";
		$arSqlOrder = array_unique($arSqlOrder);
		DelDuplicateSort($arSqlOrder);

		for ($i = 0, $n = count($arSqlOrder); $i < $n; $i++)
		{
			if($i==0)
				$strSqlOrder = " ORDER BY ";
			else
				$strSqlOrder .= ",";

			$strSqlOrder .= $arSqlOrder[$i];
		}

		$strSql .= " WHERE 1=1 ".$strSqlSearch.$strSqlOrder;

		$res = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		$res->is_filtered = $is_filtered;
		return $res;
	}

	public static function GetByID($ID)
	{
		global $DB;
		return CMailFilter::GetList(Array(), Array("ID"=>$ID));
	}

	public static function CheckPHP($code, $field_name)
	{
		return true; // not work - E_CODE_ERROR

		global $php_errormsg;
		ini_set("track_errors", "on");
		$php_errormsg_prev = $php_errormsg;
		ob_start();
		error_reporting(0);
		@eval($code);
		ob_end_clean();
		if($php_errormsg != "")
			CMailError::SetError("B_MAIL_ERR_PHP", GetMessage("MAIL_CL_ERR_IN_PHP").$field_name.". (".$php_errormsg.")");
		$php_errormsg = $php_errormsg_prev;
		ini_set("track_errors", $prev);
	}

	public static function CheckConditionTypes($fields)
	{
		if(is_set($fields, 'CONDITIONS'))
		{
			$errors = [];

			$whiteList = [
				'TYPE',
				'STRINGS',
				'COMPARE_TYPE',
				'ID',
				'FILTER_ID',
			];

			foreach ($fields['CONDITIONS'] as $item)
			{
				foreach ($item as $key => $value)
				{
					if(!in_array($key, $whiteList))
					{
						$errors[] = array("id"=>"INVALID_CONDITION_TYPE", "text"=> GetMessage("MAIL_INVALID_CONDITION_TYPE"));
						$GLOBALS["APPLICATION"]->ThrowException(new CAdminException($errors));
						return false;
					}
				}
			}
		}
		return true;
	}

	public static function CheckFields($arFields, $ID=false)
	{
		$err_cnt = CMailError::ErrCount();
		$arMsg = Array();

		if(is_set($arFields, "NAME") && mb_strlen($arFields["NAME"]) < 1)
		{
			CMailError::SetError("B_MAIL_ERR_NAME", GetMessage("MAIL_CL_ERR_NAME")." \"".GetMessage("MAIL_CL_NAME")."\"");
			$arMsg[] = array("id"=>"NAME", "text"=> GetMessage("MAIL_CL_ERR_NAME")." \"".GetMessage("MAIL_CL_NAME")."\"");
		}

		if(is_set($arFields, "PHP_CONDITION") && trim($arFields["PHP_CONDITION"]) <> '')
		{
			if (!CMailFilter::CheckPHP($arFields["PHP_CONDITION"], GetMessage("MAIL_CL_PHP_COND")))
				$arMsg[] = array("id"=>"PHP_CONDITION", "text"=> GetMessage("MAIL_CL_ERR_IN_PHP").GetMessage("MAIL_CL_PHP_COND"));
		}

		if(is_set($arFields, "ACTION_PHP") && trim($arFields["ACTION_PHP"]) <> '')
		{
			if (!CMailFilter::CheckPHP($arFields["ACTION_PHP"], GetMessage("MAIL_CL_PHP_ACT")))
				$arMsg[] = array("id"=>"ACTION_PHP", "text"=> GetMessage("MAIL_CL_ERR_IN_PHP").GetMessage("MAIL_CL_PHP_ACT"));
		}

		if(is_set($arFields, "MAILBOX_ID"))
		{
			$r = CMailBox::GetByID($arFields["MAILBOX_ID"]);
			if(!$r->Fetch())
			{
				CMailError::SetError("B_MAIL_ERR_BAD_MAILBOX", GetMessage("MAIL_CL_ERR_WRONG_MAILBOX"));
				$arMsg[] = array("id"=>"MAILBOX_ID", "text"=> GetMessage("MAIL_CL_ERR_WRONG_MAILBOX"));
			}
		}
		elseif($ID===false)
		{
			CMailError::SetError("B_MAIL_ERR_BAD_MAILBOX_NA", GetMessage("MAIL_CL_ERR_MAILBOX_NA"));
			$arMsg[] = array("id"=>"MAILBOX_ID", "text"=> GetMessage("MAIL_CL_ERR_MAILBOX_NA"));
		}

		if(!empty($arMsg))
		{
			$e = new CAdminException($arMsg);
			$GLOBALS["APPLICATION"]->ThrowException($e);
			return false;
		}
		return true;

		//return ($err_cnt == CMailError::ErrCount());
	}

	public static function Add($arFields)
	{
		if(!CMailFilter::CheckConditionTypes($arFields))
		{
			return false;
		}

		global $DB;

		if(is_set($arFields, "ACTIVE") && $arFields["ACTIVE"]!="Y")
			$arFields["ACTIVE"]="N";

		if(is_set($arFields, "ACTION_READ") && $arFields["ACTION_READ"]!="Y" && $arFields["ACTION_READ"]!="N")
			$arFields["ACTION_READ"] = "-";

		if(is_set($arFields, "ACTION_SPAM") && $arFields["ACTION_SPAM"]!="Y" && $arFields["ACTION_SPAM"]!="N")
			$arFields["ACTION_SPAM"] = "-";

		if(is_set($arFields, "ACTION_DELETE_MESSAGE") && $arFields["ACTION_DELETE_MESSAGE"]!="Y")
			$arFields["ACTION_DELETE_MESSAGE"] ="N";

		if(is_set($arFields, "ACTION_STOP_EXEC") && $arFields["ACTION_STOP_EXEC"]!="Y")
			$arFields["ACTION_STOP_EXEC"] = "N";

		if(!CMailFilter::CheckFields($arFields))
			return false;

		$ID = $DB->Add("b_mail_filter", $arFields, Array("PHP_CONDITION", "ACTION_PHP"));

		if(is_set($arFields, "CONDITIONS"))
			CMailFilterCondition::SetConditions($ID, $arFields["CONDITIONS"]);

		CMailbox::SMTPReload();

		return $ID;
	}


	public static function Update($ID, $arFields)
	{
		if(!CMailFilter::CheckConditionTypes($arFields))
		{
			return false;
		}

		global $DB;
		$ID = intval($ID);

		if(is_set($arFields, "ACTIVE") && $arFields["ACTIVE"]!="Y")
			$arFields["ACTIVE"]="N";
		if(is_set($arFields, "WHEN_MAIL_RECEIVED") && $arFields["WHEN_MAIL_RECEIVED"]!="Y")
			$arFields["WHEN_MAIL_RECEIVED"] = "N";
		if(is_set($arFields, "WHEN_MANUALLY_RUN") && $arFields["WHEN_MANUALLY_RUN"]!="Y")
			$arFields["WHEN_MANUALLY_RUN"] = "N";
		if(is_set($arFields, "ACTION_READ") && $arFields["ACTION_READ"]!="Y" && $arFields["ACTION_READ"]!="N")
			$arFields["ACTION_READ"] = "-";
		if(is_set($arFields, "ACTION_SPAM") && $arFields["ACTION_SPAM"]!="Y" && $arFields["ACTION_SPAM"]!="N")
			$arFields["ACTION_SPAM"] = "-";
		if(is_set($arFields, "ACTION_DELETE_MESSAGE") && $arFields["ACTION_DELETE_MESSAGE"]!="Y")
			$arFields["ACTION_DELETE_MESSAGE"] ="N";
		if(is_set($arFields, "ACTION_STOP_EXEC") && $arFields["ACTION_STOP_EXEC"]!="Y")
			$arFields["ACTION_STOP_EXEC"] = "N";

		if(!CMailFilter::CheckFields($arFields, $ID))
			return false;

		$arUpdateBinds = array();
		$strUpdate = $DB->PrepareUpdateBind("b_mail_filter", $arFields,"", false, $arUpdateBinds);

		$strSql =
			"UPDATE b_mail_filter SET ".
				$strUpdate." ".
			"WHERE ID=".$ID;

		$arBinds = array();
		foreach($arUpdateBinds as $field_id)
			$arBinds[$field_id] = $arFields[$field_id];

		$DB->QueryBind($strSql, $arBinds);

		if(is_set($arFields, "CONDITIONS"))
			CMailFilterCondition::SetConditions($ID, $arFields["CONDITIONS"]);

		CMailbox::SMTPReload();

		return true;
	}

	public static function Delete($ID)
	{
		global $DB;
		$ID = intval($ID);
		$dbr = CMailFilterCondition::GetList(Array(), Array("FILTER_ID"=>$ID));
		while($r = $dbr->Fetch())
		{
			if(!CMailFilterCondition::Delete($r["ID"]))
				return false;
		}

		$strSql = "DELETE FROM b_mail_filter WHERE ID=".$ID;
		CMailbox::SMTPReload();
		return $DB->Query($strSql, true);
	}

	public static function Filter($arFields, $event, $FILTER_ID=false, $PARENT_FILTER_ID = false)
	{
		global $BX_MAIL_FILTER_CACHE, $DB;
		$PARENT_FILTER_ID = intval($PARENT_FILTER_ID);
		$MAILBOX_ID = intval($arFields["MAILBOX_ID"]);
		$MESSAGE_ID = intval($arFields["ID"]);

		$cache_param = $MAILBOX_ID."|".$PARENT_FILTER_ID."|".$event."|".$FILTER_ID;

		if(is_set($BX_MAIL_FILTER_CACHE, $cache_param))
		{
			$arFilterCond = $BX_MAIL_FILTER_CACHE[$cache_param]["CONDITIONS"];
			$arFilter = $BX_MAIL_FILTER_CACHE[$cache_param]["FILTER"];
		}
		else
		{
			$strSqlAdd = "";
			if($event=="R")
				$strSqlAdd .= "	AND (WHEN_MAIL_RECEIVED='Y')";
			else
				$strSqlAdd .= "	AND (WHEN_MANUALLY_RUN='Y' ".(intval($FILTER_ID)>0?" AND f.ID='".intval($FILTER_ID)."'":"").")";

			$strSql =
				"SELECT f.*, c.*, f.ID, c.ID as CONDITION_ID
				FROM b_mail_filter f LEFT JOIN b_mail_filter_cond c ON f.ID = c.FILTER_ID
				WHERE (f.MAILBOX_ID = ".$MAILBOX_ID." OR MAILBOX_ID IS NULL)
					AND f.ACTIVE = 'Y'
					AND (f.PARENT_FILTER_ID = " . ($PARENT_FILTER_ID > 0 ? $PARENT_FILTER_ID : "0 OR f.PARENT_FILTER_ID IS NULL") . ")" .
					$strSqlAdd."
				ORDER BY f.SORT, f.ID";

			$dbr = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);

			$arFilter = Array();
			$arFilterCond = Array();
			$prev_ID = 0;
			$arr_prev = false;
			$arConds = Array();
			while($arr = $dbr->Fetch())
			{
				$arFilter[$arr["ID"]] = $arr;
				if($arr["CONDITION_ID"]>0)
				{
					if(!is_array($arFilterCond[$arr["ID"]]))
						$arFilterCond[$arr["ID"]] = Array();
					$arFilterCond[$arr["ID"]][] = $arr;
				}
			}

			$BX_MAIL_FILTER_CACHE[$cache_param] = Array("FILTER"=>$arFilter, "CONDITIONS"=>$arFilterCond);
		}

		$arFieldsOriginal = $arFields;
		foreach($arFilter as $filter_id=>$arFilterParams)
		{
			$arFields = $arFieldsOriginal;
			$arFields["MAIL_FILTER"] = $arFilterParams;

			$arAllConditions = isset($arFilterCond[$filter_id]) ? $arFilterCond[$filter_id] : [];
			$bCondOK = true;
			if(!is_array($arAllConditions))
				$arAllConditions = Array();
			foreach($arAllConditions as $k => $arCondition)
			{
				$bCondOK = false;
				$type = $arCondition["TYPE"];
				switch($type)
				{
				case "ALL":case "RECIPIENT":case "SENDER":
					if($type=="ALL")
						$arFields[$type] = $arFields["HEADER"]."\r\n".$arFields["BODY"];
					elseif($type=="RECIPIENT")
						$arFields[$type] = $arFields["FIELD_CC"]."\r\n".$arFields["FIELD_TO"]."\r\n".$arFields["FIELD_BCC"];
					else
						$arFields[$type] = $arFields["FIELD_FROM"]."\r\n".$arFields["FIELD_REPLY_TO"];
				case "HEADER": case "FIELD_FROM": case "FIELD_REPLY_TO": case "FIELD_TO": case "FIELD_CC": case "SUBJECT": case "BODY":
					$arStrings = explode("\n", $arCondition["STRINGS"]);
					if($arCondition["COMPARE_TYPE"]=="NOT_EQUAL" || $arCondition["COMPARE_TYPE"]=="NOT_CONTAIN")
					{
						$bCondOK = true;
						for($i = 0, $n = count($arStrings); $i < $n; $i++)
						{
							$str = mb_strtoupper(Trim($arStrings[$i], "\r"));
							switch($arCondition["COMPARE_TYPE"])
							{
							case "NOT_CONTAIN":
								if($str <> '' && mb_strpos(mb_strtoupper($arFields[$type]), $str) !== false)
									$bCondOK = false;
								break;
							case "NOT_EQUAL":
								if($str == mb_strtoupper($arFields[$type]))
									$bCondOK = false;
								break;
							}

							if(!$bCondOK)
								break;
						}
					}
					else
					{
						for($i = 0, $n = count($arStrings); $i < $n; $i++)
						{
							$str = mb_strtoupper(Trim($arStrings[$i], "\r"));
							switch($arCondition["COMPARE_TYPE"])
							{
							case "CONTAIN":
								if($str <> '' && mb_strpos(mb_strtoupper($arFields[$type]), $str) !== false)
									$bCondOK = true;
								break;
							case "EQUAL":
								if($str == mb_strtoupper($arFields[$type]))
									$bCondOK = true;
								break;
							case "REGEXP":
								if(preg_match("'".str_replace("'", "\'", $str)."'i", $arFields[$type]))
									$bCondOK = true;
								break;
							}

							if($bCondOK)
								break;
						}
					}
					break;

				case "ATTACHMENT":
					$db_att = CMailAttachment::GetList(Array(), Array("MESSAGE_ID"=>$arFields["ID"]));
					$arStrings = explode("\n", $arCondition["STRINGS"]);
					if($arCondition["COMPARE_TYPE"]=="NOT_EQUAL" || $arCondition["COMPARE_TYPE"]=="NOT_CONTAIN")
					{
						$bCondOK = true;
						while($arr_att = $db_att->Fetch())
						{
							for($i = 0, $n = count($arStrings); $i < $n; $i++)
							{
								$str = mb_strtoupper(Trim($arStrings[$i], "\r"));
								switch($arCondition["COMPARE_TYPE"])
								{
									case "NOT_CONTAIN":
										if($str <> '' && mb_strpos(mb_strtoupper($arr_att["FILE_NAME"]), $str) !== false)
											$bCondOK = false;
										break;
									case "NOT_EQUAL":
										if($str == mb_strtoupper($arr_att["FILE_NAME"]))
											$bCondOK = false;
										break;
								}
							}
							if(!$bCondOK)
								break;
						}
					}
					else
					{
						while($arr_att = $db_att->Fetch())
						{
							for($i = 0, $n = count($arStrings); $i < $n; $i++)
							{
								$str = mb_strtoupper(Trim($arStrings[$i], "\r"));
								switch($arCondition["COMPARE_TYPE"])
								{
								case "CONTAIN":
									if($str <> '' && mb_strpos(mb_strtoupper($arr_att["FILE_NAME"]), $str) !== false)
										$bCondOK = true;
									break;
								case "EQUAL":
									if($str == mb_strtoupper($arr_att["FILE_NAME"]))
										$bCondOK = true;
									break;
								case "REGEXP":
									if(preg_match("'".str_replace("'", "\'", $str)."'i", $arr_att["FILE_NAME"]))
										$bCondOK = true;
									break;
								}
							}
							if($bCondOK)
								break;
						}
					}
					break;
				} //switch

				if(!$bCondOK)
					break;
			} //foreach($arAllConditions as $k => $arCondition)

			if(!$bCondOK)
				continue;

			if($arFilterParams["SPAM_RATING"]>0)
			{
				$arFields["SPAM_RATING"] = CMailMessage::GetSpamRating($arFields["ID"], $arFields);
				if($arFilterParams["SPAM_RATING_TYPE"]==">" && $arFields["SPAM_RATING"]<=$arFilterParams["SPAM_RATING"])
					continue;
				if($arFilterParams["SPAM_RATING_TYPE"]!=">" && $arFields["SPAM_RATING"]>=$arFilterParams["SPAM_RATING"])
					continue;
			}

			if($arFilterParams["MESSAGE_SIZE"]>0)
			{
				$MESSAGE_SIZE = $arFields["MESSAGE_SIZE"];
				if($arFilterParams["MESSAGE_SIZE_UNIT"]=="k")
					$MESSAGE_SIZE = intval($MESSAGE_SIZE/1024);
				elseif($arFilterParams["MESSAGE_SIZE_UNIT"]=="m")
					$MESSAGE_SIZE = intval($MESSAGE_SIZE/1024/1024);

				if($arFilterParams["MESSAGE_SIZE_TYPE"]==">" && $MESSAGE_SIZE<=$arFilterParams["MESSAGE_SIZE"])
					continue;
				if($arFilterParams["MESSAGE_SIZE_TYPE"]!=">" && $MESSAGE_SIZE>=$arFilterParams["MESSAGE_SIZE"])
					continue;
			}

			if($arFilterParams["PHP_CONDITION"] <> '')
				if(!CMailFilter::DoPHPAction("php_cond_".$arFilterParams["ID"]."_", $arFilterParams["PHP_CONDITION"], $arFields))
					continue;

			$arModFilter = false;
			if($arFilterParams["ACTION_TYPE"]!="")
			{
				$res = CMailFilter::GetFilterList($arFilterParams["ACTION_TYPE"]);
				if($arModFilter = $res->Fetch())
				{
					if (
						(is_array($arModFilter["CONDITION_FUNC"]) && count($arModFilter["CONDITION_FUNC"]) > 0) ||
						$arModFilter["CONDITION_FUNC"] <> ''
					)
						if(!call_user_func_array($arModFilter["CONDITION_FUNC"], Array(&$arFields, &$arFilterParams["ACTION_VARS"])))
							continue;
				}
			}
			CMailLog::AddMessage(
				Array(
					"MAILBOX_ID"=>$MAILBOX_ID,
					"MESSAGE_ID"=>$MESSAGE_ID,
					"FILTER_ID"=>$filter_id,
					"STATUS_GOOD"=>"Y",
					"LOG_TYPE"=>"FILTER_OK",
					"MESSAGE"=>$event,
					)
				);

			if($arModFilter)
				if (
						(is_array($arModFilter["ACTION_FUNC"]) && count($arModFilter["ACTION_FUNC"]) > 0) ||
						$arModFilter["ACTION_FUNC"] <> ''
					)
					call_user_func_array($arModFilter["ACTION_FUNC"], array(&$arFields, &$arFilterParams["ACTION_VARS"]));


			if(Trim($arFilterParams["ACTION_PHP"]) <> '')
			{
				$res = CMailFilter::DoPHPAction("php_act_".$arFilterParams["ID"]."_", $arFilterParams["ACTION_PHP"], $arFields);
				CMailLog::AddMessage(
					Array(
						"MAILBOX_ID"=>$MAILBOX_ID,
						"MESSAGE_ID"=>$MESSAGE_ID,
						"FILTER_ID"=>$filter_id,
						"LOG_TYPE"=>"DO_PHP",
						"MESSAGE"=>""
						)
					);
			}

			if($arFilterParams["ACTION_SPAM"]=="Y" && $arFields["SPAM"]!="Y")
			{
				if($arFields["SPAM"]=="N")
					CMailFilter::DeleteFromSpamBase($arFields["FOR_SPAM_TEST"], false);
				CMailFilter::MarkAsSpam($arFields["FOR_SPAM_TEST"], true);
				CMailMessage::Update($MESSAGE_ID, Array("SPAM"=>"Y"));
				CMailLog::AddMessage(
					Array(
						"MAILBOX_ID"=>$MAILBOX_ID,
						"MESSAGE_ID"=>$MESSAGE_ID,
						"FILTER_ID"=>$filter_id,
						"LOG_TYPE"=>"SPAM",
						"MESSAGE"=>""
						)
					);
				$arFields["SPAM"] = "Y";
			}
			elseif($arFilterParams["ACTION_SPAM"]=="N" && $arFields["SPAM"]!="N")
			{
				if($arFields["SPAM"]=="Y")
					CMailFilter::DeleteFromSpamBase($arFields["FOR_SPAM_TEST"], true);
				CMailFilter::MarkAsSpam($arFields["FOR_SPAM_TEST"], false);
				CMailMessage::Update($MESSAGE_ID, Array("SPAM"=>"N"));
				CMailLog::AddMessage(
					Array(
						"MAILBOX_ID"=>$MAILBOX_ID,
						"MESSAGE_ID"=>$MESSAGE_ID,
						"FILTER_ID"=>$filter_id,
						"LOG_TYPE"=>"NOTSPAM",
						"MESSAGE"=>""
						)
					);
				$arFields["SPAM"] = "N";
			}

			if($arFilterParams["ACTION_READ"]=="Y" && $arFields["NEW_MESSAGE"]=="Y")
			{
				$arFields["NEW_MESSAGE"] = "N";
				CMailMessage::Update($MESSAGE_ID, Array("NEW_MESSAGE"=>"N"));
			}
			elseif($arFilterParams["ACTION_READ"]=="N" && $arFields["NEW_MESSAGE"]!="Y")
			{
				$arFields["NEW_MESSAGE"] = "Y";
				CMailMessage::Update($MESSAGE_ID, Array("NEW_MESSAGE"=>"Y"));
			}

			if($arFilterParams["ACTION_DELETE_MESSAGE"]=="Y")
			{
				CMailLog::AddMessage(
					Array(
						"MAILBOX_ID"=>$MAILBOX_ID,
						"MESSAGE_ID"=>$MESSAGE_ID,
						"FILTER_ID"=>$filter_id,
						"STATUS_GOOD"=>"Y",
						"LOG_TYPE"=>"MESSAGE_DELETED",
						"MESSAGE"=>""
						)
					);
				CMailMessage::Delete($MESSAGE_ID);
			}

			if($arFilterParams["ACTION_STOP_EXEC"]=="Y")
			{
				CMailLog::AddMessage(
					Array(
						"MAILBOX_ID"=>$MAILBOX_ID,
						"MESSAGE_ID"=>$MESSAGE_ID,
						"FILTER_ID"=>$filter_id,
						"STATUS_GOOD"=>"Y",
						"LOG_TYPE"=>"FILTER_STOP",
						"MESSAGE"=>""
						)
					);
				return true;
			}
		}

		return true;
	}


	public static function FilterMessage($message_id, $event, $FILTER_ID=false)
	{
		$res = CMailMessage::GetByID($message_id);
		if($arFields = $res->Fetch())
			return CMailFilter::Filter($arFields, $event, $FILTER_ID);

		return false;
	}

	public static function RecalcSpamRating()
	{
		global $DB;
		$res = $DB->Query("SELECT ID, HEADER, BODY_HTML, BODY FROM b_mail_message WHERE SPAM_LAST_RESULT<>'N'");
		while($arr = $res->Fetch())
		{
			$forSpamTest = sprintf('%s %s', $arr['HEADER'], $arr['BODY_HTML'] ?: $arr['BODY']);
			$arSpam = CMailFilter::GetSpamRating($forSpamTest);
			$DB->Query("UPDATE b_mail_message SET SPAM_RATING=".Round($arSpam["RATING"], 4).", SPAM_LAST_RESULT='Y', SPAM_WORDS='".$DB->ForSql($arSpam["WORDS"], 255)."' WHERE ID=".$arr["ID"]);
		}
	}

	public static function GetSpamRating($message)
	{
		global $DB;

		$arWords = CMailFilter::getWords($message, 1000);

		if (empty($arWords))
			return 0;

		// for every word find Si
		$arWords = array_map("md5", $arWords);

		global $BX_MAIL_SPAM_CNT;
		if(!is_set($BX_MAIL_SPAM_CNT, "G"))
		{
			$strSql = "SELECT MAX(GOOD_CNT) as G, MAX(BAD_CNT) as B FROM b_mail_spam_weight";
			if($res = $DB->Query($strSql))
				$BX_MAIL_SPAM_CNT = $res->Fetch();

			if(intval($BX_MAIL_SPAM_CNT["G"])<=0)
				$BX_MAIL_SPAM_CNT["G"] = 1;

			if(intval($BX_MAIL_SPAM_CNT["B"])<=0)
				$BX_MAIL_SPAM_CNT["B"] = 1;
		}

		$CNT_WORDS = COption::GetOptionInt("mail", "spam_word_count", B_MAIL_WORD_CNT);
		$MIN_COUNT =  COption::GetOptionInt("mail", "spam_min_count", B_MAIL_MIN_CNT);
		// select $CNT_WORDS words with max |Si - 0.5|
		// if the word placed less then xxx (5) times, then ignore
		$strSql =
			"SELECT SW.*, ".
			"	(BAD_CNT/".$BX_MAIL_SPAM_CNT["B"].".0) / (2*GOOD_CNT/".$BX_MAIL_SPAM_CNT["G"].".0 + BAD_CNT/".$BX_MAIL_SPAM_CNT["B"].".0) as RATING, ".
			"	ABS((BAD_CNT/".$BX_MAIL_SPAM_CNT["B"].".0) / (2*GOOD_CNT/".$BX_MAIL_SPAM_CNT["G"].".0 + BAD_CNT/".$BX_MAIL_SPAM_CNT["B"].".0) - 0.5) as MOD_RATING ".
			"FROM b_mail_spam_weight SW ".
			"WHERE WORD_ID IN ('".implode("', '", $arWords)."') ".
			"	AND ABS((BAD_CNT/".$BX_MAIL_SPAM_CNT["B"].".0) / (2*GOOD_CNT/".$BX_MAIL_SPAM_CNT["G"].".0 + BAD_CNT/".$BX_MAIL_SPAM_CNT["B"].".0) - 0.5) > 0.1 ".
			"	AND TOTAL_CNT>".$MIN_COUNT." ".
			"ORDER BY MOD_RATING DESC ".
			($DB->type == "MYSQL"?"LIMIT ".$CNT_WORDS : "");

		//echo htmlspecialcharsbx($strSql)."<br>";

		$a = 1;
		$b = 1;
		$dbr = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		$arr = true;
		$words = "";

		for($i=0; $i<$CNT_WORDS; $i++)
		{
			if($arr && $arr = $dbr->Fetch())
			{
				//echo "<font size='-3'>".htmlspecialcharsbx($arr["WORD_REAL"])."=".$arr["RATING"]."<br></font> ";
				$words .= $arr["WORD_REAL"]." ".Round($arr["RATING"]*100, 4)." ".$arr["BAD_CNT"]." ".$arr["GOOD_CNT"]."\n";
				$a = $a * ($arr["RATING"]==0?0.00001:$arr["RATING"]);
				$b = $b * (1 - ($arr["RATING"]==1?0.9999:$arr["RATING"]));
			}
			else
			{
				//if there is no word then weight Si = 0.4
				$a = $a * 0.4;
				$b = $b * (1 - 0.4);
			}
		}
		// calculate Bayes for the whole message
		$rating = $a/($a+$b) * 100;

		return Array("RATING"=>$rating, "WORDS"=>$words);
	}

	public static function getWords($message, $max_words)
	{
		static $tok = null;
		if (!isset($tok))
		{
			$tok = "}{~";
			for($i = ord("\x01"); $i < ord("\x23"); $i++)
				$tok .= chr($i);
			for($i = ord("\x25"); $i < ord("\x3F"); $i++)
				$tok .= chr($i);
			for($i = ord("\x5B"); $i < ord("\x5E"); $i++)
				$tok .= chr($i);
		}

		$arWords = array();
		$word = strtok($message, $tok);
		while($word !== false)
		{
			$arWords[$word] = $word;
			if (count($arWords) >= $max_words)
				break;
			$word = strtok($tok);
		}
		return $arWords;
	}

	public static function DoPHPAction($id, $action, &$arMessageFields)
	{
		return eval($action);
	}

	public static function DeleteFromSpamBase($message, $bIsSPAM = true)
	{
		CMailFilter::SpamAction($message, $bIsSPAM, true);
	}

	public static function MarkAsSpam($message, $bIsSPAM = true)
	{
		CMailFilter::SpamAction($message, $bIsSPAM);
	}

	public static function SpamAction($message, $bIsSPAM, $bDelete = false)
	{
		global $DB;
		global $BX_MAIL_SPAM_CNT;

		if(!is_set($BX_MAIL_SPAM_CNT, "G"))
		{
			$strSql = "SELECT MAX(GOOD_CNT) as G, MAX(BAD_CNT) as B FROM b_mail_spam_weight";
			if($res = $DB->Query($strSql))
				$BX_MAIL_SPAM_CNT = $res->Fetch();

			if(intval($BX_MAIL_SPAM_CNT["G"])<=0)
				$BX_MAIL_SPAM_CNT["G"] = 1;

			if(intval($BX_MAIL_SPAM_CNT["B"])<=0)
				$BX_MAIL_SPAM_CNT["B"] = 1;
		}

		if($bDelete && $bIsSPAM)
			$BX_MAIL_SPAM_CNT["B"]--;
		elseif($bDelete && !$bIsSPAM)
			$BX_MAIL_SPAM_CNT["G"]--;
		elseif(!$bDelete && $bIsSPAM)
			$BX_MAIL_SPAM_CNT["B"]++;
		elseif(!$bDelete && !$bIsSPAM)
			$BX_MAIL_SPAM_CNT["G"]++;

		@set_time_limit(30);

		// split to words
		$arWords = CMailFilter::getWords($message, 1000);

		// for every word find Si
		$strWords = "''";
		foreach($arWords as $word)
		{
			$word_md5 = md5($word);

			// change weight
			$strSql =
				"INSERT INTO b_mail_spam_weight(WORD_ID, WORD_REAL, GOOD_CNT, BAD_CNT, TOTAL_CNT) ".
				"VALUES('".$word_md5."', '".$DB->ForSql($word, 40)."', ".($bIsSPAM?0:1).", ".($bIsSPAM?1:0).", 1)";

			if($bDelete || (!$DB->Query($strSql, true)))
			{
				if($bDelete)
				{
					$strSql =
						"UPDATE b_mail_spam_weight SET ".
						"	GOOD_CNT = GOOD_CNT - ".($bIsSPAM?0:1).", ".
						"	BAD_CNT = BAD_CNT - ".($bIsSPAM?1:0).", ".
						"	TOTAL_CNT = TOTAL_CNT - 1 ".
						"WHERE WORD_ID = '".$word_md5."' ".
						"	AND ".($bIsSPAM?"BAD_CNT>0":"GOOD_CNT>0");// AND WORD_REAL = '".$DB->ForSql($word, 40)."'";
				}
				else
				{
					$strSql =
						"UPDATE b_mail_spam_weight SET ".
						"	GOOD_CNT = GOOD_CNT + ".($bIsSPAM?0:1).", ".
						"	BAD_CNT = BAD_CNT + ".($bIsSPAM?1:0).", ".
						"	TOTAL_CNT = TOTAL_CNT + 1 ".
						"WHERE WORD_ID='".$word_md5."'";// AND WORD_REAL = '".$DB->ForSql($word, 40)."'";
				}

				$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
			}
		}


		if(COption::GetOptionString("mail", "reset_all_spam_result", "N") == "Y")
			$DB->Query("UPDATE b_mail_message SET SPAM_LAST_RESULT='N'");
	}


	public static function GetFilterList($id = "")
	{
		static $BX_MAIL_CUST_FILTER_LIST = false;
		if($BX_MAIL_CUST_FILTER_LIST === false)
		{
			$BX_MAIL_CUST_FILTER_LIST = array();
			foreach(GetModuleEvents("mail", "OnGetFilterList", true) as $arEvent)
			{
				$arResult = ExecuteModuleEventEx($arEvent);
				if(is_array($arResult))
					$BX_MAIL_CUST_FILTER_LIST[] = $arResult;
			}
		}

		if($id != "")
		{
			$allResultsTemp = array();
			foreach($BX_MAIL_CUST_FILTER_LIST as $arResult)
			{
				if($arResult["ID"] == $id)
				{
					$allResultsTemp[] = $arResult;
					break;
				}
			}
		}
		else
		{
			$allResultsTemp = $BX_MAIL_CUST_FILTER_LIST;
		}

		$db_res = new CDBResult;
		$db_res->InitFromArray($allResultsTemp);
		return $db_res;
	}
}

class CMailFilterCondition
{
	public static function GetList($arOrder=Array(), $arFilter=Array())
	{
		global $DB;
		$strSql =
				"SELECT MFC.* ".
				"FROM b_mail_filter_cond MFC ";

		if(!is_array($arFilter))
			$arFilter = Array();
		$arSqlSearch = Array();
		$filter_keys = array_keys($arFilter);
		for($i = 0, $n = count($filter_keys); $i < $n; $i++)
		{
			$val = $arFilter[$filter_keys[$i]];
			if ($val == '') continue;
			$key = mb_strtoupper($filter_keys[$i]);
			switch($key)
			{
			case "TYPE":
			case "STRINGS":
			case "COMPARE_TYPE":
				$arSqlSearch[] = GetFilterQuery("MFC.".$key, $val);
				break;
			case "ID":
			case "FILTER_ID":
				$arSqlSearch[] = GetFilterQuery("MFC.".$key, $val, "N");
				break;
			}
		}

		$strSqlSearch = "";
		for($i = 0, $n = count($arSqlSearch); $i < $n; $i++)
		{
			if($arSqlSearch[$i] <> '')
				$strSqlSearch .= " AND  (".$arSqlSearch[$i].") ";
		}

		$arSqlOrder = Array();
		foreach($arOrder as $by=>$order)
		{
			$order = mb_strtolower($order);
			if ($order!="asc")
				$order = "desc".($DB->type == "ORACLE"?" NULLS LAST":"");
			else
				$order = "asc".($DB->type == "ORACLE"?" NULLS FIRST":"");

			switch(mb_strtoupper($by))
			{
				case "FILTER_ID":
				case "TYPE":
				case "STRINGS":
				case "COMPARE_TYPE":
					$arSqlOrder[] = " MFC.".$by." ".$order." ";
					break;
				default:
					$arSqlOrder[] = " MFC.ID ".$order." ";
			}
		}

		$strSqlOrder = "";
		$arSqlOrder = array_unique($arSqlOrder);
		DelDuplicateSort($arSqlOrder);

		for ($i = 0, $n = count($arSqlOrder); $i < $n; $i++)
		{
			if($i==0)
				$strSqlOrder = " ORDER BY ";
			else
				$strSqlOrder .= ",";

			$strSqlOrder .= $arSqlOrder[$i];
		}

		$strSql .= " WHERE 1=1 ".$strSqlSearch.$strSqlOrder;

		$res = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		$res->is_filtered = (count($arSqlOrder)>0);
		return $res;
	}

	function GetByID($ID)
	{
		global $DB;
		return CMailFilterCondition::GetList(Array(), Array("ID"=>$ID));
	}

	public static function Delete($ID)
	{
		global $DB;
		$ID = intval($ID);
		$strSql = "DELETE FROM b_mail_filter_cond WHERE ID=".$ID;
		return $DB->Query($strSql, true);
	}

	public static function SetConditions($FILTER_ID, $CONDITIONS, $bClearOther = true)
	{
		global $DB;

		$FILTER_ID = intval($FILTER_ID);

		$strSql=
			"SELECT ID ".
			"FROM b_mail_filter_cond ".
			"WHERE FILTER_ID=".$FILTER_ID;

		$dbr = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);

		while($dbr_arr = $dbr->Fetch())
		{
			if(is_set($CONDITIONS, $dbr_arr["ID"]) && is_array($CONDITIONS[$dbr_arr["ID"]]) && $CONDITIONS[$dbr_arr["ID"]]["STRINGS"] <> '')
			{
				$arFields = $CONDITIONS[$dbr_arr["ID"]];
				unset($arFields["ID"]);
				$arFields["FILTER_ID"] = $FILTER_ID;
				CMailFilterCondition::Update($dbr_arr["ID"], $arFields);
				unset($CONDITIONS[$dbr_arr["ID"]]);
			}
			elseif($bClearOther)
			{
				$DB->Query("DELETE FROM b_mail_filter_cond WHERE ID=".$dbr_arr["ID"]);
			}
		}

		foreach($CONDITIONS as $arFields)
		{
			if(is_array($arFields) && $arFields["STRINGS"] <> '')
			{
				$arFields["FILTER_ID"] = $FILTER_ID;
				unset($arFields["ID"]);
				CMailFilterCondition::Add($arFields);
			}
		}
	}

	public static function Add($arFields)
	{
		if(!CMailFilter::CheckConditionTypes($arFields))
		{
			return false;
		}

		global $DB;

		if(is_set($arFields, "COMPARE_TYPE") && $arFields["COMPARE_TYPE"]!="EQUAL" && $arFields["COMPARE_TYPE"]!="NOT_EQUAL" && $arFields["COMPARE_TYPE"]!="NOT_CONTAIN" && $arFields["COMPARE_TYPE"]!="REGEXP")
			$arFields["COMPARE_TYPE"]="CONTAIN";

		$ID = $DB->Add("b_mail_filter_cond", $arFields);
		return $ID;
	}


	public static function Update($ID, $arFields)
	{
		if(!CMailFilter::CheckConditionTypes($arFields))
		{
			return false;
		}

		global $DB;
		$ID = intval($ID);

		if(is_set($arFields, "COMPARE_TYPE") && $arFields["COMPARE_TYPE"]!="EQUAL" && $arFields["COMPARE_TYPE"]!="NOT_EQUAL" && $arFields["COMPARE_TYPE"]!="NOT_CONTAIN" && $arFields["COMPARE_TYPE"]!="REGEXP")
			$arFields["COMPARE_TYPE"]="CONTAIN";


		$strUpdate = $DB->PrepareUpdate("b_mail_filter_cond", $arFields);

		$strSql =
			"UPDATE b_mail_filter_cond SET ".
				$strUpdate." ".
			"WHERE ID=".$ID;

		$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);

		return true;
	}
}


class CMailLog
{
	public static function AddMessage($arFields)
	{
		global $DB;

		if (COption::getOptionString('mail', 'disable_log', 'N') == 'Y')
			return;

		$arFields["~DATE_INSERT"] = $DB->GetNowFunction();
		if(array_key_exists('MESSAGE', $arFields))
			$arFields['MESSAGE'] = strval(mb_substr($arFields['MESSAGE'], 0, 255));
		else
			$arFields['MESSAGE'] = '';

		return $DB->Add("b_mail_log", $arFields);
	}

	public static function Delete($ID)
	{
		global $DB;
		$ID = intval($ID);
		$strSql = "DELETE FROM b_mail_log WHERE ID=".$ID;
		return $DB->Query($strSql, true);
	}

	public static function GetList($arOrder=Array(), $arFilter=Array())
	{
		global $DB;
		$strSql =
				"SELECT ML.*, MB.NAME as MAILBOX_NAME, ".
				"	MF.NAME as FILTER_NAME, ".
				"	MM.SUBJECT as MESSAGE_SUBJECT, ".
				"	".$DB->DateToCharFunction("ML.DATE_INSERT")."	as DATE_INSERT ".
				"	".
				"FROM b_mail_log ML ".
				"	INNER JOIN b_mail_mailbox MB ON MB.ID=ML.MAILBOX_ID ".
				"	LEFT JOIN b_mail_filter MF ON MF.ID=ML.FILTER_ID ".
				"	LEFT JOIN b_mail_message MM ON MM.ID=ML.MESSAGE_ID ";

		if(!is_array($arFilter))
			$arFilter = Array();
		$arSqlSearch = Array();
		$filter_keys = array_keys($arFilter);
		for($i = 0, $n = count($filter_keys); $i < $n; $i++)
		{
			$val = $arFilter[$filter_keys[$i]];
			if ($val == '') continue;
			$key = mb_strtoupper($filter_keys[$i]);
			switch($key)
			{
			case "ID":
			case "MAILBOX_ID":
			case "FILTER_ID":
			case "MESSAGE_ID":
			case "LOG_TYPE":
			case "STATUS_GOOD":
				$arSqlSearch[] = GetFilterQuery("ML.".$key, $val, "N");
				break;
			case "MESSAGE":
				$arSqlSearch[] = GetFilterQuery("ML.".$key, $val);
				break;
			case "FILTER_NAME":
				$arSqlSearch[] = GetFilterQuery("MF.NAME", $val);
				break;
			case "MAILBOX_NAME":
				$arSqlSearch[] = GetFilterQuery("MB.NAME", $val);
				break;
			case "MESSAGE_SUBJECT":
				$arSqlSearch[] = GetFilterQuery("MM.SUBJECT", $val);
				break;
			}
		}

		$is_filtered = false;
		$strSqlSearch = "";
		for($i = 0, $n = count($arSqlSearch); $i < $n; $i++)
		{
			if($arSqlSearch[$i] <> '')
			{
				$strSqlSearch .= " AND  (".$arSqlSearch[$i].") ";
				$is_filtered = true;
			}
		}

		$arSqlOrder = Array();
		foreach($arOrder as $by=>$order)
		{
			$order = mb_strtolower($order);
			if ($order!="asc")
				$order = "desc".($DB->type == "ORACLE"?" NULLS LAST":"");
			else
				$order = "asc".($DB->type == "ORACLE"?" NULLS FIRST":"");

			switch(mb_strtoupper($by))
			{
				case "ID":
				case "MAILBOX_ID":
				case "FILTER_ID":
				case "MESSAGE_ID":
				case "DATE_INSERT":
				case "LOG_TYPE":
				case "STATUS_GOOD":
				case "MESSAGE":
					$arSqlOrder[] = " ML.".$by." ".$order." ";
				case "MESSAGE_SUBJECT":
					$arSqlOrder[] = " MM.SUBJECT ".$order." ";
				case "FILTER_NAME":
					$arSqlOrder[] = " MF.NAME ".$order." ";
				case "MAILBOX_NAME":
					$arSqlOrder[] = " MB.NAME ".$order." ";
				default:
					$arSqlOrder[] = " ML.ID ".$order." ";
			}
		}

		$strSqlOrder = "";
		$arSqlOrder = array_unique($arSqlOrder);
		DelDuplicateSort($arSqlOrder);

		for ($i = 0, $n = count($arSqlOrder); $i < $n; $i++)
		{
			if($i==0)
				$strSqlOrder = " ORDER BY ";
			else
				$strSqlOrder .= ",";

			$strSqlOrder .= $arSqlOrder[$i];
		}

		$strSql .= " WHERE 1=1 ".$strSqlSearch.$strSqlOrder;

		$res = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		$res = new _CMailLogDBRes($res);
		$res->is_filtered = $is_filtered;
		return $res;
	}

	public static function ConvertRow($arr_log)
	{
		switch($arr_log["LOG_TYPE"])
		{
		case "FILTER_OK":
			$arr_log["MESSAGE_TEXT"] = GetMessage("MAIL_CL_RULE_RUN")." \"[".$arr_log["FILTER_ID"]."] ".mb_substr($arr_log["FILTER_NAME"], 0, 30).(mb_strlen($arr_log["FILTER_NAME"]) > 30?"...":"")."\" ";
			if($arr_log["MESSAGE"]=="R")
				$arr_log["MESSAGE_TEXT"] .= GetMessage("MAIL_CL_WHEN_CONNECT");
			else
				$arr_log["MESSAGE_TEXT"] .= GetMessage("MAIL_CL_WHEN_MANUAL");
			break;
		case "NEW_MESSAGE":
			$arr_log["MESSAGE_TEXT"] = GetMessage("MAIL_CL_NEW_MESSAGE")." ".$arr_log["MESSAGE"];
			break;
		case "SPAM":
			if($arr_log["FILTER_ID"]>0)
				$arr_log["MESSAGE_TEXT"] = "&nbsp;&nbsp;".GetMessage("MAIL_CL_RULE_ACT_SPAM");
			else
				$arr_log["MESSAGE_TEXT"] = GetMessage("MAIL_CL_ACT_SPAM");
			break;
		case "NOTSPAM":
			if($arr_log["FILTER_ID"]>0)
				$arr_log["MESSAGE_TEXT"] = "&nbsp;&nbsp;".GetMessage("MAIL_CL_RULE_ACT_NOTSPAM");
			else
				$arr_log["MESSAGE_TEXT"] = GetMessage("MAIL_CL_ACT_NOTSPAM");
			break;
		case "DO_PHP":
			$arr_log["MESSAGE_TEXT"] = "&nbsp;&nbsp;".GetMessage("MAIL_CL_RULE_ACT_PHP");
			break;
		case "MESSAGE_DELETED":
			$arr_log["MESSAGE_TEXT"] = "&nbsp;&nbsp;".GetMessage("MAIL_CL_RULE_ACT_DEL");
			break;
		case "FILTER_STOP":
			$arr_log["MESSAGE_TEXT"] = "&nbsp;&nbsp;".GetMessage("MAIL_CL_RULE_ACT_CANC");
			break;
		default:
			$arr_log["MESSAGE_TEXT"] = $arr_log["MESSAGE"];
		}
		return $arr_log;
	}
}

class _CMailLogDBRes  extends CDBResult
{
	function __construct($res)
	{
		parent::__construct($res);
	}

	function Fetch()
	{
		if($arr_log = parent::Fetch())
			return CMailLog::ConvertRow($arr_log);

		return false;
	}
}
