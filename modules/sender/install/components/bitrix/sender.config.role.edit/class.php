<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Web\Uri;
use Bitrix\Sender\Access\ActionDictionary;
use Bitrix\Sender\Access\Service\RoleDealCategoryService;
use Bitrix\Sender\Access\Service\RoleDealCategoryServiceInterface;
use Bitrix\Sender\Access\Service\RolePermissionService;
use Bitrix\Sender\Access\Service\RolePermissionServiceInterface;
use Bitrix\Sender\Access\Service\RoleRelationService;
use Bitrix\Sender\Access\Service\RoleRelationServiceInterface;
use Bitrix\Sender\Integration\Crm\Connectors\Client;
use Bitrix\Sender\Security;

if (!Bitrix\Main\Loader::includeModule('sender'))
{
	ShowError('Module `sender` not installed');
	die();
}

Loc::loadMessages(__FILE__);

class ConfigRoleEditSenderComponent extends Bitrix\Sender\Internals\CommonSenderComponent
{
	/**
	 * @var RolePermissionServiceInterface;
	 */
	private $permissionService;
	/**
	 * @var RoleRelationServiceInterface;
	 */
	private $roleRelationService;

	/**
	 * @var RoleDealCategoryServiceInterface;
	 */
	private $roleDealCategoryService;
	private $settings;

	protected function initParams()
	{
		parent::initParams();
		$this->permissionService   = new RolePermissionService();
		$this->roleRelationService = new RoleRelationService();
		$this->roleDealCategoryService = new RoleDealCategoryService();

		if (!isset($this->arParams['ID']))
		{
			$this->arParams['ID'] = $this->request->get('ID');
		}
		$this->arParams['ID'] = $this->arParams['ID'] === "" ? -1 : (int)$this->arParams['ID'];
	}

	protected function preparePost()
	{

		if ($this->request->get('IFRAME') != 'Y')
			LocalRedirect($this->arParams['PATH_TO_LIST']);

		$uri = new Uri("");

		$uri->addParams(array('IFRAME' => 'Y'));
		$uri->addParams(array('IS_SAVED' => 'Y'));

		$path = $uri->getLocator();
		LocalRedirect($path);
	}

	protected function prepareResult()
	{
		/* Set title */
		if ($this->arParams['SET_TITLE'])
		{
			/**@var CAllMain*/
			$GLOBALS['APPLICATION']->SetTitle(
					Loc::getMessage('SENDER_CONFIG_ROLE_EDIT_COMP_ACCESS_RIGHTS')
			);
		}

		if (!$this->arParams['CAN_EDIT'])
		{
			$this->errors->setError(Security\AccessChecker::getError());
			return false;
		}

		if (!Security\Role\Manager::canUse())
		{
			$this->arResult['CANT_USE'] = true;
		}

		$this->arResult['ERRORS'] = array();
		$this->arResult['ACTION_URI'] = $this->getPath() . '/ajax.php';
		$this->arResult['NAME'] = Loc::getMessage('SENDER_CONFIG_ROLE_EDIT_COMP_TEMPLATE_NAME');

		return true;
	}

	private function getDealCategories()
	{
		$dealCategories = Client::getDealCategoryList();

		$items = [];

		$items[] = [
			'id' => -1,
			'text' => Loc::getMessage('SENDER_DEAL_CATEGORY_ALL'),
			'href' => sprintf("/marketing/config/role/edit/%d/", -1)
		];

		foreach ($dealCategories as $key => $dealCategory)
		{
			$items[] = [
				'id' => $key,
				'text' => $dealCategory,
				'href' => sprintf("/marketing/config/role/edit/%d/", $key)
			];
		}

		return $items;
	}

	protected function getData()
	{
		$dealCategoryId = $this->arParams['ID'] ?? -1;
		$res = $this->permissionService->getRoleList(
			[
				"filter" => ["=DEAL_CATEGORY_ID" => $dealCategoryId]
			]
		);

		if(empty($res))
		{
			$this->roleDealCategoryService
				->fillDefaultDealCategoryPermission($dealCategoryId);
		}

		$this->arResult['USER_GROUPS'] = $this
			->permissionService
			->getUserGroups(
				$this->arParams['ID'] ?? -1
			);

		$this->arResult['ACCESS_RIGHTS'] = $this
			->permissionService
			->getAccessRights();

		$this->arResult['DEAL_CATEGORIES'] = $this->getDealCategories();
	}

	public function executeComponent()
	{
		parent::executeComponent();
		$this->getData();
		$this->prepareResultAndTemplate();
	}

	public function getEditAction()
	{
		return ActionDictionary::ACTION_SETTINGS_EDIT;
	}

	public function getViewAction()
	{
		return ActionDictionary::ACTION_SETTINGS_EDIT;
	}
}