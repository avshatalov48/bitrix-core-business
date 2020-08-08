<?php


namespace Bitrix\Sale\Controller;



use Bitrix\Main\Engine\Response\DataType\Page;
use Bitrix\Main\Error;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\UI\PageNavigation;
use Bitrix\Sale\Internals\Input\File;
use Bitrix\Sale\Internals\Input\Manager;
use Bitrix\Sale\Internals\OrderPropsValueTable;
use Bitrix\Sale\Rest\Entity\RelationType;
use Bitrix\Sale\Result;

Loc::loadMessages($_SERVER["DOCUMENT_ROOT"].'/bitrix/modules/sale/admin/order_props_edit.php');

class Property extends Controller
{
	protected $property;
	protected $dbProperty;
	protected $propertySettings;
	protected $errors = [];

	protected $personTypeId;
	protected $siteId;

	//region Actions
	public function getFieldsByTypeAction($type)
	{
		$r = new Result();

		if(!in_array($type, array_keys($this->getTypes()['ENUM'])))
		{
			$r->addError(new Error('type is out of range', 200850000008));
		}

		if(!$r->isSuccess())
		{
			$this->addErrors($r->getErrors());
			return null;
		}
		else
		{
			$entity = new \Bitrix\Sale\Rest\Entity\Property();
			return ['PROPERTY'=>$entity->prepareFieldInfos(
				$entity->getFieldsByType($type)
			)];
		}
	}

	public function updateAction($id, array $fields)
	{
		$r = $this->exists($id);
		if($r->isSuccess())
		{
			$fillFields = $this->get($id);
			$fields['ID'] = $fillFields['ID'];
			$fields['TYPE'] = $fillFields['TYPE'];
			$fields['PROPS_GROUP_ID'] = $fillFields['PROPS_GROUP_ID'];
			$fields['PERSON_TYPE_ID'] = $fillFields['PERSON_TYPE_ID'];

			if(isset($fields['SETTINGS']))
			{
				$fields = array_merge($fields, $fields['SETTINGS']);
				unset($fields['SETTINGS']);
			}

			$r = $this->checkFileds($fields);

			if($r->isSuccess())
			{
				$this->personTypeId = $fields['PERSON_TYPE_ID'];
				$this->property = $fields;

				$this->initializePropertySettings();
				if ($this->validateProperty())
				{
					$this->saveProperty();
				}

				foreach ($this->errors as $error)
				{
					$r->addError(new Error($error, 200850000003));
				}
			}
		}

		if(!$r->isSuccess())
		{
			$this->addErrors($r->getErrors());
			return null;
		}
		else
		{
			return ['PROPERTY'=>$this->get($this->property['ID'])];
		}
	}

	public function getAction($id)
	{
		$r = $this->exists($id);
		if($r->isSuccess())
		{
			return ['PROPERTY'=>$this->get($id)];
		}
		else
		{
			$this->addErrors($r->getErrors());
			return null;
		}
	}

	public function deleteAction($id)
	{
		$orderProps = new \CSaleOrderProps();

		$r = $this->exists($id);
		if($r->isSuccess())
		{
			if (!$orderProps->Delete($id))
			{
				if ($ex = self::getApplication()->GetException())
					$r->addError(new Error($ex->GetString(), $ex->GetId()));
				else
					$r->addError(new Error('delete property error', 200850000004));
			}
		}

		if($r->isSuccess())
		{
			return true;
		}
		else
		{
			$this->addErrors($r->getErrors());
			return null;
		}
	}

	public function listAction($select=[], $filter=[], $order=[], PageNavigation $pageNavigation)
	{
		$select = empty($select)? ['*']:$select;
		$order = empty($order)? ['ID'=>'ASC']:$order;

		$items = \Bitrix\Sale\Property::getList(
			[
				'select'=>$select,
				'filter'=>$filter,
				'order'=>$order,
				'offset' => $pageNavigation->getOffset(),
				'limit' => $pageNavigation->getLimit()
			]
		)->fetchAll();

		return new Page('PROPERTIES', $items, function() use ($filter)
		{
			return count(
				\Bitrix\Sale\Property::getList(['filter'=>$filter])->fetchAll()
			);
		});
	}

