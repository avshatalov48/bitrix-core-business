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

class User extends EntityBase
{
	private $isIntranetUser;

	public function getType(): string
	{
		return AccessCode::TYPE_USER;
	}

	public function getName(): string
	{
		if ($this->model)
		{
			return (!empty($this->model->getName()) || !empty($this->model->getName()))
				? $this->model->getName() . ' ' . $this->model->getLastName()
				: $this->model->getLogin();
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
			$this->model = UserTable::getById($this->id)->fetchObject();
		}
	}

	private function isExtranetUser()
	{
		return !$this->isIntranetUser() && Loader::includeModule('extranet');
	}

	private function isIntranetUser()
	{
		if($this->isIntranetUser !== null)
		{
			return $this->isIntranetUser;
		}

		$this->isIntranetUser = false;
		if(!Loader::includeModule('intranet'))
		{
			return false;
		}
		$queryUser = \CUser::getList(
			'ID',
			'ASC',
			array(
				'ID_EQUAL_EXACT' => $this->id,
			),
			array(
				'FIELDS' => array('ID', 'EXTERNAL_AUTH_ID'),
				'SELECT' => array('UF_DEPARTMENT', 'UF_USER_CRM_ENTITY'),
			)
		);
		if ($user = $queryUser->fetch())
		{
			$this->isIntranetUser = !empty($user['UF_DEPARTMENT'][0]);
		}

		return $this->isIntranetUser;
	}
}