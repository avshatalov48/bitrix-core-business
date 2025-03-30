<?php
declare(strict_types = 1);

namespace Bitrix\Rest\Entity;

class Integration
{
	public function __construct(
		private readonly string $elementCode,
		private readonly string $title,
		private readonly ?int $id = null,
		private readonly ?int $userId = null,
		private readonly ?int $passwordId = null,
		private readonly ?int $appId = null,
		private readonly ?array $scope = null,
		private readonly ?array $widgetList = null,
		private readonly ?array $outgoingEvents = null,
		private readonly ?bool $outgoingNeeded = null,
		private readonly ?string $outgoingHandler = null,
		private readonly ?bool $widgetNeeded = null,
		private readonly ?string $widgetHandler = null,
		private readonly ?string $applicationToken = null,
		private readonly ?bool $applicationNeeded = null,
		private readonly ?bool $onlyApi = null,
		private readonly ?int $botId = null,
		private readonly ?string $botHandlerUrl = null,
	)
	{
	}

	public function getId(): ?int
	{
		return $this->id;
	}

	public function getUserId(): ?int
	{
		return $this->userId;
	}

	public function getPasswordId(): ?int
	{
		return $this->passwordId;
	}

	public function getAppId(): ?int
	{
		return $this->appId;
	}

	public function getScope(): ?array
	{
		return $this->scope;
	}

	public function getWidgetList(): ?array
	{
		return $this->widgetList;
	}

	public function getOutgoingEvents(): ?array
	{
		return $this->outgoingEvents;
	}

	public function getOutgoingNeeded(): ?bool
	{
		return $this->outgoingNeeded;
	}

	public function getOutgoingHandler(): ?string
	{
		return $this->outgoingHandler;
	}

	public function getWidgetNeeded(): ?bool
	{
		return $this->widgetNeeded;
	}

	public function getWidgetHandler(): ?string
	{
		return $this->widgetHandler;
	}

	public function getApplicationToken(): ?string
	{
		return $this->applicationToken;
	}

	public function getApplicationNeeded(): ?bool
	{
		return $this->applicationNeeded;
	}

	public function getOnlyApi(): ?bool
	{
		return $this->onlyApi;
	}

	public function getBotId(): ?int
	{
		return $this->botId;
	}

	public function getBotHandlerUrl(): ?string
	{
		return $this->botHandlerUrl;
	}

	public function getElementCode(): ?string
	{
		return $this->elementCode;
	}

	public function getTitle(): string
	{
		return $this->title;
	}
}