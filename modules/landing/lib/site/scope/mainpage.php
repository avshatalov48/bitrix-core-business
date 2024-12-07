<?php
namespace Bitrix\Landing\Site\Scope;

use Bitrix\Landing\Block\BlockRepo;
use Bitrix\Landing\Role;
use Bitrix\Landing\Manager;
use Bitrix\Landing\Domain;
use Bitrix\Landing\Site\Scope;
use Bitrix\Main\Entity;
use Bitrix\Main\Event;
use Bitrix\Main\EventManager;

/**
 * Scope for Main page (welcome)
 */
class Mainpage extends Scope
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

		$eventManager = EventManager::getInstance();
		$eventManager->addEventHandler(
			'landing',
			'onBlockRepoSetFilters',
			function(Event $event)
			{
				$result = new Entity\EventResult();
				$result->modifyFields([
					'ENABLE' => BlockRepo::FILTER_SKIP_COMMON_BLOCKS,
					'DISABLE' => BlockRepo::FILTER_SKIP_HIDDEN_BLOCKS,
				]);

				return $result;
			}
		);
	}

	/**
	 * Returns publication path string.
	 * @return string
	 */
	public static function getPublicationPath()
	{
		return '/vibe/';
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
		// todo: anything else?
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
			'COOKIES',
		];
	}

	/**
	 * Change manifest field by special conditions of site type
	 * @param array $manifest
	 * @return array prepared manifest
	 */
	public static function prepareBlockManifest(array $manifest): array
	{
		$allowedManifestKeys = [
			'block',
			'nodes',
			'style',
			'assets',
			'callbacks',
		];
		$manifest = array_filter(
			$manifest,
			function ($key) use ($allowedManifestKeys) {
				return in_array(mb_strtolower($key), $allowedManifestKeys);
			},
			ARRAY_FILTER_USE_KEY
		);

		$manifest['block']['type'] = (array)$manifest['block']['type'];

		// not all assets allowed
		if (isset($manifest['assets']))
		{
			$allowedExt = [
				'landing.widgetvue',
				'landing_inline_video',
			];
			$manifest['assets'] = [
				'ext' => array_filter(
					(array)$manifest['assets']['ext'],
					function ($item) use ($allowedExt)
					{
						return in_array(mb_strtolower($item), $allowedExt);
					}
				),
			];

			if (empty($manifest['assets']['ext']))
			{
				unset($manifest['assets']);
			}
		}

		// unset not allowed subtypes
		if (isset($manifest['block']['subtype']))
		{
			$allowedSubtypes = [
				'widgetvue',
			];
			$manifest['block']['subtype'] = array_filter(
				(array)$manifest['block']['subtype'],
				function ($item) use ($allowedSubtypes) {
					return in_array(mb_strtolower($item), $allowedSubtypes);
				}
			);
		}
		if (empty($manifest['block']['subtype']))
		{
			unset($manifest['block']['subtype'], $manifest['block']['subtype_params']);
		}

		// unset not allowed callbacks
		if (isset($manifest['callbacks']))
		{
			$allowedCallbacks = [
				'afteradd',
				'beforeview',
			];
			$manifest['callbacks'] = array_filter(
				(array)$manifest['callbacks'],
				function ($item) use ($allowedCallbacks) {
					return in_array(mb_strtolower($item), $allowedCallbacks);
				},
				ARRAY_FILTER_USE_KEY
			);

			if (empty($manifest['callbacks']))
			{
				unset($manifest['callbacks']);
			}
		}
		
		//unset not allowed style
		$allowedStyles = [
			//for landing block
			'background',
			'color',
			'background-color',
			'padding-top',
			'padding-bottom',
			'padding-left',
			'padding-right',
			'margin-top',
			'margin-bottom',
			'margin-left',
			'margin-right',
			'text-align',
			//for widget
			'widget',
			'widget-type',
			//for separators
			'fill-first',
			'fill-second',
			'height-increased--md',
		];

		if (isset($manifest['style']['block']['type']))
		{
			$manifest['style']['block']['type'] = (array)$manifest['style']['block']['type'];
			$manifest['block']['section'] = (array)$manifest['block']['section'];
			$filtered = array_intersect($manifest['style']['block']['type'], $allowedStyles);
			$manifest['style']['block']['type'] = array_values($filtered);
			if (
				!in_array('widget-type', $manifest['style']['block']['type'], true)
				&& !in_array('widgets_separators', $manifest['block']['section'], true)
			)
			{
				$manifest['style']['block']['type'][] = 'widget-type';
			}
		}

		foreach (($manifest['style']['nodes'] ?? []) as &$node)
		{
			$node['type'] = (array)$node['type'];
			$node['type'] = array_values(array_intersect($node['type'], $allowedStyles));
		}
		unset($node);

		// if manifest not exist in style sections block and nodes
		if (
			isset($manifest['style'])
			&& !isset($manifest['style']['block'])
			&& !isset($manifest['style']['nodes'])
		)
		{
			foreach ($manifest['style'] as &$node)
			{
				$node['type'] = (array)$node['type'];
				$node['type'] = array_values(array_intersect($node['type'], $allowedStyles));
			}
			unset($node);
		}

		return $manifest;
	}
}