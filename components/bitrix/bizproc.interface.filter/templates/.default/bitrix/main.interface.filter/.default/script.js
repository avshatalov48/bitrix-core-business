if(typeof(BX.BizprocParamBag) === "undefined")
{
	BX.BizprocParamBag = function()
	{
		this._params = {};
	};

	BX.BizprocParamBag.prototype =
	{
		initialize: function(params)
		{
			this._params = params ? params : {};
		},
		getParam: function(name, defaultvalue)
		{
			var p = this._params;
			return typeof(p[name]) != "undefined" ? p[name] : defaultvalue;
		},
		setParam: function(name, value)
		{
			this._params[name] = value;
		},
		clear: function()
		{
			this._params = {};
		}
	};

	BX.BizprocParamBag.create = function(params)
	{
		var self = new BX.BizprocParamBag();
		self.initialize(params);
		return self;
	}
}
if(typeof(BX.InterfaceGridFilter) === "undefined")
{
	BX.InterfaceGridFilter = function()
	{
		this._defaultItemId = "";
		this._id = "";
		this._settings = null;
		this._itemInfos = {};
		this._fieldInfos = {};
		this._visibleFieldCount = 0;
		this._items = {};
		this._fields = {};
		this._addFieldOpener = null;
		this._settingsOpener = null;
		this._ignoreFieldVisibilityChange = false;
		this._isApplied = false;
		this._currentItemId = '';
		this._activeItemId = '';
		this._saveAsDlg = null;
		this._manager = null;
		this._isFolded = false;
		this._presetsDeleted = [];
		this._saveVisibleFieldsTimeoutId = null;
		this._closeOpen = null;
	};

	BX.InterfaceGridFilter.prototype =
	{
		initialize: function(id, settings)
		{
			this._id = BX.type.isNotEmptyString(id) ? id : "";
			this._settings = settings ? settings : BX.BizprocParamBag.create(null);
			this._isApplied = this._settings.getParam("isApplied", false);
			this._isFolded = this._settings.getParam("isFolded", true);
			this._presetsDeleted = this._settings.getParam("presetsDeleted", []);

			this._defaultItemId = this._settings.getParam("defaultItemId", "filter_default");
			this._currentItemId = this._settings.getParam("currentItemId", "");
			this._activeItemId = this._currentItemId;
			if(this._activeItemId === "")
			{
				this._activeItemId = this._defaultItemId;
			}

			this._itemInfos = this._settings.getParam("itemInfos", {});

			var currentValues =  this._settings.getParam("currentValues", {});
			if(BX.type.isArray(currentValues))
			{
				//hack for empty values
				currentValues = {};
			}

			var isActive = this._activeItemId === this._defaultItemId;
			this._items[this._defaultItemId] = BX.InterfaceGridFilterItem.create(
					this._defaultItemId,
					BX.BizprocParamBag.create(
						{
							"filter": this,
							"info":
							{
								"name": "",
								"fields": isActive ? currentValues : {},
								"filter_rows": this._settings.getParam("defaultVisibleRows", "")
							},
							"isActive": isActive
						}
					)
			);

			for(var itemId in this._itemInfos)
			{
				if(!this._itemInfos.hasOwnProperty(itemId) || itemId === this._defaultItemId || this.isDeleletedPreset(itemId))
				{
					continue;
				}

				this._items[itemId] = BX.InterfaceGridFilterItem.create(
					itemId,
					BX.BizprocParamBag.create(
						{
							"filter": this,
							"info": this._itemInfos[itemId],
							"isActive": this._activeItemId === itemId
						}
					)
				);
			}

			this._fieldInfos = this._settings.getParam('fieldInfos', {});
			for(var fieldId in this._fieldInfos)
			{
				if(!this._fieldInfos.hasOwnProperty(fieldId))
				{
					continue;
				}

				var f =
					BX.InterfaceGridFilterField.create(
						fieldId,
						BX.BizprocParamBag.create({ "filter": this, "info": this._fieldInfos[fieldId] })
					);

				this._fields[fieldId] = f;

				if(f.isVisible())
				{
					this._visibleFieldCount++;
				}
			}

			BX.bind(this.getAddFieldButton(), 'click', BX.delegate(this._handleAddFieldButtonClick, this));
			BX.bind(this.getSettingsButton(), 'click', BX.delegate(this._handleSettingsButtonClick, this));
			BX.bind(this.getApplyButton(), 'click', BX.delegate(this._handleApplyButtonClick, this));
			BX.bind(this.getCancelButton(), 'click', BX.delegate(this._handleCancelButtonClick, this));
			BX.bind(this.getAddFilterButton(), 'click', BX.delegate(this._handleSaveAsMenuItemClick, this));

			this._closeOpen = new BX.InterfaceGridFilterCloseOpen(
				this._settings.getParam("innerBlock", ""),
				this._settings.getParam("mainBlock", ""),
				this
			);
			this._closeOpen.initialize();

			var switchBtn = this.getSwitchViewButton();
			if(switchBtn)
			{
				switchBtn.title = this.getMessage(this._isFolded ? 'buttonMaximize' : 'buttonMinimize');
				BX.bind(switchBtn, 'click', BX.delegate(this._handleSwitchViewButtonClick, this));
			}

			//this._manager = BX.CrmInterfaceGridManager.items[this.getGridId() + '_MANAGER'];
			//if(this._manager)
			//{
			//	this._initializeFieldControllers();
			//}
			//else
			//{
			//	this._manager = null;
			//	BX.addCustomEvent(
			//		BX.CrmInterfaceGridManager,
			//		'CREATED',
			//		BX.delegate(this._onManagerCreated, this)
			//	);
			//}
		},


		_onManagerCreated: function(manager)
		{
			if(manager.getId() === this.getGridId() + '_MANAGER')
			{
				this._manager = manager;
			}

			this._initializeFieldControllers();
		},
		_initializeFieldControllers: function()
		{
			for(var id in this._fields)
			{
				if(this._fields.hasOwnProperty(id))
				{
					this._fields[id].initializeController();
				}
			}
		},
		getMessage: function(id)
		{
			return BX.InterfaceGridFilter.getMessage(id);
		},
		getId: function()
		{
			return this._id;
		},
		isApplied: function()
		{
			return this._isApplied;
		},
		isFolded: function()
		{
			return this._isFolded;
		},
		getCurrentItemId: function()
		{
			return this._currentItemId;
		},
		getGridId: function()
		{
			return this._settings.getParam("gridId", "");
		},
		getFormName: function()
		{
			return this._settings.getParam("formName", "");
		},
		getForm: function()
		{
			return document.forms[this.getFormName()];
		},
		getCurrentTime: function()
		{
			return this._settings.getParam("currentTime", "");
		},
		getServiceUrl: function()
		{
			return this._settings.getParam("serviceUrl", "");
		},
		getContainerId: function()
		{
			return this._settings.getParam("containerId", "flt_wrapper");
		},
		getItemContainerId: function(id)
		{
			return this._settings.getParam("itemContainerPrefix", "flt_tab_") + id.toString();
		},
		getFieldContainerId: function(id)
		{
			return this._settings.getParam("fieldContainerPrefix", "flt_field_") + id.toString();
		},
		getFieldDelimiterContainerId: function(id)
		{
			return this._settings.getParam("fieldDelimiterContainerPrefix", "flt_field_delim_") + id.toString();
		},
		getVisibleFieldCount: function()
		{
			return this._visibleFieldCount;
		},
		getVisibleFieldIds: function()
		{
			var ary = [];
			var fields = this._fields;
			for(var id in fields)
			{
				if(fields.hasOwnProperty(id) && fields[id].isVisible())
				{
					ary.push(id);
				}
			}
			return ary;
		},
		getFieldInfo: function(fieldId)
		{
			if(!this._manager)
			{
				return null;
			}

			var infos = this._manager.getSetting('filterFields', null);
			if(!infos)
			{
				return null;
			}

			for(var i = 0; i < infos.length; i++)
			{
				if(infos[i]["id"] === fieldId)
				{
					return infos[i];
				}
			}

			return null;
		},
		saveVisibleFields: function()
		{
			if(this._saveVisibleFieldsTimeoutId !== null)
			{
				window.clearTimeout(this._saveVisibleFieldsTimeoutId);
				this._saveVisibleFieldsTimeoutId = null;
			}
			var self = this;
			this._saveVisibleFieldsTimeoutId = window.setTimeout(function(){ self._doSaveVisibleFields(); }, 100);
		},
		_doSaveVisibleFields: function()
		{
			this._saveVisibleFieldsTimeoutId = null;
			BX.ajax.get(
				this.getServiceUrl(),
				{
					"GRID_ID": this.getGridId(),
					"action": "filterrows",
					"filter_id": this._activeItemId !== this._defaultItemId ? this._activeItemId : "",
					"rows": this.getVisibleFieldIds().join(",")
				}
			);
		},
		saveActiveItem: function(callback)
		{
			var activeItem = this._items[this._activeItemId];
			if(!activeItem)
			{
				return;
			}

			BX.ajax.post(
				this.getServiceUrl(),
				{
					"GRID_ID": this.getGridId(),
					"filter_id": activeItem.getId(),
					"action": "savefilter",
					"name": activeItem.getName(),
					'fields': this.getFieldParams(),
					"rows": this.getVisibleFieldIds().join(",")
				},
				callback
			);
		},
		isDeleletedPreset: function(id)
		{
			for(var i = 0; i < this._presetsDeleted.length; i++)
			{
				if(this._presetsDeleted[i] === id)
				{
					return true;
				}
			}

			return false;
		},
		deleteActiveItem: function()
		{
			var activeItem = this._items[this._activeItemId];
			if(!activeItem)
			{
				return;
			}

			var itemId = activeItem.getId();
			BX.ajax.post(
				this.getServiceUrl(),
				{
					"GRID_ID": this.getGridId(),
					"filter_id": itemId,
					"action": "delfilter"
				}
			);

			if((/^filter_[0-9]+$/i).test(itemId) || this.isDeleletedPreset(itemId))
			{
				//is user filter or already registred
				return;
			}

			this._presetsDeleted.push(itemId);

			var d = BX.userOptions.delay;
			BX.userOptions.delay = 100;
			BX.userOptions.save("bizproc.interface.grid.filter", this.getId().toLowerCase(), "presetsDeleted", this._presetsDeleted.join(','));
			BX.userOptions.delay = d;
		},
		requireFieldVisibilityChange: function(field)
		{
			if(this._ignoreFieldVisibilityChange)
			{
				return true;
			}

			return !field.isVisible() || this.getVisibleFieldCount() > 1;
		},
		handleFieldVisibilityChange: function(field)
		{
			if(this._ignoreFieldVisibilityChange)
			{
				return;
			}

			//Sync visible row count
			if(field.isVisible())
			{
				this._visibleFieldCount++;
			}
			else
			{
				this._visibleFieldCount--;
			}

			this._adjustStyle();
			this._showDeleteButtons(this._visibleFieldCount > 1);
			this.saveVisibleFields();
		},
		requireItemActivityChange: function(item)
		{
			return true;
		},
		handleItemActivityChange: function(item)
		{
			if(!item.isActive())
			{
				return;
			}

			this._setActiveItem(item);
		},
		handleSaveAsDialogClose: function(dialog)
		{
			if(dialog.getButtonId() !== "save")
			{
				return;
			}

			var dlgValues = dialog.getValues();
			var itemId = "filter_" + Math.random().toString().substring(2).toString();
			var itemInfo =
			{
				"name": dlgValues["name"] ? dlgValues["name"] : BX.InterfaceGridFilter.getMessage("defaultFilterName"),
				"fields": this.getFieldParams()
			};

			this._itemInfos[itemId] = itemInfo;

			var item = this._items[itemId] = BX.InterfaceGridFilterItem.create(
				itemId,
				BX.BizprocParamBag.create({ "filter": this, "info": itemInfo, "isActive": false })
			);

			item.setActive(true);
			this.saveActiveItem();
		},
		getFieldParams: function()
		{
			var params = {};
			for(var id in this._fields)
			{
				if(this._fields.hasOwnProperty(id))
				{
					this._fields[id].getParams(params);
				}
			}
			return params;
		},
		setFieldParams: function(params)
		{
			for(var id in this._fields)
			{
				if(this._fields.hasOwnProperty(id))
				{
					this._fields[id].setParams(params);
				}
			}
		},
		_setActiveItem: function(item)
		{
			var activeItem = null;
			if(this._activeItemId !== "")
			{
				activeItem = this._items[this._activeItemId];
			}

			if(activeItem)
			{
				activeItem.setFieldParams(this.getFieldParams());
				activeItem.setVisibleFieldIds(this.getVisibleFieldIds());
				activeItem.setActive(false);
			}

			this._activeItemId = item.getId();
			activeItem = this._items[this._activeItemId];

			var wrapper = this._getWrapper();
			if(activeItem.isCurrent())
			{
				if (this.isApplied())
					BX.addClass(wrapper, "bx-current-filter");
			}
			else
			{
				BX.removeClass(wrapper, "bx-current-filter");
			}

			this.setFieldParams(activeItem.getFieldParams());

			this._ignoreFieldVisibilityChange = true;
			this._visibleFieldCount = 0;

			var visibleFieldIds = activeItem.getVisibleFieldIds();
			if(visibleFieldIds.length > 0)
			{
				for(var fieldId in this._fields)
				{
					if(!this._fields.hasOwnProperty(fieldId))
					{
						continue;
					}

					var isVisible = false;
					for(var i = 0; i < visibleFieldIds.length; i++)
					{
						if(visibleFieldIds[i] === fieldId)
						{
							isVisible = true;
							break;
						}
					}

					this._fields[fieldId].setVisible(isVisible);
					if(isVisible)
					{
						this._visibleFieldCount++;
					}
				}
			}
			else
			{
				// Display saved visible fields only
				for(var fieldId in this._fieldInfos)
				{
					if(!this._fieldInfos.hasOwnProperty(fieldId))
					{
						continue;
					}

					var isVisible = this._fieldInfos[fieldId]["isVisible"];
					this._fields[fieldId].setVisible(isVisible);
					if(isVisible)
					{
						this._visibleFieldCount++;
					}
				}
			}

			this._adjustStyle();
			this._showDeleteButtons(this._visibleFieldCount > 1);
			this._ignoreFieldVisibilityChange = false;
		},
		apply: function(itemId)
		{
			var item = this._items[itemId];
			if(item)
			{
				this._setActiveItem(item);
				this.applyActive();
			}
		},
		applyActive: function()
		{
			var prevCurrentItemId = this._currentItemId;
			this._currentItemId = this._activeItemId;
			if(prevCurrentItemId !== "")
			{
				this._items[prevCurrentItemId].handleCurrentItemChange(this);
			}
			this._items[this._currentItemId].handleCurrentItemChange(this);

			var f = this.getForm();
			if(!f)
			{
				return;
			}

			var filter = BX.findChild(
				f,
				{ "tag": "INPUT", "property": { "type":"hidden", "name": "grid_filter_id" } },
				true,
				false
			);

			if(filter)
			{
				filter.value = this._activeItemId !== this._defaultItemId ? this._activeItemId : "";
			}

			var apply = BX.findChild(
				f,
				{ "tag": "INPUT", "property": { "type":"hidden", "name": "apply_filter" } },
				true,
				false
			);

			if(apply)
			{
				apply.value = "Y";
			}

			BX.submit(f);
		},
		clear: function()
		{
			var f = this.getForm();
			if(!f)
			{
				return;
			}

			var clear = BX.findChild(
				f,
				{ "tag": "INPUT", "property": { "type":"hidden", "name": "clear_filter" } },
				true,
				false
			);

			if(clear)
			{
				clear.value = "Y";
			}
			
			this.setFieldParams({});
			BX.submit(f);
		},
		_adjustStyle: function()
		{
			var container = BX(this.getContainerId());
			if(!container)
			{
				return;
			}

			var bottomSeparator = BX.findChild(container, {"class": "bx-filter-bottom-separate"}, true, false);
			var content = BX.findChild(container, {"class": "bx-filter-content"}, true, false);


			if(this.getVisibleFieldCount() > 1)
			{
				BX.removeClass(content, "bx-filter-content-first");
				bottomSeparator.style.display = '';
			}
			else
			{
				BX.addClass(content, "bx-filter-content-first");
				bottomSeparator.style.display = 'none';
			}
		},
		_getWrapper: function()
		{
			return BX.findChild(
				BX(this.getContainerId()),
				{
					"tag": "DIV",
					"class": "bx-filter-wrap"
				},
				true
			);
		},
		getAddFieldButton: function()
		{
			return BX.findChild(
				BX(this.getContainerId()),
				{ "tag": "SPAN", "class": "bx-filter-add-button" },
				true,
				false
			);
		},
		getAddFilterButton: function()
		{
			return BX.findChild(
				BX(this.getContainerId()),
				{ "tag": "SPAN", "class": "bx-filter-add-tab" },
				true,
				false
			);
		},
		getSettingsButton: function()
		{
			return BX.findChild(
				BX(this.getContainerId()),
				{ "tag": "SPAN", "class": "bx-filter-setting" },
				true,
				false
			);
		},
		getApplyButton: function()
		{
			return BX.findChild(
				BX(this.getContainerId()),
				{ "tag": "INPUT", "property": { "type":"button", "name": "set_filter" } },
				true,
				false
			);
		},
		getCancelButton: function()
		{
			return BX.findChild(
				BX(this.getContainerId()),
				{ "tag": "INPUT", "property": { "type":"button", "name": "reset_filter" } },
				true,
				false
			);
		},
		getSwitchViewButton: function()
		{
			return BX.findChild(
				BX(this.getContainerId()),
				{ "tag": "SPAN", "class": "bx-filter-switcher-tab" },
				true,
				false
			);
		},
		setFieldsVisible: function(visible, options)
		{
			this._ignoreFieldVisibilityChange = true;

			this._visibleFieldCount = 0;
			visible = !!visible;
			options = options ? options : {};
			var skipTop = typeof(options["skipTop"]) !== "undefined" ? parseInt(options["skipTop"]) : 0;
			var fields = this._fields;
			for(var id in fields)
			{
				if(!fields.hasOwnProperty(id))
				{
					continue;
				}

				var f = fields[id];

				if(f.isVisible() !== visible)
				{
					if(skipTop > 0)
					{
						skipTop--;
					}
					else
					{
						f.setVisible(visible);
					}
				}

				if(f.isVisible())
				{
					this._visibleFieldCount++;
				}
			}

			this._adjustStyle();
			this._showDeleteButtons(this._visibleFieldCount > 1);
			this.saveVisibleFields();
			this._ignoreFieldVisibilityChange = false;
		},
		setFolded: function(folded)
		{
			folded = !!folded;
			if(folded === this._isFolded)
			{
				return;
			}

			this._isFolded = folded;

			if (this._closeOpen)
				this._closeOpen.toggle();

			var mainWrapperBlock = BX(this._settings.getParam("mainBlock", ""));
			if(folded)
			{
				BX.addClass(this._getWrapper(), "bx-filter-folded");
				if (mainWrapperBlock)
					mainWrapperBlock.style.height = "0";
			}
			else
			{
				BX.removeClass(this._getWrapper(), "bx-filter-folded");
			}

			if(this._activeItemId !== "")
			{
				var activeItem = this._items[this._activeItemId];
				if(activeItem)
				{
					activeItem.handleFilterFoldingChange(this);
				}
			}

			var switchBtn = this.getSwitchViewButton();
			if(switchBtn)
			{
				switchBtn.title = this.getMessage(folded ? 'buttonMaximize' : 'buttonMinimize');
			}

			var d = BX.userOptions.delay;
			BX.userOptions.delay = 100;
			BX.userOptions.save("bizproc.interface.grid.filter", this.getId().toLowerCase(), "isFolded", (folded ? "Y" : "N"));
			BX.userOptions.delay = d;
		},
		_showDeleteButtons: function(show)
		{
			show = !!show;
			var fields = this._fields;
			for(var id in fields)
			{
				if(fields.hasOwnProperty(id))
				{
					fields[id].showDeleteButton(show);
				}
			}
		},
		_handleAddFieldButtonClick: function(e)
		{
			var menuItems =[];
			var fields = this._fields;
			for(var id in fields)
			{
				if(!fields.hasOwnProperty(id))
				{
					continue;
				}

				var f = fields[id];
				menuItems.push(
					{
						"id": f.getId(),
						"text": f.getName(),
						"onchange": f.getToggleHandler(),
						"checked": f.isVisible()
					}
				);
			}

			menuItems.push(
				{
					"id": "__showAll",
					"text": this.getMessage('showAll'),
					"onclick": BX.delegate(this._handleShowAllButtonClick, this),
					"checked": false,
					"allowToggle": false,
					"separatorBefore": true
				}
			);

			menuItems.push(
				{
					"id": "__hideAll",
					"text": this.getMessage('hideAll'),
					"onclick": BX.delegate(this._handleHideAllButtonClick, this),
					"checked": false,
					"allowToggle": false
				}
			);

			var btn = this.getAddFieldButton();
			var btnPos = BX.pos(btn);
			this._addFieldOpener = BX.InterfaceGridFilterCheckListMenu.create(
				this.getId() + "_ADD_FIELDS",
				BX.BizprocParamBag.create(
					{
						"allowToggle": true,
						"items": menuItems,
						"anchor": btn,
						"offsetTop": Math.round(btnPos.height / 4),
						"offsetLeft": Math.round(btnPos.width / 2),
						"angle": { "position": "top", "offset": 0 }
					}
				)
			);

			this._addFieldOpener.open();
		},
		_handleSettingsButtonClick: function(e)
		{
			var menuItems =[];
			if(this._activeItemId == this._defaultItemId)
			{
				menuItems.push(
					{
						"id": "saveAs",
						"text": this.getMessage("saveAs"),
						"onclick": BX.delegate(this._handleSaveAsMenuItemClick, this),
						"checked": false
					}
				);
			}
			else
			{
				menuItems.push(
					{
						"id": "save",
						"text": this.getMessage("save"),
						"onclick": BX.delegate(this._handleSaveMenuItemClick, this),
						"checked": false
					}
				);

				menuItems.push(
					{
						"id": "saveAs",
						"text": this.getMessage("saveAs"),
						"onclick": BX.delegate(this._handleSaveAsMenuItemClick, this),
						"checked": false
					}
				);

				menuItems.push(
					{
						"id": "delete",
						"text": this.getMessage("delete"),
						"onclick": BX.delegate(this._handleDeleteMenuItemClick, this),
						"checked": false
					}
				);
			}

			var btn = this.getSettingsButton();
			var btnPos = BX.pos(btn);
			this._settingsOpener = BX.InterfaceGridFilterCheckListMenu.create(
				this.getId() + "_SETTINGS_" + this._activeItemId.toUpperCase(),
				BX.BizprocParamBag.create(
					{
						"allowToggle": false,
						"items": menuItems,
						"anchor": btn,
						"closeOnClick" : true,
						"offsetTop": Math.round(btnPos.height / 4),
						"offsetLeft": Math.round(btnPos.width / 2),
						"angle": { "position": "top", "offset": 0 }
					}
				)
			);

			this._settingsOpener.open();
		},
		_handleApplyButtonClick: function(e)
		{
			if(this._activeItemId === this._defaultItemId)
			{
				this.applyActive();
				return;
			}
			var self = this;
			this.saveActiveItem(function() { self.applyActive(); });
		},
		_handleCancelButtonClick: function(e)
		{
			this.clear();
		},
		_handleShowAllButtonClick: function(e)
		{
			this.setFieldsVisible(true, {});
		},
		_handleHideAllButtonClick: function(e)
		{
			this.setFieldsVisible(false, { "skipTop": 1 });
		},
		_handleSaveMenuItemClick: function(e)
		{
			if(this._isApplied && this._currentItemId === this._activeItemId)
			{
				// We have to apply active filter again for enable saved filter after page refresh
				var self = this;
				this.saveActiveItem(function() { self.applyActive(); });
			}
			else
			{
				this.saveActiveItem();
			}
		},
		_handleSaveAsMenuItemClick: function(e)
		{
			if(!this._saveAsDlg)
			{
				this._saveAsDlg = BX.InterfaceGridFilterSaveAsDialog.create(
					this.getId() + "_SAVE_AS",
					BX.BizprocParamBag.create(
						{
							"filter": this
						}
					)
				);
			}
			this._saveAsDlg.openDialog();
		},
		_handleDeleteMenuItemClick: function(e)
		{
			var id = this._activeItemId;
			var item = this._items[id];
			if(id === this._defaultItemId || !item)
			{
				return;
			}

			this.deleteActiveItem();

			if(item.isCurrent())
			{
				this.clear();
			}

			item.clearLayout();
			this._items[this._defaultItemId].setActive(true);
			delete this._items[id];
		},
		_handleSwitchViewButtonClick: function(e)
		{
			this.setFolded(!this.isFolded());
		}
	};

	BX.InterfaceGridFilter.isEmptyObject = function(obj)
	{
		if(obj === null || obj === undefined)
		{
			return true;
		}

		var hasOwnProperty = Object.prototype.hasOwnProperty;
		if(typeof(obj.length) !== "undefined")
		{
			return obj.length === 0;
		}

		if(typeof(obj) === "object")
		{
			for(var key in obj)
			{
				if(hasOwnProperty.call(obj, key))
				{
					return false;
				}
			}
		}
		return true;
	};

	if(typeof(BX.InterfaceGridFilter.messages) === "undefined")
	{
		BX.InterfaceGridFilter.messages = {};
	}

	BX.InterfaceGridFilter.getMessage = function(id)
	{
		return typeof(BX.InterfaceGridFilter.messages[id]) !== "undefined"
			? BX.InterfaceGridFilter.messages[id] : '';
	};

	BX.InterfaceGridFilter.items = {};
	BX.InterfaceGridFilter.create = function(id, settings)
	{
		var self = new BX.InterfaceGridFilter();
		self.initialize(id, settings);
		this.items[id] = self;
		return self;
	};
}

