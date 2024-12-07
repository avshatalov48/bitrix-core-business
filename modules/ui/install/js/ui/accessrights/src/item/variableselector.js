import ColumnItemOptions from "../columnitem";
import {Event, Text, Tag, Dom, Loc} from 'main.core';

import {PopupMenu} from "main.popup";
import type { ChangerOpts } from './changer';
import Changer from "./changer";
import {BaseEvent, EventEmitter} from "main.core.events";

type VariableItem = {
	id: number,
	title: string,
}

export default class VariableSelector extends Changer
{
	static TYPE = 'variables';

	changerOptions: ChangerOpts;

	constructor(options: ColumnItemOptions)
	{
		super(options);

		this.variables = options.variables || [];

		this.selectedValues = [this.currentValue ?? '0'];
	}

	bindEvents()
	{
		EventEmitter.subscribe('BX.UI.AccessRights:reset', this.reset.bind(this));
		EventEmitter.subscribe('BX.UI.AccessRights:refresh', this.refresh.bind(this));
	}

	render(): HTMLElement
	{
		const title = this.getSelected()?.title ?? Loc.getMessage('JS_UI_ACCESSRIGHTS_ADD');
		const variablesValue = Tag.render`
				<div class='ui-access-rights-column-item-text-link'>
					${Text.encode(title)}
				</div>
			`;

		Event.bind(variablesValue, 'click', this.showVariablesPopup.bind(this));

		Dom.append(variablesValue, this.getChanger());

		return this.getChanger();
	}

	refresh(): HTMLElement
	{
		if (this.isModify)
		{
			this.currentValue = this.selectedValues[0];
			this.reset();
		}
	}

	reset(): HTMLElement
	{
		if (this.isModify)
		{
			this.selectedValues = [this.currentValue];
			this.getChanger().innerHTML = '';
			this.adjustChanger();
			this.render();
		}
	}

	getSelected(): VariableItem
	{
		const selected = this.variables.filter(variable => this.selectedValues.map(String).includes(String(variable.id)));

		return selected[0];
	}

	showVariablesPopup(event: Event): void
	{
		const menuItems = [];

		this.variables.map((data) => {
			menuItems.push({
				id: data.id,
				text: data.title,
				onclick: this.select.bind(this),
			});
		});

		PopupMenu.show(
			'ui-access-rights-column-item-popup-variables',
			event.target,
			menuItems,
			{
				autoHide: true,
				events : {
					onPopupClose: () => {
						PopupMenu.destroy('ui-access-rights-column-item-popup-variables');
					}
				}
			}
		);
	}

	select(event: BaseEvent, item: MenuItem)
	{
		this.selectedValues = [item.options.id];

		item
			.getMenuWindow()
			?.close()
		;

		this.getChanger().innerHTML = '';
		this.render();
		this.adjustChanger();

		EventEmitter.emit('BX.UI.AccessRights.ColumnItem:selectAccessItems', this);
		EventEmitter.emit('BX.UI.AccessRights.ColumnItem:update', this);
	}

	adjustChanger(): void
	{
		const defaultValue = this.changerOptions.replaceNullValueTo || null;

		const selectedValue = this.selectedValues[0] || defaultValue;

		if (selectedValue === this.currentValue)
		{
			this.isModify = false;
			this.removeChangerHtmlClass();
		}
		else
		{
			this.isModify = true;
			this.addChangerHtmlClass();
		}
	}
}
