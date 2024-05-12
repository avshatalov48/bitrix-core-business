import { Type, Event, Loc, Dom, Text, Uri, ajax, Tag, onCustomEvent } from 'main.core';
import { BaseEvent, EventEmitter } from 'main.core.events';
import { ViewMode } from './view-mode';
import { Trigger } from './trigger';
import { Helper } from './helper';
import { Designer } from './designer';
import { getGlobalContext, ConditionGroup, ConditionGroupSelector, UserOptions } from 'bizproc.automation';
import { Alert, AlertColor, AlertIcon } from 'ui.alerts';
import { Popup, MenuManager } from 'main.popup';
import { SaveButton, CancelButton } from 'ui.buttons';

import 'ui.hint';

export class TriggerManager extends EventEmitter
{
	#triggersContainerNode: HTMLElement;
	#userOptions: ?UserOptions;
	#viewMode: ViewMode;
	#triggers: Array<Trigger>;
	#triggersData: Array<Object<string, any>>;
	#columnNodes: NodeList;
	#listNodes: NodeList;
	#modified: boolean;
	#triggerEventsListeners: Object<string, (event: BaseEvent) => void> = {};

	constructor(triggersContainerNode: HTMLElement, params: { userOptions: ?UserOptions } = {})
	{
		super();
		this.setEventNamespace('BX.Bizproc.Automation');

		this.#triggersContainerNode = triggersContainerNode;
		this.#userOptions = params.userOptions;
	}

	async fetchTriggers()
	{
		const self = this;

		return new Promise((resolve, reject) => ajax({
			method: 'POST',
			dataType: 'json',
			url: getGlobalContext().ajaxUrl,
			data: {
				ajax_action: 'get_triggers',
				document_signed: getGlobalContext().signedDocument,
			},
			onsuccess(response)
			{
				if (response.SUCCESS)
				{
					self.reInit({ TRIGGERS: response.DATA.triggers }, self.#viewMode);
					resolve();
				}
				else
				{
					reject();
				}
			},
			onerror()
			{
				reject();
			},
		}));
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

		// register DD
		this.#columnNodes.forEach((columnNode) => jsDD.registerDest(columnNode, 10));

		top.BX.addCustomEvent(
			top,
			'Rest:AppLayout:ApplicationInstall',
			this.onRestAppInstall.bind(this),
		);
	}

	reInit(data: ?Object<string, any>, viewMode: ?ViewMode)
	{
		if (!Type.isPlainObject(data))
		{
			data = {};
		}

		this.#viewMode = viewMode || ViewMode.none();
		this.#listNodes.forEach((node) => Dom.clean(node));

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

		Object
			.entries(this.#triggerEventsListeners)
			.forEach(([eventName, listener]) => trigger.subscribe(eventName, listener));
	}

	onTriggerEvent(eventName: string, listener: (event: BaseEvent, trigger: Trigger) => void)
	{
		this.#triggerEventsListeners[eventName] = listener;

		this.#triggers.forEach((trigger: Trigger) => {
			trigger.subscribe(eventName, (event) => listener(event, trigger));
		});
	}

	getSelectedTriggers(): Array<Trigger>
	{
		return this.#triggers.filter((trigger) => trigger.isSelected());
	}

	onSearch(event)
	{
		this.#triggers.forEach((trigger) => trigger.onSearch(event));
	}

