<?php
namespace Bitrix\Landing\Node;

use \Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class Component extends \Bitrix\Landing\Node
{
	/**
	 * Predefined values for some dynamic props.
	 * @var array
	 */
	protected static $predefineForDynamicProps = array();

	/**
	 * Get class - frontend handler.
	 * @return string
	 */
	public static function getHandlerJS()
	{
		return 'BX.Landing.Block.Node.Component';
	}

	/**
	 * Fill predefined values for some dynamic props.
	 * @param array $additionalVals Additional vals.
	 * @return void
	 */
	public static function setPredefineForDynamicProps(array $additionalVals)
	{
		foreach ($additionalVals as $code => $val)
		{
			self::$predefineForDynamicProps[$code] = $val;
		}
	}

	/**
	 * Save component with new params.
	 * @param string $content Content of block.
	 * @param string $code Code of component.
	 * @param array $params Params for replace in component.
	 * @return string Modified content.
	 */
	protected static function saveComponent($content, $code, array $params)
	{
		$components = \PHPParser::parseScript($content);
		foreach ($components as $component)
		{
			// get first component with code = $code
			if ($component['DATA']['COMPONENT_NAME'] == $code)
			{
				$params = array_merge($component['DATA']['PARAMS'], $params);
				$componentCode = ($component['DATA']['VARIABLE'] ? $component['DATA']['VARIABLE'] . '=' : '') .
					'$APPLICATION->IncludeComponent(' . PHP_EOL .
					"\t" . '"' . $component['DATA']['COMPONENT_NAME'] . '", ' . PHP_EOL .
					"\t" . '"' . $component['DATA']['TEMPLATE_NAME'] . '", ' . PHP_EOL .
					"\t" . 'array(' . PHP_EOL .
					"\t" . "\t" . \PHPParser::returnPHPStr2($params) . PHP_EOL .
					"\t" . '),' . PHP_EOL .
					"\t" . ($component['DATA']['PARENT_COMP'] ? $component['DATA']['PARENT_COMP'] : 'false') .
							(!empty($component['DATA']['FUNCTION_PARAMS']) ? ',' . PHP_EOL .
					"\t" . 'array(' . PHP_EOL . "\t" . "\t" . \PHPParser::returnPHPStr2($component['DATA']['FUNCTION_PARAMS']) . PHP_EOL .
					"\t" . ')' : '') . PHP_EOL .
					');';
				$componentCode = str_replace(array('<?', '?>'), array('< ?', '? >'), $componentCode);
				$content = substr($content, 0, $component['START']) . $componentCode . substr($content, $component['END']);
				break;
			}
		}

		return $content;
	}

	/**
	 * Check if part of array or string is php code (for component).
	 * @param mixed $code Some content.
	 * @return bool
	 */
	protected static function checkPhpCode($code)
	{
		if (is_array($code))
		{
			foreach ($code as $k => $v)
			{
				if (
					self::checkPhpCode($k) ||
					self::checkPhpCode($v)
				)
				{
					return true;
				}
			}
		}
		else{
			if (
				substr($code, 0, 2) == '={' &&
				substr($code, -1, 1) == '}' &&
				strlen($code) > 3
			)
			{
				return true;
			}
		}

		return false;
	}

	/**
	 * Save data for this node.
	 * @param \Bitrix\Landing\Block &$block Block instance.
	 * @param string $selector Selector.
	 * @param array $data Data array.
	 * @return void
	 */
	public static function saveNode(\Bitrix\Landing\Block &$block, $selector, array $data)
	{
		//$data = array_pop($data);// we allow one type of component per block
		$manifest = $block->getManifest();
		if (isset($manifest['nodes'][$selector]['extra']))
		{
			$updateProps = array();
			$allowedProps = $manifest['nodes'][$selector]['extra'];
			foreach ($data as $code => $val)
			{
				if (isset($allowedProps[$code]))
				{
					$updateProps[$code] = self::transformPropValue(
						$val,
						$allowedProps[$code]
					);
					if (self::checkPhpCode(array($code => $updateProps[$code])))
					{
						unset($updateProps[$code]);
					}
				}
			}
			if (!empty($updateProps))
			{
				// !tmp bugfix about set section id to null
				if (
					array_key_exists('SECTION_ID', $updateProps) &&
					!trim($updateProps['SECTION_ID'])
				)
				{
					$updateProps['SECTION_ID'] = '={$sectionId}';
				}
				$doc = $block->getDom();
				$newContent = self::saveComponent(
					$doc->saveHTML(),
					$selector,
					$updateProps
				);
				// first clear dom
				foreach ($doc->getChildNodesArray() as $node)
				{
					$node->getParentNode()->removeChild($node);
				}
				// and load new content
				$doc->loadHTML($newContent);
			}
		}
	}

	/**
	 * Prepare item-node of manifest.
	 * @param \Bitrix\Landing\Block $block Block instance.
	 * @param array $manifest Manifest of current node.
	 * @param array &$manifestFull Full manifest of block (by ref).
	 * @return array|null Return null for delete from manifest.
	 */
	public static function prepareManifest(\Bitrix\Landing\Block $block, array $manifest, array &$manifestFull = array())
	{
		if (
			!isset($manifest['extra']['editable']) ||
			!is_array($manifest['extra']['editable'])
		)
		{
			return null;
		}
		else
		{
			$editable = $manifest['extra']['editable'];
		}

		if (
			!isset($manifestFull['attrs']) ||
			!is_array($manifestFull['attrs'])
		)
		{
			$manifestFull['attrs'] = array();
		}

		if (
			!isset($manifestFull['style']) ||
			!is_array($manifestFull['style'])
		)
		{
			$manifestFull['style'] = array();
		}

		$manifestFull['disableCache'] = true;
		$manifest['allowInlineEdit'] = false;
		$newExtra = array();
		$originalStyleBlock = isset($manifestFull['style']['block'])
							? $manifestFull['style']['block']
							: array();

		// detect all components in text
		$components = \PHPParser::parseScript($block->getContent());
		foreach ($components as $component)
		{
			$componentName = $manifest['code'];
			// when found what need, get actually params from text and props description from component
			if ($component['DATA']['COMPONENT_NAME'] == $componentName)
			{
				// collect props
				$componentDesc = \CComponentUtil::GetComponentDescr(
					$component['DATA']['COMPONENT_NAME']
				);
				$propsTemplate = @\CComponentUtil::GetTemplateProps(//@fixme
					$component['DATA']['COMPONENT_NAME'],
					$component['DATA']['TEMPLATE_NAME'],
					'',
					self::$predefineForDynamicProps
				);
				if (isset($propsTemplate['PARAMETERS']))
				{
					$propsTemplate = $propsTemplate['PARAMETERS'];
				}
				$props = @\CComponentUtil::getComponentProps(//@fixme
					$component['DATA']['COMPONENT_NAME'],
					self::$predefineForDynamicProps
				);
				if (isset($props['PARAMETERS']))
				{
					$props = $props['PARAMETERS'];
				}
				if (!empty($propsTemplate) && is_array($propsTemplate))
				{
					foreach ($propsTemplate as $code => $prop)
					{
						$props[$code] = $prop;
					}
				}
				// style block
				$styleAttrs = array();
				if (
					!isset($manifestFull['style']) ||
					!is_array($manifestFull['style'])
				)
				{
					$manifestFull['style'] = array(
						'block' => array(),
						'nodes' => array()
					);
				}
				else if (!isset($manifestFull['style']['nodes']))
				{
					$manifestFull['style'] = array(
						'nodes' => $manifestFull['style']
					);
				}
				$manifestFull['style']['block'] = array_merge(array(
					'name' => isset($componentDesc['NAME'])
							? $componentDesc['NAME']
							: '',
					'type' => 'box',
					'additional' => array(
						array(
							'name' => Loc::getMessage('LANDING_NODE_CMP_STYLE_BLOCK'),
							'attrs' => &$styleAttrs
						)
					)
				), $originalStyleBlock);
				foreach ($editable as $field => $fieldItem)
				{
					if (isset($props[$field]))
					{
						// change node manifest
						$newExtra[$field] = $props[$field];
						$newExtra[$field]['VALUE'] = isset($component['DATA']['PARAMS'][$field])
													? $component['DATA']['PARAMS'][$field]
													: '';
						// add attr
						if (!isset($manifestFull['attrs'][$componentName]))
						{
							$manifestFull['attrs'][$componentName] = array();
						}
						$propType = self::transformPropType(array(
							'name' => isset($fieldItem['name'])
										? $fieldItem['name']
										: $newExtra[$field]['NAME'],
							'style' => isset($fieldItem['style'])
										&& $fieldItem['style'],
							'original_type' => 'component',
							'component_type' => isset($newExtra[$field]['TYPE'])
										? $newExtra[$field]['TYPE']
										: '',
							'attribute' => $field,
							'value' => self::preparePropValue(
								$newExtra[$field]['VALUE'],
								$fieldItem
							),
							//'original_value' => $newExtra[$field]['VALUE'],
							'allowInlineEdit' => false
						) + $fieldItem, $newExtra[$field]);
						$newExtra[$field]['ATTRIBUTE_TYPE'] = $propType['type'];
						if ($propType['style'])
						{
							$propType['selector'] = $componentName;
							$styleAttrs[] = $propType;
						}
						else
						{
							$manifestFull['attrs'][$componentName][] = $propType;
						}
					}
				}
				if (empty($styleAttrs))
				{
					if ($originalStyleBlock)
					{
						$manifestFull['style']['block'] = $originalStyleBlock;
					}
					else
					{
						unset($manifestFull['style']['block']);
					}
				}
				// all right
				if (!empty($newExtra))
				{
					$manifest['extra'] = $newExtra;
					return $manifest;
				}
			}
		}
		return null;
	}

	/**
	 * Additional transform type of prop item to attr item.
	 * @param array $item One attr.
	 * @param mixed $prop One prop.
	 * @return array
	 */
	protected static function transformPropType(array $item, $prop)
	{
		if (isset($prop['TYPE']))
		{
			if (
				$prop['TYPE'] == 'CUSTOM' &&
				isset($prop['JS_EVENT'])
			)
			{
				$prop['TYPE'] = $prop['TYPE'] . '_' . $prop['JS_EVENT'];
			}

			switch ($prop['TYPE'])
			{
				case 'LIST':
				{
					$item['items'] = array();
					if (isset($prop['MULTIPLE']) && $prop['MULTIPLE'] == 'Y')
					{
						$item['type'] = 'multiselect';
						if (!is_array($item['value']))
						{
							$item['value'] = array($item['value']);
						}
					}
					else
					{
						$prop['MULTIPLE'] = 'N';
						$item['type'] = 'dropdown';
					}
					if (isset($prop['VALUES']) && is_array($prop['VALUES']))
					{
						foreach ($prop['VALUES'] as $code => $val)
						{
							$item['items'][] = array(
								'name' => $val,
								'value' => $code,
								'selected' => (
												$prop['MULTIPLE'] == 'Y' &&
												in_array($code, $item['value'])
											) || $code == $item['value']
							);
						}
					}
					break;
				}
				case 'CHECKBOX':
				{
					$item['type'] = 'checkbox';
					$item['items'] = array(
						array(
							'name' => $item['name'],
							'value' => 'Y',
							'checked' => $item['value'] == 'Y'
						)
					);
					$item['compact'] = true;
					unset($item['name']);
					break;
				}
				case 'CUSTOM_initDraggableAddControl':
				{
					$item['type'] = 'catalog-view';
					$item['items'] = array(
						array('name' => '', 'image' => '/bitrix/images/landing/catalog_images/preset-1.svg', 'value' => '0'),
						array('name' => '', 'image' => '/bitrix/images/landing/catalog_images/preset-2.svg', 'value' => '1'),
						array('name' => '', 'image' => '/bitrix/images/landing/catalog_images/preset-3.svg', 'value' => '2'),
						array('name' => '', 'image' => '/bitrix/images/landing/catalog_images/preset-4.svg', 'value' => '3'),
						array('name' => '', 'image' => '/bitrix/images/landing/catalog_images/preset-1-4.svg', 'value' => '4'),
						array('name' => '', 'image' => '/bitrix/images/landing/catalog_images/preset-4-1.svg', 'value' => '5'),
						array('name' => '', 'image' => '/bitrix/images/landing/catalog_images/preset-6.svg', 'value' => '6'),
						array('name' => '', 'image' => '/bitrix/images/landing/catalog_images/preset-1-6.svg', 'value' => '7'),
						array('name' => '', 'image' => '/bitrix/images/landing/catalog_images/preset-6-1.svg', 'value' => '8'),
						array('name' => '', 'image' => '/bitrix/images/landing/catalog_images/preset-line.svg', 'value' => '9')
					);
					$jsArray = \Cutil::jsObjectToPhp($item['value']);
					$item['value'] = array();
					if (is_array($jsArray))
					{
						foreach ($jsArray as $val)
						{
							if (isset($val['VARIANT']))
							{
								$item['value'][] = (int)$val['VARIANT'];
							}
						}
					}
					break;
				}
				case 'CUSTOM_initPositionControl':
				{
					$item['type'] = 'position';
					$item['items'] = array(
						'top-left' => array('content' => '', 'value' => 'top-left'),
						'top-center' => array('content' => '', 'value' => 'top-center'),
						'top-right' => array('content' => '', 'value' => 'top-right'),
						'middle-left' => array('content' => '', 'value' => 'middle-left'),
						'middle-center' => array('content' => '', 'value' => 'middle-center'),
						'middle-right' => array('content' => '', 'value' => 'middle-right'),
						'bottom-left' => array('content' => '', 'value' => 'bottom-left'),
						'bottom-center' => array('content' => '', 'value' => 'bottom-center'),
						'bottom-right' => array('content' => '', 'value' => 'bottom-right')
					);
					break;
				}
				case 'CUSTOM_initDraggableOrderControl':
				{
					$item['type'] = 'sortable-list';
					$item['items'] = array();
					if (!is_array($item['value']))
					{
						$item['value'] = explode(',', $item['value']);
					}
					$items = \Cutil::jsObjectToPhp($prop['JS_DATA']);
					if (is_array($items))
					{
						foreach ($items as $code => $val)
						{
							$item['items'][] = array(
								'name' => $val,
								'value' => $code,
								'preview' => '/bitrix/images/landing/catalog_images/preview/' . strtolower($code) . '.svg?v3'
							);
						}
					}
					break;
				}
				default:
				{
					if (!isset($item['type']) || !$item['type'])
					{
						$item['type'] = 'text';
					}
					switch ($item['type'])
					{
						case 'url':
						{
							$item['disableBlocks'] = true;
							break;
						}
						case 'filter':
						{
							ob_start();
							$filterId = 'LANDING_FLT_' . $item['attribute'];
							\Bitrix\Landing\Manager::getApplication()->includeComponent(
								'bitrix:main.ui.filter',
								'',
								array(
									'THEME' => \Bitrix\Main\UI\Filter\Theme::BORDER,
									'FILTER_ID' => $filterId,
									'FILTER' => isset($item['fields'])
														? $item['fields']
														: array(),
									'DISABLE_SEARCH' => true,
									'ENABLE_LABEL' => true
								)
							);
							$item['html'] = ob_get_clean();
							$item['filterId'] = $filterId;
							break;
						}
						default:
						{
							$item['placeholder'] = '';
						}
					}
					break;
				}
			}
		}

		return $item;
	}

	/**
	 * Prepare prop value before output in edit form.
	 * @param mixed $value Mixed value.
	 * @param array $prop Array of field from manifest.
	 * @return mixed
	 */
	protected static function preparePropValue($value, $prop)
	{
		if (isset($prop['type']))
		{
			switch ($prop['type'])
			{
				case 'url':
					{
						if ($value && isset($prop['entityType']))
						{
							// @todo: make this more universal
							if (
								$prop['entityType'] == 'element' &&
								$value != '={$elementCode}' &&
								$value != '={$elementId}'
							)
							{
								$value = '#catalogElement' . $value;
							}
							else if (
								$prop['entityType'] == 'section' &&
								$value != '={$sectionCode}' &&
								$value != '={$sectionId}'
							)
							{
								$value = '#catalogSection' . $value;
							}
						}
					}
			}
		}
		return $value;
	}

	/**
	 * Additional transform prop value before saving.
	 * @param mixed $value Mixed value.
	 * @param array $prop Array of prop.
	 * @return mixed
	 */
	protected static function transformPropValue($value, $prop)
	{
		if (!is_array($value))
		{
			$value = \CUtil::jsObjectToPhp($value);
		}

		if (isset($prop['TYPE']))
		{
			if (
				$prop['TYPE'] == 'CUSTOM' &&
				isset($prop['JS_EVENT'])
			)
			{
				$prop['TYPE'] = $prop['TYPE'] . '_' . $prop['JS_EVENT'];
			}
			if (
				isset($prop['MULTIPLE']) &&
				$prop['MULTIPLE'] == 'Y' &&
				!is_array($value)
			)
			{
				$value = array($value);
			}

			switch ($prop['TYPE'])
			{
				case 'CHECKBOX':
				{
					if (is_array($value))
					{
						$value = array_shift($value);
					}
					if ($value != 'Y')
					{
						$value = 'N';
					}
					break;
				}
				case 'CUSTOM_initDraggableAddControl':
				{
					$newValue = array();
					if (is_array($value))
					{
						foreach ($value as $val)
						{
							$newValue[] = array(
								'VARIANT' => $val,
								'BIG_DATA' => false
							);
						}
					}
					$value = \CUtil::phpToJsObject($newValue);
					break;
				}
				case 'CUSTOM_initDraggableOrderControl':
				{
					if (is_array($value))
					{
						$value = implode(',', $value);
					}
					break;
				}
				default:
				{
					if (isset($prop['ATTRIBUTE_TYPE']))
					{
						switch ($prop['ATTRIBUTE_TYPE'])
						{
							case 'url':
							{
								if (preg_match('/^#landing([\d]+)$/', $value, $matches))
								{
									$lansing = \Bitrix\Landing\Landing::createInstance($matches[1]);
									if ($lansing->exist())
									{
										$value = $lansing->getPublicUrl();
									}
								}
								else if (preg_match('/^#catalog(Element|Section)([\d]+)$/', $value, $matches))
								{
									$value = $matches[2];
								}
								break;
							}
						}
					}
				}
			}
		}

		return $value;
	}

	/**
	 * Build element/section url.
	 * @param int $elementId Element / section id.
	 * @param string $urlType Type of url (section / detail).
	 * @deprecated since 18.4.0
	 * @return string
	 */
	public static function getIblockURL($elementId, $urlType)
	{
		return \Bitrix\Landing\PublicAction\Utils::getIblockURL($elementId, $urlType);
	}

	/**
	 * Tmp function for gets iblock params.
	 * @param string $key If isset, return value for this key.
	 * @deprecated since 18.4.0
	 * @return array|string
	 */
	public static function getIblockParams($key = false)
	{
		static $params = array();

		if (empty($params))
		{
			$params['id'] = \Bitrix\Main\Config\Option::get('crm', 'default_product_catalog_id');
			$params['type'] = 'CRM_PRODUCT_CATALOG';
			$params['default_product'] = false;
		}

		if ($key === false)
		{
			return $params;
		}
		else
		{
			return isset($params[$key]) ? $params[$key] : null;
		}
	}

	/**
	 * Get data for this node.
	 * @param \Bitrix\Landing\Block &$block Block instance.
	 * @param string $selector Selector.
	 * @return array
	 */
	public static function getNode(\Bitrix\Landing\Block &$block, $selector)
	{
		$data = array();
		$manifest = $block->getManifest();

		// gets common attrs
		if (isset($manifest['attrs'][$selector]))
		{
			$allowedProps = $manifest['attrs'][$selector];
			foreach ($allowedProps as $attr)
			{
				if (!self::checkPhpCode($attr['value']))
				{
					$data[$attr['attribute']] = $attr['value'];
				}
			}
		}

		// gets attrs from style block
		if (
			isset($manifest['style']['block']['additional']) &&
			is_array($manifest['style']['block']['additional'])
		)
		{
			foreach ($manifest['style']['block']['additional'] as $item)
			{
				if (
					isset($item['attrs']) &&
					is_array($item['attrs'])
				)
				{
					foreach ($item['attrs'] as $attr)
					{
						if (!self::checkPhpCode($attr['value']))
						{
							$data[$attr['attribute']] = $attr['value'];
						}
					}
				}
			}
		}

		return $data;
	}
}