	public function addAction($fields)
	{
		$fields = self::prepareFields($fields);

		$r = $this->checkFileds($fields);

		if(!isset($fields['PERSON_TYPE_ID']) || trim($fields['PERSON_TYPE_ID'])=='')
			$r->addError(new Error('person type id is empty', 200850000005));

		if($r->isSuccess())
		{
			$this->personTypeId = $fields['PERSON_TYPE_ID'];

			$this->property = $fields;
			$this->initializePropertySettings();
			if ($this->validateProperty())
			{
				$this->saveProperty();
			}

			foreach ($this->errors as $error)
			{
				$r->addError(new Error($error, 200850000006));
			}
		}

		if(!$r->isSuccess())
		{
			$this->addErrors($r->getErrors());
			return null;
		}
		else
		{
			return ['PROPERTY'=>$this->get($this->property['ID'])];
		}
	}
	//endregion

	protected function checkFileds($fields)
	{
		$r = new Result();

		if(isset($fields['MULTIPLE']) && $fields['MULTIPLE'] == 'Y')
		{
			if(isset($fields['IS_FILTERED']) == false)
			{
				$r->addError(new Error('Require fields: isFiltered.' , 200850000009));
			}
			elseif($fields['IS_FILTERED'] <> 'N')
			{
				$r->addError(new Error('Allowed values: isFiltered - [N]', 200850000010));
			}
		}

		if ($fields['TYPE'] == \Bitrix\Sale\Rest\Entity\Property::PROPERTY_TYPE_LOCATION)
		{
			if(isset($fields['IS_LOCATION']) && $fields['IS_LOCATION'] == 'Y')
			{
				if(isset($fields['MULTIPLE']) == false)
				{
					$r->addError(new Error('Require fields: multiple.', 200850000011));
				}
				elseif ($fields['MULTIPLE'] <> 'N')
				{
					$r->addError(new Error('Allowed values: multiple - [N]', 200850000012));
				}
			}

			if(isset($fields['IS_LOCATION4TAX']) && $fields['IS_LOCATION4TAX'] == 'Y')
			{
				if(isset($fields['MULTIPLE']) == false)
				{
					$r->addError(new Error('Require fields: multiple.', 200850000013));
				}
				elseif ($fields['MULTIPLE'] <> 'N')
				{
					$r->addError(new Error('Allowed values: multiple - [N]', 200850000014));
				}
			}
		}

		if ($fields['TYPE'] == \Bitrix\Sale\Rest\Entity\Property::PROPERTY_TYPE_STRING)
		{
			if(isset($fields['IS_PROFILE_NAME']) && $fields['IS_PROFILE_NAME'] == 'Y')
			{
				if(isset($fields['REQUIRED']) == false)
				{
					$r->addError(new Error('Require fields: require.', 200850000015));
				}
				elseif ($fields['REQUIRED'] <> 'Y')
				{
					$r->addError(new Error('Allowed values: require - [Y]', 200850000016));
				}
			}
		}

		return $r;
	}

	public function getTypes()
	{
		$r =[];
		foreach(array_keys(Manager::getTypes()) as $type)
		{
			$fields = self::getCommonFields();

			$fields += Manager::getCommonSettings(['TYPE'=>$type], null );

			$fields['MULTIPLE']['DESCRIPTION'] = Loc::getMessage('MULTIPLE_DESCRIPTION');

			$fields['DEFAULT_VALUE']=[
				'REQUIRED'=>'N',
				'DESCRIPTION'=>null,
				'LABEL'=>Loc::getMessage('F_DEFAULT_VALUE')
			];

			$fields += $this->getInputSettings(['TYPE'=>$type]);

			if ($type === 'STRING')
			{
				$fields += $this->getStringSettings();
			}
			elseif ($type === 'LOCATION')
			{
				$fields += $this->getLocationSettings();
			}

			foreach ($fields as $code=>&$v)
			{
				foreach ($v as $name=>$value)
				{
					if(in_array($name, ['ONCHANGE','ONCLICK', 'OPTIONS']))
					{
						unset($v[$name]);
					}
				}
			}

			$r[$type] = $fields;
		}

		return ['ENUM'=>$r];
	}

	protected function exists($id)
	{
		$r = new Result();
		if($this->get($id)['ID']<=0)
			$r->addError(new Error('property is not exists', 200840400001));

		return $r;
	}

	protected function get($id)
	{
		return $fields = $this->loadProperty($id);
	}

	protected function getPropertyGroupOptions()
	{
		$groupOptions = [];
		$orderPropsGroup = new \CSaleOrderPropsGroup();

		$result = $orderPropsGroup->GetList(['NAME' => 'ASC'], ['PERSON_TYPE_ID' => $this->personTypeId]);
		while ($row = $result->Fetch())
		{
			$groupOptions[$row['ID']] = $row['NAME'];
		}

		return $groupOptions;
	}

