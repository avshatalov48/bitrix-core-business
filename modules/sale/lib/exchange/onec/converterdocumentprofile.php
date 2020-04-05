<?php
namespace Bitrix\Sale\Exchange\OneC;
use Bitrix\Main\ArgumentException;


/**
 * Class ConverterDocumentProfile
 * @package Bitrix\Sale\Exchange\OneC
 * @deprecated
 */
class ConverterDocumentProfile extends Converter
{
	/**
	 * @return array
	 */
	protected function getFieldsInfo()
	{
		return UserProfileDocument::getFieldsInfo();
	}

	/**
	 * @param $documentImport
	 * @return array
	 * @throws ArgumentException
	 */
	public function resolveParams($documentImport)
	{
		if(!($documentImport instanceof DocumentBase))
			throw new ArgumentException("Document must be instanceof DocumentBase");

		$result = array();

		$params = $documentImport->getFieldValues();
		foreach($params as $k=>$v)
		{
			switch($k)
			{
				case 'VERSION':
					if(!empty($v))
						$profile['VERSION_1C'] = $v;
					break;
				case 'XML_ID':
				case 'OFICIAL_NAME':
				case 'FULL_NAME':
				case 'INN':
				case 'KPP':
				case 'OKPO_CODE':
				case 'EGRPO':
				case 'OKVED':
				case 'OKDP':
				case 'OKOPF':
				case 'OKFC':
					//case 'OKPO':
					if(!empty($v))
						$profile[$k] = $v;
					break;
				case 'ITEM_NAME':
					if(!empty($v))
					{
						$profile[$k] = $v;
						$profile['AGENT_NAME'] = $v;
					}
					break;
				case 'REGISTRATION_ADDRESS':
				case 'UR_ADDRESS':
				case 'ADDRESS':
					foreach($params[$k] as $name=>$values)
					{
						if($name == 'ADDRESS_FIELD')
						{
							foreach($values as $nameAddres=>$valuesAddres)
							{
								$profile[$k][$nameAddres] = $valuesAddres['VALUE'];
							}
						}
						else
						{
							$profile[$k][$name] = $values;
						}
					}
					break;
				case 'CONTACTS':
					foreach($params[$k]['CONTACT'] as $name=>$values)
					{
						$profile['CONTACT'][$name] = $values['VALUE'];
					}

					$profile['CONTACT']['EMAIL'] = !empty($profile['CONTACT']['MAIL_NEW'])? $profile['CONTACT']['MAIL_NEW']:null;
					$profile['CONTACT']['PHONE'] = !empty($profile['CONTACT']['WORK_PHONE_NEW'])? $profile['CONTACT']['WORK_PHONE_NEW']:null;

					break;
				case 'REPRESENTATIVES':
					foreach($params[$k]['REPRESENTATIVE'] as $name=>$values)
					{
						if($name == 'CONTACT_PERSON')
						{
							$profile['CONTACT'][$name] = $values['ITEM_NAME'];
						}
					}
					break;
			}
		}

		if(!empty($profile["OKPO_CODE"]))
			$profile["OKPO"] = $profile["OKPO_CODE"];

		if(strlen($profile["OFICIAL_NAME"]) > 0 && strlen($profile["INN"]) > 0)
			$profile["TYPE"] = "UR";
		elseif(strlen($profile["INN"]) > 0)
			$profile["TYPE"] = "IP";
		else
			$profile["TYPE"] = "FIZ";

		if(!empty($profile))
		{
			$property = array();
			foreach($profile as $name => $value)
			{
				switch($name)
				{
					case 'ID':
					case 'VERSION':
					case 'ITEM_NAME':
					case 'OFICIAL_NAME':
					case 'FULL_NAME':
					case 'INN':
					case 'KPP':
					case 'OKPO_CODE':
					case 'EGRPO':
					case 'OKVED':
					case 'OKDP':
					case 'OKOPF':
					case 'OKFC':
					case 'OKPO':
						$property[$name] = $value;
						break;
					case 'CONTACT':
						$property["EMAIL"] = $value["MAIL_NEW"];
						$property["PHONE"] = $value["WORK_PHONE_NEW"];
						break;
					case 'REPRESENTATIVE':
						$property["CONTACT_PERSON"] = $value["CONTACT_PERSON"];
						break;
					case 'REGISTRATION_ADDRESS':
					case 'UR_ADDRESS':
						foreach($value as $nameProperty => $valueProperty)
						{
							if(strlen($valueProperty) > 0 && empty($property[$nameProperty]))
								$property[$nameProperty] = $valueProperty;
						}
						$property["ADDRESS_FULL"] = $value["PRESENTATION"];
						$property["INDEX"] = $value["POST_CODE"];
						break;
					case 'ADDRESS':
						foreach($value as $nameProperty => $valueProperty)
						{
							if(strlen($valueProperty) > 0 && empty($property["F_".$nameProperty]))
								$property["F_".$nameProperty] = $valueProperty;
						}
						$property["F_ADDRESS_FULL"] = $value["PRESENTATION"];
						$property["F_INDEX"] = $value["POST_CODE"];
						break;
				}
			}
		}

		$result['TRAITS'] = isset($profile)? $profile:array();
		$result['ORDER_PROPS'] = isset($property)? $property:array();

		return $result;
	}

