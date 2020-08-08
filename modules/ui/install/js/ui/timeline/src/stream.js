import {Loc, Dom, Text, Event, Tag, Type, Reflection, Runtime} from 'main.core';
import {EventEmitter, BaseEvent} from 'main.core.events';
import {Loader} from 'main.loader';
import {Item} from './item';
import {History} from './history';
import {StageChange} from './stagechange';
import {FieldsChange} from './fieldschange';
import {Editor} from './editor';
import {Comment} from './comment';
import {Drop} from './animation/drop';
import {Pin} from './animation/pin';
import {Show} from './animation/show';
import {TaskComplete} from './animation/taskcomplete';
import {Hide} from './animation/hide';
import {Queue} from './animation/queue';

import 'main.date';

/**
 * @mixes EventEmitter
 * @memberOf BX.UI.Timeline
 */
export class Stream
{
	constructor(params: {
		items: ?Array,
		users: ?Object,
		nameFormat: ?string,
		pageSize: ?number,
		tasks: ?Array,
		editors: ?Array,
		itemClasses: ?Array,
	})
	{
		this.users = new Map();
		this.eventIds = new Set();
		this.pinnedItems = [];
		this.tasks = [];
		this.items = [];
		this.editors = new Map();
		this.layout = {};
		this.dateSeparators = new Map();
		this.nameFormat = params.nameFormat;
		EventEmitter.makeObservable(this, 'BX.UI.Timeline.Stream');
		this.initItemClasses(params.itemClasses);
		this.currentPage = 1;
		if(Type.isPlainObject(params))
		{
			if(Type.isNumber(params.pageSize))
			{
				this.pageSize = params.pageSize;
			}
			if(!this.pageSize || this.pageSize <= 0)
			{
				this.pageSize = 20;
			}
			this.addUsers(params.users);
			if(Type.isArray(params.items))
			{
				params.items.forEach((data) => {
					const item = this.createItem(data);
					if(item)
					{
						this.addItem(item);
					}
				});
			}
			if(Type.isArray(params.tasks))
			{
				this.initTasks(params.tasks);
			}
			if(Type.isArray(params.editors))
			{
				params.editors.forEach((editor: Editor) => {
					if(editor instanceof Editor)
					{
						this.editors.set(editor.getId(), editor);
					}
				})
			}
		}
		this.bindEvents();

		this.progress = false;

		this.emit('onAfterInit', {
			stream: this,
		});
	}

	initTasks(tasks: Array)
	{
		this.tasks = [];
		tasks.forEach((data) => {
			const task = this.createItem(data);
			if(task)
			{
				this.tasks.push(task);
			}
		});
	}

	bindEvents()
	{
		this.onScrollHandler = Runtime.throttle(this.onScroll.bind(this), 100).bind(this);
		Event.ready(() => {
			if(this.getItems().length >= this.pageSize)
			{
				this.enableLoadOnScroll();
			}
		});
		Array.from(this.editors.values()).forEach((editor: Editor) => {
			editor.subscribe('error', (event: BaseEvent) => {
				this.onError(event.getData());
			});
		});
	}

	initItemClasses(itemClasses: ?Array)
	{
		if(itemClasses)
		{
			this.itemClasses = new Map(itemClasses);
		}
		else
		{
			this.itemClasses = new Map();
		}
		this.itemClasses.set('item_create', History);
		this.itemClasses.set('stage_change', StageChange);
		this.itemClasses.set('fields_change', FieldsChange);
		this.itemClasses.set('comment', Comment);
	}

	createItem(data: {}, itemClassName: ?Function): ?Item
	{
		if(!Type.isPlainObject(data.events))
		{
			data.events = {};
		}
		data.eventIds = this.eventIds;
		data.events.onPinClick = this.onItemPinClick.bind(this);
		data.events.onDelete = this.onItemDelete.bind(this);
		data.events.onError = this.onError.bind(this);
		if(!Type.isFunction(itemClassName))
		{
			itemClassName = this.getItemClassName(data);
		}
		const item = new itemClassName(data);
		if(item instanceof Item)
		{
			return item
				.setUserData(this.users)
				.setDateTimeOffset(this.getUserTimeZoneOffset())
				.setTimeFormat(this.getTimeFormat())
				.setNameFormat(this.nameFormat);
		}

		return null;
	}

