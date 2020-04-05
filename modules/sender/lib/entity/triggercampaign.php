<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sender
 * @copyright 2001-2012 Bitrix
 */
namespace Bitrix\Sender\Entity;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\SiteTable;
use Bitrix\Main\Entity\ExpressionField;

use Bitrix\Sender\MailingTable;

Loc::loadMessages(__FILE__);

/**
 * Class Trigger
 * @package Bitrix\Sender\Entity
 */
class TriggerCampaign extends Campaign
{
	/** @var  Chain $chain */
	protected $chain;

	/**
	 * Get default data.
	 *
	 * @return array
	 */
	protected function getDefaultData()
	{
		return [
			'ACTIVE' => 'N',
			'IS_TRIGGER' => 'Y',
			'SITE_ID' => SITE_ID,
		] + parent::getDefaultData();
	}

	/**
	 * Get list.
	 *
	 * @param array $parameters Parameters.
	 * @return \Bitrix\Main\DB\Result
	 */
	public static function getList(array $parameters = [])
	{
		if (!isset($parameters['filter']))
		{
			$parameters['filter'] = [];
		}
		$parameters['filter']['=IS_TRIGGER'] = 'Y';

		return MailingTable::getList($parameters);
	}

	/**
	 * Get chain.
	 *
	 * @return Chain
	 */
	public function getChain()
	{
		if (!$this->chain)
		{
			$this->chain = (new Chain)->load($this->getId());
		}

		return $this->chain;
	}
}