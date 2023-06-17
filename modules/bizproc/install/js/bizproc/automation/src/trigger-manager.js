import { Type, Event, Loc, Dom, Text, Uri, ajax } from "main.core";
import { EventEmitter } from "main.core.events";
import { ViewMode } from "./view-mode";
import { Trigger } from "./trigger";
import { Helper } from "./helper";
import { Designer } from "./designer";
import { getGlobalContext, ConditionGroup, ConditionGroupSelector } from "bizproc.automation";
import { Alert, AlertColor, AlertIcon } from "ui.alerts";

export class TriggerManager extends EventEmitter
{
	#triggersContainerNode: HTMLElement;
	#viewMode: ViewMode;
	#triggers: Array<Trigger>;
	#triggersData: Array<Object<string, any>>;
	#columnNodes: NodeList;
	#listNodes: NodeList;
	#modified: boolean;

	constructor(triggersContainerNode: HTMLElement)
	{
		super();
		this.setEventNamespace('BX.Bizproc.Automation');

		this.#triggersContainerNode = triggersContainerNode;
	}

	init(data: ?Object<string, any>, viewMode: ViewMode)
	{
		if (!Type.isPlainObject(data))
		{
			data = {};
		}

		this.#viewMode = viewMode.isNone() ? ViewMode.edit() : viewMode;
		this.#triggersData = Type.isArray(data.TRIGGERS) ? data.TRIGGERS : [];
		this.#columnNodes = document.querySelectorAll('[data-type="column-trigger"]');
		this.#listNodes = this.#triggersContainerNode.querySelectorAll('[data-role="trigger-list"]');
		this.#modified = false;
		this.initTriggers();

		this.markModified(false);

		//register DD
		this.#columnNodes.forEach(columnNode => jsDD.registerDest(columnNode, 10));

		top.BX.addCustomEvent(
			top,
			'Rest:AppLayout:ApplicationInstall',
			this.onRestAppInstall.bind(this)
		);
	}

	reInit(data: ?Object<string, any>, viewMode: ?ViewMode)
	{
		if (!Type.isPlainObject(data))
		{
			data = {};
		}

		this.#viewMode = viewMode || ViewMode.none();
		this.#listNodes.forEach(node => Dom.clean(node));

		this.#triggersData = Type.isArray(data.TRIGGERS) ? data.TRIGGERS : [];

		this.initTriggers();

		this.markModified(false);
	}

