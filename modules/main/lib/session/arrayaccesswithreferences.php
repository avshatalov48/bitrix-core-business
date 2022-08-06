<?php

namespace Bitrix\Main\Session;

use Bitrix\Main\Diag\Helper;

trait ArrayAccessWithReferences
{
	/** @var array */
	protected $sessionData = [];
	/** @var array */
	protected $nullPointers = [];
	/** @var bool */
	protected $strictMode = false;

	public function has($name)
	{
		$this->processLazyStart();

		return
			isset($this->sessionData[$name]) ||
			(empty($this->nullPointers[$name]) && $this->sessionData && array_key_exists($name, $this->sessionData))
		;
	}

	public function &get($name)
	{
		$this->processLazyStart();

		if (!isset($this->sessionData[$name]) && !array_key_exists($name, $this->sessionData))
		{
			if ($this->strictMode)
			{
				$trace = Helper::getBackTrace(1, DEBUG_BACKTRACE_IGNORE_ARGS)[0];
				trigger_error("Notice: Undefined index: {$name} in {$trace['function']} called from {$trace['file']} on line {$trace['line']}.\n", E_USER_NOTICE);
			}
			$this->nullPointers[$name] = true;
		}

		return $this->sessionData[$name];
	}

	public function set($name, $value)
	{
		$this->processLazyStart();

		$this->sessionData[$name] = $value;
		unset($this->nullPointers[$name]);
	}

	public function remove($name)
	{
		$this->processLazyStart();

		unset($this->sessionData[$name], $this->nullPointers[$name]);
	}

	public function delete($name)
	{
		$this->remove($name);
	}

	public function offsetExists($offset): bool
	{
		$this->processLazyStart();

		return isset($this->sessionData[$offset]);
	}

	#[\ReturnTypeWillChange]
	public function &offsetGet($offset)
	{
		return $this->get($offset);
	}

	public function offsetSet($offset, $value): void
	{
		if ($offset === null)
		{
			$this->processLazyStart();

			$this->sessionData[] = $value;
		}
		else
		{
			$this->set($offset, $value);
		}
	}

	public function offsetUnset($offset): void
	{
		$this->remove($offset);
	}

	public function refineReferencesBeforeSave(): void
	{
		foreach ($this->nullPointers as $key => $exists)
		{
			if ($exists === true && $this->sessionData[$key] === null)
			{
				unset($this->sessionData[$key]);
			}
		}

		$this->nullPointers = [];
	}

	protected function processLazyStart()
	{}
}