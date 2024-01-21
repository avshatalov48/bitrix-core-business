import {Switcher} from 'ui.switcher'
import { Dom, Tag, Type } from 'main.core';
import 'ui.info-helper';
import {EventEmitter} from "main.core.events";
import {BaseField} from "./base-field";

export class Checker extends BaseField
{
	field: HTMLElement;
	switcher: Switcher;
	hintOn: string;
	hintOff: string;
	defaultValue: boolean;
	hideSeparator: boolean;
	alignCenter: boolean;
	noMarginBottom: boolean;
	#renderMore: ?HTMLElement;

	constructor(params)
	{
		params.label = params.title;
		super(params);
		this.hintOn = params.hintOn;
		this.hintOff = params.hintOff;
		this.hideSeparator = params.hideSeparator;
		this.alignCenter = params.alignCenter;
		this.noMarginBottom = params.noMarginBottom;

		this.switcher = new Switcher({
			inputName: this.getName(),
			checked: params.checked,
			id: this.getId(),
			attributeName: params.attributeName,
			handlers: params.handlers,
			color: params.colors,
			size: params.size
		});
		this.defaultValue = params.checked;
		EventEmitter.subscribe(
			this.switcher,
			'toggled',
			() =>
			{
				if (!this.isEnable())
				{
					this.switcher.check(this.defaultValue, false);
					if (!Type.isNil(this.getHelpMessage()))
					{
						this.getHelpMessage().show();
					}

					return;
				}
				this.switcher.inputNode.form.dispatchEvent(new Event('change'));
				this.changeHint(this.isChecked());
				this.emit('change', this.isChecked());
			}
		);
	}

	getValue(): string
	{
		return this.switcher.inputNode.value;
	}

	getInputNode(): HTMLElement
	{
		return this.switcher.node;
	}

	prefixId(): string
	{
		return 'checker_';
	}

	isChecked(): boolean
	{
		return this.switcher.isChecked();
	}

	renderMore(): ?HTMLElement
	{
		if (this.#renderMore)
		{
			return this.#renderMore;
		}

		this.#renderMore = !Type.isNil(this.getHelpdeskCode())
			? this.renderMoreElement(this.getHelpdeskCode())
			: '';

		return this.#renderMore;
	}

	renderContentField(): HTMLElement
	{
		const lockElement = !this.isEnable() ? this.renderLockElement() : null;

		return Tag.render`
		<div id="${this.getId()}" 
			class="ui-section__field-switcher ${this.hideSeparator ? '--hide-separator' : ''} ${this.alignCenter ? '--align-center --gray-title' : ''} ${this.noMarginBottom ? '--no-margin-bottom' : ''}">
			<div class="ui-section__field">
				<div class="ui-section__switcher">
					${this.getInputNode()}
				</div>
				
				<div class="ui-section__field-inner">
					<div class="ui-section__title">
						${this.getLabel()} ${lockElement}
					</div>
					${this.#renderHint(this.isChecked())}
				</div>
			</div>
		</div>
		`;
	}

	getHint(isChecked: boolean)
	{
		if (!Type.isStringFilled(this.hintOff))
		{
			return Type.isStringFilled(this.hintOn) ? this.hintOn : '';
		}
		let result = isChecked ? this.hintOn : this.hintOff;

		return Type.isStringFilled(result) ? result : '';
	}

	changeHint(isChecked: boolean)
	{
		const hintElement = this.field.querySelector('.ui-section__hint');
		Dom.replace(hintElement, this.#renderHint(isChecked));
	}

	#renderHint(isChecked: boolean)
	{
		return Tag.render`
			<div class="ui-section__hint">
				${this.getHint(isChecked)} ${Type.isDomNode(this.renderMore()) ? this.renderMore() : ''}
			</div>
		`;
	}
}