	protected function getPersonType($personTypeId)
	{
		$personTypeList = \Bitrix\Sale\PersonType::load($this->siteId, $personTypeId);

		return isset($personTypeList[$personTypeId]) ? $personTypeList[$personTypeId] : null;
	}

	static protected function getCommonFields()
	{
		return [
			'ID' => [
				'TYPE' => 'NUMBER',
				'LABEL' => 'ID',
				'MIN' => 0,
				'STEP' => 1,
				'HIDDEN' => 'Y',
			],
			'PERSON_TYPE_ID' => [
				'TYPE' => 'NUMBER',
				'LABEL' => Loc::getMessage('SALE_PERS_TYPE'),
				'MIN' => 0,
				'STEP' => 1,
				'HIDDEN' => 'Y',
				'REQUIRED' => 'Y'
			],
			'PROPS_GROUP_ID' => [
				'TYPE' => 'ENUM',
				'LABEL' => Loc::getMessage('F_PROPS_GROUP_ID'),
			],
			'NAME' => [
				'TYPE' => 'STRING',
				'LABEL' => Loc::getMessage('F_NAME'),
				'MAXLENGTH' => 255,
				'REQUIRED' => 'Y'
			],
			'CODE' => [
				'TYPE' => 'STRING',
				'LABEL' => Loc::getMessage('F_CODE'),
				'MAXLENGTH' => 50
			],
			'ACTIVE' => [
				'TYPE' => 'Y/N' ,
				'LABEL' => Loc::getMessage('F_ACTIVE'),
				'VALUE' => 'Y'
			],
			'UTIL' => [
				'TYPE' => 'Y/N',
				'LABEL' => Loc::getMessage('F_UTIL')
			],
			'USER_PROPS' => [
				'TYPE' => 'Y/N',
				'LABEL' => Loc::getMessage('F_USER_PROPS')
			],
			'IS_FILTERED' => [
				'TYPE' => 'Y/N',
				'LABEL' => Loc::getMessage('F_IS_FILTERED'),
				'DESCRIPTION' => Loc::getMessage('MULTIPLE_DESCRIPTION')
			],
			'SORT' => [
				'TYPE' => 'NUMBER',
				'LABEL' => Loc::getMessage('F_SORT'),
				'MIN' => 0,
				'STEP' => 1,
				'VALUE' => 100
			],
			'DESCRIPTION' => [
				'TYPE' => 'STRING',
				'LABEL' => Loc::getMessage('F_DESCRIPTION'),
				'MULTILINE' => 'Y',
				'ROWS' => 3,
				'COLS' => 40
			]
		];
	}

