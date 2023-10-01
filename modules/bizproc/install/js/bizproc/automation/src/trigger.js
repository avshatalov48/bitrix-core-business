import {clone, Type, Dom, Loc, Event, Tag} from "main.core";
import { EventEmitter } from "main.core.events";
import { ViewMode } from "./view-mode";
import { getGlobalContext, ConditionGroup, TrackingStatus, HelpHint } from "bizproc.automation";

export class Trigger extends EventEmitter
{
	draft: boolean;

	#data: Object<string, any>;
	#deleted: boolean;
	#viewMode: ViewMode;
	#condition: ConditionGroup;
	#node: ?HTMLDivElement;
	#draggableItem: ?HTMLElement;
	#droppableItem: ?HTMLElement;
	#droppableColumn: ?HTMLElement;
	#stub: ?HTMLElement;

	constructor()
	{
		super();
		this.setEventNamespace('BX.Bizproc.Automation');

		this.draft = false;
		this.#data = {};
		this.#deleted = false;
		this.#viewMode = ViewMode.none();
		this.#condition = new ConditionGroup();
	}

	get node(): HTMLDivElement
	{
		return this.#node;
	}

	get deleted(): boolean
	{
		return this.#deleted;
	}

	get documentStatus(): string
	{
		return this.#data['DOCUMENT_STATUS'] ?? '';
	}

