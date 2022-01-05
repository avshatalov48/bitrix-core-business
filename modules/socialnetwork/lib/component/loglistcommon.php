<?php

namespace Bitrix\Socialnetwork\Component;

use Bitrix\Main\ErrorCollection;
use Bitrix\Main\Error;
use Bitrix\Socialnetwork\Component\LogListCommon\Util;

class LogListCommon extends \CBitrixComponent implements \Bitrix\Main\Engine\Contract\Controllerable, \Bitrix\Main\Errorable
{
	/** @var ErrorCollection errorCollection */
	protected $errorCollection;
	protected $request = null;
	protected $currentUserAdmin = false;

	protected $task2LogList = [];

	public function configureActions(): array
	{
		return [];
	}

	public function getErrorByCode($code)
	{
		return $this->errorCollection->getErrorByCode($code);
	}

	/**
	 * Getting array of errors.
	 * @return Error[]
	 */
	public function getErrors(): array
	{
		return $this->errorCollection->toArray();
	}

	protected function printErrors()
	{
		foreach($this->errorCollection as $error)
		{
			ShowError($error);
		}
	}

	protected function getRequest()
	{
		if ($this->request == null)
		{
			$this->request = Util::getRequest();
		}

		return $this->request;
	}

	public function setTask2LogListValue($value = [])
	{
		$this->task2LogList = $value;
	}

	public function getTask2LogListValue()
	{
		return $this->task2LogList;
	}

	public function setCurrentUserAdmin($value = false): void
	{
		$this->currentUserAdmin = $value;
	}

	public function getCurrentUserAdmin()
	{
		return $this->currentUserAdmin;
	}
}
