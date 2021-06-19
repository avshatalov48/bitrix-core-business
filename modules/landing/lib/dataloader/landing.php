<?php
namespace Bitrix\Landing\DataLoader;

use \Bitrix\Landing\Block;
use \Bitrix\Landing\Hook;
use \Bitrix\Landing\Landing as LandingCore;
use \Bitrix\Landing\Landing\Cache;

class Landing extends \Bitrix\Landing\Source\DataLoader
{
	/**
	 * Gets all meta data for landing's ids data.
	 * @param array $ids
	 * @return array
	 */
	protected function getMetadata(array $ids)
	{
		$images = [];
		$res = \Bitrix\Landing\Internals\HookDataTable::getList([
			'select' => [
				'VALUE', 'ENTITY_ID', 'CODE'
			],
			'filter' => [
				'=HOOK' => 'METAOG',
				'=CODE' => ['IMAGE', 'DESCRIPTION'],
				'=PUBLIC' => Hook::getEditMode() ? 'N' : 'Y',
				'=ENTITY_TYPE' => Hook::ENTITY_TYPE_LANDING,
				'ENTITY_ID' => $ids
			]
		]);
		while ($row = $res->fetch())
		{
			if (!isset($images[$row['ENTITY_ID']]))
			{
				$images[$row['ENTITY_ID']] = [];
			}
			if ($row['CODE'] == 'IMAGE')
			{
				if (intval($row['VALUE']) > 0)
				{
					$row['VALUE'] = \Bitrix\Landing\File::getFilePath($row['VALUE']);
				}
			}
			$images[$row['ENTITY_ID']][$row['CODE']] = $row['VALUE'];
		}

		return $images;
	}

	/**
	 * Returns search parameter value.
	 * @return string
	 */
	protected function getSearchQuery()
	{
		static $currentRequest = null;

		if ($currentRequest === null)
		{
			$context = \Bitrix\Main\Application::getInstance()->getContext();
			$currentRequest = $context->getRequest();
		}

		return trim($currentRequest->get('q'));
	}

	/**
	 * Returns search snippet for each result item.
	 * @param string $query Search query.
	 * @param string $content Full search content.
	 * @return string
	 */
	protected function getSearchSnippet(string $query, string $content): string
	{
		$isUtf = defined('BX_UTF') && BX_UTF === true;

		if (!$isUtf)
		{
			[$content, $query] = \Bitrix\Main\Text\Encoding::convertEncoding(
				[$content, $query], SITE_CHARSET, 'UTF-8'
			);
		}

		$phrases = explode(' ', $query);
		\trimArr($phrases, true);
		$newContent = '';
		$snippetOffset = 50;

		foreach ($phrases as $phrase)
		{
			$phrasePos = mb_strpos(mb_strtolower($content), mb_strtolower($phrase));
			if ($phrasePos === false)
			{
				continue;
			}
			$newContent .= (($phrasePos > $snippetOffset) ? ' ...' : ' ') .
							mb_substr(
								$content,
								($phrasePos > $snippetOffset) ? $phrasePos - $snippetOffset : 0,
								$phrasePos + mb_strlen($phrase) + $snippetOffset
							) .
							'... ';
		}

		if (!$isUtf)
		{
			$newContent = \Bitrix\Main\Text\Encoding::convertEncoding(
				$newContent, 'UTF-8', SITE_CHARSET
			);
		}

		return $newContent;
	}