if(typeof(BX.InterfaceGridFilterItem) === "undefined")
{
	BX.InterfaceGridFilterItem = function()
	{
		this._id = "";
		this._settings = null;
		this._filter = null;
		this._container = null;
		this._isActive = false;
		this._info = {};
		this._fieldParams = {};
		this._visibleFieldIds = [];
	};

	BX.InterfaceGridFilterItem.prototype =
	{
		initialize: function(id, settings)
		{
			this._id = BX.type.isNotEmptyString(id) ? id : "";
			this._settings = settings ? settings : BX.BizprocParamBag.create(null);
			this._filter = settings.getParam("filter", null);

			this._info = settings.getParam("info", {});
			this._fieldParams = typeof(this._info["fields"]) !== "undefined" ? this._info["fields"] : {};

			var visibleFieldIds = typeof(this._info["filter_rows"]) !== "undefined" ? this._info["filter_rows"] : null;
			if(BX.type.isString(visibleFieldIds))
			{
				visibleFieldIds = visibleFieldIds.split(",");
			}

			if(BX.type.isArray(visibleFieldIds))
			{
				this._visibleFieldIds = visibleFieldIds;
			}
			else
			{
				for(var fieldId in this._fieldParams)
				{
					if(this._fieldParams.hasOwnProperty(fieldId))
					{
						this._visibleFieldIds.push(BX.InterfaceGridFilterField.convertParamToFieldId(fieldId));
					}
				}
			}

			this._isActive = settings.getParam("isActive", false);
			var containerId = this._filter.getItemContainerId(id);

			this._container = BX(containerId);
			if(!this._container)
			{
				this.layout();
			}

			BX.bind(this._container, "click", BX.delegate(this._onClick, this));

		},
		getId: function()
		{
			return this._id;
		},
		getName: function()
		{
			return this._info["name"];
		},
		getInfo: function()
		{
			return this._info;
		},
		isActive: function()
		{
			return this._isActive;
		},
		isCurrent: function()
		{
			return this._filter.getCurrentItemId() === this.getId();
		},
		setActive: function(active)
		{
			active = !!active;
			if(this._isActive === active)
			{
				return;
			}

			if(!this._filter.requireItemActivityChange(this))
			{
				return;
			}

			this._isActive = active;
			this._filter.handleItemActivityChange(this);
			this._adjustStyle();
		},
		getFieldParams: function()
		{
			return this._fieldParams;
		},
		setFieldParams: function(params)
		{
			this._fieldParams = params;
		},
		getVisibleFieldIds: function()
		{
			return this._visibleFieldIds;
		},
		setVisibleFieldIds: function(fieldIds)
		{
			this._visibleFieldIds = fieldIds;
		},
		layout: function()
		{
			var wrapper = BX.findChild(
				BX(this._filter.getContainerId()),
				{
					"tag": "DIV",
					"class": "bx-filter-tabs-block"
				},
				true
			);

			if(!wrapper)
			{
				return;
			}

			this._container = BX.create(
				"SPAN",
				{
					"props": { "id": this._filter.getItemContainerId(this.getId()) },
					"attrs": { "class": "bx-filter-tab" },
					"text": this.getName()
				}
			);

			wrapper.insertBefore(
				this._container,
				this._filter.getAddFilterButton()
			);
		},
		clearLayout: function()
		{
			if(!this._container)
			{
				return;
			}

			BX.remove(this._container);
		},
		_adjustStyle: function()
		{
			if(this._filter.isFolded())
			{
				if(this.isCurrent())
				{
					BX.addClass(this._container, "bx-filter-tab-active");
				}
				else
				{
					BX.removeClass(this._container, "bx-filter-tab-active");
				}
			}
			else
			{
				if(this._isActive)
				{
					BX.addClass(this._container, "bx-filter-tab-active");
				}
				else
				{
					BX.removeClass(this._container, "bx-filter-tab-active");
				}
			}
		},
		handleFilterFoldingChange: function(filter)
		{
			this._adjustStyle();
		},
		handleCurrentItemChange: function(filter)
		{
			this._adjustStyle();
		},
		_onClick: function(e)
		{
			if(!this.isActive())
			{
				this.setActive(true);
			}

			if(this._filter.isFolded())
			{
				this._filter.applyActive();
			}
		}
	};

	BX.InterfaceGridFilterItem.create = function(id, settings)
	{
		var self = new BX.InterfaceGridFilterItem();
		self.initialize(id, settings);
		return self;
	}
}

