<?php

namespace Bitrix\Main\Controller\Mail;

use Bitrix\Main\Engine\Controller;
use Bitrix\Main\Engine\CurrentUser;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Mail;
use Bitrix\Main;
use Bitrix\Main\Error;

class Sender extends Controller
{
	public function getSenderDataAction(int $senderId): ?array
	{
		$sender = \Bitrix\Main\Mail\Internal\SenderTable::getById($senderId)->fetch();

		if (!$sender)
		{
			return null;
		}

		$smtp = $sender['OPTIONS']['smtp'] ?? [];

		if (!empty($smtp))
		{
			$response['smtp'] = [
				'server' => $smtp['server'] ?? null,
				'port' => $smtp['port'] ?? null,
				'protocol' => $smtp['protocol'] ?? null,
				'login' => $smtp['login'] ?? null,
				'limit' => $smtp['limit'] ?? null,
			];
		}

		return array_merge(
			$response['smtp'] ?? [],
			[
				'email' => $sender['EMAIL'],
				'name' => !empty($sender['NAME']) ? $sender['NAME'] : Mail\Sender\UserSenderDataProvider::getUserFormattedName((int)$sender['USER_ID']),
				'isPublic' => (int)$sender['IS_PUBLIC'] === 1,
			]
		);
	}

	public function getAvailableSendersAction(): array
	{
		return Main\Mail\Sender::prepareUserMailboxes();
	}

	public function getSenderTransitionalDataAction(int $senderId): ?array
	{
		return Mail\Sender\UserSenderDataProvider::getSenderTransitionalData($senderId);
	}

	public function getSenderByMailboxIdAction(int $mailboxId, bool $getSenderWithoutSmtp = false): ?array
	{
		return Mail\Sender\UserSenderDataProvider::getSenderNameByMailboxId($mailboxId, $getSenderWithoutSmtp);
	}

	public function getDefaultSenderNameAction(): string
	{
		return Mail\Sender\UserSenderDataProvider::getUserFormattedName() ?? '';
	}

	public function submitSenderAction(array $data): ?array
	{
		$name = trim((string)($data['name'] ?? ''));
		$public = $data['public'] === 'Y';
		$userId = (int)CurrentUser::get()->getId();

		$email = mb_strtolower(trim((string)($data['email'] ?? '')));
		if (!check_email($email, true))
		{
			$errorCode = empty($email) ? 'MAIN_CONTROLLER_MAIL_SENDER_EMPTY_EMAIL' : 'MAIN_CONTROLLER_MAIL_SENDER_INVALID_EMAIL';
			$this->addError(new Error(Loc::getMessage($errorCode), 'ERR_INVALID_EMAIL'));

			return null;
		}

		$senderId = (int)($data['id'] ?? null);
		if (!$senderId && Mail\Sender::hasUserSenderWithEmail($email))
		{
			$this->addError(new Error(Loc::getMessage('MAIN_CONTROLLER_MAIL_SENDER_EXISTS_SENDER'), 'ERR_EXISTS_SENDER'));

			return null;
		}

		$smtp = $data['smtp'] ?? [];

		if ($senderId)
		{
			$checkResult = Main\Mail\Sender::canEditSender($senderId);
			if (!$checkResult->isSuccess())
			{
				$this->addErrors($checkResult->getErrors());

				return null;
			}

			$sender = Mail\Internal\SenderTable::getById($senderId)->fetch();
			$userId = (int)$sender['USER_ID'];
			if (!empty($smtp) && empty($smtp['password']) && $sender['OPTIONS']['smtp'])
			{
				$smtp['password'] = $sender['OPTIONS']['smtp']['password'];
			}
		}

		if (!empty($smtp))
		{
			if (!is_array($smtp))
			{
				$this->addError(new Error(Loc::getMessage('MAIN_CONTROLLER_MAIL_SENDER_AJAX_ERROR'), 'ERR_EMAIL'));

				return null;
			}

			$result = Mail\Sender::prepareSmtpConfigForSender($smtp);

			if (!$result->isSuccess())
			{
				$error = $result->getErrors()[0];
				$this->addError(new Error($error->getMessage(), 'ERR_SMTP_CONFIG'));

				return null;
			}
		}

		$fields = [
			'NAME' => $name,
			'EMAIL' => $email,
			'USER_ID' => $userId,
			'IS_CONFIRMED' => true,
			'IS_PUBLIC' => $public,
			'OPTIONS' => [],
		];

		if (!empty($smtp))
		{
			$fields['OPTIONS']['smtp'] = $smtp;
		}

		if (!$senderId)
		{
			$result = Main\Mail\Sender::add($fields);

			if (!empty($result['error']))
			{
				$this->addError($result['error']);

				return null;
			}

			if (!empty($result['errors']))
			{
				$this->addError($result['errors'][0]);

				return null;
			}

			$senderId = $result['senderId'] ?? null;
		}
		else
		{
			$updateResult = Mail\Internal\SenderTable::update($senderId, $fields);

			if(!$updateResult->isSuccess())
			{
				$this->addError($updateResult->getErrors()[0]);

				return null;
			}
		}

		if ($smtp && $smtp['limit'] !== null)
		{
			Main\Mail\Sender::setEmailLimit($email, $smtp['limit']);
		}
		elseif ($smtp && !isset($smtp['limit']))
		{
			Main\Mail\Sender::removeEmailLimit($email);
		}

		return [
			'senderId' => $senderId ?? null,
			'name' => !empty($name) ? $name : Mail\Sender\UserSenderDataProvider::getUserFormattedName($userId),
		];
	}

	/**
	 * Add Sender without smtp-server settings
	 */
	public function addAliasAction(string $name, string $email): ?array
	{
		$userId = (int)CurrentUser::get()->getId();

		if (!$userId)
		{
			return null;
		}

		if (!Main\Mail\Sender::hasUserAvailableSmtpSenderByEmail($email, $userId))
		{
			return null;
		}

		$result = Main\Mail\Sender::add([
			'NAME' => $name,
			'EMAIL' => $email,
			'IS_CONFIRMED' => true,
			'USER_ID' => $userId,
		]);

		if (!empty($result['error']))
		{
			$this->addError(new Error($result['error'], 'ERR_ADD_SENDER'));

			return null;
		}

		$userData = Mail\Sender\UserSenderDataProvider::getUserInfo($userId);
		$result['avatar'] = $userData['userAvatar'] ?? null;
		$result['userUrl'] = $userData['userUrl'] ?? null;

		return $result;
	}

	public function deleteSenderAction(int $senderId): void
	{
		$checkResult = Main\Mail\Sender::canEditSender($senderId);
		if (!$checkResult->isSuccess())
		{
			$this->addErrors($checkResult->getErrors());

			return;
		}

		Main\Mail\Sender::delete([$senderId]);
	}

	public function updateSenderNameAction(int $senderId, string $name): void
	{
		$result = Main\Mail\Sender::updateSender($senderId, ['NAME' => $name]);
		if (!$result->isSuccess())
		{
			$this->addErrors($result->getErrors());
		}
	}
}