	/**
	 * Gets data for dynamic blocks.
	 * @return array
	 */
	public function getElementListData()
	{
		$this->seo->clear();
		$needPreviewPicture = false;
		$needPreviewText = false;
		$needLink = true;//always

		// select
		$select = $this->getPreparedSelectFields();
		if (empty($select))
		{
			return [];
		}

		// filter
		$filter = $this->getInternalFilter();
		$contextFilter = $this->getOptionsValue('context_filter');
		$cache = $this->getOptionsValue('cache');
		if (empty($filter))
		{
			$filter = [];
		}
		if (isset($contextFilter['SITE_ID']))
		{
			$filter['SITE_ID'] = $contextFilter['SITE_ID'];
		}
		if (isset($contextFilter['LANDING_ACTIVE']))
		{
			$filter['=ACTIVE'] = $contextFilter['LANDING_ACTIVE'];
		}
		$filter['==AREAS.ID'] = null;

		// select, order
		$order = [];
		$select[] = 'ID';
		$rawOrder = $this->getOrder();
		if (isset($rawOrder['by']) && isset($rawOrder['order']))
		{
			$order[$rawOrder['by']] = $rawOrder['order'];
			if (!in_array($rawOrder['by'], $select))
			{
				$select[] = $rawOrder['by'];
			}
		}
		foreach ($select as $i => $code)
		{
			if ($code == 'IMAGE')
			{
				$needPreviewPicture = true;
				unset($select[$i]);
			}
			else if ($code == 'DESCRIPTION')
			{
				$needPreviewText = true;
				unset($select[$i]);
			}
			else if ($code == 'LINK')
			{
				$needLink = true;
				unset($select[$i]);
			}
		}

		// limit
		$limit = $this->getLimit();
		if ($limit <= 0)
		{
			$limit = 10;
		}

		$searchContent = [];
		$query = $this->getSearchQuery();
		if ($query)
		{
			if ($cache instanceof \CPHPCache)
			{
				$cache->abortDataCache();
			}

			if (mb_strlen($query) < 3)
			{
				return [];
			}

			// search in blocks
			$blockFilter = [];
			if (isset($filter['SITE_ID']))
			{
				$blockFilter['LANDING.SITE_ID'] = $filter['SITE_ID'];
			}
			$blocks = Block::search(
				$query,
				$blockFilter,
				['LID', 'ID', 'SEARCH_CONTENT']
			);
			$landingBlocksIds = [];
			foreach ($blocks as $block)
			{
				$searchContent[$block['LID']] = $this->getSearchSnippet($query, $block['SEARCH_CONTENT']);
				if (!$searchContent[$block['LID']])
				{
					unset($searchContent[$block['LID']]);
				}
				$landingBlocksIds[] = $block['LID'];
			}

			// merge filter with search query
			$filter[] = [
				'LOGIC' => 'OR',
				'TITLE' => '%' . $query . '%',
				'*%SEARCH_CONTENT' => $query,
				'ID' => $landingBlocksIds ? $landingBlocksIds : [-1]
			];
		}

		// get data
		$result = [];
		$res = LandingCore::getList([
			'select' => $select,
			'filter' => $filter,
			'order' => $order,
			'limit' => $limit
		]);
		while ($row = $res->fetch())
		{
			Cache::register($row['ID']);
			$result[$row['ID']] = [
				'TITLE' => \htmlspecialcharsbx($row['TITLE'])
			];
		}

		// get meta data
		$metaData = [];
		if (
			$needPreviewPicture ||
			$needPreviewText
		)
		{
			$metaData = $this->getMetadata(
				array_keys($result)
			);
		}

		// and feel result data with meta data
		foreach ($result as $id => &$item)
		{
			if (
				$needPreviewPicture &&
				isset($metaData[$id]['IMAGE'])
			)
			{
				$item['IMAGE'] = [
					'src' => $metaData[$id]['IMAGE'],
					'alt' => $item['TITLE'] ?? ''
				];
			}
			if ($needPreviewText)
			{
				if (isset($searchContent[$id]))
				{
					$item['DESCRIPTION'] = \htmlspecialcharsbx($searchContent[$id]);
				}
				else if (isset($metaData[$id]['DESCRIPTION']))
				{
					$item['DESCRIPTION'] = \htmlspecialcharsbx($metaData[$id]['DESCRIPTION']);
				}
			}
			if ($needLink)
			{
				$item['LINK'] = '#landing' . $id;
			}
			if ($query)
			{
				$item['_GET'] = [
					'q' => $query
				];
			}
		}

		return array_values($result);
	}

	/**
	 * Gets data item of dynamic blocks.
	 * @param int $element Element's key.
	 * @return array
	 */
	public function getElementData($element)
	{
		$this->seo->clear();

		$element = intval($element);
		if ($element <= 0)
		{
			return [];
		}

		// select
		$select = $this->getPreparedSelectFields();
		if (empty($select))
		{
			return [];
		}

		// filter
		$filter = $this->getInternalFilter();
		if (empty($filter))
		{
			return [];
		}
		$filter['ID'] = $element;
		$select[] = 'ID';

		// get data
		$res = LandingCore::getList([
			'select' => $select,
			'filter' => $filter,
		]);
		$row = $res->fetch();
		if (empty($row))
		{
			return [];
		}

		Cache::register($row['ID']);
		$this->seo->setTitle($row['TITLE']);

		return [$row];
	}
}