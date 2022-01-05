<?php
namespace Bitrix\Landing\Site\Scope;

use Bitrix\Landing\Domain;
use Bitrix\Landing\Internals\BindingTable;
use Bitrix\Landing\Manager;
use Bitrix\Landing\Role;
use Bitrix\Landing\Restriction;
use Bitrix\Landing\Site\Scope;
use Bitrix\Main\Loader;

class Group extends Scope
{
	/**
	 * Method for first time initialization scope.
	 * @param array $params Additional params.
	 * @return void
	 */
	public static function init(array $params = [])
	{
		if (!Restriction\Manager::isAllowed('limit_crm_free_knowledge_base_project'))
		{
			return;
		}
		parent::init($params);
		Role::setExpectedType(self::$currentScopeId);
	}

	/**
	 * Returns publication path string.
	 * @return string
	 */
	public static function getPublicationPath()
	{
		if (\Bitrix\Landing\Connector\Mobile::isMobileHit())
		{
			return '/mobile/knowledge/group/';
		}
		else
		{
			return '/knowledge/group/';
		}
	}

	/**
	 * Return general key for site path.
	 * @return string
	 */
	public static function getKeyCode()
	{
		return 'CODE';
	}

	/**
	 * Returns domain id for new site.
	 * @return int
	 */
	public static function getDomainId()
	{
		if (!Manager::isB24())
		{
			return Domain::getCurrentId();
		}
		return 0;
	}

	/**
	 * Returns filter value for 'TYPE' key.
	 * @return string
	 */
	public static function getFilterType()
	{
		return self::getCurrentScopeId();
	}

	/**
	 * Returns array of hook's codes, which excluded by scope.
	 * @return array
	 */
	public static function getExcludedHooks(): array
	{
		return [
			'B24BUTTON',
			'COPYRIGHT',
			'CSSBLOCK',
			'FAVICON',
			'GACOUNTER',
			'GTM',
			'HEADBLOCK',
			'METAGOOGLEVERIFICATION',
			'METAMAIN',
			'METAROBOTS',
			'METAYANDEXVERIFICATION',
			'PIXELFB',
			'PIXELVK',
			'ROBOTS',
			'SETTINGS',
			'SPEED',
			'YACOUNTER',
			'COOKIES'
		];
	}

	/**
	 * If for site id exists group, then returns group id.
	 * @param int $siteId Site id.
	 * @param bool $checkAccess If true check access to binding group.
	 * @return int
	 */
	public static function getGroupIdBySiteId(int $siteId, bool $checkAccess = false): ?int
	{
		$res = BindingTable::getList([
			'select' => [
				'BINDING_ID'
			],
			'filter' => [
				'=ENTITY_TYPE' => BindingTable::ENTITY_TYPE_SITE,
				'=BINDING_TYPE' => 'G',
				'ENTITY_ID' => $siteId
			]
		]);
		if ($row = $res->fetch())
		{
			$groupId = (int) $row['BINDING_ID'];
			if ($checkAccess && Loader::includeModule('socialnetwork'))
			{
				if (!\CSocNetGroup::canUserReadGroup(Manager::getUserId(), $groupId))
				{
					return null;
				}
			}
			return $groupId;
		}

		return null;
	}
}