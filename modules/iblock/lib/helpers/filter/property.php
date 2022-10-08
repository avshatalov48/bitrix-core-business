<?php
namespace Bitrix\Iblock\Helpers\Filter;

use Bitrix\Main\Loader;
use Bitrix\Main\Page\Asset;
use Bitrix\Main\SystemException;
use Bitrix\Main\Web\Json;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\UI\Filter\Options as FilterOptions;

Loc::loadMessages(__FILE__);

/**
 * Class Property
 *
 * The class to display iblock property with the type of "custom_entity" in the main.ui.filter
 *
 * @package Bitrix\Iblock\Helpers\Filter
 */
class Property
{
	/**
	 * Method renders the html for custom_entity field.
	 *
	 * @param string $filterId Filter id.
	 * @param string $propertyType Type property.
	 * @param array $listProperty List properties.
	 * @return string Html.
	 */
	public static function render($filterId, $propertyType, array $listProperty)
	{
		$result = '';
		/**
		 * @see Property::renderByECrm()
		 * @see Property::renderByE()
		 * @see Property::renderByEmployee()
		 */
		if(method_exists(__CLASS__, 'renderBy'.$propertyType))
		{
			$renderMethod = 'renderBy'.$propertyType;
			$result = self::$renderMethod($filterId, $listProperty);
		}

		return $result;
	}

	/**
	 * Add values in filter.
	 *
	 * @param array $property Property data.
	 * @param array $controlSettings Form data.
	 * @param array &$filter Filter data.
	 * @param bool &$filtered Marker filter.
	 * @return void
	 */
	public static function addFilter($property, $controlSettings, &$filter, &$filtered)
	{
		if(isset($property['PROPERTY_USER_TYPE']['USER_TYPE'])
			&& method_exists(__CLASS__, 'addFilterBy'.$property['PROPERTY_USER_TYPE']['USER_TYPE']))
		{
			$renderMethod = 'addFilterBy'.$property['PROPERTY_USER_TYPE']['USER_TYPE'];
			self::$renderMethod($property, $controlSettings, $filter, $filtered);
		}
		elseif($property['PROPERTY_TYPE'] != '')
		{
			$renderMethod = 'addFilterBy'.$property['PROPERTY_TYPE'];
			if(method_exists(__CLASS__, $renderMethod))
			{
				self::$renderMethod($property, $controlSettings, $filter, $filtered);
			}
		}
		elseif($property['TYPE'] != '')
		{
			$renderMethod = 'addFilterBy'.str_replace('_', '', $property['TYPE']);
			if(method_exists(__CLASS__, $renderMethod))
			{
				self::$renderMethod($property, $controlSettings, $filter, $filtered);
			}
		}
	}

	protected static function renderByECrm($filterId, $listProperty)
	{
		if(!Loader::includeModule('crm'))
			return '';

		\Bitrix\Main\UI\Extension::load('ui.fonts.opensans');
		Asset::getInstance()->addCss('/bitrix/js/crm/css/crm.css');
		Asset::getInstance()->addJs('/bitrix/js/crm/crm.js');

		$html = self::getJsHandlerECrm();

		if(!empty($listProperty)):
			ob_start(); ?>
			<script>
			BX.ready(function() {
				BX.FilterCrmEntitySelector.messages =
				{
					'contact': '<?=GetMessageJS('FIP_CRM_FF_CONTACT')?>',
					'company': '<?=GetMessageJS('FIP_CRM_FF_COMPANY')?>',
					'quote': '<?=GetMessageJS('FIP_CRM_FF_QUOTE')?>',
					'lead': '<?=GetMessageJS('FIP_CRM_FF_LEAD')?>',
					'deal': '<?=GetMessageJS('FIP_CRM_FF_DEAL')?>',
					'selectButton': '<?=GetMessageJS('CRM_ENTITY_SEL_BTN')?>',
					'noresult': '<?=GetMessageJS('CRM_SEL_SEARCH_NO_RESULT')?>',
					'search': '<?=GetMessageJS('CRM_ENTITY_SEL_SEARCH')?>',
					'last': '<?=GetMessageJS('CRM_ENTITY_SEL_LAST')?>'
				};
				<?
					$listAvailableEntity = array('DEAL', 'CONTACT', 'COMPANY', 'LEAD');
					foreach($listProperty as $property)
					{
						$fieldId = $property['FIELD_ID'];
						$entityTypeNames = array();
						foreach($property['USER_TYPE_SETTINGS'] as $entityType => $useMarker)
						{
							if($useMarker == 'Y' && in_array($entityType, $listAvailableEntity))
							{
								$entityTypeNames[] = $entityType;
							}
						}
						$isMultiple = $property['MULTIPLE'] == 'Y' ? true : false;
						$title = '';
						?>
							BX.FilterHandlerECrm.create(
								'<?=\CUtil::JSEscape($filterId.'_'.$fieldId)?>',
								{
									fieldId: '<?=\CUtil::JSEscape($fieldId)?>',
									entityTypeNames: <?=Json::encode($entityTypeNames)?>,
									isMultiple: <?=$isMultiple ? 'true' : 'false'?>,
									title: '<?=\CUtil::JSEscape($title)?>'
								}
							);
						<?
					}
				?>
			});
			</script>
			<?
			$html .= ob_get_contents();
			ob_end_clean();
		endif;

		return $html;
	}

