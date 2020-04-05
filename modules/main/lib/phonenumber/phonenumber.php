<?php

namespace Bitrix\Main\PhoneNumber;

class PhoneNumber
{
	protected $rawNumber;
	protected $country;

	protected $valid = false;
	protected $countryCode;
	protected $nationalNumber;
	protected $nationalPrefix;
	protected $hasPlus = false;
	protected $numberType;
	protected $extension = '';
	protected $extensionSeparator;

	protected $international = false;

	public function format($formatType = '', $forceNationalPrefix = false)
	{
		if($this->valid)
		{
			if($formatType == '')
			{
				return Formatter::formatOriginal($this);
			}
			else
			{
				return Formatter::format($this, $formatType, $forceNationalPrefix);
			}
		}
		else
		{
			if($formatType == '' && ShortNumberFormatter::isApplicable($this))
			{
				return ShortNumberFormatter::format($this);
			}
			else
			{
				return $this->rawNumber;
			}
		}
	}

	/**
	 * @return string
	 */
	public function getRawNumber()
	{
		return $this->rawNumber;
	}

	/**
	 * @param string $rawNumber
	 */
	public function setRawNumber($rawNumber)
	{
		$this->rawNumber = $rawNumber;
	}

	/**
	 * @return mixed
	 */
	public function getCountry()
	{
		return $this->country;
	}

	/**
	 * @param mixed $country
	 */
	public function setCountry($country)
	{
		$this->country = $country;
	}

	/**
	 * @return string
	 */
	public function getNationalNumber()
	{
		return $this->nationalNumber;
	}

	/**
	 * @param string $nationalNumber
	 */
	public function setNationalNumber($nationalNumber)
	{
		$this->nationalNumber = $nationalNumber;
	}

	/**
	 * @return mixed
	 */
	public function getNumberType()
	{
		return $this->numberType;
	}

	/**
	 * @param mixed $numberType
	 */
	public function setNumberType($numberType)
	{
		$this->numberType = $numberType;
	}

	/**
	 * @return bool
	 */
	public function isValid()
	{
		return $this->valid;
	}

	/**
	 * @param bool $valid
	 */
	public function setValid($valid)
	{
		$this->valid = $valid;
	}

	/**
	 * @param string $countryCode
	 */
	public function setCountryCode($countryCode)
	{
		$this->countryCode = $countryCode;
	}

	public function getCountryCode()
	{
		return $this->countryCode;
	}

	public function hasExtension()
	{
		return $this->extension != '';
	}

	/**
	 * @return string
	 */
	public function getExtension()
	{
		return $this->extension;
	}

	/**
	 * @param string $extension
	 */
	public function setExtension($extension)
	{
		$this->extension = $extension;
	}

	/**
	 * @return mixed
	 */
	public function getExtensionSeparator()
	{
		return $this->extensionSeparator;
	}

	/**
	 * @param mixed $extensionSeparator
	 */
	public function setExtensionSeparator($extensionSeparator)
	{
		$this->extensionSeparator = $extensionSeparator;
	}

	/**
	 * @return bool
	 */
	public function isInternational()
	{
		return $this->international;
	}

	/**
	 * @param bool $international
	 */
	public function setInternational($international)
	{
		$this->international = $international;
	}

	/**
	 * @return string
	 */
	public function getNationalPrefix()
	{
		return $this->nationalPrefix;
	}

	/**
	 * @param string $nationalPrefix
	 */
	public function setNationalPrefix($nationalPrefix)
	{
		$this->nationalPrefix = $nationalPrefix;
	}

	/**
	 * @return bool
	 */
	public function hasPlus()
	{
		return $this->hasPlus;
	}

	/**
	 * @param bool $hasPlus
	 */
	public function setHasPlus($hasPlus)
	{
		$this->hasPlus = $hasPlus;
	}
}