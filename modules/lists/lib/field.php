<?php
namespace Bitrix\Lists;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;
use Bitrix\Main\Text\HtmlFilter;
use Bitrix\Main\Type\RandomSequence;

Loc::loadMessages(__FILE__);

/**
 * Class work with fields - prepare and render field value, additional steps.
 * 
 * Class Field
 * @package Bitrix\Lists\UI
 */
class Field
{
	protected static $cache = array();
	protected static $separator = '<br>';
	protected static $renderForForm = false;

	/**
	 * Method renders the printing form field values.
	 *
	 * @param array $field Field structure in the CListField format.
	 * @return string
	 */
	public static function renderField(array $field)
	{
		$result = '';
		$controlSettings = !empty($field['CONTROL_SETTINGS']) ? $field['CONTROL_SETTINGS'] : array();

		if(!empty($field['DEFAULT']))
		{
			$field['VALUE'] = $field['DEFAULT_VALUE'];
			if(in_array($field['TYPE'], array('S:DiskFile')))
			{
				$renderMethod = 'renderCustomDefaultValue'.$field['PROPERTY_USER_TYPE']['USER_TYPE'];
				return self::$renderMethod($field);
			}
		}

		if(!empty($field['SEPARATOR']))
		{
			self::$separator = $field['SEPARATOR'];
		}

		if(self::$renderForForm && isset($field['CURRENT_VALUE']))
		{
			$field['VALUE'] = $field['CURRENT_VALUE'];
		}

		if(isset($field['PROPERTY_USER_TYPE']['USER_TYPE'])
			&& method_exists(__CLASS__, 'renderFieldByUserType'.$field['PROPERTY_USER_TYPE']['USER_TYPE']))
		{
			$renderMethod = 'renderFieldByUserType'.$field['PROPERTY_USER_TYPE']['USER_TYPE'];
			$result = self::$renderMethod($field);
		}
		elseif(isset($field['PROPERTY_USER_TYPE']['GetPublicViewHTMLMulty']))
		{
			$result = call_user_func_array(
				$field['PROPERTY_USER_TYPE']['GetPublicViewHTMLMulty'], array($field, $field, $controlSettings));
		}
		elseif(isset($field['PROPERTY_USER_TYPE']['GetPublicViewHTML']))
		{
			if($field['MULTIPLE'] === 'Y' && is_array($field['VALUE']))
			{
				$results = array();
				if(\CLists::isAssociativeArray($field['VALUE']))
				{
					$result = call_user_func_array(
						$field['PROPERTY_USER_TYPE']['GetPublicViewHTML'], array($field, $field, $controlSettings));
				}
				else
				{
					foreach($field['VALUE'] as $value)
					{
						$fieldParam = array('VALUE' => $value);
						$results[] = call_user_func_array($field['PROPERTY_USER_TYPE']['GetPublicViewHTML'],
							array($field, $fieldParam, $controlSettings));
					}
					$result = implode(self::$separator, $results);
				}
			}
			else
			{
				$result = call_user_func_array(
					$field['PROPERTY_USER_TYPE']['GetPublicViewHTML'], array($field, $field, $controlSettings));
			}
		}
		elseif($field['PROPERTY_TYPE'] != '')
		{
			$renderMethod = 'renderFieldByType'.$field['PROPERTY_TYPE'];
			if(method_exists(__CLASS__, $renderMethod))
			{
				$result = self::$renderMethod($field);
			}
		}
		elseif($field['TYPE'] != '')
		{
			$renderMethod = 'renderFieldByField'.str_replace('_', '', $field['TYPE']);
			if(method_exists(__CLASS__, $renderMethod))
			{
				$result = self::$renderMethod($field);
			}
			else
			{
				$result = self::renderDefaultField($field);
			}
		}

		return $result;
	}

	public static function prepareFieldDataForEditForm(array $field)
	{
		$result = array();

		self::$renderForForm = true;

		$field['SHOW'] = 'Y';
		if($field['ELEMENT_ID'] > 0 && !empty($field['SETTINGS']['SHOW_EDIT_FORM']))
			$field['SHOW'] = $field['SETTINGS']['SHOW_EDIT_FORM'];
		if(!$field['ELEMENT_ID'] && !empty($field['SETTINGS']['SHOW_ADD_FORM']))
			$field['SHOW'] = $field['SETTINGS']['SHOW_ADD_FORM'];

		$field['READ'] = 'N';
		if($field['ELEMENT_ID'] > 0 && !empty($field['SETTINGS']['EDIT_READ_ONLY_FIELD']))
			$field['READ'] = $field['SETTINGS']['EDIT_READ_ONLY_FIELD'];
		if(!$field['ELEMENT_ID'] && !empty($field['SETTINGS']['ADD_READ_ONLY_FIELD']))
			$field['READ'] = $field['SETTINGS']['ADD_READ_ONLY_FIELD'];

		if(isset($field['PROPERTY_USER_TYPE']['USER_TYPE']) && method_exists(
			__CLASS__, 'prepareEditFieldByUserType'.$field['PROPERTY_USER_TYPE']['USER_TYPE']))
		{
			$prepareEditMethod = 'prepareEditFieldByUserType'.$field['PROPERTY_USER_TYPE']['USER_TYPE'];
			$result = self::$prepareEditMethod($field);
		}
		elseif(isset($field['PROPERTY_USER_TYPE']['GetPublicEditHTMLMulty']) && $field['MULTIPLE'] == 'Y')
		{
			if(!is_array($field['VALUE']))
				$field['VALUE'] = array($field['VALUE']);
			$html = '';
			$isEmptyValue = true;
			foreach($field['VALUE'] as $key => $value)
			{
				if($field['READ'] == 'Y')
				{
					if(empty($value['VALUE']))
					{
						continue;
					}
					else
					{
						$isEmptyValue = false;
						$field['CURRENT_VALUE'] = $value['VALUE'];
						$html .= ' '.self::renderField($field);
					}
					$result['customHtml'] .= '<input type="hidden" name="'.
						$field['FIELD_ID'].'['.$key.'][VALUE]" value="'.HtmlFilter::encode($value['VALUE']).'">';
				}
			}
			if($field['READ'] == 'N')
			{
				$html .= call_user_func_array($field['PROPERTY_USER_TYPE']['GetPublicEditHTMLMulty'], array(
					$field, $field['VALUE'], array('VALUE' => $field['FIELD_ID'], 'DESCRIPTION' => '',
						'FORM_NAME' => 'form_'.$field['FORM_ID'], 'MODE' => 'FORM_FILL')));
			}
			else
			{
				if($isEmptyValue)
					$html .= Loc::getMessage('LISTS_FIELD_NOT_DATA');
			}
			$result['id'] = $field['FIELD_ID'];
			$result['name'] = $field['NAME'];
			$result['required'] = $field['IS_REQUIRED'] == 'Y' ? true : false;
			$result['type'] = 'custom';
			$result['value'] = $html;
			$result['show'] = $field['SHOW'];
		}
		elseif(isset($field['PROPERTY_USER_TYPE']['GetPublicEditHTML']))
		{
			$listTypeNotMultiple = array('S:DiskFile', 'S:ECrm');
			if(!is_array($field['VALUE']))
				$field['VALUE'] = array($field['VALUE']);

			if($field['MULTIPLE'] == 'Y' && !in_array($field['TYPE'], $listTypeNotMultiple))
			{
				$html = '<table id="tbl'.$field['FIELD_ID'].'">';
				$isEmptyValue = true;
				foreach($field['VALUE'] as $key => $value)
				{
					if($field['READ'] == 'Y')
					{
						if(empty($value['VALUE']))
						{
							continue;
						}
						else
						{
							$isEmptyValue = false;
							$field['CURRENT_VALUE'] = $value['VALUE'];
							$html .= '<tr><td>' . self::renderField($field).'</td></tr>';
						}
						$result['customHtml'] .= '<input type="hidden" name="'.$field['FIELD_ID'].
							'['.$key.'][VALUE]" value="'.HtmlFilter::encode($value['VALUE']).'">';
					}
					else
					{
						$html .= '<tr><td>'.call_user_func_array($field['PROPERTY_USER_TYPE']['GetPublicEditHTML'],
							array(
								$field,
								$value,
								array(
									'VALUE' => $field['FIELD_ID']."[".$key."][VALUE]",
									'DESCRIPTION' => '',
									'FORM_NAME' => 'form_'.$field['FORM_ID'],
									'MODE' => "FORM_FILL",
									'COPY' => $field['COPY_ID'] > 0,
								)
							)).'</td></tr>';
					}
				}
				if($field['READ'] == 'Y')
				{
					if($isEmptyValue)
						$html .= Loc::getMessage('LISTS_FIELD_NOT_DATA');
				}
				$html .= '</table>';
				if($field['READ'] == 'N')
				{
					$regExp = '/'.$field['FIELD_ID'].'\[(n)([0-9]*)\]/g';
					$html .= '<input type="button" value="'.Loc::getMessage("LISTS_FIELD_ADD_BUTTON").'"
							onclick="BX.Lists.addNewTableRow(\'tbl'.$field['FIELD_ID'].
						'\', 1, '.HtmlFilter::encode($regExp).', 2)">';
				}
			}
			else
			{
				$html = '';
				$isEmptyValue = true;
				foreach($field['VALUE'] as $key => $value)
				{
					if($field['READ'] == 'Y')
					{
						if(empty($value['VALUE']))
						{
							continue;
						}
						else
						{
							$isEmptyValue = false;
							$field['CURRENT_VALUE'] = $value['VALUE'];
							$html .= ' '.self::renderField($field);
						}
						if(!is_array($value['VALUE']))
						{
							$result['customHtml'] .= '<input type="hidden" name="'.$field['FIELD_ID'].
								'['.$key.'][VALUE]" value="'.HtmlFilter::encode($value['VALUE']).'">';
						}
					}
					else
					{
						if($field['ELEMENT_ID'] > 0 && $field['TYPE'] == 'S:DiskFile')
						{
							$html .= call_user_func_array($field['PROPERTY_USER_TYPE']['GetPublicViewHTML'],
								array($field, $value, array()));
						}
						$html .= call_user_func_array($field['PROPERTY_USER_TYPE']['GetPublicEditHTML'],
							array(
								$field,
								$value,
								array(
									'VALUE' => $field['FIELD_ID']."[".$key."][VALUE]",
									'DESCRIPTION' => '',
									'FORM_NAME' => 'form_'.$field['FORM_ID'],
									'MODE' => 'FORM_FILL',
									'COPY' => $field['COPY_ID'] > 0,
								),
							));
					}
					break;
				}
				if($field['READ'] == 'Y')
				{
					if($isEmptyValue)
						$html .= Loc::getMessage('LISTS_FIELD_NOT_DATA');
				}
			}
			$result['id'] = $field['FIELD_ID'];
			$result['name'] = $field['NAME'];
			$result['required'] = $field['IS_REQUIRED'] == 'Y' ? true: false;
			$result['type'] = 'custom';
			$result['value'] = $html;
			$result['show'] = $field['SHOW'];
		}
		elseif($field['PROPERTY_TYPE'] != '')
		{
			$prepareEditMethod = 'prepareEditFieldByType'.$field['PROPERTY_TYPE'];
			if(method_exists(__CLASS__, $prepareEditMethod))
			{
				$result = self::$prepareEditMethod($field);
			}
		}
		elseif($field['TYPE'] != '')
		{
			$prepareEditMethod = 'prepareEditFieldByField'.str_replace('_', '', $field['TYPE']);
			if(method_exists(__CLASS__, $prepareEditMethod))
			{
				$result = self::$prepareEditMethod($field);
			}
			else
			{
				$result = self::prepareEditDefaultField($field);
			}
		}

		return $result;
	}

