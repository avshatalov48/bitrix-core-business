import {
	Type,
	Tag,
	Text,
	Cache,
	Loc,
	Dom,
	Runtime,
	ZIndexManager,
	Event,
	Reflection,
	ajax as Ajax,
	type JsonObject,
} from 'main.core';

import { EventEmitter } from 'main.core.events';
import { MenuManager, PopupManager, Popup, type Menu } from 'main.popup';

import { ToolbarItem } from './toolbar-item';
import { getInstance } from './get-instance';

import { type ToolbarItemOptions } from './types/toolbar-item-options';
import { type ToolbarOptions } from './types/toolbar-options';

export class Toolbar extends EventEmitter
{
	constructor(toolbarOptions: ToolbarOptions)
	{
		super();
		this.setEventNamespace('BX.Main.SidePanel.Toolbar');

		const options = Type.isPlainObject(toolbarOptions) ? toolbarOptions : {};
		if (!Type.isStringFilled(options.context))
		{
			throw new Error('BX.Main.SidePanel.Toolbar: "context" parameter is required.');
		}

		this.context = options.context;
		this.items = [];
		this.rendered = false;
		this.refs = new Cache.MemoryCache();
		this.container = null;
		this.lsKey = 'bx.sidepanel.toolbar.item';

		this.initialPosition = { right: '5px', bottom: '20px' };
		this.shiftedPosition = { right: '5px', bottom: '20px' };
		if (Type.isPlainObject(options.position))
		{
			this.initialPosition = options.position;
		}

		if (Type.isPlainObject(options.shiftedPosition))
		{
			this.shiftedPosition = options.shiftedPosition;
		}

		this.collapsed = options.collapsed !== false;
		this.muted = false;
		this.shifted = false;

		this.maxVisibleItems = Type.isNumber(options.maxVisibleItems) ? Math.max(options.maxVisibleItems, 1) : 5;

		this.addItems(options.items);

		const item = this.restoreItemFromLocalStorage();
		if (item !== null)
		{
			const { entityType, entityId } = item;
			if (this.getItem(entityType, entityId))
			{
				this.clearLocalStorage();
			}
			else
			{
				this.minimizeItem(item);
			}
		}
	}

	show(): void
	{
		Dom.addClass(this.getContainer(), '--show');
	}

	isShown(): boolean
	{
		return Dom.hasClass(this.getContainer(), '--show');
	}

	hide(): void
	{
		Dom.removeClass(this.getContainer(), '--show');
	}

	mute(): boolean
	{
		if (this.muted)
		{
			return false;
		}

		this.muted = true;
		Dom.addClass(this.getContainer(), '--muted');

		return true;
	}

	unmute(): boolean
	{
		if (!this.muted)
		{
			return false;
		}

		this.muted = false;
		Dom.removeClass(this.getContainer(), '--muted');

		return true;
	}

	isMuted(): boolean
	{
		return this.muted;
	}

	toggleMuteness(): boolean
	{
		if (this.canShowOnTop())
		{
			return this.unmute();
		}

		return this.mute();
	}

	shift(): boolean
	{
		if (this.shifted)
		{
			return false;
		}

		this.shifted = true;
		Dom.addClass(this.getContainer(), '--shifted');
		Dom.style(document.body, '--side-panel-toolbar-shifted', 1);
		this.setPosition(this.getContainer(), this.shiftedPosition);

		return true;
	}

	unshift(): boolean
	{
		if (!this.shifted)
		{
			return false;
		}

		this.shifted = false;
		Dom.removeClass(this.getContainer(), '--shifted');
		Dom.style(document.body, '--side-panel-toolbar-shifted', null);
		this.setPosition(this.getContainer(), this.initialPosition);

		return true;
	}

	isShifted(): boolean
	{
		return this.shifted;
	}

	toggleShift(): boolean
	{
		const sliders = getInstance().getOpenSliders();
		if (sliders.length === 0 || (sliders.length === 1 && !sliders[0].isOpen()))
		{
			return this.unshift();
		}

		return this.shift();
	}

