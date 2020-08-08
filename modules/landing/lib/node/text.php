<?php
namespace Bitrix\Landing\Node;

class Text extends \Bitrix\Landing\Node
{
	/**
	 * Get class - frontend handler.
	 * @return string
	 */
	public static function getHandlerJS()
	{
		return 'BX.Landing.Block.Node.Text';
	}

	/**
	 * Save data for this node.
	 * @param \Bitrix\Landing\Block $block Block instance.
	 * @param string $selector Selector.
	 * @param array $data Data array.
	 * @param array $additional Additional prams for save.
	 * @return void
	 */
	public static function saveNode(\Bitrix\Landing\Block $block, $selector, array $data, $additional = [])
	{
		$doc = $block->getDom();
		$resultList = $doc->querySelectorAll($selector);
		$additional['sanitize'] = !isset($additional['sanitize']) ||
								  isset($additional['sanitize']) &&
								  $additional['sanitize'] === true;

		foreach ($data as $pos => $value)
		{
			if (isset($value['url']))
			{
				$url = is_array($value['url'])
					? json_encode($value['url'])
					: $value['url'];
			}
			else
			{
				$url = '';
			}

			if (isset($value['text']))
			{
				$value = $value['text'];
			}

			if (!is_string($value))
			{
				continue;
			}

			$value = trim($value);

			if (isset($resultList[$pos]))
			{
				if ($additional['sanitize'])
				{
					$value = \Bitrix\Landing\Manager::sanitize($value, $bad);
				}
				// clear some amp
				$value = preg_replace('/&amp;([^\s]{1})/is', '&$1', $value);
				$value = str_replace(
					' bxstyle="',
					' style="',
					$value
				);
				$resultList[$pos]->setInnerHTML(!$value ? ' ' : $value);
				if ($url)
				{
					$resultList[$pos]->setAttribute('data-pseudo-url', $url);
				}
			}
		}
	}

	/**
	 * Get data for this node.
	 * @param \Bitrix\Landing\Block $block Block instance.
	 * @param string $selector Selector.
	 * @return array
	 */
	public static function getNode(\Bitrix\Landing\Block $block, $selector)
	{
		$data = array();
		$doc = $block->getDom();
		$resultList = $doc->querySelectorAll($selector);

		foreach ($resultList as $pos => $res)
		{
			$data[$pos] = $res->getInnerHTML();
			$data[$pos] = str_replace(
				' style="',
				' bxstyle="',
				$data[$pos]
			);
			$data[$pos] = \Bitrix\Main\Text\Emoji::encode(
				$data[$pos]
			);
		}

		return $data;
	}

	/**
	 * This node may participate in searching.
	 * @param \Bitrix\Landing\Block $block Block instance.
	 * @param string $selector Selector.
	 * @return array
	 */
	public static function getSearchableNode($block, $selector)
	{
		$searchContent = [];

		$nodes = self::getNode($block, $selector);
		foreach ($nodes as $value)
		{
			$value = self::prepareSearchContent($value);
			if ($value && !in_array($value, $searchContent))
			{
				$searchContent[] = $value;
			}
		}

		return $searchContent;
	}
}