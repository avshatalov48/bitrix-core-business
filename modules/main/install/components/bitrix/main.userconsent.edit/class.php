<?

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ErrorCollection;
use Bitrix\Main\UserConsent\Agreement;
use Bitrix\Main\UserConsent\Intl;
use Bitrix\Main\UserConsent\Internals\FieldTable;
use Bitrix\Main\UserConsent\Text;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

Loc::loadMessages(__FILE__);

class MainUserConsentEditComponent extends CBitrixComponent
{
	/** @var ErrorCollection $errors */
	protected $errors;

	/** @var  Agreement $agreement */
	protected $agreement;

	protected function checkRequiredParams()
	{
		return true;
	}

	protected function initParams()
	{
		$this->arParams['ID'] = isset($this->arParams['ID']) ? intval($this->arParams['ID']) : null;

		$this->arParams['PATH_TO_ADD'] = isset($this->arParams['PATH_TO_ADD']) ? $this->arParams['PATH_TO_ADD'] : '';
		$this->arParams['PATH_TO_EDIT'] = isset($this->arParams['PATH_TO_EDIT']) ? $this->arParams['PATH_TO_EDIT'] : '';
		$this->arParams['PATH_TO_LIST'] = isset($this->arParams['PATH_TO_LIST']) ? $this->arParams['PATH_TO_LIST'] : '';

		$this->arParams['IFRAME'] = isset($this->arParams['IFRAME']) ? $this->arParams['IFRAME'] == 'Y' : $this->request->get('IFRAME') == 'Y';
		$this->arParams['SET_TITLE'] = isset($this->arParams['SET_TITLE']) ? $this->arParams['SET_TITLE'] == 'Y' : true;
		$this->arParams['CAN_EDIT'] = isset($this->arParams['CAN_EDIT']) ? $this->arParams['CAN_EDIT'] : false;
	}

	protected function processPost()
	{
		$languageId = $this->request->getPost('LANGUAGE_ID');

		$fieldList = array();
		$fields = $this->request->getPost('FIELDS');
		if (isset($fields[$languageId]) && is_array($fields[$languageId]))
		{
			$fieldList = $fields[$languageId];
		}

		$data = array(
			'NAME' => $this->request->getPost('NAME'),
			'TYPE' => $this->request->getPost('TYPE'),
			'LANGUAGE_ID' => $languageId,
			'DATA_PROVIDER' => $this->request->getPost('DATA_PROVIDER'),
			'AGREEMENT_TEXT' => $this->request->getPost('AGREEMENT_TEXT'),
			'LABEL_TEXT' => $this->request->getPost('LABEL_TEXT'),
			'FIELDS' => $fieldList,
		);
		$this->agreement->mergeData($data);
		$this->agreement->save();
		if ($this->agreement->hasErrors())
		{
			$this->arResult['ERRORS'] = $this->agreement->getErrors();
		}
		elseif ($this->request->get('save'))
		{
			if ($this->arParams['IFRAME'])
			{
				$this->arResult['IS_SAVED'] = true;
			}
			else
			{
				LocalRedirect($this->arParams['PATH_TO_LIST']);
			}
		}
		else
		{
			$pathTo = str_replace('#id#', $this->agreement->getId(), $this->arParams['PATH_TO_EDIT']);
			if ($this->arParams['IFRAME'])
			{
				$pathTo .= strpos($pathTo, '?') === false ? '?' : '&';
				$pathTo .= 'IFRAME=Y';
			}

			LocalRedirect($pathTo);
		}
	}

	protected function prepareResult()
	{
		$this->arResult['IS_SAVED'] = false;
		$this->arResult['ERRORS'] = array();
		$this->agreement = new Agreement($this->arParams['ID']);

		if($this->request->isPost() && check_bitrix_sessid() && $this->arParams['CAN_EDIT'])
		{
			$this->processPost();
		}

		/* Set data */
		$this->arResult['DATA'] = $this->agreement->getData();

		/* Set data providers */
		$this->prepareResultDataProviders();

		/* Set types */
		$this->prepareResultTypes();

		/* Set title */
		if ($this->arParams['SET_TITLE'])
		{
			/**@var CMain*/
			$GLOBALS['APPLICATION']->SetTitle(
				$this->agreement->getId()
				?
				Loc::getMessage('MAIN_USER_CONSENT_EDIT_COMP_TITLE_EDIT')
				:
				Loc::getMessage('MAIN_USER_CONSENT_EDIT_COMP_TITLE_ADD')
			);
		}

		return true;
	}

