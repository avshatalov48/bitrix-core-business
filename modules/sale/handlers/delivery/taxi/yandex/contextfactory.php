<?php

namespace Sale\Handlers\Delivery\Taxi\Yandex;

use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;
use Bitrix\Main\ModuleManager;
use Bitrix\Main\SystemException;
use Sale\Handlers\Delivery\Taxi\Yandex\Crm;
use \Sale\Handlers\Delivery\Taxi\Yandex\SiteManager;

/**
 * Class ContextFactory
 * @package Sale\Handlers\Delivery\Taxi\Yandex
 */
class ContextFactory
{
	/** @var Crm\Context */
	protected $crmContext;

	/** @var SiteManager\Context */
	protected $siteManagerContext;

	/**
	 * ContextFactory constructor.
	 * @param ContextContract $crmContext
	 * @param ContextContract $siteManagerContext
	 */
	public function __construct(ContextContract $crmContext, ContextContract $siteManagerContext)
	{
		$this->crmContext = $crmContext;
		$this->siteManagerContext = $siteManagerContext;
	}

	/**
	 * @return ContextContract
	 * @throws SystemException
	 * @throws \Bitrix\Main\ArgumentNullException
	 * @throws \Bitrix\Main\ArgumentOutOfRangeException
	 * @throws \Bitrix\Main\LoaderException
	 */
	public function makeContext(): ContextContract
	{
		if (ModuleManager::isModuleInstalled('crm')
			&& Option::get('sale', '~IS_SALE_CRM_SITE_MASTER_FINISH', 'N') != 'Y')
		{
			if (!Loader::includeModule('crm'))
			{
				throw new SystemException('crm module is required');
			}

			return $this->crmContext;
		}

		return $this->siteManagerContext;
	}
}
