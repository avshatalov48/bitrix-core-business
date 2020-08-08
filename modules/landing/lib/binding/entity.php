<?php
namespace Bitrix\Landing\Binding;

use \Bitrix\Landing\Internals\BindingTable;
use \Bitrix\Landing\Landing;
use \Bitrix\Landing\Site;
use \Bitrix\Main;

abstract class Entity
{
	/**
	 * Entity type 'SITE'.
	 */
	const ENTITY_TYPE_SITE = 'S';

	/**
	 * Entity type 'LANDING'.
	 */
	const ENTITY_TYPE_LANDING = 'L';

	/**
	 * Binding type, should specified in any child class.
	 * @var null|string
	 */
	protected static $bindingType = null;

	/**
	 * Current binding id.
	 * @var mixed
	 */
	protected $bindingId = null;

	/**
	 * Entity constructor.
	 * @param mixed $code Binding code.
	 */
	public function __construct($code)
	{
		$this->bindingId = $code;
	}

	/**
	 * Returns row id for current binding and entity.
	 * @param int $entityId Entity id.
	 * @param string $entityType Entity type.
	 * @return int
	 */
	private function getBindingId($entityId, $entityType)
	{
		$res = BindingTable::getList([
			'select' => [
				'ID'
			],
			'filter' => [
				'=BINDING_TYPE' => static::$bindingType,
				'=BINDING_ID' => $this->bindingId,
				'=ENTITY_TYPE' => $entityType,
				'=ENTITY_ID' => $entityId
			]
		]);
		if ($row = $res->fetch())
		{
			return $row['ID'];
		}
		else
		{
			return 0;
		}
	}

	/**
	 * Add new binding row.
	 * @param int $entityId Entity id.
	 * @param string $entityType Entity type.
	 * @return int|null New row id.
	 */
	private function addBinding($entityId, $entityType)
	{
		$res = BindingTable::add([
			'BINDING_TYPE' => static::$bindingType,
			'BINDING_ID' => $this->bindingId,
			'ENTITY_TYPE' => $entityType,
			'ENTITY_ID' => $entityId
		]);
		if ($res->isSuccess())
		{
			return $res->getId();
		}
		else
		{
			return null;
		}
	}

	/**
	 * Delete binding row by id.
	 * @param int $bindingId Row id.
	 * @return void
	 */
	private function deleteBindingId($bindingId)
	{
		$res = BindingTable::delete($bindingId);
		$res->isSuccess();
	}

	/**
	 * Bind entity for current binding type/id.
	 * @param int $entityId Entity id.
	 * @param string $entityType Entity type.
	 * @return bool Success adding.
	 */
	private function bind($entityId, $entityType)
	{
		if (!$this->getBindingId($entityId, $entityType))
		{
			return $this->addBinding($entityId, $entityType) > 0;
		}
		else
		{
			return false;
		}
	}

	/**
	 * Unbind entity for current binding type/id.
	 * @param int $entityId Entity id.
	 * @param string $entityType Entity type.
	 * @return bool Success on exist.
	 */
	private function unbind($entityId, $entityType)
	{
		$bindingId = $this->getBindingId($entityId, $entityType);
		if ($bindingId)
		{
			$this->deleteBindingId($bindingId);
			return true;
		}
		else
		{
			return false;
		}
	}

