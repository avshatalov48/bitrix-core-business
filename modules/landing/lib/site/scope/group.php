<?php
namespace Bitrix\Landing\Site\Scope;

use \Bitrix\Landing\Domain;
use \Bitrix\Landing\Manager;
use \Bitrix\Landing\Role;
use \Bitrix\Landing\Site\Scope;

class Group extends Scope
{
	/**
	 * Method for first time initialization scope.
	 * @param array $params Additional params.
	 * @return void
	 */
	public static function init(array $params = [])
	{
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
}