import {Dom, Type} from 'main.core';
import { BaseSettingsElement } from './base-settings-element';
import {Row, SeparatorRow} from 'ui.section';

export class SettingsRow extends BaseSettingsElement
{
	#rowView: Row;

	constructor(params)
	{
		super(params);
		this.#rowView = params.row instanceof Row || params.row instanceof SeparatorRow
			? params.row : new Row(Type.isPlainObject(params.row) ? params.row : {})
		;
	}

	getRowView(): Row
	{
		return this.#rowView;
	}

	render(): HTMLElement
	{
		for (let element of this.getChildrenElements())
		{
			this.getRowView().append(element.render());
		}

		return this.getRowView().render();
	}

	highlight(): boolean
	{
		this.highlightElement(this.getRowView().render());

		return true;
	}
}