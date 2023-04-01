<?php

namespace Sale\Handlers\Delivery\YandexTaxi\Api\RequestEntity;

class VisitedAt extends RequestEntity
{
	protected ?string $expected = null;
	protected ?string $actual = null;

	/**
	 * @return string|null
	 */
	public function getExpected(): ?string
	{
		return $this->expected;
	}

	/**
	 * @param string|null $expected
	 * @return VisitedAt
	 */
	public function setExpected(?string $expected): VisitedAt
	{
		$this->expected = $expected;

		return $this;
	}

	/**
	 * @return string|null
	 */
	public function getActual(): ?string
	{
		return $this->actual;
	}

	/**
	 * @param string|null $actual
	 * @return VisitedAt
	 */
	public function setActual(?string $actual): VisitedAt
	{
		$this->actual = $actual;

		return $this;
	}
}
