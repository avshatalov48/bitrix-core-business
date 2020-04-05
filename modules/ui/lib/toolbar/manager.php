<?php

namespace Bitrix\UI\Toolbar;

use Bitrix\Main\ArgumentException;

final class Manager
{
	/** @var  Manager */
	private static $instance;
	/** @var Toolbar[] */
	protected $toolbars = [];

	private function __construct()
	{}

	private function __clone()
	{}

	/**
	 * Returns Singleton of Manager
	 * @return Manager
	 */
	public static function getInstance()
	{
		if (!isset(self::$instance))
		{
			self::$instance = new Manager;
		}

		return self::$instance;
	}

	/**
	 * @param $id
	 *
	 * @return Toolbar|null
	 */
	public function getToolbarById($id)
	{
		if (isset($this->toolbars[$id]))
		{
			return $this->toolbars[$id];
		}

		return null;
	}

	public function createToolbar($id, $options)
	{
		if (empty($id))
		{
			throw new ArgumentException("id is required", 'id');
		}

		$toolbar = new Toolbar($id, $options);
		$this->toolbars[$id] = $toolbar;

		return $toolbar;
	}
}