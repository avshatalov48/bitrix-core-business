<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage socialnetwork
 * @copyright 2001-2017 Bitrix
 */
namespace Bitrix\Socialnetwork\Integration\Main\UISelector;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Event;
use Bitrix\Main\EventResult;
use Bitrix\Main\Loader;
use Bitrix\Main\ModuleManager;

Loc::loadMessages(__FILE__);

class Search
{
	protected static function actionProcessAjaxGetDepartmentData($requestFields = array())
	{
		return array(
			'USERS' => \CSocNetLogDestination::getUsers(
				array(
					'deportament_id' => $requestFields['DEPARTMENT_ID'],
					"NAME_TEMPLATE" => Handler::getNameTemplate($requestFields)
				)
			),
			'dataOnly' => true
		);
	}

	public static function searchUsers($params = array(), &$searchModified)
	{
		$search = $params['search'];
		$nameTemplate = $params['nameTemplate'];
		$requestFields = $params['requestFields'];

		$searchResult = \CSocNetLogDestination::searchUsers(
			array(
				"SEARCH" => $search,
				"NAME_TEMPLATE" => $nameTemplate,
				"SELF" => (!isset($requestFields['SELF']) || $requestFields['SELF'] != 'N'),
				"EMPLOYEES_ONLY" => (isset($requestFields['EXTRANET_SEARCH']) && $requestFields['EXTRANET_SEARCH'] == "I"),
				"EXTRANET_ONLY" => (isset($requestFields['EXTRANET_SEARCH']) && $requestFields['EXTRANET_SEARCH'] == "E"),
				"DEPARTAMENT_ID" => (
					isset($requestFields['DEPARTMENT_ID'])
					&& intval($requestFields['DEPARTMENT_ID']) > 0
						? intval($requestFields['DEPARTMENT_ID'])
						: false
				),
				"EMAIL_USERS" => (isset($requestFields['EMAIL_USERS']) && $requestFields['EMAIL_USERS'] == 'Y'),
				"CRMEMAIL_USERS" => (isset($requestFields['CRMEMAIL']) && $requestFields['CRMEMAIL'] == 'Y'),
				"NETWORK_SEARCH" => $params['useNetwork'],
			),
			$searchModified
		);

		return $searchResult;
	}

	protected static function searchSonetGroups($params = array())
	{
		$search = $params['search'];
		$features = $params['features'];

		return \CSocNetLogDestination::searchSonetGroups(array(
			"SEARCH" => $search,
			"FEATURES" => $features
		));
	}

	protected static function searchCrmEmails($params = array(), &$usersList)
	{
		$search = $params['search'];
		$nameTemplate = $params['nameTemplate'];

		$searchResult = \CSocNetLogDestination::searchCrmEntities(array(
			"SEARCH" => $search,
			"NAME_TEMPLATE" => $nameTemplate
		));

		return self::filterCrmSearchResult($searchResult, $usersList);
	}


	protected static function searchCrmEntities($params = array(), &$usersList)
	{
		$search = $params['search'];
		$nameTemplate = $params['nameTemplate'];
		$entitiesList = $params['entities'];
		$searchByEmailOnly = $params['searchByEmailOnly'];

		$searchResult = \CSocNetLogDestination::searchCrmEntities(array(
			"SEARCH" => $search,
			"NAME_TEMPLATE" => $nameTemplate,
			"ENTITIES" => $entitiesList,
			"SEARCH_BY_EMAIL_ONLY" => ($searchByEmailOnly ? "Y" : "N")
		));

		return self::filterCrmSearchResult($searchResult, $usersList);
	}

	protected static function filterCrmSearchResult($searchResult, &$usersList)
	{
		foreach($usersList as $key => $value)
		{
			if (array_key_exists($value["crmEntity"], $searchResult))
			{
				unset($searchResult[$value["crmEntity"]]);
			}
		}

		$crmUsersListTmp = $usersListTmp= array();
		foreach($usersList as $key => $user)
		{
			if (!empty($user['crmEntity']))
			{
				$crmUsersListTmp[$key] = $user;
			}
			else
			{
				$usersListTmp[$key] = $user;
			}
		}
		foreach($searchResult as $key => $user)
		{
			if (!empty($user['crmEntity']))
			{
				$crmUsersListTmp[$key] = $user;
			}
			else
			{
				$usersListTmp[$key] = $user;
			}
		}

		$usersList = $usersListTmp;
		return $crmUsersListTmp;
	}

