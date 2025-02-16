<?php

namespace Bitrix\Mail\Controller;

use Bitrix\Mail\Message;
use Bitrix\Main\Engine\Controller;
use Bitrix\Main\Loader;
use Bitrix\Mail\Internals\MailContactTable;
use Bitrix\UI\EntitySelector\ItemCollection;

/**
 * Class AddressBook
 * @package Bitrix\Mail\Controller
 */
class AddressBook extends Controller
{
	private function editContact($contactData): ItemCollection
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
			return Message::getSelectedRecipientsForDialog();
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

		return Message::getSelectedRecipientsForDialog([
			[
				'email' => $contactData['EMAIL'],
				'name' => $contactData['NAME'],
			],
		]);
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
	 * @param string $email
	 * @return int
	 */
	public function getContactIdByEmailAction(string $email): int
	{
		$currentUserId = $this->getCurrentUser()?->getId();

		if (is_null($currentUserId) || !Loader::includeModule('mail'))
		{
			return 0;
		}

		return MailContactTable::getContactByEmail($email, $currentUserId)['ID'];
	}

	/**
	 * @param $contactData
	 *
	 * @return ItemCollection
	 */
	public function saveContactAction($contactData): ItemCollection
	{
		$selectedRecipientsForDialog = Message::getSelectedRecipientsForDialog();

		if (!Loader::includeModule('mail'))
		{
			return $selectedRecipientsForDialog;
		}

		$contactData['EMAIL'] = mb_strtolower($contactData['EMAIL']);

		if(!check_email($contactData['EMAIL']))
		{
			return $selectedRecipientsForDialog;
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
				return $selectedRecipientsForDialog;
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

			if (!$result->isSuccess())
			{
				$this->addErrors($result->getErrors());

				return $selectedRecipientsForDialog;
			}
		}

		return Message::getSelectedRecipientsForDialog([
			[
				'email' => $contactData['EMAIL'],
				'name' => $contactData['NAME'],
			],
		]);
	}
}