	setPosition(container: HTMLElement, position): boolean
	{
		for (const prop of ['top', 'right', 'bottom', 'left'])
		{
			Dom.style(container, prop, null);
			if (Type.isStringFilled(position[prop]))
			{
				Dom.style(container, prop, position[prop]);
			}
		}
	}

	collapse(immediately: boolean = false): void
	{
		if (this.collapsed)
		{
			return;
		}

		if (immediately === true)
		{
			Dom.addClass(this.getContainer(), '--collapsed');
			Dom.style(this.getContentContainer(), 'width', null);
		}
		else
		{
			const width = this.getContentContainer().scrollWidth;
			Dom.style(this.getContentContainer(), 'width', `${width}px`);

			Event.unbindAll(this.getContentContainer(), 'transitionend');

			requestAnimationFrame(() => {
				requestAnimationFrame(() => {
					Dom.style(this.getContentContainer(), 'width', 0);
					Event.bindOnce(this.getContentContainer(), 'transitionend', () => {
						Dom.addClass(this.getContainer(), '--collapsed');
						Dom.style(this.getContentContainer(), 'width', null);
					});
				});
			});
		}

		this.collapsed = true;
	}

	expand(immediately: boolean = false): void
	{
		if (!this.collapsed)
		{
			return;
		}

		if (immediately === true)
		{
			Dom.removeClass(this.getContainer(), '--collapsed');
			Dom.style(this.getContentContainer(), 'width', null);
		}
		else
		{
			Dom.removeClass(this.getContainer(), '--collapsed');
			const width = this.getContentContainer().scrollWidth;
			Dom.style(this.getContentContainer(), 'width', 0);

			Event.unbindAll(this.getContentContainer(), 'transitionend');

			requestAnimationFrame(() => {
				requestAnimationFrame(() => {
					Dom.style(this.getContentContainer(), 'width', `${width}px`);
					Event.bindOnce(this.getContentContainer(), 'transitionend', () => {
						Dom.style(this.getContentContainer(), 'width', null);
					});
				});
			});
		}

		this.collapsed = false;
	}

	toggle(): void
	{
		if (this.collapsed)
		{
			this.request('expand');
			this.expand();
		}
		else
		{
			this.request('collapse');
			this.collapse();
		}
	}

	isCollapsed(): boolean
	{
		return this.collapsed;
	}

	getItems(): ToolbarItem[]
	{
		return this.items;
	}

	getItemsCount(): number
	{
		return this.items.length;
	}

	addItems(itemsOptions)
	{
		if (Type.isArrayFilled(itemsOptions))
		{
			itemsOptions.forEach((itemOptions) => {
				this.addItem(itemOptions);
			});
		}
	}

	addItem(itemOptions: ToolbarItemOptions): ToolbarItem | null
	{
		const item = this.createItem(itemOptions);
		if (item === null)
		{
			return null;
		}

		this.items.push(item);

		if (this.rendered)
		{
			this.redraw();
		}

		return item;
	}

	/**
	 *
	 * @param itemOptions
	 * @returns {ToolbarItem|null}
	 */
	prependItem(itemOptions): ToolbarItem | null
	{
		const item = this.createItem(itemOptions);
		if (item === null)
		{
			return null;
		}

		this.items.unshift(item);

		if (this.rendered)
		{
			this.redraw();
		}

		return item;
	}

	createItem(itemOptions: ToolbarItemOptions): ToolbarItem | null
	{
		const options = Type.isPlainObject(itemOptions) ? itemOptions : {};

		if (
			!Type.isStringFilled(options.entityType)
			|| !(Type.isStringFilled(options.entityId) || Type.isNumber(options.entityId))
			|| !Type.isStringFilled(options.title)
			|| !Type.isStringFilled(options.url)
		)
		{
			return null;
		}

		const item = new ToolbarItem(options);
		if (!Type.isStringFilled(item.getEntityName()))
		{
			const minimizeOptions = getInstance().getMinimizeOptions(item.getUrl());
			if (Type.isPlainObject(minimizeOptions) && Type.isStringFilled(minimizeOptions.entityName))
			{
				item.setEntityName(minimizeOptions.entityName);
			}
		}

		item.subscribe('onRemove', this.handleItemRemove.bind(this));

		return item;
	}

