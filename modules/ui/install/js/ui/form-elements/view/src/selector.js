import {Dom, Loc, Tag, Text, Type} from "main.core";
import {BaseField} from "./base-field";

export class Selector extends BaseField
{
	#items: Array = [];
	#hintTitle: String;
	#hints: Object;
	defaultValue: string;
	#hintTitleElement: HTMLElement;
	#hintDescElement: HTMLElement;
	#inputNode: HTMLElement;
	#hintSeparatorElement: HTMLElement;

	constructor(params)
	{
		params.inputName = params.name;
		super(params);
		this.#items = params.items;
		this.#hintTitle = Type.isString(params.hintTitle) ? params.hintTitle : '';
		this.#hints = Type.isObject(params.hints) ? params.hints : {};
		this.defaultValue = params.current;
		this.#hintTitleElement = Tag.render`<div class="ui-section__title"></div>`;
		this.#hintDescElement = Tag.render`<div class="ui-section__description"></div>`;
		this.#hintSeparatorElement = Tag.render`<div class="ui-section__field-inline-separator"></div>`;
		this.#inputNode = this.#buildSelector()
	}

	getHint(key: string)
	{
		let hint = this.#hints[key];
		if (!Type.isString(hint) || hint === '')
		{
			return null;
		}

		return hint;
	}

	prefixId(): string
	{
		return 'selector_';
	}

	setHint(key: string): void
	{
		const moreElement = this.renderMoreElement(this.getHelpdeskCode()).outerHTML;
		const more = !Type.isNil(this.getHelpdeskCode()) ? moreElement : '';

		const hint = this.getHint(key);
		this.#hintTitleElement.innerText = !Type.isNil(hint) ? this.#hintTitle : '';
		this.#hintDescElement.innerHTML = !Type.isNil(hint) ? hint + ' ' + more : '';

		Dom.removeClass(this.field, '--field-separator');
		Dom.remove(this.#hintSeparatorElement);
		if (!Type.isNil(hint))
		{
			Dom.addClass(this.field, '--field-separator');
			const fieldContainer = this.field.querySelector('.ui-section__field-inline-box .ui-section__field');
			Dom.insertAfter(this.#hintSeparatorElement, fieldContainer);
		}
	}

	renderContentField(): HTMLElement
	{
		const disableClass = !this.isEnable() ? 'ui-ctl-disabled' : '';
		const lockElement = !this.isEnable() ? this.renderLockElement() : null;

		return Tag.render`
		<div id="${this.getId()}" class="ui-section__field-selector ">
			<div class="ui-section__field-container">
				<div class="ui-section__field-label_box">
					<label class="ui-section__field-label" for="${this.getName()}">${this.getLabel()}</label> 
					${lockElement}
				</div>			
				<div class="ui-section__field-inline-box">
					<div class="ui-section__field">				
						<div class="ui-ctl ui-ctl-w100 ui-ctl-after-icon ui-ctl-dropdown ${disableClass}">
							<div class="ui-ctl-after ui-ctl-icon-angle"></div>
							${this.getInputNode()}
						</div>		
					</div>
							
					<div class="ui-section__hint">
						${this.#hintTitleElement}
						${this.#hintDescElement}
					</div>				
				</div>			
			</div>
		</div>
		`;
	}

	render(): HTMLElement
	{
		const render = super.render();
		this.setHint(this.getInputNode().value);

		return render;
	}

	getInputNode(): HTMLElement
	{
		return this.#inputNode;
	}

	#buildSelector(): HTMLElement
	{
		let options = [];
		for (let {value, name, selected} of this.#items)
		{
			let selectedAttr = '';
			if (selected === true)
			{
				selectedAttr = 'selected';
			}
			options.push(Tag.render`<option ${selectedAttr} value="${value}">${name}</option>`);
		}

		return Dom.create('select', {
			attrs: {
				name: this.getName(),
				class: 'ui-ctl-element',
			},
			events: {
				change: (event) => {
					this.setHint(event.target.value);
				},
				click: (event) => {
					if (!this.isEnable())
					{
						if (!Type.isNil(this.getHelpMessage()))
						{
							this.getHelpMessage().show();
						}

						event.preventDefault();
					}
				},
				mousedown: (event) => {
					if (!this.isEnable())
					{
						event.preventDefault();
					}
				}
			},
			children: options,
		});
	}
}
