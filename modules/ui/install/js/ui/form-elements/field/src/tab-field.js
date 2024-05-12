import { BaseSettingsElement } from './base-settings-element';
import { Tab, Tabs } from 'ui.tabs';
import type { TabOptionsType } from 'ui.tabs';
import {Dom} from "main.core";
import {TabsField} from "ui.form-elements.field";

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

		if (this.getParentElement() instanceof TabsField)
		{
			this.#fieldView.subscribe('changeTab', () => {
				this.getParentElement().activateTab(this);
			});
		}
	}

	getFieldView(): Tab
	{
		return this.#fieldView;
	}

	render(): HTMLElement
	{
		for (const element of this.getChildrenElements())
		{
			Dom.append(element.render(), this.getFieldView().getBodyDataContainer());
		}

		return this.getFieldView().getBody();
	}

	highlight(): boolean
	{
		this.highlightElement(this.getFieldView().getBody());
		this.highlightElement(this.getFieldView().getHeader());

		return true;
	}
}