	/**
	 * Method of preparing a data structure for the filter.
	 *
	 * @param array $field Field structure in the CListField format.
	 * @return array
	 */
	public static function prepareFieldDataForFilter(array $field)
	{
		$customEntityType = array('employee', 'ECrm');
		if($field['TYPE'] == 'SORT' || $field['TYPE'] == 'N')
		{
			$result = array(
				'id' => $field['FIELD_ID'],
				'name' => $field['NAME'],
				'type' => 'number',
				'filterable' => ''
			);
		}
		elseif($field['TYPE'] == 'ACTIVE_FROM' || $field['TYPE'] == 'ACTIVE_TO')
		{
			$result = array(
				'id' => 'DATE_'.$field['FIELD_ID'],
				'name' => $field['NAME'],
				'type' => 'date',
				'filterable' => '',
				'dateFilter' => true
			);
		}
		elseif($field['TYPE'] == 'DATE_CREATE' || $field['TYPE'] == 'TIMESTAMP_X')
		{
			$result = array(
				'id' => $field['FIELD_ID'],
				'name' => $field['NAME'],
				'type' => 'date',
				'filterable' => '',
				'dateFilter' => true
			);
		}
		elseif($field['TYPE'] == 'CREATED_BY' || $field['TYPE'] == 'MODIFIED_BY')
		{
			$result = array(
				'id' => $field['FIELD_ID'],
				'name' => $field['NAME'],
				'type' => 'custom_entity',
				'filterable' => '',
			);
		}
		elseif($field['TYPE'] == 'L')
		{
			$items = array();
			$queryObject = \CIBlockProperty::getPropertyEnum($field['ID']);
			while($queryResult = $queryObject->fetch())
				$items[$queryResult['ID']] = $queryResult['VALUE'];

			$result = array(
				'id' => $field['FIELD_ID'],
				'name' => $field['NAME'],
				'type' => 'list',
				'items' => $items,
				'params' => array('multiple' => 'Y'),
				'filterable' => ''
			);
		}
		elseif($field['TYPE'] == 'E')
		{
			$result = array(
				'id' => $field['FIELD_ID'],
				'name' => $field['NAME'],
				'type' => 'custom_entity',
				'filterable' => '',
				'customFilter' => array('Bitrix\Iblock\Helpers\Filter\Property', 'addFilter')
			);
		}
		elseif($field['TYPE'] == 'E:EList')
		{
			$items = array();
			$queryObject = \CIBlockElement::getList(
				array('SORT' => 'ASC'),
				array('IBLOCK_ID' => $field['LINK_IBLOCK_ID']),
				false,
				false,
				array('ID', 'NAME')
			);
			while($queryResult = $queryObject->fetch())
				$items[$queryResult['ID']] = $queryResult['NAME'];

			$result = array(
				'id' => $field['FIELD_ID'],
				'name' => $field['NAME'],
				'type' => 'list',
				'items' => $items,
				'params' => array('multiple' => 'Y'),
				'filterable' => ''
			);
		}
		elseif($field['TYPE'] == 'G')
		{
			$items = array();
			$queryObject = \CIBlockSection::getList(
				array('left_margin' => 'asc'),
				array('IBLOCK_ID' => $field['LINK_IBLOCK_ID'])
			);
			while($queryResult = $queryObject->fetch())
				$items[$queryResult['ID']] = str_repeat('. ', $queryResult['DEPTH_LEVEL'] - 1).$queryResult['NAME'];

			$result = array(
				'id' => $field['FIELD_ID'],
				'name' => $field['NAME'],
				'type' => 'list',
				'items' => $items,
				'params' => array('multiple' => 'Y'),
				'filterable' => ''
			);
		}
		elseif(is_array($field['PROPERTY_USER_TYPE']) && !empty($field['PROPERTY_USER_TYPE']['USER_TYPE']))
		{
			$type = $field['PROPERTY_USER_TYPE']['USER_TYPE'];
			if($type == 'Date')
			{
				$result = array(
					'id' => $field['FIELD_ID'],
					'name' => $field['NAME'],
					'type' => 'date',
					'filterable' => '',
					'dateFilter' => true
				);
			}
			elseif($type == 'DateTime')
			{
				$result = array(
					'id' => $field['FIELD_ID'],
					'name' => $field['NAME'],
					'type' => 'date',
					'time' => true,
					'filterable' => '',
					'dateFilter' => true
				);
			}
			elseif($type == 'Sequence')
			{
				$result = array(
					'id' => $field['FIELD_ID'],
					'name' => $field['NAME'],
					'type' => 'number',
					'filterable' => ''
				);
			}
			elseif(in_array($type, $customEntityType))
			{
				$result = array(
					'id' => $field['FIELD_ID'],
					'name' => $field['NAME'],
					'type' => 'custom_entity',
					'filterable' => '',
				);
			}
			else
			{
				if(array_key_exists('GetPublicFilterHTML', $field['PROPERTY_USER_TYPE']))
				{
					$result = array(
						'id' => $field['FIELD_ID'],
						'name' => $field['NAME'],
						'type' => 'custom',
						'enable_settings' => false,
						'value' => call_user_func_array(
							$field['PROPERTY_USER_TYPE']['GetPublicFilterHTML'],
							array(
								$field,
								array(
									'VALUE' => $field['FIELD_ID'],
									'FORM_NAME'=>'filter_'.$field['GRID_ID'],
									'GRID_ID' => $field['GRID_ID']
								)
							)
						),
						'filterable' => ''
					);
				}
				else
				{
					$listLikeProperty = array('S:HTML');
					$result = array(
						'id' => $field['FIELD_ID'],
						'name' => $field['NAME'],
						'filterable' => in_array($field['TYPE'], $listLikeProperty) ? '?' : ''
					);
				}
			}
			if(array_key_exists('AddFilterFields', $field['PROPERTY_USER_TYPE']))
				$result['customFilter'] = $field['PROPERTY_USER_TYPE']['AddFilterFields'];
		}
		else
		{
			$listLikeField = array('NAME', 'DETAIL_TEXT', 'PREVIEW_TEXT', 'S');
			$result = array(
				'id' => $field['FIELD_ID'],
				'name' => $field['NAME'],
				'filterable' => in_array($field['TYPE'], $listLikeField) ? '?' : ''
			);
			if($field['FIELD_ID'] == 'NAME')
			{
				$result['default'] = true;
			}
		}

		return $result;
	}

