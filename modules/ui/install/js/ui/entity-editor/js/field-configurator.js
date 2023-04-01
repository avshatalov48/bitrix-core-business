if(typeof BX.UI.EntityConfigurationManager === "undefined")
{
	BX.UI.EntityConfigurationManager = function()
	{
		this.id = "";
		this._editor = null;
	};
	BX.UI.EntityConfigurationManager.prototype =
		{
			initialize: function(id, settings)
			{
				this.id = BX.type.isNotEmptyString(id) ? id : BX.util.getRandomString(4);
				this._editor = settings.editor;
			},
			isSelectionEnabled: function()
			{
				return this._editor.getUserFieldManager().isSelectionEnabled();
			},
			isCreationEnabled: function()
			{
				return this._editor.getUserFieldManager().isCreationEnabled();
			},
			isMandatoryControlEnabled: function()
			{
				return this._editor.getUserFieldManager().isMandatoryControlEnabled();
			},
			getTypeInfos: function()
			{
				return this._editor.getUserFieldManager().getTypeInfos();
			},
			getCreationPageUrl: function(typeId)
			{
				return this._editor.getUserFieldManager().getCreationPageUrl();
			},
			openCreationPageUrl: function(typeId)
			{
				var event = new BX.Event.BaseEvent({
					data: {
						isCanceled: false
					}
				});
				BX.Event.EventEmitter.emit('BX.UI.EntityConfigurationManager:onCreateClick', event);
				if (!event.getData().isCanceled)
				{
					window.open(this.getCreationPageUrl(typeId));
				}
			},
			hasExternalForm: function(typeId)
			{
				return typeId === 'custom';
			},
			createFieldConfigurator: function(params, parent)
			{
				if(!BX.type.isPlainObject(params))
				{
					throw "BX.UI.EntityConfigurationManager: The 'params' argument must be object.";
				}

				var child = BX.prop.get(params, "field", null);
				if(!child || (child.getType() === "userField" && this._editor.getUserFieldManager().isModificationEnabled()))
				{
					return this.getUserFieldConfigurator(params, parent);
				}
				else
				{
					return this.getSimpleFieldConfigurator(params, parent);
				}
			},
			getSimpleFieldConfigurator: function(params, parent)
			{
				var typeId = "";
				var child = BX.prop.get(params, "field", null);
				if(child)
				{
					typeId = child.getType();
					child.setVisible(false);
				}
				else
				{
					typeId = BX.prop.get(params, "typeId", BX.UI.EntityUserFieldType.string);
				}

				return this._fieldConfigurator = BX.UI.EntityEditorFieldConfigurator.create(
					"",
					{
						editor: this._editor,
						schemeElement: null,
						model: parent._model,
						mode: BX.UI.EntityEditorMode.edit,
						parent: parent,
						typeId: typeId,
						field: child,
						mandatoryConfigurator: params.mandatoryConfigurator
					}
				);
			},
			getUserFieldConfigurator: function(params, parent)
			{
				var typeId = "";
				var field = BX.prop.get(params, "field", null);
				if(field)
				{
					if(!(field instanceof BX.UI.EntityEditorUserField))
					{
						throw "BX.UI.EntityConfigurationManager: The 'field' param must be EntityEditorUserField.";
					}

					typeId = field.getFieldType();
					field.setVisible(false);
				}
				else
				{
					typeId = BX.prop.get(params, "typeId", BX.UI.EntityUserFieldType.string);
				}

				return BX.UI.EntityEditorUserFieldConfigurator.create(
					"",
					{
						editor: this._editor,
						schemeElement: null,
						model: parent.getModel(),
						mode: BX.UI.EntityEditorMode.edit,
						parent: parent,
						typeId: typeId,
						field: field,
						enableMandatoryControl: BX.prop.getBoolean(params, "enableMandatoryControl", true),
						mandatoryConfigurator: params.mandatoryConfigurator,
						showAlways: true
					}
				);
			}
		};

	BX.UI.EntityConfigurationManager.create = function(id, settings)
	{
		var self = new BX.UI.EntityConfigurationManager();
		self.initialize(id, settings);
		return self;
	};
}

