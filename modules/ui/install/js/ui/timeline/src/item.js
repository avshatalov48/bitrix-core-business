import {Text, Tag, Type, Event, Dom} from 'main.core';
import {EventEmitter} from 'main.core.events';
import {MenuManager, Menu} from 'main.popup';

import 'main.date';
import {Loader} from "main.loader";

/**
 * @mixes EventEmitter
 * @memberOf BX.UI.Timeline
 */
export class Item
{
	completedData: {};
	isPinned: boolean;
	isProgress: boolean = false;

	constructor(params: {
		id: number|string,
		action: ?string,
		itemClassName: ?string,
		title: ?string,
		description: ?string,
		htmlDescription: ?string,
		textDescription: ?string,
		createdTimestamp: ?number,
		userId: number,
		isFixed: boolean,
		eventIds: Set,
		data: ?{
			item: ?{name: ?string},
			stageFrom: ?{id: ?number, name: ?string},
			stageTo: ?{id: ?number, name: ?string},
			task: ?{id: ?number, title: ?string, description: ?string},
			fields: ?Array,
			scope: ?string,
		},
		events: ?{
			onPinClick: ?Function,
			onDelete: ?Function,
		}
	})
	{
		EventEmitter.makeObservable(this, 'UI.Timeline.Item');
		this.id = params.id;
		this.createdTimestamp = null;
		this.action = '';
		this.title = '';
		this.description = '';
		this.htmlDescription = '';
		this.textDescription = '';
		this.userId = params.userId;
		this.isFixed = (params.isFixed === true);
		this.data = {};
		this.eventIds = new Set();
		if(Type.isPlainObject(params))
		{
			if(Type.isSet(params.eventIds))
			{
				this.eventIds = params.eventIds;
			}
			if(Type.isString(params.action))
			{
				this.action = params.action;
			}
			if(Type.isString(params.title))
			{
				this.title = params.title;
			}
			if(Type.isString(params.description))
			{
				this.description = params.description;
			}
			if(Type.isString(params.htmlDescription))
			{
				this.htmlDescription = params.htmlDescription;
			}
			if(Type.isString(params.textDescription))
			{
				this.textDescription = params.textDescription;
			}
			if(Type.isNumber(params.createdTimestamp))
			{
				this.createdTimestamp = params.createdTimestamp;
			}
			if(Type.isPlainObject(params.data))
			{
				this.data = params.data;
			}
		}
		this.layout = {};
		this.timeFormat = 'H:M';
		this.nameFormat = '';
		this.users = new Map();
		this.isLast = false;
		this.events = params.events;
		this.isPinned = false;
	}

	afterRender()
	{
		Event.bind(this.renderPin(), 'click', this.onPinClick.bind(this));
		this.bindActionsButtonClick();
	}

	bindActionsButtonClick()
	{
		const button = this.getActionsButton();
		if(button)
		{
			Event.bind(button, 'click', this.onActionsButtonClick.bind(this));
		}
	}

	setIsLast(isLast: boolean): Item
	{
		this.isLast = isLast;
		if(this.isRendered())
		{
			if(this.isLast)
			{
				this.getContainer().classList.add('ui-item-detail-stream-section-last');
			}
			else
			{
				this.getContainer().classList.remove('ui-item-detail-stream-section-last');
			}
		}
	}

	setUserData(users: ?Map): Item
	{
		if(users)
		{
			this.users = users;
		}

		return this;
	}

	setTimeFormat(timeFormat: string): Item
	{
		if(Type.isString(timeFormat))
		{
			this.timeFormat = timeFormat;
		}

		return this;
	}

	setNameFormat(nameFormat: string): Item
	{
		if(Type.isString(nameFormat))
		{
			this.nameFormat = nameFormat;
		}

		return this;
	}

	getContainer(): ?Element
	{
		return this.layout.container;
	}

	isRendered(): boolean
	{
		return Type.isDomNode(this.getContainer());
	}