if(typeof(BX.InterfaceGridFilterField) === "undefined")
{
	BX.InterfaceGridFilterField = function()
	{
		this._id = '';
		this._settings = null;
		this._filter = null;
		this._info = {};
		this._container = null;
		this._delimiterContainer = null;
		this._isVisible = true;
		this._deleteButton = null;
		this._controller = null;
	};

	BX.InterfaceGridFilterField.prototype =
	{
		initialize: function(id, settings)
		{
			this._id = BX.type.isNotEmptyString(id) ? id : '';
			this._settings = settings ? settings : BX.BizprocParamBag.create(null);
			this._filter = settings.getParam("filter", null);
			this._container = BX(this._filter.getFieldContainerId(id));
			this._delimiterContainer = BX(this._filter.getFieldDelimiterContainerId(id));
			this._info = settings.getParam("info", {});
			this._isVisible = this._info["isVisible"];

			this._deleteButton = BX.findChild(
				this._container,
				{ "tag": "SPAN", "class": "bx-filter-item-delete" }, true, false
			);

			if(this._deleteButton)
			{
				this._deleteButton.title = BX.InterfaceGridFilter.getMessage('buttonDeleteField');
				BX.bind(this._deleteButton, "click", BX.delegate(this._handleDeleteButtonClick, this));
			}

			if(this.getType() === "date")
			{
				this._controller = BX.InterfaceGridFilterDate.create(
					BX.BizprocParamBag.create(
						{
							"containerId": this._filter.getFieldContainerId(id),
							"formName": this._filter.getFormName(),
							"currentTime": this._filter.getCurrentTime()
						}
					)
				);
			}

			if(this._controller)
			{
				this._controller.layout();
			}

			this._handleVisibilityChange();
		},
		initializeController: function()
		{
			if(this.getType() !== "custom")
			{
				return;
			}

			var info = this._filter.getFieldInfo(this._id);
			if(info && info["typeName"] === "USER")
			{
				this._controller = BX.InterfaceGridFilterUser.create(
					BX.BizprocParamBag.create(
						{
							"containerId": this._filter.getFieldContainerId(this._id),
							"info": info
						}
					)
				);

				if(this._controller)
				{
					this._controller.layout();
				}
			}
		},
		getId: function()
		{
			return this._id;
		},
		getName: function()
		{
			return this._info["name"];
		},
		getType: function()
		{
			return this._info["type"];
		},
		isVisible: function()
		{
			return this._isVisible;
		},
		getToggleHandler: function()
		{
			return BX.delegate(this._handleToggleButtonClick, this);
		},
		_handleToggleButtonClick: function(e)
		{
			this.toggle();
		},
		_handleDeleteButtonClick: function(e)
		{
			this.setVisible(false);
		},
		_handleVisibilityChange: function()
		{
			var elements = BX.findChildren(
				this._container,
				{ "tag": /^INPUT|SELECT|TEXTAREA/i },
				true
			);

			if(!BX.type.isArray(elements))
			{
				return;
			}

			var v = !this._isVisible;
			for(var i = 0; i < elements.length; i++)
			{
				var el = elements[i];
				var name = el.name;
				if(name === "")
				{
					continue;
				}

				el.disabled = v;
			}
		},
		setVisible: function(visible)
		{
			visible = !!visible;

			if(this._isVisible === visible)
			{
				return;
			}

			if(!this._filter.requireFieldVisibilityChange(this))
			{
				return;
			}

			this._container.style.display = visible ? "" : "none";

			if(this._delimiterContainer)
			{
				this._delimiterContainer.style.display = visible ? "" : "none";
			}

			this._isVisible = visible;
			this._handleVisibilityChange();
			this._filter.handleFieldVisibilityChange(this);
		},
		toggle: function()
		{
			this.setVisible(!this._isVisible);
		},
		showDeleteButton: function(show)
		{
			if(this._deleteButton)
			{
				this._deleteButton.style.display = !!show ? "" : "none";
			}
		},
		getParams: function(params)
		{
			if(!this.isVisible())
			{
				return;
			}

			// Check if controller is able to prepare params
			if(this._controller && this._controller.tryGetParams(params))
			{
				return;
			}

			var elements = BX.findChildren(
				this._container,
				{ "tag": /^INPUT|SELECT|TEXTAREA/i },
				true
			);

			for(var i = 0; i < elements.length; i++)
			{
				var el = elements[i];
				var name = el.name;
				if(name === "")
				{
					continue;
				}

				switch (el.type.toLowerCase())
				{
					case 'select-one':
					case 'text':
					case 'textarea':
					case 'hidden':
					{
						params[name] = el.value;
						break;
					}
					case 'radio':
					{
						if (el.checked)
						{
							params[name] = el.value;
						}
						break;
					}
					case 'checkbox':
					{
						params[name] = el.checked ? el.value : false;
						break;
					}
					case 'select-multiple':
					{
						name = name.substr(0, name.length - 2);
						params[name] = {};
						for(var j = 0; j < el.options.length; j++)
						{
							if(el.options[j].selected && el.options[j].value)
							{
								params[name]['sel' + el.options[j].value] = el.options[j].value;
							}
						}
						break;
					}
				}
			}
		},
		setParams: function(params)
		{
			// Check if controller is able to prepare params
			if(this._controller && this._controller.trySetParams(params))
			{
				return;
			}

			var elements = BX.findChildren(
				this._container,
				{ "tag": /^INPUT|SELECT|TEXTAREA/i },
				true
			);

			var changed = false;
			for(var i = 0; i < elements.length; i++)
			{
				var el = elements[i];
				var name = el.name;
				if(name === "")
				{
					continue;
				}

				var elementChanged = false;
				var v = typeof(params[name]) !== "undefined" ? params[name] : null;
				switch(el.type.toLowerCase())
				{
					case 'select-one':
					case 'text':
					case 'textarea':
						el.value = v !== null ? v : '';
						elementChanged = true;
						break;
					case 'select-multiple':
					{
						name = name.substr(0, name.length - 2);
						v = typeof(params[name]) === 'object' && params[name] ? params[name] : {};
						if(v === null)
						{
							for(var k = 0; k < el.options.length; k++)
							{
								el.options[k].selected = false;
							}
						}
						else
						{
							var selected = false;
							for(var j = 0; j < el.options.length; j++)
							{
								var option = el.options[j];
								var key = 'sel' + option.value;
								var sel = v[key] ? v[key] : null;
								option.selected = option.value == sel;
								if(option.value == sel)
								{
									selected = true;
								}
							}
							if(!selected && el.options.length > 0 && el.options[0].value == '')
							{
								el.options[0].selected = true;
							}
						}
						elementChanged = true;
						break;
					}
					case 'radio':
					case 'checkbox':
						{
							el.checked = v !== null ? v : false;
						}
						elementChanged = true;
						break;
				}


				if(elementChanged)
				{
					if(!changed)
					{
						changed = true;
					}

					if(BX.type.isFunction(el.onchange))
					{
						try
						{
							el.onchange();
						}
						catch(ex)
						{
						}
					}
				}
			}

			if(changed && this._controller
				&& BX.type.isFunction(this._controller.handleParamsChange))
			{
				this._controller.handleParamsChange();
			}
		}
	};

	BX.InterfaceGridFilterField.convertParamToFieldId = function(paramName)
	{
		return paramName.replace(/_[a-z]+$/, "");
	};

	BX.InterfaceGridFilterField.create = function(id, settings)
	{
		var self = new BX.InterfaceGridFilterField();
		self.initialize(id, settings);
		return self;
	}
}

