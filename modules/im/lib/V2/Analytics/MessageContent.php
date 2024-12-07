<?php

declare(strict_types=1);

namespace Bitrix\Im\V2\Analytics;

use Bitrix\Im\Text;
use Bitrix\Im\V2\Message;
use Bitrix\Im\V2\Message\Params;
use CSmileGallery;

class MessageContent
{
	public function __construct(private readonly Message $message)
	{
	}

	public function isSystemMessage(): bool
	{
		return $this->message->getAuthorId() === 0;
	}

	public function hasText(): bool
	{
		$text = $this->message->getMessage();

		return (null !== $text) && (0 !== strlen($text));
	}

	public function hasAttach(): bool
	{
		return $this->message->getAttach()->count() > 0;
	}

	public function hasFiles(): bool
	{
		return $this->message->hasFiles();
	}

	public function isEmptyMessage(): bool
	{
		return !$this->hasText() && !$this->hasFiles() && !$this->hasAttach();
	}

	public function isDeletedMessage(): bool
	{
		return $this->message->isDeleted() || $this->isEmptyMessage();
	}

	public function isForward(): bool
	{
		return $this->message->isForward();
	}

	public function hasOnlyText(): bool
	{
		if (!$this->hasText())
		{
			return false;
		}

		return !$this->hasFiles() && !$this->hasAttach();
	}

	protected function getAllSmiles(): array
	{
		$result = [];
		$smiles = CSmileGallery::getSmilesWithSets(
			CSmileGallery::GALLERY_DEFAULT,
			['FULL_TYPINGS' => 'Y']
		)['SMILE'] ?? [];

		foreach ($smiles as $smile)
		{
			$typings = explode(' ', $smile['TYPING']);

			foreach ($typings as $typing)
			{
				$result[] = $typing;
			}
		}

		return $result;
	}

	private function isFitForSmilesOnly(): bool
	{
		$replyIds = $this->message->getAdditionalMessageIds();

		if (count($replyIds) > 0)
		{
			foreach ($replyIds as $replyId)
			{
				if ($replyId > 0)
				{
					return false;
				}
			}
		}

		if ($this->isForward())
		{
			return false;
		}

		if (!$this->hasOnlyText())
		{
			return false;
		}

		return true;
	}

	public function hasSmilesOnly(): bool
	{
		if (!$this->isFitForSmilesOnly())
		{
			return false;
		}

		$smiles = $this->getAllSmiles();
		$message = $this->message->getMessage();
		$count = 0;

		return (trim(str_replace($smiles, '', $message, $count)) === '') && ($count < 4);
	}

	public function isEmojiOnly(): bool
	{
		if (!$this->isFitForSmilesOnly())
		{
			return false;
		}

		return Text::isOnlyEmoji($this->message->getMessage());
	}

	public function isServerComponent(): bool
	{
		return $this->message->getParams()->isSet(Params::COMPONENT_ID);
	}

	public function getComponentName(): string
	{
		if ($this->isDeletedMessage())
		{
			return 'DeletedMessage';
		}

		if ($this->isServerComponent())
		{
			return $this->message->getParams()->get(Params::COMPONENT_ID)->getValue();
		}

		if ($this->isSystemMessage())
		{
			return 'SystemMessage';
		}

		if ($this->hasFiles())
		{
			return 'FileMessage';
		}

		if ($this->isEmojiOnly() || $this->hasSmilesOnly())
		{
			return 'SmileMessage';
		}

		return 'DefaultMessage';
	}
}