	protected function getCommonSettings()
	{
		$personType = $this->getPersonType($this->personTypeId);
		$groupOptions = $this->getPropertyGroupOptions();

		$commonSettings = [
			'PERSON_TYPE_ID' => [
				'TYPE' => 'NUMBER',
				'LABEL' => Loc::getMessage('SALE_PERS_TYPE'),
				'MIN' => 0,
				'STEP' => 1,
				'HIDDEN' => 'Y',
				'REQUIRED' => 'Y',
				'RLABEL' => $personType['NAME']
			],
			'PROPS_GROUP_ID' => [
				'TYPE' => 'ENUM',
				'LABEL' => Loc::getMessage('F_PROPS_GROUP_ID'),
				'OPTIONS' => $groupOptions
			],
			'NAME' => [
				'TYPE' => 'STRING',
				'LABEL' => Loc::getMessage('F_NAME'),
				'MAXLENGTH' => 255,
				'REQUIRED' => 'Y'
			],
			'CODE' => [
				'TYPE' => 'STRING',
				'LABEL' => Loc::getMessage('F_CODE'),
				'MAXLENGTH' => 50
			],
			'ACTIVE' => [
				'TYPE' => 'Y/N' ,
				'LABEL' => Loc::getMessage('F_ACTIVE'),
				'VALUE' => 'Y'
			],
			'UTIL' => [
				'TYPE' => 'Y/N',
				'LABEL' => Loc::getMessage('F_UTIL')
			],
			'USER_PROPS' => [
				'TYPE' => 'Y/N',
				'LABEL' => Loc::getMessage('F_USER_PROPS')
			],
			'IS_FILTERED' => [
				'TYPE' => 'Y/N',
				'LABEL' => Loc::getMessage('F_IS_FILTERED'),
				'DESCRIPTION' => Loc::getMessage('MULTIPLE_DESCRIPTION')
			],
			'SORT' => [
				'TYPE' => 'NUMBER',
				'LABEL' => Loc::getMessage('F_SORT'),
				'MIN' => 0,
				'STEP' => 1,
				'VALUE' => 100
			],
			'DESCRIPTION' => [
				'TYPE' => 'STRING',
				'LABEL' => Loc::getMessage('F_DESCRIPTION'),
				'MULTILINE' => 'Y',
				'ROWS' => 3,
				'COLS' => 40
			],
			'XML_ID' => [
				'TYPE' => 'STRING',
				'LABEL' => 'XML_ID',
				'MAXLENGTH' => 255,
			],
		];

		if (!empty($this->property['ID']))
		{
			$commonSettings = array_merge(
				[
					'ID' => [
						'TYPE' => 'NUMBER',
						'LABEL' => 'ID',
						'MIN' => 0,
						'STEP' => 1,
						'HIDDEN' => 'Y',
						'RLABEL' => $this->property['ID']
					]
				],
				$commonSettings
			);
		}

		$commonSettings += Manager::getCommonSettings($this->property, null );

		if (!empty($commonSettings['TYPE']['OPTIONS']))
		{
			foreach ($commonSettings['TYPE']['OPTIONS'] as $key => $option)
			{
				$commonSettings['TYPE']['OPTIONS'][$key] = mb_substr($option, 0, mb_strpos($option, '[') - 1);
			}
		}

		/*if (!$this->checkMultipleField($this->property))
		{
			$commonSettings['MULTIPLE']['DISABLED'] = 'Y';
			$commonSettings['MULTIPLE']['NO_DISPLAY'] = 'Y';
			unset($commonSettings['IS_FILTERED']['DESCRIPTION']);
		}*/

		$commonSettings['MULTIPLE']['DESCRIPTION'] = Loc::getMessage('MULTIPLE_DESCRIPTION');
		unset($commonSettings['VALUE']);

		$commonSettings['DEFAULT_VALUE'] = array(
				'REQUIRED' => 'N',
				'DESCRIPTION' => null,
				'VALUE' => $this->property['DEFAULT_VALUE'],
				'LABEL' => Loc::getMessage('F_DEFAULT_VALUE'),
			) + $this->property;

		if ($this->property['TYPE'] === 'ENUM')
		{
			$defaultOptions = $this->property['MULTIPLE'] === 'Y'
				? []
				: ['' => Loc::getMessage('NO_DEFAULT_VALUE')];

			if (!empty($this->property['VARIANTS']))
			{
				foreach ($this->property['VARIANTS'] as $row)
				{
					$defaultOptions[$row['VALUE']] = $row['NAME'];
				}
			}

			$commonSettings['DEFAULT_VALUE']['OPTIONS'] = $defaultOptions;
		}
		elseif ($this->property['TYPE'] === 'LOCATION')
		{
			if ($this->property['IS_LOCATION'] === 'Y' || $this->property['IS_LOCATION4TAX'] === 'Y')
			{
				unset($commonSettings['MULTIPLE']);
			}
		}

		return $commonSettings;
	}

	protected function getInputSettings($property)
	{
		return Manager::getSettings($property,null);
	}

	protected function getStringSettings()
	{
		return array(
			'IS_PROFILE_NAME' => [
				'TYPE' => 'Y/N',
				'LABEL' => Loc::getMessage('F_IS_PROFILE_NAME'),
				'DESCRIPTION' => Loc::getMessage('F_IS_PROFILE_NAME_DESCR')
			],
			'IS_PAYER' => [
				'TYPE' => 'Y/N',
				'LABEL' => Loc::getMessage('F_IS_PAYER'),
				'DESCRIPTION' => Loc::getMessage('F_IS_PAYER_DESCR')
			],
			'IS_EMAIL' => [
				'TYPE' => 'Y/N',
				'LABEL' => Loc::getMessage('F_IS_EMAIL'),
				'DESCRIPTION' => Loc::getMessage('F_IS_EMAIL_DESCR')
			],
			'IS_PHONE' => [
				'TYPE' => 'Y/N',
				'LABEL' => Loc::getMessage('F_IS_PHONE'),
				'DESCRIPTION' => Loc::getMessage('F_IS_PHONE_DESCR')
			],
			'IS_ZIP' => [
				'TYPE' => 'Y/N',
				'LABEL' => Loc::getMessage('F_IS_ZIP'),
				'DESCRIPTION' => Loc::getMessage('F_IS_ZIP_DESCR')
			],
			'IS_ADDRESS' => [
				'TYPE' => 'Y/N',
				'LABEL' => Loc::getMessage('F_IS_ADDRESS'),
				'DESCRIPTION' => Loc::getMessage('F_IS_ADDRESS_DESCR')
			],
		);
	}