	init(data: Object<string, any>, viewMode: ?ViewMode): void
	{
		this.#data = clone(data);

		if (Type.isString(this.#data['ID']))
		{
			const id = parseInt(this.#data['ID']);
			this.#data['ID'] = Type.isNumber(id) ? id : 0;
		}
		if (!Type.isPlainObject(this.#data['APPLY_RULES']))
		{
			this.#data['APPLY_RULES'] = {};
		}

		if (this.#data['APPLY_RULES'].Condition)
		{
			this.#condition = new ConditionGroup(this.#data['APPLY_RULES'].Condition);
		}
		else
		{
			this.#condition = new ConditionGroup();
		}

		this.#viewMode = Type.isNil(viewMode) ? ViewMode.edit() : viewMode;
		this.#node = this.createNode();
	}

	reInit(data: Object<string, any>, viewMode: ?ViewMode)
	{
		const node = this.#node;
		this.#node = this.createNode();
		if (node.parentNode)
		{
			node.parentNode.replaceChild(this.#node, node);
		}
	}

	canEdit()
	{
		return getGlobalContext().canEdit;
	}

	getId(): number
	{
		return this.#data['ID'] || 0;
	}

	getStatusId(): string
	{
		return String(this.#data['DOCUMENT_STATUS'] || '');
	}

	getStatus(): ?object
	{
		return getGlobalContext().document.statusList.find(status => String(status.STATUS_ID) === this.getStatusId());
	}

	getCode(): string
	{
		return this.#data['CODE'] ?? '';
	}

	getName(): string
	{
		let triggerName = this.#data['NAME'];
		if (!triggerName)
		{
			const code = this.getCode();
			const trigger = getGlobalContext().availableTriggers.find((trigger) => code === trigger['CODE']);
			triggerName = trigger?.NAME ?? code;
		}

		return triggerName;
	}

	setName(name: string): this
	{
		if (Type.isString(name))
		{
			this.#data['NAME'] = name;
		}

		return this;
	}

	getApplyRules()
	{
		return this.#data['APPLY_RULES'];
	}

	setApplyRules(rules: Object): this
	{
		this.#data['APPLY_RULES'] = rules;

		return this;
	}

	getLogStatus()
	{
		const log = getGlobalContext().tracker.getTriggerLog(this.getId());
		return log ? log.status : null;
	}

	getCondition(): ConditionGroup
	{
		return this.#condition;
	}

	setCondition(condition: ConditionGroup): this
	{
		this.#condition = condition;
		return this;
	}

	isBackwardsAllowed(): boolean
	{
		return this.#data['APPLY_RULES']['ALLOW_BACKWARDS'] === 'Y';
	}

	setAllowBackwards(flag: boolean): this
	{
		this.#data['APPLY_RULES']['ALLOW_BACKWARDS'] = flag ? 'Y' : 'N';

		return this;
	}

	getExecuteBy(): string
	{
		return this.#data['APPLY_RULES']['ExecuteBy'] || '';
	}

	setExecuteBy(userId: number): this
	{
		this.#data['APPLY_RULES']['ExecuteBy'] = userId;

		return this;
	}

	enableManageMode(isActive: boolean): void
	{
		this.#viewMode = ViewMode.manage().setProperty('isActive', isActive);

		// const checkboxNode = Tag.render`<div class="bizproc-automation-trigger-checkbox"></div>`
		const checkboxNode = Tag.render`<div class="ui-ctl ui-ctl-inline bizproc-automation-trigger-checkbox">
			<input class="ui-ctl-checkbox" type="checkbox" name="name">
		</div>`;
		const deleteButton = this.#node.querySelector('[data-role="btn-delete-trigger"]');
		Dom.hide(deleteButton);

		if (isActive && deleteButton)
		{
			Dom.append(checkboxNode, this.#node);
		}
		else
		{
			Dom.addClass(this.#node, '--locked-node');
		}
	}

	disableManageMode()
	{
		this.#viewMode = ViewMode.edit();

		const checkboxNode = this.#node.querySelector('.bizproc-automation-trigger-checkbox');
		const deleteButton = this.#node.querySelector('[data-role="btn-delete-trigger"]');

		this.#node.onclick = undefined;

		this.#viewMode = ViewMode.edit();
		this.unselectNode();

		Dom.removeClass(this.#node, '--locked-node');
		Dom.remove(checkboxNode);
		Dom.show(deleteButton);
	}

	selectNode()
	{
		if (this.#node)
		{
			Dom.addClass(this.#node, '--selected');

			const checkboxNode = this.#node.querySelector('input');
			if (checkboxNode)
			{
				checkboxNode.checked = true;
			}

			this.emit('Trigger:selected');
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

			this.emit('Trigger:unselected');
		}
	}

	isSelected(): boolean
	{
		return this.#viewMode.isManage() && Dom.hasClass(this.node, '--selected');
	}

	createNode()
	{
		let wrapperClass = 'bizproc-automation-trigger-item-wrapper';

		if (this.#viewMode.isEdit() && this.canEdit())
		{
			wrapperClass += ' bizproc-automation-trigger-item-wrapper-draggable';
		}

		let settingsBtn = null;
		let copyBtn = null;
		if (this.#viewMode.isEdit())
		{
			settingsBtn = Dom.create("div", {
				attrs: {
					className: "bizproc-automation-trigger-item-wrapper-edit"
				},
				text: Loc.getMessage('BIZPROC_AUTOMATION_CMP_EDIT')
			});

			copyBtn = Dom.create('div', {
				attrs: {
					className: 'bizproc-automation-trigger-btn-copy'
				},
				text: Loc.getMessage('BIZPROC_AUTOMATION_CMP_COPY') || 'copy'
			});

			Event.bind(copyBtn, 'click', this.onCopyButtonClick.bind(this, copyBtn));
		}

		if (this.getLogStatus() === TrackingStatus.COMPLETED)
		{
			wrapperClass += ' bizproc-automation-trigger-item-wrapper-complete';
		}
		else if (getGlobalContext().document.getPreviousStatusIdList().includes(this.getStatusId()))
		{
			wrapperClass += ' bizproc-automation-trigger-item-wrapper-complete-light';
		}

		const triggerName = this.getName();

		let containerClass = 'bizproc-automation-trigger-item';

		if (this.getLogStatus() === TrackingStatus.COMPLETED)
		{
			containerClass += ' --complete';
		}
		else if (this.draft)
		{
			containerClass += ' --draft';
		}

		const div = Dom.create('DIV', {
			attrs: {
				'data-role': 'trigger-container',
				'className': containerClass,
				'data-type': 'item-trigger'
			},
			children: [
				Dom.create("div", {
					attrs: {
						className: wrapperClass
					},
					children: [
						Dom.create("div", {
							attrs: {
								className: "bizproc-automation-trigger-item-wrapper-text",
								title: triggerName,
							},
							text: triggerName
						})
					]
				}),
				copyBtn,
				settingsBtn,
			]
		});

		if (!this.#viewMode.isEdit())
		{
			return div;
		}

		if (this.canEdit())
		{
			this.registerItem(div);
		}

		const deleteBtn = Dom.create('SPAN', {
			attrs: {
				'data-role': 'btn-delete-trigger',
				'className': 'bizproc-automation-trigger-btn-delete',
			}
		});

		Event.bind(deleteBtn, 'click', this.onDeleteButtonClick.bind(this, deleteBtn));

		div.appendChild(deleteBtn);

		if (this.#viewMode.isEdit())
		{
			Event.bind(div, 'click', this.onSettingsButtonClick.bind(this, div));
		}

		Event.bind(div, 'click', () => {
			if (this.#viewMode.isManage() && this.#viewMode.getProperty('isActive', false))
			{
				if (!this.isSelected())
				{
					this.selectNode();
				}
				else
				{
					this.unselectNode();
				}
			}
		});

		return div;
	}

	onSettingsButtonClick(button)
	{
		if (!this.canEdit())
		{
			HelpHint.showNoPermissionsHint(button);
		}
		else if (!this.#viewMode.isManage())
		{
			this.emit('Trigger:onSettingsOpen', {trigger: this});
		}
	}

	onCopyButtonClick(button: HTMLElement, event)
	{
		event.stopPropagation();

		if (!this.canEdit())
		{
			HelpHint.showNoPermissionsHint(button);
		}
		else if (!this.#viewMode.isManage())
		{
			const trigger = new Trigger();
			const initData = this.serialize();
			delete initData['ID'];

			const clearRules = this.getSettingProperties()
				.filter((property) => property.Copyable === false)
				.map((property) => property.Id)
			;

			clearRules.forEach(key => delete initData['APPLY_RULES'][key]);

			trigger.init(initData, this.#viewMode);
			this.emit('Trigger:copied', {trigger});
		}
	}
	onSearch(event)
	{
		if (!this.#node)
		{
			return;
		}

		const query = event.getData().queryString;
		const match = !query || this.getName().toLowerCase().indexOf(query) >= 0;

		Dom[match ? 'removeClass' : 'addClass'](this.#node, '--search-mismatch');
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
		this.#draggableItem = BX.proxy_context;

		if (!this.#draggableItem)
		{
			jsDD.stopCurrentDrag();
			return;
		}

		if (!this.#stub)
		{
			const itemWidth = this.#draggableItem.offsetWidth;
			this.#stub = this.#draggableItem.cloneNode(true);
			this.#stub.style.position = "absolute";
			this.#stub.classList.add("bizproc-automation-trigger-item-drag");
			this.#stub.style.width = itemWidth + "px";
			document.body.appendChild(this.#stub);
		}
	}

	dragMove(x, y)
	{
		this.#stub.style.left = x + "px";
		this.#stub.style.top = y + "px";
	}

	dragOver(destination, x, y)
	{
		if (this.#droppableItem)
		{
			this.#droppableItem.classList.remove("bizproc-automation-trigger-item-pre");
		}

		if (this.#droppableColumn)
		{
			this.#droppableColumn.classList.remove("bizproc-automation-trigger-list-pre");
		}

		const type = destination.getAttribute("data-type");

		if (type === "item-trigger")
		{
			this.#droppableItem = destination;
			this.#droppableColumn = null;
		}

		if (type === "column-trigger")
		{
			this.#droppableColumn = destination.querySelector('[data-role="trigger-list"]');
			this.#droppableItem = null;
		}

		if (this.#droppableItem)
		{
			this.#droppableItem.classList.add("bizproc-automation-trigger-item-pre");
		}

		if (this.#droppableColumn)
		{
			this.#droppableColumn.classList.add("bizproc-automation-trigger-list-pre");
		}
	}

	dragStop(x, y, event)
	{
		event = event || window.event;
		let trigger = null;
		const isCopy = event && (event.ctrlKey || event.metaKey);
		const copyTrigger = (parent, statusId) => {
			const trigger = new Trigger();
			const initData = parent.serialize();
			delete initData['ID'];

			const clearRules = this.getSettingProperties()
				.filter((property) => property.Copyable === false)
				.map((property) => property.Id)
			;

			clearRules.forEach(key => delete initData['APPLY_RULES'][key]);

			initData['DOCUMENT_STATUS'] = statusId;
			trigger.init(initData, parent.#viewMode);

			return trigger;
		};

		if (this.#draggableItem)
		{
			if (this.#droppableItem)
			{
				this.#droppableItem.classList.remove("bizproc-automation-trigger-item-pre");
				const thisColumn = this.#droppableItem.parentNode;
				if (!isCopy)
				{
					thisColumn.insertBefore(this.#draggableItem, this.#droppableItem);
					this.moveTo(thisColumn.getAttribute('data-status-id'));
				}
				else
				{
					trigger = copyTrigger(this, thisColumn.getAttribute('data-status-id'));
					thisColumn.insertBefore(trigger.#node, this.#droppableItem);
				}
			}
			else if (this.#droppableColumn)
			{
				this.#droppableColumn.classList.remove("bizproc-automation-trigger-list-pre");
				if (!isCopy)
				{
					this.#droppableColumn.appendChild(this.#draggableItem);
					this.moveTo(this.#droppableColumn.getAttribute('data-status-id'));
				}
				else
				{
					trigger = copyTrigger(this, this.#droppableColumn.getAttribute('data-status-id'));
					this.#droppableColumn.appendChild(trigger.#node);
				}
			}

			if (trigger)
			{
				this.emit('Trigger:copied', {
					trigger,
					skipInsert: true,
				});
			}
		}

		this.#stub.parentNode.removeChild(this.#stub);
		this.#stub = null;
		this.#draggableItem = null;
		this.#droppableItem = null;
	}

	onDeleteButtonClick(button: HTMLElement, event)
	{
		event.stopPropagation();

		if (!this.canEdit())
		{
			HelpHint.showNoPermissionsHint(button);
		}
		else if (!this.#viewMode.isManage())
		{
			Dom.remove(button.parentNode);
			this.emit('Trigger:deleted', {trigger: this});
		}
	}

	updateData(data: Object<string, any>): void
	{
		if (Type.isPlainObject(data))
		{
			this.#data = data;
		}
		else
		{
			throw 'Invalid data';
		}
	}

	markDeleted(): this
	{
		this.#deleted = true;

		return this;
	}

	serialize(): Object<string, any>
	{
		const data = clone(this.#data);
		if (this.#deleted)
		{
			data['DELETED'] = 'Y';
		}

		if (!Type.isPlainObject(data.APPLY_RULES))
		{
			data.APPLY_RULES = {};
		}

		if (!this.#condition.items.length)
		{
			delete data.APPLY_RULES.Condition;
		}
		else
		{
			data.APPLY_RULES.Condition = this.#condition.serialize();
		}

		return data;
	}

	moveTo(statusId)
	{
		this.#data['DOCUMENT_STATUS'] = statusId;
		this.emit('Trigger:modified', {trigger: this});
	}

	getReturnProperties()
	{
		const triggerData = getGlobalContext().availableTriggers.find(trigger => trigger['CODE'] === this.getCode());

		return triggerData && Type.isArray(triggerData.RETURN) ? triggerData.RETURN : [];
	}

	getSettingProperties(): Array
	{
		const triggerData = getGlobalContext().availableTriggers.find(trigger => trigger['CODE'] === this.getCode());

		if (triggerData.SETTINGS && triggerData.SETTINGS.Properties)
		{
			return triggerData.SETTINGS.Properties;
		}

		return [];
	}
}