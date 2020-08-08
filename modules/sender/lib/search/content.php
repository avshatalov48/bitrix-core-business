<?php
namespace Bitrix\Sender\Search;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\UserTable;
use Bitrix\Sender\Recipient;

/**
 * Class Content
 * @package Bitrix\Sender\Search
 */
class Content
{
	const TEXT = 0;
	const EMAIL = 1;
	const PHONE = 2;
	const USER = 3;
	const HTML_LAYOUT = 4;

	/** @var array $data Data. */
	private $data = [];

	/** @var array $callbacks Callback list. */
	private $callbacks = [];

	/**
	 * Encode text.
	 *
	 * @param string $text Text.
	 * @return string
	 */
	public static function encodeText($text)
	{
		return str_rot13(mb_strtoupper(trim($text)));
	}

	/**
	 * Get string.
	 *
	 * @return string
	 */
	public function getString()
	{
		foreach ($this->callbacks as $callback)
		{
			call_user_func_array($callback, [$this]);
		}

		return implode(' ', array_map([$this, 'encodeText'], $this->data));
	}

	/**
	 * Add callback.
	 *
	 * @param callable $callback Callback.
	 * @return $this
	 * @throws ArgumentException
	 */
	public function addCallback($callback)
	{
		if (!is_callable($callback))
		{
			throw new ArgumentException('Parameter `$callback` does not callable.');
		}
		$this->callbacks[] = $callback;
		return $this;
	}

	/**
	 * Clear.
	 *
	 * @return $this
	 */
	public function clear()
	{
		$this->data = array();
		return $this;
	}

	/**
	 * Add.
	 *
	 * @param string $value Value.
	 * @param integer $typeId Type ID.
	 * @param integer|null $length Length.
	 * @return $this
	 */
	public function add($value, $typeId = self::TEXT, $length = null)
	{
		switch ($typeId)
		{
			case self::EMAIL:
				$this->addEmail($value);
				break;

			case self::PHONE:
				$this->addPhone($value);
				break;

			case self::USER:
				$this->addUserByID($value);
				break;

			case self::HTML_LAYOUT:
				$this->addHtmlLayout($value, $length);
				break;

			case self::TEXT:
			default:
				$this->addText($value, $length);
		}

		return $this;
	}

	/**
	 * Add one field from fields.
	 *
	 * @param array $fields Fields.
	 * @param string $name Name.
	 * @param integer $typeId Type ID.
	 * @param integer|null $length Length.
	 * @return $this
	 */
	public function addField(array $fields, $name, $typeId = self::TEXT, $length = null)
	{
		$value = isset($fields[$name]) ? $fields[$name] : '';
		return $this->add($value, $typeId, $length);
	}

	/**
	 * Add text.
	 *
	 * @param string $text Text.
	 * @param integer|null $length Length.
	 * @return $this
	 */
	public function addText($text, $length = null)
	{
		if(!is_string($text))
		{
			$text = (string) $text;
		}

		$text = trim($text);
		if($length > 0)
		{
			$text = mb_substr($text, 0, $length);
		}

		if($text !== '' && !in_array($text, $this->data))
		{
			$this->data[] = $text;
		}

		return $this;
	}

	/**
	 * Add html layout.
	 *
	 * @param string $layout Layout.
	 * @param integer|null $length Length.
	 * @return $this
	 */
	public function addHtmlLayout($layout, $length = null)
	{
		return $this->addText(self::convertBodyHtmlToText($layout), $length);
	}

	/**
	 * Add recipient.
	 *
	 * @param string $code Code.
	 * @param integer $typeId Type ID.
	 * @return $this
	 */
	public function addRecipient($code, $typeId)
	{
		$code = Recipient\Normalizer::normalize($code, $typeId);
		if($code && Recipient\Validator::validate($code, $typeId))
		{
			$this->addText($code);
		}

		return $this;
	}

	/**
	 * Add email.
	 *
	 * @param string $email Email.
	 * @return $this
	 */
	public function addEmail($email)
	{
		return $this->addRecipient($email, Recipient\Type::EMAIL);
	}

	/**
	 * Add phone.
	 *
	 * @param string $phone Phone.
	 * @return $this
	 */
	public function addPhone($phone)
	{
		return $this->addRecipient($phone, Recipient\Type::PHONE);
	}

	/**
	 * Add user by id.
	 *
	 * @param integer $userID User ID.
	 * @return $this
	 */
	public function addUserById($userID)
	{
		$userData = UserTable::getRow([
			'select' => ['ID', 'LOGIN', 'NAME', 'LAST_NAME', 'SECOND_NAME', 'TITLE'],
			'filter' => ['=ID'=> $userID]
		]);

		if(is_array($userData))
		{
			$value = \CAllUser::formatName(\CAllSite::getNameFormat(), $userData, true, false);
			$this->addText($value);
		}

		return $this;
	}

	protected static function convertBodyHtmlToText($body)
	{
		// remove tags
		$innerBody = preg_replace('/<(script|iframe|style)(.*?)>(.*?)(<\\/\\1.*?>)/is', '', $body);
		$body = $innerBody ?: $body;

		// get <body> inner html if exists
		$innerBody = trim(preg_replace('/(.*?<body[^>]*>)(.*?)(<\/body>.*)/is', '$2', $body));
		$body = $innerBody ?: $body;

		// modify links to text version
		$body = preg_replace_callback(
			"/<a[^>]*?>([^>]*?)<\\/a>/i",
			function ($matches)
			{
				return $matches[1];
			},
			$body
		);

		// change <br> to new line
		$body = preg_replace('/\<br(\s*)?\/?\>/i', "\n", $body);

		// remove tags
		$body = strip_tags($body);

		// format text to the left side
		$lines = [];
		foreach (explode("\n", trim($body)) as $line)
		{
			$lines[] = trim($line);
		}

		// remove redundant new lines
		$body = preg_replace("/[\\n]{2,}/", " ", implode(" ", $lines));

		// remove redundant spaces
		$body = preg_replace("/[ \\t]{2,}/", " ", $body);

		// decode html-entities
		return html_entity_decode($body);
	}
}