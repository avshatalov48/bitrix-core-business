/**
 * @module ui
 * @version 1.0
 * @copyright 2001-2019 Bitrix
 */

BX.namespace("BX.UI");

//region EDITOR
if(typeof BX.UI.EntityEditor === "undefined")
{
	BX.UI.EntityEditor = function()
	{
		this._id = "";
		this._settings = {};

		this._entityTypeName = '';
		this._entityId = 0;

		this._userFieldManager = null;

		this._container = null;
		this._layoutContainer = null;
		this._buttonContainer = null;
		this._createSectionButton = null;
		this._configMenuButton = null;
		this._configIcon = null;

		this._pageTitle = null;
		this._pageTitleInput = null;
		this._buttonWrapper = null;
		this._editPageTitleButton = null;
		this._copyPageUrlButton = null;

		this._actionTypes = null;
		this._formElement = null;
		this._ajaxForm = null;
		this._ajaxForms = null;
		this._reloadAjaxForm = null;
		this._formSubmitHandler = BX.delegate(this.onFormSubmit, this);

		this._controllers = null;
		this._controls = null;
		this._activeControls = null;
		this._toolPanel = null;

		this._model = null;
		this._scheme = null;
		this._config = null;
		this._context = null;
		this._contextId = "";
		this._externalContextId = "";

		this._mode = BX.UI.EntityEditorMode.intermediate;

		this._isNew = false;
		this._readOnly = false;

		this._enableRequiredUserFieldCheck = true;
		this._enableAjaxForm = true;
		this._enableSectionEdit = false;
		this._enableSectionCreation = false;
		this._enableModeToggle = true;
		this._enableVisibilityPolicy = true;
		this._enablePageTitleControls = true;
		this._enableToolPanel = true;
		this._isToolPanelAlwaysVisible = false;
		this._enableBottomPanel = true;
		this._enableConfigControl = true;
		this._enableFieldsContextMenu = true;

		this._serviceUrl = "";
		this._htmlEditorConfigs = null;

		this._pageTitleExternalClickHandler = BX.delegate(this.onPageTitleExternalClick, this);
		this._pageTitleKeyPressHandler = BX.delegate(this.onPageTitleKeyPress, this);

		this._validators = null;
		this._modeSwitch = null;
		this._delayedSaveHandle = 0;

		this._isEmbedded = false;
		this._isRequestRunning = false;
		this._isConfigMenuShown = false;
		this._isReleased = false;

		this._enableCloseConfirmation = true;
		this._closeConfirmationHandler = BX.delegate(this.onCloseConfirmButtonClick, this);
		this._cancelConfirmationHandler = BX.delegate(this.onCancelConfirmButtonClick, this);

		this._windowResizeHandler = BX.debounce(BX.delegate(this.onResize, this), 50);

		this._sliderOpenHandler = BX.delegate(this.onSliderOpen, this);
		this._sliderCloseHandler = BX.delegate(this.onSliderClose, this);

		this._areAvailableSchemeElementsChanged = false;
		this._availableSchemeElements = null;

		this._dragPlaceHolder = null;
		this._dragContainerController = null;
		this._dropHandler = BX.delegate(this.onDrop, this);
		this._dragConfig = {};

		this.eventsNamespace = 'BX.UI.EntityEditor';
		this.pageTitleInputClassName = "pagetitle-item";
		this._configurationFieldManager = null;
		this._commonConfigEditUrl = BX.prop.getString(
			this._settings,
			"commonConfigEditUrl",
			"/configs/editor/?ENTITY_TYPE_ID=#ENTITY_TYPE_ID_VALUE#&MODULE_ID=#MODULE_ID#"
		);
		this.moduleId = null;
		this._restrictions = {};
	};
	BX.UI.EntityEditor.prototype =
	{
		initialize: function(id, settings)
		{
			this._id = BX.type.isNotEmptyString(id) ? id : BX.util.getRandomString(4);
			this._settings = settings ? settings : {};

			this._model = BX.prop.get(this._settings, "model", null);
			this._scheme = BX.prop.get(this._settings, "scheme", null);
			this._config = BX.prop.get(this._settings, "config", null);

			this._serviceUrl = BX.prop.getString(this._settings, "serviceUrl", "");
			this._entityTypeName = BX.prop.getString(this._settings, "entityTypeName", '');
			this._entityTypeTitle = BX.prop.getString(this._settings, "entityTypeTitle", '');
			this._useFieldsSearch = BX.prop.getBoolean(this._settings, "useFieldsSearch", false);
			this._entityId = BX.prop.getInteger(this._settings, "entityId", 0);
			this.moduleId = BX.prop.getString(this._settings, "moduleId", '');
			this._isNew = this._entityId <= 0 && this._model.isIdentifiable();

			this._isEmbedded = BX.prop.getBoolean(this._settings, "isEmbedded", false);
			this._creationFieldPageUrl = BX.prop.getBoolean(this._settings, "creationFieldPageUrl", false);

			var container = BX.prop.get(this._settings, "container");
			if(!BX.type.isElementNode(container))
			{
				container = BX(BX.prop.get(this._settings, "containerId"));
			}
			this._container = container;

			this._parentContainer = BX.findParent(this._container, { className: 'ui-entity-section' }, false);
			this._buttonContainer = BX(BX.prop.get(this._settings, "buttonContainerId"));
			this._configIcon = BX(BX.prop.get(this._settings, "configIconId"));

			this._enableVisibilityPolicy = BX.prop.getBoolean(this._settings, "enableVisibilityPolicy", true);
			this._enablePageTitleControls = BX.prop.getBoolean(this._settings, "enablePageTitleControls", true);
			if(this._enablePageTitleControls)
			{
				this._pageTitle = BX("pagetitle");
				this._buttonWrapper = BX("pagetitle_btn_wrapper");
				this._editPageTitleButton = BX("pagetitle_edit");
				this._copyPageUrlButton = BX("page_url_copy_btn");
			}

			this.adjustSize();
			this.adjustTitle();

			//region Form
			var formTagName = BX.prop.getString(this._settings, "formTagName", "form");
			this._formElement = BX.create(formTagName, {props: { name: this._id + "_form"}});
			this._container.appendChild(this._formElement);

			this._layoutContainer = BX.create("div", {props: { className: "ui-entity-editor-column-wrapper"}});
			this._formElement.appendChild(this._layoutContainer);

			this._enableRequiredUserFieldCheck = BX.prop.getBoolean(this._settings, "enableRequiredUserFieldCheck", true);

			this._enableAjaxForm = BX.prop.getBoolean(this._settings, "enableAjaxForm", true);
			if(this._enableAjaxForm)
			{
				this.initializeAjaxForm();
			}
			//endregion

			this._restrictions = BX.prop.getObject(this._settings, "restrictions", {});

			this.initializeManagers();

			this._context = BX.prop.getObject(this._settings, "context", {});
			this._contextId = BX.prop.getString(this._settings, "contextId", "");
			this._externalContextId = BX.prop.getString(this._settings, "externalContextId", "");

			this._readOnly = BX.prop.getBoolean(this._settings, "readOnly", false);
			if(this._readOnly)
			{
				this._enableSectionEdit = this._enableSectionCreation = false;
			}
			else
			{
				this._enableSectionEdit = BX.prop.getBoolean(this._settings, "enableSectionEdit", false);
				this._enableSectionCreation = BX.prop.getBoolean(this._settings, "enableSectionCreation", false);
			}

			this._controllers = [];
			this._controls = [];
			this._activeControls = [];
			this._modeSwitch = BX.UI.EntityEditorModeSwitch.create(this._id, { editor: this });

			this._htmlEditorConfigs = BX.prop.getObject(this._settings, "htmlEditorConfigs", {});

			var initialMode = BX.UI.EntityEditorMode.view;
			if(!this._readOnly)
			{
				initialMode = BX.UI.EntityEditorMode.parse(BX.prop.getString(this._settings, "initialMode", ""));
			}
			this._mode = initialMode !== BX.UI.EntityEditorMode.intermediate ? initialMode : BX.UI.EntityEditorMode.view;

			this._enableModeToggle = false;
			if(!this._readOnly)
			{
				this._enableModeToggle = BX.prop.getBoolean(this._settings, "enableModeToggle", true);
			}

			if(this._isNew && !this._readOnly)
			{
				this._mode = BX.UI.EntityEditorMode.edit;
			}

			this._availableSchemeElements = this._scheme.getAvailableElements();

			this.initializeControllers();
			this.initializeControls();

			if(this._mode === BX.UI.EntityEditorMode.edit && this._controls.length > 0)
			{
				this.initializeControlsEditMode();
			}

			this.initializeValidators();

			this._enableToolPanel = BX.prop.getBoolean(this._settings, "enableToolPanel", true);
			this._isToolPanelAlwaysVisible = BX.prop.getBoolean(this._settings, "isToolPanelAlwaysVisible", false);
			if(this._enableToolPanel)
			{
				this.initializeToolPanel();

				if (this.isToolPanelAlwaysVisible())
				{
					this.showToolPanel();
				}
			}

			this._enableBottomPanel = BX.prop.getBoolean(this._settings, "enableBottomPanel", true);
			this._enableConfigControl = BX.prop.getBoolean(this._settings, "enableConfigControl", true);

			this._enableFieldsContextMenu = BX.prop.getBoolean(this._settings, "enableFieldsContextMenu", true);

			this.initializeDragDrop();

			this.layout();
			this.attachToEvents();

			var eventArgs =
				{
					id: this._id,
					externalContext: this._externalContextId,
					context: this._contextId,
					entityTypeName: this._entityTypeName,
					entityId: this._entityId,
					model: this._model
				};
			BX.onCustomEvent(window, this.eventsNamespace + ":onInit", [ this, eventArgs ]);
		},
		initializeControllers: function()
		{
			var i, length;
			var controllerData = BX.prop.getArray(this._settings, "controllers", []);
			for(i = 0, length = controllerData.length; i < length; i++)
			{
				var controller = this.createController(controllerData[i]);
				if(controller)
				{
					this._controllers.push(controller);
				}
			}
		},
		initializeControls: function()
		{
			var elements = this._scheme.getElements();
			var i, length, element, control;
			for(i = 0, length = elements.length; i < length; i++)
			{
				element = elements[i];
				control = this.createControl(
					element.getType(),
					element.getName(),
					{ schemeElement: element, mode: BX.UI.EntityEditorMode.view }
				);

				if(!control)
				{
					continue;
				}

				this._controls.push(control);
			}
		},
		initializeControlsEditMode: function()
		{
			var i, length;
			for(i = 0, length = this._controls.length; i < length; i++)
			{
				this._controls[i].setMode(BX.UI.EntityEditorMode.edit, { notify: false });
			}
		},
		initializeValidators: function()
		{
			//region Validators
			var i, length;
			this._validators = [];
			var validatorConfigs = BX.prop.getArray(this._settings, "validators", []);
			for(i = 0, length = validatorConfigs.length; i < length; i++)
			{
				var validator = this.createValidator(validatorConfigs[i]);
				if(validator)
				{
					this._validators.push(validator);
				}
			}
			//endregion
		},
		initializeToolPanel: function()
		{
			var buttonsOrder = BX.prop.getObject(this._settings, 'toolPanelButtonsOrder', {});
			var customButtons = BX.prop.getArray(this._settings, 'customToolPanelButtons', []);

			this._toolPanel = BX.UI.EntityEditorToolPanel.create(
				this._id,
				{
					container: this._isEmbedded ? this._formElement : document.body,
					editor: this,
					visible: false,
					buttonsOrder: buttonsOrder,
					customButtons: customButtons,
				}
			);
		},
		initializeDragDrop: function()
		{
			this._dragConfig = {};

			var sectionDragModes = {};
			sectionDragModes[BX.UI.EntityEditorMode.names.view]
				= sectionDragModes[BX.UI.EntityEditorMode.names.edit]
				= BX.prop.getBoolean(this._settings, "enableSectionDragDrop", true);

			this._dragConfig[BX.UI.EditorDragObjectType.section] =
				{
					scope: BX.UI.EditorDragScope.form,
					modes: sectionDragModes
				};

			var fieldDragModes = {};
			fieldDragModes[BX.UI.EntityEditorMode.names.view]
				= fieldDragModes[BX.UI.EntityEditorMode.names.edit]
				= BX.prop.getBoolean(this._settings, "enableFieldDragDrop", true);

			this._dragConfig[BX.UI.EditorDragObjectType.field] =
				{
					scope: BX.UI.EditorDragScope.form,
					modes: fieldDragModes
				};

			// if(this.canChangeScheme())
			// {
			// 	this._dragContainerController = BX.UI.EditorDragContainerController.create(
			// 		"editor_" + this.getId(),
			// 		{
			// 			charge: BX.UI.EditorSectionDragContainer.create({ editor: this }),
			// 			node: this._formElement
			// 		}
			// 	);
			// 	this._dragContainerController.addDragFinishListener(this._dropHandler);
			// }
		},
		initializeManagers: function()
		{
			this._userFieldManager = BX.prop.get(this._settings, "userFieldManager", null);
			this._configurationFieldManager = BX.UI.EntityConfigurationManager.create(
				this._id,
				{ editor: this }
			);
			var eventArgs = {
				id: this._id,
				editor: this,
				type: 'editor',
				configurationFieldManager: this._configurationFieldManager,
			};
			BX.onCustomEvent(window, "BX.UI.EntityConfigurationManager:onInitialize", [ this, eventArgs ]);
			this._configurationFieldManager = eventArgs.configurationFieldManager;
		},
		initializeCustomEditors: function()
		{
		},
		attachToEvents: function()
		{
			BX.bind(window, "resize", this._windowResizeHandler);

			BX.addCustomEvent("SidePanel.Slider:onOpenComplete", this._sliderOpenHandler);
			BX.addCustomEvent("SidePanel.Slider:onClose", this._sliderCloseHandler);
		},
		deattachFromEvents: function()
		{
			BX.unbind(window, "resize", this._windowResizeHandler);

			BX.removeCustomEvent("SidePanel.Slider:onOpenComplete", this._sliderOpenHandler);
			BX.removeCustomEvent("SidePanel.Slider:onClose", this._sliderCloseHandler);
		},
		release: function()
		{
			for(var i = 0, length = this._controls.length; i < length; i++)
			{
				this._controls[i].clearLayout();
			}

			this.deattachFromEvents();

			this.releaseAjaxForm();
			this.releaseReloadAjaxForm();
			this._container = BX.remove(this._container);

			this._isReleased = true;
		},
		onSliderOpen: function(event)
		{
			//Reset close confirmation flag
			this._enableCloseConfirmation = true;
			var eventArgs =
				{
					id: this._id,
					externalContext: this._externalContextId,
					context: this._contextId,
					entityTypeId: this._entityTypeId,
					entityId: this._entityId,
					model: this._model
				};
			BX.onCustomEvent(window, this.eventsNamespace + ":onOpen", [ this, eventArgs ]);
		},
		onSliderClose: function(event)
		{
			if(!this._enableCloseConfirmation)
			{
				return;
			}

			var slider = top.BX.SidePanel.Instance.getSliderByWindow(window);
			if(slider !== event.getSlider())
			{
				return;
			}

			if(!slider.isOpen())
			{
				return;
			}

			if(!this.hasChangedControls() && !this.hasChangedControllers())
			{
				return;
			}

			event.denyAction();

			if(BX.UI.EditorAuxiliaryDialog.isItemOpened("close_confirmation"))
			{
				return;
			}

			BX.UI.EditorAuxiliaryDialog.create(
				"close_confirmation",
				{
					title: BX.message("UI_ENTITY_EDITOR_CONFIRMATION"),
					content: BX.message("UI_ENTITY_EDITOR_CLOSE_CONFIRMATION"),
					zIndex: 100,
					buttons:
						[
							{
								id: "close",
								type: BX.UI.DialogButtonType.accept,
								text: BX.message("JS_CORE_WINDOW_CLOSE"),
								callback: this._closeConfirmationHandler
							},
							{
								id: "cancel",
								type: BX.UI.DialogButtonType.cancel,
								text: BX.message("JS_CORE_WINDOW_CANCEL"),
								callback: this._closeConfirmationHandler
							}
						]
				}
			).open();
		},
		onCloseConfirmButtonClick: function(button)
		{
			button.getDialog().close();
			if(button.getId() === "close")
			{
				this._enableCloseConfirmation = false;
				top.BX.SidePanel.Instance.getSliderByWindow(window).close();
			}
		},
		initializeAjaxForm: function()
		{
			if(this._ajaxForm)
			{
				return;
			}

			var ajaxData = BX.prop.getObject(this._settings, "ajaxData", {});
			var actionName = BX.prop.getString(ajaxData, "ACTION_NAME", "");
			var componentName = BX.prop.getString(ajaxData, "COMPONENT_NAME", "");
			var signedParameters = BX.prop.getString(ajaxData, "SIGNED_PARAMETERS", "");

			this._ajaxForms = {};
			this._actionTypes = {};

			this._actionTypes[BX.UI.EntityEditorActionIds.defaultActionId] = BX.UI.EntityEditorActionTypes.save;
			var defaultAjaxForm = this.createAjaxForm(
				{
					componentName: componentName,
					actionName: actionName,
					elementNode: this._formElement,
					signedParameters: signedParameters,
					enableRequiredUserFieldCheck: this._enableRequiredUserFieldCheck
				},
				{
					onSuccess: this.onSaveSuccess.bind(this),
					onFailure: this.onSaveFailure.bind(this)
				}
			);
			this._ajaxForms[BX.UI.EntityEditorActionIds.defaultActionId] = defaultAjaxForm;

			BX.addCustomEvent(defaultAjaxForm, "onAfterSubmit", this._formSubmitHandler);

			if (ajaxData.ADDITIONAL_ACTIONS && ajaxData.ADDITIONAL_ACTIONS.length > 0)
			{
				var additionalActions = ajaxData.ADDITIONAL_ACTIONS;
				for (var i=0; i < additionalActions.length; i++) {
					var action = additionalActions[i];

					this._actionTypes[action.ID] = action.ACTION_TYPE;

					var ajaxForm = this.createAjaxForm(
						{
							componentName: componentName,
							actionName: action.ACTION,
							elementNode: this._formElement,
							signedParameters: signedParameters,
							enableRequiredUserFieldCheck: this._enableRequiredUserFieldCheck
						},
						{
							onSuccess: this.onSaveSuccess.bind(this),
							onFailure: this.onSaveFailure.bind(this)
						}
					);
					BX.addCustomEvent(ajaxForm, "onAfterSubmit", this._formSubmitHandler);
					this._ajaxForms[action.ID] = ajaxForm;
				}
			}

			// compatibility
			this._ajaxForm = this._ajaxForms[BX.UI.EntityEditorActionIds.defaultActionId];

			//Disable submit action by pressing Enter key (if there is only one input on the form)
			this._formElement.setAttribute("onsubmit", "return false;");
		},
		createAjaxForm: function(options, callbacks)
		{
			var componentName = BX.prop.getString(options, "componentName", "");
			var actionName = BX.prop.getString(options, "actionName", "");
			var elementNode = BX.prop.getElementNode(options, "elementNode", null);
			var formData = BX.prop.getObject(options, "formData", null);

			if(componentName !== "")
			{
				if(actionName === "")
				{
					actionName = "save";
				}

				return BX.UI.ComponentAjax.create(
					this._id,
					{
						elementNode: elementNode,
						formData: formData,
						className: componentName,
						signedParameters: BX.prop.getString(options, "signedParameters", null),
						actionName: actionName,
						callbacks:
							{
								onSuccess: (callbacks ? callbacks.onSuccess : null),
								onFailure: (callbacks ? callbacks.onFailure : null)
							}
					}
				);
			}
			else
			{
				if(actionName === "")
				{
					actionName = "SAVE";
				}

				return BX.UI.AjaxForm.create(
					this._id,
					{
						elementNode: elementNode,
						formData: formData,
						config:
							{
								url: this._serviceUrl,
								method: "POST",
								dataType: "json",
								processData : true,
								onsuccess: (callbacks ? callbacks.onSuccess : null),
								data:
									{
										"ACTION": actionName,
										"ACTION_ENTITY_TYPE": this._entityTypeName,
										"ENABLE_REQUIRED_USER_FIELD_CHECK": BX.prop.getBoolean(options, "enableRequiredUserFieldCheck", false) ? 'Y' : 'N'
									}
							}
					}
				);
			}
		},
		releaseAjaxForm: function()
		{
			if(!this._ajaxForm)
			{
				return;
			}

			var _this = this;

			if (BX.Type.isObject(this._ajaxForms))
			{
				Object.keys(this._ajaxForms).forEach(function (ajaxForm) {
					BX.removeCustomEvent(_this._ajaxForms[ajaxForm], "onAfterSubmit", _this._formSubmitHandler);
					_this._ajaxForms[ajaxForm] = null;
				});
			}

			BX.removeCustomEvent(this._ajaxForm, "onAfterSubmit", this._formSubmitHandler);
			this._ajaxForm = null;
		},
		releaseReloadAjaxForm: function()
		{
			if(!this._reloadAjaxForm)
			{
				return;
			}

			this._reloadAjaxForm = null;
		},
		getId: function()
		{
			return this._id;
		},
		getEntityTypeName: function()
		{
			return this._entityTypeName;
		},
		getEntityId: function()
		{
			return this._entityId;
		},
		getOwnerInfo: function()
		{
			return this._model.getOwnerInfo();
		},
		getMode: function()
		{
			return this._mode;
		},
		getContextId: function()
		{
			return this._contextId;
		},
		getContext: function()
		{
			return this._context;
		},
		getExternalContextId: function()
		{
			return this._externalContextId;
		},
		getScheme: function()
		{
			return this._scheme;
		},
		isVisible: function()
		{
			return this._container.offsetParent !== null;
		},
		isVisibilityPolicyEnabled: function()
		{
			return this._enableVisibilityPolicy;
		},
		isToolPanelAlwaysVisible: function()
		{
			return this._isToolPanelAlwaysVisible;
		},
		isBottomPanelEnabled: function()
		{
			return this._enableBottomPanel;
		},
		isConfigControlEnabled: function()
		{
			return this._enableConfigControl;
		},
		isSectionEditEnabled: function()
		{
			return this._enableSectionEdit;
		},
		isSectionCreationEnabled: function()
		{
			return this._enableSectionCreation && this.canChangeScheme();
		},
		isFieldsContextMenuEnabled: function()
		{
			return this._enableFieldsContextMenu;
		},
		isModeToggleEnabled: function()
		{
			return this._enableModeToggle;
		},
		isNew: function()
		{
			return this._isNew;
		},
		isReadOnly: function()
		{
			return this._readOnly;
		},
		isEmbedded: function()
		{
			return this._isEmbedded;
		},
		isEditInViewEnabled: function()
		{
			return this._entityId > 0;
		},
		getDetailManager: function()
		{
			if(typeof(BX.UI.EntityDetailManager) === "undefined")
			{
				return null;
			}

			return BX.UI.EntityDetailManager.get(BX.prop.getString(this._settings, "detailManagerId", ""));
		},
		getConfigurationFieldManager: function()
		{
			return this._configurationFieldManager;
		},
		getUserFieldManager: function()
		{
			return this._userFieldManager;
		},
		getAttributeManager: function()
		{
			return null;
		},
		getHtmlEditorConfig: function(fieldName)
		{
			return BX.prop.getObject(this._htmlEditorConfigs, fieldName, null);
		},
		//region Validators
		createValidator: function(settings)
		{
			settings["editor"] = this;
			return BX.UI.EntityEditorValidatorFactory.create(
				BX.prop.getString(settings, "type", ""),
				settings
			);
		},
		//endregion
		//region Controls & Events
		getControlByIndex: function(index)
		{
			return (index >= 0 && index < this._controls.length) ? this._controls[index] : null;
		},
		getControlIndex: function(control)
		{
			for(var i = 0, length = this._controls.length; i < length; i++)
			{
				if(this._controls[i] === control)
				{
					return i;
				}
			}
			return -1;
		},
		getControls: function()
		{
			return this._controls;
		},
		getControlCount: function()
		{
			return this._controls.length;
		},
		createControl: function(type, controlId, settings)
		{
			settings["serviceUrl"] = this._serviceUrl;
			settings["container"] = this._layoutContainer;
			settings["model"] = this._model;
			settings["editor"] = this;

			return BX.UI.EntityEditorControlFactory.create(type, controlId, settings);
		},
		addControlAt: function(control, index)
		{
			var options = {};
			if(index < this._controls.length)
			{
				options["anchor"] = this._controls[index].getWrapper();
				this._controls.splice(index, 0, control);
			}
			else
			{
				this._controls.push(control);
			}
			control.layout(options);
		},
		moveControl: function(control, index)
		{
			var qty = this._controls.length;
			var lastIndex = qty - 1;
			if(index < 0  || index > qty)
			{
				index = lastIndex;
			}

			var currentIndex = this.getControlIndex(control);
			if(currentIndex < 0 || currentIndex === index)
			{
				return false;
			}

			control.clearLayout();
			this._controls.splice(currentIndex, 1);
			qty--;

			var anchor = index < qty
				? this._controls[index].getWrapper()
				: null;

			if(index < qty)
			{
				this._controls.splice(index, 0, control);
			}
			else
			{
				this._controls.push(control);
			}

			if(anchor)
			{
				control.layout({ anchor: anchor });
			}
			else
			{
				control.layout();
			}

			this._config.moveSchemeElement(control.getSchemeElement(), index);
		},
		removeControl: function(control)
		{
			var index = this.getControlIndex(control);
			if(index < 0)
			{
				return false;
			}

			this.processControlRemove(control);
			control.clearLayout();
			this._controls.splice(index, 1);
		},
		getControlById: function(id)
		{
			for(var i = 0, length = this._controls.length; i < length; i++)
			{
				var control = this._controls[i];
				if(control.getId() === id)
				{
					return control;
				}

				var child = control.getChildById(id);
				if(child)
				{
					return child;
				}
			}
			return null;
		},
		getControlByIdRecursive: function(name, controls)
		{
			var res;

			if(!controls)
			{
				controls = this.getControls();
			}

			for (var i=0; i < controls.length; i++)
			{
				if (!controls[i] instanceof BX.UI.EntityEditorControl)
				{
					continue;
				}

				if(controls[i].getId() === name)
				{
					return controls[i];
				}
				else if (
					controls[i] instanceof BX.UI.EntityEditorColumn
					|| controls[i] instanceof BX.UI.EntityEditorSection
				)
				{
					if(res = this.getControlByIdRecursive(name, controls[i].getChildren()))
					{
						return res;
					}
				}
			}

			return null;
		},
		getAllControls: function(controls)
		{
			var result = [], res;

			if(!controls)
			{
				controls = this.getControls();
			}

			for (var i=0; i < controls.length; i++)
			{
				if (controls[i] instanceof BX.UI.EntityEditorControl)
				{
					if (
						controls[i] instanceof BX.UI.EntityEditorColumn
						|| controls[i] instanceof BX.UI.EntityEditorSection
					)
					{
						if(res = this.getAllControls(controls[i].getChildren()))
						{
							result = result.concat(res);
						}
					}
					else
					{
						result.push(controls[i]);
					}
				}
			}

			return result;
		},
		getActiveControlCount: function()
		{
			return this._activeControls.length;
		},
		getActiveControlIndex: function(control)
		{
			var length = this._activeControls.length;
			if(length === 0)
			{
				return -1;
			}

			for(var i = 0; i < length; i++)
			{
				if(this._activeControls[i] === control)
				{
					return i;
				}
			}
			return -1;
		},
		getActiveControlById: function(id, recursive)
		{
			recursive = !!recursive;
			var length = this._activeControls.length;
			if(length === 0)
			{
				return null;
			}

			for(var i = 0; i < length; i++)
			{
				var control = this._activeControls[i];
				if(control.getId() === id)
				{
					return control;
				}

				if(recursive)
				{
					var child = control.getChildById(id);
					if(child)
					{
						return child;
					}
				}
			}
			return null;
		},
		getActiveControlByIndex: function(index)
		{
			return index >= 0 && index < this._activeControls.length ? this._activeControls[index] : null;
		},
		registerActiveControl: function(control)
		{
			var index = this.getActiveControlIndex(control);
			if(index >= 0)
			{
				return;
			}

			this._activeControls.push(control);
			control.setActive(true);
			if(this._mode !== BX.UI.EntityEditorMode.edit)
			{
				this._mode = BX.UI.EntityEditorMode.edit;
			}
		},
		unregisterActiveControl: function(control)
		{
			var index = this.getActiveControlIndex(control);
			if(index < 0)
			{
				return;
			}

			this._activeControls.splice(index, 1);
			control.setActive(false);
			if(this._activeControls.length === 0 && this._mode !== BX.UI.EntityEditorMode.view)
			{
				this._mode = BX.UI.EntityEditorMode.view;
			}
		},
		releaseActiveControls: function(options)
		{
			//region Release Event
			var eventArgs =
				{
					id: this._id,
					externalContext: this._externalContextId,
					context: this._contextId,
					entityTypeName: this._entityTypeName,
					entityId: this._entityId,
					model: this._model
				};
			BX.onCustomEvent(window, this.eventsNamespace + ":onRelease", [ this, eventArgs ]);
			//endregion

			for(var i = 0, length = this._activeControls.length; i < length; i++)
			{
				var control = this._activeControls[i];
				control.setActive(false);
				control.toggleMode(false, options);
			}
			this._activeControls = [];
		},
		hasChangedControls: function()
		{
			for(var i = 0, length = this._activeControls.length; i < length; i++)
			{
				if(this._activeControls[i].isChanged())
				{
					return true;
				}
			}
			return false;
		},
		hasChangedControllers: function()
		{
			for(var i = 0, length = this._controllers.length; i < length; i++)
			{
				if(this._controllers[i].isChanged())
				{
					return true;
				}
			}
			return false;
		},
		isWaitingForInput: function()
		{
			if(this._mode !== BX.UI.EntityEditorMode.edit)
			{
				return false;
			}

			for(var i = 0, length = this._activeControls.length; i < length; i++)
			{
				if(this._activeControls[i].isWaitingForInput())
				{
					return true;
				}
			}
			return false;
		},
		processControlModeChange: function(control)
		{
			if(control.getMode() === BX.UI.EntityEditorMode.edit)
			{
				this.registerActiveControl(control);
			}
			else //BX.UI.EntityEditorMode.view
			{
				this.unregisterActiveControl(control);
			}

			if(this.getActiveControlCount() > 0)
			{
				this.showToolPanel();
			}
			else if (!this.isToolPanelAlwaysVisible())
			{
				this.hideToolPanel();
			}

			var eventArgs = {
				control: control,
			}
			BX.onCustomEvent(window, this.eventsNamespace + ":onControlModeChange", [ this, eventArgs ]);
		},
		processControlChange: function(control, params)
		{
			this.showToolPanel();
			var eventArgs = {
				control: control,
				params: params,
			}
			BX.onCustomEvent(window, this.eventsNamespace + ":onControlChange", [ this, eventArgs ]);
		},
		processControlAdd: function(control)
		{
			this.removeAvailableSchemeElement(control.getSchemeElement());
		},
		processControlMove: function(control)
		{
		},
		processControlRemove: function(control)
		{
			if(control instanceof BX.UI.EntityEditorField)
			{
				this.addAvailableSchemeElement(control.getSchemeElement());
			}
			else if(control instanceof BX.UI.EntityEditorSection)
			{
				var children = control.getChildren();
				for(var i= 0, length = children.length; i < length; i++)
				{
					this.addAvailableSchemeElement(children[i].getSchemeElement());
				}
			}
		},
		processSchemeChange: function()
		{
			for(var i = 0, length = this._controls.length; i < length; i++)
			{
				this._controls[i].processSchemeChange();
			}
		},
		//endregion
		//region Available Scheme Elements
		getAvailableSchemeElements: function()
		{
			return this._availableSchemeElements;
		},
		addAvailableSchemeElement: function(schemeElement)
		{
			this._availableSchemeElements.push(schemeElement);
			this._areAvailableSchemeElementsChanged = true;
			this.notifyAvailableSchemeElementsChanged();
		},
		removeAvailableSchemeElement: function(element)
		{
			var index = this.getAvailableSchemeElementIndex(element);
			if(index < 0)
			{
				return;
			}

			this._availableSchemeElements.splice(index, 1);
			this._areAvailableSchemeElementsChanged = true;
			this.notifyAvailableSchemeElementsChanged();
		},
		getAvailableSchemeElementIndex: function(element)
		{
			var schemeElements = this._availableSchemeElements;
			for(var i = 0, length = schemeElements.length; i < length; i++)
			{
				if(schemeElements[i] === element)
				{
					return i;
				}
			}
			return -1;
		},
		getAvailableSchemeElementByName: function(name)
		{
			var schemeElements = this._availableSchemeElements;
			for(var i = 0, length = schemeElements.length; i < length; i++)
			{
				var schemeElement = schemeElements[i];
				if(schemeElement.getName() === name)
				{
					return schemeElement;
				}
			}
			return null;
		},
		hasAvailableSchemeElements: function()
		{
			return (this._availableSchemeElements.length > 0);
		},
		getSchemeElementByName: function(name)
		{
			return this._scheme.findElementByName(name, { isRecursive: true });
		},
		notifyAvailableSchemeElementsChanged: function()
		{
			for(var i = 0, length = this._controls.length; i < length; i++)
			{
				this._controls[i].processAvailableSchemeElementsChange();
			}
		},
		hasTransferableElements: function(excludedNames)
		{
			var excludedCount = 0;
			if(BX.type.isArray(excludedNames))
			{
				excludedCount = excludedNames.length;
			}

			var columns = this._scheme.getElements();
			for (var columnsIterator = 0, columnsCount = columns.length; columnsIterator < columnsCount; columnsIterator++)
			{
				var sections = columns[columnsIterator].getElements();
				for(var sectionsIterator = 0, sectionsCount = sections.length; sectionsIterator < sectionsCount; sectionsIterator++)
				{
					var section = sections[sectionsIterator];
					var isExcluded = false;
					if(excludedCount > 0)
					{
						var sectionName = section.getName();
						for(var j = 0; j < excludedCount; j++)
						{
							if(excludedNames[j] === sectionName)
							{
								isExcluded = true;
								break;
							}
						}
					}

					if(isExcluded)
					{
						continue;
					}

					var childElements = section.getElements();
					for(var k = 0, childrenCount = childElements.length; k < childrenCount; k++)
					{
						if(childElements[k].isTransferable() &&  childElements[k].getName() !== "")
						{
							return true;
						}
					}
				}
			}

			return false;
		},
		//endregion
		//region Controllers
		createController: function(data)
		{
			return BX.UI.EntityEditorControllerFactory.create(
				BX.prop.getString(data, "type", ""),
				BX.prop.getString(data, "name", ""),
				{
					config: BX.prop.getObject(data, "config", {}),
					model: this._model,
					editor: this
				}
			);
		},
		processControllerChange: function(controller)
		{
			this.showToolPanel();
			var eventArgs = {
				controller: controller,
			}
			BX.onCustomEvent(window, this.eventsNamespace + ":onControllerChange", [ this, eventArgs ]);
		},
		getControllers: function()
		{
			return this._controllers;
		},
		//endregion
		//region Layout
		getContainer: function()
		{
			return this._container;
		},
		prepareContextDataLayout: function(context, parentName)
		{
			for(var key in context)
			{
				if(!context.hasOwnProperty(key))
				{
					continue;
				}

				var item = context[key];
				var name = key;
				if(BX.type.isNotEmptyString(parentName))
				{
					name = parentName + "[" + name + "]";
				}
				if(BX.type.isPlainObject(item))
				{
					this.prepareContextDataLayout(item, name);
				}
				else
				{
					this._formElement.appendChild(
						BX.create("input", { props: { type: "hidden", name: name, value: item } })
					);
				}
			}
		},
		layout: function()
		{
			var eventArgs = { cancel: false };
			BX.onCustomEvent(window, this.eventsNamespace + ":onBeforeLayout", [ this, eventArgs ]);
			if(eventArgs["cancel"])
			{
				return;
			}

			this.prepareContextDataLayout(this._context, "");

			if(this._toolPanel)
			{
				this._toolPanel.layout();
			}

			var userFieldLoaders =
			{
				edit: BX.UI.EntityUserFieldLayoutLoader.create(
					this._id,
					{ mode: BX.UI.EntityEditorMode.edit, enableBatchMode: true, owner: this }
				),
				view: BX.UI.EntityUserFieldLayoutLoader.create(
					this._id,
					{ mode: BX.UI.EntityEditorMode.view, enableBatchMode: true, owner: this }
				)
			};

			var i, length, control;
			for(i = 0, length = this._controls.length; i < length; i++)
			{
				control = this._controls[i];
				var mode = control.getMode();

				var layoutOptions =
					{
						userFieldLoader: userFieldLoaders[BX.UI.EntityEditorMode.getName(mode)],
						enableFocusGain: !this._isEmbedded
					};
				control.layout(layoutOptions);

				if(mode === BX.UI.EntityEditorMode.edit)
				{
					this.registerActiveControl(control);
				}
			}

			for(var key in userFieldLoaders)
			{
				if(userFieldLoaders.hasOwnProperty(key))
				{
					userFieldLoaders[key].runBatch();
				}
			}

			if(this.getActiveControlCount() > 0)
			{
				this.showToolPanel();
			}

			if(this._model.isCaptionEditable())
			{
				BX.bind(
					this._pageTitle,
					"click",
					BX.delegate(this.onPageTileClick, this)
				);

				if(this._editPageTitleButton)
				{
					BX.bind(
						this._editPageTitleButton,
						"click",
						BX.delegate(this.onPageTileClick, this)
					);
				}
			}

			if(this._buttonContainer && this.isBottomPanelEnabled())
			{
				if(this.isSectionCreationEnabled())
				{
					this._createSectionButton = BX.create(
						"span",
						{
							props: { className: "ui-entity-add-widget-link" },
							text: BX.message("UI_ENTITY_EDITOR_CREATE_SECTION"),
							events: { click: BX.delegate(this.onCreateSectionButtonClick, this) }
						}
					);
					this._buttonContainer.appendChild(this._createSectionButton);
				}

				if(this.isConfigControlEnabled())
				{
					var configScope = this._config.getScope();
					var configScopeCaption = BX.UI.EntityConfigScope.getCaption(configScope);
					this._buttonContainer.appendChild(
						BX.create(
							"span",
							{
								props:
									{
										className: configScope === BX.UI.EntityConfigScope.common ? "ui-entity-card-common" : "ui-entity-card-private",
										title: configScopeCaption
									}
							}
						)
					);

					this._configMenuButton = BX.create(
						"span",
						{
							props: { className: "ui-entity-settings-link" },
							text: configScopeCaption,
							events: { click: BX.delegate(this.onConfigMenuButtonClick, this) }
						}
					);
					this._buttonContainer.appendChild(this._configMenuButton);
				}
			}

			this.adjustButtons();
		},
		refreshLayout: function(options)
		{
			var userFieldLoaders =
				{
					edit: BX.UI.EntityUserFieldLayoutLoader.create(
						this._id,
						{ mode: BX.UI.EntityEditorMode.edit, enableBatchMode: true, owner: this }
					),
					view: BX.UI.EntityUserFieldLayoutLoader.create(
						this._id,
						{ mode: BX.UI.EntityEditorMode.view, enableBatchMode: true, owner: this }
					)
				};


			if(!BX.type.isPlainObject(options))
			{
				options = {};
			}

			for(var i = 0, length = this._controls.length; i < length; i++)
			{
				var control = this._controls[i];
				var mode = control.getMode();

				var layoutOptions = BX.mergeEx(
					options,
					{
						userFieldLoader: userFieldLoaders[BX.UI.EntityEditorMode.getName(mode)],
						enableFocusGain: !this._isEmbedded
					}
				);
				control.refreshLayout(layoutOptions);
			}

			for(var key in userFieldLoaders)
			{
				if(userFieldLoaders.hasOwnProperty(key))
				{
					userFieldLoaders[key].runBatch();
				}
			}

			this.adjustButtons();

			BX.onCustomEvent(window, this.eventsNamespace + ":onRefreshLayout", [ this ]);
		},
		refreshViewModeLayout: function(options)
		{
			var userFieldLoader = BX.UI.EntityUserFieldLayoutLoader.create(
				this._id,
				{ mode: BX.UI.EntityEditorMode.view, enableBatchMode: true, owner: this }
			);

			if(!BX.type.isPlainObject(options))
			{
				options = {};
			}

			for(var i = 0, length = this._controls.length; i < length; i++)
			{
				var control = this._controls[i];

				var layoutOptions = BX.mergeEx(
					options,
					{
						userFieldLoader: userFieldLoader,
						enableFocusGain: false,
						isRefreshViewModeLayout: true
					}
				);
				control.refreshViewModeLayout(layoutOptions);
			}

			userFieldLoader.runBatch();

			BX.onCustomEvent(window, this.eventsNamespace + ":onRefreshViewModeLayout", [ this ]);
		},
		//endregion
		switchControlMode: function(control, mode, options)
		{
			if(!this.isModeToggleEnabled())
			{
				return;
			}

			if(mode === BX.UI.EntityEditorMode.view)
			{
				if(control.checkModeOption(BX.UI.EntityEditorModeOptions.saveOnExit))
				{
					this._modeSwitch.getQueue().add(control, BX.UI.EntityEditorMode.view);
					this._modeSwitch.run();
				}
				else
				{
					control.setMode(mode, { options: options, notify: true });
					control.refreshLayout();
				}
			}
			else// if(mode === BX.UI.EntityEditorMode.edit)
			{
				if(!BX.UI.EntityEditorModeOptions.check(options, BX.UI.EntityEditorModeOptions.exclusive))
				{
					control.setMode(BX.UI.EntityEditorMode.edit, { options: options, notify: true });
					control.refreshLayout();
				}
				else
				{
					var queuedControlQty = 0;
					for(var i = 0, length = this._activeControls.length; i < length; i++)
					{
						var activeControl = this._activeControls[i];
						if(activeControl.checkModeOption(BX.UI.EntityEditorModeOptions.saveOnExit))
						{
							this._modeSwitch.getQueue().add(activeControl, BX.UI.EntityEditorMode.view, options);
							queuedControlQty++;
						}
					}

					if(queuedControlQty > 0)
					{
						this._modeSwitch.getQueue().add(control, BX.UI.EntityEditorMode.edit, options);
						this._modeSwitch.run();
					}
					else
					{
						control.setMode(BX.UI.EntityEditorMode.edit, { options: options, notify: true });
						control.refreshLayout();
					}
				}
			}
		},
		switchToViewMode: function(options)
		{
			this.releaseActiveControls(options);
			if (!this.isToolPanelAlwaysVisible())
			{
				this.hideToolPanel();
			}

			var eventArgs = {
				options: options,
			}
			BX.onCustomEvent(window, this.eventsNamespace + ":onSwitchToViewMode", [ this, eventArgs ]);
		},
		switchTitleMode: function(mode)
		{
			if(mode === BX.UI.EntityEditorMode.edit)
			{
				this._pageTitle.style.display = "none";

				if(this._buttonWrapper)
				{
					this._buttonWrapper.style.display = "none";
				}

				this._pageTitleInput = BX.create(
					"input",
					{
						props:
							{
								type: "text",
								className: this.pageTitleInputClassName,
								value: this._model.getCaption()
							}
					}
				);
				//this._pageTitle.parentNode.insertBefore(this._pageTitleInput, this._buttonWrapper);
				this._pageTitle.parentNode.insertBefore(this._pageTitleInput, this._pageTitle);
				this._pageTitleInput.focus();

				window.setTimeout(
					BX.delegate(
						function()
						{
							BX.bind(document, "click", this._pageTitleExternalClickHandler);
							BX.bind(this._pageTitleInput, "keyup", this._pageTitleKeyPressHandler);
						},
						this
					),
					300
				);
			}
			else
			{
				if(this._pageTitleInput)
				{
					this._pageTitleInput = BX.remove(this._pageTitleInput);
				}

				this._pageTitle.innerHTML = BX.util.htmlspecialchars(this._model.getCaption());
				this._pageTitle.style.display = "";

				if(this._buttonWrapper)
				{
					this._buttonWrapper.style.display = "";
				}

				BX.unbind(document, "click", this._pageTitleExternalClickHandler);
				BX.unbind(this._pageTitleInput, "keyup", this._pageTitleKeyPressHandler);

				this.adjustTitle();
			}
		},
		adjustTitle: function()
		{
			if(!this._enablePageTitleControls || !this._buttonWrapper)
			{
				return;
			}

			var caption = this._model.getCaption().trim();
			var captionTail = "";
			var match = caption.match(/\s+\S+\s*$/);
			if(match)
			{
				captionTail = caption.substr(match["index"]);
				caption = caption.substr(0, match["index"]);
			}
			else
			{
				captionTail = caption;
				caption = "";
			}

			BX.cleanNode(this._buttonWrapper);
			if(captionTail !== "")
			{
				this._buttonWrapper.appendChild(document.createTextNode(captionTail));
			}
			if(this._editPageTitleButton)
			{
				this._buttonWrapper.appendChild(this._editPageTitleButton);
			}
			if(this._copyPageUrlButton)
			{
				this._buttonWrapper.appendChild(this._copyPageUrlButton);
			}

			this._pageTitle.innerHTML = BX.util.htmlspecialchars(caption);
		},
		adjustSize: function()
		{
			if(!this._enablePageTitleControls || !this._pageTitle)
			{
				return;
			}

			var wrapper = this._pageTitle.parentNode ? this._pageTitle.parentNode : this._pageTitle;
			var enableNarrowSize = wrapper.offsetWidth <= 480 && this._model.getCaption().length >= 40;
			if(enableNarrowSize && !BX.hasClass(wrapper, "pagetitle-narrow"))
			{
				BX.addClass(wrapper, "pagetitle-narrow");
			}
			else if(!enableNarrowSize && BX.hasClass(wrapper, "pagetitle-narrow"))
			{
				BX.removeClass(wrapper, "pagetitle-narrow");
			}

		},
		adjustButtons: function()
		{
			//Move configuration menu button to last section if bottom panel is hidden.
			if(this._config.isScopeToggleEnabled() && !this._enableBottomPanel && this._controls.length > 0)
			{
				var control = this._controls[this._controls.length - 1];
				if(control instanceof BX.UI.EntityEditorColumn)
				{
					if(control._sections.length > 0)
					{
						control = control._sections[control._sections.length - 1]
					}
					else
					{
						// nowhere to add
						return;
					}
				}
				control.addButtonElement(
					BX.create(
						"span",
						{
							props:
								{
									className: this._config.getScope() === BX.UI.EntityConfigScope.common
										? "ui-entity-card-common" : "ui-entity-card-private"
								},
							events: { click: BX.delegate(this.onConfigMenuButtonClick, this) }
						}
					),
					{ position: "right" }
				);
			}
		},
		showToolPanel: function()
		{
			if(!this._toolPanel || this._toolPanel.isVisible())
			{
				return;
			}

			this._toolPanel.setVisible(true);
			if(this._parentContainer)
			{
				this._parentContainer.style.paddingBottom = "50px";

				document.body.style.paddingBottom = "60px";
				document.body.style.height = "auto";
			}
		},
		hideToolPanel: function()
		{
			if(!this._toolPanel || !this._toolPanel.isVisible())
			{
				return;
			}

			this._toolPanel.setVisible(false);
			if(this._parentContainer)
			{
				this._parentContainer.style.paddingBottom = "";

				document.body.style.paddingBottom = "";
				document.body.style.height = "";
			}
		},
		showMessageDialog: function(id, title, content)
		{
			var dlg = BX.UI.EditorAuxiliaryDialog.create(
				id,
				{
					title: title,
					content: content,
					buttons:
						[
							{
								id: "continue",
								type: BX.UI.DialogButtonType.accept,
								text: BX.message("UI_ENTITY_EDITOR_CONTINUE"),
								callback: function(button) { button.getDialog().close(); }
							}
						]
				}
			);
			dlg.open();
		},
		getMessage: function(name)
		{
			var m = BX.UI.EntityEditor.messages;
			return m.hasOwnProperty(name) ? m[name] : name;
		},
		getFormElement: function()
		{
			return this._formElement;
		},
		isChanged: function()
		{
			return this._isNew || this.hasChangedControls() || this.hasChangedControllers();
		},
		getEntityTypeForAction: function()
		{
			return this._entityTypeName;
		},
		prepareControllersData: function(data)
		{
			if (!BX.type.isPlainObject(data))
			{
				data = {};
			}
			for(var i = 0, length = this._controllers.length; i < length; i++)
			{
				if (BX.Type.isFunction(this._controllers[i].onBeforesSaveControl))
				{
					data = this._controllers[i].onBeforesSaveControl(data);
				}
			}
			return data;
		},
		savePageTitle: function()
		{
			if(!this._pageTitleInput)
			{
				return;
			}

			var title = BX.util.trim(this._pageTitleInput.value);
			if(title === "")
			{
				return;
			}

			this._model.setCaption(title);
			var data =
				{
					"ACTION": "SAVE",
					"ACTION_ENTITY_ID": this._entityId,
					"ACTION_ENTITY_TYPE": this.getEntityTypeForAction(),
					"PARAMS": BX.prop.getObject(this._context, "PARAMS", {})
				};

			this._model.prepareCaptionData(data);
			data = BX.mergeEx(data, this.prepareControllersData(data));
			BX.ajax(
				{
					url: this._serviceUrl,
					method: "POST",
					dataType: "json",
					data: data,
					onsuccess: BX.delegate(this.onSaveSuccess, this)
				}
			);
		},
		performAction: function (actionId)
		{
			if (!this._actionTypes)
			{
				return;
			}
			if (this._actionTypes[actionId] === BX.UI.EntityEditorActionTypes.save)
			{
				this.performSaveChangedAction(actionId);
			}
			else if (this._actionTypes[actionId] === BX.UI.EntityEditorActionTypes.direct)
			{
				this.performDirectAction(actionId);
			}
		},
		performDirectAction: function (actionId)
		{
			if(this._toolPanel)
			{
				this._toolPanel.setLocked(true);
			}

			var eventArgs = this.getActionEventArguments();
			eventArgs.actionId = actionId;

			BX.onCustomEvent(window, this.eventsNamespace + ":onDirectAction", [ this, eventArgs ]);

			if(eventArgs["cancel"])
			{
				return;
			}

			this._ajaxForms[actionId].submit();
		},
		saveChanged: function()
		{
			this.performSaveChangedAction(BX.UI.EntityEditorActionIds.defaultActionId);
		},
		performSaveChangedAction: function(action)
		{
			if(!this._isNew && !this.hasChangedControls() && !this.hasChangedControllers() && !this.isWaitingForInput())
			{
				this._modeSwitch.reset();
				this.releaseActiveControls();
				this.refreshLayout({ reset: true });
				if (!this.isToolPanelAlwaysVisible())
				{
					this.hideToolPanel();
				}

				BX.onCustomEvent(window, this.eventsNamespace + ":onNothingChanged", [ this ]);
			}
			else
			{
				this._modeSwitch.reset();
				this._modeSwitch.getQueue().addBatch(this._activeControls, BX.UI.EntityEditorMode.view);
				this._modeSwitch.setRunAction(action);
				this._modeSwitch.run();
			}
		},
		saveDelayed: function(delay)
		{
			this.performSaveDelayedAction(BX.UI.EntityEditorActionIds.defaultActionId, delay);
		},
		performSaveDelayedAction: function (action, delay)
		{
			if(typeof(delay) === "undefined")
			{
				delay = 0;
			}

			if(this._delayedSaveHandle > 0)
			{
				window.clearTimeout(this._delayedSaveHandle);
			}
			this._delayedSaveHandle = window.setTimeout(BX.delegate(function () {
				this.performSaveAction(action);
			}, this), delay);
		},
		save: function()
		{
			this.performSaveAction(BX.UI.EntityEditorActionIds.defaultActionId);
		},
		performSaveAction: function (action)
		{
			if(this._toolPanel)
			{
				this._toolPanel.setLocked(true);
			}

			var result = BX.UI.EntityValidationResult.create();
			this.validate(result).then(
				BX.delegate(
					function()
					{
						if(this._bizprocManager)
						{
							return this._bizprocManager.onBeforeSave(result);
						}

						var promise = new BX.Promise();
						window.setTimeout(function(){ promise.fulfill(); }, 0);
						return promise;
					},
					this
				)
			).then(
				BX.delegate(
					function()
					{
						if(result.getStatus())
						{
							this.performInnerSaveAction(action);
							if(this._bizprocManager)
							{
								this._bizprocManager.onAfterSave();
							}
						}
						else
						{
							if(this.isVisible())
							{
								var field = result.getTopmostField();
								if(field)
								{
									field.focus();
									var toolPanelOffset = 130;
									window.scroll(window.pageXOffset, window.pageYOffset + toolPanelOffset);
								}
							}

							if(this._toolPanel)
							{
								this._toolPanel.setLocked(false);
							}

							BX.onCustomEvent(window, this.eventsNamespace + ":onFailedValidation", [ this, result ]);
						}
					},
					this
				)
			);

			if(this._delayedSaveHandle > 0)
			{
				this._delayedSaveHandle = 0;
			}
		},
		saveControl: function(control)
		{
			if(this._entityId <= 0 && this._model.isIdentifiable())
			{
				return;
			}

			var result = BX.UI.EntityValidationResult.create();
			control.validate(result);

			if(!result.getStatus())
			{
				return;
			}

			var data =
				{
					"ACTION": "SAVE",
					"ACTION_ENTITY_ID": this._entityId,
					"ACTION_ENTITY_TYPE": this.getEntityTypeForAction()
				};

			data = BX.mergeEx(data, this._context);
			control.save();
			control.prepareSaveData(data);

			data = BX.mergeEx(data, this.prepareControllersData(data));

			BX.ajax(
				{
					method: "POST",
					dataType: "json",
					url: this._serviceUrl,
					data: data,
					onsuccess: BX.delegate(this.onSaveSuccess, this)
				}
			);
		},
		saveData: function(data)
		{
			if(this._entityId <= 0 && this._model.isIdentifiable())
			{
				return;
			}

			data = BX.mergeEx(data, this._context);
			data = BX.mergeEx(
				data,
				{
					"ACTION": "SAVE",
					"ACTION_ENTITY_ID": this._entityId,
					"ACTION_ENTITY_TYPE": this.getEntityTypeForAction()
				}
			);

			BX.ajax(
				{
					method: "POST",
					dataType: "json",
					url: this._serviceUrl,
					data: data,
					onsuccess: BX.delegate(this.onSaveSuccess, this)
				}
			);
		},
		reload: function()
		{
			if(this._isRequestRunning)
			{
				return;
			}
			if(this._entityId <= 0 && this._model.isIdentifiable())
			{
				return;
			}
			var eventArgs = this.getActionEventArguments();

			BX.onCustomEvent(window, this.eventsNamespace + ':onEntityStartReload', [ this, eventArgs ]);

			if(eventArgs["cancel"])
			{
				return;
			}

			var ajaxData = BX.prop.getObject(this._settings, 'ajaxData', {});
			var componentName = BX.prop.getString(ajaxData, 'COMPONENT_NAME', '');
			var signedParameters = BX.prop.getString(ajaxData, 'SIGNED_PARAMETERS', '');
			var reloadActionFormData = BX.prop.getObject(ajaxData,'RELOAD_FORM_DATA', {});
			var reloadActionName = BX.prop.getString(ajaxData, "RELOAD_ACTION_NAME", '');
			if (reloadActionName === '')
			{
				console.warn("Can't reload entity editor because RELOAD_ACTION_NAME is not defined");
				return;
			}

			this._reloadAjaxForm = this.createAjaxForm(
				{
					componentName: componentName,
					actionName: reloadActionName,
					signedParameters: signedParameters,
					formData: reloadActionFormData,
					enableRequiredUserFieldCheck: false
				},
				{
					onSuccess: this.onReloadSuccess.bind(this)
				}
			);

			if(this._reloadAjaxForm)
			{
				this._reloadAjaxForm.submit();
			}
		},
		validate: function(result)
		{
			for(var i = 0, length = this._activeControls.length; i < length; i++)
			{
				this._activeControls[i].validate(result);
			}

			var promise = new BX.Promise();
			this._userFieldManager.validate(result).then(
				BX.delegate(function() { promise.fulfill(); }, this)
			);
			return promise;
		},
		isRequestRunning: function()
		{
			return this._isRequestRunning;
		},
		getActionEventArguments: function()
		{
			return {
				id: this._id,
				externalContext: this._externalContextId,
				context: this._contextId,
				entityTypeName: this._entityTypeName,
				entityId: this._entityId,
				model: this._model,
				cancel: false
			};
		},
		innerSave: function()
		{
			this.performInnerSaveAction(BX.UI.EntityEditorActionIds.defaultActionId);
		},
		performInnerSaveAction: function(action)
		{
			if(this._isRequestRunning)
			{
				return;
			}

			var i, length;
			for(i = 0, length = this._controllers.length; i < length; i++)
			{
				this._controllers[i].onBeforeSubmit();
			}

			for(i = 0, length = this._activeControls.length; i < length; i++)
			{
				var control = this._activeControls[i];

				control.save();
				control.onBeforeSubmit();

				if(control.isSchemeChanged())
				{
					this._config.updateSchemeElement(control.getSchemeElement());
				}
			}

			if(this._areAvailableSchemeElementsChanged)
			{
				this._scheme.setAvailableElements(this._availableSchemeElements);
				this._areAvailableSchemeElementsChanged = false;
			}

			if(this._config && this._config.isChanged())
			{
				this._config.save(false);
			}

			//region Rise Save Event
			var eventArgs = this.getActionEventArguments();
			eventArgs.actionId = action;

			BX.onCustomEvent(window, this.eventsNamespace + ":onSave", [ this, eventArgs ]);

			if(eventArgs["cancel"])
			{
				return;
			}

			var enableCloseConfirmation = BX.prop.getBoolean(
				eventArgs,
				"enableCloseConfirmation",
				null
			);
			if(BX.type.isBoolean(enableCloseConfirmation))
			{
				this._enableCloseConfirmation = enableCloseConfirmation;
			}

			var ajaxFormToSubmit = null;

			if (this._ajaxForms && this._ajaxForms[action])
			{
				ajaxFormToSubmit = this._ajaxForms[action];
			}
			else
			{
				ajaxFormToSubmit = this._ajaxForm;
			}

			if(ajaxFormToSubmit)
			{
				var detailManager = this.getDetailManager();
				if(detailManager)
				{
					var params =  detailManager.prepareAnalyticParams(
						(this._entityId <= 0 && this._model.isIdentifiable()) ? "create" : "update",
						{ embedded: this.isEmbedded() ? "Y" : "N" }
					);

					if(params)
					{
						ajaxFormToSubmit.addUrlParams(params);
					}
				}
				ajaxFormToSubmit.submit();
			}
			//endregion
		},
		cancel: function()
		{
			//region Rise Cancel Event
			var eventArgs = this.getActionEventArguments();

			BX.onCustomEvent(window, this.eventsNamespace + ":onCancel", [ this, eventArgs ]);

			if(eventArgs["cancel"])
			{
				return;
			}
			//endregion

			var enableCloseConfirmation = BX.prop.getBoolean(
				eventArgs,
				"enableCloseConfirmation",
				null
			);
			if(BX.type.isBoolean(enableCloseConfirmation))
			{
				this._enableCloseConfirmation = enableCloseConfirmation;
			}

			if(this.hasChangedControls() || this.hasChangedControllers())
			{
				window.setTimeout(
					BX.delegate(this.openCancellationConfirmationDialog, this),
					250
				);
				return;
			}

			this.innerCancel();
		},
		innerCancel: function()
		{
			var i, length;
			for(i = 0, length = this._controllers.length; i < length; i++)
			{
				this._controllers[i].innerCancel();
			}

			this.rollback();

			if(this._isNew)
			{
				this.refreshLayout();
				if(typeof(top.BX.SidePanel) !== "undefined")
				{
					window.setTimeout(
						function ()
						{
							var slider = top.BX.SidePanel.Instance.getSliderByWindow(window);
							if(slider && slider.isOpen())
							{
								slider.close(false);
							}
						},
						250
					);
				}
			}
			else
			{
				this.switchToViewMode({ refreshLayout: false });
				this.refreshLayout();
			}
		},
		openCancellationConfirmationDialog: function()
		{
			if (this._confirmationCancelDialog)
			{
				return;
			}

			this._confirmationCancelDialog = BX.UI.EditorAuxiliaryDialog.create(
				"cancel_confirmation",
				{
					title: BX.message("UI_ENTITY_EDITOR_CONFIRMATION"),
					content: BX.message("UI_ENTITY_EDITOR_CANCEL_CONFIRMATION"),
					buttons:
						[
							{
								id: "yes",
								type: BX.UI.DialogButtonType.accept,
								text: BX.message("UI_ENTITY_EDITOR_YES"),
								callback: this._cancelConfirmationHandler
							},
							{
								id: "no",
								type: BX.UI.DialogButtonType.cancel,
								text: BX.message("UI_ENTITY_EDITOR_NO"),
								callback: this._cancelConfirmationHandler
							}
						]
				}
			);
			this._confirmationCancelDialog.open();
		},
		onCancelConfirmButtonClick: function(button)
		{
			button.getDialog().close();
			this._confirmationCancelDialog = null;
			if(button.getId() === "yes")
			{
				this.innerCancel();
			}
		},
		rollback: function()
		{
			this._model.rollback();

			var i, length;
			for(i = 0, length = this._controllers.length; i < length; i++)
			{
				this._controllers[i].rollback();
			}

			for(i = 0, length = this._activeControls.length; i < length; i++)
			{
				this._activeControls[i].rollback();
			}

			if(this._areAvailableSchemeElementsChanged)
			{
				this._availableSchemeElements = this._scheme.getAvailableElements();
				this._areAvailableSchemeElementsChanged = false;
			}
		},
		addSchemeElementAt: function(schemeElement, index)
		{
			if(this._config)
			{
				this._config.addSchemeElementAt(schemeElement, index);
			}
		},
		updateSchemeElement: function(schemeElement)
		{
			if(this._config)
			{
				this._config.updateSchemeElement(schemeElement);
			}
		},
		removeSchemeElement: function(schemeElement)
		{
			if(this._config)
			{
				this._config.removeSchemeElement(schemeElement);
			}
		},
		canChangeScheme: function()
		{
			return this._config && this._config.isChangeable();
		},
		isSchemeChanged: function()
		{
			return this._config && this._config.isChanged();
		},
		saveScheme: function()
		{
			if(!this._config)
			{
				return false;
			}

			var result = this._config.save(false);
			if(result)
			{
				this._areAvailableSchemeElementsChanged = false;
				this.processSchemeChange();
			}
			return result;
		},
		saveSchemeChanges: function()
		{
			this.commitSchemeChanges();
			return this.saveScheme();
		},
		commitSchemeChanges: function()
		{
			for(var i = 0, length = this._controls.length; i < length; i++)
			{
				this._controls[i].commitSchemeChanges();
			}

			if(this._areAvailableSchemeElementsChanged)
			{
				this._scheme.setAvailableElements(this._availableSchemeElements);
				this._areAvailableSchemeElementsChanged = false;
			}
		},
		canChangeCommonConfiguration: function()
		{
			return this._config.isCanChangeCommonConfiguration();
		},
		onSaveSuccess: function(result)
		{
			this._isRequestRunning = false;

			if(this._toolPanel)
			{
				this._toolPanel.setLocked(false);
				this._toolPanel.clearErrors();
			}

			//region Event Params
			var eventParams = BX.prop.getObject(result, "EVENT_PARAMS", {});
			eventParams["entityTypeName"] = this._entityTypeName;

			if(typeof(window.top.BX.Bitrix24) !== "undefined")
			{
				var slider = window.top.BX.Bitrix24.Slider.getTopSlider();
				if(slider)
				{
					eventParams["sliderUrl"] = slider.getUrl();
				}
			}
			//endregion

			var checkErrors = BX.prop.getObject(result, "CHECK_ERRORS", null);
			var error = BX.prop.getString(result, "ERROR", "");
			if(checkErrors || error !== "")
			{
				if(checkErrors)
				{
					var firstField = null;
					var errorMessages = [];
					for(var fieldId in checkErrors)
					{
						if(!checkErrors.hasOwnProperty(fieldId))
						{
							return;
						}

						var field = this.getActiveControlById(fieldId, true);
						if(field)
						{
							field.showError(checkErrors[fieldId]);
							if(!firstField)
							{
								firstField = field;
							}
						}
						else
						{
							errorMessages.push(checkErrors[fieldId]);
						}
					}

					if(firstField)
					{
						firstField.scrollAnimate();
					}

					error = errorMessages.join("<br/>");
				}

				if(error !== "" && this._toolPanel)
				{
					this._toolPanel.addError(error);
				}

				eventParams["checkErrors"] = checkErrors;
				eventParams["error"] = error;

				if(this._isNew)
				{
					BX.onCustomEvent(window, "onEntityCreateError", [eventParams]);
				}
				else
				{
					eventParams["entityId"] = this._entityId;
					BX.onCustomEvent(window, "onEntityUpdateError", [eventParams]);
				}

				this.releaseAjaxForm();
				this.initializeAjaxForm();

				return;
			}

			var entityData = BX.prop.getObject(result, "ENTITY_DATA", null);
			eventParams["entityData"] = entityData;

			if(!this._model.isIdentifiable())
			{
				//fire onEntityUpdate
				eventParams["sender"] = this;
				BX.onCustomEvent(window, "onEntityUpdate", [eventParams]);
			}
			else
			{
				if(this._isNew)
				{
					this._entityId = BX.prop.getInteger(result, "ENTITY_ID", 0);
					if(this._entityId <= 0)
					{
						if(this._toolPanel)
						{
							this._toolPanel.addError(BX.message("UI_ENTITY_EDITOR_COULD_NOT_FIND_ENTITY_ID"));
						}
						return;
					}

					//fire onEntityCreate
					eventParams["sender"] = this;
					eventParams["entityId"] = this._entityId;
					BX.onCustomEvent(window, "onEntityCreate", [eventParams]);

					this._isNew = false;
				}
				else
				{
					//fire onEntityUpdate
					eventParams["sender"] = this;
					eventParams["entityId"] = this._entityId;
					BX.onCustomEvent(window, "onEntityUpdate", [eventParams]);
				}
			}

			var redirectUrl = BX.prop.getString(result, "REDIRECT_URL", "");

			var additionalEventParams = BX.prop.getObject(result, "EVENT_PARAMS", null);
			if(additionalEventParams)
			{
				var eventName = BX.prop.getString(additionalEventParams, "name", "");
				var eventArgs = BX.prop.getObject(additionalEventParams, "args", null);
				if(eventName !== "" && eventArgs !== null)
				{
					if(redirectUrl !== "")
					{
						eventArgs["redirectUrl"] = redirectUrl;
					}
					BX.localStorage.set(eventName, eventArgs, 10);
				}
			}

			if(this._isReleased)
			{
				return;
			}

			if(redirectUrl !== "")
			{
				eventParams.redirectUrl = redirectUrl;
				BX.onCustomEvent(window, "beforeEntityRedirect", [eventParams]);
				window.location.replace(
					BX.util.add_url_param(
						redirectUrl,
						{ "IFRAME": "Y", "IFRAME_TYPE": "SIDE_SLIDER" }
					)
				);
			}
			else
			{
				if(BX.type.isPlainObject(entityData))
				{
					//Notification event is disabled because we will call "refreshLayout" for all controls at the end.
					this._model.setData(entityData, { enableNotification: false });
				}

				this.adjustTitle();
				this.adjustSize();
				this.releaseAjaxForm();
				this.initializeAjaxForm();

				for(var i = 0, length = this._controllers.length; i < length; i++)
				{
					this._controllers[i].onAfterSave();
				}

				if(this._modeSwitch.isRunning())
				{
					this._modeSwitch.complete();
				}
				else
				{
					this.switchToViewMode({ refreshLayout: false });
				}

				this.refreshLayout({ reset: true });
				if (!this.isToolPanelAlwaysVisible())
				{
					this.hideToolPanel();
				}
			}
		},
		onSaveFailure: function(response)
		{
			this._isRequestRunning = false;

			if(this._toolPanel)
			{
				this._toolPanel.setLocked(false);
				this._toolPanel.clearErrors();
			}

			var errors = BX.prop.getArray(response, "ERRORS", []);
			if(this._toolPanel)
			{
				for(var i = 0, length = errors.length; i < length; i++)
				{
					this._toolPanel.addError(errors[i]);
				}
			}
		},
		onReloadSuccess: function(result)
		{
			var eventParams = BX.prop.getObject(result, "EVENT_PARAMS", {});
			eventParams["entityId"] = this._entityId;
			eventParams["entityTypeName"] = this._entityTypeName;

			var checkErrors = BX.prop.getObject(result, "CHECK_ERRORS", null);
			var error = BX.prop.getString(result, "ERROR", "");
			if(checkErrors || error !== "")
			{
				eventParams["checkErrors"] = checkErrors;
				eventParams["error"] = error;

				BX.onCustomEvent(window, this.eventsNamespace + ":onEntityReloadError", [eventParams]);
				return;
			}
			var entityData = BX.prop.getObject(result, "ENTITY_DATA", null);

			eventParams["entityData"] = entityData;
			eventParams["sender"] = this;
			eventParams["entityId"] = this._entityId;
			BX.onCustomEvent(window, this.eventsNamespace + ":onEntityReload", [eventParams]);

			if(BX.type.isPlainObject(entityData))
			{
				var previousModel = Object.create(this._model); // clone model object
				previousModel.setData(  // copy model data
					BX.clone(this._model.getData()),
					{
						enableNotification: false
					}
				);

				//Notification event is disabled because we will call "refreshViewModeLayout" for all controls at the end.
				this._model.setData(entityData, {enableNotification: false});

				this.adjustTitle();
				this.adjustSize();

				for(var i = 0, length = this._controllers.length; i < length; i++)
				{
					this._controllers[i].onReload();
				}

				this.refreshViewModeLayout({
					previousModel: previousModel,
					reset: true
				});
			}
		},
		formatMoney: function(sum, currencyId, callback)
		{
			BX.ajax(
				{
					url: BX.prop.getString(this._settings, "serviceUrl", ""),
					method: "POST",
					dataType: "json",
					data:
						{
							"ACTION": "GET_FORMATTED_SUM",
							"CURRENCY_ID": currencyId,
							"SUM": sum
						},
					onsuccess: callback
				}
			);
		},
		findOption: function (value, options)
		{
			for(var i = 0, l = options.length; i < l; i++)
			{
				if(value === options[i].VALUE)
				{
					return options[i].NAME;
				}
			}
			return value;
		},
		prepareConfigMenuItems: function()
		{
			var items = [];
			var callback = BX.delegate(this.onMenuItemClick, this);

			if(this._config.isScopeToggleEnabled())
			{
				var configScope = this._config.getScope();
				items.push(
					{
						id: "switchToPersonalConfig",
						text: BX.message("UI_ENTITY_EDITOR_SWITCH_TO_PERSONAL_CONFIG"),
						onclick: callback,
						className: configScope === BX.UI.EntityConfigScope.personal
							? "menu-popup-item-accept" : "menu-popup-item-none"
					}
				);

				items.push(
					{
						id: "switchToCommonConfig",
						text: BX.message("UI_ENTITY_EDITOR_SWITCH_TO_COMMON_CONFIG"),
						onclick: callback,
						className: configScope === BX.UI.EntityConfigScope.common
							? "menu-popup-item-accept" : "menu-popup-item-none"
					}
				);
			}

			if (this._config._userScopes)
			{
				for (var userScopeId in this._config._userScopes)
				{
					items.push(
						{
							text: BX.message('UI_ENTITY_EDITOR_CHECK_SCOPE').replace('#SCOPE_NAME#', this._config._userScopes[userScopeId]['NAME']),
							onclick: callback,
							attributes: {
								'data-id': userScopeId
							},
							className:
								(
									this._config.getScope() === BX.UI.EntityConfigScope.custom
									&& this._config._userScopeId === userScopeId
								)
									? "menu-popup-item-accept" : "menu-popup-item-none"

						}
					);
				}
			}

			if(this.canChangeScheme())
			{
				if(this._config.isScopeToggleEnabled())
				{
					items.push({ delimiter: true });
				}

				items.push(
					{
						id: "resetConfig",
						text: BX.message("UI_ENTITY_EDITOR_RESET_CONFIG"),
						onclick: callback,
						className: "menu-popup-item-none"
					}
				);

				if(BX.prop.getBoolean(this._settings, "enableSettingsForAll", false))
				{
					items.push(
						{
							id: "forceCommonConfigForAllUsers",
							text: BX.message("UI_ENTITY_EDITOR_FORCE_COMMON_CONFIG_FOR_ALL"),
							onclick: callback,
							className: "menu-popup-item-none"
						}
					);
				}

				if(this.moduleId && this.canChangeCommonConfiguration())
				{
					items.push({ delimiter: true });

					items.push(
						{
							id: "createConfigForCheckedUsers",
							text: BX.message('UI_ENTITY_EDITOR_CREATE_SCOPE'),
							onclick: callback,
							className: "menu-popup-item-none"
						}
					);

					items.push(
						{
							id: "editCommonConfig",
							text: BX.message('UI_ENTITY_EDITOR_UPDATE_SCOPE'),
							onclick: callback,
							className: "menu-popup-item-none"
						}
					);
				}
			}

			BX.onCustomEvent(window, this.eventsNamespace + ":onPrepareConfigMenuItems", [ this, items ]);
			return items;
		},
		getServiceUrl: function()
		{
			return this._serviceUrl;
		},
		loadCustomHtml: function(actionName, actionData, callback)
		{
			actionData["ACTION"] = actionName;
			actionData["ACTION_ENTITY_ID"] = this._entityId;
			BX.ajax(
				{
					url: this._serviceUrl,
					method: "POST",
					dataType: "html",
					data: actionData,
					onsuccess: callback
				}
			);
		},
		onFormSubmit: function(sender, eventArgs)
		{
			this._isRequestRunning = true;
			if(this._toolPanel)
			{
				this._toolPanel.setLocked(true);
			}
		},
		//region Events
		onResize: function(e)
		{
			this.adjustSize();
		},
		onPageTileClick: function(e)
		{
			if(this._readOnly)
			{
				return
			}

			if(this.isChanged())
			{
				this.showMessageDialog(
					"titleEditDenied",
					BX.message("UI_ENTITY_EDITOR_TITLE_EDIT"),
					BX.message("UI_ENTITY_EDITOR_TITLE_EDIT_UNSAVED_CHANGES")
				);
				return;
			}

			this.switchTitleMode(BX.UI.EntityEditorMode.edit);
		},
		onCreateSectionButtonClick: function(e)
		{
			if(!this.isSectionCreationEnabled())
			{
				return;
			}

			var index = this.getControlCount();
			var name = "user_" + BX.util.getRandomString(8).toLowerCase();

			var schemeElement = BX.UI.EntitySchemeElement.create(
				{
					type: "section",
					name: name,
					title: BX.message("UI_ENTITY_EDITOR_NEW_SECTION_TITLE")
				}
			);

			var sectionSettings = {
				schemeElement: schemeElement,
				model: this._model,
			};

			var firstColumn = this.getControlByIndex(0);

			if (!firstColumn)
			{
				this.addSchemeElementAt(schemeElement, index);
				sectionSettings.container = this._formElement;
			}

			var control = this.createControl(
				"section",
				name,
				sectionSettings
			);

			if (firstColumn)
			{
				firstColumn.addChild(control,{
					enableSaving: false
				});
			}
			else
			{
				this.addControlAt(control, 0);
				this.saveScheme();
			}

			control.setMode(BX.UI.EntityEditorMode.edit, { notify: false });
			control.refreshLayout();
			control.setTitleMode(BX.UI.EntityEditorMode.edit);
			this.registerActiveControl(control);
		},
		onConfigMenuButtonClick: function(e)
		{
			if(this._isConfigMenuShown)
			{
				return;
			}

			var menuItems = this.prepareConfigMenuItems();
			if(menuItems.length > 0)
			{
				BX.PopupMenu.show(
					this._id + "_config_menu",
					BX.getEventTarget(e),
					menuItems,
					{
						angle: false,
						autoHide: true,
						closeByEsc: true,
						events:
							{
								onPopupShow: function(){ this._isConfigMenuShown = true; }.bind(this),
								onPopupClose: function(){ BX.PopupMenu.destroy(this._id + "_config_menu"); }.bind(this),
								onPopupDestroy: function(){ this._isConfigMenuShown = false; }.bind(this)
							}
					}
				);
			}
		},
		onPageTitleExternalClick: function(e)
		{
			var target = BX.getEventTarget(e);
			if(target !== this._pageTitleInput)
			{
				this.savePageTitle();
				this.switchTitleMode(BX.UI.EntityEditorMode.view);
			}
		},
		onPageTitleKeyPress: function(e)
		{
			var c = e.keyCode;
			if(c === 13)
			{
				this.savePageTitle();
				this.switchTitleMode(BX.UI.EntityEditorMode.view);
			}
			else if(c === 27)
			{
				this.switchTitleMode(BX.UI.EntityEditorMode.view);
			}
		},
		onInterfaceToolbarMenuBuild: function(sender, eventArgs)
		{
			var menuItems = BX.prop.getArray(eventArgs, "items", null);
			if(!menuItems)
			{
				return;
			}

			var configMenuItems = this.prepareConfigMenuItems();
			if(configMenuItems.length > 0)
			{
				if(menuItems.length > 0)
				{
					menuItems.push({ delimiter: true });
				}

				for(var i = 0, length = configMenuItems.length; i < length; i++)
				{
					menuItems.push(configMenuItems[i]);
				}
			}
		},
		//endregion
		//region Configuration
		getCommonConfigEditUrl: function(entityTypeId, moduleId)
		{
			return this._commonConfigEditUrl
				.replace(/#ENTITY_TYPE_ID_VALUE#/gi, entityTypeId)
				.replace(/#MODULE_ID#/gi, moduleId);
		},
		onMenuItemClick: function(event, menuItem)
		{
			var id = BX.prop.getString(menuItem, "id", "");
			switch (id) {
				case 'resetConfig':
					this.resetConfig();
					break;
				case 'switchToPersonalConfig':
					this.setConfigScope(BX.UI.EntityConfigScope.personal);
					break;
				case 'switchToCommonConfig':
					this.setConfigScope(BX.UI.EntityConfigScope.common);
					break;
				case 'forceCommonConfigForAllUsers':
					this.forceCommonConfigScopeForAll();
					break;
				case 'createConfigForCheckedUsers':
					this.createConfigScopeForCheckedUsers();
					break;
				case 'editCommonConfig':
					BX.SidePanel.Instance.open(
						this.getCommonConfigEditUrl(this._config._id, this.moduleId),
						{width: 980}
					);
					break;
				default:
					var attributes = BX.prop.getObject(menuItem, "attributes", "");
					if (attributes['data-id'] !== undefined)
					{
						this.setConfigScope(BX.UI.EntityConfigScope.custom, attributes['data-id']);
					}
			}

			if(menuItem.menuWindow)
			{
				menuItem.menuWindow.close();
			}
		},
		setConfigScope: function(scope, userScopeId)
		{
			if(
				(
					scope === this._config.getScope()
					&& this._config.getScope() !== BX.UI.EntityConfigScope.custom
				)
				||
				(
					scope === BX.UI.EntityConfigScope.custom
					&&
					(userScopeId === undefined || userScopeId === this._config._userScopeId)
				)
			)
			{
				return;
			}

			this._config.setScope(scope, userScopeId, this.moduleId).then(
				function()
				{
					var eventArgs = {
						id: this._id,
						moduleId: this.moduleId,
						scope: scope, userScopeId: userScopeId,
						enableReload: true
					};
					BX.onCustomEvent(window, this.eventsNamespace + ":onConfigScopeChange", [ this, eventArgs ]);

					if(eventArgs["enableReload"] && !this._isEmbedded)
					{
						window.location.reload(true);
					}
				}.bind(this)
			);
		},
		createConfigScopeForCheckedUsers: function()
		{
			var config = BX.UI.EntityEditorScopeConfig.create(
				this._id+'_config', {
					editor: this,
					config: this._config.toJSON(),
					entityTypeId: this._config._id,
					isCommonConfig: true,
					moduleId: this.moduleId
				});
			config.open();
		},
		forceCommonConfigScopeForAll: function()
		{
			this._config.forceCommonScopeForAll().then(
				function()
				{
					var scope = this._config.getScope();
					var eventArgs = { id: this._id, scope: scope, enableReload: true };
					BX.onCustomEvent(window, this.eventsNamespace + ":onForceCommonConfigScopeForAll", [ this, eventArgs ]);

					if(eventArgs["enableReload"] && !this._isEmbedded && scope !== BX.UI.EntityConfigScope.common)
					{
						window.location.reload(true);
					}
				}.bind(this)
			);
		},
		resetConfig: function()
		{
			this._config.reset(false).then(
				function()
				{
					var scope = this._config.getScope();
					var eventArgs = { id: this._id, scope: scope, enableReload: true };
					BX.onCustomEvent(window, this.eventsNamespace + ":onConfigReset", [ this, eventArgs ]);

					if(eventArgs["enableReload"] && !this._isEmbedded)
					{
						window.location.reload(true);
					}
				}.bind(this)
			);
		},
		getConfigOption: function(name, defaultValue)
		{
			return this._config.getOption(name, defaultValue);
		},
		setConfigOption: function(name, value)
		{
			return this._config.setOption(name, value);
		},
		//endregion
		//region Options
		getOption: function(name, defaultValue)
		{
			return BX.prop.getString(this._settings["options"], name, defaultValue);
		},
		setOption: function(name, value)
		{
			if(typeof(value) === "undefined" || value === null)
			{
				return;
			}

			if(BX.prop.getString(this._settings["options"], name, null) === value)
			{
				return;
			}

			this._settings["options"][name] = value;
		},
		//endregion
		//region D&D
		// D&D for sections moved from root editor entity to column entities
		getDragConfig: function(typeId)
		{
			return BX.prop.getObject(this._dragConfig, typeId, {});
		},
		hasPlaceHolder: function()
		{
			return !!this._dragPlaceHolder;
		},
		createPlaceHolder: function(index)
		{
			var qty = this.getControlCount();
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

			this._dragPlaceHolder = BX.UI.EditorDragSectionPlaceholder.create(
				{
					container: this._formElement,
					anchor: (index < qty) ? this._controls[index].getWrapper() : null,
					index: index
				}
			);

			this._dragPlaceHolder.layout();
			return this._dragPlaceHolder;
		},
		getPlaceHolder: function()
		{
			return this._dragPlaceHolder;
		},
		removePlaceHolder: function()
		{
			if(this._dragPlaceHolder)
			{
				this._dragPlaceHolder.clearLayout();
				this._dragPlaceHolder = null;
			}
		},
		processDraggedItemDrop: function(dragContainer, draggedItem)
		{
			var containerCharge = dragContainer.getCharge();
			if(!((containerCharge instanceof BX.UI.EditorSectionDragContainer) && containerCharge.getEditor() === this))
			{
				return;
			}

			var context = draggedItem.getContextData();
			var contextId = BX.type.isNotEmptyString(context["contextId"]) ? context["contextId"] : "";
			if(contextId !== BX.UI.EditorSectionDragItem.contextId)
			{
				return;
			}

			var itemCharge = typeof(context["charge"]) !== "undefined" ?  context["charge"] : null;
			if(!(itemCharge instanceof BX.UI.EditorSectionDragItem))
			{
				return;
			}

			var control = itemCharge.getControl();
			if(!control)
			{
				return;
			}

			var currentIndex = this.getControlIndex(control);
			if(currentIndex < 0)
			{
				return;
			}

			var placeholder = this.getPlaceHolder();
			var placeholderIndex = placeholder ? placeholder.getIndex() : -1;
			if(placeholderIndex < 0)
			{
				return;
			}

			var index = placeholderIndex <= currentIndex ? placeholderIndex : (placeholderIndex - 1);
			if(index !== currentIndex)
			{
				this.moveControl(control, index);
				this.saveScheme();
			}
		},
		onDrop: function(event)
		{
			this.processDraggedItemDrop(event.data["dropContainer"], event.data["draggedItem"]);
		},
		//endregion
		getConfigScope: function()
		{
			return this._config.getScope();
		},
		prepareFieldLayoutOptions: function(field)
		{
			var hasContent = field.hasContentToDisplay();
			var result = { isNeedToDisplay: (hasContent || this._showEmptyFields) };
			if(this.isExternalLayoutResolversEnabled())
			{
				var eventArgs =
					{
						id: this._id,
						field: field,
						hasContent: hasContent,
						showEmptyFields: this._showEmptyFields,
						layoutOptions: result
					};

				BX.onCustomEvent(
					window,
					this.eventsNamespace + ":onResolveFieldLayoutOptions",
					[ this, eventArgs ]
				);
			}
			return result;
		},
		isExternalLayoutResolversEnabled: function()
		{
			return !!this._enableExternalLayoutResolvers;
		},
		getRestriction: function(id)
		{
			return BX.prop.getObject(this._restrictions, id, null);
		}
	};
	BX.UI.EntityEditor.defaultInstance = null;
	BX.UI.EntityEditor.items = {};
	BX.UI.EntityEditor.get = function(id)
	{
		return this.items.hasOwnProperty(id) ? this.items[id] : null;
	};
	if(typeof(BX.UI.EntityEditor.messages) === "undefined")
	{
		BX.UI.EntityEditor.messages = {};
	}
	BX.UI.EntityEditor.setDefault = function(instance)
	{
		BX.UI.EntityEditor.defaultInstance = instance;
	};
	BX.UI.EntityEditor.getDefault = function()
	{
		return BX.UI.EntityEditor.defaultInstance;
	};
	BX.UI.EntityEditor.create = function(id, settings)
	{
		var self = new BX.UI.EntityEditor();
		self.initialize(id, settings);
		this.items[self.getId()] = self;
		return self;
	};
}
//endregion

//region ENTITY EDITOR MODE QUEUE
if(typeof BX.UI.EntityEditorModeQueue === "undefined")
{
	BX.UI.EntityEditorModeQueue = function()
	{
		this._id = "";
		this._settings = {};
		this._items = [];
	};
	BX.UI.EntityEditorModeQueue.prototype =
		{
			initialize: function(id, settings)
			{
				this._id = BX.type.isNotEmptyString(id) ? id : BX.util.getRandomString(4);
				this._settings = settings ? settings : {};
			},
			findIndex: function(control)
			{
				for(var i = 0, length = this._items.length; i < length; i++)
				{
					if(this._items[i]["control"] === control)
					{
						return i;
					}
				}
				return -1;
			},
			getLength: function ()
			{
				return this._items.length;
			},
			add: function(control, mode, options)
			{
				if(typeof(options) === "undefined")
				{
					options = BX.UI.EntityEditorModeOptions.none;
				}
				var index = this.findIndex(control);
				if(index >= 0)
				{
					this._items[index] = { control: control, mode: mode, options: options };
				}
				else
				{
					this._items.push({ control: control, mode: mode, options: options });
				}
			},
			addBatch: function(controls, mode, options)
			{
				for(var i = 0, length = controls.length; i < length; i++)
				{
					this.add(controls[i], mode, options);
				}
			},
			remove: function(control)
			{
				var index = this.findIndex(control);
				if(index >= 0)
				{
					this._items.splice(index, 1)
				}
			},
			clear: function()
			{
				this._items = [];
			},
			process: function()
			{
				var length = this._items.length;
				if(length === 0)
				{
					return 0;
				}

				for(var i = 0; i < length; i++)
				{
					var item = this._items[i];
					item["control"].setMode(item["mode"], { options: item["options"], notify: true });
				}

				return length;
			}
		};
	BX.UI.EntityEditorModeQueue.create = function(id, settings)
	{
		var self = new BX.UI.EntityEditorModeQueue();
		self.initialize(id, settings);
		return self;
	};
}
//endregion

//region ENTITY EDITOR MODE SWITCH
if(typeof BX.UI.EntityEditorModeSwitch === "undefined")
{
	BX.UI.EntityEditorModeSwitch = function()
	{
		this._id = "";
		this._settings = {};
		this._queue = null;
		this._isRunning = false;
		this._runHandle = 0;

		this._runAction = "";
	};
	BX.UI.EntityEditorModeSwitch.prototype =
		{
			initialize: function(id, settings)
			{
				this._id = BX.type.isNotEmptyString(id) ? id : BX.util.getRandomString(4);
				this._settings = settings ? settings : {};
				this._editor = BX.prop.get(this._settings, "editor");
				this._queue = BX.UI.EntityEditorModeQueue.create(this._id, {});
			},
			getQueue: function()
			{
				return this._queue;
			},
			reset: function()
			{
				this._queue.clear();
				this._isRunning = false;
			},
			isRunning: function()
			{
				return this._isRunning;
			},
			setRunAction: function(action)
			{
				this._runAction = action;
			},
			run: function()
			{
				if(this._isRunning)
				{
					return;
				}

				if(this._runHandle > 0)
				{
					window.clearTimeout(this._runHandle);
				}
				this._runHandle = window.setTimeout(BX.delegate(this.doRun, this), 50);
			},
			doRun: function()
			{
				this._editor.performSaveDelayedAction(this._runAction);

				this._isRunning = true;
				this._runHandle = 0;
			},
			complete: function ()
			{
				this._queue.process();
				this.reset();
			}
		};
	BX.UI.EntityEditorModeSwitch.create = function(id, settings)
	{
		var self = new BX.UI.EntityEditorModeSwitch();
		self.initialize(id, settings);
		return self;
	};
}
//endregion

//region ENTITY EDITOR MODE
if(typeof(BX.UI.EntityEditorScopeConfig) === "undefined")
{
	BX.UI.EntityEditorScopeConfig = function()
	{
		this._id = "";
		this._settings = {};

		this._editor = {};
		this._config = {};
		this._isCommonConfig = false;

		this._popup = null;
		this._selector = null;

		this._name = "";
		this._items = [];

		this._nameInput = {};
		this._usersInput = {};
		this._usersInputTagSelector = {};
		this._forceSetInput = {};
		this._nameInputError = null;
		this._usersInputError = null;

		this._entityId = "";
		this._entityTypeId = '';

		this._isOpened = false;
		this._closeNotifier = null;

		this.moduleId = null;

		this._onSquareClick = BX.delegate(this.onSquareClick, this);
	};

	BX.UI.EntityEditorScopeConfig.prototype =
		{
			initialize: function(id, settings)
			{
				this._id = BX.type.isNotEmptyString(id) ? id : BX.util.getRandomString(4);
				this._settings = settings ? settings : {};

				this._editor = this.getSetting('editor', {});
				this._config = this.getSetting('config', {});
				this._isCommonConfig = this.getSetting('isCommonConfig', false);

				this._name = this.getSetting('name', '');
				this._items = this.getSetting('items', []);

				this._entityId = this.getSetting('entityId', null);

				this._entityTypeId = this.getSetting('entityTypeId', null);
				this.moduleId = this.getSetting('moduleId', null);
			},
			getId: function()
			{
				return this._id;
			},
			getSetting: function(name, defaultValue)
			{
				return (this._settings.hasOwnProperty(name) ? this._settings[name] : defaultValue);
			},
			isOpened: function()
			{
				return this._isOpened;
			},
			open: function()
			{
				if(this._isOpened)
				{
					return;
				}

				this._popup = this.createPopup();

				this._popup.show();
			},
			createPopup: function()
			{
				return (this._popup || new BX.PopupWindow(this._id, null, {
					className: 'ui-entity-editor-content-user-scope-popup',
					titleBar: BX.message('UI_ENTITY_EDITOR_CREATE_SCOPE'),
					closeIcon : true,
					autoHide: false,
					closeByEsc: true,
					padding: 0,
					contentPadding: 0,
					contentBackground: 'none',
					draggable: true,
					minWidth: 550,
					maxWidth: 550,
					content: this.prepareContent(),
					buttons: this.prepareButtons(),
					events:
						{
							onPopupShow: BX.delegate(this.onPopupShow, this),
							onPopupClose: BX.delegate(this.onPopupClose, this),
							onPopupDestroy: BX.delegate(this.onPopupDestroy, this)
						},
				}));
			},
			prepareContent: function()
			{
				var container = BX.create('div', {
					style: {
						padding: '0 20px',
					}
				});
				container.appendChild(this.prepareNameControl());
				container.appendChild(this.prepareUserSelectControl());
				container.appendChild(this.prepareForceSetToUsersControl());

				return container;
			},
			prepareNameControl: function()
			{
				var container = BX.create('div', {
					style: {
						paddingBottom: '25px',
						marginBottom: '20px',
						borderBottom: '1px solid #f2f2f4'
					}
				});
				container.appendChild(BX.create('div', {
					props: {
						className: 'ui-ctl-label-text'
					},
					text: BX.message('UI_ENTITY_EDITOR_CONFIG_SCOPE_NAME')
				}));

				var control = BX.create('div', {
					props:{
						className: 'ui-ctl ui-ctl-textbox ui-ctl-w100'
					}
				});

				this._nameInput = BX.create("input", {
					props:{
						className: 'ui-ctl-element',
						value: this.getName(),
						type: 'text',
						placeholder: BX.message('UI_ENTITY_EDITOR_CONFIG_SCOPE_NAME_PLACEHOLDER')
					}
				});

				control.appendChild(this._nameInput);
				container.appendChild(control);
				return container;
			},
			prepareUserSelectControl: function()
			{
				var container = BX.create('div', {
					style: {
						paddingBottom: '25px',
						marginBottom: '10px',
						borderBottom: '1px solid #f2f2f4'
					}
				});
				container.appendChild(BX.create('div', {
					props: {
						className: 'ui-ctl-label-text'
					},
					text: BX.message('UI_ENTITY_EDITOR_CONFIG_SCOPE_MEMBERS')
				}));

				var control = BX.create('div', {
					props:{
						className: 'ui-ctl ui-ctl-textbox ui-ctl-w100'
					}
				});

				this._usersInput = BX.create("div", {
					style:{
						width: '100%'
					},
					attrs: {
						id: 'user-selector-item',
					},
				});

				control.appendChild(this._usersInput);
				container.appendChild(control);

				this._usersInputTagSelector = new BX.UI.EntitySelector.TagSelector({
					dialogOptions: {
						context: 'UI_ENTITY_EDITOR_SCOPE',
						entities: [
							{
								id: 'user',
							},
							{
								id: 'project',
							},
							{
								id: 'department',
								options: {
									selectMode: 'usersAndDepartments'
								}
							},
						],
					}
				});

				this._usersInputTagSelector.renderTo(this._usersInput);

				return container;
			},
			prepareForceSetToUsersControl: function()
			{
				var container = BX.create('div', {
					style: {
						paddingBottom: '10px',
						borderBottom: '1px solid #f2f2f4'
					}
				});

				var control = BX.create('div', {
					props:{
						className: 'ui-ctl ui-ctl-checkbox ui-ctl-w100'
					}
				});

				this._forceSetInput = BX.create("input", {
					props:{
						className: 'ui-ctl-element',
						type: 'checkbox',
						checked: true
					},
				});

				control.appendChild(this._forceSetInput);
				control.appendChild(BX.create('div', {
					props:{
						className: 'ui-ctl-label-text',
					},
					text: BX.message('UI_ENTITY_EDITOR_CONFIG_SCOPE_FORCE_INSTALL_TO_USERS')
				}));

				container.appendChild(control);

				return container;
			},
			prepareButtons: function()
			{
				return [
					new BX.UI.Button({
						text: BX.message('UI_ENTITY_EDITOR_CONFIG_SCOPE_SAVE'),
						tag: BX.UI.Button.Tag.LINK,
						color: BX.UI.Button.Color.PRIMARY,
						events: {
							click: function(params, event) {
								event.preventDefault();
								this.processSave();
							}.bind(this)
						}
					}),
					new BX.UI.Button({
						text: BX.message('UI_ENTITY_EDITOR_CONFIG_SCOPE_CANCEL'),
						tag: BX.UI.Button.Tag.LINK,
						color: BX.UI.Button.Color.LINK,
						events: {
							click: function(params, event) {
								event.preventDefault();
								this.processCancel();
							}.bind(this)
						}
					})
				];
			},
			close: function()
			{
				if (this._popup)
				{
					this._popup.close();
				}
			},
			addCloseListener: function(listener)
			{
				this._closeNotifier.addListener(listener);
			},
			removeCloseListener: function(listener)
			{
				this._closeNotifier.removeListener(listener);
			},
			createUserInfo: function(item)
			{
				return {
					ID: item.id,
					FORMATTED_NAME: BX.util.htmlspecialcharsback(BX.prop.getString(item, 'name', '')),
				};
			},
			isCustomized: function()
			{
				var accessCodes = BX.prop.getObject(this._config, 'accessCodes', []);
				return !!Object.keys(accessCodes).length;
			},
			getName: function()
			{
				return this._name;
			},
			setName: function(name)
			{
				this._name = name;
			},
			processSave: function()
			{
				this.clearErrors();
				this.setName(this._nameInput.value);

				BX.ajax.runComponentAction(
					'bitrix:ui.form.config',
					'save',
					{
						data: {
							moduleId: this.moduleId,
							entityTypeId: this._entityTypeId,
							name: this.getName(),
							accessCodes: this.getSelectedItems(),
							config: this._config,
							params: {
								forceSetToUsers: this._forceSetInput.checked,
								categoryName: this._editor._config.categoryName,
								common: 'Y',
							}
						}
					}
				)
				.then(
					function(response) {
						this.close();
						BX.UI.EntityEditorScopeConfig.prototype.notifyShow(response);
						var scopeId = parseInt(response.data, 10);
						this._editor.setConfigScope(BX.UI.EntityConfigScope.custom, scopeId);
					}.bind(this)
				).catch(function(response){
					//todo show errors some other way
					this.fillErrors(response.data);
				}.bind(this));
			},
			/**
			 *
			 * @returns {{entityType: string, id: string|number}[]}
			 */
			getSelectedItems: function()
			{
				var items = this._usersInputTagSelector.getTags();
				return items.map(function(item){
					return {
						id: item.id,
						entityId: item.entityId,
					}
				});
			},
			fillErrors: function(errors)
			{
				if (errors.name)
				{
					this._nameInputError = this.createErrorElement(this._nameInput, errors.name.message);
				}
				if (errors.accessCodes)
				{
					this._usersInputError = this.createErrorElement(this._usersInput, errors.accessCodes.message);
				}
			},
			createErrorElement: function(fieldNode, message)
			{
				var errorContainer = BX.create('div', {
					props: {
						className: 'ui-entity-section-control-error-text'
					}
				});
				errorContainer.innerHTML = message;
				fieldNode.parentNode.parentNode.appendChild(errorContainer);

				return errorContainer;
			},
			clearErrors: function()
			{
				if (this._nameInputError)
				{
					this._nameInputError.remove();
				}
				if (this._usersInputError)
				{
					this._usersInputError.remove();
				}
			},
			notifyShow: function(response)
			{
				window.top.BX.UI.Notification.Center.notify({
					content: BX.message('UI_ENTITY_EDITOR_CONFIG_SCOPE_SAVED'),
					width: 'auto',
				});
			},
			processCancel: function()
			{
				this.close();
			},
			onPopupShow: function()
			{
				this._isOpened = true;
			},
			onPopupClose: function()
			{
				if(this._popup)
				{
					this._popup.destroy();
				}
			},
			onPopupDestroy: function()
			{
				this._isOpened = false;
				this._popup = null;
			},
		};

	if(BX.UI.EntityEditorScopeConfig.messages === undefined)
	{
		BX.UI.EntityEditorScopeConfig.messages = {};
	}

	BX.UI.EntityEditorScopeConfig.create = function(id, settings)
	{
		var self = new BX.UI.EntityEditorScopeConfig();
		self.initialize(id, settings);
		return self;
	};
}
//endregion
