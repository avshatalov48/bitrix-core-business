import { Dom, Event, Tag, Type } from 'main.core';
import { EventEmitter } from 'main.core.events';
import { Switcher, SwitcherSize } from 'ui.switcher';
import type { SwitcherNestedItemOptions } from './types';
import { WarningMessage } from './warning-message';

export class SwitcherNestedItem
{
	#node: HTMLElement;
	#id: string;
	#inputName: string;
	#title: string;
	#switcher: Switcher;
	#isChecked: boolean;
	#settingsPath:? string;
	#settingsTitle:? string;
	#infoHelperCode:? string;
	#warning: WarningMessage;
	#warningMessage: HTMLElement;
	#isDefault: boolean;
	#isDisabled: boolean;

	constructor(options: SwitcherNestedItemOptions)
	{
		this.#id = options.id;
		this.#inputName = options.inputName;
		this.#title = options.title;
		this.#isChecked = options.isChecked;
		this.#settingsPath = Type.isString(options.settingsPath) ? options.settingsPath : null;
		this.#settingsTitle = Type.isString(options.settingsTitle) ? options.settingsTitle : null;
		this.#infoHelperCode = Type.isString(options.infoHelperCode) ? options.infoHelperCode : null;
		this.#isDefault = Type.isBoolean(options.isDefault) ? options.isDefault : false;
		this.#isDisabled = Type.isBoolean(options.isDisabled) ? options.isDisabled : false;
		this.#warningMessage = options.helpMessage;

		Event.bind(
			this.getSwitcher().getNode(),
			'click',
			() => {
				if (this.#isDisabled)
				{
					this.getWarningMessage().show();
					this.getSwitcher().check(this.#isChecked, false);
				}
				else if (this.#isDefault && !this.getSwitcher().isChecked())
				{
					this.getSwitcher().check(true, false);
					this.getWarningMessage().show();
				}
			},
		);
	}

	getId(): string
	{
		return this.#id;
	}

	isDefault(): boolean
	{
		return this.#isDefault;
	}

	render(): HTMLElement
	{
		if (this.#node)
		{
			return this.#node;
		}

		this.#node = Tag.render`
			<div class="ui-section__row-tool-selector --tool-selector${this.#isChecked ? ' --active --checked' : ''}">
				<div class="ui-section__tools-subgroup_left-wrapper">
					<div class="ui-section__switcher-row_wrapper"/>
					<div class="ui-section__row-tool-selector_title">${this.#title}</div>
				</div>
				${this.#getLink()}
			</div>
		`;

		return this.#node;
	}

	getSwitcher(): Switcher
	{
		if (this.#switcher instanceof Switcher)
		{
			return this.#switcher;
		}

		this.#switcher = this.createSwitcher(this.render().querySelector('.ui-section__switcher-row_wrapper'));

		return this.#switcher;
	}

	createSwitcher(node: HTMLElement): Switcher
	{
		return new Switcher({
			inputName: this.#inputName,
			node: node,
			checked: this.#isChecked,
			id: this.#id,
			size: SwitcherSize.extraSmall,
			handlers: {
				checked: () => { // There is in error in Switcher UI, so we have inversion in event names.
					if (!this.#isDisabled && !this.#isDefault)
					{
						Dom.removeClass(this.render(), '--active --checked');
						EventEmitter.emit(this.getSwitcher(), 'inactive');
					}
				},
				unchecked: () => {
					if (!this.#isDisabled)
					{
						Dom.addClass(this.render(), '--active --checked');
						EventEmitter.emit(this.getSwitcher(), 'active');
					}
				},
			},
		});
	}

	renderTo(targetNode: HTMLElement): HTMLElement
	{
		if (!Type.isDomNode(targetNode))
		{
			throw new Error('Target node must be HTMLElement');
		}

		return Dom.append(this.render(), targetNode);
	}

	#getLink(): ?HTMLElement
	{
		if (Type.isNil(this.#settingsTitle))
		{
			return null;
		}

		if (!Type.isNil(this.#settingsPath))
		{
			return Tag.render`
				<a target="_blank" data-slider-ignore-autobinding="true" href="${this.#settingsPath}" class="ui-section__tools-subgroup-description-link">${this.#settingsTitle}</a>
			`;
		}

		if (!Type.isNil(this.#infoHelperCode))
		{
			return Tag.render`
				<a href="javascript:top.BX.UI.InfoHelper.show('${this.#infoHelperCode}')" class="ui-section__tools-subgroup-description-link">${this.#settingsTitle}</a>
			`;
		}

		return null;
	}

	getWarningMessage(): WarningMessage
	{
		if (this.#warning)
		{
			return this.#warning;
		}

		this.#warning = new WarningMessage({
			id: this.getId(),
			bindElement: this.getSwitcher().getNode(),
			message: this.#warningMessage,
		});

		return this.#warning;
	}
}