	protected static function renderFieldByTypeS(array $field)
	{
		if($field['MULTIPLE'] == 'Y' && is_array($field['VALUE']))
		{
			$results = array();
			foreach($field['VALUE'] as $value)
			{
				$results[] = nl2br(HtmlFilter::encode($value));
			}
			$result = implode(self::$separator, $results);
		}
		else
		{
			$result = nl2br(HtmlFilter::encode($field['VALUE']));
		}
		return $result;
	}

	protected static function renderFieldByTypeN(array $field)
	{
		if(empty($field['VALUE']))
			return '';

		if($field['MULTIPLE'] == 'Y' && is_array($field['VALUE']))
		{
			$results = array();
			foreach($field['VALUE'] as $value)
			{
				$results[] = (float)$value;
			}
			$result = implode(self::$separator, $results);
		}
		else
		{
			$result = (float)$field['VALUE'];
		}
		return $result;
	}

	protected static function renderFieldByTypeL(array $field)
	{
		if(!empty(self::$cache[$field['ID']]))
		{
			$items = self::$cache[$field['ID']];
		}
		else
		{
			$items = array();
			if(empty($field['DEFAULT']))
				$items[] = Loc::getMessage('LISTS_FIELD_NO_VALUE');
			$listElements = \CIBlockProperty::getPropertyEnum($field['ID']);
			while($listElement = $listElements->fetch())
			{
				if(!empty($field['DEFAULT']))
				{
					if($listElement['DEF'] == 'Y')
					{
						$items[$listElement['ID']] = HtmlFilter::encode($listElement['VALUE']);
					}
				}
				else
				{
					$items[$listElement['ID']] = HtmlFilter::encode($listElement['VALUE']);
				}
			}

			self::$cache[$field['ID']] = $items;
		}

		if (is_array($field['VALUE']))
		{
			foreach ($items as $itemKey => $itemValue)
			{
				if (!in_array($itemKey, $field['VALUE']))
				{
					unset($items[$itemKey]);
				}
			}
			$result = implode(self::$separator, $items);
		}
		else
		{
			$result = $items[$field['VALUE']];
		}

		return $result;
	}

	protected static function renderFieldByTypeF(array $field)
	{
		if((empty($field['VALUE']) || !empty($field['DEFAULT'])) && !self::$renderForForm)
			return '';

		$iblockId = !empty($field['IBLOCK_ID']) ? intval($field['IBLOCK_ID']) : 0;
		$sectionId = !empty($field['SECTION_ID']) ? intval($field['SECTION_ID']) : 0;
		$elementId = !empty($field['ELEMENT_ID']) ? intval($field['ELEMENT_ID']) : 0;
		$fieldId = !empty($field['FIELD_ID']) ? $field['FIELD_ID'] : '';
		$socnetGroupId = !empty($field['SOCNET_GROUP_ID']) ? intval($field['SOCNET_GROUP_ID']) : 0;
		$urlTemplate = !empty($field['LIST_FILE_URL']) ? $field['LIST_FILE_URL'] : '';
		$downloadUrl = !empty($field['DOWNLOAD_FILE_URL']) ? $field['DOWNLOAD_FILE_URL'] : '';

		$params = array(
			'max_size' => 2024000,
			'max_width' => 100,
			'max_height' => 100,
			'url_template' => $urlTemplate,
			'download_url' => $downloadUrl,
			'download_text' => Loc::getMessage('LISTS_FIELD_FILE_DOWNLOAD'),
			'show_input' => false
		);
		if(!empty($field['READ']) && $field['READ'] == 'N')
			$params['show_input'] = true;

		if($field['MULTIPLE'] == 'Y' && is_array($field['VALUE']))
		{
			$results = array();
			foreach($field['VALUE'] as $key => $value)
			{
				$file = new \CListFile($iblockId, $sectionId, $elementId, $fieldId,
					is_array($value) && isset($value['VALUE']) ? $value['VALUE'] : $value);
				$file->setSocnetGroup($socnetGroupId);
				$fieldControlId = $field['TYPE'] == 'F' && self::$renderForForm ?
					$fieldId.'['.$key.'][VALUE]' : $fieldId;
				$fileControl = new \CListFileControl($file, $fieldControlId);
				$results[] = $fileControl->getHTML($params);
			}
			$result = implode(self::$separator, $results);
		}
		else
		{
			if(is_array($field['VALUE']))
			{
				$results = array();
				foreach($field['VALUE'] as $key => $value)
				{
					$file = new \CListFile($iblockId, $sectionId, $elementId, $fieldId,
						is_array($value) && isset($value['VALUE']) ? $value['VALUE'] : $value);
					$file->setSocnetGroup($socnetGroupId);
					$fieldControlId = $field['TYPE'] == 'F' && self::$renderForForm ?
						$fieldId.'['.$key.'][VALUE]' : $fieldId;
					$fileControl = new \CListFileControl($file, $fieldControlId);
					$results[] = $fileControl->getHTML($params);
				}
				$result = implode(self::$separator, $results);
			}
			else
			{
				$file = new \CListFile($iblockId, $sectionId, $elementId, $fieldId, $field['VALUE']);
				$file->setSocnetGroup($socnetGroupId);
				$fileControl = new \CListFileControl($file, $fieldId);
				$result = $fileControl->getHTML($params);
			}
		}

		return $result;
	}

