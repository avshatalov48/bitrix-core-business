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
				window.open(this.getCreationPageUrl(typeId));
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
						mandatoryConfigurator: null
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
			},
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
		this._enumItemWrapper = null;
		this._enumItemContainer = null;
		this._enumButtonWrapper = null;
		this._optionWrapper = null;

		this._enumItems = null;

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
		this._enumItems = [];
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

	BX.UI.EntityEditorFieldConfigurator.prototype.layoutInternal = function()
	{
		this._wrapper.appendChild(this.getInputContainer());
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
					labelSettings: { props: { className: "ui-entity-new-field-addiction-label" } },
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

	BX.UI.EntityEditorFieldConfigurator.prototype.appendEnumerationSettings = function()
	{
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
					labelSettings: { props: { className: "ui-entity-new-field-addiction-label" } },
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
		this._enumItemWrapper = null;
		this._enumButtonWrapper = null;
		this._enumItemContainer = null;
		this._optionWrapper = null;

		this._enumItems = [];

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
			props: { className: "ui-ctl ui-ctl-checkbox ui-ctl-xs" },
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
				? this._isRequiredCheckBox.checked : this._field.isRequired();
		}
		else
		{
			if(this._isRequiredCheckBox)
			{
				params["mandatory"] = this._isRequiredCheckBox.checked;
			}
		}

		params["showAlways"] = this._showAlwaysCheckBox.checked;
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