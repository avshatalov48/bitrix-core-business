import {Type, Dom, Loc, Event, Runtime, Uri, Text, Tag} from 'main.core';
import { BaseEvent, EventEmitter } from 'main.core.events';
import {
	Context,
	SelectorContext,
	getGlobalContext,
	Tracker,
	Designer,
	ConditionGroupSelector,
	ConditionGroup,
	DelayIntervalSelector,
	SelectorManager,
} from 'bizproc.automation';
import { Robot } from './robot'
import { UserOptions } from './user-options';
import { ViewMode } from './view-mode';
import { Helper } from './helper';
import { HelpHint } from './help-hint';
import { DelayInterval } from './delay-interval';
import 'ui.hint';

export class Template extends EventEmitter
{
	#context: Context;

	constants: Object<string, any>;
	variables: Object<string, any>;
	robotSettingsControls;

	#delayMinLimitM: number;
	#userOptions: UserOptions | null;
	#tracker: Tracker;
	#viewMode: ViewMode;

	#templateContainerNode: Element;
	#templateNode: ?Element;
	#listNode: Element | undefined;
	#buttonsNode: Element | undefined;

	#robots: Array<Robot>;
	#data: Object;

	constructor(params: {
		context: ?Context,
		templateContainerNode: Element,
		constants: Object<string, any>,
		variables: Object<string, any>,
		userOptions: ?UserOptions,
		delayMinLimitM: number,
	})
	{
		super();
		this.setEventNamespace('BX.Bizproc.Automation');

		this.#context = params.context ?? getGlobalContext();
		this.constants = params.constants;
		this.variables = params.variables;

		this.#templateContainerNode = params.templateContainerNode;
		this.#delayMinLimitM = params.delayMinLimitM;
		this.#userOptions = params.userOptions;
		this.#tracker = this.#context.tracker;
		this.#data = {};
		this.#robots = [];
		this.#viewMode = ViewMode.none();
	}

