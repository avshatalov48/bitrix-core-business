<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage tasks
 * @copyright 2001-2021 Bitrix
 */

namespace Bitrix\Main\UI\AccessRights\Entity;


use Bitrix\Main\Access\AccessCode;
use Bitrix\Main\Loader;
use Bitrix\Main\UI\AccessRights\Avatar;
use Bitrix\Main\UserTable;
use Bitrix\Main\Web\Uri;

class User extends EntityBase
{
	private const SELECT_FIELDS = [
		'ID',
		'NAME',
		'LAST_NAME',
		'LOGIN',
		'PERSONAL_PHOTO',
		'UF_DEPARTMENT',
	];

	private $isIntranetUser;
	private static $modelsCache = [];

	public function getType(): string
	{
		return AccessCode::TYPE_USER;
	}

	public function getName(): string
	{
		if ($this->model)
		{
			$name = trim($this->model->getName() . ' ' . $this->model->getLastName());

			return $name ?: $this->model->getLogin();
		}
		return '';
	}

	public function getUrl(): string
	{
		if ($this->isExtranetUser())
		{
			$userPage = \COption::getOptionString("socialnetwork", "user_page", false, \CExtranet::getExtranetSiteID());
			if(!$userPage)
			{
				$userPage = '/extranet/contacts/personal/';
			}
		}
		else
		{
			$userPage = \COption::getOptionString("socialnetwork", "user_page", false, SITE_ID);
			if(!$userPage)
			{
				$userPage = SITE_DIR . 'company/personal/';
			}
		}

		return $userPage . 'user/' .  $this->getId() . '/';
	}

	public function getAvatar(int $width = 58, int $height = 58): ?string
	{
		if ($this->model)
		{
			return Avatar::getSrc($this->model->getPersonalPhoto(), $width, $height);
		}
		return '';
	}

	protected function loadModel()
	{
		if (!$this->model)
		{
			if (array_key_exists($this->id, self::$modelsCache))
			{
				$this->model = self::$modelsCache[$this->id];
			}
			else
			{
				$this->model = UserTable::getList([
					'select' => self::SELECT_FIELDS,
					'filter' => [
						'=ID' => $this->id,
					],
					'limit' => 1,
				])->fetchObject();

				self::$modelsCache[$this->id] = $this->model;
			}
		}
	}

	private function isExtranetUser()
	{
		return !$this->isIntranetUser() && Loader::includeModule('extranet');
	}

	private function isIntranetUser()
	{
		if (isset($this->isIntranetUser))
		{
			return $this->isIntranetUser;
		}

		$this->isIntranetUser = false;

		if ($this->model && Loader::includeModule('intranet'))
		{
			$this->isIntranetUser = !empty($this->model->getUfDepartment());
		}

		return $this->isIntranetUser;
	}

	/**
	 * Preload user models.
	 *
	 * If you need to get information about many users, you need to use this method first.
	 *
	 * @param array $filter for `\Bitrix\Main\UserTable` tablet.
	 *
	 * @return void
	 */
	public static function preLoadModels(array $filter): void
	{
		$rows = UserTable::getList([
			'select' => self::SELECT_FIELDS,
			'filter' => $filter,
		]);
		while ($row = $rows->fetchObject())
		{
			self::$modelsCache[$row->getId()] = $row;
		}
	}
}
