<?php
namespace Bitrix\Landing\PublicAction;

use \Bitrix\Main\Localization\Loc;
use \Bitrix\Rest\Marketplace\Client;
use \Bitrix\Landing\Block;
use \Bitrix\Landing\Repo as RepoCore;
use \Bitrix\Landing\PublicActionResult;

Loc::loadMessages(__FILE__);

class Repo
{
	/**
	 * Sanitize bad script.
	 * @param string $str Very bad html with script.
	 * @param array $params Some params.
	 * @return string
	 */
	public static function sanitize($str, $params = array())
	{
		static $sanitizer = null;
		static $internal = true;

		if ($sanitizer === null)
		{
			$sanitizer = new \CBXSanitizer;
			$sanitizer->setLevel($sanitizer::SECURE_LEVEL_LOW);
			$sanitizer->addTags(array(
				'header' => array('class'),
				'footer' => array('class'),
				'menu' => array('class'),
				'main' => array('class'),
				'section' => array('class'),
				'article' => array('class'),
				'summary' => array('class'),
				'cite' => array('class')
			));
		}

		// allow some additional attributes
		if (
			isset($params['allowAttributes']) &&
			is_array($params['allowAttributes']) &&
			method_exists($sanitizer, 'allowAttributes')
		)
		{
			$allowAttributes = array();
			foreach ($params['allowAttributes'] as $attr)
			{
				if (preg_match('/^data\-[a-z0-9]+$/i', $attr))
				{
					$allowAttributes[$attr] = array(
						'tag' => function ($tag)
						{
							return true;
						},
						'content' => function ($value)
						{
							return !preg_match("#[^\\s\\w\\-\\#\\.;]#i" . BX_UTF_PCRE_MODIFIER, $value);
						}
					);
				}
			}
			$sanitizer->allowAttributes(
				$allowAttributes
			);
		}

		return $sanitizer->sanitizeHtml($str);
	}

	/**
	 * Register new block.
	 * @param string $code Unique code of block (for one app context).
	 * @param array $fields Block data.
	 * @param array $manifest Manifest data.
	 * @return \Bitrix\Landing\PublicActionResult
	 */
	public static function register($code, $fields, $manifest = array())
	{
		$result = new PublicActionResult();
		$error = new \Bitrix\Landing\Error;

		// unset not allowed keys
		$notAllowed = array('block', 'callbacks');
		foreach ($notAllowed as $key)
		{
			if (isset($manifest[$key]))
			{
				unset($manifest[$key]);
			}
		}

		if (!is_array($fields))
		{
			$fields = array();
		}

		$check = false;
		$fields['XML_ID'] = trim($code);
		$fields['MANIFEST'] = serialize((array)$manifest);

		if (isset($fields['CONTENT']))
		{
			// fix module security
			$fields['CONTENT'] = str_replace('st yle="', 'style="', $fields['CONTENT']);
			// allow data-attrs (attrs, group attrs)
			$allowAttributes = array();
			if (
				isset($manifest['attrs']) &&
				is_array($manifest['attrs'])
			)
			{
				foreach ($manifest['attrs'] as $attr)
				{
					if (isset($attr['attribute']))
					{
						$allowAttributes[] = $attr['attribute'];
					}
					elseif (is_array($attr))
					{
						foreach ($attr as $attrKey => $attrIn)
						{
							if (
								isset($attrIn['attrs']) &&
								is_array($attrIn['attrs'])
							)
							{
								foreach ($attrIn['attrs'] as $attrIn2)
								{
									$attr[] = $attrIn2;
								}
								unset($attrKey[$attrKey]);
							}
						}
						foreach ($attr as $attrIn)
						{
							if (isset($attrIn['attribute']))
							{
								$allowAttributes[] = $attrIn['attribute'];
							}
						}
					}
				}
			}
			// allow data-attrs (style)
			if (
				isset($manifest['style']) &&
				is_array($manifest['style'])
			)
			{
				foreach ($manifest['style'] as $style)
				{
					if (
						isset($style['additional']) &&
						is_array($style['additional'])
					)
					{
						foreach ($style['additional'] as $styleAdd)
						{
							if (
								isset($styleAdd['attrs']) &&
								is_array($styleAdd['attrs'])
							)
							{
								foreach ($styleAdd['attrs'] as $attrIn)
								{
									if (isset($attrIn['attribute']))
									{
										$allowAttributes[] = $attrIn['attribute'];
									}
								}
							}
						}
					}
				}
			}
			$fields['CONTENT'] = self::sanitize(
				$fields['CONTENT'],
				array(
					'allowAttributes' => $allowAttributes
				)
			);
		}

		// set app code
		if (($app = \Bitrix\Landing\PublicAction::restApplication()))
		{
			$fields['APP_CODE'] = $app['CODE'];
		}

		// check unique
		if ($fields['XML_ID'])
		{
			$check = RepoCore::getList(array(
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
			$res = RepoCore::update($check['ID'], $fields);
		}
		else
		{
			$res = RepoCore::add($fields);
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
		$result = new PublicActionResult();
		$error = new \Bitrix\Landing\Error;

		$result->setResult(false);

		// search and delete
		if ($code)
		{
			// set app code
			$app = \Bitrix\Landing\PublicAction::restApplication();

			$row = RepoCore::getList(array(
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
				// delete all sush blocks from landings
				$codeToDelete = array();
				$res = RepoCore::getList(array(
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
				while ($rowRepo = $res->fetch())
				{
					$codeToDelete[] = 'repo_' . $rowRepo['ID'];
				}
				if (!empty($codeToDelete))
				{
					Block::deleteByCode($codeToDelete);
				}
				// delete block from repo
				$res = RepoCore::delete($row['ID']);
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

	/**
	 * Get info about app from Repo.
	 * @param string $code App code.
	 * @return \Bitrix\Landing\PublicActionResult
	 */
	public static function getAppInfo($code)
	{
		$result = new PublicActionResult();
		$error = new \Bitrix\Landing\Error;
		$app = array();

		if ($appLocal = RepoCore::getAppByCode($code))
		{
			$app = array(
				'CODE' => $appLocal['CODE'],
				'NAME' => $appLocal['APP_NAME'],
				'DATE_FINISH' => (string)$appLocal['DATE_FINISH'],
				'PAYMENT_ALLOW' => $appLocal['PAYMENT_ALLOW'],
				'ICON' => '',
				'PRICE' => array(),
				'UPDATES' => 0
			);
			if (\Bitrix\Main\Loader::includeModule('rest'))
			{
				$appRemote = Client::getApp($code);
				if (isset($appRemote['ITEMS']))
				{
					$data = $appRemote['ITEMS'];
					if (isset($data['ICON']))
					{
						$app['ICON'] = $data['ICON'];
					}
					if (isset($data['PRICE']) && !empty($data['PRICE']))
					{
						$app['PRICE'] = $data['PRICE'];
					}
				}
				$updates = Client::getUpdates(array(
					$code => $appLocal['VERSION']
				));
				if (
					isset($updates['ITEMS'][0]['VERSIONS']) &&
					is_array($updates['ITEMS'][0]['VERSIONS'])
				)
				{
					$app['UPDATES'] = count($updates['ITEMS'][0]['VERSIONS']);
				}
			}
			$result->setResult($app);
		}

		if (empty($app))
		{
			$error->addError(
				'NOT_FOUND',
				Loc::getMessage('LANDING_APP_NOT_FOUND')
			);
		}

		$result->setError($error);

		return $result;
	}
}