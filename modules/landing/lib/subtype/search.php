<?php
namespace Bitrix\Landing\Subtype;

use \Bitrix\Landing\Landing;
use \Bitrix\Landing\Block;

class Search
{
	/**
	 * Returns search result page in block's site.
	 * @param int $siteId Site id.
	 * @param mixed $tplCode Landing template code.
	 * @return int
	 */
	public static function getSearchResultPage($siteId, $tplCode)
	{
		$siteId = (int) $siteId;
		$tplCode = (string) $tplCode;

		$res = Landing::getList([
			'select' => [
				'ID'
			],
			'filter' => [
				'SITE_ID' => $siteId,
				'=TPL_CODE' => $tplCode
			]
		]);
		if ($row = $res->fetch())
		{
			$landingId = $row['ID'];
		}
		else
		{
			$res = \Bitrix\Landing\PublicAction\Landing::addByTemplate(
				$siteId,
				$tplCode
			);
			$landingId = $res->getResult();
		}

		return $landingId;
	}

	/**
	 * Prepares manifest only for search forms.
	 * @param array $manifest Block's manifest.
	 * @param array $params Additional params.
	 * @return array Manifest.
	 */
	protected static function prepareForm(array $manifest, array $params = [])
	{
		if (\Bitrix\Landing\Transfer\AppConfiguration::inProcess())
		{
			return $manifest;
		}

		// necessary params
		if (!isset($params['resultPage']))
		{
			return $manifest;
		}

		// force set action attribute for form after block add
		$manifest['callbacks'] = array(
			'afterAdd' => function (Block &$block) use($params)
			{
				$manifest = $block->getManifest();
				$landingId = Search::getSearchResultPage(
					$block->getSiteId(),
					$params['resultPage']
				);

				if (!$landingId)
				{
					return;
				}

				// try to find url attrs and set default value
				$attributeSelector = '';
				foreach ($manifest['attrs'] as $selector => $item)
				{
					if (
						$item['type'] == 'url' &&
						$item['attribute'] == 'action'
					)
					{
						$attributeSelector = $selector;
						break;
					}
				}

				$block->setAttributes([
					$attributeSelector => [
						'action' => '#landing' . $landingId
					]
				]);
				$block->save();
			},
		);

		return $manifest;
	}

	/**
	 * Prepares manifest.
	 * @param array $manifest Block's manifest.
	 * @param Block $block Block instance.
	 * @param array $params Additional params.
	 * @return array Manifest.
	 */
	public static function prepareManifest(array $manifest, Block $block = null, array $params = [])
	{
		if (!isset($params['type']))
		{
			return $manifest;
		}

		if ($params['type'] == 'form')
		{
			return self::prepareForm($manifest, $params);
		}

		return $manifest;
	}
}