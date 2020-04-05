import Menu from './menu';
import { Type, Text, Dom, Event, Tag } from 'main.core';
import { EventEmitter } from 'main.core.events';
import { MenuItemOptions } from './menu-types';

const aliases = {
	onSubMenuShow: { namespace: 'BX.Main.Menu.Item', eventName: 'SubMenu:onShow' },
	onSubMenuClose: { namespace: 'BX.Main.Menu.Item', eventName: 'SubMenu:onClose' }
};

EventEmitter.registerAliases(aliases);

export default class MenuItem extends EventEmitter
{
	constructor(options: MenuItemOptions)
	{
		super();
		this.setEventNamespace('BX.Main.Menu.Item');

		options = options || {};
		this.options = options;

		this.id = options.id || Text.getRandom();
		this.text = Type.isStringFilled(options.text) ? options.text : '';
		this.title = Type.isStringFilled(options.title) ? options.title : '';
		this.delimiter = options.delimiter === true;
		this.href = Type.isStringFilled(options.href) ? options.href : null;
		this.target = Type.isStringFilled(options.target) ? options.target : null;
		this.dataset = Type.isPlainObject(options.dataset) ? options.dataset : null;
		this.className = Type.isStringFilled(options.className) ? options.className : null;
		this.menuShowDelay = Type.isNumber(options.menuShowDelay) ? options.menuShowDelay : 300;
		this.subMenuOffsetX = Type.isNumber(options.subMenuOffsetX) ? options.subMenuOffsetX : 4;
		this._items = Type.isArray(options.items) ? options.items : [];
		this.disabled = options.disabled === true;
		this.cacheable = options.cacheable === true;

		/**
		 *
		 * @type {function|string}
		 */
		this.onclick =
			Type.isStringFilled(options.onclick) || Type.isFunction(options.onclick)
				? options.onclick
				: null
		;

		this.subscribeFromOptions(options.events, aliases);

		/**
		 *
		 * @type {Menu}
		 */
		this.menuWindow = null;

		/**
		 *
		 * @type {Menu}
		 */
		this.subMenuWindow = null;

		/**
		 *
		 * @type {{item: Element, text: Element}}
		 */
		this.layout = {
			item: null,
			text: null
		};

		this.getLayout(); //compatibility

		//compatibility
		//now use this.options
		this.events = {};
		this.items = [];
		for (let property in options)
		{
			if (options.hasOwnProperty(property) && typeof (this[property]) === 'undefined')
			{
				this[property] = options[property];
			}
		}
	}

	getLayout(): Element
	{
		if (this.layout.item)
		{
			return this.layout;
		}

		if (this.delimiter)
		{
			if (Type.isStringFilled(this.text))
			{
				this.layout.item = Dom.create('span', {
					props: {
						className: 'popup-window-delimiter-section'
					},
					children: [
						(this.layout.text = Dom.create('span', {
							props: {
								className: 'popup-window-delimiter-text'
							},
							html: this.text
						}))
					]
				});

			}
			else
			{
				this.layout.item = Tag.render`<span class="popup-window-delimiter">`;
			}
		}
		else
		{
			this.layout.item = Dom.create(this.href ? 'a' : 'span', {
				props: {
					className: [
						'menu-popup-item',
						(this.className ? this.className : 'menu-popup-no-icon'),
						(this.hasSubMenu() ? 'menu-popup-item-submenu' : '')
					].join(' ')
				},

				attrs: {
					title: this.title,
					onclick: Type.isString(this.onclick) ? this.onclick : '', // compatibility
					target: this.target ? this.target : ''
				},

				dataset: this.dataset,

				events:
					Type.isFunction(this.onclick)
						? { click: this.onItemClick.bind(this) }
						: null
				,

				children: [
					Dom.create('span', { props: { className: 'menu-popup-item-icon' } }),
					(this.layout.text = Dom.create('span', {
						props: {
							className: 'menu-popup-item-text'
						},
						html: this.text
					}))
				]
			});

			if (this.href)
			{
				this.layout.item.href = this.href;
			}

			if (this.isDisabled())
			{
				this.disable();
			}

			Event.bind(this.layout.item, 'mouseenter', this.onItemMouseEnter.bind(this));
			Event.bind(this.layout.item, 'mouseleave', this.onItemMouseLeave.bind(this));
		}

		return this.layout;
	}

