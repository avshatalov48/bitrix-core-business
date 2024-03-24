<?php
namespace Bitrix\Landing\PublicAction;

use \Bitrix\Landing\Manager;
use \Bitrix\Landing\File;
use \Bitrix\Landing\Rights;
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
		$disallow = ['ACTIVE', 'SPECIAL', 'TPL_CODE'];

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
		$id = (int)$id;

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
	 * Gets public url of site (or sites).
	 * @param int[] $id Site id or array of ids.
	 * @return \Bitrix\Landing\PublicActionResult
	 */
	public static function getPublicUrl($id)
	{
		static $mixedParams = ['id'];

		$result = new PublicActionResult();
		$result->setResult(SiteCore::getPublicUrl($id));
		return $result;
	}

	/**
	 * Returns site's preview (index page's preview).
	 * @param int $id Site id.
	 * @return PublicActionResult
	 */
	public static function getPreview(int $id): PublicActionResult
	{
		$result = new PublicActionResult();
		$result->setResult(SiteCore::getPreview($id));
		return $result;
	}

	/**
	 * Get available sites.
	 * @param array $params Params ORM array.
	 * @param string $initiator Initiator code.
	 * @return \Bitrix\Landing\PublicActionResult
	 */
	public static function getList(array $params = [], $initiator = null)
	{
		$result = new PublicActionResult();
		$params = $result->sanitizeKeys($params);
		$getPublicUrl = false;
		$getPreviewPicture = false;
		$getPhone = false;
		$mobileHit = $initiator === 'mobile';

		if ($mobileHit)
		{
			\Bitrix\Landing\Connector\Mobile::forceMobile();
		}

		// necessary params for us
		if (
			!isset($params['select']) ||
			!is_array($params['select'])
		)
		{
			$params['select'] = ['*'];
		}
		if (
			!isset($params['filter']) ||
			!is_array($params['filter'])
		)
		{
			$params['filter'] = [];
		}

		// fix for smn sites
		if (
			isset($params['filter']['=TYPE']) &&
			$params['filter']['=TYPE'] == 'STORE'
		)
		{
			$params['filter']['=TYPE'] = [
				$params['filter']['=TYPE'],
				'SMN'
			];
		}
		if (
			isset($params['filter']['TYPE']) &&
			$params['filter']['TYPE'] == 'STORE'
		)
		{
			$params['filter']['TYPE'] = [
				$params['filter']['TYPE'],
				'SMN'
			];
		}

		if (isset($params['filter']['CHECK_PERMISSIONS']))
		{
			unset($params['filter']['CHECK_PERMISSIONS']);
		}

		// extend select's param
		if (is_array($params['select']))
		{
			if (in_array('DOMAIN_NAME', $params['select']))
			{
				$params['select']['DOMAIN_NAME'] = 'DOMAIN.DOMAIN';
			}
			if (in_array('PUBLIC_URL', $params['select']))
			{
				$getPublicUrl = true;
			}
			if (in_array('PREVIEW_PICTURE', $params['select']))
			{
				$getPreviewPicture = true;
			}
			if (in_array('PHONE', $params['select']))
			{
				$getPhone = true;
				$params['select'][] = 'ID';
			}
			// delete this keys for ORM
			$deleted = ['DOMAIN_NAME', 'PUBLIC_URL', 'PREVIEW_PICTURE', 'PHONE'];
			foreach ($params['select'] as $k => $code)
			{
				if (in_array($code, $deleted))
				{
					unset($params['select'][$k]);
				}
			}
		}

		// set additional select fields
		if (
			$getPreviewPicture &&
			!in_array('LANDING_ID_INDEX', $params['select'])
		)
		{
			$params['select'][] = 'LANDING_ID_INDEX';
		}
		if (!in_array('ID', $params['select']))
		{
			$params['select'][] = 'ID';
		}
		if (!in_array('TYPE', $params['select']))
		{
			$params['select'][] = 'TYPE';
		}

		// get ORM data
		$data = [];
		$landingIndexes = [];
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
			if ($row['LANDING_ID_INDEX'] && $getPreviewPicture)
			{
				$landingIndexes[$row['ID']] = $row['LANDING_ID_INDEX'];
			}
			if ($getPublicUrl)
			{
				$row['PUBLIC_URL'] = '';
			}
			if ($getPreviewPicture)
			{
				$row['PREVIEW_PICTURE'] = '';
			}
			if ($getPhone)
			{
				$row['PHONE'] = \Bitrix\Landing\Connector\Crm::getContacts(
					$row['ID']
				)['PHONE'] ?? null;
			}
			$data[$row['ID']] = $row;
		}

		// gets public url for sites
		if ($getPublicUrl || $getPreviewPicture)
		{
			$urls = SiteCore::getPublicUrl(array_keys($data), true, !$mobileHit);
			foreach ($urls as $siteId => $url)
			{
				$data[$siteId]['PUBLIC_URL'] = $url;
			}
		}

		// get preview pictures
		if ($landingIndexes)
		{
			$landing = Landing::createInstance(0);
			foreach ($landingIndexes as $siteId => $landingId)
			{
				$data[$siteId]['PREVIEW_PICTURE'] = $landing->getPreview(
					$landingId,
					false,
					$data[$siteId]['PUBLIC_URL']
				);
			}
		}

		// set and return result
		$result->setResult(
			array_values($data)
		);

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
	 * Mark site as deleted.
	 * @param int $id Site id.
	 * @param boolean $mark Mark.
	 * @return \Bitrix\Landing\PublicActionResult
	 */
	public static function markDelete($id, $mark = true)
	{
		$result = new PublicActionResult();
		$error = new \Bitrix\Landing\Error;
		$id = (int)$id;

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
	 * Mark site as undeleted.
	 * @param int $id Site id.
	 * @return \Bitrix\Landing\PublicActionResult
	 */
	public static function markUnDelete($id)
	{
		return self::markDelete($id, false);
	}

	/**
	 * Creates folder into the site.
	 * @param int $siteId Site id.
	 * @param array $fields Folder's fields.
	 * @return \Bitrix\Landing\PublicActionResult
	 */
	public static function addFolder(int $siteId, array $fields): PublicActionResult
	{
		$result = new PublicActionResult();
		$error = new \Bitrix\Landing\Error;
		if (!($fields['PARENT_ID'] ?? null))
		{
			$fields['PARENT_ID'] = null;
		}
		$addResult = SiteCore::addFolder($siteId, $fields);

		if ($addResult->isSuccess())
		{
			$result->setResult($addResult->getId());
		}
		else
		{
			$error->addFromResult($addResult);
			$result->setError($error);
		}

		return $result;
	}

	/**
	 * Updates folder into the site.
	 * @param int $siteId Site id.
	 * @param int $folderId Folder id.
	 * @param array $fields Folder's fields.
	 * @return PublicActionResult
	 */
	public static function updateFolder(int $siteId, int $folderId, array $fields): PublicActionResult
	{
		$result = new PublicActionResult();
		$error = new \Bitrix\Landing\Error;

		if (!($fields['PARENT_ID'] ?? null))
		{
			$fields['PARENT_ID'] = null;
		}
		$addResult = SiteCore::updateFolder($siteId, $folderId, $fields);

		if ($addResult->isSuccess())
		{
			$result->setResult(true);
		}
		else
		{
			$error->addFromResult($addResult);
			$result->setError($error);
		}

		return $result;
	}

	/**
	 * Move folder.
	 * @param int $folderId Current folder id.
	 * @param int|null $toFolderId Destination folder id (or null for root folder of current folder's site).
	 * @param int|null $toSiteId Destination site id (if different from current).
	 * @return PublicActionResult
	 */
	public static function moveFolder(int $folderId, ?int $toFolderId, ?int $toSiteId = null): PublicActionResult
	{
		$result = new PublicActionResult();
		$error = new \Bitrix\Landing\Error;
		$moveResult = SiteCore::moveFolder($folderId, $toFolderId ?: null, $toSiteId ?: null);

		if ($moveResult->isSuccess())
		{
			$result->setResult($moveResult->getId());
		}
		else
		{
			$error->addFromResult($moveResult);
			$result->setError($error);
		}

		return $result;
	}

	/**
	 * Public all folder's breadcrumb.
	 * @param int $folderId Folder id.
	 * @param bool $mark Publication / depublication.
	 * @return PublicActionResult
	 */
	public static function publicationFolder(int $folderId, bool $mark = true): PublicActionResult
	{
		$result = new PublicActionResult();
		$error = new \Bitrix\Landing\Error;

		$publicationResult = SiteCore::publicationFolder($folderId, $mark);

		if ($publicationResult->isSuccess())
		{
			$result->setResult($publicationResult->isSuccess());
		}
		else
		{
			$error->addFromResult($publicationResult);
			$result->setError($error);
		}

		return $result;
	}

	/**
	 * Unpublic all folder's breadcrumb.
	 * @param int $folderId Folder id.
	 * @return PublicActionResult
	 */
	public static function unPublicFolder(int $folderId): PublicActionResult
	{
		return self::publicationFolder($folderId, false);
	}

	/**
	 * Returns folder's list of site.
	 * @param int $siteId Site id.
	 * @param array $filter Folder's filter.
	 * @return PublicActionResult
	 */
	public static function getFolders(int $siteId, array $filter = []): PublicActionResult
	{
		$result = new PublicActionResult();
		if (array_key_exists('PARENT_ID', $filter) && !($filter['PARENT_ID'] ?? null))
		{
			$filter['PARENT_ID'] = null;
		}

		$rows = array_values(SiteCore::getFolders($siteId, $filter));
		foreach ($rows as &$row)
		{
			if (isset($row['DATE_CREATE']))
			{
				$row['DATE_CREATE'] = (string) $row['DATE_CREATE'];
			}
			if (isset($row['DATE_MODIFY']))
			{
				$row['DATE_MODIFY'] = (string) $row['DATE_MODIFY'];
			}
		}
		unset($row);

		$result->setResult($rows);

		return $result;
	}

	/**
	 * Mark folder as deleted.
	 * @param int $id Folder id.
	 * @param boolean $mark Mark.
	 * @return \Bitrix\Landing\PublicActionResult
	 */
	public static function markFolderDelete(int $id, bool $mark = true): PublicActionResult
	{
		$result = new PublicActionResult();
		$error = new \Bitrix\Landing\Error;

		if ($mark)
		{
			$res = SiteCore::markFolderDelete($id);
		}
		else
		{
			$res = SiteCore::markFolderUnDelete($id);
		}
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
	 * Mark folder as undeleted.
	 * @param int $id Folder id.
	 * @return \Bitrix\Landing\PublicActionResult
	 */
	public static function markFolderUnDelete(int $id): PublicActionResult
	{
		return self::markFolderDelete($id, false);
	}

	/**
	 * Makes site public.
	 * @param int $id Site id.
	 * @param bool $mark Mark.
	 * @return PublicActionResult
	 */
	public static function publication(int $id, bool $mark = true): PublicActionResult
	{
		$result = new PublicActionResult();
		$error = new \Bitrix\Landing\Error;

		if ($mark)
		{
			$res = SiteCore::publication($id);
		}
		else
		{
			$res = SiteCore::unpublic($id);
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
	 * Marks site unpublic.
	 * @param int $id Site id.
	 * @return PublicActionResult
	 */
	public static function unpublic(int $id): PublicActionResult
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
	 * Set rights for site.
	 * @param int $id Site id.
	 * @param array $rights Array of rights for site.
	 * @return \Bitrix\Landing\PublicActionResult
	 */
	public static function setRights($id, $rights = [])
	{
		static $mixedParams = ['rights'];

		$result = new PublicActionResult();
		$error = new \Bitrix\Landing\Error;
		$result->setResult(false);
		$id = (int)$id;

		if (!is_array($rights))
		{
			$rights = [];
		}

		// check access for set rights
		if (!Rights::isAdmin())
		{
			$error->addError(
				'IS_NOT_ADMIN',
				Loc::getMessage('LANDING_IS_NOT_ADMIN_ERROR')
			);
			$result->setError($error);
		}
		else if (!Manager::checkFeature(Manager::FEATURE_PERMISSIONS_AVAILABLE))
		{
			$error->addError(
				'FEATURE_NOT_AVAIL',
				\Bitrix\Landing\Restriction\Manager::getSystemErrorMessage(
					'limit_sites_access_permissions'
				)
			);
			$result->setError($error);
		}
		// set rights
		else
		{
			$result->setResult(
				Rights::setOperationsForSite(
					$id,
					$rights
				)
			);
		}

		return $result;
	}

	/**
	 * Get rights about site.
	 * @param int $id Site id.
	 * @return \Bitrix\Landing\PublicActionResult
	 */
	public static function getRights($id)
	{
		$result = new PublicActionResult();
		$error = new \Bitrix\Landing\Error;
		$result->setResult([]);
		$id = (int)$id;

		// check access for get rights
		if (!Manager::checkFeature(Manager::FEATURE_PERMISSIONS_AVAILABLE))
		{
			$error->addError(
				'FEATURE_NOT_AVAIL',
				\Bitrix\Landing\Restriction\Manager::getSystemErrorMessage(
					'limit_sites_access_permissions'
				)
			);
			$result->setError($error);
		}
		// get rights
		else
		{
			$result->setResult(
				Rights::getOperationsForSite(
					$id
				)
			);
		}

		return $result;
	}

	/**
	 * Upload file by url or from FILE.
	 * @param int $id Site id.
	 * @param string $picture File url / file array.
	 * @param string $ext File extension.
	 * @param array $params Some file params.
	 * @param bool $temp This is temporary file.
	 * @return PublicActionResult
	 */
	public static function uploadFile($id, $picture, $ext = false, array $params = [], $temp = false): PublicActionResult
	{
		static $internal = true;
		static $mixedParams = ['picture'];

		$result = new PublicActionResult();
		$result->setResult(false);
		$error = new \Bitrix\Landing\Error;
		$id = (int)$id;

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
				File::addToSite($id, $file['ID'], Utils::isTrue($temp));
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

	/**
	 * Sets scope for work with module.
	 * @param string $type Scope code.
	 * @return \Bitrix\Landing\PublicActionResult
	 */
	public static function setScope($type)
	{
		\Bitrix\Landing\Site\Type::setScope($type);

		return new PublicActionResult();
	}

	/**
	 * Binds or unbinds site with specific menu or Group.
	 * @param int $id Site id.
	 * @param \Bitrix\Landing\Binding\Entity $binding Binding instance.
	 * @param bool $bind Bind or unbind to menu (true or false).
	 * @return PublicActionResult
	 */
	protected static function binding(int $id, \Bitrix\Landing\Binding\Entity $binding, bool $bind): PublicActionResult
	{
		$result = new PublicActionResult();

		if (Rights::hasAccessForSite($id, Rights::ACCESS_TYPES['read']) && !$binding->isForbiddenBindingAction())
		{
			if ($bind)
			{
				$result->setResult($binding->bindSite($id));
			}
			else
			{
				$result->setResult($binding->unbindSite($id));
			}
		}
		else
		{
			$result->setResult(false);
		}

		return $result;
	}

	/**
	 * Binds site with specific menu.
	 * @param int $id Site id.
	 * @param string $menuCode Menu code.
	 * @return PublicActionResult
	 */
	public static function bindingToMenu(int $id, string $menuCode): PublicActionResult
	{
		\Bitrix\Landing\Site\Type::setScope('KNOWLEDGE');
		$binding = new \Bitrix\Landing\Binding\Menu($menuCode);
		return self::binding($id, $binding, true);
	}

	/**
	 * Unbinds site with specific menu.
	 * @param int $id Site id.
	 * @param string $menuCode Menu code.
	 * @return PublicActionResult
	 */
	public static function unbindingFromMenu(int $id, string $menuCode): PublicActionResult
	{
		\Bitrix\Landing\Site\Type::setScope('KNOWLEDGE');
		$binding = new \Bitrix\Landing\Binding\Menu($menuCode);
		return self::binding($id, $binding, false);
	}

	/**
	 * Binds site with specific socialnetwork group.
	 * @param int $id Site id.
	 * @param int $groupId Group id.
	 * @return PublicActionResult
	 */
	public static function bindingToGroup(int $id, int $groupId): PublicActionResult
	{
		\Bitrix\Landing\Site\Type::setScope('KNOWLEDGE');

		if (
			\Bitrix\landing\Connector\SocialNetwork::canCreateNewBinding($groupId) &&
			!\Bitrix\landing\Binding\Group::getList($groupId)
		)
		{
			$binding = new \Bitrix\Landing\Binding\Group($groupId);
			$result = self::binding($id, $binding, true);
			if ($result->getResult())
			{
				Rights::setGlobalOff();
				\Bitrix\Landing\Site::update($id, [
					'TYPE' => 'GROUP'
				]);
				Rights::setGlobalOn();
			}
			return $result;
		}

		$result = new PublicActionResult();
		$result->setResult(false);
		return $result;
	}

	/**
	 * Unbinds site with specific socialnetwork group.
	 * @param int $id Site id.
	 * @param int $groupId Group id.
	 * @return PublicActionResult
	 */
	public static function unbindingFromGroup(int $id, int $groupId): PublicActionResult
	{
		\Bitrix\Landing\Site\Type::setScope('GROUP');

		if (\Bitrix\landing\Connector\SocialNetwork::canCreateNewBinding($groupId))
		{
			$binding = new \Bitrix\Landing\Binding\Group($groupId);
			$result = self::binding($id, $binding, false);
			if ($result->getResult())
			{
				Rights::setGlobalOff();
				\Bitrix\Landing\Site::update($id, [
					'TYPE' => 'KNOWLEDGE'
				]);
				Rights::setGlobalOn();
			}
			return $result;
		}

		$result = new PublicActionResult();
		$result->setResult(false);
		return $result;
	}

	/**
	 * Removes empty binding.
	 * @param array $bindings Bindings array.
	 * @return array
	 */
	protected static function removeEmptyBindings(array $bindings): array
	{
		// if PUBLIC_URL is empty user don't have read access
		foreach ($bindings as $i => $binding)
		{
			if (!$binding['PUBLIC_URL'])
			{
				unset($bindings[$i]);
			}
		}

		return array_values($bindings);
	}

	/**
	 * Returns exists bindings.
	 * @param string|null $menuCode Menu code (only for this menu).
	 * @return PublicActionResult
	 */
	public static function getMenuBindings(?string $menuCode = null): PublicActionResult
	{
		$result = new PublicActionResult();
		\Bitrix\Landing\Site\Type::setScope('KNOWLEDGE');
		$bindings = \Bitrix\Landing\Binding\Menu::getList($menuCode);
		$result->setResult(self::removeEmptyBindings($bindings));
		return $result;
	}

	/**
	 * Returns exists bindings.
	 * @param int|null $groupId Group id (only for this group).
	 * @return PublicActionResult
	 */
	public static function getGroupBindings(?int $groupId = null): PublicActionResult
	{
		$result = new PublicActionResult();
		\Bitrix\Landing\Site\Type::setScope('GROUP');
		$bindings = \Bitrix\Landing\Binding\Group::getList($groupId);
		$result->setResult(self::removeEmptyBindings($bindings));
		return $result;
	}
}