	/**
	 * @param null $entity
	 * @param array $fields
	 * @return array
	 */
	public function sanitizeFields($entity = null, array &$fields)
	{
		// TODO: Implement sanitizeFields() method.
	}

	public function externalize(array $fields)
	{
		$result = array();
		$traits = $fields['TRAITS'];
		$businessValue = $fields['BUSINESS_VALUE'];

		$availableFields = $this->getFieldsInfo();

		foreach ($availableFields as $k=>$v)
		{
			$value='';
			$replacedFields = $businessValue;
			switch ($k)
			{
				case 'XML_ID':
					$value = $this->getXmlId($traits);
					break;
				case 'ITEM_NAME':
					$value = $businessValue['AGENT_NAME'];
					break;
				case 'INN':
					$value = $businessValue['INN'];
					break;
				case 'KPP':
					$value = $businessValue['KPP'];
					break;
				case 'ADDRESS':
					$replaceNameFields = array(
						'PRESENTATION' => 'F_ADDRESS_FULL',
						'POST_CODE' => 'F_INDEX',
						'COUNTRY' => 'F_COUNTRY',
						'REGION' => 'F_REGION',
						'STATE' => 'F_STATE',
						'SMALL_CITY' => 'F_TOWN',
						'CITY' => 'F_CITY',
						'STREET' => 'F_STREET',
						'HOUSE' => 'F_HOUSE',
						'BUILDING' => 'F_BUILDING',
						'FLAT' => 'F_FLAT');
					$this->replaceNameFields($replacedFields, $replaceNameFields);
					$value = $this->externalizeArrayFields($replacedFields, $v);
					break;
				case 'CONTACTS':
					$replaceNameFields = array(
						'WORK_PHONE_NEW' => 'PHONE',
						'MAIL_NEW' => 'EMAIL'
					);
					$this->replaceNameFields($replacedFields, $replaceNameFields);
					$value = $this->externalizeArrayFields($replacedFields, $v);
					break;
				case 'REPRESENTATIVES':
					$replaceNameFields = array(
						'RELATION' => 'CONTACT_PERSON',
						'ID' => 'CONTACT_PERSON',
						'ITEM_NAME' => 'CONTACT_PERSON',
					);
					$this->replaceNameFields($replacedFields, $replaceNameFields);
					$value = $this->externalizeArrayFields($replacedFields, $v);
					break;
				case 'ROLE':
					$value = DocumentBase::getLangByCodeField('BUYER');
					break;
			}
			$result[$k] = $value;
		}

		if($this->isFiz($businessValue))
		{
			foreach ($availableFields as $k=>$v)
			{
				if(in_array($k, array('XML_ID','ITEM_NAME', 'INN', 'KPP', 'ADDRESS', 'CONTACTS', 'REPRESENTATIVES', 'ROLE')))
					continue;

				$value='';
				$replacedFields = $businessValue;
				switch ($k)
				{
					case 'FULL_NAME':
						$value = $businessValue['FULL_NAME'];
						break;
					case 'SURNAME':
						$value = $businessValue['SURNAME'];
						break;
					case 'NAME':
						$value = $businessValue['NAME'];
						break;
					case 'MIDDLE_NAME':
						$value = $businessValue['SECOND_NAME'];
						break;
					case 'BIRTHDAY':
						$value = $businessValue['BIRTHDAY'];
						break;
					case 'SEX':
						$value = $businessValue['MALE'];
						break;
					case 'REGISTRATION_ADDRESS':
						$replaceNameFields = array(
							'PRESENTATION'=>'ADDRESS_FULL',
							'POST_CODE'=>'INDEX',
						);
						$this->replaceNameFields($replacedFields, $replaceNameFields);
						$value = $this->externalizeArrayFields($replacedFields, $v);
						break;
				}
				if(!is_array($value))
					$this->externalizeField($value, $v);

				$result[$k] = $value;

			}
		}
		else
		{
			foreach ($availableFields as $k=>$v)
			{
				if(in_array($k, array('XML_ID','ITEM_NAME', 'INN', 'KPP', 'ADDRESS', 'CONTACTS', 'REPRESENTATIVES', 'ROLE')))
					continue;

				$value='';
				$replacedFields = $businessValue;
				switch ($k)
				{
					case 'UR_ADDRESS':
						$replaceNameFields = array(
							'PRESENTATION'=>'ADDRESS_FULL',
							'POST_CODE'=>'INDEX',
						);
						$this->replaceNameFields($replacedFields, $replaceNameFields);
						$value = $this->externalizeArrayFields($replacedFields, $v);
						break;
					case 'OFICIAL_NAME':
						$value = $businessValue['FULL_NAME'];
						break;
					case 'EGRPO':
						$value = $businessValue['EGRPO'];
						break;
					case 'OKVED':
						$value = $businessValue['OKVED'];
						break;
					case 'OKDP':
						$value = $businessValue['OKDP'];
						break;
					case 'OKOPF':
						$value = $businessValue['OKOPF'];
						break;
					case 'OKFC':
						$value = $businessValue['OKFC'];
						break;
					case 'OKPO':
					case 'OKPO_CODE':
						$value = $businessValue['OKPO'];
						break;
					case 'MONEY_ACCOUNTS':
						$replaceNameFields = array(
							'ITEM_NAME'=>'B_NAME',
							'PRESENTATION'=>'B_ADDRESS_FULL',
							'POST_CODE'=>'B_INDEX',
							'COUNTRY'=>'B_COUNTRY',
							'REGION'=>'B_REGION',
							'STATE'=>'B_STATE',
							'SMALL_CITY'=>'B_TOWN',
							'CITY'=>'B_CITY',
							'STREET'=>'B_STREET',
							'HOUSE'=>'B_HOUSE',
							'BUILDING'=>'B_BUILDING',
							'FLAT'=>'B_FLAT',
						);
						$this->replaceNameFields($replacedFields, $replaceNameFields);
						$value = $this->externalizeArrayFields($replacedFields, $v);
						break;
				}
				if(!is_array($value))
					$this->externalizeField($value, $v);

				$result[$k] = $value;
			}
		}

		foreach ($availableFields as $k=>$v)
		{
			if($k <> 'REK_VALUES')
				continue;

			$value='';
			switch ($k)
			{
				case 'REK_VALUES':
					$value=array();
					foreach($v['FIELDS'] as $name=>$fieldInfo)
					{
						$valueRV='';
						switch($name)
						{
							case 'DELIVERY_ADDRESS':
								$valueRV = isset($result['ADDRESS']['PRESENTATION']) ? $result['ADDRESS']['PRESENTATION']:'';
								if($valueRV === '')
									$valueRV = $result[($this->isFiz($businessValue) ? 'REGISTRATION_ADDRESS':'UR_ADDRESS')]['PRESENTATION'];
								break;
						}
						$value[] = $this->externalizeRekvValue($name, $valueRV, $fieldInfo);
					}
					break;
			}
			$result[$k] = $value;
		}

		$result = $this->modifyTrim($result);

		return $result;
	}

