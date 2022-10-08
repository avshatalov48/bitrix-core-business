<?php

namespace Bitrix\Main;

use Bitrix\Main\Config;
use Bitrix\Main\Localization;

/**
 * Class HttpContext extends Context with http specific methods.
 * @package Bitrix\Main
 */
class HttpContext extends Context
{
	public const CACHE_TTL = 86400;

	/**
	 * Creates new instance of context.
	 *
	 * @param HttpApplication $application
	 */
	public function __construct(HttpApplication $application)
	{
		parent::__construct($application);
	}

	public function rewriteUri($url, $queryString, $redirectStatus = null)
	{
		$request = $this->request;
		$request->modifyByQueryString($queryString);

		$this->server->rewriteUri($url, $queryString, $redirectStatus);
	}

	public function transferUri($url, $queryString)
	{
		$request = $this->request;
		$request->modifyByQueryString($queryString);

		$this->server->transferUri($url, $queryString);
	}

	/**
	 * @param string | null $definedSite
	 * @param string | null $definedLanguage
	 * @return void
	 * @throws SystemException
	 */
	public function initializeCulture($definedSite = null, $definedLanguage = null): void
	{
		$request = $this->getRequest();

		$language = null;
		$culture = null;
		$site = null;

		if ($definedSite === null && $request->isAdminSection())
		{
			$lang = $request->get('lang');
			if ($lang == '')
			{
				$lang = Config\Option::get('main', 'admin_lid', 'ru');
			}

			if ($lang != '')
			{
				$language = Localization\LanguageTable::getList([
					'filter' => ['=LID' => $lang, '=ACTIVE' => 'Y'],
					'cache' => ['ttl' => static::CACHE_TTL],
				])->fetchObject();
			}

			if (!$language)
			{
				// no language found - get default
				$language = Localization\LanguageTable::getList([
					'filter' => ['=ACTIVE' => 'Y'],
					'order' => ['DEF' => 'DESC'],
					'cache' => ['ttl' => static::CACHE_TTL],
				])->fetchObject();
			}

			if ($language)
			{
				$culture = Localization\CultureTable::getByPrimary($language->getCultureId(), ['cache' => ['ttl' => static::CACHE_TTL]])->fetchObject();
			}
		}
		else
		{
			if ($definedSite !== null)
			{
				$site = SiteTable::getByPrimary($definedSite, ['cache' => ['ttl' => static::CACHE_TTL]])->fetch();
				if (!$site)
				{
					throw new SystemException('Incorrect site: ' . $definedSite . '.');
				}
			}
			else
			{
				// get the site by domain or path
				$site = SiteTable::getByDomain($request->getHttpHost(), $request->getRequestedPageDirectory());
			}

			if ($site)
			{
				$languageId = $definedLanguage ?? $site['LANGUAGE_ID'];
				$language = Localization\LanguageTable::getByPrimary($languageId, ['cache' => ['ttl' => static::CACHE_TTL]])->fetchObject();

				$culture = Localization\CultureTable::getByPrimary($site['CULTURE_ID'], ['cache' => ['ttl' => static::CACHE_TTL]])->fetchObject();
			}
		}

		if ($culture === null || $language === null)
		{
			throw new SystemException("Culture not found, or there are no active sites or languages.");
		}

		$this->setLanguage($language);
		$this->setCulture($culture);

		if ($site)
		{
			$this->setSite(SiteTable::wakeUpObject($site));
		}
	}
}
