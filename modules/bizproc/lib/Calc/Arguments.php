<?php

namespace Bitrix\Bizproc\Calc;

class Arguments
{
	protected Parser $parser;
	protected array $args;

	public function __construct(Parser $parser, ?array $args = null)
	{
		$this->parser = $parser;
		$this->args = $args ?? [];
	}

	public function getParser(): Parser
	{
		return $this->parser;
	}

	public function setArgs(array $args): self
	{
		$this->args = $args;

		return $this;
	}

	public function getArray(): array
	{
		return $this->args;
	}

	public function getFlatArray(): array
	{
		return \CBPHelper::flatten($this->args);
	}

	/**
	 * @return mixed
	 */
	public function getFirst()
	{
		return $this->args[0] ?? null;
	}

	/**
	 * @return mixed
	 */
	public function getFirstSingle()
	{
		return $this->toSingle($this->getFirst());
	}

	/**
	 * @return mixed
	 */
	public function getSecond()
	{
		return $this->args[1] ?? null;
	}

	/**
	 * @return mixed
	 */
	public function getSecondSingle()
	{
		return $this->toSingle($this->getSecond());
	}

	/**
	 * @return mixed
	 */
	public function getThird()
	{
		return $this->args[2] ?? null;
	}

	/**
	 * @return mixed
	 */
	public function getThirdSingle()
	{
		return $this->toSingle($this->getThird());
	}

	/**
	 * @return mixed
	 */
	public function getArg(int $position)
	{
		return $this->args[$position] ?? null;
	}

	/**
	 * @return mixed
	 */
	public function getArgSingle(int $position)
	{
		return $this->toSingle($this->getArg($position));
	}

	/**
	 * @return mixed
	 */
	private function toSingle($value)
	{
		return is_array($value) ? array_shift($value) : $value;
	}
}
