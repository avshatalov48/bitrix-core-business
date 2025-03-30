<?php
declare(strict_types = 1);

namespace Bitrix\Rest\Entity;

use Bitrix\Main\Type\DateTime;

class App
{
	public function __construct(
		private readonly string $clientId,
		private readonly string $code,
		private readonly bool $active,
		private readonly bool $installed,
		private readonly string $url,
		private readonly string $scope,
		private readonly string $status,
		private readonly ?int $id = null,
		private readonly ?string $urlDemo = null,
		private readonly ?string $urlInstall = null,
		private readonly ?string $version = null,
		private readonly ?\Bitrix\Main\Type\Date $dateFinish = null,
		private readonly ?bool $isTrialled = null,
		private readonly ?string $sharedKey = null,
		private readonly ?string $clientSecret = null,
		private readonly ?string $appName = null,
		private readonly ?string $access = null,
		private readonly ?string $aplicationToken = null,
		private readonly ?bool $mobile = null,
		private readonly ?bool $userInstall = null,
		private readonly ?string $urlSettings = null,
	)
	{
	}

	public function getClientId(): string
	{
		return $this->clientId;
	}

	public function getCode(): string
	{
		return $this->code;
	}

	public function isActive(): bool
	{
		return $this->active;
	}

	public function isInstalled(): bool
	{
		return $this->installed;
	}

	public function getUrl(): string
	{
		return $this->url;
	}

	public function getScope(): array
	{
		return $this->scope;
	}

	public function isStatus(): string
	{
		return $this->status;
	}

	public function getId(): ?int
	{
		return $this->id;
	}

	public function getUrlDemo(): ?string
	{
		return $this->urlDemo;
	}

	public function getUrlInstall(): ?string
	{
		return $this->urlInstall;
	}

	public function getVersion(): ?string
	{
		return $this->version;
	}

	public function getDateFinish(): ?DateTime
	{
		return $this->dateFinish;
	}

	public function getIsTrialled(): ?bool
	{
		return $this->isTrialled;
	}

	public function getSharedKey(): ?string
	{
		return $this->sharedKey;
	}

	public function getClientSecret(): ?string
	{
		return $this->clientSecret;
	}

	public function getAppName(): ?string
	{
		return $this->appName;
	}

	public function getAccess(): ?string
	{
		return $this->access;
	}

	public function getAplicationToken(): ?string
	{
		return $this->aplicationToken;
	}

	public function getMobile(): ?bool
	{
		return $this->mobile;
	}

	public function getUserInstall(): ?bool
	{
		return $this->userInstall;
	}

	public function getUrlSettings(): ?string
	{
		return $this->urlSettings;
	}
}