	/**
	 * @private
	 */
	minimizeItem(itemOptions: ToolbarItemOptions): ToolbarItem | null
	{
		const { entityType, entityId } = itemOptions;
		let item = this.getItem(entityType, entityId);
		const itemExists = item !== null;
		if (!itemExists)
		{
			item = this.prependItem(itemOptions);
		}

		if (item !== null)
		{
			if (!itemExists)
			{
				this.saveItemToLocalStorage(item);
			}

			this.request('minimize', item)
				.then((response) => {
					if (response.status === 'success')
					{
						this.clearLocalStorage();
					}
				}).catch(() => {
					this.clearLocalStorage();
					this.removeItem(item);
				})
			;
		}

		return item;
	}

	saveItemToLocalStorage(item): void
	{
		const cache = { item, ttl: Date.now() };
		localStorage.setItem(this.lsKey, JSON.stringify(cache));
	}

	restoreItemFromLocalStorage(): JsonObject | null
	{
		const data = localStorage.getItem(this.lsKey);
		if (Type.isStringFilled(data))
		{
			const { item, ttl } = JSON.parse(data);
			if ((Date.now() - ttl) > 10000)
			{
				this.clearLocalStorage();

				return null;
			}

			if (Type.isPlainObject(item))
			{
				return item;
			}
		}

		return null;
	}

	clearLocalStorage(): void
	{
		localStorage.removeItem(this.lsKey);
	}

	getContext(): string
	{
		return this.context;
	}

	request(action, item, data): Promise
	{
		const additional = Type.isPlainObject(data) ? data : {};

		return Ajax.runAction(`main.api.sidepanel.toolbar.${action}`, {
			json: {
				toolbar: {
					context: this.getContext(),
				},
				item: item ? item.toJSON() : null,
				...additional,
			},
		});
	}

	handleItemRemove(event): void
	{
		const item = event.getTarget();
		item.hideTooltip();
		this.removeItem(item);
	}

	handleMenuItemRemove(event): void
	{
		event.preventDefault();
		event.stopPropagation();

		const itemId = event.currentTarget.dataset.menuItemId;
		const itemToRemove = this.getItemById(itemId);
		if (itemToRemove)
		{
			this.removeItem(itemToRemove);
		}

		const menu = this.getMenu();
		if (menu)
		{
			menu.removeMenuItem(itemId);

			const invisibleItemsCount = this.getItems().reduce((count, item) => {
				return item.isRendered() ? count : count + 1;
			}, 0);

			if (invisibleItemsCount > 0)
			{
				menu.getPopupWindow().adjustPosition();
			}
			else
			{
				menu.close();
			}
		}
	}

	removeItem(itemToRemove): void
	{
		itemToRemove.remove();
		this.items = this.items.filter((item) => {
			return item !== itemToRemove;
		});

		const restored = this.restoreItemFromLocalStorage();
		if (restored !== null)
		{
			const { entityType, entityId } = restored;
			if (itemToRemove.getEntityType() === entityType && itemToRemove.getEntityId() === entityId)
			{
				this.clearLocalStorage();
			}
		}

		if (this.rendered)
		{
			this.redraw();
			this.request('remove', itemToRemove);

			if (this.getItemsCount() === 0)
			{
				this.hide();
			}
		}
	}

	redraw(): void
	{
		let visibleItemsCount = 0;
		for (let i = 0; i < this.getItems().length; i++)
		{
			const item = this.getItems()[i];
			if (visibleItemsCount >= this.maxVisibleItems)
			{
				if (item.isRendered())
				{
					item.remove();
				}
			}
			else
			{
				if (!item.isRendered())
				{
					const previousItem = this.getItems()[i - 1] || null;
					const nextItem = this.getItems()[i + 1] || null;
					if (previousItem)
					{
						item.insertAfter(previousItem.getContainer());
					}
					else if (nextItem)
					{
						// eslint-disable-next-line @bitrix24/bitrix24-rules/no-native-dom-methods
						item.insertBefore(nextItem.getContainer());
					}
					else
					{
						item.appendTo(this.getItemsContainer());
					}
				}

				visibleItemsCount++;
			}
		}
	}

