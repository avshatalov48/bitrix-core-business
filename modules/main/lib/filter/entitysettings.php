<?php
namespace Bitrix\Main\Filter;

abstract class EntitySettings extends Settings
{
	const FLAG_NONE = 0;

	/** @var int */
	protected $flags = 0;

	function __construct(array $params)
	{
		parent::__construct($params);

		$this->flags = isset($params['flags'])
			? (int)$params['flags'] : self::FLAG_NONE;
	}
	/**
	 * Get Entity Type Name.
	 * @return string
	 */
	abstract public function getEntityTypeName();

	/**
	 * Get User Field Entity ID.
	 * @return string
	 */
	abstract public function getUserFieldEntityID();

	/**
	 * Check if specified flag is enabled.
	 * @param int $flag Flag value.
	 * @return bool
	 */
	public function checkFlag($flag)
	{
		return ($this->flags & $flag) === $flag;
	}
}