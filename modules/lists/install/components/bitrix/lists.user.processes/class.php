<? if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class ListsSelectElementComponent extends CBitrixComponent
{
	public function onPrepareComponentParams($arParams)
	{
		$arParams['ERROR'] = array();
		if (!Loader::includeModule('lists'))
		{
			$arParams['ERROR'][] = Loc::getMessage('CC_BLL_MODULE_NOT_INSTALLED');
			return $arParams;
		}
		if(!Loader::includeModule('bizproc') || !CBPRuntime::isFeatureEnabled())
		{
			$arParams['ERROR'][] = Loc::getMessage('CC_BLL_BIZPROC_MODULE_NOT_INSTALLED');
			return $arParams;
		}
		global $USER;
		$arParams['LIST_PERM'] = CListPermissions::CheckAccess(
			$USER,
			COption::GetOptionString("lists", "livefeed_iblock_type_id"),
			false
		);
		if($arParams['LIST_PERM'] < 0)
		{
			switch($arParams['LIST_PERM'])
			{
				case CListPermissions::WRONG_IBLOCK_TYPE:
					$arParams['ERROR'][] = Loc::getMessage("CC_BLL_WRONG_IBLOCK_TYPE");
					break;
				case CListPermissions::WRONG_IBLOCK:
					$arParams['ERROR'][] = Loc::getMessage("CC_BLL_WRONG_IBLOCK");
					break;
				case CListPermissions::LISTS_FOR_SONET_GROUP_DISABLED:
					$arParams['ERROR'][] = Loc::getMessage("CC_BLL_LISTS_FOR_SONET_GROUP_DISABLED");
					break;
				default:
					$arParams['ERROR'][] = Loc::getMessage("CC_BLL_UNKNOWN_ERROR");
					break;
			}
		}
		elseif($arParams['LIST_PERM'] <= CListPermissions::ACCESS_DENIED)
		{
			$arParams['ERROR'][] = Loc::getMessage("CC_BLL_ACCESS_DENIED");
		}

		$arParams['IBLOCK_TYPE_ID'] = COption::GetOptionString("lists", "livefeed_iblock_type_id");

		return $arParams;
	}

	public function executeComponent()
	{
		if($this->arParams['SET_TITLE'] == 'Y')
			$this->getApplication()->setTitle(Loc::getMessage('CC_BLL_TITLE'));

		if(!empty($this->arParams['ERROR']))
		{
			ShowError(array_shift($this->arParams['ERROR']));
			return;
		}

		$this->arResult['USER_ID'] = $this->arParams['USER_ID'];
		$this->arResult['GRID_ID'] = 'lists_processes';
		$this->arResult['FILTER_ID'] = 'lists_processes';

		$selectFields = array('ID', 'IBLOCK_TYPE_ID', 'IBLOCK_ID', 'NAME');

		$gridOptions = new CGridOptions($this->arResult['GRID_ID']);
		$gridSort = $gridOptions->getSorting(array('sort' => array('ID' => 'desc')));

		$this->arResult['HEADERS'] = array(
			array(
				"id" => "ID",
				"name" => "ID",
				"default" => false,
				"sort" => "ID"
			),
			array(
				'id' => 'DOCUMENT_NAME',
				'name' => Loc::getMessage('CC_BLL_DOCUMENT_NAME'),
				'default' => true, 'sort' => 'DOCUMENT_NAME'
			),
			array(
				'id' => 'COMMENTS',
				'name' => Loc::getMessage('CC_BLL_COMMENTS'),
				'default' => true,
				'sort' => '',
				'hideName' => true,
				'iconCls' => 'bp-comments-icon'
			),
			array(
				'id' => 'WORKFLOW_PROGRESS',
				'name' => Loc::getMessage('CC_BLL_WORKFLOW_PROGRESS'),
				'default' => true,
				'sort' => ''
			),
			array(
				'id' => 'WORKFLOW_STATE',
				'name' => Loc::getMessage('CC_BLL_WORKFLOW_STATE'),
				'default' => true,
				'sort' => ''
			),
		);

		$this->arResult['FILTER'] = array(
			array(
				"id" => "NAME",
				"name" => Loc::getMessage("BPATL_NAME"),
				"type" => "string",
				'default' => true
			),
			array(
				'id' => 'TIMESTAMP_X',
				'name' => Loc::getMessage('CC_BLL_MODIFIED'),
				'type' => 'date',
				'default' => true
			),
			array(
				'id' => 'DATE_CREATE',
				'name' => Loc::getMessage('CC_BLL_CREATED'),
				'type' => 'date',
				'default' => true
			),
		);
		$filterOption = new Bitrix\Main\UI\Filter\Options($this->arResult['FILTER_ID']);
		$filterData = $filterOption->getFilter($this->arResult['FILTER']);
		foreach($filterData as $key => $value)
		{
			if(empty($value))
				continue;

			if(substr($key, -5) == "_from")
			{
				$op = ">=";
				$new_key = substr($key, 0, -5);
			}
			elseif(substr($key, -3) == "_to")
			{
				$op = "<=";
				$new_key = substr($key, 0, -3);
				$newKey = substr($key, 0, -3);

				if(in_array($newKey, array("TIMESTAMP_X", 'DATE_CREATE')))
				{
					global $DB;
					$dateFormat = $DB->dateFormatToPHP(Csite::getDateFormat());
					$dateParse = date_parse_from_format($dateFormat, $value);
					if(!strlen($dateParse["hour"]) && !strlen($dateParse["minute"]) && !strlen($dateParse["second"]))
					{
						$timeFormat = $DB->dateFormatToPHP(CSite::getTimeFormat());
						$value .= " ".date($timeFormat, mktime(23, 59, 59, 0, 0, 0));
					}
				}
			}
			elseif($key == "NAME")
			{
				$op = "?";
				$new_key = $key;
			}
			else
			{
				$op = "";
				$new_key = $key;
			}

			$filter[$op.$new_key] = $value;

			if($key == "FIND")
			{
				$op = "?";
				$filter[$op."SEARCHABLE_CONTENT"] = $value;
			}
		}

		$this->arResult['SORT'] = $gridSort['sort'];

		$useComments = (bool)CModule::includeModule("forum");
		$workflows = array();
		$this->arResult['DATA'] = array();
		$this->arResult["COMMENTS_COUNT"] = array();

		$filter['CREATED_BY'] = $this->arParams['USER_ID'];
		$iblockTypeId = COption::GetOptionString("lists", "livefeed_iblock_type_id");
		$filter['IBLOCK_TYPE'] = $iblockTypeId;
		$filter['CHECK_PERMISSIONS'] = ($this->arParams['LIST_PERM'] >= CListPermissions::CAN_READ ? "N": "Y");
		$elementObject = CIBlockElement::getList(
			$gridSort['sort'],
			$filter,
			false,
			$gridOptions->getNavParams(),
			$selectFields
		);
		$documentStates = true;
		$path = rtrim(SITE_DIR, '/');
		while($element = $elementObject->fetch())
		{
			$documentStates = CBPDocument::GetDocumentStates(
				BizprocDocument::generateDocumentComplexType($iblockTypeId, $element['IBLOCK_ID']),
				BizprocDocument::getDocumentComplexId($iblockTypeId, $element['ID'])
			);

			if(!empty($documentStates))
			{
				foreach($documentStates as $documentState)
				{
					if(empty($documentState['ID']))
						continue;

					$this->arResult['DATA'][$documentState['ID']]['ID'] = $element['ID'];
					$this->arResult['DATA'][$documentState['ID']]['DOCUMENT_NAME'] = $element['NAME'];
					$this->arResult['DATA'][$documentState['ID']]['DOCUMENT_URL'] = $path.COption::GetOptionString('lists', 'livefeed_url').'?livefeed=y&list_id='.$element["IBLOCK_ID"].'&element_id='.$element['ID'];
					$this->arResult['DATA'][$documentState['ID']]['DOCUMENT_STATE'] = true;
					$this->arResult['DATA'][$documentState['ID']]['WORKFLOW_ID'] = $documentState['ID'];
					$this->arResult['DATA'][$documentState['ID']]["WORKFLOW_NAME"] = $documentState["TEMPLATE_NAME"];
					$this->arResult['DATA'][$documentState['ID']]["WORKFLOW_STATE"] = $documentState["STATE_TITLE"];
					$this->arResult['DATA'][$documentState['ID']]["WORKFLOW_STARTED"] = FormatDateFromDB($documentState["STARTED_FORMATTED"]);
					$this->arResult['DATA'][$documentState['ID']]["WORKFLOW_STARTED_BY"] = "";
					if (intval($documentState["STARTED_BY"]) > 0)
					{
						$dbUserTmp = CUser::getByID($documentState["STARTED_BY"]);
						$arUserTmp = $dbUserTmp->fetch();
						$this->arResult['DATA'][$documentState['ID']]["WORKFLOW_STARTED_BY"] = CUser::FormatName($this->arParams["NAME_TEMPLATE"], $arUserTmp, true);
						$this->arResult['DATA'][$documentState['ID']]["WORKFLOW_STARTED_BY"] .= " [".$documentState["STARTED_BY"]."]";
					}

					$this->arResult['DATA'][$documentState['ID']]['MODULE_ID'] = $documentState["DOCUMENT_ID"][0];
					$this->arResult['DATA'][$documentState['ID']]['ENTITY'] = $documentState["DOCUMENT_ID"][1];
					$this->arResult['DATA'][$documentState['ID']]['DOCUMENT_ID'] = $documentState["DOCUMENT_ID"][2];
				}
			}
		}

		foreach ($this->arResult['DATA'] as $data)
		{
			if($documentStates)
			{
				if ($useComments)
					$workflows[] = 'WF_'.$data['WORKFLOW_ID'];
			}

			$actions = array();
			if (strlen($data["DOCUMENT_URL"]) > 0)
				$actions[] = array('ICONCLASS'=>'', 'DEFAULT' => true, 'TEXT'=>Loc::getMessage('CC_BLL_C_DOCUMENT'),
					'ONCLICK'=>'window.open("'.$data["DOCUMENT_URL"].'");');
			$this->arResult['RECORDS'][] = array('data' => $data, 'actions' => $actions);
		}

		if ($useComments && $documentStates)
		{
			$workflows = array_unique($workflows);
			if ($workflows)
			{
				$iterator = CForumTopic::getList(array(), array("@XML_ID" => $workflows));
				while ($row = $iterator->fetch())
				{
					$this->arResult["COMMENTS_COUNT"][$row['XML_ID']] = $row['POSTS'];
				}
			}
		}

		$this->arResult['COUNTERS'] = array('all' => 0);

		$this->arResult["NAV_OBJECT"] = $elementObject;
		$componentObject = null;
		$this->arResult["GRID_ENABLE_NEXT_PAGE"] = ($elementObject->PAGEN < $elementObject->NavPageCount);
		$this->arResult["NAV_STRING"] = $elementObject->getPageNavStringEx(
			$componentObject, "", "grid", false, null, $gridOptions->getNavParams());
		$this->arResult["GRID_PAGE_SIZES"] = array(
			array("NAME" => "5", "VALUE" => "5"),
			array("NAME" => "10", "VALUE" => "10"),
			array("NAME" => "20", "VALUE" => "20"),
			array("NAME" => "50", "VALUE" => "50"),
			array("NAME" => "100", "VALUE" => "100"),
			array("NAME" => "200", "VALUE" => "200"),
			array("NAME" => "500", "VALUE" => "500")
		);

		$this->includeComponentTemplate();
	}

	protected function getApplication()
	{
		global $APPLICATION;
		return $APPLICATION;
	}

	protected function getUser()
	{
		global $USER;
		return $USER;
	}
}