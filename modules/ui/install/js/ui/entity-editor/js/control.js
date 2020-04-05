/**
 * @author Grigoriy Zavodov <zavodov@gmail.com>
 * @module ui
 * @version 1.0
 * @copyright 2001-2019 Bitrix
 */

BX.namespace("BX.UI");

if(typeof BX.UI.EntityEditorControlOptions === "undefined")
{
	BX.UI.EntityEditorControlOptions =
	{
		none: 0,
		showAlways: 1,
		check: function(options, option)
		{
			return((options & option) === option);
		}
	};
}

if(typeof BX.UI.EntityEditorControl === "undefined")
{
	BX.UI.EntityEditorControl = function()
	{
		this._id = "";
		this._settings = {};

		this._editor = null;
		this._parent = null;

		this._mode = BX.UI.EntityEditorMode.intermediate;
		this._modeOptions = BX.UI.EntityEditorModeOptions.none;
		this._model = null;
		this._schemeElement = null;

		this._container = null;
		this._wrapper = null;

		this._dragButton = null;
		this._dragItem = null;

		this._hasLayout = false;
		this._isValidLayout = false;

		this._isVisible = true;
		this._isActive = false;
		this._isChanged = false;
		this._isSchemeChanged = false;
		this._changeHandler = BX.delegate(this.onChange, this);

		this._contextMenuButton = null;
		this._isContextMenuOpened = false;
	};
	BX.UI.EntityEditorControl.prototype =
	{
		initialize: function(id, settings)
		{
			this._id = BX.type.isNotEmptyString(id) ? id : BX.util.getRandomString(4);
			this._settings = settings ? settings : {};

			this._editor = BX.prop.get(this._settings, "editor", null);
			this._parent = BX.prop.get(this._settings, "parent", null);

			this._model = BX.prop.get(this._settings, "model", null);

			this._schemeElement = BX.prop.get(this._settings, "schemeElement", null);
			this._container = BX.prop.getElementNode(this._settings, "container", null);

			var mode = BX.prop.getInteger(this._settings, "mode", BX.UI.EntityEditorMode.view);
			if(mode === BX.UI.EntityEditorMode.edit && this._schemeElement && !this._schemeElement.isEditable())
			{
				mode = BX.UI.EntityEditorMode.view;
			}
			this._mode = mode;

			this.doInitialize();
			this.bindModel();
		},
		doInitialize: function()
		{
		},
		bindModel: function()
		{
		},
		unbindModel: function()
		{
		},
		getMessage: function(name)
		{
			var m = BX.UI.EntityEditorControl.messages;
			return m.hasOwnProperty(name) ? m[name] : name;
		},
		getId: function()
		{
			return this._id;
		},
		getEditor: function()
		{
			return this._editor;
		},
		setEditor: function(editor)
		{
			this._editor = editor;
		},
		getParentPosition: function()
		{
			var parent = this.getParent();
			return (parent ? parent.getPosition() : { top: 0, right: 0, bottom: 0, left: 0, width: 0, height: 0 });
		},
		getParent: function()
		{
			return this._parent;
		},
		setParent: function(parent)
		{
			this._parent = parent;
		},
		getSiblingByIndex: function (index)
		{
			return this._editor ? this._editor.getControlByIndex(index) : null;
		},
		getChildCount: function()
		{
			return 0;
		},
		getChildById: function(childId)
		{
			return null;
		},
		editChild: function(child)
		{
		},
		removeChild: function(child)
		{
		},
		getChildren: function()
		{
			return [];
		},
		editChildConfiguration: function(child)
		{
		},
		areAttributesEnabled: function()
		{
			return this._schemeElement && this._schemeElement.areAttributesEnabled();
		},
		getType: function()
		{
			return this._schemeElement ? this._schemeElement.getType() : "";
		},
		getName: function()
		{
			return this._schemeElement ? this._schemeElement.getName() : "";
		},
		getTitle: function()
		{
			if(!this._schemeElement)
			{
				return "";
			}

			var title = this._schemeElement.getTitle();
			if(title === "")
			{
				title = this._schemeElement.getName();
			}

			return title;
		},
		setTitle: function(title)
		{
			if(!this._schemeElement)
			{
				return;
			}

			this._schemeElement.setTitle(title);
			this.refreshTitleLayout();
		},
		getOptionFlags: function()
		{
			return(this._schemeElement
					? this._schemeElement.getOptionFlags()
					: BX.UI.EntityEditorControlOptions.none
			);
		},
		setOptionFlags: function(flags)
		{
			if(this._schemeElement)
			{
				this._schemeElement.setOptionFlags(flags);
			}
		},
		toggleOptionFlag: function(flag)
		{
			var flags = this.getOptionFlags();
			if(BX.UI.EntityEditorControlOptions.check(flags, flag))
			{
				flags &= ~flag;
			}
			else
			{
				flags |= flag;
			}
			this.setOptionFlags(flags);
		},
		checkOptionFlag: function(flag)
		{
			return BX.UI.EntityEditorControlOptions.check(this.getOptionFlags(), flag);
		},
		getData: function()
		{
			return this._schemeElement ? this._schemeElement.getData() : {};
		},
		isVisible: function()
		{
			if(!this._isVisible)
			{
				return false;
			}

			if(this.checkOptionFlag(BX.UI.EntityEditorControlOptions.showAlways))
			{
				return true;
			}
			return BX.UI.EntityEditorVisibilityPolicy.checkVisibility(this);
		},
		setVisible: function(visible)
		{
			visible = !!visible;
			if(this._isVisible === visible)
			{
				return;
			}

			this._isVisible = visible;
			if(this._hasLayout)
			{
				this._wrapper.style.display = this._isVisible ? "" : "none";
			}
		},
		isActive: function()
		{
			return this._isActive;
		},
		setActive: function(active)
		{
			active = !!active;
			if(this._isActive === active)
			{
				return;
			}

			this._isActive = active;
			this.doSetActive();
		},
		doSetActive: function()
		{
		},
		isEditable: function()
		{
			return this._schemeElement && this._schemeElement.isEditable();
		},
		isRequired: function()
		{
			return this._schemeElement && this._schemeElement.isRequired();
		},
		isRequiredConditionally: function()
		{
			return this._schemeElement && this._schemeElement.isRequiredConditionally();
		},
		isHeading: function()
		{
			return this._schemeElement && this._schemeElement.isHeading();
		},
		getCreationPlaceholder: function()
		{
			return this._schemeElement ? this._schemeElement.getCreationPlaceholder() : "";
		},
		isReadOnly: function()
		{
			return this._editor && this._editor.isReadOnly();
		},
		getVisibilityPolicy: function()
		{
			if(this._editor && !this._editor.isVisibilityPolicyEnabled())
			{
				return BX.UI.EntityEditorVisibilityPolicy.always;
			}

			return this._schemeElement && this._schemeElement.getVisibilityPolicy();
		},
		getPosition: function()
		{
			return BX.pos(this._wrapper);
		},
		focus: function()
		{
		},
		save: function()
		{
		},
		validate: function(result)
		{
			return true;
		},
		rollback: function()
		{
		},
		getMode: function()
		{
			return this._mode;
		},
		setMode: function(mode, options)
		{
			if(!this.canChangeMode(mode))
			{
				return;
			}

			var modeOptions = BX.prop.getInteger(options, "options", BX.UI.EntityEditorModeOptions.none);
			if(this._mode === mode && this._modeOptions === modeOptions)
			{
				return;
			}

			this.onBeforeModeChange();

			this._mode = mode;
			this._modeOptions = modeOptions;
			this.doSetMode(this._mode);

			this.onAfterModeChange();

			if(BX.prop.getBoolean(options, "notify", false))
			{
				if(this._parent)
				{
					this._parent.processChildControlModeChange(this);
				}
				else if(this._editor)
				{
					this._editor.processControlModeChange(this);
				}
			}

			this._isSchemeChanged = false;
			this._isChanged = false;

			if(this._hasLayout)
			{
				this._isValidLayout = false;
			}
		},
		onBeforeModeChange: function()
		{
		},
		doSetMode: function(mode)
		{
		},
		onAfterModeChange: function()
		{
		},
		canChangeMode: function(mode)
		{
			if(mode === BX.UI.EntityEditorMode.edit)
			{
				return this.isEditable();
			}
			return true;
		},
		isModeToggleEnabled: function()
		{
			return this._editor.isModeToggleEnabled();
		},
		toggleMode: function(notify, options)
		{
			if(!this.isModeToggleEnabled())
			{
				return false;
			}

			this.setMode(
				this._mode === BX.UI.EntityEditorMode.view
					? BX.UI.EntityEditorMode.edit : BX.UI.EntityEditorMode.view,
				{ notify: notify }
			);

			if(BX.prop.getBoolean(options, "refreshLayout", true))
			{
				this.refreshLayout();
			}
			return true;
		},
		isSingleEditEnabled: function()
		{
			//"Single Edit" - control may be switched to edit mode independently of parent control (section)
			return(
				this.isModeToggleEnabled()
				&& this.isEditable()
				&& !this.getDataBooleanParam("enableEditInView", false)
				&& this.getDataBooleanParam("enableSingleEdit", true)
			);
		},
		isInSingleEditMode: function()
		{
			if(!this.isInEditMode())
			{
				return false;
			}

			return(this.checkModeOption(BX.UI.EntityEditorModeOptions.exclusive)
				|| this.checkModeOption(BX.UI.EntityEditorModeOptions.individual)
			);
		},
		isInEditMode: function()
		{
			return this._mode === BX.UI.EntityEditorMode.edit;
		},
		isInViewMode: function()
		{
			return this._mode === BX.UI.EntityEditorMode.view;
		},
		checkModeOption: function(option)
		{
			return BX.UI.EntityEditorModeOptions.check(this._modeOptions, option);
		},
		getContextId: function()
		{
			return this._editor ? this._editor.getContextId() : '';
		},
		getExternalContextId: function()
		{
			return this._editor ? this._editor.getExternalContextId() : '';
		},
		processSchemeChange: function()
		{
		},
		processAvailableSchemeElementsChange: function()
		{
		},
		processChildControlModeChange: function(control)
		{
		},
		processChildControlChange: function(control, params)
		{
		},
		isChanged: function()
		{
			return this._isChanged;
		},
		markAsChanged: function(params)
		{
			if(typeof(params) === "undefined")
			{
				params = {};
			}

			var control = BX.prop.get(params, "control", null);
			if(!(control && control instanceof BX.UI.EntityEditorControl))
			{
				control = params["control"] = this;
			}

			if(!control.isInEditMode())
			{
				return;
			}

			if(!this._isChanged)
			{
				this._isChanged = true;
			}

			this.notifyChanged(params);
		},
		isSchemeChanged: function()
		{
			return this._isSchemeChanged;
		},
		markSchemeAsChanged: function()
		{
			if(this._isSchemeChanged)
			{
				return;
			}

			this._isSchemeChanged = true;
		},
		saveScheme: function()
		{
			if(!this._isSchemeChanged)
			{
				return;
			}

			this.commitSchemeChanges();
			return this._editor.saveScheme();
		},
		commitSchemeChanges: function()
		{
			if(!this._isSchemeChanged)
			{
				return;
			}

			this._editor.updateSchemeElement(this._schemeElement);
			this._isSchemeChanged = false;
		},
		getRootContainer: function()
		{
			return this._editor ? this._editor.getContainer() : null;
		},
		getRootContainerPosition: function()
		{
			return BX.pos(this.getRootContainer());
		},
		getContainer: function()
		{
			return this._container;
		},
		setContainer: function (container)
		{
			this._container = container;
			if(this._hasLayout)
			{
				this._hasLayout = false;
			}
		},
		getWrapper: function()
		{
			return this._wrapper;
		},
		enablePointerEvents: function(enable)
		{
			if(this._wrapper)
			{
				this._wrapper.style.pointerEvents = enable ? "" : "none";
			}
		},
		getModel: function()
		{
			return this._model;
		},
		getSchemeElement: function()
		{
			return this._schemeElement;
		},
		hasScheme: function()
		{
			return !!this._schemeElement;
		},
		getDataBooleanParam: function(name, defaultval)
		{
			return(this._schemeElement
					? this._schemeElement.getDataBooleanParam(name, defaultval)
					: defaultval
			);
		},
		layout: function(options)
		{
		},
		registerLayout:  function(options)
		{
			if(!this._wrapper)
			{
				return;
			}

			this._wrapper.setAttribute("data-cid", this.getId());

			//HACK: Fix positions of context menu and drag button for readonly fields in editing section
			if(this.isInViewMode() && this._parent && this._parent.isInEditMode())
			{
				BX.addClass(this._wrapper, "ui-entity-editor-content-block-field-readonly");
			}
			else
			{
				BX.removeClass(this._wrapper, "ui-entity-editor-content-block-field-readonly");
			}

			if(typeof options === "undefined")
			{
				options = {};
			}

			if(!BX.prop.getBoolean(options, "preservePosition", false))
			{
				var anchor = BX.prop.getElementNode(options, "anchor", null);
				if (anchor)
				{
					BX.addClass(this._wrapper, "ui-entity-card-content-hide");
					this._container.insertBefore(this._wrapper, anchor);
					setTimeout(BX.delegate(function ()
					{
						BX.removeClass(this._wrapper, "ui-entity-card-content-hide");
						BX.addClass(this._wrapper, "ui-entity-card-content-show");
					}, this), 1);
					setTimeout(BX.delegate(function ()
					{
						BX.removeClass(this._wrapper, "ui-entity-card-content-show");
					}, this), 310);
				}
				else
				{
					this._container.appendChild(this._wrapper);
				}
			}

			this._isValidLayout = true;
			this.doRegisterLayout();
		},
		doRegisterLayout: function()
		{
		},
		refreshLayout: function(options)
		{
			if(!this._hasLayout)
			{
				return;
			}

			this.clearLayout({ preservePosition: true });

			if(!BX.type.isPlainObject(options))
			{
				options = {};
			}

			options["preservePosition"] = true;
			this.layout(options);
		},
		clearLayout: function(options)
		{
		},
		refreshTitleLayout: function()
		{
		},
		releaseLayout: function ()
		{
			this._wrapper = null;
		},
		release: function()
		{
		},
		reset: function()
		{
		},
		hide: function()
		{
			if(this.isRequired() || this.isRequiredConditionally())
			{
				return;
			}

			if(this._parent)
			{
				BX.addClass(this._wrapper, "ui-entity-card-content-hide");
				setTimeout(BX.delegate(function ()
				{
					this._parent.removeChild(this);
				}, this), 350);
			}
			else
			{
				this.clearLayout();
			}
		},
		showMessageDialog: function(id, title, content)
		{
			if(this._editor)
			{
				this._editor.showMessageDialog(id, title, content);
			}
		},
		prepareSaveData: function(data)
		{
		},
		onBeforeSubmit: function()
		{
		},
		onChange: function(e)
		{
			this.markAsChanged();
		},
		notifyChanged: function(params)
		{
			if(typeof(params) === "undefined")
			{
				params = {};
			}

			if(this._parent)
			{
				this._parent.processChildControlChange(this, params);
			}
			else if(this._editor)
			{
				this._editor.processControlChange(this, params);
			}
		},
		//region D&D
		isDragEnabled: function()
		{
			if(!this._editor)
			{
				return false;
			}

			if(!this._editor.canChangeScheme())
			{
				return false;
			}

			if(!this._schemeElement.isDragEnabled())
			{
				return false;
			}

			return BX.prop.getBoolean(
				BX.prop.getObject(
					this._editor.getDragConfig(this.getDragObjectType()),
					"modes",
					{}
				),
				BX.UI.EntityEditorMode.getName(this._mode),
				false
			);
		},
		getDragObjectType: function()
		{
			return BX.UI.EditorDragObjectType.intermediate;
		},
		getChildDragObjectType: function()
		{
			return BX.UI.EditorDragObjectType.intermediate;
		},
		getDragScope: function()
		{
			if(this._parent)
			{
				return this._parent.getChildDragScope();
			}

			if(!this._editor)
			{
				return BX.UI.EditorDragScope.getDefault();
			}

			return BX.prop.getInteger(
				this._editor.getDragConfig(this.getDragObjectType()),
				"scope",
				BX.UI.EditorDragScope.getDefault()
			);
		},
		getChildDragScope: function()
		{
			if(!this._editor)
			{
				return BX.UI.EditorDragScope.getDefault();
			}

			return BX.prop.getInteger(
				this._editor.getDragConfig(this.getChildDragObjectType()),
				"scope",
				BX.UI.EditorDragScope.getDefault()
			);
		},
		getDraggableContextId: function()
		{
			return this._draggableContextId;
		},
		setDraggableContextId: function(contextId)
		{
			this._draggableContextId = contextId;
		},
		createDragButton: function()
		{
			return this._dragButton;
		},
		//endregion
		//region Context Menu
		isContextMenuEnabled: function()
		{
			if(this._editor && !(this._editor.isFieldsContextMenuEnabled() && this._editor.canChangeScheme()))
			{
				return false;
			}

			return this._schemeElement.isContextMenuEnabled();
		},
		onContextMenuShow: function()
		{
			this._isContextMenuOpened = true;
		},
		onContextMenuClose: function()
		{
			BX.PopupMenu.destroy(this._id);
			this._isContextMenuOpened = false;
		},
		createContextMenuButton: function()
		{
			this._contextMenuButton = BX.create("div",
			{
				props: { className: "ui-entity-editor-block-context-menu" },
				events: { click: BX.delegate(this.onContextButtonClick, this) }
			});

			return this._contextMenuButton;
		},
		onContextButtonClick: function(e)
		{
			if(!this._isContextMenuOpened)
			{
				this.openContextMenu();
			}
			else
			{
				this.closeContextMenu();
			}
		},
		openContextMenu: function()
		{
			if(this._isContextMenuOpened)
			{
				return;
			}

			var menu = this.prepareContextMenuItems();
			if(BX.type.isArray(menu) && menu.length > 0)
			{
				var handler = BX.delegate( this.onContextMenuItemSelect, this);
				for(var i = 0, length = menu.length; i < length; i++)
				{
					if(typeof menu[i]["onclick"] === "undefined")
					{
						menu[i]["onclick"] = handler;
					}
				}
				BX.PopupMenu.show(
					this._id,
					this._contextMenuButton,
					menu,
					{
						angle: false,
						events:
							{
								onPopupShow: BX.delegate(this.onContextMenuShow, this),
								onPopupClose: BX.delegate(this.onContextMenuClose, this)
							}
					}
				);
			}
		},
		closeContextMenu: function()
		{
			var menu = BX.PopupMenu.getMenuById(this._id);
			if(menu)
			{
				menu.popupWindow.close();
			}
		},
		onContextMenuItemSelect: function(e, item)
		{
			this.processContextMenuCommand(e, BX.prop.getString(item, "value"));
		},
		prepareContextMenuItems: function()
		{
			return [];
		},
		processContextMenuCommand: function(e, command)
		{
		},
		//endregion
		isWaitingForInput: function()
		{
			return false;
		}
	};
}

