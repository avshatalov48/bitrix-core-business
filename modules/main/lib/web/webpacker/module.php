<?php

namespace Bitrix\Main\Web\WebPacker;

/**
 * Class Module
 *
 * @package Bitrix\Main\Web\WebPacker
 */
class Module
{
	/** @var string $name */
	protected $name;

	/** @var Resource\Package $package */
	protected $package;

	/** @var Resource\Profile $profile */
	protected $profile;

	/**
	 * Module constructor.
	 *
	 * @param @var string $name Name.
	 * @param Resource\Package|null $package Resource package.
	 * @param Resource\Profile|null $profile Profile.
	 */
	public function __construct($name, Resource\Package $package = null, Resource\Profile $profile = null)
	{
		$this->name = $name;

		if ($package)
		{
			$this->package = $package;
		}
		if ($profile)
		{
			$this->profile = $profile;
		}
	}

	/**
	 * Get name.
	 *
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * Set resource package.
	 *
	 * @param Resource\Package $package Resource package.
	 * @return $this
	 */
	public function setPackage(Resource\Package $package)
	{
		$this->package = $package;
		return $this;
	}

	/**
	 * Get package.
	 *
	 * @return Resource\Package|null
	 */
	public function getPackage()
	{
		return $this->package;
	}

	/**
	 * Set profile.
	 *
	 * @param Resource\Profile $profile Profile.
	 * @return $this
	 */
	public function setProfile(Resource\Profile $profile)
	{
		$this->profile = $profile;
		return $this;
	}

	/**
	 * Get profile.
	 *
	 * @return Resource\Profile|null
	 */
	public function getProfile()
	{
		return $this->profile;
	}

	/**
	 * To string.
	 *
	 * @return string
	 */
	public function __toString()
	{
		return $this->getName();
	}
}