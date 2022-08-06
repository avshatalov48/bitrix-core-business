<?php

namespace Bitrix\Bizproc\Debugger\Session;

class DebuggerState
{
	public const RUN = 0;
	public const NEXT_STEP = 1;
	public const STOP = 2;
	public const PAUSE = 3;
	public const UNDEFINED = -1;

	private int $stateId;

	public function __construct(int $stateId)
	{
		$this->stateId = $stateId;
	}

	public static function undefined(): self
	{
		return new self(self::UNDEFINED);
	}

	public static function run(): self
	{
		return new self(self::RUN);
	}

	public static function nextStep(): self
	{
		return new self(self::NEXT_STEP);
	}

	public static function pause(): self
	{
		return new self(self::PAUSE);
	}

	public static function stop(): self
	{
		return new self(self::STOP);
	}

	public function is(int $stateId): bool
	{
		return $this->stateId === $stateId;
	}

	public function getId(): int
	{
		return $this->stateId;
	}
}
