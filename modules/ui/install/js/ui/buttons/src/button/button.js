import BaseButton from '../base-button';
import { Menu } from 'main.popup';
import { Type, Dom, Text, Event } from 'main.core';
import ButtonColor from './button-color';
import ButtonSize from './button-size';
import ButtonIcon from './button-icon';
import ButtonState from './button-state';
import ButtonStyle from './button-style';
import ButtonTag from './button-tag';
import { ButtonOptions } from './button-options';
import { type MenuOptions } from 'main.popup';

/**
 * @namespace {BX.UI}
 */
export default class Button extends BaseButton
{
	static BASE_CLASS = 'ui-btn';

	constructor(options: ButtonOptions)
	{
		options = Type.isPlainObject(options) ? options : {};
		options.baseClass = Type.isStringFilled(options.baseClass) ? options.baseClass : Button.BASE_CLASS;

		super(options);

		this.size = null;
		this.color = null;
		this.icon = null;
		this.state = null;
		this.id = null;
		this.context = null;

		this.menuWindow = null;
		this.handleMenuClick = this.handleMenuClick.bind(this);
		this.handleMenuClose = this.handleMenuClose.bind(this);

		this.setSize(this.options.size);
		this.setColor(this.options.color);
		this.setIcon(this.options.icon);
		this.setState(this.options.state);
		this.setId(this.options.id);
		this.setMenu(this.options.menu);
		this.setContext(this.options.context);

		this.options.noCaps && this.setNoCaps();
		this.options.round && this.setRound();

		if (this.options.dropdown || (this.getMenuWindow() && this.options.dropdown !== false))
		{
			this.setDropdown();
		}
	}

	static Size = ButtonSize;
	static Color = ButtonColor;
	static State = ButtonState;
	static Icon = ButtonIcon;
	static Tag = ButtonTag;
	static Style = ButtonStyle;

	/**
	 * @public
	 * @param {ButtonSize|null} size
	 * @return {this}
	 */
	setSize(size: ButtonSize | null): this
	{
		return this.setProperty('size', size, ButtonSize);
	}

	/**
	 * @public
	 * @return {?ButtonSize}
	 */
	getSize(): ButtonSize | null
	{
		return this.size;
	}

	/**
	 * @public
	 * @param {ButtonColor|null} color
	 * @return {this}
	 */
	setColor(color: ButtonColor | null): this
	{
		return this.setProperty('color', color, ButtonColor);
	}

	/**
	 * @public
	 * @return {?ButtonSize}
	 */
	getColor(): ButtonSize | null
	{
		return this.color;
	}

	/**
	 * @public
	 * @param {?ButtonIcon} icon
	 * @return {this}
	 */
	setIcon(icon: ButtonIcon | null): this
	{
		this.setProperty('icon', icon, ButtonIcon);

		if (this.isInputType() && this.getIcon() !== null)
		{
			throw new Error('BX.UI.Button: Input type button cannot have an icon.');
		}

		return this;
	}

	/**
	 * @public
	 * @return {?ButtonIcon}
	 */
	getIcon(): ButtonIcon | null
	{
		return this.icon;
	}

	/**
	 * @public
	 * @param {ButtonState|null} state
	 * @return {this}
	 */
	setState(state: ButtonState | null): this
	{
		return this.setProperty('state', state, ButtonState);
	}

	/**
	 * @public
	 * @return {?ButtonState}
	 */
	getState(): ButtonState | null
	{
		return this.state;
	}

	/**
	 * @public
	 * @param {boolean} [flag=true]
	 * @return {this}
	 */
	setNoCaps(flag: ? boolean): this
	{
		if (flag === false)
		{
			Dom.removeClass(this.getContainer(), ButtonStyle.NO_CAPS);
		}
		else
		{
			Dom.addClass(this.getContainer(), ButtonStyle.NO_CAPS);
		}

		return this;
	}

	/**
	 *
	 * @return {boolean}
	 */
	isNoCaps(): boolean
	{
		return Dom.hasClass(this.getContainer(), ButtonStyle.NO_CAPS);
	}

	/**
	 * @public
	 * @param {boolean} [flag=true]
	 * @return {this}
	 */
	setRound(flag: ? boolean): this
	{
		if (flag === false)
		{
			Dom.removeClass(this.getContainer(), ButtonStyle.ROUND);
		}
		else
		{
			Dom.addClass(this.getContainer(), ButtonStyle.ROUND);
		}

		return this;
	}

	/**
	 * @public
	 * @return {boolean}
	 */
	isRound(): boolean
	{
		return Dom.hasClass(this.getContainer(), ButtonStyle.ROUND);
	}

	/**
	 *
	 * @param {boolean} [flag=true]
	 * @return {this}
	 */
	setDropdown(flag: ? boolean): this
	{
		if (flag === false)
		{
			Dom.removeClass(this.getContainer(), ButtonStyle.DROPDOWN);
		}
		else
		{
			Dom.addClass(this.getContainer(), ButtonStyle.DROPDOWN);
		}

		return this;
	}

	/**
	 *
	 * @return {boolean}
	 */
	isDropdown(): boolean
	{
		return Dom.hasClass(this.getContainer(), ButtonStyle.DROPDOWN);
	}

	/**
	 *
	 * @param {boolean} [flag=true]
	 * @return {this}
	 */
	setCollapsed(flag: ? boolean): this
	{
		if (flag === false)
		{
			Dom.removeClass(this.getContainer(), ButtonStyle.COLLAPSED);
		}
		else
		{
			Dom.addClass(this.getContainer(), ButtonStyle.COLLAPSED);
		}

		return this;
	}

