<?php

namespace Bitrix\Main\Search;

use Bitrix\Main\ORM\Query\Filter;
use Bitrix\Main\PhoneNumber;

class MapBuilder
{
	/** @var array [search_token => true] */
	protected $tokens = array();

	/**
	 * StringBuilder constructor.
	 */
	public function __construct()
	{

	}

	/**
	 * Creates instance of the StringBuilder
	 * @return static
	 */
	public static function create()
	{
		return new static();
	}

	/**
	 * Adds arbitrary integer content to the builder.
	 * @param string $token Arbitrary string.
	 * @return $this.
	 */
	public function addText($token)
	{
		$token = (string)$token;
		if($token == '')
			return $this;

		$value = Content::prepareStringToken($token);
		$this->tokens[$value] = true;
		return $this;
	}

	/**
	 * Adds digit content to the builder.
	 * @param int $token .
	 * @return $this.
	 */
	public function addInteger($token)
	{
		if (!Content::isIntegerToken($token))
			return $this;

		$token = Content::prepareIntegerToken($token);

		$this->tokens[$token] = true;
		return $this;
	}

	/**
	 * Adds phone number to the builder.
	 * @param string $phone Phone number.
	 * @return $this
	 */
	public function addPhone($phone)
	{
		$phone = (string)$phone;
		$value = preg_replace("/[^0-9\#\*]/i", "", $phone);
		if($value == '')
			return $this;

		$altPhone = str_replace(' ', '', $phone);
		$this->tokens[$altPhone] = true;

		$convertedPhone = PhoneNumber\Parser::getInstance()
			->parse($altPhone)
			->format(PhoneNumber\Format::E164);
		if ($convertedPhone != $altPhone)
		{
			$this->tokens[$convertedPhone] = true;
		}

		$length = mb_strlen($value);
		if($length >= 10 && mb_substr($value, 0, 1) === '7')
		{
			$altPhone = '8'.mb_substr($value, 1);
			$this->tokens[$altPhone] = true;
		}

		//Right bound. We will stop when 3 digits are left.
		$bound = $length - 2;
		if($bound > 0)
		{
			for($i = 0; $i < $bound; $i++)
			{
				$key = mb_substr($value, $i);
				$this->tokens[$key] = true;
			}
		}

		return $this;
	}

	/**
	 * Adds email to the builder.
	 * @param string $email Email.
	 * @return $this
	 */
	public function addEmail($email)
	{
		if($email === '')
		{
			return $this;
		}

		$keys = preg_split('/\W+/', $email, -1, PREG_SPLIT_NO_EMPTY);
		foreach($keys as $key)
		{
			$key = Content::prepareStringToken($key);
			if(!isset($this->tokens[$key]))
			{
				$this->tokens[$key] = true;
			}
		}

		$key = Content::prepareStringToken($email);
		$this->tokens[$key] = true;

		return $this;
	}

	/**
	 * Adds full user name to the builder.
	 * @param array|int $userId Id of the user.
	 * @return $this
	 */
	public function addUser($userId)
	{
		if(empty($userId))
		{
			return $this;
		}

		$orm = \Bitrix\Main\UserTable::getList(Array(
			'select' => array('ID', 'LOGIN', 'NAME', 'LAST_NAME', 'SECOND_NAME', 'TITLE', 'EMAIL', 'PERSONAL_MOBILE'),
			'filter' => array('=ID' => $userId)
		));

		while($user = $orm->fetch())
		{
			$value = \CUser::FormatName(
				\CSite::GetNameFormat(),
				$user,
				true,
				false
			);

			$value = Content::prepareStringToken($value);
			if($value != '')
			{
				$this->tokens[$value] = true;
			}

			self::addPhone($user['PERSONAL_MOBILE']);
			self::addEmail($user['EMAIL']);
		}

		return $this;
	}

	/**
	 * Builds search string.
	 * @return string
	 */
	public function build()
	{
		$tokens = array();

		$minTokenSize = Filter\Helper::getMinTokenSize();

		foreach ($this->tokens as $token => $result)
		{
			if (mb_strlen($token) >= $minTokenSize)
			{
				$tokens[$token] = $result;
			}
		}

		return implode(" ", array_keys($tokens));
	}
}