	protected static function renderByE($filterId, array $listProperty)
	{
		$html = '';

		if(!empty($listProperty))
		{
			$html .= self::getJsHandlerE();
			global $APPLICATION;

			$filterOption = new FilterOptions($filterId);
			$filterData = $filterOption->getFilter();
			foreach($listProperty as $property)
			{
				$currentElements = array();
				if(!empty($filterData[$property['FIELD_ID']]))
				{
					try
					{
						global $APPLICATION;
						$convertValue = $APPLICATION->ConvertCharset(
							$filterData[$property['FIELD_ID']], 'UTF-8', LANG_CHARSET);
						$currentValues = Json::decode($convertValue);
					}
					catch(SystemException $e)
					{
						return $e->getMessage();
					}
					if(is_array($currentValues))
					{
						foreach($currentValues as $value)
						{
							$currentElements[] = current($value);
						}
					}
					else
					{
						$currentElements[] = $currentValues;
					}
				}
				ob_start();
				$APPLICATION->includeComponent('bitrix:iblock.element.selector', '',
					array(
						'SELECTOR_ID' => $filterId.'_'.$property['FIELD_ID'],
						'SEARCH_INPUT_ID' => $filterId.'_'.$property['FIELD_ID'],
						'IBLOCK_ID' => $property['LINK_IBLOCK_ID'],
						'MULTIPLE' => 'Y',
						'PANEL_SELECTED_VALUES' => 'N',
						'CURRENT_ELEMENTS_ID' => $currentElements
					),
					null, array('HIDE_ICONS' => 'Y')
				);
				?>
				<script>
					BX.ready(function(){
						BX.FilterHandlerE.create(
							'<?=\CUtil::JSEscape($filterId.'_'.$property['FIELD_ID'])?>',
							{
								fieldId: '<?=\CUtil::JSEscape($property['FIELD_ID'])?>',
								controlId: '<?=\CUtil::JSEscape($filterId.'_'.$property['FIELD_ID'])?>',
								multiple: 'Y'
							}
						);
					});
				</script>
				<?
				$html .= ob_get_contents();
				ob_end_clean();
			}
		}

		return $html;
	}

	protected static function renderByEmployee($filterId, array $listProperty)
	{
		if(!Loader::includeModule('intranet'))
			return '';

		$html = '';
		if(!empty($listProperty))
		{
			$html .= self::getJsHandlerEmployee();
			global $APPLICATION;
			ob_start();
			foreach($listProperty as $property):
				$APPLICATION->includeComponent('bitrix:intranet.user.selector.new', '',
					array(
						'MULTIPLE' => 'N',
						'NAME' => $filterId.'_'.$property['FIELD_ID'],
						'INPUT_NAME' => mb_strtolower($filterId.'_'.$property['FIELD_ID']),
						'POPUP' => 'Y',
						'SITE_ID' => SITE_ID,
						'SHOW_EXTRANET_USERS' => 'NONE',
					),
					null, array('HIDE_ICONS' => 'Y')
				);
				?>
				<script>
				BX.ready(function(){
					BX.FilterHandlerEmployee.create(
						'<?=\CUtil::JSEscape($filterId.'_'.$property['FIELD_ID'])?>',
						{
							fieldId: '<?=\CUtil::JSEscape($property['FIELD_ID'])?>',
							controlId: '<?=\CUtil::JSEscape($filterId.'_'.$property['FIELD_ID'])?>'
						}
					);
				});
				</script>
			<? endforeach;
			$html .= ob_get_contents();
			ob_end_clean();
		}

		return $html;
	}

