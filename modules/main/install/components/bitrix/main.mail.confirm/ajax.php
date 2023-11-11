<?php

use Bitrix\Main;
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
			$act = $_REQUEST['act'] ?? null;

			switch ($act)
			{
				case 'info':
					$result = (array) self::getInfo($error);
					break;
				case 'add':
					$result = (array) self::executeAdd($error);
					break;
				case 'edit':
					$result = (array) self::executeAdd($error, true);
					break;
				case 'delete':
				case 'deleteSender':
					$result = (array) self::executeDelete($error);
					break;
				case 'sendersListCanDel':
					$result = (array) self::executeSenderListCanDel($error);
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

	private static function getInfo(&$error): array
	{
		global $USER;

		$error = false;
		$sender = Main\Mail\Internal\SenderTable::getById((int) $_REQUEST['senderId'])->fetch();

		$response = [];
		if (!$sender)
		{
			return $response;
		}

		if ($smtp = $sender['OPTIONS']['smtp'])
		{
			$response['smtp'] = [
				'server' => $smtp['server'],
				'port' => $smtp['port'],
				'protocol' => $smtp['protocol'],
				'login' => $smtp['login'],
				'limit' => $smtp['limit'],
				'isOauth' => $smtp['isOauth'] ?? false,
			];
		}

		$response = array_merge($response['smtp']??[],
			[
				'email' => $sender['EMAIL'],
				'name' => $sender['NAME'],
				'isPublic' => $sender['IS_PUBLIC'],
			])
		;

		return $response;
	}

	private static function executeAdd(&$error, $update = false)
	{
		global $USER;

		$error = false;

		$isAdmin = Main\Loader::includeModule('bitrix24') ? \CBitrix24::isPortalAdmin($USER->getId()) : $USER->isAdmin();

		$name   = trim($_REQUEST['name']);
		$email = mb_strtolower(trim($_REQUEST['email']));
		$smtp   = $_REQUEST['smtp'];
		$code = mb_strtolower(trim($_REQUEST['code']));
		$public = $isAdmin && $_REQUEST['public'] == 'Y';
		$limit = $_REQUEST['smtp']['limit'] ?? null;
		if ($limit !== null)
		{
			$limit = (int)$limit;
			$limit = $limit < 0 ? 0 : $limit;
		}

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
				'server'   => mb_strtolower(trim($smtp['server'])),
				'port'     => mb_strtolower(trim($smtp['port'])),
				'protocol' => 'Y' == $smtp['ssl'] ? 'smtps' : 'smtp',
				'login'    => $smtp['login'],
				'password' => $smtp['password'],
			);

			if ($limit !== null)
			{
				$smtp['limit'] = $limit;
			}

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

			if (empty($smtp['password']) && !$update)
			{
				$error = getMessage('MAIN_MAIL_CONFIRM_EMPTY_SMTP_PASSWORD');
				return;
			}
			else if (preg_match('/^\^/', $smtp['password']) && !$update)
			{
				$error = getMessage('MAIN_MAIL_CONFIRM_INVALID_SMTP_PASSWORD_CARET');
				return;
			}
			else if (preg_match('/\x00/', $smtp['password']) && !$update)
			{
				$error = getMessage('MAIN_MAIL_CONFIRM_INVALID_SMTP_PASSWORD_NULL');
				return;
			}
		}

		$pending = array();
		$expires = array();
		$toDelete = array();

		$queryParams = array(
			'filter' => array(
				'=USER_ID' => $USER->getId(),
				array(
					'LOGIC' => 'OR',
					'=IS_CONFIRMED' => false,
					'=EMAIL'        => $email,
				),
			)
		);

		$senderId = $_REQUEST['id'];
		if ($senderId && is_numeric($senderId))
		{
			$queryParams = [
				'filter' => [
					'=ID' => (int)$senderId,
				]
			];
		}


		$res = Main\Mail\Internal\SenderTable::getList($queryParams);

		while ($item = $res->fetch())
		{
			if ($item['IS_CONFIRMED'])
			{
				if ($item['EMAIL'] == $email)
				{
					$alreadyConfirmed = true;
					if (!empty($item['OPTIONS']['smtp']['isOauth']) && $update) {
						$smtp = $item['OPTIONS']['smtp'];
						if ($limit) {
							$smtp['limit'] = $limit;
						} else {
							unset($smtp['limit']);
						}
					}
				}
			}
			else
			{
				if (
					($senderId === $item['ID'] && $item['EMAIL'] != $email) ||
					(
						empty($code)
						&& $item['EMAIL'] == $email
						&& $item['USER_ID'] == $USER->getId()
					)
				)
				{
					if ($senderId === $item['ID'])
					{
						$senderId = null;
					}

					$toDelete[] = $item['ID'];
				}

				if (time() - $item['OPTIONS']['confirm_time'] > 60*60*24)
				{
					$expires[] = $item['ID'];
				}
				else
				{
					if (!array_key_exists($item['EMAIL'], $pending))
					{
						$pending[$item['EMAIL']] = array();
					}

					$pending[$item['EMAIL']][$item['ID']] = mb_strtolower($item['OPTIONS']['confirm_code']);
				}
			}
		}

		Main\Mail\Sender::delete(array_merge($toDelete, $expires));

		if ($smtp && $smtp['limit'] !== null)
		{
			Main\Mail\Sender::setEmailLimit($email, $smtp['limit']);
		}
		elseif ($smtp && !isset($smtp['limit']))
		{
			Main\Mail\Sender::removeEmailLimit($email);
		}

		$fields = array(
			'NAME' => $name,
			'EMAIL' => $email,
			'USER_ID' => $USER->getId(),
			'IS_CONFIRMED' => false,
			'IS_PUBLIC' => $public,
			'OPTIONS' => array(
				'source' => 'main.mail.confirm',
			),
		);

		if (!empty($smtp))
		{
			$fields['OPTIONS']['smtp'] = $smtp;
		}

		if ($senderId && $smtp && !empty($smtp['password']))
		{
			Main\Mail\Sender::checkEmail($fields, $checkError);

			if ($checkError)
			{
				$error = $checkError;
				return;
			}

			if (!$checkError)
			{
				$fields['IS_CONFIRMED'] = true;
			}

			Main\Mail\Internal\SenderTable::update($senderId, $fields);

			return ['senderId' => $senderId, 'confirmed' => $fields['IS_CONFIRMED']];
		}
		elseif (empty($code))
		{
			$result = Main\Mail\Sender::add($fields);
			if (!empty($result['error']))
			{
				$error = $result['error'];
				return;
			}

			return $result;
		}
		else
		{
			if (!in_array($code, $pending[$email]))
			{
				$error = getMessage('MAIN_MAIL_CONFIRM_INVALID_CODE');
				return;
			}

			Main\Mail\Sender::confirm(array_keys($pending[$email], $code));

			return array();
		}
	}

	private static function executeDelete(&$error)
	{
		global $USER;

		$error = false;

		$isAdmin = Main\Loader::includeModule('bitrix24') ? \CBitrix24::isPortalAdmin($USER->getId()) : $USER->isAdmin();

		$senderId = Main\Application::getInstance()->getContext()->getRequest()->getPost('senderId');

		$item = Main\Mail\Internal\SenderTable::getList(array(
			'filter' => array(
				'=ID' => $senderId,
			),
		))->fetch();

		if (empty($item))
		{
			$error = getMessage('MAIN_MAIL_CONFIRM_AJAX_ERROR');
			return;
		}

		if ($USER->getId() != $item['USER_ID'] && !($item['IS_PUBLIC'] && $isAdmin))
		{
			$error = getMessage('MAIN_MAIL_CONFIRM_AJAX_ERROR');
			return;
		}

		Main\Mail\Sender::delete([$senderId]);

		return [];
	}

	private static function executeSenderListCanDel(&$error)
	{
		global $USER;

		$error = false;
		if(is_object($USER) && ($userId = $USER->getId()) !== null)
		{
			$mailboxes = Main\Mail\Sender::prepareUserMailboxes($userId);
			foreach ($mailboxes as $key => $box)
			{
				if(!(isset($box['can_delete']) && $box['can_delete']))
				{
					unset($mailboxes[$key]);
				}
			}
			return [
				'mailboxes'=> $mailboxes,
			];
		}
		$error = getMessage('MAIN_MAIL_CONFIRM_AUTH');
		return null;
	}

	private static function returnJson($data)
	{
		global $APPLICATION;

		$APPLICATION->restartBuffer();

		header('Content-Type: application/x-javascript; charset=UTF-8');
		echo Main\Web\Json::encode($data);
	}

}

MainMailConfirmAjax::execute();

require $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/epilog_after.php';
