import { Dom, Type, Event, Text, Loc, Runtime, Tag } from 'main.core';
import { EventEmitter } from 'main.core.events';
import { Template } from './template';
import { DelayInterval } from './delay-interval';
import { ViewMode } from './view-mode';
import { HelpHint } from './help-hint';
import { ConditionGroup, Helper, Document } from 'bizproc.automation';
import { Tracker } from './tracker/tracker';
import { TrackingStatus } from './tracker/types';
import { Menu, MenuItem } from 'main.popup';

export class Robot extends EventEmitter
{
	SYSTEM_EXPRESSION_PATTERN = '\\{=\\s*(?<object>[a-z0-9_]+)\\s*\\:\\s*(?<field>[a-z0-9_\\.]+)(\\s*>\\s*(?<mod1>[a-z0-9_\\:]+)(\\s*,\\s*(?<mod2>[a-z0-9_]+))?)?\\s*\\}';

	#data: Object<string, any>;
	#document: Document;
	#template: ?Template;
	#tracker: ?Object;
	#delay: DelayInterval;
	#node: HTMLElement;
	#condition: ConditionGroup;
	#isDraft: boolean;

	#isFrameMode: boolean;
	#viewMode: ViewMode;

	#customOnBeforeSaveRobotSettings: Function = () => {};

	constructor(params: {
		document: Document,
		template: ?Template,
		isFrameMode: boolean,
		tracker: Tracker,
	})
	{
		super();
		this.setEventNamespace('BX.Bizproc.Automation');

		this.#document = params.document;
		if (!Type.isNil(params.template))
		{
			this.#template = params.template;
		}
		this.#isFrameMode = params.isFrameMode;
		this.#viewMode = ViewMode.none();
		this.#tracker = params.tracker;
		this.#isDraft = false;

		this.#delay = new DelayInterval();
	}

	get node()
	{
		return this.#node;
	}

	get data()
	{
		return {
			...this.#data,
			Condition: this.#condition.serialize(),
			Delay: this.#delay.serialize(),
		};
	}

	get draft()
	{
		return this.#isDraft;
	}

	set draft(draft: boolean)
	{
		this.#isDraft = draft;
	}

	get template()
	{
		return this.#template;
	}