	protected static function getJsHandlerECrm()
	{
		ob_start();
		?>
		<script>
		(function () {
		'use strict';
		if(typeof(BX.FilterHandlerECrm) === 'undefined')
		{
			BX.FilterHandlerECrm = function()
			{
				this._id = '';
				this._settings = {};
				this._fieldId = '';
				this._control = null;
				this._entitySelector = null;
			};

			BX.FilterHandlerECrm.prototype =
			{
				initialize: function(id, settings)
				{
					this._id = id;
					this._settings = settings ? settings : {};
					this._fieldId = this.getSetting('fieldId', '');

					BX.addCustomEvent(window, 'BX.Main.Filter:customEntityFocus',
						BX.delegate(this.onCustomEntitySelectorOpen, this));
					BX.addCustomEvent(window, 'BX.Main.Filter:customEntityBlur',
						BX.delegate(this.onCustomEntitySelectorClose, this));
				},
				getId: function()
				{
					return this._id;
				},
				getSetting: function (name, defaultval)
				{
					return this._settings.hasOwnProperty(name)  ? this._settings[name] : defaultval;
				},
				getSearchInput: function()
				{
					return this._control ? this._control.getLabelNode() : null;
				},
				onCustomEntitySelectorOpen: function(control)
				{
					if(control.getId() !== this._fieldId)
					{
						this._control = null;
						this.close();
					}
					else
					{
						this._control = control;
						this.closeSiblings();
						this.open();
					}
				},
				onCustomEntitySelectorClose: function(control)
				{
					if(this._fieldId === control.getId())
					{
						this._control = null;
						this.close();
					}
				},
				onSelect: function(sender, data)
				{
					if(!this._control || this._control.getId() !== this._fieldId)
					{
						return;
					}

					var labels = [];
					var values = {};
					for(var typeName in data)
					{
						if(!data.hasOwnProperty(typeName))
						{
							continue;
						}

						var infos = data[typeName];
						for(var i = 0, l = infos.length; i < l; i++)
						{
							var info = infos[i];
							labels.push(info['title']);
							if(typeof(values[typeName]) === 'undefined')
							{
								values[typeName] = [];
							}

							values[typeName].push(info['entityId']);
						}
					}
					this._control.setData(labels.join(', '), JSON.stringify(values));
				},
				open: function()
				{
					if(!this._entitySelector)
					{
						this._entitySelector = BX.FilterCrmEntitySelector.create(
							this._id,
							{
								control: this._control,
								entityTypeNames: this.getSetting('entityTypeNames', []),
								isMultiple: this.getSetting('isMultiple', false),
								anchor: this.getSearchInput(),
								title: this.getSetting('title', '')
							}
						);

						BX.addCustomEvent(this._entitySelector, 'BX.FilterCrmEntitySelector:select',
							BX.delegate(this.onSelect, this));
					}

					this._entitySelector.open();
					if(this._control)
					{
						if(this._entitySelector.getPopup())
						{
							this._control.setPopupContainer(this._entitySelector.getPopup()['contentContainer']);
						}
					}
				},
				close: function()
				{
					if(this._entitySelector)
					{
						this._entitySelector.close();
						if(this._control)
						{
							this._control.setPopupContainer(null);
						}
					}
				},
				closeSiblings: function()
				{
					var siblings = BX.FilterCrmEntitySelector.items;
					for(var k in siblings)
					{
						if(siblings.hasOwnProperty(k) && siblings[k] !== this)
						{
							siblings[k].close();
						}
					}
				}
			};

			BX.FilterHandlerECrm.items = {};
			BX.FilterHandlerECrm.create = function(id, settings)
			{
				var self = new BX.FilterHandlerECrm(id, settings);
				self.initialize(id, settings);
				BX.FilterHandlerECrm.items[self.getId()] = self;
				return self;
			}
		}

		if(typeof(BX.FilterCrmEntitySelector) === 'undefined')
		{
			BX.FilterCrmEntitySelector = function()
			{
				this._id = '';
				this._settings = {};
				this._entityTypeNames = [];
				this._isMultiple = false;
				this._entityInfos = null;
				this._entitySelectHandler = BX.delegate(this.onEntitySelect, this);
			};
			BX.FilterCrmEntitySelector.prototype =
			{
				initialize: function(id, settings)
				{
					this._id = id;
					this._settings = settings ? settings : {};
					this._entityTypeNames = this.getSetting('entityTypeNames', []);
					this._isMultiple = this.getSetting('isMultiple', false);
					this._entityInfos = [];
					this._control = this.getSetting('control', null)
				},
				getId: function()
				{
					return this._id;
				},
				getSetting: function (name, defaultval)
				{
					return this._settings.hasOwnProperty(name)  ? this._settings[name] : defaultval;
				},
				getMessage: function(name)
				{
					var msg = BX.FilterCrmEntitySelector.messages;
					return msg.hasOwnProperty(name) ? msg[name] : name;
				},
				getPopup: function()
				{
					return typeof(obCrm[this._id]) !== 'undefined' ? obCrm[this._id].popup : null;
				},
				isOpened: function()
				{
					return ((obCrm[this._id].popup instanceof BX.PopupWindow) && obCrm[this._id].popup.isShown());
				},
				getSearchInput: function()
				{
					return this._control ? this._control.getLabelNode() : null;
				},
				open: function()
				{
					if(typeof(obCrm[this._id]) === 'undefined')
					{
						var entityTypes = [];
						for(var i = 0, l = this._entityTypeNames.length; i < l; i++)
						{
							entityTypes.push(this._entityTypeNames[i].toLowerCase());
						}

						obCrm[this._id] = new CRM(
							this._id,
							null,
							this.getSearchInput(),
							this._id,
							this._entityInfos,
							false,
							this._isMultiple,
							entityTypes,
							{
								'contact': this.getMessage('contact'),
								'company': this.getMessage('company'),
								'quote': this.getMessage('quote'),
								'lead': this.getMessage('lead'),
								'deal': this.getMessage('deal'),
								'ok': this.getMessage('selectButton'),
								'cancel': BX.message('JS_CORE_WINDOW_CANCEL'),
								'close': BX.message('JS_CORE_WINDOW_CLOSE'),
								'wait': BX.message('JS_CORE_LOADING'),
								'noresult': this.getMessage('noresult'),
								'search' : this.getMessage('search'),
								'last' : this.getMessage('last')
							},
							true
						);
						obCrm[this._id].Init();
						obCrm[this._id].AddOnSaveListener(this._entitySelectHandler);
					}

					if(!((obCrm[this._id].popup instanceof BX.PopupWindow) && obCrm[this._id].popup.isShown()))
					{
						obCrm[this._id].Open(
							{
								closeIcon: { top: '10px', right: '15px' },
								closeByEsc: true,
								autoHide: false,
								gainFocus: false,
								anchor: this.getSearchInput(),
								titleBar: this.getSetting('title')
							}
						);
					}
				},
				close: function()
				{
					if(typeof(obCrm[this._id]) !== 'undefined')
					{
						obCrm[this._id].RemoveOnSaveListener(this._entitySelectHandler);
						obCrm[this._id].Clear();
						delete obCrm[this._id];
					}

				},
				onEntitySelect: function(settings)
				{
					this.close();

					var data = {};
					for(var type in settings)
					{
						if(!settings.hasOwnProperty(type))
						{
							continue;
						}

						var entityInfos = settings[type];
						if(!BX.type.isPlainObject(entityInfos))
						{
							continue;
						}

						var typeName = type.toUpperCase();
						for(var key in entityInfos)
						{
							if(!entityInfos.hasOwnProperty(key))
							{
								continue;
							}

							var entityInfo = entityInfos[key];
							this._entityInfos.push(
								{
									'id': entityInfo['id'],
									'type': entityInfo['type'],
									'title': entityInfo['title'],
									'desc': entityInfo['desc'],
									'url': entityInfo['url'],
									'image': entityInfo['image'],
									'selected': entityInfo['selected']
								}
							);

							var entityId = BX.type.isNotEmptyString(entityInfo['id']) ?
								parseInt(entityInfo['id']) : 0;
							if(entityId > 0)
							{
								if(typeof(data[typeName]) === 'undefined')
								{
									data[typeName] = [];
								}

								data[typeName].push(
									{
										entityTypeName: typeName,
										entityId: entityId,
										title: BX.type.isNotEmptyString(entityInfo['title']) ?
											entityInfo['title'] : ('[' + entityId + ']')
									}
								);
							}
						}
					}

					BX.onCustomEvent(this, 'BX.FilterCrmEntitySelector:select', [this, data]);
				}
			};

			if(typeof(BX.FilterCrmEntitySelector.messages) === 'undefined')
			{
				BX.FilterCrmEntitySelector.messages = {};
			}

			BX.FilterCrmEntitySelector.items = {};
			BX.FilterCrmEntitySelector.create = function(id, settings)
			{
				var self = new BX.FilterCrmEntitySelector(id, settings);
				self.initialize(id, settings);
				BX.FilterCrmEntitySelector.items[self.getId()] = self;
				return self;
			}
		}
		})();
		</script>
		<?
		$script = ob_get_contents();
		ob_end_clean();
		return  $script;
	}

