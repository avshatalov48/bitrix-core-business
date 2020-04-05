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


Loc::loadMessages(__FILE__);

class Ad extends Letter
{
	/**
	 * Get filter fields.
	 *
	 * @return array
	 */
	protected static function getFilterFields()
	{
		return array(
			array(
				'CODE' => null,
				'VALUE' => 'N',
				'FILTER' => '=CAMPAIGN.IS_TRIGGER'
			),
			array(
				'CODE' => 'IS_ADS',
				'VALUE' => 'Y',
				'FILTER' => '=IS_ADS'
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
		if (!Integration\Seo\Ads\Service::isAvailable())
		{
			$this->addError(Loc::getMessage('SENDER_ENTITY_AD_ERROR_NO_ACCESS'));
			return $id;
		}

		return parent::saveData($id, $data);
	}
}