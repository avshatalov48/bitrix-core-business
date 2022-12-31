<?php
namespace Bitrix\Sale\Domain\Verification;

use Bitrix\Main,
	Bitrix\Landing,
	Bitrix\Main\NotImplementedException,
	Bitrix\Main\Event;

/**
 * Class Manager
 * @package Bitrix\Main\Domain
 */
abstract class BaseManager
{
	private const ON_BUILD_VERIFICATION_MANAGER_LIST = "onBuildVerificationManagerList";

	/**
	 * @return string
	 */
	abstract public static function getPathPrefix(): string;

	/**
	 * @return string
	 */
	abstract protected static function getUrlRewritePath(): string;

	/**
	 * @return array
	 * @throws Main\ArgumentException
	 * @throws Main\LoaderException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	public static function getSiteList() : array
	{
		$siteList = [];

		$res = Main\SiteTable::getList([
			"select" => ["ID", "NAME", "SERVER_NAME"],
			"filter" => [
				"ACTIVE" => "Y"
			]
		]);
		while ($row = $res->fetch())
		{
			if (empty($row["SERVER_NAME"]))
			{
				$row["SERVER_NAME"] = Main\Application::getInstance()->getContext()->getServer()->getServerName();
			}

			$siteList[] = $row;
		}

		$landingSiteList = self::getLandingSiteList();
		if ($landingSiteList)
		{
			$siteList = array_merge($siteList, $landingSiteList);
		}

		return $siteList;
	}

	/**
	 * @return array
	 * @throws Main\LoaderException
	 */
	public static function getLandingSiteList() : array
	{
		$landingSiteList = [];

		if (Main\Loader::includeModule('landing'))
		{
			$res = Landing\Site::getList([
				'select' => [
					'ID', 'NAME' => 'TITLE', 'SERVER_NAME' => 'DOMAIN.DOMAIN'
				],
				'filter' => [
					'ACTIVE' => 'Y',
					'TYPE' => 'STORE'
				]
			]);
			while ($row = $res->fetch())
			{
				$landingSiteList[] = $row;
			}
		}

		return $landingSiteList;
	}

	/**
	 * @param $domain
	 * @return bool
	 * @throws Main\LoaderException
	 */
	public static function isLandingSite($domain) : bool
	{
		$landingSiteList = self::getLandingSiteList();

		$result = array_filter($landingSiteList, static function($site) use ($domain) {
			return $site['SERVER_NAME'] === $domain;
		});

		return $result ? true : false;
	}

	/**
	 * @param array $parameters
	 * @return Main\ORM\Query\Result
	 * @throws Main\ArgumentException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	public static function getList(array $parameters = []) : Main\ORM\Query\Result
	{
		return Internals\DomainVerificationTable::getList($parameters);
	}

	/**
	 * @param $data
	 * @param $file (from $_FILES)
	 * @return Main\ORM\Data\AddResult|Main\ORM\Data\UpdateResult|Main\Result
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentNullException
	 * @throws Main\IO\FileNotFoundException
	 * @throws Main\LoaderException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	public static function save($data, $file)
	{
		if (empty($file["tmp_name"]) || empty($file["name"]))
		{
			return (new Main\Result())->addError(new Main\Error("File not found"));
		}

		$data["CONTENT"] = self::readFile($file["tmp_name"]);

		$data = self::prepareData($data);
		$checkDataResult = self::checkData($data);
		if (!$checkDataResult->isSuccess())
		{
			return $checkDataResult;
		}

		$res = Internals\DomainVerificationTable::getList([
			"select" => ["ID"],
			"filter" => [
				"=PATH" => $data["PATH"],
				"=DOMAIN" => $data["DOMAIN"],
			]
		])->fetch();
		if ($res)
		{
			return Internals\DomainVerificationTable::update($res["ID"], [
				"CONTENT" => $data["CONTENT"],
				"ENTITY" => $data["ENTITY"],
			]);
		}

		$addResult = Internals\DomainVerificationTable::add($data);
		if ($addResult->isSuccess())
		{
			if (self::canUseUrlRewrite($data["DOMAIN"]))
			{
				self::addUrlRewrite($data["DOMAIN"], $data["PATH"]);
			}
			elseif (self::isLandingSite($data["DOMAIN"]))
			{
				self::registerLandingEventHandler();
			}
			else
			{
				self::registerB24EventHandler();
			}
		}

		return $addResult;
	}

	/**
	 * @param array $data
	 * @return array
	 */
	private static function prepareData(array $data) : array
	{
		if ($data["PATH"])
		{
			$data["PATH"] = static::getPathPrefix().$data["PATH"];
		}

		$data["DOMAIN"] = self::prepareDomain($data["DOMAIN"]);

		return $data;
	}

