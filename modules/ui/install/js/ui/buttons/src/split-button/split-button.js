import { Type, Tag } from 'main.core';
import { ButtonCounterOptions } from 'ui.buttons';
import type { Switcher } from 'ui.switcher';

import Button from '../button/button';
import SplitSubButton from './split-sub-button';
import SplitButtonState from './split-button-state';
import ButtonState from '../button/button-state';
import ButtonSize from '../button/button-size';
import SplitSubButtonType from './split-sub-button-type';
import ButtonTag from '../button/button-tag';
import { getSwitcherSizeByButtonSize } from './helpers/get-switcher-size';
import type { SplitButtonOptions } from './split-button-options';

/**
 * @namespace {BX.UI}
 */
export default class SplitButton extends Button
{
	static BASE_CLASS = 'ui-btn-split';

	constructor(options: SplitButtonOptions)
	{
		options = Type.isPlainObject(options) ? options : {};

		if (Type.isStringFilled(options.link))
		{
			options.mainButton = Type.isPlainObject(options.mainButton) ? options.mainButton : {};
			options.mainButton.link = options.link;
			delete options.link;
		}

		options.tag = ButtonTag.DIV;
		options.baseClass = SplitButton.BASE_CLASS;

		super(options);
	}

	init(): void
	{
		const mainOptions = Type.isPlainObject(this.options.mainButton) ? this.options.mainButton : {};
		const menuOptions = Type.isPlainObject(this.options.menuButton) ? this.options.menuButton : {};
		mainOptions.buttonType = SplitSubButtonType.MAIN;
		mainOptions.splitButton = this;

		menuOptions.buttonType = SplitSubButtonType.MENU;
		menuOptions.splitButton = this;

		this.mainButton = new SplitSubButton({
			...mainOptions,
			useAirDesign: this.options.useAirDesign,
			style: this.options.style,
		});

		this.menuButton = new SplitSubButton(menuOptions);
		this.menuTarget = SplitSubButtonType.MAIN;

		if (this.options.menuTarget === SplitSubButtonType.MENU)
		{
			this.menuTarget = SplitSubButtonType.MENU;
		}

		if (Type.isPlainObject(this.options.switcher) || this.options.switcher === true)
		{
			const addSwitcherOptions = Type.isPlainObject(this.options.switcher) ? this.options.switcher : {};
			const buttonSize = Type.isStringFilled(this.options.size) ? this.options.size : ButtonSize.MEDIUM;

			this.switcherButton = new SplitSubButton({
				buttonType: SplitSubButtonType.SWITCHER,
				splitButton: this,
				switcherOptions: {
					...addSwitcherOptions,
					disabled: this.options.disabled,
					size: getSwitcherSizeByButtonSize(buttonSize),
					useAirDesign: this.options.useAirDesign === true,
				},
			});
		}

		super.init();
	}

	static State = SplitButtonState;

	getContainer(): HTMLElement
	{
		this.button ??= Tag.render`
			<div class="${this.getBaseClass()}">
				${this.getMainButton().getContainer()}
				${(this.getSwitcherButton() ?? this.getMenuButton()).getContainer()}
			</div>
		`;

		return this.button;
	}

	getMainButton(): SplitSubButton
	{
		return this.mainButton;
	}

	getMenuButton(): SplitSubButton
	{
		return this.menuButton;
	}

	getSwitcherButton(): ?SplitSubButton
	{
		return this.switcherButton;
	}

	getSwitcher(): ?Switcher
	{
		return this.getSwitcherButton()?.getSwitcher();
	}

	setAirDesign(use: boolean): void
	{
		super.setAirDesign(use);

		this.getSwitcher()?.setAirDesign(use);
	}

	setText(text: string): this
	{
		if (Type.isString(text))
		{
			this.getMainButton().setText(text);
		}

		return this;
	}

	getText(): string
	{
		return this.getMainButton().getText();
	}

	setCounter(counter: number | string): this
	{
		return this.getMainButton().setCounter(counter);
	}

	// use only with air buttons
	setLeftCounter(options: ButtonCounterOptions | null): this
	{
		this.getMainButton().setLeftCounter(this.prepareCounterOptions(options));

		return this;
	}

	// use only with air buttons
	setRightCounter(options: ButtonCounterOptions | null): this
	{
		this.getMainButton().setRightCounter(this.prepareCounterOptions(options));

		return this;
	}

	getCounter(): number | string | null
	{
		return this.getMainButton().getCounter();
	}

	setLink(link: string): this
	{
		return this.getMainButton().setLink(link);
	}

	getLink(): string
	{
		return this.getMainButton().getLink();
	}

	setState(state: SplitButtonState | null): this
	{
		return this.setProperty('state', state, SplitButtonState);
	}

	setDisabled(disabled: boolean = true): this
	{
		this.setState(disabled ? ButtonState.DISABLED : null);
		this.getMainButton().setDisabled(disabled);
		this.getMenuButton()?.setDisabled(disabled);
		this.getSwitcherButton()?.setDisabled(disabled);

		return this;
	}

	/**
	 * @protected
	 */
	getMenuBindElement(): HTMLElement
	{
		if (this.getMenuTarget() === SplitSubButtonType.MENU)
		{
			return this.getMenuButton().getContainer();
		}

		return this.getContainer();
	}

	/**
	 * @protected
	 */
	handleMenuClick(event: MouseEvent): void
	{
		this.getMenuWindow().show();

		const isActive = this.getMenuWindow().getPopupWindow().isShown();
		this.getMenuButton().setActive(isActive);
	}

	/**
	 * @protected
	 */
	handleMenuClose(): void
	{
		this.getMenuButton().setActive(false);
	}

	/**
	 * @protected
	 */
	getMenuClickElement(): HTMLElement
	{
		return this.getMenuButton().getContainer();
	}

	getMenuTarget(): SplitSubButtonType
	{
		return this.menuTarget;
	}

	setDropdown(dropdown: boolean = true): this
	{
		return this;
	}

	isDropdown(): boolean
	{
		return true;
	}
}
