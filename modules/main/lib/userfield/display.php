<?php

/**
 * @deprecated
 */

namespace Bitrix\Main\UserField;

use Bitrix\Main\UserField\Types\BaseType;

class Display implements IDisplay
{
	const MODE_EDIT = BaseType::MODE_EDIT;
	const MODE_VIEW = BaseType::MODE_VIEW;

	protected $userField;
	protected $additionalParameters = array();
	protected $defaultAdditionalParameters = array(
		"bVarsFromForm" => false,
	);
	protected $mode = self::MODE_VIEW;
	protected $tpl = '';

	public function __construct($mode = self::MODE_VIEW, string $tpl = '')
	{
		$this->mode = $mode;
		$this->tpl = $tpl;
		$this->additionalParameters = $this->defaultAdditionalParameters;
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

	public function display()
	{
		global $USER_FIELD_MANAGER;

		$this->setAdditionalParameter('mode', $this->mode);
		//$this->setAdditionalParameter('tpl', $this->tpl);

		if($this->mode === self::MODE_EDIT)
		{
			return $USER_FIELD_MANAGER->GetPublicEdit(
				$this->getField(),
				$this->getAdditionalParameters()
			);
		}
		else
		{
			return $USER_FIELD_MANAGER->GetPublicView(
				$this->getField(),
				$this->getAdditionalParameters()
			);
		}
	}
}