	/**
	 * Local method for selecting binding entities.
	 * @param mixed $bindingId Binding code.
	 * @return array
	 */
	private static function getBindings($bindingId)
	{
		$items = [];

		// filter
		$filter = [
			'=BINDING_TYPE' => static::$bindingType,
			'=ENTITY_TYPE' => [
				self::ENTITY_TYPE_SITE,
				self::ENTITY_TYPE_LANDING
			]
		];
		if ($bindingId !== null)
		{
			$filter['=BINDING_ID'] = $bindingId;
		}

		// runtime fields
		$runtime = [];
		$runtime[] = new Main\Entity\ReferenceField(
			'SITE',
			'Bitrix\Landing\Internals\SiteTable',
			[
				'=this.ENTITY_ID' => 'ref.ID',
				'=this.ENTITY_TYPE' => [
					'?', self::ENTITY_TYPE_SITE
				]
			]
		);
		$runtime[] = new Main\Entity\ReferenceField(
			'LANDING',
			'Bitrix\Landing\Internals\LandingTable',
			[
				'=this.ENTITY_ID' => 'ref.ID',
				'=this.ENTITY_TYPE' => [
					'?', self::ENTITY_TYPE_LANDING
				]
			]
		);

		// selecting
		$urls = [
			self::ENTITY_TYPE_SITE => [],
			self::ENTITY_TYPE_LANDING => []
		];
		$res = BindingTable::getList([
			'select' => [
				'ENTITY_ID',
				'ENTITY_TYPE',
				'BINDING_ID',
				'SITE_TITLE' => 'SITE.TITLE',
				'LANDING_TITLE' => 'LANDING.TITLE',
				'SITE_DELETED' => 'SITE.DELETED',
				'LANDING_DELETED' => 'LANDING.DELETED'
			],
			'filter' => $filter,
			'runtime' => $runtime,
			'order' => [
				'ID' => 'desc'
			]
		]);
		while ($row = $res->fetch())
		{
			if (
				$row['SITE_DELETED'] == 'Y' ||
				$row['LANDING_DELETED'] == 'Y'
			)
			{
				continue;
			}
			$urls[$row['ENTITY_TYPE']][] = $row['ENTITY_ID'];
			$title = ($row['ENTITY_TYPE'] == self::ENTITY_TYPE_SITE)
					? $row['SITE_TITLE']
					: $row['LANDING_TITLE'];
			if (!$title)
			{
				continue;
			}
			$items[] = [
				'ENTITY_ID' => $row['ENTITY_ID'],
				'ENTITY_TYPE' => $row['ENTITY_TYPE'],
				'BINDING_ID' => $row['BINDING_ID'],
				'TITLE' => $title,
				'PUBLIC_URL' => '',
			];
		}

		// get urls
		if ($urls[self::ENTITY_TYPE_SITE])
		{
			$urls[self::ENTITY_TYPE_SITE] = Site::getPublicUrl(
				$urls[self::ENTITY_TYPE_SITE]
			);
		}
		if ($urls[self::ENTITY_TYPE_LANDING])
		{
			$landing = Landing::createInstance(0);
			$urls[self::ENTITY_TYPE_LANDING] = $landing->getPublicUrl(
				$urls[self::ENTITY_TYPE_LANDING]
			);
		}

		// rebuild for urls
		foreach ($items as &$item)
		{
			if (isset($urls[$item['ENTITY_TYPE']][$item['ENTITY_ID']]))
			{
				$item['PUBLIC_URL'] = $urls[$item['ENTITY_TYPE']][$item['ENTITY_ID']];
			}
		}
		unset($item);

		return $items;
	}

	/**
	 * Returns binding list of entities by specified binding type.
	 * @param mixed $code Binding code.
	 * @return array
	 */
	public static function getList($code = null)
	{
		return self::getBindings($code);
	}

	/**
	 * Bind site for current entity.
	 * @param int $siteId Site id.
	 * @return void
	 */
	public function bindSite($siteId)
	{
		$siteId = intval($siteId);

		$success = $this->bind($siteId, $this::ENTITY_TYPE_SITE);

		if ($success && method_exists($this, 'addSiteRights'))
		{
			$this->addSiteRights($siteId);
		}
	}

	/**
	 * Unbind site for current entity.
	 * @param int $siteId Site id.
	 * @return void
	 */
	public function unbindSite($siteId)
	{
		$siteId = intval($siteId);

		$success = $this->unbind($siteId, $this::ENTITY_TYPE_SITE);

		if ($success && method_exists($this, 'removeSiteRights'))
		{
			$this->removeSiteRights($siteId);
		}
	}

	/**
	 * Bind landing for current entity.
	 * @param int $landingId Landing id.
	 * @return void
	 */
	public function bindLanding($landingId)
	{
		$landingId = intval($landingId);

		$success = $this->bind($landingId, $this::ENTITY_TYPE_LANDING);

		if ($success && method_exists($this, 'addLandingRights'))
		{
			$this->addLandingRights($landingId);
		}
	}

	/**
	 * Unbind landing for current entity.
	 * @param int $landingId Landing id.
	 * @return void
	 */
	public function unbindLanding($landingId)
	{
		$landingId = intval($landingId);

		$success = $this->unbind($landingId, $this::ENTITY_TYPE_LANDING);

		if ($success && method_exists($this, 'removeLandingRights'))
		{
			$this->removeLandingRights($landingId);
		}
	}
}
