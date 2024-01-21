import { Dom, Tag, Type } from 'main.core';
import { EventEmitter } from 'main.core.events';
import { Switcher, SwitcherSize } from 'ui.switcher';
import { SingleChecker } from 'ui.form-elements.view';
import { HelpMessage } from 'ui.section';

export class NestedSwitcherItem
{
	#node: HTMLElement;
	#id: string;
	#inputName: string;
	#title: string;
	#switcher: Switcher;
	#field: SingleChecker;
	#isChecked: boolean;
	#settingsPath:? string;
	#settingsTitle:? string;
	#infoHelperCode:? string;
	isDefault: boolean;

	constructor(params)
	{
		this.#id = params.id;
		this.#inputName = params.inputName;
		this.#title = params.title;
		this.#isChecked = params.isChecked;
		this.#settingsPath = Type.isString(params.settingsPath) ? params.settingsPath : null;
		this.#settingsTitle = Type.isString(params.settingsTitle) ? params.settingsTitle : null;
		this.#infoHelperCode = Type.isString(params.infoHelperCode) ? params.infoHelperCode : null;
		this.isDefault = Type.isBoolean(params.isDefault) ? params.isDefault : false;
		this.getSwitcher();
		this.#field = new SingleChecker({
			switcher: this.getSwitcher(),
			inputName: params.inputName,
			isEnable: !this.isDefault,
			helpMessageProvider: this.getHelpMessageProvider(params.id, this.#switcher.getNode(), params.helpMessage),
		});
	}

	getId(): string
	{
		return this.#id;
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
				${this.getLink()}
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

	getField(): SingleChecker
	{
		return this.#field;
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
				'checked' : () => { // There is in error in Switcher UI, so we have inversion in event names.
					Dom.removeClass(this.render(), '--active --checked')
					EventEmitter.emit(this.getSwitcher(), 'inactive');
				},
				'unchecked' : () => {
					Dom.addClass(this.render(), '--active --checked');
					EventEmitter.emit(this.getSwitcher(), 'active');
				},
			}
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

	getLink(): ?HTMLElement
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

	getHelpMessageProvider(id, node, message: HTMLElement): function
	{
		return () => {
			const helpMessagePopup = new HelpMessage(id, node, message);
			helpMessagePopup.getPopup().setOffset({ offsetLeft: 14 });
			return helpMessagePopup;
		};
	}
}
