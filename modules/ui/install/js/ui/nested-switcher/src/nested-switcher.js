import { Dom, Tag, Type } from 'main.core';
import { EventEmitter } from 'main.core.events';
import { NestedSwitcherItem } from './nested-switcher-item';
import { SingleChecker } from 'ui.form-elements.view';
import { Section } from 'ui.section';
import { Switcher } from 'ui.switcher';

export type NestedSwitcherOptions = {
	linkTitle?: string,
	link?: string,
	isChecked?: boolean,
	mainInputName: string,
	items?: Array<NestedSwitcherItem>,
	infoHelperCode?: string,
}

export class NestedSwitcher extends Section
{
	linkTitle: ?HTMLElement;
	link: ?HTMLElement;
	items: Array<NestedSwitcherItem>;
	#mainTool: Switcher;
	field: SingleChecker;
	infoHelperCode: ?string;
	#sectionWrapper: ?HTMLElement;

	constructor(options: NestedSwitcherOptions)
	{
		super(options);
		this.linkTitle = Type.isString(options.linkTitle) ? options.linkTitle : null;
		this.link = Type.isString(options.link) ? options.link : null;
		this.isChecked = Type.isBoolean(options.isChecked) ? options.isChecked : false;
		this.items = Type.isArray(options.items) ? options.items : [];
		this.isNestedMenu = this.items.length > 0;
		this.infoHelperCode = Type.isString(options.infoHelperCode) ? options.infoHelperCode : null;

		if (!Type.isString(options.mainInputName))
		{
			throw new Error('Missing required parameter');
		}

		this.mainInputName = options.mainInputName;
		this.render();
		this.items.forEach((item) => {
			this.append(item.render());
		});
		this.field = new SingleChecker({switcher: this.#mainTool});
	}

	getContent(): HTMLElement
	{
		if (this.#sectionWrapper)
		{
			return this.#sectionWrapper;
		}

		this.#sectionWrapper = Tag.render`
			<div id="${this.id}" class="ui-section__wrapper --tool-selector${this.isChecked ? ' --checked' : ''} ${this.canCollapse ? ' clickable' : ''}" >
				<div class="ui-section__header">
					<div class="ui-section__header-left-wrapper">
						<span class="ui-section__switcher-wrapper" onclick="event.stopPropagation()"/>
						<span class="ui-section__title">${this.title}</span>
						${this.getMenuIcon()}
					</div>
					${this.getLink()}
				</div>
				<div class="ui-section__content ui-section__section-body_inner">
					<div class="ui-section__row_box"></div>
				</div>
			</div>
		`;

		this.#mainTool = this.createSwitcher(this.#sectionWrapper.querySelector('.ui-section__switcher-wrapper'));

		EventEmitter.subscribe(
			this.#mainTool,
			'toggled',
			() =>
			{
				this.toggle(this.#mainTool.isChecked());
				this.#mainTool.inputNode.form.dispatchEvent(new Event('change'));

				Dom[this.#mainTool.isChecked() ? 'addClass' : 'removeClass'](this.#sectionWrapper, '--checked');
				this.items.forEach((item) => item.getSwitcher().check(this.#mainTool.isChecked()));
			}
		);

		this.items.forEach(
			(item) => {

				if (item.isDefault !== true) // if only this item is not required
				{
					EventEmitter.subscribe(item.getSwitcher(), 'inactive', this.#turnOffUnrequiredTools.bind(this));
				}

				EventEmitter.subscribe(item.getSwitcher(), 'active', this.#turnOnMainAndRequiredTools.bind(this));
			});

		return this.#sectionWrapper;
	}

	#turnOnMainAndRequiredTools(item)
	{
		this.#mainTool.inputNode.form.dispatchEvent(new Event('change'));
		if (this.#mainTool.isChecked())
		{
			return;
		}

		this.#mainTool.check(true, false);
		this.toggle(true);
		Dom.addClass(this.#sectionWrapper, '--checked');

		this.items.forEach((item) => item.isDefault && !item.getSwitcher().isChecked() ? item.getSwitcher().check(true) : null);
	}

	#turnOffUnrequiredTools({target})
	{
		this.#mainTool.inputNode.form.dispatchEvent(new Event('change'));
		if (this.#mainTool.isChecked() !== true)
		{
			return;
		}
		if (this.items.some((item) => item.getSwitcher().isChecked()))
		{
			return;
		}
		this.#mainTool.check(false, false);
	}

	getMenuIcon(): HTMLElement
	{
		if (this.isNestedMenu)
		{
			return Tag.render`
				<span class="ui-section__collapse-icon ui-icon-set ${this.isOpen ? this.className.arrowTop : this.className.arrowDown} --tool-selector-icon"></span>
			`;
		}

		return null;
	}

	getFields(): Array<SingleChecker>
	{
		let result = [];
		result.push(this.field);
		this.items.forEach((item) => {
			result.push(item.getField());
		});

		return result;
	}

	getLink(): ?HTMLElement
	{
		if (Type.isNil(this.linkTitle))
		{
			return null;
		}

		if (!Type.isNil(this.link))
		{
			return Tag.render`
				<a target="_blank" href="${this.link}" class="ui-section__header-link ui-section__tools-description-link" onclick="event.stopPropagation()">${this.linkTitle}</a>
			`;
		}

		if (!Type.isNil(this.infoHelperCode))
		{
			return Tag.render`
				<a href="javascript:top.BX.UI.InfoHelper.show('${this.infoHelperCode}')" class="ui-section__header-link ui-section__tools-description-link" onclick="event.stopPropagation()">${this.linkTitle}</a>
			`;
		}

		return null;
	}

	createSwitcher(node: HTMLElement): Switcher
	{
		return new Switcher({
			inputName: this.mainInputName,
			node: node,
			checked: this.isChecked,
			id: this.id,
		});
	}
}