<?php

namespace Bitrix\Im\Internals;

use Bitrix\Main\Localization\Loc;

class ChatIndex
{
	private const CHARS_TO_REPLACE = ['(', ')', '[', ']', '{', '}', '<', '>', '-', '#', '"', '\''];
	
	/** @var string */
	private $title;

	/** @var int */
	private $chatId;

	/** @var array list of full names users*/
	private $userList;

	public static function create(): ChatIndex
	{
		return new static();
	}

	private function __construct()
	{
	}

	/**
	 * @return bool
	 */
	public function isEmptyUsers(): bool
	{
		return empty($this->userList);
	}

	/**
	 * @return string
	 */
	public function getRowTitle(): string
	{
		return $this->title;
	}

	public function getClearedTitle(): string
	{
		return ChatIndex::clearText($this->title);
	}

	/**
	 * @param string $title
	 * @return ChatIndex
	 */
	public function setTitle(string $title): ChatIndex
	{
		$this->title = $title;

		return $this;
	}

	/**
	 * @return int
	 */
	public function getChatId(): int
	{
		return $this->chatId;
	}

	/**
	 * @param int $chatId
	 * @return ChatIndex
	 */
	public function setChatId(int $chatId): ChatIndex
	{
		$this->chatId = $chatId;

		return $this;
	}

	/**
	 * @return array
	 */
	public function getRowUserNameList(): array
	{
		return $this->userList;
	}

	/**
	 * @return array
	 */
	public function getClearedUserList(): array
	{
		$clearedUsers = [];
		foreach ($this->userList as $user)
		{
			$clearedUsers[] = ChatIndex::clearText($user);
		}

		return $clearedUsers;
	}

	/**
	 * @param array $users
	 * @return ChatIndex
	 */
	public function setUserList(array $users): ChatIndex
	{
		$this->userList = $users;

		return $this;
	}

	/**
	 * removes all special characters found in the text
	 *
	 * @see \Bitrix\Im\Internals\ChatIndex::CHARS_TO_REPLACE
	 * @param string $text
	 * @return string
	 */
	public static function clearText(string $text): string
	{
		$clearedText = str_replace(static::CHARS_TO_REPLACE, ' ', $text);
		$clearedText = preg_replace('/\s+/', ' ', $clearedText);

		return trim($clearedText);
	}

	public static function matchAgainstWildcard($phrase, $leftWildcard = '+' , $rightWildcard = '*', $minTokenSize = null)
	{
		$ftMinTokenSize = $minTokenSize ?: \Bitrix\Main\ORM\Query\Filter\Helper::getMinTokenSize();

		$orValues = [];

		//split to words by any non-word symbols
		$andValues = \Bitrix\Main\ORM\Query\Filter\Helper::splitWords($phrase);

		if(!empty($andValues))
		{
			$andValues = array_filter(
				$andValues,
				static function($val) use ($ftMinTokenSize)
				{
					return (mb_strlen($val) >= $ftMinTokenSize);
				}
			);

			if(!empty($andValues))
			{
				$orValues[] = $leftWildcard . implode($rightWildcard . " " . $leftWildcard, $andValues) . $rightWildcard;
			}
		}

		if(!empty($orValues))
		{
			return "(".implode(") (", $orValues).")";
		}

		return '';
	}
}