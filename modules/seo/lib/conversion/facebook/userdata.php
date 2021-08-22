<?php

namespace Bitrix\Seo\Conversion\Facebook;

use Bitrix\Main\Type\DateTime;

final class UserData
{
	public const GENDER_FEMALE = 'f';
	public const GENDER_MALE = 'm';

	private $container = [];

	/**
	 * UserData constructor.
	 *
	 * @param array|null $params
	 */
	public function __construct(?array $params = null)
	{
		if ($params && !empty($params))
		{
			if (array_key_exists('email', $params) && is_string($params['email']))
			{
				$this->setEmail($params['email']);
			}
			if (array_key_exists('phone', $params) && is_string($params['phone']))
			{
				$this->setPhone($params['phone']);
			}
			if (array_key_exists('gender', $params) && is_string($params['gender']))
			{
				$this->setGender($params['gender']);
			}
			if (array_key_exists('date_of_birth', $params) && $params['date_of_birth'] instanceof DateTime)
			{
				$this->setDateOfBirth($params['date_of_birth']);
			}
			if (array_key_exists('last_name', $params) && is_string($params['last_name']))
			{
				$this->setLastName($params['last_name']);
			}
			if (array_key_exists('first_name', $params) && is_string($params['first_name']))
			{
				$this->setFirstName($params['first_name']);
			}
			if (array_key_exists('city', $params) && is_string($params['city']))
			{
				$this->setCity($params['city']);
			}
			if (array_key_exists('client_ip_address', $params) && is_string($params['client_ip_address']))
			{
				$this->setClientIpAddress($params['client_ip_address']);
			}
			if (array_key_exists('client_user_agent', $params) && is_string($params['client_user_agent']))
			{
				$this->setClientUserAgent($params['client_user_agent']);
			}
			if (array_key_exists('fbc', $params) && is_string($params['fbc']))
			{
				$this->setFacebookClick($params['fbc']);
			}
			if (array_key_exists('fbp', $params) && is_string($params['fbp']))
			{
				$this->setFacebookPixel($params['fbp']);
			}
		}
	}

	/**
	 * @param string|null $email
	 *
	 * @return $this
	 */
	public function setEmail(?string $email)
	{
		if (check_email($email))
		{
			$this->container['email'] = $email;
		}

		return $this;
	}

	/**
	 * @param string|null $phone
	 *
	 * @return $this
	 */
	public function setPhone(?string $phone)
	{
		if (preg_match('/^[\+]?[\d]{4,25}$/', $phone))
		{
			$this->container['phone'] = $phone;
		}

		return $this;
	}

	/**
	 * @param string|null $gender
	 *
	 * @return $this
	 */
	public function setGender(?string $gender)
	{
		if (in_array($gender, [static::GENDER_FEMALE, static::GENDER_MALE]))
		{
			$this->container['gender'] = $gender;
		}

		return $this;
	}

	/**
	 * @param DateTime|null $date
	 *
	 * @return $this
	 */
	public function setDateOfBirth(?DateTime $date)
	{
		if ($date)
		{
			$this->container['date_of_birth'] = $date->format('Ymd');
		}

		return $this;
	}

	/**
	 * @param string|null $lastName
	 *
	 * @return $this
	 */
	public function setLastName(?string $lastName)
	{
		$this->container['last_name'] = $lastName;

		return $this;
	}

	/**
	 * @param string|null $name
	 *
	 * @return $this
	 */
	public function setFirstName(?string $name)
	{
		$this->container['first_name'] = $name;

		return $this;
	}

	/**
	 * @param string|null $city
	 *
	 * @return $this
	 */
	public function setCity(?string $city)
	{
		$this->container['city'] = $city;

		return $this;
	}

	/**
	 * @param string|null $ipAddress
	 *
	 * @return $this
	 */
	public function setClientIpAddress(?string $ipAddress)
	{
		if ($ipAddress &&
			preg_match('/^[1-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}.[0-9]{1,3}$/i', $ipAddress) &&
			array_reduce(explode('.', $ipAddress),
				function($element) {
					$element = (int)$element;

					return $element > 0 && $element < 255;
				},
				true))
		{
			$this->container['client_ip_address'] = $ipAddress;
		}

		return $this;
	}

	public function setClientUserAgent(?string $userAgent)
	{
		if ($userAgent)
		{
			$this->container['client_user_agent'] = $userAgent;
		}

		return $this;
	}

	/**
	 * doc: https://developers.facebook.com/docs/marketing-api/conversions-api/parameters/fbp-and-fbc/#fbc
	 *
	 * @param string|null $facebookClick
	 *
	 * @return $this
	 */
	public function setFacebookClick(?string $facebookClick)
	{
		if ($facebookClick && preg_match('/^fb\.[0-2]{1}\.[0-9]+\.[0-9a-z]+$/i', $facebookClick))
		{
			$this->container['fbc'] = $facebookClick;
		}

		return $this;
	}

	/**
	 * doc: https://developers.facebook.com/docs/marketing-api/conversions-api/parameters/fbp-and-fbc/#fbp
	 *
	 * @param string|null $facebookPixel
	 *
	 * @return $this
	 */
	public function setFacebookPixel(?string $facebookPixel)
	{
		if ($facebookPixel && preg_match('/^fb\.[0-2]{1}\.[0-9]+\.[0-9]+$/i', $facebookPixel))
		{
			$this->container['fbp'] = $facebookPixel;
		}

		return $this;
	}

	public function getEmail()
	{
		return $this->container['email'];
	}

	public function getPhone()
	{
		return $this->container['phone'];
	}

	public function getGender()
	{
		return $this->container['email'];
	}

	public function getDateOfBirth()
	{
		return $this->container['date_of_birth'];
	}

	public function getLastName()
	{
		return $this->container['last_name'];
	}

	public function getFirstName()
	{
		return $this->container['first_name'];
	}

	public function getCity()
	{
		return $this->container['city'];
	}

	public function getClientIpAddress()
	{
		return $this->container['client_ip_address'];
	}

	public function getClientUserAgent()
	{
		return $this->container['client_user_agent'];
	}

	public function getFacebookClick()
	{
		return $this->container['fbc'];
	}

	public function getFacebookPixel()
	{
		return $this->container['fbp'];
	}

	public function validate(): bool
	{
		if (0 === $count = count($this->container))
		{
			return false;
		}
		elseif ($count === 1)
		{
			return !($this->container['client_user_agent'] || $this->container['client_ip_address']);
		}

		return true;
	}

	public function toArray()
	{
		return $this->container;
	}
}