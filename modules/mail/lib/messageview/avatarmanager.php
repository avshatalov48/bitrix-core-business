<?php
namespace Bitrix\Mail\MessageView;

use Bitrix\Main;
use Bitrix\Mail\Internals\MailContactTable;

class AvatarManager
{
	private $currentUserId;

	public function __construct($currentUserId = null)
	{
		$this->currentUserId = $currentUserId;
	}

	public static function getAvatarKeyByString($string): ?string
	{
		$parts = explode(",", $string);
		$firstContact = trim($parts[0]);

		$address = new Main\Mail\Address($firstContact);
		if ($address->validate())
		{
			return $address->getEmail();
		}

		return null;
	}

	public function getAvatarParamsFromEmails($filedFromList)
	{
		$fileds = [];

		foreach ($filedFromList as $filed)
		{
			$fileds = array_merge($fileds, $this->getAvatarParamsFromMessagesHeaders([['FIELD_FROM' => $filed]]));
		}

		return $fileds;
	}

	public function getAvatarParamsFromMessagesHeaders($messages)
	{
		$mailsNames = $this->getEmailsNames($messages);

		$mailContacts = $this->fetchMailContacts(array_map(
			function ($item)
			{
				return $item['email'];
			},
			$mailsNames
		));

		$mailContacts = $this->fillFileIdColumn($mailContacts);

		foreach ($mailsNames as $email => $data)
		{
			if (!empty($mailContacts[$email]))
			{
				if ((!$data['name'] || $data['name'] === $data['email']) &&
					($mailContacts[$email]['NAME'] && $mailContacts[$email]['NAME'] !== $mailContacts[$email]['EMAIL']))
				{
					$mailsNames[$email]['name'] = $mailContacts[$email]['NAME'];
				}
				$mailsNames[$email]['mailContact'] = $mailContacts[$email];
			}
			else
			{
				$mailsNames[$email]['mailContact'] = [
					'EMAIL' => $data['email'],
					'NAME' => $data['name'],
				];
			}
		}
		return $mailsNames;
	}

	private function getEmailsNames($messages, $fieldList = false)
	{
		$emailNames = [];

		if($fieldList)
		{
			$messages = $fieldList;
		}

		foreach ($messages as $index => $message)
		{
			$emailNames = array_merge($emailNames, $this->extractMailsNamesFrom($message['FIELD_FROM']));
			$emailNames = array_merge($emailNames, $this->extractMailsNamesFrom($message['FIELD_TO']));
			$emailNames = array_merge($emailNames, $this->extractMailsNamesFrom($message['FIELD_CC']));
			$emailNames = array_merge($emailNames, $this->extractMailsNamesFrom($message['FIELD_BCC']));
		}
		$emailNames = $this->getBestNameChoices($emailNames);

		return $emailNames;
	}

	private function getBestNameChoices($emailNames)
	{
		$results = [];
		$bestNames = [];
		foreach ($emailNames as $index => $data)
		{
			if (!isset($bestNames[$data['email']]))
			{
				$bestNames[$data['email']] = $data['name'];
				$results[$data['email']] = [
					'email' => $data['email'],
					'name' => $data['name'],
				];
				continue;
			}
			$newName = $data['name'];
			$oldName = $bestNames[$data['email']];
			if (!$oldName || $oldName == $data['email'])
			{
				$bestNames[$data['email']] = $newName;
				$results[$data['email']] = [
					'email' => $data['email'],
					'name' => $newName,
				];
			}
		}

		return $results;
	}

	private function extractMailsNamesFrom($parsedListOfEmails)
	{
		$emailNames = [];

		if ($parsedListOfEmails)
		{
			foreach (\Bitrix\Mail\Helper\Message::parseAddressList($parsedListOfEmails) as $mailCopy)
			{
				$avatarKey = static::getAvatarKeyByString($mailCopy);
				if ($avatarKey)
				{
					$address = new Main\Mail\Address($avatarKey);
					if ($address->validate())
					{
						$emailNames[] = [
							'email' => $address->getEmail(),
							'name' => $address->getName() ?: $address->getEmail(),
						];
					}
				}
			}
		}
		return $emailNames;
	}

	private function fillFileIdColumn($mailContacts)
	{
		foreach ($mailContacts as $mail => $mailContact)
		{
			if (!empty($mailContacts[$mail]['AVATAR_ID']))
			{
				$mailContacts[$mail]['FILE_ID'] = $mailContacts[$mail]['AVATAR_ID'];
			}
		}
		return $mailContacts;
	}

	protected function fetchMailContacts(array $emails): array
	{
		if (empty($emails))
		{
			return [];
		}

		static $cachedContacts = [];

		$newEmails = array_diff($emails, array_keys($cachedContacts));

		if (!empty($newEmails))
		{
			$fetchedContacts = MailContactTable::getList(array(
				'runtime' => array(
					new Main\ORM\Fields\Relations\Reference(
						'USER',
						Main\UserTable::class,
						array(
							'=this.EMAIL' => 'ref.EMAIL',
							'=ref.ACTIVE' => new Main\DB\SqlExpression('?', 'Y'),
						)
					)
				),
				'select' => array(
					'NAME', 'EMAIL', 'ICON', 'FILE_ID',
					'AVATAR_ID' => 'USER.PERSONAL_PHOTO',
				),
				'filter' => array(
					'=USER_ID' => $this->currentUserId,
					'@EMAIL' => $emails,
				),
			))->fetchAll();

			foreach ($fetchedContacts as $contact)
			{
				$cachedContacts[$contact['EMAIL']] = $contact;
			}
		}

		return array_intersect_key($cachedContacts, array_flip($emails));
	}
}