<?php

namespace Bitrix\Im\V2\Integration\AI\Dto;

class Message implements \JsonSerializable
{
	/**
	 * @param string $text
	 * @param \Bitrix\Main\Type\DateTime $dateCreate
	 * @param string $authorName
	 * @param ForwardInfo|null $forwardInfo
	 * @param File[] $files
	 * @param Message|null $reply
	 */
	public function __construct(
		public readonly int $id,
		public readonly string $text,
		public readonly \Bitrix\Main\Type\DateTime $dateCreate,
		public readonly string $authorName,
		public readonly ?ForwardInfo $forwardInfo,
		public readonly array $files,
		public readonly ?Message $reply,
	) {}

	public function setText(string $newText): self
	{
		return new Message(
			$this->id,
			$newText,
			$this->dateCreate,
			$this->authorName,
			$this->forwardInfo,
			$this->files,
			$this->reply
		);
	}

	public function setReply(Message $newReply): self
	{
		return new Message(
			$this->id,
			$this->text,
			$this->dateCreate,
			$this->authorName,
			$this->forwardInfo,
			$this->files,
			$newReply
		);
	}

	public function jsonSerialize(): array
	{
		$json = [
			'id' => $this->id,
			'text' => $this->text,
			'date' => $this->dateCreate->format(\DateTime::ATOM),
			'author' => $this->authorName,
		];

		if ($this->forwardInfo !== null)
		{
			$json['forwardInfo'] = $this->forwardInfo;
		}

		if ($this->reply !== null)
		{
			$json['reply'] = $this->reply;
		}

		foreach ($this->files as $file)
		{
			$json['files'][] = $file;
		}

		return $json;
	}
}