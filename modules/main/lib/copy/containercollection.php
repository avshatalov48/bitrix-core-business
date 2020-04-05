<?php
namespace Bitrix\Main\Copy;

use Bitrix\Main\Type\Dictionary;

/**
 * Class for to store containers that will be used for copying.
 *
 * @package Bitrix\Main\Copy
 */
class ContainerCollection extends Dictionary
{
	protected function addContainer(Container $container, $offset = null)
	{
		parent::offsetSet($offset, $container);
	}

	public function offsetSet($offset, $value)
	{
		$this->addContainer($value, $offset);
	}
}