if(typeof(BX.InterfaceGridFilterDate) === "undefined")
{
	BX.InterfaceGridFilterDate = function()
	{
		this._settings = null;
		this._container = null;
	};

	BX.InterfaceGridFilterDate.prototype =
	{
		initialize: function(settings)
		{
			this._settings = settings ? settings : BX.BizprocParamBag.create(null);
			this._container = BX(settings.getParam("containerId"));

			var intervalSelect = BX.findChild(
				this._container,
				{ "tag": "SELECT", "class": "bx-filter-date-interval-select" },
				true,
				false
			);

			if(intervalSelect)
			{
				BX.bind(intervalSelect, 'change', BX.delegate(this._onIntervalChange, this));
			}

			BX.bind(
				BX.findChild(
					BX.findChild(
						this._container,
						{ "tag": "DIV", "class": "bx-filter-date-from" }, true, false
					),
					{ "tag": "SPAN", "class": "bx-calendar-icon" }, true, false),
				'click',
				BX.delegate(this._onDataFromClick, this)
			);

			BX.bind(
				BX.findChild(
					BX.findChild(
						this._container,
						{ "tag": "DIV", "class": "bx-filter-date-to" }, true, false
					),
					{ "tag": "SPAN", "class": "bx-calendar-icon" }, true, false),
				'click',
				BX.delegate(this._onDataToClick, this)
			);
		},
		_openCalendar: function(anchor, field)
		{
			BX.calendar(
				{
					"node": anchor,
					"field": field,
					"form": this._settings.getParam("formName"),
					"bTime": false,
					"currentTime": this._settings.getParam("currentTime"),
					"bHideTime": false
				}
			);
		},
		_onIntervalChange: function(e)
		{
			this.layout();
		},
		_onDataFromClick: function(e)
		{
			var wrapper = BX.findChild(
				this._container,
				{ "tag": "DIV", "class": "bx-filter-date-from" }, true, false
			);

			if(!wrapper)
			{
				return;
			}

			this._openCalendar(
				BX.findChild(wrapper, { "tag": "SPAN", "class": "bx-calendar-icon" }, true, false),
				BX.findChild(wrapper, { "tag": "INPUT", "class": "bx-input-date" }, true, false)
			);
		},
		_onDataToClick: function(e)
		{
			var wrapper = BX.findChild(
				this._container,
				{ "tag": "DIV", "class": "bx-filter-date-to" }, true, false
			);

			if(!wrapper)
			{
				return;
			}

			this._openCalendar(
				BX.findChild(wrapper, { "tag": "SPAN", "class": "bx-calendar-icon" }, true, false),
				BX.findChild(wrapper, { "tag": "INPUT", "class": "bx-input-date" }, true, false)
			);
		},
		_displayNode: function(search, display)
		{
			var node = BX.findChild(this._container, search, true, false);
			if(node)
			{
				node.style.display = display ? "" : "none";
			}
		},
		layout: function()
		{
			var intervalSelect = BX.findChild(
				this._container,
				{ "tag": "SELECT", "class": "bx-filter-date-interval-select" },
				true,
				false
			);

			if(!intervalSelect)
			{
				return;
			}

			var showFrom, showTo, showHellip, showDays;
			showFrom = showTo = showHellip = showDays = false;
			var v = intervalSelect.value;
			if(v === "interval")
			{
				showFrom = showTo = showHellip = true;
			}
			else if(v === "before")
			{
				showTo = true;
			}
			else if(v === "after" || v === "exact")
			{
				showFrom = true;
			}
			else if(v === "days")
			{
				showDays = true;
			}

			this._displayNode({ "tag": "DIV", "class": "bx-filter-date-days" }, showDays);
			this._displayNode({ "tag": "DIV", "class": "bx-filter-date-from" }, showFrom);
			this._displayNode({ "tag": "DIV", "class": "bx-filter-date-to" }, showTo);
			this._displayNode({ "tag": "SPAN", "class": "bx-filter-calendar-separate" }, showHellip);
		},
		tryGetParams: function(params)
		{
			return false;
		},
		trySetParams: function(params)
		{
			return false;
		},
		handleParamsChange: function()
		{
			this.layout();
		}
	};

	BX.InterfaceGridFilterDate.create = function(settings)
	{
		var self = new BX.InterfaceGridFilterDate();
		self.initialize(settings);
		return self;
	};
}

