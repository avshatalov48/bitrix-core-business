<?
namespace Bitrix\Calendar\Integration\Sender;
use Bitrix\Main\Loader;

class AllowedSender
{
	/**
	 * Get list of allowed senders
	 * @param int|null $forUserId For whom.
	 * @return array
	 */
	public static function getList($forUserId = null)
	{
		$result = [];

		$userMailboxes = \Bitrix\Main\Mail\Sender::prepareUserMailboxes($forUserId);
		if (is_array($userMailboxes))
		{
			foreach ($userMailboxes as $mailbox)
			{
				$formatted = isset($mailbox['formatted']) ? $mailbox['formatted'] : $mailbox['formated'];
				$result[] = [
					'name' => $mailbox['name'],
					'email' => $mailbox['email'],
					'formatted' => preg_replace("/^<(.*)>$/i", "$1", $formatted),
				];
			}
		}

		if (Loader::includeModule("sender")
			&& !\Bitrix\Sender\Integration\Bitrix24\Service::isCloud())
		{
			$addressFromList = \Bitrix\Sender\MailingChainTable::getEmailFromList();
			$address = new \Bitrix\Main\Mail\Address();
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
					'formatted' => preg_replace("/^<(.*)>$/i", "$1", $formatted),
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