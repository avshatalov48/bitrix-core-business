<?php
declare(strict_types=1);

namespace Bitrix\Landing\Copilot\Connector\AI;

class Prompt
{
	private const DEFAULT_REQUEST_TEMPERATURE = 0.7;
	private string $code;
	private string $category;
	private int $maxTokens;
	private array $markers;
	private float $temperature;
	private array $result;

	public function __construct(string $code)
	{
		$this->code = $code;
		$this->setTemperature(self::DEFAULT_REQUEST_TEMPERATURE);
	}

	public function getCode(): string
	{
		return $this->code;
	}

	public function setMarkers($markers): self
	{
		$this->markers = $markers;

		return $this;
	}

	public function getMarkers(): array
	{
		return $this->markers;
	}

	public function setTemperature($temperature): self
	{
		$this->temperature = $temperature;

		return $this;
	}

	public function getTemperature(): float
	{
		return $this->temperature;
	}
}