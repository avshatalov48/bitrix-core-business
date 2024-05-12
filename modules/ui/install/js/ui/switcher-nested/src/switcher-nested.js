import { Dom, Tag, Type } from 'main.core';
import { EventEmitter } from 'main.core.events';
import { Draggable } from 'ui.draganddrop.draggable';
import { SwitcherNestedItem } from './switcher-nested-item';
import { Section } from 'ui.section';
import { Switcher } from 'ui.switcher';
import type { SwitcherNestedOptions } from './types';
import { WarningMessage } from './warning-message';

export class SwitcherNested extends Section
{
	linkTitle: ?HTMLElement;
	link: ?HTMLElement;
	items: Array<SwitcherNestedItem>;
	#mainTool: Switcher;
	infoHelperCode: ?string;
	#sectionWrapper: ?HTMLElement;
	#isDefault: boolean;
	#isDisabled: boolean;
	#warningMessage: ?WarningMessage;
	#helpMessage: ?string;
	#draggable: ?Draggable = null;

	constructor(options: SwitcherNestedOptions)
	{
		super(options);
		this.linkTitle = Type.isString(options.linkTitle) ? options.linkTitle : null;
		this.link = Type.isString(options.link) ? options.link : null;
		this.isChecked = Type.isBoolean(options.isChecked) ? options.isChecked : false;
		this.items = Type.isArray(options.items) ? options.items : [];
		this.isNestedMenu = this.items.length > 0;
		this.infoHelperCode = Type.isString(options.infoHelperCode) ? options.infoHelperCode : null;
		this.#isDefault = Type.isBoolean(options.isDefault) ? options.isDefault : false;
		this.#isDisabled = Type.isBoolean(options.isDisabled) ? options.isDisabled : false;
		this.#helpMessage = Type.isString(options.helpMessage) ? options.helpMessage : null;

		if (options.draggable instanceof Draggable)
		{
			this.#draggable = options.draggable;
		}

		if (!Type.isString(options.mainInputName))
		{
			throw new Error('Missing required parameter');
		}

		this.mainInputName = options.mainInputName;
		this.render();
		this.items.forEach((item) => {
			this.append(item.render());
		});
	}

	getContent(): HTMLElement
	{
		if (this.#sectionWrapper)
		{
			return this.#sectionWrapper;
		}

		this.#sectionWrapper = Tag.render`
			<div class="ui-section__wrapper --tool-selector${this.isChecked ? ' --checked' : ''} ${this.isNestedMenu ? ' clickable' : ''}" >
				<div class="ui-section__header">
					<div class="ui-section__header-left-wrapper">
						${this.#getDraggableIcon() ?? ''}
						<span class="ui-section__switcher-wrapper" onclick="event.stopPropagation()"/>
						<span class="ui-section__title">${this.title}</span>
						${this.#getMenuIcon()}
					</div>
					${this.#getLink()}
				</div>
				<div class="ui-section__content ui-section__section-body_inner">
					<div class="ui-section__row_box"></div>
				</div>
			</div>
		`;

		this.#mainTool = this.#createSwitcher(this.#sectionWrapper.querySelector('.ui-section__switcher-wrapper'));
		if (this.#helpMessage)
		{
			this.#warningMessage = this.getWarningMessage(this.#helpMessage);
		}

		EventEmitter.subscribe(
			this.#mainTool,
			'toggled',
			() => {
				if (this.#isDisabled)
				{
					this.#mainTool.check(!this.#mainTool.isChecked(), false);
					if (this.#warningMessage)
					{
						this.#warningMessage.show();
					}
				}
				else if (this.#isDefault)
				{
					this.#mainTool.check(true, false);
					if (this.#warningMessage)
					{
						this.#warningMessage.show();
					}
				}
				else
				{
					this.toggle(this.#mainTool.isChecked());
					this.#mainTool.inputNode.form.dispatchEvent(new Event('change'));

					Dom[this.#mainTool.isChecked() ? 'addClass' : 'removeClass'](this.#sectionWrapper, '--checked');
					this.items.forEach((item) => item.getSwitcher().check(this.#mainTool.isChecked()));
				}
			},
		);

		this.items.forEach(
			(item) => {

				if (item.isDefault() !== true) // if only this item is not required
				{
					EventEmitter.subscribe(item.getSwitcher(), 'inactive', this.#turnOffDispensableTools.bind(this));
				}

				EventEmitter.subscribe(item.getSwitcher(), 'active', this.#turnOnMainAndRequiredTools.bind(this));
			});

		return this.#sectionWrapper;
	}

	#turnOnMainAndRequiredTools()
	{
		this.#mainTool.inputNode.form.dispatchEvent(new Event('change'));

		if (this.#mainTool.isChecked())
		{
			return;
		}

		this.#mainTool.check(true, false);
		this.toggle(true);
		Dom.addClass(this.#sectionWrapper, '--checked');

		this.items.forEach((item) => {
			return item.isDefault() && !item.getSwitcher().isChecked() ? item.getSwitcher().check(true) : null;
		});
	}

	#turnOffDispensableTools()
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
		Dom.removeClass(this.#sectionWrapper, '--checked');
	}

	#getMenuIcon(): HTMLElement
	{
		if (this.isNestedMenu)
		{
			return Tag.render`
				<span class="ui-section__collapse-icon ui-icon-set ${this.isOpen ? this.className.arrowTop : this.className.arrowDown} --tool-selector-icon"></span>
			`;
		}

		return null;
	}

	#getDraggableIcon(): ?HTMLElement
	{
		if (this.#draggable)
		{
			return Tag.render`
				<div onclick="event.stopPropagation()" class="ui-section__dragdrop-icon-wrapper">
					<div onclick="event.stopPropagation()" class="ui-section__dragdrop-icon"/>
				</div>
			`;
		}

		return null;
	}

	#getLink(): ?HTMLElement
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

	#createSwitcher(node: HTMLElement): Switcher
	{
		return new Switcher({
			inputName: this.mainInputName,
			node: node,
			checked: this.isChecked,
			id: this.id
		});
	}

	getWarningMessage(message: HTMLElement): WarningMessage
	{
		if (this.#warningMessage)
		{
			return this.#warningMessage;
		}

		this.#warningMessage = new WarningMessage({
			id: this.id,
			bindElement: this.#mainTool.getNode(),
			message: message,
		});

		return this.#warningMessage;
	}

	isDefault(): boolean
	{
		return this.#isDefault;
	}
}