<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2018 Bitrix
 */
namespace Bitrix\Main\Mail;

/**
 * Class Address
 * @package Bitrix\Main\Mail
 */
class Address
{
	/** @var string|null $name Name. */
	protected $name = null;

	/** @var string|null $email Email. */
	protected $email = null;

	/**
	 * Return true if is valid.
	 *
	 * @param string $address Address.
	 * @return bool
	 */
	public static function isValid($address)
	{
		return (new static($address))->validate();
	}

	/**
	 * Address constructor.
	 *
	 * @param string|null $address Address.
	 */
	public function __construct($address = null)
	{
		if ($address)
		{
			$this->set($address);
		}
	}

	/**
	 * Get address.
	 *
	 * @return null|string
	 */
	public function get()
	{
		if (!$this->email)
		{
			return null;
		}

		$address = '';
		if ($this->name)
		{
			$address = $this->name . ' ';
		}

		$address .= "<{$this->email}>";

		return $address;
	}

	/**
	 * Set address.
	 *
	 * @param null|string $address
	 * @return $this
	 */
	public function set($address)
	{
		$this->parse($address);
		return $this;
	}

	/**
	 * Get name.
	 *
	 * @return null|string
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * Set name.
	 *
	 * @param null|string $name
	 * @return $this
	 */
	public function setName($name)
	{
		$name = trim($name, "\"\x20\t\n\r\0\x0b");
		if ($name != '')
		{
			$name = str_replace(
				array('\\', '"', '<', '>'),
				array('/', '\'', '(', ')'),
				$name
			);
		}

		$this->name = $name;
		return $this;
	}

	/**
	 * Get email.
	 *
	 * @return null|string
	 */
	public function getEmail()
	{
		return $this->email;
	}

	/**
	 * Set email.
	 *
	 * @param null|string $email
	 * @return $this
	 */
	public function setEmail($email)
	{
		$email = strtolower(trim($email));
		if (!check_email($email, true))
		{
			$email = null;
		}

		$this->email = $email;
		return $this;
	}



	/**
	 * Validate address.
	 *
	 * @return bool.
	 */
	public function validate()
	{
		return !empty($this->email);
	}

	/**
	 * Parse address.
	 *
	 * @param string $address Address.
	 * @return void
	 */
	protected function parse($address)
	{
		$this->setName('');
		$this->setEmail('');

		if (!$address)
		{
			return;
		}

		if (preg_match('/(.*)<(.+?)>\s*$/is', $address, $matches))
		{
			$this->setName($matches[1]);
			$this->setEmail($matches[2]);
		}
		else
		{
			$this->setEmail($address);
		}
	}
}
