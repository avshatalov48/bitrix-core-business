import {Event, Tag, Text, Type, Loc, Dom, Runtime} from 'main.core';
import {Base} from "../fields-group/base";
import {FieldScheme} from "../types/field-scheme";

export class CacheCheckbox
{
	constructor(settings: FieldScheme, section: Base)
	{
		this.id = Text.encode(settings.ID);
		this.title = Text.encode(settings.TITLE);
		this.section = section;
	}

	layout(): HTMLElement
	{
		const checkbox = Tag.render`<input type="checkbox" class="ui-ctl-element">`;
		checkbox.checked = this.#isChecked();

		Event.bind(checkbox, 'change', this.#setValue.bind(this));

		return Tag.render`
			<div class="ui-form-row">
				<div class='ui-form-label'>
				</div>
				<div class="ui-form-content">
					<label class="ui-ctl ui-ctl-checkbox ui-ctl-w100">
						${checkbox}
						<div class="ui-ctl-label-text">${this.title}</div>
					</label>
				</div>
			</div>
		`;
	}

	#isChecked(): boolean
	{
		const value = this.section.getForm().getValue(this.id);

		return value.clearCache === 'Y';
	}

	#setValue(event: Event): CacheCheckbox
	{
		const value = this.section.getForm().getValue(this.id);
		value.clearCache = event.target.checked ? 'Y' : 'N';

		return this;
	}
}