<?php

namespace Bitrix\Calendar\Core\Event\Properties;

use Bitrix\Calendar\Core\Base\BaseProperty;

class Relations extends BaseProperty
{
	/**
	 * @var string|null
	 */
	private ?string $commentXmlId;

	public function __construct(?string $commentXmlId)
	{
		$this->commentXmlId = $commentXmlId;
	}

	public function getFields(): array
	{
		return [
			'COMMENT_XML_ID' => $this->commentXmlId
		];
	}

	public function getCommentXmlId(): ?string
	{
		return $this->commentXmlId;
	}

	public function setCommentXmlId($commentXmlId): Relations
	{
		$this->commentXmlId = $commentXmlId;

		return $this;
	}

	public function toString(): string
	{
		return $this->commentXmlId;
	}
}