if(typeof(BX.InterfaceGridFilterUser) === "undefined")
{
	BX.InterfaceGridFilterUser = function()
	{
		this._settings = null;
		this._info = null;
		this._container = null;
	};

	BX.InterfaceGridFilterUser.prototype =
	{
		initialize: function(settings)
		{
			this._settings = settings ? settings : BX.BizprocParamBag.create(null);
			this._container = BX(settings.getParam("containerId"));
			this._info = settings.getParam("info", null);
		},
		layout: function() { },
		trySetParams: function(params)
		{
			if(!this._info)
			{
				return false;
			}

			var paramInfo = this._info['params'] ? this._info['params'] : {};

			var data = paramInfo['data'] ? paramInfo['data'] : {};
			this._setElementByParam(data["elementId"], data["paramName"], params);

			var search = paramInfo['search'] ? paramInfo['search'] : {};
			this._setElementByParam(search["elementId"], search["paramName"], params);

			return true;
		},
		tryGetParams: function(params)
		{
			return false;
		},
		_setElementByParam: function(elementId, paramName, params)
		{
			var element = BX.type.isNotEmptyString(elementId) ? BX(elementId) : null;
			if(BX.type.isElementNode(element))
			{
				element.value = BX.type.isNotEmptyString(paramName) && params[paramName] ? params[paramName] : '';
			}
		}
	};

	BX.InterfaceGridFilterUser.create = function(settings)
	{
		var self = new BX.InterfaceGridFilterUser();
		self.initialize(settings);
		return self;
	};
}