	protected static function searchCrmContacts($params = array())
	{
		$searchResult = array();

		if (Loader::includeModule('crm'))
		{
			$search = $params['search'];
			$nameTemplate = $params['nameTemplate'];

			$res = \CCrmContact::getListEx(
				[],
				['%FULL_NAME' => $search, '@CATEGORY_ID' => 0,],
				false,
				['nTopCount' => 20],
				['ID', 'NAME', 'SECOND_NAME', 'LAST_NAME', 'COMPANY_TITLE', 'PHOTO']
			);

			while ($res && ($contact = $res->fetch()))
			{
				$searchResult['CRMCONTACT'.$contact['ID']] = array(
					'id' => 'CRMCONTACT'.$contact['ID'],
					'entityType' => 'contacts',
					'entityId' => $contact['ID'],
					'name' => htmlspecialcharsbx(\CUser::formatName(
						$nameTemplate,
						array(
							'LOGIN' => '',
							'NAME' => $contact['NAME'],
							'SECOND_NAME' => $contact['SECOND_NAME'],
							'LAST_NAME' => $contact['LAST_NAME']
						),
						false, false
					)),
					'desc' => htmlspecialcharsbx($contact['COMPANY_TITLE'])
				);

				if (!empty($contact['PHOTO']) && intval($contact['PHOTO']) > 0)
				{
					$image = \CFile::resizeImageGet($contact['PHOTO'], array('width' => 100, 'height' => 100), BX_RESIZE_IMAGE_EXACT);
					$searchResult['CRMCONTACT'.$contact['ID']]['avatar'] = $image['src'];
				}
			}
		}

		return $searchResult;
	}

	protected static function searchCrmCompanies($params = array())
	{
		$searchResult = array();

		if (Loader::includeModule('crm'))
		{
			$search = $params['search'];

			$companyTypeList = \CCrmStatus::getStatusListEx('COMPANY_TYPE');
			$companyIndustryList = \CCrmStatus::getStatusListEx('INDUSTRY');

			$res = \CCrmCompany::getListEx(
				[],
				['%TITLE' => $search, '@CATEGORY_ID' => 0,],
				false,
				['nTopCount' => 20],
				['ID', 'TITLE', 'COMPANY_TYPE', 'INDUSTRY',  'LOGO']
			);

			while ($res && ($company = $res->fetch()))
			{
				$descList = array();
				if (isset($companyTypeList[$company['COMPANY_TYPE']]))
				{
					$descList[] = $companyTypeList[$company['COMPANY_TYPE']];
				}
				if (isset($companyIndustryList[$company['INDUSTRY']]))
				{
					$descList[] = $companyIndustryList[$company['INDUSTRY']];
				}

				$searchResult['CRMCOMPANY'.$company['ID']] = array(
					'id' => 'CRMCOMPANY'.$company['ID'],
					'entityId' => $company['ID'],
					'entityType' => 'companies',
					'name' => htmlspecialcharsbx(str_replace(array(';', ','), ' ', $company['TITLE'])),
					'desc' => htmlspecialcharsbx(implode(', ', $descList))
				);

				if (!empty($company['LOGO']) && intval($company['LOGO']) > 0)
				{
					$image = \CFile::resizeImageGet($company['LOGO'], array('width' => 100, 'height' => 100), BX_RESIZE_IMAGE_EXACT);
					$searchResult['CRMCOMPANY'.$company['ID']]['avatar'] = $image['src'];
				}
			}
		}

		return $searchResult;
	}

	protected static function searchCrmLeads($params = array())
	{
		$searchResult = array();

		if (Loader::includeModule('crm'))
		{
			$search = $params['search'];
			$nameTemplate = $params['nameTemplate'];

			$res = \CCrmLead::getListEx(
				$arOrder = array(),
				$arFilter = array('LOGIC' => 'OR', '%FULL_NAME' => $search, '%TITLE' => $search),
				$arGroupBy = false,
				$arNavStartParams = array('nTopCount' => 20),
				$arSelectFields = array('ID', 'TITLE', 'NAME', 'SECOND_NAME', 'LAST_NAME', 'STATUS_ID')
			);

			while ($res && ($lead = $res->fetch()))
			{
				$searchResult['CRMLEAD'.$lead['ID']] = array(
					'id' => 'CRMLEAD'.$lead['ID'],
					'entityId' => $lead['ID'],
					'entityType' => 'leads',
					'name' => htmlspecialcharsbx($lead['TITLE']),
					'desc' => htmlspecialcharsbx(\CUser::formatName(
						$nameTemplate,
						array(
							'LOGIN' => '',
							'NAME' => $lead['NAME'],
							'SECOND_NAME' => $lead['SECOND_NAME'],
							'LAST_NAME' => $lead['LAST_NAME']
						),
						false, false
					))
				);
			}
		}

		return $searchResult;
	}

	protected static function searchCrmDeals($params = array())
	{
		$searchResult = array();

		if (Loader::includeModule('crm'))
		{
			$search = $params['search'];
			$nameTemplate = $params['nameTemplate'];

			$res = \CCrmDeal::getListEx(
				$arOrder = array(),
				$arFilter = array('%TITLE' => $search),
				$arGroupBy = false,
				$arNavStartParams = array('nTopCount' => 20),
				$arSelectFields = array('ID', 'TITLE', 'COMPANY_TITLE', 'CONTACT_NAME', 'CONTACT_SECOND_NAME', 'CONTACT_LAST_NAME')
			);

			while ($res && ($deal = $res->fetch()))
			{
				$descList = array();
				if ($deal['COMPANY_TITLE'] != '')
				{
					$descList[] = $deal['COMPANY_TITLE'];
				}
				$descList[] = \CUser::formatName(
					$nameTemplate,
					array(
						'LOGIN' => '',
						'NAME' => $deal['CONTACT_NAME'],
						'SECOND_NAME' => $deal['CONTACT_SECOND_NAME'],
						'LAST_NAME' => $deal['CONTACT_LAST_NAME']
					),
					false, false
				);

				$arDeals['CRMDEAL'.$deal['ID']] = array(
					'id' => 'CRMDEAL'.$deal['ID'],
					'entityId' => $deal['ID'],
					'entityType' => 'deals',
					'name' => htmlspecialcharsbx($deal['TITLE']),
					'desc' => htmlspecialcharsbx(implode(', ', $descList))
				);
			}
		}

		return $searchResult;
	}

