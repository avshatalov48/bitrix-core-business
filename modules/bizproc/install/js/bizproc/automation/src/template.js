import { Type, Dom, Loc, Event, Runtime, Uri, Text, Tag, ajax } from 'main.core';
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
	SelectorManager, SelectorItemsManager, enrichFieldsWithModifiers,
} from 'bizproc.automation';
import { SaveButton, BaseButton, CancelButton } from 'ui.buttons';
import { Robot } from './robot';
import { UserOptions } from './user-options';
import { ViewMode } from './view-mode';
import { Helper } from './helper';
import { HelpHint } from './help-hint';
import { DelayInterval } from './delay-interval';
import { Popup } from 'main.popup';

import 'ui.hint';
import showExecutionQueuePopup from './views/execution-queue-popup';

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

			this.markExternalModified(this.#data.IS_EXTERNAL_MODIFIED);
			this.markModified(false);
		}

		this.#viewMode = ViewMode.fromRaw(viewMode);

		if (!this.#viewMode.isNone())
		{
			this.#templateNode = this.#templateContainerNode.querySelector(
				`[data-role="automation-template"][data-status-id="${this.#data.DOCUMENT_STATUS}"]`,
			);
			this.#listNode = this.#templateNode.querySelector('[data-role="robot-list"]');
			this.#buttonsNode = this.#templateNode.querySelector('[data-role="buttons"]');

			this.initRobots();
			this.initButtons();

			if (!this.isExternalModified() && this.canEdit())
			{
				// register DD
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
		this.#robots.forEach((robot) => robot.destroy());
	}

	static copyRobotTo(dstTemplate: Template, robot: Robot, beforeRobot: ?Robot): Robot
	{
		const copiedRobot = robot.copyTo(dstTemplate, beforeRobot);
		dstTemplate.emit('Template:robot:add', { robot: copiedRobot });
	}

	canEdit(): boolean
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

		this.#robots.forEach((robot) => {
			if (robot.isSelected())
			{
				selectedRobots.push(robot.data.Name);
			}
		});

		return selectedRobots;
	}

	getActivatedRobotNames(): Array<string>
	{
		const activatedRobots = [];
		this.#robots.forEach((robot) => {
			if (robot.isActivated())
			{
				activatedRobots.push(robot.data.Name);
			}
		});

		return activatedRobots;
	}

	getDeactivatedRobotNames(): Array<string>
	{
		const deactivatedRobots = [];
		this.#robots.forEach((robot) => {
			if (!robot.isActivated())
			{
				deactivatedRobots.push(robot.data.Name);
			}
		});

		return deactivatedRobots;
	}

	getSerializedRobots(): []
	{
		const serialized = [];
		this.#robots.forEach((robot) => serialized.push(robot.serialize()));

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

	getStatus(): ?{}
	{
		return this.#context.document.statusList.find((status) => String(status.STATUS_ID) === this.getStatusId());
	}

	getTemplateId(): number
	{
		const id = parseInt(this.#data.ID, 10);

		return Number.isNaN(id) ? 0 : id;
	}

	initButtons()
	{
		if (this.isExternalModified())
		{
			this.createExternalLocker();
			this.createManageModeButton();
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
				this.#robots.forEach((robot) => {
					if (robot.isInvalid())
					{
						robot.enableManageMode(false);
					}
					else
					{
						robot.enableManageMode(isActive);
					}
				});
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
				this.#robots.forEach((robot) => {
					robot.disableManageMode()

					if (!robot.isInvalid())
					{
						const draggableNode = robot.node.querySelector('.bizproc-automation-robot-container-wrapper');
						if (draggableNode)
						{
							Dom.addClass(draggableNode, 'bizproc-automation-robot-container-wrapper-draggable');
						}
					}
				});
			}
		}
	}

	enableDragAndDrop()
	{
		this.#robots.forEach((robot) => {
			if (!robot.isInvalid())
			{
				robot.registerItem(robot.node);

				const draggableNode = robot.node.querySelector('.bizproc-automation-robot-container-wrapper');
				if (draggableNode)
				{
					Dom.addClass(draggableNode, 'bizproc-automation-robot-container-wrapper-draggable');
				}
			}
		});
	}

	disableDragAndDrop()
	{
		this.#robots.forEach((robot) => robot.unregisterItem(robot.node));

		this.#templateNode.querySelectorAll('.bizproc-automation-robot-container-wrapper').forEach((node) => {
			Dom.removeClass(node, 'bizproc-automation-robot-container-wrapper-draggable');
		});
	}

	createExternalEditTemplateButton(): undefined | boolean
	{
		if (Type.isNil(this.#context.bizprocEditorUrl))
		{
			return false;
		}

		const anchor = Tag.render`
			<a class="bizproc-automation-robot-btn-set" href="#" target="_top">
				${Loc.getMessage('BIZPROC_AUTOMATION_CMP_EXTERNAL_EDIT')}
			</a>
		`;
		Event.bind(anchor, 'click', (event) => {
			event.preventDefault();

			if (!this.#viewMode.isManage())
			{
				this.onExternalEditTemplateButtonClick(anchor);
			}
		});

		if (this.#context.bizprocEditorUrl.length === 0)
		{
			Dom.addClass(anchor, 'bizproc-automation-robot-btn-set-locked');
		}

		Dom.append(anchor, this.#buttonsNode);
	}

	createManageModeButton()
	{
		if (!this.#context.canManage)
		{
			return;
		}

		const manageButton = Tag.render`
			<a class="bizproc-automation-robot-btn-set" target="_top" style="cursor: pointer">
				${Loc.getMessage('BIZPROC_AUTOMATION_CMP_MANAGE_ROBOTS_1')}
			</a>
		`;
		Event.bind(manageButton, 'click', (event) => {
			event.preventDefault();
			this.onManageModeButtonClick(manageButton);
		});

		Dom.append(manageButton, this.#buttonsNode);
	}

	onManageModeButtonClick(manageButtonNode: HTMLElement)
	{
		if (this.canEdit())
		{
			this.emit('Template:enableManageMode', {
				documentStatus: this.#data.DOCUMENT_STATUS,
			});
		}
		else
		{
			HelpHint.showNoPermissionsHint(manageButtonNode);
		}
	}

	createConstantsEditButton(): boolean | undefined
	{
		if (Type.isNil(this.#context.constantsEditorUrl))
		{
			return false;
		}

		const url = (
			this.#viewMode.isManage()
				? '#'
				: this.#context.constantsEditorUrl.replace('#ID#', this.getTemplateId())
		);

		if (url.length === 0)
		{
			return false;
		}

		const anchor = Tag.render`
			<a class="bizproc-automation-robot-btn-set" href="${Text.encode(url)}">
				${Loc.getMessage('BIZPROC_AUTOMATION_CMP_CONSTANTS_EDIT')}
			</a>
		`;
		Dom.append(anchor, this.#buttonsNode);
	}

	createParametersEditButton(): boolean | undefined
	{
		if (Type.isNil(this.#context.parametersEditorUrl))
		{
			return false;
		}

		const url = this.#context.parametersEditorUrl.replace('#ID#', this.getTemplateId());

		if (url.length === 0 || this.#viewMode.isManage())
		{
			return false;
		}

		const anchor = Tag.render`
			<a class="bizproc-automation-robot-btn-set" href="${Text.encode(url)}">
				${Loc.getMessage('BIZPROC_AUTOMATION_CMP_PARAMETERS_EDIT')}
			</a>
		`;
		Dom.append(anchor, this.#buttonsNode);
	}

	createExternalLocker()
	{
		const div = Tag.render`
			<div class="bizproc-automation-robot-container">
				<div class="bizproc-automation-robot-container-wrapper bizproc-automation-robot-container-wrapper-lock">
					<div class="bizproc-automation-robot-deadline"></div>
					<div class="bizproc-automation-robot-title">
						${Loc.getMessage('BIZPROC_AUTOMATION_CMP_EXTERNAL_EDIT_TEXT')}
					</div>
				</div>
			</div>
		`;

		if (this.#viewMode.isEdit())
		{
			const settingsBtn = Tag.render`
				<div class="bizproc-automation-robot-btn-settings">
					${Loc.getMessage('BIZPROC_AUTOMATION_CMP_EDIT')}
				</div>
			`;
			Event.bind(div, 'click', (event) => {
				event.stopPropagation();
				if (!this.#viewMode.isManage())
				{
					this.onExternalEditTemplateButtonClick(div);
				}
			});
			Dom.append(settingsBtn, div);

			const deleteBtn = Tag.render`<span class="bizproc-automation-robot-btn-delete"></span>`;
			Event.bind(deleteBtn, 'click', (event) => {
				event.stopPropagation();
				if (!this.#viewMode.isManage())
				{
					this.onUnsetExternalModifiedClick(deleteBtn);
				}
			});
			Dom.append(deleteBtn, div.lastChild);
		}

		Dom.append(div, this.#listNode);
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
			this.#robots.forEach((robot) => robot.onSearch(event));
		}
	}

	onExternalModifiedSearch(event)
	{
		if (this.#templateNode)
		{
			const query = event.getData().queryString;
			Dom[query ? 'addClass' : 'removeClass'](this.#templateNode, '--search-mismatch');
		}
	}

	onExternalEditTemplateButtonClick(button)
	{
		if (!this.canEdit())
		{
			HelpHint.showNoPermissionsHint(button);

			return;
		}

		if (this.#context.bizprocEditorUrl.length === 0)
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
		if (!this.canEdit())
		{
			HelpHint.showNoPermissionsHint(button);

			return;
		}

		this.#templateNode = null;

		this.markExternalModified(false);
		this.markModified();
		this.reInit(null, this.#viewMode.intoRaw());
	}

	openBizprocEditor(templateId)
	{
		top.window.location.href = this.#context.bizprocEditorUrl.replace('#ID#', templateId);
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
			Type: robotData.CLASS,
			Properties: {
				Title: robotData.NAME,
			},
			DialogContext: robotData.DIALOG_CONTEXT,
		};

		if (this.#robots.length > 0)
		{
			const parentRobot = this.#robots[this.#robots.length - 1];
			if (!parentRobot.getDelayInterval().isNow() || parentRobot.isExecuteAfterPrevious())
			{
				initData.Delay = parentRobot.getDelayInterval().serialize();
				initData.ExecuteAfterPrevious = 1;
			}
		}

		robot.draft = true;
		robot.init(initData, this.#viewMode);

		this.insertRobot(robot);
		this.insertRobotNode(robot.node);
		this.emit('Template:robot:add', { robot });

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
		for (let i = 0; i < this.#robots.length; ++i)
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
			Dom.append(robotNode, this.#listNode);
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
			return;
		}

		const robotBrokenLinks = robot.getBrokenLinks();

		const formName = 'bizproc_automation_robot_dialog';
		const form = Tag.render`
			<form name="${formName}">
				${this.#renderExecutionQueue(robot)}
				${this.renderDelaySettings(robot)}
				${this.renderConditionSettings(robot)}
				${robotBrokenLinks.length > 0 ? this.renderBrokenLinkAlert(robotBrokenLinks) : ''}
			</form>
		`;

		Designer.getInstance().setRobotSettingsDialog({
			template: this,
			context,
			robot,
			form,
		});

		context.DOCUMENT_CATEGORY_ID = this.#context.document.getCategoryId();
		if (
			Type.isPlainObject(robot.data.DialogContext)
			&& !Type.isNil(robot.data.DialogContext.addMenuGroup)
		)
		{
			context.addMenuGroup = robot.data.DialogContext.addMenuGroup;
		}

		ajax({
			method: 'POST',
			dataType: 'html',
			url: Uri.addParam(
				this.#context.ajaxUrl,
				{
					analyticsLabel: `automation_robot${robot.draft ? '_draft' : ''}_settings_${robot.data.Type.toLowerCase()}`,
				},
			),
			data: {
				ajax_action: 'get_robot_dialog',
				document_signed: this.#context.signedDocument,
				document_status: this.#context.document.getCurrentStatusId(),
				context,
				robot_json: Helper.toJsonString(robot.serialize()),
				form_name: formName,
			},
			onsuccess: (html) => {
				if (html)
				{
					const dialogRows = Dom.create('div', { html });
					Dom.append(dialogRows, form);
				}

				this.showRobotSettingsPopup(robot, form, saveCallback);
			},
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
				this.#userOptions.get('defaults', 'robot_settings_popup_width', 580),
				10,
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

		const popup = new Popup({
			id: Helper.generateUniqueId(),
			bindElement: null,
			content: form,
			closeByEsc: true,
			buttons: [
				new SaveButton({
					onclick: (button: BaseButton) => {
						const isNewRobot = robot.draft;
						const callback = () => {
							popup.close();
							if (isNewRobot)
							{
								this.emit('Template:robot:add', { robot });
							}

							if (saveCallback)
							{
								saveCallback(robot);
							}
						};

						this.saveRobotSettings(form, robot, callback, button.getContainer());
					},
				}),
				new CancelButton({
					text: Loc.getMessage('BIZPROC_JS_AUTOMATION_CANCEL_BUTTON_CAPS'),
					onclick: () => {
						popup.close();
					},
				}),
			],
			width: popupWidth,
			minWidth: popupMinWidth,
			minHeight: 100,
			contentPadding: 12,
			resizable: true,
			closeIcon: true,
			events: {
				onPopupClose: () => {
					Designer.getInstance().setRobotSettingsDialog(null);
					this.destroyRobotSettingsControls();
					popup.destroy();
					this.emit('Template:robot:closeSettings');
				},
				onPopupResize: () => {
					this.onResizeRobotSettings();
				},
				onPopupResizeEnd: () => {
					if (this.#userOptions)
					{
						this.#userOptions.set('defaults', 'robot_settings_popup_width', popup.getWidth());
					}
				},
			},
			titleBar: {
				content: titleBarContent,
			},
			draggable: { restrict: false },
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
		for (const controlNode of controlNodes)
		{
			this.initRobotSettingsControl(robot, controlNode);
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
				...this.globalConstants.filter((constant) => constant.Type === 'user').map((constant) => ({
					id: constant.Expression,
					title: constant.Name,
				})),
				...this.globalVariables.filter((variable) => variable.Type === 'user').map((variable) => ({
					id: variable.Expression,
					title: variable.Name,
				})),
			]);
		}
		else if (role === SelectorManager.SELECTOR_ROLE_FILE)
		{
			this.robots.forEach((robot) => {
				controlProps.context.fields.push(
					...robot
						.getReturnFieldsDescription()
						.filter((field) => field.Type === 'file')
						.map((field) => ({
							Id: `{{~${robot.getId()}:${field.Id}}}`,
							Name: `${robot.getTitle()}: ${field.Name}`,
							Type: 'file',
							Expression: `{{~${robot.getId()}:${field.Id}}}`,
						})),
				);
			});
		}

		const control = SelectorManager.createSelectorByRole(role, controlProps);

		if (control && role !== SelectorManager.SELECTOR_ROLE_SAVE_STATE)
		{
			control.renderTo(controlNode);

			control.subscribe('onAskConstant', (event) => {
				const { fieldProperty } = event.getData();
				control.onFieldSelect(this.addConstant(fieldProperty));
			});
			control.subscribe('onAskParameter', (event) => {
				const { fieldProperty } = event.getData();
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
				.getRobotsWithReturnFields()
				.flatMap((robot) => (
					robot
						.getReturnFieldsDescription()
						.filter((field) => field.Type === 'user')
						.map((field) => ({
							id: `{{~${robot.getId()}:${field.Id}}}`,
							title: `${robot.getTitle()}: ${field.Name}`,
						}))
				))
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

	getRobotsWithReturnFields(skipRobot: ?Robot): Array<Robot>
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

		const {
			root,
			delayTypeNode,
			delayValueNode,
			delayValueTypeNode,
			delayBasisNode,
			delayWorkTimeNode,
			delayWaitWorkDayNode,
			delayInTimeNode,
			delayIntervalLabelNode,
		} = Tag.render`
			<div class="bizproc-automation-popup-settings">
				<div class="bizproc-automation-popup-settings-block">
					<span class="bizproc-automation-popup-settings-title-wrapper">
						<input
							ref="delayTypeNode"
							type="hidden"
							name="delay_type"
							value="${Text.encode(delay.type)}"
						/>
						<input
							ref="delayValueNode"
							type="hidden"
							name="delay_value"
							value="${Text.encode(delay.value)}"
						/>
						<input
							ref="delayValueTypeNode"
							type="hidden"
							name="delay_value_type"
							value="${Text.encode(delay.valueType)}"
						/>
						<input
							ref="delayBasisNode"
							type="hidden"
							name="delay_basis"
							value="${Text.encode(delay.basis)}"
						/>
						<input 
							ref="delayWorkTimeNode"
							type="hidden"
							name="delay_worktime"
							value="${delay.workTime ? 1 : 0}"
						/>
						<input
							ref="delayWaitWorkDayNode"
							type="hidden"
							name="delay_wait_workday"
							value="${delay.waitWorkDay ? 1 : 0}"
						/>
						<input
							ref="delayInTimeNode"
							type="hidden"
							name="delay_in_time"
							value="${Text.encode(delay.inTimeString)}"
						/>
						<span
							class="bizproc-automation-popup-settings-title bizproc-automation-popup-settings-title-left"
						>
							${Loc.getMessage('BIZPROC_JS_AUTOMATION_TO_EXECUTE_TITLE')}
						</span>
						<span
							ref="delayIntervalLabelNode"
							class="bizproc-automation-popup-settings-link bizproc-automation-delay-interval-basis"
						></span>
					</span>
				</div>
			</div>
		`;

		const basisFields = [];

		const docFields = this.#context.document.getFields();
		const minLimitM = this.#delayMinLimitM;

		if (Type.isArray(docFields))
		{
			for (const field of docFields)
			{
				if (field.Type === 'date' || field.Type === 'datetime')
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
			basisFields,
			minLimitM,
			useAfterBasis: true,
			showWaitWorkDay: true,
		});
		delayIntervalSelector.init(delay);

		return root;
	}

	setDelaySettingsFromForm(formFields, robot)
	{
		const delay = new DelayInterval();
		delay.setType(formFields.delay_type);
		delay.setValue(formFields.delay_value);
		delay.setValueType(formFields.delay_value_type);
		delay.setBasis(formFields.delay_basis);
		delay.setWorkTime(formFields.delay_worktime === '1');
		delay.setWaitWorkDay(formFields.delay_wait_workday === '1');
		delay.setInTime(formFields.delay_in_time ? formFields.delay_in_time.split(':') : null);
		robot.setDelayInterval(delay);

		if (robot.hasTemplate())
		{
			robot.setExecuteAfterPrevious(
				formFields.execute_after_previous && (formFields.execute_after_previous) === '1',
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
			caption: {
				head: Loc.getMessage('BIZPROC_JS_AUTOMATION_ROBOT_CONDITION_TITLE'),
			},
			isExpanded: this.#userOptions?.get('defaults', 'isConditionGroupExpanded', 'N') === 'Y',
		});

		this.conditionSelector.subscribe('onToggleGroupViewClick', (event: BaseEvent) => {
			const data = event.getData();
			this.#userOptions.set('defaults', 'isConditionGroupExpanded', data.isExpanded ? 'Y' : 'N');
		});

		return this.conditionSelector.createNode();
	}

	#renderExecutionQueue(robot): HTMLDivElement
	{
		const title = (
			robot.isExecuteAfterPrevious()
				? Loc.getMessage('BIZPROC_JS_AUTOMATION_EXECUTION_QUEUE_AFTER_PREVIOUS_TITLE')
				: Loc.getMessage('BIZPROC_JS_AUTOMATION_EXECUTION_QUEUE_PARALLEL_TITLE')
		);
		const value = robot.isExecuteAfterPrevious() ? '1' : '0';

		const { root, executionQueueLink, input } = Tag.render`
			<div class="bizproc-automation-popup-settings">
				<div class="bizproc-automation-popup-settings-block">
					<span class="bizproc-automation-popup-settings-title">
						${Loc.getMessage('BIZPROC_JS_AUTOMATION_EXECUTION_QUEUE_TITLE')}
					</span>
					<span class="bizproc-automation-popup-settings-link-wrapper">
						<a ref="executionQueueLink" class="bizproc-automation-popup-settings-link">${title}</a>
					</span>
					<input ref="input" type="hidden" value="${value}" name="execute_after_previous"/>
				</div>
			</div>
		`;
		Event.bind(executionQueueLink, 'click', () => {
			showExecutionQueuePopup({
				bindElement: executionQueueLink,
				currentValue: input.value,
				onSubmitButtonClick: (formData: FormData) => {
					const afterPrevious = formData.get('execution') === 'afterPrevious';
					Dom.adjust(input, { attrs: { value: afterPrevious ? '1' : '0' } });
					Dom.adjust(
						executionQueueLink,
						{
							text: (
								afterPrevious
									? Loc.getMessage('BIZPROC_JS_AUTOMATION_EXECUTION_QUEUE_AFTER_PREVIOUS_TITLE')
									: Loc.getMessage('BIZPROC_JS_AUTOMATION_EXECUTION_QUEUE_PARALLEL_TITLE')
							),
						},
					);
				},
			});
		});

		return root;
	}

	onOpenMenu(event: BaseEvent, robot: Robot): void
	{
		const selector = event.getData().selector;
		const isMixedCondition = event.getData().isMixedCondition;

		const needAddGroups = !(Type.isBoolean(isMixedCondition) && !isMixedCondition);
		if (needAddGroups)
		{
			const selectorManager = new SelectorItemsManager({
				activityResultFields: this.#getRobotResultFieldForSelector(robot),
				constants: this.getConstants(),
				// variables: this.getVariables(),
				globalConstants: this.globalConstants,
				globalVariables: this.globalVariables,
			});

			selectorManager.groupsWithChildren.forEach((group) => {
				selector.addGroup(group.id, group);
			});
		}

		this.emit(
			'Template:onSelectorMenuOpen',
			{
				template: this,
				robot,
				...event.getData(),
			},
		);
	}

	#getRobotResultFieldForSelector(skipRobot): Array<{id: string, title: string, fields: Array<Field>}>
	{
		return (
			this.getRobotsWithReturnFields(skipRobot)
				.map((robotWithReturnFields) => {
					return {
						id: robotWithReturnFields.getId(),
						title: robotWithReturnFields.getTitle(),
						fields: enrichFieldsWithModifiers(
							robotWithReturnFields.getReturnFieldsDescription(),
							robotWithReturnFields.getId(),
							{
								friendly: false,
								printable: false,
								server: false,
								responsible: false,
								shortLink: true,
							},
						),
					};
				})
		);
	}

	setConditionSettingsFromForm(formFields, robot)
	{
		robot.setCondition(ConditionGroup.createFromForm(formFields));

		return this;
	}

	renderBrokenLinkAlert(brokenLinks: [] = []): HTMLDivElement
	{
		const moreInfoNode = Tag.render`
			<div class="bizproc-automation-robot-broken-link-full-info">
				${brokenLinks
					.map((value) => Text.encode(value))
					.join('<br>')
				}
			</div>
		`;

		const showMoreLabel = Tag.render`
			<span class="bizproc-automation-robot-broken-link-show-more">
				${Loc.getMessage('JS_BIZPROC_AUTOMATION_BROKEN_LINK_MESSAGE_ERROR_MORE_INFO')}
			</span>
		`;
		Event.bindOnce(showMoreLabel, 'click', () => {
			Dom.style(moreInfoNode, 'height', `${moreInfoNode.scrollHeight}px`);
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

		Event.bindOnce(closeBtn, 'click', () => {
			Dom.remove(alert);
		});

		return alert;
	}

	saveRobotSettings(form, robot, callback, btnNode)
	{
		if (btnNode)
		{
			Dom.addClass(btnNode, 'ui-btn-wait');
		}

		this.onBeforeSaveRobotSettings();
		const formData = BX.ajax.prepareForm(form);
		const robotData = robot.onBeforeSaveRobotSettings(formData);

		const ajaxUrl = this.#context.ajaxUrl;
		const documentSigned = this.#context.signedDocument;
		ajax({
			method: 'POST',
			dataType: 'json',
			url: Uri.addParam(
				ajaxUrl,
				{
					analyticsLabel: `automation_robot${robot.draft ? '_draft' : ''}_save_${robot.data.Type.toLowerCase()}`,
				},
			),
			data: {
				ajax_action: 'save_robot_settings',
				document_signed: documentSigned,
				robot_json: Helper.toJsonString(robot.serialize()),
				form_data_json: Helper.toJsonString({ ...formData.data, ...robotData }),
				form_data: formData.data, /** @bug 0135641 */
			},
			onsuccess: (response) => {
				if (btnNode)
				{
					Dom.removeClass(btnNode, 'ui-btn-wait');
				}

				if (response.SUCCESS)
				{
					robot.updateData(response.DATA.robot);
					this.setDelaySettingsFromForm(formData.data, robot);
					this.setConditionSettingsFromForm(formData.data, robot);

					robot.draft = false;

					robot.reInit();
					this.markModified();
					if (callback)
					{
						callback(response.DATA);
					}
				}
				else
				{
					alert(response.ERRORS[0]);
				}
			},
		});
	}

	serialize()
	{
		const data = Runtime.clone(this.#data);
		data.IS_EXTERNAL_MODIFIED = this.isExternalModified() ? 1 : 0;
		data.ROBOTS = [];

		for (let i = 0; i < this.#robots.length; ++i)
		{
			data.ROBOTS.push(this.#robots[i].serialize());
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
		return this.#robots.find((robot) => robot.getId() === id);
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

		Object.keys(this.#data.CONSTANTS).forEach((id) => {
			const constant = Runtime.clone(this.#data.CONSTANTS[id]);

			constant.Id = id;
			constant.ObjectId = 'Constant';
			constant.SystemExpression = `{=Constant:${id}}`;
			constant.Expression = `{{~&:${id}}}`;
			constant.SuperTitle = Loc.getMessage('BIZPROC_AUTOMATION_CMP_TEMPLATE_CONSTANTS_LIST');

			constants.push(constant);
		});

		return constants;
	}

	getConstant(id)
	{
		const constants = this.getConstants();

		for (const constant of constants)
		{
			if (constant.Id === id)
			{
				return constant;
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

		this.emit('Template:constant:update', { constant: this.getConstant(id) });
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
			this.#data.CONSTANTS[id].Default = value;

			return true;
		}

		return false;
	}

	getParameters()
	{
		const params = [];

		Object.keys(this.#data.PARAMETERS).forEach((id) => {
			const param = Runtime.clone(this.#data.PARAMETERS[id]);

			param.Id = id;
			param.ObjectId = 'Template';
			param.SystemExpression = `{=Template:${id}}`;
			param.Expression = `{{~*:${id}}}`;

			params.push(param);
		});

		return params;
	}

	getParameter(id)
	{
		const params = this.getParameters();

		for (const param of params)
		{
			if (param.Id === id)
			{
				return param;
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

		this.emit('Template:parameter:add', { parameter: this.getParameter(id) });
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

		// TODO: only Description yet.
		this.#data.PARAMETERS[id].Description = property.Description;

		this.emit('Template:parameter:update', { parameter: this.getParameter(id) });
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
			this.#data.PARAMETERS[id].Default = value;

			return true;
		}

		return false;
	}

	getVariables(): []
	{
		const variables = [];

		Object.keys(this.#data.VARIABLES).forEach((id) => {
			const variable = Runtime.clone(this.#data.VARIABLES[id]);

			variable.Id = id;
			variable.ObjectId = 'Variable';
			variable.SystemExpression = `{=Variable:${id}}`;
			variable.Expression = `{=Variable:${id}}`;

			variables.push(variable);
		});

		return variables;
	}

	generatePropertyId(prefix, existsList)
	{
		let index;
		for (index = 1; index <= 1000; ++index)
		{
			if (!existsList[prefix + index])
			{
				break; // found
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
			Activity: new Set(),
		};

		this.#robots.forEach((robot) => {
			const robotUsages = robot.collectUsages();

			Object.keys(usages).forEach((key) => {
				robotUsages[key].forEach((usage) => {
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
		this.#robots.forEach((robot) => robot.subscribe(eventName, listener));

		return this;
	}

	unsubscribeRobotEvents(eventName: string, listener: (BaseEvent) => void)
	{
		this.#robots.forEach((robot) => robot.unsubscribe(eventName, listener));

		return this;
	}

	getRobotDescription(type: string): ?object
	{
		return this.#context.availableRobots.find((item) => item.CLASS === type);
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