if(typeof(BX.InterfaceGridFilterSaveAsDialog) === "undefined")
{
	BX.InterfaceGridFilterSaveAsDialog = function()
	{
		this._id = "";
		this._settings = null;
		this._filter = null;
		this._dlg = null;
		this._buttonId = "";
		this._nameInput = null;
	};

	BX.InterfaceGridFilterSaveAsDialog.prototype =
	{
		initialize: function(id, settings)
		{
			this._id = id;
			this._settings = settings ? settings : BX.BizprocParamBag.create(null);
			this._filter = settings.getParam("filter", null);
		},
		getId: function()
		{
			return this._id;
		},
		getButtonId: function()
		{
			return this._buttonId;
		},
		getValues: function()
		{
			return(
				{
					"name": this._nameInput ? this._nameInput.value : ""
				}
			);
		},
		openDialog: function()
		{
			if (this._dlg)
			{
				this._dlg.show();
				return;
			}

			this._dlg = new BX.PopupWindow(
				this.getId() + '_SAVE_AS',
				null,
				{
					autoHide: false,
					draggable: true,
					offsetLeft: 0,
					offsetTop: 0,
					bindOptions: { forceBindPosition: false },
					closeByEsc: true,
					closeIcon: true,
					titleBar: BX.InterfaceGridFilter.getMessage('saveAsDialogTitle'),
					events:
					{
						onPopupClose: BX.delegate(this._handleDialogClose, this)
					},
					content: this._prepareContent(),
					buttons: this._prepareButtons()
				}
			);

			this._dlg.show();
		},
		_prepareContent: function()
		{
			var table = BX.create(
				"TABLE",
				{
					"style":
					{
						"width":"350px",
						"margin": "5px 0 0 0"
					}
				}
			);

			var row = table.insertRow(-1);
			var cell = row.insertCell(-1);
			cell.align = "right";
			cell.innerHTML = BX.InterfaceGridFilter.getMessage("saveAsDialogFieldName") + ":";

			cell = row.insertCell(-1);
			this._nameInput = BX.create(
				"INPUT",
				{
					"style":
					{
						"width":"200px"
					},
					"props":
					{
						"type": "text",
						"maxlength": "255",
						"size": "30"
					},
					"text": BX.InterfaceGridFilter.getMessage("defaultFilterName")
				}
			);

			cell.appendChild(this._nameInput);

			return table;
		},
		_prepareButtons: function()
		{
			return(
				[
					new BX.PopupWindowButton(
						{
							text: BX.InterfaceGridFilter.getMessage("buttonSave"),
							className: "popup-window-button-accept",
							events:
							{
								click : BX.delegate(this._handleSaveButtonClick, this)
							}
						}
					),
					new BX.PopupWindowButtonLink(
						{
							text: BX.InterfaceGridFilter.getMessage("buttonCancel"),
							className: "popup-window-button-link-cancel",
							events:
							{
								click : BX.delegate(this._handleCancelButtonClick, this)
							}
						}
					)
				]
			);

		},
		_handleSaveButtonClick: function(e)
		{
			this._buttonId = "save";
			this._filter.handleSaveAsDialogClose(this);
			this._dlg.close();
		},
		_handleCancelButtonClick: function(e)
		{
			this._buttonId = "cancel";
			this._filter.handleSaveAsDialogClose(this);
			this._dlg.close();
		},
		_handleDialogClose: function(e)
		{
			if(this._dlg)
			{
				this._dlg.destroy();
			}
			this._dlg = null;
		}
	};

	BX.InterfaceGridFilterSaveAsDialog.create = function(id, settings)
	{
		var self = new BX.InterfaceGridFilterSaveAsDialog();
		self.initialize(id, settings);
		return self;
	};
}