	enableManageMode(status)
	{
		this.#viewMode = ViewMode.manage();

		document.querySelectorAll('[data-role="trigger-list"]').forEach((listNode) => {
			if (listNode.dataset.statusId === status)
			{
				Dom.addClass(listNode, '--multiselect-mode');
			}
		});
		this.#triggers.forEach((trigger) => {
			trigger.enableManageMode(trigger.documentStatus === status);
		});
	}

	disableManageMode()
	{
		this.#viewMode = ViewMode.edit();

		document.querySelectorAll('[data-role="trigger-list"]').forEach((listNode) => {
			Dom.removeClass(listNode, '--multiselect-mode');
		});

		this.#triggers.forEach((trigger) => trigger.disableManageMode());
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
		this.emit('TriggerManager:trigger:add', { trigger });
	}

	deleteTrigger(trigger: Trigger, callback)
	{
		if (trigger.getId() > 0)
		{
			trigger.markDeleted();
		}
		else
		{
			for (let i = 0; i < this.#triggers.length; ++i)
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
		this.#triggers.forEach((trigger) => trigger.registerItem(trigger.node));
		this.#triggersContainerNode
			.querySelectorAll('.bizproc-automation-trigger-item-wrapper')
			.forEach((node) => {
				Dom.addClass(node, 'bizproc-automation-trigger-item-wrapper-draggable');
			})
		;
	}

	disableDragAndDrop()
	{
		this.#triggers.forEach((trigger) => trigger.unregisterItem(trigger.node));
		this.#triggersContainerNode
			.querySelectorAll('.bizproc-automation-trigger-item-wrapper')
			.forEach((node) => {
				Dom.removeClass(node, 'bizproc-automation-trigger-item-wrapper-draggable');
			})
		;
	}

	insertTrigger(trigger)
	{
		this.#triggers.push(trigger);
		this.markModified(true);
	}

	insertTriggerNode(documentStatus: string, triggerNode)
	{
		const listNode = this.#triggersContainerNode.querySelector(`[data-role="trigger-list"][data-status-id="${documentStatus}"]`);
		if (listNode)
		{
			Dom.append(triggerNode, listNode);
		}
	}

	serialize(): Array<Object<string, any>>
	{
		return this.#triggers.map((trigger) => trigger.serialize());
	}

	countAllTriggers(): number
	{
		return this.#triggers.filter((trigger) => !trigger.deleted).length;
	}

	findTriggerById(id: number): Trigger | undefined
	{
		return this.#triggers.find((trigger) => trigger.getId() === id);
	}

	findTriggersByDocumentStatus(statusId: string): Array<Trigger>
	{
		return this.#triggers.filter((trigger) => trigger.getStatusId() === statusId);
	}

	getTriggerName(code: string)
	{
		return getGlobalContext().availableTriggers.find((trigger) => code === trigger.CODE)?.NAME ?? code;
	}

	getAvailableTrigger(code): ?Object
	{
		const availableTriggers = getGlobalContext().availableTriggers;
		for (const availableTrigger of availableTriggers)
		{
			if (code === availableTrigger.CODE)
			{
				return availableTrigger;
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

		const title = this.getTriggerName(trigger.getCode());
		const form = Tag.render`
			<form name="${formName}" style="min-width: 540px;">
				${this.renderConditionSettings(trigger)}
				<span class="bizproc-automation-popup-settings-title bizproc-automation-popup-settings-title-autocomplete">
					${Loc.getMessage('BIZPROC_AUTOMATION_CMP_TRIGGER_NAME')}:
				</span>
				<div class="bizproc-automation-popup-settings">
					<input
						class="bizproc-automation-popup-input"
						type="text"
						name="name"
						value="${Text.encode(trigger.getName() || title)}"
					/>
				</div>
			</form>
		`;

		const triggerData = this.getAvailableTrigger(trigger.getCode());

		if (triggerData && triggerData.SETTINGS)
		{
			this.#renderTriggerProperties(trigger, triggerData.SETTINGS.Properties, form);
		}

		onCustomEvent(
			`BX.Bizproc.Automation.TriggerManager:onOpenSettingsDialog-${trigger.getCode()}`,
			[trigger, form],
		);

		if (this.canSetExecuteBy())
		{
			this.renderExecuteByControl(trigger, form);
		}

		this.renderAllowBackwardsControl(trigger, form);

		Dom.addClass(this.#triggersContainerNode, 'automation-base-blocked');

		Designer.getInstance().setTriggerSettingsDialog({
			triggerManager: this,
			trigger,
			form,
		});

		const popup = new Popup({
			id: Helper.generateUniqueId(),
			bindElement: null,
			content: form,
			closeByEsc: true,
			buttons: [
				new SaveButton({
					onclick: () => {
						const formData = ajax.prepareForm(form);
						trigger.setName(formData.data.name);

						if (triggerData.SETTINGS)
						{
							this.#setTriggerProperties(trigger, triggerData.SETTINGS.Properties, form);
						}

						onCustomEvent(
							`BX.Bizproc.Automation.TriggerManager:onSaveSettings-${trigger.getCode()}`,
							[trigger, formData],
						);

						this.setConditionSettingsFromForm(formData.data, trigger);
						trigger.setAllowBackwards(formData.data.allow_backwards === 'Y');

						if (this.canSetExecuteBy())
						{
							trigger.setExecuteBy(formData.data.execute_by);
						}

						// analytics
						ajax.runAction(
							'bizproc.analytics.push',
							{
								analyticsLabel: `automation_trigger${trigger.draft ? '_draft' : ''}_save_${trigger.getCode().toLowerCase()}`,
							},
						);

						delete trigger.draft;

						trigger.reInit();
						this.markModified();
						popup.close();
					},
				}),
				new CancelButton({
					onclick: () => {
						popup.close();
					},
				}),
			],
			width: 590,
			contentPadding: 12,
			closeIcon: true,
			events: {
				onPopupClose: () => {
					Designer.getInstance().setTriggerSettingsDialog(null);
					this.destroySettingsDialogControls();
					popup.destroy();
					Dom.removeClass(this.#triggersContainerNode, 'automation-base-blocked');
					this.emit('TriggerManager:onCloseTriggerSettingsDialog');
				},
			},
			titleBar: title,
			overlay: false,
			draggable: { restrict: false },
		});

		Designer.getInstance().getTriggerSettingsDialog().popup = popup;
		popup.show();

		// analytics
		ajax.runAction(
			'bizproc.analytics.push',
			{
				analyticsLabel: `automation_trigger${trigger.draft ? '_draft' : ''}_settings_${trigger.getCode().toLowerCase()}`,
			},
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

			const toRenderProperty = { AllowSelection: false, ...property };

			if (toRenderProperty.Type === '@robot-select')
			{
				this.#prepareRobotSelectProperty(toRenderProperty);
			}

			Dom.append(
				Tag.render`
					<span 
						class="bizproc-automation-popup-settings-title bizproc-automation-popup-settings-title-top bizproc-automation-popup-settings-title-autocomplete"
					>${Text.encode(property.Name)}:</span>
				`,
				form,
			);
			Dom.append(
				Tag.render`
					<div class="bizproc-automation-popup-settings">
						${BX.Bizproc.FieldType.renderControl(
							[
							...getGlobalContext().document.getRawType(),
							getGlobalContext().document.getCategoryId(),
							],
						toRenderProperty,
						property.Id,
						value || '',
						)}
					</div>
				`,
				form,
			);
		});
	}

	#prepareRobotSelectProperty(property)
	{
		const cmp = Designer.getInstance().component;
		property.Options = [];
		const filter = property.Settings.Filter;
		const check = (robot) => {
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
						{ value: robot.getId(), name: robot.getProperty(property.Settings.OptionNameProperty) },
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
				caption: {
					head: Loc.getMessage('BIZPROC_JS_AUTOMATION_ROBOT_CONDITION_TITLE'),
				},
				isExpanded: (
					this.#userOptions && this.#userOptions.get('defaults', 'isConditionGroupExpanded', 'N') === 'Y'
				),
			},
		);

		if (this.#userOptions)
		{
			this.conditionSelector.subscribe('onToggleGroupViewClick', (event: BaseEvent) => {
				const data = event.getData();
				this.#userOptions.set('defaults', 'isConditionGroupExpanded', data.isExpanded ? 'Y' : 'N');
			});
		}

		return this.conditionSelector.createNode();
	}

	#renderConditionGroupSelector(property, value, form)
	{
		const selector = new ConditionGroupSelector(new ConditionGroup(value), {
			fields: property.Settings.Fields,
			fieldPrefix: property.Id,
			showValuesSelector: false,
			caption: {
				head: property.Name,
			},
		});

		Dom.append(selector.createNode(), form);
	}

	#setConditionGroupValue(property, form)
	{
		const formData = BX.ajax.prepareForm(form);
		const conditionGroup = ConditionGroup.createFromForm(formData.data, property.Id);

		return conditionGroup.serialize();
	}

	#renderWebhookCodeProperty(property, value, form)
	{
		if (!value)
		{
			value = Text.getRandom(5);
		}

		Dom.append(
			Tag.render`
				<span class="bizproc-automation-popup-settings-title bizproc-automation-popup-settings-title-autocomplete">
					${Text.encode(property.Name)}:
				</span>
			`,
			form,
		);
		Dom.append(Tag.render`<input type="hidden" value="${Text.encode(value)}" name="code"/>`, form);

		const hookLinkTextarea = Tag.render`
			<textarea class="bizproc-automation-popup-textarea" placeholder="..." readonly="readonly" name="webhook_handler">
			</textarea>
		`;
		Event.bind(hookLinkTextarea, 'click', () => {
			this.select();
		});

		Dom.append(Tag.render`<div class="bizproc-automation-popup-settings">${hookLinkTextarea}</div>`, form);

		Dom.append(
			Tag.render`
				<span class="bizproc-automation-popup-settings-title bizproc-automation-popup-settings-title-autocomplete">
					${Loc.getMessage('BIZPROC_AUTOMATION_CMP_WEBHOOK_ID')}
				</span>
			`,
			form,
		);

		if (property.Settings.Handler)
		{
			let url = window.location.protocol + '//' + window.location.host + property.Settings.Handler;
			url = Uri.addParam(url, { code: value });
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
			const myAlertText = (
				Loc.getMessage('BIZPROC_AUTOMATION_WEBHOOK_PASSWORD_ALERT')
					.replace(
						'#A1#',
						'<a class="bizproc-automation-popup-settings-link '
						+ 'bizproc-automation-popup-settings-link-light" data-role="token-gen">',
					)
					.replace('#A2#', '</a>')
			);

			const passwordAlert = new Alert({
				color: AlertColor.WARNING,
				icon: AlertIcon.WARNING,
				text: myAlertText,
			});

			Event.bind(
				passwordAlert.getTextContainer().querySelector('[data-role="token-gen"]'),
				'click',
				() => {
					const loaderConfig = property.Settings.PasswordLoader;

					ajax.runComponentAction(
						loaderConfig.component,
						loaderConfig.action,
						{
							mode: loaderConfig.mode || undefined,
							data: {
								documentType: [
									...getGlobalContext().document.getRawType(),
									getGlobalContext().document.getCategoryId(),
								],
							},
						},
					).then(
						(response) => {
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
						},
					);
				},
			);

			Dom.append(passwordAlert.getContainer(), form);
		}
	}

	#renderFieldSelectorProperty(property, value, form)
	{
		const menuId = `@field-selector${Math.random()}`;
		const fieldName = `${property.Id}[]`;

		const fieldsList = property.Settings.Fields;

		const renderFieldCheckbox = function(field, listNode)
		{
			const exists = listNode.querySelector(`[data-field="${field.Id}"]`);
			if (exists)
			{
				return;
			}

			Dom.append(
				Tag.render`
					<div class="bizproc-automation-popup-checkbox-item" data-field="${Text.encode(field.Id)}">
						<label class="bizproc-automation-popup-chk-label">
							<input
								class="bizproc-automation-popup-chk"
								type="checkbox"
								name="${Text.encode(fieldName)}"
								value="${Text.encode(field.Id)}"
								checked
							/>
							${Text.encode(field.Name)}
						</label>
					</div>
				`,
				listNode,
			);
		};

		const fieldSelectorHandler = function(targetNode, listNode)
		{
			if (BX.Main.MenuManager.getMenuById(menuId))
			{
				return BX.Main.MenuManager.getMenuById(menuId).show();
			}

			const menuItems = [];

			fieldsList.forEach((field) => {
				menuItems.push({
					text: Text.encode(field.Name),
					field,
					onclick(event, item)
					{
						renderFieldCheckbox(item.field, listNode);
						this.popupWindow.close();
					},
				});
			});

			MenuManager.show(
				menuId,
				targetNode,
				menuItems,
				{
					autoHide: true,
					offsetLeft: (Dom.getPosition(this).width / 2),
					angle: { position: 'top', offset: 0 },
					zIndex: 200,
					className: 'bizproc-automation-inline-selector-menu',
					events: {
						onPopupClose: (popup) => {
							popup.destroy();
						},
					},
				},
			);
		};

		Dom.append(
			Tag.render`
				<span class="bizproc-automation-popup-settings-title bizproc-automation-popup-settings-title-autocomplete">
					${Text.encode(property.Name)}:
				</span>
			`,
			form,
		);

		const fieldListNode = Tag.render`<div class="bizproc-automation-popup-checkbox"></div>`;
		Dom.append(fieldListNode, form);

		const fieldSelectorNode = Tag.render`
			<span class="bizproc-automation-popup-settings-link">${Text.encode(property.Settings.ChooseFieldLabel)}</span>
		`;
		Event.bind(fieldSelectorNode, 'click', function() {
			fieldSelectorHandler(this, fieldListNode);
		});

		Dom.append(
			Tag.render`
				<div class="bizproc-automation-popup-settings bizproc-automation-popup-settings-text">
					${fieldSelectorNode}
				</div>
			`,
			form,
		);

		if (Type.isArray(value))
		{
			value.forEach((field) => {
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
		Dom.append(
			Tag.render`
				<span class="bizproc-automation-popup-settings-title bizproc-automation-popup-settings-title-top bizproc-automation-popup-settings-title-autocomplete">
					${Loc.getMessage('BIZPROC_AUTOMATION_CMP_TRIGGER_EXECUTE_BY')}:
				</span>
			`,
			form,
		);

		const documentType = [...getGlobalContext().document.getRawType(), getGlobalContext().document.getCategoryId()];
		const property = { Type: 'user' };
		const value = (
			trigger.draft
				? Helper.getResponsibleUserExpression(getGlobalContext().document.getFields())
				: trigger.getExecuteBy()
		);

		Dom.append(
			Tag.render`
				<div class="bizproc-automation-popup-settings">
					${BX.Bizproc.FieldType.renderControl(documentType, property, 'execute_by', value)}
				</div>
			`,
			form,
		);
	}

	renderAllowBackwardsControl(trigger, form)
	{
		Dom.append(
			Tag.render`
				<div class="bizproc-automation-popup-checkbox">
					<div class="bizproc-automation-popup-checkbox-item">
						<label class="bizproc-automation-popup-chk-label">
							<input
								class="bizproc-automation-popup-chk"
								type="checkbox"
								name="allow_backwards"
								value="Y"
								${trigger.isBackwardsAllowed() ? 'checked' : ''}
							/>
							${Loc.getMessage('BIZPROC_AUTOMATION_CMP_TRIGGER_ALLOW_REVERSE')}
						</label>
					</div>
				</div>
			`,
			form,
		);
	}

	setConditionSettingsFromForm(formFields: Object, trigger: Trigger): this
	{
		trigger.setCondition(ConditionGroup.createFromForm(formFields));

		return this;
	}

	onRestAppInstall(installed, eventResult)
	{
		eventResult.redirect = false;

		setTimeout(() => {
			ajax({
				method: 'POST',
				dataType: 'json',
				url: getGlobalContext().ajaxUrl,
				data: {
					ajax_action: 'get_available_triggers',
					document_signed: getGlobalContext().signedDocument,
				},
				onsuccess(response)
				{
					if (Type.isArray(response.DATA))
					{
						getGlobalContext().set('availableTriggers', response.DATA);
					}
				},
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
		for (const controlNode of controlNodes)
		{
			let control = null;
			const role = controlNode.getAttribute('data-role');

			if (role === 'user-selector')
			{
				control = BX.Bizproc.UserSelector.decorateNode(controlNode);
			}

			BX.UI.Hint.init(controlNode);

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
			if (props.length > 0)
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
							Expression: `{{~*:${property.Id}}}`,
							SystemExpression: `{=Template:${property.Id}}`,
							ObjectRealId: trigger.getId(),
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
		for (const property of properties)
		{
			if (property.Id === propertyId)
			{
				return property;
			}
		}

		return null;
	}
}
