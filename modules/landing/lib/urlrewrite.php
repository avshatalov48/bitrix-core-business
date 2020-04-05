<?php
namespace Bitrix\Landing;

use Bitrix\Landing\Internals\SiteTable;
use \Bitrix\Landing\Internals\UrlRewriteTable;

class UrlRewrite
{
	/**
	 * Set rule for the site.
	 * @param int $siteId Site id.
	 * @param string $rule Rule.
	 * @param int $landingId Landing id. If empty then remove.
	 * @return void
	 */
	public static function set($siteId, $rule, $landingId = null)
	{
		$rule = trim($rule);

		// check for exist
		$check = SiteTable::getList([
			'select' => [
				'ID'
			],
			'filter' => [
				'ID' => $siteId,
				'=DELETED' => ['Y', 'N']
			]
		])->fetch();
		if (!$check)
		{
			return;
		}
		if ($landingId)
		{
			$check = SiteTable::getList([
				'select' => [
					'ID'
				],
				'filter' => [
					'ID' => $landingId,
					'=DELETED' => ['Y', 'N']
				]
			])->fetch();
			if (!$check)
			{
				return;
			}
		}

		// set or unset
		$filter = [
			'SITE_ID' => $siteId,
			'=RULE' => $rule
		];
		if ($landingId)
		{
			$filter['!LANDING_ID'] = $landingId;
		}

		$res = UrlRewriteTable::getList([
			'select' => [
				'ID'
			],
			'filter' => $filter
		]);
		if ($row = $res->fetch())
		{
			if ($landingId)
			{
				UrlRewriteTable::update($row['ID'], [
					'LANDING_ID' => $landingId
				]);
			}
			else
			{
				UrlRewriteTable::delete(
					$row['ID']
				);
			}
		}
		else if ($landingId)
		{
			UrlRewriteTable::add([
				'SITE_ID' => $siteId,
				'RULE' => $rule,
				'LANDING_ID' => $landingId
			]);
		}
	}

	/**
	 * Unset rule for the site.
	 * @param int $siteId Site id.
	 * @param string $rule Rule.
	 * @return void
	 */
	public static function remove($siteId, $rule)
	{
		self::set($siteId, $rule);
	}

	/**
	 * Matching rule for url.
	 * @param int $siteId Site id.
	 * @param string $url Some url.
	 * @return int Landing id.
	 */
	public static function matchUrl($siteId, $url)
	{
		//
	}

	/**
	 * Clear rules for one site.
	 * @param int $siteId Site id.
	 * @return void
	 */
	public static function removeForSite($siteId)
	{
		$res = UrlRewriteTable::getList([
			'select' => [
				'ID'
			],
			'filter' => [
				'SITE_ID' => $siteId
			]
		]);
		while ($row = $res->fetch())
		{
			UrlRewriteTable::delete($row['ID']);
		}
	}

	/**
	 * Clear rules for one site.
	 * @param int $landingId Landing id.
	 * @return void
	 */
	public static function removeForLanding($landingId)
	{
		$res = UrlRewriteTable::getList([
			'select' => [
				'ID'
			],
			'filter' => [
				'LANDING_ID' => $landingId
			]
		]);
		while ($row = $res->fetch())
		{
			UrlRewriteTable::delete($row['ID']);
		}
	}
}