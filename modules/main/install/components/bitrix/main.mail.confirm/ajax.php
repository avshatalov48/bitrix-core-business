<?php

use Bitrix\Main\Localization\Loc;

define('PUBLIC_AJAX_MODE', true);
define('NOT_CHECK_PERMISSIONS', true);

require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php');

Loc::loadMessages(__DIR__.'/class.php');

class MainMailConfirmAjax
{

	public static function execute()
	{
		global $USER;

		$result = array();
		$error  = false;

		if (!is_object($USER) || !$USER->isAuthorized())
			$error = getMessage('MAIN_MAIL_CONFIRM_AUTH');

		\CUtil::jsPostUnescape();

		if ($error === false)
		{
			$act = isset($_REQUEST['act']) ? $_REQUEST['act'] : null;

			switch ($act)
			{
				case 'add':
					$result = (array) self::executeAdd($error);
					break;
				default:
					$error = getMessage('MAIN_MAIL_CONFIRM_AJAX_ERROR');
			}
		}

		self::returnJson(array_merge(array(
			'result' => $error === false ? 'ok' : 'error',
			'error'  => $error
		), $result));
	}

	private static function executeAdd(&$error)
	{
		global $USER;

		$error = false;

		$name   = trim($_REQUEST['name']);
		$email  = strtolower(trim($_REQUEST['email']));
		$smtp   = $_REQUEST['smtp'];
		$code   = strtolower(trim($_REQUEST['code']));
		$public = $_REQUEST['public'] == 'Y';

		if (!check_email($email, true))
		{
			$error = getMessage(empty($email) ? 'MAIN_MAIL_CONFIRM_EMPTY_EMAIL' : 'MAIN_MAIL_CONFIRM_INVALID_EMAIL');
			return;
		}

		if (!empty($smtp))
		{
			if (!is_array($smtp))
			{
				$error = getMessage('MAIN_MAIL_CONFIRM_AJAX_ERROR');
				return;
			}

			$smtp = array(
				'server'   => strtolower(trim($smtp['server'])),
				'port'     => strtolower(trim($smtp['port'])),
				'login'    => $smtp['login'],
				'password' => $smtp['password'],
			);

			if (!preg_match('/^([a-z0-9-]+\.)+[a-z0-9-]{2,20}$/i', $smtp['server']))
			{
				$error = getMessage(
					empty($smtp['server'])
						? 'MAIN_MAIL_CONFIRM_EMPTY_SMTP_SERVER'
						: 'MAIN_MAIL_CONFIRM_INVALID_SMTP_SERVER'
				);
				return;
			}

			if (!preg_match('/^[0-9]+$/i', $smtp['port']) || $smtp['port'] < 1 || $smtp['port'] > 65535)
			{
				$error = getMessage(
					empty($smtp['port'])
						? 'MAIN_MAIL_CONFIRM_EMPTY_SMTP_PORT'
						: 'MAIN_MAIL_CONFIRM_INVALID_SMTP_PORT'
				);
				return;
			}

			if (empty($smtp['login']))
			{
				$error = getMessage('MAIN_MAIL_CONFIRM_EMPTY_SMTP_LOGIN');
				return;
			}

			if (empty($smtp['password']))
			{
				$error = getMessage('MAIN_MAIL_CONFIRM_EMPTY_SMTP_PASSWORD');
				return;
			}
		}

		$pending = array();
		$expires = array();

		$res = \Bitrix\Main\Mail\Internal\SenderTable::getList(array(
			'filter' => array(
				'=USER_ID' => $USER->getId(),
				array(
					'LOGIC' => 'OR',
					'IS_CONFIRMED' => false,
					'EMAIL'        => $email,
				),
			),
		));
		while ($item = $res->fetch())
		{
			if ($item['IS_CONFIRMED'])
			{
				if ($item['EMAIL'] == $email)
				{
					$alreadyConfirmed = true;
				}
			}
			else
			{
				if (time() - $item['OPTIONS']['confirm_time'] > 60*60*24*7)
				{
					$expires[] = $item['ID'];
				}
				else
				{
					if (!array_key_exists($item['EMAIL'], $pending))
					{
						$pending[$item['EMAIL']] = array();
					}

					$pending[$item['EMAIL']][$item['ID']] = strtolower($item['OPTIONS']['confirm_code']);
				}
			}
		}

		\Bitrix\Main\Mail\Sender::delete($expires);

		if (!empty($smtp))
		{
			$fields = array(
				'NAME'         => $name,
				'EMAIL'        => $email,
				'USER_ID'      => $USER->getId(),
				'IS_CONFIRMED' => true,
				'IS_PUBLIC'    => $public,
				'OPTIONS'      => array(
					'smtp' => $smtp,
				),
			);
			\Bitrix\Main\Mail\Internal\SenderTable::add($fields);
		}
		else if (empty($code))
		{
			$fields = array(
				'NAME'         => $name,
				'EMAIL'        => $email,
				'USER_ID'      => $USER->getId(),
				'IS_CONFIRMED' => false,
				'IS_PUBLIC'    => $public,
				'OPTIONS'      => array(
					'confirm_code' => \Bitrix\Main\Security\Random::getStringByCharsets(5, '0123456789abcdefghjklmnpqrstuvwxyz'),
					'confirm_time' => time(),
				),
			);
			\Bitrix\Main\Mail\Internal\SenderTable::add($fields);

			$sendResult = \CEvent::sendImmediate(
				'MAIN_MAIL_CONFIRM_CODE',
				SITE_ID,
				array(
					'EMAIL_TO'        => $email,
					'MESSAGE_SUBJECT' => getMessage('MAIN_MAIL_CONFIRM_MESSAGE_SUBJECT'),
					'CONFIRM_CODE'    => strtoupper($fields['OPTIONS']['confirm_code']),
				)
			);
		}
		else
		{
			if (!in_array($code, $pending[$email]))
			{
				$error = getMessage('MAIN_MAIL_CONFIRM_INVALID_CODE');
				return;
			}

			\Bitrix\Main\Mail\Sender::confirm(array_keys($pending[$email]));

			return array();
		}
	}

	private static function returnJson($data)
	{
		global $APPLICATION;

		$APPLICATION->restartBuffer();

		header('Content-Type: application/x-javascript; charset=UTF-8');
		echo \Bitrix\Main\Web\Json::encode($data);
	}

}

MainMailConfirmAjax::execute();

require $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/epilog_after.php';
