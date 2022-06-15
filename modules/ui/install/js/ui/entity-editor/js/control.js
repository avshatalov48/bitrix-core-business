/**
 * @module ui
 * @version 1.0
 * @copyright 2001-2019 Bitrix
 */

BX.namespace("BX.UI");

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
		this._modeChangeNotifier = null;

		this._configurationFieldManager = null;
		this._draggableContextId = "";
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

			this.initializeManagers();
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
		getInnerConfig: function()
		{
			return this._schemeElement ? this._schemeElement.getInnerConfig() : {};
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
		isRequiredByAttribute: function()
		{
			return this._schemeElement && this._schemeElement.isRequiredByAttribute();
		},
		isHeading: function()
		{
			return this._schemeElement && this._schemeElement.isHeading();
		},
		getCreationPlaceholder: function()
		{
			return this._schemeElement ? this._schemeElement.getCreationPlaceholder() : "";
		},
		getChangePlaceholder: function()
		{
			return this._schemeElement ? this._schemeElement.getChangePlaceholder() : "";
		},
		isReadOnly: function()
		{
			return this._editor && this._editor.isReadOnly();
		},
		isEditInViewEnabled: function()
		{
			//"Edit in View" - control value may be changed in view mode
			return(this._editor
				&& this._editor.isEditInViewEnabled()
				&& this.getDataBooleanParam("enableEditInView", false)
			);
		},
		getVisibilityPolicy: function()
		{
			if(this._editor && !this._editor.isVisibilityPolicyEnabled())
			{
				return BX.UI.EntityEditorVisibilityPolicy.always;
			}

			return this._schemeElement && this._schemeElement.getVisibilityPolicy();
		},
		getEditPriority: function()
		{
			return BX.UI.EntityEditorPriority.normal;
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
			if(this._modeChangeNotifier)
			{
				this._modeChangeNotifier.notify();
			}
		},
		getModeChangeNotifier: function()
		{
			return this._modeChangeNotifier;
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
		isEditInViewEnabled: function()
		{
			//"Edit in View" - control value may be changed in view mode
			return(this._editor
				&& this._editor.isEditInViewEnabled()
				&& this.getDataBooleanParam("enableEditInView", false)
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

			var parent = this.getParent();
			if(parent)
			{
				parent.markSchemeAsChanged();
			}

			this._isSchemeChanged = true;
		},
		saveScheme: function()
		{
			if(!this._isSchemeChanged)
			{
				return;
			}

			var parent = this.getParent();
			if(parent && parent.isSchemeChanged())
			{
				return parent.saveScheme();
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
		hasLayout: function()
		{
			return this._hasLayout;
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
		needRefreshViewModeLayout: function(options)
		{
			if (this._mode === BX.UI.EntityEditorMode.edit)
			{
				return false;
			}
			if(!this._hasLayout)
			{
				return false;
			}
			return true;
		},
		refreshViewModeLayout: function(options)
		{
			if (this.needRefreshViewModeLayout(options))
			{
				this.refreshLayout(options);
			}
		},
		refreshLayout: function(options)
		{
			if(!this._hasLayout)
			{
				return;
			}
			if(!BX.type.isPlainObject(options))
			{
				options = {};
			}
			options["preservePosition"] = true;

			this.clearLayout(options);

			if(BX.prop.getBoolean(options, "reset", false))
			{
				this.reset();
			}

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
		onHideButtonClick: function(e)
		{
			this.hide();
		},
		createHideButton: function()
		{
			var enabled = !this.isRequired() && !this.isRequiredByAttribute() && !this.isRequiredConditionally();
			var button = BX.create(
				"div",
				{
					props:
						{
							className: "ui-entity-widget-content-block-hide-btn",
							title: this.getHideButtonHint(enabled)
						}
				}
			);

			if(enabled)
			{
				BX.bind(button, "click", BX.delegate(this.onHideButtonClick, this));
			}
			return button;
		},
		hide: function()
		{
			if(this.isRequired() || this.isRequiredByAttribute() || this.isRequiredConditionally())
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
			BX.onCustomEvent(this._editor, "onControlChanged", [ this, params ]);
		},
		scrollIntoView: function()
		{
			var wrapper = this.getWrapper();

			setTimeout(function() {
				var doc = BX.GetDocElement(document);
				var pos = BX.pos(wrapper);

				var finish = null;

				if (doc.scrollTop > pos.top - 10)
				{
					finish = pos.top - 10;
				}
				else if (doc.scrollTop + window.innerHeight < pos.bottom + 10)
				{
					finish = pos.bottom - window.innerHeight + 10;
				}

				if (BX.type.isNumber(finish))
				{
					(new BX.easing({
						duration: 150,
						start: {position: doc.scrollTop},
						finish: {position: finish},
						step: function(state) {
							doc.scrollTop = state.position;
						}
					})).animate();
				}
			}, 300);
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

			return this._schemeElement ? this._schemeElement.isContextMenuEnabled() : false;
		},
		onContextMenuShow: function()
		{
			this._isContextMenuOpened = true;
		},
		onContextMenuClose: function()
		{
			BX.PopupMenu.destroy(this._id);
		},
		onPopupDestroy: function()
		{
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
								onPopupClose: BX.delegate(this.onContextMenuClose, this),
								onPopupDestroy: BX.delegate(this.onPopupDestroy, this)
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
		},
		initializeManagers: function()
		{
			var eventArgs = {
				id: this.getId(),
				editor: this.getEditor(),
				type: this.getType(),
				configurationFieldManager: this._configurationFieldManager,
			};
			BX.onCustomEvent(window, "BX.UI.EntityConfigurationManager:onInitialize", [ this, eventArgs ]);

			if (eventArgs.configurationFieldManager)
			{
				this._configurationFieldManager = eventArgs.configurationFieldManager;
			}
		},
		getConfigurationFieldManager: function()
		{
			if (this._configurationFieldManager)
			{
				return this._configurationFieldManager;
			}

			return this.getEditor().getConfigurationFieldManager();
		},
		createGhostNode: function()
		{
			return null;
		},
		getHideButtonHint: function(enabled)
		{
			return "";
		}
	};

	if (typeof (BX.UI.EntityEditorControl.messages) === "undefined")
	{
		BX.UI.EntityEditorControl.messages = {};
	}
}

if(typeof BX.UI.EntityEditorField === "undefined")
{
	/**
	 * @extends BX.UI.EntityEditorControl
	 * @constructor
	 */
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

		this.eventsNamespace = 'BX.UI.EntityEditorField';
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
	BX.UI.EntityEditorField.prototype.setVisibilityConfiguration = function(configuration)
	{
		return this._schemeElement.setVisibilityConfiguration(configuration);
	};
	BX.UI.EntityEditorField.prototype.removeVisibilityConfiguration = function(attributeTypeId)
	{
		return this._schemeElement.removeVisibilityConfiguration(attributeTypeId);
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
		this._model.addChangeListener(BX.delegate(this.onModelChange, this));
		this._model.addLockListener(BX.delegate(this.onModelLock, this));
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
	BX.UI.EntityEditorField.prototype.needRefreshViewModeLayout = function(options)
	{
		if (!BX.UI.EntityEditorField.superclass.needRefreshViewModeLayout.call(this, options))
		{
			return false;
		}
		var prevModel = BX.prop.get(options, 'previousModel', null);
		if (!prevModel)
		{
			return true;
		}

		var affectedFields = this._schemeElement ? this._schemeElement.getAffectedFields() : [];
		if (!affectedFields.length)
		{
			affectedFields.push(this.getDataKey());
		}

		return affectedFields.reduce(function(result, fieldName) {
			return result || !this.areModelValuesEqual(prevModel, this._model, fieldName);
		}.bind(this), false);
	};
	BX.UI.EntityEditorField.prototype.areModelValuesEqual = function(previousModel, currentModel, fieldName)
	{
		var prevModelHasField = previousModel.hasField(fieldName);
		var curModelHasField = currentModel.hasField(fieldName);

		if (!prevModelHasField && !curModelHasField)
		{
			return true;
		}

		if (!prevModelHasField || !curModelHasField)
		{
			return false;
		}
		var prevValue = previousModel.getField(fieldName);
		var curValue = currentModel.getField(fieldName);

		return this.areValuesEqual(prevValue, curValue);
	};
	BX.UI.EntityEditorField.prototype.areValuesEqual = function(value1, value2)
	{
		return (JSON.stringify(value1) === JSON.stringify(value2));
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
	BX.UI.EntityEditorField.prototype.getMessage = function(name)
	{
		var m = BX.UI.EntityEditorField.messages;
		return (m.hasOwnProperty(name)
				? m[name]
				: BX.UI.EntityEditorField.superclass.getMessage.apply(this, arguments)
		);
	};
	BX.UI.EntityEditorField.prototype.hasContentWrapper = function()
	{
		return this.getContentWrapper() !== null;
	};
	BX.UI.EntityEditorField.prototype.getHideButtonHint = function(enabled)
	{
		return this.getMessage(
			enabled ? "hideButtonHint" : "hideButtonDisabledHint"
		);
	};
	BX.UI.EntityEditorField.prototype.getEditButton = function()
	{
		return this._singleEditButton;
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

		this.createAdditionalWrapperBlock();

		var classNames = BX.prop.getArray(params, "classNames", []);
		for(var i = 0, length = classNames.length;  i < length; i++)
		{
			BX.addClass(this._wrapper, classNames[i]);
		}
		return this._wrapper;
	};
	BX.UI.EntityEditorField.prototype.createAdditionalWrapperBlock = function()
	{
		if(!this._wrapper)
		{
			return;
		}

		var additionalBlock = BX.create("div", {
			props: { className: "ui-entity-editor-block-before-action" },
			attrs: { "data-field-tag": this.getId() }
		});

		this._wrapper.appendChild(additionalBlock);
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
	BX.UI.EntityEditorField.prototype.needShowTitle = function()
	{
		return this._schemeElement ? this._schemeElement.needShowTitle() : true;
	};
	BX.UI.EntityEditorField.prototype.isVirtual = function()
	{
		return this._schemeElement ? this._schemeElement.isVirtual() : false;
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

		if (this._mode === BX.UI.EntityEditorMode.edit)
		{
			BX.addClass(this._titleWrapper, "ui-entity-widget-content-block-title-edit");
		}

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

		var hint = this.createTitleHint();
		if (hint)
		{
			titleNode.appendChild(hint);
			BX.UI.Hint.init(titleNode);
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

		if(this.isRequired() || this.isRequiredByAttribute())
		{
			return BX.create("span", { style: { color: "#f00" }, text: "*" });
		}
		else if(this.isRequiredConditionally())
		{
			return BX.create("span", { text: "*" });
		}
		return null;
	};
	BX.UI.EntityEditorField.prototype.createTitleHint = function()
	{
		var hint = this._schemeElement ? this._schemeElement.getHint() : null;
		if(hint)
		{
			return BX.create("span", {
				dataset: {
					hint,
					hintHtml: true,
					hintInteractivity: true,
				}
			});
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

		if(this._singleEditButton)
		{
			this._singleEditButton = null;
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
	BX.UI.EntityEditorField.prototype.raiseLayoutEvent = function()
	{
		BX.onCustomEvent(window, this.eventsNamespace + ":onLayout", [ this ]);
	};
	BX.UI.EntityEditorField.prototype.hasContentToDisplay = function()
	{
		return this.hasValue();
	};
	BX.UI.EntityEditorField.prototype.isNeedToDisplay = function(options)
	{
		if (
			!(this._editor && this._editor.isExternalLayoutResolversEnabled()) &&
			(
				this._mode === BX.UI.EntityEditorMode.edit
				|| this.checkOptionFlag(BX.UI.EntityEditorControlOptions.showAlways)
				|| this._schemeElement.isShownAlways()
			)
		)
		{
			return true;
		}

		if(this._editor && BX.prop.getBoolean(options, "enableLayoutResolvers", true))
		{
			return BX.prop.getBoolean(
				this._editor.prepareFieldLayoutOptions(this),
				"isNeedToDisplay",
				true
			);
		}

		return this.hasContentToDisplay();
	};
	BX.UI.EntityEditorField.prototype.isWaitingForInput = function()
	{
		return this.isInEditMode() && (this.isRequired() || this.isRequiredByAttribute()) && !this.hasValue();
	};
	BX.UI.EntityEditorField.prototype.hide = function()
	{
		if(!(this.isRequired() || this.isRequiredConditionally() || this.isRequiredByAttribute()))
		{
			BX.UI.EntityEditorField.superclass.hide.apply(this, arguments);
			BX.onCustomEvent(window, this.eventsNamespace + ":onChildMenuItemDeselect", [ this, arguments ]);
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
	BX.UI.EntityEditorField.prototype.getEditPriority = function()
	{
		var hasValue = this.hasValue();
		if(!hasValue && (this.isRequired() || this.isRequiredByAttribute() || this.isRequiredConditionally()))
		{
			return BX.UI.EntityEditorPriority.high;
		}

		if(!this._editor.isNew())
		{
			return BX.UI.EntityEditorPriority.normal;
		}

		return hasValue ? BX.UI.EntityEditorPriority.high : this.doGetEditPriority();
	};
	BX.UI.EntityEditorField.prototype.doGetEditPriority = function()
	{
		return BX.UI.EntityEditorPriority.normal;
	};
	BX.UI.EntityEditorField.prototype.checkIfNotEmpty = function(value)
	{
		if(BX.type.isString(value))
		{
			return value.trim() !== "";
		}

		return (value !== null && value !== undefined);
	};
	BX.UI.EntityEditorField.prototype.setupFromModel = function(model, options)
	{
		if(!model)
		{
			model = this._model;
		}

		if(!model)
		{
			return;
		}

		var data = this.getRelatedModelData(model);
		this._model.updateData(data, options);
	};
	BX.UI.EntityEditorField.prototype.getRelatedModelData = function(model)
	{
		if(!model)
		{
			model = this._model;
		}

		if(!model)
		{
			return {};
		}

		var data = {};
		var keys = this.getRelatedDataKeys();
		for(var i = 0, length = keys.length; i < length; i++)
		{
			var key = keys[i];
			if(key !== "")
			{
				data[key] = model.getField(key, null);
			}
		}
		return data;
	};
	BX.UI.EntityEditorField.prototype.getRelatedDataKeys = function()
	{
		return [this.getDataKey()];
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

		this._errorContainer.innerHTML = BX.util.htmlspecialchars(error);
		if (this._wrapper)
		{
			this._wrapper.appendChild(this._errorContainer);
			BX.addClass(this._wrapper, "ui-entity-editor-field-error");
		}
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
	BX.UI.EntityEditorField.prototype.isContentWrapperOnScreen = function()
	{
		if (this.hasContentWrapper())
		{
			var doc = BX.GetDocElement(document);
			var pos = BX.pos(this.getContentWrapper());

			if (
				doc.scrollTop <= pos.top
				&& doc.scrollTop + window.innerHeight >= pos.bottom
			)
			{
				return true;
			}
		}

		return false;
	};
	BX.UI.EntityEditorField.prototype.focus = function()
	{
		if (!this.isContentWrapperOnScreen())
		{
			this.scrollAnimate();
		}
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
				html: '<label class="ui-entity-card-context-menu-item-hide-empty-wrap">' +
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

		if(this._editor)
		{
			this._editor.switchControlMode(
				this,
				BX.UI.EntityEditorMode.edit,
				BX.UI.EntityEditorModeOptions.individual
			);
		}
	};

	if (typeof (BX.UI.EntityEditorField.messages) === "undefined")
	{
		BX.UI.EntityEditorField.messages = {};
	}
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

if(typeof BX.UI.EntityEditorColumn === "undefined")
{
	/**
	 * @extends BX.UI.EntityEditorControl
	 * @constructor
	 */
	BX.UI.EntityEditorColumn = function()
	{
		BX.UI.EntityEditorColumn.superclass.constructor.apply(this);
		this._sections = null;
		this._width = 0;

		this._draggableContextId = "";
		this._dragContainerController = null;
		this._dragPlaceHolder = null;
		this._dropHandler = BX.delegate(this.onDrop, this);
	};
	BX.extend(BX.UI.EntityEditorColumn, BX.UI.EntityEditorControl);
	BX.UI.EntityEditorColumn.prototype.doSetActive = function()
	{
		for(var i = 0, l = this._sections.length; i < l; i++)
		{
			this._sections[i].setActive(this._isActive);
		}
	};
	//region Initialization
	BX.UI.EntityEditorColumn.prototype.initialize =  function(id, settings)
	{
		BX.UI.EntityEditorColumn.superclass.initialize.call(this, id, settings);
		this.initializeFromModel();
		this._width = this._schemeElement.getDataIntegerParam('width', 0);
	};
	BX.UI.EntityEditorColumn.prototype.initializeFromModel =  function()
	{
		var i, length;
		if(this._sections)
		{
			for(i = 0, length = this._sections.length; i < length; i++)
			{
				this._sections[i].release();
			}
		}

		this._sections = [];

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
			this._sections.push(field);
		}
	};
	//endregion
	//region Layout
	BX.UI.EntityEditorColumn.prototype.layout = function(options)
	{
		//Create wrapper
		this._wrapper = BX.create(
			"div",
			{
				props: {className: "ui-entity-editor-column-content"},
				style: this._width ? {flex: this._width} : null
			}
		);

		this._container.appendChild(this._wrapper);

		if (!BX.type.isPlainObject(options))
		{
			options = {};
		}

		var enableReset = BX.prop.getBoolean(options, "reset", false);

		var sectionOptions = options;
		if (sectionOptions.hasOwnProperty('anchor'))
		{
			delete sectionOptions.anchor;
		}
		for (var i = 0, l = this._sections.length; i < l; i++)
		{
			var section = this._sections[i];
			section.setContainer(this._wrapper);

			//Force layout reset because of animation implementation
			section.releaseLayout();
			if (enableReset)
			{
				section.reset();
			}

			section.layout(sectionOptions);
		}

		if (this.isDragEnabled())
		{
			this._dragContainerController = BX.UI.EditorDragContainerController.create(
				"column_" + this.getId(),
				{
					charge: BX.UI.EditorSectionDragContainer.create({
						editor: this._editor,
						column: this
					}),
					node: this._wrapper
				}
			);
			this._dragContainerController.addDragFinishListener(this._dropHandler);
		}

		this._hasLayout = true;
	};
	BX.UI.EntityEditorColumn.prototype.clearLayout = function()
	{
		if(!this._hasLayout)
		{
			return;
		}

		for(var i = 0, length = this._sections.length; i < length; i++)
		{
			var section = this._sections[i];
			section.clearLayout();
			section.setContainer(null);
		}

		this._wrapper = BX.remove(this._wrapper);
		this._hasLayout = false;
	};
	BX.UI.EntityEditorColumn.prototype.refreshLayout = function(options)
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
	BX.UI.EntityEditorColumn.prototype.refreshViewModeLayout = function(options)
	{
		if (this.needRefreshViewModeLayout(options))
		{
			for (var i = 0, l = this._sections.length; i < l; i++)
			{
				var section = this._sections[i];
				section.refreshViewModeLayout(options);
			}
		}
	};
	BX.UI.EntityEditorColumn.prototype.onStubClick = function(e)
	{
		this.toggle();
	};
	BX.UI.EntityEditorColumn.prototype.processChildControlChange = function(child, params)
	{
		if(typeof(params) === "undefined")
		{
			params = {};
		}

		if(!BX.prop.get(params, "control", null))
		{
			params["control"] = child;
		}

		if(!child.isInEditMode() && !params["control"].isInEditMode())
		{
			return;
		}

		this.markAsChanged(params);
		this.enableToggling(false);
	};
	//region Toggling & Mode control
	BX.UI.EntityEditorColumn.prototype.enableToggling = function(enable)
	{
		enable = !!enable;
		if(this._enableToggling === enable)
		{
			return;
		}

		this._enableToggling = enable;
	};
	BX.UI.EntityEditorColumn.prototype.toggle = function()
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
	BX.UI.EntityEditorColumn.prototype.onToggleBtnClick = function(e)
	{
		this.toggle();
	};
	BX.UI.EntityEditorColumn.prototype.onBeforeModeChange = function()
	{
	};
	BX.UI.EntityEditorColumn.prototype.doSetMode = function(mode)
	{
		for(var i = 0, l = this._sections.length; i < l; i++)
		{
			this._sections[i].setMode(mode, { notify: false });
		}
	};
	//endregion
	//region Tracking of Changes, Validation, Saving and Rolling back
	BX.UI.EntityEditorColumn.prototype.processAvailableSchemeElementsChange = function()
	{
	};
	BX.UI.EntityEditorColumn.prototype.validate = function(result)
	{
		if(this._mode !== BX.UI.EntityEditorMode.edit)
		{
			return true;
		}

		var validator = BX.UI.EntityAsyncValidator.create();
		for(var i = 0, length = this._sections.length; i < length; i++)
		{
			var field = this._sections[i];
			if(field.getMode() !== BX.UI.EntityEditorMode.edit)
			{
				continue;
			}

			validator.addResult(field.validate(result));
		}

		return validator.validate();
	};
	BX.UI.EntityEditorColumn.prototype.commitSchemeChanges = function()
	{
		if(this._isSchemeChanged)
		{
			var schemeElements = [];

			for(var i = 0, length = this._sections.length; i < length; i++)
			{
				this._sections[i].commitSchemeChanges();

				var schemeElement = this._sections[i].getSchemeElement();
				if(schemeElement)
				{
					schemeElements.push(schemeElement);
				}
			}

			this._schemeElement.setElements(schemeElements);
		}
		return BX.UI.EntityEditorColumn.superclass.commitSchemeChanges.call(this);
	};
	BX.UI.EntityEditorColumn.prototype.save = function()
	{
		for(var i = 0, length = this._sections.length; i < length; i++)
		{
			this._sections[i].save();
		}
	};
	BX.UI.EntityEditorColumn.prototype.rollback = function()
	{
		for(var i = 0, l = this._sections.length; i < l; i++)
		{
			this._sections[i].rollback();
		}

		if(this._isChanged)
		{
			this.initializeFromModel();
			this._isChanged = false;
		}
	};
	BX.UI.EntityEditorColumn.prototype.onBeforeSubmit = function()
	{
		for(var i = 0, length = this._sections.length; i < length; i++)
		{
			this._sections[i].onBeforeSubmit();
		}
	};
	//endregion
	//region Children & User Fields
	BX.UI.EntityEditorColumn.prototype.getChildIndex = function(child)
	{
		for(var i = 0, l = this._sections.length; i < l; i++)
		{
			if(this._sections[i] === child)
			{
				return i;
			}
		}
		return -1;
	};
	BX.UI.EntityEditorColumn.prototype.addChild = function(child, options)
	{
		if(!BX.type.isPlainObject(options))
		{
			options = {};
		}

		var related = null;
		var index = BX.prop.getInteger(options, "index", -1);
		if(index >= 0)
		{
			this._sections.splice(index, 0, child);
			if(index < (this._sections.length - 1))
			{
				related = this._sections[index + 1];
			}
		}
		else
		{
			this._sections.push(child);
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
			child.setContainer(this._wrapper);

			var layoutOpts = BX.prop.getObject(options, "layout", {});

			if(related)
			{
				layoutOpts["anchor"] = related.getWrapper();
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
	BX.UI.EntityEditorColumn.prototype.removeChild = function(child, options)
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

		this._sections.splice(index, 1);

		var processScheme = child.hasScheme();

		if(processScheme)
		{
			child.getSchemeElement().setParent(null);
		}

		if(this._hasLayout)
		{
			child.clearLayout();
			child.setContainer(null);
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
	BX.UI.EntityEditorColumn.prototype.moveChild = function(child, index, options)
	{
		if (!BX.type.isPlainObject(options))
		{
			options = {};
		}

		var qty = this.getChildCount();
		var lastIndex = qty - 1;
		if (index < 0 || index > qty)
		{
			index = lastIndex;
		}

		var currentIndex = this.getChildIndex(child);
		if (currentIndex < 0 || currentIndex === index)
		{
			return false;
		}

		if (this._hasLayout)
		{
			child.clearLayout();
		}
		this._sections.splice(currentIndex, 1);

		qty--;

		var anchor = null;
		if (this._hasLayout)
		{
			anchor = index < qty
				? this._sections[index].getWrapper()
				: null;
		}

		if (index < qty)
		{
			this._sections.splice(index, 0, child);
		}
		else
		{
			this._sections.push(child);
		}

		if (this._hasLayout)
		{
			if (anchor)
			{
				child.layout({anchor: anchor});
			}
			else
			{
				child.layout();
			}
		}

		this._editor.processControlMove(child);
		this.markSchemeAsChanged();

		if (BX.prop.getBoolean(options, "enableSaving", true))
		{
			this.saveScheme();
		}

		return true;
	};
	BX.UI.EntityEditorColumn.prototype.editChild = function(child)
	{
		if(this._mode === BX.UI.EntityEditorMode.edit)
		{
			child.focus();
		}
		else if(!this.isReadOnly())
		{
			var isHomogeneous = true;
			for(var i = 0, length = this._sections.length; i < length; i++)
			{
				if(this._sections[i].getMode() !== this._mode)
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
	BX.UI.EntityEditorColumn.prototype.getChildById = function(childId)
	{
		for(var i = 0, length = this._sections.length; i < length; i++)
		{
			var field = this._sections[i];
			if(field.getId() === childId)
			{
				return field;
			}

			var child = field.getChildById(childId);
			if(child)
			{
				return child;
			}
		}
		return null;
	};
	BX.UI.EntityEditorColumn.prototype.getChildCount = function()
	{
		return this._sections.length;
	};
	BX.UI.EntityEditorColumn.prototype.getChildren = function()
	{
		return this._sections;
	};
	BX.UI.EntityEditorColumn.prototype.processChildControlModeChange = function(child)
	{
		if(!this.isActive() && this._editor)
		{
			this._editor.processControlModeChange(child);
		}
	};
	//region Context Menu
	BX.UI.EntityEditorColumn.prototype.hasAdditionalMenu = function()
	{
		return false;
	};
	BX.UI.EntityEditorColumn.prototype.getAdditionalMenu = function()
	{
		return [];
	};
	//endregion
	BX.UI.EntityEditorColumn.prototype.isWaitingForInput = function()
	{
		for(var i = 0, l = this._sections.length; i < l; i++)
		{
			if(this._sections[i].isWaitingForInput())
			{
				return true;
			}
		}
		return false;
	};
	BX.UI.EntityEditorColumn.prototype.isRequired = function()
	{
		for(var i = 0, l = this._sections.length; i < l; i++)
		{
			if(this._sections[i].isRequired())
			{
				return true;
			}
		}
		return false;
	};
	BX.UI.EntityEditorColumn.prototype.isRequiredConditionally = function()
	{
		for(var i = 0, l = this._sections.length; i < l; i++)
		{
			if(this._sections[i].isRequiredConditionally())
			{
				return true;
			}
		}
		return false;
	};
	BX.UI.EntityEditorColumn.prototype.getDragObjectType = function()
	{
		return BX.UI.EditorDragObjectType.section;
	};
	BX.UI.EntityEditorColumn.prototype.getChildDragObjectType = function()
	{
		return BX.UI.EditorDragObjectType.field;
	};
	BX.UI.EntityEditorColumn.prototype.hasPlaceHolder = function()
	{
		return !!this._dragPlaceHolder;
	};
	BX.UI.EntityEditorColumn.prototype.createPlaceHolder = function(index)
	{
		var qty = this._sections.length;

		if (index < 0 || index > qty)
		{
			index = qty > 0 ? qty : 0;
		}

		if (this._dragPlaceHolder)
		{
			if (this._dragPlaceHolder.getIndex() === index)
			{
				return this._dragPlaceHolder;
			}

			this._dragPlaceHolder.clearLayout();
			this._dragPlaceHolder = null;
		}

		this._dragPlaceHolder = BX.UI.EditorDragSectionPlaceholder.create(
			{
				container: this._wrapper,
				anchor: (index < qty) ? this._sections[index].getWrapper() : null,
				index: index
			}
		);

		this._dragPlaceHolder.layout();

		return this._dragPlaceHolder;
	};
	BX.UI.EntityEditorColumn.prototype.getPlaceHolder = function()
	{
		return this._dragPlaceHolder;
	};
	BX.UI.EntityEditorColumn.prototype.removePlaceHolder = function()
	{
		if(this._dragPlaceHolder)
		{
			this._dragPlaceHolder.clearLayout();
			this._dragPlaceHolder = null;
		}
	};
	BX.UI.EntityEditorColumn.prototype.processDraggedItemDrop = function(dropContainer, draggedItem)
	{
		var containerCharge = dropContainer.getCharge();
		if(!((containerCharge instanceof BX.UI.EditorSectionDragContainer) && containerCharge.getColumn() === this))
		{
			return;
		}

		var context = draggedItem.getContextData();
		var contextId = BX.type.isNotEmptyString(context["contextId"]) ? context["contextId"] : "";
		if(contextId !== BX.UI.EditorSectionDragItem.contextId)
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
		if(!(itemCharge instanceof BX.UI.EditorSectionDragItem))
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
			if(source.getMode() === BX.UI.EntityEditorMode.edit)
			{
				this._editor.unregisterActiveControl(source);
			}
			sourceParent.removeChild(source, { enableSaving: false });

			var target = this._editor.createControl(
				schemeElement.getType(),
				schemeElement.getName(),
				{ schemeElement: schemeElement, model: this._model, parent: this, mode: source.getMode() }
			);

			this.addChild(target, { index: placeholderIndex});
			this._editor.processControlModeChange(target);
			this._editor.saveSchemeChanges();
		}
	};
	BX.UI.EntityEditorColumn.prototype.onDrop = function(event)
	{
		this.processDraggedItemDrop(event.data["dropContainer"], event.data["draggedItem"]);
	};
	BX.UI.EntityEditorColumn.prototype.initializeDragDropAbilities = function()
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
	BX.UI.EntityEditorColumn.prototype.releaseDragDropAbilities = function()
	{
		if(this._dragItem)
		{
			this._dragItem.release();
			this._dragItem = null;
		}
	};
	BX.UI.EntityEditorColumn.create = function(id, settings)
	{
		var self = new BX.UI.EntityEditorColumn();
		self.initialize(id, settings);
		return self;
	}
}

if(typeof BX.UI.EntityEditorSection === "undefined")
{
	/**
	 * @extends BX.UI.EntityEditorControl
	 * @constructor
	 */
	BX.UI.EntityEditorSection = function()
	{
		BX.UI.EntityEditorSection.superclass.constructor.apply(this);
		this._fields = null;
		this._fieldConfigurator = null;
		this._mandatoryConfigurator = null;
		this._visibilityConfigurator = null;

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

		this._detailButton = null;
		this.eventsNamespace = 'BX.UI.EntityEditorSection';
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
		this.release();

		var elements = this._schemeElement.getElements();
		for(var i = 0, length = elements.length; i < length; i++)
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
	BX.UI.EntityEditorSection.prototype.registerLayout = function(options)
	{
		this._wrapper.style.display = this.isVisible() ? '' : 'none';
		BX.UI.EntityEditorField.superclass.registerLayout.apply(this, arguments);
	};

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

		var firstColumn = this.getEditor().getControlByIndex(0);
		var url = BX.prop.getString(this.getEditor()._settings, "entityDetailsUrl", "");
		if (this.getEditor().isEmbedded() && url.length)
		{
			var sectionIndex = null;
			if(firstColumn)
			{
				sectionIndex = firstColumn.getChildren().indexOf(this);
			}

			if (sectionIndex === 0)
			{
				this._detailButton = BX.create("a",
					{
						attrs: {
							className: "ui-entity-editor-detail-btn",
							href: url
						},
						text: BX.message('UI_ENTITY_EDITOR_SECTION_OPEN_DETAILS')
					}
				);
			}
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

			if (this._detailButton)
			{
				this._titleActions.appendChild(this._detailButton);
			}

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
		var focusedField = null;
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
				focusedField = field;
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
			this.showButtonPanel();
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
		var serialNumber = null;
		if(firstColumn)
		{
			serialNumber = firstColumn.getChildren().indexOf(this);
		}
		var eventArgs =  { id: this._id, customNodes: [], visible: true, serialNumber: serialNumber };
		BX.onCustomEvent(window, this.eventsNamespace + ":onLayout", [ this, eventArgs ]);
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
		if("visible" in eventArgs && BX.type.isBoolean(eventArgs["visible"]))
		{
			this.setVisible(eventArgs["visible"]);
		}
		//endregion

		this.registerLayout(options);
		this._hasLayout = true;
		if (focusedField)
		{
			focusedField.focus(focusedField.getDataBooleanParam('selected', false));
		}
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
	BX.UI.EntityEditorSection.prototype.refreshViewModeLayout = function(options)
	{
		if (this.needRefreshViewModeLayout(options))
		{
			for (var i = 0, l = this._fields.length; i < l; i++)
			{
				var field = this._fields[i];
				field.refreshViewModeLayout(options);
			}
		}
	};

	BX.UI.EntityEditorSection.prototype.release = function()
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
	BX.UI.EntityEditorSection.prototype.showButtonPanel = function()
	{
		this.ensureButtonPanelCreated();

		if (this._schemeElement.getDataBooleanParam("isChangeable", true))
		{
			if (this.getConfigurationFieldManager().isSelectionEnabled())
			{
				this._addChildButton = BX.create("span",
					{
						props: {className: "ui-entity-editor-content-add-lnk"},
						text: BX.message("UI_ENTITY_EDITOR_SELECT_FIELD"),
						events: {click: BX.delegate(this.onAddChildBtnClick, this)}
					});
				this.addButtonElement(this._addChildButton, {position: "left"});
			}

			if (this.getConfigurationFieldManager().isCreationEnabled())
			{
				this._createChildButton = BX.create("span",
					{
						props: {className: "ui-entity-editor-content-create-lnk"},
						text: BX.message("UI_ENTITY_EDITOR_CREATE_FIELD"),
						events: {click: BX.delegate(this.onCreateFieldBtnClick, this)}
					});
				this.addButtonElement(this._createChildButton, {position: "left"});
			}
		}

		if (this._schemeElement.getDataBooleanParam("isRemovable", true))
		{
			var deleteClassName = "ui-entity-editor-content-remove-lnk";
			if (this.isRequired() || this.isRequiredByAttribute() || this.isRequiredConditionally())
			{
				deleteClassName = "ui-entity-editor-content-remove-lnk-disabled";
			}

			this._deleteButton = BX.create("span", {
				props: {className: deleteClassName},
				text: BX.message("UI_ENTITY_EDITOR_DELETE_SECTION")
			});
			this.addButtonElement(this._deleteButton, {position: "right"});
			BX.bind(this._deleteButton, "click", this._deleteButtonHandler);
		}

		this._contentContainer.appendChild(this._buttonPanelWrapper);
		this.adjustButtons();
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
		this.removeFieldConfigurator();
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

		var validator = BX.UI.EntityAsyncValidator.create();
		for(var i = 0, length = this._fields.length; i < length; i++)
		{
			var field = this._fields[i];
			if(field.getMode() !== BX.UI.EntityEditorMode.edit)
			{
				continue;
			}

			validator.addResult(field.validate(result));
		}

		return validator.validate();
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
			if (
				!BX.hasClass(this._contentContainer, "ui-entity-editor-section-content-padding-right") &&
				child.isContextMenuEnabled()
			)
			{
				BX.addClass(this._contentContainer, "ui-entity-editor-section-content-padding-right");
			}
		}

		var scrollIntoView = BX.prop.getBoolean(options, "scrollIntoView", false);
		if (scrollIntoView)
		{
			child.scrollIntoView();
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
		this.createFieldConfigurator(
			{
				field: child,
				enableMandatoryControl: this.getConfigurationFieldManager().isMandatoryControlEnabled()
			}
		);
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

			if (schemeElement)
			{
				var data = schemeElement.getData();

				if (data.doNotDisplayInShowFieldList)
				{
					continue;
				}
			}

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
		BX.onCustomEvent(window, this.eventsNamespace + ":onOpenChildMenu", [ this, eventArgs ]);

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
		BX.onCustomEvent(window, this.eventsNamespace + ":onChildMenuItemSelect", [ this, eventArgs ]);

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
					excludedNames: [this.getSchemeElement().getName()],
					title: BX.message("UI_ENTITY_EDITOR_FIELD_TRANSFER_DIALOG_TITLE"),
					buttonTitle: this._settings.editor._entityTypeTitle,
					useFieldsSearch: this._settings.editor._useFieldsSearch,
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
	BX.UI.EntityEditorSection.prototype.createFieldConfigurator = function(params)
	{
		if(!BX.type.isPlainObject(params))
		{
			throw "EntityEditorSection: The 'params' argument must be object.";
		}

		params.mandatoryConfigurator = null;
		var child = BX.prop.get(params, "field", null);
		var attrManager = this._editor.getAttributeManager();
		if(attrManager)
		{
			params.mandatoryConfigurator = attrManager.createFieldConfigurator(
				child,
				BX.UI.EntityFieldAttributeType.required
			);
		}

		this._fieldConfigurator = this.getConfigurationFieldManager().createFieldConfigurator(params, this);

		this.addChild(this._fieldConfigurator, {
			related: child,
			scrollIntoView: true
		});

		if (this._fieldConfigurator instanceof BX.UI.EntityEditorUserFieldConfigurator)
		{
			this._mandatoryConfigurator = params.mandatoryConfigurator;
			BX.addCustomEvent(this._fieldConfigurator, "onSave", BX.delegate(this.onUserFieldConfigurationSave, this));
			BX.addCustomEvent(this._fieldConfigurator, "onCancel", BX.delegate(this.onFieldConfigurationCancel, this));
		}
		else
		{
			BX.addCustomEvent(this._fieldConfigurator, "onSave", BX.delegate(this.onFieldConfigurationSave, this));
			BX.addCustomEvent(this._fieldConfigurator, "onCancel", BX.delegate(this.onFieldConfigurationCancel, this));
		}
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
			BX.onCustomEvent(this._editor, this._editor.eventsNamespace + ":onFieldCreate", [ this, params ]);
			this.removeFieldConfigurator();

			return;
		}

		var label = BX.prop.getString(params, "label", "");
		var showAlways = BX.prop.getBoolean(params, "showAlways", null);
		if(label === "" && showAlways === null)
		{
			this.removeFieldConfigurator();
			this._mandatoryConfigurator = null;
			this._visibilityConfigurator = null;
			return;
		}

		this._fieldConfigurator.setLocked(true);
		field.getSchemeElement().setTitle(label);
		if(showAlways !== null && showAlways !== field.checkOptionFlag(BX.UI.EntityEditorControlOptions.showAlways))
		{
			field.toggleOptionFlag(BX.UI.EntityEditorControlOptions.showAlways);
		}

		BX.onCustomEvent(this._editor, this._editor.eventsNamespace + ":onFieldModify", [ this, params ]);

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
								this._editor.getAttributeManager().saveConfiguration(
									attributeConfig,
									field.getName()
								);
								field.setAttributeConfiguration(attributeConfig);
							}
							else
							{
								var attributeTypeId = this._mandatoryConfigurator.getTypeId();
								this._editor.getAttributeManager().removeConfiguration(
									attributeTypeId,
									field.getName()
								);
								field.removeAttributeConfiguration(attributeTypeId);
							}
						}
						this._mandatoryConfigurator = null;
						this._visibilityConfigurator = null;
					}
					this.removeFieldConfigurator();
					field.setTitle(label);
				},
				this
			)
		)

		var typeId = BX.prop.getString(params, "typeId");
		if (typeId === "list")
		{
			var fieldData = { "typeId": typeId };

			fieldData["innerConfig"] = BX.prop.getObject(params, "innerConfig", {});
			fieldData["enumeration"] = BX.prop.getArray(params, "enumeration", []);

			if (
				BX.Type.isPlainObject(fieldData["innerConfig"])
				&& fieldData["innerConfig"].hasOwnProperty("controller")
				&& BX.Type.isStringFilled(fieldData["innerConfig"]["controller"])
			)
			{
				BX.ajax.runAction(fieldData["innerConfig"]["controller"], { data: { configData: fieldData } }).then(
					function(response) {
						if (
							BX.Type.isObject(response)
							&& response.hasOwnProperty("status")
							&& response.status === "success"
							&& response.hasOwnProperty("data")
							&& BX.Type.isArray(response["data"])
						)
						{
							var field = BX.prop.get(params, "field", null);
							if (BX.Type.isObject(field))
							{
								var enumeration = response["data"];
								var items = [];
								for (var i = 0; i < enumeration.length; i++)
								{
									items.push({
										"NAME": enumeration[i]["VALUE"],
										"VALUE": enumeration[i]["ID"]
									});
								}
								field.getSchemeElement().setDataParam("items", items);
								field.setItems();
								field.refreshLayout();
							}
						}
						else
						{
							console.error("Invalid server response.");
						}
					}.bind(this),
					function(response) {
						if (
							BX.Type.isObject(response)
							&& response.hasOwnProperty("status")
							&& response["status"] === "error"
							&& response.hasOwnProperty("errors")
							&& BX.Type.isArray(response["errors"])
							&& response["errors"].length > 0
							&& BX.Type.isPlainObject(response["errors"][0])
							&& response["errors"][0].hasOwnProperty("message")
							&& BX.Type.isString(response["errors"][0]["message"])
						)
						{
							console.error(response["errors"][0]["message"]);
						}
						else
						{
							console.error("Invalid server response.");
						}
					}.bind(this)
				);
			}
		}
	};
	BX.UI.EntityEditorSection.prototype.onFieldConfigurationCancel = function(sender, params)
	{
		if(sender !== this._fieldConfigurator)
		{
			return;
		}

		this.removeFieldConfigurator();

		this._mandatoryConfigurator = null;
		this._visibilityConfigurator = null;
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
	BX.UI.EntityEditorSection.prototype.onCreateFieldBtnClick = function(e)
	{
		if(!this._fieldTypeSelectMenu)
		{
			var infos = this.getConfigurationFieldManager().getTypeInfos();
			var items = [];
			for(var i = 0, length = infos.length; i < length; i++)
			{
				var info = infos[i];
				items.push({
					value: info.name,
					text: info.title,
					legend: info.legend,
					callback: !!info.callback ? info.callback : null
				});
			}

			this._fieldTypeSelectMenu = BX.UI.UserFieldTypeMenu.create(
				this._id,
				{
					items: items,
					callback: BX.delegate(this.onFieldTypeSelect, this)
				}
			);
		}
		this._fieldTypeSelectMenu.open(this._createChildButton);
	};
	BX.UI.EntityEditorSection.prototype.getCreationConfigurator = function(typeId)
	{
		return this.createFieldConfigurator(
			{
				typeId: typeId,
				enableMandatoryControl: this.getConfigurationFieldManager().isMandatoryControlEnabled()
			}
		);
	};
	BX.UI.EntityEditorSection.prototype.onFieldTypeSelect = function(sender, item)
	{
		this._fieldTypeSelectMenu.close();

		var typeId = item.getValue();
		if(typeId === "")
		{
			return;
		}

		if(this.getConfigurationFieldManager().hasExternalForm(typeId))
		{
			this.getConfigurationFieldManager().openCreationPageUrl(typeId);
		}
		else
		{
			this.removeFieldConfigurator();
			this.getCreationConfigurator(typeId);
		}
	};
	BX.UI.EntityEditorSection.prototype.onUserFieldConfigurationSave = function(sender, params)
	{
		if(sender !== this._fieldConfigurator)
		{
			return;
		}

		this._fieldConfigurator.setLocked(true);

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

			if (!this.getEditor().canChangeCommonConfiguration())
			{
				if(showAlways !== null)
				{
					this._editor.setOption("show_always", showAlways ? "Y" : "N");
				}
				BX.delegate(this.onUserFieldUpdate, this);
				this.removeFieldConfigurator();
				return;
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
				fieldData['SETTINGS']['DISPLAY'] = BX.prop.getString(params, 'display', 'UI');
			}

			if(
				typeId === BX.UI.EntityUserFieldType.enumeration
				|| typeId === BX.UI.EntityUserFieldType.crmStatus
			)
			{
				fieldData['SETTINGS']['DISPLAY'] = BX.prop.getString(params, 'display', 'UI');
			}

			field.adjustFieldParams(fieldData, false);

			var updateField = function()
			{
				this._editor.getUserFieldManager().updateField(
					fieldData,
					field.getMode()
				).then(
					BX.delegate(this.onUserFieldUpdate, this)
				);
			}.bind(this);

			if (typeId === BX.UI.EntityUserFieldType.crmStatus)
			{
				var displaySettings = {};
				displaySettings.DISPLAY= BX.prop.getString(params, 'display', 'UI');

				var configData = {
					"typeId": typeId,
					"innerConfig": BX.prop.getObject(params, "innerConfig", {}),
					"enumeration": BX.prop.getArray(params, "enumeration", []),
					"SETTINGS": displaySettings,
				};

				if (
					BX.Type.isPlainObject(configData["innerConfig"])
					&& configData["innerConfig"].hasOwnProperty("controller")
					&& BX.Type.isStringFilled(configData["innerConfig"]["controller"])
				)
				{
					BX.ajax.runAction(
						configData["innerConfig"]["controller"],
						{ data: { configData: configData } }
					).then(
						function(response) {
							if (
								!(
									BX.Type.isObject(response)
									&& response.hasOwnProperty("status")
									&& response.status === "success"
									&& response.hasOwnProperty("data")
									&& BX.Type.isArray(response["data"])
								)
							)
							{
								console.error("Invalid server response.");
							}
							updateField();
						}.bind(this),
						function(response) {
							if (
								BX.Type.isObject(response)
								&& response.hasOwnProperty("status")
								&& response["status"] === "error"
								&& response.hasOwnProperty("errors")
								&& BX.Type.isArray(response["errors"])
								&& response["errors"].length > 0
								&& BX.Type.isPlainObject(response["errors"][0])
								&& response["errors"][0].hasOwnProperty("message")
								&& BX.Type.isString(response["errors"][0]["message"])
							)
							{
								console.error(response["errors"][0]["message"]);
							}
							else
							{
								console.error("Invalid server response.");
							}
							updateField();
						}.bind(this)
					);
				}
				else
				{
					updateField();
				}
			}
			else
			{
				updateField();
			}
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
				fieldData['SETTINGS']['DISPLAY'] = BX.prop.getString(params, 'display', 'UI');
			}

			if(
				typeId === BX.UI.EntityUserFieldType.enumeration
				|| typeId === BX.UI.EntityUserFieldType.crmStatus
			)
			{
				fieldData['SETTINGS']['DISPLAY'] = BX.prop.getString(params, 'display', 'UI');
			}

			this._editor.getUserFieldManager().createField(
				fieldData,
				this._mode
			).then(BX.delegate(this.onUserFieldCreate, this));
		}
	};
	BX.UI.EntityEditorSection.prototype.onUserFieldCreate = function(result)
	{
		if(!BX.type.isPlainObject(result))
		{
			return;
		}

		this.removeFieldConfigurator();

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

			if (this._visibilityConfigurator) {
				var visibilityConfig = {
					'accessCodes': this._visibilityConfigurator.formatAccessCodesFromConfig(
						this._visibilityConfigurator.getItems()
					)
				};
				this._visibilityConfigurator.onUserFieldConfigurationSave(
					element.getName(),
					this._editor.getEntityTypeId()
				);
				field.setVisibilityConfiguration(visibilityConfig);
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

		this._mandatoryConfigurator = null;
		this._visibilityConfigurator = null;
	};
	BX.UI.EntityEditorSection.prototype.onUserFieldUpdate = function(result)
	{
		if(!BX.type.isPlainObject(result))
		{
			return;
		}

		this.removeFieldConfigurator();

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

			if (this._visibilityConfigurator) {
				var visibilityConfig = {
					'accessCodes': this._visibilityConfigurator.formatAccessCodesFromConfig(
						this._visibilityConfigurator.getItems()
					)
				};
				this._visibilityConfigurator.onUserFieldConfigurationSave(
					element.getName(),
					this._editor.getEntityTypeId()
				);
				field.setVisibilityConfiguration(visibilityConfig);
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

		this._mandatoryConfigurator = null;
		this._visibilityConfigurator = null;
	};
	//endregion
	//region Create|Delete Section
	BX.UI.EntityEditorSection.prototype.onDeleteConfirm = function(result)
	{
		if(BX.prop.getBoolean(result, "cancel", true))
		{
			return;
		}

		if (this.getParent())
		{
			this.getParent().removeChild(this);
		}
		else
		{
			this._editor.removeSchemeElement(this.getSchemeElement());
			this._editor.removeControl(this);
			this._editor.saveScheme();
		}
	};
	BX.UI.EntityEditorSection.prototype.onDeleteSectionBtnClick = function(e)
	{
		if(this.isRequired() || this.isRequiredByAttribute() || this.isRequiredConditionally())
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
	BX.UI.EntityEditorSection.prototype.isRequiredByAttribute = function()
	{
		for(var i = 0, l = this._fields.length; i < l; i++)
		{
			if(this._fields[i].isRequiredByAttribute())
			{
				return true;
			}
		}
		return false;
	};
	BX.UI.EntityEditorSection.prototype.ensureButtonPanelWrapperCreated = function()
	{
		if(!this._hasLayout)
		{
			throw "EntityEditorSection: Control does not have layout.";
		}

		if(!this._buttonPanelWrapper)
		{
			this._buttonPanelWrapper = BX.create("div", { props: { className: "ui-entity-card-content-actions-container" } });
			this._contentContainer.appendChild(this._buttonPanelWrapper);
		}
		return this._buttonPanelWrapper;
	};
	BX.UI.EntityEditorSection.create = function(id, settings)
	{
		var self = new BX.UI.EntityEditorSection();
		self.initialize(id, settings);
		return self;
	}
}

if(typeof BX.UI.EntityEditorIncludedArea === "undefined")
{
	BX.UI.EntityEditorIncludedArea = function()
	{
		this._settings = null;
		this._owner = null;
		this._container = null;
		this._wrapper = null;
		this._loadedHtml = null;
		this._hasLayout = false;
	};

	BX.extend(BX.UI.EntityEditorIncludedArea, BX.UI.EntityEditorSection);

	BX.UI.EntityEditorIncludedArea.prototype.layout = function()
	{
		if (this._hasLayout)
		{
			return;
		}

		if (!this._wrapper)
		{
			this._wrapper = BX.create("div", {
				props: {
					className: "ui-entity-editor-included-area"
				},
				attrs: {
					'data-name': this.getName()
				}
			});
		}

		this._includedAreaContainer = BX.create("div", {
			attrs: {className: "ui-entity-editor-included-area-container"},
			children: [
				BX.create("div", {
					attrs: {
						className: "ui-entity-editor-included-area-container-loader"
					}
				})
			]
		});
		this._contentContainer = BX.create("div", {
			attrs: {className: "ui-entity-editor-included-area-content-block"},
			children: [this._includedAreaContainer]
		});

		this._wrapper.appendChild(this._contentContainer);
		this._container.appendChild(this._wrapper);

		this.loadArea();

		if (this._editor.canChangeScheme() && this._schemeElement.getDataBooleanParam("showButtonPanel", true))
		{
			this.showButtonPanel();
		}

		this._hasLayout = true;
	};

	BX.UI.EntityEditorIncludedArea.prototype.clearLayout = function()
	{
		this._buttonPanelWrapper = BX.remove(this._buttonPanelWrapper);
		this._wrapper = BX.remove(this._wrapper);
		this._hasLayout = false;
	};

	BX.UI.EntityEditorIncludedArea.prototype.loadArea = function()
	{
		if (this._includedAreaContainer && this._loadedHtml)
		{
			BX.html(this._includedAreaContainer, this._loadedHtml);
			return;
		}

		var action = this._schemeElement.getDataStringParam("action");

		if (!BX.type.isNotEmptyString(action))
		{
			return;
		}

		var config = this.prepareConfigForAction();

		BX.onCustomEvent(window, "BX.UI.EntityEditorIncludedArea:onBeforeLoad", [this, config]);

		if (this._schemeElement.getDataStringParam("type", "") === "component")
		{
			var componentName = this._schemeElement.getDataStringParam("componentName", "");
			if (!BX.type.isNotEmptyString(componentName))
			{
				return;
			}

			config.mode = this._schemeElement.getDataStringParam("mode", "ajax");

			BX.ajax.runComponentAction(componentName, action, config)
				.then(this.onLoadAreaSuccess.bind(this));
		}
		else
		{
			BX.ajax.runAction(action, config)
				.then(this.onLoadAreaSuccess.bind(this));
		}
	};

	BX.UI.EntityEditorIncludedArea.prototype.prepareConfigForAction = function()
	{
		var config = {};

		var dataName = this._schemeElement.getDataStringParam("dataName", "");
		if (BX.type.isNotEmptyString(dataName))
		{
			var data = this._model.getField(dataName, {});

			if (BX.type.isPlainObject(data))
			{
				config.data = data;
			}
		}

		var signedParametersName = this._schemeElement.getDataStringParam("signedParametersName", "");
		if (BX.type.isNotEmptyString(signedParametersName))
		{
			var signedParameters = this._model.getField(signedParametersName, "");

			if (BX.type.isNotEmptyString(signedParameters))
			{
				config.signedParameters = signedParameters;
			}
		}

		return config;
	};

	BX.UI.EntityEditorIncludedArea.prototype.onLoadAreaSuccess = function(result)
	{
		this._loadedHtml = BX.prop.getString(BX.prop.getObject(result, "data", {}), "html", null);
		BX.html(this._includedAreaContainer, this._loadedHtml);

		BX.onCustomEvent(window, "BX.UI.EntityEditorIncludedArea:onAfterLoad", [this]);
	};

	BX.UI.EntityEditorIncludedArea.create = function(id, settings)
	{
		var self = new BX.UI.EntityEditorIncludedArea();
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
	BX.UI.EntityEditorText.prototype.focus = function(isSelectedFocus)
	{
		if(!this._input)
		{
			return;
		}

		BX.focus(this._input);
		if (Boolean(isSelectedFocus))
		{
			this._input.select();
		}
		else
		{
			BX.UI.EditorTextHelper.getCurrent().setPositionAtEnd(this._input);
		}
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
			this._innerWrapper = BX.create("div",
			{
				props: { className: "ui-entity-editor-content-block" },
				children: this.getEditModeHtmlNodes()
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
		}

		if(this.isDragEnabled())
		{
			this.initializeDragDropAbilities();
		}

		this.registerLayout(options);
		this._hasLayout = true;
	};
	BX.UI.EntityEditorText.prototype.getEditModeHtmlNodes = function()
	{
		var value = this.getValue();
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
							className: "ui-ctl-element",
							type: "text",
							value: value,
							id: this._id.toLowerCase() + "_text"
						}
				}
			);
		}
		if (!this.isVirtual())
		{
			this._input.name = this.getName();
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
		return [ this._input ];
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

		var isValid = !(this.isRequired() || this.isRequiredByAttribute()) || BX.util.trim(this._input.value) !== "";
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

if(typeof BX.UI.EntityEditorMultiText === "undefined")
{
	BX.UI.EntityEditorMultiText = function()
	{
		BX.UI.EntityEditorMultiText.superclass.constructor.apply(this);
		this._items = null;
		this._input = null;
		this._select = null;
		this._inputValue = [];
		this._addInputHandler = BX.delegate(this.addInputField, this);
		this._innerWrapper = null;
		this._isOpened = false;
	};
	BX.extend(BX.UI.EntityEditorMultiText, BX.UI.EntityEditorField);
	BX.UI.EntityEditorMultiText.prototype.getModeSwitchType = function(mode)
	{
		var result = BX.UI.EntityEditorModeSwitchType.common;
		if(mode === BX.UI.EntityEditorMode.edit)
		{
			result |= BX.UI.EntityEditorModeSwitchType.button|BX.UI.EntityEditorModeSwitchType.content;
		}
		return result;
	};
	BX.UI.EntityEditorMultiText.prototype.getContentWrapper = function()
	{
		return this._innerWrapper;
	};
	BX.UI.EntityEditorMultiText.prototype.hasContentToDisplay = function()
	{
		var values = this.getValue();
		if (!BX.type.isArray(values) || values.length === 0)
		{
			return false;
		}

		var filteredItems = values.filter(function(value){
			return BX.type.isNotEmptyString('' + value)
		});

		return (filteredItems.length > 0);
	};
	BX.UI.EntityEditorMultiText.prototype.getLineCount = function()
	{
		return this._schemeElement.getDataIntegerParam("lineCount", 1);
	};
	BX.UI.EntityEditorMultiText.prototype.createSingleInput = function(value)
	{
		var inputContainer = BX.create("div",
			{
				attrs: { className: "ui-ctl ui-ctl-textbox ui-ctl-w100" }
			}
		);
		if (this.getLineCount() > 1)
		{
			inputContainer.appendChild(
				BX.create("textarea", {
					props:
						{
							className: "ui-ctl-element ui-entity-editor-field-textarea",
							name: this.getName() + '[]',
							rows: this.getLineCount(),
							value: value || ''
						},
					events: {
						input: this._changeHandler
					}
				})
			);
		}
		else
		{
			inputContainer.appendChild(
				BX.create("input", {
					attrs:
						{
							name: this.getName() + '[]',
							className: "ui-ctl-element",
							type: "text",
							value: value || ''
						},
					events: {
						input: this._changeHandler
					}
				})
			);
		}

		return inputContainer;
	};
	BX.UI.EntityEditorMultiText.prototype.getCloneButton = function()
	{
		return 	BX.create('input', {
			attrs:
				{
					type: "button",
					value:  BX.message("UI_ENTITY_EDITOR_ADD"),
				},
			events: {
				click: this._addInputHandler
			}
		});
	};
	BX.UI.EntityEditorMultiText.prototype.addInputField = function (e)
	{
		if (BX.type.isDomNode(this._inputContainer))
		{
			var newInput = this.createSingleInput();
			this._inputContainer.appendChild(newInput);
			newInput.querySelector('.ui-ctl-element').focus();
		}
	};
	BX.UI.EntityEditorMultiText.prototype.onChange = function (e)
	{
		this._inputValue = [];
		for (var i = 0; i < this._inputContainer.children.length; i++)
		{
			var innerInput = this._inputContainer.children[i].querySelector('input');
			if (BX.type.isDomNode(innerInput) && innerInput.value !== '')
			{
				this._inputValue.push(innerInput.value);
			}
		}

		this.markAsChanged();
	};
	BX.UI.EntityEditorMultiText.prototype.layout = function(options)
	{
		if(this._hasLayout)
		{
			return;
		}

		this.ensureWrapperCreated({ classNames: [ "ui-entity-editor-field-multitext" ] });
		this.adjustWrapper();

		if(!this.isNeedToDisplay())
		{
			this.registerLayout(options);
			this._hasLayout = true;
			return;
		}

		var title = this.getTitle();

		var values = this.getValue();
		this._inputValue = values;
		this._innerWrapper = null;
		if(this.isDragEnabled())
		{
			this._wrapper.appendChild(this.createDragButton());
		}

		if(this._mode === BX.UI.EntityEditorMode.edit)
		{
			this._wrapper.appendChild(this.createTitleNode(title));
			this._inputContainer = BX.create("div");

			if (values.length > 0)
			{
				for (var i = 0, l = values.length; i < l; i++)
				{
					this._inputContainer.appendChild(this.createSingleInput(values[i]));
				}
			}
			else
			{
				var newInput = this.createSingleInput();
				this._inputContainer.appendChild(newInput);
				if(this.isNewEntity())
				{
					var placeholder = this.getCreationPlaceholder();
					if(placeholder !== "")
					{
						this._input.setAttribute("placeholder", placeholder);
					}
				}
			}

			this._innerWrapper = BX.create("div",
				{
					props: { className: "ui-entity-editor-content-block" },
					children: [
						this._inputContainer,
						this.getCloneButton()
					]
				});
		}
		else
		{
			this._wrapper.appendChild(this.createTitleNode(title));

			this._innerWrapper = BX.create("div",
				{
					props: { className: "ui-entity-editor-content-block" },
					children: [
						this.getViewInnerLayout()
					]
				}
			);
		}
		this._wrapper.appendChild(this._innerWrapper);

		if (newInput)
		{
			var firstInput = newInput.querySelector('.ui-ctl-element');
			if (firstInput)
			{
				firstInput.focus();
			}
		}

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
	BX.UI.EntityEditorMultiText.prototype.getViewInnerLayout = function()
	{
		var textValue = BX.create("div", {
			props: { className: "ui-entity-editor-content-block-text" }
		});
		if(!this.hasContentToDisplay())
		{
			textValue.innerHTML = BX.message("UI_ENTITY_EDITOR_FIELD_EMPTY");
		}
		else
		{
			var values = this.getValue();
			for (var i=0; i<values.length; i++)
			{
				textValue.appendChild(this.getSingleViewItem(values[i]));
			}
		}
		return textValue;
	};
	BX.UI.EntityEditorMultiText.prototype.getSingleViewItem = function(value)
	{
		return BX.create('p', {text: value});
	};
	BX.UI.EntityEditorMultiText.prototype.doClearLayout = function(options)
	{
		this._select = null;
		this._innerWrapper = null;
	};
	BX.UI.EntityEditorMultiText.prototype.refreshLayout = function()
	{
		if(!this._hasLayout)
		{
			return;
		}

		if(!this._isValidLayout)
		{
			BX.UI.EntityEditorMultiText.superclass.refreshLayout.apply(this, arguments);
			return;
		}

		var values = this.getValue();
		if(this._mode === BX.UI.EntityEditorMode.edit && this._inputContainer)
		{
			for (var i = 0, l = values.length; i < l; i++)
			{
				this._inputContainer.appendChild(this.createSingleInput(values[i]));
			}
		}
		else if(this._mode === BX.UI.EntityEditorMode.view && this._innerWrapper)
		{

			this._innerWrapper.innerHTML = '';
			this._innerWrapper.appendChild(this.getViewInnerLayout());
		}
	};
	BX.UI.EntityEditorMultiText.prototype.validate = function(result)
	{
		if(this._mode !== BX.UI.EntityEditorMode.edit)
		{
			throw "BX.UI.EntityEditorMultiText. Invalid validation context";
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

		var isEmptyValue = true;
		if(this._inputContainer)
		{
			var inputs = this._inputContainer.querySelectorAll('input');
			for (var i=0; i<inputs.length; i++)
			{
				if (BX.util.trim(inputs[i].value) !== '')
				{
					isEmptyValue = false
				}
			}
		}

		var isValid = !this.isRequired() || !isEmptyValue;
		if(!isValid)
		{
			result.addError(BX.UI.EntityValidationError.create({ field: this }));
			this.showRequiredFieldError(this._input);
		}
		return isValid;
	};
	BX.UI.EntityEditorMultiText.prototype.showError =  function(error, anchor)
	{
		BX.UI.EntityEditorMultiText.superclass.showError.apply(this, arguments);
		if(this._inputContainer)
		{
			for (var i=0; this._inputContainer.children.length<i; i++)
			{
				BX.addClass(this._inputContainer.children[i], "ui-ctl-danger");
			}
		}
	};
	BX.UI.EntityEditorMultiText.prototype.clearError =  function()
	{
		BX.UI.EntityEditorMultiText.superclass.clearError.apply(this);
		for (var i=0; this._inputContainer.children.length<i; i++)
		{
			BX.removeClass(this._inputContainer.children[i], "ui-ctl-danger");
		}
	};
	BX.UI.EntityEditorMultiText.prototype.save = function()
	{
		if(!this.isEditable())
		{
			return;
		}

		this._model.setField(this.getName(), this._inputValue);
	};
	BX.UI.EntityEditorMultiText.prototype.processModelChange = function(params)
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
	BX.UI.EntityEditorMultiText.prototype.getRuntimeValue = function()
	{
		return (this._mode === BX.UI.EntityEditorMode.edit && this._input
				? this._inputValue : ""
		);
	};
	BX.UI.EntityEditorMultiText.prototype.getValue = function()
	{
		var value = BX.UI.EntityEditorBoolean.superclass.getValue.apply(this);
		if (!BX.type.isArray(value) && value !== '')
		{
			return [value];
		}

		return value;
	};
	BX.UI.EntityEditorMultiText.create = function(id, settings)
	{
		var self = new BX.UI.EntityEditorMultiText();
		self.initialize(id, settings);
		return self;
	}
}

if(typeof BX.UI.EntityEditorMultiMoney === "undefined")
{
	BX.UI.EntityEditorMultiMoney = function()
	{
		BX.UI.EntityEditorMultiMoney.superclass.constructor.apply(this);
		this._currencyEditor = [];
		this._amountInput = [];
		this._currencyInput = [];
		this._sumElement = [];
		this._select = [];
		this._selectContainer = [];
		this._selectIcon = [];
		this._inputValue = [];
		this._selectedCurrencyValue = [];
		this._addInputHandler = BX.delegate(this.addInputField, this);
		this._inputWrapper = null;
		this._innerWrapper = null;
		this._isCurrencyMenuOpened = false;

		BX.UI.EntityEditorMoney.superclass.constructor.apply(this);
		this.wrapperClassName = "ui-entity-editor-field-money";
	};
	BX.extend(BX.UI.EntityEditorMultiMoney, BX.UI.EntityEditorMultiText);
	BX.UI.EntityEditorMultiMoney.prototype.getModeSwitchType = function(mode)
	{
		var result = BX.UI.EntityEditorModeSwitchType.common;
		if(mode === BX.UI.EntityEditorMode.edit)
		{
			result |= BX.UI.EntityEditorModeSwitchType.button|BX.UI.EntityEditorModeSwitchType.content;
		}
		return result;
	};
	BX.UI.EntityEditorMultiMoney.prototype.getContentWrapper = function()
	{
		return this._innerWrapper;
	};
	BX.UI.EntityEditorMultiMoney.prototype.hasContentToDisplay = function()
	{
		var values = this.getValue();
		if (!BX.type.isArray(values) || values.length === 0)
		{
			return false;
		}

		var filteredItems = values.filter(function(value){
			return BX.type.isNotEmptyString('' + value)
		});

		return (filteredItems.length > 0);
	};
	BX.UI.EntityEditorMultiMoney.prototype.getLineCount = function()
	{
		return this._schemeElement.getDataIntegerParam("lineCount", 1);
	};
	BX.UI.EntityEditorMultiMoney.prototype.createSingleInput = function(index)
	{
		var data = this.getData();
		var amountInputName = BX.prop.getString(data, "amount");
		var currencyInputName = BX.prop.getString(BX.prop.getObject(data, "currency"), "name");
		var currencyValues = this._model.getField(
			BX.prop.getString(BX.prop.getObject(data, "currency"), "name", ""),
			[]
		);

		if(!BX.type.isNotEmptyString(currencyValues[index]))
		{
			currencyValues[index] = BX.Currency.Editor.getBaseCurrencyId();
		}

		var currencyName = this._editor.findOption(
			currencyValues[index],
			BX.prop.getArray(BX.prop.getObject(data, "currency"), "items")
		);

		var amountFieldName = this.getAmountFieldName();
		var currencyFieldName = this.getCurrencyFieldName();
		var amountValues = this._model.getField(amountFieldName, ""); //SET CURRENT SUM VALUE
		var formattedValues = this._model.getField(BX.prop.getString(data, "formatted"), ""); //SET FORMATTED VALUE

		this._selectedCurrencyValue.push(currencyValues[index]);

		this._amountValue.push(BX.create("input",
			{
				attrs:
					{
						name: amountInputName + '[]',
						type: "hidden",
						value: amountValues[index]
					}
			}
		));

		this._amountInput.push(BX.create("input",
			{
				attrs:
					{
						className: "ui-ctl-inline ui-ctl-element ui-ctl-w75",
						type: "text",
						value: formattedValues[index]
					}
			}
		));

		BX.bind(this._amountInput[index], "input", this._changeHandler);

		if(this._model.isFieldLocked(amountFieldName))
		{
			this._amountInput[index].disabled = true;
		}

		this._currencyInput.push(BX.create("input",
			{
				attrs:
					{
						name: currencyInputName + '[]',
						type: "hidden",
						value: currencyValues[index]
					}
			}
		));

		var containerProps = {
			props: { className: "ui-ctl-element" },
			text: currencyName,
		};

		this._select.push(BX.create("div", containerProps));

		this._selectIcon.push(BX.create("div",
			{
				attrs: { className: "ui-ctl-after ui-ctl-icon-angle" }
			}
		));

		this._selectContainer.push(BX.create("div",
			{
				props: {className: "ui-ctl ui-ctl-inline ui-ctl-after-icon ui-ctl-dropdown ui-ctl-w33"},
				children :[
					this._select[index],
					this._selectIcon[index]
				]
			}
		));

		if(this._model.isFieldLocked(currencyFieldName))
		{
			this._selectContainer[index].disabled = true;
		}
		else
		{
			BX.bind(
				this._selectContainer[index],
				"click",
				BX.delegate(function(e){this.onSelectorClick(e, index)}, this)
			);
		}

		var inputWrapper = BX.create("div",
			{
				props: { className: "ui-ctl-inline ui-ctl-w100" },
				children:
					[
						this._amountValue[index],
						this._currencyInput[index],
						this._amountInput[index],
						this._selectContainer[index],
					]
			}
		);

		var inputContainer = BX.create("div",
			{
				props: { className: "ui-entity-editor-content-block" },
				children: [ inputWrapper ]
			}
		);

		this._currencyEditor[index] = new BX.Currency.Editor(
			{
				input: this._amountInput[index],
				currency: currencyValues[index],
				callback: BX.delegate(function(value){
					this.onAmountValueChange(value, index)
				}, this)
			}
		);

		this._currencyEditor[index].changeValue();

		return inputContainer;
	};
	BX.UI.EntityEditorMultiMoney.prototype.getCloneButton = function()
	{
		return 	BX.create('input', {
			attrs:
				{
					type: "button",
					value:  BX.message("UI_ENTITY_EDITOR_ADD"),
				},
			events: {
				click: this._addInputHandler
			}
		});
	};
	BX.UI.EntityEditorMultiMoney.prototype.addInputField = function (e)
	{
		if (BX.type.isDomNode(this._inputContainer))
		{
			var newInput = this.createSingleInput(this._amountInput.length);
			this._inputContainer.appendChild(newInput);
			newInput.querySelector('.ui-ctl-element').focus();
		}
	};
	BX.UI.EntityEditorMultiMoney.prototype.getAmountFieldName = function()
	{
		return this._schemeElement.getDataStringParam("amount", "");
	};
	BX.UI.EntityEditorMultiMoney.prototype.getCurrencyFieldName = function()
	{
		return BX.prop.getString(
			this._schemeElement.getDataObjectParam("currency", {}),
			"name",
			""
		);
	};
	BX.UI.EntityEditorMultiMoney.prototype.layout = function(options)
	{
		if(this._hasLayout)
		{
			return;
		}

		this.ensureWrapperCreated({ classNames: [ "ui-entity-editor-field-multitext" ] });
		this.adjustWrapper();

		if(!this.isNeedToDisplay())
		{
			this.registerLayout(options);
			this._hasLayout = true;
			return;
		}

		var title = this.getTitle();
		var values = this.getValue();

		this._amountValue = [];
		this._amountInput = [];
		this._currencyInput = [];
		this._selectContainer = [];
		this._innerWrapper = null;
		this._sumElement = [];

		if(this.isDragEnabled())
		{
			this._wrapper.appendChild(this.createDragButton());
		}

		if(this._mode === BX.UI.EntityEditorMode.edit)
		{
			this._wrapper.appendChild(this.createTitleNode(title));
			this._inputContainer = BX.create("div");

			if (values.length > 0)
			{
				for (var i = 0, l = values.length; i < l; i++)
				{
					this._inputContainer.appendChild(this.createSingleInput(i));
				}
			}
			else
			{
				var newInput = this.createSingleInput(0);
				this._inputContainer.appendChild(newInput);
				if(this.isNewEntity())
				{
					var placeholder = this.getCreationPlaceholder();
					if(placeholder !== "")
					{
						this._input.setAttribute("placeholder", placeholder);
					}
				}
			}

			this._innerWrapper = BX.create("div",
				{
					props: { className: "ui-entity-editor-content-block" },
					children: [
						this._inputContainer,
						this.getCloneButton()
					]
				});
		}
		else
		{
			this._wrapper.appendChild(this.createTitleNode(title));

			this._innerWrapper = BX.create("div",
				{
					props: { className: "ui-entity-editor-content-block" },
					children: [
						this.getViewInnerLayout()
					]
				}
			);
		}
		this._wrapper.appendChild(this._innerWrapper);

		if (newInput)
		{
			var firstInput = newInput.querySelector('.ui-ctl-element');
			if (firstInput)
			{
				firstInput.focus();
			}
		}

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
	BX.UI.EntityEditorMultiMoney.prototype.onSelectorClick = function (e, index)
	{
		this.openCurrencyMenu(index);
	};
	BX.UI.EntityEditorMultiMoney.prototype.openCurrencyMenu = function(index)
	{
		if(this._isCurrencyMenuOpened)
		{
			return;
		}

		var data = this._schemeElement.getData();
		var currencyList = BX.prop.getArray(BX.prop.getObject(data, "currency"), "items"); //{NAME, VALUE}

		var key = 0;
		var menu = [];
		while (key < currencyList.length)
		{
			menu.push(
				{
					text: BX.util.htmlspecialchars(currencyList[key]["NAME"]),
					value: BX.util.htmlspecialchars(currencyList[key]["VALUE"]),
					onclick: BX.delegate( this.onCurrencySelect, this)
				}
			);
			key++
		}

		BX.PopupMenu.show(
			this._id,
			this._selectContainer[index],
			menu,
			{
				angle: false, width: this._selectContainer[index].offsetWidth + 'px', index: index,
				events:
					{
						onPopupShow: BX.delegate( this.onCurrencyMenuOpen, this),
						onPopupClose: BX.delegate( this.onCurrencyMenuClose, this)
					}
			}
		);
	};
	BX.UI.EntityEditorMultiMoney.prototype.closeCurrencyMenu = function()
	{
		if(!this._isCurrencyMenuOpened)
		{
			return;
		}

		var menu = BX.PopupMenu.getMenuById(this._id);
		if(menu)
		{
			menu.popupWindow.close();
		}
	};
	BX.UI.EntityEditorMultiMoney.prototype.onCurrencyMenuOpen = function()
	{
		BX.addClass(this._selectContainer, "active");
		this._isCurrencyMenuOpened = true;
	};
	BX.UI.EntityEditorMultiMoney.prototype.onCurrencyMenuClose = function()
	{
		BX.PopupMenu.destroy(this._id);

		BX.removeClass(this._selectContainer, "active");
		this._isCurrencyMenuOpened = false;
	};
	BX.UI.EntityEditorMultiMoney.prototype.onCurrencySelect = function(e, item)
	{
		this.closeCurrencyMenu();
		if (
			!(
				item
				&& item['menuWindow']
				&& item['menuWindow']['params']
				&& BX.type.isInteger(item['menuWindow']['params']['index'])
			)
		)
		{
			return;
		}
		var index = item['menuWindow']['params']['index'];

		this._selectedCurrencyValue[index] = this._currencyInput[index].value = item.value;
		this._select[index].innerHTML = BX.util.htmlspecialchars(item.text);
		if(this._currencyEditor[index])
		{
			this._currencyEditor[index].setCurrency(this._selectedCurrencyValue[index]);
		}
		this.markAsChanged(
			{
				fieldName: this.getCurrencyFieldName(),
				fieldValue: this._selectedCurrencyValue[index]
			}
		);
	};
	BX.UI.EntityEditorMultiMoney.prototype.onAmountValueChange = function(v, index)
	{
		if(this._amountValue[index])
		{
			this._amountValue[index].value = v;
		}
	};
	BX.UI.EntityEditorMultiMoney.prototype.getViewInnerLayout = function()
	{
		var textValue = BX.create("div", {
			props: { className: "ui-entity-editor-content-block-text" }
		});
		if(!this.hasContentToDisplay())
		{
			textValue.innerHTML = BX.message("UI_ENTITY_EDITOR_FIELD_EMPTY");
		}
		else
		{
			var values = this.getValue();
			for (var i=0; i<values.length; i++)
			{
				textValue.appendChild(this.getSingleViewItem(i));
			}
		}
		return textValue;
	};
	BX.UI.EntityEditorMultiMoney.prototype.getSingleViewItem = function(index)
	{
		return BX.create(
			'p',
			{
				html: this.renderMoney(index),
				props: {className: 'ui-entity-editor-content-block-wallet'},
			}
		);
	};
	BX.UI.EntityEditorMultiMoney.prototype.validate = function(result)
	{
		if(!(this._mode === BX.UI.EntityEditorMode.edit && this._amountInput && this._amountValue))
		{
			throw "BX.UI.EntityEditorMultiMoney. Invalid validation context";
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

		var isEmptyValue = true;
		if(this._inputContainer)
		{
			var inputs = this._inputContainer.querySelectorAll('input');
			for (var i=0; i<inputs.length; i++)
			{
				if (BX.util.trim(inputs[i].value) !== '')
				{
					isEmptyValue = false
				}
			}
		}

		var isValid = !this.isRequired() || !isEmptyValue;
		if(!isValid)
		{
			result.addError(BX.UI.EntityValidationError.create({ field: this }));
			this.showRequiredFieldError(this._input);
		}
		return isValid;
	};
	BX.UI.EntityEditorMultiMoney.prototype.renderMoney = function(index)
	{
		var data = this._schemeElement.getData();
		var currencyValues = this._model.getField(
			BX.prop.getString(BX.prop.getObject(data, "currency"), "name", ""),
			[]
		);

		if(!BX.type.isNotEmptyString(currencyValues[index]))
		{
			currencyValues[index] = BX.Currency.Editor.getBaseCurrencyId();
		}
		this._selectedCurrencyValue.push(currencyValues[index]);

		var formattedWithCurrencyArray = this._model.getField(BX.prop.getString(data, "formattedWithCurrency"), []);
		var formattedArray = this._model.getField(BX.prop.getString(data, "formatted"), []);
		var formattedWithCurrencyCurrent = formattedWithCurrencyArray[index];
		var formattedCurrent = formattedArray[index];
		var result = BX.Currency.Editor.trimTrailingZeros(formattedCurrent, this._selectedCurrencyValue[index]);

		return formattedWithCurrencyCurrent.replace(
			formattedCurrent,
			result
		);
	};
	BX.UI.EntityEditorMultiMoney.prototype.doClearLayout = function(options)
	{
		BX.PopupMenu.destroy(this._id);

		for (var index = 0; index < this._currencyEditor.length; index++)
		{
			this._currencyEditor[index].clean();
		}

		this._currencyEditor = [];
		this._select = [];
		this._selectIcon = [];
		this._amountValue = [];
		this._amountInput = [];
		this._currencyInput = [];
		this._sumElement = [];
		this._selectContainer = [];
		this._inputWrapper = null;
		this._innerWrapper = null;
	};
	BX.UI.EntityEditorMultiMoney.prototype.refreshLayout = function()
	{
		if(!this._hasLayout)
		{
			return;
		}

		if(!this._isValidLayout)
		{
			BX.UI.EntityEditorMultiMoney.superclass.refreshLayout.apply(this, arguments);
			return;
		}

		var values = this.getValue();
		if(this._mode === BX.UI.EntityEditorMode.edit && this._inputContainer)
		{
			for (var i = 0, l = values.length; i < l; i++)
			{
				this._inputContainer.appendChild(this.createSingleInput(i));
			}
		}
		else if(this._mode === BX.UI.EntityEditorMode.view && this._innerWrapper)
		{

			this._innerWrapper.innerHTML = '';
			this._innerWrapper.appendChild(this.getViewInnerLayout());
		}
	};
	BX.UI.EntityEditorMultiMoney.prototype.save = function()
	{
		if(!this.isEditable())
		{
			return;
		}

		var data = this._schemeElement.getData();
		this._model.setField(
			BX.prop.getString(BX.prop.getObject(data, "currency"), "name"),
			this._selectedCurrencyValue,
			{ originator: this }
		);

		if(this._amountValue)
		{
			var amountValues = [];
			for (var index = 0; index < this._amountValue.length; index++)
			{
				amountValues.push(this._amountValue[index]);
			}

			this._model.setField(
				BX.prop.getString(data, "amount"),
				amountValues,
				{ originator: this }
			);

			this._model.setField(
				BX.prop.getString(data, "formatted"),
				[],
				{ originator: this }
			);
		}
	};
	BX.UI.EntityEditorMultiMoney.prototype.processModelChange = function(params)
	{
		if(BX.prop.get(params, "originator", null) === this)
		{
			return;
		}

		if(!BX.prop.getBoolean(params, "forAll", false)
			&& BX.prop.getString(params, "name", "") !== this.getAmountFieldName()
		)
		{
			return;
		}

		this.refreshLayout();
	};
	BX.UI.EntityEditorMultiMoney.prototype.getRuntimeValue = function()
	{
		var data = [];
		if (this._mode === BX.UI.EntityEditorMode.edit)
		{
			if(this._amountValue)
			{
				var amountValues = [];
				for (var index = 0; index < this._amountValue.length; index++)
				{
					amountValues.push(this._amountValue[index].value);
				}
				data[ BX.prop.getString(data, "amount")] = amountValues;
			}
			data[ BX.prop.getString(data, "currency")] = this._selectedCurrencyValue;

			return data;
		}
		return "";
	};
	BX.UI.EntityEditorMultiMoney.create = function(id, settings)
	{
		var self = new BX.UI.EntityEditorMultiMoney();
		self.initialize(id, settings);
		return self;
	}
}

if(typeof BX.UI.EntityEditorMultiDatetime === "undefined")
{
	BX.UI.EntityEditorMultiDatetime = function()
	{
		BX.UI.EntityEditorMultiDatetime.superclass.constructor.apply(this);
		this._input = null;
		this._inputClickHandler = BX.delegate(this.onInputClick, this);
		this._innerWrapper = null;
	};
	BX.extend(BX.UI.EntityEditorMultiDatetime, BX.UI.EntityEditorMultiText);
	BX.UI.EntityEditorMultiDatetime.prototype.isTimeEnabled = function()
	{
		return BX.prop.get(this._schemeElement.getData(), "enableTime", true);
	};
	BX.UI.EntityEditorMultiDatetime.prototype.onInputClick = function(e)
	{
		this.showCalendar(e.target);
	};
	BX.UI.EntityEditorMultiDatetime.prototype.showCalendar = function(element)
	{
		if (BX.type.isDomNode(element))
		{
			BX.calendar({ node: element, field: element, bTime: this.isTimeEnabled(), bSetFocus: false });
		}
	};
	BX.UI.EntityEditorMultiDatetime.prototype.getDateFormat = function()
	{
		var timeFormat = 'j F Y';
		if (this.isTimeEnabled())
		{
			timeFormat = 'j F Y H:i';
		}
		return BX.prop.getString(this._schemeElement.getData(), "dateViewFormat", timeFormat);
	};
	BX.UI.EntityEditorMultiDatetime.prototype.getSingleViewItem = function(value)
	{
		return BX.create('p', {
			text: BX.date.format(this.getDateFormat(), BX.parseDate(value))
		});
	};
	BX.UI.EntityEditorMultiDatetime.prototype.createSingleInput = function(value)
	{
		return BX.create("div",
			{
				attrs: { className: "ui-ctl ui-ctl-after-icon ui-ctl-datetime field-wrap" },
				children: [
					BX.create("div",
						{
							attrs: { className: "ui-ctl-after ui-ctl-icon-calendar" }
						}
					),
					BX.create("input", {
						attrs:
							{
								name: this.getName() + '[]',
								className: "ui-ctl-element",
								type: "text",
								value: value || ''
							},
						events: {
							input: this._changeHandler,
							change: this._changeHandler,
							click: this._inputClickHandler,
						}
					})
				]
			}
		);
	};
	BX.UI.EntityEditorMultiDatetime.create = function(id, settings)
	{
		var self = new BX.UI.EntityEditorMultiDatetime();
		self.initialize(id, settings);
		return self;
	};
}

if(typeof BX.UI.EntityEditorMultiNumber === "undefined")
{
	BX.UI.EntityEditorMultiNumber = function()
	{
		BX.UI.EntityEditorMultiNumber.superclass.constructor.apply(this);
		this._input = null;
		this._innerWrapper = null;
	};
	BX.extend(BX.UI.EntityEditorMultiNumber, BX.UI.EntityEditorMultiText);
	BX.UI.EntityEditorMultiNumber.prototype.createSingleInput = function(value)
	{
		return BX.create("div",
			{
				attrs: { className: "ui-ctl ui-ctl-number field-wrap" },
				children: [
					BX.create("input", {
						attrs:
							{
								name: this.getName() + '[]',
								className: "ui-ctl-element",
								type: "number",
								value: value || ''
							},
						events: {
							input: this._changeHandler
						}
					})
				]
			}
		);
	};
	BX.UI.EntityEditorMultiNumber.create = function(id, settings)
	{
		var self = new BX.UI.EntityEditorMultiNumber();
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
						type: "number",
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

		var isValid = !(this.isRequired() || this.isRequiredByAttribute()) || BX.util.trim(this._input.value) !== "";
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
			BX.addClass(this._input, "ui-entity-editor-field-error");
		}
	};
	BX.UI.EntityEditorNumber.prototype.clearError =  function()
	{
		BX.UI.EntityEditorNumber.superclass.clearError.apply(this);
		if(this._input)
		{
			BX.removeClass(this._input, "ui-entity-editor-field-error");
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
					props: { className: "ui-ctl ui-ctl-after-icon ui-ctl-datetime ui-ctl-w50" },
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
	BX.UI.EntityEditorDatetime.prototype.isTimeEnabled = function()
	{
		return BX.prop.get(this._schemeElement.getData(), "enableTime", true);
	};
	BX.UI.EntityEditorDatetime.prototype.onInputClick = function(e)
	{
		this.showCalendar();
	};
	BX.UI.EntityEditorDatetime.prototype.showCalendar = function()
	{
		BX.calendar({ node: this._input, field: this._input, bTime: this.isTimeEnabled(), bSetFocus: false });
	};
	BX.UI.EntityEditorDatetime.prototype.getDateFormat = function()
	{
		var timeFormat = 'j F Y';
		if (this.isTimeEnabled())
		{
			timeFormat = 'j F Y H:i';
		}
		return BX.prop.getString(this._schemeElement.getData(), "dateViewFormat", timeFormat);
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

		var isValid = !(this.isRequired() || this.isRequiredByAttribute()) || BX.util.trim(this._input.value) !== "";
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

		if(value !== this.getCheckedValue() && value !== "N")
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
	BX.UI.EntityEditorBoolean.prototype.getCheckedValue = function()
	{
		return this._schemeElement.getDataParam('value', 'Y');
	};
	BX.UI.EntityEditorBoolean.prototype.isChecked = function()
	{
		return (this.getValue() === this.getCheckedValue());
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
							value: this.getCheckedValue(),
							checked: this.isChecked(),
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
									text: BX.message(this.isChecked() ? "UI_ENTITY_EDITOR_YES" : "UI_ENTITY_EDITOR_NO")
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

		var isValid = !(this.isRequired() || this.isRequiredByAttribute()) || BX.util.trim(this._input.value) !== "";
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

		var isValid = !(this.isRequired() || this.isRequiredByAttribute())
			|| (this._input && BX.util.trim(this._input.value) !== "");
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

			var itemParams = {
				value: value,
				onclick: BX.delegate( this.onItemSelect, this)
			};

			if (this.getDataBooleanParam('isHtml', false))
			{
				itemParams['html'] = name;
			}
			else
			{
				itemParams['text'] = name;
			}

			menu.push(itemParams);
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

		var eventArgs =
			{
				field: this,
				item: item,
				cancel: false
			};
		BX.onCustomEvent(window, "BX.UI.EntityEditorList:onItemSelect", [ this, eventArgs ]);
		if(eventArgs["cancel"])
		{
			return;
		}

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
	BX.UI.EntityEditorList.prototype.setItems = function(items)
	{
		if (!BX.type.isArray(items))
		{
			items = BX.prop.getArray(this._schemeElement.getData(), "items", []);
		}

		if (!this.isRequired() && BX.prop.get(this._schemeElement.getData(), "enableEmptyItem", false))
		{
			var empties = items.filter(function(item){
				return (item.VALUE === '')
			});

			if (empties.length === 0)
			{
				items.unshift({
					VALUE: '',
					NAME: BX.message("UI_ENTITY_EDITOR_NOT_SELECTED")
				});
			}
		}

		this._items = items;
	};
	BX.UI.EntityEditorList.prototype.getItems = function()
	{
		if(!this._items)
		{
			this.setItems();
		}
		return this._items;
	};
	BX.UI.EntityEditorList.prototype.getItemByValue = function(value)
	{
		value = value || '';
		value = value.toString();
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
		this._selector = null;
		this._selectedValues = [];
		this._hiddenNode = "";
		this._selectorClickHandler = BX.delegate(this.onSelectorClick, this);
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
	BX.UI.EntityEditorMultiList.prototype.hasContentToDisplay = function()
	{
		var values = this.getValue();
		this.getItems();
		if (!BX.type.isArray(values) || values.length === 0)
		{
			return false;
		}

		var filteredItems = this._items.filter(function(item){
			return BX.util.in_array(item.VALUE, values)
		});

		return (filteredItems.length > 0);
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

		var name = this.getId();
		var title = this.getTitle();

		var value = this.getValue();
		this._selectedValues = this.getSelectedValues(value);

		if(this._mode === BX.UI.EntityEditorMode.edit)
		{
			this._wrapper.appendChild(this.createTitleNode(title));

			var params = {
				'isMulti': true,
				'fieldName': name
			};
			if (!this._selector)
			{
				this._selector = BX.decl({
					block: 'main-ui-multi-select',
					name: name,
					items: this._items,
					value: this._selectedValues,
					params: params,
					valueDelete: true
				});
			}

			var selector = BX.create(
				"div",
				{
					props: { className: "fields enumeration field-item" },
					children: [this._selector]
				}
			);
			BX.addCustomEvent(
				window,
				'UI::Select::change',
				BX.delegate(this.onChange, this)
			);

			BX.bind(selector, 'click', BX.defer(
				function(){
					this.onChange({
						params: params,
						node: selector.firstChild
					});
				}, this));

			this._innerWrapper = BX.create(
				"div",
				{
					props: { className: "ui-entity-editor-content-block" }
				}
			);

			this._innerWrapper.appendChild(selector);
			this.layoutHidden();
			this._innerWrapper.appendChild(this._hiddenNode);
		}
		else// if(this._mode === BX.UI.EntityEditorMode.view)
		{
			this._wrapper.appendChild(this.createTitleNode(title));
			this._innerWrapper = BX.create(
				"div",
				{
					props: { className: "ui-entity-editor-content-block" }
				}
			);

			if(!this.hasContentToDisplay())
			{
				this._innerWrapper.appendChild(BX.create("div",
					{
						text: this.getMessage("isEmpty")
					}
				));
			}
			else
			{
				var selectedNames = [];
				for (var i=0; i<this._selectedValues.length ;i++)
				{
					var item = this.getItemByValue(this._selectedValues[i].VALUE);
					var selectedName;
					if (BX.type.isNotEmptyString(item['HTML']))
					{
						selectedName = item['HTML'];
					}
					else if (BX.type.isNotEmptyString(item['NAME']))
					{
						selectedName = BX.util.htmlspecialchars(item['NAME']);
					}
					else
					{
						selectedName = BX.util.htmlspecialchars(item['VALUE']);
					}
					selectedNames.push(selectedName);
				}

				if (selectedNames.length > 0)
				{
					this._innerWrapper.appendChild(BX.create("div",
						{
							props: {className: "ui-entity-editor-content-block"},
							html: selectedNames.join(', ')
						}
					));
				}
			}
		}
		this._wrapper.appendChild(this._innerWrapper);
		//
		if(this.isDragEnabled())
		{
			this._wrapper.appendChild(this.createDragButton());
		}

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
	BX.UI.EntityEditorMultiList.prototype.doClearLayout = function(options)
	{
		this._selector = null;
		this._innerWrapper = null;
	};
	BX.UI.EntityEditorMultiList.prototype.layoutHidden = function()
	{
		if (!BX.type.isDomNode(this._hiddenNode))
		{
			this._hiddenNode =  BX.create("div");
		}
		else
		{
			this._hiddenNode.innerHTML = '';
		}
		var selectedLength = this._selectedValues.length;
		if (selectedLength > 0)
		{
			for (var i=0;i<selectedLength;i++)
			{
				this._hiddenNode.appendChild(
					BX.create('input',{
						attrs:{
							type: 'hidden',
							name: this.getName() + "[]",
							value: this._selectedValues[i].VALUE
						}
					})
				)
			}
		}
		else
		{
			this._hiddenNode.appendChild(
				BX.create('input',{
					attrs:{
						type: 'hidden',
						name: this.getName() + "[]"
					}
				})
			)
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

		var isValid = !this.isRequired() || this._selectedValues.length > 0;
		if(!isValid)
		{
			result.addError(BX.UI.EntityValidationError.create({ field: this }));
			this.showRequiredFieldError(this._selector);
		}
		return isValid;
	};
	BX.UI.EntityEditorMultiList.prototype.showError =  function(error, anchor)
	{
		BX.UI.EntityEditorMultiList.superclass.showError.apply(this, arguments);
		if(this._selector)
		{
			BX.addClass(this._selector, "ui-entity-editor-field-error");
		}
	};
	BX.UI.EntityEditorMultiList.prototype.clearError =  function()
	{
		BX.UI.EntityEditorMultiList.superclass.clearError.apply(this);
		if(this._selector)
		{
			BX.removeClass(this._selector, "ui-entity-editor-field-error");
		}
	};
	BX.UI.EntityEditorMultiList.prototype.onChange = function(e)
	{
		if (e.params.fieldName === this.getName() && this._selector)
		{
			this._selectedValues = JSON.parse(this._selector.getAttribute('data-value'));
			this.layoutHidden();
			this.markAsChanged();
		}
	};
	BX.UI.EntityEditorMultiList.prototype.getItems = function()
	{
		if(!this._items)
		{
			this._items = BX.prop.getArray(this._schemeElement.getData(), "items", []);
		}
		return this._items;
	};
	BX.UI.EntityEditorMultiList.prototype.getItemByValue = function(value)
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
	BX.UI.EntityEditorMultiList.prototype.getSelectedValues = function(values)
	{
		var result = [];
		var items = this.getItems();
		if (!BX.type.isArray(values))
		{
			return [];
		}

		for(var j = 0; j < values.length; j++)
		{
			value = '' + values[j];
			for(var i = 0, l = items.length; i < l; i++)
			{
				var item = items[i];
				if(value === BX.prop.getString(item, "VALUE", ""))
				{
					result.push(item);
				}
			}
		}

		return result;
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

		this._model.setField(this.getName(), this.getRuntimeValue());
	};
	BX.UI.EntityEditorMultiList.prototype.getRuntimeValue = function()
	{
		var value = [];
		if (this._mode === BX.UI.EntityEditorMode.edit && this._selector)
		{
			for (var i=0;i<this._selectedValues.length;i++)
			{
				value.push(this._selectedValues[i].VALUE);
			}
		}

		return value;
	};
	BX.UI.EntityEditorMultiList.prototype.setItems = function(items)
	{
		if (!BX.type.isArray(items))
		{
			items = BX.prop.getArray(this._schemeElement.getData(), "items", []);
		}

		this._items = items;
	};
	BX.UI.EntityEditorMultiList.create = function(id, settings)
	{
		var self = new BX.UI.EntityEditorMultiList();
		self.initialize(id, settings);
		return self;
	}
}

if(typeof BX.UI.EntityEditorHtml === "undefined")
{
	BX.UI.EntityEditorHtml = function()
	{
		BX.UI.EntityEditorHtml.superclass.constructor.apply(this);
		this._htmlEditorContainer = null;
		this._htmlEditor = null;
		this._isEditorInitialized = false;
		this._focusOnLoad = false;

		this._input = null;
		this._innerWrapper = null;

		this._editorInitializationHandler = BX.delegate(this.onEditorInitialized, this);
		this._viewClickHandler = BX.delegate(this.onViewClick, this);
	};
	BX.extend(BX.UI.EntityEditorHtml, BX.UI.EntityEditorField);
	BX.UI.EntityEditorHtml.prototype.getModeSwitchType = function(mode)
	{
		var result = BX.UI.EntityEditorModeSwitchType.common;
		if(mode === BX.UI.EntityEditorMode.edit)
		{
			result |= BX.UI.EntityEditorModeSwitchType.button|BX.UI.EntityEditorModeSwitchType.content;
		}
		return result;
	};
	BX.UI.EntityEditorHtml.prototype.getContentWrapper = function()
	{
		return this._innerWrapper;
	};
	BX.UI.EntityEditorHtml.prototype.checkIfNotEmpty = function(value)
	{
		return BX.UI.EntityEditorHtml.isNotEmptyValue(value);
	};
	BX.UI.EntityEditorHtml.prototype.focus = function()
	{
		if(this._htmlEditor && this._isEditorInitialized)
		{
			this._htmlEditor.Focus(true);
		}
		else
		{
			this._focusOnLoad = true;
		}
	};
	BX.UI.EntityEditorHtml.prototype.layout = function(options)
	{
		if(this._hasLayout)
		{
			return;
		}

		this.release();
		this.ensureWrapperCreated({ classNames: [ "ui-entity-editor-content-block-field-html" ] });
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
		this._innerWrapper = null;

		if(this.isDragEnabled())
		{
			this._wrapper.appendChild(this.createDragButton());
		}

		if(this._mode === BX.UI.EntityEditorMode.edit)
		{
			if(!this._editor)
			{
				throw "BX.UI.EntityEditorHtml: Editor instance is required for create layout.";
			}

			var htmlEditorConfig = this._editor.getHtmlEditorConfig(name);
			if(!htmlEditorConfig)
			{
				throw "BX.UI.EntityEditorHtml: Could not find HTML editor config.";
			}

			this._htmlEditorContainer = BX(BX.prop.getString(htmlEditorConfig, "containerId"));
			if(!BX.type.isElementNode(this._htmlEditorContainer))
			{
				throw "BX.UI.EntityEditorHtml: Could not find HTML editor container.";
			}

			this._htmlEditor = BXHtmlEditor.Get(BX.prop.getString(htmlEditorConfig, "id"));
			if(!this._htmlEditor)
			{
				throw "BX.UI.EntityEditorHtml: Could not find HTML editor instance.";
			}

			this._wrapper.appendChild(this.createTitleNode(title));
			this._input = BX.create("input", { attrs: { name: name, type: "hidden", value: value } });
			this._wrapper.appendChild(this._input);

			this._innerWrapper = BX.create("div",
				{
					props: { className: "ui-entity-editor-content-block" },
					children:
						[
							BX.create("div",
								{
									props: {className: "ui-entity-editor-content-block-field-container"},
									children: [this._htmlEditorContainer]
								}
							)
						]
				}
			);
		}
		else// if(this._mode === BX.UI.EntityEditorMode.view)
		{
			this._wrapper.appendChild(this.createTitleNode(title));

			this._innerWrapper = BX.create("div",
				{
					props: { className: "ui-entity-editor-content-block" }
				}
			);

			if(this.hasContentToDisplay())
			{
				this._innerWrapper.appendChild(
					BX.create("div",
						{
							props: { className: "ui-entity-editor-content-block-field-container" },
							children:
								[
									BX.create("div",
										{
											props: { className: "ui-entity-editor-content-block-inner-html" },
											html: value
										}
									)
								]
						}
					)
				);

				if (value.length > 200)
				{
					BX.addClass(this._wrapper, "ui-entity-editor-content-block-field-html-collapsed");
					this._innerWrapper.appendChild(
						BX.create("DIV",
							{
								attrs: { className: "ui-entity-editor-content-block-field-html-expand-btn-container" },
								children:
									[
										BX.create("A",
											{
												attrs:
													{
														className: "ui-entity-editor-content-block-field-html-expand-btn",
														href: "#"
													},
												events:
													{
														click: BX.delegate(this.onExpandButtonClick, this)
													},
												text: BX.message("UI_ENTITY_EDITOR_EXPAND_HTML")
											}
										)
									]
							}
						)
					);
					this._isCollapsed = true;
				}
			}
			else
			{
				this._innerWrapper.appendChild(
					document.createTextNode(BX.message("UI_ENTITY_EDITOR_FIELD_EMPTY"))
				);
			}

			this._wrapper.appendChild(this._innerWrapper);

			BX.bindDelegate(
				this._wrapper,
				"mousedown",
				BX.delegate(this.filterViewNode, this),
				this._viewClickHandler
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

		if(this._mode === BX.UI.EntityEditorMode.edit)
		{
			this._isEditorInitialized = !!this._htmlEditor.inited;
			if(this._isEditorInitialized)
			{
				this.prepareEditor();
			}
			else
			{
				BX.addCustomEvent(
					this._htmlEditor,
					"OnCreateIframeAfter",
					this._editorInitializationHandler
				);
				this._htmlEditor.Init();
			}

			window.top.setTimeout(BX.delegate(this.bindChangeEvent, this), 1000);
			this.initializeDragDropAbilities();
		}

		this._hasLayout = true;
	};
	BX.UI.EntityEditorHtml.prototype.doClearLayout = function(options)
	{
		this.release();
		this._input = null;
		this._innerWrapper = null;
	};
	BX.UI.EntityEditorHtml.prototype.onExpandButtonClick = function(e)
	{
		if (!this._wrapper)
		{
			return BX.PreventDefault(e);
		}

		if (this._hasFiles && BX.type.isDomNode(this._commentWrapper) && !this._textLoaded)
		{
			this._textLoaded = true;
			this.loadContent(this._commentWrapper, "GET_TEXT")
		}
		var eventWrapper = this._wrapper.querySelector(".ui-entity-editor-content-block-inner-html");
		if (this._isCollapsed)
		{

			BX.defer(
				function() {
					eventWrapper.style.height = eventWrapper.scrollHeight + 20 + "px";
					eventWrapper.style.maxHeight = eventWrapper.scrollHeight + 130 + "px";
				}
			)();

			setTimeout(
				BX.delegate(function() {
					BX.removeClass(this._wrapper, "ui-entity-editor-content-block-field-html-collapsed");
					BX.addClass(this._wrapper, "ui-entity-editor-content-block-field-html-expand");
					eventWrapper.style.maxHeight = "";
				}, this),
				200
			);
		}
		else
		{
			BX.defer(
				function() {
					eventWrapper.style.maxHeight = eventWrapper.clientHeight + "px";
				}
			)();


			BX.defer(
				function() {
					BX.removeClass(this._wrapper, "ui-entity-editor-content-block-field-html-expand");
					BX.addClass(this._wrapper, "ui-entity-editor-content-block-field-html-collapsed");
				},
				this
			)();

			setTimeout(
				function() {
					eventWrapper.style.maxHeight = "";
				},
				200
			);
		}

		this._isCollapsed = !this._isCollapsed;

		var button = this._wrapper.querySelector("a.ui-entity-editor-content-block-field-html-expand-btn");
		if (button)
		{
			button.innerHTML = BX.message(this._isCollapsed ? "UI_ENTITY_EDITOR_EXPAND_HTML" : "UI_ENTITY_EDITOR_COLLAPSE_HTML");
		}
		return BX.PreventDefault(e);
	};
	BX.UI.EntityEditorHtml.prototype.filterViewNode = function(obj)
	{
		return true;
	};
	BX.UI.EntityEditorHtml.prototype.onViewClick = function(e)
	{
		var link = null;
		var node = BX.getEventTarget(e);
		if(node.tagName === "A")
		{
			link = node;
		}
		else
		{
			link = BX.findParent(node, { tagName: "a" }, this._wrapper);
		}

		if(link && link.target !== "_blank")
		{
			link.target = "_blank";
		}
	};
	BX.UI.EntityEditorHtml.prototype.onEditorInitialized = function()
	{
		this._isEditorInitialized = true;
		BX.removeCustomEvent(
			this._htmlEditor,
			"OnCreateIframeAfter",
			this._editorInitializationHandler
		);
		this.prepareEditor();
	};
	BX.UI.EntityEditorHtml.prototype.prepareEditor = function()
	{
		this._htmlEditorContainer.style.display = "";

		setTimeout(function() {
			this._htmlEditor.CheckAndReInit();
			this._htmlEditor.ResizeSceleton("100%", 200);
			this._htmlEditor.SetContent(this.getStringValue(""), true);

			if(this._focusOnLoad)
			{
				this._htmlEditor.Focus(true);
				this._focusOnLoad = false;
			}
		}.bind(this), 0);
	};
	BX.UI.EntityEditorHtml.prototype.release = function()
	{
		if(this._htmlEditorContainer)
		{
			var stub = BX.create("DIV",
				{
					style:
						{
							height: this._htmlEditorContainer.offsetHeight + "px",
							border: "1px solid #bbc4cd",
							boxSizing: "border-box"
						}
				}
			);
			this._htmlEditorContainer.parentNode.insertBefore(stub, this._htmlEditorContainer);

			document.body.appendChild(this._htmlEditorContainer);
			this._htmlEditorContainer.style.display = "none";
			this._htmlEditorContainer = null;
		}

		if(this._htmlEditor)
		{
			this.unbindChangeEvent();
			this._htmlEditor.SetContent("");
			this._htmlEditor = null;
			this._isEditorInitialized = false;
		}

		this._focusOnLoad = false;
	};
	BX.UI.EntityEditorHtml.prototype.bindChangeEvent = function()
	{
		if(this._htmlEditor)
		{
			BX.addCustomEvent(this._htmlEditor, "OnContentChanged", this._changeHandler);
		}
	};
	BX.UI.EntityEditorHtml.prototype.unbindChangeEvent = function()
	{
		if(this._htmlEditor)
		{
			BX.removeCustomEvent(this._htmlEditor, "OnContentChanged", this._changeHandler);
		}
	};
	BX.UI.EntityEditorHtml.prototype.validate = function(result)
	{
		if(!(this._mode === BX.UI.EntityEditorMode.edit && this._htmlEditor))
		{
			throw "BX.UI.EntityEditorHtml. Invalid validation context";
		}

		this.clearError();

		if(this.hasValidators())
		{
			return this.executeValidators(result);
		}

		var isValid = !(this.isRequired() || this.isRequiredByAttribute())
			|| BX.UI.EntityEditorHtml.isNotEmptyValue(this._htmlEditor.GetContent());
		if(!isValid)
		{
			result.addError(BX.UI.EntityValidationError.create({ field: this }));
			this.showRequiredFieldError(this._htmlEditorContainer);
		}
		return isValid;
	};
	BX.UI.EntityEditorHtml.prototype.showError =  function(error, anchor)
	{
		BX.UI.EntityEditorHtml.superclass.showError.apply(this, arguments);
		if(this._htmlEditorContainer)
		{
			BX.addClass(this._htmlEditorContainer, "ui-entity-editor-content-error");
		}
	};
	BX.UI.EntityEditorHtml.prototype.clearError =  function()
	{
		BX.UI.EntityEditorHtml.superclass.clearError.apply(this);
		if(this._htmlEditorContainer)
		{
			BX.removeClass(this._htmlEditorContainer, "ui-entity-editor-content-error");
		}
	};
	BX.UI.EntityEditorHtml.prototype.save = function()
	{
		if(this._htmlEditor)
		{
			var value = this._input.value = this._htmlEditor.GetContent();
			this._model.setField(this.getName(), value);
		}
	};
	BX.UI.EntityEditorHtml.prototype.getRuntimeValue = function()
	{
		return (this._mode === BX.UI.EntityEditorMode.edit && this._input
				? this._htmlEditor.GetContent() : ""
		);
	};
	BX.UI.EntityEditorHtml.isNotEmptyValue = function(value)
	{
		if (BX.type.isNotEmptyString(value))
		{
			return BX.util.trim(value.replace(/<br\/?>|&nbsp;/ig, "")) !== "";
		}

		return false;
	};
	BX.UI.EntityEditorHtml.create = function(id, settings)
	{
		var self = new BX.UI.EntityEditorHtml();
		self.initialize(id, settings);
		return self;
	};
}

if (typeof BX.UI.EntityEditorFile === "undefined")
{
	BX.UI.EntityEditorFile = function()
	{
		BX.UI.EntityEditorFile.superclass.constructor.apply(this);
		this._innerWrapper = null;

		this._dialogShowHandler = BX.delegate(this.onDialogShow, this);
		this._dialogCloseHandler = BX.delegate(this.onDialogClose, this);
		this._fileChangeHandler = BX.delegate(this.onFileChange, this);
		this._fileAddHandler = BX.delegate(this.onFileAdd, this);
		this._fileDeleteHandler = BX.delegate(this.onFileDelete, this);
	};
	BX.extend(BX.UI.EntityEditorFile, BX.UI.EntityEditorField);
	BX.UI.EntityEditorFile.prototype.getModeSwitchType = function(mode)
	{
		var result = BX.UI.EntityEditorModeSwitchType.common;
		if(mode === BX.UI.EntityEditorMode.edit)
		{
			result |= BX.UI.EntityEditorModeSwitchType.button|BX.UI.EntityEditorModeSwitchType.content;
		}
		return result;
	};
	BX.UI.EntityEditorFile.prototype.getContentWrapper = function()
	{
		return this._innerWrapper;
	};
	BX.UI.EntityEditorFile.prototype.hasContentToDisplay = function()
	{
		return (this._mode === BX.UI.EntityEditorMode.edit || this.getValue().length > 0);
	};
	BX.UI.EntityEditorFile.prototype.loadInput = function()
	{
		this._editor.loadCustomHtml("RENDER_IMAGE_INPUT", { "FIELD_NAME": this.getDataKey(), "ALLOW_UPLOAD": this._mode === BX.UI.EntityEditorMode.edit ? 'Y' : 'N' }, BX.delegate(this.onEditorHtmlLoad, this));
	};
	BX.UI.EntityEditorFile.prototype.onEditorHtmlLoad = function(html)
	{
		if(this._innerWrapper)
		{
			this._innerWrapper.innerHTML = html;

			BX.addCustomEvent(window, "onAfterPopupShow", this._dialogShowHandler);
			BX.addCustomEvent(window, "onPopupClose", this._dialogCloseHandler);

			if (this._mode !== BX.UI.EntityEditorMode.edit)
			{
				this._innerWrapper.querySelectorAll("del").forEach(function(element) {
					element.remove();
				});
			}

			window.setTimeout(BX.delegate(this.bindFileEvents, this), 500)
		}
	};
	BX.UI.EntityEditorFile.prototype.layoutViewMode = function()
	{
		var title = this.getTitle();
		this._wrapper.appendChild(this.createTitleNode(title));
		this._innerWrapper = BX.create("div", { props: { className: "ui-entity-editor-content-block" } });

		if (this.hasContentToDisplay())
		{
			this.loadInput();
		}
		else
		{
			this._innerWrapper.appendChild(document.createTextNode(this.getMessage("isEmpty")));
		}
	};
	BX.UI.EntityEditorFile.prototype.layout = function(options)
	{
		if(this._hasLayout)
		{
			return;
		}

		this.ensureWrapperCreated({ classNames: [ "ui-entity-widget-content-block-field-file" ] });
		this.adjustWrapper();

		if(!this.isNeedToDisplay())
		{
			this.registerLayout(options);
			this._hasLayout = true;
			return;
		}

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

			this.loadInput();
		}
		else// if(this._mode === BX.UI.EntityEditorMode.view)
		{
			this.layoutViewMode();
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
	BX.UI.EntityEditorFile.prototype.doClearLayout = function(options)
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
	BX.UI.EntityEditorFile.prototype.validate = function(result)
	{
		var numberOfFiles = 0;
		var fileControl = BX.MFInput ? BX.MFInput.get(this.getName().toLowerCase() + "_uploader") : null;
		if(fileControl)
		{
			numberOfFiles = fileControl.agent.getItems().length;
		}

		var isValid = !(this.isRequired() || this.isRequiredByAttribute()) || numberOfFiles > 0;
		if(!isValid)
		{
			result.addError(BX.UI.EntityValidationError.create({ field: this }));
			this.showRequiredFieldError(this._input);
		}

		return isValid;
	};
	BX.UI.EntityEditorFile.prototype.bindFileEvents = function()
	{
		var fileControl = BX.MFInput ? BX.MFInput.get(this.getName().toLowerCase() + "_uploader") : null;
		if(fileControl)
		{
			BX.addCustomEvent(fileControl, "onAddFile", this._fileAddHandler);
			BX.addCustomEvent(fileControl, "onDeleteFile", this._fileDeleteHandler);
		}
	};
	BX.UI.EntityEditorFile.prototype.unbindFileEvents = function()
	{
		var fileControl = BX.MFInput ? BX.MFInput.get(this.getName().toLowerCase() + "_uploader") : null;
		if(fileControl)
		{
			BX.removeCustomEvent(fileControl, "onAddFile", this._fileAddHandler);
			BX.removeCustomEvent(fileControl, "onDeleteFile", this._fileDeleteHandler);
		}
	};
	BX.UI.EntityEditorFile.prototype.onDialogShow = function(popup)
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
	BX.UI.EntityEditorFile.prototype.onDialogClose = function(popup)
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
	BX.UI.EntityEditorFile.prototype.onFileChange = function(result)
	{
		this.markAsChanged();
	};
	BX.UI.EntityEditorFile.prototype.onFileAdd = function(result)
	{
		var value = this.getValue();
		value.push(result);
		this._model.setField(this.getName(), value);
		this.markAsChanged();
	};
	BX.UI.EntityEditorFile.prototype.onFileDelete = function(result)
	{
		var value = this.getValue();
		value.splice(value.indexOf(result), 1);
		this._model.setField(this.getName(), value);
		this.markAsChanged();
	};
	BX.UI.EntityEditorFile.create = function(id, settings)
	{
		var self = new BX.UI.EntityEditorFile();
		self.initialize(id, settings);
		return self;
	};
}

if(typeof BX.UI.EntityEditorImage === "undefined")
{
	BX.UI.EntityEditorImage = function()
	{
		BX.UI.EntityEditorImage.superclass.constructor.apply(this);
	};
	BX.extend(BX.UI.EntityEditorImage, BX.UI.EntityEditorFile);
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
	BX.UI.EntityEditorImage.prototype.hasContentToDisplay = function()
	{
		return(this._mode === BX.UI.EntityEditorMode.edit
			|| this._model.getSchemeField(this._schemeElement, "showUrl", "") !== ""
		);
	};
	BX.UI.EntityEditorImage.prototype.layoutViewMode = function()
	{
		var title = this.getTitle();
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
	};
	BX.UI.EntityEditorImage.prototype.loadInput = function()
	{
		console.error('loadInput is not implemented');
		// BX.ajax.runComponentAction(
		// 	"bitrix:ui.form",
		// 	"renderImageInput",
		// 	{ mode: "ajax", data: { moduleId: "ui", name: this.getName(), value: this.getValue() } }
		// ).then(
		// 	function(result)
		// 	{
		// 		var data = BX.prop.getObject(result, "data", {});
		// 		var assets = BX.prop.getObject(data, "assets", {});
		//
		// 		BX.html(null, BX.prop.getString(assets, "css", "")).then(
		// 			function() {
		// 				BX.loadScript(
		// 					BX.prop.getArray(assets, "js", []),
		// 					function() {
		// 						BX.html(null, BX.prop.getArray(assets, "string", []).join("\n")).then(
		// 							function() {
		// 								BX.html(this._innerWrapper, BX.prop.getString(data, "html", "")).then(
		// 									function() {
		// 										BX.addCustomEvent(window, "onAfterPopupShow", this._dialogShowHandler);
		// 										BX.addCustomEvent(window, "onPopupClose", this._dialogCloseHandler);
		//
		// 										window.setTimeout(BX.delegate(this.bindFileEvents, this), 500)
		// 									}.bind(this)
		// 								);
		// 							}.bind(this)
		// 						);
		// 					}.bind(this)
		// 				);
		// 			}.bind(this)
		// 		);
		// 	}.bind(this));this.getName()
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

if(typeof BX.UI.EntityEditorCustom === "undefined")
{
	BX.UI.EntityEditorCustom = function()
	{
		BX.UI.EntityEditorCustom.superclass.constructor.apply(this);
		this._innerWrapper = null;
		this._runtimeValue = null;
	};

	BX.extend(BX.UI.EntityEditorCustom, BX.UI.EntityEditorField);
	BX.UI.EntityEditorCustom.prototype.initialize = function(id, settings)
	{
		BX.UI.EntityEditorCustom.superclass.initialize.call(this, id, settings);
		if (this._schemeElement && this._schemeElement.getDataParam('type') === 'LOCATION' &&  this._model && this._model.getField(id))
		{
			this.setRuntimeValue(this._model.getField(id));
		}
	}
	BX.UI.EntityEditorCustom.prototype.hasContentToDisplay = function()
	{
		return this.getHtmlContent() !== "";
	};
	BX.UI.EntityEditorCustom.prototype.doClearLayout = function(options)
	{
		this.setRuntimeValue(this.getValue());
	};
	BX.UI.EntityEditorCustom.prototype.getModeSwitchType = function(mode)
	{
		var result = BX.UI.EntityEditorModeSwitchType.common;
		if(mode === BX.UI.EntityEditorMode.edit)
		{
			result |= BX.UI.EntityEditorModeSwitchType.button|BX.UI.EntityEditorModeSwitchType.content;
		}
		return result;
	};
	BX.UI.EntityEditorCustom.prototype.areModelValuesEqual = function(previousModel, currentModel)
	{
		var prevValue = previousModel.getSchemeField(
			this._schemeElement,
			'view',
			''
		);
		var curValue = currentModel.getSchemeField(
			this._schemeElement,
			'view',
			''
		);

		return this.areValuesEqual(prevValue, curValue);
	};
	BX.UI.EntityEditorCustom.prototype.layout = function(options)
	{
		if(this._hasLayout)
		{
			return;
		}

		var classNames = this._schemeElement.getDataArrayParam("classNames", []);
		classNames.push("ui-entity-editor-field-text");

		this.ensureWrapperCreated({ classNames: classNames });
		this.adjustWrapper();

		if(!this.isNeedToDisplay())
		{
			this.registerLayout(options);
			this._hasLayout = true;
			return;
		}

		if(this.isDragEnabled())
		{
			this._wrapper.appendChild(this.createDragButton());
		}

		this._wrapper.appendChild(this.createTitleNode(this.getTitle()));
		this._innerWrapper = BX.create("div",
			{
				props: { className: "ui-entity-editor-content-block" }
			}
		);
		this._wrapper.appendChild(this._innerWrapper);
		if (this._mode === BX.UI.EntityEditorMode.edit)
		{
			BX.addClass(this._innerWrapper, "ui-ctl-custom");
		}

		var html = this.getHtmlContent();
		if (BX.type.isNotEmptyString(html))
		{
			setTimeout(
				BX.delegate(function(){
					BX.html(this._innerWrapper, html);
					if (this._mode === BX.UI.EntityEditorMode.edit)
					{
						BX.bindDelegate(
							this._innerWrapper,
							"bxchange",
							{ tag: [ "input", "select", "textarea" ] },
							this._changeHandler
						);
					}

				}, this),
				0
			);
		}
		else if (this._mode !== BX.UI.EntityEditorMode.edit)
		{
			this._innerWrapper.appendChild(BX.create("div",
				{
					props: { className: "ui-entity-editor-content-block-text" },
					text: BX.message("UI_ENTITY_EDITOR_FIELD_EMPTY")
				}));
		}

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
	BX.UI.EntityEditorCustom.prototype.getContentWrapper = function()
	{
		return this._innerWrapper;
	};
	BX.UI.EntityEditorCustom.prototype.processModelChange = function(params)
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
	BX.UI.EntityEditorCustom.prototype.getHtmlContent = function()
	{
		return(
			this._model.getSchemeField(
				this._schemeElement,
				this.isInEditMode() ? "edit" : "view",
				""
			)
		);
	};

	BX.UI.EntityEditorCustom.prototype.setRuntimeValue = function(value)
	{
		this._runtimeValue = value;
	};

	BX.UI.EntityEditorCustom.prototype.getRuntimeValue = function()
	{
		return (this._mode === BX.UI.EntityEditorMode.edit ? this._runtimeValue : "");
	};

	BX.UI.EntityEditorCustom.prototype.validate = function(result)
	{
		if(this._mode !== BX.UI.EntityEditorMode.edit)
		{
			throw "BX.UI.EntityEditorCustom. Invalid validation context";
		}

		this.clearError();

		if(this.hasValidators())
		{
			return this.executeValidators(result);
		}

		return true;
	};

	BX.UI.EntityEditorCustom.create = function(id, settings)
	{
		var self = new BX.UI.EntityEditorCustom();
		self.initialize(id, settings);
		return self;
	};
}

if(typeof BX.UI.EntityEditorMoney === "undefined")
{
	BX.UI.EntityEditorMoney = function()
	{
		BX.UI.EntityEditorMoney.superclass.constructor.apply(this);
		this._currencyEditor = null;
		this._amountInput = null;
		this._currencyInput = null;
		this._sumElement = null;
		this._selectContainer = null;
		this._inputWrapper = null;
		this._innerWrapper = null;
		this._selectedCurrencyValue = "";
		this._selectorClickHandler = BX.delegate(this.onSelectorClick, this);
		this._isCurrencyMenuOpened = false;
		this.wrapperClassName = "ui-entity-editor-field-money";
	};
	BX.extend(BX.UI.EntityEditorMoney, BX.UI.EntityEditorField);
	BX.UI.EntityEditorMoney.prototype.getModeSwitchType = function(mode)
	{
		var result = BX.UI.EntityEditorModeSwitchType.common;
		if(mode === BX.UI.EntityEditorMode.edit)
		{
			result |= BX.UI.EntityEditorModeSwitchType.button|BX.UI.EntityEditorModeSwitchType.content;
		}
		return result;
	};
	BX.UI.EntityEditorMoney.prototype.getContentWrapper = function()
	{
		return this._innerWrapper;
	};
	BX.UI.EntityEditorMoney.prototype.focus = function()
	{
		if(this._amountInput)
		{
			BX.focus(this._amountInput);
			BX.UI.EditorTextHelper.getCurrent().selectAll(this._amountInput);
		}
	};
	BX.UI.EntityEditorMoney.prototype.getValue = function(defaultValue)
	{
		if(!this._model)
		{
			return "";
		}

		return(
			this._model.getStringField(
				this.getAmountFieldName(),
				(defaultValue !== undefined ? defaultValue : "")
			)
		);
	};
	BX.UI.EntityEditorMoney.prototype.layout = function(options)
	{
		if(this._hasLayout)
		{
			return;
		}

		this.ensureWrapperCreated({ classNames: [ this.wrapperClassName ] });
		this.adjustWrapper();

		if(!this.isNeedToDisplay())
		{
			this.registerLayout(options);
			this._hasLayout = true;
			return;
		}

		//var name = this.getName();
		var title = this.getTitle();
		var data = this.getData();

		var amountInputName = BX.prop.getString(data, "amount");
		var currencyInputName = BX.prop.getString(BX.prop.getObject(data, "currency"), "name");

		var currencyValue = this._model.getField(
			BX.prop.getString(BX.prop.getObject(data, "currency"), "name", "")
		);

		if(!BX.type.isNotEmptyString(currencyValue))
		{
			currencyValue = BX.Currency.Editor.getBaseCurrencyId();
		}

		this._selectedCurrencyValue = currencyValue;

		var currencyName = this._editor.findOption(
			currencyValue,
			BX.prop.getArray(BX.prop.getObject(data, "currency"), "items")
		);

		var amountFieldName = this.getAmountFieldName();
		var currencyFieldName = this.getCurrencyFieldName();
		var amountValue = this._model.getField(amountFieldName, ""); //SET CURRENT SUM VALUE
		var formatted = this._model.getField(BX.prop.getString(data, "formatted"), ""); //SET FORMATTED VALUE

		this._amountValue = null;
		this._amountInput = null;
		this._currencyInput = null;
		this._selectContainer = null;
		this._innerWrapper = null;
		this._sumElement = null;

		if(this.isDragEnabled())
		{
			this._wrapper.appendChild(this.createDragButton());
		}

		if(this._mode === BX.UI.EntityEditorMode.edit)
		{
			this._wrapper.appendChild(this.createTitleNode(title));

			this._amountValue = BX.create("input",
				{
					attrs:
						{
							name: amountInputName,
							type: "hidden",
							value: amountValue
						}
				}
			);

			this._amountInput = BX.create("input",
				{
					attrs:
						{
							className: "ui-ctl-inline ui-ctl-element ui-ctl-w75",
							type: "text",
							value: formatted
						}
				}
			);

			BX.bind(this._amountInput, "input", this._changeHandler);

			if(this._model.isFieldLocked(amountFieldName))
			{
				this._amountInput.disabled = true;
			}

			this._currencyInput = BX.create("input",
				{
					attrs:
						{
							name: currencyInputName,
							type: "hidden",
							value: currencyValue
						}
				}
			);

			containerProps = {
				props: { className: "ui-ctl-element" },
				text: currencyName,
			};

			this._select = BX.create("div", containerProps);

			this._selectIcon = BX.create("div",
				{
					attrs: { className: "ui-ctl-after ui-ctl-icon-angle" }
				}
			);

			this._selectContainer = BX.create("div",
				{
					props: {className: "ui-ctl ui-ctl-inline ui-ctl-after-icon ui-ctl-dropdown ui-ctl-w33"},
					children :[
						this._select,
						this._selectIcon
					]
				}
			);

			if(this._model.isFieldLocked(currencyFieldName))
			{
				this._selectContainer.disabled = true;
			}
			else
			{
				BX.bind(this._selectContainer, "click", this._selectorClickHandler);
			}

			this._inputWrapper = BX.create("div",
				{
					props: { className: "ui-ctl-inline ui-ctl-w100" },
					children:
						[
							this._amountValue,
							this._currencyInput,
							this._amountInput,
							this._selectContainer,
						]
				}
			);

			this._innerWrapper = BX.create("div",
				{
					props: { className: "ui-entity-editor-content-block" },
					children: [ this._inputWrapper ]
				}
			);

			this._currencyEditor = new BX.Currency.Editor(
				{
					input: this._amountInput,
					currency: currencyValue,
					callback: BX.delegate(this.onAmountValueChange, this)
				}
			);

			this._currencyEditor.changeValue();
		}
		else //this._mode === BX.UI.EntityEditorMode.view
		{
			this._wrapper.appendChild(this.createTitleNode(title));
			if(this.hasContentToDisplay())
			{
				var className = "ui-entity-editor-content-block-wallet";
				var isLargeFormat = BX.prop.getBoolean(data, "largeFormat", false);
				if (isLargeFormat)
				{
					className += " ui-entity-editor-content-block-wallet-large"
				}
				this._sumElement = BX.create("span",
					{
						props: { className: className }
					}
				);
				this._sumElement.innerHTML = this.renderMoney();
				this._innerWrapper = BX.create("div",
					{
						props: { className: "ui-entity-editor-content-block" },
						children:
							[
								BX.create("div",
									{
										props: { className: "ui-entity-editor-content-block-text" },
										children: [ this._sumElement ]
									}
								)
							]
					}
				);
			}
			else
			{
				this._innerWrapper = BX.create("div",
					{
						props: { className: "ui-entity-editor-content-block" },
						text: this.getMessage("isEmpty")
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
	BX.UI.EntityEditorMoney.prototype.doClearLayout = function(options)
	{
		BX.PopupMenu.destroy(this._id);

		if(this._currencyEditor)
		{
			this._currencyEditor.clean();
			this._currencyEditor = null;
		}

		this._amountValue = null;
		this._amountInput = null;
		this._currencyInput = null;
		this._sumElement = null;
		this._selectContainer = null;
		this._inputWrapper = null;
		this._innerWrapper = null;
	};
	BX.UI.EntityEditorMoney.prototype.refreshLayout = function(options)
	{
		if(!this._hasLayout)
		{
			return;
		}

		if(!this._isValidLayout)
		{
			BX.UI.EntityEditorMoney.superclass.refreshLayout.apply(this, arguments);
			return;
		}

		if(this._mode === BX.UI.EntityEditorMode.edit && this._amountInput)
		{
			var currencyValue = this._currencyEditor
				? this._currencyEditor.currency
				: this._model.getField(this.getCurrencyFieldName());

			if(!BX.type.isNotEmptyString(currencyValue))
			{
				currencyValue = BX.Currency.Editor.getBaseCurrencyId();
			}

			var amountFieldName = this.getAmountFieldName();
			this._amountValue.value = this._model.getField(amountFieldName);
			this._amountInput.value = BX.Currency.Editor.getFormattedValue(
				this._model.getField(amountFieldName, ""),
				currencyValue
			);

			this._amountInput.disabled = this._model.isFieldLocked(amountFieldName);
		}
		else if(this._mode === BX.UI.EntityEditorMode.view && this._sumElement)
		{
			this._sumElement.innerHTML = this.renderMoney();
		}
	};
	BX.UI.EntityEditorMoney.prototype.onAmountValueChange = function(v)
	{
		if(this._amountValue)
		{
			this._amountValue.value = v;
		}
	};
	BX.UI.EntityEditorMoney.prototype.getAmountFieldName = function()
	{
		return this._schemeElement.getDataStringParam("amount", "");
	};
	BX.UI.EntityEditorMoney.prototype.getCurrencyFieldName = function()
	{
		return BX.prop.getString(
			this._schemeElement.getDataObjectParam("currency", {}),
			"name",
			""
		);
	};
	BX.UI.EntityEditorMoney.prototype.onSelectorClick = function (e)
	{
		this.openCurrencyMenu();
	};
	BX.UI.EntityEditorMoney.prototype.openCurrencyMenu = function()
	{
		if(this._isCurrencyMenuOpened)
		{
			return;
		}

		var data = this._schemeElement.getData();
		var currencyList = BX.prop.getArray(BX.prop.getObject(data, "currency"), "items"); //{NAME, VALUE}

		var key = 0;
		var menu = [];
		while (key < currencyList.length)
		{
			menu.push(
				{
					text: BX.util.htmlspecialchars(currencyList[key]["NAME"]),
					value: BX.util.htmlspecialchars(currencyList[key]["VALUE"]),
					onclick: BX.delegate( this.onCurrencySelect, this)
				}
			);
			key++
		}

		BX.PopupMenu.show(
			this._id,
			this._selectContainer,
			menu,
			{
				angle: false, width: this._selectContainer.offsetWidth + 'px',
				events:
					{
						onPopupShow: BX.delegate( this.onCurrencyMenuOpen, this),
						onPopupClose: BX.delegate( this.onCurrencyMenuClose, this)
					}
			}
		);
	};
	BX.UI.EntityEditorMoney.prototype.closeCurrencyMenu = function()
	{
		if(!this._isCurrencyMenuOpened)
		{
			return;
		}

		var menu = BX.PopupMenu.getMenuById(this._id);
		if(menu)
		{
			menu.popupWindow.close();
		}
	};
	BX.UI.EntityEditorMoney.prototype.onCurrencyMenuOpen = function()
	{
		BX.addClass(this._selectContainer, "active");
		this._isCurrencyMenuOpened = true;
	};
	BX.UI.EntityEditorMoney.prototype.onCurrencyMenuClose = function()
	{
		BX.PopupMenu.destroy(this._id);

		BX.removeClass(this._selectContainer, "active");
		this._isCurrencyMenuOpened = false;
	};
	BX.UI.EntityEditorMoney.prototype.onCurrencySelect = function(e, item)
	{
		this.closeCurrencyMenu();

		this._selectedCurrencyValue = this._currencyInput.value = item.value;
		this._select.innerHTML = BX.util.htmlspecialchars(item.text);
		if(this._currencyEditor)
		{
			this._currencyEditor.setCurrency(this._selectedCurrencyValue);
		}
		this.markAsChanged(
			{
				fieldName: this.getCurrencyFieldName(),
				fieldValue: this._selectedCurrencyValue
			}
		);
	};
	BX.UI.EntityEditorMoney.prototype.processModelChange = function(params)
	{
		if(BX.prop.get(params, "originator", null) === this)
		{
			return;
		}

		if(!BX.prop.getBoolean(params, "forAll", false)
			&& BX.prop.getString(params, "name", "") !== this.getAmountFieldName()
		)
		{
			return;
		}

		this.refreshLayout();
	};
	BX.UI.EntityEditorMoney.prototype.processModelLock = function(params)
	{
		var name = BX.prop.getString(params, "name", "");
		if(this.getAmountFieldName() === name)
		{
			this.refreshLayout();
		}
	};
	BX.UI.EntityEditorMoney.prototype.validate = function(result)
	{
		if(!(this._mode === BX.UI.EntityEditorMode.edit && this._amountInput && this._amountValue))
		{
			throw "BX.UI.EntityEditorMoney. Invalid validation context";
		}

		this.clearError();

		if(this.hasValidators())
		{
			return this.executeValidators(result);
		}

		var isValid = !(this.isRequired() || this.isRequiredByAttribute()) || BX.util.trim(this._amountValue.value) !== "";
		if(!isValid)
		{
			result.addError(BX.UI.EntityValidationError.create({ field: this }));
			this.showRequiredFieldError(this._inputWrapper);
		}
		return isValid;
	};
	BX.UI.EntityEditorMoney.prototype.showError =  function(error, anchor)
	{
		BX.UI.EntityEditorMoney.superclass.showError.apply(this, arguments);
		if(this._amountInput)
		{
			BX.addClass(this._amountInput, "ui-entity-editor-field-error");
		}
	};
	BX.UI.EntityEditorMoney.prototype.clearError =  function()
	{
		BX.UI.EntityEditorMoney.superclass.clearError.apply(this);
		if(this._amountInput)
		{
			BX.removeClass(this._amountInput, "ui-entity-editor-field-error");
		}
	};
	BX.UI.EntityEditorMoney.prototype.getRuntimeValue = function()
	{
		var data = [];
		if (this._mode === BX.UI.EntityEditorMode.edit)
		{
			if(this._amountValue)
			{
				data[ BX.prop.getString(data, "amount")] = this._amountValue.value;
			}
			data[ BX.prop.getString(data, "currency")] = this._selectedCurrencyValue;

			return data;
		}
		return "";
	};
	BX.UI.EntityEditorMoney.prototype.save = function()
	{
		var data = this._schemeElement.getData();
		this._model.setField(
			BX.prop.getString(BX.prop.getObject(data, "currency"), "name"),
			this._selectedCurrencyValue,
			{ originator: this }
		);

		if(this._amountValue)
		{
			this._model.setField(
				BX.prop.getString(data, "amount"),
				this._amountValue.value,
				{ originator: this }
			);

			this._model.setField(
				BX.prop.getString(data, "formatted"),
				"",
				{ originator: this }
			);

			this._editor.formatMoney(
				this._amountValue.value,
				this._selectedCurrencyValue,
				BX.delegate(this.onMoneyFormatRequestSuccess, this)
			);
		}
	};
	BX.UI.EntityEditorMoney.prototype.onMoneyFormatRequestSuccess = function(data)
	{
		var schemeData = this._schemeElement.getData();
		var formattedWithCurrency = BX.type.isNotEmptyString(data["FORMATTED_SUM_WITH_CURRENCY"]) ? data["FORMATTED_SUM_WITH_CURRENCY"] : "";
		this._model.setField(BX.prop.getString(schemeData, "formattedWithCurrency"), formattedWithCurrency);

		var formatted = BX.type.isNotEmptyString(data["FORMATTED_SUM"]) ? data["FORMATTED_SUM"] : "";
		this._model.setField(
			BX.prop.getString(schemeData, "formatted"),
			formatted,
			{ originator: this }
		);

		if(this._sumElement)
		{
			while (this._sumElement.firstChild)
			{
				this._sumElement.removeChild(this._sumElement.firstChild);
			}
			this._sumElement.innerHTML = this.renderMoney();
		}
	};
	BX.UI.EntityEditorMoney.prototype.renderMoney = function()
	{
		var data = this._schemeElement.getData();
		var formattedWithCurrency = this._model.getField(BX.prop.getString(data, "formattedWithCurrency"), "");
		var formatted = this._model.getField(BX.prop.getString(data, "formatted"), "");
		var result = BX.Currency.Editor.trimTrailingZeros(formatted, this._selectedCurrencyValue);

		var isLargeFormat = BX.prop.getBoolean(data, "largeFormat", false);
		if (isLargeFormat)
		{
			result = "<span class=\"ui-entity-widget-content-block-columns-right\">" + result + "</span>";
		}

		return formattedWithCurrency.replace(
			formatted,
			result
		);
	};
	BX.UI.EntityEditorMoney.create = function(id, settings)
	{
		var self = new BX.UI.EntityEditorMoney();
		self.initialize(id, settings);
		return self;
	}
}

if(typeof BX.UI.EntityEditorUser === "undefined")
{
	BX.UI.EntityEditorUser = function()
	{
		BX.UI.EntityEditorUser.superclass.constructor.apply(this);
		this._input = null;
		this._editButton = null;
		this._photoElement = null;
		this._nameElement = null;
		this._positionElement = null;
		this._selectedData = {};
		this._editButtonClickHandler = BX.delegate(this.onEditBtnClick, this);
	};
	BX.extend(BX.UI.EntityEditorUser, BX.UI.EntityEditorField);
	BX.UI.EntityEditorUser.prototype.isSingleEditEnabled = function()
	{
		return true;
	};
	BX.UI.EntityEditorUser.prototype.getRelatedDataKeys = function()
	{
		return (
			[
				this.getDataKey(),
				this._schemeElement.getDataStringParam("formated", ""),
				this._schemeElement.getDataStringParam("position", ""),
				this._schemeElement.getDataStringParam("showUrl", ""),
				this._schemeElement.getDataStringParam("photoUrl", "")
			]
		);
	};
	BX.UI.EntityEditorUser.prototype.hasContentToDisplay = function()
	{
		return true;
	};
	BX.UI.EntityEditorUser.prototype.layout = function(options)
	{
		if(this._hasLayout)
		{
			return;
		}

		this.ensureWrapperCreated();
		this.adjustWrapper();

		if(!this.isNeedToDisplay())
		{
			this.registerLayout(options);
			this._hasLayout = true;
			return;
		}

		var name = this._schemeElement.getName();
		var title = this._schemeElement.getTitle();
		var value = this._model.getField(name);
		var formattedName = this._model.getSchemeField(this._schemeElement, "formated", "");
		var position = this._model.getSchemeField(this._schemeElement, "position", "");
		var showUrl = this._model.getSchemeField(this._schemeElement, "showUrl", "", "");
		var photoUrl = this._model.getSchemeField(this._schemeElement, "photoUrl", "");

		this._photoElement = BX.create("a",
			{
				props: { className: "ui-entity-editor-user-avatar-container", target: "_blank" },
				style:
					{
						backgroundImage: BX.type.isNotEmptyString(photoUrl) ? "url('" + photoUrl + "')" : "",
						backgroundSize: BX.type.isNotEmptyString(photoUrl) ? "30px" : ""
					}
			}
		);

		this._nameElement = BX.create("a",
			{
				props: { className: "ui-entity-editor-user-name", target: "_blank" },
				text: formattedName
			}
		);

		if (showUrl !== "")
		{
			this._photoElement.href = showUrl;
			this._nameElement.href = showUrl;
		}

		this._positionElement = BX.create("SPAN",
			{
				props: { className: "ui-entity-editor-user-position" },
				text: position
			}
		);

		if(this.isDragEnabled())
		{
			this._wrapper.appendChild(this.createDragButton());
		}

		this._wrapper.appendChild(this.createTitleNode(title));

		var userElement = BX.create("div", { props: { className: "ui-entity-editor-user-container" } });
		this._editButton = null;
		this._input = null;

		if(this._mode === BX.UI.EntityEditorMode.edit || (this.isEditInViewEnabled() && !this.isReadOnly()))
		{
			this._input = BX.create("input", { attrs: { name: name, type: "hidden", value: value } });
			this._wrapper.appendChild(this._input);

			this._editButton = BX.create("span", { props: { className: "ui-entity-editor-user-change" }, text: BX.message("UI_ENTITY_EDITOR_CHANGE") });
			BX.bind(this._editButton, "click", this._editButtonClickHandler);
			userElement.appendChild(this._editButton);
		}

		userElement.appendChild(this._photoElement);
		userElement.appendChild(
			BX.create("span",
				{
					props: { className: "ui-entity-editor-user-info" },
					children: [ this._nameElement, this._positionElement ]
				}
			)
		);

		this._wrapper.appendChild(
			BX.create("div",
				{ props: { className: "ui-entity-editor-user-content-block-inner" }, children: [ userElement ] }
			)
		);

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
	BX.UI.EntityEditorUser.prototype.doRegisterLayout = function()
	{
		if(this.isInEditMode()
			&& this.checkModeOption(BX.UI.EntityEditorModeOptions.individual)
		)
		{
			window.setTimeout(BX.delegate(this.openSelector, this), 0);
		}
	};
	BX.UI.EntityEditorUser.prototype.doClearLayout = function(options)
	{
		this._input = null;
		this._editButton = null;
		this._photoElement = null;
		this._nameElement = null;
		this._positionElement = null;
	};
	BX.UI.EntityEditorUser.prototype.onEditBtnClick = function(e)
	{
		//If any other control has changed try to switch to edit mode.
		if(this._mode === BX.UI.EntityEditorMode.view && this.isEditInViewEnabled() && this.getEditor().isChanged())
		{
			this.switchToSingleEditMode();
		}
		else
		{
			this.openSelector();
		}
	};
	BX.UI.EntityEditorUser.prototype.openSelector = function()
	{
		BX.onCustomEvent(this._editor, "BX.UI.EntityEditorUser:openSelector", [ this, {
			id: this._id,
			callback: BX.delegate(this.processItemSelect, this),
			anchor: this._editButton,
		}]);
	};
	BX.UI.EntityEditorUser.prototype.processItemSelect = function(selector, item)
	{
		var isViewMode = this._mode === BX.UI.EntityEditorMode.view;
		var editInView = this.isEditInViewEnabled();
		if(isViewMode && !editInView)
		{
			return;
		}

		this._selectedData =
			{
				id: BX.prop.getInteger(item, "entityId", 0),
				photoUrl: BX.prop.getString(item, "avatar", ""),
				formattedNameHtml: BX.prop.getString(item, "name", ""),
				positionHtml: BX.prop.getString(item, "desc", "")
			};

		this._input.value = this._selectedData["id"];
		this._photoElement.style.backgroundImage = this._selectedData["photoUrl"] !== ""
			? "url('" + this._selectedData["photoUrl"] + "')" : "";
		this._photoElement.style.backgroundSize = this._selectedData["photoUrl"] !== ""
			? "30px" : "";

		this._nameElement.innerHTML = this._selectedData["formattedNameHtml"];
		this._positionElement.innerHTML = this._selectedData["positionHtml"];

		BX.onCustomEvent(this._editor, "BX.UI.EntityEditorUser:closeSelector", [ this, {
			id: this._id,
		}]);

		if(!isViewMode)
		{
			this.markAsChanged();
		}
		else
		{
			this._editor.saveControl(this);
		}
	};
	BX.UI.EntityEditorUser.prototype.save = function()
	{
		var data = this._schemeElement.getData();
		if(this._selectedData["id"] > 0)
		{
			var itemId = this._selectedData["id"];

			this._model.setField(
				BX.prop.getString(data, "formated"),
				BX.util.htmlspecialcharsback(this._selectedData["formattedNameHtml"])
			);

			this._model.setField(
				BX.prop.getString(data, "position"),
				this._selectedData["positionHtml"] !== "&nbsp;"
					? BX.util.htmlspecialcharsback(this._selectedData["positionHtml"]) : ""
			);

			this._model.setField(
				BX.prop.getString(data, "showUrl"),
				BX.prop.getString(data, "pathToProfile").replace(/#user_id#/ig, itemId)
			);

			this._model.setField(
				BX.prop.getString(data, "photoUrl"),
				this._selectedData["photoUrl"]
			);

			this._model.setField(this.getName(), itemId);
		}
	};
	BX.UI.EntityEditorUser.prototype.processModelChange = function(params)
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
	BX.UI.EntityEditorUser.prototype.getRuntimeValue = function()
	{
		if (this._mode === BX.UI.EntityEditorMode.edit && this._selectedData["id"] > 0)
		{
			return this._selectedData["id"];
		}
		return "";
	};
	BX.UI.EntityEditorUser.prototype.getMessage = function(name)
	{
		var m = BX.UI.EntityEditorUser.messages;
		return (m.hasOwnProperty(name)
				? m[name]
				: BX.UI.EntityEditorUser.superclass.getMessage.apply(this, arguments)
		);
	};

	if(typeof(BX.UI.EntityEditorUser.messages) === "undefined")
	{
		BX.UI.EntityEditorUser.messages = {};
	}
	BX.UI.EntityEditorUser.create = function(id, settings)
	{
		var self = new BX.UI.EntityEditorUser();
		self.initialize(id, settings);
		return self;
	};
}