	protected function getLocationSettings()
	{
		$orderProps = new \CSaleOrderProps();

		$locationOptions = ['' => Loc::getMessage('NULL_ANOTHER_LOCATION')];

		$result = $orderProps->GetList([],
			[
				'PERSON_TYPE_ID' => $this->personTypeId,
				'TYPE' => 'STRING',
				'ACTIVE' => 'Y'
			],
			false, false, ['ID', 'NAME']
		);
		while ($row = $result->Fetch())
		{
			$locationOptions[$row['ID']] = $row['NAME'];
		}

		return [
			'IS_LOCATION' => [
				'TYPE' => 'Y/N' ,
				'LABEL' => Loc::getMessage('F_IS_LOCATION'),
				'DESCRIPTION' => Loc::getMessage('F_IS_LOCATION_DESCR'),
				'ONCLICK' => null
			],
			'INPUT_FIELD_LOCATION' => [
				'TYPE' => 'ENUM',
				'LABEL' => Loc::getMessage('F_ANOTHER_LOCATION'),
				'DESCRIPTION' => Loc::getMessage('F_INPUT_FIELD_DESCR'),
				'OPTIONS' => $locationOptions,
				'VALUE' => 0
			],
			'IS_LOCATION4TAX' => [
				'TYPE' => 'Y/N',
				'LABEL' => Loc::getMessage('F_IS_LOCATION4TAX'),
				'DESCRIPTION' => Loc::getMessage('F_IS_LOCATION4TAX_DESCR'),
				'ONCLICK' => null
			],
		];
	}

	protected function getVariantSettings()
	{
		return [
			'VALUE' => [
				'TYPE' => 'STRING', 'LABEL' => Loc::getMessage('SALE_VARIANTS_CODE'), 'SIZE' => '5', 'MAXLENGTH' => 255, 'REQUIRED' => 'Y'
			],
			'NAME' => [
				'TYPE' => 'STRING', 'LABEL' => Loc::getMessage('SALE_VARIANTS_NAME'), 'SIZE' => '20', 'MAXLENGTH' => 255, 'REQUIRED' => 'Y'
			],
			'SORT' => [
				'TYPE' => 'NUMBER', 'LABEL' => Loc::getMessage('SALE_VARIANTS_SORT'), 'MIN' => 0, 'STEP' => 1, 'VALUE' => 100
			],
			'DESCRIPTION' => [
				'TYPE' => 'STRING', 'LABEL' => Loc::getMessage('SALE_VARIANTS_DESCR'), 'SIZE' => '30', 'MAXLENGTH' => 255
			],
			'ID' => [
				'TYPE' => 'NUMBER', 'MIN' => 0, 'STEP' => 1, 'HIDDEN' => 'Y'
			],
			'XML_ID' => [
				'TYPE' => 'STRING', 'LABEL' => 'XML_ID', 'SIZE' => '20', 'MAXLENGTH' => 255
			],
		];
	}

	protected function getRelationSettings()
	{
		$paymentOptions = array();
		$result = \CSalePaySystem::GetList(
			array("SORT"=>"ASC", "NAME"=>"ASC"),
			array("ACTIVE" => "Y"),
			false,
			false,
			array("ID", "NAME", "ACTIVE", "SORT", "LID")
		);
		while ($row = $result->Fetch())
			$paymentOptions[$row['ID']] = $row['NAME'] . ($row['LID'] ? " ({$row['LID']}) " : ' ') . "[{$row['ID']}]";

		// delivery system options
		$deliveryOptions = array();

		foreach(\Bitrix\Sale\Delivery\Services\Manager::getActiveList(true) as $deliveryId => $deliveryFields)
		{
			$name = $deliveryFields["NAME"]." [".$deliveryId."]";
			$sites = \Bitrix\Sale\Delivery\Restrictions\Manager::getSitesByServiceId($deliveryId);

			if(!empty($sites))
				$name .= " (".implode(", ", $sites).")";

			$deliveryOptions[$deliveryId] = $name;
		}

		return [
			'P' => ['TYPE' => 'ENUM', 'LABEL' => Loc::getMessage('SALE_PROPERTY_PAYSYSTEM'), 'OPTIONS' => $paymentOptions , 'MULTIPLE' => 'Y', 'SIZE' => '5'],
			'D' => ['TYPE' => 'ENUM', 'LABEL' => Loc::getMessage('SALE_PROPERTY_DELIVERY' ), 'OPTIONS' => $deliveryOptions, 'MULTIPLE' => 'Y', 'SIZE' => '5'],
		];
	}

