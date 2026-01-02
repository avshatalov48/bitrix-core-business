<?php

namespace Bitrix\Main\Diag;

class StopWatch
{
	// seconds with nanoseconds
	protected ?float $start = null;
	protected float $timer = 0.0;
	protected int $precision;

	public function __construct(int $precision = 6)
	{
		$this->precision = $precision;
	}

	public function start(): static
	{
		$this->start = hrtime(true);

		return $this;
	}

	public function stop(): float
	{
		if ($this->start !== null)
		{
			$this->timer += (hrtime(true) - $this->start)/1e+9;
			$this->start = null;
		}

		return $this->get();
	}

	public function reset(): static
	{
		return $this->set(0.0);
	}

	public function flyback(): float
	{
		$time = $this->stop();
		$this->reset();
		$this->start();

		return $time;
	}

	public function set(float $timer): static
	{
		$this->timer = $timer;
		$this->start = null;

		return $this;
	}

	public function get(): float
	{
		return round($this->timer, $this->precision);
	}
}
