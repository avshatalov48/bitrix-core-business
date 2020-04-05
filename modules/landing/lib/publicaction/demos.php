<?php
namespace Bitrix\Landing\PublicAction;

use \Bitrix\Main\Localization\Loc;
use \Bitrix\Landing\Demos as DemoCore;
use \Bitrix\Landing\PublicActionResult;

Loc::loadMessages(__FILE__);

class Demos
{
	/**
	 * Get demo items.
	 * @param string $type Type of demo-template (page, store, etc...).
	 * @param bool $page If true, list of pages, not site.
	 * @return \Bitrix\Landing\PublicActionResult
	 */
	protected static function getList($type, $page = false)
	{
		$result = new PublicActionResult();

		$componentName = 'bitrix:landing.demo';
		$className = \CBitrixComponent::includeComponentClass($componentName);
		$demoCmp = new $className;
		$demoCmp->initComponent($componentName);
		$demoCmp->arParams = array(
			'TYPE' => strtoupper($type)
		);

		if ($page)
		{
			$data = $demoCmp->getDemoPage();
		}
		else
		{
			$data = $demoCmp->getDemoSite();
		}

		if (is_array($data))
		{
			foreach ($data as &$item)
			{
				if (isset($item['DATA']['items']))
				{
					$item['DATA']['encoded'] = true;
					$item['DATA']['items'] = \Bitrix\Main\Text\Encoding::convertEncoding(
						$data['DATA']['items'],
						'cp1251',
						SITE_CHARSET
					);
				}
			}
			unset($item);
		}

		$result->setResult($data);

		return $result;
	}

	/**
	 * Get demo sites.
	 * @param string $type Type of demo-template (page, store, etc...).
	 * @return \Bitrix\Landing\PublicActionResult
	 */
	public static function getSiteList($type)
	{
		return self::getList($type);
	}

	/**
	 * Get demo pages.
	 * @param string $type Type of demo-template (page, store, etc...).
	 * @return \Bitrix\Landing\PublicActionResult
	 */
	public static function getPageList($type)
	{
		return self::getList($type, true);
	}

	/**
	 * Get preview of url by code.
	 * @param string $code Code of page.
	 * @param string $type Code of content.
	 * @return \Bitrix\Landing\PublicActionResult
	 */
	public static function getUrlPreview($code, $type)
	{
		$result = new PublicActionResult();

		$componentName = 'bitrix:landing.demo';
		$className = \CBitrixComponent::includeComponentClass($componentName);
		$demoCmp = new $className;
		$demoCmp->initComponent($componentName);
		$demoCmp->arParams = array(
			'TYPE' => strtoupper($type)
		);

		$result->setResult($demoCmp->getUrlPreview($code));

		return $result;
	}

	/**
	 * Register new item.
	 * @param string $code Unique code of item (for one app context).
	 * @param array $fields Item data.
	 * @param array $manifest Manifest data.
	 * @return \Bitrix\Landing\PublicActionResult
	 */
	public static function register($code, $fields, $manifest = array())
	{
		static $internal = true;

		$result = new PublicActionResult();
		$error = new \Bitrix\Landing\Error;

		$check = false;
		$fields['XML_ID'] = trim($code);
		$fields['MANIFEST'] = serialize((array)$manifest);

		// set app code
		if (($app = \Bitrix\Landing\PublicAction::restApplication()))
		{
			$fields['APP_CODE'] = $app['CODE'];
		}

		// check unique
		if ($fields['XML_ID'])
		{
			$check = DemoCore::getList(array(
				'select' => array(
					'ID'
				),
				'filter' =>
					isset($fields['APP_CODE'])
					? array(
						'=XML_ID' => $fields['XML_ID'],
						'=APP_CODE' => $fields['APP_CODE']
					)
					: array(
						'=XML_ID' => $fields['XML_ID']
					)
			))->fetch();
		}

		// register (add / update)
		if ($check)
		{
			$res = DemoCore::update($check['ID'], $fields);
		}
		else
		{
			$res = DemoCore::add($fields);
		}
		if ($res->isSuccess())
		{
			$result->setResult($res->getId());
		}
		else
		{
			$error->addFromResult($res);
			$result->setError($error);
		}

		return $result;
	}

	/**
	 * Unregister new block.
	 * @param string $code Code of block.
	 * @return \Bitrix\Landing\PublicActionResult
	 */
	public static function unregister($code)
	{
		static $internal = true;

		$result = new PublicActionResult();
		$error = new \Bitrix\Landing\Error;

		$result->setResult(false);

		// search and delete
		if ($code)
		{
			// set app code
			$app = \Bitrix\Landing\PublicAction::restApplication();

			$row = DemoCore::getList(array(
				'select' => array(
					'ID'
				),
				'filter' =>
					isset($app['CODE'])
					? array(
						'=XML_ID' => $code,
						'=APP_CODE' => $app['CODE']
					)
					: array(
						'=XML_ID' => $code
					)
			))->fetch();
			if ($row)
			{
				// delete block from repo
				$res = DemoCore::delete($row['ID']);
				if ($res->isSuccess())
				{
					$result->setResult(true);
				}
				else
				{
					$error->addFromResult($res);
				}
			}
		}

		$result->setError($error);

		return $result;
	}
}