	protected function checkMultipleField($property)
	{
		return true;
	}

	protected function modifyInputSettingsByType(&$propertySettings)
	{
		if ($this->property['MULTIPLE'] === 'Y')
		{
			$propertySettings['IS_FILTERED']['DISABLED'] = 'Y';
		}

		if ($this->property['TYPE'] === 'STRING')
		{
			$propertySettings += $this->getStringSettings();
		}
		elseif ($this->property['TYPE'] === 'LOCATION')
		{
			$propertySettings += $this->getLocationSettings();

			if ($this->property['IS_LOCATION'] !== 'Y' || $this->property['MULTIPLE'] === 'Y')
			{
				unset($propertySettings['INPUT_FIELD_LOCATION']);
			}
		}
	}

	protected function initializePropertySettings()
	{
		if ($this->propertySettings === null)
		{
			$this->propertySettings = $this->getCommonSettings();
			$this->propertySettings += $this->getInputSettings($this->property);

			$this->modifyInputSettingsByType($this->propertySettings);
		}
	}

	protected function validateFields()
	{
		foreach ($this->propertySettings as $name => $input)
		{
			if ($error = Manager::getError($input, $this->property[$name]))
			{
				if ($input['MULTIPLE'] && $input['MULTIPLE'] === 'Y')
				{
					$errorString = '';

					foreach ($error as $k => $v)
					{
						$errorString .= ' '.(++$k).': '.implode(', ', $v).';';
					}

					$this->errors[] = $input['LABEL'].$errorString;
				}
				else
				{
					$this->errors[] = $input['LABEL'].': '.implode(', ', $error);
				}
			}
		}
	}

	protected function validateVariants()
	{
		if (!empty($this->property))
		{
			$index = 0;
			$variantSettings = $this->getVariantSettings();

			foreach ($this->property['VARIANTS'] as $row)
			{
				++$index;

				if (isset($row['DELETE']) && $row['DELETE'] === 'Y')
				{
					unset($this->propertySettings['DEFAULT_VALUE']['OPTIONS'][$row['VALUE']]);
				}
				else
				{
					$hasError = false;

					foreach ($variantSettings as $name => $input)
					{
						if ($error = Manager::getError($input, $row[$name]))
						{
							$this->errors[] = Loc::getMessage('INPUT_ENUM')." $index: ".$input['LABEL'].': '.implode(', ', $error);
							$hasError = true;
						}
					}

					if ($hasError)
					{
						unset($this->propertySettings['DEFAULT_VALUE']['OPTIONS'][$row['VALUE']]);
					}
				}
			}
		}
	}

	protected function validateRelations()
	{
		$hasRelations = false;
		$relationsSettings = $this->getRelationSettings();

		foreach ($relationsSettings as $name => $input)
		{
			if (($value = $this->property['RELATIONS'][$name]) && $value != array(''))
			{
				$hasRelations = true;
				if ($error = Manager::getError($input, $value))
					$errors [] = $input['LABEL'].': '.implode(', ', $error);
			}
			else
			{
				$relations[$name] = array();
			}
		}

		if ($hasRelations)
		{
			if ($this->property['IS_LOCATION4TAX'] === 'Y')
			{
				$this->errors[] = Loc::getMessage('ERROR_LOCATION4TAX_RELATION_NOT_ALLOWED');
			}

			if ($this->property['IS_EMAIL'] === 'Y')
			{
				$this->errors[] = Loc::getMessage('ERROR_EMAIL_RELATION_NOT_ALLOWED');
			}

			if ($this->property['IS_PROFILE_NAME'] === 'Y')
			{
				$this->errors[] = Loc::getMessage('ERROR_PROFILE_NAME_RELATION_NOT_ALLOWED');
			}
		}
	}

	protected function validateProperty()
	{
		$this->initializePropertySettings();
		$this->validateFields();

		if ($this->property['TYPE'] === 'ENUM')
		{
			$this->validateVariants();
		}

		$this->validateRelations();

		return !$this->hasErrors();
	}