if(typeof(BX.InterfaceGridFilterCheckListMenu) === "undefined")
{
	BX.InterfaceGridFilterCheckListMenu = function()
	{
		this._id = "";
		this._settings = null;
		this._filter = null;
		this._allowToggle = true;
		this._items = [];
		this._menuId = "";
		this._menu = null;
	};

	BX.InterfaceGridFilterCheckListMenu.prototype =
	{
		initialize: function(id, settings)
		{
			this._id = id;
			this._menuId = this._id.toLowerCase();
			this._settings = settings ? settings : BX.BizprocParamBag.create(null);
			this._filter = settings.getParam("filter", null);
			this._allowToggle =  this.getSetting("allowToggle", true);
			this._closeOnClick =  this.getSetting("closeOnClick", false);
			var items =  this.getSetting("items");

			for(var i = 0; i < items.length; i++)
			{
				var item = items[i];
				this._items.push(
					BX.InterfaceGridFilterCheckListMenuItem.create(
						item["id"],
						BX.BizprocParamBag.create(
							{
								"text": BX.type.isString(item["text"]) ? item["text"] : "",
								"checked": BX.type.isBoolean(item["checked"]) ? item["checked"] : false,
								"onchange": BX.type.isFunction(item["onchange"]) ? item["onchange"] : null,
								"onclick": BX.type.isFunction(item["onclick"]) ? item["onclick"] : null,
								"allowToggle": BX.type.isBoolean(item["allowToggle"]) ? item["allowToggle"] : this._allowToggle,
								"closeOnClick": BX.type.isBoolean(item["closeOnClick"]) ? item["closeOnClick"] : this._closeOnClick,
								"separatorBefore": BX.type.isBoolean(item["separatorBefore"]) ? item["separatorBefore"] : false,
								"separatorAfter": BX.type.isBoolean(item["separatorAfter"]) ? item["separatorAfter"] : false,
								"menu": this
							}
						)
					)
				);
			}
		},
		getId: function()
		{
			return this._id;
		},
		getSetting: function(name, defaultval)
		{
			return this._settings.getParam(name, defaultval);
		},
		open: function()
		{
			var menuItems = [];
			for(var i = 0; i < this._items.length; i++)
			{
				var popupItems = this._items[i].createPopupMenuItems();
				for(var j = 0; j < popupItems.length; j++)
				{
					menuItems.push(popupItems[j]);
				}
			}

			if(typeof(BX.PopupMenu.Data[this._menuId]) !== "undefined")
			{
				BX.PopupMenu.Data[this._menuId].popupWindow.destroy();
				delete BX.PopupMenu.Data[this._menuId];
			}

			this._menu = BX.PopupMenu.show(
				this._menuId,
				this.getSetting("anchor", null),
				menuItems,
				{
					"offsetTop": parseInt(this.getSetting("offsetTop", 0)),
					"offsetLeft": parseInt(this.getSetting("offsetLeft", 0)),
					"angle": this.getSetting("angle", {})
				}
			);
		},
		close: function()
		{
			if(typeof(BX.PopupMenu.Data[this._menuId]) !== "undefined")
			{
				BX.PopupMenu.Data[this._menuId].popupWindow.close();
			}
		},
		getContainer: function()
		{
			return BX("menu-popup-" + this.getId().toLowerCase());
		}
	};

	BX.InterfaceGridFilterCheckListMenu.create = function(id, settings)
	{
		var self = new BX.InterfaceGridFilterCheckListMenu();
		self.initialize(id, settings);
		return self;
	};
}

if(typeof(BX.InterfaceGridFilterCheckListMenuItem) === "undefined")
{
	BX.InterfaceGridFilterCheckListMenuItem = function()
	{
		this._id = "";
		this._settings = null;
		this._menu = null;
		this._checked = false;
		this._allowToggle = true;
		this._closeOnClick = false;
	};

	BX.InterfaceGridFilterCheckListMenuItem.prototype =
	{
		initialize: function(id, settings)
		{
			this._id = id;
			this._settings = settings ? settings : BX.BizprocParamBag.create(null);
			this._menu = settings.getParam("menu", null);
			this._checked = settings.getParam("checked", false);
			this._allowToggle =  this.getSetting("allowToggle", true);
			this._closeOnClick =  this.getSetting("closeOnClick", false);
		},
		getSetting: function(name, defaultval)
		{
			return this._settings.getParam(name, defaultval);
		},
		getId: function()
		{
			return this._id;
		},
		isChecked: function()
		{
			return this._checked;
		},
		setChecked: function(checked)
		{
			checked = !!checked;
			if(this._checked === checked)
			{
				return;
			}

			this._checked = checked;

			var handler = this.getSetting("onchange", null);
			if(BX.type.isFunction(handler))
			{
				try
				{
					handler({ "id": this.getId(), "checked": checked }, this);
				}
				catch(ex)
				{
				}
			}
		},
		toggle: function()
		{
			if(this._allowToggle)
			{
				this.setChecked(!this.isChecked());
			}
		},
		createPopupMenuItems: function()
		{
			var result = [];

			if(this.getSetting("separatorBefore", false))
			{
				result.push({ "SEPARATOR": true });
			}

			result.push(
				{
					"text": this.getSetting("text", this.getId()),
					"className" : "bp-filter-popup-item" + (this.isChecked() ? " menu-popup-item-checked" : "") + " bp-check-list-menu-item-" + this.getId().toLowerCase(),
					"href" : "#",
					"onclick": BX.delegate(this._onClick, this)
				}
			);

			if(this.getSetting("separatorAfter", false))
			{
				result.push({ "SEPARATOR": true });
			}

			return result;
		},
		_onClick: function(e)
		{
			BX.PreventDefault(e);

			var handler = this.getSetting("onclick", null);
			if(BX.type.isFunction(handler))
			{
				try
				{
					handler({ "id": this.getId(), "checked": this.isChecked() }, this);
				}
				catch(ex)
				{
				}
			}

			if(this._closeOnClick)
			{
				this._menu.close();
				return;
			}

			this.toggle();

			var node = BX.findChild(
				this._menu.getContainer(),
				{ "tag":"A", "class": "bp-check-list-menu-item-" + this.getId().toLowerCase() },
				true,
				false
			);

			if(this.isChecked())
			{
				BX.addClass(node,"menu-popup-item-checked");
			}
			else
			{
				BX.removeClass(node,"menu-popup-item-checked");
			}
		}
	};

	BX.InterfaceGridFilterCheckListMenuItem.create = function(id, settings)
	{
		var self = new BX.InterfaceGridFilterCheckListMenuItem();
		self.initialize(id, settings);
		return self;
	}
}

if(typeof(BX.InterfaceGridFilterCloseOpen) === "undefined")
{
	BX.InterfaceGridFilterCloseOpen = function(innerBlock, mainBlock, filter)
	{
		this.filter = filter;
		this.innerBlock = BX(innerBlock);
		this.mainBlock = BX(mainBlock);
		this.isOpen = null;
	};

	BX.InterfaceGridFilterCloseOpen.prototype =
	{
		initialize: function()
		{
			this.isOpen = !this.filter.isFolded();
		},
		_easing: function(params)
		{
			var _self = this;
			var easing = new BX.easing({
				duration: 300,
				start: {
					height: params.start
				},
				finish: {
					height: params.finish
				},
				transition: BX.easing.makeEaseOut(BX.easing.transitions.circ),
				step: BX.delegate(this._step, this),
				complete: BX.proxy(this._complete, this)
			});
			easing.animate()
		},
		toggle: function()
		{
			if(this.isOpen)
			{
				this._easing({
					start : this.innerBlock.offsetHeight,
					finish : 0
				});
				this.isOpen = false;

			}
			else {
				this._easing({
					start: this.mainBlock.offsetHeight,
					finish: this.innerBlock.offsetHeight
				});
				this.isOpen = true;
			}
		},
		_step: function(state)
		{
			this.mainBlock.style.height = state.height + 'px';
		},
		_complete: function ()
		{
			if (this.isOpen)
				this.mainBlock.style.height = 'auto';
		}
	};
}

