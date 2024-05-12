import {BaseSettingsElement} from "./base-settings-element";
import {TabField} from "./tab-field";
import { Tabs, Tab } from 'ui.tabs';
import type { TabsOptionsType } from 'ui.tabs';

export type TabsFieldType = {
	parent: ?BaseSettingsElement,
	tabsOptions: ?TabsOptionsType
};

export class TabsField extends BaseSettingsElement
{
	#fieldView: Tabs

	constructor(params: TabsFieldType)
	{
		super(params);
		this.setParentElement(params.parent);

		this.#fieldView = new Tabs(params.tabsOptions);
		this.#fieldView.getItems().forEach(
			(tab: Tab) => {
				new TabField({
					parent: this,
					fieldView: tab
				});
			}
		);
	}

	activateTab(tabField: TabField, withAnimation: boolean = true)
	{
		this.getFieldView().activateItem(tabField.getFieldView(), withAnimation);
		tabField.render();
	}

	getFieldView(): Tabs
	{
		return this.#fieldView;
	}

	render(): HTMLElement
	{
		return this.#fieldView.getContainer();
	}
}