	protected static function renderFieldByTypeE(array $field)
	{
		return self::getLinkToElement($field);
	}

	protected static function renderFieldByTypeG(array $field)
	{
		if(empty($field['VALUE']))
			return Loc::getMessage('LISTS_FIELD_NOT_DATA');

		if(!is_array($field['VALUE']))
			$field['VALUE'] = array($field['VALUE']);

		$urlTemplate = !empty($field['LIST_URL']) ? $field['LIST_URL'] : '';
		$socnetGroupId = !empty($field['SOCNET_GROUP_ID']) ? intval($field['SOCNET_GROUP_ID']) : 0;

		$result = array();
		$filter = array();
		foreach($field['VALUE'] as $value)
		{
			if(!empty(self::$cache[$field['TYPE']][$value]))
				$result[] = self::$cache[$field['TYPE']][$value];

			$filter['ID'][] = $value;
		}

		if(!empty($result) && (count($result) == count($field['VALUE'])))
			return implode(self::$separator, $result);
		else
			$result = array();

		$queryObject = \CIBlockSection::getList(array(),
			array('=ID' => $field['VALUE']), false, array('ID', 'IBLOCK_ID', 'NAME'));
		while($section = $queryObject->getNext())
		{
			if($urlTemplate)
			{
				$sectionUrl = \CHTTP::URN2URI(\CHTTP::urlAddParams(
					str_replace(array("#list_id#", "#section_id#", "#group_id#"),
					array($section['IBLOCK_ID'], 0, $socnetGroupId),
					$urlTemplate), array('list_section_id' => $section['ID'])));

				$html = '<a href="'.HtmlFilter::encode($sectionUrl).'"  target="_blank">'.
					HtmlFilter::encode($section['~NAME']).'</a>';
			}
			else
			{
				$html = HtmlFilter::encode($section['~NAME']);
			}

			$result[] = $html;
			self::$cache[$field['TYPE']][$section['ID']] = $html;
		}

		return implode(self::$separator, $result);
	}

	protected static function renderFieldByUserTypeElist(array $field)
	{
		return self::getLinkToElement($field);
	}

	protected static function renderFieldByFieldPreviewPicture(array $field)
	{
		return self::renderFieldByTypeF($field);
	}

	protected static function renderFieldByFieldDetailPicture(array $field)
	{
		return self::renderFieldByTypeF($field);
	}

	protected static function renderFieldByFieldActiveFrom(array $field)
	{
		return self::renderDateField($field);
	}

	protected static function renderFieldByFieldActiveTo(array $field)
	{
		return self::renderDateField($field);
	}

	protected static function renderFieldByFieldDateCreate(array $field)
	{
		return self::renderDateField($field);
	}

	protected static function renderFieldByFieldTimestampX(array $field)
	{
		return self::renderDateField($field);
	}

	protected static function renderFieldByFieldCreatedBy(array $field)
	{
		$userId = (int)$field['VALUE'];

		if(!empty(self::$cache[$field['TYPE']][$userId]))
			return self::$cache[$field['TYPE']][$userId];

		$user = new \CUser();
		$userDetails = $user->getByID($userId)->fetch();
		$result = null;

		if(is_array($userDetails))
		{
			$siteNameFormat = \CSite::getNameFormat(false);
			$formattedUsersName = \CUser::formatName($siteNameFormat, $userDetails, true, true);

			$pathToUser = str_replace(array('#user_id#'), $userId,
				Option::get('main', 'TOOLTIP_PATH_TO_USER', false, SITE_ID));

			$anchorId = randString(6);
			$result = '<a id="'.$anchorId.'" href="'.$pathToUser.'" target="_blank">'.$formattedUsersName.'</a>';
			$result .= '<script>BX.tooltip("'.$userId.'", "'.$anchorId.'", "");</script>';

			self::$cache[$field['TYPE']][$userId] = $result;
		}

		return $result;
	}

	protected static function renderFieldByFieldModifiedBy(array $field)
	{
		return self::renderFieldByFieldCreatedBy($field);
	}

	protected static function renderFieldByFieldDetailText(array $field)
	{
		if(isset($field["SETTINGS"]["USE_EDITOR"]) && $field["SETTINGS"]["USE_EDITOR"] == "Y")
			return nl2br($field['VALUE']);
		else
			return nl2br(HtmlFilter::encode($field['VALUE']));
	}

	protected static function renderFieldByFieldPreviewText(array $field)
	{
		if(isset($field["SETTINGS"]["USE_EDITOR"]) && $field["SETTINGS"]["USE_EDITOR"] == "Y")
			return nl2br($field['VALUE']);
		else
			return nl2br(HtmlFilter::encode($field['VALUE']));
	}

	protected static function renderDateField(array $field)
	{
		if(empty($field['VALUE']))
			return '';

		if($field['VALUE'] === '=now')
			return ConvertTimeStamp(time()+\CTimeZone::getOffset(), 'FULL');
		elseif($field['VALUE'] === '=today')
			return ConvertTimeStamp(time()+\CTimeZone::getOffset(), 'SHORT');

		if($field['MULTIPLE'] == 'Y' && is_array($field['VALUE']))
		{
			$results = array();
			foreach($field['VALUE'] as $value)
			{
				$results[] = FormatDateFromDB($value, 'FULL');
			}
			$result = implode(self::$separator, $results);
		}
		else
		{
			$result = FormatDateFromDB($field['VALUE'], 'FULL');
		}
		return $result;
	}

	protected static function renderFieldByFieldName(array $field)
	{
		if(empty($field['LIST_ELEMENT_URL']))
			return self::renderDefaultField($field);

		$iblockId = !empty($field['IBLOCK_ID']) ? intval($field['IBLOCK_ID']) : 0;
		$sectionId = !empty($field['SECTION_ID']) ? intval($field['SECTION_ID']) : 0;
		$elementId = !empty($field['ELEMENT_ID']) ? intval($field['ELEMENT_ID']) : 0;
		$socnetGroupId = !empty($field['SOCNET_GROUP_ID']) ? intval($field['SOCNET_GROUP_ID']) : 0;
		$urlTemplate = $field['LIST_ELEMENT_URL'];

		$url = str_replace(
			array('#list_id#', '#section_id#', '#element_id#', '#group_id#'),
			array($iblockId, $sectionId, $elementId, $socnetGroupId),
			$urlTemplate
		);
		$url = \CHTTP::urlAddParams($url, array("list_section_id" => ""));

		$result = '<a href="'.\CHTTP::URN2URI(HtmlFilter::encode($url)).'">'.HtmlFilter::encode($field['VALUE']).'</a>';
		return $result;
	}

	protected static function renderDefaultField(array $field)
	{
		if($field['MULTIPLE'] == 'Y' && is_array($field['VALUE']))
		{
			$results = array();
			foreach($field['VALUE'] as $value)
			{
				$results[] = $value;
			}
			$result = implode(self::$separator, $results);
		}
		else
		{
			$result = $field['VALUE'];
		}
		return HtmlFilter::encode($result);
	}