	/**
	 * @param $data
	 * @return Main\Result
	 */
	private static function checkData($data) : Main\Result
	{
		$result = new Main\Result();

		if (!isset($data["PATH"]))
		{
			$result->addError(new Main\Error("Path not found"));
		}
		elseif (!isset($data["DOMAIN"]))
		{
			$result->addError(new Main\Error("Domain not found"));
		}
		elseif (!isset($data["CONTENT"]))
		{
			$result->addError(new Main\Error("Content not found"));
		}
		elseif (!isset($data["ENTITY"]))
		{
			$result->addError(new Main\Error("Entity not found"));
		}

		return $result;
	}

	/**
	 * @param $id
	 * @return Main\ORM\Data\DeleteResult
	 * @throws \Exception
	 */
	public static function delete($id): Main\ORM\Data\DeleteResult
	{
		$domainVerificationData = Internals\DomainVerificationTable::getById($id)->fetch();

		$deleteResult = Internals\DomainVerificationTable::delete($id);
		if ($deleteResult->isSuccess())
		{
			if (self::canUseUrlRewrite($domainVerificationData["DOMAIN"]))
			{
				self::deleteUrlRewrite($domainVerificationData["DOMAIN"], $domainVerificationData["PATH"]);
			}
			else
			{
				self::unRegisterEventHandlers();
			}
		}

		return $deleteResult;
	}

	/**
	 * @param $path
	 * @return bool|false|string
	 * @throws Main\IO\FileNotFoundException
	 */
	private static function readFile($path)
	{
		$file = new Main\IO\File($path);
		if ($file->isExists())
		{
			return $file->getContents();
		}

		return null;
	}

	/**
	 * @param $domain
	 * @return mixed|string
	 */
	private static function prepareDomain($domain)
	{
		$domain = filter_var($domain, FILTER_SANITIZE_URL);
		$domain = trim($domain, " \t\n\r\0\x0B/\\");
		$components = parse_url($domain);

		if (isset($components["host"]) && !empty($components["host"]))
		{
			return $components["host"];
		}

		if (isset($components["path"]) && !empty($components["path"]))
		{
			return $components["path"];
		}

		return $domain;
	}

	/**
	 * @param $entityName
	 * @return bool
	 * @throws NotImplementedException
	 */
	public static function needVerification($entityName) : bool
	{
		$handlerList = static::getEntityList();
		return in_array($entityName, $handlerList);
	}

	/**
	 * @return array
	 * @throws NotImplementedException
	 */
	abstract protected static function getEntityList() : array;

	/**
	 * @param string $domain
	 * @param string $path
	 * @return bool
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentNullException
	 * @throws Main\LoaderException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	private static function addUrlRewrite(string $domain, string $path): bool
	{
		$fields = [
			"CONDITION" => "#^{$path}#",
			"RULE" => "",
			"ID" => "",
			"PATH" => static::getUrlRewritePath(),
		];

		$siteId = self::getSiteIdByDomain($domain);
		if (!$siteId)
		{
			return false;
		}

		Main\UrlRewriter::add($siteId, $fields);
		return true;
	}

	/**
	 * @param string $domain
	 * @param string $path
	 * @return bool
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentNullException
	 * @throws Main\LoaderException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	private static function deleteUrlRewrite(string $domain, string $path): bool
	{
		$fields = [
			"CONDITION" => "#^{$path}#",
			"PATH" => static::getUrlRewritePath(),
		];

		$siteId = self::getSiteIdByDomain($domain);
		if (!$siteId)
		{
			return false;
		}

		Main\UrlRewriter::delete($siteId, $fields);

		return true;
	}

	/**
	 * @param $domain
	 * @return mixed|null
	 * @throws Main\ArgumentException
	 * @throws Main\LoaderException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	private static function getSiteIdByDomain($domain)
	{
		$site = array_filter(static::getSiteList(), function($site) use ($domain) {
			return $domain === $site["SERVER_NAME"];
		});

		if (!$site)
		{
			return null;
		}

		$site = current($site);
		if ($site["ID"])
		{
			return $site["ID"];
		}

		return null;
	}

	/**
	 * @param $serverName
	 * @param $requestUri
	 * @return array|false
	 * @throws Main\ArgumentException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	public static function searchByRequest($serverName, $requestUri)
	{
		return self::getList([
			"select" => ["*"],
			"filter" => [
				"=PATH" => $requestUri,
				"=DOMAIN" => $serverName,
			],
			"limit" => 1
		])->fetch();
	}

	/**
	 * @return bool
	 */
	private static function isB24(): bool
	{
		return Main\ModuleManager::isModuleInstalled('bitrix24');
	}

