<?php

namespace Bitrix\Sender\Integration\Yandex\Toloka\DTO;

class OverlapConfig implements TolokaTransferObject
{
	/**
	 * @var string
	 */
	private $type = "BASIC";

	/**
	 * @var int
	 */
	private $maxOverlap;

	/**
	 * @var float
	 */
	private $minConfidence;

	/**
	 * @var int
	 */
	private $answerWeightSkill;

	/**
	 * @var InputValue[]
	 */
	private $fields;

	/**
	 * @return string
	 */
	public function getType(): string
	{
		return $this->type;
	}

	/**
	 * @param string $type
	 *
	 * @return OverlapConfig
	 */
	public function setType(string $type): OverlapConfig
	{
		$this->type = $type;

		return $this;
	}

	/**
	 * @return int
	 */
	public function getMaxOverlap(): int
	{
		return $this->maxOverlap;
	}

	/**
	 * @param int $maxOverlap
	 *
	 * @return OverlapConfig
	 */
	public function setMaxOverlap(int $maxOverlap): OverlapConfig
	{
		$this->maxOverlap = $maxOverlap;

		return $this;
	}

	/**
	 * @return float
	 */
	public function getMinConfidence(): float
	{
		return $this->minConfidence;
	}

	/**
	 * @param float $minConfidence
	 *
	 * @return OverlapConfig
	 */
	public function setMinConfidence(float $minConfidence): OverlapConfig
	{
		$this->minConfidence = $minConfidence;

		return $this;
	}

	/**
	 * @return int
	 */
	public function getAnswerWeightSkill(): int
	{
		return $this->answerWeightSkill;
	}

	/**
	 * @param int $answerWeightSkill
	 *
	 * @return OverlapConfig
	 */
	public function setAnswerWeightSkill(int $answerWeightSkill): OverlapConfig
	{
		$this->answerWeightSkill = $answerWeightSkill;

		return $this;
	}

	/**
	 * @return InputValue[]
	 */
	public function getFields(): array
	{
		return $this->fields;
	}

	/**
	 * @param InputValue[] $fields
	 *
	 * @return OverlapConfig
	 */
	public function setFields(array $fields): OverlapConfig
	{
		$this->fields = $fields;

		return $this;
	}

	public function toArray(): array
	{
		return [
			"type"                   => $this->type,
			"max_overlap"            => $this->maxOverlap,
			"min_confidence"         => $this->minConfidence,
			"answer_weight_skill_id" => $this->answerWeightSkill,
			"fields"                 => $this->fields
		];
	}
}