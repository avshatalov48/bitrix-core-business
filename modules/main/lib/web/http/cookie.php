<?php

/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2022 Bitrix
 */

namespace Bitrix\Main\Web\Http;

class Cookie
{
	public const SAME_SITE_NONE = 'None';
	public const SAME_SITE_LAX = 'Lax';
	public const SAME_SITE_STRICT = 'Strict';

	protected $domain;
	protected $expires;
	protected $httpOnly = true;
	protected $name;
	protected $path = '/';
	protected $secure = false;
	protected $value;
	protected $sameSite;

	/**
	 * Cookie constructor.
	 * @param string $name The cooke name
	 * @param string|null $value The cooke value
	 * @param int $expires Timestamp
	 */
	public function __construct(string $name, ?string $value, int $expires = 0)
	{
		$this->name = $name;
		$this->value = $value;
		$this->expires = $expires;
	}

	public function setDomain(string $domain): self
	{
		$this->domain = $domain;

		return $this;
	}

	public function getDomain(): ?string
	{
		return $this->domain;
	}

	public function setExpires(int $expires): self
	{
		$this->expires = $expires;

		return $this;
	}

	public function getExpires(): int
	{
		return $this->expires;
	}

	public function setHttpOnly(bool $httpOnly): self
	{
		$this->httpOnly = $httpOnly;

		return $this;
	}

	public function getHttpOnly(): bool
	{
		return $this->httpOnly;
	}

	public function setName(string $name): self
	{
		$this->name = $name;

		return $this;
	}

	public function getName(): string
	{
		return $this->name;
	}

	public function setPath(string $path): self
	{
		$this->path = $path;

		return $this;
	}

	public function getPath(): string
	{
		return $this->path;
	}

	public function setSecure(bool $secure): self
	{
		$this->secure = $secure;

		return $this;
	}

	public function getSecure(): bool
	{
		return $this->secure;
	}

	public function setValue(?string $value): self
	{
		$this->value = $value;

		return $this;
	}

	public function getValue(): ?string
	{
		return $this->value;
	}

	public function setSameSite(?string $sameSite): self
	{
		$this->sameSite = $sameSite;

		return $this;
	}

	public function getSameSite(): ?string
	{
		return $this->sameSite;
	}
}