	protected function hasErrors()
	{
		return !empty($this->errors);
	}

	protected function saveRelations()
	{
		$orderProps = new \CSaleOrderProps();
		$relationsSettings = $this->getRelationSettings();

		foreach ($relationsSettings as $name => $input)
		{
			$orderProps->UpdateOrderPropsRelations( $this->property['ID'], $this->property['RELATIONS'][$name], $name);
		}
	}

	protected function saveProperty()
	{
		if ($this->property['TYPE'] === 'FILE')
		{
			$savedFiles = $this->saveFiles($this->property);
		}
		else
		{
			$savedFiles = [];
		}

		$propertiesToSave = [];

		foreach ($this->propertySettings as $name => $input)
		{
			$inputValue = Manager::getValue($input, $this->property[$name]);

			if ($name === 'DEFAULT_VALUE' || $inputValue !== null)
			{
				$propertiesToSave[$name] = $inputValue;
			}
		}

		$inputSettings = $this->getInputSettings($this->property);
		$propertiesToSave['SETTINGS'] = array_intersect_key($propertiesToSave, $inputSettings);
		$propertiesToSave = array_diff_key($propertiesToSave, $propertiesToSave['SETTINGS']);

		if (!empty($this->property['ID']))
		{
			$this->initializeDbProperty($this->property['ID']);
		}

		if (!empty($this->property['ID']))
		{
			$this->updateProperty($propertiesToSave);
		}
		else
		{
			$this->property['ID'] = $this->addProperty($propertiesToSave);
		}

		$this->cleanUpFiles($savedFiles);

		if (!$this->hasErrors())
		{
			$this->saveVariants();
			$this->saveRelations();
		}
	}

	protected function saveFiles(&$property)
	{
		$savedFiles = array();

		$files = File::asMultiple($property['DEFAULT_VALUE']);
		foreach ($files as $i => $file)
		{
			if (File::isDeletedSingle($file))
			{
				unset($files[$i]);
			}
			else
			{
				if (
					File::isUploadedSingle($file)
					&& ($fileId = \CFile::SaveFile(array('MODULE_ID' => 'sale') + $file, 'sale/order/properties/default'))
					&& is_numeric($fileId)
				)
				{
					$file = $fileId;
					$savedFiles[] = $fileId;
				}

				$files[$i] = File::loadInfoSingle($file);
			}
		}

		$property['DEFAULT_VALUE'] = $files;

		return $savedFiles;
	}

	protected function initializeDbProperty($propertyId)
	{
		if ($this->dbProperty === null)
		{
			$this->dbProperty = $this->loadProperty($propertyId);
		}
	}

	protected function loadProperty($propertyId)
	{
		if (empty($propertyId))
		{
			return [];
		}

		$property = \Bitrix\Sale\Internals\OrderPropsTable::getRow([
			'filter' => [
				'=ID' => $propertyId
			]
		]);
		if (!empty($property))
		{
			$property += $property['SETTINGS'];
			$property = $this->modifyDataDependedByType($property);
			$property = $this->modifyRelationsDataDependedByType($property);
		}

		return $property;
	}

	protected function modifyDataDependedByType($property)
	{
		$propsVariant = new \CSaleOrderPropsVariant();
		if (!empty($property))
		{
			switch ($property['TYPE'])
			{
				case 'ENUM':
					$variants = [];

					$result = $propsVariant->GetList([], [
						'ORDER_PROPS_ID' => $property['ID']
					]);
					while ($row = $result->Fetch())
					{
						$variants[] = $row;
					}

					$property['VARIANTS'] = $variants;
					break;
				case 'FILE':
					$property['DEFAULT_VALUE'] = File::loadInfo($property['DEFAULT_VALUE']);
					break;
			}
		}

		return $property;
	}

	protected function modifyRelationsDataDependedByType($property)
	{
		$orderProps = new \CSaleOrderProps();
		$result = $orderProps->GetOrderPropsRelations(['PROPERTY_ID' => $property['ID']]);
		while ($row = $result->Fetch())
		{
			if(RelationType::isDefined(RelationType::resolveID($row['ENTITY_TYPE'])))
			{
				$property['RELATIONS'][$row['ENTITY_TYPE']][] = $row['ENTITY_ID'];
			}
		}

		return $property;
	}