	protected static function getJsHandlerEmployee()
	{
		ob_start();
		?>
		<script>
		(function () {
			'use strict';
			if(typeof(BX.FilterHandlerEmployee) === 'undefined')
			{
				BX.FilterHandlerEmployee = function() {
					this._id = '';
					this._settings = {};
					this._fieldId = '';
					this._control = null;

					this._currentUser = null;
					this._controlId = null;
					this._controlObj = null;
					this._controlContainer = null;
					this._serviceContainer = null;

					this._zIndex = 1100;
					this._isDialogDisplayed = false;
					this._dialog = null;

					this._inputKeyPressHandler = BX.delegate(this.onInputKeyPress, this);
				};
				BX.FilterHandlerEmployee.prototype =
				{
					initialize: function(id, settings)
					{
						this._id = id;
						this._settings = settings ? settings : {};
						this._fieldId = this.getSetting('fieldId', '');
						this._controlId = this.getSetting('controlId', '');
						this._controlContainer = BX(this._controlId + '_selector_content');

						this._serviceContainer = this.getSetting('serviceContainer', null);
						if(!BX.type.isDomNode(this._serviceContainer))
						{
							this._serviceContainer = document.body;
						}

						BX.addCustomEvent(window, 'BX.Main.Filter:customEntityFocus',
							BX.delegate(this.onCustomEntitySelectorOpen, this));
						BX.addCustomEvent(window, 'BX.Main.Filter:customEntityBlur',
							BX.delegate(this.onCustomEntitySelectorClose, this));
					},
					getId: function()
					{
						return this._id;
					},
					getSetting: function (name, defaultval)
					{
						return this._settings.hasOwnProperty(name)  ? this._settings[name] : defaultval;
					},
					getSearchInput: function()
					{
						return this._control ? this._control.getLabelNode() : null;
					},
					isOpened: function()
					{
						return this._isDialogDisplayed;
					},
					open: function()
					{
						if(this._controlObj === null)
						{
							var objName = 'O_' + this._controlId;
							if(!window[objName])
							{
								throw 'BX.FilterHandlerEmployee: Could not find '+ objName +' user selector.';
							}
							this._controlObj = window[objName];
						}

						var searchInput = this.getSearchInput();
						if(this._controlObj.searchInput)
						{
							BX.unbind(this._controlObj.searchInput, 'keyup',
								BX.proxy(this._controlObj.search, this._controlObj));
						}
						this._controlObj.searchInput = searchInput;
						BX.bind(this._controlObj.searchInput, 'keyup',
							BX.proxy(this._controlObj.search, this._controlObj));
						this._controlObj.onSelect = BX.delegate(this.onSelect, this);
						BX.bind(searchInput, 'keyup', this._inputKeyPressHandler);

						if(this._currentUser)
						{
							this._controlObj.setSelected([ this._currentUser ]);
						}
						else
						{
							var selected = this._controlObj.getSelected();
							if(selected)
							{
								for(var key in selected)
								{
									if(selected.hasOwnProperty(key))
									{
										this._controlObj.unselect(key);
									}
								}
							}
						}

						if(this._dialog === null)
						{
							this._controlContainer.style.display = '';
							this._dialog = new BX.PopupWindow(
								this._id,
								this.getSearchInput(),
								{
									autoHide: false,
									draggable: false,
									closeByEsc: true,
									offsetLeft: 0,
									offsetTop: 0,
									zIndex: this._zIndex,
									bindOptions: { forceBindPosition: true },
									content : this._controlContainer,
									events:
										{
											onPopupShow: BX.delegate(this.onDialogShow, this),
											onPopupClose: BX.delegate(this.onDialogClose, this),
											onPopupDestroy: BX.delegate(this.onDialogDestroy, this)
										}
								}
							);
						}

						this._dialog.show();
						this._controlObj._onFocus();
						if(this._control)
						{
							this._control.setPopupContainer(this._controlContainer);
						}
					},
					close: function()
					{
						var searchInput = this.getSearchInput();
						if(searchInput)
						{
							BX.bind(searchInput, 'keyup', this._inputKeyPressHandler);
						}

						if(this._dialog)
						{
							this._dialog.close();
						}

						if(this._control)
						{
							this._control.setPopupContainer(null);
						}
					},
					onCustomEntitySelectorOpen: function(control)
					{
						if(control.getId() !== this._fieldId)
						{
							this._control = null;
							this.close();
						}
						else
						{
							this._control = control;
							var currentValues = this._control.getCurrentValues();
							this._currentUser = {'id':currentValues.value,'name':currentValues.label};
							this.open();
						}
					},
					onCustomEntitySelectorClose: function(control)
					{
						if(control.getId() !== this._fieldId)
						{
							return;
						}
						this.close();
					},
					onDialogShow: function()
					{
						this._isDialogDisplayed = true;
					},
					onDialogClose: function()
					{
						this._isDialogDisplayed = false;
						this._controlContainer.parentNode.removeChild(this._controlContainer);
						this._serviceContainer.appendChild(this._controlContainer);
						this._controlContainer.style.display = 'none';
						this._dialog.destroy();
					},
					onDialogDestroy: function()
					{
						this._dialog = null;
					},
					onInputKeyPress: function(e)
					{
						if(!this._dialog || !this._isDialogDisplayed)
						{
							this.open();
						}

						if(this._controlObj)
						{
							this._controlObj.search();
						}
					},
					onSelect: function(user)
					{
						this._currentUser = user;
						if(this._control)
						{
							var node = this._control.getLabelNode();
							node.value = '';
							this._control.setData(user['name'], user['id']);
						}
						this.close();
					}
				};
				BX.FilterHandlerEmployee.closeAll = function()
				{
					for(var k in this.items)
					{
						if(this.items.hasOwnProperty(k))
						{
							this.items[k].close();
						}
					}
				};
				BX.FilterHandlerEmployee.items = {};
				BX.FilterHandlerEmployee.create = function(id, settings)
				{
					var self = new BX.FilterHandlerEmployee(id, settings);
					self.initialize(id, settings);
					BX.FilterHandlerEmployee.items[self.getId()] = self;
					return self;
				}
			}
		})();
		</script>
		<?
		$script = ob_get_contents();
		ob_end_clean();
		return  $script;
	}