	init(data: Object, viewMode: number)
	{
		if (Type.isPlainObject(data))
		{
			this.#data = data;
			if (!Type.isPlainObject(this.#data.CONSTANTS))
			{
				this.#data.CONSTANTS = {};
			}
			if (!Type.isPlainObject(this.#data.PARAMETERS))
			{
				this.#data.PARAMETERS = {};
			}
			if (!Type.isPlainObject(this.#data.VARIABLES))
			{
				this.#data.VARIABLES = {};
			}
			if (!Type.isNil(this.#data.DOCUMENT_STATUS))
			{
				this.#data.DOCUMENT_STATUS = String(this.#data.DOCUMENT_STATUS);
			}

			this.markExternalModified(this.#data['IS_EXTERNAL_MODIFIED']);
			this.markModified(false);
		}

		this.#viewMode = ViewMode.fromRaw(viewMode);

		if (!this.#viewMode.isNone())
		{
			this.#templateNode = this.#templateContainerNode.querySelector(
				'[data-role="automation-template"][data-status-id="' + this.#data.DOCUMENT_STATUS + '"]'
			);
			this.#listNode = this.#templateNode.querySelector('[data-role="robot-list"]');
			this.#buttonsNode = this.#templateNode.querySelector('[data-role="buttons"]');

			this.initRobots();
			this.initButtons();

			if (!this.isExternalModified() && this.canEdit())
			{
				//register DD
				jsDD.registerDest(this.#templateNode, 10);
			}
			else
			{
				jsDD.unregisterDest(this.#templateNode);
			}
		}
	}

	reInit(data: Object, viewMode: number)
	{
		Dom.clean(this.#listNode);
		Dom.clean(this.#buttonsNode);

		this.destroy();
		this.init(data, viewMode);
	}

	destroy()
	{
		this.#robots.forEach(robot => robot.destroy());
	}

	static copyRobotTo(dstTemplate: Template, robot: Robot, beforeRobot: ?Robot): Robot
	{
		const copiedRobot = robot.copyTo(dstTemplate, beforeRobot);
		dstTemplate.emit('Template:robot:add', {robot: copiedRobot});
	}

	canEdit()
	{
		return this.#context.canEdit;
	}

	initRobots()
	{
		this.#robots = [];

		if (Type.isArray(this.#data.ROBOTS))
		{
			for (let i = 0; i < this.#data.ROBOTS.length; ++i)
			{
				const robot = new Robot({
					document: this.#context.document,
					template: this,
					isFrameMode: this.#context.get('isFrameMode'),
					tracker: this.#tracker,
				});
				robot.init(this.#data.ROBOTS[i], this.#viewMode);
				this.insertRobotNode(robot.node);
				this.#robots.push(robot);
			}
		}
	}

	get robots(): Array<Robot>
	{
		return this.#robots;
	}

	get userOptions(): ?UserOptions
	{
		return this.#userOptions;
	}

	getSelectedRobotNames(): Array<Robot>
	{
		const selectedRobots = [];

		this.#robots.forEach(robot => {
			if (robot.isSelected())
			{
				selectedRobots.push(robot.data.Name);
			}
		});

		return selectedRobots;
	}

	getSerializedRobots()
	{
		const serialized = [];
		this.#robots.forEach(robot => serialized.push(robot.serialize()));

		return serialized;
	}

	getId()
	{
		return this.#data.ID;
	}

	getStatusId(): ?string
	{
		return this.#data.DOCUMENT_STATUS;
	}

	getStatus(): ?object
	{
		return this.#context.document.statusList.find(status => String(status.STATUS_ID) === this.getStatusId());
	}

	getTemplateId()
	{
		const id = parseInt(this.#data.ID);

		return !isNaN(id) ? id : 0;
	}

	initButtons()
	{
		if (this.isExternalModified())
		{
			this.createExternalLocker();
		}
		else if (this.#viewMode.isEdit() && this.getTemplateId() > 0)
		{
			this.createConstantsEditButton();
			this.createParametersEditButton();
			this.createExternalEditTemplateButton();
			this.createManageModeButton();
		}
	}

	enableManageMode(isActive: boolean)
	{
		if (this.#listNode)
		{
			this.#viewMode = ViewMode.manage().setProperty('isActive', isActive);

			if (isActive)
			{
				Dom.addClass(this.#listNode, '--multiselect-mode');
			}

			if (this.isExternalModified())
			{
				Dom.addClass(this.#listNode, '--locked-node');
			}
			else
			{
				this.#robots.forEach(robot => robot.enableManageMode(isActive));
			}
		}
	}

	disableManageMode()
	{
		if (this.#listNode)
		{
			this.#viewMode = ViewMode.edit();
			Dom.removeClass(this.#listNode, '--multiselect-mode');
			if (this.isExternalModified())
			{
				Dom.removeClass(this.#listNode, '--locked-node');
			}
			else
			{
				this.#robots.forEach(robot => robot.disableManageMode());
			}

			this.#templateNode.querySelectorAll('.bizproc-automation-robot-container-wrapper').forEach(node => {
				Dom.addClass(node, 'bizproc-automation-robot-container-wrapper-draggable');
			});
		}
	}

	enableDragAndDrop()
	{
		this.#robots.forEach(robot => robot.registerItem(robot.node));

		this.#templateNode.querySelectorAll('.bizproc-automation-robot-container-wrapper').forEach(node => {
			Dom.addClass(node, 'bizproc-automation-robot-container-wrapper-draggable');
		});
	}

	disableDragAndDrop()
	{
		this.#robots.forEach(robot => robot.unregisterItem(robot.node));

		this.#templateNode.querySelectorAll('.bizproc-automation-robot-container-wrapper').forEach(node => {
			Dom.removeClass(node, 'bizproc-automation-robot-container-wrapper-draggable');
		});
	}

	createExternalEditTemplateButton(): undefined | boolean
	{
		if (Type.isNil(this.#context.bizprocEditorUrl))
		{
			return false;
		}

		const self = this;
		const anchor = Dom.create('a', {
			text: Loc.getMessage('BIZPROC_AUTOMATION_CMP_EXTERNAL_EDIT'),
			props: {
				href: '#'
			},
			events: {
				click(event)
				{
					event.preventDefault();

					if (!self.#viewMode.isManage())
					{
						self.onExternalEditTemplateButtonClick(this);
					}
				}
			},
			attrs: {
				className: "bizproc-automation-robot-btn-set",
				target: '_top'
			}
		});

		if (!this.#context.bizprocEditorUrl.length)
		{
			Dom.addClass(anchor, 'bizproc-automation-robot-btn-set-locked');
		}

		this.#buttonsNode.appendChild(anchor);
	}

	createManageModeButton()
	{
		if (!this.#context.canManage)
		{
			return;
		}

		const manageButton = Dom.create('a', {
			text: Loc.getMessage('BIZPROC_AUTOMATION_CMP_MANAGE_ROBOTS'),
			attrs: {
				className: "bizproc-automation-robot-btn-set",
				target: '_top',
			},
			style: {
				cursor: 'pointer',
			},
			events: {
				click: event => {
					event.preventDefault();
					this.onManageModeButtonClick(manageButton);
				},
			}
		});

		this.#buttonsNode.appendChild(manageButton);
	}

	onManageModeButtonClick(manageButtonNode: HTMLElement)
	{
		if (!this.canEdit())
		{
			HelpHint.showNoPermissionsHint(manageButtonNode);
		}
		else
		{
			this.emit('Template:enableManageMode', {
				documentStatus: this.#data.DOCUMENT_STATUS,
			});
		}
	}

	createConstantsEditButton(): boolean | undefined
	{
		if (Type.isNil(this.#context.constantsEditorUrl))
		{
			return false;
		}

		const url =
			!this.#viewMode.isManage()
				? this.#context.constantsEditorUrl.replace('#ID#', this.getTemplateId())
				: '#'
		;

		if (!url.length)
		{
			return false;
		}

		const anchor = Dom.create('a', {
			text: Loc.getMessage('BIZPROC_AUTOMATION_CMP_CONSTANTS_EDIT'),
			props: {
				href: url
			},
			attrs: { className: "bizproc-automation-robot-btn-set" }
		});

		this.#buttonsNode.appendChild(anchor);
	}

	createParametersEditButton(): boolean | undefined
	{
		if (Type.isNil(this.#context.parametersEditorUrl))
		{
			return false;
		}

		const url = this.#context.parametersEditorUrl.replace('#ID#', this.getTemplateId());

		if (!url.length || this.#viewMode.isManage())
		{
			return false;
		}

		const anchor = Dom.create('a', {
			text: Loc.getMessage('BIZPROC_AUTOMATION_CMP_PARAMETERS_EDIT'),
			props: {
				href: url
			},
			attrs: { className: "bizproc-automation-robot-btn-set" }
		});

		this.#buttonsNode.appendChild(anchor);
	}

	createExternalLocker()
	{
		const div = Dom.create("div", {
			attrs: {
				className: "bizproc-automation-robot-container"
			},
			children: [
				Dom.create('div', {
					attrs: {
						className: 'bizproc-automation-robot-container-wrapper bizproc-automation-robot-container-wrapper-lock'
					},
					children: [
						Dom.create("div", {
							attrs: { className: "bizproc-automation-robot-deadline" }
						}),
						Dom.create("div", {
							attrs: { className: "bizproc-automation-robot-title" },
							text: Loc.getMessage('BIZPROC_AUTOMATION_CMP_EXTERNAL_EDIT_TEXT')
						}),
					]
				})
			]
		});

		if (this.#viewMode.isEdit())
		{
			const settingsBtn = Dom.create('div', {
				attrs: {
					className: 'bizproc-automation-robot-btn-settings'
				},
				text: Loc.getMessage('BIZPROC_AUTOMATION_CMP_EDIT')
			});

			const self = this;
			Event.bind(div, 'click', function (event) {
				event.stopPropagation();
				if (!self.#viewMode.isManage())
				{
					self.onExternalEditTemplateButtonClick(this);
				}
			});
			div.appendChild(settingsBtn);

			const deleteBtn = Dom.create('SPAN', {
				attrs: {
					className: 'bizproc-automation-robot-btn-delete'
				}
			});

			Event.bind(deleteBtn, 'click', function (event) {
				event.stopPropagation();

				if (!self.#viewMode.isManage())
				{
					self.onUnsetExternalModifiedClick(this);
				}
			});
			div.lastChild.appendChild(deleteBtn);
		}

		this.#listNode.appendChild(div);
		this.#templateNode = div;
	}

	onSearch(event: BaseEvent)
	{
		if (this.isExternalModified())
		{
			this.onExternalModifiedSearch(event);
		}
		else
		{
			this.#robots.forEach(robot => robot.onSearch(event));
		}
	}

	onExternalModifiedSearch(event)
	{
		if (this.#templateNode)
		{
			const query = event.getData().queryString;
			BX[!query ? 'removeClass' : 'addClass'](this.#templateNode, '--search-mismatch');
		}
	}

	onExternalEditTemplateButtonClick(button)
	{
		if (!this.canEdit())
		{
			HelpHint.showNoPermissionsHint(button);
			return;
		}
		if (!this.#context.bizprocEditorUrl.length)
		{
			if (top.BX.UI && top.BX.UI.InfoHelper)
			{
				top.BX.UI.InfoHelper.show('limit_office_bp_designer');
			}

			return;
		}

		const templateId = this.getTemplateId();
		if (templateId > 0)
		{
			this.openBizprocEditor(templateId);
		}
	}

	onUnsetExternalModifiedClick(button)
	{
		this.#templateNode = null;

		this.markExternalModified(false);
		this.markModified();
		this.reInit(null, this.#viewMode.intoRaw());
	}

	openBizprocEditor(templateId)
	{
		top.window.location.href = this.#context.bizprocEditorUrl.replace('#ID#', templateId)
	}

	addRobot(robotData, callback)
	{
		const robot = new Robot({
			document: this.#context.document,
			template: this,
			isFrameMode: this.#context.get('isFrameMode'),
			tracker: this.#tracker,
		});
		const initData = {
			Type: robotData['CLASS'],
			Properties: {
				Title: robotData['NAME']
			},
			DialogContext: robotData['DIALOG_CONTEXT'],
		};

		if (this.#robots.length > 0)
		{
			const parentRobot = this.#robots[this.#robots.length - 1];
			if (!parentRobot.getDelayInterval().isNow() || parentRobot.isExecuteAfterPrevious())
			{
				initData['Delay'] = parentRobot.getDelayInterval().serialize();
				initData['ExecuteAfterPrevious'] =  1;
			}
		}

		robot.draft = true;
		robot.init(initData, this.#viewMode);

		this.insertRobot(robot);
		this.insertRobotNode(robot.node);
		this.emit('Template:robot:add', {robot});

		if (callback)
		{
			callback.call(this, robot);
		}
	}

	insertRobot(robot, beforeRobot)
	{
		if (beforeRobot)
		{
			for (let i = 0; i < this.#robots.length; ++i)
			{
				if (this.#robots[i] !== beforeRobot)
				{
					continue;
				}

				this.#robots.splice(i, 0, robot);
				break;
			}
		}
		else
		{
			this.#robots.push(robot);
		}

		this.markModified();
	}

	getNextRobot(robot)
	{
		for (let i = 0; i < this.#robots.length; ++i)
		{
			if (this.#robots[i] === robot)
			{
				return (this.#robots[i + 1] || null);
			}
		}

		return null;
	}

	deleteRobot(robot, callback)
	{
		for(let i = 0; i < this.#robots.length; ++i)
		{
			if (this.#robots[i].isEqual(robot))
			{
				this.#robots.splice(i, 1);

				if (callback)
				{
					callback(robot);
				}

				this.markModified();
				this.emit('Template:robot:delete', { robot });
				break;
			}
		}
	}

	insertRobotNode(robotNode, beforeNode)
	{
		if (beforeNode)
		{
			this.#listNode.insertBefore(robotNode, beforeNode);
		}
		else
		{
			this.#listNode.appendChild(robotNode);
		}
	}

	openRobotSettingsDialog(robot: Robot, context?: Object, saveCallback: (Robot) => void)
	{
		if (!Type.isPlainObject(context))
		{
			context = {};
		}

		if (Designer.getInstance().getRobotSettingsDialog())
		{
			if (context.changeRobot)
			{
				Designer.getInstance().getRobotSettingsDialog().popup.close();
			}
			else
			{
				return;
			}
		}

		const formName = 'bizproc_automation_robot_dialog';

		const form = Dom.create('form', {
			props: {
				name: formName
			}
		});

		Designer.getInstance().setRobotSettingsDialog({
			template: this,
			context: context,
			robot: robot,
			form: form
		});

		form.appendChild(this.renderDelaySettings(robot));
		form.appendChild(this.renderConditionSettings(robot));

		const robotBrokenLinks = robot.getBrokenLinks();
		if (robotBrokenLinks.length > 0)
		{
			Dom.append(this.renderBrokenLinkAlert(robotBrokenLinks), form);
		}

		const iconHelp = Dom.create('div', {
			attrs: { className: 'bizproc-automation-robot-help' },
			events: {
				click: (event) => this.emit('Template:help:show', event)
			},
		});
		form.appendChild(iconHelp);
		context['DOCUMENT_CATEGORY_ID'] = this.#context.document.getCategoryId();
		if (Type.isPlainObject(robot.data.DialogContext) && (robot.data.DialogContext.hasOwnProperty('addMenuGroup')))
		{
			context['addMenuGroup'] = robot.data.DialogContext.addMenuGroup;
		}

		BX.ajax({
			method: 'POST',
			dataType: 'html',
			url: Uri.addParam(
				this.#context.ajaxUrl,
				{
					analyticsLabel: `automation_robot${robot.draft ? '_draft' : ''}_settings_${robot.data.Type.toLowerCase()}`
				}
			),
			data: {
				ajax_action: 'get_robot_dialog',
				document_signed: this.#context.signedDocument,
				document_status: this.#context.document.getCurrentStatusId(),
				context: context,
				robot_json: Helper.toJsonString(robot.serialize()),
				form_name: formName
			},
			onsuccess: html => {
				if (html)
				{
					const dialogRows = Dom.create('div', {
						html: html
					});
					form.appendChild(dialogRows);
				}

				this.showRobotSettingsPopup(robot, form, saveCallback);
			}
		});
	}

	showRobotSettingsPopup(robot: Robot, form: HTMLFormElement, saveCallback: (Robot) => void)
	{
		let popupMinWidth = 580;
		let popupWidth = popupMinWidth;

		if (this.#userOptions)
		{
			// TODO move from if?
			this.emit('Template:robot:showSettings');
			popupWidth = parseInt(
				this.#userOptions.get('defaults', 'robot_settings_popup_width', 580)
			);
		}

		this.initRobotSettingsControls(robot, form);

		if (
			robot.data.Type === 'CrmSendEmailActivity'
			|| robot.data.Type === 'MailActivity'
			|| robot.data.Type === 'RpaApproveActivity'
		)
		{
			popupMinWidth += 170;
			if (popupWidth < popupMinWidth)
			{
				popupWidth = popupMinWidth;
			}
		}

		let robotTitle = Loc.getMessage('BIZPROC_AUTOMATION_ROBOT_SETTINGS_TITLE');
		let descriptionTitle = Loc.getMessage('BIZPROC_AUTOMATION_ROBOT_SETTINGS_TITLE');

		if (robot.hasTitle())
		{
			robotTitle = robot.getTitle();
			descriptionTitle = robot.getDescriptionTitle();

			if (descriptionTitle === 'untitled')
			{
				descriptionTitle = robotTitle;
			}
		}

		const titleBarContent = Tag.render`
			<div class="popup-window-titlebar-text bizproc-automation-robot-settings-popup-titlebar">
				<span class="bizproc-automation-robot-settings-popup-titlebar-text">${Text.encode(robotTitle)}</span>
				<div class="ui-hint">
					<span class="ui-hint-icon" data-text="${Text.encode(descriptionTitle)}"></span>
				</div>
			</div>
		`;
		HelpHint.bindAll(titleBarContent);

		const me = this;
		const popup = new BX.PopupWindow(Helper.generateUniqueId(), null, {
			titleBar: {
				content: titleBarContent,
			},
			content: form,
			closeIcon: true,
			width: popupWidth,
			resizable: {
				minWidth: popupMinWidth,
				minHeight: 100
			},
			offsetLeft: 0,
			offsetTop: 0,
			closeByEsc: true,
			draggable: {restrict: false},
			events: {
				onPopupClose: (popup) => {
					Designer.getInstance().setRobotSettingsDialog(null);
					this.destroyRobotSettingsControls();
					popup.destroy();
					this.emit('Template:robot:closeSettings');
				},
				onPopupResize: () => {
					this.onResizeRobotSettings();
				},
				onPopupResizeEnd: function() {
					if (me.#userOptions)
					{
						me.#userOptions.set(
							'defaults',
							'robot_settings_popup_width',
							this.getWidth()
						);
					}
				}
			},
			buttons: [
				new BX.PopupWindowButton({
					text : Loc.getMessage('JS_CORE_WINDOW_SAVE'),
					className : "popup-window-button-accept",
					events : {
						click()
						{
							const isNewRobot = robot.draft;

							me.saveRobotSettings(form, robot, BX.delegate(function()
							{
								this.popupWindow.close();
								if (isNewRobot)
								{
									me.emit('Template:robot:add', { robot });
								}
								if (saveCallback)
								{
									saveCallback(robot);
								}
							}, this), this.buttonNode);
						}
					}
				}),
				new BX.PopupWindowButtonLink({
					text : Loc.getMessage('JS_CORE_WINDOW_CANCEL'),
					className : "popup-window-button-link-cancel",
					events : {
						click: function(){
							this.popupWindow.close();
						}
					}
				})
			]
		});

		Designer.getInstance().getRobotSettingsDialog().popup = popup;
		popup.show();
	}

	initRobotSettingsControls(robot, node)
	{
		if (!Type.isArray(this.robotSettingsControls))
		{
			this.robotSettingsControls = [];
		}

		const controlNodes = node.querySelectorAll('[data-role]');
		for (let i = 0; i < controlNodes.length; ++i)
		{
			this.initRobotSettingsControl(robot, controlNodes[i]);
		}
	}

	initRobotSettingsControl(robot, controlNode)
	{
		if (!Type.isArray(this.robotSettingsControls))
		{
			this.robotSettingsControls = [];
		}

		const role = controlNode.getAttribute('data-role');

		const controlProps = {
			context: new SelectorContext({
				fields: Runtime.clone(this.#context.document.getFields()),
				useSwitcherMenu: this.#context.get('showTemplatePropertiesMenuOnSelecting'),
				rootGroupTitle: this.#context.document.title,
				userOptions: this.#context.userOptions,
			}),
			needSync: robot.draft,
			checkbox: controlNode,
		};

		if (role === SelectorManager.SELECTOR_ROLE_USER)
		{
			const fieldProperty = JSON.parse(controlNode.getAttribute('data-property'));
			controlProps.context.set('additionalUserFields', [
				...this.#getUserSelectorAdditionalFields(fieldProperty),
				...this.globalConstants.filter(constant => constant['Type'] === 'user').map(constant => ({
					id: constant['Expression'],
					title: constant['Name'],
				})),
				...this.globalVariables.filter(variable => variable['Type'] === 'user').map(variable => ({
					id: variable['Expression'],
					title: variable['Name'],
				})),
			]);
		}
		else if (role === SelectorManager.SELECTOR_ROLE_FILE)
		{
			this.robots.forEach((robot) => {
				controlProps.context.fields.push(
					...robot
						.getReturnFieldsDescription()
						.filter(field => field['Type'] === 'file')
						.map((field) => ({
							Id: `{{~${robot.getId()}:${field['Id']}}}`,
							Name: `${robot.getTitle()}: ${field['Name']}`,
							Type: 'file',
							Expression: `{{~${robot.getId()}:${field['Id']}}}`,
						}))
				);
			});
		}

		const control = SelectorManager.createSelectorByRole(role, controlProps);

		if (control && role !== SelectorManager.SELECTOR_ROLE_SAVE_STATE)
		{
			control.renderTo(controlNode);

			control.subscribe('onAskConstant', (event) => {
				const {fieldProperty} = event.getData();
				control.onFieldSelect(this.addConstant(fieldProperty));
			});
			control.subscribe('onAskParameter', (event) => {
				const {fieldProperty} = event.getData();
				control.onFieldSelect(this.addParameter(fieldProperty));
			});
			control.subscribe('onOpenFieldMenu', (event) => this.onOpenMenu(event, robot));
			control.subscribe('onOpenMenu', (event) => this.onOpenMenu(event, robot));
		}

		BX.UI.Hint.init(controlNode);

		if (control)
		{
			this.robotSettingsControls.push(control);
		}
	}

	#getUserSelectorAdditionalFields(fieldProperty): Array<object>
	{
		const additionalFields = (
			this
				.#getRobotsWithReturnFields()
				.map((robot) => (
					robot
						.getReturnFieldsDescription()
						.filter(field => field['Type'] === 'user')
						.map((field) => ({
							id: `{{~${robot.getId()}:${field['Id']}}}`,
							title: `${robot.getTitle()}: ${field['Name']}`,
						}))
				))
				.flat()
		);

		if (this.#context.get('showTemplatePropertiesMenuOnSelecting') && fieldProperty)
		{
			const ask = this.addConstant(Runtime.clone(fieldProperty));

			additionalFields.push({
				id: ask.Expression,
				title: Loc.getMessage('BIZPROC_AUTOMATION_ASK_CONSTANT'),
				tabs: ['recents', 'bpuserroles'],
				sort: 1,
			});

			const param = this.addParameter(Runtime.clone(fieldProperty));

			additionalFields.push({
				id: param.Expression,
				title: Loc.getMessage('BIZPROC_AUTOMATION_ASK_PARAMETER'),
				tabs: ['recents', 'bpuserroles'],
				sort: 2,
			});
		}

		return additionalFields;
	}

	#addRobotReturnFieldsToSelector(event: BaseEvent, skipRobot: ?Robot)
	{
		const selector = event.getData().selector;
		const isMixedCondition = event.getData().isMixedCondition;

		if (Type.isBoolean(isMixedCondition) && !isMixedCondition)
		{
			return;
		}

		const robotMenuItems = (
			this
				.#getRobotsWithReturnFields(skipRobot)
				.map((robot) => ({
					id: robot.getId(),
					title: robot.getTitle(),
					children: robot.getReturnFieldsDescription().map((field) => ({
						id: field.Expression,
						title: field.Name,
						customData: { field },
					}))
				}))
		);

		if (robotMenuItems.length > 0)
		{
			selector.addGroup('__RESULT', {
				id: '__RESULT',
				title: Loc.getMessage('BIZPROC_AUTOMATION_CMP_ROBOT_LIST'),
				children: robotMenuItems
			});
		}
	}

	#addConstantsToSelector(event: BaseEvent)
	{
		const selector = event.getData().selector;
		const isMixedCondition = event.getData().isMixedCondition;

		if (Type.isBoolean(isMixedCondition) && !isMixedCondition)
		{
			return;
		}

		// TODO - test !this.showTemplatePropertiesMenuOnSelecting
		const constants = this.getConstants().map((constant) => {
			return {
				id: constant.SystemExpression,
				title: constant.Name,
				supertitle: Loc.getMessage('BIZPROC_AUTOMATION_CMP_TEMPLATE_CONSTANTS_LIST'),
				customData: { field: constant }
			};
		});

		this.globalConstants.forEach((constant) => {
			constants.push({
				id: constant.SystemExpression,
				title: constant['Name'],
				supertitle: constant.SuperTitle,
				customData: { field: constant },
			});
		});

		if (Type.isArrayFilled(constants))
		{
			selector.addGroup(
				'__CONSTANTS',
				{
					id: '__CONSTANTS',
					title: Loc.getMessage('BIZPROC_AUTOMATION_CMP_CONSTANTS_LIST'),
					children: constants
				}
			);
		}
	}

	#addVariablesToSelector(event: BaseEvent)
	{
		const selector = event.getData().selector;
		const isMixedCondition = event.getData().isMixedCondition;

		if (Type.isBoolean(isMixedCondition) && !isMixedCondition)
		{
			return;
		}

		const gVariables = this.globalVariables.map((variable) => {
			return {
				id: variable.SystemExpression,
				title: variable.Name,
				supertitle: variable.SuperTitle,
				customData: {field: variable},
			};
		});

		if (Type.isArrayFilled(gVariables))
		{
			selector.addGroup(
				'__GLOB_VARIABLES',
				{
					id: '__GLOB_VARIABLES',
					title: Loc.getMessage('BIZPROC_AUTOMATION_CMP_GLOB_VARIABLES_LIST_1'),
					children: gVariables,
				}
			);
		}
	}

	#getRobotsWithReturnFields(skipRobot: ?Robot = undefined): Array<Robot>
	{
		const skipId = skipRobot?.getId() || '';

		return this
			.robots
			.filter((templateRobot) => (
				templateRobot.getId() !== skipId && templateRobot.hasReturnFields()
			))
		;
	}

	destroyRobotSettingsControls()
	{
		if (this.conditionSelector)
		{
			this.conditionSelector.destroy();
			this.conditionSelector = null;
		}

		if (Type.isArray(this.robotSettingsControls))
		{
			for (let i = 0; i < this.robotSettingsControls.length; ++i)
			{
				if (Type.isFunction(this.robotSettingsControls[i].destroy))
				{
					this.robotSettingsControls[i].destroy();
				}
			}
		}

		this.robotSettingsControls = null;
	}

	onBeforeSaveRobotSettings()
	{
		if (Type.isArray(this.robotSettingsControls))
		{
			for (let i = 0; i < this.robotSettingsControls.length; ++i)
			{
				if (Type.isFunction(this.robotSettingsControls[i].onBeforeSave))
				{
					this.robotSettingsControls[i].onBeforeSave();
				}
			}
		}
	}

	onResizeRobotSettings()
	{
		if (Type.isArray(this.robotSettingsControls))
		{
			for (let i = 0; i < this.robotSettingsControls.length; ++i)
			{
				if (Type.isFunction(this.robotSettingsControls[i].onPopupResize))
				{
					this.robotSettingsControls[i].onPopupResize();
				}
			}
		}
	}

	renderDelaySettings(robot)
	{
		const delay = robot.getDelayInterval().clone();
		const idSalt = Helper.generateUniqueId();

		const delayTypeNode = Dom.create("input", {
			attrs: {
				type: "hidden",
				name: "delay_type",
				value: delay.type
			}
		});
		const delayValueNode = Dom.create("input", {
			attrs: {
				type: "hidden",
				name: "delay_value",
				value: delay.value
			}
		});
		const delayValueTypeNode = Dom.create("input", {
			attrs: {
				type: "hidden",
				name: "delay_value_type",
				value: delay.valueType
			}
		});
		const delayBasisNode = Dom.create("input", {
			attrs: {
				type: "hidden",
				name: "delay_basis",
				value: delay.basis
			}
		});
		const delayWorkTimeNode = Dom.create("input", {
			attrs: {
				type: "hidden",
				name: "delay_worktime",
				value: delay.workTime ? 1 : 0
			}
		});

		const delayWaitWorkDayNode = Dom.create("input", {
			attrs: {
				type: "hidden",
				name: "delay_wait_workday",
				value: delay.waitWorkDay ? 1 : 0
			}
		});

		const delayInTimeNode = Dom.create("input", {
			attrs: {
				type: "hidden",
				name: "delay_in_time",
				value: delay.inTimeString,
			}
		});

		const delayIntervalLabelNode = Dom.create("span", {
			attrs: {
				className: "bizproc-automation-popup-settings-link bizproc-automation-delay-interval-basis"
			}
		});

		const basisFields = [];

		const docFields = this.#context.document.getFields();
		const minLimitM = this.#delayMinLimitM;

		if (Type.isArray(docFields))
		{
			for (let i = 0; i < docFields.length; ++i)
			{
				const field = docFields[i];
				if (field['Type'] === 'date' || field['Type'] === 'datetime')
				{
					basisFields.push(field);
				}
			}
		}

		const delayIntervalSelector = new DelayIntervalSelector({
			labelNode: delayIntervalLabelNode,
			onchange(delay) {
				delayTypeNode.value = delay.type;
				delayValueNode.value = delay.value;
				delayValueTypeNode.value = delay.valueType;
				delayBasisNode.value = delay.basis;
				delayWorkTimeNode.value = delay.workTime ? 1 : 0;
				delayWaitWorkDayNode.value = delay.waitWorkDay ? 1 : 0;
				delayInTimeNode.value = delay.inTimeString;
			},
			basisFields: basisFields,
			minLimitM: minLimitM,
			useAfterBasis: true,
			showWaitWorkDay: true,
		});

		let executeAfterPreviousBlock = null;
		if (robot.hasTemplate())
		{
			const executeAfterPreviousCheckbox = Dom.create("input", {
				attrs: {
					type: "checkbox",
					id: "param-group-3-1" + idSalt,
					name: "execute_after_previous",
					value: '1',
					style: 'vertical-align: middle'
				}
			});
			if (robot.isExecuteAfterPrevious())
			{
				executeAfterPreviousCheckbox.setAttribute('checked', 'checked');
			}
			executeAfterPreviousBlock = Dom.create("div", {
				attrs: { className: "bizproc-automation-popup-settings-block" },
				children: [
					executeAfterPreviousCheckbox,
					Dom.create("label", {
						attrs: {
							for: "param-group-3-1" + idSalt,
							style: 'color: #535C69'
						},
						text: Loc.getMessage('BIZPROC_AUTOMATION_CMP_AFTER_PREVIOUS_WIDE')
					})
				]
			})
		}

		const div = Dom.create("div", {
			attrs: { className: "bizproc-automation-popup-settings bizproc-automation-popup-settings-flex" },
			children: [
				Dom.create("div", {
					attrs: { className: "bizproc-automation-popup-settings-block bizproc-automation-popup-settings-block-flex" },
					children: [
						Dom.create("span", {
							attrs: { className: "bizproc-automation-popup-settings-title-wrapper" },
							children: [
								delayTypeNode,
								delayValueNode,
								delayValueTypeNode,
								delayBasisNode,
								delayWorkTimeNode,
								delayWaitWorkDayNode,
								delayInTimeNode,
								Dom.create("span", {
									attrs: { className: "bizproc-automation-popup-settings-title bizproc-automation-popup-settings-title-left" },
									text: Loc.getMessage('BIZPROC_AUTOMATION_CMP_TO_EXECUTE') + ":"
								}),
								delayIntervalLabelNode
							]
						})
					]
				}),
				executeAfterPreviousBlock
			]
		});

		delayIntervalSelector.init(delay);

		return div;
	}

	setDelaySettingsFromForm(formFields,  robot)
	{
		const delay = new DelayInterval();
		delay.setType(formFields['delay_type']);
		delay.setValue(formFields['delay_value']);
		delay.setValueType(formFields['delay_value_type']);
		delay.setBasis(formFields['delay_basis']);
		delay.setWorkTime(formFields['delay_worktime'] === '1');
		delay.setWaitWorkDay(formFields['delay_wait_workday'] === '1');
		delay.setInTime(formFields['delay_in_time'] ? formFields['delay_in_time'].split(':') : null);
		robot.setDelayInterval(delay);

		if (robot.hasTemplate())
		{
			robot.setExecuteAfterPrevious(
				formFields['execute_after_previous'] && (formFields['execute_after_previous']) === '1'
			);
		}

		return this;
	}

	renderConditionSettings(robot)
	{
		const conditionGroup = robot.getCondition();
		this.conditionSelector = new ConditionGroupSelector(conditionGroup, {
			fields: this.#context.document.getFields(),
			onOpenFieldMenu: (event) => this.onOpenMenu(event, robot),
			onOpenMenu: (event) => this.onOpenMenu(event, robot),
		});

		return Dom.create("div", {
			attrs: { className: "bizproc-automation-popup-settings" },
			children: [
				Dom.create("div", {
					attrs: { className: "bizproc-automation-popup-settings-block" },
					children: [
						Dom.create("span", {
							attrs: { className: "bizproc-automation-popup-settings-title" },
							text: Loc.getMessage('BIZPROC_AUTOMATION_ROBOT_CONDITION') + ":"
						}),
						this.conditionSelector.createNode(),
					],
				})
			]
		});
	}

	onOpenMenu(event: BaseEvent, robot: Robot): void
	{
		this.#addRobotReturnFieldsToSelector(event, robot);
		this.#addConstantsToSelector(event);
		this.#addVariablesToSelector(event);

		this.emit(
			'Template:onSelectorMenuOpen',
			{
				template: this,
				robot,
				...event.getData()
			}
		);
	}

	setConditionSettingsFromForm(formFields, robot)
	{
		robot.setCondition(ConditionGroup.createFromForm(formFields));

		return this;
	}

	renderBrokenLinkAlert(brokenLinks: [] = [])
	{
		const moreInfoNode = Tag.render`
			<div class="bizproc-automation-robot-broken-link-full-info">
				${brokenLinks.map((value) => {return Text.encode(value)}).join('<br>')}
			</div>
		`;

		const showMoreLabel = Tag.render`
			<span class="bizproc-automation-robot-broken-link-show-more">
				${Loc.getMessage('JS_BIZPROC_AUTOMATION_BROKEN_LINK_MESSAGE_ERROR_MORE_INFO')}
			</span>
		`;
		Event.bindOnce(showMoreLabel, 'click', () => {
			Dom.style(moreInfoNode, 'height', moreInfoNode.scrollHeight + 'px');
			Dom.remove(showMoreLabel);
		});

		const closeBtn = Tag.render`<span class="ui-alert-close-btn"></span>`;

		const alert = Tag.render`
			<div class="ui-alert ui-alert-warning ui-alert-icon-info">
				<div class="ui-alert-message">
					<div>
						<span>${Loc.getMessage('BIZPROC_AUTOMATION_BROKEN_LINK_MESSAGE_ERROR')}</span>
						${showMoreLabel}
					</div>
					${moreInfoNode}
				</div>
				${closeBtn}
			</div>
		`;

		Event.bindOnce(closeBtn, 'click', () => {Dom.remove(alert)});

		return alert;
	}

	saveRobotSettings(form, robot, callback, btnNode)
	{
		if (btnNode)
		{
			btnNode.classList.add('popup-window-button-wait');
		}

		this.onBeforeSaveRobotSettings();
		const formData = BX.ajax.prepareForm(form);

		const ajaxUrl = this.#context.ajaxUrl;
		const documentSigned = this.#context.signedDocument;
		BX.ajax({
			method: 'POST',
			dataType: 'json',
			url: Uri.addParam(
				ajaxUrl,
				{
					analyticsLabel: `automation_robot${robot.draft ? '_draft' : ''}_save_${robot.data.Type.toLowerCase()}`
				}
			),
			data: {
				ajax_action: 'save_robot_settings',
				document_signed: documentSigned,
				robot_json: Helper.toJsonString(robot.serialize()),
				form_data_json: Helper.toJsonString(formData['data']),
				form_data: formData['data'], /** @bug 0135641 */
			},
			onsuccess: response => {
				if (btnNode)
				{
					btnNode.classList.remove('popup-window-button-wait');
				}

				if (response.SUCCESS)
				{
					robot.updateData(response.DATA.robot);
					this.setDelaySettingsFromForm(formData['data'], robot);
					this.setConditionSettingsFromForm(formData['data'], robot);

					robot.draft = false;

					robot.reInit();
					this.markModified();
					if (callback)
					{
						callback(response.DATA)
					}
				}
				else
				{
					alert(response.ERRORS[0]);
				}
			}
		});
	}

	serialize()
	{
		const data = BX.clone(this.#data);
		data['IS_EXTERNAL_MODIFIED'] = this.isExternalModified() ? 1 : 0;
		data['ROBOTS'] = [];

		for (let i = 0; i < this.#robots.length; ++i)
		{
			data['ROBOTS'].push(this.#robots[i].serialize());
		}

		return data;
	}

	isExternalModified()
	{
		return (this.externalModified === true);
	}

	markExternalModified(modified)
	{
		this.externalModified = modified !== false;
	}

	getRobotById(id)
	{
		return this.#robots.find(robot => robot.getId() === id);
	}

	isModified()
	{
		return this.modified;
	}

	markModified(modified)
	{
		this.modified = modified !== false;

		if (this.modified)
		{
			this.emit('Template:modified');
		}
	}

	getConstants(): []
	{
		const constants = [];

		Object.keys(this.#data.CONSTANTS).forEach(id => {
			const constant = Runtime.clone(this.#data.CONSTANTS[id]);

			constant.Id = id;
			constant.ObjectId = 'Constant';
			constant.SystemExpression = '{=Constant:' + id + '}';
			constant.Expression = '{{~&:' + id + '}}';

			constants.push(constant);
		});

		return constants;
	}

	getConstant(id)
	{
		const constants = this.getConstants();

		for (let i = 0; i < constants.length; ++i)
		{
			if (constants[i].Id === id)
			{
				return constants[i];
			}
		}

		return null;
	}

	addConstant(property)
	{
		const id = property.Id || this.generatePropertyId('Constant', this.#data.CONSTANTS);

		if (this.#data.CONSTANTS[id])
		{
			throw `Constant with id "${id}" is already exists`;
		}

		this.#data.CONSTANTS[id] = property;

		this.emit('Template:constant:add');
		// if (this.component)
		// {
		// 	BX.onCustomEvent(this.component, 'onTemplateConstantAdd', [this, this.getConstant(id)]);
		// }

		return this.getConstant(id);
	}

	updateConstant(id, property)
	{
		if (!this.#data.CONSTANTS[id])
		{
			throw `Constant with id "${id}" does not exists`;
		}

		//TODO: only Description yet.
		this.#data.CONSTANTS[id].Description = property.Description;

		this.emit('Template:constant:update', {constant: this.getConstant(id)});
		// if (this.component)
		// {
		// 	BX.onCustomEvent(this.component, 'onTemplateConstantUpdate', [this, this.getConstant(id)]);
		// }

		return this.getConstant(id);
	}

	deleteConstant(id)
	{
		delete this.#data.CONSTANTS[id];

		return true;
	}

	setConstantValue(id, value)
	{
		if (this.#data.CONSTANTS[id])
		{
			this.#data.CONSTANTS[id]['Default'] = value;

			return true;
		}

		return false;
	}

	getParameters()
	{
		const params = [];

		Object.keys(this.#data.PARAMETERS).forEach(id => {
			const param = BX.clone(this.#data.PARAMETERS[id]);

			param.Id = id;
			param.ObjectId = 'Template';
			param.SystemExpression = '{=Template:' + id + '}';
			param.Expression = '{{~*:' + id + '}}';

			params.push(param);
		});

		return params;
	}

	getParameter(id)
	{
		const params = this.getParameters();

		for (let i = 0; i < params.length; ++i)
		{
			if (params[i].Id === id)
			{
				return params[i];
			}
		}

		return null;
	}

	addParameter(property)
	{
		const id = property.Id || this.generatePropertyId('Parameter', this.#data.PARAMETERS);

		if (this.#data.PARAMETERS[id])
		{
			throw `Parameter with id "${id}" is already exists`;
		}

		this.#data.PARAMETERS[id] = property;

		this.emit('Template:parameter:add', {parameter: this.getParameter(id)});
		// if (this.component)
		// {
		// 	BX.onCustomEvent(this.component, 'onTemplateParameterAdd', [this, this.getParameter(id)]);
		// }

		return this.getParameter(id);
	}

	updateParameter(id, property)
	{
		if (!this.#data.PARAMETERS[id])
		{
			throw `Parameter with id "${id}" does not exists`;
		}

		//TODO: only Description yet.
		this.#data.PARAMETERS[id].Description = property.Description;

		this.emit('Template:parameter:update', {parameter: this.getParameter(id)});
		// if (this.component)
		// {
		// 	BX.onCustomEvent(this.component, 'onTemplateParameterUpdate', [this, this.getParameter(id)]);
		// }

		return this.getParameter(id);
	}

	deleteParameter(id)
	{
		delete this.#data.PARAMETERS[id];

		return true;
	}

	setParameterValue(id, value)
	{
		if (this.#data.PARAMETERS[id])
		{
			this.#data.PARAMETERS[id]['Default'] = value;

			return true;
		}

		return false;
	}

	getVariables(): []
	{
		const variables = [];

		Object.keys(this.#data.VARIABLES).forEach(id => {
			const variable = Runtime.clone(this.#data.VARIABLES[id]);

			variable.Id = id;
			variable.ObjectId = 'Variable';
			variable.SystemExpression = '{=Variable:' + id + '}';
			variable.Expression = '{=Variable:' + id + '}';

			variables.push(variable);
		});

		return variables;
	}

	generatePropertyId(prefix, existsList)
	{
		let index;
		for(index = 1; index <= 1000; ++index)
		{
			if (!existsList[prefix + index])
			{
				break; //found
			}
		}

		return prefix + index;
	}

	collectUsages()
	{
		const usages = {
			Document: new Set(),
			Constant: new Set(),
			Variable: new Set(),
			Parameter: new Set(),
			GlobalConstant: new Set(),
			GlobalVariable: new Set(),
			Activity: new Set()
		};

		this.#robots.forEach(robot => {
			const robotUsages = robot.collectUsages();

			Object.keys(usages).forEach(key => {
				robotUsages[key].forEach(usage => {
					if (!usages[key].has(usage))
					{
						usages[key].add(usage);
					}
				});
			});
		});

		return usages;
	}

	subscribeRobotEvents(eventName: string, listener: (BaseEvent) => void): this
	{
		this.#robots.forEach(robot => robot.subscribe(eventName, listener))

		return this;
	}

	unsubscribeRobotEvents(eventName: string, listener: (BaseEvent) => void)
	{
		this.#robots.forEach(robot => robot.unsubscribe(eventName, listener));

		return this;
	}

	getRobotDescription(type: string): ?object
	{
		return this.#context.availableRobots.find(item => item['CLASS'] === type);
	}

	get globalConstants(): []
	{
		return this.#context.automationGlobals ? this.#context.automationGlobals.globalConstants : [];
	}

	get globalVariables(): []
	{
		return this.#context.automationGlobals ? this.#context.automationGlobals.globalVariables : [];
	}
}