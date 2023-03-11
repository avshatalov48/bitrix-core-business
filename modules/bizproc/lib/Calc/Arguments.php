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
		return $this->toFlatArray($this->args);
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
	public function getSecond()
	{
		return $this->args[1] ?? null;
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
	public function getArg(int $position)
	{
		return $this->args[$position] ?? null;
	}

	private function toFlatArray($args): array
	{
		if (!is_array($args))
		{
			return [$args];
		}

		$result = [];
		foreach ($args as $arg)
		{
			if (!is_array($arg))
			{
				$result[] = $arg;
			}
			else
			{
				foreach ($this->toFlatArray($arg) as $val)
				{
					$result[] = $val;
				}
			}
		}

		return $result;
	}
}