	protected static function getLinkToElement(array $field)
	{
		if(empty($field['VALUE']))
			return Loc::getMessage('LISTS_FIELD_NOT_DATA');

		if(!is_array($field['VALUE']))
			$field['VALUE'] = array($field['VALUE']);

		$result = array();
		$filter = array();
		foreach($field['VALUE'] as $value)
		{
			if(!empty(self::$cache[$field['TYPE']][$value]))
				$result[] = self::$cache[$field['TYPE']][$value];

			$filter['ID'][] = $value;
		}

		if(!empty($result) && (count($result) == count($field['VALUE'])))
			return implode(self::$separator, $result);
		else
			$result = array();

		$urlTemplate = \CList::getUrlByIblockId($field['LINK_IBLOCK_ID']);
		if(!$urlTemplate && !empty($field["LIST_ELEMENT_URL"]))
			$urlTemplate = $field["LIST_ELEMENT_URL"];
		$filter['ACTIVE'] = 'Y';
		$filter['ACTIVE_DATE'] = 'Y';
		$filter['CHECK_PERMISSIONS'] = 'Y';
		if ($field['LINK_IBLOCK_ID'] > 0)
			$filter['IBLOCK_ID'] = $field['LINK_IBLOCK_ID'];

		$queryObject = \CIBlockElement::getList(array(), $filter, false, false, array('*'));
		while($element = $queryObject->getNext())
		{
			$elementUrl = str_replace(
				array('#list_id#', '#section_id#', '#element_id#'),
				array($element['IBLOCK_ID'], '0', $element['ID']),
				$urlTemplate
			);
			$elementUrl = \CHTTP::urlAddParams($elementUrl, array("list_section_id" => ""));
			$result[] = '<a href="'.HtmlFilter::encode($elementUrl).'" target="_blank">'.HtmlFilter::encode(
				$element['~NAME']).'</a>';

			self::$cache[$field['TYPE']][$element['ID']] =
				'<a href="'.HtmlFilter::encode($elementUrl).'" target="_blank">'.HtmlFilter::encode(
					$element['~NAME']).'</a>';
		}

		return implode(self::$separator, $result);
	}

	protected static function renderCustomDefaultValueDiskFile(array $field)
	{
		if(!Loader::includeModule('disk'))
			return '';

		if(is_array($field['VALUE']))
			$field['VALUE'] = array_diff($field['VALUE'], array(''));
		else
			$field['VALUE'] = explode(',', $field['VALUE']);

		$listValue = array();
		foreach($field['VALUE'] as $value)
		{
			list($type, $realId) = \Bitrix\Disk\Uf\FileUserType::detectType($value);
			if($type == \Bitrix\Disk\Uf\FileUserType::TYPE_NEW_OBJECT)
			{
				$fileModel = \Bitrix\Disk\File::loadById($realId, array('STORAGE'));
				if(!$fileModel)
				{
					return '';
				}

				$listValue[] = $fileModel->getName();
			}
			else
			{
				$listValue[] = $realId;
			}
		}

		return implode(',', $listValue);
	}

	protected static function prepareEditFieldByTypeL(array $field)
	{
		$items = array('' => Loc::getMessage('LISTS_FIELD_NO_VALUE'));
		$queryObject = \CIBlockProperty::getPropertyEnum($field['ID']);
		while($enum = $queryObject->fetch())
			$items[$enum['ID']] = $enum['VALUE'];

		$inputName = $field['FIELD_ID'];
		if($field['MULTIPLE'] == 'Y')
		{
			$inputName .= '[]';
			$params = array('size' => 5, 'multiple' => 'multiple');
		}
		else
		{
			$params = array();
		}

		if(!is_array($field['VALUE']))
			$field['VALUE'] = array($field['VALUE']);

		$result = array(
			'id' => $inputName,
			'name' => $field['NAME'],
			'required' => $field['IS_REQUIRED'] == 'Y' ? true : false,
			"type"=>'list',
			"items"=>$items,
			'show' => $field['SHOW'],
			'value' => $field['VALUE']
		);
		if($field['READ'] == 'Y')
		{
			$params['disabled'] = 'disabled';

			foreach($field['VALUE'] as $value)
			{
				$result['customHtml'] .= '<input type="hidden" name="'.$inputName.
					'" value="'.HtmlFilter::encode($value).'">';
			}
		}
		$result['params'] = $params;

		return $result;
	}

	protected static function prepareEditFieldByTypeS(array $field)
	{
		$html = '';
		$disabled = $field['READ'] == 'Y' ? 'disabled' : '';
		if(!is_array($field['VALUE']))
			$field['VALUE'] = array($field['VALUE']);

		if($field['MULTIPLE'] == 'Y')
		{
			$html .= '<table id="tbl'.$field['FIELD_ID'].'">';
			if ($field["ROW_COUNT"] > 1)
			{
				foreach($field['VALUE'] as $key => $value)
				{
					if($field['READ'] == 'Y')
					{
						if(empty($value['VALUE'])) continue;
						$html .= '<input type="hidden" name="'.$field['FIELD_ID'].'['.$key.'][VALUE]" value="'.
							HtmlFilter::encode($value["VALUE"]).'">';
					}
					$html.='<tr><td><textarea '.$disabled.' style="width:auto;height:auto;" name="'.$field['FIELD_ID'].
						'['.$key.'][VALUE]" rows="'.intval($field["ROW_COUNT"]).'" cols="'.
						intval($field["COL_COUNT"]).'">'.HtmlFilter::encode($value["VALUE"]).'</textarea></td></tr>';
				}
			}
			else
			{
				foreach($field['VALUE'] as $key => $value)
				{
					if($field['READ'] == 'Y')
					{
						if(empty($value['VALUE'])) continue;
						$html .= '<input type="hidden" name="'.$field['FIELD_ID'].'['.$key.'][VALUE]" value="'.
							HtmlFilter::encode($value["VALUE"]).'">';
					}
					$html .= '<tr><td class="bx-field-value"><input '.$disabled.' type="text" name="'.$field['FIELD_ID'].
						'['.$key.'][VALUE]" value="'.HtmlFilter::encode($value['VALUE']).'" size="'.
						intval($field["COL_COUNT"]).'"></td></tr>';
				}
			}
			$html .= '</table>';
			if($field['READ'] == 'N')
			{
				$html .= '<input type="button" value="'.Loc::getMessage('LISTS_FIELD_ADD_BUTTON').'"
					onclick="BX.Lists.addNewTableRow(\'tbl'.$field['FIELD_ID'].'\', 1, /'.
					$field['FIELD_ID'].'\[(n)([0-9]*)\]/g, 2)">';
			}
		}
		else
		{
			if ($field["ROW_COUNT"] > 1)
			{
				foreach($field['VALUE'] as $key => $value)
				{
					$html .= '<textarea '.$disabled.' style="width:auto;height:auto;" name="'.$field['FIELD_ID'].
						'['.$key.'][VALUE]" rows="'.intval($field["ROW_COUNT"]).'" cols="'.intval(
							$field["COL_COUNT"]).'">'.HtmlFilter::encode($value["VALUE"]).'</textarea>';
					if($field['READ'] == 'Y')
					{
						if(empty($value['VALUE'])) continue;
						$html .= '<input type="hidden" name="'.$field['FIELD_ID'].'['.$key.'][VALUE]" value="'.
							HtmlFilter::encode($value["VALUE"]).'">';
					}
				}
			}
			else
			{
				foreach($field['VALUE'] as $key => $value)
				{
					$html .= '<input '.$disabled.' type="text" name="'.$field['FIELD_ID'].'['.$key.'][VALUE]" value="'.
						HtmlFilter::encode($value["VALUE"]).'" size="'.intval($field["COL_COUNT"]).'">';
					if($field['READ'] == 'Y')
					{
						if(empty($value['VALUE'])) continue;
						$html .= '<input type="hidden" name="'.$field['FIELD_ID'].'['.$key.'][VALUE]" value="'.
							HtmlFilter::encode($value["VALUE"]).'">';
					}
				}
			}
		}

		$result = array(
			'id' => $field['FIELD_ID'],
			'name' => $field['NAME'],
			'required' => $field['IS_REQUIRED'] == 'Y' ? true : false,
			'type' => 'custom',
			'show' => $field['SHOW'],
			'value' => $html
		);

		return $result;
	}

