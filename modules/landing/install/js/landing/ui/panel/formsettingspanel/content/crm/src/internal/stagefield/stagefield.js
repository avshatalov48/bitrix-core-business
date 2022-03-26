import {BaseField} from 'landing.ui.field.basefield';
import {Loc} from 'landing.loc';
import {Dom, Tag, Type} from 'main.core';

import './css/style.css';

const fetchId = (item) => {
	return !Type.isNil(item.ID) ? item.ID : item.id;
};

const fetchName = (item) => {
	return !Type.isNil(item.NAME) ? item.NAME : item.name;
};

export default class StageField extends BaseField
{
	constructor(options)
	{
		super(options);
		Dom.replace(this.input, this.getInner());
	}

	getInner()
	{
		return this.cache.remember('inner', () => {
			return Tag.render`
				<div class="landing-ui-field-stages">
					${this.getCategoriesDropdown().getLayout()}
				</div>
			`;
		});
	}

	getCategoriesDropdown(): BX.Landing.UI.Field.Dropdown
	{
		return this.cache.remember('categoriesDropdown', () => {
			return new BX.Landing.UI.Field.Dropdown({
				title: this.options.listTitle || Loc.getMessage('LANDING_FORM_SETTINGS_CATEGORIES_FIELD_TITLE'),
				content: this.options.value.category,
				items: this.options.categories.map((category) => {
					return {
						name: fetchName(category),
						value: fetchId(category),
					};
				}),
				onChange: this.onCategoryChange.bind(this),
			});
		});
	}

	getCurrentCategory()
	{
		const currentCategoryId = this.getCategoriesDropdown().getValue();

		return this.options.categories.find((category) => {
			return String(fetchId(category)) === String(currentCategoryId);
		});
	}

	onCategoryChange()
	{
		this.emit('onChange');
	}

	getValue()
	{
		return {
			category: this.getCategoriesDropdown().getValue(),
			stage: '',
		};
	}

	setValue(value, preventEvent = false)
	{
		this.getCategoriesDropdown().setValue(value.category);

		if (!preventEvent)
		{
			this.emit('onChange');
		}
	}
}