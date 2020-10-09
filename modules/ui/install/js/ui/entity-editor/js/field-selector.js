/**
 * @version 1.0
 * @copyright Bitrix Inc. 2019
 */

BX.namespace("BX.UI");

//region FIELD SELECTOR
if(typeof(BX.UI.EntityEditorFieldSelector) === "undefined")
{
	BX.UI.EntityEditorFieldSelector = function()
	{
		this._id = "";
		this._settings = {};
		this._scheme = null;
		this._excludedNames = null;
		this._contentWrapper = null;
		this._popup = null;
	};

	BX.UI.EntityEditorFieldSelector.prototype =
	{
		initialize: function(id, settings)
		{
			this._id = id;
			this._settings = settings ? settings : {};
			this._scheme = BX.prop.get(this._settings, "scheme", null);
			if(!this._scheme)
			{
				throw "BX.UI.EntityEditorFieldSelector. Parameter 'scheme' is not found.";
			}
			this._excludedNames = BX.prop.getArray(this._settings, "excludedNames", []);
		},
		getMessage: function(name)
		{
			return BX.prop.getString(BX.UI.EntityEditorFieldSelector.messages, name, name);
		},
		isSchemeElementEnabled: function(schemeElement)
		{
			var name = schemeElement.getName();
			for(var i = 0, length = this._excludedNames.length; i < length; i++)
			{
				if(this._excludedNames[i] === name)
				{
					return false;
				}
			}
			return true;
		},
		addClosingListener: function(listener)
		{
			BX.Event.EventEmitter.subscribe("BX.UI.EntityEditorFieldSelector:close", listener);
		},
		removeClosingListener: function(listener)
		{
			BX.Event.EventEmitter.unsubscribe("BX.UI.EntityEditorFieldSelector:close", listener);
		},
		isOpened: function()
		{
			return this._popup && this._popup.isShown();
		},
		open: function()
		{
			if(this.isOpened())
			{
				return;
			}

			this._popup = new BX.PopupWindow(
				this._id,
				null,
				{
					autoHide: false,
					draggable: true,
					bindOptions: { forceBindPosition: false },
					closeByEsc: true,
					closeIcon: {},
					zIndex: 1,
					titleBar: BX.prop.getString(this._settings, "title", ""),
					content: this.prepareContent(),
					lightShadow : true,
					contentNoPaddings: true,
					buttons: [
						new BX.PopupWindowButton(
							{
								text : BX.message("UI_ENTITY_EDITOR_SELECT"),
								className : "ui-btn ui-btn-success",
								events:
									{
										click: BX.delegate(this.onAcceptButtonClick, this)
									}
							}
						),
						new BX.PopupWindowButtonLink(
							{
								text : BX.message("UI_ENTITY_EDITOR_CANCEL"),
								className : "ui-btn ui-btn-link",
								events:
									{
										click: BX.delegate(this.onCancelButtonClick, this)
									}
							}
						)
					],
					events: {
						onPopupClose: this.onPopupClose.bind(this),
					}
				}
			);

			this._popup.show();
		},
		close: function()
		{
			if(!(this._popup && this._popup.isShown()))
			{
				return;
			}

			this._popup.close();
		},
		prepareContent: function()
		{
			this._contentWrapper = BX.create("div", {
				props: { className: "ui-entity-editor-popup-field-selector" }
			});
			var container = BX.create("div", {
				props: { className: "ui-entity-editor-popup-field-selector-list" }
			});

			var columns = this._scheme.getElements();
			for(var i = 0, columnCount = columns.length; i < columnCount; i++)
			{
				var column = columns[i];

				var sections = column.getElements();
				for(var k = 0, sectionCount = sections.length; k < sectionCount; k++)
				{
					var section = sections[k];
					if(!this.isSchemeElementEnabled(section))
					{
						continue;
					}

					var effectiveElements = [];
					var elementChildren = section.getElements();
					var childElement;
					for(var j = 0; j < elementChildren.length; j++)
					{
						childElement = elementChildren[j];
						if(childElement.isTransferable() && childElement.getName() !== "")
						{
							effectiveElements.push(childElement);
						}
					}

					if(effectiveElements.length === 0)
					{
						continue;
					}

					var parentName = section.getName();
					var parentTitle = section.getTitle();

					container.appendChild(
						BX.create(
							"div",
							{
								attrs: { className: "ui-entity-editor-popup-field-selector-list-caption" },
								text: parentTitle
							}
						)
					);

					for(var j = 0; j < effectiveElements.length; j++)
					{
						childElement = effectiveElements[j];

						var childElementName = childElement.getName();
						var childElementTitle = childElement.getTitle();

						var itemId = parentName + "\\" + childElementName;
						var itemWrapper = BX.create(
							"div",
							{
								attrs: { className: "ui-entity-editor-popup-field-selector-list-item" }
							}
						);
						container.appendChild(itemWrapper);

						itemWrapper.appendChild(
							BX.create(
								"input",
								{
									attrs:
										{
											id: itemId,
											type: "checkbox",
											className: "ui-entity-editor-popup-field-selector-list-checkbox"
										}
								}
							)
						);

						itemWrapper.appendChild(
							BX.create(
								"label",
								{
									attrs:
										{
											for: itemId,
											className: "ui-entity-editor-popup-field-selector-list-label"
										},
									text: childElementTitle
								}
							)
						);
					}
				}
			}

			this._contentWrapper.appendChild(container);

			return this._contentWrapper;
		},
		getSelectedItems: function()
		{
			if(!this._contentWrapper)
			{
				return [];
			}

			var results = [];
			var checkBoxes = this._contentWrapper.querySelectorAll("input.ui-entity-editor-popup-field-selector-list-checkbox");
			for(var i = 0, length = checkBoxes.length; i < length; i++)
			{
				var checkBox = checkBoxes[i];
				if(checkBox.checked)
				{
					var parts = checkBox.id.split("\\");
					if(parts.length >= 2)
					{
						results.push({ sectionName: parts[0], fieldName: parts[1] });
					}
				}
			}

			return results;
		},
		onAcceptButtonClick: function()
		{
			BX.Event.EventEmitter.emit(
				"BX.UI.EntityEditorFieldSelector:close",
				{ sender: this, isCanceled: false, items: this.getSelectedItems() }
			);
			this.close();
		},
		onCancelButtonClick: function()
		{
			BX.Event.EventEmitter.emit(
				"BX.UI.EntityEditorFieldSelector:close",
				{ sender: this, isCanceled: true }
			);
			this.close();
		},
		onPopupClose: function()
		{
			if(this._popup)
			{
				this._contentWrapper = null;
				this._popup.destroy();
			}
		},
		onPopupDestroy: function()
		{
			if(!this._popup)
			{
				return;
			}

			this._contentWrapper = null;
			this._popup = null;
		}
	};

	if(typeof(BX.UI.EntityEditorFieldSelector.messages) === "undefined")
	{
		BX.UI.EntityEditorFieldSelector.messages = {};
	}

	BX.UI.EntityEditorFieldSelector.create = function(id, settings)
	{
		var self = new BX.UI.EntityEditorFieldSelector(id, settings);
		self.initialize(id, settings);
		return self;
	}
}
//endregion