	addItem(item: Item): Stream
	{
		if(item instanceof Item)
		{
			this.items.push(item);
			if(item.isFixed)
			{
				this.pinnedItems.push(this.getPinnedItemFromItem(item));
			}
		}

		return this;
	}

	/**
	 * @protected
	 */
	static getItemFromArray(items: Array, id: string|number): ?Item
	{
		let result = null;
		let key = 0;
		while(true)
		{
			if(!items[key])
			{
				break;
			}
			const item = items[key];
			if(item.getId() === id)
			{
				result = item;
				break;
			}
			key++;
		}

		return result;
	}

	static getItemIndexFromArray(items: Array, id: string|number): ?number
	{
		let result = null;
		let key = 0;
		while(true)
		{
			if(!items[key])
			{
				break;
			}
			const item = items[key];
			if(item.getId() === id)
			{
				result = key;
				break;
			}
			key++;
		}

		return result;
	}

	getItems(): Array
	{
		return this.items;
	}

	getItem(id: string|number): ?Item
	{
		return Stream.getItemFromArray(this.getItems(), id);
	}

	getPinnedItems(): Array
	{
		return this.pinnedItems;
	}

	getPinnedItem(id: string|number): ?Item
	{
		return Stream.getItemFromArray(this.getPinnedItems(), id);
	}

	getTasks(): Array
	{
		return this.tasks;
	}

	getTask(id: string|number): ?Item
	{
		return Stream.getItemFromArray(this.getTasks(), id);
	}

	render(): Element
	{
		if(!this.layout.container)
		{
			this.layout.container = Tag.render`<div class="ui-item-detail-stream-container"></div>`;
		}

		if(this.editors.size > 0)
		{
			this.renderEditors();
		}

		if(!this.layout.content)
		{
			this.layout.content = Tag.render`<div class="ui-item-detail-stream-content"></div>`;
			this.layout.container.appendChild(this.layout.content);
		}

		if(!this.layout.pinnedItemsContainer)
		{
			this.layout.pinnedItemsContainer = Tag.render`<div class="ui-item-detail-stream-container-list ui-item-detail-stream-container-list-fixed"></div>`;
			this.layout.content.appendChild(this.layout.pinnedItemsContainer);
		}

		this.renderPinnedItems();

		if(!this.layout.tasksContainer)
		{
			this.layout.tasksContainer = Tag.render`<div class="ui-item-detail-stream-container-list"></div>`;
			this.layout.content.appendChild(this.layout.tasksContainer);
		}

		this.renderTasks();

		if(!this.layout.itemsContainer)
		{
			this.layout.itemsContainer = Tag.render`<div class="ui-item-detail-stream-container-list"></div>`;
			this.layout.content.appendChild(this.layout.itemsContainer);
		}

		this.renderItems();

		this.emit('onAfterRender');

		return this.layout.container;
	}

	getContainer(): ?Element
	{
		return this.layout.container
	}

	renderEditors()
	{
		if(!this.layout.container)
		{
			return;
		}
		if(!this.layout.editors)
		{
			this.layout.editorsTitle = Tag.render`<div class="ui-item-detail-stream-section-new-header"></div>`;
			this.layout.editorsContent = Tag.render`<div class="ui-item-detail-stream-section-new-detail"></div>`;
			this.layout.editors = Tag.render`<div class="ui-item-detail-stream-section ui-item-detail-stream-section-new">
				<div class="ui-item-detail-stream-section-icon"></div>
				<div class="ui-item-detail-stream-section-content">
					${this.layout.editorsTitle}
				</div>
				${this.layout.editorsContent}
			</div>`;

			let isTitleActive = true;
			Array.from(this.editors.values()).forEach((editor: Editor) => {
				this.layout.editorsTitle.appendChild(Tag.render`<a class="ui-item-detail-stream-section-new-action ${isTitleActive ? 'ui-item-detail-stream-section-new-action-active' : ''}">${editor.getTitle()}</a>`);
				this.layout.editorsContent.appendChild(editor.render());
				isTitleActive = false;
			});

			this.layout.container.appendChild(this.layout.editors);
		}
	}