	getCreatedTime(): ?Date
	{
		if(this.createdTimestamp > 0)
		{
			this.createdTimestamp = Text.toInteger(this.createdTimestamp);
			return new Date(this.createdTimestamp);
		}

		return null;
	}

	formatTime(time): string
	{
		return BX.date.format(this.timeFormat, time);
	}

	getId(): string|number
	{
		return this.id;
	}

	getTitle(): ?string
	{
		return this.title;
	}

	getUserId(): number
	{
		return Text.toInteger(this.userId);
	}

	getScope(): ?string
	{
		if(Type.isString(this.data.scope))
		{
			return this.data.scope;
		}

		return null;
	}

	isScopeManual(): boolean
	{
		const scope = this.getScope();
		return (!scope || scope === 'manual');
	}

	isScopeAutomation(): boolean
	{
		return (this.getScope() === 'automation');
	}

	isScopeTask(): boolean
	{
		return (this.getScope() === 'task');
	}

	isScopeRest(): boolean
	{
		return (this.getScope() === 'rest');
	}

	render(): Element
	{
		this.layout.container = this.renderContainer();

		this.updateLayout();

		return this.layout.container;
	}

	updateLayout()
	{
		this.clearLayout(true);

		this.layout.container.appendChild(this.renderIcon());
		if(this.hasMenu())
		{
			this.layout.container.appendChild(this.renderActionsButton());
		}
		this.layout.container.appendChild((this.renderPin()));

		let content = this.getContent();
		if(!content)
		{
			content = this.renderContent();
		}
		this.layout.container.appendChild(content);

		this.afterRender();
	}

	renderContainer(): Element
	{
		return Tag.render`<div class="ui-item-detail-stream-section ${(this.isLast ? 'ui-item-detail-stream-section-last' : '')}"></div>`;
	}

	renderPin(): Element
	{
		if(!this.layout.pin)
		{
			this.layout.pin = Tag.render`<span class="ui-item-detail-stream-section-top-fixed-btn"></span>`;
		}

		if(this.isFixed)
		{
			this.layout.pin.classList.add('ui-item-detail-stream-section-top-fixed-btn-active');
		}
		else
		{
			this.layout.pin.classList.remove('ui-item-detail-stream-section-top-fixed-btn-active');
		}

		return this.layout.pin;
	}

	renderContent(): Element
	{
		this.layout.content = Tag.render`<div class="ui-item-detail-stream-section-content">${this.renderDescription()}</div>`;

		return this.getContent();
	}

	getContent(): ?Element
	{
		return this.layout.content;
	}

	renderDescription(): Element
	{
		this.layout.description = Tag.render`<div class="ui-item-detail-stream-content-event"></div>`;

		let header = this.renderHeader();
		if(header)
		{
			this.layout.description.appendChild(header);
		}

		this.layout.description.appendChild(this.renderMain());

		return this.layout.description;
	}

	renderHeader(): ?Element
	{
		return null;
	}

	renderHeaderUser(userId: ?number, size: ?number = 21): Element
	{
		userId = Text.toInteger(userId);
		let userData = {
			link: 'javascript: void(0)',
			fullName: '',
			photo: null,
		};
		if(userId > 0)
		{
			userData = this.users.get(userId);
		}
		if(!userData)
		{
			return Tag.render`<a></a>`;
		}

		const safeFullName = Tag.safe`${userData.fullName}`;
		return Tag.render`<a class="ui-item-detail-stream-content-employee" href="${userData.link}" target="_blank" title="${safeFullName}" ${userData.photo ? 'style="background-image: url(\'' + userData.photo + '\'); background-size: 100%;"' : ''}></a>`;
	}

	renderMain(): Element
	{
		this.layout.main = Tag.render`<div class="ui-item-detail-stream-content-detail">${this.description}</div>`;

		return this.getMain();
	}

