<?php
namespace Bitrix\Landing;

use \Bitrix\Landing\Internals\SyspageTable;

class Syspage
{
	/**
	 * Allowed types.
	 * @var array
	 */
	protected static $allowedTypes = array(
		'mainpage',
		'catalog',
		'personal',
		'cart',
		'order',
		'payment',
		'compare'
	);

	/**
	 * Set new system page for site.
	 * @param int $id Site id.
	 * @param string $type System page type.
	 * @param int $lid Landing id (if not set, ref was deleted).
	 * @return void
	 */
	public static function set($id, $type, $lid = false)
	{
		$type = trim($type);
		if (!in_array($type, self::$allowedTypes))
		{
			return;
		}

		$res = SyspageTable::getList(array(
			'select' => array(
				'ID'
			),
			'filter' => array(
				'SITE_ID' => $id,
				'=TYPE' => $type
			)
		));
		if ($row = $res->fetch())
		{
			if ($lid === false)
			{
				SyspageTable::delete($row['ID']);
			}
			else
			{
				SyspageTable::update($row['ID'], array(
					'LANDING_ID' => $lid
				));
			}
		}
		else if ($lid !== false)
		{
			SyspageTable::add(array(
				'SITE_ID' => $id,
				'TYPE' => $type,
				'LANDING_ID' => $lid
			));
		}
	}

	/**
	 * Get pages for site.
	 * @param integer $id Site id.
	 * @return array
	 */
	public static function get($id)
	{
		static $types = array();

		if (isset($types[$id]))
		{
			return $types[$id];
		}

		$types[$id] = array();

		$res = SyspageTable::getList(array(
			'select' => array(
				'TYPE',
				'LANDING_ID',
				'TITLE' => 'LANDING.TITLE'
			),
			'filter' => array(
				'SITE_ID' => $id
			),
			'runtime' => array(
				new \Bitrix\Main\Entity\ReferenceField(
					'LANDING',
					'\Bitrix\Landing\Internals\LandingTable',
					array('=this.LANDING_ID' => 'ref.ID')
				)
			)
		));
		while ($row = $res->fetch())
		{
			$types[$id][$row['TYPE']] = $row;
		}

		return $types[$id];
	}

	/**
	 * Delete all sys pages by site id.
	 * @param integer $id Site id.
	 * @return void
	 */
	public static function deleteForSite($id)
	{
		$res = SyspageTable::getList(array(
			'filter' => array(
				'SITE_ID' => $id
			)
		));
		while ($row = $res->fetch())
		{
			SyspageTable::delete($row['ID']);
		}
	}

	/**
	 * Delete all sys pages by id.
	 * @param integer $id Landing id.
	 * @return void
	 */
	public static function deleteForLanding($id)
	{
		$res = SyspageTable::getList(array(
			'filter' => array(
				'LANDING_ID' => $id
			)
		));
		while ($row = $res->fetch())
		{
			SyspageTable::delete($row['ID']);
		}
	}
}