if(typeof BX.UI.EntityEditorField === "undefined")
{
	BX.UI.EntityEditorField = function()
	{
		BX.UI.EntityEditorField.superclass.constructor.apply(this);
		this._titleWrapper = null;

		this._singleEditButton = null;
		this._singleEditController = null;
		this._singleEditTimeoutHandle = 0;
		this._viewController = null;

		this._singleEditButtonHandler = BX.delegate(this.onSingleEditBtnClick, this);

		this._validators = null;
		this._hasError = false;
		this._errorContainer = null;

		this._layoutAttributes = null;
		this._spotlight = null;

		this._dragObjectType = BX.UI.EditorDragObjectType.field;
	};
	BX.extend(BX.UI.EntityEditorField, BX.UI.EntityEditorControl);
	BX.UI.EntityEditorField.prototype.isNewEntity = function()
	{
		return this._editor && this._editor.isNew();
	};
	BX.UI.EntityEditorField.prototype.configure = function()
	{
		if(this._parent)
		{
			this._parent.editChildConfiguration(this);
		}
	};
	BX.UI.EntityEditorField.prototype.hasAttributeConfiguration = function(attributeTypeId)
	{
		return this._schemeElement.hasAttributeConfiguration(attributeTypeId);
	};
	BX.UI.EntityEditorField.prototype.getAttributeConfiguration = function(attributeTypeId)
	{
		return this._schemeElement.getAttributeConfiguration(attributeTypeId);
	};
	BX.UI.EntityEditorField.prototype.setAttributeConfiguration = function(configuration)
	{
		return this._schemeElement.setAttributeConfiguration(configuration);
	};
	BX.UI.EntityEditorField.prototype.removeAttributeConfiguration = function(attributeTypeId)
	{
		return this._schemeElement.removeAttributeConfiguration(attributeTypeId);
	};
	BX.UI.EntityEditorField.prototype.getDuplicateControlConfig = function()
	{
		return this._schemeElement ? this._schemeElement.getDataObjectParam("duplicateControl", null) : null;
	};
	BX.UI.EntityEditorField.prototype.markAsChanged = function(params)
	{
		BX.UI.EntityEditorField.superclass.markAsChanged.apply(this, arguments);
		if(this.hasError())
		{
			this.clearError();
		}

		var validators = this.getValidators();
		for(var i = 0, length = validators.length; i < length; i++)
		{
			validators[i].processFieldChange(this);
		}
	};
	BX.UI.EntityEditorField.prototype.bindModel = function()
	{
	};
	BX.UI.EntityEditorField.prototype.onBeforeModeChange = function()
	{
		//Enable animation if it is going to view mode
		this._layoutAttributes = null;
		if(this.isInEditMode())
		{
			this._layoutAttributes = { animate: "show" };
		}
	};
	BX.UI.EntityEditorField.prototype.onModelChange = function(sender, params)
	{
		this.processModelChange(params);
	};
	BX.UI.EntityEditorField.prototype.onModelLock = function(sender, params)
	{
		this.processModelLock(params);
	};
	BX.UI.EntityEditorField.prototype.processModelChange = function(params)
	{
	};
	BX.UI.EntityEditorField.prototype.processModelLock = function(params)
	{
	};
	BX.UI.EntityEditorField.prototype.hasContentWrapper = function()
	{
		return this.getContentWrapper() !== null;
	};
	BX.UI.EntityEditorField.prototype.getContentWrapper = function()
	{
		return null;
	};
	BX.UI.EntityEditorField.prototype.ensureWrapperCreated = function(params)
	{
		if(!this._wrapper)
		{
			this._wrapper = BX.create("div", { props: { className: "ui-entity-editor-content-block" } });
		}

		var classNames = BX.prop.getArray(params, "classNames", []);
		for(var i = 0, length = classNames.length;  i < length; i++)
		{
			BX.addClass(this._wrapper, classNames[i]);
		}
		return this._wrapper;
	};
	BX.UI.EntityEditorField.prototype.adjustWrapper = function()
	{
		if(!this._wrapper)
		{
			return;
		}

		if(this.isInEditMode()
			&& (this.checkModeOption(BX.UI.EntityEditorModeOptions.exclusive)
				|| this.checkModeOption(BX.UI.EntityEditorModeOptions.individual)
			)
		)
		{
			BX.addClass(this._wrapper, "ui-entity-editor-content-block-edit");
		}
		else
		{
			BX.removeClass(this._wrapper, "ui-entity-editor-content-block-edit");
		}

		//region Applying layout attributes
		/*
		for(var i = this._wrapper.attributes.length - 1; i >= 0; i--)
		{
			this._wrapper.removeAttribute(this._wrapper.attributes[i].name);
		}
		*/
		if(this._layoutAttributes)
		{
			for(var key in this._layoutAttributes)
			{
				if(this._layoutAttributes.hasOwnProperty(key))
				{
					this._wrapper.setAttribute("data-" + key, this._layoutAttributes[key]);
				}
			}
			this._layoutAttributes = null;
		}
		//endregion
	};
	BX.UI.EntityEditorField.prototype.createTitleNode = function(title)
	{
		this._titleWrapper = BX.create("div",
			{
				attrs: { className: "ui-entity-editor-block-title" }
			}
		);

		this.prepareTitleLayout(BX.type.isNotEmptyString(title) ? title : this.getTitle());
		return this._titleWrapper;
	};
	BX.UI.EntityEditorField.prototype.prepareTitleLayout = function(title)
	{
		if(!this._titleWrapper)
		{
			return;
		}

		var titleNode = BX.create("label",
			{ attrs: { className: "ui-entity-editor-block-title-text" }, text: title }
		);

		var focusInputId = this.getFocusInputID();
		if (focusInputId !== "") {
			BX.adjust(titleNode,
				{
					attrs: { "for": focusInputId}
				}
		);
		}

		var marker = this.createTitleMarker();
		if(marker)
		{
			titleNode.appendChild(marker);
		}
		this._titleWrapper.appendChild(titleNode);

		var actionControls = this.createTitleActionControls();
		if(actionControls.length > 0)
		{
			var actionWrapper = BX.create("span", { attrs: { className: "ui-entity-editor-block-title-actions" } });
			this._titleWrapper.appendChild(actionWrapper);

			for(var i = 0, length = actionControls.length; i < length; i++)
			{
				actionWrapper.appendChild(actionControls[i]);
			}
		}
	};
	BX.UI.EntityEditorField.prototype.refreshTitleLayout = function()
	{
		if(!this._titleWrapper)
		{
			return;
		}

		BX.cleanNode(this._titleWrapper);
		this.prepareTitleLayout(this.getTitle());
	};
	BX.UI.EntityEditorField.prototype.createTitleMarker = function()
	{
		if(this._mode === BX.UI.EntityEditorMode.view)
		{
			return null;
		}

		if(this.isRequired())
		{
			return BX.create("span", { style: { color: "#f00" }, text: "*" });
		}
		else if(this.isRequiredConditionally())
		{
			return BX.create("span", { text: "*" });
		}
		return null;
	};
	BX.UI.EntityEditorField.prototype.createTitleActionControls = function()
	{
		return [];
	};
	BX.UI.EntityEditorField.prototype.clearLayout = function(options)
	{
		if(!this._hasLayout)
		{
			return;
		}

		this.releaseLightingAbilities();

		BX.UI.EntityEditorField.superclass.clearLayout.apply(this, arguments);

		if(!BX.type.isPlainObject(options))
		{
			options = {};
		}

		this.releaseDragDropAbilities();
		this.releaseSwitchingAbilities();
		if(!BX.prop.getBoolean(options, "preservePosition", false))
		{
			this._wrapper = BX.remove(this._wrapper);
		}
		else
		{
			BX.removeClass(this._wrapper, "ui-entity-editor-content-block-click-editable");
			BX.removeClass(this._wrapper, "ui-entity-editor-content-block-click-empty");
			this._wrapper = BX.cleanNode(this._wrapper);
			if(this.hasError())
			{
				this.clearError();
			}
		}

		this.doClearLayout(options);

		this._hasLayout = false;
	};
	BX.UI.EntityEditorField.prototype.doClearLayout = function(options)
	{
	};
	BX.UI.EntityEditorField.prototype.registerLayout = function(options)
	{
		var isVisible = this.isVisible();
		var isNeedToDisplay = this.isNeedToDisplay();

		this._wrapper.style.display = (isVisible && isNeedToDisplay) ? "" : "none";

		this.initializeSwitchingAbilities();
		if(this.isInEditMode() && this.checkModeOption(BX.UI.EntityEditorModeOptions.individual))
		{
			window.setTimeout(BX.delegate(this.focus, this), 0);
		}
		BX.UI.EntityEditorField.superclass.registerLayout.apply(this, arguments);

		var lighting = BX.prop.getObject(options, "lighting", null);
		if(lighting)
		{
			window.setTimeout(
				function(){ this.initializeLightingAbilities(lighting); }.bind(this),
				1000
			)
		}

		if(!isNeedToDisplay && BX.prop.getBoolean(options, "notifyIfNotDisplayed", false))
		{
			BX.UI.Notification.Center.notify(
				{
					content: BX.message("UI_ENTITY_EDITOR_FIELD_HIDDEN_IN_VIEW_MODE").replace(/#TITLE#/gi, this.getTitle()),
					position: "top-center",
					autoHideDelay: 5000
				}
			);
		}
	};
	BX.UI.EntityEditorField.prototype.hasContentToDisplay = function()
	{
		return this.hasValue();
	};
	BX.UI.EntityEditorField.prototype.isNeedToDisplay = function()
	{
		return(this._mode === BX.UI.EntityEditorMode.edit
			|| this.checkOptionFlag(BX.UI.EntityEditorControlOptions.showAlways)
			|| this.hasContentToDisplay()
		);
	};
	BX.UI.EntityEditorField.prototype.isWaitingForInput = function()
	{
		return this.isInEditMode() && this.isRequired() && !this.hasValue();
	};
	BX.UI.EntityEditorField.prototype.hide = function()
	{
		if(!(this.isRequired() || this.isRequiredConditionally()))
		{
			BX.UI.EntityEditorField.superclass.hide.apply(this, arguments);
		}
		else
		{
			this.showMessageDialog(
				"operationDenied",
				BX.message("UI_ENTITY_EDITOR_HIDE_TITLE"),
				BX.message("UI_ENTITY_EDITOR_HIDE_DENIED")
			);
		}
	};
	//region Value
	BX.UI.EntityEditorField.prototype.checkIfNotEmpty = function(value)
	{
		return BX.util.trim(value) !== "";
	};
	BX.UI.EntityEditorField.prototype.hasValue = function()
	{
		return this.checkIfNotEmpty(this.getValue());
	};
	BX.UI.EntityEditorField.prototype.getValue = function(defaultValue)
	{
		if(!this._model)
		{
			return "";
		}

		return(
			this._model.getField(
				this.getDataKey(),
				(defaultValue !== undefined ? defaultValue : "")
			)
		);
	};
	BX.UI.EntityEditorField.prototype.getStringValue = function(defaultValue)
	{
		return this._model ? this._model.getStringField(this.getName(), defaultValue) : "";
	};
	BX.UI.EntityEditorField.prototype.getRuntimeValue = function()
	{
		return "";
	};
	BX.UI.EntityEditorField.prototype.getDataKey = function()
	{
		return this.getName();
	};
	BX.UI.EntityEditorField.prototype.prepareSaveData = function(data)
	{
		data[this.getDataKey()] = this.getValue();
	};
	//endregion
	//region Validators
	BX.UI.EntityEditorField.prototype.findValidatorIndex = function(validator)
	{
		if(!this._validators)
		{
			return -1;
		}

		for(var i = 0, length = this._validators.length; i < length; i++)
		{
			if(this._validators[i] === validator)
			{
				return i;
			}
		}
		return -1;
	};
	BX.UI.EntityEditorField.prototype.addValidator = function(validator)
	{
		if(validator && this.findValidatorIndex(validator) < 0)
		{
			if(!this._validators)
			{
				this._validators = [];
			}
			this._validators.push(validator);
		}
	};
	BX.UI.EntityEditorField.prototype.removeValidator = function(validator)
	{
		if(!this._validators || !validator)
		{
			return;
		}

		var index = this.findValidatorIndex(validator);
		if(index >= 0)
		{
			this._validators.splice(index, 1);
		}
	};
	BX.UI.EntityEditorField.prototype.getValidators = function()
	{
		return this._validators ? this._validators : [];
	};
	BX.UI.EntityEditorField.prototype.hasValidators = function()
	{
		return this._validators && this._validators.length > 0;
	};
	BX.UI.EntityEditorField.prototype.executeValidators = function(result)
	{
		if(!this._validators)
		{
			return true;
		}

		var isValid = true;
		for(var i = 0, length = this._validators.length; i < length; i++)
		{
			if(!this._validators[i].validate(result))
			{
				isValid = false;
			}
		}
		return isValid;
	};
	//endregion
	BX.UI.EntityEditorField.prototype.hasError = function()
	{
		return this._hasError;
	};
	BX.UI.EntityEditorField.prototype.showError = function(error, anchor)
	{
		if(!this._errorContainer)
		{
			this._errorContainer = BX.create(
				"div",
				{ attrs: { className: "ui-entity-editor-field-error-text" } }
			);
		}

		this._errorContainer.innerHTML = error;
		this._wrapper.appendChild(this._errorContainer);
		BX.addClass(this._wrapper, "ui-entity-editor-field-error");
		this._hasError = true;
	};
	BX.UI.EntityEditorField.prototype.showRequiredFieldError =  function(anchor)
	{
		this.showError(BX.message("UI_ENTITY_EDITOR_REQUIRED_FIELD_ERROR"), anchor);
	};
	BX.UI.EntityEditorField.prototype.clearError =  function()
	{
		if(!this._hasError)
		{
			return;
		}

		if(this._errorContainer && this._errorContainer.parentNode)
		{
			this._errorContainer.parentNode.removeChild(this._errorContainer);
		}
		BX.removeClass(this._wrapper, "ui-entity-editor-field-error");
		this._hasError = false;
	};
	BX.UI.EntityEditorField.prototype.scrollAnimate = function()
	{
		var doc = BX.GetDocElement(document);
		var anchor = this._wrapper;
		window.setTimeout(
			function()
			{
				(new BX.easing(
						{
							duration : 300,
							start : { position: doc.scrollTop },
							finish: { position: BX.pos(anchor).top - 10 },
							step: function(state)
							{
								doc.scrollTop = state.position;
							}
						}
					)
				).animate();
			},
			0
		);
	};
	//region D&D
	BX.UI.EntityEditorField.prototype.createDragButton = function()
	{
		if(!this._dragButton)
		{
			this._dragButton = BX.create(
				"div",
				{
					props: { className: "ui-entity-editor-block-draggable-btn-container" },
					children:
						[
							BX.create(
								"div",
								{
									props: { className: "ui-entity-editor-draggable-btn" }
								}
							)
						]
				}
			);
		}
		return this._dragButton;
	};
	BX.UI.EntityEditorField.prototype.createGhostNode = function()
	{
		if(!this._wrapper)
		{
			return null;
		}

		var pos = BX.pos(this._wrapper);
		var node = BX.create('div',
			{
				props: { className: "ui-entity-field-grabbing"},
				children: [
					this._wrapper.cloneNode(true)
				]
			});
		node.style.width = pos.width + "px";
		node.style.height = pos.height + "px";
		return node;
	};
	BX.UI.EntityEditorField.prototype.setDragObjectType = function(type)
	{
		this._dragObjectType = type;
	};
	BX.UI.EntityEditorField.prototype.getDragObjectType = function()
	{
		return this._dragObjectType;
	};
	BX.UI.EntityEditorField.prototype.initializeDragDropAbilities = function()
	{
		if(this._dragItem)
		{
			return;
		}

		this._dragItem = BX.UI.EditorDragItemController.create(
			"field_" +  this.getId(),
			{
				charge: BX.UI.EditorFieldDragItem.create(
					{
						control: this,
						contextId: this._draggableContextId,
						scope: this.getDragScope()
					}
				),
				node: this.createDragButton(),
				showControlInDragMode: false,
				ghostOffset: { x: 0, y: 0 }
			}
		);
	};
	BX.UI.EntityEditorField.prototype.releaseDragDropAbilities = function()
	{
		if(this._dragItem)
		{
			this._dragItem.release();
			this._dragItem = null;
		}
	};
	//endregion
	//region Context Menu
	BX.UI.EntityEditorField.prototype.prepareContextMenuItems = function()
	{
		var results = [];
		results.push({ value: "hide", text: BX.message("UI_ENTITY_EDITOR_HIDE") });
		results.push({ value: "configure", text: BX.message("UI_ENTITY_EDITOR_CONFIGURE") });

		if (this._parent && this._parent.hasAdditionalMenu())
		{
			var additionalMenu = this._parent.getAdditionalMenu();
			for (var i=0; i<additionalMenu.length; i++)
			{
				results.push(additionalMenu[i]);
			}
		}

		results.push(
			{
				value: "showAlways",
				text: '<label class="ui-entity-card-context-menu-item-hide-empty-wrap">' +
				'<input type="checkbox"' +
				(this.checkOptionFlag(BX.UI.EntityEditorControlOptions.showAlways) ? ' checked = "true"' : '') +
				' class="ui-entity-card-context-menu-item-hide-empty-input">' +
				'<span class="ui-entity-card-context-menu-item-hide-empty-text">' +
				BX.message("UI_ENTITY_EDITOR_SHOW_ALWAYS") +
				'</span></label>'
			}
		);

		this.doPrepareContextMenuItems(results);
		return results;
	};
	BX.UI.EntityEditorField.prototype.doPrepareContextMenuItems = function(menuItems)
	{
	};
	BX.UI.EntityEditorField.prototype.processContextMenuCommand = function(e, command)
	{
		if(command === "showAlways")
		{
			var target = BX.getEventTarget(e);
			if(target && target.tagName === "INPUT")
			{
				this.toggleOptionFlag(BX.UI.EntityEditorControlOptions.showAlways);
				if(this._parent)
				{
					this._parent.processChildControlSchemeChange(this);
				}

				if(!this.isNeedToDisplay())
				{
					window.setTimeout(BX.delegate(this.clearLayout, this), 500);
					BX.UI.Notification.Center.notify(
						{
							content: BX.message("UI_ENTITY_EDITOR_FIELD_HIDDEN_DUE_TO_SHOW_ALWAYS_CHANGED").replace(/#TITLE#/gi, this.getTitle()),
							position: "top-center",
							autoHideDelay: 5000
						}
					);
					this.closeContextMenu();
				}
			}
			return;
		}

		if(command === "hide")
		{
			window.setTimeout(BX.delegate(this.hide, this), 500);
		}
		else if(command === "configure")
		{
			this.configure();
		}
		else if (this._parent && this._parent.hasAdditionalMenu())
		{
			this._parent.processChildAdditionalMenuCommand(this, command);
		}
		this.closeContextMenu();
	};
	//endregion
	BX.UI.EntityEditorField.prototype.initializeSwitchingAbilities = function()
	{
		if(this.isInViewMode())
		{
			if(this.isSingleEditEnabled())
			{
				BX.addClass(this._wrapper, "ui-entity-editor-content-block-click-editable");
				if(!this.hasContentToDisplay())
				{
					BX.addClass(this._wrapper, "ui-entity-editor-content-block-click-empty");
				}

				if(this._singleEditButton)
				{
					BX.bind(this._singleEditButton, "click", this._singleEditButtonHandler);
				}
			}

			if(this.hasContentWrapper()
				&& BX.UI.EntityEditorModeSwitchType.check(
					this.getModeSwitchType(BX.UI.EntityEditorMode.edit),
					BX.UI.EntityEditorModeSwitchType.content
				)
			)
			{
				this._viewController = BX.UI.EditorFieldViewController.create(
					this._id,
					{ field: this, wrapper: this.getContentWrapper() }
				);
			}
		}
		else if(this.checkModeOption(BX.UI.EntityEditorModeOptions.exclusive))
		{
			this._singleEditController = BX.UI.EditorFieldSingleEditController.create(
				this._id,
				{ field: this }
			);
		}
	};
	BX.UI.EntityEditorField.prototype.releaseSwitchingAbilities = function()
	{
		if(this._singleEditButton)
		{
			BX.unbind(this._singleEditButton, "click", this._singleEditButtonHandler);
		}

		if(this._viewController)
		{
			this._viewController.release();
			this._viewController = null;
		}

		if(this._singleEditController)
		{
			this._singleEditController.release();
			this._singleEditController = null;
		}
	};
	BX.UI.EntityEditorField.prototype.initializeLightingAbilities = function(params)
	{
		var text = BX.prop.getString(params, "text", "");
		if(!BX.type.isNotEmptyString(text))
		{
			return;
		}

		var wrapper = this.getContentWrapper();
		if(!wrapper)
		{
			return;
		}

		this._spotlight = new BX.SpotLight(
			{
				id: BX.prop.getString(params, "id", ""),
				targetElement: wrapper,
				autoSave: true,
				content: text,
				targetVertex: "middle-left",
				zIndex: 200
			}
		);
		this._spotlight.show();

		var events = BX.prop.getObject(params, "events", {});
		for(var key in events)
		{
			if(events.hasOwnProperty(key))
			{
				BX.addCustomEvent(this._spotlight, key, events[key]);
			}
		}
	};
	BX.UI.EntityEditorField.prototype.releaseLightingAbilities = function()
	{
		if(this._spotlight)
		{
			this._spotlight.close();
			this._spotlight = null;
		}
	};
	BX.UI.EntityEditorField.prototype.getFocusInputID = function()
	{
		return "";
	};
	BX.UI.EntityEditorField.prototype.onSingleEditBtnClick = function(e)
	{
		if(!(this.isSingleEditEnabled() && this._editor))
		{
			return;
		}

		if(this._singleEditTimeoutHandle > 0)
		{
			window.clearTimeout(this._singleEditTimeoutHandle);
			this._singleEditTimeoutHandle = 0;
		}

		this._singleEditTimeoutHandle = window.setTimeout(
			BX.delegate(this.switchToSingleEditMode, this),
			250
		);

		BX.eventCancelBubble(e);
	};
	BX.UI.EntityEditorField.prototype.getModeSwitchType = function(mode)
	{
		var result = BX.UI.EntityEditorModeSwitchType.common;
		if(mode === BX.UI.EntityEditorMode.edit)
		{
			result |= BX.UI.EntityEditorModeSwitchType.button;
		}
		return result;
	};
	BX.UI.EntityEditorField.prototype.switchToSingleEditMode = function()
	{
		if(!(this.isSingleEditEnabled() && this._editor))
		{
			return;
		}

		this._singleEditTimeoutHandle = 0;

		this._editor.switchControlMode(
			this,
			BX.UI.EntityEditorMode.edit,
			BX.UI.EntityEditorModeOptions.individual
		);
	};
}

if(typeof BX.UI.EntityEditorSectionContentStub === "undefined")
{
	BX.UI.EntityEditorSectionContentStub = function()
	{
		this._settings = null;
		this._owner = null;
		this._container = null;
		this._wrapper = null;

		this._clickHandler = BX.delegate(this.onClick, this);
	};
	BX.UI.EntityEditorSectionContentStub.prototype =
	{
		initialize: function(settings)
		{
			this._settings = settings ? settings : {};
			this._owner = BX.prop.get(this._settings, "owner", null);
			this._container = BX.prop.getElementNode(this._settings, "container", null);
		},
		layout: function()
		{
			this._wrapper = BX.create(
				"div",
				{
					props: { className: "ui-entity-editor-content-block" },
					children:
						[
							BX.create(
								"div",
								{
									props: { className: "ui-entity-card-content-nothing-selected" },
									children:
										[
											BX.create(
												"div",
												{
													props: { className: "ui-entity-card-content-nothing-selected-text" },
													text: BX.message("UI_ENTITY_EDITOR_NOTHING_SELECTED")
												}
											)
										]
								}
							)
						]
				}
			);

			BX.bind(this._wrapper, "click", this._clickHandler);

			if(this._container.children.length > 0)
			{
				this._container.insertBefore(this._wrapper, this._container.children[0]);
			}
			else
			{
				this._container.appendChild(this._wrapper);
			}
		},
		clearLayout: function()
		{
			BX.unbind(this._wrapper, "click", this._clickHandler);
			this._wrapper = BX.remove(this._wrapper);
		},
		onStubClick: function(e)
		{
			if(this._owner && this._owner.isModeToggleEnabled())
			{
				this._owner.toggle();
			}
		}
	};
	BX.UI.EntityEditorSectionContentStub.create = function(id, settings)
	{
		var self = new BX.UI.EntityEditorSectionContentStub();
		self.initialize(id, settings);
		return self;
	}
}

if(typeof BX.UI.EntityEditorSection === "undefined")
{
	BX.UI.EntityEditorSection = function()
	{
		BX.UI.EntityEditorSection.superclass.constructor.apply(this);
		this._fields = null;
		this._fieldConfigurator = null;
		this._userFieldConfigurator = null;
		this._mandatoryConfigurator = null;

		this._titleEditButton = null;
		this._titleEditHandler = BX.delegate(this.onTitleEditButtonClick, this);
		this._headerContainer = null;
		this._titleContainer = null;
		this._titleView = null;
		this._titleInput = null;
		this._titleActions = null;
		this._titleMode = BX.UI.EntityEditorMode.intermediate;
		this._titleInputKeyHandler = BX.delegate(this.onTitleInputKeyPress, this);
		this._documentClickHandler = BX.delegate(this.onExternalClick, this);
		this._detetionConfirmDlgId = "section_deletion_confirm";

		this._enableToggling = true;
		this._toggleButton = null;
		this._addChildButton = null;
		this._buttonPanelBlockLeft = null;
		this._buttonPanelBlockRight = null;
		this._createChildButton = null;
		this._childSelectMenu = null;
		this._buttonPanelWrapper = null;

		this._deleteButton = null;
		this._deleteButtonHandler = BX.delegate(this.onDeleteSectionBtnClick, this);

		this._draggableContextId = "";
		this._dragContainerController = null;
		this._dragPlaceHolder = null;
		this._dropHandler = BX.delegate(this.onDrop, this);

		this._fieldSelector = null;
		this._fieldTypeSelectMenu = null;

		this._stub = null;
	};
	BX.extend(BX.UI.EntityEditorSection, BX.UI.EntityEditorControl);
	BX.UI.EntityEditorSection.prototype.doSetActive = function()
	{
		for(var i = 0, l = this._fields.length; i < l; i++)
		{
			this._fields[i].setActive(this._isActive);
		}
	};
	//region Initialization
	BX.UI.EntityEditorSection.prototype.initialize =  function(id, settings)
	{
		BX.UI.EntityEditorSection.superclass.initialize.call(this, id, settings);

		this._draggableContextId = BX.UI.EditorFieldDragItem.contextId;
		if(this.getChildDragScope() === BX.UI.EditorDragScope.parent)
		{
			this._draggableContextId += "_" + this.getId();
		}

		this.initializeFromModel();
	};
	BX.UI.EntityEditorSection.prototype.initializeFromModel =  function()
	{
		var i, length;
		if(this._fields)
		{
			for(i = 0, length = this._fields.length; i < length; i++)
			{
				this._fields[i].release();
			}
		}

		this._fields = [];

		var elements = this._schemeElement.getElements();
		for(i = 0, length = elements.length; i < length; i++)
		{
			var element = elements[i];
			var field = this._editor.createControl(
				element.getType(),
				element.getName(),
				{ schemeElement: element, model: this._model, parent: this }
			);

			if(!field)
			{
				continue;
			}

			element.setParent(this._schemeElement);
			field.setMode(this._mode, { notify: false });
			this._fields.push(field);
		}
	};
	//endregion
	//region Layout
	BX.UI.EntityEditorSection.prototype.layout = function(options)
	{
		//Create wrapper
		var title = this._schemeElement.getTitle();
		this._contentContainer = BX.create("div", {props: { className: "ui-entity-editor-section-content" } });
		var isViewMode = this._mode === BX.UI.EntityEditorMode.view ;

		var wrapperClassName = isViewMode
			? "ui-entity-editor-section"
			: "ui-entity-editor-section-edit";

		this._enableToggling = this.isModeToggleEnabled() && this._schemeElement.getDataBooleanParam("enableToggling", true);
		this._toggleButton = BX.create("span",
			{
				attrs: { className: "ui-entity-editor-header-edit-lnk" },
				events: { click: BX.delegate(this.onToggleBtnClick, this) },
				text: BX.message(isViewMode ? "UI_ENTITY_EDITOR_CHANGE" : "UI_ENTITY_EDITOR_CANCEL")
			}
		);
		if(!this._enableToggling)
		{
			this._toggleButton.style.display = "none";
		}

		this._titleMode = BX.UI.EntityEditorMode.view;

		this._wrapper = BX.create("div", { props: { className: wrapperClassName }});

		if(this._schemeElement.isTitleEnabled())
		{

			this._headerContainer = BX.create('div',
			{
				props: { className: 'ui-entity-editor-section-header' }
			});

			if(this.isDragEnabled())
			{
				this._headerContainer.appendChild(this.createDragButton());
			}

			this._titleEditButton = BX.create("span",
			{
				props: { className: "ui-entity-editor-header-title-edit-icon" },
				events: { click: this._titleEditHandler }
			});

			if(!this._editor.isSectionEditEnabled() || !this._editor.canChangeScheme())
			{
				this._titleEditButton.style.display = "none";
			}

			this._titleView = BX.create("span",
			{
				props: { className: "ui-entity-editor-header-title-text" },
				text: title
			});

			this._titleInput = BX.create("input",
			{
				props: { className: "ui-entity-editor-header-title-text" },
				style: { display: "none" }
			});

			this._titleActions = BX.create("div",
			{
				props: { className: "ui-entity-editor-header-actions" },
				children : [ this._toggleButton ]
			});

			this._titleContainer = BX.create("div",
			{
				props: { className: "ui-entity-editor-header-title" },
				children :
				[
					this._titleView,
					this._titleInput,
					this._titleEditButton
				]
			});

			this._headerContainer.appendChild(this._titleContainer);
			this._headerContainer.appendChild(this._titleActions);


			this._wrapper.appendChild(this._headerContainer);
		}

		this._wrapper.appendChild(this._contentContainer);

		if(!BX.type.isPlainObject(options))
		{
			options = {};
		}

		var anchor = BX.prop.getElementNode(options, "anchor", null);
		if (anchor)
		{
			this._container.insertBefore(this._wrapper, anchor);
		}
		else
		{
			this._container.appendChild(this._wrapper);
		}

		if(isViewMode && this._fields.length === 0)
		{
			this._stub = BX.UI.EntityEditorSectionContentStub.create(
				{ owner: this, container: this._contentContainer }
			);
			this._stub.layout();
		}

		var enableReset = BX.prop.getBoolean(options, "reset", false);
		//Layout fields
		var userFieldLoader = BX.prop.get(options, "userFieldLoader", null);
		if(!userFieldLoader)
		{
			userFieldLoader = BX.UI.EntityUserFieldLayoutLoader.create(
				this._id,
				{ mode: this._mode, enableBatchMode: true, owner: this }
			);
		}

		var enableFocusGain = BX.prop.getBoolean(options, "enableFocusGain", true);
		var lighting = BX.prop.getObject(options, "lighting", null);
		var isLighted = false;
		var isFieldContextMenuEnabled = false;
		for(var i = 0, l = this._fields.length; i < l; i++)
		{
			var field = this._fields[i];
			field.setContainer(this._contentContainer);
			field.setDraggableContextId(this._draggableContextId);

			//Force layout reset because of animation implementation
			field.releaseLayout();
			if(enableReset)
			{
				field.reset();
			}

			var layoutOptions = { userFieldLoader: userFieldLoader };
			if(!isLighted && lighting && field.isVisible() && field.isNeedToDisplay())
			{
				layoutOptions["lighting"] = lighting;
				isLighted = true;
			}

			field.layout(layoutOptions);
			if(enableFocusGain && !isViewMode && field.isHeading())
			{
				field.focus();
			}

			if(!isFieldContextMenuEnabled && field.isContextMenuEnabled())
			{
				isFieldContextMenuEnabled = true;
			}
		}

		if(isFieldContextMenuEnabled)
		{
			BX.addClass(this._contentContainer, "ui-entity-editor-section-content-padding-right");
		}

		if(userFieldLoader.getOwner() === this)
		{
			userFieldLoader.runBatch();
		}

		this._addChildButton = this._createChildButton = this._deleteButton = null;

		if(this._editor.canChangeScheme() && this._schemeElement.getDataBooleanParam("showButtonPanel", true))
		{
			this.ensureButtonPanelCreated();

			if(this._schemeElement.getDataBooleanParam("isChangeable", true))
			{
				this._addChildButton = BX.create("span",
				{
					props: { className: "ui-entity-editor-content-add-lnk" },
					text: BX.message("UI_ENTITY_EDITOR_SELECT_FIELD"),
					events: { click: BX.delegate(this.onAddChildBtnClick, this) }
				});
				this.addButtonElement(this._addChildButton, { position: "left" });

				if(this._editor.getUserFieldManager().isCreationEnabled())
				{
					this._createChildButton = BX.create("span",
					{
						props: { className: "ui-entity-editor-content-create-lnk" },
						text: BX.message("UI_ENTITY_EDITOR_CREATE_FIELD"),
						events: { click: BX.delegate(this.onCreateUserFieldBtnClick, this) }
					});
					this.addButtonElement(this._createChildButton, { position: "left" });
				}
			}

			if(this._schemeElement.getDataBooleanParam("isRemovable", true))
			{
				var deleteClassName = "ui-entity-editor-content-remove-lnk";
				if (this.isRequired() || this.isRequiredConditionally())
				{
					deleteClassName = "ui-entity-editor-content-remove-lnk-disabled";
				}

				this._deleteButton = BX.create("span",
				{
					props: { className: deleteClassName },
					text: BX.message("UI_ENTITY_EDITOR_DELETE_SECTION")
				});
				this.addButtonElement(this._deleteButton, { position: "right" });
				BX.bind(this._deleteButton, "click", this._deleteButtonHandler);
			}

			this._contentContainer.appendChild(this._buttonPanelWrapper);
			this.adjustButtons();
		}

		if(this.isDragEnabled())
		{
			this._dragContainerController = BX.UI.EditorDragContainerController.create(
				"section_" + this.getId(),
				{
					charge: BX.UI.EditorFieldDragContainer.create(
						{
							section: this,
							context: this._draggableContextId
						}
					),
					node: this._wrapper
				}
			);
			this._dragContainerController.addDragFinishListener(this._dropHandler);

			this.initializeDragDropAbilities();
		}

		//region Add custom Html
		var eventArgs =  { id: this._id, customNodes: [] };
		BX.onCustomEvent(window, "BX.UI.EntityEditorSection:onLayout", [ this, eventArgs ]);
		if(this._titleActions && BX.type.isArray(eventArgs["customNodes"]))
		{
			for(var j = 0, length = eventArgs["customNodes"].length; j < length; j++)
			{
				var node = eventArgs["customNodes"][j];
				if(BX.type.isElementNode(node))
				{
					this._titleActions.appendChild(node);
				}
			}
		}
		//endregion

		this._hasLayout = true;
	};
	BX.UI.EntityEditorSection.prototype.clearLayout = function()
	{
		if(!this._hasLayout)
		{
			return;
		}

		if(this._dragContainerController)
		{
			this._dragContainerController.removeDragFinishListener(this._dropHandler);
			this._dragContainerController.release();
			this._dragContainerController = null;
		}
		this.releaseDragDropAbilities();

		if(this._stub !== null)
		{
			this._stub.clearLayout();
			this._stub = null;
		}

		for(var i = 0, length = this._fields.length; i < length; i++)
		{
			var field = this._fields[i];
			field.clearLayout();
			field.setContainer(null);
		}

		this._buttonPanelWrapper = BX.remove(this._buttonPanelWrapper);
		this._stub = null;
		this._wrapper = BX.remove(this._wrapper);
		this._hasLayout = false;
	};
	BX.UI.EntityEditorSection.prototype.refreshLayout = function(options)
	{
		options = BX.type.isPlainObject(options) ? BX.mergeEx({}, options) : {};

		//region CALLBACK
		var callback = BX.prop.getFunction(options, "callback", null);
		delete options["callback"];
		//endregion

		//region ANCHOR
		delete options["anchor"];
		if(this._wrapper && this._wrapper.nextSibling)
		{
			options["anchor"] = this._wrapper.nextSibling;
		}
		//endregion

		//region LAYOUT
		this.clearLayout();
		this.layout(options);
		//endregion

		if(callback)
		{
			callback();
		}
	};
	BX.UI.EntityEditorSection.prototype.onStubClick = function(e)
	{
		this.toggle();
	};
	BX.UI.EntityEditorSection.prototype.adjustButtons = function()
	{
		var hasAvailableFields = this._editor.hasAvailableSchemeElements();
		var hasTransferableFields = this._editor.hasTransferableElements([ this.getName() ]);
		if(this._addChildButton)
		{
			this._addChildButton.style.display = (hasAvailableFields || hasTransferableFields) ? "" : "none";
		}
	};
	BX.UI.EntityEditorSection.prototype.processChildAdditionalMenuCommand = function(child, command)
	{
	};
	BX.UI.EntityEditorSection.prototype.ensureButtonPanelCreated = function()
	{
		if(!this._buttonPanelWrapper)
		{
			this._buttonPanelWrapper = BX.create("div", { props: { className: "ui-entity-card-content-actions-container" } });
			this._contentContainer.appendChild(this._buttonPanelWrapper);

			this._buttonPanelBlockLeft = BX.create("div",
				{
					props: {
						className: "ui-entity-card-content-actions-block"
					}
				});
			this._buttonPanelWrapper.appendChild(this._buttonPanelBlockLeft);

			this._buttonPanelBlockRight = BX.create("div",
				{
					props: {
						className: "ui-entity-card-content-actions-block"
					}
				});
			this._buttonPanelWrapper.appendChild(this._buttonPanelBlockRight);
		}
	};
	BX.UI.EntityEditorSection.prototype.addButtonElement = function(element, options)
	{
		this.ensureButtonPanelCreated();

		if(BX.prop.getString(options, "position", "") === "left")
		{
			this._buttonPanelBlockLeft.appendChild(element);
		}
		else
		{
			this._buttonPanelBlockRight.appendChild(element);
		}

	};
	//endregion
	//region Title Edit
	BX.UI.EntityEditorSection.prototype.setTitleMode = function(mode)
	{
		if(this._titleMode === mode)
		{
			return;
		}

		this._titleMode = mode;

		if(!this._schemeElement.isTitleEnabled())
		{
			return;
		}

		if(this._titleMode === BX.UI.EntityEditorMode.view)
		{
			this._titleView.style.display = "";
			this._titleInput.style.display = "none";
			this._titleEditButton.style.display = "";

			var title = this._titleInput.value;
			this._titleView.innerHTML = BX.util.htmlspecialchars(title);

			this._schemeElement.setTitle(title);
			this.markSchemeAsChanged();
			this.saveScheme();

			BX.unbind(this._titleInput, "keyup", this._titleInputKeyHandler);
			BX.unbind(window.document, "click", this._documentClickHandler);
		}
		else
		{
			this._titleView.style.display = "none";
			this._titleInput.style.display = "";
			this._titleEditButton.style.display = "none";

			this._titleInput.value = this._schemeElement.getTitle();

			BX.bind(this._titleInput, "keyup", this._titleInputKeyHandler);
			this._titleInput.focus();

			window.setTimeout(
				BX.delegate(function() { BX.bind(window.document, "click", this._documentClickHandler); }, this),
				100
			);
		}
	};
	BX.UI.EntityEditorSection.prototype.toggleTitleMode = function()
	{
		this.setTitleMode(
			this._titleMode === BX.UI.EntityEditorMode.view
				? BX.UI.EntityEditorMode.edit
				: BX.UI.EntityEditorMode.view
		);
	};
	BX.UI.EntityEditorSection.prototype.onTitleEditButtonClick = function(e)
	{
		if(this._editor.isSectionEditEnabled())
		{
			this.toggleTitleMode();
		}
	};
	BX.UI.EntityEditorSection.prototype.onTitleInputKeyPress = function(e)
	{
		if(!e)
		{
			e = window.event;
		}

		if(e.keyCode === 13)
		{
			this.toggleTitleMode();
		}
	};
	BX.UI.EntityEditorSection.prototype.onExternalClick = function(e)
	{
		if(!e)
		{
			e = window.event;
		}

		if(this._titleInput !== BX.getEventTarget(e))
		{
			this.toggleTitleMode();
		}
	};
	//endregion
	//region Toggling & Mode control
	BX.UI.EntityEditorSection.prototype.enableToggling = function(enable)
	{
		enable = !!enable;
		if(this._enableToggling === enable)
		{
			return;
		}

		this._enableToggling = enable;
		if(this._hasLayout)
		{
			this._toggleButton.style.display = this._enableToggling ? "" : "none";
		}
	};
	BX.UI.EntityEditorSection.prototype.toggle = function()
	{
		if(this._enableToggling && this._editor)
		{
			this._editor.switchControlMode(
				this,
				this._mode === BX.UI.EntityEditorMode.view
					? BX.UI.EntityEditorMode.edit : BX.UI.EntityEditorMode.view
			);
		}
	};
	BX.UI.EntityEditorSection.prototype.onToggleBtnClick = function(e)
	{
		this.toggle();
	};
	BX.UI.EntityEditorSection.prototype.onBeforeModeChange = function()
	{
	};
	BX.UI.EntityEditorSection.prototype.doSetMode = function(mode)
	{
		if(this._titleMode === BX.UI.EntityEditorMode.edit)
		{
			this.toggleTitleMode();
		}
		for(var i = 0, l = this._fields.length; i < l; i++)
		{
			this._fields[i].setMode(mode, { notify: false });
		}
	};
	//endregion
	//region Tracking of Changes, Validation, Saving and Rolling back
	BX.UI.EntityEditorSection.prototype.processAvailableSchemeElementsChange = function()
	{
	};
	BX.UI.EntityEditorSection.prototype.validate = function(result)
	{
		if(this._mode !== BX.UI.EntityEditorMode.edit)
		{
			return true;
		}

		var currentResult = BX.UI.EntityValidationResult.create();
		for(var i = 0, length = this._fields.length; i < length; i++)
		{
			var field = this._fields[i];
			if(field.getMode() !== BX.UI.EntityEditorMode.edit)
			{
				continue;
			}

			field.validate(currentResult);
		}

		result.addResult(currentResult);
		return currentResult.getStatus();
	};
	BX.UI.EntityEditorSection.prototype.commitSchemeChanges = function()
	{
		if(this._isSchemeChanged)
		{
			var schemeElements = [];
			for(var i = 0, length = this._fields.length; i < length; i++)
			{
				var schemeElement = this._fields[i].getSchemeElement();
				if(schemeElement)
				{
					schemeElements.push(schemeElement);
				}
			}
			this._schemeElement.setElements(schemeElements);
		}
		return BX.UI.EntityEditorSection.superclass.commitSchemeChanges.call(this);
	};
	BX.UI.EntityEditorSection.prototype.save = function()
	{
		for(var i = 0, length = this._fields.length; i < length; i++)
		{
			this._fields[i].save();
		}
	};
	BX.UI.EntityEditorSection.prototype.rollback = function()
	{
		for(var i = 0, l = this._fields.length; i < l; i++)
		{
			this._fields[i].rollback();
		}

		if(this._isChanged)
		{
			this.initializeFromModel();
			this._isChanged = false;
		}
	};
	BX.UI.EntityEditorSection.prototype.onBeforeSubmit = function()
	{
		for(var i = 0, length = this._fields.length; i < length; i++)
		{
			this._fields[i].onBeforeSubmit();
		}
	};
	//endregion
	//region Children & User Fields
	BX.UI.EntityEditorSection.prototype.getChildIndex = function(child)
	{
		for(var i = 0, l = this._fields.length; i < l; i++)
		{
			if(this._fields[i] === child)
			{
				return i;
			}
		}
		return -1;
	};
	BX.UI.EntityEditorSection.prototype.addChild = function(child, options)
	{
		if(!BX.type.isPlainObject(options))
		{
			options = {};
		}

		var related = null;
		var index = BX.prop.getInteger(options, "index", -1);
		if(index >= 0)
		{
			this._fields.splice(index, 0, child);
			if(index < (this._fields.length - 1))
			{
				related = this._fields[index + 1];
			}
		}
		else
		{
			this._fields.push(child);
			related = BX.prop.get(options, "related", null);
		}

		if(child.getParent() !== this)
		{
			child.setParent(this);
		}

		if(child.hasScheme())
		{
			child.getSchemeElement().setParent(this._schemeElement);
		}

		child.setActive(this._isActive);

		if(this._hasLayout)
		{
			if(this._stub !== null)
			{
				this._stub.clearLayout();
				this._stub = null;
			}

			child.setContainer(this._contentContainer);

			var layoutOpts = BX.prop.getObject(options, "layout", {});

			if(related)
			{
				layoutOpts["anchor"] = related.getWrapper();
			}
			else
			{
				layoutOpts["anchor"] = this._buttonPanelWrapper;
			}

			if(BX.prop.getBoolean(layoutOpts, "forceDisplay", false) &&
				!child.isNeedToDisplay() &&
				!child.checkOptionFlag(BX.UI.EntityEditorControlOptions.showAlways)
			)
			{
				//Ensure that field will be displayed.
				child.toggleOptionFlag(BX.UI.EntityEditorControlOptions.showAlways);
			}

			child.layout(layoutOpts);
		}

		if(child.hasScheme())
		{
			this._editor.processControlAdd(child);
			this.markSchemeAsChanged();

			if(BX.prop.getBoolean(options, "enableSaving", true))
			{
				this.saveScheme();
			}
		}
	};
	BX.UI.EntityEditorSection.prototype.removeChild = function(child, options)
	{
		if(!BX.type.isPlainObject(options))
		{
			options = {};
		}

		var index = this.getChildIndex(child);
		if(index < 0)
		{
			return;
		}

		if(child.isActive())
		{
			child.setActive(false);
		}

		this._fields.splice(index, 1);

		var processScheme = child.hasScheme();

		if(processScheme)
		{
			child.getSchemeElement().setParent(null);
		}

		if(this._hasLayout)
		{
			child.clearLayout();
			child.setContainer(null);

			if(this.isInViewMode())
			{
				if(this._fields.length > 0)
				{
					if(this._stub !== null)
					{
						this._stub.clearLayout();
						this._stub = null;
					}
				}
				else
				{
					if(this._stub === null)
					{
						this._stub = BX.UI.EntityEditorSectionContentStub.create(
							{ owner: this, container: this._contentContainer }
						);
					}
					this._stub.layout();
				}
			}
		}

		if(processScheme)
		{
			this._editor.processControlRemove(child);
			this.markSchemeAsChanged();

			if(BX.prop.getBoolean(options, "enableSaving", true))
			{
				this.saveScheme();
			}
		}
	};
	BX.UI.EntityEditorSection.prototype.moveChild = function(child, index, options)
	{
		if(!BX.type.isPlainObject(options))
		{
			options = {};
		}

		var qty = this.getChildCount();
		var lastIndex = qty - 1;
		if(index < 0  || index > qty)
		{
			index = lastIndex;
		}

		var currentIndex = this.getChildIndex(child);
		if(currentIndex < 0 || currentIndex === index)
		{
			return false;
		}

		if(this._hasLayout)
		{
			child.clearLayout();
		}
		this._fields.splice(currentIndex, 1);

		qty--;

		var anchor = null;
		if(this._hasLayout)
		{
			anchor = index < qty
				? this._fields[index].getWrapper()
				: this._buttonPanelWrapper;
		}

		if(index < qty)
		{
			this._fields.splice(index, 0, child);
		}
		else
		{
			this._fields.push(child);
		}

		if(this._hasLayout)
		{
			if(anchor)
			{
				child.layout({ anchor: anchor });
			}
			else
			{
				child.layout();
			}
		}

		this._editor.processControlMove(child);
		this.markSchemeAsChanged();

		if(BX.prop.getBoolean(options, "enableSaving", true))
		{
			this.saveScheme();
		}

		return true;
	};
	BX.UI.EntityEditorSection.prototype.editChild = function(child)
	{
		if(this._mode === BX.UI.EntityEditorMode.edit)
		{
			child.focus();
		}
		else if(!this.isReadOnly())
		{
			var isHomogeneous = true;
			for(var i = 0, length = this._fields.length; i < length; i++)
			{
				if(this._fields[i].getMode() !== this._mode)
				{
					isHomogeneous = false;
					break;
				}
			}

			if(isHomogeneous)
			{
				this.setMode(BX.UI.EntityEditorMode.edit, { notify: true });
				this.refreshLayout(
					{
						callback: function(){ child.focus(); }
					}
				);
			}
		}
	};
	BX.UI.EntityEditorSection.prototype.getChildById = function(childId)
	{
		for(var i = 0, length = this._fields.length; i < length; i++)
		{
			var field = this._fields[i];
			if(field.getId() === childId)
			{
				return field;
			}
		}
		return null;
	};
	BX.UI.EntityEditorSection.prototype.getChildCount = function()
	{
		return this._fields.length;
	};
	BX.UI.EntityEditorSection.prototype.getChildren = function()
	{
		return this._fields;
	};
	BX.UI.EntityEditorSection.prototype.editChildConfiguration = function(child)
	{
		this.removeFieldConfigurator();
		this.removeUserFieldConfigurator();

		if(child.getType() === "userField" && this._editor.getUserFieldManager().isModificationEnabled())
		{
			this.createUserFieldConfigurator(
				{
					field: child,
					enableMandatoryControl: this._editor.getUserFieldManager().isMandatoryControlEnabled()
				}
			);
		}
		else
		{
			this.createFieldConfigurator(child);
		}
	};
	BX.UI.EntityEditorSection.prototype.processChildControlModeChange = function(child)
	{
		if(!this.isActive() && this._editor)
		{
			this._editor.processControlModeChange(child);
		}
	};
	BX.UI.EntityEditorSection.prototype.processChildControlSchemeChange = function(child)
	{
		this.markSchemeAsChanged();
		this.saveScheme();
	};
	BX.UI.EntityEditorSection.prototype.processSchemeChange = function()
	{
		this.adjustButtons();
	};
	BX.UI.EntityEditorSection.prototype.onAddChildBtnClick = function(e)
	{
		this.openAddChildMenu();
	};
	BX.UI.EntityEditorSection.prototype.openAddChildMenu = function()
	{
		var schemeElements = this._editor.getAvailableSchemeElements();
		var length = schemeElements.length;

		var menuItems = [];
		for(var i = 0; i < length; i++)
		{
			var schemeElement = schemeElements[i];
			menuItems.push({ text: schemeElement.getTitle(), value: schemeElement.getName() });
		}

		if(this._editor.hasTransferableElements([ this.getName() ]))
		{
			if(length > 0)
			{
				menuItems.push({ delimiter: true });
			}

			menuItems.push({ text: BX.message("UI_ENTITY_EDITOR_SELECT_FIELD_FROM_OTHER_SECTION"), value: "ACTION.TRANSFER" });
		}

		var eventArgs =
			{
				id: this._id,
				menuItems: menuItems,
				button: this._addChildButton,
				cancel: false
			};
		BX.onCustomEvent(window, "BX.UI.EntityEditorSection:onOpenChildMenu", [ this, eventArgs ]);

		if(eventArgs["cancel"])
		{
			return;
		}

		if(this._childSelectMenu)
		{
			this._childSelectMenu.setupItems(menuItems);
		}
		else
		{
			this._childSelectMenu = BX.UI.SelectorMenu.create(this._id, { items: menuItems });
			this._childSelectMenu.addOnSelectListener(BX.delegate(this.onChildSelect, this));
		}
		this._childSelectMenu.open(this._addChildButton);
	};
	BX.UI.EntityEditorSection.prototype.processChildControlChange = function(child, params)
	{
		if(!child.isInEditMode())
		{
			return;
		}

		if(typeof(params) === "undefined")
		{
			params = {};
		}

		if(!BX.prop.get(params, "control", null))
		{
			params["control"] = child;
		}

		this.markAsChanged(params);
		this.enableToggling(false);
	};
	BX.UI.EntityEditorSection.prototype.onChildSelect = function(event)
	{
		var item = event.data["item"];

		var eventArgs =
			{
				id: this._id,
				item: item,
				button: this._addChildButton,
				cancel: false
			};
		BX.onCustomEvent(window, "BX.UI.EntityEditorSection:onChildMenuItemSelect", [ this, eventArgs ]);

		if(eventArgs["cancel"])
		{
			return;
		}

		var v = item.getValue();
		if(v === "ACTION.TRANSFER")
		{
			this.openTransferDialog();
			return;
		}

		var element = this._editor.getAvailableSchemeElementByName(v);
		if(!element)
		{
			return;
		}

		var field = this._editor.createControl(
			element.getType(),
			element.getName(),
			{ schemeElement: element, model: this._model, parent: this, mode: this._mode }
		);

		if(field)
		{
			//Option "notifyIfNotDisplayed" to enable user notification if field will not be displayed because of settings.
			//Option "forceDisplay" to enable "showAlways" flag if required .
			this.addChild(field, { layout: { forceDisplay: true } });
		}
	};
	BX.UI.EntityEditorSection.prototype.openTransferDialog = function()
	{
		if(!this._fieldSelector)
		{
			this._fieldSelector = BX.UI.EntityEditorFieldSelector.create(
				this._id,
				{
					scheme: this._editor.getScheme(),
					excludedNames: [ this.getSchemeElement().getName() ],
					title: BX.message("UI_ENTITY_EDITOR_FIELD_TRANSFER_DIALOG_TITLE")
				}
			);
			this._fieldSelector.addClosingListener(BX.delegate(this.onTransferFieldSelect, this));
		}

		this._fieldSelector.open();
	};
	BX.UI.EntityEditorSection.prototype.onTransferFieldSelect = function(event)
	{
		if(event.data["isCanceled"])
		{
			return;
		}

		var items = BX.prop.getArray(event.data, "items");
		if(items.length === 0)
		{
			return;
		}

		for(var i = 0, length = items.length; i < length; i++)
		{
			var item = items[i];

			var sectionName = BX.prop.getString(item, "sectionName", "");
			var fieldName = BX.prop.getString(item, "fieldName", "");

			var sourceSection = this._editor.getControlById(sectionName);
			if(!sourceSection)
			{
				continue;
			}

			var sourceField = sourceSection.getChildById(fieldName);
			if(!sourceField)
			{
				continue;
			}

			var schemeElement = sourceField.getSchemeElement();

			sourceSection.removeChild(sourceField, { enableSaving: false });

			var targetField = this._editor.createControl(
				schemeElement.getType(),
				schemeElement.getName(),
				{ schemeElement: schemeElement, model: this._model, parent: this, mode: this._mode }
			);

			//Option "notifyIfNotDisplayed" to enable user notification if field will not be displayed because of settings.
			//Option "forceDisplay" to enable "showAlways" flag if required .
			this.addChild(targetField, { layout: { forceDisplay: true }, enableSaving: false });
		}

		this._editor.saveSchemeChanges();
	};
	BX.UI.EntityEditorSection.prototype.removeUserFieldConfigurator = function()
	{
		if(this._userFieldConfigurator)
		{
			var field = this._userFieldConfigurator.getField();
			if(field)
			{
				field.setVisible(true);
			}
			this.removeChild(this._userFieldConfigurator);
			this._userFieldConfigurator = null;
		}
	};
	BX.UI.EntityEditorSection.prototype.createFieldConfigurator = function(child)
	{
		child.setVisible(false);

		/*
		var attrManager = this._editor.getAttributeManager();
		if(attrManager)
		{
			this._mandatoryConfigurator = attrManager.createFieldConfigurator(
				child,
				BX.UI.EntityFieldAttributeType.required
			);
		}
		*/

		this._fieldConfigurator = BX.UI.EntityEditorFieldConfigurator.create(
			"",
			{
				editor: this._editor,
				schemeElement: null,
				model: this._model,
				mode: BX.UI.EntityEditorMode.edit,
				parent: this,
				field: child,
				mandatoryConfigurator: null
			}
		);
		this.addChild(this._fieldConfigurator, { related: child });

		BX.addCustomEvent(this._fieldConfigurator, "onSave", BX.delegate(this.onFieldConfigurationSave, this));
		BX.addCustomEvent(this._fieldConfigurator, "onCancel", BX.delegate(this.onFieldConfigurationCancel, this));
	};
	BX.UI.EntityEditorSection.prototype.removeFieldConfigurator = function()
	{
		if(this._fieldConfigurator)
		{
			var field = this._fieldConfigurator.getField();
			if(field)
			{
				field.setVisible(true);
			}
			this.removeChild(this._fieldConfigurator);
			this._fieldConfigurator = null;
		}
	};
	BX.UI.EntityEditorSection.prototype.onFieldConfigurationSave = function(sender, params)
	{
		if(sender !== this._fieldConfigurator)
		{
			return;
		}

		var field = BX.prop.get(params, "field", null);
		if(!field)
		{
			throw "EntityEditorSection. Could not find target field.";
		}

		var label = BX.prop.getString(params, "label", "");
		var showAlways = BX.prop.getBoolean(params, "showAlways", null);
		if(label === "" && showAlways === null)
		{
			this.removeFieldConfigurator();
			if(this._mandatoryConfigurator)
			{
				this._mandatoryConfigurator = null;
			}
			return;
		}

		this._fieldConfigurator.setLocked(true);
		field.setTitle(label);
		if(showAlways !== null && showAlways !== field.checkOptionFlag(BX.UI.EntityEditorControlOptions.showAlways))
		{
			field.toggleOptionFlag(BX.UI.EntityEditorControlOptions.showAlways);
		}

		this.markSchemeAsChanged();
		this.saveScheme().then(
			BX.delegate(
				function()
				{
					if(this._mandatoryConfigurator)
					{
						if(this._mandatoryConfigurator.isPermitted()
							&& field.areAttributesEnabled()
							&& !field.isRequired()
						)
						{
							if(this._mandatoryConfigurator.isEnabled())
							{
								if(this._mandatoryConfigurator.isChanged())
								{
									this._mandatoryConfigurator.acceptChanges();
								}
								var attributeConfig = this._mandatoryConfigurator.getConfiguration();
								this._editor.getAttributeManager().saveConfiguration(attributeConfig, field.getName());
								field.setAttributeConfiguration(attributeConfig);
							}
							else
							{
								var attributeTypeId = this._mandatoryConfigurator.getTypeId();
								this._editor.getAttributeManager().removeConfiguration(attributeTypeId, field.getName());
								field.removeAttributeConfiguration(attributeTypeId);
							}
						}
						this._mandatoryConfigurator = null;
					}
					this.removeFieldConfigurator();
				},
				this
			)
		)
	};
	BX.UI.EntityEditorSection.prototype.onFieldConfigurationCancel = function(sender, params)
	{
		if(sender !== this._fieldConfigurator)
		{
			return;
		}

		var field = BX.prop.get(params, "field", null);
		if(!field)
		{
			throw "EntityEditorSection. Could not find target field.";
		}

		this.removeFieldConfigurator();
		if(this._mandatoryConfigurator)
		{
			this._mandatoryConfigurator = null;
		}
	};
	BX.UI.EntityEditorSection.prototype.enablePointerEvents = function(enable)
	{
		if(!this._fields)
		{
			return;
		}

		enable = !!enable;
		for(var i = 0, length = this._fields.length; i < length; i++)
		{
			this._fields[i].enablePointerEvents(enable);
		}
	};
	BX.UI.EntityEditorSection.prototype.onCreateUserFieldBtnClick = function(e)
	{
		if(!this._fieldTypeSelectMenu)
		{
			var infos = this._editor.getUserFieldManager().getTypeInfos();
			var items = [];
			for(var i = 0, length = infos.length; i < length; i++)
			{
				var info = infos[i];
				items.push({ value: info.name, text: info.title, legend: info.legend });
			}

			this._fieldTypeSelectMenu = BX.UI.UserFieldTypeMenu.create(
				this._id,
				{
					items: items,
					callback: BX.delegate(this.onUserFieldTypeSelect, this)
				}
			);
		}
		this._fieldTypeSelectMenu.open(this._createChildButton);
	};
	BX.UI.EntityEditorSection.prototype.onUserFieldTypeSelect = function(sender, item)
	{
		this._fieldTypeSelectMenu.close();

		var typeId = item.getValue();
		if(typeId === "")
		{
			return;
		}

		if(typeId === "custom")
		{
			window.open(this._editor.getUserFieldManager().getCreationPageUrl());
		}
		else
		{
			this.removeFieldConfigurator();
			this.removeUserFieldConfigurator();
			this.createUserFieldConfigurator(
				{
					typeId: typeId,
					enableMandatoryControl: this._editor.getUserFieldManager().isMandatoryControlEnabled()
				}
			);
		}
	};
	BX.UI.EntityEditorSection.prototype.createUserFieldConfigurator = function(params)
	{
		if(!BX.type.isPlainObject(params))
		{
			throw "EntityEditorSection: The 'params' argument must be object.";
		}

		var typeId = "";
		var field = BX.prop.get(params, "field", null);
		if(field)
		{
			if(!(field instanceof BX.UI.EntityEditorUserField))
			{
				throw "EntityEditorSection: The 'field' param must be EntityEditorUserField.";
			}

			typeId = field.getFieldType();
			field.setVisible(false);
		}
		else
		{
			typeId = BX.prop.get(params, "typeId", BX.UI.EntityUserFieldType.string);
		}

		var attrManager = this._editor.getAttributeManager();
		if(attrManager)
		{
			this._mandatoryConfigurator = attrManager.createFieldConfigurator(
				field,
				BX.UI.EntityFieldAttributeType.required
			);
		}

		this._userFieldConfigurator = BX.UI.EntityEditorUserFieldConfigurator.create(
			"",
			{
				editor: this._editor,
				schemeElement: null,
				model: this._model,
				mode: BX.UI.EntityEditorMode.edit,
				parent: this,
				typeId: typeId,
				field: field,
				enableMandatoryControl: BX.prop.getBoolean(params, "enableMandatoryControl", true),
				mandatoryConfigurator: this._mandatoryConfigurator,
				showAlways: true
			}
		);

		this.addChild(this._userFieldConfigurator, { related: field });

		BX.addCustomEvent(this._userFieldConfigurator, "onSave", BX.delegate(this.onUserFieldConfigurationSave, this));
		BX.addCustomEvent(this._userFieldConfigurator, "onCancel", BX.delegate(this.onUserFieldConfigurationCancel, this));
	};
	BX.UI.EntityEditorSection.prototype.onUserFieldConfigurationSave = function(sender, params)
	{
		if(sender !== this._userFieldConfigurator)
		{
			return;
		}

		this._userFieldConfigurator.setLocked(true);

		var typeId = BX.prop.getString(params, "typeId");
		if(typeId === BX.UI.EntityUserFieldType.datetime && !BX.prop.getBoolean(params, "enableTime", false))
		{
			typeId = BX.UI.EntityUserFieldType.date;
		}

		var fieldData = { "USER_TYPE_ID": typeId };

		if(this._mandatoryConfigurator
			&& this._mandatoryConfigurator.isPermitted()
			&& this._mandatoryConfigurator.isEnabled()
			&& this._mandatoryConfigurator.isCustomized()
		)
		{
			if(this._mandatoryConfigurator.isChanged())
			{
				this._mandatoryConfigurator.acceptChanges();
			}

			fieldData["MANDATORY"] = "N";
		}
		else
		{
			fieldData["MANDATORY"] = BX.prop.getBoolean(params, "mandatory", false) ? "Y" : "N";
		}

		var settings = BX.prop.get(params, "settings", null);
		if (settings)
		{
			fieldData["SETTINGS"] = settings;
		}

		var showAlways = BX.prop.getBoolean(params, "showAlways", null);
		var label = BX.prop.getString(params, "label", "");
		var field = BX.prop.get(params, "field", null);

		if(field)
		{
			var previousLabel = field.getTitle();
			if(label !== "" || showAlways !== null)
			{
				field.setTitle(label);
				if(showAlways !== null && showAlways !== field.checkOptionFlag(BX.UI.EntityEditorControlOptions.showAlways))
				{
					field.toggleOptionFlag(BX.UI.EntityEditorControlOptions.showAlways);
				}

				this.markSchemeAsChanged();
				this.saveScheme();
			}

			fieldData["FIELD"] = field.getName();
			fieldData["ENTITY_VALUE_ID"] = field.getEntityValueId();

			if(this._editor.getConfigScope() === BX.UI.EntityConfigScope.common && label !== '' && previousLabel !== label)
			{
				fieldData["EDIT_FORM_LABEL"] = fieldData["LIST_COLUMN_LABEL"] = fieldData["LIST_FILTER_LABEL"] = label;
			}

			fieldData["VALUE"] = field.getFieldValue();

			if(typeId === BX.UI.EntityUserFieldType.enumeration)
			{
				fieldData["ENUM"] = BX.prop.getArray(params, "enumeration", []);
			}

			field.adjustFieldParams(fieldData, false);

			this._editor.getUserFieldManager().updateField(
				fieldData,
				field.getMode()
			).then(
				BX.delegate(this.onUserFieldUpdate, this)
			);
		}
		else
		{
			if(showAlways !== null)
			{
				this._editor.setOption("show_always", showAlways ? "Y" : "N");
			}

			fieldData["EDIT_FORM_LABEL"] = fieldData["LIST_COLUMN_LABEL"] = fieldData["LIST_FILTER_LABEL"] = BX.prop.getString(params, "label");
			fieldData["MULTIPLE"] = BX.prop.getBoolean(params, "multiple", false) ? "Y" : "N";

			if(typeId === BX.UI.EntityUserFieldType.enumeration)
			{
				fieldData["ENUM"] = BX.prop.getArray(params, "enumeration", []);
			}

			this._editor.getUserFieldManager().createField(
				fieldData,
				this._mode
			).then(BX.delegate(this.onUserFieldCreate, this));
		}
	};
	BX.UI.EntityEditorSection.prototype.onUserFieldConfigurationCancel = function(sender, params)
	{
		if(sender !== this._userFieldConfigurator)
		{
			return;
		}

		this.removeUserFieldConfigurator();

		if(this._mandatoryConfigurator)
		{
			this._mandatoryConfigurator = null;
		}
	};
	BX.UI.EntityEditorSection.prototype.onUserFieldCreate = function(result)
	{
		if(!BX.type.isPlainObject(result))
		{
			return;
		}

		this.removeUserFieldConfigurator();

		var manager = this._editor.getUserFieldManager();
		for(var key in result)
		{
			if(!result.hasOwnProperty(key))
			{
				continue;
			}

			var data = result[key];
			var info = BX.prop.getObject(data, "FIELD", null);
			if(!info)
			{
				continue;
			}

			var element = manager.createSchemeElement(info);
			if(!element)
			{
				continue;
			}

			this._model.registerNewField(
				element.getName(),
				{ "VALUE": "", "SIGNATURE": BX.prop.getString(info, "SIGNATURE", "") }
			);

			var field = this._editor.createControl(
				element.getType(),
				element.getName(),
				{ schemeElement: element, model: this._model, parent: this, mode: this._mode }
			);

			if(this._mandatoryConfigurator
				&& this._mandatoryConfigurator.isPermitted()
				&& this._mandatoryConfigurator.isEnabled()
				&& this._mandatoryConfigurator.isCustomized()
			)
			{
				var attributeConfig = this._mandatoryConfigurator.getConfiguration();
				this._editor.getAttributeManager().saveConfiguration(attributeConfig, element.getName());
				field.setAttributeConfiguration(attributeConfig);
			}

			var showAlways = this._editor.getOption("show_always", "Y") === "Y";
			if(showAlways !== field.checkOptionFlag(BX.UI.EntityEditorControlOptions.showAlways))
			{
				field.toggleOptionFlag(BX.UI.EntityEditorControlOptions.showAlways);
			}

			//Option "notifyIfNotDisplayed" to enable user notification if field will not be displayed because of settings.
			this.addChild(field, { layout: { notifyIfNotDisplayed: true, html: BX.prop.getString(data, "HTML", "") } });

			break;
		}

		if(this._mandatoryConfigurator)
		{
			this._mandatoryConfigurator = null;
		}
	};
	BX.UI.EntityEditorSection.prototype.onUserFieldUpdate = function(result)
	{
		if(!BX.type.isPlainObject(result))
		{
			return;
		}

		this.removeUserFieldConfigurator();

		var manager = this._editor.getUserFieldManager();
		for(var key in result)
		{
			if(!result.hasOwnProperty(key))
			{
				continue;
			}

			var data = result[key];
			var info = BX.prop.getObject(data, "FIELD", null);
			if(!info)
			{
				continue;
			}

			var field = this.getChildById(key);
			if(!field)
			{
				continue;
			}

			var element = field.getSchemeElement();
			if(!element)
			{
				continue;
			}

			if(this._mandatoryConfigurator && this._mandatoryConfigurator.isPermitted())
			{
				if(this._mandatoryConfigurator.isEnabled() && this._mandatoryConfigurator.isCustomized())
				{
					var attributeConfig = this._mandatoryConfigurator.getConfiguration();
					this._editor.getAttributeManager().saveConfiguration(attributeConfig, element.getName());
					field.setAttributeConfiguration(attributeConfig);
				}
				else
				{
					var attributeTypeId = this._mandatoryConfigurator.getTypeId();
					this._editor.getAttributeManager().removeConfiguration(attributeTypeId, element.getName());
					field.removeAttributeConfiguration(attributeTypeId);
				}
			}

			manager.updateSchemeElement(element, info);
			var options = {};
			var html = BX.prop.getString(data, "HTML", "");
			if(html !== "")
			{
				options["html"] = html;
			}

			field.refreshLayout(options);

			break;
		}

		if(this._mandatoryConfigurator)
		{
			this._mandatoryConfigurator = null;
		}
	};
	//endregion
	//region Create|Delete Section
	BX.UI.EntityEditorSection.prototype.onDeleteConfirm = function(result)
	{
		if(BX.prop.getBoolean(result, "cancel", true))
		{
			return;
		}

		this._editor.removeSchemeElement(this.getSchemeElement());
		this._editor.removeControl(this);
		this._editor.saveScheme();
	};
	BX.UI.EntityEditorSection.prototype.onDeleteSectionBtnClick = function(e)
	{
		if(this.isRequired() || this.isRequiredConditionally())
		{
			this.showMessageDialog(
				"operationDenied",
				BX.message("UI_ENTITY_EDITOR_DELETE_SECTION"),
				BX.message("UI_ENTITY_EDITOR_DELETE_SECTION_DENIED")
			);
			return;
		}

		var dlg = BX.UI.ConfirmationDialog.get(this._detetionConfirmDlgId);
		if(!dlg)
		{
			dlg = BX.UI.ConfirmationDialog.create(
				this._detetionConfirmDlgId,
				{
					title: BX.message("UI_ENTITY_EDITOR_DELETE_SECTION"),
					content: BX.message("UI_ENTITY_EDITOR_DELETE_SECTION_CONFIRM")
				}
			);
		}
		dlg.open().then(BX.delegate(this.onDeleteConfirm, this));
	};
	//endregion
	//region D&D
	BX.UI.EntityEditorSection.prototype.createDragButton = function()
	{
		if(!this._dragButton)
		{
			this._dragButton = BX.create(
				"div",
				{
					props: { className: "ui-entity-editor-header-draggable-btn-container" },
					children:
						[
							BX.create(
								"div",
								{
									props: { className: "ui-entity-editor-draggable-btn" }
								}
							)
						]
				}
			);
		}
		return this._dragButton;
	};
	BX.UI.EntityEditorSection.prototype.createGhostNode = function()
	{
		if(!this._wrapper)
		{
			return null;
		}

		var pos = BX.pos(this._wrapper);
		var node =  BX.create("div",
		{
			props: { className: "ui-entity-section-grabbing" },
			children :
			[
				BX.create("div",
				{
					props: { className: "ui-entity-editor-section-header" },
					children:
					[
						BX.create("div",
						{
							props: { className: "ui-entity-editor-header-draggable-btn-container" },
							children:
							[
								BX.create("div",
								{
									props: { className: "ui-entity-editor-draggable-btn" }
								})
							]
						}),
						BX.create("div",
						{
							props: { className: "ui-entity-editor-header-title" },
							children :
							[
								BX.create("span",
								{
									props: { className: "ui-entity-editor-header-title-text" },
									text: this._schemeElement.getTitle()
								})
							]
						})
					]
				})
			]
		});

		BX.addClass(node, "ui-entity-card-card-drag");
		node.style.width = pos.width + "px";
		return node;
	};
	BX.UI.EntityEditorSection.prototype.getDragObjectType = function()
	{
		return BX.UI.EditorDragObjectType.section;
	};
	BX.UI.EntityEditorSection.prototype.getChildDragObjectType = function()
	{
		return BX.UI.EditorDragObjectType.field;
	};
	BX.UI.EntityEditorSection.prototype.hasPlaceHolder = function()
	{
		return !!this._dragPlaceHolder;
	};
	BX.UI.EntityEditorSection.prototype.createPlaceHolder = function(index)
	{
		this.enablePointerEvents(false);

		if(this._stub !== null)
		{
			this._stub.clearLayout();
			this._stub = null;
		}

		var qty = this.getChildCount();
		if(index < 0 || index > qty)
		{
			index = qty > 0 ? qty : 0;
		}

		if(this._dragPlaceHolder)
		{
			if(this._dragPlaceHolder.getIndex() === index)
			{
				return this._dragPlaceHolder;
			}

			this._dragPlaceHolder.clearLayout();
			this._dragPlaceHolder = null;
		}

		this._dragPlaceHolder = BX.UI.EditorDragFieldPlaceholder.create(
			{
				container: this._contentContainer,
				anchor: (index < qty) ? this._fields[index].getWrapper() : this._buttonPanelWrapper,
				index: index
			}
		);
		this._dragPlaceHolder.layout();
		return this._dragPlaceHolder;
	};
	BX.UI.EntityEditorSection.prototype.getPlaceHolder = function()
	{
		return this._dragPlaceHolder;
	};
	BX.UI.EntityEditorSection.prototype.removePlaceHolder = function()
	{
		this.enablePointerEvents(true);

		if(this.isInViewMode() && this.getChildCount() === 0 && this._stub == null)
		{
			this._stub = BX.UI.EntityEditorSectionContentStub.create(
				{ owner: this, container: this._contentContainer }
			);
			this._stub.layout();
		}

		if(this._dragPlaceHolder)
		{
			this._dragPlaceHolder.clearLayout();
			this._dragPlaceHolder = null;
		}
	};
	BX.UI.EntityEditorSection.prototype.processDraggedItemDrop = function(dropContainer, draggedItem)
	{
		var containerCharge = dropContainer.getCharge();
		if(!((containerCharge instanceof BX.UI.EditorFieldDragContainer) && containerCharge.getSection() === this))
		{
			return;
		}

		var context = draggedItem.getContextData();
		var contextId = BX.type.isNotEmptyString(context["contextId"]) ? context["contextId"] : "";

		if(contextId !== this.getDraggableContextId())
		{
			return;
		}

		var placeholder = this.getPlaceHolder();
		var placeholderIndex = placeholder ? placeholder.getIndex() : -1;
		if(placeholderIndex < 0)
		{
			return;
		}

		var itemCharge = typeof(context["charge"]) !== "undefined" ?  context["charge"] : null;
		if(!(itemCharge instanceof BX.UI.EditorFieldDragItem))
		{
			return;
		}

		var source = itemCharge.getControl();
		if(!source)
		{
			return;
		}

		var sourceParent = source.getParent();
		if(sourceParent === this)
		{
			var currentIndex = this.getChildIndex(source);
			if(currentIndex < 0)
			{
				return;
			}

			var index = placeholderIndex <= currentIndex ? placeholderIndex : (placeholderIndex - 1);
			if(index === currentIndex)
			{
				return;
			}

			this.moveChild(source, index, { enableSaving: false });
			this._editor.saveSchemeChanges();
		}
		else
		{
			var schemeElement = source.getSchemeElement();
			sourceParent.removeChild(source, { enableSaving: false });

			var target = this._editor.createControl(
				schemeElement.getType(),
				schemeElement.getName(),
				{ schemeElement: schemeElement, model: this._model, parent: this, mode: this._mode }
			);

			if(this._mode === BX.UI.EntityEditorMode.view
				&& !target.hasContentToDisplay()
				&& !target.checkOptionFlag(BX.UI.EntityEditorControlOptions.showAlways)
			)
			{
				//Activate 'showAlways' flag for display empty field in view mode.
				target.toggleOptionFlag(BX.UI.EntityEditorControlOptions.showAlways);
			}

			this.addChild(target, { index: placeholderIndex, enableSaving: false });
			this._editor.saveSchemeChanges();
		}
	};
	BX.UI.EntityEditorSection.prototype.onDrop = function(event)
	{
		this.processDraggedItemDrop(event.data["dropContainer"], event.data["draggedItem"]);
	};
	BX.UI.EntityEditorSection.prototype.initializeDragDropAbilities = function()
	{
		if(this._dragItem)
		{
			return;
		}

		this._dragItem = BX.UI.EditorDragItemController.create(
			"section_" + this.getId(),
			{
				charge: BX.UI.EditorSectionDragItem.create({ control: this }),
				node: this.createDragButton(),
				showControlInDragMode: false,
				ghostOffset: { x: 0, y: 0 }
			}
		);
	};
	BX.UI.EntityEditorSection.prototype.releaseDragDropAbilities = function()
	{
		if(this._dragItem)
		{
			this._dragItem.release();
			this._dragItem = null;
		}
	};
	//endregion
	//region Context Menu
	BX.UI.EntityEditorSection.prototype.hasAdditionalMenu = function()
	{
		return false;
	};
	BX.UI.EntityEditorSection.prototype.getAdditionalMenu = function()
	{
		return [];
	};
	//endregion
	BX.UI.EntityEditorSection.prototype.isWaitingForInput = function()
	{
		for(var i = 0, l = this._fields.length; i < l; i++)
		{
			if(this._fields[i].isWaitingForInput())
			{
				return true;
			}
		}
		return false;
	};
	BX.UI.EntityEditorSection.prototype.isRequired = function()
	{
		for(var i = 0, l = this._fields.length; i < l; i++)
		{
			if(this._fields[i].isRequired())
			{
				return true;
			}
		}
		return false;
	};
	BX.UI.EntityEditorSection.prototype.isRequiredConditionally = function()
	{
		for(var i = 0, l = this._fields.length; i < l; i++)
		{
			if(this._fields[i].isRequiredConditionally())
			{
				return true;
			}
		}
		return false;
	};
	BX.UI.EntityEditorSection.create = function(id, settings)
	{
		var self = new BX.UI.EntityEditorSection();
		self.initialize(id, settings);
		return self;
	}
}

if(typeof BX.UI.EntityEditorFieldConfigurator === "undefined")
{
	BX.UI.EntityEditorFieldConfigurator = function()
	{
		BX.UI.EntityEditorFieldConfigurator.superclass.constructor.apply(this);
		this._field = null;
		this._name = null;
		this._isLocked = false;

		this._labelInput = null;
		this._isRequiredCheckBox = null;
		this._showAlwaysCheckBox = null;
		this._saveButton = null;
		this._cancelButton = null;
		this._optionWrapper = null;

		this._mandatoryConfigurator = null;
	};
	BX.extend(BX.UI.EntityEditorFieldConfigurator, BX.UI.EntityEditorControl);
	BX.UI.EntityEditorFieldConfigurator.prototype.doInitialize = function()
	{
		BX.UI.EntityEditorFieldConfigurator.superclass.doInitialize.apply(this);
		this._field = BX.prop.get(this._settings, "field", null);
		this._name = BX.prop.getString(this._fieldData, "name", "");

		this._mandatoryConfigurator = BX.prop.get(this._settings, "mandatoryConfigurator", null);
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





		this._labelInput = BX.create(
			"input",
			{
				attrs:
					{
						className: "ui-ctl-element",
						type: "text",
						value: this._field.getTitle()
					}
			}
		);

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

		this._wrapper.appendChild(
			BX.create(
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
			)
		);

		this._optionWrapper = BX.create(
			"div",
			{
				props: { className: "ui-entity-editor-content-block" }
			}
		);
		this._wrapper.appendChild(
			BX.create(
				"div",
				{
					props: { className: "ui-entity-editor-content-block ui-entity-editor-content-block-checkbox" },
					children: [ this._optionWrapper ]
				}
			)
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

		this._wrapper.appendChild(
			BX.create("hr", { props: { className: "ui-entity-editor-line" } })
		);

		this._wrapper.appendChild(
			BX.create (
				"div",
				{
					props: {
						className: "ui-entity-editor-content-block-new-fields-btn-container"
					},
					children:
						[
							this._saveButton,
							this._cancelButton
						]
				}
			)
		);

		this.registerLayout(options);
		this._hasLayout = true;
	};
	BX.UI.EntityEditorFieldConfigurator.prototype.clearLayout = function()
	{
		if(!this._hasLayout)
		{
			return;
		}

		this._wrapper = BX.remove(this._wrapper);

		this._labelInput = null;
		this._isRequiredCheckBox = null;
		this._showAlwaysCheckBox = null;
		this._saveButton = null;
		this._cancelButton = null;
		this._optionWrapper = null;

		this._hasLayout = false;
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

		var params =
			{
				field: this._field,
				label: this._labelInput.value,
				showAlways: this._showAlwaysCheckBox.checked
			};

		BX.onCustomEvent(this, "onSave", [ this, params ]);
	};
	BX.UI.EntityEditorFieldConfigurator.prototype.onCancelButtonClick = function(e)
	{
		if(this._isLocked)
		{
			return;
		}

		var params = { field: this._field };
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
	BX.UI.EntityEditorFieldConfigurator.prototype.createOption = function(params)
	{
		var element = BX.create("input", { props: { type: "checkbox" } });
		var label = BX.create(
			"label",
			{ children: [ element, BX.create("span", { text: BX.prop.getString(params, "caption", "") }) ] }
		);

		var labelSettings = BX.prop.getObject(params, "labelSettings", null);
		if(labelSettings)
		{
			BX.adjust(label, labelSettings);
		}

		var helpUrl = BX.prop.getString(params, "helpUrl", "");
		if(helpUrl !== "")
		{
			label.appendChild(
				BX.create("a", { props: { className: "ui-entity-editor-new-field-helper-icon", href: helpUrl, target: "_blank" } })
			);
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
				props: { className: "ui-entity-editor-content-block-field-container" },
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
	BX.UI.EntityEditorFieldConfigurator.create = function(id, settings)
	{
		var self = new BX.UI.EntityEditorFieldConfigurator();
		self.initialize(id, settings);
		return self;
	}
}

if(typeof BX.UI.EntityEditorText === "undefined")
{
	BX.UI.EntityEditorText = function()
	{
		BX.UI.EntityEditorText.superclass.constructor.apply(this);
		this._input = null;
		this._innerWrapper = null;
	};

	BX.extend(BX.UI.EntityEditorText, BX.UI.EntityEditorField);
	BX.UI.EntityEditorText.prototype.getModeSwitchType = function(mode)
	{
		var result = BX.UI.EntityEditorModeSwitchType.common;
		if(mode === BX.UI.EntityEditorMode.edit)
		{
			result |= BX.UI.EntityEditorModeSwitchType.button|BX.UI.EntityEditorModeSwitchType.content;
		}
		return result;
	};
	BX.UI.EntityEditorText.prototype.getContentWrapper = function()
	{
		return this._innerWrapper;
	};
	BX.UI.EntityEditorText.prototype.focus = function()
	{
		if(!this._input)
		{
			return;
		}

		BX.focus(this._input);
		BX.UI.EditorTextHelper.getCurrent().setPositionAtEnd(this._input);
	};
	BX.UI.EntityEditorText.prototype.getLineCount = function()
	{
		return this._schemeElement.getDataIntegerParam("lineCount", 1);
	};
	BX.UI.EntityEditorText.prototype.layout = function(options)
	{
		if(this._hasLayout)
		{
			return;
		}

		this.ensureWrapperCreated({ classNames: [ "ui-entity-editor-field-text" ] });
		this.adjustWrapper();

		if(!this.isNeedToDisplay())
		{
			this.registerLayout(options);
			this._hasLayout = true;
			return;
		}

		var name = this.getName();
		var title = this.getTitle();
		var value = this.getValue();

		this._input = null;
		this._inputContainer = null;
		this._innerWrapper = null;

		if(this.isDragEnabled())
		{
			this._wrapper.appendChild(this.createDragButton());
		}

		if(this._mode === BX.UI.EntityEditorMode.edit)
		{
			this._wrapper.appendChild(this.createTitleNode(title));

			var lineCount = this.getLineCount();

			this._inputContainer = BX.create("div",
				{
					attrs: { className: "ui-ctl ui-ctl-textbox ui-ctl-w100" }
				}
			);

			if(lineCount > 1)
			{
				this._input = BX.create("textarea",
					{
						props:
							{
								className: "ui-entity-editor-field-textarea",
								name: name,
								rows: lineCount,
								value: value
							}
					}
				);
			}
			else
			{
				this._input = BX.create("input",
					{
						attrs:
							{
								name: name,
								className: "ui-ctl-element",
								type: "text",
								value: value,
								id: this._id.toLowerCase() + "_text"
							}
					}
				);
			}

			this._inputContainer.appendChild(this._input);

			if(this.isNewEntity())
			{
				var placeholder = this.getCreationPlaceholder();
				if(placeholder !== "")
				{
					this._input.setAttribute("placeholder", placeholder);
				}
			}

			BX.bind(this._input, "input", this._changeHandler);

			this._innerWrapper = BX.create("div",
			{
				props: { className: "ui-entity-editor-content-block" },
				children: [ this._inputContainer ]
			});
		}
		else// if(this._mode === BX.UI.EntityEditorMode.view)
		{
			this._wrapper.appendChild(this.createTitleNode(title));

			if(this.hasContentToDisplay())
			{
				if(this.getLineCount() > 1)
				{
					this._innerWrapper = BX.create("div",
					{
						props: { className: "ui-entity-editor-content-block" },
						children:
						[
							BX.create("div",
							{
								props: { className: "ui-entity-editor-content-block-text" },
								html: BX.util.nl2br(BX.util.htmlspecialchars(value))
							})
						]
					});
				}
				else
				{
					this._innerWrapper = BX.create("div",
					{
						props: { className: "ui-entity-editor-content-block" },
						children:
						[
							BX.create("div",
							{
								props: { className: "ui-entity-editor-content-block-text" },
								text: value
							})
						]
					});
				}
			}
			else
			{
				this._innerWrapper = BX.create("div",
				{
					props: { className: "ui-entity-editor-content-block" },
					text: BX.message("UI_ENTITY_EDITOR_FIELD_EMPTY")
				});
			}
		}

		this._wrapper.appendChild(this._innerWrapper);

		if(this.isContextMenuEnabled())
		{
			this._wrapper.appendChild(this.createContextMenuButton());
			// BX.addClass(this._wrapper, "sdsds");
		}

		if(this.isDragEnabled())
		{
			this.initializeDragDropAbilities();
		}

		this.registerLayout(options);
		this._hasLayout = true;
	};
	BX.UI.EntityEditorText.prototype.doClearLayout = function(options)
	{
		this._input = null;
		//BX.unbind(this._innerWrapper, "click", this._viewClickHandler);
		this._innerWrapper = null;
	};
	BX.UI.EntityEditorText.prototype.refreshLayout = function()
	{
		if(!this._hasLayout)
		{
			return;
		}

		if(!this._isValidLayout)
		{
			BX.UI.EntityEditorText.superclass.refreshLayout.apply(this, arguments);
			return;
		}

		if(this._mode === BX.UI.EntityEditorMode.edit && this._input)
		{
			this._input.value = this.getValue();
		}
		else if(this._mode === BX.UI.EntityEditorMode.view && this._innerWrapper)
		{
			this._innerWrapper.innerHTML = BX.util.htmlspecialchars(this.getValue());
		}
	};
	BX.UI.EntityEditorText.prototype.getRuntimeValue = function()
	{
		return (this._mode === BX.UI.EntityEditorMode.edit && this._input
				? BX.util.trim(this._input.value) : ""
		);
	};
	BX.UI.EntityEditorText.prototype.validate = function(result)
	{
		if(!(this._mode === BX.UI.EntityEditorMode.edit && this._input))
		{
			throw "BX.UI.EntityEditorText. Invalid validation context";
		}

		this.clearError();

		if(this.hasValidators())
		{
			return this.executeValidators(result);
		}

		var isValid = !this.isRequired() || BX.util.trim(this._input.value) !== "";
		if(!isValid)
		{
			result.addError(BX.UI.EntityValidationError.create({ field: this }));
			this.showRequiredFieldError(this._input);
		}
		return isValid;
	};
	BX.UI.EntityEditorText.prototype.showError =  function(error, anchor)
	{
		BX.UI.EntityEditorText.superclass.showError.apply(this, arguments);
		if(this._input)
		{
			BX.addClass(this._inputContainer, "ui-ctl-danger");
		}
	};
	BX.UI.EntityEditorText.prototype.clearError =  function()
	{
		BX.UI.EntityEditorText.superclass.clearError.apply(this);
		if(this._input)
		{
			BX.removeClass(this._inputContainer, "ui-ctl-danger");
		}
	};
	BX.UI.EntityEditorText.prototype.save = function()
	{
		if(this._input)
		{
			this._model.setField(this.getName(), this._input.value, { originator: this });
		}
	};
	BX.UI.EntityEditorText.prototype.processModelChange = function(params)
	{
		if(BX.prop.get(params, "originator", null) === this)
		{
			return;
		}

		if(!BX.prop.getBoolean(params, "forAll", false)
			&& BX.prop.getString(params, "name", "") !== this.getName()
		)
		{
			return;
		}

		this.refreshLayout();
	};
	BX.UI.EntityEditorText.prototype.getFocusInputID = function()
	{
		return this._id.toLowerCase() + "_text";
	};
	BX.UI.EntityEditorText.create = function(id, settings)
	{
		var self = new BX.UI.EntityEditorText();
		self.initialize(id, settings);
		return self;
	};
}

if(typeof BX.UI.EntityEditorTextarea === "undefined")
{
	BX.UI.EntityEditorTextarea = function()
	{
		BX.UI.EntityEditorText.superclass.constructor.apply(this);
		this._input = null;
		this._innerWrapper = null;
	};

	BX.extend(BX.UI.EntityEditorTextarea, BX.UI.EntityEditorField);
	BX.UI.EntityEditorTextarea.prototype.getModeSwitchType = function(mode)
	{
		var result = BX.UI.EntityEditorModeSwitchType.common;
		if(mode === BX.UI.EntityEditorMode.edit)
		{
			result |= BX.UI.EntityEditorModeSwitchType.button|BX.UI.EntityEditorModeSwitchType.content;
		}
		return result;
	};
	BX.UI.EntityEditorTextarea.prototype.getContentWrapper = function()
	{
		return this._innerWrapper;
	};
	BX.UI.EntityEditorTextarea.prototype.focus = function()
	{
		if(!this._input)
		{
			return;
		}

		BX.focus(this._input);
		BX.UI.EditorTextHelper.getCurrent().setPositionAtEnd(this._input);
	};
	BX.UI.EntityEditorTextarea.prototype.layout = function(options)
	{
		if(this._hasLayout)
		{
			return;
		}

		this.ensureWrapperCreated({ classNames: [ "ui-entity-editor-field-text" ] });
		this.adjustWrapper();

		if(!this.isNeedToDisplay())
		{
			this.registerLayout(options);
			this._hasLayout = true;
			return;
		}

		var name = this.getName();
		var title = this.getTitle();
		var value = this.getValue();

		this._input = null;
		this._inputContainer = null;
		this._innerWrapper = null;

		if(this.isDragEnabled())
		{
			this._wrapper.appendChild(this.createDragButton());
		}

		if(this._mode === BX.UI.EntityEditorMode.edit)
		{
			this._wrapper.appendChild(this.createTitleNode(title));


			this._inputContainer = BX.create("div",
			{
				attrs: { className: "ui-ctl ui-ctl-textarea ui-ctl-no-resize ui-ctl-w100" }
			});

			this._input = BX.create("textarea",
			{
				attrs:
				{
					name: name,
					className: "ui-ctl-element",
					id: this._id.toLowerCase() + "_text"
				},
				children: value
			});

			this._inputContainer.appendChild(this._input);


			if(this.isNewEntity())
			{
				var placeholder = this.getCreationPlaceholder();
				if(placeholder !== "")
				{
					this._input.setAttribute("placeholder", placeholder);
				}
			}

			BX.bind(this._input, "input", this._changeHandler);

			this._innerWrapper = BX.create("div",
			{
				props: { className: "ui-entity-editor-content-block" },
				children: [ this._inputContainer ]
			});
		}
		else// if(this._mode === BX.UI.EntityEditorMode.view)
		{
			this._wrapper.appendChild(this.createTitleNode(title));

			if(this.hasContentToDisplay())
			{
				this._innerWrapper = BX.create("div",
				{
					props: { className: "ui-entity-editor-content-block" },
					children:
					[
						BX.create("div",
						{
							props: { className: "ui-entity-editor-content-block-text" },
							html: BX.util.nl2br(BX.util.htmlspecialchars(value))
						})
					]
				});
			}
			else
			{
				this._innerWrapper = BX.create("div",
				{
					props: { className: "ui-entity-editor-content-block" },
					text: BX.message("UI_ENTITY_EDITOR_FIELD_EMPTY")
				});
			}
		}

		this._wrapper.appendChild(this._innerWrapper);

		if(this.isContextMenuEnabled())
		{
			this._wrapper.appendChild(this.createContextMenuButton());
		}

		if(this.isDragEnabled())
		{
			this.initializeDragDropAbilities();
		}

		this.registerLayout(options);
		this._hasLayout = true;
	};
	BX.UI.EntityEditorTextarea.prototype.doClearLayout = function(options)
	{
		this._input = null;
		//BX.unbind(this._innerWrapper, "click", this._viewClickHandler);
		this._innerWrapper = null;
	};
	BX.UI.EntityEditorTextarea.prototype.refreshLayout = function()
	{
		if(!this._hasLayout)
		{
			return;
		}

		if(!this._isValidLayout)
		{
			BX.UI.EntityEditorTextarea.superclass.refreshLayout.apply(this, arguments);
			return;
		}

		if(this._mode === BX.UI.EntityEditorMode.edit && this._input)
		{
			this._input.value = this.getValue();
		}
		else if(this._mode === BX.UI.EntityEditorMode.view && this._innerWrapper)
		{
			this._innerWrapper.innerHTML = BX.util.htmlspecialchars(this.getValue());
		}
	};
	BX.UI.EntityEditorTextarea.prototype.getRuntimeValue = function()
	{
		return (this._mode === BX.UI.EntityEditorMode.edit && this._input
				? BX.util.trim(this._input.value) : ""
		);
	};
	BX.UI.EntityEditorTextarea.prototype.validate = function(result)
	{
		if(!(this._mode === BX.UI.EntityEditorMode.edit && this._input))
		{
			throw "BX.UI.EntityEditorTextarea. Invalid validation context";
		}

		this.clearError();

		if(this.hasValidators())
		{
			return this.executeValidators(result);
		}

		var isValid = !this.isRequired() || BX.util.trim(this._input.value) !== "";
		if(!isValid)
		{
			result.addError(BX.UI.EntityValidationError.create({ field: this }));
			this.showRequiredFieldError(this._input);
		}
		return isValid;
	};
	BX.UI.EntityEditorTextarea.prototype.showError =  function(error, anchor)
	{
		BX.UI.EntityEditorTextarea.superclass.showError.apply(this, arguments);
		if(this._input)
		{
			BX.addClass(this._inputContainer, "ui-ctl-danger");
		}
	};
	BX.UI.EntityEditorTextarea.prototype.clearError =  function()
	{
		BX.UI.EntityEditorTextarea.superclass.clearError.apply(this);
		if(this._input)
		{
			BX.removeClass(this._inputContainer, "ui-ctl-danger");
		}
	};
	BX.UI.EntityEditorTextarea.prototype.save = function()
	{
		if(this._input)
		{
			this._model.setField(this.getName(), this._input.value, { originator: this });
		}
	};
	BX.UI.EntityEditorTextarea.prototype.processModelChange = function(params)
	{
		if(BX.prop.get(params, "originator", null) === this)
		{
			return;
		}

		if(!BX.prop.getBoolean(params, "forAll", false)
			&& BX.prop.getString(params, "name", "") !== this.getName()
		)
		{
			return;
		}

		this.refreshLayout();
	};
	BX.UI.EntityEditorTextarea.prototype.getFocusInputID = function()
	{
		return this._id.toLowerCase() + "_text";
	};
	BX.UI.EntityEditorTextarea.create = function(id, settings)
	{
		var self = new BX.UI.EntityEditorTextarea();
		self.initialize(id, settings);
		return self;
	};
}

if(typeof BX.UI.EntityEditorNumber === "undefined")
{
	BX.UI.EntityEditorNumber = function()
	{
		BX.UI.EntityEditorNumber.superclass.constructor.apply(this);
		this._input = null;
		this._innerWrapper = null;
	};
	BX.extend(BX.UI.EntityEditorNumber, BX.UI.EntityEditorField);
	BX.UI.EntityEditorNumber.prototype.getModeSwitchType = function(mode)
	{
		var result = BX.UI.EntityEditorModeSwitchType.common;
		if(mode === BX.UI.EntityEditorMode.edit)
		{
			result |= BX.UI.EntityEditorModeSwitchType.button|BX.UI.EntityEditorModeSwitchType.content;
		}
		return result;
	};
	BX.UI.EntityEditorNumber.prototype.getContentWrapper = function()
	{
		return this._innerWrapper;
	};
	BX.UI.EntityEditorNumber.prototype.focus = function()
	{
		if(!this._input)
		{
			return;
		}

		BX.focus(this._input);
		BX.UI.EditorTextHelper.getCurrent().selectAll(this._input);
	};
	BX.UI.EntityEditorNumber.prototype.layout = function(options)
	{
		if(this._hasLayout)
		{
			return;
		}

		this.ensureWrapperCreated({ classNames: [ "ui-entity-editor-content-block-field-number" ] });
		this.adjustWrapper();

		if(!this.isNeedToDisplay())
		{
			this.registerLayout(options);
			this._hasLayout = true;
			return;
		}

		var name = this.getName();
		var title = this.getTitle();
		var value = this.getValue();

		if(this.isDragEnabled())
		{
			this._wrapper.appendChild(this.createDragButton());
		}

		this._input = null;
		if(this._mode === BX.UI.EntityEditorMode.edit)
		{
			this._wrapper.appendChild(this.createTitleNode(title));

			this._inputContainer = BX.create("div",
			{
				attrs: { className: "ui-ctl ui-ctl-textbox" }
			}
			);

			this._input = BX.create("input",
			{
				attrs:
					{
						name: name,
						className: "ui-ctl-element",
						type: "text",
						value: value,
						id: this._id.toLowerCase() + "_text"
					}
			}
			);

			this._inputContainer.appendChild(this._input);

			if(this.isNewEntity())
			{
				var placeholder = this.getCreationPlaceholder();
				if(placeholder !== "")
				{
					this._input.setAttribute("placeholder", placeholder);
				}
			}

			BX.bind(this._input, "input", this._changeHandler);

			this._innerWrapper = BX.create("div",
			{
				props: { className: "ui-entity-editor-content-block" },
				children: [ this._inputContainer ]
			});

		}
		else// if(this._mode === BX.UI.EntityEditorMode.view)
		{
			this._wrapper.appendChild(this.createTitleNode(title));

			if(!this.hasContentToDisplay())
			{
				value = BX.message("UI_ENTITY_EDITOR_FIELD_EMPTY");
			}

			this._innerWrapper = BX.create("div",
				{
					props: { className: "ui-entity-editor-content-block" },
					children:
						[
							BX.create("div",
							{
								props: { className: "ui-entity-editor-content-block-number" },
								text: value
							})
						]
				});
		}
		this._wrapper.appendChild(this._innerWrapper);

		if(this.isContextMenuEnabled())
		{
			this._wrapper.appendChild(this.createContextMenuButton());
		}

		if(this.isDragEnabled())
		{
			this.initializeDragDropAbilities();
		}

		this.registerLayout(options);
		this._hasLayout = true;
	};
	BX.UI.EntityEditorNumber.prototype.doClearLayout = function(options)
	{
		this._input = null;
		this._innerWrapper = null;
	};
	BX.UI.EntityEditorNumber.prototype.getRuntimeValue = function()
	{
		return (this._mode === BX.UI.EntityEditorMode.edit && this._input
				? BX.util.trim(this._input.value) : ""
		);
	};
	BX.UI.EntityEditorNumber.prototype.validate = function(result)
	{
		if(!(this._mode === BX.UI.EntityEditorMode.edit && this._input))
		{
			throw "BX.UI.EntityEditorNumber. Invalid validation context";
		}

		this.clearError();

		if(this.hasValidators())
		{
			return this.executeValidators(result);
		}

		var isValid = !this.isRequired() || BX.util.trim(this._input.value) !== "";
		if(!isValid)
		{
			result.addError(BX.UI.EntityValidationError.create({ field: this }));
			this.showRequiredFieldError(this._input);
		}
		return isValid;
	};
	BX.UI.EntityEditorNumber.prototype.showError =  function(error, anchor)
	{
		BX.UI.EntityEditorNumber.superclass.showError.apply(this, arguments);
		if(this._input)
		{
			BX.addClass(this._input, "crm-entity-widget-content-error");
		}
	};
	BX.UI.EntityEditorNumber.prototype.clearError =  function()
	{
		BX.UI.EntityEditorNumber.superclass.clearError.apply(this);
		if(this._input)
		{
			BX.removeClass(this._input, "crm-entity-widget-content-error");
		}
	};
	BX.UI.EntityEditorNumber.prototype.save = function()
	{
		if(this._input)
		{
			this._model.setField(this.getName(), this._input.value);
		}
	};
	BX.UI.EntityEditorNumber.create = function(id, settings)
	{
		var self = new BX.UI.EntityEditorNumber();
		self.initialize(id, settings);
		return self;
	}
}

if(typeof BX.UI.EntityEditorDatetime === "undefined")
{
	BX.UI.EntityEditorDatetime = function()
	{
		BX.UI.EntityEditorDatetime.superclass.constructor.apply(this);
		this._input = null;
		this._inputClickHandler = BX.delegate(this.onInputClick, this);
		this._innerWrapper = null;
	};
	BX.extend(BX.UI.EntityEditorDatetime, BX.UI.EntityEditorField);
	BX.UI.EntityEditorDatetime.prototype.getModeSwitchType = function(mode)
	{
		var result = BX.UI.EntityEditorModeSwitchType.common;
		if(mode === BX.UI.EntityEditorMode.edit)
		{
			result |= BX.UI.EntityEditorModeSwitchType.button|BX.UI.EntityEditorModeSwitchType.content;
		}
		return result;
	};
	BX.UI.EntityEditorDatetime.prototype.getContentWrapper = function()
	{
		return this._innerWrapper;
	};
	BX.UI.EntityEditorDatetime.prototype.focus = function()
	{
		if(this._input)
		{
			BX.focus(this._input);
			BX.UI.EditorTextHelper.getCurrent().selectAll(this._input);
		}
	};
	BX.UI.EntityEditorDatetime.prototype.layout = function(options)
	{
		if(this._hasLayout)
		{
			return;
		}

		this.ensureWrapperCreated({ classNames: [ "ui-entity-editor-field-date" ] });
		this.adjustWrapper();

		if(!this.isNeedToDisplay())
		{
			this.registerLayout(options);
			this._hasLayout = true;
			return;
		}

		var name = this.getName();
		var title = this.getTitle();
		var value = this.getValue();

		this._input = null;
		this._inputIcon = null;
		this._innerContainer = null;
		this._innerWrapper = null;

		if(this.isDragEnabled())
		{
			this._wrapper.appendChild(this.createDragButton());
		}

		if(this._mode === BX.UI.EntityEditorMode.edit)
		{
			this._input = BX.create("input",
				{
					attrs:
						{
							name: name,
							className: "ui-ctl-element",
							type: "text",
							value: value
						}
				}
			);

			BX.bind(this._input, "click", this._inputClickHandler);
			BX.bind(this._input, "change", this._changeHandler);
			BX.bind(this._input, "input", this._changeHandler);

			this._inputIcon = BX.create("div",
				{
					attrs: { className: "ui-ctl-after ui-ctl-icon-calendar" }
				}
			);

			this._wrapper.appendChild(this.createTitleNode(title));

			this._innerContainer = BX.create("div",
				{
					props: { className: "ui-ctl ui-ctl-after-icon ui-ctl-datetime" },
					children: [
						this._inputIcon,
						this._input
					]
				}
			);

			this._innerWrapper = BX.create("div",
				{
					props: { className: "ui-entity-editor-content-block" },
					children: [ this._innerContainer ]
				}
			);

		}
		else// if(this._mode === BX.UI.EntityEditorMode.view)
		{
			value = BX.date.format(this.getDateFormat(), BX.parseDate(value));

			this._wrapper.appendChild(this.createTitleNode(title));
			if(!this.hasContentToDisplay())
			{
				value = BX.message("UI_ENTITY_EDITOR_FIELD_EMPTY");
			}

			this._innerWrapper = BX.create("div",
				{
					props: { className: "ui-entity-editor-content-block" },
					children:
						[
							BX.create("div",
								{
									props: {className: "ui-entity-editor-content-block-text"},
									text: value
								}
							)
						]
				}
			);
		}

		this._wrapper.appendChild(this._innerWrapper);

		if(this.isContextMenuEnabled())
		{
			this._wrapper.appendChild(this.createContextMenuButton());
		}

		if(this.isDragEnabled())
		{
			this.initializeDragDropAbilities();
		}

		this.registerLayout(options);
		this._hasLayout = true;
	};
	BX.UI.EntityEditorDatetime.prototype.doRegisterLayout = function()
	{
	};
	BX.UI.EntityEditorDatetime.prototype.doClearLayout = function(options)
	{
		this._input = null;
		this._innerWrapper = null;
	};
	BX.UI.EntityEditorDatetime.prototype.getRuntimeValue = function()
	{
		return (this._mode === BX.UI.EntityEditorMode.edit && this._input
				? BX.util.trim(this._input.value) : ""
		);
	};
	BX.UI.EntityEditorDatetime.prototype.onInputClick = function(e)
	{
		this.showCalendar();
	};
	BX.UI.EntityEditorDatetime.prototype.showCalendar = function()
	{
		BX.calendar({ node: this._input, field: this._input, bTime: false, bSetFocus: false });
	};
	BX.UI.EntityEditorDatetime.prototype.getDateFormat = function()
	{
		return BX.prop.getString(this._schemeElement.getData(), "dateViewFormat", "j F Y");
	};
	BX.UI.EntityEditorDatetime.prototype.validate = function(result)
	{
		if(!(this._mode === BX.UI.EntityEditorMode.edit && this._input))
		{
			throw "BX.UI.EntityEditorDatetime. Invalid validation context";
		}

		this.clearError();

		if(this.hasValidators())
		{
			return this.executeValidators(result);
		}

		var isValid = !this.isRequired() || BX.util.trim(this._input.value) !== "";
		if(!isValid)
		{
			result.addError(BX.UI.EntityValidationError.create({ field: this }));
			this.showRequiredFieldError(this._input);
		}
		return isValid;
	};
	BX.UI.EntityEditorDatetime.prototype.showError =  function(error, anchor)
	{
		BX.UI.EntityEditorDatetime.superclass.showError.apply(this, arguments);
		if(this._input)
		{
			BX.addClass(this._inputContainer, "ui-ctl-danger");
		}
	};
	BX.UI.EntityEditorDatetime.prototype.clearError =  function()
	{
		BX.UI.EntityEditorDatetime.superclass.clearError.apply(this);
		if(this._input)
		{
			BX.removeClass(this._inputContainer, "ui-ctl-danger");
		}
	};
	BX.UI.EntityEditorDatetime.prototype.save = function()
	{
		if(this._input)
		{
			this._model.setField(this.getName(), this._input.value);
		}
	};
	BX.UI.EntityEditorDatetime.create = function(id, settings)
	{
		var self = new BX.UI.EntityEditorDatetime();
		self.initialize(id, settings);
		return self;
	}
}

if(typeof BX.UI.EntityEditorBoolean === "undefined")
{
	BX.UI.EntityEditorBoolean = function()
	{
		BX.UI.EntityEditorBoolean.superclass.constructor.apply(this);
		this._input = null;
		this._innerWrapper = null;
	};
	BX.extend(BX.UI.EntityEditorBoolean, BX.UI.EntityEditorField);
	BX.UI.EntityEditorBoolean.prototype.getModeSwitchType = function(mode)
	{
		var result = BX.UI.EntityEditorModeSwitchType.common;
		if(mode === BX.UI.EntityEditorMode.edit)
		{
			result |= BX.UI.EntityEditorModeSwitchType.button|BX.UI.EntityEditorModeSwitchType.content;
		}
		return result;
	};
	BX.UI.EntityEditorBoolean.prototype.doInitialize = function()
	{
		BX.UI.EntityEditorBoolean.superclass.doInitialize.apply(this);
		this._selectedValue = this._model.getField(this._schemeElement.getName());
	};
	BX.UI.EntityEditorBoolean.prototype.areAttributesEnabled = function()
	{
		return false;
	};
	BX.UI.EntityEditorBoolean.prototype.getContentWrapper = function()
	{
		return this._innerWrapper;
	};
	BX.UI.EntityEditorBoolean.prototype.hasValue = function()
	{
		return BX.util.trim(this.getValue()) !== "";
	};
	BX.UI.EntityEditorBoolean.prototype.getValue = function(defaultValue)
	{
		if(!this._model)
		{
			return "";
		}

		if(defaultValue === undefined)
		{
			defaultValue = "N";
		}

		var value = this._model.getStringField(
			this.getName(),
			defaultValue
		);

		if(value !== "Y" && value !== "N")
		{
			value = "N";
		}

		return value;
	};
	BX.UI.EntityEditorBoolean.prototype.getRuntimeValue = function()
	{
		if (this._mode !== BX.UI.EntityEditorMode.edit || !this._input)
			return "";

		var value = BX.util.trim(this._input.value);
		if(value !== "Y" && value !== "N")
		{
			value = "N";
		}
		return value;
	};
	BX.UI.EntityEditorBoolean.prototype.layout = function(options)
	{
		if(this._hasLayout)
		{
			return;
		}

		this.ensureWrapperCreated({ classNames: [ "ui-entity-editor-field-checkbox" ] });
		this.adjustWrapper();

		var name = this.getName();
		var title = this.getTitle();
		var value = this.getValue();

		this._input = null;
		this._innerWrapper = null;

		if(this.isDragEnabled())
		{
			this._wrapper.appendChild(this.createDragButton());
		}

		if(this._mode === BX.UI.EntityEditorMode.edit)
		{
			this._wrapper.appendChild(
				BX.create("input", { attrs: { name: name, type: "hidden", value: "N" } })
			);

			this._input = BX.create("input",
				{
					attrs:
						{
							className: "ui-ctl-element",
							name: name,
							type: "checkbox",
							value: "Y",
							checked: value === "Y"
						}
				}
			);
			BX.bind(this._input, "change", this._changeHandler);

			this._innerWrapper = BX.create("div",
				{
					props: { className: "ui-entity-editor-content-block" },
					children:
						[
							BX.create("label",
								{
									props: {className: "ui-ctl ui-ctl-xs ui-ctl-w100 ui-ctl-checkbox"},
									children:
										[
											this._input,
											BX.create("div",
												{
													attrs: { className: "ui-ctl-label-text" },
													text: title
												}
											)
										]
								}
							)
						]
				}
			);
		}
		else//if(this._mode === BX.UI.EntityEditorMode.view)
		{
			this._wrapper.appendChild(this.createTitleNode(title));
			this._innerWrapper = BX.create("div",
				{
					props: { className: "ui-entity-editor-content-block" },
					children:
						[
							BX.create("div",
								{
									props: { className: "ui-entity-editor-content-block-text"},
									text: BX.message(value === "Y" ? "UI_ENTITY_EDITOR_YES" : "UI_ENTITY_EDITOR_NO")
								}
							)
						]
				}
			);
		}

		this._wrapper.appendChild(this._innerWrapper);

		if(this.isContextMenuEnabled())
		{
			this._wrapper.appendChild(this.createContextMenuButton());
		}

		if(this.isDragEnabled())
		{
			this.initializeDragDropAbilities();
		}

		this.registerLayout(options);
		this._hasLayout = true;
	};
	BX.UI.EntityEditorBoolean.prototype.doClearLayout = function(options)
	{
		this._input = null;
		this._innerWrapper = null;
		//this._select = null;
	};
	BX.UI.EntityEditorBoolean.prototype.validate = function(result)
	{
		if(!(this._mode === BX.UI.EntityEditorMode.edit && this._input))
		{
			throw "BX.UI.EntityEditorBoolean. Invalid validation context";
		}

		if(this.hasValidators())
		{
			return this.executeValidators(result);
		}

		var isValid = !this.isRequired() || BX.util.trim(this._input.value) !== "";
		if(!isValid)
		{
			result.addError(BX.UI.EntityValidationError.create({ field: this }));
			BX.addClass(this._inputContainer, "ui-ctl-danger");
			this.showRequiredFieldError(this._input);
		}
		else
		{
			BX.removeClass(this._input, "ui-ctl-danger");
			this.clearError();
		}
		return isValid;
	};
	BX.UI.EntityEditorBoolean.prototype.showError =  function(error, anchor)
	{
		BX.UI.EntityEditorBoolean.superclass.showError.apply(this, arguments);
		if(this._input)
		{
			BX.addClass(this._inputContainer, "ui-ctl-danger");
		}
	};
	BX.UI.EntityEditorBoolean.prototype.clearError =  function()
	{
		BX.UI.EntityEditorBoolean.superclass.clearError.apply(this);
		if(this._input)
		{
			BX.removeClass(this._input, "ui-ctl-danger");
		}
	};
	BX.UI.EntityEditorBoolean.prototype.save = function()
	{
		if(this._input)
		{
			this._model.setField(this.getName(), this._input.checked ? "Y" : "N", { originator: this });
		}
	};
	BX.UI.EntityEditorBoolean.create = function(id, settings)
	{
		var self = new BX.UI.EntityEditorBoolean();
		self.initialize(id, settings);
		return self;
	}
}

if(typeof BX.UI.EntityEditorList === "undefined")
{
	BX.UI.EntityEditorList = function()
	{
		BX.UI.EntityEditorList.superclass.constructor.apply(this);
		this._items = null;
		this._input = null;
		this._select = null;
		this._selectContainer = null;
		this._selectedValue = "";
		this._selectorClickHandler = BX.delegate(this.onSelectorClick, this);
		this._innerWrapper = null;
		this._isOpened = false;
	};
	BX.extend(BX.UI.EntityEditorList, BX.UI.EntityEditorField);
	BX.UI.EntityEditorList.prototype.getModeSwitchType = function(mode)
	{
		var result = BX.UI.EntityEditorModeSwitchType.common;
		if(mode === BX.UI.EntityEditorMode.edit)
		{
			result |= BX.UI.EntityEditorModeSwitchType.button|BX.UI.EntityEditorModeSwitchType.content;
		}
		return result;
	};
	BX.UI.EntityEditorList.prototype.getContentWrapper = function()
	{
		return this._innerWrapper;
	};
	BX.UI.EntityEditorList.prototype.checkIfNotEmpty = function(value)
	{
		//0 is value for "Not Selected" item
		return value !== "" && value !== "0";
	};
	BX.UI.EntityEditorList.prototype.layout = function(options)
	{
		if(this._hasLayout)
		{
			return;
		}

		this.ensureWrapperCreated({ classNames: [ "ui-entity-editor-field-select" ] });
		this.adjustWrapper();

		if(!this.isNeedToDisplay())
		{
			this.registerLayout(options);
			this._hasLayout = true;
			return;
		}

		var name = this.getName();
		var title = this.getTitle();

		var value = this.getValue();
		var item = this.getItemByValue(value);
		var isHtmlOption = this.getDataBooleanParam('isHtml', false);
		var containerProps = {};

		if(!item)
		{
			item = this.getFirstItem();
			if(item)
			{
				value = item["VALUE"];
			}
		}
		this._selectedValue = value;

		this._select = null;
		this._selectIcon = null;
		this._innerWrapper = null;

		if(this.isDragEnabled())
		{
			this._wrapper.appendChild(this.createDragButton());
		}

		if(this._mode === BX.UI.EntityEditorMode.edit)
		{
			this._wrapper.appendChild(this.createTitleNode(title));

			this._input = BX.create("input", { attrs: { name: name, type: "hidden", value: value } });
			this._wrapper.appendChild(this._input);

			containerProps = { props: { className: "ui-ctl-element" }};
			if (isHtmlOption)
			{
				containerProps.html = (item ? item["NAME"] : value);
			}
			else
			{
				containerProps.text = (item ? item["NAME"] : value);
			}

			this._select = BX.create("div", containerProps);
			BX.bind(this._select, "click", this._selectorClickHandler);

			this._selectIcon = BX.create("div",
				{
					attrs: { className: "ui-ctl-after ui-ctl-icon-angle" }
				}
			);

			this._selectContainer = BX.create("div",
				{
					props: {className: "ui-ctl ui-ctl-after-icon ui-ctl-dropdown ui-ctl-w100"},
					children :[
						this._select,
						this._selectIcon
					]
				}
			);

			this._innerWrapper = BX.create("div",
				{
					props: { className: "ui-entity-editor-content-block" },
					children: [ this._selectContainer ]
				}
			);
		}
		else// if(this._mode === BX.UI.EntityEditorMode.view)
		{
			this._wrapper.appendChild(this.createTitleNode(title));

			var text = "";
			if(!this.hasContentToDisplay())
			{
				text = BX.message("UI_ENTITY_EDITOR_FIELD_EMPTY");
			}
			else if(item)
			{
				text = item["NAME"];
			}
			else
			{
				text = value;
			}

			var containerProps = {props: { className: "ui-entity-editor-content-block-text" }};

			if (isHtmlOption)
			{
				containerProps.html = text;
			}
			else
			{
				containerProps.text = text;
			}

			this._innerWrapper = BX.create("div",
				{
					props: { className: "ui-entity-editor-content-block" },
					children:
						[
							BX.create("div", containerProps)
						]
				}
			);
		}
		this._wrapper.appendChild(this._innerWrapper);

		if(this.isContextMenuEnabled())
		{
			this._wrapper.appendChild(this.createContextMenuButton());
		}

		if(this.isDragEnabled())
		{
			this.initializeDragDropAbilities();
		}

		this.registerLayout(options);
		this._hasLayout = true;
	};
	BX.UI.EntityEditorList.prototype.doRegisterLayout = function()
	{
	};
	BX.UI.EntityEditorList.prototype.doClearLayout = function(options)
	{
		this.closeMenu();

		this._input = null;
		this._select = null;
		this._innerWrapper = null;
	};
	BX.UI.EntityEditorList.prototype.refreshLayout = function()
	{
		if(!this._hasLayout)
		{
			return;
		}

		if(!this._isValidLayout)
		{
			BX.UI.EntityEditorList.superclass.refreshLayout.apply(this, arguments);
			return;
		}

		var value = this.getValue();
		var item = this.getItemByValue(value);
		var text = item ? BX.prop.getString(item, "NAME", value) : value;
		if(this._mode === BX.UI.EntityEditorMode.edit)
		{
			this._selectedValue = value;
			if(this._input)
			{
				this._input.value  = value;
			}
			if(this._select)
			{
				this._select.innerHTML = this.getDataBooleanParam('isHtml', false) ? text : BX.util.htmlspecialchars(text);
			}
		}
		else if(this._mode === BX.UI.EntityEditorMode.view && this._innerWrapper)
		{
			this._innerWrapper.innerHTML = this.getDataBooleanParam('isHtml', false) ? text : BX.util.htmlspecialchars(text);
		}
	};
	BX.UI.EntityEditorList.prototype.validate = function(result)
	{
		if(this._mode !== BX.UI.EntityEditorMode.edit)
		{
			throw "BX.UI.EntityEditorList. Invalid validation context";
		}

		if(!this.isEditable())
		{
			return true;
		}

		this.clearError();

		if(this.hasValidators())
		{
			return this.executeValidators(result);
		}

		var isValid = !this.isRequired() || BX.util.trim(this._input.value) !== "";
		if(!isValid)
		{
			result.addError(BX.UI.EntityValidationError.create({ field: this }));
			this.showRequiredFieldError(this._input);
		}
		return isValid;
	};
	BX.UI.EntityEditorList.prototype.showError =  function(error, anchor)
	{
		BX.UI.EntityEditorList.superclass.showError.apply(this, arguments);
		if(this._input)
		{
			BX.addClass(this._selectContainer, "ui-ctl-danger");
		}
	};
	BX.UI.EntityEditorList.prototype.clearError =  function()
	{
		BX.UI.EntityEditorList.superclass.clearError.apply(this);
		if(this._input)
		{
			BX.removeClass(this._selectContainer, "ui-ctl-danger");
		}
	};
	BX.UI.EntityEditorList.prototype.onSelectorClick = function (e)
	{
		if(!this._isOpened)
		{
			this.openMenu();
		}
		else
		{
			this.closeMenu();
		}
	};
	BX.UI.EntityEditorList.prototype.openMenu = function()
	{
		if(this._isOpened)
		{
			return;
		}

		var menu = [];
		var items = this.getItems();
		for(var i = 0, length = items.length; i < length; i++)
		{
			var item = items[i];
			if(!BX.prop.getBoolean(item, "IS_EDITABLE", true))
			{
				continue;
			}

			var value = BX.prop.getString(item, "VALUE", i);
			var name = BX.prop.getString(item, "NAME", value);
			menu.push(
				{
					text: this.getDataBooleanParam('isHtml', false) ? name : BX.util.htmlspecialchars(name),
					value: value,
					onclick: BX.delegate( this.onItemSelect, this)
				}
			);
		}

		BX.PopupMenu.show(
			this._id,
			this._select,
			menu,
			{
				angle: false, width: this._select.offsetWidth + 'px',
				events:
					{
						onPopupShow: BX.delegate( this.onMenuShow, this),
						onPopupClose: BX.delegate( this.onMenuClose, this)
					}
			}
		);
		BX.PopupMenu.currentItem.popupWindow.setWidth(BX.pos(this._select)["width"]);
	};
	BX.UI.EntityEditorList.prototype.closeMenu = function()
	{
		var menu = BX.PopupMenu.getMenuById(this._id);
		if(menu)
		{
			menu.popupWindow.close();
		}
	};
	BX.UI.EntityEditorList.prototype.onMenuShow = function()
	{
		BX.addClass(this._selectContainer, "ui-ctl-active");
		this._isOpened = true;
	};
	BX.UI.EntityEditorList.prototype.onMenuClose = function()
	{
		BX.PopupMenu.destroy(this._id);

		BX.removeClass(this._selectContainer, "ui-ctl-active");
		this._isOpened = false;
	};
	BX.UI.EntityEditorList.prototype.onItemSelect = function(e, item)
	{
		this.closeMenu();

		this._selectedValue = this._input.value  = item.value;
		var name = BX.prop.getString(
			this.getItemByValue(this._selectedValue),
			"NAME",
			this._selectedValue
		);

		this._select.innerHTML = this.getDataBooleanParam('isHtml', false) ? name : BX.util.htmlspecialchars(name);
		this.markAsChanged();
		BX.PopupMenu.destroy(this._id);

	};
	BX.UI.EntityEditorList.prototype.getItems = function()
	{
		if(!this._items)
		{
			this._items = BX.prop.getArray(this._schemeElement.getData(), "items", []);
		}
		return this._items;
	};
	BX.UI.EntityEditorList.prototype.getItemByValue = function(value)
	{
		var items = this.getItems();
		for(var i = 0, l = items.length; i < l; i++)
		{
			var item = items[i];
			if(value === BX.prop.getString(item, "VALUE", ""))
			{
				return item;
			}
		}
		return null;
	};
	BX.UI.EntityEditorList.prototype.getFirstItem = function()
	{
		var items = this.getItems();
		return items.length > 0 ? items[0] : null;
	};
	BX.UI.EntityEditorList.prototype.save = function()
	{
		if(!this.isEditable())
		{
			return;
		}

		this._model.setField(this.getName(), this._selectedValue);
	};
	BX.UI.EntityEditorList.prototype.processModelChange = function(params)
	{
		if(BX.prop.get(params, "originator", null) === this)
		{
			return;
		}

		if(!BX.prop.getBoolean(params, "forAll", false)
			&& BX.prop.getString(params, "name", "") !== this.getName()
		)
		{
			return;
		}

		this.refreshLayout();
	};
	BX.UI.EntityEditorList.prototype.getRuntimeValue = function()
	{
		return (this._mode === BX.UI.EntityEditorMode.edit && this._input
				? this._selectedValue : ""
		);
	};
	BX.UI.EntityEditorList.create = function(id, settings)
	{
		var self = new BX.UI.EntityEditorList();
		self.initialize(id, settings);
		return self;
	}
}

if(typeof BX.UI.EntityEditorMultiList === "undefined")
{
	BX.UI.EntityEditorMultiList = function()
	{
		BX.UI.EntityEditorMultiList.superclass.constructor.apply(this);
		this._items = null;
		this._input = null;
		this._select = null;
		this._selectContainer = null;
		this._selectedValue = "";
		this._selectorChangeHandler = BX.delegate(this.onSelectorChange, this);
		this._innerWrapper = null;
		this._isOpened = false;
	};
	BX.extend(BX.UI.EntityEditorMultiList, BX.UI.EntityEditorField);
	BX.UI.EntityEditorMultiList.prototype.getModeSwitchType = function(mode)
	{
		var result = BX.UI.EntityEditorModeSwitchType.common;
		if(mode === BX.UI.EntityEditorMode.edit)
		{
			result |= BX.UI.EntityEditorModeSwitchType.button|BX.UI.EntityEditorModeSwitchType.content;
		}
		return result;
	};
	BX.UI.EntityEditorMultiList.prototype.getContentWrapper = function()
	{
		return this._innerWrapper;
	};
	BX.UI.EntityEditorMultiList.prototype.checkIfNotEmpty = function(value)
	{
		//0 is value for "Not Selected" item
		return value !== "" && value !== "0";
	};
	BX.UI.EntityEditorMultiList.prototype.layout = function(options)
	{
		if(this._hasLayout)
		{
			return;
		}

		this.ensureWrapperCreated({ classNames: [ "ui-entity-editor-field-multiselect" ] });
		this.adjustWrapper();

		if(!this.isNeedToDisplay())
		{
			this.registerLayout(options);
			this._hasLayout = true;
			return;
		}

		var name = this.getName();
		var title = this.getTitle();

		var values = this.getValue();
		var items = this.getItems();
		var selectedItems = this.getItemsByValue(values);

		var isHtmlOption = this.getDataBooleanParam('isHtml', false);
		var containerProps = {};

		this._selectedValue = values;
		this._select = null;
		this._innerWrapper = null;

		if(this.isDragEnabled())
		{
			this._wrapper.appendChild(this.createDragButton());
		}

		if(this._mode === BX.UI.EntityEditorMode.edit)
		{
			this._wrapper.appendChild(this.createTitleNode(title));

			containerProps = { props: { name: name + "[]", className: "ui-ctl-element", multiple: true, size: 5}, children: []};
			if (isHtmlOption)
			{

			}
			else
			{
				for (var i = 0, l = items.length; i < l; i++)
				{
					containerProps["children"].push(BX.create("option", {
						props: {
							"value": items[i].VALUE,
							"selected": values.indexOf(BX.prop.getString(items[i], "VALUE", "")) != -1 ? true : false
						},
						text: items[i].NAME
					}));
				}
			}

			this._select = BX.create("select", containerProps);
			BX.bind(this._select, "change", this._selectorChangeHandler);

			this._selectContainer = BX.create("div",
				{
					props: {className: "ui-ctl ui-ctl-multiple-select"},
					children :[
						this._select
					]
				}
			);

			this._innerWrapper = BX.create("div",
				{
					props: { className: "ui-entity-editor-content-block" },
					children: [ this._selectContainer ]
				}
			);
		}
		else// if(this._mode === BX.UI.EntityEditorMode.view)
		{
			this._wrapper.appendChild(this.createTitleNode(title));

			var text = "";
			if(!this.hasContentToDisplay())
			{
				text = BX.message("UI_ENTITY_EDITOR_FIELD_EMPTY");
			}
			else if(selectedItems)
			{
				var selectedNames = [];
				for (i = 0, l = selectedItems.length; i < l ; i++)
				{
					selectedNames.push(selectedItems[i].NAME);
				}
				text = selectedNames.join(', ');
			}

			containerProps = {props: { className: "ui-entity-editor-content-block-text" }};

			if (isHtmlOption)
			{
				containerProps.html = text;
			}
			else
			{
				containerProps.text = text;
			}

			this._innerWrapper = BX.create("div",
				{
					props: { className: "ui-entity-editor-content-block" },
					children:
						[
							BX.create("div", containerProps)
						]
				}
			);
		}
		this._wrapper.appendChild(this._innerWrapper);

		if(this.isContextMenuEnabled())
		{
			this._wrapper.appendChild(this.createContextMenuButton());
		}

		if(this.isDragEnabled())
		{
			this.initializeDragDropAbilities();
		}

		this.registerLayout(options);
		this._hasLayout = true;
	};
	BX.UI.EntityEditorMultiList.prototype.doRegisterLayout = function()
	{
	};
	BX.UI.EntityEditorMultiList.prototype.doClearLayout = function(options)
	{
		this._select = null;
		this._innerWrapper = null;
	};
	BX.UI.EntityEditorMultiList.prototype.refreshLayout = function()
	{
		if(!this._hasLayout)
		{
			return;
		}

		if(!this._isValidLayout)
		{
			BX.UI.EntityEditorMultiList.superclass.refreshLayout.apply(this, arguments);
			return;
		}

		var value = this.getValue();
		var item = this.getItemByValue(value);
		var text = item ? BX.prop.getString(item, "NAME", value) : value;
		if(this._mode === BX.UI.EntityEditorMode.edit)
		{
			this._selectedValue = value;
			if(this._input)
			{
				this._input.value  = value;
			}
			if(this._select)
			{
				this._select.innerHTML = this.getDataBooleanParam('isHtml', false) ? text : BX.util.htmlspecialchars(text);
			}
		}
		else if(this._mode === BX.UI.EntityEditorMode.view && this._innerWrapper)
		{
			this._innerWrapper.innerHTML = this.getDataBooleanParam('isHtml', false) ? text : BX.util.htmlspecialchars(text);
		}
	};
	BX.UI.EntityEditorMultiList.prototype.validate = function(result)
	{
		if(this._mode !== BX.UI.EntityEditorMode.edit)
		{
			throw "BX.UI.EntityEditorMultiList. Invalid validation context";
		}

		if(!this.isEditable())
		{
			return true;
		}

		this.clearError();

		if(this.hasValidators())
		{
			return this.executeValidators(result);
		}

		var isValid = !this.isRequired() || BX.util.trim(this._input.value) !== "";
		if(!isValid)
		{
			result.addError(BX.UI.EntityValidationError.create({ field: this }));
			this.showRequiredFieldError(this._input);
		}
		return isValid;
	};
	BX.UI.EntityEditorMultiList.prototype.showError =  function(error, anchor)
	{
		BX.UI.EntityEditorMultiList.superclass.showError.apply(this, arguments);
		if(this._input)
		{
			BX.addClass(this._selectContainer, "ui-ctl-danger");
		}
	};
	BX.UI.EntityEditorMultiList.prototype.clearError =  function()
	{
		BX.UI.EntityEditorMultiList.superclass.clearError.apply(this);
		if(this._input)
		{
			BX.removeClass(this._selectContainer, "ui-ctl-danger");
		}
	};
	BX.UI.EntityEditorMultiList.prototype.onSelectorChange = function (e)
	{
		this._selectedValue = [];
		for (var i = 0; i < this._select.children.length; i += 1)
		{
			if (this._select.children[i].selected)
			{
				this._selectedValue.push(this._select.children[i].value);
			}
		}

		this.markAsChanged();
	};

	BX.UI.EntityEditorMultiList.prototype.getItems = function()
	{
		if(!this._items)
		{
			this._items = BX.prop.getArray(this._schemeElement.getData(), "items", []);
		}
		return this._items;
	};

	BX.UI.EntityEditorMultiList.prototype.getItemsByValue = function(values)
	{
		var items = this.getItems();
		var showItems = [];

		for (var j in values)
		{
			values[j] = String(values[j]);
		}

		for(var i = 0, l = items.length; i < l; i++)
		{
			var item = items[i];

			if (values.indexOf(BX.prop.getString(item, "VALUE", "")) != -1)
			{
				showItems.push(item);
			}
		}

		return showItems;
	};

	BX.UI.EntityEditorMultiList.prototype.getFirstItem = function()
	{
		var items = this.getItems();
		return items.length > 0 ? items[0] : null;
	};
	BX.UI.EntityEditorMultiList.prototype.save = function()
	{
		if(!this.isEditable())
		{
			return;
		}

		this._model.setField(this.getName(), this._selectedValue);
	};
	BX.UI.EntityEditorMultiList.prototype.processModelChange = function(params)
	{
		if(BX.prop.get(params, "originator", null) === this)
		{
			return;
		}

		if(!BX.prop.getBoolean(params, "forAll", false)
			&& BX.prop.getString(params, "name", "") !== this.getName()
		)
		{
			return;
		}

		this.refreshLayout();
	};
	BX.UI.EntityEditorMultiList.prototype.getRuntimeValue = function()
	{
		return (this._mode === BX.UI.EntityEditorMode.edit && this._input
				? this._selectedValue : ""
		);
	};
	BX.UI.EntityEditorMultiList.create = function(id, settings)
	{
		var self = new BX.UI.EntityEditorMultiList();
		self.initialize(id, settings);
		return self;
	}
}

if(typeof BX.UI.EntityEditorImage === "undefined")
{
	BX.UI.EntityEditorImage = function()
	{
		BX.UI.EntityEditorImage.superclass.constructor.apply(this);
		this._innerWrapper = null;

		this._dialogShowHandler = BX.delegate(this.onDialogShow, this);
		this._dialogCloseHandler = BX.delegate(this.onDialogClose, this);
		this._fileChangeHandler = BX.delegate(this.onFileChange, this);
	};
	BX.extend(BX.UI.EntityEditorImage, BX.UI.EntityEditorField);
	BX.UI.EntityEditorImage.prototype.getModeSwitchType = function(mode)
	{
		var result = BX.UI.EntityEditorModeSwitchType.common;
		if(mode === BX.UI.EntityEditorMode.edit)
		{
			result |= BX.UI.EntityEditorModeSwitchType.button|BX.UI.EntityEditorModeSwitchType.content;
		}
		return result;
	};
	BX.UI.EntityEditorImage.prototype.getContentWrapper = function()
	{
		return this._innerWrapper;
	};
	BX.UI.EntityEditorImage.prototype.hasContentToDisplay = function()
	{
		return(this._mode === BX.UI.EntityEditorMode.edit
			|| this._model.getSchemeField(this._schemeElement, "showUrl", "") !== ""
		);
	};
	BX.UI.EntityEditorImage.prototype.layout = function(options)
	{
		if(this._hasLayout)
		{
			return;
		}

		this.ensureWrapperCreated({ classNames: [ "crm-entity-widget-content-block-field-file" ] });
		this.adjustWrapper();

		if(!this.isNeedToDisplay())
		{
			this.registerLayout(options);
			this._hasLayout = true;
			return;
		}

		var name = this.getName();
		var title = this.getTitle();
		this._innerWrapper = null;

		if(this.isDragEnabled())
		{
			this._wrapper.appendChild(this.createDragButton());
		}

		if(this._mode === BX.UI.EntityEditorMode.edit)
		{
			this._wrapper.appendChild(this.createTitleNode(title));

			this._innerWrapper = BX.create("div",
				{
					props: { className: "crm-entity-widget-content-block-inner" }
				}
			);

			BX.ajax.runComponentAction(
				"bitrix:ui.form",
				"renderImageInput",
				{ mode: "ajax", data: { moduleId: "ui", name: name, value: this.getValue() } }
			).then(
				function(result)
				{
					var data = BX.prop.getObject(result, "data", {});
					var assets = BX.prop.getObject(data, "assets", {});

					BX.html(null, BX.prop.getString(assets, "css", "")).then(
						function() {
							BX.loadScript(
								BX.prop.getArray(assets, "js", []),
								function() {
									BX.html(null, BX.prop.getArray(assets, "string", []).join("\n")).then(
										function() {
											BX.html(this._innerWrapper, BX.prop.getString(data, "html", "")).then(
												function() {
													BX.addCustomEvent(window, "onAfterPopupShow", this._dialogShowHandler);
													BX.addCustomEvent(window, "onPopupClose", this._dialogCloseHandler);

													window.setTimeout(BX.delegate(this.bindFileEvents, this), 500)
												}.bind(this)
											);
										}.bind(this)
									);
								}.bind(this)
							);
						}.bind(this)
					);
				}.bind(this));
		}
		else// if(this._mode === BX.UI.EntityEditorMode.view)
		{
			this._wrapper.appendChild(this.createTitleNode(title));
			this._innerWrapper = BX.create("div", { props: { className: "crm-entity-widget-content-block-inner" } });

			if(this.hasContentToDisplay())
			{
				this._innerWrapper.appendChild(
					BX.create("div",
						{
							props: { className: "crm-entity-widget-content-block-inner-box" },
							children:
								[
									BX.create(
										"img",
										{
											props:
												{
													className: "crm-entity-widget-content-block-photo",
													src: this._model.getSchemeField(this._schemeElement, "showUrl", "")
												}
										}
									)
								]
						}
					)
				);
			}
			else
			{
				this._innerWrapper.appendChild(document.createTextNode(this.getMessage("isEmpty")));
			}
		}
		this._wrapper.appendChild(this._innerWrapper);

		if(this.isContextMenuEnabled())
		{
			this._wrapper.appendChild(this.createContextMenuButton());
		}

		if(this.isDragEnabled())
		{
			this.initializeDragDropAbilities();
		}

		this.registerLayout(options);
		this._hasLayout = true;
	};
	BX.UI.EntityEditorImage.prototype.doClearLayout = function(options)
	{
		if(this._innerWrapper)
		{
			BX.removeCustomEvent(window, "onAfterPopupShow", this._dialogShowHandler);
			BX.removeCustomEvent(window, "onPopupClose", this._dialogCloseHandler);

			BX.cleanNode(this._innerWrapper);
			this._innerWrapper = null;
		}

		this.unbindFileEvents();
	};
	BX.UI.EntityEditorImage.prototype.bindFileEvents = function()
	{
		var fileControl = BX.MFInput ? BX.MFInput.get(this.getName().toLowerCase() + "_uploader") : null;
		if(fileControl)
		{
			BX.addCustomEvent(fileControl, "onAddFile", this._fileChangeHandler);
			BX.addCustomEvent(fileControl, "onDeleteFile", this._fileChangeHandler);
		}
	};
	BX.UI.EntityEditorImage.prototype.unbindFileEvents = function()
	{
		var fileControl = BX.MFInput ? BX.MFInput.get(this.getName().toLowerCase() + "_uploader") : null;
		if(fileControl)
		{
			BX.removeCustomEvent(fileControl, "onAddFile", this._fileChangeHandler);
			BX.removeCustomEvent(fileControl, "onDeleteFile", this._fileChangeHandler);
		}
	};
	BX.UI.EntityEditorImage.prototype.onDialogShow = function(popup)
	{
		if(popup.uniquePopupId.indexOf("popupavatarEditor") !== 0)
		{
			return;
		}

		BX.addCustomEvent(window, "onApply", this._fileChangeHandler);

		if(this._singleEditController)
		{
			this._singleEditController.setActiveDelayed(false);
		}

		BX.bind(
			popup.popupContainer,
			"click",
			function (e) { BX.eventCancelBubble(e); }
		);
	};
	BX.UI.EntityEditorImage.prototype.onDialogClose = function(popup)
	{
		if(BX.prop.getString(popup, "uniquePopupId", "").indexOf("popupavatarEditor") !== 0)
		{
			return;
		}

		BX.removeCustomEvent(window, "onApply", this._fileChangeHandler);

		if(this._singleEditController)
		{
			this._singleEditController.setActiveDelayed(true);
		}
	};
	BX.UI.EntityEditorImage.prototype.onFileChange = function(result)
	{
		this.markAsChanged();
	};
	BX.UI.EntityEditorImage.create = function(id, settings)
	{
		var self = new BX.UI.EntityEditorImage();
		self.initialize(id, settings);
		return self;
	};
}

if(typeof BX.UI.EntityEditorLink === "undefined")
{
	BX.UI.EntityEditorLink = function()
	{
		BX.UI.EntityEditorLink.superclass.constructor.apply(this);
		this._input = null;
		this._innerWrapper = null;
		this._link_template = "";
		this._link_target = "";
	};

	BX.extend(BX.UI.EntityEditorLink, BX.UI.EntityEditorField);
	BX.UI.EntityEditorLink.prototype.getModeSwitchType = function(mode)
	{
		var result = BX.UI.EntityEditorModeSwitchType.common;
		if(mode === BX.UI.EntityEditorMode.edit)
		{
			result |= BX.UI.EntityEditorModeSwitchType.button|BX.UI.EntityEditorModeSwitchType.content;
		}
		return result;
	};
	BX.UI.EntityEditorLink.prototype.getContentWrapper = function()
	{
		return this._innerWrapper;
	};
	BX.UI.EntityEditorLink.prototype.focus = function()
	{
		if(!this._input)
		{
			return;
		}

		BX.focus(this._input);
		BX.UI.EditorTextHelper.getCurrent().setPositionAtEnd(this._input);
	};
	BX.UI.EntityEditorLink.prototype.getLineCount = function()
	{
		return this._schemeElement.getDataIntegerParam("lineCount", 1);
	};
	BX.UI.EntityEditorLink.prototype.layout = function(options)
	{
		if(this._hasLayout)
		{
			return;
		}

		this.ensureWrapperCreated({ classNames: [ "ui-entity-editor-field-text" ] });
		this.adjustWrapper();

		if(!this.isNeedToDisplay())
		{
			this.registerLayout(options);
			this._hasLayout = true;
			return;
		}

		var name = this.getName();
		var title = this.getTitle();
		var value = this.getValue();
		var link_template = this.getLinkTemplate();
		var link_target = this.getLinkTarget();

		this._input = null;
		this._inputContainer = null;
		this._innerWrapper = null;

		if(this.isDragEnabled())
		{
			this._wrapper.appendChild(this.createDragButton());
		}

		if(this._mode === BX.UI.EntityEditorMode.edit)
		{
			this._wrapper.appendChild(this.createTitleNode(title));

			var lineCount = this.getLineCount();
			if(lineCount > 1)
			{
				this._input = BX.create("textarea",
					{
						props:
							{
								className: "ui-entity-editor-field-textarea",
								name: name,
								rows: lineCount,
								value: value
							}
					}
				);
			}
			else
			{
				this._inputContainer = BX.create("div",
					{
						attrs: { className: "ui-ctl ui-ctl-textbox ui-ctl-w100" }
					}
				);

				this._input = BX.create("input",
					{
						attrs:
							{
								name: name,
								className: "ui-ctl-element",
								type: "text",
								value: value,
								id: this._id.toLowerCase() + "_text"
							}
					}
				);

				this._inputContainer.appendChild(this._input);
			}

			if(this.isNewEntity())
			{
				var placeholder = this.getCreationPlaceholder();
				if(placeholder !== "")
				{
					this._input.setAttribute("placeholder", placeholder);
				}
			}

			BX.bind(this._input, "input", this._changeHandler);

			this._innerWrapper = BX.create("div",
				{
					props: { className: "ui-entity-editor-content-block" },
					children: [ this._inputContainer ]
				}
			);
		}
		else// if(this._mode === BX.UI.EntityEditorMode.view)
		{
			this._wrapper.appendChild(this.createTitleNode(title));

			if(this.hasContentToDisplay())
			{
				this._innerWrapper = BX.create(
					"div",
					{
						props: { className: "ui-entity-editor-content-block" },
						children:
							[
								BX.create(
									"a",
									{
										props: {
											className: "ui-entity-editor-content-block-text",
											href: link_template.replace("#LINK#", value),
											target: link_target
										},
										text: value
									}
								)
							]
					}
				);
			}
			else
			{
				this._innerWrapper = BX.create(
					"div",
					{
						props: { className: "ui-entity-editor-content-block" },
						text: BX.message("UI_ENTITY_EDITOR_FIELD_EMPTY")
					}
				);
			}
		}

		this._wrapper.appendChild(this._innerWrapper);

		if(this.isContextMenuEnabled())
		{
			this._wrapper.appendChild(this.createContextMenuButton());
		}

		if(this.isDragEnabled())
		{
			this.initializeDragDropAbilities();
		}

		this.registerLayout(options);
		this._hasLayout = true;
	};
	BX.UI.EntityEditorLink.prototype.doClearLayout = function(options)
	{
		this._input = null;
		//BX.unbind(this._innerWrapper, "click", this._viewClickHandler);
		this._innerWrapper = null;
	};
	BX.UI.EntityEditorLink.prototype.refreshLayout = function()
	{
		if(!this._hasLayout)
		{
			return;
		}

		if(!this._isValidLayout)
		{
			BX.UI.EntityEditorLink.superclass.refreshLayout.apply(this, arguments);
			return;
		}

		if(this._mode === BX.UI.EntityEditorMode.edit && this._input)
		{
			this._input.value = this.getValue();
		}
		else if(this._mode === BX.UI.EntityEditorMode.view && this._innerWrapper)
		{
			this._innerWrapper.innerHTML = BX.util.htmlspecialchars(this.getValue());
		}
	};
	BX.UI.EntityEditorLink.prototype.getLinkTemplate = function()
	{
		if(!this._link_template)
		{
			this._link_template = BX.prop.get(this._schemeElement.getData(), "link_template", "#LINK#");
		}

		return this._link_template;
	};
	BX.UI.EntityEditorLink.prototype.getLinkTarget = function()
	{
		if(!this._link_target)
		{
			this._link_target = BX.prop.get(this._schemeElement.getData(), "target", "");
		}

		return this._link_target;
	};
	BX.UI.EntityEditorLink.prototype.getRuntimeValue = function()
	{
		return (this._mode === BX.UI.EntityEditorMode.edit && this._input
				? BX.util.trim(this._input.value) : ""
		);
	};
	BX.UI.EntityEditorLink.prototype.validate = function(result)
	{
		if(!(this._mode === BX.UI.EntityEditorMode.edit && this._input))
		{
			throw "BX.UI.EntityEditorLink. Invalid validation context";
		}

		this.clearError();

		if(this.hasValidators())
		{
			return this.executeValidators(result);
		}

		var isValid = !this.isRequired() || BX.util.trim(this._input.value) !== "";
		if(!isValid)
		{
			result.addError(BX.UI.EntityValidationError.create({ field: this }));
			this.showRequiredFieldError(this._input);
		}
		return isValid;
	};
	BX.UI.EntityEditorLink.prototype.showError =  function(error, anchor)
	{
		BX.UI.EntityEditorLink.superclass.showError.apply(this, arguments);
		if(this._input)
		{
			BX.addClass(this._input, "ui-entity-editor-field-error");
		}
	};
	BX.UI.EntityEditorLink.prototype.clearError =  function()
	{
		BX.UI.EntityEditorLink.superclass.clearError.apply(this);
		if(this._input)
		{
			BX.removeClass(this._input, "ui-entity-editor-field-error");
		}
	};
	BX.UI.EntityEditorLink.prototype.save = function()
	{
		if(this._input)
		{
			this._model.setField(this.getName(), this._input.value, { originator: this });
		}
	};
	BX.UI.EntityEditorLink.prototype.processModelChange = function(params)
	{
		if(BX.prop.get(params, "originator", null) === this)
		{
			return;
		}

		if(!BX.prop.getBoolean(params, "forAll", false)
			&& BX.prop.getString(params, "name", "") !== this.getName()
		)
		{
			return;
		}

		this.refreshLayout();
	};
	BX.UI.EntityEditorLink.prototype.getFocusInputID = function()
	{
		return this._id.toLowerCase() + "_text";
	};
	BX.UI.EntityEditorLink.create = function(id, settings)
	{
		var self = new BX.UI.EntityEditorLink();
		self.initialize(id, settings);
		return self;
	};
}