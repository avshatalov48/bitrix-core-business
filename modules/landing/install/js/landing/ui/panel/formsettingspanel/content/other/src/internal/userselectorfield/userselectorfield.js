import {BaseField} from 'landing.ui.field.basefield';
import {TagSelector} from 'ui.entity-selector';
import {PageObject} from 'landing.pageobject';
import {Dom} from 'main.core';

export default class UserSelectorField extends BaseField
{
	constructor(options)
	{
		super(options);

		Dom.removeClass(this.input, 'landing-ui-field-input');

		this.getTagSelector().renderTo(this.input);
	}

	getTagSelector(): TagSelector
	{
		return this.cache.remember('tagSelector', () => {
			const root = PageObject.getRootWindow();
			return new root.BX.UI.EntitySelector.TagSelector({
				id: 'user-selector',
				dialogOptions: {
					id: 'user-selector',
					entities: [
						{
							id: 'user',
							options: {
								activeUsers: true,
							},
						},
					],
					preselectedItems: this.options.value,
					events: {
						'Item:onSelect': () => {
							this.emit('onChange', {skipPrepare: true});
						},
						'Item:onDeselect': () => {
							this.emit('onChange', {skipPrepare: true});
						},
					},
				},
			});
		});
	}

	getValue(): Array<number>
	{
		return this.getTagSelector().getDialog().getSelectedItems().map((item) => {
			return item.id;
		});
	}
}