	protected static function prepareEditFieldByTypeN(array $field)
	{
		$result = array(
			'id' => $field['FIELD_ID'],
			'name' => $field['NAME'],
			'required' => $field['IS_REQUIRED'] == 'Y' ? true : false,
			'type' => 'custom',
			'show' => $field['SHOW'],
		);
		$html = '';
		$disabled = $field['READ'] == 'Y' ? 'disabled' : '';
		if(!is_array($field['VALUE']))
			$field['VALUE'] = array($field['VALUE']);

		if($field['MULTIPLE'] == 'Y')
		{
			$html .= '<table id="tbl'.$field['FIELD_ID'].'">';
			foreach($field['VALUE'] as $key => $value)
			{
				if($field['READ'] == 'Y')
				{
					if(empty($value['VALUE'])) continue;
					$result['customHtml'] .= '<input type="hidden" name="'.$field['FIELD_ID'].
						'['.$key.'][VALUE]" value="'.$value['VALUE'].'">';
				}
				$html .= '<tr><td class="bx-field-value"><input '.$disabled.' type="text" name="'.$field['FIELD_ID'].
					'['.$key.'][VALUE]" value="'.HtmlFilter::encode($value["VALUE"]).'"></td></tr>';
			}
			$html .= '</table>';
			if($field['READ'] == 'N')
			{
				$html .= '<input type="button" value="'.Loc::getMessage('LISTS_FIELD_ADD_BUTTON').'"
					onclick="BX.Lists.addNewTableRow(\'tbl'.$field['FIELD_ID'].'\', 1, /'.
					$field['FIELD_ID'].'\[(n)([0-9]*)\]/g, 2)">';
			}
		}
		else
		{
			foreach($field['VALUE'] as $key => $value)
			{
				$html .= '<input '.$disabled.' type="text" name="'.$field['FIELD_ID'].
					'['.$key.'][VALUE]" value="'.$value["VALUE"].'">';
				if($field['READ'] == 'Y')
				{
					if(empty($value['VALUE'])) continue;
					$result['customHtml'] .= '<input type="hidden" name="'.$field['FIELD_ID'].
						'['.$key.'][VALUE]" value="'.$value["VALUE"].'">';
				}
			}
		}
		$result['value'] = $html;

		return $result;
	}

	protected static function prepareEditFieldByUserTypeHTML(array $field)
	{
		$result = array(
			'id' => $field['FIELD_ID'].'[]',
			'name' => $field['NAME'],
			'required' => $field['IS_REQUIRED'] == 'Y' ? true : false,
			'type' => 'custom',
			'show' => $field['SHOW']
		);
		$html = '';
		if(!is_array($field['VALUE']))
			$field['VALUE'] = array($field['VALUE']);

		$isEmptyValue = true;
		foreach($field['VALUE'] as $value)
		{
			if(!empty($value['VALUE']))
				$isEmptyValue = false;
		}
		if($isEmptyValue && $field['READ'] == 'Y')
		{
			$result['value'] = Loc::getMessage('LISTS_FIELD_NOT_DATA');
			return $result;
		}

		if($field['MULTIPLE'] == 'Y')
		{
			$params = array('width' => '100%','height' => '200px');
			$html .= '<table id="tbl'.$field['FIELD_ID'].'">';
			foreach($field['VALUE'] as $key => $value)
			{
				if($field['READ'] == 'Y')
				{
					$html .= call_user_func_array($field['PROPERTY_USER_TYPE']['GetPublicViewHTML'],
						array(
							$field,
							$value,
							array(
								'VALUE' => $field['FIELD_ID']."[".$key."][VALUE]",
								'DESCRIPTION' => '',
								'FORM_NAME' => 'form_'.$field['FORM_ID'],
								'MODE' => 'FORM_FILL',
								'COPY' => $field['COPY_ID'] > 0,
							),
						));
					if(is_array($value['VALUE']))
					{
						$value['VALUE']['TEXT'] ? $htmlContent = $value['VALUE']['TEXT'] : $htmlContent = '';
					}
					else
					{
						$value['VALUE'] ? $htmlContent = $value['VALUE'] : $htmlContent = '';
					}
					$result['customHtml'] .= '<input type="hidden" name="'.$field['FIELD_ID'].
						'['.$key.'][VALUE][TYPE]" value="html">';
					$result['customHtml'] .= '<input type="hidden" name="'.$field['FIELD_ID'].
						'['.$key.'][VALUE][TEXT]" value="'.HtmlFilter::encode($htmlContent).'">';
				}
				else
				{
					if(is_array($value['VALUE']))
						$htmlContent = $value['VALUE']['TEXT'] ? $value['VALUE']['TEXT'] : '';
					else
						$htmlContent = $value['VALUE'] ? $value['VALUE'] : '';
					$fieldIdForHtml = 'id_'.$field['FIELD_ID'].'__'.$key.'_';
					$fieldNameForHtml = $field['FIELD_ID']."[".$key."][VALUE][TEXT]";
					$html .= '<tr><td><input type="hidden" name="'.$field['FIELD_ID'].
						'['.$key.'][VALUE][TYPE]" value="html">'.self::renderHtmlEditor(
							$fieldIdForHtml, $fieldNameForHtml, $params, $htmlContent).'</td></tr>';
				}
			}
			$html .= '</table>';
			if($field['READ'] == 'N')
			{
				$html .= '<input type="button" value="'.Loc::getMessage("LISTS_FIELD_ADD_BUTTON").'"
				onclick="BX.Lists.createAdditionalHtmlEditor(\'tbl'.$field['FIELD_ID'].'\',
				\''.$field['FIELD_ID'].'\', \''.$field['FIELD_ID'].'\');">';
			}

		}
		else
		{
			foreach($field['VALUE'] as $key => $value)
			{
				if($field['READ'] == 'Y')
				{
					$html .= call_user_func_array($field['PROPERTY_USER_TYPE']['GetPublicViewHTML'],
						array(
							$field,
							$value,
							array(
								'VALUE' => $field['FIELD_ID']."[".$key."][VALUE]",
								'DESCRIPTION' => '',
								'FORM_NAME' => 'form_'.$field['FORM_ID'],
								'MODE' => 'FORM_FILL',
								'COPY' => $field['COPY_ID'] > 0,
							),
						));
					if(is_array($value['VALUE']))
						$value['VALUE']['TEXT'] ? $htmlContent = $value['VALUE']['TEXT'] : $htmlContent = '';
					else
						$value['VALUE'] ? $htmlContent = $value['VALUE'] : $htmlContent = '';
					$result['customHtml'] .= '<input type="hidden" name="'.$field['FIELD_ID'].
						'['.$key.'][VALUE][TYPE]" value="html">';
					$result['customHtml'] .= '<input type="hidden" name="'.$field['FIELD_ID'].
						'['.$key.'][VALUE][TEXT]" value="'.HtmlFilter::encode($htmlContent).'">';
				}
				else
				{
					$html .= call_user_func_array($field['PROPERTY_USER_TYPE']['GetPublicEditHTML'],
						array(
							$field,
							$value,
							array(
								'VALUE' => $field['FIELD_ID']."[".$key."][VALUE]",
								'DESCRIPTION' => '',
								'FORM_NAME' => 'form_'.$field['FORM_ID'],
								'MODE' => 'FORM_FILL',
								'COPY' => $field['COPY_ID'] > 0,
							),
						));
				}
			}
		}

		$result['value'] = $html;

		return $result;
	}

	protected static function prepareEditFieldByFieldCreatedBy(array $field)
	{
		$result = array();
		if($field['ELEMENT_ID'])
		{
			$result = array(
				'id' => $field['FIELD_ID'],
				'name' => $field['NAME'],
				'required' => $field['IS_REQUIRED'] == 'Y' ? true : false,
				'type' => 'custom',
				'show' => $field['SHOW'],
				'value' => self::renderFieldByFieldCreatedBy($field)
			);
		}
		return $result;
	}

	protected static function prepareEditFieldByFieldModifiedBy(array $field)
	{
		return self::prepareEditFieldByFieldCreatedBy($field);
	}

