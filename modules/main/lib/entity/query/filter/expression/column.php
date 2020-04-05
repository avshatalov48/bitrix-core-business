<?php
/**
 * Bitrix Framework
 * @package    bitrix
 * @subpackage main
 * @copyright  2001-2016 Bitrix
 */

namespace Bitrix\Main\Entity\Query\Filter\Expression;

/**
 * Wrapper for columns values in QueryFilter.
 * @package    bitrix
 * @subpackage main
 */
class Column extends Base
{
	/**
	 * @var string
	 */
	protected $definition;

	/**
	 * @param $definition
	 */
	public function __construct($definition)
	{
		$this->definition = $definition;
	}

	/**
	 * @return string
	 */
	public function getDefinition()
	{
		return $this->definition;
	}

	/**
	 * @param string $definition
	 */
	public function setDefinition($definition)
	{
		$this->definition = $definition;
	}
}
