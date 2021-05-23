<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

use Bitrix\Main\Context;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\SystemException;
use Bitrix\Main\UI;
use Bitrix\Main\Web\Json;
use Bitrix\Socialservices\ContactConnectTable;
use Bitrix\Socialservices\ContactTable;


class CSocservContactsComponent extends CBitrixComponent
{
	const DEFAULT_PAGE_SIZE = 12;

	const CONNECT_PREFIX = "network";

	protected $userId = null;
	protected $contactList = array();
	protected $navObject = null;

	/**
	 * Load language file.
	 */
	public function onIncludeComponentLang()
	{
		$this->includeComponentLang(basename(__FILE__));
		Loc::loadMessages(__FILE__);
	}

	/**
	 * Is AJAX Request?
	 * @return bool
	 */
	protected function isAjax()
	{
		$request = Context::getCurrent()->getRequest();
		return isset($request['sc_ajax']) && $request['sc_ajax'] == 'Y';
	}

	/**
	 * Prepare Component Params.
	 *
	 * @param array $params Component parameters.
	 * @return array
	 */
	public function onPrepareComponentParams($params)
	{
		global $USER;

		$params["USER_ID"] = intval($params["USER_ID"]);
		$params["NAV_PAGE_SIZE"] = intval($params["NAV_PAGE_SIZE"]);

		if($params["USER_ID"] <= 0)
		{
			$params["USER_ID"] = $USER->GetID();
		}

		if($params["NAV_PAGE_SIZE"] <= 0)
		{
			$params["NAV_PAGE_SIZE"] = static::DEFAULT_PAGE_SIZE;
		}

		return $params;
	}

	/**
	 * Process incoming request
	 * @return void
	 */
	protected function processRequest()
	{
	}

	/**
	 * Check Required Modules
	 * @throws Exception
	 */
	protected function checkModules()
	{
		if (!Loader::includeModule('socialservices'))
		{
			return false;
		}

		return true;
	}

	/**
	 * Check Required functionality
	 * @throws Exception
	 */
	protected function checkAvailability()
	{
		$network = new \Bitrix\Socialservices\Network();
		return $network->isEnabled();
	}

	/**
	 * Get main data - user contacts
	 * @return void
	 */
	protected function prepareData()
	{
		$this->navObject = new UI\PageNavigation("nav-ss-contacts");
		$this->navObject->allowAllRecords(false)
			->setPageSize($this->arParams["NAV_PAGE_SIZE"])
			->initFromUri();

		$contactList = ContactTable::getList(
			array(
				"filter" => array(
					"=USER_ID"=>$this->arParams["USER_ID"]
				),
				"count_total" => true,
				"offset" => $this->navObject->getOffset(),
				"limit" => $this->navObject->getLimit(),
				"select" => array(
					"ID", "CONTACT_NAME", "CONTACT_LAST_NAME", "CONTACT_PHOTO"
				),
				'group' => array("CONNECT.CONTACT_ID"),
				'runtime' => array(
					new \Bitrix\Main\Entity\ReferenceField(
						"CONNECT",
						ContactConnectTable::getEntity(),
						array(
							"=ref.CONTACT_ID" => "this.ID",
							"=ref.CONNECT_TYPE" => new \Bitrix\Main\DB\SqlExpression(
								'?', ContactConnectTable::TYPE_PORTAL
							)
						),
						array("join_type"=>"inner")
					),
				)
			)
		);

		$this->navObject->setRecordCount($contactList->getCount());

		while($contact = $contactList->fetch())
		{
			$this->contactList[$contact["ID"]] = $contact;
		}

		if(count($this->contactList) > 0)
		{
			$dbRes = ContactConnectTable::getList(array(
				'filter' => array("=CONTACT_ID" => array_keys($this->contactList)),
				'select' => array(
					"CONTACT_ID", "CONTACT_PROFILE_ID", "CONTACT_PORTAL", "CONNECT_TYPE"
				)
			));
			while($connect = $dbRes->fetch())
			{
				if(!isset($this->contactList[$connect["CONTACT_ID"]]["CONNECT"]))
				{
					$this->contactList[$connect["CONTACT_ID"]]["CONNECT"] = array();
				}

				$this->contactList[$connect["CONTACT_ID"]]["CONNECT"][] = array(
					'id' => $this->getConnectId($connect),
					'portal' => $connect["CONTACT_PORTAL"]
				);
			}
		}

	}

	/**
	 * Prepare data to render
	 * @return void
	 */
	protected function formatResult()
	{
		$this->arResult['CONTACTS'] = $this->contactList;

		foreach($this->arResult['CONTACTS'] as $key => $contact)
		{
			$this->arResult['CONTACTS'][$key]['NAME_FORMATTED'] = \CUser::FormatName(
				\CSite::GetNameFormat(),
				array(
					'NAME' => $contact['CONTACT_NAME'],
					'LAST_NAME' => $contact['CONTACT_LAST_NAME'],
				),
				false, false
			);
		}

		$this->arResult['NAV'] = $this->navObject;
	}

	protected function getConnectId($connect)
	{
		return static::CONNECT_PREFIX.ContactTable::getConnectId($connect);
	}

	/**
	 * Extract data from cache
	 * @return bool
	 */
	protected function extractDataFromCache()
	{
		return false;
	}

	protected function putDataToCache()
	{
	}

	protected function abortDataCache()
	{
	}

	/**
	 * Start Component
	 */
	public function executeComponent()
	{
		global $APPLICATION;

		if(!$this->checkModules() || !$this->checkAvailability())
		{
			return;
		}

		try
		{
			$this->processRequest();
			if (!$this->extractDataFromCache())
			{
				$this->prepareData();
				$this->formatResult();
				$this->setResultCacheKeys(array());
				$this->includeComponentTemplate();
				$this->putDataToCache();
			}
		}
		catch (SystemException $e)
		{
			$this->abortDataCache();
			if ($this->isAjax())
			{
				$APPLICATION->restartBuffer();
				echo Json::encode(array('STATUS' => 'ERROR', 'MESSAGE' => $e->getMessage()));
				\CMain::FinalActions();
				die();
			}
			ShowError($e->getMessage());
		}
	}
}