	getContainer(): Element
	{
		return this.getLayout().item;
	}

	getTextContainer(): Element
	{
		return this.getLayout().text;
	}

	getText(): string
	{
		return this.text;
	}

	setText(text: string)
	{
		if (Type.isString(text))
		{
			this.text = text;
			this.getTextContainer().innerHTML = text;
		}
	}

	hasSubMenu(): boolean
	{
		return this.subMenuWindow !== null || this._items.length;
	}

	showSubMenu(): void
	{
		this.addSubMenu(this._items);

		if (this.subMenuWindow)
		{
			Dom.addClass(this.layout.item, 'menu-popup-item-open');

			this.closeSiblings();
			this.closeChildren();

			const popupWindow = this.subMenuWindow.getPopupWindow();
			if (!popupWindow.isShown())
			{
				this.emit('SubMenu:onShow');
				popupWindow.show();
			}

			this.adjustSubMenu();
		}
	}

	addSubMenu(items: []): Menu
	{
		if (this.subMenuWindow !== null || !Type.isArray(items) || !items.length)
		{
			return;
		}

		const rootMenuWindow = this.getMenuWindow().getRootMenuWindow() || this.getMenuWindow();
		const options = rootMenuWindow.params;

		//Override root menu options
		options.autoHide = false;
		options.menuShowDelay = this.menuShowDelay;
		options.cacheable = this.isCacheable();
		options.zIndexAbsolute = this.getMenuWindow().getPopupWindow().getZindex() + 2;
		options.bindOptions = {
			forceTop: true,
			forceLeft: true,
			forceBindPosition: true
		};

		delete options.zIndex;
		delete options.events;
		delete options.angle;
		delete options.overlay;

		this.subMenuWindow = new Menu('popup-submenu-' + this.id, this.layout.item, items, options);
		this.subMenuWindow.setParentMenuWindow(this.getMenuWindow());
		this.subMenuWindow.setParentMenuItem(this);

		this.subMenuWindow.getPopupWindow().subscribe('onDestroy', this.handleSubMenuDestroy.bind(this));
		Dom.addClass(this.layout.item, 'menu-popup-item-submenu');

		return this.subMenuWindow;
	}

	closeSubMenu(): void
	{
		this.clearSubMenuTimeout();

		if (this.subMenuWindow)
		{
			Dom.removeClass(this.layout.item, 'menu-popup-item-open');

			this.closeChildren();

			const popup = this.subMenuWindow.getPopupWindow();
			if (popup.isShown())
			{
				this.emit('SubMenu:onClose');
			}

			this.subMenuWindow.close();
		}
	}

	closeSiblings(): void
	{
		const siblings = this.menuWindow.getMenuItems();
		for (let i = 0; i < siblings.length; i++)
		{
			if (siblings[i] !== this)
			{
				siblings[i].closeSubMenu();
			}
		}
	}

	closeChildren(): void
	{
		if (this.subMenuWindow)
		{
			const children = this.subMenuWindow.getMenuItems();
			for (let i = 0; i < children.length; i++)
			{
				children[i].closeSubMenu();
			}
		}
	}

	destroySubMenu(): void
	{
		if (this.subMenuWindow)
		{
			Dom.removeClass(this.layout.item, 'menu-popup-item-open menu-popup-item-submenu');
			this.destroyChildren();
			this.subMenuWindow.destroy();

			this.subMenuWindow = null;
			this._items = [];
		}
	}

	destroyChildren(): void
	{
		if (this.subMenuWindow)
		{
			const children = this.subMenuWindow.getMenuItems();
			for (let i = 0; i < children.length; i++)
			{
				children[i].destroySubMenu();
			}
		}
	}

