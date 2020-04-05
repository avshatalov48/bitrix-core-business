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

	/**
	 * Set new template refs for entity.
	 * @param int $id Entity id.
	 * @param string $type Entity type.
	 * @param array $data Ref array.
	 * @return void
	 */
	protected static function set($id, $type, array $data = array())
	{
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
				}
				unset($data[$row['AREA']]);
			}
			else
			{
				TemplateRefTable::delete($row['ID']);
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
		self::set($id, self::ENTITY_TYPE_SITE, $data);
	}

	/**
	 * Set new template refs for landing.
	 * @param int $id Landing id.
	 * @param array $data Ref array (area => landing).
	 * @return void
	 */
	public static function setForLanding($id, array $data = array())
	{
		self::set($id, self::ENTITY_TYPE_LANDING, $data);
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
	 * @param int|array $lid Landing id.
	 * @return boolean|array
	 */
	public static function landingIsArea($lid)
	{
		$res = TemplateRefTable::getList(array(
			'filter' => array(
				'LANDING_ID' => $lid
			)
		));
		if (is_array($lid))
		{
			$return = array();
			foreach ($lid as $id)
			{
				$return[$id] = false;
			}
			while ($row = $res->fetch())
			{
				$return[$row['LANDING_ID']] = true;
			}
			return $return;
		}
		else
		{
			return $res->fetch() ? true : false;
		}
	}

	/**
	 * Delete all area-landing by id.
	 * @param integer $lid Landing id.
	 * @return void
	 */
	public static function deleteArea($lid)
	{
		$res = TemplateRefTable::getList(array(
			'filter' => array(
				'LANDING_ID' => $lid
			)
		));
		while ($row = $res->fetch())
		{
			TemplateRefTable::delete($row['ID']);
		}
	}
}