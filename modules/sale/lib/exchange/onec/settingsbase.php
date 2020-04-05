<?php

namespace Bitrix\Sale\Exchange\OneC;


use Bitrix\Main\ArgumentNullException;
use Bitrix\Main\ArgumentTypeException;
use Bitrix\Main\NotImplementedException;
use Bitrix\Main\NotSupportedException;
use Bitrix\Sale\Exchange\EntityType;
use Bitrix\Sale\Exchange\ISettings;

abstract class SettingsBase
	implements ISettings
{
	protected static $currentSettings = null;
	protected $settings = array();

	/**
	 * ImportSettings constructor.
	 * @param array|null $settings
	 */
	protected function __construct(array $settings = null)
	{
		if($settings !== null)
		{
			$this->settings = $settings;
		}
	}

	/**
	 * @param $entityTypeId
	 * @return string
	 * @throws ArgumentTypeException
	 * @throws NotSupportedException
	 */
	protected function resolveName($entityTypeId)
	{
		if(!is_int($entityTypeId))
		{
			throw new ArgumentTypeException('entityTypeID', 'integer');
		}

		if(!EntityType::IsDefined($entityTypeId))
		{
			throw new NotSupportedException("Entity ID: '{$entityTypeId}' is not supported in current context");
		}

		return EntityType::ResolveName($entityTypeId);
	}

	/**
	 * @return array|null
	 * @throws ArgumentNullException
	 */
	static protected function loadCurrentSettings()
	{
		throw new NotImplementedException('The method is not implemented.');
	}

	/**
	 * @param $entityTypeId
	 * @param $name
	 * @param string $default
	 * @return string
	 */
	protected function getValueFor($entityTypeId, $name, $default='')
	{
		$entityTypeName = $this->resolveName($entityTypeId);
		return (isset($this->settings[$name][$entityTypeName]) ? $this->settings[$name][$entityTypeName]: $default);
	}

	/**
	 * @param $entityTypeId
	 * @return mixed
	 * @throws ArgumentTypeException
	 * @throws NotSupportedException
	 */
	public function prefixFor($entityTypeId)
	{
		return $this->getValueFor($entityTypeId, 'accountNumberPrefix');
	}
}