	renderPinnedItems()
	{
		Dom.clean(this.layout.pinnedItemsContainer);
		this.createFixedAnchor();

		this.getPinnedItems().forEach((pinnedItem: Item) => {
			if(!pinnedItem.isRendered())
			{
				pinnedItem.render();
			}
			Dom.append(pinnedItem.getContainer(), this.layout.pinnedItemsContainer);
		});
	}

	createFixedAnchor()
	{
		this.fixedAnchor = Tag.render`<div class="ui-item-detail-stream-section-fixed-anchor"></div>`;
		Dom.prepend(this.fixedAnchor, this.layout.pinnedItemsContainer);
	}

	updateTasks(tasks: Array)
	{
		if(!this.tasks)
		{
			this.tasks = [];
		}
		const newTasks = [];
		tasks.forEach((data) => {
			const task = this.createItem(data);
			if(task)
			{
				newTasks.push(task);
				this.addUsers(data.users);
			}
		});
		const deleteTasks = [];
		this.tasks.forEach((task: Item) => {
			if(!Stream.getItemFromArray(newTasks, task.getId()))
			{
				deleteTasks.push(task);
			}
		});
		deleteTasks.forEach((task) => {
			this.deleteItem(task);
		});
		let tasksTitle = this.getTasksTitle();
		if(newTasks.length > 0)
		{
			if(!tasksTitle)
			{
				tasksTitle = this.renderTasksTitle();
				this.layout.tasksContainer.appendChild(tasksTitle);
			}
			newTasks.forEach((task: Item) => {
				if(!this.getTask(task.getId()))
				{
					this.tasks.push(task);
					Queue.add(new Show({
						item: task,
						container: this.layout.tasksContainer,
						insertAfter: tasksTitle,
					}));
				}
				else
				{
					const streamTask = this.getTask(task.getId());
					streamTask.setUserData(this.users);
					streamTask.update(task.getDataForUpdate());
				}
			});
		}
		else
		{
			const title = this.getTasksTitle();
			if(title)
			{
				Dom.remove(title);
				this.layout.tasksTitle = null;
			}
		}
		Queue.run();
	}

	renderTasks()
	{
		if(this.getTasks().length > 0)
		{
			this.layout.tasksContainer.appendChild(this.renderTasksTitle());
			this.getTasks().forEach((task: Item) => {
				if(!task.isRendered())
				{
					Dom.append(task.render(), this.layout.tasksContainer);
				}
			});
		}
		else
		{
			const title = this.getTasksTitle();
			if(title)
			{
				title.parentElement.removeChild(title);
			}
		}
	}

	getTasksTitle(): ?Element
	{
		return this.layout.tasksTitle;
	}

	renderTasksTitle(): Element
	{
		if(!this.layout.tasksTitle)
		{
			this.layout.tasksTitle = Tag.render`<div class="ui-item-detail-stream-section ui-item-detail-stream-section-planned-label">
				<div class="ui-item-detail-stream-section-content">
					<div class="ui-item-detail-stream-planned-text">${Loc.getMessage('UI_TIMELINE_TASKS_TITLE')}</div>
				</div>
			</div>`;
		}

		return this.layout.tasksTitle;
	}

	renderItems()
	{
		const lastItem = this.items[this.items.length - 1];
		this.items.forEach((item: Item) => {
			item.setIsLast((item === lastItem));
			if(!item.isRendered())
			{
				const day = this.constructor.getDayFromDate(item.getCreatedTime());
				if(!this.getDateSeparator(day))
				{
					const dateSeparator = this.createDateSeparator(day);
					Dom.append(dateSeparator, this.layout.itemsContainer);
				}
				Dom.append(item.render(), this.layout.itemsContainer);
			}
		});
	}

	getDateSeparator(day: string): ?Element
	{
		return this.dateSeparators.get(day);
	}

	createDateSeparator(day: string): Element
	{
		const separator = this.renderDateSeparator(day);
		this.dateSeparators.set(day, separator);

		return separator;
	}

