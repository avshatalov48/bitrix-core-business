<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage rest
 * @copyright 2001-2016 Bitrix
 */

namespace Bitrix\Rest\Dictionary;

use Bitrix\Main\Application;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Event;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Type\Dictionary;
use Bitrix\Main\Web\HttpClient;
use Bitrix\Main\Web\Json;
use Bitrix\Main\Web\Uri;

class RemoteDictionary extends Dictionary
{
	const ID = 'generic';

	const CACHE_TTL = 86400;
	const CACHE_PREFIX = 'rest_dictionary';

	protected $baseUrl = array(
		'ru' => 'https://www.bitrix24.ru/util/',
		'ua' => 'https://www.bitrix24.ua/util/',
		'by' => 'https://www.bitrix24.by/util/',
		'in' => 'https://www.bitrix24.in/util/',
		'en' => 'https://www.bitrix24.com/util/',
		'de' => 'https://www.bitrix24.de/util/',
		'kz' => 'https://www.bitrix24.kz/util/',
		'br' => 'https://www.bitrix24.com.br/util/',
		'pl' => 'https://www.bitrix24.pl/util/',
		'fr' => 'https://www.bitrix24.fr/util/',
		'la' => 'https://www.bitrix24.es/util/',
		'eu' => 'https://www.bitrix24.eu/util/',
		'cn' => 'https://www.bitrix24.cn/util/',
		'tc' => 'https://www.bitrix24.cn/util/',
		'sc' => 'https://www.bitrix24.cn/util/',
		'tr' => 'https://www.bitrix24.com.tr/util/',
	);
	protected $language = null;

	public function __construct()
	{
		$this->language = LANGUAGE_ID;

		$values = $this->init();

		parent::__construct($values);
	}

	public function setLanguage($language)
	{
		if($language !== $this->language)
		{
			$this->language = $language;
			$this->set($this->init());
		}
	}

	protected function init()
	{
		$managedCache = Application::getInstance()->getManagedCache();
		if($managedCache->read(static::CACHE_TTL, $this->getCacheId()))
		{
			$dictionary = $managedCache->get($this->getCacheId());
		}
		else
		{
			$dictionary = $this->load();

			$managedCache->set($this->getCacheId(), $dictionary);
		}

		$event = new Event('rest', 'onRemoteDictionaryLoad', array(
			'ID' => static::ID,
			'DICTIONARY' => &$dictionary
		));
		$event->send();

		return $dictionary;
	}

	protected function load()
	{
		$httpClient = new HttpClient();

		$uri = $this->getDictionaryUri();

		$httpResult = $httpClient->get($uri->getLocator());

		try
		{
			$result = Json::decode($httpResult);
		}
		catch(ArgumentException $e)
		{
			$result = null;
		}

		return $result;
	}

	protected function getCacheId()
	{
		return static::CACHE_PREFIX.'/'.static::ID.'/'.$this->language;
	}

	/**
	 * @return Uri
	 * @throws \Bitrix\Main\LoaderException
	 */
	protected function getDictionaryUri()
	{
		if(Loader::includeModule('bitrix24'))
		{
			$lang = \CBitrix24::getLicensePrefix();
		}
		else
		{
			$lang = $this->language;
		}

		$baseUrl = array_key_exists($lang, $this->baseUrl)
			? $this->baseUrl[$lang]
			: $this->baseUrl[Loc::getDefaultLang($lang)];

		$uri = new Uri($baseUrl);
		$uri->addParams(array(
			'type' => static::ID,
			'lng' => $this->language,
		));

		return $uri;
	}
}
