<?php
namespace Bitrix\Landing\PublicAction;

use \Bitrix\Landing\Manager;
use \Bitrix\Landing\File;
use \Bitrix\Landing\Site as SiteCore;
use \Bitrix\Landing\PublicActionResult;

class Site
{
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
	public static function getList($params = array())
	{
		$result = new PublicActionResult();

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
	public static function add($fields)
	{
		$result = new PublicActionResult();
		$error = new \Bitrix\Landing\Error;

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
	public static function update($id, $fields)
	{
		$result = new PublicActionResult();
		$error = new \Bitrix\Landing\Error;

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
	 * Upload file by url or from FILE.
	 * @param int $id Site id.
	 * @param string $picture File url / file array.
	 * @param string $ext File extension.
	 * @param array $params Some file params.
	 * @return \Bitrix\Landing\PublicActionResult
	 */
	public static function uploadFile($id, $picture, $ext = false, $params = array())
	{
		static $internal = true;

		$result = new PublicActionResult();
		$result->setResult(false);

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
		}


		return $result;
	}
}