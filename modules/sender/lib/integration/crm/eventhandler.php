<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sender
 * @copyright 2001-2012 Bitrix
 */

namespace Bitrix\Sender\Integration\Crm;

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;

use Bitrix\Sender\Entity;
use Bitrix\Sender\Recipient;

use Bitrix\Crm\Integrity\ActualEntitySelector;
use Bitrix\Crm\Activity\BindingSelector;

Loc::loadMessages(__FILE__);

/**
 * Class EventHandler
 * @package Bitrix\Sender\Integration\Crm
 */
class EventHandler
{
	/**
	 * Handler of event sender/OnAfterPostingSendRecipient.
	 *
	 * @param array $eventData Event.
	 * @param Entity\Letter $letter Letter.
	 */
	public static function onAfterPostingSendRecipient(array $eventData, Entity\Letter $letter)
	{
		if (!$eventData['SEND_RESULT'])
		{
			return;
		}

		static $isModuleIncluded = null;
		if ($isModuleIncluded === null)
		{
			$isModuleIncluded = Loader::includeModule('crm');
		}

		if (!$isModuleIncluded)
		{
			return;
		}

		if ($letter->getMessage()->isReturnCustomer())
		{
			return;
		}

		$recipient = $eventData['RECIPIENT'];
		$fields = $eventData['RECIPIENT']['FIELDS'];
		$entityTypeId = $entityId = null;
		if (isset($fields['CRM_ENTITY_TYPE_ID']) && $fields['CRM_ENTITY_TYPE_ID'])
		{
			$entityTypeId = $fields['CRM_ENTITY_TYPE_ID'];
		}
		if (isset($fields['CRM_ENTITY_ID']) && $fields['CRM_ENTITY_ID'])
		{
			$entityId = $fields['CRM_ENTITY_ID'];
		}

		if (!$entityTypeId || !$entityId)
		{
			$selector = self::getEntitySelectorByRecipient(
				$eventData['RECIPIENT']['CONTACT_TYPE_ID'],
				$eventData['RECIPIENT']['CONTACT_CODE']
			);
		}
		else
		{
			$selector = self::getEntitySelectorById($entityTypeId, $entityId);
		}


		if (!$selector)
		{
			return;
		}

		if (!$selector->search()->hasEntities())
		{
			return;
		}

		self::addTimeLineEvent($selector, $letter, $recipient);
	}

	protected static function addTimeLineEvent(ActualEntitySelector $selector, Entity\Letter $letter, $recipient)
	{
		$isAd = $letter instanceof Entity\Ad;
		$createdBy = $letter->get('CREATED_BY');
		if (!$createdBy)
		{
			return;
		}

		// convert format to time line
		$bindings = array();
		$activityBindings = BindingSelector::findBindings($selector);
		foreach ($activityBindings as $binding)
		{
			$binding['ENTITY_ID'] = $binding['OWNER_ID'];
			$binding['ENTITY_TYPE_ID'] = $binding['OWNER_TYPE_ID'];
			$bindings[] = array(
				'ENTITY_TYPE_ID' => $binding['OWNER_TYPE_ID'],
				'ENTITY_ID' => $binding['OWNER_ID'],
			);
		}

		$parameters = array(
			'ENTITY_TYPE_ID' => $selector->getPrimaryTypeId(),
			'ENTITY_ID' => $selector->getPrimaryId(),
			'TYPE_CATEGORY_ID' => $letter->getMessage()->getCode(),
			'AUTHOR_ID' => $createdBy,
			'SETTINGS' => array(
				'letterId' => $letter->getId(),
				'isAds' => $isAd,
				'recipient' => array(
					'id' => $recipient['ID'],
					'typeId' => $recipient['CONTACT_TYPE_ID'],
					'code' => $recipient['CONTACT_ID'],
				),
			),
			'BINDINGS' => $bindings
		);
		Timeline\RecipientEntry::create($parameters);
	}

	protected static function getEntitySelector()
	{
		/** @var ActualEntitySelector $selector */
		static $selector = null;
		if (!$selector)
		{
			$selector = new ActualEntitySelector();
		}
		else
		{
			$selector->clear();
		}

		return $selector;
	}

	protected static function getEntitySelectorById($entityTypeId, $entityId)
	{
		return self::getEntitySelector()->setEntity($entityTypeId, $entityId);
	}

	protected static function getEntitySelectorByRecipient($recipientTypeId, $recipientCode)
	{
		$selector = self::getEntitySelector();

		switch ($recipientTypeId)
		{
			case Recipient\Type::EMAIL:
				$selector->appendEmailCriterion($recipientCode);
				break;

			case Recipient\Type::PHONE:
				$selector->appendPhoneCriterion($recipientCode);
				break;

			default:
				return null;
		}

		return $selector;
	}
}