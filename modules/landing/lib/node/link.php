<?php
namespace Bitrix\Landing\Node;

use Bitrix\Landing\History;
use \Bitrix\Main\Application;

class Link extends \Bitrix\Landing\Node
{
	/**
	 * Get class - frontend handler.
	 * @return string
	 */
	public static function getHandlerJS()
	{
		return 'BX.Landing.Node.Link';
	}

	/**
	 * Allowed or not this target.
	 * @param string $target Type of target.
	 * @return boolean
	 */
	protected static function isAllowedTarget($target)
	{
		return in_array($target, array('_self', '_blank', '_popup'));
	}

	/**
	 * Allowed attrs.
	 * @return array
	 */
	protected static function allowedAttrs()
	{
		return array('data-embed', 'data-url');
	}

	/**
	 * Detects if we are in iframe.
	 * @return bool
	 */
	protected static function isFrame(): bool
	{
		static $isIframe = null;

		if ($isIframe === null)
		{
			$request = Application::getInstance()->getContext()->getRequest();
			$isIframe = $request->get('IFRAME') == 'Y';
		}

		return $isIframe;
	}

	/**
	 * Save data for this node. Returns affected content for the selector.
	 * @param \Bitrix\Landing\Block $block Block instance.
	 * @param string $selector Selector.
	 * @param array $data Data array.
	 * @return array
	 */
	public static function saveNode(\Bitrix\Landing\Block $block, $selector, array $data): array
	{
		$result = [];
		$manifest = $block->getManifest();
		$globalSkipContent = false;
		if ($manifest['nodes'][$selector]['skipContent'] ?? false)
		{
			$globalSkipContent = true;
		}

		$doc = $block->getDom();
		$resultList = $doc->querySelectorAll($selector);
		$valueBefore = static::getNode($block, $selector);
		$isIframe = self::isFrame();

		foreach ($data as $pos => $value)
		{
			$text = (isset($value['text']) && is_string($value['text'])) ? trim($value['text']) : '';
			$href = (isset($value['href']) && is_string($value['href'])) ? trim($value['href']) : '';
			$query = (isset($value['query']) && is_string($value['query'])) ? trim($value['query']) : '';
			$target = (isset($value['target']) && is_string($value['target'])) ? trim(mb_strtolower($value['target'])) : '';
			$attrs = isset($value['attrs']) ? (array)$value['attrs'] : array();
			$skipContent = $globalSkipContent || (isset($value['skipContent']) ? (boolean)$value['skipContent'] : false);
			$result[$pos]['attrs'] = [];

			if ($query)
			{
				$href .= (mb_strpos($href, '?') === false && !$isIframe) ? '?' : '&';
				$href .= $query;
			}

			if (isset($value['text']) && !$text)
			{
				$text = '&nbsp;';
			}

			if (isset($resultList[$pos]))
			{
				if (
					$text &&
					!$skipContent &&
					trim($resultList[$pos]->getTextContent()) != ''
				)
				{
					$text = \htmlspecialcharsbx($text);
					$result[$pos]['content'] = $text;
					$resultList[$pos]->setInnerHTML($text);
				}

				if ($href != '')
				{
					$result[$pos]['attrs']['href'] = $href;
					$resultList[$pos]->setAttribute('href', $href);
				}

				if (self::isAllowedTarget($target))
				{
					$result[$pos]['attrs']['target'] = $target;
					$resultList[$pos]->setAttribute('target', $target);
				}

				$allowedAttrs = self::allowedAttrs();
				if (!empty($attrs))
				{
					foreach ($attrs as $code => $val)
					{
						if ($val && in_array($code, $allowedAttrs))
						{
							$result[$pos]['attrs'][$code] = $val;
							$resultList[$pos]->setAttribute($code, $val);
						}
					}
				}
				else
				{
					foreach ($allowedAttrs as $attr)
					{
						$resultList[$pos]->removeAttribute($attr);
					}
				}

				if (History::isActive())
				{
					$history = new History($block->getLandingId(), History::ENTITY_TYPE_LANDING);
					$history->push('EDIT_LINK', [
						'block' => $block,
						'selector' => $selector,
						'position' => (int)$pos,
						'valueBefore' => $valueBefore[$pos],
						'valueAfter' => $value,
					]);
				}
			}
		}

		return $result;
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
		$manifest = $block->getManifest();
		$resultList = $doc->querySelectorAll($selector);

		foreach ($resultList as $pos => $res)
		{
			$data[$pos] = array(
				'href' => $res->getAttribute('href'),
				'target' => $res->getAttribute('target'),
				'attrs' => array(
					'data-embed' => $res->getAttribute('data-embed'),
					'data-url' => $res->getAttribute('data-url')
				)
			);
			if (
				!isset($manifest['nodes'][$selector]['skipContent']) ||
				$manifest['nodes'][$selector]['skipContent'] !== true
			)
			{
				$text = \htmlspecialcharsback($res->getInnerHTML());
				$text = str_replace('&amp;nbsp;', '', $text);
				$data[$pos]['text'] = $text;
			}
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
		foreach ($nodes as $node)
		{
			if (!isset($node['text']))
			{
				continue;
			}
			$node['text'] = self::prepareSearchContent($node['text']);
			if ($node['text'] && !in_array($node['text'], $searchContent))
			{
				$searchContent[] = $node['text'];
			}
		}

		return $searchContent;
	}

	/**
	 * @param array $field
	 * @return array|null
	 */
	protected static function validateFieldDefinition(array $field)
	{
		$result = parent::validateFieldDefinition($field);
		if (empty($result))
		{
			return null;
		}

		$field['actions'] = static::prepareActions($field);
		if (empty($field['actions']))
		{
			return null;
		}

		$result['actions'] = $field['actions'];
		return $result;
	}

	/**
	 * @param array $field
	 * @return array|null
	 */
	protected static function prepareActions(array $field)
	{
		if (empty($field['actions']) || !is_array($field['actions']))
		{
			return null;
		}
		$result = [];
		$dublicate = [];
		foreach ($field['actions'] as $row)
		{
			if (empty($row) || !is_array($row))
			{
				continue;
			}
			$row = array_change_key_case($row, CASE_LOWER);

			$row['name'] = static::prepareStringValue($row, 'name');
			$row['type'] = static::prepareStringValue($row, 'type');
			if (empty($row['name']) || empty($row['type']))
			{
				continue;
			}
			if (isset($dublicate[$row['type']]))
			{
				continue;
			}

			$result[] = [
				'type' => $row['type'],
				'name' => $row['name']
			];
			$dublicate[$row['type']] = true;
		}

		return (!empty($result) ? $result : null);
	}
}