//region USER SELECTOR
if(typeof(BX.UI.EntityEditorUserSelector) === "undefined")
{
	BX.UI.EntityEditorUserSelector = function()
	{
		this._id = "";
		this._settings = {};
	};

	BX.UI.EntityEditorUserSelector.prototype =
		{
			initialize: function(id, settings)
			{
				this._id = id;
				this._settings = settings ? settings : {};
				this._isInitialized = false;
				this._onlyUsers = BX.prop.getBoolean(this._settings, "onlyUsers", true);
			},
			getId: function()
			{
				return this._id;
			},
			open: function(anchor)
			{
				if(this._mainWindow && this._mainWindow === BX.SocNetLogDestination.containerWindow)
				{
					return;
				}

				if(!this._isInitialized)
				{
					BX.SocNetLogDestination.init(
						{
							name: this._id,
							extranetUser:  false,
							userSearchArea: "I",
							bindMainPopup: { node: anchor, offsetTop: "5px", offsetLeft: "15px" },
							callback: {
								select : BX.delegate(this.onSelect, this),
								unSelect: BX.delegate(this.onSelect, this)
							},
							showSearchInput: BX.prop.getBoolean(this._settings, "showSearchInput", true),
							departmentSelectDisable: (this._onlyUsers ? true : false),
							items:
								{
									users: BX.UI.EntityEditorUserSelector.users,
									groups: {},
									sonetgroups: (this._onlyUsers ? {} : BX.UI.EntityEditorUserSelector.socnetGroups),
									department: BX.UI.EntityEditorUserSelector.department,
									departmentRelation : BX.SocNetLogDestination.buildDepartmentRelation(BX.UI.EntityEditorUserSelector.department)
								},
							itemsLast: BX.UI.EntityEditorUserSelector.last,
							itemsSelected: BX.prop.getObject(this._settings, "itemsSelected", {}),
							isCrmFeed: false,
							useClientDatabase: false,
							destSort: {},
							allowAddUser: false,
							allowSearchCrmEmailUsers: false,
							allowUserSearch: true
						}
					);
					this._isInitialized = true;
				}

				BX.SocNetLogDestination.openDialog(this._id, { bindNode: anchor });
				this._mainWindow = BX.SocNetLogDestination.containerWindow;
			},
			close: function()
			{
				if(this._mainWindow && this._mainWindow === BX.SocNetLogDestination.containerWindow)
				{
					BX.SocNetLogDestination.closeDialog();
					this._mainWindow = null;
					this._isInitialized = false;
				}

			},
			onSelect: function(item, type, search, bUndeleted)
			{
				if(this._onlyUsers && type !== "users")
				{
					return;
				}

				var callback = BX.prop.getFunction(this._settings, "callback", null);
				if(callback)
				{
					callback(this, item);
				}
			}
		};

	BX.UI.EntityEditorUserSelector.items = {};
	BX.UI.EntityEditorUserSelector.create = function(id, settings)
	{
		var self = new BX.UI.EntityEditorUserSelector(id, settings);
		self.initialize(id, settings);
		this.items[self.getId()] = self;
		return self;
	}
}
//endregion