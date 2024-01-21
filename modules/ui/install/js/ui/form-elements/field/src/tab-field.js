import { BaseSettingsElement } from './base-settings-element';
import { Tab, Tabs } from 'ui.tabs';
import type { TabOptionsType } from 'ui.tabs';

export type TabFieldType = {
	parent: BaseSettingsElement,
	tabsOptions?: TabOptionsType,
	fieldView?: Tab
};

export class TabField extends BaseSettingsElement
{
	#fieldView: Tab;

	constructor(params: TabFieldType)
	{
		super(params);

		this.setParentElement(params.parent);

		if (params.fieldView instanceof Tab)
		{
			this.#fieldView = params.fieldView;
		}
		else if (params.tabsOptions)
		{
			this.#fieldView = new Tab(params.tabsOptions);
		}
		else
		{
			throw new Error('Tab field in Settings is not correct.');
		}

		if (params.parent.getFieldView() instanceof Tabs)
		{
			params.parent.getFieldView().addItem(this.#fieldView);
		}
	}

	getFieldView(): Tab
	{
		return this.#fieldView;
	}

	render(): HTMLElement
	{
		return this.getFieldView().getBody();
	}
}