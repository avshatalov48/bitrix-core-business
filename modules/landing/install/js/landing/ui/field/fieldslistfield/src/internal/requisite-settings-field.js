import {Dom, Tag, Loc} from 'main.core';
import {BaseField} from 'landing.ui.field.basefield';

import './css/style.css';

export class RequisiteSettingsField extends BaseField
{
	constructor(options)
	{
		super(options);
		this.setEventNamespace('BX.Landing.UI.Field.FieldsListField.RequisiteSettingsField');
		this.subscribeFromOptions(options.events);

		Dom.replace(this.input, this.getSettingsLayout());
	}

	getOptions(): {[key: string]: any}
	{
		return this.options;
	}

	getSettingsLayout(): HTMLDivElement
	{
		return this.cache.remember('settingsLayout', () => {
			return Tag.render`
				<div class="landing-ui-field-requisite-settings">
					${[...this.getCheckboxTree().keys()].map((checkbox) => checkbox.layout)}
				</div>
			`;
		});
	}

	getCheckboxTree(): Map<BX.Landing.UI.Field.Checkbox, BX.Landing.UI.Field.Checkbox>
	{
		return this.cache.remember('checkboxTree', () => {
			const requisites = this.getOptions().value;

			return requisites.reduce((map: Map, requisite) => {
				const fieldsCheckbox = new BX.Landing.UI.Field.Checkbox({
					selector: `${requisite.id}_fields`,
					compact: true,
					items: requisite.fields.map((field) => {
						return {
							name: field.label,
							value: field.name,
							checked: field.disabled !== false,
						};
					}),
					onChange: () => {
						this.emit('onChange');
					},
				});

				const onFieldSettingsLinkClick = (event: MouseEvent) => {
					event.preventDefault();
					event.stopPropagation();

					if (!categoryCheckbox.layout.contains(fieldsCheckbox.layout))
					{
						Dom.append(fieldsCheckbox.layout, categoryCheckbox.layout);
					}
					else
					{
						Dom.remove(fieldsCheckbox.layout);
					}
				};

				const fieldSettingsLink = Tag.render`
					<span 
						class="ui-link ui-link-dashed"
						onclick="${onFieldSettingsLinkClick}"
					>
						${Loc.getMessage('LANDING_FIELDS_ITEM_REQUISITE_SETTINGS_FIELDS_LABEL')}
					</span>
				`;

				const categoryCheckbox = new BX.Landing.UI.Field.Checkbox({
					selector: requisite.id,
					compact: true,
					items: [{
						name: requisite.label,
						value: requisite.id,
						checked: requisite.disabled !== false,
					}],
					onChange: () => {
						this.emit('onChange');

						const labelLayout = categoryCheckbox.layout
							.querySelector('.landing-ui-field-checkbox-item-label');

						if (categoryCheckbox.getValue().length > 0)
						{
							Dom.append(fieldSettingsLink, labelLayout);
						}
						else
						{
							Dom.remove(fieldSettingsLink);
							Dom.remove(fieldsCheckbox.layout);
						}
					},
				});

				if (requisite.disabled !== false)
				{
					const labelLayout = categoryCheckbox.layout
						.querySelector('.landing-ui-field-checkbox-item-label');
					Dom.append(fieldSettingsLink, labelLayout);
				}

				map.set(
					categoryCheckbox,
					fieldsCheckbox,
				);

				return map;
			}, new Map());
		});
	}

	getValue(): Array<{disabled: boolean, fields: Array<{disabled: boolean}>}>
	{
		const entries = [...this.getCheckboxTree().entries()];

		return entries.reduce((acc, [categoryCheckbox, fieldsCheckbox], index) => {
			const fieldsValue = fieldsCheckbox.getValue();
			acc.push({
				id: categoryCheckbox.selector,
				disabled: categoryCheckbox.getValue().length === 0,
				fields: this.getOptions().value[index].fields.map((field) => {
					return {
						name: field.name,
						disabled: !fieldsValue.includes(field.name),
					};
				}),
			});

			return acc;
		}, []);
	}
}