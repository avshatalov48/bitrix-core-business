<?php

namespace Bitrix\Seo\Controller\Business;

use Bitrix\Main;
use Bitrix\Seo\BusinessSuite\ServiceAdapter;
use Bitrix\Seo\BusinessSuite\Utils;
use Bitrix\Seo\BusinessSuite;
use Bitrix\Main\Engine\Response\AjaxJson;
use Bitrix\Seo\BusinessSuite\Configuration\Facebook;

final class Extension extends Main\Engine\Controller
{
	public function installAction($engineCode, $setup, $config): AjaxJson
	{
		$response = null;
		try
		{
			if (is_string($engineCode) && is_array($setup) && is_array($config))
			{
				$response = AjaxJson::createSuccess([
						'authUrl' => ServiceAdapter::createServiceWrapperContainer()
							->setService($service = Utils\ServiceFactory::getServiceByEngineCode($engineCode))
							->getAuthAdapter($service::getTypeByEngine($engineCode))
							->setConfig($config = Facebook\Config::loadFromArray($config))
							->setSetup($setup = Facebook\Setup::loadFromArray($setup))
							->setInstalls(Facebook\Installs::load())
							->getAuthUrl()
					]);
				$response = ($setup->save()? $response : AjaxJson::createError(null,[]));
			}
		}
		catch (BusinessSuite\Exception\ConfigException $exception)
		{
			$handler = BusinessSuite\Exception\ConfigExceptionHandler::handle($exception);
			$response = AjaxJson::createError($handler->getErrorCollection(),$handler->getCustomData());
		}
		catch (\Throwable $exception)
		{
			$response = AjaxJson::createError();
		}
		finally
		{
			return $response;
		}
	}
	public function uninstallAction($type)
	{
		$response = null;
		try
		{
			if(ServiceAdapter::load($type)->getExtension()->uninstall()->isSuccess())
			{
				$response = AjaxJson::createSuccess();
			}
		}
		finally
		{
			return $response ?? AjaxJson::createError();
		}
	}
}