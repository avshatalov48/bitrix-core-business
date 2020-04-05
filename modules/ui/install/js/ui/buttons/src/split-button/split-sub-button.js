import BaseButton from '../base-button';
import SplitButtonState from './split-button-state';
import SplitSubButtonType from './split-sub-button-type';
import type SplitButton from './split-button';
import { Type } from 'main.core';
import type { SplitSubButtonOptions } from './split-sub-button-options';

/**
 * @namespace {BX.UI}
 */
export default class SplitSubButton extends BaseButton
{
	constructor(options: SplitSubButtonOptions)
	{
		options = Type.isPlainObject(options) ? options : {};
		options.baseClass =
			options.buttonType === SplitSubButtonType.MAIN
				? SplitSubButtonType.MAIN
				: SplitSubButtonType.MENU
		;

		super(options);

		if (this.isInputType())
		{
			throw new Error('BX.UI.SplitSubButton: Split button cannot be an input tag.');
		}
	}

	static Type = SplitSubButtonType;

	init(): void
	{
		this.buttonType = this.options.buttonType;
		this.splitButton = this.options.splitButton;

		super.init();
	}

	/**
	 * @public
	 * @return {SplitButton}
	 */
	getSplitButton(): SplitButton
	{
		return this.splitButton;
	}

	/**
	 * @public
	 * @return {boolean}
	 */
	isMainButton(): boolean
	{
		return this.buttonType === SplitSubButtonType.MAIN;
	}

	/**
	 * @public
	 * @return {boolean}
	 */
	isMenuButton(): boolean
	{
		return this.buttonType === SplitSubButtonType.MENU;
	}

	setText(text: string): this
	{
		if (Type.isString(text) && this.isMenuButton())
		{
			throw new Error('BX.UI.SplitButton: a menu button doesn\'t support a text caption.');
		}

		return super.setText(text);
	}

	/**
	 * @public
	 * @param {boolean} [flag=true]
	 * @return {this}
	 */
	setActive(flag?: boolean): this
	{
		this.toggleState(
			flag,
			SplitButtonState.ACTIVE,
			SplitButtonState.MAIN_ACTIVE,
			SplitButtonState.MENU_ACTIVE
		);

		return this;
	}

	/**
	 * @public
	 * @return {boolean}
	 */
	isActive(): boolean
	{
		const state = this.getSplitButton().getState();
		if (state === SplitButtonState.ACTIVE)
		{
			return true;
		}

		if (this.isMainButton())
		{
			return state === SplitButtonState.MAIN_ACTIVE;
		}

		return state === SplitButtonState.MENU_ACTIVE;
	}

	/**
	 * @public
	 * @param {boolean} [flag=true]
	 * @return {this}
	 */
	setDisabled(flag?: boolean): this
	{
		this.toggleState(
			flag,
			SplitButtonState.DISABLED,
			SplitButtonState.MAIN_DISABLED,
			SplitButtonState.MENU_DISABLED
		);

		super.setDisabled(flag);

		return this;
	}

	/**
	 * @public
	 * @param {boolean} flag
	 * @return {this}
	 */
	setHovered(flag): this
	{
		this.toggleState(
			flag,
			SplitButtonState.HOVER,
			SplitButtonState.MAIN_HOVER,
			SplitButtonState.MENU_HOVER
		);

		return this;
	}

	/**
	 * @public
	 * @return {boolean}
	 */
	isHovered(): boolean
	{
		const state = this.getSplitButton().getState();
		if (state === SplitButtonState.HOVER)
		{
			return true;
		}

		if (this.isMainButton())
		{
			return state === SplitButtonState.MAIN_HOVER;
		}

		return state === SplitButtonState.MENU_HOVER;
	}

	/**
	 * @private
	 * @param flag
	 * @param globalState
	 * @param mainState
	 * @param menuState
	 */
	toggleState(flag, globalState, mainState, menuState)
	{
		const state = this.getSplitButton().getState();
		if (flag === false)
		{
			if (state === globalState)
			{
				this.getSplitButton().setState(this.isMainButton() ? menuState : mainState);
			}
			else
			{
				this.getSplitButton().setState(null);
			}
		}
		else
		{
			if (state === mainState && this.isMenuButton())
			{
				this.getSplitButton().setState(globalState);
			}
			else if (state === menuState && this.isMainButton())
			{
				this.getSplitButton().setState(globalState);
			}
			else if (state !== globalState)
			{
				this.getSplitButton().setState(this.isMainButton() ? mainState : menuState);
			}
		}
	}
}