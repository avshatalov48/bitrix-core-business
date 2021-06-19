import {BaseField} from 'landing.ui.field.basefield';
import {Loc} from 'landing.loc';
import {Dom, Tag} from 'main.core';

import './css/style.css';

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
						name: category.NAME || category.name,
						value: category.ID || category.id,
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
			return String(category.ID || category.id) === String(currentCategoryId);
		});
	}

	onCategoryChange()
	{
		const oldStagesDropdown = this.getStagesDropdown();
		this.cache.delete('stagesDropdown');

		if (oldStagesDropdown.popup)
		{
			oldStagesDropdown.popup.destroy();
		}

		const newStagesDropdown = this.getStagesDropdown();
		Dom.replace(oldStagesDropdown.getLayout(), newStagesDropdown.getLayout());
		this.emit('onChange');
	}

	getStagesDropdown(): BX.Landing.UI.Field.Dropdown
	{
		return this.cache.remember('stagesDropdown', () => {
			const stages = this.getCurrentCategory().STAGES || this.getCurrentCategory().stages;

			return new BX.Landing.UI.Field.Dropdown({
				title: this.options.listTitle || Loc.getMessage('LANDING_FORM_SETTINGS_STAGES_FIELD_TITLE'),
				items: stages.map((stage) => {
					return {
						name: stage.NAME || stage.name,
						value: stage.ID || stage.id,
					};
				}),
			});
		});
	}

	getValue()
	{
		return {
			category: this.getCategoriesDropdown().getValue(),
			stage: this.getStagesDropdown().getValue(),
		};
	}
}