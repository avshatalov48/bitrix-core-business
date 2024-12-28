<?php
namespace Bitrix\Landing;

use \Bitrix\Landing\Internals\TemplateRefTable;

class TemplateRef
{
	/**
	 * Entity type site.
	 */
	const ENTITY_TYPE_SITE = 'S';

	/**
	 * Entity type landing.
	 */
	const ENTITY_TYPE_LANDING = 'L';

	private const CACHE_DIR = '/landing/is_area/';

	/**
	 * Set new template refs for entity.
	 * @param int $id Entity id.
	 * @param string $type Entity type.
	 * @param array $data Ref array.
	 * @return void
	 */
	protected static function set($id, $type, array $data = array())
	{
		$id = intval($id);
		$res = TemplateRefTable::getList(array(
			'select' => array(
				'ID', 'AREA', 'LANDING_ID'
			),
			'filter' => array(
				'ENTITY_ID' => $id,
				'=ENTITY_TYPE' => $type
			)
		));
		while (($row = $res->fetch()))
		{
			if (isset($data[$row['AREA']]) && $data[$row['AREA']] > 0)
			{
				if ($row['LANDING_ID'] != $data[$row['AREA']])
				{
					TemplateRefTable::update($row['ID'], array(
						'LANDING_ID' => $data[$row['AREA']]
					));
					BXClearCache(true, self::CACHE_DIR);
				}
				unset($data[$row['AREA']]);
			}
			else
			{
				TemplateRefTable::delete($row['ID']);
				BXClearCache(true, self::CACHE_DIR);
			}
		}
		foreach ($data as $area => $lid)
		{
			if ($lid > 0)
			{
				TemplateRefTable::add(array(
					'ENTITY_ID' => $id,
					'ENTITY_TYPE' => $type,
					'LANDING_ID' => $lid,
					'AREA' => $area
				));
				BXClearCache(true, self::CACHE_DIR);
			}
		}
	}

	/**
	 * Get template refs for entity.
	 * @param int $id Entity id.
	 * @param string $type Entity type.
	 * @return array
	 */
	protected static function get($id, $type)
	{
		static $staticData = array();
		$id = intval($id);

		if (!isset($staticData[$type . $id]))
		{
			$data = array();
			if ($id > 0)
			{
				$res = TemplateRefTable::getList(array(
					'select' => array(
						'AREA', 'LANDING_ID'
					),
					'filter' => array(
						'ENTITY_ID' => $id,
						'=ENTITY_TYPE' => $type
					)
				));
				while (($row = $res->fetch()))
				{
					$data[$row['AREA']] = $row['LANDING_ID'];
				}
			}

			$staticData[$type . $id] = $data;
		}

		return $staticData[$type . $id];
	}

	/**
	 * Set new template refs for site.
	 * @param int $id Site id.
	 * @param array $data Ref array (area => landing).
	 * @return void
	 */
	public static function setForSite($id, array $data = array())
	{
		if (Rights::hasAccessForSite($id, Rights::ACCESS_TYPES['sett']))
		{
			self::set($id, self::ENTITY_TYPE_SITE, $data);
		}
	}

	/**
	 * Set new template refs for landing.
	 * @param int $id Landing id.
	 * @param array $data Ref array (area => landing).
	 * @return void
	 */
	public static function setForLanding($id, array $data = array())
	{
		if (Rights::hasAccessForLanding($id, Rights::ACCESS_TYPES['sett']))
		{
			self::set($id, self::ENTITY_TYPE_LANDING, $data);
		}
	}

	/**
	 * Get template refs for site.
	 * @param int $id Site id.
	 * @return array
	 */
	public static function getForSite($id)
	{
		return self::get($id, self::ENTITY_TYPE_SITE);
	}

	/**
	 * Get template refs for site.
	 * @param int $id Landing id.
	 * @return array
	 */
	public static function getForLanding($id)
	{
		return self::get($id, self::ENTITY_TYPE_LANDING);
	}

	/**
	 * This landing id is used as a area?
	 *
	 * @param int|array $lid Landing id.
	 *
	 * @return boolean|array
	 */
	public static function landingIsArea(int|array $lid): bool|array
	{
		$cache = new \CPHPCache();
		$cacheTime = 3600;
		$cacheId = is_array($lid) ? md5(serialize($lid)) : (int)$lid;

		if ($cache->InitCache($cacheTime, $cacheId, self::CACHE_DIR))
		{
			$result = $cache->GetVars();
		}
		else
		{
			$cache->StartDataCache();

			$res = TemplateRefTable::getList([
				'filter' => [
					'LANDING_ID' => $lid,
				],
			]);

			if (is_array($lid))
			{
				$result = [];
				foreach ($lid as $id)
				{
					$result[(int)$id] = false;
				}
				while ($row = $res->fetch())
				{
					$result[$row['LANDING_ID']] = true;
				}
			}
			else
			{
				$result = (bool)$res->fetch();
			}

			$cache->EndDataCache($result);
		}

		return $result;
	}

	/**
	 * Delete all area-landing by id.
	 * @param integer $lid Landing id.
	 * @return void
	 */
	public static function deleteArea($lid)
	{
		$lid = intval($lid);

		$res = TemplateRefTable::getList(array(
			'filter' => array(
				'LANDING_ID' => $lid
			)
		));
		while ($row = $res->fetch())
		{
			TemplateRefTable::delete($row['ID']);
			BXClearCache(true, self::CACHE_DIR);
		}
	}

	/**
	 * Resolves class by type.
	 * @param string $type Entity type.
	 * @return string
	 */
	public static function resolveClassByType($type)
	{
		if ($type == self::ENTITY_TYPE_SITE)
		{
			return '\Bitrix\Landing\Site';
		}
		else if ($type == self::ENTITY_TYPE_LANDING)
		{
			return '\Bitrix\Landing\Landing';
		}
		return '';
	}
}
