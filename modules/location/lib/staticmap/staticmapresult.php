<?php

namespace Bitrix\Location\StaticMap;

use Bitrix\Main\Result;

final class StaticMapResult extends Result
{
	private ?string $path = null;
	private ?string $content = null;
	private ?string $mimeType = null;

	public function getPath(): ?string
	{
		return $this->path;
	}

	public function setPath(string $path): StaticMapResult
	{
		$this->path = $path;

		return $this;
	}

	public function getContent(): ?string
	{
		return $this->content;
	}

	public function setContent(string $content): StaticMapResult
	{
		$this->content = $content;

		return $this;
	}

	public function getMimeType(): ?string
	{
		return $this->mimeType;
	}

	public function setMimeType(string $mimeType): StaticMapResult
	{
		$this->mimeType = $mimeType;

		return $this;
	}
}