	removeAll(): void
	{
		this.getItemsContainer().innerHTML = '';
		this.items = [];
		this.clearLocalStorage();
	}

	getItem(entityType: string, entityId: string | number): ToolbarItem | null
	{
		return this.items.find((item) => {
			return item.getEntityType() === entityType && item.getEntityId() === String(entityId);
		}) || null;
	}

	getItemByUrl(url: string): ToolbarItem | null
	{
		return this.items.find((item) => item.getUrl() === url) || null;
	}

	getItemById(id: string): ToolbarItem | null
	{
		return this.items.find((item) => item.getId() === id) || null;
	}

	getContainer(): HTMLElement
	{
		return this.refs.remember('container', () => {
			const classes = [];
			if (this.collapsed)
			{
				classes.push('--collapsed');
			}

			const container = Tag.render`
				<div class="side-panel-toolbar ${classes.join(' ')}">
					${this.getContentContainer()}
					<div class="side-panel-toolbar-toggle" onclick="${this.handleToggleClick.bind(this)}"></div>
				</div>
			`;

			this.setPosition(container, this.initialPosition);
			Dom.append(container, document.body);
			ZIndexManager.register(container, { alwaysOnTop: true });
			this.rendered = true;

			const toggleMuteness = Runtime.debounce(this.toggleMuteness, 50, this);
			EventEmitter.subscribe('BX.Main.Popup:onShow', toggleMuteness);
			EventEmitter.subscribe('BX.Main.Popup:onClose', toggleMuteness);
			EventEmitter.subscribe('BX.Main.Popup:onDestroy', toggleMuteness);
			EventEmitter.subscribe('onWindowClose', toggleMuteness);
			EventEmitter.subscribe('onWindowRegister', toggleMuteness);

			let forceCollapsed = false;
			const onSliderClose = () => {
				this.toggleMuteness();
				if (this.isMuted())
				{
					return;
				}

				this.toggleShift();
				if (!this.isShifted() && forceCollapsed)
				{
					forceCollapsed = false;
					this.expand();
				}
			};

			EventEmitter.subscribe('SidePanel.Slider:onClosing', onSliderClose);
			EventEmitter.subscribe('SidePanel.Slider:onCloseComplete', onSliderClose);
			EventEmitter.subscribe('SidePanel.Slider:onDestroyComplete', onSliderClose);
			EventEmitter.subscribe('SidePanel.Slider:onOpening', () => {
				this.toggleMuteness();
				if (this.isMuted())
				{
					return;
				}

				if (!this.isCollapsed())
				{
					forceCollapsed = true;
					this.collapse();
				}

				this.toggleShift();
			});

			EventEmitter.subscribe('BX.UI.Viewer.Controller:onBeforeShow', toggleMuteness);
			EventEmitter.subscribe(
				'BX.UI.Viewer.Controller:onClose',
				Runtime.debounce(this.toggleMuteness, 500, this),
			);

			Event.bind(window, 'resize', Runtime.throttle(() => {
				const menu = this.getMenu();
				if (menu !== null)
				{
					menu.close();
				}
			}, 300));

			return container;
		});
	}

	getContentContainer(): HTMLElement
	{
		return this.refs.remember('content-container', () => {
			return Tag.render`
				<div class="side-panel-toolbar-content">
					<div class="side-panel-toolbar-collapse-btn" onclick="${this.handleToggleClick.bind(this)}">
						<div class="ui-icon-set --chevron-right"></div>
					</div>
					${this.getItemsContainer()}
					${this.getMoreButton()}
				</div>
			`;
		});
	}

	getItemsContainer(): HTMLElement
	{
		return this.refs.remember('items-container', () => {
			const container = Tag.render`<div class="side-panel-toolbar-items"></div>`;
			[...this.items].slice(0, this.maxVisibleItems).forEach((item) => {
				item.appendTo(container);
			});

			return container;
		});
	}

	getMoreButton(): HTMLElement
	{
		return this.refs.remember('more-button', () => {
			return Tag.render`
				<div class="side-panel-toolbar-more-btn" onclick="${this.handleMoreBtnClick.bind(this)}">
					<div class="ui-icon-set --more"></div>
				</div>
			`;
		});
	}

