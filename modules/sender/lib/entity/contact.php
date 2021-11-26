<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sender
 * @copyright 2001-2012 Bitrix
 */
namespace Bitrix\Sender\Entity;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Error;
use Bitrix\Main\DB\SqlQueryException;

use Bitrix\Sender\ContactTable;
use Bitrix\Sender\ContactListTable;
use Bitrix\Sender\MailingSubscriptionTable;
use Bitrix\Sender\Recipient;

Loc::loadMessages(__FILE__);

/**
 * Class Contact
 * @package Bitrix\Sender\Entity
 */
class Contact extends Base
{
	/**
	 * Get list.
	 *
	 * @param array $parameters Parameters.
	 * @return \Bitrix\Main\DB\Result
	 */
	public static function getList(array $parameters = array())
	{
		if (!isset($parameters['select']))
		{
			$parameters['select'] = array(
				'*',
			);
		}
		if (!isset($parameters['filter']))
		{
			$parameters['filter'] = array();
		}

		return ContactTable::getList($parameters);
	}

	/**
	 * Get default data.
	 *
	 * @return array
	 */
	protected function getDefaultData()
	{
		return array(
			'NAME' => '',
			'CODE' => '',
			'TYPE_ID' => Recipient\Type::EMAIL,
			'BLACKLISTED' => 'N',
			'SET_LIST' => [],
			'SUB_LIST' => [],
			'UNSUB_LIST' => [],
		);
	}

	/**
	 * Save data.
	 *
	 * @param integer|null $id ID.
	 * @param array $data Data.
	 * @return integer|null
	 * @throws
	 */
	protected function saveData($id = null, array $data)
	{
		$setList = array_filter($data['SET_LIST'], 'is_numeric');
		$subList = array_filter($data['SUB_LIST'], 'is_numeric');
		$unsubList = array_filter($data['UNSUB_LIST'], 'is_numeric');

		$this->filterDataByEntityFields(ContactTable::getEntity(), $data);

		try
		{
			$id = $this->saveByEntity(ContactTable::getEntity(), $id, $data);
		}
		catch (SqlQueryException $exception)
		{
			if (mb_strpos($exception->getMessage(), '(1062) Duplicate entry') !== false)
			{
				$this->errors->setError(new Error(Loc::getMessage('SENDER_ENTITY_CONTACT_ERROR_DUPLICATE')));
				return $id;
			}

			throw $exception;
		}

		if ($this->hasErrors())
		{
			return $id;
		}

		$this->saveDataLists($id, $setList, $subList, $unsubList);

		return $id;
	}

	protected function saveDataLists($id, $setList, $subList, $unsubList)
	{
		$setList = array_unique($setList);
		$unsubList = array_unique($unsubList);
		$subList = array_unique($subList);
		$subList = array_diff($subList, $unsubList);

		ContactListTable::deleteList(['CONTACT_ID' => $id]);
		foreach ($setList as $itemId)
		{
			ContactListTable::add(['CONTACT_ID' => $id, 'LIST_ID' => $itemId]);
		}

		MailingSubscriptionTable::deleteList(['CONTACT_ID' => $id]);
		foreach ($subList as $itemId)
		{
			MailingSubscriptionTable::add(['CONTACT_ID' => $id, 'MAILING_ID' => $itemId, 'IS_UNSUB' => 'N']);
		}
		foreach ($unsubList as $itemId)
		{
			MailingSubscriptionTable::add(['CONTACT_ID' => $id, 'MAILING_ID' => $itemId, 'IS_UNSUB' => 'Y']);
		}
	}

	/**
	 * Load data.
	 *
	 * @param integer $id ID.
	 * @return array|null
	 */
	public function loadData($id)
	{
		$data = ContactTable::getRowById($id);
		if ($data)
		{
			$list = ContactListTable::getList([
				'select' => ['LIST_ID'],
				'filter' => ['CONTACT_ID' => $id]
			])->fetchAll();
			$data['SET_LIST'] = array_column($list, 'LIST_ID');

			$list = MailingSubscriptionTable::getList([
				'select' => ['MAILING_ID'],
				'filter' => ['CONTACT_ID' => $id, 'IS_UNSUB' => 'N']
			])->fetchAll();
			$data['SUB_LIST'] = array_column($list, 'MAILING_ID');

			$list = MailingSubscriptionTable::getList([
				'select' => ['MAILING_ID'],
				'filter' => ['CONTACT_ID' => $id, 'IS_UNSUB' => 'Y']
			])->fetchAll();
			$data['UNSUB_LIST'] = array_column($list, 'MAILING_ID');
		}

		return $data;
	}

	/**
	 * Remove.
	 *
	 * @return bool
	 */
	public function remove()
	{
		return $this->removeByEntity(ContactTable::getEntity(), $this->getId());
	}

	/**
	 * Remove by contact ID.
	 *
	 * @param integer $id Contact ID.
	 * @return bool
	 */
	public static function removeById($id)
	{
		return static::create()->removeByEntity(ContactTable::getEntity(), $id);
	}

	/**
	 * Remove from blacklist by contact ID.
	 *
	 * @param integer $id Contact ID.
	 * @return bool
	 */
	public static function removeFromBlacklistById($id)
	{
		return ContactTable::update($id, array('BLACKLISTED' => 'N'))->isSuccess();
	}

	/**
	 * Subscribe.
	 *
	 * @param integer|null $campaignId Campaign ID.
	 * @return bool
	 */
	public function subscribe($campaignId = null)
	{
		if (!$this->getId())
		{
			return false;
		}

		$campaignId = $campaignId ?: Campaign::getDefaultId(SITE_ID);
		return MailingSubscriptionTable::addSubscription(array(
			'MAILING_ID' => $campaignId,
			'CONTACT_ID' => $this->getId(),
		));
	}

	/**
	 * Unsubscribe.
	 *
	 * @param integer|null $campaignId Campaign ID.
	 * @return bool
	 */
	public function unsubscribe($campaignId = null)
	{
		if (!$this->getId())
		{
			return false;
		}

		$campaignId = $campaignId ?: Campaign::getDefaultId(SITE_ID);
		return MailingSubscriptionTable::addUnSubscription(array(
			'MAILING_ID' => $campaignId,
			'CONTACT_ID' => $this->getId(),
		));
	}

	/**
	 * Add to blacklist.
	 *
	 * @return bool
	 */
	public function addToBlacklist()
	{
		if (!$this->getId())
		{
			return false;
		}

		return ContactTable::update($this->getId(), array('BLACKLISTED' => 'Y'))->isSuccess();
	}

	/**
	 * Remove from blacklist.
	 *
	 * @return bool
	 */
	public function removeFromBlacklist()
	{
		if (!$this->getId())
		{
			return false;
		}

		return self::removeFromBlacklistById($this->getId());
	}

	/**
	 * Add to list.
	 *
	 * @param int $listId List ID.
	 * @return bool
	 */
	public function addToList($listId)
	{
		if (!$this->getId())
		{
			return false;
		}

		return ContactListTable::addIfNotExist($this->getId(), $listId);
	}

	/**
	 * Remove from list.
	 *
	 * @param int $listId List ID.
	 * @return bool
	 */
	public function removeFromList($listId)
	{
		if (!$this->getId())
		{
			return false;
		}

		return ContactListTable::delete(array(
			'CONTACT_ID' => $this->getId(),
			'LIST_ID' => $listId
		))->isSuccess();
	}
}