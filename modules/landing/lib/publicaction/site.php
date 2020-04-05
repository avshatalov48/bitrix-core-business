<?php
namespace Bitrix\Landing\PublicAction;

use \Bitrix\Landing\Manager;
use \Bitrix\Landing\File;
use \Bitrix\Landing\Landing;
use \Bitrix\Landing\Site as SiteCore;
use \Bitrix\Landing\PublicActionResult;
use \Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class Site
{
	/**
	 * Clear disallow keys from add/update fields.
	 * @param array $fields Array fields.
	 * @return array
	 */
	protected static function clearDisallowFields(array $fields)
	{
		$disallow = array('ACTIVE');

		if (is_array($fields))
		{
			foreach ($fields as $k => $v)
			{
				if (in_array($k, $disallow))
				{
					unset($fields[$k]);
				}
			}
		}

		return $fields;
	}

	/**
	 * Get additional fields of site.
	 * @param int $id Id of site.
	 * @return \Bitrix\Landing\PublicActionResult
	 */
	public static function getAdditionalFields($id)
	{
		$result = new PublicActionResult();

		if (($fields = SiteCore::getAdditionalFields($id)))
		{
			foreach ($fields as $key => $field)
			{
				$fields[$key] = $field->getValue();
				if (!$fields[$key])
				{
					unset($fields[$key]);
				}
			}
			$result->setResult(
				$fields
			);
		}

		return $result;
	}

	/**
	 * Get available sites.
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

		// more usable for domain mame
		if (
			isset($params['select']) &&
			is_array($params['select']) &&
			in_array('DOMAIN_NAME', $params['select'])
		)
		{
			foreach ($params['select'] as $k => $code)
			{
				if ($code == 'DOMAIN_NAME')
				{
					unset($params['select'][$k]);
					break;
				}
			}
			$params['select']['DOMAIN_NAME'] = 'DOMAIN.DOMAIN';
		}

		$data = array();
		$res = SiteCore::getList($params);
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
			$data[] = $row;
		}
		$result->setResult($data);

		return $result;
	}

	/**
	 * Create new site.
	 * @param array $fields Site data.
	 * @return \Bitrix\Landing\PublicActionResult
	 */
	public static function add(array $fields)
	{
		$result = new PublicActionResult();
		$error = new \Bitrix\Landing\Error;

		$fields = self::clearDisallowFields($fields);
		$fields['ACTIVE'] = 'N';

		$res = SiteCore::add($fields);

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
	 * Update site.
	 * @param int $id Site id.
	 * @param array $fields Site new data.
	 * @return \Bitrix\Landing\PublicActionResult
	 */
	public static function update($id, array $fields)
	{
		$result = new PublicActionResult();
		$error = new \Bitrix\Landing\Error;

		$fields = self::clearDisallowFields($fields);

		$res = SiteCore::update($id, $fields);

		if ($res->isSuccess())
		{
			$result->setResult(true);
		}
		else
		{
			$error->addFromResult($res);
			$result->setError($error);
		}

		return $result;
	}

	/**
	 * Delete site.
	 * @param int $id Site id.
	 * @return \Bitrix\Landing\PublicActionResult
	 */
	public static function delete($id)
	{
		$result = new PublicActionResult();
		$error = new \Bitrix\Landing\Error;

		$res = SiteCore::delete($id);

		if ($res->isSuccess())
		{
			$result->setResult(true);
		}
		else
		{
			$error->addFromResult($res);
			$result->setError($error);
		}

		return $result;
	}

	/**
	 * Mark entity as deleted.
	 * @param int $id Entity id.
	 * @param boolean $mark Mark.
	 * @return \Bitrix\Landing\PublicActionResult
	 */
	public static function markDelete($id, $mark = true)
	{
		$result = new PublicActionResult();
		$error = new \Bitrix\Landing\Error;

		if ($mark)
		{
			$res = SiteCore::markDelete($id);
		}
		else
		{
			$res = SiteCore::markUnDelete($id);
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
	 * Mark entity as undeleted.
	 * @param int $id Entity id.
	 * @return \Bitrix\Landing\PublicActionResult
	 */
	public static function markUnDelete($id)
	{
		return self::markDelete($id, false);
	}

	/**
	 * Make site public.
	 * @param int $id Entity id.
	 * @param boolean $mark Mark.
	 * @return \Bitrix\Landing\PublicActionResult
	 */
	public static function publication($id, $mark = true)
	{
		$result = new PublicActionResult();
		$error = new \Bitrix\Landing\Error;

		$res = SiteCore::update($id, array(
			'ACTIVE' => $mark ? 'Y' : 'N'
		));
		if ($res->isSuccess())
		{
			$result->setResult($res->getId());
			// work with pages
			$res = Landing::getList(array(
				'select' => array(
					'ID'
				),
				'filter' => array(
					'SITE_ID' => $id
				)
			));
			while ($row = $res->fetch())
			{
				$landing = Landing::createInstance($row['ID']);
				if ($mark)
				{
					$landing->publication();
				}
				else
				{
					$landing->unpublic();
				}
			}
		}
		else
		{
			$error->addFromResult($res);
			$result->setError($error);
		}

		return $result;
	}

	/**
	 * Mark site unpublic.
	 * @param int $id Entity id.
	 * @return \Bitrix\Landing\PublicActionResult
	 */
	public static function unpublic($id)
	{
		return self::publication($id, false);
	}

	/**
	 * Full export of the site.
	 * @param int $id Site id.
	 * @param array $params Params array.
	 * @return \Bitrix\Landing\PublicActionResult
	 */
	public static function fullExport($id, array $params = array())
	{
		$result = new PublicActionResult();

		$result->setResult(
			SiteCore::fullExport($id, $params)
		);

		return $result;
	}

	/**
	 * Upload file by url or from FILE.
	 * @param int $id Site id.
	 * @param string $picture File url / file array.
	 * @param string $ext File extension.
	 * @param array $params Some file params.
	 * @return \Bitrix\Landing\PublicActionResult
	 */
	public static function uploadFile($id, $picture, $ext = false, array $params = array())
	{
		static $internal = true;
		static $mixedParams = ['picture'];

		$result = new PublicActionResult();
		$result->setResult(false);
		$error = new \Bitrix\Landing\Error;

		$res = SiteCore::getList(array(
			'filter' => array(
				'ID' => $id
			)
		));

		if ($res->fetch())
		{
			$file = Manager::savePicture($picture, $ext, $params);
			if ($file)
			{
				File::addToSite($id, $file['ID']);
				$result->setResult(array(
					'id' => $file['ID'],
					'src' => $file['SRC']
				));
			}
			else
			{
				$error->addError(
					'FILE_ERROR',
					Loc::getMessage('LANDING_FILE_ERROR')
				);
				$result->setError($error);
			}
		}


		return $result;
	}
}