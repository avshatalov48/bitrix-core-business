<?php

namespace Bitrix\Im\V2\Chat;

use Bitrix\Im\Model\EO_Chat;
use Bitrix\Im\V2\Result;
use Bitrix\Im\V2\Service\Context;

class GeneralChat extends GroupChat
{
	protected function getDefaultEntityType(): string
	{
		return self::ENTITY_TYPE_GENERAL;
	}

	/**
	 * @param int|array|EO_Chat $source
	 */
	public function load($source = null): Result
	{
		$chatId = -1;

		if (is_numeric($source))
		{
			$chatId = (int)$source;
		}
		elseif ($source instanceof EO_Chat)
		{
			$chatId = $source->getId();
		}
		elseif (is_array($source))
		{
			$chatId = (int)$source['ID'];
		}

		if ($chatId <= 0)
		{
			//$chatId = (int)\Bitrix\Main\Config\Option::get('im', 'general_chat_id');
			$chatId = (int)\CIMChat::GetGeneralChatId();
			if ($chatId)
			{
				$source = $chatId;
			}
		}

		return parent::load($source);
	}

	/**
	 * @return Result
	 */
	public function save(): Result
	{
		$saveResult = parent::save();
		if (
			$saveResult->isSuccess()
			&& $this->getChatId()
		)
		{
			\Bitrix\Main\Config\Option::set('im', 'general_chat_id', $this->getChatId());
		}

		return $saveResult;
	}

	/**
	 * Looks for self-personal chat by its owner.
	 *
	 * @param array $params
	 * @param Context|null $context
	 * @return Result
	 */
	public static function find(array $params = [], ?Context $context = null): Result
	{
		$result = new Result;

		//$chatId = (int)\Bitrix\Main\Config\Option::get('im', 'general_chat_id');
		$chatId = (int)\CIMChat::GetGeneralChatId();
		if ($chatId > 0)
		{
			$result->setResult(['CHAT_ID' => $chatId]);
		}

		return $result;
	}
}
