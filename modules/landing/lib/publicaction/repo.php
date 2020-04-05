<?php
namespace Bitrix\Landing\PublicAction;

use Bitrix\Landing\Manager;
use \Bitrix\Main\Localization\Loc;
use \Bitrix\Rest\Marketplace\Client;
use \Bitrix\Rest\PlacementTable;
use \Bitrix\Landing\Placement;
use \Bitrix\Landing\Block as BlockCore;
use \Bitrix\Landing\Repo as RepoCore;
use \Bitrix\Landing\PublicActionResult;

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
			'content' => $content
		));
		return $result;
	}

	/**
	 * Register new block.
	 * @param string $code Unique code of block (for one app context).
	 * @param array $fields Block data.
	 * @param array $manifest Manifest data.
	 * @return \Bitrix\Landing\PublicActionResult
	 */
	public static function register($code, array $fields, array $manifest = array())
	{
		$result = new PublicActionResult();
		$error = new \Bitrix\Landing\Error;

		// unset not allowed keys
		$notAllowed = array('callbacks');
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
													'#card#' => $cardCode
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

		$fields['MANIFEST'] = serialize((array)$manifest);

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
	 * Unregister block.
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
					BlockCore::deleteByCode($codeToDelete);
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

	/**
	 * Bind the placement.
	 * @param array $fields Fields array.
	 * @return \Bitrix\Landing\PublicActionResult
	 */
	public static function bind(array $fields)
	{
		$result = new PublicActionResult();
		$error = new \Bitrix\Landing\Error;

		if (($app = \Bitrix\Landing\PublicAction::restApplication()))
		{
			$fields['APP_ID'] = $app['ID'];
		}

		$res = Placement::getList(array(
			'select' => array(
				'ID'
			),
			'filter' => array(
				'APP_ID' => isset($fields['APP_ID'])
							? $fields['APP_ID']
							: false,
				'PLACEMENT' => isset($fields['PLACEMENT'])
							? $fields['PLACEMENT']
							: false
			)
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
	 * @return \Bitrix\Landing\PublicActionResult
	 */
	public static function unbind($code)
	{
		$result = new PublicActionResult();
		$error = new \Bitrix\Landing\Error;
		$code = trim($code);
		$deleteLocal = false;

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

		// get first local, if exists
		$resLocal = Placement::getList(array(
			'select' => array(
				'ID'
			),
			'filter' => array(
				'APP_ID' => $fields['APP_ID'],
				'=PLACEMENT' => $code
			)
		));
		if ($rowLocal = $resLocal->fetch())
		{
			$deleteLocal = true;
		}

		// try delete from rest placements
		if (\Bitrix\Main\Loader::includeModule('rest'))
		{
			$resRest = PlacementTable::getList(array(
				'select' => array(
					'ID'
				),
				'filter' => array(
					'APP_ID' => $fields['APP_ID'],
					'=PLACEMENT' => $code
				)
			));
			if ($rowRest = $resRest->fetch())
			{
				$result->setResult(true);
				$res = PlacementTable::delete($rowRest['ID']);
				// disable delete local if cant delete rest
				if (!$res->isSuccess())
				{
					$deleteLocal = false;
				}
			}
			else
			{
				$error->addError(
					'PLACEMENT_NO_EXIST',
					Loc::getMessage('LANDING_APP_PLACEMENT_NO_EXIST')
				);
			}
		}

		// finally delete local
		if ($deleteLocal)
		{
			$result->setResult(true);
			Placement::delete($rowLocal['ID']);
		}

		$result->setError($error);

		return $result;
	}

	/**
	 * Get items of current app.
	 * @param array $params Params ORM array.
	 * @return \Bitrix\Landing\PublicActionResult
	 */
	public static function getList(array $params = array())
	{
		$result = new PublicActionResult();

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
			$params['filter']['APP_CODE'] = $app['CODE'];
		}
		else
		{
			$params['filter']['APP_CODE'] = false;
		}

		$data = array();
		$res = RepoCore::getList($params);
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
			$row['MANIFEST'] = unserialize($row['MANIFEST']);
			$data[] = $row;
		}
		$result->setResult($data);

		return $result;
	}
}