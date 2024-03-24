<?php

namespace Bitrix\Mail\Controller;

use Bitrix\Main\Engine\Controller;
use Bitrix\Main\Loader;
use Bitrix\Mail\Internals\MailContactTable;
use Bitrix\Main\Error;

/**
 * Class AddressBook
 * @package Bitrix\Mail\Controller
 */
class AddressBook extends Controller
{
	private function editContact($contactData)
	{
		$id = $contactData['ID'];
		$userID = MailContactTable::getRow(
			[
				'filter' => ['ID' => $id],
				'select' => ['USER_ID'],
			]
		)['USER_ID'];

		$currentUserId = $this->getCurrentUser()?->getId();

		if (is_null($currentUserId) || !((int)$this->getCurrentUser()?->getId() === (int)$userID &&
			$contactData['NAME'] <> "" &&
			check_email($contactData['EMAIL'])))
		{
			return false;
		}

		MailContactTable::update(
			$id,
			[
				'ICON' => [
					'INITIALS' => $contactData['INITIALS'],
					'COLOR' => $contactData['COLOR'],
				],
				'NAME' => trim($contactData['NAME']),
				'EMAIL' => $contactData['EMAIL'],
			]
		);

		return true;
	}

	private function isUserAdmin()
	{
		global $USER;
		if (!(is_object($USER) && $USER->IsAuthorized()))
		{
			return false;
		}

		return $USER->isAdmin() || $USER->canDoOperation('bitrix24_config');
	}

	/**
	 * @param $idSet
	 *
	 * @return bool
	 * @throws \Exception
	 */
	public function removeContactsAction($idSet)
	{
		$currentUserId = $this->getCurrentUser()?->getId();

		if (!Loader::includeModule('mail') || is_null($currentUserId))
		{
			return false;
		}

		foreach ($idSet as $id)
		{
			$contactToDelete = MailContactTable::getRow(
				[
					'filter' => [
						'=ID' => $id,
						'=USER_ID' => $currentUserId,
					],
				]
			);

			if (is_null($contactToDelete))
			{
				return false;
			}

			MailContactTable::delete($id);
		}

		return true;
	}

	/**
	 * @param $contactData
	 *
	 * @return bool
	 * @throws \Bitrix\Main\Db\SqlQueryException
	 */
	public function saveContactAction($contactData)
	{
		if (!Loader::includeModule('mail'))
		{
			return false;
		}

		$contactData['EMAIL'] = mb_strtolower($contactData['EMAIL']);

		if(!check_email($contactData['EMAIL']))
		{
			return false;
		}

		if ($contactData['ID'] !== 'new')
		{
			return $this->editContact($contactData);
		}
		else
		{
			$currentUserId = $this->getCurrentUser()?->getId();

			if (is_null($currentUserId))
			{
				return false;
			}

			$contactsData[] = [
				'USER_ID' => $currentUserId,
				'NAME' => $contactData['NAME'],
				'ICON' => [
					'INITIALS' => $contactData['INITIALS'],
					'COLOR' => $contactData['COLOR'],
				],
				'EMAIL' => $contactData['EMAIL'],
				'ADDED_FROM' => 'MANUAL',
			];

			$result = MailContactTable::addContactsBatch($contactsData);

			iF($result !== true)
			{
				$this->addError(new Error($result));
				return false;
			}
		}

		return true;
	}
}