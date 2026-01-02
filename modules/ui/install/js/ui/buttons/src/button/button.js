import { Type, Dom, Text, Event, Tag } from 'main.core';
import { Menu, type MenuOptions } from 'main.popup';

import { SplitButton, type ButtonCounterOptions } from 'ui.buttons';
import { Icon } from 'ui.icon-set.api.core';
import 'ui.cnt';

import BaseButton from '../base-button';
import ButtonColor from './button-color';
import ButtonSize from './button-size';
import ButtonIcon from './button-icon';
import ButtonState from './button-state';
import ButtonStyle from './button-style';
import ButtonTag from './button-tag';
import AirButtonStyle from './air-button-style';
import type { ButtonOptions } from './button-options';

/**
 * @namespace {BX.UI}
 */
export default class Button extends BaseButton
{
	static BASE_CLASS = 'ui-btn';

	#style: ?string;
	#isWide: boolean = false;
	#layout: {
		icon: HTMLElement,
	} = {};

	constructor(options: ButtonOptions)
	{
		super({
			dependOnTheme: options.className?.includes(ButtonStyle.DEPEND_ON_THEME),
			...(Type.isPlainObject(options) ? options : {}),
			baseClass: Type.isStringFilled(options?.baseClass) ? options.baseClass : Button.BASE_CLASS,
		});

		this.isDependOnTheme = null;
		this.size = null;
		this.color = null;
		this.icon = null;
		this.state = null;
		this.id = null;
		this.context = null;

		this.menuWindow = null;
		this.handleMenuClick = this.handleMenuClick.bind(this);
		this.handleMenuClose = this.handleMenuClose.bind(this);

		this.setDependOnTheme(this.options.dependOnTheme ?? false);
		this.setSize(this.options.size);
		this.setColor(this.options.color);
		this.setIcon(this.options.icon, this.options.iconPosition || 'left');
		this.setState(this.options.state);
		this.setId(this.options.id);
		this.setMenu(this.options.menu);
		this.setContext(this.options.context);
		this.setWide(this.options.wide === true);
		this.setLeftCorners(this.options.removeLeftCorners !== true);
		this.setRightCorners(this.options.removeRightCorners !== true);
		if (this.options.collapsedIcon)
		{
			this.setCollapsedIcon(this.options.collapsedIcon);
		}

		if (this.hasAirDesign())
		{
			this.setStyle(this.options.style || AirButtonStyle.FILLED);
			this.setNoCaps(true);

			if (!this.text && !(this instanceof SplitButton))
			{
				this.setCollapsed(true);
			}
		}

		if (this.options.noCaps)
		{
			this.setNoCaps();
		}

		if (this.options.round)
		{
			this.setRound();
		}

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
	static AirStyle = AirButtonStyle;

	setText(text: string): this
	{
		super.setText(text);

		if (this.hasAirDesign())
		{
			Dom.toggleClass(this.getContainer(), ButtonStyle.COLLAPSED, !this.text);
		}

		return this;
	}

	setSize(size: ButtonSize | null): this
	{
		return this.setProperty('size', size, ButtonSize);
	}

	getSize(): ButtonSize | null
	{
		return this.size;
	}

	setColor(color: ButtonColor | null): this
	{
		return this.setProperty('color', color, ButtonColor);
	}

	getColor(): ButtonColor | null
	{
		return this.color;
	}

	setIcon(icon: ?string, iconPosition: 'right' | 'left' = 'left'): this
	{
		if (icon && !icon.startsWith('ui-btn-icon'))
		{
			this.#layout.icon?.remove();
			this.#layout.icon = new Icon({ icon }).render();
			Dom.addClass(this.getContainer(), '--with-icon');
			Dom.prepend(this.#layout.icon, this.getContainer());

			return this;
		}

		this.setProperty('icon', icon, ButtonIcon);

		const iconClass = {
			left: '--with-left-icon',
			right: '--with-right-icon',
		}[iconPosition] ?? '';

		Dom.removeClass(this.getContainer(), '--with-icon');
		Dom.toggleClass(this.getContainer(), ['ui-icon-set__scope', iconClass], Boolean(icon));

		if (this.isInputType() && this.getIcon() !== null)
		{
			throw new Error('BX.UI.Button: Input type button cannot have an icon.');
		}

		return this;
	}

	setCollapsedIcon(icon: ButtonIcon | null): this
	{
		this.setProperty('icon', icon, ButtonIcon);

		Dom.toggleClass(this.getContainer(), ['ui-icon-set__scope', '--with-collapsed-icon'], Boolean(icon));
	}

	getIcon(): ButtonIcon | null
	{
		return this.icon;
	}

	setState(state: ButtonState | null): this
	{
		return this.setProperty('state', state, ButtonState);
	}

	getState(): ButtonState | null
	{
		return this.state;
	}

	setNoCaps(noCaps: boolean = true): this
	{
		Dom.toggleClass(this.getContainer(), ButtonStyle.NO_CAPS, noCaps);

		return this;
	}

	isNoCaps(): boolean
	{
		return Dom.hasClass(this.getContainer(), ButtonStyle.NO_CAPS);
	}

	setRound(round: boolean = true): this
	{
		Dom.toggleClass(this.getContainer(), ButtonStyle.ROUND, round);

		return this;
	}

	isRound(): boolean
	{
		return Dom.hasClass(this.getContainer(), ButtonStyle.ROUND);
	}

	setDependOnTheme(dependOnTheme: boolean = true): this
	{
		Dom.toggleClass(this.getContainer(), ButtonStyle.DEPEND_ON_THEME, dependOnTheme);

		return this;
	}

	setDropdown(dropdown: boolean = true): this
	{
		Dom.toggleClass(this.getContainer(), ButtonStyle.DROPDOWN, dropdown);

		return this;
	}

	isDropdown(): boolean
	{
		return Dom.hasClass(this.getContainer(), ButtonStyle.DROPDOWN);
	}

	setCollapsed(collapsed: boolean = true): this
	{
		const isAirWithoutText = this.hasAirDesign() && !this.getText();
		Dom.toggleClass(this.getContainer(), ButtonStyle.COLLAPSED, collapsed || isAirWithoutText);

		return this;
	}

	isCollapsed(): boolean
	{
		return Dom.hasClass(this.getContainer(), ButtonStyle.COLLAPSED);
	}

	// works only with air buttons
	setLeftCorners(withLeftCorners: boolean = true): this
	{
		Dom.toggleClass(this.getContainer(), '--remove-left-corners', !withLeftCorners);

		return this;
	}

	// works only with air buttons
	setRightCorners(withRightCorners: boolean = true): this
	{
		Dom.toggleClass(this.getContainer(), '--remove-right-corners', !withRightCorners);

		return this;
	}

	/**
	 * @protected
	 */
	setMenu(options: MenuOptions): this
	{
		if (Type.isPlainObject(options) && Type.isArray(options.items) && options.items.length > 0)
		{
			this.setMenu(false);

			this.menuWindow = new Menu({
				id: `ui-btn-menu-${Text.getRandom().toLowerCase()}`,
				bindElement: this.getMenuBindElement(),
				...options,
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

	getMenuBindElement(): HTMLElement
	{
		return this.getContainer();
	}

	getMenuClickElement(): HTMLElement
	{
		return this.getContainer();
	}

	/**
	 * @protected
	 */
	handleMenuClick(event: MouseEvent): void
	{
		this.getMenuWindow().show();
		this.setActive(this.getMenuWindow().getPopupWindow().isShown());
	}

	setAirDesign(use: boolean) {
		super.setAirDesign(use);

		const isButtonInOldFormat = !this.getContainer()?.querySelector('.ui-btn-text-inner');

		if (this.hasAirDesign() && isButtonInOldFormat)
		{
			Dom.remove(this.textNode);
			this.textNode = null;
			this.setText(this.getText());
		}
	}

	/**
	 * @protected
	 */
	handleMenuClose(): void
	{
		this.setActive(false);
	}

	getMenuWindow(): Menu
	{
		return this.menuWindow;
	}

	setId(id: string | null): this
	{
		if (Type.isStringFilled(id) || Type.isNull(id))
		{
			this.id = id;
		}

		return this;
	}

	getId(): ?string
	{
		return this.id;
	}

	setActive(active: boolean = true): this
	{
		return this.setState(active ? ButtonState.ACTIVE : null);
	}

	isActive(): boolean
	{
		return this.getState() === ButtonState.ACTIVE;
	}

	setHovered(hovered: boolean = true): this
	{
		return this.setState(hovered ? ButtonState.HOVER : null);
	}

	isHover(): boolean
	{
		return this.getState() === ButtonState.HOVER;
	}

	setDisabled(disabled: boolean = true): this
	{
		this.setState(disabled ? ButtonState.DISABLED : null);
		super.setDisabled(disabled);

		return this;
	}

	isDisabled(): boolean
	{
		return this.getState() === ButtonState.DISABLED;
	}

	setWaiting(waiting: boolean = true): this
	{
		this.setState(waiting ? ButtonState.WAITING : null);
		this.setProps({ disabled: waiting ? true : null });

		return this;
	}

	isWaiting(): boolean
	{
		return this.getState() === ButtonState.WAITING;
	}

	setClocking(clocking: boolean = true): this
	{
		this.setState(clocking ? ButtonState.CLOCKING : null);
		this.setProps({ disabled: clocking ? true : null });

		return this;
	}

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

	setContext(context: any): this
	{
		if (!Type.isUndefined(context))
		{
			this.context = context;
		}

		return this;
	}

	getContext(): any
	{
		return this.context;
	}

	setWide(isWide: boolean): this
	{
		this.#isWide = isWide === true;

		Dom.toggleClass(this.getContainer(), '--wide', this.#isWide);

		return this;
	}

	isWide(): boolean
	{
		return this.#isWide;
	}

	// This method works only with useAirDesign: true option
	setStyle(style: string): void
	{
		if (this.hasAirDesign() === false)
		{
			console.warn('Style option works only with air buttons.');

			return;
		}

		if (Object.values(AirButtonStyle).includes(style) === false)
		{
			console.warn('Undefined style option. Use value from AirButtonStyle');

			return;
		}

		Dom.removeClass(this.getContainer(), this.#style);
		Dom.addClass(this.getContainer(), style);

		this.#style = style;
	}

	getStyle(): string
	{
		return this.#style;
	}

	setLeftCounter(options: ButtonCounterOptions | null): this
	{
		super.setLeftCounter(this.prepareCounterOptions(options));

		return this;
	}

	setRightCounter(options: ButtonCounterOptions | null): this
	{
		super.setRightCounter(this.prepareCounterOptions(options));

		return this;
	}

	/**
	 * @protected
	 */
	prepareCounterOptions(options: ButtonCounterOptions | null): ButtonCounterOptions | null
	{
		if (!options)
		{
			return null;
		}

		return {
			...options,
			...(this.getSize() ? { size: this.getSize() } : {}),
		};
	}

	startShimmer(): void
	{
		const highlighter = Tag.render`<span class="ui-button__shimmer"></span>`;

		Dom.append(highlighter, this.getContainer());
	}

	stopShimmer(): void
	{
		const highlighter = this.getContainer().querySelector('.ui-button__shimmer');

		Dom.remove(highlighter);
	}
}
