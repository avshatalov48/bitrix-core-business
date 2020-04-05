<?php

namespace Bitrix\Sale\Discount\Preset;


use Bitrix\Main\HttpRequest;
use Bitrix\Main\Type\Dictionary;

final class State extends Dictionary
{
	const STATE_NAME_VAR = '__state';
	const CHAIN_NAME_VAR = '__chain';

	/**
	 * State constructor.
	 * @param array $values
	 */
	public function __construct(array $values = null)
	{
		parent::__construct($values);

		$this->setDefaultValues();
	}

	private function setDefaultValues()
	{
		if(empty($this[self::CHAIN_NAME_VAR]))
		{
			$this[self::CHAIN_NAME_VAR] = array();
		}
	}

	public function set(array $values)
	{
		parent::set($values);

		$this->setDefaultValues();

		return $this;
	}

	public function append(array $values)
	{
		foreach($values as $name => $value)
		{
			$this[$name] = $value;
		}

		return $this;
	}

	/**
	 * @param string $name
	 * @param callable|null $defaultValue
	 *
	 * @return null|string
	 */
	public function get($name, $defaultValue = null)
	{
		$value = parent::get($name);
		if ($defaultValue && is_callable($defaultValue))
		{
			return $defaultValue($value);
		}

		return $value !== null? $value : $defaultValue;
	}

	public static function createFromEncodedData($data)
	{
		$state = new State;

		if(empty($data))
		{
			return $state;
		}

		$data = base64_decode($data);
		if($data === false)
		{
			return $state;
		}

		$data = unserialize($data);
		
		return $state->set($data?: array());
	}

	public static function createFromRequest(HttpRequest $request)
	{
		$prevState = self::createFromEncodedData($request->getPost(self::STATE_NAME_VAR));

		$postData = array();
		foreach($request->getPostList()->toArray() as $name => $data)
		{
			if(is_array($data) && count($data) === 1 && empty($data[0]))
			{
				//empty array
				unset($prevState[$name]);
				continue;
			}

			if(is_array($data) && empty($data[0]))
			{
				unset($data[0]);
			}
			$postData[$name] = $data;
		}

		return new State(array_merge($prevState->toArray(), $postData));
	}

	public function addStepChain($step)
	{
		$chain = $this[self::CHAIN_NAME_VAR];
		$lastStep = end($chain);

		if($lastStep != $step)
		{
			$chain[] = $step;
			$this[self::CHAIN_NAME_VAR] = $chain;
		}

		return $this;
	}

	public function popStepChain()
	{
		$chain = $this[self::CHAIN_NAME_VAR];
		$step = array_pop($chain);

		$this[self::CHAIN_NAME_VAR] = $chain;

		return $step;
	}

	public function getPrevStep()
	{
		return end($this[self::CHAIN_NAME_VAR]);
	}

	private function getStepChain()
	{
		return $this->get(self::CHAIN_NAME_VAR, array());
	}

	public function __toString()
	{
		$data = $this->toArray();
		$value = base64_encode(serialize($data));

		return '<input type="hidden" name="' . self::STATE_NAME_VAR . '" value="' . $value . '">';
	}

	public function toString()
	{
		return $this->__toString();
	}

	public function toArray()
	{
		$toArray = parent::toArray();

		unset(
			$toArray['sessid'],
			$toArray['lang'],
			$toArray['__next_step']
		);

		return $toArray;
	}

	public function getStepNumber()
	{
		$countPrevSteps = count($this->getStepChain());

		return $countPrevSteps + 1;
	}
}