	/**
	 * @param array $fields
	 * @return bool
	 */
	protected function isFiz(array $fields)
	{
		return ($fields["IS_FIZ"]=="Y");
	}

	/**
	 * @param $fields
	 * @param null $fieldsInfo
	 * @return array
	 */
	public function externalizeArrayFields($fields, $fieldsInfo, $key=null)
	{
		$result = array();
		foreach ($fieldsInfo['FIELDS'] as $name=>$fieldInfo)
		{
			if($fieldInfo['TYPE'] == 'array')
			{
				switch ($name)
				{
					case 'ADDRESS_FIELD':
					case 'CONTACT':
					case 'REPRESENTATIVE':
						$value = $this->externalizeArrayFields($fields, $fieldInfo);
						break;
					default:
						$value = $this->externalizeArrayFields($fields, $fieldInfo, $name);
				}
			}
			else
			{
				switch ($name)
				{
					case 'RELATION':
					case 'TYPE':
						$value = DocumentBase::getLangByCodeField($key);
						break;
					case 'VALUE':
						$value = $fields[$key];
						break;
					default:
						$value = $fields[$name];
				}
			}
			if(!is_array($value))
				$this->externalizeField($value, $fieldInfo);

			$result[$name] = $value;
		}
		return $result;
	}

	/**
	 * @param array $original
	 * @param array $replace
	 */
	private function replaceNameFields(array &$original, array $replace)
	{
		foreach ($original as $k=>$v)
		{
			$replaceNameField = array_search($k, $replace);
			if($replaceNameField !== false)
			{
				$original[$replaceNameField] = $v;
				unset($original[$k]);
			}
		}
	}

	/**
	 * @param array $fields
	 * @return string
	 */
	private function getXmlId(array $fields)
	{
		if(strlen($fields['XML_ID'])>0)
		{
			$result = $fields['XML_ID'];
		}
		else
		{
			$result = htmlspecialcharsbx(substr($fields["ID"]."#".$fields["LOGIN"]."#".$fields["LAST_NAME"]." ".$fields["NAME"]." ".$fields["SECOND_NAME"], 0, 40));
			\Bitrix\Sale\Exchange\Entity\UserImportBase::updateEmptyXmlId($fields["ID"], $result);
		}
		return $result;
	}
}