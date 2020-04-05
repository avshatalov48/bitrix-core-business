<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sender
 * @copyright 2001-2012 Bitrix
 */
namespace Bitrix\Sender\Entity;


use Bitrix\Main\Localization\Loc;

use Bitrix\Sender\Integration;
use Bitrix\Sender\Message\Factory;


Loc::loadMessages(__FILE__);

class Rc extends Letter
{
	/**
	 * Get filter fields.
	 *
	 * @return array
	 */
	protected static function getFilterFields()
	{
		$messageCodes = [];
		$messages = Factory::getReturnCustomerMessages();
		foreach ($messages as $message)
		{
			$messageCodes[] = $message->getCode();
		}

		return array(
			array(
				'CODE' => null,
				'VALUE' => 'N',
				'FILTER' => '=CAMPAIGN.IS_TRIGGER'
			),
			array(
				'CODE' => 'IS_ADS',
				'VALUE' => 'N',
				'FILTER' => '=IS_ADS'
			),
			array(
				'CODE' => 'MESSAGE_CODE',
				'VALUE' => $messageCodes,
				'FILTER' => '=MESSAGE_CODE'
			),
		);
	}

	/**
	 * Save data.
	 *
	 * @param integer|null $id ID.
	 * @param array $data Data.
	 * @return integer|null
	 */
	protected function saveData($id = null, array $data)
	{
		if (!Integration\Crm\ReturnCustomer\Service::isAvailable())
		{
			$this->addError(Loc::getMessage('SENDER_ENTITY_RC_ERROR_NO_ACCESS'));
			return $id;
		}

		return parent::saveData($id, $data);
	}
}