	static getDayFromDate(date: Date): ?string
	{
		if(date instanceof Date)
		{
			if(Stream.isToday(date))
			{
				return BX.date.format('today');
			}

			return BX.date.format('d F Y', date);
		}

		return null;
	}

	static isToday(date: Date): boolean
	{
		return (BX.date.format('d F Y', date) === BX.date.format('d F Y'));
	}

	renderDateSeparator(day: string): Element
	{
		return Tag.render`<div class="ui-item-detail-stream-section ui-item-detail-stream-section-history-label">
			<div class="ui-item-detail-stream-section-content">
				<div class="ui-item-detail-stream-history-text">${day}</div>
			</div>
		</div>`;
	}

	getItemClassName(data: {
		action: ?string,
		itemClassName: ?string
	}): ?Function
	{
		let itemClassName = null;
		if(Type.isPlainObject(data) && Type.isString(data.itemClassName))
		{
			itemClassName = data.itemClassName;
		}

		if(itemClassName)
		{
			itemClassName = Reflection.getClass(itemClassName);
		}
		if(!Type.isFunction(itemClassName))
		{
			if(Type.isPlainObject(data) && Type.isString(data.action))
			{
				itemClassName = this.itemClasses.get(data.action);
			}
			if(!itemClassName)
			{
				itemClassName = History;
			}
		}

		return itemClassName;
	}

	insertItem(item: Item): this
	{
		if(!(item instanceof Item))
		{
			return this;
		}

		if(this.getItem(item.getId()))
		{
			return this;
		}

		this.items.unshift(item);
		const day = this.constructor.getDayFromDate(item.getCreatedTime());
		if(!day)
		{
			return this;
		}
		if(!this.getDateSeparator(day))
		{
			const separator = this.createDateSeparator(day);
			Dom.prepend(separator, this.layout.itemsContainer);
		}

		Queue.add(new Drop({
			item,
			insertAfter: this.getDateSeparator(day),
			container: this.layout.editorsContent,
		})).run();

		return this;
	}

	getUserTimeZoneOffset(): number
	{
		if(!this.userTimeZoneOffset)
		{
			this.userTimeZoneOffset = Text.toInteger(Loc.getMessage('USER_TZ_OFFSET'));
		}

		return this.userTimeZoneOffset;
	}

	getTimeFormat(): string
	{
		if(!this.timeFormat)
		{
			const datetimeFormat = Loc.getMessage("FORMAT_DATETIME").replace(/:SS/, "");
			const dateFormat = Loc.getMessage("FORMAT_DATE");
			this.timeFormat = BX.date.convertBitrixFormat(datetimeFormat.trim().replace(dateFormat, ""));
		}

		return this.timeFormat;
	}

	getDateTimeFormat(): string
	{
		if(!this.dateTimeFormat)
		{
			const datetimeFormat = Loc.getMessage("FORMAT_DATETIME").replace(/:SS/, "");
			this.dateTimeFormat = BX.date.convertBitrixFormat(datetimeFormat);
		}

		return this.dateTimeFormat;
	}

	startProgress()
	{
		this.progress = true;
		if(!this.getLoader().isShown())
		{
			const lastItem = this.items[this.items.length - 1];
			if(lastItem && lastItem.isRendered())
			{
				this.getLoader().show(lastItem.getContainer());
			}
			else
			{
				this.getLoader().show(this.layout.container);
			}
		}
	}

	stopProgress()
	{
		this.progress = false;
		this.getLoader().hide();
	}

	isProgress()
	{
		return (this.progress === true);
	}

	getLoader()
	{
		if(!this.loader)
		{
			this.loader = new Loader({size: 150});
		}

		return this.loader;
	}

	enableLoadOnScroll()
	{
		Event.bind(window, 'scroll', this.onScrollHandler);
	}

	disableLoadOnScroll()
	{
		Event.unbind(window, 'scroll', this.onScrollHandler);
	}

	onScroll()
	{
		if(this.isProgress())
		{
			return;
		}
		const lastItem = this.items[this.items.length - 1];
		if(!lastItem)
		{
			this.disableLoadOnScroll();
			return;
		}
		if(!lastItem.isRendered())
		{
			return;
		}
		const pos = lastItem.getContainer().getBoundingClientRect();
		if(pos.top <= document.documentElement.clientHeight)
		{
			this.emit('onScrollToTheBottom');
		}
	}