	getMain(): ?Element
	{
		return this.layout.main;
	}

	renderIcon(): Element
	{
		this.layout.icon = Tag.render`<div class="ui-item-detail-stream-section-icon"></div>`;

		return this.layout.icon;
	}

	getItem(): ?{name: ?string}
	{
		if(Type.isPlainObject(this.data.item))
		{
			return this.data.item;
		}

		return null;
	}

	onPinClick()
	{
		this.isFixed = !this.isFixed;
		this.renderPin();
		if(Type.isFunction(this.events.onPinClick))
		{
			this.events.onPinClick(this);
		}
		this.emit('onPinClick');
	}

	clearLayout(isSkipContainer = false): Item
	{
		const container = this.getContainer();
		Object.keys(this.layout).forEach((name: string) =>
		{
			const node = this.layout[name];
			if(!isSkipContainer || container !== node)
			{
				Dom.remove(node);
				delete this.layout[name];
			}
		});

		return this;
	}

	getDataForUpdate(): {}
	{
		return {
			description: this.description,
			htmlDescription: this.htmlDescription,
			data: this.data,
			userId: this.userId,
		};
	}

	updateData(params: {
		description: ?string,
		htmlDescription: ?string,
		data: ?{},
		userId: ?number,
	}): Item
	{
		if(Type.isPlainObject(params))
		{
			if(Type.isString(params.description))
			{
				this.description = params.description;
			}
			if(Type.isString(params.htmlDescription))
			{
				this.htmlDescription = params.htmlDescription;
			}
			if(Type.isPlainObject(params.data))
			{
				this.data = params.data;
			}
			if(params.userId > 0)
			{
				this.userId = params.userId;
			}
		}

		return this;
	}

	update(params): Item
	{
		this.updateData(params).updateLayout();

		return this;
	}

	onError(params: {message: string})
	{
		if(Type.isFunction(this.events.onError))
		{
			this.events.onError(params);
		}
		this.emit('error', params);
	}

	onDelete()
	{
		if(Type.isFunction(this.events.onDelete))
		{
			this.events.onDelete(this);
		}
		this.emit('onDeleteComplete');
	}

	hasMenu(): boolean
	{
		return this.hasActions();
	}

	hasActions(): boolean
	{
		return (this.getActions().length > 0);
	}

	getActions(): Array
	{
		return [];
	}

	renderActionsButton(): Element
	{
		this.layout.contextMenuButton = Tag.render`<div class="ui-timeline-item-context-menu"></div>`;

		return this.getActionsButton()
	}

	getActionsButton(): ?Element
	{
		return this.layout.contextMenuButton;
	}

	getActionsMenuId(): string
	{
		return 'ui-timeline-item-context-menu-' + this.getId();
	}

	onActionsButtonClick()
	{
		this.getActionsMenu().toggle();
	}

	getActionsMenu(): Menu
	{
		return MenuManager.create({
			id: this.getActionsMenuId(),
			bindElement: this.getActionsButton(),
			items: this.getActions(),
			offsetTop: 0,
			offsetLeft: 16,
			angle: { position: "top", offset: 0 },
			events:
			{
				onPopupShow: this.onContextMenuShow.bind(this),
				onPopupClose: this.onContextMenuClose.bind(this),
			},
		});
	}

	onContextMenuShow()
	{
		this.getActionsButton().classList.add('active');
	}

	onContextMenuClose()
	{
		this.getActionsButton().classList.remove('active');
		this.getActionsMenu().destroy();
	}

	startProgress()
	{
		this.isProgress = true;
		this.getLoader().show();
	}

	stopProgress()
	{
		this.isProgress = false;
		if(this.getLoader().isShown())
		{
			this.getLoader().hide();
		}
	}

	getLoader(): Loader
	{
		if(!this.loader)
		{
			this.loader = new Loader({
				target: this.getContainer(),
			});
		}

		return this.loader;
	}
}