if(typeof(BX.CrmUserSearchPopup) === 'undefined')
{
	BX.CrmUserSearchPopup = function()
	{
		this._id = '';
		this._search_input = null;
		this._data_input = null;
		this._componentName = '';
		this._componentContainer = null;
		this._componentObj = null;
		this._serviceContainer = null;
		this._zIndex = 0;
		this._dlg = null;
		this._dlgDisplayed = false;
		this._currentUser = {};

		this._searchKeyHandler = BX.delegate(this._handleSearchKey, this);
		this._searchFocusHandler = BX.delegate(this._handleSearchFocus, this);
		this._externalClickHandler = BX.delegate(this._handleExternalClick, this);
		this._clearButtonClickHandler = BX.delegate(this._hadleClearButtonClick, this);

		this._userSelectorInitCounter = 0;
	};

	BX.CrmUserSearchPopup.prototype =
	{
		//initialize: function(id, search_input, data_input, componentName, user, serviceContainer, zIndex)
		initialize: function(id, settings)
		{
			this._id = BX.type.isNotEmptyString(id) ? id : ('crm_user_search_popup_' + Math.random());

			if(!settings)
			{
				settings = {};
			}

			if(!BX.type.isElementNode(settings['searchInput']))
			{
				throw  "BX.CrmUserSearchPopup: 'search_input' is not defined!";
			}
			this._search_input = settings['searchInput'];
			this._clearButton = BX.findPreviousSibling(this._search_input, { className: "bizproc-user-search-filter-clean" });

			if(!BX.type.isElementNode(settings['dataInput']))
			{
				throw  "BX.CrmUserSearchPopup: 'data_input' is not defined!";
			}
			this._data_input = settings['dataInput'];

			if(!BX.type.isNotEmptyString(settings['componentName']))
			{
				throw  "BX.CrmUserSearchPopup: 'componentName' is not defined!";
			}

			this._currentUser = settings['user'] ? settings['user'] : {};
			this._componentName = settings['componentName'];
			this._componentContainer = BX(this._componentName + '_selector_content');

			this._initializeUserSelector();
			this._adjustUser();

			this._serviceContainer = settings['serviceContainer'] ? settings['serviceContainer'] : document.body;
			this.setZIndex(settings['zIndex']);
		},
		_initializeUserSelector: function()
		{
			var objName = 'O_' + this._componentName;
			if(!window[objName])
			{
				if(this._userSelectorInitCounter === 10)
				{
					throw "BX.CrmUserSearchPopup: Could not find '"+ objName +"' user selector!";
				}

				this._userSelectorInitCounter++;
				window.setTimeout(BX.delegate(this._initializeUserSelector, this), 200);
				return;
			}

			this._componentObj = window[objName];
			this._componentObj.onSelect = BX.delegate(this._handleUserSelect, this);
			this._componentObj.searchInput = this._search_input;

			if(this._currentUser && this._currentUser['id'] > 0)
			{
				this._componentObj.setSelected([ this._currentUser ]);
			}

			BX.bind(this._search_input, 'keyup', this._searchKeyHandler);
			BX.bind(this._search_input, 'focus', this._searchFocusHandler);

			if(BX.type.isElementNode(this._clearButton))
			{
				BX.bind(this._clearButton, 'click', this._clearButtonClickHandler);
			}

			BX.bind(document, 'click', this._externalClickHandler);
		},
		open: function()
		{
			this._componentContainer.style.display = '';
			this._dlg = new BX.PopupWindow(
				this._id,
				this._search_input,
				{
					autoHide: false,
					draggable: false,
					closeByEsc: true,
					offsetLeft: 0,
					offsetTop: 0,
					zIndex: this._zIndex,
					bindOptions: { forceBindPosition: true },
					content : this._componentContainer,
					events:
					{
						onPopupShow: BX.delegate(
							function()
							{
								this._dlgDisplayed = true;
							},
							this
						),
						onPopupClose: BX.delegate(
							function()
							{
								this._dlgDisplayed = false;
								this._componentContainer.parentNode.removeChild(this._componentContainer);
								this._serviceContainer.appendChild(this._componentContainer);
								this._componentContainer.style.display = 'none';
								this._dlg.destroy();
							},
							this
						),
						onPopupDestroy: BX.delegate(
							function()
							{
								this._dlg = null;
							},
							this
						)
					}
				}
			);

			this._dlg.show();
		},
		_adjustUser: function()
		{
			//var container = BX.findParent(this._search_input, { className: 'webform-field-textbox' });
			if(parseInt(this._currentUser['id']) > 0)
			{
				this._data_input.value = this._currentUser['id'];
				this._search_input.value = this._currentUser['name'] ? this._currentUser.name : this._currentUser['id'];
				//BX.removeClass(container, 'webform-field-textbox-empty');
			}
			else
			{
				this._data_input.value = this._search_input.value = '';
				//BX.addClass(container, 'webform-field-textbox-empty');
			}
		},
		getZIndex: function()
		{
			return this._zIndex;
		},
		setZIndex: function(zIndex)
		{
			if(typeof(zIndex) === 'undefined' || zIndex === null)
			{
				zIndex = 0;
			}

			var i = parseInt(zIndex);
			this._zIndex = !isNaN(i) ? i : 0;
		},
		close: function()
		{
			if(this._dlg)
			{
				this._dlg.close();
			}
		},
		select: function(user)
		{
			this._currentUser = user;
			this._adjustUser();
			if(this._componentObj)
			{
				this._componentObj.setSelected([ user ]);
			}
		},
		_onBeforeDelete: function()
		{
			if(BX.type.isElementNode(this._search_input))
			{
				BX.unbind(this._search_input, 'keyup', this._searchKeyHandler);
				BX.unbind(this._search_input, 'focus', this._searchFocusHandler);
			}

			if(BX.type.isElementNode(this._clearButton))
			{
				BX.bind(this._clearButton, 'click', this._clearButtonClickHandler);
			}

			BX.unbind(document, 'click', this._externalClickHandler);

			if(this._componentContainer)
			{
				this._componentContainer.parentNode.removeChild(this._componentContainer);
				this._serviceContainer.appendChild(this._componentContainer);
				this._componentContainer.style.display = 'none';
				this._componentContainer = null;
			}
		},
		_hadleClearButtonClick: function(e)
		{
			this._data_input.value = this._search_input.value = '';
		},
		_handleExternalClick: function(e)
		{
			if(!e)
			{
				e = window.event;
			}

			if(!this._dlgDisplayed)
			{
				return;
			}

			var target = null;
			if(e)
			{
				if(e.target)
				{
					target = e.target;
				}
				else if(e.srcElement)
				{
					target = e.srcElement;
				}
			}

			if(target !== this._search_input &&
				!BX.findParent(target, { attribute:{ id: this._componentName + '_selector_content' } }))
			{
				this._adjustUser();
				this.close();
			}
		},
		_handleSearchKey: function(e)
		{
			if(!this._dlg || !this._dlgDisplayed)
			{
				this.open();
			}

			this._componentObj.search();
		},
		_handleSearchFocus: function(e)
		{
			if(!this._dlg || !this._dlgDisplayed)
			{
				this.open();
			}

			this._componentObj._onFocus(e);
		},
		_handleUserSelect: function(user)
		{
			this._currentUser = user;
			this._adjustUser();
			this.close();
		}
	};

	BX.CrmUserSearchPopup.items = {};

	BX.CrmUserSearchPopup.create = function(id, settings, delay)
	{
		if(isNaN(delay))
		{
			delay = 0;
		}

		if(delay > 0)
		{
			window.setTimeout(
				function(){ BX.CrmUserSearchPopup.create(id, settings, 0); },
				delay
			);
			return null;
		}

		var self = new BX.CrmUserSearchPopup();
		self.initialize(id, settings);
		this.items[id] = self;
		return self;
	};

	BX.CrmUserSearchPopup.createIfNotExists = function(id, settings)
	{
		var self = this.items[id];
		if(typeof(self) !== 'undefined')
		{
			self.initialize(id, settings);
		}
		else
		{
			self = new BX.CrmUserSearchPopup();
			self.initialize(id, settings);
			this.items[id] = self;
		}
		return self;
	};

	BX.CrmUserSearchPopup.deletePopup = function(id)
	{
		var item = this.items[id];
		if(typeof(item) === 'undefined')
		{
			return false;
		}

		item._onBeforeDelete();
		delete this.items[id];
		return true;
	}
}