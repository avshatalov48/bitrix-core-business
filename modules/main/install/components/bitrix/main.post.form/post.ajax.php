<?
define("PUBLIC_AJAX_MODE", true);
define("EXTRANET_NO_REDIRECT", true);
define("NO_KEEP_STATISTIC", "Y");
define("NO_AGENT_STATISTIC","Y");
define("NO_AGENT_CHECK", true);
define("DisableEventsCheck", true);

$siteId = (isset($_REQUEST["SITE_ID"]) && is_string($_REQUEST["SITE_ID"])) ? trim($_REQUEST["SITE_ID"]): "";
$siteId = mb_substr(preg_replace("/[^a-z0-9_]/i", "", $siteId), 0, 2);

if (!empty($siteId))
{
	define("SITE_ID", $siteId);
}

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
header('Content-Type: application/x-javascript; charset='.LANG_CHARSET);

if (
	!CModule::IncludeModule("socialnetwork")
	|| IsModuleInstalled("b24network")
)
{
	echo CUtil::PhpToJsObject(Array('ERROR' => 'MODULE_NOT_INSTALLED'));
	die();
}

if (check_bitrix_sessid())
{
	if (
		isset($_POST["nt"])
		&& !empty($_POST["nt"])
	)
	{
		preg_match_all("/(#NAME#)|(#LAST_NAME#)|(#SECOND_NAME#)|(#NAME_SHORT#)|(#SECOND_NAME_SHORT#)|\\s|\\,/", urldecode($_REQUEST["nt"]), $matches);
		$nameTemplate = implode("", $matches[0]);
	}
	else
	{
		$nameTemplate = CSite::GetNameFormat(false);
	}

	if (isset($_POST['LD_SEARCH']) && $_POST['LD_SEARCH'] == 'Y')
	{
		CUtil::decodeURIComponent($_POST);

		$search = $_POST['SEARCH'];
		$searchConverted = (!empty($_POST['SEARCH_CONVERTED']) ? $_POST['SEARCH_CONVERTED'] : false);

		$searchResults = array();

		if (
			isset($_POST['ADDITIONAL_SEARCH'])
			&& $_POST['ADDITIONAL_SEARCH'] == 'Y'
		)
		{
			$searchResults["USERS"] = array();
			if (
				isset($_POST['NETWORK_SEARCH'])
				&& $_POST['NETWORK_SEARCH'] == 'Y'
				&& \Bitrix\Main\Loader::includeModule('socialservices')
			)
			{
				$network = new \Bitrix\Socialservices\Network();
				if ($network->isEnabled())
				{
					$result = $network->searchUser($search);
					if ($result)
					{
						foreach ($result as $user)
						{
							$user = \CSocNetLogDestination::formatNetworkUser($user, array(
								"NAME_TEMPLATE" => $nameTemplate,
							));
							$searchResults["USERS"][$user['id']] = $user;
						}

						$userList = \Bitrix\Main\UserTable::getList(array(
							"select" => array("ID", "XML_ID"),
							"filter" => array(
								"=EXTERNAL_AUTH_ID" => "replica",
								"=XML_ID" => array_keys($searchResults["USERS"]),
							),
						));
						while ($user = $userList->fetch())
						{
							unset($searchResults["USERS"][$user["XML_ID"]]);
						}
					}
				}
			}

			echo CUtil::PhpToJsObject($searchResults);
			return;
		}

		if (
			!isset($_POST['USER_SEARCH'])
			|| $_POST['USER_SEARCH'] != 'N'
		)
		{
			$searchResults['USERS'] = CSocNetLogDestination::SearchUsers(
				array(
					"SEARCH" => $search,
					"NAME_TEMPLATE" => $nameTemplate,
					"SELF" => (!isset($_POST['SELF']) || $_POST['SELF'] != 'N'),
					"EMPLOYEES_ONLY" => (isset($_POST['EXTRANET_SEARCH']) && $_POST['EXTRANET_SEARCH'] == "I"),
					"EXTRANET_ONLY" => (isset($_POST['EXTRANET_SEARCH']) && $_POST['EXTRANET_SEARCH'] == "E"),
					"DEPARTAMENT_ID" => (
					isset($_POST['DEPARTMENT_ID'])
					&& intval($_POST['DEPARTMENT_ID']) > 0
						? intval($_POST['DEPARTMENT_ID'])
						: false
					),
					"EMAIL_USERS" => (isset($_POST['EMAIL_USERS']) && $_POST['EMAIL_USERS'] == 'Y'),
					"CRMEMAIL_USERS" => (isset($_POST['CRMEMAIL']) && $_POST['CRMEMAIL'] == 'Y'),
					"NETWORK_SEARCH" => (isset($_POST['NETWORK_SEARCH']) && $_POST['NETWORK_SEARCH'] == 'Y'),
					"ONLY_WITH_EMAIL" => (isset($_POST['SEARCH_ONLY_WITH_EMAIL']) && $_POST['SEARCH_ONLY_WITH_EMAIL'] == 'Y'),
				),
				$searchModified
			);

			if (!empty($searchModified))
			{
				$searchResults['SEARCH'] = $searchModified;
			}

			if (
				empty($searchResults['USERS'])
				&& $searchConverted
				&& $search != $searchConverted
			)
			{
				$searchResults['USERS'] = CSocNetLogDestination::searchUsers(
					array(
						"SEARCH" => $searchConverted,
						"NAME_TEMPLATE" => $nameTemplate,
						"SELF" => (!isset($_POST['SELF']) || $_POST['SELF'] != 'N'),
						"EMPLOYEES_ONLY" => (isset($_POST['EXTRANET_SEARCH']) && $_POST['EXTRANET_SEARCH'] == "I"),
						"EXTRANET_ONLY" => (isset($_POST['EXTRANET_SEARCH']) && $_POST['EXTRANET_SEARCH'] == "E"),
						"DEPARTAMENT_ID" => (
						isset($_POST['DEPARTMENT_ID'])
						&& intval($_POST['DEPARTMENT_ID']) > 0
							? intval($_POST['DEPARTMENT_ID'])
							: false
						),
						"EMAIL_USERS" => (isset($_POST['EMAIL_USERS']) && $_POST['EMAIL_USERS'] == 'Y'),
						"CRMEMAIL_USERS" => (isset($_POST['CRMEMAIL']) && $_POST['CRMEMAIL'] == 'Y'),
						"ONLY_WITH_EMAIL" => (isset($_POST['SEARCH_ONLY_WITH_EMAIL']) && $_POST['SEARCH_ONLY_WITH_EMAIL'] == 'Y'),
					)
				);
				$searchResults['SEARCH'] = $searchConverted;
			}
		}

		if (
			isset($_POST['SEARCH_SONET_GROUPS'])
			&& $_POST['SEARCH_SONET_GROUPS'] == 'Y'
		)
		{
			$searchResults['SONET_GROUPS'] = CSocNetLogDestination::searchSonetGroups(array(
				"SEARCH" => $search,
				"FEATURES" => (isset($_POST['SEARCH_SONET_FEATUES']) && is_array($_POST['SEARCH_SONET_FEATUES']) ? $_POST['SEARCH_SONET_FEATUES'] : false)
			));
		}

		if (
			isset($_POST['SEARCH_MAIL_CONTACTS'])
			&& $_POST['SEARCH_MAIL_CONTACTS'] == 'Y'
			&& \Bitrix\Main\Loader::includeModule('mail')
		)
		{
			$currentUser = \Bitrix\Main\Engine\CurrentUser::get();
			if (!is_null($currentUser->getId()))
			{
				$searchWords = preg_split('/\s+/', trim($search), ($wordsLimit = 10) + 1);
				$searchWords = array_splice($searchWords, 0, $wordsLimit);
				$sortExpr = '0';
				$sqlHelper = \Bitrix\Main\Application::getConnection()->getSqlHelper();
				foreach ($searchWords as $word)
				{
					$word = str_replace('%', '%%', $word);
					$word = $sqlHelper->forSql($word);
					$sortExpr .= sprintf(
						'+(CASE WHEN %s THEN 2 WHEN %s THEN 1 ELSE 0 END)',
						"(%1\$s LIKE '%%" . $word . "%%')",
						"(%2\$s LIKE '%%" . $word . "%%')"
					);
				}
				$sortWeight = new \Bitrix\Main\Entity\ExpressionField('SORT_WEIGHT', $sortExpr, ['NAME', 'EMAIL']);
				$queryFilter = [
					[
						'LOGIC' => 'OR',
						'%NAME' => $searchWords,
						'%EMAIL' => $searchWords,
					],
				];
				$queryFilter[] = ['=USER_ID' => $currentUser->getId()];
				$mailContacts = \Bitrix\Mail\Internals\MailContactTable::getList([
					'order' => [
						'SORT_WEIGHT' => 'DESC',
						'NAME' => 'ASC',
					],
					'filter' => $queryFilter,
					'select' => ['ID', 'NAME', 'EMAIL', 'ICON', $sortWeight],
					'limit' => 10,
				])->fetchAll();

				$contactAvatars = $resultsMailContacts = [];
				foreach ($mailContacts as $mailContact)
				{
					$resultsMailContacts[$mailContact['EMAIL']] = $mailContact;
				}
				foreach ($resultsMailContacts as $mailContact)
				{
					$email = $mailContact['EMAIL'];
					if ($contactAvatars[$email] === null)
					{
						ob_start();
						$APPLICATION->IncludeComponent('bitrix:mail.contact.avatar', '',
							[
								'mailContact' => $mailContact,
							]);
						$contactAvatars[$email] = ob_get_clean();
					}
					$searchResults['MAIL_CONTACTS']['MC' . $mailContact['ID']] = [
						'id' => 'MC' . $mailContact['ID'],
						'entityType' => 'mailContacts',
						'entityId' => $mailContact['ID'],
						'name' => htmlspecialcharsbx($mailContact['NAME']),
						'iconCustom' => $contactAvatars[$email],
						'email' => htmlspecialcharsbx($mailContact['EMAIL']),
						'desc' => htmlspecialcharsbx($mailContact['EMAIL']),
					];
				}
			}
		}

		if (
			isset($_POST['CRMEMAIL'])
			&& $_POST['CRMEMAIL'] == 'Y'
		)
		{
			$searchResults['CRM_EMAILS'] = CSocNetLogDestination::SearchCrmEntities(array(
				"SEARCH" => $search,
				"NAME_TEMPLATE" => $nameTemplate,
				"ONLY_WITH_EMAIL" => (isset($_POST['SEARCH_ONLY_WITH_EMAIL']) && $_POST['SEARCH_ONLY_WITH_EMAIL'] == 'Y'),
			));

			foreach($searchResults['USERS'] as $key => $arValue)
			{
				if (array_key_exists($arValue["crmEntity"], $searchResults['CRM_EMAILS']))
				{
					unset($searchResults['CRM_EMAILS'][$arValue["crmEntity"]]);
				}
			}

			$arUsersTmp = $arCrmUsersTmp = array();
			foreach($searchResults['USERS'] as $key => $ar)
			{
				if (!empty($ar['crmEntity']))
				{
					$arCrmUsersTmp[$key] = $ar;
				}
				else
				{
					$arUsersTmp[$key] = $ar;
				}
			}
			foreach($searchResults['CRM_EMAILS'] as $key => $ar)
			{
				if (!empty($ar['crmEntity']))
				{
					$arCrmUsersTmp[$key] = $ar;
				}
				else
				{
					$arUsersTmp[$key] = $ar;
				}
			}

			$searchResults['USERS'] = $arUsersTmp;
			$searchResults['CRM_EMAILS'] = $arCrmUsersTmp;
		}
		elseif (
			isset($_POST['CRMCONTACTEMAIL'])
			&& $_POST['CRMCONTACTEMAIL'] == 'Y'
		)
		{
			$searchResults['CRM_EMAILS'] = CSocNetLogDestination::SearchCrmEntities(array(
				"SEARCH" => $search,
				"NAME_TEMPLATE" => $nameTemplate,
				"ENTITIES" => array("CONTACT"),
				"SEARCH_BY_EMAIL_ONLY" => "Y",
				"ONLY_WITH_EMAIL" => (isset($_POST['SEARCH_ONLY_WITH_EMAIL']) && $_POST['SEARCH_ONLY_WITH_EMAIL'] == 'Y'),
			));

			foreach($searchResults['USERS'] as $key => $arValue)
			{
				if (array_key_exists($arValue["crmEntity"], $searchResults['CRM_EMAILS']))
				{
					unset($searchResults['CRM_EMAILS'][$arValue["crmEntity"]]);
				}
			}

			$arUsersTmp = $arCrmUsersTmp = array();
			foreach($searchResults['USERS'] as $key => $ar)
			{
				if (!empty($ar['crmEntity']))
				{
					$arCrmUsersTmp[$key] = $ar;
				}
				else
				{
					$arUsersTmp[$key] = $ar;
				}
			}
			foreach($searchResults['CRM_EMAILS'] as $key => $ar)
			{
				if (!empty($ar['crmEntity']))
				{
					$arCrmUsersTmp[$key] = $ar;
				}
				else
				{
					$arUsersTmp[$key] = $ar;
				}
			}

			$searchResults['USERS'] = $arUsersTmp;
			$searchResults['CRM_EMAILS'] = $arCrmUsersTmp;
		}

		if (
			isset($_POST['CRM_SEARCH'])
			&& $_POST['CRM_SEARCH'] == 'Y'
			&& CModule::IncludeModule('crm')
		)
		{
			$siteNameFormat = CSite::GetNameFormat(false);
			$arCrmAllowedTypes = array();

			if (
				isset($_POST['CRM_SEARCH_TYPES'])
				&& is_array($_POST['CRM_SEARCH_TYPES'])
				&& !empty($_POST['CRM_SEARCH_TYPES'])
			)
			{
				$arCrmAllowedTypes = $_POST['CRM_SEARCH_TYPES'];
			}

			$arContacts = $arCompanies = $arLeads = $arDeals = array();

			if (
				empty($arCrmAllowedTypes)
				|| in_array("CRMCONTACT", $arCrmAllowedTypes)
			)
			{
				$searchParts = preg_split ('/[\s]+/', $search, 2, PREG_SPLIT_NO_EMPTY);
				if(count($searchParts) < 2)
				{
					$arFilter = array('%FULL_NAME' => $search);
				}
				else
				{
					$arFilter = array('LOGIC' => 'AND');
					for($i = 0; $i < 2; $i++)
					{
						$arFilter["__INNER_FILTER_NAME_{$i}"] = array('%FULL_NAME' => $searchParts[$i]);
					}
				}

				if (isset($_POST['SEARCH_ONLY_WITH_EMAIL']) && $_POST['SEARCH_ONLY_WITH_EMAIL'] == 'Y')
				{
					$arFilter['=HAS_EMAIL'] = 'Y';
				}
				$arFilter['@CATEGORY_ID'] = 0;

				$dbContacts = \CCrmContact::GetListEx(
					$arOrder = array(),
					$arFilter,
					$arGroupBy = false,
					$arNavStartParams = array('nTopCount' => 20),
					$arSelectFields = array('ID', 'NAME', 'SECOND_NAME', 'LAST_NAME', 'COMPANY_TITLE', 'PHOTO', 'HAS_EMAIL')
				);

				while ($dbContacts && ($arContact = $dbContacts->fetch()))
				{
					$entityId = 'CRMCONTACT'.$arContact['ID'];
					$arContacts[$entityId] = array(
						'id'         => $entityId,
						'entityType' => 'contacts',
						'entityId'   => $arContact['ID'],
						'name'       => htmlspecialcharsbx(CUser::FormatName(
							$siteNameFormat,
							array(
								'LOGIN'       => '',
								'NAME'        => $arContact['NAME'],
								'SECOND_NAME' => $arContact['SECOND_NAME'],
								'LAST_NAME'   => $arContact['LAST_NAME']
							),
							false, false
						)),
						'desc' => htmlspecialcharsbx($arContact['COMPANY_TITLE'])
					);

					if (!empty($arContact['PHOTO']) && intval($arContact['PHOTO']) > 0)
					{
						$arImg = CFile::ResizeImageGet($arContact['PHOTO'], array('width' => 100, 'height' => 100), BX_RESIZE_IMAGE_EXACT);
						$arContacts[$entityId]['avatar'] = $arImg['src'];
					}
					if (!empty($arContact['HAS_EMAIL']) && $arContact['HAS_EMAIL'] == 'Y')
					{
						// adding crm multi field to result array
						$dbContactsFields = \CCrmFieldMulti::GetList(
							array('ID' => 'asc'),
							array(
								'ENTITY_ID' => \CCrmOwnerType::ContactName,
								'TYPE_ID' => \CCrmFieldMulti::EMAIL,
								'ELEMENT_ID' => $arContact['ID'],
							)
						);
						while ($arMulti = $dbContactsFields->Fetch())
						{
							if (!empty($arMulti['VALUE']))
							{
								$arContacts[$entityId]['email'] = htmlspecialcharsbx($arMulti['VALUE']);
								break;
							}
						}
					}
				}
			}

			if (
				empty($arCrmAllowedTypes)
				|| in_array("CRMCOMPANY", $arCrmAllowedTypes)
			)
			{
				$arFilter = array('%TITLE' => $search);
				if(isset($_POST['CRMCOMPANYMY']) && $_POST['CRMCOMPANYMY'] == 'Y')
				{
					$arFilter['=IS_MY_COMPANY'] = 'Y';
				}

				if (isset($_POST['SEARCH_ONLY_WITH_EMAIL']) && $_POST['SEARCH_ONLY_WITH_EMAIL'] == 'Y')
				{
					$arFilter['=HAS_EMAIL'] = 'Y';
				}
				$arFilter['@CATEGORY_ID'] = 0;

				$arCompanyTypeList = \CCrmStatus::GetStatusListEx('COMPANY_TYPE');
				$arCompanyIndustryList = \CCrmStatus::GetStatusListEx('INDUSTRY');
				$dbCompanies = \CCrmCompany::GetListEx(
					array(),
					$arFilter,
					false,
					array('nTopCount' => 20),
					array('ID', 'TITLE', 'COMPANY_TYPE', 'INDUSTRY', 'LOGO', 'HAS_EMAIL')
				);

				while ($dbCompanies && ($arCompany = $dbCompanies->fetch()))
				{
					$entityId = 'CRMCOMPANY'.$arCompany['ID'];

					$arDesc = Array();
					if (isset($arCompanyTypeList[$arCompany['COMPANY_TYPE']]))
					{
						$arDesc[] = $arCompanyTypeList[$arCompany['COMPANY_TYPE']];
					}
					if (isset($arCompanyIndustryList[$arCompany['INDUSTRY']]))
					{
						$arDesc[] = $arCompanyIndustryList[$arCompany['INDUSTRY']];
					}

					$arCompanies[$entityId] = array(
						'id'         => $entityId,
						'entityId'   => $arCompany['ID'],
						'entityType' => 'companies',
						'name'       => htmlspecialcharsbx(str_replace(array(';', ','), ' ', $arCompany['TITLE'])),
						'desc'       => htmlspecialcharsbx(implode(', ', $arDesc))
					);

					if (!empty($arCompany['LOGO']) && intval($arCompany['LOGO']) > 0)
					{
						$arImg = \CFile::ResizeImageGet($arCompany['LOGO'], array('width' => 100, 'height' => 100), BX_RESIZE_IMAGE_EXACT);
						$arCompanies[$entityId]['avatar'] = $arImg['src'];
					}
					if (!empty($arCompany['HAS_EMAIL']) && $arCompany['HAS_EMAIL'] == 'Y')
					{
						$dbCompanyFields = \CCrmFieldMulti::GetList(
							array('ID' => 'asc'),
							array(
								'ENTITY_ID' => \CCrmOwnerType::CompanyName,
								'TYPE_ID' => \CCrmFieldMulti::EMAIL,
								'ELEMENT_ID' => $arCompany['ID'],
							)
						);
						while ($arMulti = $dbCompanyFields->Fetch())
						{
							if (!empty($arMulti['VALUE']))
							{
								$arCompanies[$entityId]['email'] = htmlspecialcharsbx($arMulti['VALUE']);
								break;
							}
						}
					}
				}
			}

			if (
				empty($arCrmAllowedTypes)
				|| in_array("CRMLEAD", $arCrmAllowedTypes)
			)
			{

				$arFilter = array(
					'LOGIC' => 'OR',
					'%FULL_NAME' => $search,
					'%TITLE' => $search,
				);
				if (isset($_POST['SEARCH_ONLY_WITH_EMAIL']) && $_POST['SEARCH_ONLY_WITH_EMAIL'] == 'Y')
				{
					$arFilter = array(
						'=HAS_EMAIL' => 'Y',
						'__INNER_FILTER_1' => $arFilter
					);
				}

				$dbLeads = \CCrmLead::GetListEx(
					$arOrder = array(),
					$arFilter,
					$arGroupBy = false,
					$arNavStartParams = array('nTopCount' => 20),
					$arSelectFields = array('ID', 'TITLE', 'NAME', 'SECOND_NAME', 'LAST_NAME', 'STATUS_ID', 'HAS_EMAIL')
				);
				while ($dbLeads && ($arLead = $dbLeads->fetch()))
				{
					$entityId = 'CRMLEAD'.$arLead['ID'];
					$arLeads[$entityId] = array(
						'id'         => $entityId,
						'entityId'   => $arLead['ID'],
						'entityType' => 'leads',
						'name'       => htmlspecialcharsbx($arLead['TITLE']),
						'desc'       => htmlspecialcharsbx(CUser::FormatName(
							$siteNameFormat,
							array(
								'LOGIN'       => '',
								'NAME'        => $arLead['NAME'],
								'SECOND_NAME' => $arLead['SECOND_NAME'],
								'LAST_NAME'   => $arLead['LAST_NAME']
							),
							false, false
						))
					);
					if (!empty($arLead['HAS_EMAIL']) && $arLead['HAS_EMAIL'] == 'Y')
					{
						$dbLeadFields = \CCrmFieldMulti::GetList(
							array('ID' => 'asc'),
							array(
								'ENTITY_ID' => \CCrmOwnerType::LeadName,
								'TYPE_ID' => \CCrmFieldMulti::EMAIL,
								'ELEMENT_ID' => $arLead['ID'],
							)
						);
						while ($arMulti = $dbLeadFields->Fetch())
						{
							if (!empty($arMulti['VALUE']))
							{
								$arLeads[$entityId]['email'] = htmlspecialcharsbx($arMulti['VALUE']);
								break;
							}
						}
					}
				}
			}

			if (
				empty($arCrmAllowedTypes)
				|| in_array("CRMDEAL", $arCrmAllowedTypes)
			)
			{
				$dbDeals = \CCrmDeal::GetListEx(
					$arOrder = array(),
					$arFilter = array('%TITLE' => $search),
					$arGroupBy = false,
					$arNavStartParams = array('nTopCount' => 20),
					$arSelectFields = array('ID', 'TITLE', 'COMPANY_TITLE', 'CONTACT_NAME', 'CONTACT_SECOND_NAME', 'CONTACT_LAST_NAME')
				);

				while ($dbDeals && ($arDeal = $dbDeals->fetch()))
				{
					$arDesc = array();
					if ($arDeal['COMPANY_TITLE'] != '')
						$arDesc[] = $arDeal['COMPANY_TITLE'];
					$arDesc[] = CUser::FormatName(
						$siteNameFormat,
						array(
							'LOGIN' => '',
							'NAME' => $arDeal['CONTACT_NAME'],
							'SECOND_NAME' => $arDeal['CONTACT_SECOND_NAME'],
							'LAST_NAME' => $arDeal['CONTACT_LAST_NAME']
						),
						false, false
					);

					$arDeals['CRMDEAL'.$arDeal['ID']] = array(
						'id' => 'CRMDEAL'.$arDeal['ID'],
						'entityId' => $arDeal['ID'],
						'entityType' => 'deals',
						'name' => htmlspecialcharsbx($arDeal['TITLE']),
						'desc' => htmlspecialcharsbx(implode(', ', $arDesc))
					);
				}
			}

			$searchResults['CONTACTS'] = $arContacts;
			$searchResults['COMPANIES'] = $arCompanies;
			$searchResults['LEADS'] = $arLeads;
			$searchResults['DEALS'] = $arDeals;
		}
		echo CUtil::PhpToJsObject($searchResults);
	}
	elseif ($_POST['LD_DEPARTMENT_RELATION'] == 'Y')
	{
		echo CUtil::PhpToJsObject(Array(
			'USERS' => CSocNetLogDestination::GetUsers(Array('deportament_id' => $_POST['DEPARTMENT_ID'], "NAME_TEMPLATE" => $nameTemplate)),
		));
	}
	elseif ($_POST['LD_ALL'] == 'Y')
	{
		echo CUtil::PhpToJsObject(Array(
			'USERS' => CSocNetLogDestination::GetUsers(Array('all' => 'Y', "NAME_TEMPLATE" => $nameTemplate)),
		));
	}
	else
	{
		echo CUtil::PhpToJsObject(Array('ERROR' => 'UNKNOWN_ERROR'));
	}
}
else
{
	echo CUtil::PhpToJsObject(Array('ERROR' => 'SESSION_ERROR'));
}
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");
?>