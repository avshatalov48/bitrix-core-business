import {Event, Tag, Text, Type, Loc, Dom, Runtime} from 'main.core';
import type {FieldScheme} from "../types/field-scheme";
import {Element} from "../fields-group/element";
import type {ValueScheme} from "../types/value-scheme";

export class SeoInput
{
	input: HTMLElement;
	hintValue: string;
	hintWrapper: HTMLElement;
	inputWrapper: HTMLElement;
	transliterateWrapper: HTMLElement;

	constructor(setting: FieldScheme = {}, section: Element)
	{
		this.id = Text.encode(setting.ID);
		this.title = Text.encode(setting.TITLE);
		this.section = section;
		this.handleInput = Runtime.debounce(this.onInput, 500, this);
	}

	layout(): HTMLElement
	{
		this.input = Tag.render`
			<input class='ui-ctl-element' name="${this.id}" value="${Text.encode(this.#getTemplate())}">
		`;

		if (!this.#isReadOnly())
		{
			Event.bind(this.input, 'keydown', (event: Event) => {
				this.section.getForm().hideInputMenu();
			});
			Event.bind(this.input, 'input', this.handleInput);
			Event.bind(this.input, 'click', this.toggleTemplatesMenu.bind(this));
		}

		if (!this.hintWrapper)
		{
			this.hintWrapper = Tag.render`<div class='ui-ctl-label-text catalog-seo-detail-input-hint'>${this.#getHint()}</div>`;
		}

		let menuButton = null;
		if (!this.#isReadOnly())
		{
			menuButton = Tag.render`<div class="ui-ctl-after ui-ctl-icon-angle" data-id=""></div>`;
		}

		this.inputWrapper = Tag.render`
			<div class="ui-ctl ui-ctl-textbox ui-ctl-after-icon ui-ctl-w100">
				${menuButton}
				${this.input}
			</div>
		`;

		let inheritCheckbox = null;
		let checkboxWrapper = null;
		if (this.#isExistedInheritedCheckbox())
		{
			inheritCheckbox = Tag.render`<input type="checkbox" class="ui-ctl-element">`;
			Event.bind(inheritCheckbox, 'change', this.#toggleInherited.bind(this));

			checkboxWrapper = Tag.render`
				<label class="ui-ctl ui-ctl-checkbox ui-ctl-w100">
					${inheritCheckbox}
					<div class="ui-ctl-label-text">${this.section.getInheritedLabel()}</div>
				</label>
			`;
		}

		if (inheritCheckbox && !this.#isInherited())
		{
			inheritCheckbox.checked = true;
		}
		else if (!this.section.getForm().isCatalogMode() || this.#isReadOnly())
		{
			Dom.addClass(this.inputWrapper, 'ui-ctl-disabled');
			this.input.disabled = true;
		}

		let lowercaseCheckboxWrapper = null;
		let transliterateCheckboxWrapper = null;
		this.transliterateWrapper = null;
		if (this.#isExistedAttributes())
		{
			const lowercaseCheckbox = Tag.render`<input type="checkbox" class="ui-ctl-element">`;
			Event.bind(lowercaseCheckbox, 'change', this.#toggleLowercase.bind(this));
			lowercaseCheckboxWrapper = Tag.render`
				<label class="ui-ctl ui-ctl-checkbox ui-ctl-w100">
					${lowercaseCheckbox}
					<div class="ui-ctl-label-text">${Loc.getMessage('CSD_LOWERCASE_CHECKBOX_INPUT_TITLE')}</div>
				</label>
			`;
			if (this.#isLowerCase())
			{
				lowercaseCheckbox.checked = true;
			}

			const transliterateCheckbox = Tag.render`<input type="checkbox" class="ui-ctl-element">`;
			Event.bind(transliterateCheckbox, 'change', this.#toggleTransliterate.bind(this));
			transliterateCheckboxWrapper = Tag.render`
				<label class="ui-ctl ui-ctl-checkbox ui-ctl-w100">
					${transliterateCheckbox}
					<div class="ui-ctl-label-text">${Loc.getMessage('CSD_TRANSLITERATE_CHECKBOX_INPUT_TITLE')}</div>
				</label>
			`;
			if (this.#isTransliterated())
			{
				transliterateCheckbox.checked = true;
			}

			const whitespaceInput = Tag.render`
				<input 
					class="ui-ctl-element ui-text-center" 
					size="1" maxlength="1" 
					value="${this.#getWhitespace()}"
				>
			`;
			Event.bind(whitespaceInput, 'input', this.#inputWhitespaceChar.bind(this));

			this.transliterateWrapper = Tag.render`
				<div class="ui-ctl ui-ctl-checkbox ui-ctl-w100">
					${whitespaceInput}
					<div class="ui-ctl-label-text">${Loc.getMessage('CSD_WHITESPACE_CHARACTER_INPUT_TITLE')}</div>
				</div>
			`;

			if (!this.#isTransliterated())
			{
				Dom.addClass(this.transliterateWrapper, 'ui-form-row-hidden');
			}
		}

		return Tag.render`
			<div class="ui-form-row">
				<div class='ui-form-label'>
					<div class="ui-ctl-label-text">${this.title}</div>
				</div>
				<div class="ui-form-content">
					${this.inputWrapper}
					${checkboxWrapper}					
					${lowercaseCheckboxWrapper}
					${transliterateCheckboxWrapper}
					${this.transliterateWrapper}
					${this.hintWrapper}
				</div>
			</div>
		`;
	}

	#getTemplate(): string
	{
		const value = this.section.getForm().getValue(this.id);

		return Type.isStringFilled(value?.template) ? value.template : '';
	}

	#getHint(): string
	{
		const value = this.section.getForm().getValue(this.id);

		return Type.isStringFilled(value?.hint) ? value.hint : '';
	}

	#isInherited(): boolean
	{
		const value = this.section.getForm().getValue(this.id);

		return value?.inherited !== 'N';
	}

	#isTransliterated(): boolean
	{
		const value = this.section.getForm().getValue(this.id);

		return value?.transliterate === 'Y';
	}

	#isLowerCase(): boolean
	{
		const value = this.section.getForm().getValue(this.id);

		return value?.lowercase === 'Y';
	}

	#getWhitespace(): boolean
	{
		const value = this.section.getForm().getValue(this.id);

		return Type.isStringFilled(value?.whitespaceCharacter) ? value.whitespaceCharacter : '';
	}

	#toggleLowercase(event: Event): boolean
	{
		const value = this.section.getForm().getValue(this.id);
		value.lowercase = event.target.checked ? 'Y' : 'N';

		if (Type.isStringFilled(value.template))
		{
			this.refreshHint(value);
		}

		return this;
	}

	#toggleTransliterate(event: Event): SeoInput
	{
		const checkboxValue = event.target.checked;
		if (checkboxValue)
		{
			Dom.removeClass(this.transliterateWrapper, 'ui-form-row-hidden');
		}
		else
		{
			Dom.addClass(this.transliterateWrapper, 'ui-form-row-hidden');
		}

		const value = this.section.getForm().getValue(this.id);
		value.transliterate = checkboxValue ? 'Y' : 'N';

		if (Type.isStringFilled(value.template))
		{
			this.refreshHint(value);
		}

		return this;
	}

	#inputWhitespaceChar(event: Event): boolean
	{
		const value = this.section.getForm().getValue(this.id);
		value.whitespaceCharacter = event.target.value.slice(0, 1);
		if (Type.isStringFilled(value.template) && this.#isTransliterated())
		{
			this.refreshHint(value);
		}

		return value?.transliterate === 'Y';
	}

	#isExistedAttributes(): boolean
	{
		const value = this.section.getForm().getValue(this.id);

		return !this.#isReadOnly() && value?.isExistedAttributes;
	}

	#isExistedInheritedCheckbox(): boolean
	{
		return !this.#isReadOnly() && !this.section.getForm().isCatalogMode();
	}

	#setTemplate(template: string): SeoInput
	{
		if (this.#isReadOnly())
		{
			return this;
		}

		const value = this.section.getForm().getValue(this.id);
		value.template = template;
		if (Type.isStringFilled(template))
		{
			this.refreshHint(value);
		}
		else
		{
			value.hint = '';
			this.hintWrapper.innerHTML = '';
		}

		return this;
	}

	#toggleInherited(event: Event): SeoInput
	{
		if (this.#isReadOnly() || this.section.getForm().isCatalogMode())
		{
			return this;
		}

		const isChecked = event.target.checked;
		const value = this.section.getForm().getValue(this.id);

		value.inherited = isChecked ? 'N' : 'Y';
		this.input.disabled = !isChecked;
		if (isChecked)
		{
			Dom.removeClass(this.inputWrapper, 'ui-ctl-disabled');
		}
		else
		{
			Dom.addClass(this.inputWrapper, 'ui-ctl-disabled');
		}

		return this;
	}

	#isReadOnly(): boolean
	{
		return this.section.getForm().isReadOnly();
	}

	onInput(event: Event): void
	{
		this.#setTemplate(event.target.value);
	}

	refreshHint(template: ValueScheme): SeoInput
	{
		if (this.#isReadOnly())
		{
			return this;
		}

		this.section
			.getForm()
			.getHint(this.id, template)
			.then((result) => {
				const value = this.section.getForm().getValue(this.id);
				value.hint = result.data;
				this.hintWrapper.innerHTML = result.data;
			})
		;

		return this;
	}

	toggleTemplatesMenu(): void
	{
		this.section.toggleInputMenu(this);
	}

	getInput(): HTMLElement
	{
		return this.input;
	}

	addTemplateValue(template: string): void
	{
		this.getInput().value += template;
		this.#setTemplate(this.getInput().value);
	}
}