	protected function prepareResultTypes()
	{
		$this->arResult['TYPE_LIST'] = array();
		$data = $this->arResult['DATA'];
		$postFields = (isset($data['FIELDS']) && is_array($data['FIELDS'])) ? $data['FIELDS'] : array();

		$typeNames = Agreement::getTypeNames();
		$this->arResult['TYPE_LIST'][] = array(
			'TYPE' => Agreement::TYPE_CUSTOM,
			'LANGUAGE_ID' => '',
			'NAME' => $typeNames[Agreement::TYPE_CUSTOM],
			'AGREEMENT_TEXT' => '',
			'AVAILABLE' => true,
			'SELECTED' => (Agreement::TYPE_CUSTOM == $data['TYPE'] && !$data['LANGUAGE_ID']),
			'FIELDS' => array(
				array(
					'INPUT_NAME' => 'AGREEMENT_TEXT',
					'CODE' => 'AGREEMENT_TEXT',
					'TYPE' => 'text',
					'CAPTION' => Loc::getMessage('MAIN_USER_CONSENT_EDIT_COMP_FIELD_AGREEMENT_TEXT'),
					'PLACEHOLDER' => Loc::getMessage('MAIN_USER_CONSENT_EDIT_COMP_FIELD_AGREEMENT_TEXT_HINT'),
					'VALUE' => $data['AGREEMENT_TEXT'],
				),
				array(
					'INPUT_NAME' => 'LABEL_TEXT',
					'CODE' => 'LABEL_TEXT',
					'TYPE' => 'string',
					'CAPTION' => Loc::getMessage('MAIN_USER_CONSENT_EDIT_COMP_FIELD_LABEL_TEXT'),
					'PLACEHOLDER' => Loc::getMessage('MAIN_USER_CONSENT_EDIT_COMP_FIELD_LABEL_TEXT_HINT'),
					'VALUE' => $data['LABEL_TEXT'] ? $data['LABEL_TEXT'] : Loc::getMessage('MAIN_USER_CONSENT_EDIT_COMP_FIELD_LABEL_TEXT_DEFAULT_VALUE'),
				)
			),
			'IS_SUPPORT_DATA_PROVIDERS' => false
		);

		$fieldValues = FieldTable::getConsentFields($this->agreement->getId());
		$intlList = Intl::getList();
		foreach ($intlList as $intl)
		{
			$demoTextFields = array('FIELDS' => Loc::getMessage('MAIN_USER_CONSENT_EDIT_COMP_FIELD_FORM_FIELD_NAMES'));
			$fields = array();
			foreach ($intl['FIELDS'] as $field)
			{
				$fieldCode = $field['CODE'];
				if (isset($postFields[$fieldCode]))
				{
					$field['VALUE'] = $postFields[$fieldCode];
				}
				elseif (isset($fieldValues[$fieldCode]))
				{
					$field['VALUE'] = $fieldValues[$fieldCode];
				}
				else
				{
					$field['VALUE'] = '';
				}
				$fields[] = $field;

				$demoTextFields[$fieldCode] = '<' . str_replace(' ', '_', strtoupper($field['CAPTION'])) . '>';
			}

			$this->arResult['TYPE_LIST'][] = array(
				'TYPE' => Agreement::TYPE_STANDARD,
				'LANGUAGE_ID' => $intl['LANGUAGE_ID'],
				'NAME' => $intl['NAME'],
				'AGREEMENT_TEXT' => Text::replace($intl['AGREEMENT_TEXT'], $demoTextFields, true),
				'SELECTED' => (
					Agreement::TYPE_STANDARD == $data['TYPE']
					&&
					$intl['LANGUAGE_ID'] == $data['LANGUAGE_ID']
				),
				'AVAILABLE' => $intl['BASE_LANGUAGE_ID'] == $this->getLanguageId(),
				'FIELDS' => $fields,
				'IS_SUPPORT_DATA_PROVIDERS' => true
			);
		}

		$this->arResult['TYPE_LIST_AVAILABLE_COUNT'] = 0;
		foreach ($this->arResult['TYPE_LIST'] as $type)
		{
			$this->arResult['TYPE_LIST_AVAILABLE_COUNT'] += ($type['SELECTED'] || $type['AVAILABLE']) ? 1 : 0;
		}
	}

	protected function prepareResultDataProviders()
	{
		$this->arResult['DATA_PROVIDER_LIST'] = array();
		$data = $this->arResult['DATA'];

		$dataProviders = \Bitrix\Main\UserConsent\DataProvider::getList();
		foreach ($dataProviders as $provider)
		{
			$provider = $provider->toArray();
			$valuesData = $provider['DATA'];
			$provider['DATA'] = array();
			foreach ($valuesData as $key => $value)
			{
				$provider['DATA'][] = array(
					'CODE' => $key,
					'VALUE' => $value,
				);
			}
			$provider['SELECTED'] = $provider['CODE'] == $data['DATA_PROVIDER'];
			$this->arResult['DATA_PROVIDER_LIST'][] = $provider;
		}
	}

	protected function printErrors()
	{
		foreach ($this->errors as $error)
		{
			ShowError($error);
		}
	}

	public function executeComponent()
	{
		$this->errors = new \Bitrix\Main\ErrorCollection();
		$this->initParams();
		if (!$this->checkRequiredParams())
		{
			$this->printErrors();
			return;
		}

		if (!$this->prepareResult())
		{
			$this->printErrors();
			return;
		}

		$this->includeComponentTemplate();
	}
}