	/**
	 *
	 * @return {boolean}
	 */
	isCollapsed(): boolean
	{
		return Dom.hasClass(this.getContainer(), ButtonStyle.COLLAPSED);
	}

	/**
	 * @protected
	 * @param {MenuOptions|false} options
	 */
	setMenu(options: MenuOptions): this
	{
		if (Type.isPlainObject(options) && Type.isArray(options.items) && options.items.length > 0)
		{
			this.setMenu(false);

			this.menuWindow = new Menu({
				id: `ui-btn-menu-${Text.getRandom().toLowerCase()}`,
				bindElement: this.getMenuBindElement(),
				...options
			});

			this.menuWindow.getPopupWindow().subscribe('onClose', this.handleMenuClose);
			Event.bind(this.getMenuClickElement(), 'click', this.handleMenuClick);
		}
		else if (options === false && this.menuWindow !== null)
		{
			this.menuWindow.close();

			this.menuWindow.getPopupWindow().unsubscribe('onClose', this.handleMenuClose);
			Event.unbind(this.getMenuClickElement(), 'click', this.handleMenuClick);

			this.menuWindow.destroy();
			this.menuWindow = null;
		}

		return this;
	}

	/**
	 * @public
	 * @return {HTMLElement}
	 */
	getMenuBindElement(): HTMLElement
	{
		return this.getContainer();
	}

	/**
	 * @public
	 * @return {HTMLElement}
	 */
	getMenuClickElement(): HTMLElement
	{
		return this.getContainer();
	}

	/**
	 * @protected
	 * @param {MouseEvent} event
	 */
	handleMenuClick(event: MouseEvent): void
	{
		this.getMenuWindow().show();
		this.setActive(this.getMenuWindow().getPopupWindow().isShown());
	}

	/**
	 * @protected
	 */
	handleMenuClose(): void
	{
		this.setActive(false);
	}

	/**
	 * @public
	 * @return {Menu}
	 */
	getMenuWindow(): Menu
	{
		return this.menuWindow;
	}

	/**
	 * @public
	 * @param {string|null} id
	 * @return {this}
	 */
	setId(id: string | null): this
	{
		if (Type.isStringFilled(id) || Type.isNull(id))
		{
			this.id = id;
		}

		return this;
	}

	/**
	 * @public
	 * @return {?string}
	 */
	getId(): ?string
	{
		return this.id;
	}

	/**
	 * @public
	 * @param {boolean} [flag=true]
	 * @return {this}
	 */
	setActive(flag?: boolean): this
	{
		return this.setState(flag === false ? null : ButtonState.ACTIVE);
	}

	/**
	 * @public
	 * @return {boolean}
	 */
	isActive(): boolean
	{
		return this.getState() === ButtonState.ACTIVE;
	}

	/**
	 * @public
	 * @param {boolean} [flag=true]
	 * @return {this}
	 */
	setHovered(flag?: boolean): this
	{
		return this.setState(flag === false ? null : ButtonState.HOVER);
	}

	/**
	 * @public
	 * @return {boolean}
	 */
	isHover(): boolean
	{
		return this.getState() === ButtonState.HOVER;
	}

	/**
	 * @public
	 * @param {boolean} [flag=true]
	 * @return {this}
	 */
	setDisabled(flag): this
	{
		this.setState(flag === false ? null : ButtonState.DISABLED);
		super.setDisabled(flag);

		return this;
	}

	/**
	 * @public
	 * @return {boolean}
	 */
	isDisabled(): boolean
	{
		return this.getState() === ButtonState.DISABLED;
	}

	/**
	 * @public
	 * @param {boolean} [flag=true]
	 * @return {this}
	 */
	setWaiting(flag: ? boolean): this
	{
		if (flag === false)
		{
			this.setState(null);
			this.setProps({ disabled: null });
		}
		else
		{
			this.setState(ButtonState.WAITING);
			this.setProps({ disabled: true });
		}

		return this;
	}

	/**
	 * @public
	 * @return {boolean}
	 */
	isWaiting(): boolean
	{
		return this.getState() === ButtonState.WAITING;
	}

	/**
	 * @public
	 * @param {boolean} [flag=true]
	 * @return {this}
	 */
	setClocking(flag?: boolean): this
	{
		if (flag === false)
		{
			this.setState(null);
			this.setProps({ disabled: null });
		}
		else
		{
			this.setState(ButtonState.CLOCKING);
			this.setProps({ disabled: true });
		}

		return this;
	}

	/**
	 * @public
	 * @return {boolean}
	 */
	isClocking(): boolean
	{
		return this.getState() === ButtonState.CLOCKING;
	}

	/**
	 * @protected
	 */
	setProperty(property: string, value?: any, enumeration: Object): this
	{
		if (this.isEnumValue(value, enumeration))
		{
			Dom.removeClass(this.getContainer(), this[property]);
			Dom.addClass(this.getContainer(), value);
			this[property] = value;
		}
		else if (value === null)
		{
			Dom.removeClass(this.getContainer(), this[property]);
			this[property] = null;
		}

		return this;
	}

	/**
	 * @public
	 * @param {*} context
	 */
	setContext(context: any): this
	{
		if (!Type.isUndefined(context))
		{
			this.context = context;
		}

		return this;
	}

	/**
	 *
	 * @return {*}
	 */
	getContext(): any
	{
		return this.context;
	}
}