	handleMoreBtnClick(event): void
	{
		const targetNode = this.getMoreButton();
		const rect = targetNode.getBoundingClientRect();
		const targetNodeWidth = rect.width;

		const items = [...this.items].filter((item) => !item.isRendered()).map((item) => {
			const title = (
				Type.isStringFilled(item.getEntityName())
					? `${item.getEntityName()}\n${item.getTitle()}`
					: item.getTitle()
			);

			return {
				id: item.getId(),
				html: this.createMenuItemText(item),
				title,
				href: item.getUrl(),
				onclick: () => {
					menu.close();
				},
			};
		});

		if (items.length > 0)
		{
			items.push({
				delimiter: true,
			});
		}

		items.push({
			text: Loc.getMessage('MAIN_SIDEPANEL_REMOVE_ALL'),
			onclick: () => {
				this.removeAll();
				this.hide();
				menu.close();

				this.request('removeAll');
			},
		});

		const menu = MenuManager.create({
			id: 'sidepanel-toolbar-more-btn',
			cacheable: false,
			bindElement: rect,
			bindOptions: {
				forceBindPosition: true,
				forceTop: true,
				position: 'top',
			},
			maxWidth: 260,
			fixed: true,
			offsetTop: 0,
			maxHeight: 305,
			items,
			events: {
				onShow: (event) => {
					const popup = event.getTarget();
					const popupWidth = popup.getPopupContainer().offsetWidth;
					const offsetLeft = (targetNodeWidth / 2) - (popupWidth / 2);
					const angleShift = Popup.getOption('angleLeftOffset') - Popup.getOption('angleMinTop');

					popup.setAngle({ offset: popupWidth / 2 - angleShift });
					popup.setOffset({ offsetLeft: offsetLeft + Popup.getOption('angleLeftOffset') });
				},
			},
		});

		menu.show();
	}

	canShowOnTop(): boolean
	{
		const popups = PopupManager.getPopups();
		for (const popup of popups)
		{
			if (!popup.isShown())
			{
				continue;
			}

			if (
				popup.getId().startsWith('timeman_weekly_report_popup_')
				|| popup.getId().startsWith('timeman_daily_report_popup_')
				|| Dom.hasClass(popup.getPopupContainer(), 'b24-whatsnew__popup')
			)
			{
				return false;
			}
		}

		if (Reflection.getClass('BX.UI.Viewer.Instance') && BX.UI.Viewer.Instance.isOpen())
		{
			return false;
		}

		const sliders = getInstance().getOpenSliders();
		for (const slider of sliders)
		{
			const sliderId = slider.getUrl().toString();
			if (
				slider.shouldHideToolbarOnOpen()
				|| sliderId.startsWith('im:slider')
				|| sliderId.startsWith('release-slider')
				|| sliderId.startsWith('main:helper')
				|| sliderId.startsWith('ui:info_helper')
			)
			{
				return false;
			}
		}

		const stack = ZIndexManager.getStack(document.body);
		const components = stack === null ? [] : stack.getComponents();
		for (const component of components)
		{
			if (component.getOverlay() !== null && component.getOverlay().offsetWidth > 0)
			{
				return false;
			}
		}

		return true;
	}

	getMenu(): Menu | null
	{
		return MenuManager.getMenuById('sidepanel-toolbar-more-btn');
	}

	createMenuItemText(item): HTMLElement
	{
		return Tag.render`
			<span class="side-panel-toolbar-menu-item">${[
				Tag.render`
					<span class="side-panel-toolbar-menu-item-title">${Text.encode(item.getTitle())}</span>
				`,
				Tag.render`
					<span
						class="side-panel-toolbar-menu-item-remove"
						data-slider-ignore-autobinding="true"
						data-menu-item-id="${item.getId()}"
						onclick="${this.handleMenuItemRemove.bind(this)}"
					>
						<span class="ui-icon-set --cross-20" data-slider-ignore-autobinding="true"></span>
					</span>
				`,
			]}</span>
		`;
	}

	handleToggleClick(): void
	{
		this.toggle();
	}
}