	initTriggers()
	{
		this.#triggers = [];
		this.#triggersData.forEach((triggerData) => {
			const trigger = new Trigger();
			trigger.init(triggerData, this.#viewMode);
			this.subscribeTriggerEvents(trigger);
			this.insertTriggerNode(trigger.getStatusId(), trigger.node);
			this.#triggers.push(trigger);
		});
	}

	subscribeTriggerEvents(trigger: Trigger)
	{
		trigger.subscribe('Trigger:copied', (event) => {
			const trigger = event.data.trigger;

			this.#triggers.push(trigger);
			if (!event.data.skipInsert)
			{
				this.insertTriggerNode(trigger.getStatusId(), trigger.node);
			}
			this.subscribeTriggerEvents(trigger);
			this.markModified();
		});
		trigger.subscribe('Trigger:modified', () => this.markModified());
		trigger.subscribe('Trigger:onSettingsOpen', (event) => {
			this.openTriggerSettingsDialog(event.data.trigger);
		});
		trigger.subscribe('Trigger:deleted', (event) => this.deleteTrigger(event.data.trigger));

	}

	onSearch(event)
	{
		this.#triggers.forEach(trigger => trigger.onSearch(event));
	}

	enableManageMode()
	{
		this.#viewMode = ViewMode.manage();
		const deleteButtons = document.querySelectorAll('[data-role="btn-delete-trigger"]');
		deleteButtons.forEach(node => Dom.hide(node));

		this.#triggers.forEach(trigger => Dom.addClass(trigger.node, '--locked-node'));
	}

	disableManageMode()
	{
		this.#viewMode = ViewMode.edit();
		const deleteButtons = document.querySelectorAll('[data-role="btn-delete-trigger"]');
		deleteButtons.forEach(node => Dom.show(node));

		this.#triggers.forEach(trigger => Dom.removeClass(trigger.node, '--locked-node'));
	}

	addTrigger(triggerData: ?Object<string, any>, callback)
	{
		const trigger = new Trigger();
		trigger.draft = true;
		trigger.init(triggerData, this.#viewMode);
		this.subscribeTriggerEvents(trigger);
		if (callback)
		{
			callback.call(this, trigger);
		}
		this.emit('TriggerManager:trigger:add', {trigger});
	}

	deleteTrigger(trigger: Trigger, callback)
	{
		if (trigger.getId() > 0)
		{
			trigger.markDeleted();
		}
		else
		{
			for(let i = 0; i < this.#triggers.length; ++i)
			{
				if (this.#triggers[i] === trigger)
				{
					this.#triggers.splice(i, 1);
				}
			}
		}

		if (callback)
		{
			callback(trigger);
		}

		this.emit('TriggerManager:trigger:delete', { trigger });

		this.markModified();
	}

	enableDragAndDrop()
	{
		this.#triggers.forEach(trigger => trigger.registerItem(trigger.node));
		this.#triggersContainerNode.querySelectorAll('.bizproc-automation-trigger-item-wrapper').forEach((node) => {
			Dom.addClass(node, 'bizproc-automation-trigger-item-wrapper-draggable');
		});
	}

	disableDragAndDrop()
	{
		this.#triggers.forEach(trigger => trigger.unregisterItem(trigger.node));
		this.#triggersContainerNode.querySelectorAll('.bizproc-automation-trigger-item-wrapper').forEach((node) => {
			Dom.removeClass(node, 'bizproc-automation-trigger-item-wrapper-draggable');
		});
	}

	insertTrigger(trigger)
	{
		this.#triggers.push(trigger);
		this.markModified(true);
	}

	insertTriggerNode(documentStatus: string, triggerNode)
	{
		const listNode = this.#triggersContainerNode.querySelector('[data-role="trigger-list"][data-status-id="'+documentStatus+'"]');
		if (listNode)
		{
			Dom.append(triggerNode, listNode);
		}
	}

	serialize(): Array<Object<string, any>>
	{
		return this.#triggers.map(trigger => trigger.serialize());
	}

	countAllTriggers(): number
	{
		return this.#triggers.filter(trigger => !trigger.deleted).length;
	}

	getTriggerName(code: string)
	{
		return getGlobalContext().availableTriggers.find((trigger) => code === trigger['CODE'])?.NAME ?? code;
	}

	getAvailableTrigger(code): ?Object
	{
		const availableTriggers = getGlobalContext().availableTriggers;
		for (let i = 0; i < availableTriggers.length; ++i)
		{
			if (code === availableTriggers[i]['CODE'])
			{
				return availableTriggers[i];
			}
		}

		return null;
	}

	canEdit(): boolean
	{
		return getGlobalContext().canEdit;
	}

	canSetExecuteBy(): boolean
	{
		return getGlobalContext().get('TRIGGER_CAN_SET_EXECUTE_BY') ?? false;
	}

	needSave()
	{
		return this.#modified;
	}

	markModified(modified: boolean)
	{
		this.#modified = modified !== false;
		if (this.#modified)
		{
			this.emit('TriggerManager:dataModified');
		}
	}

	openTriggerSettingsDialog(trigger, context)
	{
		if (Designer.getInstance().getTriggerSettingsDialog())
		{
			if (context && context.changeTrigger)
			{
				Designer.getInstance().getTriggerSettingsDialog().popup.close();
			}
			else
			{
				return;
			}
		}

		const formName = 'bizproc_automation_trigger_dialog';
		const form = Dom.create('form', {
			props: {
				name: formName
			},
			style: {"min-width": '540px'}
		});

		Dom.append(this.renderConditionSettings(trigger), form);

		const iconHelp = Dom.create('div', {
			attrs: { className: 'bizproc-automation-robot-help' },
			events: {click: (event) => this.emit('TriggerManager:onHelpClick', event)}
		});
		Dom.append(iconHelp, form);

		const title = this.getTriggerName(trigger.getCode());

		Dom.append(Dom.create("span", {
			attrs: { className: "bizproc-automation-popup-settings-title bizproc-automation-popup-settings-title-autocomplete" },
			text: Loc.getMessage('BIZPROC_AUTOMATION_CMP_TRIGGER_NAME') + ':'
		}), form);

		Dom.append(Dom.create("div", {
			attrs: { className: "bizproc-automation-popup-settings" },
			children: [
				Dom.create("input", {
					attrs: {
						className: 'bizproc-automation-popup-input',
						type: "text",
						name: "name",
						value: trigger.getName() || title
					}
				}),
			],
		}), form);

		const triggerData = this.getAvailableTrigger(trigger.getCode());

		if (triggerData && triggerData['SETTINGS'])
		{
			this.#renderTriggerProperties(trigger, triggerData['SETTINGS']['Properties'], form);
		}

		BX.onCustomEvent(
			'BX.Bizproc.Automation.TriggerManager:onOpenSettingsDialog-' + trigger.getCode(),
			[trigger, form]
		);

		if (this.canSetExecuteBy())
		{
			this.renderExecuteByControl(trigger, form);
		}

		this.renderAllowBackwardsControl(trigger, form);

		Dom.addClass(this.#triggersContainerNode, 'automation-base-blocked');

		Designer.getInstance().setTriggerSettingsDialog({
			triggerManager: this,
			trigger: trigger,
			form: form
		});

		const self = this;
		const popup = new BX.PopupWindow(Helper.generateUniqueId(), null, {
			titleBar: title,
			content: form,
			closeIcon: true,
			offsetLeft: 0,
			offsetTop: 0,
			closeByEsc: true,
			draggable: {restrict: false},
			overlay: false,
			events: {
				onPopupClose: function(popup)
				{
					Designer.getInstance().setTriggerSettingsDialog(null);
					self.destroySettingsDialogControls();
					popup.destroy();
					Dom.removeClass(self.#triggersContainerNode, 'automation-base-blocked');
					self.emit('TriggerManager:onCloseTriggerSettingsDialog')
				}
			},
			buttons: [
				new BX.PopupWindowButton({
					text : Loc.getMessage('JS_CORE_WINDOW_SAVE'),
					className : "popup-window-button-accept",
					events : {
						click: () => {
							const formData = BX.ajax.prepareForm(form);
							trigger.setName(formData['data']['name']);

							if (triggerData['SETTINGS'])
							{
								this.#setTriggerProperties(trigger, triggerData['SETTINGS']['Properties'], form);
							}

							BX.onCustomEvent(
								'BX.Bizproc.Automation.TriggerManager:onSaveSettings-'+trigger.getCode(),
								[trigger, formData]
							);

							self.setConditionSettingsFromForm(formData['data'], trigger);
							trigger.setAllowBackwards(formData['data']['allow_backwards'] === 'Y');

							if (self.canSetExecuteBy())
							{
								trigger.setExecuteBy(formData['data']['execute_by']);
							}

							if (trigger.draft)
							{
								// remove orange/yellow color

								//self.#triggers.push(trigger);
								//self.insertTriggerNode(trigger.getStatusId(), trigger.node)
							}

							//analytics
							ajax.runAction(
								'bizproc.analytics.push',
								{
									analyticsLabel: `automation_trigger${trigger.draft ? '_draft' : ''}_save_${trigger.getCode().toLowerCase()}`
								}
							);

							delete trigger.draft;

							trigger.reInit();
							self.markModified();
							popup.close();
						}
					}
				}),
				new BX.PopupWindowButtonLink({
					text : Loc.getMessage('JS_CORE_WINDOW_CANCEL'),
					className : "popup-window-button-link-cancel",
					events : {
						click()
						{
							this.popupWindow.close();
						}
					}
				})
			]
		});

		Designer.getInstance().getTriggerSettingsDialog().popup = popup;
		popup.show();

		//analytics
		ajax.runAction(
			'bizproc.analytics.push',
			{
				analyticsLabel: `automation_trigger${trigger.draft ? '_draft' : ''}_settings_${trigger.getCode().toLowerCase()}`
			}
		);
	}

	#renderTriggerProperties(trigger: Trigger, properties: [], form: Element)
	{
		properties.forEach((property) => {

			const value = trigger.getApplyRules()[property.Id];

			if (property.Type === '@condition-group-selector')
			{
				this.#renderConditionGroupSelector(property, value, form);

				return;
			}

			if (property.Type === '@webhook-code')
			{
				this.#renderWebhookCodeProperty(property, value, form);

				return;
			}

			if (property.Type === '@field-selector')
			{
				this.#renderFieldSelectorProperty(property, value, form);

				return;
			}

			const toRenderProperty = {AllowSelection: false, ...property};

			if (toRenderProperty.Type === '@robot-select')
			{
				this.#prepareRobotSelectProperty(toRenderProperty);
			}

			Dom.append(Dom.create("span", {
				attrs: {
					className: "bizproc-automation-popup-settings-title bizproc-automation-popup-settings-title-top bizproc-automation-popup-settings-title-autocomplete",
				},
				text: property.Name + ':',
			}), form);

			Dom.append(Dom.create("div", {
				attrs: { className: "bizproc-automation-popup-settings" },
				children: [
					BX.Bizproc.FieldType.renderControl(
						[
							...getGlobalContext().document.getRawType(),
							getGlobalContext().document.getCategoryId(),
						],
						toRenderProperty,
						property.Id,
						value || ''
					)
				],
			}), form);
		});
	}

	#prepareRobotSelectProperty(property)
	{
		const cmp = Designer.getInstance().component;
		property.Options = [];
		const filter = property.Settings.Filter;
		const check = (robot) =>
		{
			for (const field in filter)
			{
				if (robot.data[field] !== filter[field])
				{
					return false;
				}
			}

			return true;
		};

		cmp.templateManager.templates.forEach((template) => {
			template.robots.forEach((robot) => {
				if (check(robot))
				{
					property.Options.push(
						{value: robot.getId(), name: robot.getProperty(property.Settings.OptionNameProperty)}
					);
				}
			});
		});

		delete property.Settings;
		property.Type = 'select';
	}

	#setTriggerProperties(trigger: Trigger, properties: [], form: Element)
	{
		const values = {};

		properties.forEach((property) => {

			if (property.Type === '@condition-group-selector')
			{
				values[property.Id] = this.#setConditionGroupValue(property, form);

				return;
			}

			const formData = BX.ajax.prepareForm(form);
			values[property.Id] = formData.data[property.Id];
		});

		trigger.setApplyRules(values);
	}

	renderConditionSettings(trigger: Trigger)
	{
		const conditionGroup = trigger.getCondition().clone();

		this.conditionSelector = new ConditionGroupSelector(
			conditionGroup,
			{
				fields: getGlobalContext().document.getFields(),
				showValuesSelector: false,
			},
		);
		const selector = this.conditionSelector;

		return Dom.create("div", {
			attrs: { className: "bizproc-automation-popup-settings" },
			children: [
				Dom.create("div", {
					attrs: { className: "bizproc-automation-popup-settings-block" },
					children: [
						Dom.create("span", {
							attrs: { className: "bizproc-automation-popup-settings-title" },
							text: Loc.getMessage('BIZPROC_AUTOMATION_ROBOT_CONDITION') + ":",
						}),
						selector.createNode(),
					]
				})
			]
		});
	}

	#renderConditionGroupSelector(property, value, form)
	{
		const selector = new ConditionGroupSelector(new ConditionGroup(value), {
			fields: property.Settings.Fields,
			fieldPrefix: property.Id,
			showValuesSelector: false,
		});

		Dom.append(
			Dom.create("div", {
				attrs: { className: "bizproc-automation-popup-settings" },
				children: [
					Dom.create("div", {
						attrs: { className: "bizproc-automation-popup-settings-block" },
						children: [
							Dom.create("span", {
								attrs: { className: "bizproc-automation-popup-settings-title" },
								text: property.Name + ":"
							}),
							selector.createNode()
						]
					})
				],
			}),
			form
		);
	}

	#setConditionGroupValue(property, form)
	{
		const formData = BX.ajax.prepareForm(form);
		const conditionGroup = ConditionGroup.createFromForm(
			formData['data'],
			property.Id
		);

		return conditionGroup.serialize();
	}

	#renderWebhookCodeProperty(property, value, form)
	{
		if (!value)
		{
			value = Text.getRandom(5);
		}

		Dom.append(Dom.create("span", {
			attrs: { className: "bizproc-automation-popup-settings-title bizproc-automation-popup-settings-title-autocomplete" },
			text: property.Name + ':'
		}), form);

		Dom.append(Dom.create('input', {
			props: {
				type: 'hidden',
				value: value,
				name: 'code'
			}
		}), form);

		const hookLinkTextarea = Dom.create("textarea", {
			attrs: {
				className: "bizproc-automation-popup-textarea",
				placeholder: "...",
				readonly: 'readonly',
				name: 'webhook_handler'
			},
			events: {
				click()
				{
					this.select();
				}
			}
		});

		Dom.append(Dom.create("div", {
			attrs: { className: "bizproc-automation-popup-settings" },
			children: [hookLinkTextarea]
		}), form);

		Dom.append(Dom.create("span", {
			attrs: { className: "bizproc-automation-popup-settings-title bizproc-automation-popup-settings-title-autocomplete" },
			text: Loc.getMessage('BIZPROC_AUTOMATION_CMP_WEBHOOK_ID'),
		}), form);

		if (property.Settings.Handler)
		{
			let url = window.location.protocol + '//' + window.location.host + property.Settings.Handler;
			url = Uri.addParam(url, {code: value});
			url = url.replace('{{DOCUMENT_TYPE}}', getGlobalContext().document.getRawType()[2]);
			url = url.replace('{{USER_ID}}', Loc.getMessage('USER_ID'));

			if (property.Settings.Password)
			{
				url = url.replace('{{PASSWORD}}', property.Settings.Password);
			}

			hookLinkTextarea.value = url;
		}

		if (!property.Settings.Password && property.Settings.PasswordLoader)
		{
			const myAlertText =
				Loc.getMessage('BIZPROC_AUTOMATION_WEBHOOK_PASSWORD_ALERT')
					.replace(
						'#A1#',
						'<a class="bizproc-automation-popup-settings-link '
						+ 'bizproc-automation-popup-settings-link-light" data-role="token-gen">'
					)
					.replace('#A2#', '</a>')
			;

			const passwordAlert = new Alert({
				color: AlertColor.WARNING,
				icon: AlertIcon.WARNING,
				text: myAlertText
			});

			Event.bind(
				passwordAlert.getTextContainer().querySelector('[data-role="token-gen"]'),
				'click',
				() =>
				{
					const loaderConfig = property.Settings.PasswordLoader;

					BX.ajax.runComponentAction(
						loaderConfig.component,
						loaderConfig.action,
						{
							mode: loaderConfig.mode || undefined,
							data: {
								documentType: [
									...getGlobalContext().document.getRawType(),
									getGlobalContext().document.getCategoryId(),
								],
							}
						}
					).then(
						function(response)
						{
							if (response.data.error)
							{
								window.alert(response.data.error);
							}
							else if (response.data.password)
							{
								property.Settings.Password = response.data.password;
								hookLinkTextarea.value = hookLinkTextarea
									.value.replace('{{PASSWORD}}', property.Settings.Password)
								;
								passwordAlert.handleCloseBtnClick();
							}
						}
					);
				}
			);

			Dom.append(passwordAlert.getContainer(), form);
		}
	}

	#renderFieldSelectorProperty(property, value, form)
	{
		const menuId = '@field-selector' + Math.random();
		const fieldName = property.Id + '[]';

		const fieldsList = property.Settings.Fields;

		const renderFieldCheckbox = function(field, listNode)
		{
			const exists = listNode.querySelector('[data-field="' + field['Id'] + '"]');
			if (exists)
			{
				return;
			}

			Dom.append(
				Dom.create(
					'div',
					{
						attrs: {
							className: 'bizproc-automation-popup-checkbox-item',
							'data-field': field['Id']
						},
						children: [
							Dom.create(
								'label',
								{
									attrs: {
										className: 'bizproc-automation-popup-chk-label'
									},
									children: [
										Dom.create(
											'input', {
												attrs: {
													className: 'bizproc-automation-popup-chk',
													type: 'checkbox',
													name: fieldName,
													value: field['Id'],
												},
												props: {
													checked: true
												}
											}
										),
										document.createTextNode(field['Name']),
									]
								}
							),
						]
					}
				),
				listNode
			);
		}

		const fieldSelectorHandler = function(targetNode, listNode)
		{
			if (BX.Main.MenuManager.getMenuById(menuId))
			{
				return BX.Main.MenuManager.getMenuById(menuId).show();
			}

			const menuItems = [];

			fieldsList.forEach(function(field)
			{
				menuItems.push({
					text: BX.Text.encode(field['Name']),
					field: field,
					onclick: function(event, item)
					{
						renderFieldCheckbox(item.field, listNode);
						this.popupWindow.close();
					}
				});
			});

			BX.Main.MenuManager.show(
				menuId,
				targetNode,
				menuItems,
				{
					autoHide: true,
					offsetLeft: (BX.pos(this)['width'] / 2),
					angle: { position: 'top', offset: 0 },
					zIndex: 200,
					className: 'bizproc-automation-inline-selector-menu',
					events: {
						onPopupClose: function(popup)
						{
							popup.destroy();
						}
					}
				}
			);
		}

		Dom.append(
			Dom.create(
				'span',
				{
					attrs: {
						className: "bizproc-automation-popup-settings-title bizproc-automation-popup-settings-title-autocomplete"
					},
					text: property.Name + ':',
				}
			),
			form
		);

		const fieldListNode = Dom.create(
			'div',
			{
				attrs: {
					className: 'bizproc-automation-popup-checkbox',
				},
				children: [],
			}
		);
		Dom.append(fieldListNode, form);

		Dom.append(
			Dom.create(
				'div',
				{
					attrs: {
						className: 'bizproc-automation-popup-settings bizproc-automation-popup-settings-text',
					},
					children: [
						Dom.create(
							'span',
							{
								attrs: {
									className: "bizproc-automation-popup-settings-link"
								},
								text: property.Settings.ChooseFieldLabel,
								events: {
									click: function()
									{
										fieldSelectorHandler(this, fieldListNode);
									}
								}
							}
						),
					]
				}
			),
			form
		);

		if (Type.isArray(value))
		{
			value.forEach(function(field)
			{
				const foundField = fieldsList.find((fld) => fld.Id === field);
				if (foundField)
				{
					renderFieldCheckbox(foundField, fieldListNode);
				}
			});
		}
	}

	renderExecuteByControl(trigger, form)
	{
		Dom.append(Dom.create("span", {
			attrs: {
				className: "bizproc-automation-popup-settings-title bizproc-automation-popup-settings-title-top bizproc-automation-popup-settings-title-autocomplete",
			},
			text: Loc.getMessage('BIZPROC_AUTOMATION_CMP_TRIGGER_EXECUTE_BY') + ':',
		}), form);

		Dom.append(Dom.create("div", {
			attrs: { className: "bizproc-automation-popup-settings" },
			children: [
				BX.Bizproc.FieldType.renderControl(
					getGlobalContext().document.getRawType(),
					{
						Type: 'user'
					},
					'execute_by',
					trigger.draft
						? Helper.getResponsibleUserExpression(getGlobalContext().document.getFields())
						: trigger.getExecuteBy()
				),
			],
		}), form);
	}

	renderAllowBackwardsControl(trigger, form)
	{
		Dom.append(Dom.create("div", {
			attrs: { className: "bizproc-automation-popup-checkbox" },
			children: [
				Dom.create("div", {
					attrs: { className: "bizproc-automation-popup-checkbox-item" },
					children: [
						Dom.create("label", {
							attrs: { className: "bizproc-automation-popup-chk-label" },
							children: [
								Dom.create("input", {
									attrs: {
										className: 'bizproc-automation-popup-chk',
										type: "checkbox",
										name: "allow_backwards",
										value: 'Y'
									},
									props: {
										checked: trigger.isBackwardsAllowed()
									}
								}),
								document.createTextNode(Loc.getMessage('BIZPROC_AUTOMATION_CMP_TRIGGER_ALLOW_REVERSE')),
							],
						}),
					],
				}),
			],
		}), form);
	}

	setConditionSettingsFromForm(formFields: Object,  trigger: Trigger): this
	{
		trigger.setCondition(ConditionGroup.createFromForm(formFields));

		return this;
	}

	onRestAppInstall(installed, eventResult)
	{
		eventResult.redirect = false;
		const self = this;

		setTimeout(function()
		{
			BX.ajax({
				method: 'POST',
				dataType: 'json',
				url: getGlobalContext().ajaxUrl,
				data: {
					ajax_action: 'get_available_triggers',
					document_signed: getGlobalContext().signedDocument
				},
				onsuccess(response)
				{
					if (Type.isArray(response['DATA']))
					{
						getGlobalContext().set('availableTriggers', response['DATA']);
					}
				}
			});
		}, 1500);
	}

	initSettingsDialogControls(node)
	{
		if (!Type.isArray(this.settingsDialogControls))
		{
			this.settingsDialogControls = [];
		}

		const controlNodes = node.querySelectorAll('[data-role]');
		for (let i = 0; i < controlNodes.length; ++i)
		{
			let control = null;
			const role = controlNodes[i].getAttribute('data-role');

			if (role === 'user-selector')
			{
				control = BX.Bizproc.UserSelector.decorateNode(controlNodes[i]);
			}

			BX.UI.Hint.init(controlNodes[i]);

			if (control)
			{
				this.settingsDialogControls.push(control);
			}
		}
	}

	destroySettingsDialogControls()
	{
		if (this.conditionSelector)
		{
			this.conditionSelector.destroy();
			this.conditionSelector = null;
		}

		if (Type.isArray(this.settingsDialogControls))
		{
			for (let i = 0; i < this.settingsDialogControls.length; ++i)
			{
				if (Type.isFunction(this.settingsDialogControls[i].destroy))
				{
					this.settingsDialogControls[i].destroy();
				}
			}
		}
		this.settingsDialogControls = null;
	}

	getListByDocumentStatus(statusId): Array<Trigger>
	{
		const result = [];
		this.#triggers.forEach((trigger) => {
			if (trigger.getStatusId() === statusId)
			{
				result.push(trigger);
			}
		});

		return result;
	}

	getReturnProperties(statusId): Array<Object>
	{
		const result = [];
		const exists = {};
		const triggers = this.getListByDocumentStatus(statusId);

		triggers.forEach((trigger) => {
			const props = trigger.deleted ? [] : trigger.getReturnProperties();
			if (props.length)
			{
				props.forEach((property) => {
					if (!exists[property.Id])
					{
						result.push({
							Id: property.Id,
							ObjectId: 'Template',
							Name: property.Name,
							ObjectName: trigger.getName(),
							Type: property.Type,
							Expression: '{{~*:'+property.Id+'}}',
							SystemExpression: '{=Template:'+property.Id+'}'
						});
						exists[property.Id] = true;
					}
				});
			}
		});

		return result;
	}

	getReturnProperty(statusId, propertyId): ?Object
	{
		const properties = this.getReturnProperties(statusId);
		for (let i = 0; i < properties.length; ++i)
		{
			if (properties[i].Id === propertyId)
			{
				return properties[i];
			}
		}

		return null;
	}
}