	protected function updateProperty($propertiesToSave)
	{
		$update = \Bitrix\Sale\Internals\OrderPropsTable::update(
			$this->property['ID'],
			array_diff_key($propertiesToSave, array('ID' => 1))
		);
		if ($update->isSuccess())
		{
			$propertyCode = $propertiesToSave['CODE'] ?: false;

			$result = OrderPropsValueTable::getList(['filter'=>[
				'ORDER_PROPS_ID' => $this->property['ID'],
				'!CODE' => $propertyCode,
			]]);
			while ($row = $result->Fetch())
			{
				OrderPropsValueTable::update($row['ID'], ['CODE' => $propertyCode]);
			}
		}
		else
		{
			foreach ($update->getErrorMessages() as $errorMessage)
			{
				$this->errors[] = $errorMessage;
			}
		}
	}

	protected function addProperty($propertiesToSave)
	{
		$propertyId = null;

		if(!isset($propertiesToSave['XML_ID']) && $propertiesToSave['XML_ID'] == '')
		{
			$propertiesToSave['XML_ID'] = \Bitrix\Sale\Internals\OrderPropsTable::generateXmlId();
		}

		$propertiesToSave['ENTITY_REGISTRY_TYPE'] = \Bitrix\Sale\Registry::REGISTRY_TYPE_ORDER;
		$addResult = \Bitrix\Sale\Internals\OrderPropsTable::add($propertiesToSave);
		if ($addResult->isSuccess())
		{
			$propertyId = $addResult->getId();
		}
		else
		{
			foreach ($addResult->getErrorMessages() as $errorMessage)
			{
				$this->errors[] = $errorMessage;
			}
		}

		return $propertyId;
	}

	protected function cleanUpFiles($savedFiles)
	{
		$filesToDelete = [];

		if ($this->hasErrors())
		{
			if (!empty($savedFiles))
			{
				$filesToDelete = $savedFiles;
			}
		}
		else
		{
			if (!empty($this->dbProperty) && $this->dbProperty['TYPE'] === 'FILE')
			{
				$filesToDelete = File::asMultiple(File::getValue(
					$this->dbProperty, $this->dbProperty['DEFAULT_VALUE']
				));

				if (!empty($savedFiles))
				{
					$filesToDelete = array_diff(
						$filesToDelete,
						File::asMultiple(File::getValue($this->property, $this->property['DEFAULT_VALUE']))
					);
				}
			}
		}

		foreach ($filesToDelete as $fileId)
		{
			if (is_numeric($fileId))
			{
				\CFile::Delete($fileId);
			}
		}
	}

	protected function saveVariants()
	{
		$orderPropsVariant = new \CSaleOrderPropsVariant();

		if ($this->property['TYPE'] === 'ENUM')
		{
			$index = 0;
			$variantSettings = $this->getVariantSettings();

			foreach ($this->property['VARIANTS'] as $key => $row)
			{
				if (isset($row['DELETE']) && $row['DELETE'] === 'Y')
				{
					if ($row['ID'])
					{
						$orderPropsVariant->Delete($row['ID']);
					}

					unset($this->property['VARIANTS'][$key]);
				}
				else
				{
					++$index;
					$variantId = $row['ID'];
					$row = array_intersect_key($row, $variantSettings);

					if ($variantId)
					{
						unset($row['ID']);
						if (!$orderPropsVariant->Update($variantId, $row))
						{
							$this->errors[] = Loc::getMessage('ERROR_EDIT_VARIANT')." $index";
						}
					}
					else
					{
						$row['ORDER_PROPS_ID'] = $this->property['ID'];

						if ($variantId = $orderPropsVariant->Add($row))
						{
							$variants[$key]['ID'] = $variantId;
						}
						else
						{
							$this->errors[] = Loc::getMessage('ERROR_ADD_VARIANT')." $index";
						}
					}
				}
			}
		}
		// cleanup variants
		elseif (!empty($this->dbProperty) && $this->dbProperty['TYPE'] === 'ENUM')
		{
			\CSaleOrderPropsVariant::DeleteAll($this->dbProperty['ID']);
		}
	}

	static public function prepareFields(array $fields)
	{
		$fields['TYPE'] = mb_strtoupper($fields['TYPE']);

		return $fields;
	}

	protected function checkPermissionEntity($name)
	{
		if($name == 'getfieldssettingsbytype'
			|| $name == 'getfieldsbytype'
		){
			$r = $this->checkReadPermissionEntity();
		}
		else
		{
			$r = parent::checkPermissionEntity($name);
		}
		return $r;
	}
}