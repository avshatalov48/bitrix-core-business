<?php
namespace Bitrix\Main\UserField;

abstract class Display implements IDisplay
{
	protected $userField;
	protected $additionalParameters = array();
	protected $defaultAdditionalParameters = array(
		"bVarsFromForm" => false,
	);

	public function __construct()
	{
		$this->clear();
	}

	/**
	 * @return mixed
	 */
	public function getField()
	{
		return $this->userField;
	}

	/**
	 * @param mixed $userField
	 */
	public function setField(array $userField)
	{
		$this->userField = $userField;
	}

	public function setAdditionalParameter($param, $value, $storeAsDefault = false)
	{
		$this->additionalParameters[$param] = $value;
		if($storeAsDefault)
		{
			$this->defaultAdditionalParameters[$param] = $value;
		}
	}

	public function clear()
	{
		$this->userField = null;
		$this->additionalParameters = $this->defaultAdditionalParameters;
	}

	protected function getAdditionalParameters()
	{
		return $this->additionalParameters;
	}
}