	public static function process($requestFields = array())
	{
		$result = array(
			'dataOnly' => true
		);

		$search = $requestFields['SEARCH'];
		$searchConverted = (!empty($requestFields['SEARCH_CONVERTED']) ? $requestFields['SEARCH_CONVERTED'] : false);
		$nameTemplate = Handler::getNameTemplate($requestFields);

		if (
			isset($requestFields['ADDITIONAL_SEARCH'])
			&& $requestFields['ADDITIONAL_SEARCH'] == 'Y'
		)
		{
			$result["USERS"] = array();

			return $result;
		}

		if (
			!isset($requestFields['USER_SEARCH'])
			|| $requestFields['USER_SEARCH'] != 'N'
		)
		{
			$searchModified = false;
			$result["USERS"] = self::searchUsers(array(
				'search' => $search,
				'nameTemplate' => $nameTemplate,
				'useNetwork' => false,
				'requestFields' => $requestFields
			), $searchModified);

			if (!empty($searchModified))
			{
				$result['SEARCH'] = $searchModified;
			}

			if (
				empty($result['USERS'])
				&& $searchConverted
				&& $search != $searchConverted
			)
			{
				$result['USERS'] = self::searchUsers(array(
					'search' => $searchConverted,
					'nameTemplate' => $nameTemplate,
					'useNetwork' => false,
					'requestFields' => $requestFields
				), $searchModified);
				$result['SEARCH'] = $searchConverted;
			}
		}

		if (
			isset($requestFields['SEARCH_SONET_GROUPS'])
			&& $requestFields['SEARCH_SONET_GROUPS'] == 'Y'
		)
		{
			$result['SONET_GROUPS'] = self::searchSonetGroups(array(
				'search' => $search,
				'features' => (isset($requestFields['SEARCH_SONET_FEATUES']) && is_array($requestFields['SEARCH_SONET_FEATUES']) ? $requestFields['SEARCH_SONET_FEATUES'] : false),
			));
		}

		if (
			isset($requestFields['CRMEMAIL'])
			&& $requestFields['CRMEMAIL'] == 'Y'
		)
		{
			$result['CRM_EMAILS'] = self::searchCrmEmails(array(
				'search' => $search,
				'nameTemplate' => $nameTemplate
			), $result['USERS']);
		}
		elseif (
			isset($requestFields['CRMCONTACTEMAIL'])
			&& $requestFields['CRMCONTACTEMAIL'] == 'Y'
		)
		{
			$result['CRM_EMAILS'] = self::searchCrmEntities(array(
				'search' => $search,
				'nameTemplate' => $nameTemplate,
				'entities' => array("CONTACT"),
				'searchByEmailOnly' => true
			), $result['USERS']);
		}

		if (
			isset($requestFields['CRM_SEARCH'])
			&& $requestFields['CRM_SEARCH'] == 'Y'
		)
		{
			$crmAllowedTypesList = array();

			if (
				isset($requestFields['CRM_SEARCH_TYPES'])
				&& is_array($requestFields['CRM_SEARCH_TYPES'])
				&& !empty($requestFields['CRM_SEARCH_TYPES'])
			)
			{
				$crmAllowedTypesList = $requestFields['CRM_SEARCH_TYPES'];
			}

			$result['CONTACTS'] = array();
			$result['COMPANIES'] = array();
			$result['LEADS'] = array();
			$result['DEALS'] = array();

			if (
				empty($crmAllowedTypesList)
				|| in_array("CRMCONTACT", $crmAllowedTypesList)
			)
			{
				$result['CONTACTS'] = self::searchCrmContacts(array(
					'search' => $search,
					'nameTemplate' => $nameTemplate
				));
			}

			if (
				empty($crmAllowedTypesList)
				|| in_array("CRMCOMPANY", $crmAllowedTypesList)
			)
			{
				$result['CONTACTS'] = self::searchCrmCompanies(array(
					'search' => $search
				));
			}

			if (
				empty($crmAllowedTypesList)
				|| in_array("CRMLEAD", $crmAllowedTypesList)
			)
			{
				$result['LEADS'] = self::searchCrmLeads(array(
					'search' => $search,
					'nameTemplate' => $nameTemplate
				));
			}

			if (
				empty($crmAllowedTypesList)
				|| in_array("CRMDEAL", $crmAllowedTypesList)
			)
			{
				$result['LEADS'] = self::searchCrmDeals(array(
					'search' => $search,
					'nameTemplate' => $nameTemplate
				));
			}
		}

		return $result;
	}
}
