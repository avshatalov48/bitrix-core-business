<?php

namespace Bitrix\Sale\Discount\Preset;


use Bitrix\Main\HttpRequest;
use Bitrix\Main\Type\Dictionary;
use Bitrix\Main\Security\Sign\Signer;

final class State extends Dictionary
{
	private const STATE_NAME_VAR = '__state';
	private const CHAIN_NAME_VAR = '__chain';
	private const STATE_SIGNER_SALT = 'discount.preset.state';

	/** @var Signer */
	private $signer = null;

	/**
	 * State constructor.
	 * @param array $values
	 */
	public function __construct(array $values = null)
	{
		parent::__construct($values);

		$this->setDefaultValues();
		$this->initSigner();
	}

	private function setDefaultValues()
	{
		if(empty($this[self::CHAIN_NAME_VAR]))
		{
			$this[self::CHAIN_NAME_VAR] = array();
		}
	}

	private function initSigner(): void
	{
		$this->signer = new Signer();
	}

	private function sign(string $data): string
	{
		return $this->signer->sign($data, self::STATE_SIGNER_SALT);
	}

	public function unSign(string $data): string
	{
		try
		{
			$signedData = $this->signer->unsign($data, self::STATE_SIGNER_SALT);
		}
		catch(\Bitrix\Main\Security\Sign\BadSignatureException $e)
		{
			die('Bad signature.');
		}
		return $signedData;
	}

	public function set($name, $value = null)
	{
		parent::set($name, $value);

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
	 * @return null|string|array
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

		$data = $state->unSign($data);

		$data = base64_decode($data);
		if($data === false)
		{
			return $state;
		}
		if (!CheckSerializedData($data))
		{
			return $state;
		}
		$data = unserialize($data, ['allowed_classes' => false]);

		return $state->set($data?: array());
	}

	public static function createFromRequest(HttpRequest $request)
	{
		$prevState = self::createFromEncodedData($request->getPost(self::STATE_NAME_VAR));

		$postData = array();
		foreach($request->getPostList()->toArray() as $name => $data)
		{
			if(is_array($data) && count($data) === 1 && ($data[0] !== '0' && empty($data[0])))
			{
				//empty array
				unset($prevState[$name]);
				continue;
			}

			if(is_array($data) && ($data[0] !== '0' && empty($data[0])))
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

	private function getStepChain(): array
	{
		return $this->get(self::CHAIN_NAME_VAR, array());
	}

	public function __toString()
	{
		$data = $this->toArray();
		$value = $this->sign(base64_encode(serialize($data)));

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