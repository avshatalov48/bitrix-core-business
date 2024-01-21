<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sender
 * @copyright 2001-2018 Bitrix
 */
namespace Bitrix\Sender\Connector;

use Bitrix\Main\ArgumentException;
use Bitrix\Sender\UI\PageNavigation;

/**
 * Class ResultView
 *
 * @package Bitrix\Sender\Connector
 */
Class ResultView
{
	const Title = 'title';
	const Draw = 'draw';
	const Filter = 'filter';
	const FilterModifier = 'filter-modifier';
	const ColumnModifier = 'column-modifier';

	/** @var Base $connector Connector. */
	protected $connector;

	/** @var array $callbacks Callbacks. */
	protected $callbacks;

	/** @var PageNavigation $nav Page navigation. */
	protected $nav;

	/**
	 * Constructor.
	 *
	 * @param Base $connector Connector.
	 */
	public function __construct(Base $connector)
	{
		$this->connector = $connector;
	}

	/**
	 * Set callback.
	 *
	 * @param string $name Name.
	 * @param callable $callable Callback.
	 * @return $this
	 * @throws ArgumentException
	 */
	public function setCallback($name, $callable)
	{
		if (!is_callable($callable))
		{
			throw new ArgumentException('Parameter \'callable\' should be callable.');
		}

		$this->callbacks[$name] = $callable;

		return $this;
	}

	protected function runCallback($name, array $arguments = [])
	{
		if (!isset($this->callbacks[$name]))
		{
			return null;
		}

		return call_user_func_array($this->callbacks[$name], $arguments);
	}

	/**
	 * Get filter.
	 *
	 * @return string|null
	 */
	public function getTitle()
	{
		return $this->runCallback(self::Title) ?: $this->connector->getName();
	}

	/**
	 * Modify result view filter.
	 *
	 * @param array $filter Filter.
	 * @return void
	 */
	public function modifyFilter(array &$filter)
	{
		$this->runCallback(self::FilterModifier, [&$filter]);
	}

	/**
	 * Modify result view columns.
	 *
	 * @param array $columns columns.
	 * @return void
	 */
	public function modifyColumns(array &$columns)
	{
		$this->runCallback(self::ColumnModifier, [&$columns]);
	}

	/**
	 * Set page navigation.
	 *
	 * @param PageNavigation|null $nav Page navigation.
	 * @return $this
	 */
	public function setNav(PageNavigation $nav = null)
	{
		$this->nav = $nav;
		return $this;
	}

	/**
	 * Get page navigation.
	 *
	 * @return PageNavigation|null
	 */
	public function getNav()
	{
		return $this->nav;
	}

	/**
	 * Has page navigation.
	 *
	 * @return bool
	 */
	public function hasNav()
	{
		return !empty($this->nav);
	}

	/**
	 * Callback on filter of result view.
	 *
	 * @return void
	 */
	public function onFilter()
	{
		$this->runCallback(self::Filter);
	}

	/**
	 * Callback on draw of result view.
	 *
	 * @param array $raw Raw.
	 * @return void
	 */
	public function onDraw(array &$raw)
	{
		$this->runCallback(self::Draw, [&$raw]);
	}
}
