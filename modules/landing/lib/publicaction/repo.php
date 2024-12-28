<?php
namespace Bitrix\Landing\PublicAction;

use Bitrix\Landing;
use Bitrix\Landing\Error;
use Bitrix\Landing\Manager;
use Bitrix\Landing\Site;
use Bitrix\Main\Localization\Loc;
use Bitrix\Rest\Marketplace\Client;
use Bitrix\Rest\PlacementTable;
use Bitrix\Landing\Placement;
use Bitrix\Landing\PublicActionResult;
use Bitrix\Landing\Node\StyleImg;

Loc::loadMessages(__FILE__);

class Repo
{
	/**
	 * Check content for bad substring.
	 * @param string $content
	 * @param string $splitter
	 * @return PublicActionResult
	 */
	public static function checkContent($content, $splitter = '#SANITIZE#')
	{
		$result = new PublicActionResult();
		$content = Manager::sanitize(
			$content,
			$bad,
			$splitter
		);
		$result->setResult(array(
			'is_bad' => $bad,
			'content' => $content,
		));

		return $result;
	}

	/**
	 * Registers new block.
	 * @param string $code Unique code of block (unique within app).
	 * @param array $fields Block data.
	 * @param array $manifest Manifest data.
	 * @return PublicActionResult
	 */
	public static function register(string $code, array $fields, array $manifest = []): PublicActionResult
	{
		$result = new PublicActionResult();
		$error = new \Bitrix\Landing\Error;

		static::onRegisterCheckFields($fields, $error);
		static::onRegisterBefore($fields, $manifest, $error);
		if (!empty($error->getErrors()))
		{
			$result->setError($error);

			return $result;
		}

		$fields['XML_ID'] = trim($code);

		// check intersect item of nodes and styles for background type
		if (is_array($manifest['nodes'] ?? null))
		{
			foreach ($manifest['nodes'] as $selector => $manifestItem)
			{
				$styleItem = null;

				if (isset($manifest['style'][$selector]))
				{
					$styleItem = $manifest['style'][$selector];
				}
				if (isset($manifest['style']['nodes'][$selector]))
				{
					$styleItem = $manifest['style']['nodes'][$selector];
				}

				if ($styleItem['type'] ?? null)
				{
					if (!empty(array_intersect((array)$styleItem['type'], StyleImg::STYLES_WITH_IMAGE)))
					{
						$error->addError(
							'MANIFEST_INTERSECT_IMG',
							Loc::getMessage('LANDING_APP_MANIFEST_INTERSECT_IMG', ['#selector#' => $selector])
						);
						$result->setError($error);

						return $result;
					}
				}
			}
		}

		if (isset($fields['CONTENT']))
		{
			// sanitize content
			$fields['CONTENT'] = Manager::sanitize(
				$fields['CONTENT'],
				$bad
			);
			if ($bad)
			{
				$error->addError(
					'CONTENT_IS_BAD',
					Loc::getMessage('LANDING_APP_CONTENT_IS_BAD')
				);
				$result->setError($error);

				return $result;
			}
			// sanitize card's content
			if (
				isset($manifest['cards']) &&
				is_array($manifest['cards'])
			)
			{
				foreach ($manifest['cards'] as $cardCode => &$card)
				{
					if (
						isset($card['presets']) &&
						is_array($card['presets'])
					)
					{
						foreach ($card['presets'] as $presetCode => &$preset)
						{
							foreach (['html', 'name', 'values'] as $code)
							{
								if (isset($preset[$code]))
								{
									$preset[$code] = Manager::sanitize(
										$preset[$code],
										$bad
									);
									if ($bad)
									{
										$error->addError(
											'PRESET_CONTENT_IS_BAD',
											Loc::getMessage(
												'LANDING_APP_PRESET_CONTENT_IS_BAD',
												array(
													'#preset#' => $presetCode,
													'#card#' => $cardCode,
												))
										);
										$result->setError($error);

										return $result;
									}
								}
							}
						}
						unset($preset);
					}
				}
				unset($card);
			}
		}

		$fields['MANIFEST'] = serialize($manifest);

		// set app code
		if (($app = \Bitrix\Landing\PublicAction::restApplication()))
		{
			$fields['APP_CODE'] = $app['CODE'];
		}

		// check unique
		$exists = false;
		if ($fields['XML_ID'])
		{
			$exists = Landing\Repo::getList([
				'select' => ['ID'],
				'filter' =>
					isset($fields['APP_CODE'])
					? [
						'=XML_ID' => $fields['XML_ID'],
						'=APP_CODE' => $fields['APP_CODE'],
					]
					: [
						'=XML_ID' => $fields['XML_ID'],
					],
			])->fetch();
		}

		// register (add / update)
		if ($exists)
		{
			$res = Landing\Repo::update($exists['ID'], $fields);
		}
		else
		{
			$res = Landing\Repo::add($fields);
		}
		if ($res->isSuccess())
		{
			if (
				isset($fields['RESET']) &&
				$fields['RESET'] == 'Y'
			)
			{
				\Bitrix\Landing\Update\Block::register(
					'repo_' . $res->getId()
				);
			}
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
	 * Check required fields
	 * @param $fields
	 * @param Error $error - object for set errors
	 * @return void
	 */
	protected static function onRegisterCheckFields($fields, Error $error): void
	{
		$requiredFields = [
			'NAME',
			'CONTENT',
			'SECTIONS',
			'PREVIEW',
		];

		foreach ($requiredFields as $field)
		{
			if (!isset($fields[$field]))
			{
				$error->addError(
					'REQUIRED_FIELD_NO_EXISTS',
					Loc::getMessage('LANDING_FIELD_NO_EXISTS', ['#field#' => $field])
				);
			}
		}
	}

	/**
	 * Some fixes in fields and manifest, specific by scope (mainpage widget or any)
	 * @param array $fields
	 * @param array $manifest
	 * @param Error $error - object for set errors
	 * @return array
	 */
	protected static function onRegisterBefore(array &$fields, array &$manifest, Error $error): void
	{
		// todo: test err

		// unset not allowed keys
		$notAllowedManifestKey = ['callbacks'];
		foreach ($notAllowedManifestKey as $key)
		{
			if (isset($manifest[$key]))
			{
				unset($manifest[$key]);
			}
		}

		// unset not allowed site types
		if (isset($manifest['block']['type']))
		{
			$manifest['block']['type'] = array_filter(
				(array)$manifest['block']['type'],
				function ($type) use ($error) {
					$notAllowedBlockTypes = [
						Site\Type::SCOPE_CODE_MAINPAGE,
					];
					if (in_array(mb_strtolower($type), $notAllowedBlockTypes))
					{
						$error->addError(
							'UNSUPPORTED_BLOCK_TYPE',
							Loc::getMessage('LANDING_UNSUPPORTED_BLOCK_TYPE', ['#type#' => $type])
						);

						return false;
					}
					return true;
				}
			);
		}

		// unset not allowed subtypes
		if (isset($manifest['block']['subtype']))
		{
			$manifest['block']['subtype'] = array_filter(
				(array)$manifest['block']['subtype'],
				function ($type) use ($error) {
					$notAllowedSubtypes = [
						'widget',
					];
					if (in_array(mb_strtolower($type), $notAllowedSubtypes))
					{
						$error->addError(
							'UNSUPPORTED_BLOCK_SUBTYPE',
							Loc::getMessage('LANDING_UNSUPPORTED_BLOCK_SUBTYPE', ['#type#' => $type])
						);

						return false;
					}
					return true;
				}
			);
		}
	}

	/**
	 * Unregister block.
	 * @param string $code Code of block.
	 * @return \Bitrix\Landing\PublicActionResult
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

			$row = Landing\Repo::getList(array(
				'select' => array(
					'ID',
				),
				'filter' =>
					isset($app['CODE'])
					? array(
						'=XML_ID' => $code,
						'=APP_CODE' => $app['CODE'],
					)
					: array(
						'=XML_ID' => $code,
					),
			))->fetch();
			if ($row)
			{
				// delete all sush blocks from landings
				$codeToDelete = array();
				$res = Landing\Repo::getList(array(
					'select' => array(
						'ID',
					),
					'filter' =>
						isset($app['CODE'])
						? array(
							'=XML_ID' => $code,
							'=APP_CODE' => $app['CODE'],
						)
						: array(
							'=XML_ID' => $code,
						),
				));
				while ($rowRepo = $res->fetch())
				{
					$codeToDelete[] = 'repo_' . $rowRepo['ID'];
				}
				if (!empty($codeToDelete))
				{
					Landing\Block::deleteByCode($codeToDelete);
				}
				// delete block from repo
				$res = Landing\Repo::delete($row['ID']);
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

		if (!is_string($code))
		{
			return $result;
		}

		if ($appLocal = Landing\Repo::getAppByCode($code))
		{
			$app = array(
				'CODE' => $appLocal['CODE'],
				'NAME' => $appLocal['APP_NAME'],
				'DATE_FINISH' => (string)$appLocal['DATE_FINISH'],
				'PAYMENT_ALLOW' => $appLocal['PAYMENT_ALLOW'],
				'ICON' => '',
				'PRICE' => array(),
				'UPDATES' => 0,
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
					$code => $appLocal['VERSION'],
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

	/**
	 * Bind the placement.
	 * @param array $fields Fields array.
	 * @return \Bitrix\Landing\PublicActionResult
	 */
	public static function bind(array $fields)
	{
		$result = new PublicActionResult();
		$error = new \Bitrix\Landing\Error;
		\trimArr($fields);

		if (($app = \Bitrix\Landing\PublicAction::restApplication()))
		{
			$fields['APP_ID'] = $app['ID'];
		}

		$res = Placement::getList(array(
			'select' => array(
				'ID',
			),
			'filter' => array(
				'APP_ID' => isset($fields['APP_ID'])
							? $fields['APP_ID']
							: false,
				'PLACEMENT' => isset($fields['PLACEMENT'])
							? $fields['PLACEMENT']
							: false,
				'PLACEMENT_HANDLER' => isset($fields['PLACEMENT_HANDLER'])
							? $fields['PLACEMENT_HANDLER']
							: false,
			),
		));
		// add, if not exist
		if (!$res->fetch())
		{
			if (\Bitrix\Main\Loader::includeModule('rest'))
			{
				// first try add in the local table
				$resLocal = Placement::add($fields);
				if ($resLocal->isSuccess())
				{
					// then add in the rest table
					$resRest = PlacementTable::add(
						$fields
					);
					if ($resRest->isSuccess())
					{
						$result->setResult(true);
					}
					else
					{
						$error->addFromResult($resRest);
						Placement::delete($resLocal->getId());
					}
				}
				else
				{
					$error->addFromResult($resLocal);
				}
			}
		}
		else
		{
			$error->addError(
				'PLACEMENT_EXIST',
				Loc::getMessage('LANDING_APP_PLACEMENT_EXIST')
			);
		}

		$result->setError($error);

		return $result;
	}

	/**
	 * Unbind the placement.
	 * @param string $code Placement code.
	 * @param string $handler Handler path (if you want delete specific).
	 * @return \Bitrix\Landing\PublicActionResult
	 */
	public static function unbind($code, $handler = null)
	{
		$result = new PublicActionResult();
		$error = new \Bitrix\Landing\Error;

		if (!is_string($code))
		{
			return $result;
		}

		$code = trim($code);
		$wasDeleted = false;

		if (($app = \Bitrix\Landing\PublicAction::restApplication()))
		{
			$fields['APP_ID'] = $app['ID'];
		}
		if (
			!isset($fields['APP_ID']) ||
			!$fields['APP_ID']
		)
		{
			return $result;
		}

		// common ORM params
		$params = [
			'select' => [
				'ID',
			],
			'filter' => [
				'APP_ID' => $fields['APP_ID'],
				'=PLACEMENT' => $code,
			],
		];
		if ($handler)
		{
			$params['filter']['=PLACEMENT_HANDLER'] = trim($handler);
		}

		// at first, delete local binds
		$res = Placement::getList($params);
		while ($row = $res->fetch())
		{
			$wasDeleted = true;
			Placement::delete($row['ID']);
		}
		unset($res, $row);

		// then delete from rest placements
		if (\Bitrix\Main\Loader::includeModule('rest'))
		{
			$res = PlacementTable::getList($params);
			while ($row = $res->fetch())
			{
				PlacementTable::delete($row['ID']);
			}
			unset($res, $row);
		}

		// make answer
		if ($wasDeleted)
		{
			$result->setResult(true);
		}
		else
		{
			$error->addError(
				'PLACEMENT_NO_EXIST',
				Loc::getMessage('LANDING_APP_PLACEMENT_NO_EXIST')
			);
			$result->setError($error);
		}

		return $result;
	}

	/**
	 * Get items of current app.
	 * @param array $params Params ORM array.
	 * @return \Bitrix\Landing\PublicActionResult
	 */
	public static function getList(array $params = array()): PublicActionResult
	{
		$result = new PublicActionResult();
		$params = $result->sanitizeKeys($params);

		if (!is_array($params))
		{
			$params = [];
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
			$params['filter']['APP_CODE'] = $app['CODE'];
		}
		else
		{
			$params['filter']['APP_CODE'] = false;
		}

		// manifest always needed
		if (isset($params['select']))
		{
			$params['select'][] = 'MANIFEST';
		}

		$data = [];
		$res = Landing\Repo::getList($params);

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
			$data[] = $row;
		}
		$result->setResult($data);

		return $result;
	}
}