	hasTemplate(): boolean
	{
		return !Type.isNil(this.#template);
	}

	getTemplate(): ?Template
	{
		return this.#template;
	}

	getDocument(): Document
	{
		return this.#document;
	}

	static generateName(): string
	{
		return (
			`A${parseInt(Math.random() * 100_000, 10)
			}_${parseInt(Math.random() * 100_000, 10)
			}_${parseInt(Math.random() * 100_000, 10)
			}_${parseInt(Math.random() * 100_000, 10)}`
		);
	}

	clone(): Robot
	{
		const clonedRobot = new Robot({
			document: this.#document,
			template: this.#template,
			isFrameMode: this.#isFrameMode,
			tracker: this.#tracker,
		});

		const robotData = {
			...Runtime.clone(this.#data),
			Name: Robot.generateName(),
			Delay: this.getDelayInterval().serialize(),
			Condition: this.getCondition().serialize(),
		};
		clonedRobot.init(robotData, this.#viewMode);

		return clonedRobot;
	}

	isEqual(other: Robot): boolean
	{
		return this.#data.Name === other.#data.Name;
	}

	init(data: Object, viewMode: ?ViewMode): void
	{
		if (Type.isPlainObject(data))
		{
			this.#data = { ...data };
		}

		if (!this.#data.Name)
		{
			this.#data.Name = Robot.generateName();
		}
		this.#data.Activated = Type.isNil(this.#data.Activated) ? true : Text.toBoolean(this.#data.Activated);

		this.#delay = new DelayInterval(this.#data.Delay);
		this.#condition = new ConditionGroup(this.#data.Condition);
		if (!this.#data.Condition)
		{
			this.#condition.type = ConditionGroup.CONDITION_TYPE.Mixed;
		}

		delete this.#data.Condition;
		delete this.#data.Delay;

		this.#viewMode = Type.isNil(viewMode) ? ViewMode.edit() : viewMode;
		if (!this.#viewMode.isNone())
		{
			this.#node = this.createNode();
		}
	}

	reInit(data: Object, viewMode: ?ViewMode): void
	{
		if (Type.isNil(viewMode) && this.#viewMode.isNone())
		{
			return;
		}

		const node = this.#node;
		this.#node = this.createNode();
		if (node.parentNode)
		{
			Dom.replace(node, this.#node);
		}
	}

	destroy()
	{
		Dom.remove(this.#node);
		this.emit('Robot:destroyed');
	}

	canEdit(): boolean
	{
		return this.#template.canEdit();
	}

	getProperties(): Object
	{
		if (this.#data && Type.isPlainObject(this.#data.Properties))
		{
			return this.#data.Properties;
		}

		return {};
	}

	getProperty(name: string): ?Object
	{
		return this.getProperties()[name] || null;
	}

	hasProperty(name: string): boolean
	{
		return Object.hasOwn(this.getProperties(), name);
	}

	setProperty(name: string, value: any): Robot
	{
		this.#data.Properties[name] = value;

		return this;
	}

	getId(): ?string
	{
		return this.#data.Name || null;
	}

	getLogStatus(): number
	{
		let status = TrackingStatus.WAITING;
		let log = this.#tracker.getRobotLog(this.getId());

		if (log)
		{
			status = log.status;
		}
		else if (this.#data.DelayName)
		{
			log = this.#tracker.getRobotLog(this.#data.DelayName);
			if (log && log.status === TrackingStatus.RUNNING)
			{
				status = TrackingStatus.RUNNING;
			}
		}

		return status;
	}

	getLogErrors(): Array<string>
	{
		let errors = [];
		const log = this.#tracker.getRobotLog(this.getId());
		if (log && log.errors)
		{
			errors = log.errors;
		}

		return errors;
	}

	getDelayNotes(): Array<string>
	{
		if (this.#data.DelayName)
		{
			const log = this.#tracker.getRobotLog(this.#data.DelayName);
			if (log && log.status === TrackingStatus.RUNNING)
			{
				return log.notes;
			}
		}

		return [];
	}

	selectNode(): void
	{
		if (this.#node)
		{
			Dom.addClass(this.#node, '--selected');

			const checkboxNode = this.#node.querySelector('input');
			if (checkboxNode)
			{
				checkboxNode.checked = true;
			}

			this.emit('Robot:selected');
		}
	}

	unselectNode()
	{
		if (this.#node)
		{
			Dom.removeClass(this.#node, '--selected');

			const checkboxNode = this.#node.querySelector('input');
			if (checkboxNode)
			{
				checkboxNode.checked = false;
			}

			this.emit('Robot:unselected');
		}
	}

	isSelected()
	{
		return this.#node && Dom.hasClass(this.#node, '--selected');
	}

	isActivated(): boolean
	{
		return Text.toBoolean(this.#data.Activated);
	}

	isInvalid(): boolean
	{
		return this.#data.viewData?.isInvalid === true;
	}

	setActivated(activated: boolean): this
	{
		this.#data.Activated = Text.toBoolean(activated);
		this.emit(this.#data.Activated === true ? 'Robot:onAfterActivated' : 'Robot:onAfterDeactivated');

		return this;
	}

	enableManageMode(isActive: boolean)
	{
		this.#viewMode = ViewMode.manage().setProperty('isActive', isActive);

		if (!isActive)
		{
			Dom.addClass(this.#node, '--locked-node');
		}

		const deleteButton = this.#node.querySelector('.bizproc-automation-robot-btn-delete');
		Dom.hide(deleteButton);

		this.#node.onclick = () => {
			if (!this.#viewMode.isManage() || !this.#viewMode.getProperty('isActive', false))
			{
				return;
			}

			if (!this.isSelected())
			{
				this.selectNode();
			}
			else
			{
				this.unselectNode();
			}
		};
	}

	disableManageMode()
	{
		this.#viewMode = ViewMode.edit();
		this.unselectNode();
		Dom.removeClass(this.#node, '--locked-node');

		const deleteButton = this.#node.querySelector('.bizproc-automation-robot-btn-delete');
		Dom.show(deleteButton);

		this.#node.onclick = undefined;
	}

	createNode(): HTMLElement
	{
		let wrapperClass = 'bizproc-automation-robot-container-wrapper';
		let containerClass = 'bizproc-automation-robot-container';

		if (this.#viewMode.isEdit() && this.canEdit() && this.#canEditRobot())
		{
			wrapperClass += ' bizproc-automation-robot-container-wrapper-draggable';
		}

		if (this.isActivated() === false)
		{
			containerClass += ' --deactivated';
			wrapperClass += ' --deactivated';
		}

		if (this.isInvalid())
		{
			containerClass += ' --invalid';
			wrapperClass += ' --invalid';
		}

		if (this.draft)
		{
			containerClass += ' --draft';
		}

		const targetLabel = Loc.getMessage('BIZPROC_AUTOMATION_CMP_TO');
		const targetNode = Tag.render`
			<a
				class="bizproc-automation-robot-settings-name ${(this.#viewMode.isView() ? '--mode-view' : '')}"
				title="${Loc.getMessage('BIZPROC_AUTOMATION_CMP_AUTOMATICALLY')}"
			>${Loc.getMessage('BIZPROC_AUTOMATION_CMP_AUTOMATICALLY')}</a>
		`;

		if (Type.isPlainObject(this.#data.viewData) && this.#data.viewData.responsibleLabel)
		{
			let labelText = (
				this.#data.viewData.responsibleLabel
					.replace('{=Document:ASSIGNED_BY_ID}', Loc.getMessage('BIZPROC_AUTOMATION_CMP_RESPONSIBLE'))
					.replace('author', Loc.getMessage('BIZPROC_AUTOMATION_CMP_RESPONSIBLE'))
					.replace(/\{=Constant\:Constant[0-9]+\}/, Loc.getMessage('BIZPROC_AUTOMATION_ASK_CONSTANT'))
					.replace(/\{\{~&\:Constant[0-9]+\}\}/, Loc.getMessage('BIZPROC_AUTOMATION_ASK_CONSTANT'))
					.replace(/\{=Template\:Parameter[0-9]+\}/, Loc.getMessage('BIZPROC_AUTOMATION_ASK_PARAMETER'))
					.replace(/\{\{~&:\:Parameter[0-9]+\}\}/, Loc.getMessage('BIZPROC_AUTOMATION_ASK_PARAMETER'))
			);

			if (labelText.includes('{=Document'))
			{
				this.#document.getFields().forEach((field) => {
					labelText = labelText.replace(field.SystemExpression, field.Name);
				});
			}

			if (labelText.includes('{=A'))
			{
				this.#template.robots.forEach((robot) => {
					robot.getReturnFieldsDescription().forEach((field) => {
						if (field.Type === 'user')
						{
							labelText = labelText.replace(
								field.SystemExpression,
								`${robot.getTitle()}: ${field.Name}`,
							);
						}
					});
				});
			}

			if (labelText.includes('{=GlobalVar:') && Type.isArrayFilled(this.#template.globalVariables))
			{
				this.#template.globalVariables.forEach((variable) => {
					labelText = labelText.replace(variable.SystemExpression, variable.Name);
				});
			}

			if (labelText.includes('{=GlobalConst:') && Type.isArrayFilled(this.#template.globalConstants))
			{
				this.#template.globalConstants.forEach((constant) => {
					labelText = labelText.replace(constant.SystemExpression, constant.Name);
				});
			}

			targetNode.textContent = labelText;
			targetNode.setAttribute('title', labelText);

			if (this.#data.viewData.responsibleUrl)
			{
				targetNode.href = this.#data.viewData.responsibleUrl;
				if (this.#isFrameMode)
				{
					targetNode.setAttribute('target', '_blank');
				}
			}

			if (this.#viewMode.isEdit() && parseInt(this.#data.viewData.responsibleId, 10) > 0)
			{
				targetNode.setAttribute('bx-tooltip-user-id', this.#data.viewData.responsibleId);
			}
		}

		let delayLabel = this.getDelayInterval().format(
			Loc.getMessage('BIZPROC_AUTOMATION_CMP_AT_ONCE'),
			this.#document.getFields(),
		);

		if (this.isExecuteAfterPrevious())
		{
			delayLabel = (delayLabel === Loc.getMessage('BIZPROC_AUTOMATION_CMP_AT_ONCE')) ? '' : `${delayLabel}, `;
			delayLabel += Loc.getMessage('BIZPROC_AUTOMATION_CMP_AFTER_PREVIOUS');
		}

		if (this.getCondition().items.length > 0)
		{
			delayLabel += `, ${Loc.getMessage('BIZPROC_AUTOMATION_CMP_BY_CONDITION')}`;
		}

		const delayNode = Dom.create(
			(this.#canEditRobot()) ? 'a' : 'span',
			{
				attrs: {
					className: this.#canEditRobot() ? 'bizproc-automation-robot-link' : 'bizproc-automation-robot-text',
					title: delayLabel,
				},
				text: delayLabel,
			},
		);

		const statusNode = Tag.render`<div class="bizproc-automation-robot-information"></div>`;
		this.subscribeOnce('Robot:destroyed', () => {
			if (HelpHint.isBindedToNode(statusNode))
			{
				HelpHint.hideHint();
			}
		});

		switch (this.getLogStatus())
		{
			case TrackingStatus.RUNNING:
				if (this.#document.getCurrentStatusId() === this.#template.getStatusId())
				{
					statusNode.classList.add('--loader');

					const delayNotes = this.getDelayNotes();
					if (delayNotes.length)
					{
						statusNode.setAttribute('data-text', delayNotes.join('\n'));
						HelpHint.bindToNode(statusNode);
					}
				}
				break;
			case TrackingStatus.COMPLETED:
			case TrackingStatus.AUTOCOMPLETED:
				containerClass += ' --complete';
				statusNode.classList.add('--complete');
				break;
		}

		const errors = this.getLogErrors();
		if (errors.length > 0)
		{
			Dom.addClass(statusNode, '--errors');
			statusNode.setAttribute('data-text', errors.join('\n'));
			HelpHint.bindToNode(statusNode);
		}

		let titleClassName = 'bizproc-automation-robot-title-text';
		if (this.#canEditRobot() && this.canEdit())
		{
			titleClassName += ' bizproc-automation-robot-title-text-editable';
		}

		const { root: div, titleNode } = Tag.render`
			<div
				class="${containerClass}"
				data-role="robot-container"
				data-type="item-robot"
				data-id="${Text.encode(this.getId())}"
			>
				${this.#renderCheckbox()}
				${this.#renderDeactivatedInfoBlock()}
				${this.#renderInvalidInfoBlock()}
				<div class="${wrapperClass}">
					<div class="bizproc-automation-robot-deadline">${delayNode}</div>
					<div class="bizproc-automation-robot-title">
						<div ref="titleNode" class="${titleClassName}" title="${Text.encode(this.getTitle())}">
							${this.clipTitle(this.getTitle())}
						</div>
					</div>
					<div class="bizproc-automation-robot-settings">
						<div class="bizproc-automation-robot-settings-title">${targetLabel}:</div>
						${targetNode}
					</div>
					${statusNode}
				</div>
			</div>
		`;
		Event.bind(titleNode, 'click', (event) => {
			if (this.#canEditRobot() && this.canEdit() && !this.#viewMode.isManage())
			{
				this.onTitleEditClick(event);
			}
		});

		if (this.canEdit() && this.#canEditRobot())
		{
			this.registerItem(div);
		}

		if (this.#viewMode.isEdit())
		{
			const deleteBtn = Tag.render`<span class="bizproc-automation-robot-btn-delete"></span>`;
			Event.bind(deleteBtn, 'click', this.onDeleteButtonClick.bind(this, deleteBtn));
			Dom.append(deleteBtn, div.lastChild);

			if (this.isInvalid())
			{
				const deleteBottomButton = Tag.render`
					<div class="bizproc-automation-robot-btn-settings">
						${Loc.getMessage('BIZPROC_AUTOMATION_DELETE_BUTTON_TITLE')}
					</div>
				`;
				Event.bind(deleteBottomButton, 'click', this.onDeleteButtonClick.bind(this, deleteBottomButton));
				Dom.append(deleteBottomButton, div);
			}
			else
			{
				const actionsButton = Tag.render`
					<div class="bizproc-automation-robot-btn-copy">
						${Loc.getMessage('BIZPROC_AUTOMATION_ACTIONS_BUTTON_TEXT')}
					</div>
				`;
				Event.bind(actionsButton, 'click', this.#onActionsButtonClick.bind(this, actionsButton));
				Dom.append(actionsButton, div);

				const settingsBtn = Tag.render`
					<div class="bizproc-automation-robot-btn-settings">
						${Loc.getMessage('BIZPROC_AUTOMATION_CMP_EDIT')}
					</div>
				`;
				Event.bind(div, 'click', this.onSettingsButtonClick.bind(this, div));
				Dom.append(settingsBtn, div);
			}
		}

		return div;
	}

	#renderCheckbox(): string | HTMLElement
	{
		if (this.isInvalid())
		{
			return '';
		}

		return Tag.render`
			<div class="ui-ctl ui-ctl-inline bizproc-automation-robot-container-checkbox">
				<input class="ui-ctl-checkbox" type="checkbox" name="name"/>
			</div>
		`;
	}

	#canEditRobot(): boolean
	{
		return this.#viewMode.isEdit() && !this.isInvalid();
	}

	#renderDeactivatedInfoBlock()
	{
		if (this.#data.Activated === true)
		{
			return '';
		}

		return Tag.render`
			<div class="bizproc-automation-robot-deactivated">
				${Loc.getMessage('BIZPROC_AUTOMATION_DEACTIVATED_ROBOT_BLOCK_TITLE')}
			</div>
		`;
	}

	#renderInvalidInfoBlock()
	{
		if (!this.isInvalid())
		{
			return '';
		}

		return Tag.render`
			<div class="bizproc-automation-robot-invalid">
				${Loc.getMessage('BIZPROC_AUTOMATION_INVALID_REST_ROBOT_BLOCK_TITLE')}
			</div>
		`;
	}

	onDeleteButtonClick(button, event)
	{
		event.stopPropagation();

		if (!this.canEdit())
		{
			HelpHint.showNoPermissionsHint(button);
		}
		else if (!this.#viewMode.isManage())
		{
			Dom.remove(this.#node);
			this.#template.deleteRobot(this);
		}
	}

	onSettingsButtonClick(button)
	{
		if (!this.canEdit())
		{
			HelpHint.showNoPermissionsHint(button);
		}
		else if (!this.#viewMode.isManage())
		{
			this.#template.openRobotSettingsDialog(this, this.#data.DialogContext ?? null);
		}
	}

	#onActionsButtonClick(button, event)
	{
		if (!this.canEdit())
		{
			event.stopPropagation();
			HelpHint.showNoPermissionsHint(button);

			return;
		}

		if (!this.#viewMode.isManage())
		{
			event.stopPropagation();
			const buttonText = (
				this.#data.Activated
					? Loc.getMessage('BIZPROC_AUTOMATION_ACTIONS_DEACTIVATE_BUTTON_TEXT')
					: Loc.getMessage('BIZPROC_AUTOMATION_ACTIONS_ACTIVATE_BUTTON_TEXT')
			);

			const menu = new Menu({
				bindElement: button,
				width: 150,
				height: 90,
				autoHide: true,
				angle: {
					offset: (Dom.getPosition(button).width / 2) + 23,
				},
				items: [
					{
						text: Loc.getMessage('BIZPROC_AUTOMATION_ACTIONS_COPY_BUTTON_TEXT'),
						title: Loc.getMessage('BIZPROC_AUTOMATION_ACTIONS_COPY_BUTTON_TEXT'),
						onclick: (e: PointerEvent, menuItem: MenuItem) => {
							this.onCopyButtonClick(menuItem, e);
							menu.destroy();
						},
					},
					{
						text: buttonText,
						title: buttonText,
						onclick: () => {
							this.#onDeactivateButtonClick();
							menu.destroy();
						},
					},
				],
			});
			menu.show();
		}
	}

	onCopyButtonClick(button, event)
	{
		event.stopPropagation();

		if (!this.canEdit())
		{
			HelpHint.showNoPermissionsHint(button);
		}
		else if (!this.#viewMode.isManage())
		{
			const copiedRobot = this.clone();
			const robotTitle = copiedRobot.getProperty('Title');
			if (!Type.isNil(robotTitle))
			{
				const newTitle = robotTitle + ' ' + ' ' + Loc.getMessage('BIZPROC_AUTOMATION_CMP_COPY_CAPTION');
				copiedRobot.setProperty('Title', newTitle);
				copiedRobot.reInit();
			}

			Template.copyRobotTo(this.#template, copiedRobot, this.#template.getNextRobot(this));
		}
	}

	#onDeactivateButtonClick()
	{
		this.setActivated(!this.isActivated());
		this.reInit();
	}

	onTitleEditClick(e)
	{
		e.preventDefault();
		e.stopPropagation();

		const formName = 'bizproc_automation_robot_title_dialog';

		const form = Dom.create('form', {
			props: {
				name: formName
			},
			style: {"min-width": '540px'}
		});

		form.appendChild(Dom.create("span", {
			attrs: { className: "bizproc-automation-popup-settings-title bizproc-automation-popup-settings-title-autocomplete" },
			text: Loc.getMessage('BIZPROC_AUTOMATION_CMP_ROBOT_NAME') + ':'
		}));

		form.appendChild(Dom.create("div", {
			attrs: { className: "bizproc-automation-popup-settings" },
			children: [BX.create("input", {
				attrs: {
					className: 'bizproc-automation-popup-input',
					type: "text",
					name: "name",
					value: this.getTitle()
				}
			})]
		}));

		this.emit('Robot:title:editStart');

		const self = this;
		const popup = new BX.PopupWindow(Helper.generateUniqueId(), null, {
			titleBar: Loc.getMessage('BIZPROC_AUTOMATION_CMP_ROBOT_NAME'),
			content: form,
			closeIcon: true,
			offsetLeft: 0,
			offsetTop: 0,
			closeByEsc: true,
			draggable: {restrict: false},
			overlay: false,
			events: {
				onPopupClose(popup)
				{
					popup.destroy();
					self.emit('Robot:title:editCompleted');
				}
			},
			buttons: [
				new BX.PopupWindowButton({
					text : Loc.getMessage('JS_CORE_WINDOW_SAVE'),
					className : "popup-window-button-accept",
					events : {
						click()
						{
							const nameNode = form.elements.name;
							self.setProperty('Title', nameNode.value);
							self.reInit();
							self.#template.markModified();
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
							this.popupWindow.close()
						}
					}
				})
			]
		});

		popup.show();
	}

	onSearch(event)
	{
		if (!this.#node)
		{
			return;
		}

		const query = event.getData().queryString;
		const match = !query || this.getTitle().toLowerCase().indexOf(query) >= 0;

		if (match)
		{
			Dom.removeClass(this.#node, '--search-mismatch');
		}
		else
		{
			Dom.addClass(this.#node, '--search-mismatch');
		}
	}

	clipTitle(fullTitle: string)
	{
		let title = Text.encode(fullTitle);
		const arrTitle = title.split(" ");
		const lastWord = "<span>" + arrTitle[arrTitle.length - 1] + "</span>";

		arrTitle.splice(arrTitle.length - 1);

		title = arrTitle.join(" ") + " " + lastWord;

		return title;
	}

	updateData(data)
	{
		if (Type.isPlainObject(data))
		{
			this.#data = data;
			this.#data.Activated = !Type.isNil(this.#data.Activated) ? Text.toBoolean(this.#data.Activated) : true;
		}
		else
		{
			throw 'Invalid data';
		}
	}

	serialize()
	{
		const result = BX.clone(this.#data);
		delete result['viewData'];
		delete result['DialogContext'];
		result.Delay = this.#delay.serialize();
		result.Condition = this.#condition.serialize();
		result.Activated = result.Activated ? 'Y' : 'N';

		return result;
	}

	getDelayInterval(): DelayInterval
	{
		return this.#delay;
	}

	setDelayInterval(delay): Robot
	{
		this.#delay = delay;

		return this;
	}

	getCondition(): ConditionGroup
	{
		return this.#condition;
	}

	setCondition(condition)
	{
		this.#condition = condition;

		return this;
	}

	setExecuteAfterPrevious(flag)
	{
		this.#data.ExecuteAfterPrevious = flag ? 1 : 0;

		return this;
	}

	isExecuteAfterPrevious()
	{
		return (this.#data.ExecuteAfterPrevious === 1 || this.#data.ExecuteAfterPrevious === '1')
	}

	registerItem(object)
	{
		if (Type.isNil(object["__bxddid"]))
		{
			object.onbxdragstart = BX.proxy(this.dragStart, this);
			object.onbxdrag = BX.proxy(this.dragMove, this);
			object.onbxdragstop = BX.proxy(this.dragStop, this);
			object.onbxdraghover = BX.proxy(this.dragOver, this);
			jsDD.registerObject(object);
			jsDD.registerDest(object, 1);
		}
	}

	unregisterItem(object)
	{
		object.onbxdragstart = undefined;
		object.onbxdrag = undefined;
		object.onbxdragstop = undefined;
		object.onbxdraghover = undefined;
		jsDD.unregisterObject(object);
		jsDD.unregisterDest(object);
	}

	dragStart()
	{
		this.draggableItem = BX.proxy_context;

		if (!this.draggableItem)
		{
			jsDD.stopCurrentDrag();
			return;
		}

		if (!this.stub)
		{
			const itemWidth = this.draggableItem.offsetWidth;
			this.stub = this.draggableItem.cloneNode(true);
			this.stub.style.position = "absolute";
			this.stub.classList.add("bizproc-automation-robot-container-drag");
			this.stub.style.width = itemWidth + "px";
			document.body.appendChild(this.stub);
		}
	}

	dragMove(x,y)
	{
		this.stub.style.left = x + "px";
		this.stub.style.top = y + "px";
	}

	dragOver(destination, x, y)
	{
		if (this.droppableItem)
		{
			this.droppableItem.classList.remove("bizproc-automation-robot-container-pre");
		}

		if (this.droppableColumn)
		{
			this.droppableColumn.classList.remove("bizproc-automation-robot-list-pre");
		}

		const type = destination.getAttribute("data-type");

		if (type === "item-robot")
		{
			this.droppableItem = destination;
			this.droppableColumn = null;
		}

		if (type === "column-robot")
		{
			this.droppableColumn = destination.querySelector('[data-role="robot-list"]');
			this.droppableItem = null;
		}

		if (this.droppableItem)
		{
			this.droppableItem.classList.add("bizproc-automation-robot-container-pre");
		}

		if (this.droppableColumn)
		{
			this.droppableColumn.classList.add("bizproc-automation-robot-list-pre");
		}
	}

	dragStop(x, y, event)
	{
		event = event || window.event;
		const isCopy = event && (event.ctrlKey || event.metaKey);

		if (this.draggableItem)
		{
			if (this.droppableItem)
			{
				this.droppableItem.classList.remove("bizproc-automation-robot-container-pre");
				this.emit('Robot:manage', {
					templateNode: this.droppableItem.parentNode,
					isCopy,
					droppableItem: this.droppableItem,
					robot: this,
				});
			}
			else if (this.droppableColumn)
			{
				this.droppableColumn.classList.remove("bizproc-automation-robot-list-pre");
				this.emit('Robot:manage', {
					templateNode: this.droppableColumn,
					isCopy,
					robot: this,
				});
			}
		}

		this.stub.parentNode.removeChild(this.stub);
		this.stub = null;
		this.draggableItem = null;
		this.droppableItem = null;
	}

	moveTo(template, beforeRobot)
	{
		Dom.remove(this.#node);
		this.#template.deleteRobot(this);
		this.#template = template;

		this.#template.insertRobot(this, beforeRobot);
		this.#node = this.createNode();
		this.#template.insertRobotNode(this.#node, beforeRobot ? beforeRobot.node : null);
	}

	copyTo(template, beforeRobot)
	{
		const robot = new Robot({
			document: this.#document,
			template,
			isFrameMode: this.#isFrameMode,
			tracker: this.#tracker,
		});

		const robotData = this.serialize();
		delete robotData['Name'];
		delete robotData['DelayName'];

		robot.init(robotData, this.#viewMode);

		template.insertRobot(robot, beforeRobot);
		template.insertRobotNode(robot.node, beforeRobot ? beforeRobot.node : null);

		return robot;
	}

	getTitle()
	{
		return this.getProperty('Title') || this.getDescriptionTitle();
	}

	getDescriptionTitle()
	{
		let name = 'untitled';
		const description = this.template?.getRobotDescription(this.#data['Type']) ?? {};
		if (description['NAME'])
		{
			name = description['NAME'];
		}
		if (description['ROBOT_SETTINGS'] && description['ROBOT_SETTINGS']['TITLE'])
		{
			name = description['ROBOT_SETTINGS']['TITLE'];
		}

		return name;
	}

	hasTitle(): boolean
	{
		return this.getTitle() !== 'untitled';
	}

	hasReturnFields(): boolean
	{
		const description = this.template.getRobotDescription(this.#data['Type']);
		const props = this.#data['Properties'];

		if (!Type.isObject(description))
		{
			return false;
		}

		const hasReturnProperties = () => (
			Type.isObject(description['RETURN'])
			&& Type.isArrayFilled(Object.values(description['RETURN']))
		);

		const hasAdditionalResultProperties = () => (
			Type.isArray(description['ADDITIONAL_RESULT'])
			&& description['ADDITIONAL_RESULT'].some(addProperty => Object.values(props[addProperty] ?? []).length > 0)
		);

		return hasReturnProperties() || hasAdditionalResultProperties();
	}

	getReturnFieldsDescription()
	{
		const fields = [];
		const description = this.template.getRobotDescription(this.#data['Type']);

		if (description && description['RETURN'])
		{
			for (const fieldId in description['RETURN'])
			{
				if (description['RETURN'].hasOwnProperty(fieldId))
				{
					const field = description['RETURN'][fieldId];
					fields.push({
						Id: fieldId,
						ObjectId: this.getId(),
						ObjectName: this.getTitle(),
						Name: field['NAME'],
						Type: field['TYPE'],
						Options: field['OPTIONS'] || null,
						Expression: '{{~'+this.getId()+':'+fieldId+' # '+this.getTitle()+': '+field['NAME']+'}}',
						SystemExpression: '{='+this.getId()+':'+fieldId+'}'
					});

					if (!this.appendPropertyMods)
					{
						continue;
					}

					//generate printable version
					if (
						field['TYPE'] === 'user'
						||
						field['TYPE'] === 'bool'
						||
						field['TYPE'] === 'file'
					)
					{
						const printableTag = (field['TYPE'] === 'user') ? 'friendly' : 'printable';
						fields.push({
							Id: fieldId + '_printable',
							ObjectId: this.getId(),
							ObjectName: this.getTitle(),
							Name: field['NAME'] + ' ' + Loc.getMessage('BIZPROC_AUTOMATION_CMP_MOD_PRINTABLE_PREFIX'),
							Type: 'string',
							Expression: `{{~${this.getId()}:${fieldId} > ${printableTag} # ${this.getTitle()}: ${field['NAME']}}}`,
							SystemExpression: `{=${this.getId()}:${fieldId}>${printableTag}}`,
						});
					}
				}
			}
		}

		if (description && Type.isArray(description['ADDITIONAL_RESULT']))
		{
			const props = this.#data['Properties'];

			description['ADDITIONAL_RESULT'].forEach((addProperty) => {
				if (props[addProperty])
				{
					for (const fieldId in props[addProperty])
					{
						if (props[addProperty].hasOwnProperty(fieldId))
						{
							const field = props[addProperty][fieldId];
							fields.push({
								Id: fieldId,
								ObjectId: this.getId(),
								ObjectName: this.getTitle(),
								Name: field['Name'],
								Type: field['Type'],
								Options: field['Options'] || null,
								Expression: `{{~${this.getId()}:${fieldId} # ${this.getTitle()}: ${field['Name']}}}`,
								SystemExpression: '{=' + this.getId() + ':' + fieldId + '}',
							});

							//generate printable version
							if (
								field['Type'] === 'user'
								||
								field['Type'] === 'bool'
								||
								field['Type'] === 'file'
							)
							{
								const printableTag = (field['Type'] === 'user') ? 'friendly' : 'printable';
								const expression = `{{~${this.getId()}:${fieldId} > ${printableTag} # ${this.getTitle()}: ${field['Name']}}}`;
								fields.push({
									Id: fieldId + '_printable',
									ObjectId: this.getId(),
									ObjectName: this.getTitle(),
									Name: field['Name'] + ' ' + Loc.getMessage('BIZPROC_AUTOMATION_CMP_MOD_PRINTABLE_PREFIX'),
									Type: 'string',
									Expression: expression,
									SystemExpression: '{=' + this.getId() + ':' + fieldId + '>' + printableTag + '}',
								});
							}
						}
					}
				}
			});
		}

		return fields;
	}

	getReturnProperty(id): Array<Object>
	{
		const fields = this.getReturnFieldsDescription();
		for (let i = 0; i < fields.length; ++i)
		{
			if (fields[i]['Id'] === id)
			{
				return fields[i];
			}
		}

		return null;
	}

	collectUsages()
	{
		const properties = this.getProperties();
		const usages = {
			Document: new Set(),
			Constant: new Set(),
			Variable: new Set(),
			Parameter: new Set(),
			GlobalConstant: new Set(),
			GlobalVariable: new Set(),
			Activity: new Set()
		};

		Object.values(properties).forEach(property => this.collectExpressions(property, usages));

		const conditions = this.getCondition().serialize();
		conditions.items.forEach(item => this.collectParsedExpressions(item[0], usages));

		return usages;
	}

	collectExpressions(value, usages)
	{
		if (Type.isArray(value))
		{
			value.forEach(v => this.collectExpressions(v, usages));
		}
		else if (Type.isPlainObject(value))
		{
			Object.values(value).forEach(value => this.collectExpressions(value, usages));
		}
		else if (Type.isStringFilled(value))
		{
			let found;
			const systemExpressionRegExp = new RegExp(this.SYSTEM_EXPRESSION_PATTERN, 'ig');
			while ((found = systemExpressionRegExp.exec(value)) !== null)
			{
				this.collectParsedExpressions(found.groups, usages);
			}
		}
	}

	collectParsedExpressions(parsedUsage, usages)
	{
		if (Type.isPlainObject(parsedUsage) && parsedUsage['object'] && parsedUsage['field'])
		{
			switch (parsedUsage['object'])
			{
				case 'Document':
					usages.Document.add(parsedUsage['field']);
					return;

				case 'Constant':
					usages.Constant.add(parsedUsage['field']);
					return;

				case 'Variable':
					usages.Variable.add(parsedUsage['field']);
					return;

				case 'Template':
					usages.Parameter.add(parsedUsage['field']);
					return;

				case 'GlobalConst':
					usages.GlobalConstant.add(parsedUsage['field']);
					return;

				case 'GlobalVar':
					usages.GlobalVariable.add(parsedUsage['field']);
					return;
			}

			const activityRegExp = new RegExp(/^A[_0-9]+$/, 'ig');
			if (activityRegExp.exec(parsedUsage['object']))
			{
				usages.Activity.add([parsedUsage['object'], parsedUsage['field']]);
			}
		}
	}

	hasBrokenLink(): boolean
	{
		return this.getBrokenLinks().length > 0;
	}

	getBrokenLinks(): []
	{
		const usages = Runtime.clone(this.collectUsages());

		if (!this.template)
		{
			return [];
		}

		const objectsData = {
			Document: this.#document.getFields(),
			Constant: this.#template.getConstants(),
			Variable: this.#template.getVariables(),
			GlobalConstant: this.#template.globalConstants,
			GlobalVariable: this.#template.globalVariables,
			Parameter: this.#template.getParameters(),
			Activity: this.#template.getSerializedRobots()
		};

		const brokenLinks = [];
		for (const object in usages)
		{
			if (usages[object].size > 0)
			{
				const source = new Set();

				for (const key in objectsData[object])
				{
					if (objectsData[object][key]['Id'])
					{
						source.add(objectsData[object][key]['Id']);
					}
					else if (objectsData[object][key]['Name'])
					{
						source.add(objectsData[object][key]['Name']);
					}
				}

				for (const value of usages[object].values())
				{
					let searchInSource = value;
					let id = value;

					if (Type.isArray(searchInSource))
					{
						searchInSource = value[0];
						id = value[1];
					}

					if (!source.has(searchInSource))
					{
						if (object === 'Activity')
						{
							brokenLinks.push('{=' + searchInSource + ':' + id + '}');
						}
						else
						{
							let brokenLinkObject = object;

							if (brokenLinkObject === 'GlobalVariable')
							{
								brokenLinkObject = 'GlobalVar';
							}
							if (brokenLinkObject === 'GlobalConstant')
							{
								brokenLinkObject = 'GlobalConst';
							}
							if (brokenLinkObject === 'Parameter')
							{
								brokenLinkObject = 'Template';
							}

							brokenLinks.push('{=' + brokenLinkObject + ':' + searchInSource + '}');
						}

						continue;
					}

					if (object === 'Activity')
					{
						const robot = this.#template.getRobotById(searchInSource);
						if (!robot.getReturnProperty(id))
						{
							brokenLinks.push('{=' + searchInSource + ':' + id + '}');
						}
					}
				}
			}
		}

		return brokenLinks;
	}

	onBeforeSaveRobotSettings(): Object
	{
		const data = this.#customOnBeforeSaveRobotSettings();

		return Type.isPlainObject(data) ? data : {};
	}

	setOnBeforeSaveRobotSettings(callback: Function): void
	{
		if (Type.isFunction(callback))
		{
			this.#customOnBeforeSaveRobotSettings = callback;
		}
	}
}
