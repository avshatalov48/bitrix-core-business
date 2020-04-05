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
	 * @param \Bitrix\Landing\Block &$block Block instance.
	 * @param string $selector Selector.
	 * @param array $data Data array.
	 * @return void
	 */
	abstract public static function saveNode(\Bitrix\Landing\Block &$block, $selector, array $data);

	/**
	 * Prepare item-node of manifest.
	 * @param \Bitrix\Landing\Block $block Block instance.
	 * @param array $manifest Manifest of current node.
	 * @param array $manifestFull Full manifest of block (by ref).
	 * @return array|null Return null no delete from manifest.
	 */
	//abstract public static function prepareManifest(\Bitrix\Landing\Block $block, array $manifest, array &$manifestFull = array());
}