	protected static function prepareEditFieldByFieldDateCreate(array $field)
	{
		$result = array();
		if($field['ELEMENT_ID'])
		{
			$result = array(
				'id' => $field['FIELD_ID'],
				'name' => $field['NAME'],
				'required' => $field['IS_REQUIRED'] == 'Y' ? true : false,
				'type' => 'custom',
				'show' => $field['SHOW'],
				'value' => $field['VALUE']
			);
		}
		return $result;
	}

	protected static function prepareEditFieldByFieldTimestampX(array $field)
	{
		return self::prepareEditFieldByFieldDateCreate($field);
	}

	protected static function prepareEditFieldByFieldDetailText(array $field)
	{
		return self::prepareEditFieldByText($field);
	}

	protected static function prepareEditFieldByFieldPreviewText(array $field)
	{
		return self::prepareEditFieldByText($field);
	}

	protected static function prepareEditFieldByFieldPreviewPicture(array $field)
	{
		$result = array(
			'id' => $field['FIELD_ID'],
			'name' => $field['NAME'],
			'required' => $field['IS_REQUIRED'] == 'Y' ? true : false,
			'type' => 'custom',
			'show' => $field['SHOW'],
			'value' => ($field['ELEMENT_ID'] > 0 && empty($field['VALUE']) && $field['READ'] == 'Y') ?
				Loc::getMessage('LISTS_FIELD_NOT_DATA') : self::renderFieldByTypeF($field)
		);
		return $result;
	}

	protected static function prepareEditFieldByFieldDetailPicture(array $field)
	{
		$result = array(
			'id' => $field['FIELD_ID'],
			'name' => $field['NAME'],
			'required' => $field['IS_REQUIRED'] == 'Y' ? true : false,
			'type' => 'custom',
			'show' => $field['SHOW'],
			'value' => ($field['ELEMENT_ID'] > 0 && empty($field['VALUE']) && $field['READ'] == 'Y') ?
				Loc::getMessage('LISTS_FIELD_NOT_DATA') : self::renderFieldByTypeF($field)
		);
		return $result;
	}

	protected static function prepareEditFieldByFieldActiveFrom(array $field)
	{
		return self::prepareDateEditField($field);
	}

	protected static function prepareEditFieldByFieldActiveTo(array $field)
	{
		return self::prepareDateEditField($field);
	}

	protected static function prepareEditFieldByText($field)
	{
		if($field['READ'] == 'Y')
		{
			$result = array(
				'id' => $field['FIELD_ID'],
				'name' => $field['NAME'],
				'required' => $field['IS_REQUIRED'] == 'Y' ? true : false,
				'type' => 'custom',
				'value' => '<textarea disabled>'.HtmlFilter::encode($field['VALUE']).'</textarea>
					<input type="hidden" name="'.$field['FIELD_ID'].'" value="'.HtmlFilter::encode($field['VALUE']).'">',
				'show' => $field['SHOW']
			);
		}
		else
		{
			$result = array(
				'id' => $field['FIELD_ID'],
				'name' => $field['NAME'],
				'required' => $field['IS_REQUIRED'] == 'Y' ? true : false,
				'type' => 'textarea',
				'show' => $field['SHOW']
			);
			if($field['SETTINGS']['USE_EDITOR'] == 'Y')
			{
				$params = array('width' => '100%', 'height' => '200px');
				$match = array();
				if(preg_match('/\s*(\d+)\s*(px|%|)/', $field['SETTINGS']['WIDTH'], $match) && ($match[1] > 0))
					$params['width'] = $match[1].$match[2];
				if(preg_match('/\s*(\d+)\s*(px|%|)/', $field['SETTINGS']['HEIGHT'], $match) && ($match[1] > 0))
					$params['height'] = $match[1].$match[2];
				$result['type'] = 'custom';
				$result['params'] = $params;
				$result['value'] = self::renderHtmlEditor(
					$field['FIELD_ID'], $field['FIELD_ID'], $params, $field['VALUE']);
			}
			else
			{
				$params = array('style' => '');
				if(preg_match('/\s*(\d+)\s*(px|%|)/', $field['SETTINGS']['WIDTH'], $match) && ($match[1] > 0))
					$params['style'] .= 'width:'.$match[1].'px;';
				if(preg_match('/\s*(\d+)\s*(px|%|)/', $field['SETTINGS']['HEIGHT'], $match) && ($match[1] > 0))
					$params['style'] .= 'height:'.$match[1].'px;';
				$result['params'] = $params;
			}
		}

		return $result;
	}

	protected static function prepareEditFieldByTypeE(array $field)
	{
		if($field['READ'] == 'Y' && empty($field['VALUE']))
		{
			return array(
				'id' => $field['FIELD_ID'],
				'name' => $field['NAME'],
				'required' => $field['IS_REQUIRED'] == 'Y' ? true : false,
				'type' => 'custom',
				'show' => $field['SHOW'],
				'value' => Loc::getMessage('LISTS_FIELD_NOT_DATA')
			);
		}

		if(!is_array($field['VALUE']))
			$field['VALUE'] = array($field['VALUE']);

		$currentElements = array();
		foreach($field['VALUE'] as $value)
		{
			if($value)
			{
				$currentElements[] = $value;
			}
		}

		$randomGenerator = new RandomSequence($field['FIELD_ID']);
		$randString = strtolower($randomGenerator->randString(6));

		$html = '';
		global $APPLICATION;
		ob_start();
		$APPLICATION->includeComponent('bitrix:iblock.element.selector', '',
			array(
				'SELECTOR_ID' => $randString,
				'INPUT_NAME' => $field['FIELD_ID'],
				'IBLOCK_ID' => $field['LINK_IBLOCK_ID'],
				'MULTIPLE' => $field['MULTIPLE'],
				'CURRENT_ELEMENTS_ID' => $currentElements,
				'POPUP' => 'Y',
				'ONLY_READ' => $field['READ'],
				'PANEL_SELECTED_VALUES' => 'Y'
			),
			null, array('HIDE_ICONS' => 'Y')
		);
		$html .= ob_get_contents();
		ob_end_clean();

		$result = array(
			'id' => $field['FIELD_ID'],
			'name' => $field['NAME'],
			'required' => $field['IS_REQUIRED'] == 'Y' ? true : false,
			'type' => 'custom',
			'show' => $field['SHOW'],
			'value' => $html
		);

		return $result;
	}

	protected static function prepareEditFieldByTypeG(array $field)
	{
		if($field['IS_REQUIRED'] == 'Y')
			$items = array();
		else
			$items = array('' => Loc::getMessage('LISTS_FIELD_NO_VALUE'));

		$queryObject = \CIBlockSection::getTreeList(array('IBLOCK_ID' => $field['LINK_IBLOCK_ID']));
		while($section = $queryObject->getNext())
			$items[$section['ID']] = str_repeat(' . ', $section['DEPTH_LEVEL']).$section['~NAME'];

		$inputName = $field['FIELD_ID'];
		if($field['MULTIPLE'] == 'Y')
		{
			$inputName .= '[]';
			$params = array('size' => 5, 'multiple' => 'multiple');
		}
		else
		{
			$params = array();
		}
		if($field['READ'] == 'Y')
			$params["disabled"] = 'disabled';

		if(!is_array($field['VALUE']))
			$field['VALUE'] = array($field['VALUE']);

		$result = array(
			'id' => $inputName,
			'name' => $field['NAME'],
			'required' => $field['IS_REQUIRED'] == 'Y' ? true : false,
			'type' => 'list',
			'show' => $field['SHOW'],
			'value' => $field['VALUE'],
			'items' => $items,
			'params' => $params
		);

		if($field['READ'] == 'Y')
		{
			foreach($field['VALUE'] as $value)
				$result['customHtml'] .= '<input type="hidden" name="'.$field['FIELD_ID'].'[]" value="'.
					HtmlFilter::encode($value).'">';
		}

		return $result;
	}

