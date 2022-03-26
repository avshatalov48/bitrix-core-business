<?php
namespace Bitrix\Sale\Domain\Verification;

use Bitrix\Main,
	Bitrix\Landing;

/**
 * Class Service
 * @package Bitrix\Main\Domain
 */
final class Service
{
	/**
	 * @param Main\Event $event
	 * @return Main\Entity\EventResult
	 * @throws Main\ArgumentException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 * @noinspection PhpUnused
	 */
	public static function landingDomainVerificationHandler(Main\Event $event): Main\Entity\EventResult
	{
		$result = new Main\Entity\EventResult;

		if (class_exists('\LandingPubComponent'))
		{
			$code = $event->getParameter('code');
			if ($code === \LandingPubComponent::ERROR_STATUS_NOT_FOUND)
			{
				$landingInstance = \LandingPubComponent::getMainInstance();
				if ($landingInstance)
				{
					$res = Landing\Site::getList([
						'select' => [
							'DOMAIN_NAME' => 'DOMAIN.DOMAIN'
						],
						'filter' => [
							'CHECK_PERMISSIONS' => 'N',
							'ID' => $landingInstance['SITE_ID']
						]
					]);
					if ($row = $res->fetch())
					{
						$context = Main\Application::getInstance()->getContext();

						$realFilePath = $context->getServer()->get('REAL_FILE_PATH');
						if (!$realFilePath)
						{
							$realFilePath = $_SERVER['REAL_FILE_PATH'] ?? null;
						}
						if (!$realFilePath)
						{
							$realFilePath = $context->getServer()->get('SCRIPT_NAME');
						}

						$requestURL = $context->getRequest()->getRequestedPage();

						$landingUrl = Landing\Site::getPublicUrl($landingInstance['SITE_ID']);
						$realFilePath = str_replace('/index.php', '/', $realFilePath);
						if (mb_strpos($landingUrl, $realFilePath) === false)
						{
							$requestURL = str_replace($realFilePath.$landingInstance['SITE_ID'], '', $requestURL);
						}

						$domainVerification = BaseManager::searchByRequest($row['DOMAIN_NAME'], $requestURL);

						if (!$domainVerification)
						{
							$pubPath = \Bitrix\Landing\Manager::getPublicationPath($landingInstance['SITE_ID']);
							$domainVerification = BaseManager::searchByRequest($row['DOMAIN_NAME'], substr($requestURL, strlen($pubPath)-1));
						}

						if ($domainVerification)
						{
							$result->modifyFields([
								'code' => \LandingPubComponent::ERROR_STATUS_OK
							]);

							self::setEndBufferContentHandler($domainVerification['CONTENT']);
						}
					}
				}
			}
		}

		return $result;
	}

	/**
	 * @throws Main\ArgumentException
	 * @throws Main\LoaderException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 * @noinspection PhpUnused
	 */
	public static function b24DomainVerificationHandler(): void
	{
		if (!Main\ModuleManager::isModuleInstalled('bitrix24'))
		{
			return;
		}

		$isFound = false;
		$managerList = BaseManager::getManagerList();

		/** @var BaseManager $manager */
		foreach (array_keys($managerList) as $manager)
		{
			if (strpos($_SERVER['REQUEST_URI'], $manager::getPathPrefix()) !== false)
			{
				$isFound = true;
				break;
			}
		}

		if ($isFound)
		{
			$context = Main\Application::getInstance()->getContext();
			$serverName = $context->getServer()->getServerName();
			$requestUri = $context->getServer()->getRequestUri();

			$hasParams = strpos($requestUri, '?');
			$requestUriWithoutParams = ($hasParams !== false)
				? substr($requestUri, 0, $hasParams)
				: $requestUri;

			$domainVerification = BaseManager::searchByRequest($serverName, $requestUriWithoutParams);
			if ($domainVerification)
			{
				self::setEndBufferContentHandler($domainVerification['CONTENT']);
			}

			\CHTTP::SetStatus('200 OK');
		}
	}

	/**
	 * @param $content
	 */
	public static function setEndBufferContentHandler($content): void
	{
		$eventManager = Main\EventManager::getInstance();
		$eventManager->addEventHandler(
			'main',
			'onEndBufferContent',
			static function(&$resultContent) use ($content)
			{
				header('Content-Type: text/plain');
				$resultContent = $content;
			}
		);
	}
}