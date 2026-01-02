<?php

namespace Bitrix\Rest\V3\Interaction;

use Bitrix\Rest\V3\Interaction\Request\Request;
use Bitrix\Rest\V3\Interaction\Response\ResponseWithRelations;

class Relation
{
	private ?ResponseWithRelations $response = null;

	public function __construct(
		private string $name,
		private string $method,
		private string $fromField,
		private string $toField,
		private Request $request,
		private bool $multiply,
	) {
	}

	public function getName(): string
	{
		return $this->name;
	}

	public function getMethod(): string
	{
		return $this->method;
	}

	public function getFromField(): string
	{
		return $this->fromField;
	}

	public function getToField(): string
	{
		return $this->toField;
	}

	public function getRequest(): Request
	{
		return $this->request;
	}

	public function isMultiply(): bool
	{
		return $this->multiply;
	}

	public function getResponse(): ?ResponseWithRelations
	{
		return $this->response;
	}

	public function setResponse(ResponseWithRelations $response): self
	{
		$this->response = $response;

		return $this;
	}
}
