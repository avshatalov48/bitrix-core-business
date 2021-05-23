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
	/**
	 * Install fbe action
	 * @param $engineCode
	 * @param $setup
	 * @param $config
	 *
	 * @return AjaxJson
	 */
	public function installAction($engineCode, $setup, $config): AjaxJson
	{
		$response = null;
		try
		{
			if (is_string($engineCode) && is_array($setup) && is_array($config))
			{
				$meta = BusinessSuite\ServiceMetaData::create()
					->setService($service = Utils\ServiceFactory::getServiceByEngineCode($engineCode))
					->setEngineCode($engineCode)
					->setType($service::getTypeByEngine($engineCode));

				$serviceContainer = ServiceAdapter::createServiceWrapperContainer()->setMeta($meta);

				$response = AjaxJson::createSuccess([
						'authUrl' => $serviceContainer->getAuthAdapter($meta->getType())
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
}