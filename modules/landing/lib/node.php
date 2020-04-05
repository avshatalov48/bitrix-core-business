<?php
namespace Bitrix\Landing;

abstract class Node
{
	/**
	 * Must return js class - frontend handler.
	 * @return string
	 */
	abstract public static function getHandlerJS();

	/**
	 * Save data for this node.
	 * @param \Bitrix\Landing\Block $block Block instance.
	 * @param string $selector Selector.
	 * @param array $data Data array.
	 * @return void
	 */
	abstract public static function saveNode(\Bitrix\Landing\Block $block, $selector, array $data);

	/**
	 * Get data for this node.
	 * @param \Bitrix\Landing\Block $block Block instance.
	 * @param string $selector Selector.
	 * @return array
	 */
	abstract public static function getNode(\Bitrix\Landing\Block $block, $selector);

	/**
	 * Prepare item-node of manifest.
	 * @param \Bitrix\Landing\Block $block Block instance.
	 * @param array $manifest Manifest of current node.
	 * @param array $manifestFull Full manifest of block (by ref).
	 * @return array|null Return null no delete from manifest.
	 */
	//abstract public static function prepareManifest(\Bitrix\Landing\Block $block, array $manifest, array &$manifestFull = array());

	/**
	 * If exists, means that this node may participate in searching. Must returns content for search.
	 * @param Block &$block Block instance.
	 * @param string $selector Selector.
	 * @return array
	 */
	//abstract public static function getSearchableNode($block, $selector);

	/**
	 * Prepares some content for search.
	 * @param string $value Text value.
	 * @return string
	 */
	protected static function prepareSearchContent($value)
	{
		if (is_string($value))
		{
			$value = strip_tags($value);
			$value = preg_replace('/[\s]{2,}/', ' ', $value);
			$value = trim($value);
		}

		return $value;
	}

	/**
	 * Prepare field definition for node.
	 *
	 * @param array $field
	 * @return array|null
	 */
	public static function prepareFieldDefinition(array $field)
	{
		$field = array_change_key_case($field, CASE_LOWER);
		$field['id'] = static::prepareStringValue($field, 'id');
		$field['type'] = static::prepareStringValue($field, 'type');
		$field['name'] = static::prepareStringValue($field, 'name');
		if (empty($field['id']) || empty($field['type']) || empty($field['name']))
		{
			return null;
		}

		/** @var Node $className */
		$className = Node\Type::getClassName($field['type']);
		if (!class_exists($className))
		{
			return null;
		}
		return $className::validateFieldDefinition($field);
	}

	/**
	 * @param array $field
	 * @return array|null
	 */
	protected static function validateFieldDefinition(array $field)
	{
		return [
			'id' => $field['id'],
			'type' => $field['type'],
			'name' => $field['name']
		];
	}

	/**
	 * @param array $row
	 * @param string $name
	 * @return string|null
	 */
	protected static function prepareStringValue(array $row, $name)
	{
		if (empty($row[$name]) || !is_string($row[$name]))
		{
			return null;
		}
		$row[$name] = trim($row[$name]);
		if ($row[$name] === '')
		{
			return null;
		}
		return $row[$name];
	}
}