	adjustSubMenu(): void
	{
		if (!this.subMenuWindow || !this.layout.item)
		{
			return;
		}

		const popupWindow = this.subMenuWindow.getPopupWindow();
		const itemRect = this.layout.item.getBoundingClientRect();

		let offsetLeft = itemRect.width + this.subMenuOffsetX;
		let offsetTop = itemRect.height + this.getPopupPadding();
		let angleOffset = itemRect.height / 2 - this.getPopupPadding();
		let anglePosition = 'left';

		const popupWidth = popupWindow.getPopupContainer().offsetWidth;
		const popupHeight = popupWindow.getPopupContainer().offsetHeight;
		const popupBottom = itemRect.top + popupHeight;

		const clientWidth = document.documentElement.clientWidth;
		const clientHeight = document.documentElement.clientHeight;

		// let's try to fit a submenu to the browser viewport
		const exceeded = popupBottom - clientHeight;
		if (exceeded > 0)
		{
			let roundOffset = Math.ceil(exceeded / itemRect.height) * itemRect.height;
			if (roundOffset > itemRect.top)
			{
				// it cannot be higher than the browser viewport.
				roundOffset -= Math.ceil((roundOffset - itemRect.top) / itemRect.height) * itemRect.height;
			}

			if (itemRect.bottom > (popupBottom - roundOffset))
			{
				// let's sync bottom boundaries.
				roundOffset -= itemRect.bottom - (popupBottom - roundOffset) + this.getPopupPadding();
			}

			offsetTop += roundOffset;
			angleOffset += roundOffset;
		}

		if ((itemRect.left + offsetLeft + popupWidth) > clientWidth)
		{
			const left = itemRect.left - popupWidth - this.subMenuOffsetX;
			if (left > 0)
			{
				offsetLeft = -popupWidth - this.subMenuOffsetX;
				anglePosition = 'right';
			}
		}

		popupWindow.setBindElement(this.layout.item);
		popupWindow.setOffset({ offsetLeft: offsetLeft, offsetTop: -offsetTop });
		popupWindow.setAngle({ position: anglePosition, offset: angleOffset });
		popupWindow.adjustPosition();
	}

	getPopupPadding(): number
	{
		if (!Type.isNumber(this.popupPadding))
		{
			if (this.subMenuWindow)
			{
				const menuContainer = this.subMenuWindow.layout.menuContainer;
				this.popupPadding = parseInt(Dom.style(menuContainer, 'paddingTop'), 10);
			}
			else
			{
				this.popupPadding = 0;
			}
		}

		return this.popupPadding;
	}

	getSubMenu(): Menu | null
	{
		return this.subMenuWindow;
	}

	getId(): string
	{
		return this.id;
	}

	setMenuWindow(menu: Menu): string
	{
		this.menuWindow = menu;
	}

	getMenuWindow(): Menu | null
	{
		return this.menuWindow;
	}

	getMenuShowDelay(): number
	{
		return this.menuShowDelay;
	}

	enable(): void
	{
		this.disabled = false;
		this.getContainer().classList.remove('menu-popup-item-disabled');
	}

	disable(): void
	{
		this.disabled = true;
		this.closeSubMenu();
		this.getContainer().classList.add('menu-popup-item-disabled');
	}

	isDisabled(): boolean
	{
		return this.disabled;
	}

	setCacheable(cacheable): void
	{
		this.cacheable = cacheable !== false;
	}

	isCacheable(): boolean
	{
		return this.cacheable;
	}

	/**
	 * @private
	 */
	onItemClick(event): void
	{
		this.onclick.call(this.menuWindow, event, this); //compatibility
	}

	/**
	 * @private
	 */
	onItemMouseEnter(event): void
	{
		if (this.isDisabled())
		{
			return;
		}

		EventEmitter.emit(this, 'onMouseEnter', undefined, { thisArg: this });

		this.clearSubMenuTimeout();

		if (this.hasSubMenu())
		{
			this.subMenuTimeout = setTimeout(function() {
				this.showSubMenu();
			}.bind(this), this.menuShowDelay);
		}
		else
		{
			this.subMenuTimeout = setTimeout(function() {
				this.closeSiblings();
			}.bind(this), this.menuShowDelay);
		}
	}

	/**
	 * @private
	 */
	onItemMouseLeave(event): void
	{
		if (this.isDisabled())
		{
			return;
		}

		EventEmitter.emit(this, 'onMouseLeave', undefined, { thisArg: this });

		this.clearSubMenuTimeout();
	}

	/**
	 * @private
	 */
	clearSubMenuTimeout(): void
	{
		if (this.subMenuTimeout)
		{
			clearTimeout(this.subMenuTimeout);
		}

		this.subMenuTimeout = null;
	}

	/**
	 * @private
	 */
	handleSubMenuDestroy(): void
	{
		this.subMenuWindow = null;
	}
}