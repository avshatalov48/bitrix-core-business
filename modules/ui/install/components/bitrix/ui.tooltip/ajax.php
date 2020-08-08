<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;
use Bitrix\Main\ModuleManager;
use Bitrix\Main\Localization\Loc;

Loc::loadLanguageFile($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/install/components/bitrix/main.user.link/component.php');

class CUITooltipComponentAjaxController extends \Bitrix\Main\Engine\Controller
{
	private $userId = 0;
	private $context = array();
	private $birthday = false;

	private function setUserId($userId = 0)
	{
		$this->userId = intval($userId);
	}

	private function getUserId()
	{
		return intval($this->userId);
	}

	private function setBirthday()
	{
		$userFields = $this->getUserFields($this->getUserId());
		if (!empty($userFields['PERSONAL_BIRTHDAY']))
		{
			$parsedDate = parseDateTime($userFields['PERSONAL_BIRTHDAY'], \CSite::getDateFormat('SHORT'));

			$this->birthday = (
				intval($parsedDate['MM']) == date('n')
				&& intval($parsedDate['DD']) == date('j')
			);
		}
	}

	private function getBirthday()
	{
		return !!$this->birthday;
	}

	private function getHonored()
	{
		$result = false;

		$userId = $this->getUserId();
		if ($userId <= 0)
		{
			return $result;
		}

		if (Loader::includeModule('intranet'))
		{
			$result = \CIntranetUtils::isUserHonoured($userId);
		}

		return $result;
	}

	private function getAbsent()
	{
		$result = false;

		$userId = $this->getUserId();
		if ($userId <= 0)
		{
			return $result;
		}

		if (Loader::includeModule('intranet'))
		{
			$result = \CIntranetUtils::isUserAbsent($userId);
		}

		return $result;
	}

	private function getPosition()
	{
		$result = '';

		$userFields = $this->getUserFields($this->getUserId());

		if ($userFields['WORK_POSITION'] <> '')
		{
			$val = htmlspecialcharsbx($userFields['WORK_POSITION']);
			if (!$this->isExtranetUser())
			{
				if (ModuleManager::isModuleInstalled('intranet'))
				{
					$result = $val;
				}
			}
			else
			{
				$result = Loc::getMessage("MAIN_UL_EXTRANET_USER");
			}
		}

		return $result;
	}

	private function getDepartmentUrl()
	{
		return Option::get('main', 'TOOLTIP_PATH_TO_CONPANY_DEPARTMENT', SITE_DIR."company/structure.php?set_filter_structure=Y&structure_UF_DEPARTMENT=#ID#");
	}

	private function getUserFields($userId = false)
	{
		static $cache = array();

		$result = array();
		$userId = intval($userId);

		if ($userId <= 0)
		{
			return $result;
		}

		if (isset($cache[$userId]))
		{
			$result = $cache[$userId];
		}
		else
		{
			$res = \CUser::getById($userId);
			$result = $cache[$userId] = $res->fetch();
		}

		return $result;
	}

	private function getTooltipUserFields()
	{
		$userFields = $this->getUserFields($this->getUserId());
		$this->setBirthday();
		if (!empty($userFields))
		{
			$userFields['MANAGERS'] = $this->getUserManagers($userFields);
		}

		return $userFields;
	}

	private function getCurrentUserFields()
	{
		return $this->getUserFields($this->getCurrentUser()->getId());
	}

	private function getCardFields()
	{
		$result = array();

		if (!Loader::includeModule('socialnetwork'))
		{
			return $result;
		}

		$result = unserialize(Option::get("socialnetwork", "tooltip_fields", 's:0:"";'));

		if (!is_array($result))
		{
			$result = (
				ModuleManager::isModuleInstalled('intranet')
					? array(
						"EMAIL",
						"WORK_PHONE",
						"PERSONAL_PHOTO",
						"PERSONAL_CITY",
						"WORK_COMPANY",
						"WORK_POSITION",
						"MANAGERS"
					)
					: array(
						"PERSONAL_ICQ",
						"PERSONAL_BIRTHDAY",
						"PERSONAL_PHOTO",
						"PERSONAL_CITY",
						"WORK_COMPANY",
						"WORK_POSITION"
					)
				);
		}

		return $result;
	}

	private function getCardProperties()
	{
		$result = array();

		if (!Loader::includeModule('socialnetwork'))
		{
			return $result;
		}

		$result = unserialize(Option::get("socialnetwork", "tooltip_properties", 's:0:"";'));
		if (
			!is_array($result)
			&& ModuleManager::isModuleInstalled('intranet')
		)
		{
			$result = array(
				"UF_DEPARTMENT",
				"UF_PHONE_INNER",
				"UF_SKYPE"
			);
		}

		return $result;
	}

	private function getCardFieldsValues()
	{
		global $USER_FIELD_MANAGER, $APPLICATION;

		$fieldsSorted = array(
			"LOGIN",
			"NAME",
			"SECOND_NAME",
			"LAST_NAME",
			"WORK_POSITION",
			"UF_DEPARTMENT",
			"MANAGERS",
			"EMAIL",
			"LAST_LOGIN",
			"DATE_REGISTER",
			"PERSONAL_BIRTHDAY",
			"PERSONAL_GENDER",
			"PERSONAL_COUNTRY",
			"PERSONAL_STATE",
			"PERSONAL_ZIP",
			"PERSONAL_CITY",
			"PERSONAL_STREET",
			"PERSONAL_MAILBOX",
			"PERSONAL_PROFESSION",
			"PERSONAL_PHONE",
			"PERSONAL_FAX",
			"PERSONAL_MOBILE",
			"PERSONAL_WWW",
			"PERSONAL_ICQ",
			"PERSONAL_PAGER",
			"PERSONAL_NOTES",
			"WORK_COMPANY",
			"WORK_LOGO",
			"WORK_WWW",
			"WORK_PROFILE",
			"WORK_COUNTRY",
			"WORK_STATE",
			"WORK_ZIP",
			"WORK_CITY",
			"WORK_STREET",
			"WORK_MAILBOX",
			"WORK_DEPARTMENT",
			"WORK_PHONE",
			"WORK_FAX",
			"WORK_PAGER",
			"WORK_NOTES",
		);

		$cardFieldsList = $this->getCardFields();
		$cardPropertiesList = $this->getCardProperties();

		$userFields = $this->getTooltipUserFields();

		$userFieldsFormatted = array();

		foreach ($fieldsSorted as $field)
		{
			if (in_array($field, $cardFieldsList))
			{
				$val = (isset($userFields[$field]) ? $userFields[$field] : '');

				switch ($field)
				{
					case 'LOGIN':
					case 'NAME':
					case 'LAST_NAME':
					case 'SECOND_NAME':
					case 'PERSONAL_PROFESSION':
					case 'PERSONAL_NOTES':
					case 'PERSONAL_PAGER':
					case 'PERSONAL_STATE':
					case 'PERSONAL_ZIP':
					case 'PERSONAL_CITY':
					case 'PERSONAL_STREET':
					case 'PERSONAL_MAILBOX':
					case 'WORK_COMPANY':
					case 'WORK_DEPARTMENT':
					case 'WORK_PROFILE':
					case 'WORK_NOTES':
					case 'WORK_PAGER':
					case 'WORK_STATE':
					case 'WORK_ZIP':
					case 'WORK_CITY':
					case 'WORK_STREET':
					case 'WORK_MAILBOX':
						if ($val <> '')
						{
							$val = htmlspecialcharsbx($val);
						}
						break;
					case 'WORK_POSITION':
						if (
							$val <> ''
							&& ModuleManager::isModuleInstalled('intranet')
							&& !$this->isExtranetUser()
						)
						{
							$val = '';
						}
						break;
					case 'LAST_LOGIN':
					case 'DATE_REGISTER':
						if ($val <> '')
						{
							$val = date($this->getDateTimeFormat(), makeTimeStamp($val, \CSite::getDateFormat("FULL")));
						}
						break;
					case 'EMAIL':
						$val = (
							$val <> ''
							&& ModuleManager::isModuleInstalled('intranet')
								? '<a href="mailto:'.htmlspecialcharsbx($val).'">'.htmlspecialcharsbx($val).'</a>'
								: ''
						);
						break;
					case 'PERSONAL_WWW':
					case 'WORK_WWW':
						if ($val == "http://")
						{
							$val = "";
						}
						elseif ($val <> '')
						{
							$val = htmlspecialcharsbx($val);
							$valLink = $val;
							if (mb_strtolower(mb_substr($val, 0, mb_strlen("http://"))) != "http://")
							{
								$valLink = "http://".$val;
							}
							$val = '<a href="'.$valLink.'" target="_blank">'.$val.'</a>';
						}
						break;
					case 'PERSONAL_COUNTRY':
					case 'WORK_COUNTRY':
						if ($val <> '')
						{
							$val = getCountryById($val);
						}
						break;
					case 'PERSONAL_ICQ':
						if ($val <> '')
						{
							$val = htmlspecialcharsbx($val);
						}
						break;
					case 'PERSONAL_PHONE':
					case 'PERSONAL_FAX':
					case 'PERSONAL_MOBILE':
					case 'WORK_PHONE':
					case 'WORK_FAX':
						if ($val <> '')
						{
							$valEncoded = preg_replace('/[^\d\+]+/', '', htmlspecialcharsbx($val));
							$val = '<a href="callto:'.$valEncoded.'">'.htmlspecialcharsbx($val).'</a>';
						}
						break;
					case 'PERSONAL_GENDER':
						$val = (
							$val == 'F'
								? Loc::getMessage("MAIN_UL_SEX_F")
								: (
									($val == 'M')
										? Loc::getMessage("MAIN_UL_SEX_M")
										: ""
								)
						);
						break;
					case 'PERSONAL_BIRTHDAY':
						if ($val <> '')
						{
							$parsedDate = parseDateTime($val, \CSite::getDateFormat('SHORT'));
							$day = intval($parsedDate["DD"]);
							$month = intval($parsedDate["MM"]);
							$year = intval($parsedDate["YYYY"]);

							$val = $day.' '.toLower(Loc::getMessage('MONTH_'.$month.'_S'));
							if ($userFields['PERSONAL_GENDER'] == 'M')
							{
								$val .= ' '.$year;
							}
						}
						break;
					case 'WORK_LOGO':
						if (intval($val) > 0)
						{
							$size = 150;
							$imageFile = \CFile::getFileArray($val);
							if ($imageFile !== false)
							{
								$file = \CFile::resizeImageGet(
									$imageFile,
									array("width" => $size, "height" => $size),
									BX_RESIZE_IMAGE_PROPORTIONAL,
									false
								);
								$val = \CFile::showImage($file["src"], $size, $size, "border=0", "");
							}
						}
						break;

					case 'MANAGERS':
						$formattedValue = '';
						if (is_array($val))
						{
							foreach($val as $manager)
							{
								$formattedValue .= ($formattedValue <> '' ? ', ' : '').(
									!$this->currentEmailUser()
										? '<a href="'.$manager["URL"].'">'.$manager["NAME_FORMATTED"].'</a>'
										: $manager["NAME_FORMATTED"]
								);
							}
						}
						$val = $formattedValue;
						break;
					default:
						$val = "";
						break;
				}

				if($val <> '')
				{
					$userFieldsFormatted[$field] = array(
						"code" => $field,
						"name" => getMessage("MAIN_UL_".$field),
						"value" => $val
					);
				}
			}
		}

		if (!empty($cardPropertiesList))
		{
			$tooltipUserProperties = $USER_FIELD_MANAGER->getUserFields("USER", $this->getUserId(), LANGUAGE_ID);

			foreach ($tooltipUserProperties as $fieldCode => $userField)
			{
				if (in_array($fieldCode, $cardPropertiesList))
				{
					if (
						ModuleManager::isModuleInstalled('intranet')
						&& $fieldCode == "UF_DEPARTMENT"
					)
					{
						$userField['SETTINGS']['SECTION_URL'] = $this->getDepartmentUrl();
					}

					if (
						(
							Loader::includeModule('extranet')
							&& !\CExtranet::isIntranetUser()
						)
						|| $this->currentEmailUser()
					)
					{
						$userField['SETTINGS']['SECTION_URL'] = false;
					}

					$userField["EDIT_FORM_LABEL"] = $userField["EDIT_FORM_LABEL"] <> '' ? $userField["EDIT_FORM_LABEL"] : $userField["FIELD_NAME"];
					$userField["EDIT_FORM_LABEL"] = htmlspecialcharsEx($userField["EDIT_FORM_LABEL"]);
					$userField["~EDIT_FORM_LABEL"] = $userField["EDIT_FORM_LABEL"];

					$value = "";

					if (
						(
							(is_array($userField["VALUE"]) && empty($userField["VALUE"]))
							|| (!is_array($userField["VALUE"]) && !$userField["VALUE"])
						)
						&& $userField["USER_TYPE_ID"] != "boolean"
					)
					{
						continue;
					}

					ob_start();

					$APPLICATION->IncludeComponent(
						"bitrix:system.field.view",
						$userField["USER_TYPE_ID"],
						array("arUserField" => $userField),
						null,
						array("HIDE_ICONS" => "Y")
					);
					$value .= ob_get_contents();
					ob_end_clean();

					if($value <> '')
					{
						$userFieldsFormatted[$fieldCode] = array(
							"code" => $fieldCode,
							"name" => htmlspecialcharsEx(
								$userField["EDIT_FORM_LABEL"] <> ''
									? $userField["EDIT_FORM_LABEL"]
									: $userField["FIELD_NAME"]
							),
							"value" => $value
						);
					}
				}
			}
		}

		foreach($fieldsSorted as $index => $field)
		{
			if(isset($userFieldsFormatted[$field]))
			{
				$userFieldsFormatted[$field]["sort"] = $index;
			}
		}

		\Bitrix\Main\Type\Collection::sortByColumn($userFieldsFormatted, array("sort" => SORT_ASC), '', 1000, true);

		return $userFieldsFormatted;
	}

	private function currentEmailUser()
	{
		$currentUserFields = $this->getCurrentUserFields();

		return (
			!empty($currentUserFields)
			&& in_array($currentUserFields["EXTERNAL_AUTH_ID"], array('email'))
		);
	}

	private function isExtranetUser()
	{
		$userFields = $this->getTooltipUserFields();

		return (
			ModuleManager::isModuleInstalled('extranet')
			&& (
				(is_array($userFields["UF_DEPARTMENT"]) && empty($userFields["UF_DEPARTMENT"]))
				|| (!is_array($userFields["UF_DEPARTMENT"]) && intval($userFields["UF_DEPARTMENT"]) <= 0)
			)
		);
	}

	private function getUserData()
	{
		$result = array();
		$userFields = $this->getTooltipUserFields();

		if (empty($userFields))
		{
			return $result;
		}

		$detailUrl = $this->getUserUrl();
		$nameFormatted = \CUser::formatName($this->getNameTemplate(), $userFields, true);

		if ($this->currentEmailUser())
		{
			$detailUrl = '';
		}

		$photoSrc = '';

		if (!empty($userFields['PERSONAL_PHOTO']))
		{
			$imageSize = 57;
			$imageFile = \CFile::getFileArray($userFields['PERSONAL_PHOTO']);
			if ($imageFile !== false)
			{
				$imageResized = CFile::resizeImageGet(
					$imageFile,
					array("width" => $imageSize, "height" => $imageSize),
					BX_RESIZE_IMAGE_EXACT,
					false
				);

				$photoSrc = \CFile::showImage($imageResized["src"], $imageSize, $imageSize, "border=0", "");
			}
		}

		$result = array(
			'id' => $userFields['ID'],
			'active' => ($userFields['ACTIVE'] == 'Y'),
			'nameFormatted' => $nameFormatted,
			'photo' => $photoSrc,
			'position' => $this->getPosition(),
			'cardFields' => $this->getCardFieldsValues(),
			'detailUrl' => $detailUrl,
			'hasBirthday' => $this->getBirthday(),
			'hasHonour' => $this->getHonored(),
			'hasAbsence' => $this->getAbsent(),
		);

		return $result;
	}

	private function setContext($params = array())
	{
		if (
			isset($params["entityType"])
			&& $params["entityType"] <> ''
		)
		{
			$this->context["ENTITY_TYPE"] = $params["entityType"];
		}

		if (
			isset($params["entityId"])
			&& intval($params["entityId"]) > 0
		)
		{
			$this->context["ENTITY_ID"] = intval($params["entityId"]);
		}
	}

	private function getContext()
	{
		return $this->context;
	}

	private function getUserUrlTemplate()
	{
		static $result = null;

		if ($result === null)
		{
			$userPage = Option::get('socialnetwork', 'user_page');
			if (!empty($userPage))
			{
				$result = $userPage.'user/#ID#/';
			}

			if (empty($urlTemplate))
			{
				$result = Option::get('intranet', 'search_user_url', '/user/#ID#/');
			}
		}

		return $result;
	}

	private function getUserUrl()
	{
		$result = '';
		$userId = $this->getUserId();

		if ($userId <= 0)
		{
			return $result;
		}

		$result = $this->getUserUrlTemplate();
		if (!empty($result))
		{
			$result = \CComponentEngine::makePathFromTemplate($result, array(
				"user_id" => $userId,
				"USER_ID" => $userId,
				"ID" => $userId
			));
		}

		return $result;
	}

	private function getNameTemplate()
	{
		static $result = null;

		if ($result === null)
		{
			$result = \CSite::getNameFormat(false);
		}

		return $result;
	}

	private function getDateTimeFormat()
	{
		static $result = null;
		global $DB;

		if ($result === null)
		{
			$result = trim($DB->dateFormatToPHP(\CSite::getDateFormat("FULL")));
		}

		return $result;
	}

	private function getUserManagers($userData = array())
	{
		$result = array();
		$userId = $this->getUserId();

		if ($userId <= 0)
		{
			return $result;
		}

		if (empty($userData))
		{
			$userData = $this->getTooltipUserFields();
		}

		if (empty($userData))
		{
			return $result;
		}

		if (
			!empty($userData["UF_DEPARTMENT"])
			&& Loader::includeModule('intranet')
		)
		{
			$result = \CIntranetUtils::getDepartmentManager($userData["UF_DEPARTMENT"], $this->getUserId(), true);

			foreach($result as $key => $manager)
			{
				$result[$key]["NAME_FORMATTED"] = \CUser::formatName($this->getNameTemplate(), $manager, true, false);
				$result[$key]["URL"] = \CComponentEngine::makePathFromTemplate($this->getUserUrlTemplate(), array(
					"user_id" => $manager["ID"],
					"USER_ID" => $manager["ID"],
					"ID" => $manager["ID"]
				));
			}
		}

		return $result;
	}

	private function getCurrentUserPermissions()
	{
		$result = \CSocNetUserPerms::initUserPerms($this->getCurrentUser()->getID(), $this->getUserId(), \CSocNetUser::isCurrentUserModuleAdmin());

		if (
			!Loader::includeModule("video")
			|| !\CVideo::canUserMakeCall()
		)
		{
			$result["Operations"]["videocall"] = false;
		}

		if (!Loader::includeModule("im"))
		{
			$result["Operations"]["message"] = false;
		}

		$result['operations'] = $result["Operations"];
		$result['isCurrentUser'] = $result["IsCurrentUser"];
		$result['relation'] = $result["Relation"];

		unset($result["Operations"]);
		unset($result["IsCurrentUser"]);
		unset($result["Relation"]);

		return $result;
	}

	public function getDataAction($userId, array $params = array())
	{
		$this->setUserId($userId);
		$this->setContext($params);

		$userData = $this->getUserData();

		if (empty($userData))
		{
			$this->addError(new \Bitrix\Main\Error('No user found'));
			return null;
		}

		if(
			Loader::includeModule("socialnetwork")
			&& !\CSocNetUser::canProfileView($this->getCurrentUser()->getId(), $userData['id'], SITE_ID, $this->getContext())
		)
		{
			$this->addError(new \Bitrix\Main\Error('No access'));
			return null;
		}

		$result = array(
			'user' => $userData,
			'currentUserPerms' => $this->getCurrentUserPermissions()
		);

		return $result;
	}
}
