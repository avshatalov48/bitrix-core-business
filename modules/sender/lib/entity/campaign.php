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
 * Class Campaign
 * @package Bitrix\Sender\Entity
 */
class Campaign extends Base
{
	/** @var integer $defaultId Default ID. */
	private static $defaultId;

	/**
	 * Get list.
	 *
	 * @param array $parameters Parameters.
	 * @return \Bitrix\Main\DB\Result
	 */
	public static function getList(array $parameters = array())
	{
		if (!isset($parameters['filter']))
		{
			$parameters['filter'] = [];
		}
		if (!isset($parameters['select']))
		{
			$parameters['select'] = ['*', 'SUBSCRIBER_COUNT'];
		}
		$parameters['filter']['=IS_TRIGGER'] = 'N';
		if (in_array('SUBSCRIBER_COUNT', $parameters['select']))
		{
			$parameters['runtime'][] = new ExpressionField('SUBSCRIBER_COUNT', 'COUNT(DISTINCT %s)', 'SUBSCRIBER.CONTACT_ID');
		}

		return MailingTable::getList($parameters);
	}

	/**
	 * Get default campaign id.
	 *
	 * @return int
	 */
	public static function getDefaultId()
	{
		if (self::$defaultId)
		{
			return self::$defaultId;
		}

		$row = MailingTable::getRow(array(
			'select' => array('ID'),
			'filter' => array('=ACTIVE' => 'Y', '=IS_TRIGGER' => 'N'),
			'limit' => 1,
			'order' => array('ID' => 'DESC')
		));
		if ($row)
		{
			self::$defaultId = $row['ID'];
			return self::$defaultId;
		}

		$site = SiteTable::getRow(['select' => ['ID' => 'LID'], 'filter' => ['=DEF' => 'Y']]);
		$result = MailingTable::add(array(
			'NAME' => Loc::getMessage('SENDER_ENTITY_CAMPAIGN_NAME_DEFAULT'),
			'SITE_ID' => ($site ? $site['ID'] : SITE_ID)
		));
		if ($result->isSuccess())
		{
			self::$defaultId = $result->getId();
		}

		return self::$defaultId;
	}

	/**
	 * Get sites.
	 *
	 * @return array
	 */
	public static function getSites()
	{
		static $sites = null;
		if ($sites === null)
		{
			$sites = SiteTable::getList(['select' => ['LID', 'NAME']])->fetchAll();
			$sites = array_combine(
				array_column($sites, 'LID'),
				array_column($sites, 'NAME')
			);
		}

		return $sites;
	}

	/**
	 * Get default data.
	 *
	 * @return array
	 */
	protected function getDefaultData()
	{
		return [
			'NAME' => '',
			'SITE_ID' => SITE_ID,
			'ACTIVE' => 'Y',
			'IS_PUBLIC' => 'Y',
		];
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
		$this->filterDataByEntityFields(MailingTable::getEntity(), $data);
		return $this->saveByEntity(MailingTable::getEntity(), $id, $data);
	}

	/**
	 * Load data.
	 *
	 * @param integer $id ID.
	 * @return array|null
	 */
	public function loadData($id)
	{
		return static::getList(['filter' => ['=ID' => $id], 'limit' => 1])->fetch();
	}

	/**
	 * Get site ID.
	 *
	 * @return string
	 */
	public function getSiteId()
	{
		return $this->get('SITE_ID') ?: SITE_ID;
	}

	/**
	 * Get site name.
	 *
	 * @return string
	 */
	public function getSiteName()
	{
		$sites = self::getSites();
		return $this->get('SITE_ID') ? $sites[$this->get('SITE_ID')] : null;
	}

	/**
	 * Get subscriber count.
	 *
	 * @return int
	 */
	public function getSubscriberCount()
	{
		return (int) $this->get('SUBSCRIBER_COUNT') ?: 0;
	}

	/**
	 * Activate.
	 *
	 * @param bool $isActivate Is activate.
	 * @return $this
	 */
	public function activate($isActivate = true)
	{
		$this->set('ACTIVE', $isActivate ? 'Y' : 'N');
		$this->save();

		/*
		$result = MailingTable::update($this->getId(), ['ACTIVE' => $isActivate ? 'Y' : 'N']);
		if ($result->isSuccess())
		{
			$this->set('ACTIVE', 'Y');
		}
		else
		{
			$this->errors->add($result->getErrors());
		}
		*/

		return $this;
	}

	/**
	 * Deactivate.
	 *
	 * @return $this
	 */
	public function deactivate()
	{
		$this->activate(false);
		return $this;
	}

	/**
	 * Remove.
	 *
	 * @return bool
	 */
	public function remove()
	{
		return $this->removeByEntity(MailingTable::getEntity(), $this->getId());
	}

	/**
	 * Remove by contact ID.
	 *
	 * @param integer $id Contact ID.
	 * @return bool
	 */
	public static function removeById($id)
	{
		return static::create()->removeByEntity(MailingTable::getEntity(), $id);
	}
}