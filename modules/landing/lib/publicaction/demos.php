<?php
namespace Bitrix\Landing\PublicAction;

use \Bitrix\Main\Localization\Loc;
use \Bitrix\Landing\Demos as DemoCore;
use \Bitrix\Landing\PublicActionResult;

Loc::loadMessages(__FILE__);

class Demos
{
	/**
	 * Return true, if item of data is suitable by filter.
	 * @param array $item One data element.
	 * @param array $filter Filter for separate allowed items.
	 * @return bool
	 */
	protected static function isItemSuitable(array $item, array $filter = [])
	{
		if ($filter)
		{
			foreach ($item as $key => $value)
			{
				$key = mb_strtoupper($key);
				if (isset($filter[$key]))
				{
					$value = (array)$value;
					$filter[$key] = (array)$filter[$key];
					if (!array_intersect($value, $filter[$key]))
					{
						return false;
					}
				}
			}
		}

		return true;
	}

	/**
	 * Get demo items from files in component.
	 * @param string $type Type of demo-template (page, store, etc...).
	 * @param bool $page If true, list of pages, not site.
	 * @param array $filter Additional filter.
	 * @return PublicActionResult
	 */
	protected static function getFilesList($type, $page = false, array $filter = [])
	{
		$result = new PublicActionResult();

		if (!is_string($type))
		{
			return $result;
		}

		$componentName = 'bitrix:landing.demo';
		$className = \CBitrixComponent::includeComponentClass($componentName);
		$demoCmp = new $className;
		$demoCmp->initComponent($componentName);
		$demoCmp->arParams = [
			'TYPE' => mb_strtoupper($type),
			'SKIP_REMOTE' => 'Y',
		];

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
			foreach ($data as $key => &$item)
			{
				if (
					!is_array($item) ||
					!self::isItemSuitable($item, $filter)
				)
				{
					unset($data[$key]);
					continue;
				}
				if (isset($item['DATA']['items']))
				{
					// always convert to UTF-8 for REST
					$item['DATA']['encoded'] = true;
					$item['DATA']['charset'] = 'UTF-8';
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
	 * @param array $filter Additional filter.
	 * @return PublicActionResult
	 */
	public static function getSiteList($type, array $filter = [])
	{
		$filter = array_change_key_case($filter, CASE_UPPER);
		return self::getFilesList($type, false, $filter);
	}

	/**
	 * Get demo pages.
	 * @param string $type Type of demo-template (page, store, etc...).
	 * @param array $filter Additional filter.
	 * @return PublicActionResult
	 */
	public static function getPageList($type, array $filter = [])
	{
		$filter = array_change_key_case($filter, CASE_UPPER);
		return self::getFilesList($type, true, $filter);
	}

	/**
	 * Get preview of url by code.
	 * @param string $code Code of page.
	 * @param string $type Code of content.
	 * @return PublicActionResult
	 */
	public static function getUrlPreview($code, $type)
	{
		$result = new PublicActionResult();

		if (!is_string($code) || !is_string($type))
		{
			return $result;
		}

		$componentName = 'bitrix:landing.demo';
		$className = \CBitrixComponent::includeComponentClass($componentName);
		$demoCmp = new $className;
		$demoCmp->initComponent($componentName);
		$demoCmp->arParams = array(
			'TYPE' => mb_strtoupper($type)
		);

		$result->setResult($demoCmp->getUrlPreview($code));

		return $result;
	}

	/**
	 * Register new demo template (site [and pages]).
	 * @param array $data Full data from \Bitrix\Landing\Site::fullExport.
	 * @param array $params Additional params.
	 * @see \Bitrix\Landing\Site::fullExport
	 * @return PublicActionResult
	 */
	public static function register(array $data = array(), array $params = array())
	{
		$result = new PublicActionResult();
		$error = new \Bitrix\Landing\Error;
		$themeCode = null;
		$themeCodeTypo = null;

		// make line array from site and pages
		if (
			isset($data['items'])
		)
		{
			if (is_array($data['items']))
			{
				$dataPages = $data['items'];
			}
			else
			{
				$dataPages = array();
			}
			unset($data['items']);
			// set theme codes from sites to pages
			if (isset($data['fields']['ADDITIONAL_FIELDS']['THEME_CODE']))
			{
				$themeCode = $data['fields']['ADDITIONAL_FIELDS']['THEME_CODE'];
			}
			if (isset($data['fields']['ADDITIONAL_FIELDS']['THEME_CODE_TYPO']))
			{
				$themeCodeTypo = $data['fields']['ADDITIONAL_FIELDS']['THEME_CODE_TYPO'];
			}
			foreach ($dataPages as &$page)
			{
				if (
					!isset($page['fields']) ||
					!is_array($page['fields'])
				)
				{
					$page['fields'] = array();
				}
				if (
					!isset($page['fields']['ADDITIONAL_FIELDS']) ||
					!is_array($page['fields']['ADDITIONAL_FIELDS'])
				)
				{
					$page['fields']['ADDITIONAL_FIELDS'] = array();
				}
				if (!isset($page['fields']['ADDITIONAL_FIELDS']['THEME_CODE']))
				{
					$page['fields']['ADDITIONAL_FIELDS']['THEME_CODE'] = $themeCode;
				}
				if (!isset($page['fields']['ADDITIONAL_FIELDS']['THEME_CODE_TYPO']))
				{
					$page['fields']['ADDITIONAL_FIELDS']['THEME_CODE_TYPO'] = $themeCodeTypo;
				}
			}
			unset($page);

			$data['items'] = array_keys($dataPages);
			$data['tpl_type'] = DemoCore::TPL_TYPE_SITE;
			$data = array_merge([$data], $dataPages);
		}

		if (empty($data) || !is_array($data))
		{
			$error->addError(
				'REGISTER_ERROR_DATA',
				Loc::getMessage('LANDING_DEMO_REGISTER_ERROR_DATA')
			);
			$result->setError($error);
			return $result;
		}

		// set app code
		if (($app = \Bitrix\Landing\PublicAction::restApplication()))
		{
			$appCode = $app['CODE'];
		}
		else
		{
			$appCode = null;
		}

		$deleteAdded = function(array $added)
		{
			foreach ($added as $id)
			{
				DemoCore::delete($id);
			}
		};

		// add item separate
		$success = $return = array();
		$fieldCode = array(
			'TYPE', 'TPL_TYPE', 'SHOW_IN_LIST', 'TITLE', 'DESCRIPTION',
			'PREVIEW_URL', 'PREVIEW', 'PREVIEW2X', 'PREVIEW3X'
		);
		foreach ($data as $item)
		{
			// collect fields
			$fields = array(
				'XML_ID' => null,
				'APP_CODE' => $appCode,
				'TPL_TYPE' => DemoCore::TPL_TYPE_PAGE,
				'LANG' => []
			);
			if (isset($params['site_template_id']))
			{
				$fields['SITE_TEMPLATE_ID'] = $params['site_template_id'];
			}
			else
			{
				$fields['SITE_TEMPLATE_ID'] = '';
			}
			if (isset($item['code']))
			{
				$fields['XML_ID'] = trim($item['code']);
			}
			if (isset($item['name']))
			{
				$fields['TITLE'] = $item['name'];
			}
			if (isset($params['lang']))
			{
				$fields['LANG']['lang'] = $params['lang'];
			}
			if (isset($params['lang_original']))
			{
				$fields['LANG']['lang_original'] = $params['lang_original'];
			}
			if (isset($item['items']) && !is_array($item['items']))
			{
				$item['items'] = [];
			}
			foreach ($fieldCode as $code)
			{
				$codel = mb_strtolower($code);
				if (isset($item[$codel]))
				{
					$fields[$code] = $item[$codel];
				}
			}
			// serialize and check content
			$item = (array) $item;
			$fields['LANG'] = (array) $fields['LANG'];
			$fields['MANIFEST'] = serialize($item);
			if ($fields['LANG'])
			{
				$fields['LANG'] = serialize($fields['LANG']);
			}
			else
			{
				unset($fields['LANG']);
			}
			if (isset($item['fields']['ADDITIONAL_FIELDS']))
			{
				unset($item['fields']['ADDITIONAL_FIELDS']);
			}
			\Bitrix\Landing\Manager::sanitize(
				serialize($item),
				$bad
			);
			if ($bad)
			{
				$error->addError(
					'CONTENT_IS_BAD',
					Loc::getMessage('LANDING_DEMO_CONTENT_IS_BAD') .
					' [code: ' . $fields['XML_ID'] . ']'
				);
				$result->setError($error);
				$deleteAdded($success);
				return $result;
			}
			$check = false;
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
							'=APP_CODE' => $fields['APP_CODE'],
							'=TPL_TYPE' => $fields['TPL_TYPE']
						)
						: array(
							'=XML_ID' => $fields['XML_ID'],
							'=TPL_TYPE' => $fields['TPL_TYPE']
						)
					)
				)->fetch();
			}
			// register (add / update)
			if ($check)
			{
				$res = DemoCore::update($check['ID'], $fields);
			}
			else
			{
				$res = DemoCore::add($fields);
				if ($res->isSuccess())
				{
					$success[] = $res->getId();
				}
			}
			if ($res->isSuccess())
			{
				$return[] = (int)$res->getId();
			}
			else
			{
				$error->addFromResult($res);
				$result->setError($error);
				$deleteAdded($success);
				return $result;
			}
		}

