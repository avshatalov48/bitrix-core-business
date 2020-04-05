<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sender
 * @copyright 2001-2012 Bitrix
 */
namespace Bitrix\Sender\Connector\Filter;

use Bitrix\Main\UI\Filter\Options as FilterOptions;

/**
 * Class DateField
 * @package Bitrix\Sender\Connector\Filter
 */
abstract class AbstractField
{
	/** @var array $data Data. */
	protected $data;

	/**
	 * DateField constructor.
	 *
	 * @param array $data Data.
	 * @return static
	 */
	public static function create(array $data)
	{
		return new static($data);
	}

	/**
	 * DateField constructor.
	 *
	 * @param array $data Data.
	 */
	public function __construct(array $data)
	{
		$this->data = $data;
	}

	/**
	 * Get id.
	 *
	 * @return string
	 */
	public function getId()
	{
		return $this->data['id'];
	}

	/**
	 * Get filter key.
	 *
	 * @return string
	 */
	public function getFilterKey()
	{
		return $this->data['filter-key'];
	}

	/**
	 * Get value.
	 *
	 * @param mixed $defaultValue Default value.
	 * @return string
	 */
	public function getValue($defaultValue = null)
	{
		return isset($this->data['value']) ? $this->data['value'] : $defaultValue;
	}

	/**
	 * Apply filter.
	 *
	 * @param array $filter Filter.
	 * @return void
	 */
	abstract public function applyFilter(array &$filter = array());
}