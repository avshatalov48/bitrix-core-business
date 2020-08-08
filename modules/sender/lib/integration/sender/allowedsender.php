<?php

/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sender
 * @copyright 2001-2019 Bitrix
 */

namespace Bitrix\Sender\Integration\Sender;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Mail\Address;

Loc::loadMessages(__FILE__);

class AllowedSender
{
	/**
	 * Get list of allowed senders
	 * @param int|null $forUserId For whom.
	 * @return array
	 */
	public static function getList($forUserId = null)
	{
		$result = \Bitrix\Main\Mail\Sender::prepareUserMailboxes($forUserId);
		if (!\Bitrix\Sender\Integration\Bitrix24\Service::isCloud())
		{
			$addressFromList = \Bitrix\Sender\MailingChainTable::getEmailFromList();

			$address = new Address();
			foreach ($addressFromList as $email)
			{
				$address->set($email);
				$formatted = $address->get();
				if (!$formatted)
				{
					continue;
				}

				$result[] = [
					'name' => $address->getName(),
					'email' => $address->getEmail(),
					'formatted' => $address->get(),
				];
			}
		}
		return $result;
	}

	/**
	 * Is $email an allowed sender or not
	 * @param string $email Sender email.
	 * @param int|null $userId For whom.
	 * @return bool
	 */
	public static function isAllowed($email, $userId = null)
	{
		if ($email == '')
		{
			return false;
		}

		$address = new Address();
		$address->set($email);
		$normalizedEmail = $address->getEmail();
		if (!$normalizedEmail)
		{
			return false;
		}
		$normalizedEmail = mb_strtolower($normalizedEmail);
		$allowedSenders = self::getList($userId);
		if (empty(array_filter($allowedSenders, function ($item) use ($normalizedEmail)
		{
			return mb_strtolower($item['email']) === $normalizedEmail;
		})))
		{
			return false;
		}

		return true;
	}
}