	/**
	 * @return array|string[]
	 * @throws Main\LoaderException
	 */
	public static function getManagerList(): array
	{
		$handlerList = [
			'\Bitrix\Sale\PaySystem\Domain\Verification\Manager' => '/bitrix/modules/sale/lib/paysystem/domain/verification/manager.php',
		];

		$event = new Event('sale', self::ON_BUILD_VERIFICATION_MANAGER_LIST);
		$event->send();

		$resultList = $event->getResults();
		if (is_array($resultList) && !empty($resultList))
		{
			$customHandlerList = [];
			foreach ($resultList as $eventResult)
			{
				/** @var  Main\EventResult $eventResult */
				if ($eventResult->getType() === Main\EventResult::SUCCESS)
				{
					$params = $eventResult->getParameters();
					if (!empty($params) && is_array($params))
					{
						$customHandlerList[] = $params;
					}
				}
			}

			$handlerList = array_merge($handlerList, ...$customHandlerList);
		}

		Main\Loader::registerAutoLoadClasses(null, $handlerList);

		return $handlerList;
	}

	/**
	 * @param $domain
	 * @return bool
	 * @throws Main\LoaderException
	 */
	private static function canUseUrlRewrite($domain): bool
	{
		return (!self::isB24() && !self::isLandingSite($domain));
	}

	private static function registerLandingEventHandler(): void
	{
		$eventManager = Main\EventManager::getInstance();
		$handlers = $eventManager->findEventHandlers('landing', 'onPubHttpStatus');
		$onPubHttStatus = array_filter($handlers, static function($handler) {
			return $handler['TO_METHOD'] === 'landingDomainVerificationHandler';
		});
		if (!$onPubHttStatus)
		{
			$eventManager->registerEventHandler(
				'landing',
				'onPubHttpStatus',
				'sale',
				'\Bitrix\Sale\Domain\Verification\Service',
				'landingDomainVerificationHandler'
			);
		}
	}

	private static function registerB24EventHandler(): void
	{
		$eventManager = Main\EventManager::getInstance();
		$handlers = $eventManager->findEventHandlers('main', 'OnEpilog');
		$onEpilog = array_filter($handlers, static function($handler) {
			return $handler['TO_METHOD'] === 'b24DomainVerificationHandler';
		});
		if (!$onEpilog)
		{
			$eventManager->registerEventHandler(
				'main',
				'OnEpilog',
				'sale',
				'\Bitrix\Sale\Domain\Verification\Service',
				'b24DomainVerificationHandler');
		}
	}

	private static function unRegisterEventHandlers(): void
	{
		$domainVerificationList = Internals\DomainVerificationTable::getList()->fetchAll();

		$needUnRegisterLandingHandler = true;
		$needUnRegisterB24Handler = true;

		foreach ($domainVerificationList as $domainVerification)
		{
			if (self::isLandingSite($domainVerification['DOMAIN']))
			{
				$needUnRegisterLandingHandler = false;
			}
			else
			{
				$needUnRegisterB24Handler = false;
			}
		}

		if ($needUnRegisterLandingHandler)
		{
			self::unRegisterLandingEventHandler();
		}

		if ($needUnRegisterB24Handler)
		{
			self::unRegisterB24EventHandler();
		}
	}

	private static function unRegisterLandingEventHandler(): void
	{
		$eventManager = Main\EventManager::getInstance();
		$eventManager->unRegisterEventHandler(
			'landing',
			'onPubHttpStatus',
			'sale',
			'\Bitrix\Sale\Domain\Verification\Service',
			'landingDomainVerificationHandler'
		);
	}

	private static function unRegisterB24EventHandler(): void
	{
		$eventManager = Main\EventManager::getInstance();
		$eventManager->unRegisterEventHandler(
			'main',
			'OnEpilog',
			'sale',
			'\Bitrix\Sale\Domain\Verification\Service',
			'b24DomainVerificationHandler'
		);
	}
}