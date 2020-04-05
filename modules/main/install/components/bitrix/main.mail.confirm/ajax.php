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
		$code   = strtolower(trim($_REQUEST['code']));
		$public = $_REQUEST['public'] == 'Y';

		if (!check_email($email, true))
			$error = getMessage(empty($email) ? 'MAIN_MAIL_CONFIRM_EMPTY_EMAIL' : 'MAIN_MAIL_CONFIRM_INVALID_EMAIL');

		if ($error === false)
		{
			$pending = \CUserOptions::getOption('mail', 'pending_from_emails', null);
			if (!is_array($pending))
				$pending = array();

			foreach ($pending as $key => $item)
			{
				if (time()-$item['time'] > 60*60*24*7)
					unset($pending[$key]);
			}

			\CUserOptions::setOption('mail', 'pending_from_emails', $pending);

			$key = hash('crc32b', strtolower($name).$email);

			if (empty($code))
			{
				$pending[$key] = array(
					'name'   => $name,
					'email'  => $email,
					'public' => $public,
					'code'   => \Bitrix\Main\Security\Random::getStringByCharsets(5, '0123456789ABCDEFGHJKLMNPQRSTUVWXYZ'),
					'time'   => time(),
				);
				\CUserOptions::setOption('mail', 'pending_from_emails', $pending);

				$sendResult = \CEvent::sendImmediate(
					'MAIN_MAIL_CONFIRM_CODE',
					SITE_ID,
					array(
						'EMAIL_TO'        => $email,
						'MESSAGE_SUBJECT' => getMessage('MAIN_MAIL_CONFIRM_MESSAGE_SUBJECT'),
						'CONFIRM_CODE'    => $pending[$key]['code'],
					)
				);
			}
			else
			{
				if (empty($pending[$key]) || strtolower($pending[$key]['code']) != $code)
					$error = getMessage('MAIN_MAIL_CONFIRM_INVALID_CODE');

				if ($error === false)
				{
					$entry = \CUserOptions::getList(false, array(
						'USER_ID'  => $public ? 0 : $USER->getId(),
						'CATEGORY' => 'mail',
						'NAME'     => 'confirmed_from_emails',
						'COMMON'   => $public ? 'Y' : 'N',
					))->fetch();
					if (!empty($entry['VALUE']))
						$confirmed = unserialize($entry['VALUE']);

					if (empty($confirmed) || !is_array($confirmed))
						$confirmed = array();

					$confirmed[$key] = array(
						'name'  => $name,
						'email' => $email,
					);
					\CUserOptions::setOption('mail', 'confirmed_from_emails', $confirmed, $public);

					unset($pending[$key]);
					\CUserOptions::setOption('mail', 'pending_from_emails', $pending);

					return array();
				}
			}
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
