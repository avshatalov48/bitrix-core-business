<?php

/*
##############################################
# Bitrix Site Manager                        #
# Copyright (c) 2002 - 2007 Bitrix           #
# http://www.bitrixsoft.com                  #
# mailto:admin@bitrixsoft.com                #
##############################################
*/

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/mail/classes/general/mail.php");

class CMailbox extends CAllMailBox
{
	public static function CleanUp()
	{
		$connection = \Bitrix\Main\Application::getConnection();
		$helper = $connection->getSqlHelper();
		$days = COption::GetOptionInt("mail", "time_keep_log", B_MAIL_KEEP_LOG);

		$strSql = "DELETE FROM b_mail_log WHERE DATE_INSERT < " . $helper->addDaysToDateTime(-intval($days));
		$connection->query($strSql);

		$mt = microtime(true);
		$dbr = $connection->query("
			SELECT MS.ID
			FROM
				b_mail_message MS
				INNER JOIN b_mail_mailbox MB ON MS.MAILBOX_ID = MB.ID
			WHERE
				MB.MAX_KEEP_DAYS > 0
				AND MS.DATE_INSERT < ".$helper->addDaysToDateTime('-MB.MAX_KEEP_DAYS')."
		");
		while ($ar = $dbr->fetch())
		{
			CMailMessage::Delete($ar["ID"]);
			if (microtime(true) - $mt > 10 * 1000)
				break;
		}

		return "CMailbox::CleanUp();";
	}
}

class CMailUtil extends CAllMailUtil
{
	public static function IsSizeAllowed($size)
	{
		global $B_MAIL_MAX_ALLOWED;

		$dbConnection = \Bitrix\Main\Application::getConnection();

		$B_MAIL_MAX_ALLOWED = $dbConnection->getMaxAllowedPacket();

		return $B_MAIL_MAX_ALLOWED > $size;
	}
}

class CMailMessage extends CAllMailMessage
{
}