	protected static function getJsHandlerE()
	{
		ob_start();
		?>
		<script>
			(function () {
				'use strict';
				if(typeof(BX.FilterHandlerE) === 'undefined')
				{
					BX.FilterHandlerE = function() {
						this._id = '';
						this._settings = {};
						this._fieldId = '';
						this._control = null;

						this._currentElements = [];
						this._controlId = null;
						this._controlObj = null;
						this._controlContainer = null;
						this._serviceContainer = null;

						this._zIndex = 1100;
						this._isDialogDisplayed = false;
						this._dialog = null;

						this._inputKeyPressHandler = BX.proxy(this.onInputKeyPress, this);
					};
					BX.FilterHandlerE.prototype =
						{
							initialize: function(id, settings)
							{
								this._id = id;
								this._settings = settings ? settings : {};
								this._fieldId = this.getSetting('fieldId', '');
								this._controlId = this.getSetting('controlId', '');
								this._multiple = this.getSetting('multiple', 'Y') === 'Y';
								this._controlContainer = BX(this._controlId);

								this._serviceContainer = this.getSetting('serviceContainer', null);
								if(!BX.type.isDomNode(this._serviceContainer))
								{
									this._serviceContainer = document.body;
								}

								BX.addCustomEvent(window, 'BX.Main.Filter:customEntityFocus',
									BX.proxy(this.onCustomEntitySelectorOpen, this));
								BX.addCustomEvent(window, 'BX.Main.Filter:customEntityBlur',
									BX.proxy(this.onCustomEntitySelectorClose, this));
							},
							getId: function()
							{
								return this._id;
							},
							getSetting: function (name, defaultval)
							{
								return this._settings.hasOwnProperty(name)  ? this._settings[name] : defaultval;
							},
							getSearchInput: function()
							{
								return this._control ? this._control.getLabelNode() : null;
							},
							isOpened: function()
							{
								return this._isDialogDisplayed;
							},
							open: function()
							{
								if(this._controlObj === null)
								{
									var objName = BX.Iblock[this._controlId];
									if(!objName)
									{
										throw 'BX.FilterHandlerE: Could not find '+ objName +' element selector.';
									}
									this._controlObj = objName;
								}

								this._multiple = this._controlObj.multiple;
								var searchInput = this.getSearchInput();
								if(this._controlObj.searchInput)
								{
									BX.unbind(this._controlObj.searchInput, 'keyup',
										BX.proxy(this._controlObj.search, this._controlObj));
								}
								this._controlObj.searchInput = searchInput;
								BX.bind(this._controlObj.searchInput, 'keyup',
									BX.proxy(this._controlObj.search, this._controlObj));
								this._controlObj.onSelect = BX.proxy(this.onSelect, this);
								BX.bind(searchInput, 'keyup', this._inputKeyPressHandler);
								if(this._multiple)
								{
									this._controlObj.onUnSelect = BX.proxy(this.onSelect, this);
								}

								if(this._currentElements)
								{
									this._controlObj.setSelected(this._currentElements);
								}
								else
								{
									var selected = this._controlObj.getSelected();
									if(selected)
									{
										for(var key in selected)
										{
											if(selected.hasOwnProperty(key))
											{
												this._controlObj.unselect(key);
											}
										}
									}
								}

								if(this._dialog === null)
								{
									this._controlContainer.style.display = '';
									this._dialog = new BX.PopupWindow(
										this._id,
										this.getSearchInput(),
										{
											autoHide: false,
											draggable: false,
											closeByEsc: true,
											offsetLeft: 0,
											offsetTop: 0,
											zIndex: this._zIndex,
											bindOptions: { forceBindPosition: true },
											content : this._controlContainer,
											events:
												{
													onPopupShow: BX.delegate(this.onDialogShow, this),
													onPopupClose: BX.delegate(this.onDialogClose, this),
													onPopupDestroy: BX.delegate(this.onDialogDestroy, this)
												}
										}
									);
								}

								this._dialog.show();
								this._controlObj._onFocus();
								if(this._control)
								{
									this._control.setPopupContainer(this._controlContainer);
								}
							},
							close: function()
							{
								var searchInput = this.getSearchInput();
								if(searchInput)
								{
									BX.bind(searchInput, 'keyup', this._inputKeyPressHandler);
								}

								if(this._dialog)
								{
									this._dialog.close();
								}

								if(this._control)
								{
									this._control.setPopupContainer(null);
								}
							},
							onCustomEntitySelectorOpen: function(control)
							{
								if(control.getId() !== this._fieldId)
								{
									this._control = null;
									this.close();
								}
								else
								{
									this._control = control;
									var currentValues = this._control.getCurrentValues();
									if(!!currentValues.value)
									{
										this._currentElements = [];
										if(this._multiple)
										{
											var values = JSON.parse(currentValues.value);
											for(var k in values)
											{
												this._currentElements.push(
													{"id": values[k][0], "name": values[k][1]});
											}
										}
										else
										{
											this._currentElements.push(
												{"id": currentValues.value, "name": currentValues.label});
										}
									}
									this.open();
								}
							},
							onCustomEntitySelectorClose: function(control)
							{
								if (control.getId() !== this._fieldId)
								{
									return;
								}

								var currentValues = control.getCurrentValues();
								if (!currentValues.value && control.getLabelNode())
								{
									var value = control.getLabelNode().value;
									if (parseInt(value))
									{
										control.getLabelNode().value = "";
										control.setData(value, value);
									}
								}

								this.close();
							},
							onDialogShow: function()
							{
								this._isDialogDisplayed = true;
							},
							onDialogClose: function()
							{
								this._isDialogDisplayed = false;
								this._controlContainer.parentNode.removeChild(this._controlContainer);
								this._serviceContainer.appendChild(this._controlContainer);
								this._controlContainer.style.display = 'none';
								this._dialog.destroy();
							},
							onDialogDestroy: function()
							{
								this._dialog = null;
							},
							onInputKeyPress: function()
							{
								if(!this._dialog || !this._isDialogDisplayed)
								{
									this.open();
								}
								if(this._controlObj)
								{
									this._controlObj.search();
								}
							},
							onSelect: function(element)
							{
								if(!this._control || this._control.getId() !== this._fieldId)
								{
									return;
								}
								var node = this._control.getLabelNode();
								node.value = '';

								if(this._multiple)
								{
									this._currentElements = element;
									var labels = [];
									var values = {};
									for(var k in this._currentElements)
									{
										if(!this._currentElements.hasOwnProperty(k) || !this._currentElements[k])
										{
											continue;
										}
										labels.push(this._currentElements[k].name);
										if(typeof(values[this._currentElements[k].id]) === 'undefined')
										{
											values[this._currentElements[k].id] = [];
										}
										values[this._currentElements[k].id].push(this._currentElements[k].id);
										values[this._currentElements[k].id].push(this._currentElements[k].name);
									}
									if (labels.join(', '))
									{
										this._control.setData(labels.join(', '), JSON.stringify(values));
									}
									else
									{
										this._control.removeSquares();
									}
								}
								else
								{
									this._currentElements.push(element);
									this._control.setData(element.name, element.id);
									this.close();
								}
							}
						};
					BX.FilterHandlerE.closeAll = function()
					{
						for(var k in this.items)
						{
							if(this.items.hasOwnProperty(k))
							{
								this.items[k].close();
							}
						}
					};
					BX.FilterHandlerE.items = {};
					BX.FilterHandlerE.create = function(id, settings)
					{
						var self = new BX.FilterHandlerE(id, settings);
						self.initialize(id, settings);
						BX.FilterHandlerE.items[self.getId()] = self;
						return self;
					}
				}
			})();
		</script>
		<?
		$script = ob_get_contents();
		ob_end_clean();
		return  $script;
	}

	protected static function addFilterByE($property, $controlSettings, &$filter, &$filtered)
	{
		$filtered = false;

		$filterOption = new \Bitrix\Main\UI\Filter\Options($controlSettings["FILTER_ID"]);
		$filterData = $filterOption->getFilter();
		if(!empty($filterData[$controlSettings['VALUE']]))
			$currentValue = $filterData[$controlSettings['VALUE']];

		if(!empty($currentValue))
		{
			try
			{
				$values = array();
				global $APPLICATION;
				$currentValue = $APPLICATION->ConvertCharset($currentValue, 'UTF-8', LANG_CHARSET);
				$currentValues = Json::decode($currentValue);
				if(is_array($currentValues))
				{
					foreach($currentValues as $value)
					{
						$values[] = current($value);
					}
				}
				else
				{
					$values[] = $currentValues;
				}
				if(!empty($values))
				{
					$filter[$controlSettings['VALUE']] = array();
					foreach($values as $value)
					{
						$filter[$controlSettings['VALUE']][] = intval($value);
					}
					$filtered = true;
				}
			}
			catch(SystemException $e)
			{
				return;
			}
		}
	}
}