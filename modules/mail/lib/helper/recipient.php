<?php

namespace Bitrix\Mail\Helper;

use Bitrix\Main;
use Bitrix\Mail;

/**
 * Class Recipient
 * @package Bitrix\Mail\Helper
 */
class Recipient
{
	/**
	 * Builds unique code for BX.UI.Selector selector.
	 *
	 * @param string $email Email.
	 *
	 * @return string
	 */
	public static function buildUniqueEmailCode($email)
	{
//		return 'U' . md5($email);
		return 'MC' . $email;
	}

	/**
	 * Load last used Rcpt.
	 *
	 * @param string $emailTo Add this email to the result list.
	 * @param integer $limit Limit list length.
	 *
	 * @return array Data preformed for BX.UI.Selector selector with email only mode enabled.
	 *
	 * @throws Main\SystemException
	 */
	public static function loadLastRcpt($emailTo = null, $limit = 10)
	{
		global $APPLICATION;

		$result = array();

		$currentUser = \Bitrix\Main\Engine\CurrentUser::get();

		$lastRcptResult = \Bitrix\Main\FinderDestTable::getList(array(
			'filter' => array(
				'=USER_ID' => $currentUser->getId(),
				'=CONTEXT' => 'MAIL_LAST_RCPT',
			),
			'select' => array('CODE'),
			'order' => array('LAST_USE_DATE' => 'DESC'),
			'limit' => $limit,
		));

		$emailUsersIds = array();
		while ($item = $lastRcptResult->fetch())
		{
			$emailUsersIds[] = (int) str_replace('MC', '', $item['CODE']);
		}

		if (count($emailUsersIds) > 0)
		{
			$mailContacts = Mail\Internals\MailContactTable::getList([
				'filter' => array(
					'@ID' => $emailUsersIds,
					'=USER_ID' => $currentUser->getId(),
				),
				'select' => ['ID', 'NAME', 'EMAIL', 'ICON'],
				'limit' => $limit,
			])->fetchAll();

			$contactAvatars = $resultsMailContacts = [];
			foreach ($mailContacts as $mailContact)
			{
				$resultsMailContacts[$mailContact['EMAIL']] = $mailContact;
			}
			foreach ($resultsMailContacts as $mailContact)
			{
				$email = $mailContact['EMAIL'];
				if ($contactAvatars[$email] === null)
				{
					ob_start();
					$APPLICATION->includeComponent('bitrix:mail.contact.avatar', '', array(
						'mailContact' => $mailContact,
					));
					$contactAvatars[$email] = ob_get_clean();
				}
				$id = static::buildUniqueEmailCode($email);
				$result[$id] = [
					'id' => $id,
					'entityType' => 'email',
					'entityId' => $mailContact['ID'],
					'name' => htmlspecialcharsbx($mailContact['NAME']),
					'iconCustom' => $contactAvatars[$email],
					'email' => htmlspecialcharsbx($mailContact['EMAIL']),
					'desc' => htmlspecialcharsbx($mailContact['EMAIL']),
					'isEmail' => 'Y',
				];
			}
		}

		return $result;
	}


	/**
	 * Load mail contacts - users with EXTERNAL_AUTH_ID = email.
	 *
	 * @param array $filter Filter.
	 * @param integer $limit Limit list length.
	 *
	 * @return array  Data preformed for BX.UI.Selector selector with email only mode enabled.
	 *
	 * @throws Main\SystemException
	 */
	public static function loadMailContacts($filter = [], $limit = 20)
	{
		global $APPLICATION;

		$result = array();

		$mailContacts = \Bitrix\Main\UserTable::getList(array(
			'select' => array(
				'ID',
				'NAME',
				'LAST_NAME',
				'SECOND_NAME',
				'EMAIL',
				'PERSONAL_PHOTO',
			),
			'filter' => array_merge(
				array(
					'=ACTIVE' => 'Y',
					'=EXTERNAL_AUTH_ID' => 'email',
				),
				$filter
			),
			'order' => array(
				'LAST_NAME' => 'ASC',
			),
			'limit' => $limit,
		));

		$contactAvatars = array();
		while ($mailContact = $mailContacts->fetch())
		{
			$email = $mailContact['EMAIL'];
			if ($contactAvatars[$email] === null)
			{
				ob_start();
				$APPLICATION->includeComponent('bitrix:mail.contact.avatar', '',
					[
						'mailContact' => array(
							'FILE_ID' => $mailContact['PERSONAL_PHOTO'],
							'name' => \CUser::formatName(\CSite::getNameFormat(), $mailContact),
							'email' => $mailContact['EMAIL'],
						),
					]);
				$contactAvatars[$email] = ob_get_clean();
			}

			$id = static::buildUniqueEmailCode($email);
			$result[$id] = array(
				'id' => $id,
				'entityType' => 'mailContacts',
				'entityId' => $mailContact['ID'],
				'name' => \CUser::formatName(\CSite::getNameFormat(), $mailContact, true, true),
				'iconCustom' => $contactAvatars[$email],
				'email' => htmlspecialcharsbx($mailContact['EMAIL']),
				'desc' => htmlspecialcharsbx($mailContact['EMAIL']),
				'isEmail' => 'Y',
			);
		}

		return $result;
	}

	/**
	 * Load mail contacts from CRM - users with EXTERNAL_AUTH_ID = email and contact crm entity.
	 *
	 * @param array $filter Filter.
	 * @param integer $limit Limit list length.

	 * @return array  Data preformed for BX.UI.Selector selector with email only mode enabled.
	 *
	 * @throws Main\LoaderException
	 */
	public static function loadCrmMailContacts($filter = [], $limit = 20)
	{
		global $APPLICATION;

		$result = array();

		if (Main\Loader::includeModule('crm'))
		{
			$mailContacts = \Bitrix\Main\UserTable::getList(array(
				'select' => array(
					'ID',
					'NAME',
					'LAST_NAME',
					'SECOND_NAME',
					'EMAIL',
					'PERSONAL_PHOTO',
				),
				'filter' => array_merge(
					array(
						'=ACTIVE' => 'Y',
						'=EXTERNAL_AUTH_ID' => 'email',
					),
					$filter
				),
				'order' => array(
					'LAST_NAME' => 'ASC',
				),
				'limit' => $limit,
			));

			$contactAvatars = array();
			while ($mailContact = $mailContacts->fetch())
			{
				$email = $mailContact['EMAIL'];
				if (empty($email))
				{
					continue;
				}

				$crmCommunications = \CSocNetLogDestination::searchCrmEntities(array(
					'SEARCH' => $email,
					'ENTITIES' => array('CONTACT'),
				));
				foreach ($crmCommunications as $communication)
				{
					$email = $communication['email'];

					if ($contactAvatars[$email] === null)
					{
						ob_start();
						$APPLICATION->includeComponent('bitrix:mail.contact.avatar', '',
							[
								'mailContact' => array(
									'FILE_ID' => $mailContact['PERSONAL_PHOTO'],
									'name' => \CUser::formatName(\CSite::getNameFormat(), $mailContact),
									'email' => $email,
								),
							]);
						$contactAvatars[$email] = ob_get_clean();
						$communication['iconCustom'] = $contactAvatars[$email];
					}

					$id = static::buildUniqueEmailCode($email);
					$communication['id'] = $id;
					$result[$id] = $communication;
				}
			}
		}

		return $result;
	}
}