		$result->setResult($return);

		return $result;
	}

	/**
	 * Unregister demo template.
	 * @param string $code Code of item.
	 * @return PublicActionResult
	 */
	public static function unregister($code)
	{
		$result = new PublicActionResult();
		$error = new \Bitrix\Landing\Error;

		$result->setResult(false);

		if (!is_string($code))
		{
			return $result;
		}

		// search and delete
		if ($code)
		{
			// set app code
			$app = \Bitrix\Landing\PublicAction::restApplication();

			$res = DemoCore::getList(array(
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
			));
			while ($row = $res->fetch())
			{
				// delete block from repo
				$resDel = DemoCore::delete($row['ID']);
				if ($resDel->isSuccess())
				{
					$result->setResult(true);
				}
				else
				{
					$error->addFromResult($resDel);
					$result->setError($error);
					return $result;
				}
			}
		}

		return $result;
	}

	/**
	 * Get items of current app.
	 * @param array $params Params ORM array.
	 * @return PublicActionResult
	 */
	public static function getList(array $params = array())
	{
		$result = new PublicActionResult();
		$params = $result->sanitizeKeys($params);

		if (!is_array($params))
		{
			$params = array();
		}
		if (
			!isset($params['filter']) ||
			!is_array($params['filter'])
		)
		{
			$params['filter'] = array();
		}

		// set app code
		if (($app = \Bitrix\Landing\PublicAction::restApplication()))
		{
			$params['filter']['=APP_CODE'] = $app['CODE'];
		}

		$data = array();
		$res = DemoCore::getList($params);
		while ($row = $res->fetch())
		{
			if (isset($row['DATE_CREATE']))
			{
				$row['DATE_CREATE'] = (string) $row['DATE_CREATE'];
			}
			if (isset($row['DATE_MODIFY']))
			{
				$row['DATE_MODIFY'] = (string) $row['DATE_MODIFY'];
			}
			$row['MANIFEST'] = unserialize($row['MANIFEST'], ['allowed_classes' => false]);
			if ($row['LANG'])
			{
				$row['LANG'] = unserialize($row['LANG'], ['allowed_classes' => false]);
			}
			$data[] = $row;
		}
		$result->setResult($data);

		return $result;
	}
}
