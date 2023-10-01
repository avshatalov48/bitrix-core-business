<?php

namespace Bitrix\Bizproc\Script;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Result;
use Bitrix\Main\Error;

class StartScriptResult extends Result
{
	public const CODE_NOT_ENOUGH_RIGHTS = 'NOT_ENOUGH_RIGHTS';
	public const CODE_SCRIPT_NOT_EXIST = 'SCRIPT_NOT_EXIST';
	public const CODE_INACTIVE_SCRIPT = 'INACTIVE_SCRIPT';
	public const CODE_TEMPLATE_NOT_EXIST = 'TEMPLATE_NOT_EXIST';
	public const CODE_EMPTY_TEMPLATE_PARAMETERS = 'EMPTY_TEMPLATE_PARAMETERS';
	public const CODE_INVALID_PARAMETERS = 'INVALID_PARAMETERS';

	public function addNotEnoughRightsError(): StartScriptResult
	{
		$this->addError(new Error(
			Loc::getMessage('BIZPROC_LIB_SCRIPT_START_SCRIPT_RESULT_NOT_ENOUGH_RIGHTS'),
			self::CODE_NOT_ENOUGH_RIGHTS)
		);

		return $this;
	}

	public function addScriptNotExistError(): StartScriptResult
	{
		$this->addError(new Error(
			Loc::getMessage('BIZPROC_LIB_SCRIPT_START_SCRIPT_RESULT_SCRIPT_NOT_EXIST'),
			self::CODE_SCRIPT_NOT_EXIST)
		);

		return $this;
	}

	public function addInactiveScriptError(): StartScriptResult
	{
		$this->addError(new Error(
			Loc::getMessage('BIZPROC_LIB_SCRIPT_START_SCRIPT_RESULT_INACTIVE_SCRIPT'),
			self::CODE_INACTIVE_SCRIPT)
		);

		return $this;
	}

	public function addTemplateNotExistError(): StartScriptResult
	{
		$this->addError(new Error(
			Loc::getMessage('BIZPROC_LIB_SCRIPT_START_SCRIPT_RESULT_TEMPLATE_NOT_EXIST'),
			self::CODE_TEMPLATE_NOT_EXIST)
		);

		return $this;
	}

	public function addEmptyTemplateParameterError(): StartScriptResult
	{
		$this->addError(new Error(
			Loc::getMessage('BIZPROC_LIB_SCRIPT_START_SCRIPT_RESULT_EMPTY_TEMPLATE_PARAMETERS'),
			self::CODE_EMPTY_TEMPLATE_PARAMETERS)
		);

		return $this;
	}

	public function addInvalidParameterErrors(array $errors): StartScriptResult
	{
		foreach ($errors as $error)
		{
			$this->addError(new Error($error['message'], self::CODE_INVALID_PARAMETERS));
		}

		return $this;
	}
}