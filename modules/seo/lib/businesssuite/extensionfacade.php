<?php

namespace Bitrix\Seo\BusinessSuite;

use Bitrix\Seo\BusinessSuite\Configuration;

final class ExtensionFacade
{

	/**@var Configuration\Facebook\Config $config*/
	private $config;

	/**@var Configuration\Facebook\Setup $setup*/
	private $setup;

	/**@var Configuration\Facebook\Installs $setup*/
	private $installs;

	/**@var ServiceAdapter $adapter*/
	private $adapter;

	/**@var bool $isExceptionHandled*/
	private $isExceptionHandled = false;

	public static function getInstance() : self
	{
		static $instance;
		if(!$instance)
		{
			$instance = new self();
		}
		return $instance;
	}

	private function __construct()
	{
		try
		{
			$this->adapter = ServiceAdapter::loadFacebookService();
			$this->config = Configuration\Facebook\Config::load();
			$this->setup = Configuration\Facebook\Setup::load();
			$this->installs = Configuration\Facebook\Installs::load();
		}
		catch (\Throwable $exception)
		{
			$this->isExceptionHandled = true;
		}
	}

	/**
	 * @return bool
	 */
	public function isInstalled() : bool
	{
		return (!$this->isExceptionHandled) && $this->setup && $this->installs && $this->config && $this->adapter;
	}

	/**
	 * @return Configuration\Facebook\Config
	 */
	public function getCurrentConfig() : ?Configuration\Facebook\Config
	{
		return $this->config;
	}

	/**
	 * @return Configuration\Facebook\Setup|null
	 */
	public function getCurrentSetup() : ?Configuration\Facebook\Setup
	{
		return $this->setup;
	}

	/**
	 * @return Configuration\Facebook\Installs|null
	 */
	public function getCurrentInstalls() : ?Configuration\Facebook\Installs
	{
		return $this->installs;
	}

	public function getServiceAdapter() : ?ServiceAdapter
	{
		return $this->adapter;
	}
}