<?php

namespace Bitrix\Main\UserField;

use CUserTypeManager;

/**
 * Class Renderer
 * @package Bitrix\Main\UserField
 */
class Renderer
{
	/**
	 * @var array $userField
	 * @var array $additionalParameters
	 * @var array $defaultAdditionalParameters
	 * @var string|array $mode
	 */
	protected
		$userField = [],
		$additionalParameters = [],
		$defaultAdditionalParameters = [
		'bVarsFromForm' => false
	],
		$mode;

	/**
	 * Renderer constructor.
	 * @param array $userField
	 * @param array $additionalParameters
	 */
	public function __construct(array $userField, ?array $additionalParameters = [])
	{
		$this->setUserField($userField);
		$this->setAdditionalParameters($additionalParameters);

		if($this->getAdditionalParameter('mode'))
		{
			$this->setMode($this->getAdditionalParameter('mode'));
		}
	}

	/**
	 * @return string|null
	 */
	public function render(): ?string
	{
		global $USER_FIELD_MANAGER;

		return $USER_FIELD_MANAGER->renderField(
			$this->getUserField(),
			$this->getAdditionalParameters()
		);
	}

	/**
	 * @return array|string
	 */
	public function getMode()
	{
		return $this->mode;
	}

	/**
	 * @param string|array $mode
	 * @return Renderer
	 */
	public function setMode($mode): Renderer
	{
		$this->mode = $mode;
		return $this;
	}

	/**
	 * @return array
	 */
	public function getUserField(): array
	{
		return $this->userField;
	}

	/**
	 * @param string $param
	 * @param string|array|int $value
	 * @param bool|null $storeAsDefault
	 * @return Renderer
	 */
	public function setAdditionalParameter(string $param, $value, ?bool $storeAsDefault = false): Renderer
	{
		$this->additionalParameters[$param] = $value;
		if($storeAsDefault)
		{
			$this->defaultAdditionalParameters[$param] = $value;
		}
		return $this;
	}

	/**
	 * @param string $param
	 * @return null|mixed
	 */
	public function getAdditionalParameter(string $param)
	{
		return ($this->additionalParameters[$param] ?: null);
	}

	/**
	 * @return array
	 */
	protected function getAdditionalParameters(): array
	{
		return $this->additionalParameters;
	}

	/**
	 * @param array $userField
	 * @return Renderer
	 */
	public function setUserField(array $userField): Renderer
	{
		$this->userField = $userField;
		return $this;
	}

	/**
	 * @param array|null $additionalParameters
	 * @return Renderer
	 */
	public function setAdditionalParameters(?array $additionalParameters): Renderer
	{
		$this->additionalParameters = $additionalParameters;
		return $this;
	}
}