	getPinnedItemFromItem(item: Item): Item
	{
		const pinnedItem = Runtime.clone(item);
		if(item.isRendered())
		{
			pinnedItem.clearLayout();
		}
		pinnedItem.setTimeFormat(this.getDateTimeFormat());
		pinnedItem.isPinned = true;

		return pinnedItem;
	}

	onItemPinClick(item: Item)
	{
		if(item.isFixed)
		{
			this.pinItem(item);
		}
		else
		{
			this.unPinItem(item);
		}
		this.emit('onPinClick', {item});
	}

	pinItem(item: Item): Stream
	{
		const pinnedItem = this.getPinnedItem(item.getId());
		if(!pinnedItem)
		{
			this.getPinnedItems().push(this.getPinnedItemFromItem(item));
		}

		Queue.add(new Pin({
			item: this.getPinnedItem(item.getId()),
			anchor: this.fixedAnchor,
			startPosition: Dom.getPosition(item.getContainer()),
		})).run();

		return this;
	}

	unPinItem(item: Item): Stream
	{
		const pinnedItem = this.getPinnedItem(item.getId());
		if(pinnedItem === item)
		{
			const commonItem = this.getItem(pinnedItem.getId());
			if(commonItem)
			{
				commonItem.isFixed = false;
				commonItem.renderPin();
			}
		}
		if(pinnedItem && pinnedItem.isRendered())
		{
			Queue.add(new Hide({
				node: pinnedItem.getContainer(),
			})).run();
		}
		this.pinnedItems = this.pinnedItems.filter(filteredItem => filteredItem.getId() !== item.getId());
	}

	onItemDelete(item: Item)
	{
		this.deleteItem(item);
	}

	deleteItem(item: Item)
	{
		let itemIndex = Stream.getItemIndexFromArray(this.items, item.getId());
		const animations = [];
		if(itemIndex !== null)
		{
			if(item.isRendered())
			{
				const animation = new Hide({
					node: this.getItem(item.getId()).getContainer(),
				});
				animations.push(animation);
			}
			this.items.splice(itemIndex, 1);
		}
		itemIndex = Stream.getItemIndexFromArray(this.pinnedItems, item.getId());
		if(itemIndex !== null)
		{
			if(item.isRendered())
			{
				const animation = new Hide({
					node: this.getPinnedItem(item.getId()).getContainer(),
				});
				animations.push(animation);
			}
			this.pinnedItems.splice(itemIndex, 1);
		}
		itemIndex = Stream.getItemIndexFromArray(this.tasks, item.getId());
		if(itemIndex !== null)
		{
			let isAddHideAnimation = true;
			if(item.completedData)
			{
				const newItem = this.createItem(item.completedData);
				if(newItem)
				{
					if(!this.getItem(newItem.getId()))
					{
						this.items.unshift(newItem);
						const day = this.constructor.getDayFromDate(newItem.getCreatedTime());
						if(day)
						{
							if(!this.getDateSeparator(day))
							{
								const separator = this.createDateSeparator(day);
								Dom.prepend(separator, this.layout.itemsContainer);
							}

							Queue.add(new TaskComplete({
								item: newItem,
								task: item,
								insertAfter: this.getDateSeparator(day),
							})).run();

							isAddHideAnimation = false;
						}
					}
				}
			}
			if(isAddHideAnimation)
			{
				animations.push(new Hide({
					node: this.getTask(item.getId()).getContainer(),
				}));
			}
			this.tasks.splice(itemIndex, 1);
		}
		Queue.add(animations).run();
	}

	onError({message})
	{
		this.showError(message);
	}

	showError(message)
	{
		console.error(message);
	}

	addUsers(users: Object)
	{
		if(Type.isPlainObject(users))
		{
			if(!this.users)
			{
				this.users = new Map();
			}
			Object.keys(users).forEach((userId) => {
				userId = Text.toInteger(userId);
				if(userId > 0)
				{
					this.users.set(userId, users[userId]);
				}
			});
		}
	}

	addAnimation(animation: Animation)
	{
		Queue.add(animation).run();
	}
}