if (typeof BX.UI.EntityEditorFieldConfigurator === "undefined")
{
	BX.UI.EntityEditorFieldConfigurator = function()
	{
		BX.UI.EntityEditorFieldConfigurator.superclass.constructor.apply(this);
		this._field = null;
		this._typeId = "";
		this._isLocked = false;

		this._labelInput = null;
		this._saveButton = null;
		this._cancelButton = null;
		this._isTimeEnabledCheckBox = null;
		this._isRequiredCheckBox = null;
		this._isMultipleCheckBox = null;
		this._showAlwaysCheckBox = null;
		this._optionWrapper = null;

		this._enumConfigurator = null;

		this._enableMandatoryControl = true;
		this._mandatoryConfigurator = null;
	};
	BX.extend(BX.UI.EntityEditorFieldConfigurator, BX.UI.EntityEditorControl);
	BX.UI.EntityEditorFieldConfigurator.prototype.doInitialize = function()
	{
		BX.UI.EntityEditorFieldConfigurator.superclass.doInitialize.apply(this);
		this._field = BX.prop.get(this._settings, "field", null);
		if(this._field)
		{
			this.checkField();
		}

		this._enableMandatoryControl = BX.prop.getBoolean(this._settings, "enableMandatoryControl", true);
		this._mandatoryConfigurator = BX.prop.get(this._settings, "mandatoryConfigurator", null);

		this._typeId = BX.prop.getString(this._settings, "typeId", "");
	};
	BX.UI.EntityEditorFieldConfigurator.prototype.checkField = function()
	{
	};
	BX.UI.EntityEditorFieldConfigurator.prototype.getDefaultFieldLabel = function()
	{
		var manager = this._editor.getUserFieldManager();
		return this._field ? this._field.getTitle() : manager.getDefaultFieldLabel(this._typeId);
	};
	BX.UI.EntityEditorFieldConfigurator.prototype.layout = function(options)
	{
		if(this._hasLayout)
		{
			return;
		}

		if(this._mode === BX.UI.EntityEditorMode.view)
		{
			throw "EntityEditorFieldConfigurator. View mode is not supported by this control type.";
		}

		this._wrapper = BX.create("div", { props: { className: "ui-entity-editor-content-block-new-fields" } });
		this.layoutInternal();
		this.registerLayout(options);
		this._hasLayout = true;
	};

	BX.UI.EntityEditorFieldConfigurator.prototype.layoutInnerConfigurator = function(innerConfig, listItems, nextNode)
	{
		if (
			BX.Type.isPlainObject(innerConfig)
			&& BX.Type.isArray(listItems)
			&& this._enumConfigurator === null
		)
		{
			var enums = [];

			for (var i = 0; i < listItems.length; i++)
			{
				enums.push({
					ID: listItems[i]["VALUE"],
					VALUE: listItems[i]["NAME"],
					XML_ID: ""
				});
			}

			this._enumConfigurator = BX.UI.EntityEditorEnumConfigurator.create({
				enumInfo: {
					enumItems: enums,
					innerConfig: innerConfig
				},
				wrapper: this._wrapper,
				nextNode: (BX.Type.isDomNode(nextNode) ? nextNode : null)
			});
			this._enumConfigurator.layout();
		}
	}

	BX.UI.EntityEditorFieldConfigurator.prototype.layoutInternal = function()
	{
		this._wrapper.appendChild(this.getInputContainer());
		if(this._typeId === "list" && (!this._field || this._field.getEditor().canChangeCommonConfiguration()))
		{
			this.layoutInnerConfigurator(this._field.getInnerConfig(), this._field.getItems());
		}
		this._wrapper.appendChild(this.getOptionContainer());
		this._wrapper.appendChild(
			BX.create("hr", { props: { className: "ui-entity-editor-line" } })
		);
		this._wrapper.appendChild(this.getButtonContainer());
	};
	BX.UI.EntityEditorFieldConfigurator.prototype.getInputTitle = function()
	{
		return this._field.getTitle();
	};
	BX.UI.EntityEditorFieldConfigurator.prototype.getInputContainer = function()
	{
		this._labelInput = BX.create(
			"input",
			{
				attrs:
					{
						className: "ui-ctl-element",
						type: "text",
						value: this.getInputTitle()
					}
			}
		);

		return BX.create(
			"div",
			{
				props: { className: "ui-entity-editor-content-block" },
				children:
					[
						BX.create(
							"div",
							{
								props: { className: "ui-entity-editor-block-title" },
								children:
									[
										BX.create(
											"span",
											{
												attrs: { className: "ui-entity-editor-block-title-text" },
												text: BX.message("UI_ENTITY_EDITOR_FIELD_TITLE")
											}
										)
									]
							}
						),
						BX.create(
							"div",
							{
								props: { className: "ui-entity-editor-content-block" },
								children:
									[
										BX.create(
											"div",
											{
												props: { className: "ui-ctl ui-ctl-textbox ui-ctl-w100" },
												children: [ this._labelInput ]
											}
										)
									]
							}
						)
					]
			}
		);
	};
	BX.UI.EntityEditorFieldConfigurator.prototype.getOptionContainer = function()
	{
		this._optionWrapper = BX.create(
			"div",
			{
				props: { className: "ui-entity-editor-content-block" }
			}
		);

		if(this._field.areAttributesEnabled() && !this._field.isRequired() && this._mandatoryConfigurator)
		{
			this._isRequiredCheckBox = this.createOption(
				{
					caption: this._mandatoryConfigurator.getTitle() + ":",
					//labelSettings: { props: { className: "ui-entity-new-field-addiction-label" } },
					containerSettings: { style: { alignItems: "center" } },
					elements: this._mandatoryConfigurator.getButton().prepareLayout()
				}
			);
			this._isRequiredCheckBox.checked = !this._mandatoryConfigurator.isEmpty();

			this._mandatoryConfigurator.setSwitchCheckBox(this._isRequiredCheckBox);
			this._mandatoryConfigurator.setLabel(this._isRequiredCheckBox.nextSibling);

			this._mandatoryConfigurator.setEnabled(this._isRequiredCheckBox.checked);
			this._mandatoryConfigurator.adjust();
		}

		//region Show Always
		this._showAlwaysCheckBox = this.createOption(
			{ caption: BX.message("UI_ENTITY_EDITOR_SHOW_ALWAYS") }
		);
		this._showAlwaysCheckBox.checked = this._field.checkOptionFlag(BX.UI.EntityEditorControlOptions.showAlways);
		//endregion

		return BX.create(
			"div",
			{
				props: { className: "ui-entity-editor-content-block ui-entity-editor-content-block-checkbox" },
				children: [ this._optionWrapper ]
			}
		);
	};
	BX.UI.EntityEditorFieldConfigurator.prototype.getButtonContainer = function()
	{
		this._saveButton = BX.create(
			"span",
			{
				props: { className: "ui-btn ui-btn-primary" },
				text: BX.message("UI_ENTITY_EDITOR_SAVE"),
				events: {  click: BX.delegate(this.onSaveButtonClick, this) }
			}
		);
		this._cancelButton = BX.create(
			"span",
			{
				props: { className: "ui-btn ui-btn-light-border" },
				text: BX.message("UI_ENTITY_EDITOR_CANCEL"),
				events: {  click: BX.delegate(this.onCancelButtonClick, this) }
			}
		);

		return BX.create(
			"div",
			{
				props: {
					className: "ui-entity-editor-content-block-new-fields-btn-container"
				},
				children: [
					this._saveButton,
					this._cancelButton
				]
			}
		);
	};

	BX.UI.EntityEditorFieldConfigurator.prototype.getIsTimeEnabledCheckBox = function()
	{
		var checkBox = null;
		if(this._field === null && (this._typeId === "datetime" || this._typeId === "date"))
		{
			checkBox = this.createOption({ caption: BX.message("UI_ENTITY_EDITOR_UF_ENABLE_TIME") });
		}
		return checkBox;
	};
	BX.UI.EntityEditorFieldConfigurator.prototype.getMultipleCheckBox = function()
	{
		var checkBox = null;
		if (this._field === null && this._typeId !== "boolean")
		{
			checkBox = this.createOption({ caption: BX.message("UI_ENTITY_EDITOR_UF_MULTIPLE_FIELD") });
		}
		return checkBox;
	};
	BX.UI.EntityEditorFieldConfigurator.prototype.getIsRequiredCheckBox = function()
	{
		var checkBox = null;
		if( this._field !== null
			&& this._field.areAttributesEnabled()
			&& !this._field.isRequired()
			&& this._mandatoryConfigurator
		)
		{
			checkBox = this.createOption(
				{
					caption: this._mandatoryConfigurator.getTitle() + ":",
					//labelSettings: { props: { className: "ui-entity-new-field-addiction-label" } },
					containerSettings: { style: { alignItems: "center" } },
					elements: this._mandatoryConfigurator.getButton().prepareLayout()
				}
			);
			checkBox.checked = !this._mandatoryConfigurator.isEmpty();

			this._mandatoryConfigurator.setSwitchCheckBox(checkBox);
			this._mandatoryConfigurator.setLabel(checkBox.nextSibling);

			this._mandatoryConfigurator.setEnabled(checkBox.checked);
			this._mandatoryConfigurator.adjust();
		}
		return checkBox;
	};
	BX.UI.EntityEditorFieldConfigurator.prototype.clearLayout = function()
	{
		if(!this._hasLayout)
		{
			return;
		}

		this._wrapper = BX.remove(this._wrapper);

		this._labelInput = null;
		this._saveButton = null;
		this._cancelButton = null;
		// this._isTimeEnabledCheckBox = null;
		this._isRequiredCheckBox = null;
		// this._isMultipleCheckBox = null;
		this._showAlwaysCheckBox = null;
		this._optionWrapper = null;

		this._hasLayout = false;
	};

	BX.UI.EntityEditorFieldConfigurator.prototype.createOption = function(params)
	{
		var element = BX.create("input", {
			props: {
				className: "ui-ctl-element",
				type: "checkbox"
			}
		});

		var label = BX.create("div", {
			props: { className: "ui-ctl ui-ctl-wa ui-ctl-checkbox ui-ctl-xs" },
			style: { marginBottom: 0 },
			children: [
				BX.create("label", {
					children: [
						element,
						BX.create("span", {
							props: { className: "ui-ctl-label-text" },
							text: BX.prop.getString(params, "caption", "")
						})
					]
				})
			]
		});

		var labelSettings = BX.prop.getObject(params, "labelSettings", null);
		if(labelSettings)
		{
			BX.adjust(label, labelSettings);
		}

		var helpCode = BX.prop.getString(params, "helpCode", "");
		if (helpCode)
		{
			label.appendChild(
				BX.create("span", {
					props: {
						className: "ui-entity-editor-new-field-helper-icon"
					},
					events: {
						click: function () {
							top.BX.Helper.show("redirect=detail&code=" + helpCode);
						}
					}
				})
			);
		}
		else
		{
			var helpUrl = BX.prop.getString(params, "helpUrl", "");
			if(helpUrl !== "")
			{
				label.appendChild(
					BX.create("a", { props: { className: "ui-entity-editor-new-field-helper-icon", href: helpUrl, target: "_blank" } })
				);
			}
		}

		var childElements = [ label ];
		var elements = BX.prop.getArray(params, "elements", []);
		for(var i = 0, length = elements.length; i < length; i++)
		{
			childElements.push(elements[i]);
		}

		var container = BX.create(
			"div",
			{
				children: childElements
			}
		);

		var containerSettings = BX.prop.getObject(params, "containerSettings", null);
		if(containerSettings)
		{
			BX.adjust(container, containerSettings);
		}
		this._optionWrapper.appendChild(container);

		return element;
	};
	BX.UI.EntityEditorFieldConfigurator.prototype.onSaveButtonClick = function(e)
	{
		if(this._isLocked)
		{
			return;
		}

		if(this._mandatoryConfigurator)
		{
			if(this._mandatoryConfigurator.isChanged())
			{
				this._mandatoryConfigurator.acceptChanges();
			}
			this._mandatoryConfigurator.close();
		}

		var params = this.prepareSaveParams();

		BX.onCustomEvent(this, "onSave", [ this, params]);
	};
	BX.UI.EntityEditorFieldConfigurator.prototype.prepareSaveParams = function()
	{
		var params =
			{
				typeId: this._typeId,
				label: this._labelInput.value
			};

		if(this._field)
		{
			params["field"] = this._field;
			params["mandatory"] = this._isRequiredCheckBox
				? this._isRequiredCheckBox.checked
				: (this._field.isRequired() || this._field.isRequiredByAttribute());
		}
		else
		{
			if(this._isRequiredCheckBox)
			{
				params["mandatory"] = this._isRequiredCheckBox.checked;
			}
		}

		params["showAlways"] = this._showAlwaysCheckBox.checked;
		params['settings'] = (params['settings'] || []);

		if (this._useTimezoneCheckBox)
		{
			params['settings']['USE_TIMEZONE'] = (this._useTimezoneCheckBox.checked ? 'Y' : 'N');
		}

		if(this._typeId === "list" && this._enumConfigurator)
		{
			params["innerConfig"] = (this._field) ? this._field.getInnerConfig() : {};
			params["enumeration"] = this._enumConfigurator.prepareSaveParams();
		}

		return params;
	};
	BX.UI.EntityEditorFieldConfigurator.prototype.onCancelButtonClick = function(e)
	{
		if(this._isLocked)
		{
			return;
		}

		var params = { typeId: this._typeId };
		if(this._field)
		{
			params["field"] = this._field;
		}

		BX.onCustomEvent(this, "onCancel", [ this, params ]);
	};
	BX.UI.EntityEditorFieldConfigurator.prototype.setLocked = function(locked)
	{
		locked = !!locked;
		if(this._isLocked === locked)
		{
			return;
		}

		this._isLocked = locked;
		if(this._isLocked)
		{
			BX.addClass(this._saveButton, "ui-btn-clock");
		}
		else
		{
			BX.removeClass(this._saveButton, "ui-btn-clock");
		}
	};
	BX.UI.EntityEditorFieldConfigurator.prototype.getField = function()
	{
		return this._field;
	};
	BX.UI.EntityEditorFieldConfigurator.create = function(id, settings)
	{
		var self = new BX.UI.EntityEditorFieldConfigurator();
		self.initialize(id, settings);
		return self;
	};
}
if (typeof BX.UI.EntityEditorEnumConfigurator === "undefined")
{
	BX.UI.EntityEditorEnumConfigurator = function()
	{
		this._settings = null;

		this._enumInfo = null;

		this._wrapper = null;
		this._nextNode = null;
		this._enumItemWrapper = null;
		this._enumItemContainer = null;
		this._enumButtonWrapper = null;
		this._draggable = null;

		this._enumItems = [];

		this.displaySelect = null;
		this.displaySelectValue = null;
		this.defaultDisplaySelectValue = 'UI';
		this.showDisplaySettings = null;
	};

	BX.UI.EntityEditorEnumConfigurator.prototype = {
		initialize: function (settings)
		{
			this._settings = settings ? settings : {};

			this._enumInfo = BX.prop.getObject(this._settings, "enumInfo", {});
			this._wrapper = BX.prop.getElementNode(this._settings, "wrapper", null);
			this._nextNode = BX.prop.getElementNode(this._settings, "nextNode", null);
			this.showDisplaySettings = BX.prop.getBoolean(this._settings, "showDisplaySettings", false);
			this.displaySelectValue = BX.prop.getString(
				this._settings,
				'display',
				this.defaultDisplaySelectValue
			);

			if (!this.displaySelectValue.length)
			{
				this.displaySelectValue = this.defaultDisplaySelectValue;
			}
		},
		layout: function()
		{
			if (BX.Type.isDomNode(this._wrapper))
			{
				this.layoutElements();
				if (this.showDisplaySettings)
				{
					this.layoutDisplay();
				}
			}
		},
		layoutElements: function()
		{
			var isNextNode = BX.Type.isDomNode(this._nextNode);
			var enumContainer = this.getEnumerationContainer();
			var elements = [
				BX.create("hr", { props: { className: "ui-entity-editor-line" } }),
				BX.create(
					"div",
					{
						props: { className: "ui-entity-editor-block-title" },
						children: [
							BX.create(
								"span",
								{
									attrs: { className: "ui-entity-editor-block-title-text" },
									text: BX.message("UI_ENTITY_EDITOR_UF_ENUM_ITEMS")
								}
							)
						]
					}
				),
				enumContainer
			];
			for (var i = 0; i < elements.length; i++)
			{
				if (isNextNode)
				{
					this._wrapper.insertBefore(elements[i], this._nextNode);
				}
				else
				{
					this._wrapper.appendChild(elements[i]);
				}
			}
		},
		layoutDisplay: function()
		{
			var displayWrapper = BX.Dom.create('div', {
				attrs: {
					class: 'ui-entity-editor-content-block',
					style: 'padding-right:38px;'
				},
				html: '<hr class="ui-entity-editor-line">'
			});

			var blockTitle = BX.Dom.create('div', {
				attrs: {
					class: 'ui-entity-editor-block-title'
				},
				children: [
					BX.Dom.create('span', {
						attrs: {
							class: 'ui-entity-editor-block-title-text'
						},
						props: {
							text: BX.Loc.getMessage('UI_ENTITY_EDITOR_UF_ENUM_ITEMS')
						}
					}),
				]
			});

			displayWrapper.appendChild(blockTitle);
			blockTitle.appendChild(this.getDisplaySelect());

			if (BX.Type.isDomNode(this._nextNode))
			{
				this._wrapper.insertBefore(displayWrapper, this._nextNode);
			}
			else
			{
				this._wrapper.appendChild(displayWrapper);
			}
		},
		getDisplaySelect: function()
		{
			if (!this.displaySelect)
			{
				var displaySelect = BX.Dom.create('select', {
					attrs: {
						className: 'main-ui-control main-enum-dialog-input'
					},
					props: {
						name: 'display'
					}
				});
				var items = {
					UI: BX.Loc.getMessage('UI_ENTITY_EDITOR_UF_ENUM_DISPLAY_UI'),
					DIALOG: BX.Loc.getMessage('UI_ENTITY_EDITOR_UF_ENUM_DISPLAY_DIALOG'),
					LIST: BX.Loc.getMessage('UI_ENTITY_EDITOR_UF_ENUM_DISPLAY_LIST'),
					CHECKBOX: BX.Loc.getMessage('UI_ENTITY_EDITOR_UF_ENUM_DISPLAY_CHECKBOX')
				};

				for (var displayName in items)
				{
					var option = BX.Dom.create('option', {
						attrs: {
							value: displayName
						},
						props: {
							text: items[displayName]
						}
					});

					displaySelect.appendChild(option);
				}
				displaySelect.value = this.getDisplaySelectValue();
				this.displaySelect = displaySelect;
			}

			return this.displaySelect;
		},
		getDisplaySelectValue: function()
		{
			return (this.displaySelect ? this.displaySelect.value : this.displaySelectValue);
		},
		getEnumerationContainer: function()
		{
			this._enumItemWrapper = BX.create(
				"div",
				{
					props: { className: "ui-entity-editor-content-block" }
				}
			);

			this._enumItemContainer = BX.create("div", { props: { className: "ui-entity-editor-content-block" } });
			this._enumItemWrapper.appendChild(this._enumItemContainer);

			this._enumButtonWrapper = BX.create(
				"div",
				{ props: { className: "ui-entity-editor-content-block-add-field" } }
			);
			this._enumItemWrapper.appendChild(this._enumButtonWrapper);

			this._enumButtonWrapper.appendChild(
				BX.create(
					"span",
					{
						props: { className: "ui-entity-card-content-add-field" },
						events: { click: BX.delegate(this.onEnumerationItemAddButtonClick, this) },
						text: BX.message("UI_ENTITY_EDITOR_ADD")
					}
				)
			);

			var enumItems = BX.Runtime.clone(BX.prop.getArray(this._enumInfo, "enumItems", []));
			var innerConfig = BX.prop.getObject(this._enumInfo, "innerConfig", {});
			var itemsConfig = BX.prop.getObject(innerConfig, "itemsConfig", {});
			var fakeValues = BX.prop.getArray(itemsConfig, "fakeValues", []);
			var systemValues = BX.prop.getArray(itemsConfig, "systemValues", []);
			var systemInitText = BX.prop.getObject(itemsConfig, "systemInitText", {});
			for(var i = 0, length = enumItems.length; i < length; i++)
			{
				if (enumItems[i].hasOwnProperty("ID"))
				{
					var id = enumItems[i]["ID"];
					var isFakeItem = (fakeValues.indexOf(id) >= 0);
					var isSystemItem = (systemValues.indexOf(id) >= 0);
					var hasInitText = (
						isSystemItem
						&& systemInitText.hasOwnProperty(id)
						&& BX.Type.isString(systemInitText[id])
					);
					enumItems[i]["IS_FAKE"] = (isFakeItem) ? "Y" : "N";
					enumItems[i]["IS_SYSTEM"] = (isSystemItem) ? "Y" : "N";
					enumItems[i]["INIT_TEXT"] = (hasInitText) ? systemInitText[id] : "";
				}
				this.createEnumerationItem(enumItems[i]);
			}
			this.createEnumerationItem();

			this._draggable = new BX.UI.DragAndDrop.Draggable({
				container: this._enumItemContainer,
				draggable: '.ui-ctl-row',
				dragElement: '.ui-ctl-row-draggable',
				type: BX.UI.DragAndDrop.Draggable.CLONE
			});

			return this._enumItemWrapper;
		},
		onEnumerationItemAddButtonClick: function(e)
		{
			this.createEnumerationItem().focus();
		},
		createEnumerationItem: function(data)
		{
			var item = BX.UI.EntityEditorFieldConfiguratorEnumItem.create(
				"",
				{
					configurator: this,
					container: this._enumItemContainer,
					data: data
				}
			);

			this._enumItems.push(item);
			item.layout();
			return item;
		},
		removeEnumerationItem: function(item)
		{
			for(var i = 0, length = this._enumItems.length; i < length; i++)
			{
				if(this._enumItems[i] === item)
				{
					this._enumItems[i].clearLayout();
					this._enumItems.splice(i, 1);
					break;
				}
			}
		},
		prepareSaveParams: function()
		{
			var result = [];

			var hashes = [];
			var sortIndex;
			for(var i = 0, length = this._enumItems.length; i < length; i++)
			{
				var enumData = this._enumItems[i].prepareData();
				if(!enumData)
				{
					continue;
				}

				var hash = BX.util.hashCode(enumData["VALUE"]);
				if(BX.util.in_array(hash, hashes))
				{
					continue;
				}

				hashes.push(hash);
				sortIndex = -1;
				if (this._draggable)
				{
					sortIndex = this._draggable.getElementIndex(this._enumItems[i].getDraggableContainer());
				}
				enumData["SORT"] = (sortIndex >= 0) ? sortIndex * 100 : (result.length + 1) * 100;
				result.push(enumData);
			}

			return result;
		}
	};

	BX.UI.EntityEditorEnumConfigurator.create = function(settings)
	{
		var self = new BX.UI.EntityEditorEnumConfigurator();
		self.initialize(settings);
		return self;
	};
}
if(typeof BX.UI.EntityEditorFieldConfiguratorEnumItem === "undefined")
{
	BX.UI.EntityEditorFieldConfiguratorEnumItem = function()
	{
		this._id = "";
		this._settings = null;
		this._data = null;
		this._configurator = null;
		this._container = null;
		this._labelInput = null;

		this._hasLayout = false;

		this._isFake = null;
		this._isSystem = null;

		this._initText = "";

		this._onRevertButtonClickHandler = BX.delegate(this.onRevertButtonClick, this);
		this._onChangeTextHandler = BX.delegate(this.onChangeText, this);
	};
	BX.UI.EntityEditorFieldConfiguratorEnumItem.prototype = {
		initialize: function(id, settings)
		{
			this._id = BX.type.isNotEmptyString(id) ? id : BX.util.getRandomString(4);
			this._settings = BX.type.isPlainObject(settings) ? settings : {};

			this._data = BX.prop.getObject(this._settings, "data", {});
			this._configurator = BX.prop.get(this._settings, "configurator");
			this._container = BX.prop.getElementNode(this._settings, "container");

			this._isFake = (BX.prop.getString(this._data, "IS_FAKE", "N") === "Y");
			this._isSystem = (BX.prop.getString(this._data, "IS_SYSTEM", "N") === "Y");

			this._initText = BX.prop.getString(this._data, "INIT_TEXT", "");
		},
		isFake: function()
		{
			return this._isFake;
		},
		isSystem: function()
		{
			return this._isSystem;
		},
		getDraggableContainer: function()
		{
			return (this._hasLayout && BX.Type.isDomNode(this._wrapper)) ? this._wrapper : null;
		},
		layout: function()
		{
			if(this._hasLayout)
			{
				return;
			}

			this._wrapper = BX.create("div", {
				props: { className: "ui-ctl ui-ctl-textbox ui-ctl-w100 ui-ctl-row" },
				style: { marginTop: "10px", marginBottom: "10px" }
			});

			if (!this.isFake())
			{
				this._wrapper.appendChild(
					BX.create("span", {
						props: { className: "ui-ctl-row-draggable" }
					})
				);
			}

			this._labelInput = BX.create(
				"input",
				{
					props:
						{
							className: "ui-ctl-element",
							placeholder: BX.message("UI_ENTITY_EDITOR_NEW_LIST_ITEM"),
							type: "input",
							value: BX.prop.getString(this._data, "VALUE", "")
						}
				}
			);

			this._wrapper.appendChild(this._labelInput);
			if (this.isFake())
			{
				this._labelInput.setAttribute("disabled", "");
				this._labelInput.style.cursor = "auto";
				this._wrapper.appendChild(
					BX.create("div", { props: { className: "ui-entity-editor-content-remove-block-system" } })
				);
			}
			else if (this.isSystem())
			{
				if (this.isInitialTextDifferent())
				{
					this._revertButton = BX.create(
						"div",
						{
							props: { className: "ui-entity-editor-content-revert-name-block" },
							events: { click: this._onRevertButtonClickHandler }
						}
					);
				}
				else
				{
					this._revertButton = BX.create(
						"div",
						{
							props: { className: "ui-entity-editor-content-remove-block-system" }
						}
					);
				}
				this._wrapper.appendChild(this._revertButton);

				BX.Event.bind(this._labelInput, "keyup", this._onChangeTextHandler);
				BX.Event.bind(this._labelInput, "input", this._onChangeTextHandler);
			}
			else
			{
				this._wrapper.appendChild(
					BX.create(
						"div",
						{
							props: { className: "ui-entity-editor-content-remove-block" },
							events: { click: BX.delegate(this.onDeleteButtonClick, this) }
						}
					)
				);
			}

			var anchor = BX.prop.getElementNode(this._settings, "anchor");
			if(anchor)
			{
				this._container.insertBefore(this._wrapper, anchor);
			}
			else
			{
				this._container.appendChild(this._wrapper);
			}

			this._hasLayout = true;
		},
		clearLayout: function()
		{
			if(!this._hasLayout)
			{
				return;
			}

			if (BX.Type.isDomNode(this._revertButton))
			{
				BX.Event.unbind(this._revertButton, "click", this._onRevertButtonClickHandler);
			}

			if (BX.Type.isDomNode(this._labelInput))
			{
				BX.Event.unbind(this._labelInput, "keyup", this._onChangeTextHandler);
				BX.Event.unbind(this._labelInput, "input", this._onChangeTextHandler);
			}

			this._wrapper = BX.remove(this._wrapper);
			this._hasLayout = false;
		},
		focus: function()
		{
			if(this._labelInput)
			{
				setTimeout(function() {
					this._labelInput.focus();
				}.bind(this), 0);
			}
		},
		prepareData: function()
		{
			var value = this._labelInput ? BX.util.trim(this._labelInput.value) : "";
			if(value === "")
			{
				return null;
			}

			var data = {
				"IS_FAKE": (BX.prop.getString(this._data, "IS_FAKE", "N") === "Y") ? "Y" : "N",
				"IS_SYSTEM": (BX.prop.getString(this._data, "IS_SYSTEM", "N") === "Y") ? "Y" : "N",
				"VALUE": value
			};

			var id = BX.prop.get(this._data, "ID", null);
			if (id !== null)
			{
				data["ID"] = id;
			}

			var xmlId = BX.prop.get(this._data, "XML_ID", null);
			if (xmlId !== null)
			{
				data["XML_ID"] = xmlId;
			}

			return data;
		},
		isInitialTextDifferent: function()
		{
			return (
				(BX.Type.isDomNode(this._labelInput))
				? (this._initText !== this._labelInput.value)
				: false
			);
		},
		toggleRevertButton: function()
		{
			if (this.isSystem() && BX.Type.isDomNode(this._revertButton))
			{
				var systemClassName = "ui-entity-editor-content-remove-block-system";
				var revertClassName = "ui-entity-editor-content-revert-name-block";
				if (this.isInitialTextDifferent())
				{
					if (BX.Dom.hasClass(this._revertButton, systemClassName))
					{
						BX.Dom.removeClass(this._revertButton, systemClassName);
						BX.Dom.addClass(this._revertButton, revertClassName);
						BX.Event.bind(this._revertButton, "click", this._onRevertButtonClickHandler);
					}
				}
				else
				{
					if (BX.Dom.hasClass(this._revertButton, revertClassName))
					{
						BX.Dom.removeClass(this._revertButton, revertClassName);
						BX.Dom.addClass(this._revertButton, systemClassName);
						BX.Event.unbind(this._revertButton, "click", this._onRevertButtonClickHandler);
					}
				}
			}
		},
		revertText: function()
		{
			if (this.isSystem())
			{
				if (BX.Type.isStringFilled(this._initText) && BX.Type.isDomNode(this._labelInput))
				{
					this._labelInput.value = this._initText;
				}
			}
		},
		onChangeText: function()
		{
			this.toggleRevertButton();
		},
		onDeleteButtonClick: function(e)
		{
			this._configurator.removeEnumerationItem(this);
		},
		onRevertButtonClick: function(e)
		{
			this.revertText();
			this.toggleRevertButton();
		}
	};
	BX.UI.EntityEditorFieldConfiguratorEnumItem.create = function(id, settings)
	{
		var self = new BX.UI.EntityEditorFieldConfiguratorEnumItem();
		self.initialize(id, settings);
		return self;
	};
}