	protected static function prepareEditFieldByTypeF(array $field)
	{
		$html = '';
		if(!is_array($field['VALUE']))
			$field['VALUE'] = array($field['VALUE']);

		$isEmptyValue = true;
		foreach($field['VALUE'] as $value)
		{
			if(!empty($value['VALUE']))
				$isEmptyValue = false;
		}

		if($field['MULTIPLE'] == 'Y')
		{
			$html .= '<table id="tbl'.$field['FIELD_ID'].'">';
			if($field['ELEMENT_ID'] > 0 && $isEmptyValue && $field['READ'] == 'Y')
			{
				$html .= '<tr><td>';
				$html .= Loc::getMessage('LISTS_FIELD_NOT_DATA');
				$html .= '</td></tr>';
			}
			else
			{
				$iblockId = !empty($field['IBLOCK_ID']) ? intval($field['IBLOCK_ID']) : 0;
				$sectionId = !empty($field['SECTION_ID']) ? intval($field['SECTION_ID']) : 0;
				$elementId = !empty($field['ELEMENT_ID']) ? intval($field['ELEMENT_ID']) : 0;
				$fieldId = !empty($field['FIELD_ID']) ? $field['FIELD_ID'] : '';
				$socnetGroupId = !empty($field['SOCNET_GROUP_ID']) ? intval($field['SOCNET_GROUP_ID']) : 0;
				$urlTemplate = !empty($field['LIST_FILE_URL']) ? $field['LIST_FILE_URL'] : '';
				$downloadUrl = !empty($field['DOWNLOAD_FILE_URL']) ? $field['DOWNLOAD_FILE_URL'] : '';
				$params = array(
					'max_size' => 2024000,
					'max_width' => 100,
					'max_height' => 100,
					'url_template' => $urlTemplate,
					'download_url' => $downloadUrl,
					'download_text' => Loc::getMessage('LISTS_FIELD_FILE_DOWNLOAD'),
					'show_input' => $field['READ'] == 'N'
				);
				foreach($field['VALUE'] as $key => $value)
				{
					$html .= '<tr><td>';
					$file = new \CListFile($iblockId, $sectionId, $elementId, $fieldId,
						is_array($value) && isset($value['VALUE']) ? $value['VALUE'] : $value);
					$file->setSocnetGroup($socnetGroupId);
					$fieldControlId = $field['TYPE'] == 'F' && self::$renderForForm ?
						$fieldId.'['.$key.'][VALUE]' : $fieldId;
					$fileControl = new \CListFileControl($file, $fieldControlId);
					$html .= $fileControl->getHTML($params);
					$html .= '</td></tr>';
				}
			}
			$html .= '</table>';
			if($field['READ'] == 'N')
			{
				$html .= '<input type="button" value="'.Loc::getMessage("LISTS_FIELD_ADD_BUTTON").'"
					onclick="BX.Lists.addNewTableRow(\'tbl'.$field['FIELD_ID'].'\', 1, /'.
					$field['FIELD_ID'].'\[(n)([0-9]*)\]/g, 2)">';
			}
		}
		else
		{
			$html .= ($field['ELEMENT_ID'] > 0 && $isEmptyValue && $field['READ'] == 'Y') ?
				Loc::getMessage('LISTS_FIELD_NOT_DATA') : self::renderFieldByTypeF($field);
		}

		$result = array(
			'id' => $field['FIELD_ID'],
			'name' => $field['NAME'],
			'required' => $field['IS_REQUIRED'] == 'Y' ? true : false,
			'type' => 'custom',
			'show' => $field['SHOW'],
			'value' => $html
		);
		return $result;
	}

	protected static function prepareDateEditField(array $field)
	{
		$result = array(
			'id' => $field['FIELD_ID'],
			'name' => $field['NAME'],
			'required' => $field['IS_REQUIRED'] == 'Y' ? true : false,
			'type' => 'date',
			'show' => $field['SHOW']
		);

		if($field['READ'] == 'Y')
		{
			$result['type'] = 'custom';
			if($field['ELEMENT_ID'] > 0 && empty($field['VALUE']))
			{
				$result['value'] = Loc::getMessage('LISTS_FIELD_NOT_DATA');
			}
			else
			{
				$result['value'] = '<input disabled type="text" value="'.HtmlFilter::encode($field['VALUE']).
					'"><input type="hidden" name="'.$field['FIELD_ID'].'" value="'.
					HtmlFilter::encode($field['VALUE']).'">';
			}
		}
		return $result;
	}

	protected static function prepareEditDefaultField(array $field)
	{
		$result = array(
			'id' => $field['FIELD_ID'],
			'name' => $field['NAME'],
			'required' => $field['IS_REQUIRED'] == 'Y' ? true : false,
			'type' => 'text',
			'show' => $field['SHOW']
		);
		if($field['READ'] == 'Y')
		{
			$result['type'] = 'custom';
			$result['value'] = '<input disabled type="text" value="'.HtmlFilter::encode($field['VALUE']).
				'"><input type="hidden" name="'.$field["FIELD_ID"].'" value="'.HtmlFilter::encode($field['VALUE']).'">';
		}
		return $result;
	}

	protected static function renderHtmlEditor($fieldId, $fieldNameForHtml, $params, $content)
	{
		$html = '';
		if (Loader::includeModule('fileman'))
		{
			ob_start();
			$editor = new \CHTMLEditor;
			$res = array(
				'name' => $fieldNameForHtml,
				'inputName' => $fieldNameForHtml,
				'id' => $fieldId,
				'width' => $params['width'],
				'height' => $params['height'],
				'content' => $content,
				'useFileDialogs' => false,
				'minBodyWidth' => 350,
				'normalBodyWidth' => 555,
				'bAllowPhp' => false,
				'limitPhpAccess' => false,
				'showTaskbars' => false,
				'showNodeNavi' => false,
				'beforeUnloadHandlerAllowed' => true,
				'askBeforeUnloadPage' => false,
				'bbCode' => false,
				'siteId' => SITE_ID,
				'autoResize' => true,
				'autoResizeOffset' => 40,
				'saveOnBlur' => true,
				'actionUrl' => '/bitrix/tools/html_editor_action.php',
				'setFocusAfterShow' => false,
				'controlsMap' => array(
					array('id' => 'Bold', 'compact' => true, 'sort' => 80),
					array('id' => 'Italic', 'compact' => true, 'sort' => 90),
					array('id' => 'Underline', 'compact' => true, 'sort' => 100),
					array('id' => 'Strikeout', 'compact' => true, 'sort' => 110),
					array('id' => 'RemoveFormat', 'compact' => true, 'sort' => 120),
					array('id' => 'Color', 'compact' => true, 'sort' => 130),
					array('id' => 'FontSelector', 'compact' => false, 'sort' => 135),
					array('id' => 'FontSize', 'compact' => false, 'sort' => 140),
					array('separator' => true, 'compact' => false, 'sort' => 145),
					array('id' => 'OrderedList', 'compact' => true, 'sort' => 150),
					array('id' => 'UnorderedList', 'compact' => true, 'sort' => 160),
					array('id' => 'AlignList', 'compact' => false, 'sort' => 190),
					array('separator' => true, 'compact' => false, 'sort' => 200),
					array('id' => 'InsertLink', 'compact' => true, 'sort' => 210),
					array('id' => 'InsertImage', 'compact' => false, 'sort' => 220),
					array('id' => 'InsertVideo', 'compact' => true, 'sort' => 230),
					array('id' => 'InsertTable', 'compact' => false, 'sort' => 250),
					array('separator' => true, 'compact' => false, 'sort' => 290),
					array('id' => 'Fullscreen', 'compact' => false, 'sort' => 310),
					array('id' => 'More', 'compact' => true, 'sort' => 400)
				),
			);
			$editor->show($res);
			$html = ob_get_contents();
			ob_end_clean();
		}
		return $html;
	}
}