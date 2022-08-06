import { Type, Event, Loc, Dom, Text, Reflection, Uri } from "main.core";
import { MenuManager } from "main.popup";
import { EventEmitter } from "main.core.events";
import { ViewMode } from "./view-mode";
import { Trigger } from "./trigger";
import { HelpHint } from "./help-hint";
import { Helper } from "./helper";
import { Designer } from "./designer";
import { getGlobalContext, ConditionGroup, ConditionGroupSelector } from "bizproc.automation";

export class TriggerManager extends EventEmitter
{
	#triggersContainerNode: HTMLElement;
	#viewMode: ViewMode;
	#triggers: Array<Trigger>;
	#triggersData: Array<Object<string, any>>;
	#columnNodes: NodeList;
	#listNodes: NodeList;
	#buttonsNodes: NodeList;
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
		this.#buttonsNodes = this.#triggersContainerNode.querySelectorAll('[data-role="trigger-buttons"]');
		this.#modified = false;
		this.initButtons();
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
		this.#buttonsNodes.forEach(node => Dom.clean(node));

		this.#triggersData = Type.isArray(data.TRIGGERS) ? data.TRIGGERS : [];

		this.initTriggers();
		this.initButtons();

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

	initButtons()
	{
		if (this.#viewMode.isEdit())
		{
			this.#buttonsNodes.forEach(node => this.createAddButton(node));
		}
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

	createAddButton(containerNode: HTMLElement)
	{
		const self = this;
		const div = Dom.create('span', {
			events: {
				click(event)
				{
					if (!self.canEdit())
					{
						HelpHint.showNoPermissionsHint(this);
					}
					else if (!self.#viewMode.isManage())
					{
						self.onAddButtonClick(this);
					}
				}
			},
			attrs: {
				className: 'bizproc-automation-btn-add',
				'data-status-id': containerNode.getAttribute('data-status-id'),
			},
			children: [
				Dom.create('span', {
					attrs: {
						className: 'bizproc-automation-btn-add-text',
					},
					text: Loc.getMessage('BIZPROC_AUTOMATION_CMP_ADD'),
				})
			]
		});

		containerNode.appendChild(div);
	}

	onAddButtonClick(button: HTMLElement, context: Object<string, any>)
	{
		const self = this;
		const onMenuClick = function(event, item)
		{
			self.addTrigger(item.triggerData, function(trigger)
			{
				this.openTriggerSettingsDialog(trigger, context);
			});

			this.popupWindow.close();
		};

		const menuItems = [];
		getGlobalContext().availableTriggers.forEach((availableTrigger) => {
			if (availableTrigger.CODE === 'APP')
			{
				menuItems.push(this.createAppTriggerMenuItem(
					button.getAttribute('data-status-id'),
					availableTrigger,
				));
			}
			else
			{
				menuItems.push({
					text: availableTrigger.NAME,
					triggerData: {
						DOCUMENT_STATUS: button.getAttribute('data-status-id') || context.statusId,
						CODE: availableTrigger.CODE
					},
					onclick: onMenuClick
				});
			}
		});

		MenuManager.show(
			Helper.generateUniqueId(),
			button,
			menuItems,
			{
				autoHide: true,
				offsetLeft: (Dom.getPosition(button)['width'] / 2),
				angle: { position: 'top', offset: 0 },
				events : {
					onPopupClose()
					{
						this.destroy();
					}
				}
			}
		);
	}

	onChangeTriggerClick(statusId: string, event)
	{
		this.onAddButtonClick(event.target, {changeTrigger: true, statusId: statusId});
	}

	createAppTriggerMenuItem(status, triggerData)
	{
		const self = this;
		const onMenuClick = function(e, item)
		{
			self.addTrigger(item.triggerData, function(trigger)
			{
				this.openTriggerSettingsDialog(trigger);
			});

			this.getRootMenuWindow().close();
		};

		const menuItems = [];
		for (let i = 0; i < triggerData['APP_LIST'].length; ++i)
		{
			const item = triggerData['APP_LIST'][i];
			const itemName = '[' + item['APP_NAME'] + '] ' + item['NAME'];
			menuItems.push({
				text: Text.encode(itemName),
				triggerData: {
					DOCUMENT_STATUS: status,
					NAME: itemName,
					CODE: triggerData.CODE,
					APPLY_RULES: {
						APP_ID: item['APP_ID'],
						CODE: item['CODE']
					}
				},
				onclick: onMenuClick
			});
		}

		if (Reflection.getClass('BX.rest.Marketplace'))
		{
			if (menuItems.length)
			{
				menuItems.push({ delimiter: true });
			}

			menuItems.push({
				text: Loc.getMessage('BIZPROC_AUTOMATION_ROBOT_CATEGORY_OTHER_MARKETPLACE_2'),
				onclick: function()
				{
					BX.rest.Marketplace.open({PLACEMENT: getGlobalContext().get('marketplaceRobotCategory')});
					this.getRootMenuWindow().close();
				}
			});
		}

		return {
			text: triggerData.NAME,
			items: menuItems
		}
	}

	addTrigger(triggerData: ?Object<string, any>, callback)
	{
		const trigger = new Trigger();
		trigger.init(triggerData, this.#viewMode);
		this.subscribeTriggerEvents(trigger);
		trigger.draft = true;
		if (callback)
		{
			callback.call(this, trigger);
		}
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

	insertTriggerNode(documentStatus: string, triggerNode)
	{
		const listNode = this.#triggersContainerNode.querySelector('[data-role="trigger-list"][data-status-id="'+documentStatus+'"]');
		if (listNode)
		{
			listNode.appendChild(triggerNode);
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

		form.appendChild(this.renderConditionSettings(trigger));

		const iconHelp = Dom.create('div', {
			attrs: { className: 'bizproc-automation-robot-help' },
			events: {click: (event) => this.emit('TriggerManager:onHelpClick', event)}
		});
		form.appendChild(iconHelp);

		const title = this.getTriggerName(trigger.getCode());

		form.appendChild(Dom.create("span", {
			attrs: { className: "bizproc-automation-popup-settings-title bizproc-automation-popup-settings-title-autocomplete" },
			text: Loc.getMessage('BIZPROC_AUTOMATION_CMP_TRIGGER_NAME') + ':'
		}));

		form.appendChild(Dom.create("div", {
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
		}));

		//TODO: refactoring
		const triggerData = this.getAvailableTrigger(trigger.getCode());
		if (trigger.getCode() === 'WEBHOOK')
		{
			if (!trigger.getApplyRules()['code'])
			{
				trigger.getApplyRules()['code'] = Text.getRandom(5);
			}

			form.appendChild(Dom.create("span", {
				attrs: { className: "bizproc-automation-popup-settings-title bizproc-automation-popup-settings-title-autocomplete" },
				text: "URL:"
			}));

			form.appendChild(Dom.create('input', {
				props: {
					type: 'hidden',
					value: trigger.getApplyRules()['code'],
					name: 'code'
				}
			}));

			const hookLinkTextarea = Dom.create("textarea", {
				attrs: {
					className: "bizproc-automation-popup-textarea",
					placeholder: "...",
					readonly: 'readonly',
					name: 'webhook_handler'
				},
				events: {
					click(e)
					{
						this.select();
					}
				}
			});

			form.appendChild(Dom.create("div", {
				attrs: { className: "bizproc-automation-popup-settings" },
				children: [hookLinkTextarea]
			}));

			form.appendChild(Dom.create("span", {
				attrs: { className: "bizproc-automation-popup-settings-title bizproc-automation-popup-settings-title-autocomplete" },
				text: Loc.getMessage('BIZPROC_AUTOMATION_CMP_WEBHOOK_ID'),
			}));

			if (triggerData && triggerData['HANDLER'])
			{
				let url = window.location.protocol + '//' + window.location.host + triggerData['HANDLER'];
				url = Uri.addParam(url, {code: trigger.getApplyRules()['code']});
				url = url.replace('{{DOCUMENT_TYPE}}', getGlobalContext().document.getRawType()[2]);
				hookLinkTextarea.value = url;
			}
		}
		else if (trigger.getCode() === 'EMAIL_LINK')
		{
			form.appendChild(Dom.create("span", {
				attrs: { className: "bizproc-automation-popup-settings-title bizproc-automation-popup-settings-title-autocomplete" },
				text: Loc.getMessage('BIZPROC_AUTOMATION_TRIGGER_EMAIL_LINK_URL') + ':',
			}));

			form.appendChild(Dom.create("div", {
				attrs: { className: "bizproc-automation-popup-settings" },
				children: [
					Dom.create("textarea", {
						attrs: {
							className: "bizproc-automation-popup-textarea",
							placeholder: "https://example.com"
						},
						props: {name: 'url'},
						text: trigger.getApplyRules()['url'] || ''
					}),
				],
			}));
		}
		else if (trigger.getCode() === 'WEBFORM')
		{
			if (triggerData && triggerData['WEBFORM_LIST'])
			{
				const select = Dom.create('select', {
					attrs: {className: 'bizproc-automation-popup-settings-dropdown'},
					props: {
						name: 'form_id',
						value: ''
					},
					children: [
						Dom.create('option', {
							props: {value: ''},
							text: Loc.getMessage('BIZPROC_AUTOMATION_TRIGGER_WEBFORM_ANY')
						}),
					],
				});

				for (let i = 0; i < triggerData['WEBFORM_LIST'].length; ++i)
				{
					const item = triggerData['WEBFORM_LIST'][i];
					select.appendChild(Dom.create('option', {
						props: {value: item['ID']},
						text: item['NAME']
					}));
				}
				if (Type.isPlainObject(trigger.getApplyRules()) && trigger.getApplyRules()['form_id'])
				{
					select.value = trigger.getApplyRules()['form_id'];
				}

				const div = Dom.create('div', {
					attrs: {
						className: 'bizproc-automation-popup-settings'
					},
					children: [
						Dom.create('span', {
							attrs: {
								className: 'bizproc-automation-popup-settings-title'
							},
							text: Loc.getMessage('BIZPROC_AUTOMATION_TRIGGER_WEBFORM_LABEL') + ':'
						}),
						select,
					],
				});
				form.appendChild(div);
			}
		}
		else if (trigger.getCode() === 'CALLBACK')
		{
			if (triggerData && triggerData['WEBFORM_LIST'])
			{
				const select = Dom.create('select', {
					attrs: {className: 'bizproc-automation-popup-settings-dropdown'},
					props: {
						name: 'form_id',
						value: ''
					},
					children: [
						Dom.create('option', {
							props: {value: ''},
							text: Loc.getMessage('BIZPROC_AUTOMATION_TRIGGER_WEBFORM_ANY'),
						}),
					],
				});

				for (let i = 0; i < triggerData['WEBFORM_LIST'].length; ++i)
				{
					const item = triggerData['WEBFORM_LIST'][i];
					select.appendChild(
						Dom.create('option', {
							props: {value: item['ID']},
							text: item['NAME']
						})
					);
				}
				if (Type.isPlainObject(trigger.getApplyRules()) && trigger.getApplyRules()['form_id'])
				{
					select.value = trigger.getApplyRules()['form_id'];
				}

				const div = Dom.create('div', {
					attrs: {
						className: 'bizproc-automation-popup-settings'
					},
					children: [
						Dom.create('span', {
							attrs: {
								className: 'bizproc-automation-popup-settings-title'
							},
							text: Loc.getMessage('BIZPROC_AUTOMATION_TRIGGER_WEBFORM_LABEL') + ':'}),
						select
					],
				});
				form.appendChild(div);
			}
		}
		else if (trigger.getCode() === 'STATUS')
		{
			if (triggerData && triggerData['STATUS_LIST'])
			{
				const select = Dom.create('select', {
					attrs: {className: 'bizproc-automation-popup-settings-dropdown'},
					props: {
						name: 'STATUS',
						value: ''
					},
					children: [
						Dom.create('option', {
							props: {value: ''},
							text: Loc.getMessage('BIZPROC_AUTOMATION_TRIGGER_STATUS_ANY'),
						}),
					],
				});

				for (let i = 0; i < triggerData['STATUS_LIST'].length; ++i)
				{
					const item = triggerData['STATUS_LIST'][i];
					select.appendChild(Dom.create('option', {
						props: {value: item['ID']},
						text: item['NAME']
					}));
				}
				if (Type.isPlainObject(trigger.getApplyRules()) && trigger.getApplyRules()['STATUS'])
				{
					select.value = trigger.getApplyRules()['STATUS'];
				}

				const div = Dom.create('div', {
					attrs: {className: 'bizproc-automation-popup-settings'},
					children: [
						Dom.create('span', {
							attrs: {
								className: 'bizproc-automation-popup-settings-title'
							},
							text: triggerData['STATUS_LABEL'] + ':'}
						),
						select
					],
				});
				form.appendChild(div);
			}
		}
		else if (trigger.getCode() == 'CALL')
		{
			if (triggerData && triggerData['LINES'])
			{
				const select = Dom.create('select', {
					attrs: {className: 'bizproc-automation-popup-settings-dropdown'},
					props: {
						name: 'LINE_NUMBER',
						value: ''
					},
					children: [
						Dom.create('option', {
							props: {value: ''},
							text: Loc.getMessage('BIZPROC_AUTOMATION_TRIGGER_WEBFORM_ANY'),
						}),
					],
				});

				for (let i = 0; i < triggerData['LINES'].length; ++i)
				{
					const item = triggerData['LINES'][i];
					select.appendChild(Dom.create('option', {
						props: {value: item['LINE_NUMBER']},
						text: item['SHORT_NAME']
					}));
				}
				if (trigger.getApplyRules()['LINE_NUMBER'])
				{
					select.value = trigger.getApplyRules()['LINE_NUMBER'];
				}

				const div = Dom.create('div', {
					attrs: {className: 'bizproc-automation-popup-settings'},
					children: [
						Dom.create('span', {
							attrs: {
								className: 'bizproc-automation-popup-settings-title'
							},
							text: Loc.getMessage('BIZPROC_AUTOMATION_TRIGGER_CALL_LABEL') + ':'
						}),
						select,
					],
				});
				form.appendChild(div);
			}
		}
		else if (trigger.getCode() == 'OPENLINE' || trigger.getCode() == 'OPENLINE_MSG')
		{
			if (triggerData && triggerData['CONFIG_LIST'])
			{
				const select = Dom.create('select', {
					attrs: {className: 'bizproc-automation-popup-settings-dropdown'},
					props: {
						name: 'config_id',
						value: ''
					},
					children: [
						Dom.create('option', {
							props: {value: ''},
							text: Loc.getMessage('BIZPROC_AUTOMATION_TRIGGER_WEBFORM_ANY')
						}),
					],
				});

				for (let i = 0; i < triggerData['CONFIG_LIST'].length; ++i)
				{
					const item = triggerData['CONFIG_LIST'][i];
					select.appendChild(Dom.create('option', {
						props: {value: item['ID']},
						text: item['NAME']
					}));
				}
				if (Type.isPlainObject(trigger.getApplyRules()) && trigger.getApplyRules()['config_id'])
				{
					select.value = trigger.getApplyRules()['config_id'];
				}

				const div = Dom.create('div', {
					attrs: {className: 'bizproc-automation-popup-settings'},
					children: [
						Dom.create('span', {
							attrs: {
								className: 'bizproc-automation-popup-settings-title'
							},
							text: Loc.getMessage('BIZPROC_AUTOMATION_TRIGGER_OPENLINE_LABEL') + ':'
						}),
						select,
					],
				});
				form.appendChild(div);
			}
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

		const titleBar = trigger.draft ? this.createChangeTriggerTitleBar(title, trigger.documentStatus) : null;

		const self = this;
		const popup = new BX.PopupWindow(BX.Bizproc.Helper.generateUniqueId(), null, {
			titleBar: titleBar || title,
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
				}
			},
			buttons: [
				new BX.PopupWindowButton({
					text : Loc.getMessage('JS_CORE_WINDOW_SAVE'),
					className : "popup-window-button-accept",
					events : {
						click() {
							const formData = BX.ajax.prepareForm(form);
							trigger.setName(formData['data']['name']);

							//TODO: refactoring
							if (trigger.getCode() === 'WEBFORM')
							{
								trigger.setApplyRules({form_id:  formData['data']['form_id']});
							}
							if (trigger.getCode() === 'CALLBACK')
							{
								trigger.setApplyRules({form_id: formData['data']['form_id']});
							}
							if (trigger.getCode() === 'STATUS')
							{
								trigger.setApplyRules({STATUS: formData['data']['STATUS']});
							}
							if (trigger.getCode() === 'CALL' && 'LINE_NUMBER' in formData['data'])
							{
								trigger.setApplyRules({LINE_NUMBER: formData['data']['LINE_NUMBER']});
							}
							if (trigger.getCode() === 'OPENLINE' || trigger.getCode() === 'OPENLINE_MSG')
							{
								trigger.setApplyRules({config_id: formData['data']['config_id']});
							}

							if (trigger.getCode() === 'WEBHOOK')
							{
								trigger.setApplyRules({code: formData['data']['code']});
							}

							if (trigger.getCode() === 'EMAIL_LINK')
							{
								trigger.setApplyRules({url: formData['data']['url']});
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
								self.#triggers.push(trigger);
								self.insertTriggerNode(trigger.getStatusId(), trigger.node)
							}
							delete trigger.draft;

							trigger.reInit();
							self.markModified();
							this.popupWindow.close();
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
	}

	createChangeTriggerTitleBar(title, statusId)
	{
		return {
			content: Dom.create('div', {
				props: {
					className: 'popup-window-titlebar-text bizproc-automation-popup-titlebar-with-link'
				},
				children: [
					document.createTextNode(title),
					Dom.create('span', {
						props: {
							className: 'bizproc-automation-popup-titlebar-link'
						},
						text: Loc.getMessage('BIZPROC_AUTOMATION_CMP_CHANGE_TRIGGER'),
						events: {
							click: this.onChangeTriggerClick.bind(this, statusId)
						}
					})
				]
			})
		};
	}

	renderConditionSettings(trigger: Trigger)
	{
		const conditionGroup = trigger.getCondition().clone();

		this.conditionSelector = new ConditionGroupSelector(
			conditionGroup,
			{fields: getGlobalContext().document.getFields()},
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

	renderExecuteByControl(trigger, form)
	{
		form.appendChild(Dom.create("span", {
			attrs: {
				className: "bizproc-automation-popup-settings-title bizproc-automation-popup-settings-title-top bizproc-automation-popup-settings-title-autocomplete",
			},
			text: Loc.getMessage('BIZPROC_AUTOMATION_CMP_TRIGGER_EXECUTE_BY') + ':',
		}));

		form.appendChild(Dom.create("div", {
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
		}));
	}

	renderAllowBackwardsControl(trigger, form)
	{
		form.appendChild(Dom.create("div", {
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
		}));
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