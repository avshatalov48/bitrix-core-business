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
		if (!is_string($type))
		{
			return;
		}

		$id = intval($id);
		$type = trim($type);

		if (!in_array($type, self::$allowedTypes))
		{
			return;
		}

		if (!Rights::hasAccessForSite($id, Rights::ACCESS_TYPES['sett']))
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
					'LANDING_ID' => (int)$lid
				));
			}
		}
		else if ($lid !== false)
		{
			$check = Site::getList([
				'select' => [
					'ID'
				],
				'filter' => [
					'ID' => $id
				]
			])->fetch();
			if ($check)
			{
				SyspageTable::add(array(
					'SITE_ID' => $id,
					'TYPE' => $type,
					'LANDING_ID' => (int)$lid
				));
			}
		}
	}

	/**
	 * Get pages for site.
	 * @param int $id Site id.
	 * @param bool $active Only active items.
	 * @return array
	 */
	public static function get($id, $active = false)
	{
		static $types = array();
		$id = intval($id);

		// check items for un active elements
		$removeHidden = function(array $items) use($active)
		{
			if (!$active)
			{
				return $items;
			}
			foreach ($items as $k => $item)
			{
				if (
					$item['DELETED'] == 'Y' ||
					$item['ACTIVE'] == 'N'
				)
				{
					unset($items[$k]);
				}
			}
			return $items;
		};

		if (
			isset($types[$id])
			&& count($types[$id]) > 0
		)
		{
			return $removeHidden($types[$id]);
		}

		$types[$id] = array();

		$res = SyspageTable::getList(array(
			'select' => array(
				'TYPE',
				'LANDING_ID',
				'TITLE' => 'LANDING.TITLE',
				'DELETED' => 'LANDING.DELETED',
				'ACTIVE' => 'LANDING.ACTIVE'
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

		return $removeHidden($types[$id]);
	}

	/**
	 * Delete all sys pages by site id.
	 * @param int $id Site id.
	 * @return void
	 */
	public static function deleteForSite($id)
	{
		$id = intval($id);
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
	 * @param int $id Landing id.
	 * @return void
	 */
	public static function deleteForLanding($id)
	{
		$id = intval($id);
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

	/**
	 * Get url of special page of site.
	 * @param int $siteId Site id.
	 * @param string $type Type of special page.
	 * @param array $additional Additional params for uri.
	 *
	 * @return string
	 */
	public static function getSpecialPage($siteId, $type, array $additional = [])
	{
		$url = '';
		$siteId = (int)$siteId;

		if (!is_string($type))
		{
			return $url;
		}

		$res = SyspageTable::getList([
			'select' => [
				'LANDING_ID'
			],
			'filter' => [
				'SITE_ID' => $siteId,
				'=TYPE' => $type
			]
		]);
		if ($row = $res->fetch())
		{
			$landing = Landing::createInstance(0);
			$url = $landing->getPublicUrl($row['LANDING_ID']);
			if ($additional)
			{
				$uri = new \Bitrix\Main\Web\Uri($url);
				$uri->addParams($additional);
				$url = $uri->getUri();
				unset($uri);
			}
		}
		unset($row, $res);

		return $url;
	}
}
