import Column from "./column";
import Grid from "./grid";
import {Dom, Reflection, Tag} from 'main.core';
import Title from './item/title';
import Hint from './item/hint';
import Member from './item/member';
import Role from './item/role';
import Toggler from './item/toggler';
import Controller from './item/controller';
import VariableSelector from './item/variableselector';
import UserGroupTitle from './item/usergrouptitle';
import MultiSelector from "./item/multivariable/multiselector";

type ColumnItemOptions = {
	grid: Grid;
	id: number;
	type: string;
	text: string;
	placeholder: string;
	hintTitle: string;
	hint: string;
	variables: [];
	enableSearch: boolean;
	showAvatars: boolean;
	compactView: boolean;
	userGroup: null;
	access: null;
	currentValue: null;
	controller: null;
	openPopupEvent: null;
	popupContainer: null;
	accessCodes: null;
	allSelectedCode: number;
	container: HTMLElement;
	column: Column;
};

export default class ColumnItem {
	constructor(options: ColumnItemOptions)
	{
		this.options = options;
		this.type = options.type ? options.type : null;
		this.hint = options.hint ? options.hint : null;
		this.controller = options.controller ? options.controller : null;
		this.column = options.column;
	}

	render(): HTMLElement
	{
		let item = null;
		const container = Tag.render`<div class='ui-access-rights-column-item'></div>`;
		this.options.container = container;
		if (this.type === Role.TYPE)
		{
			item = new Role(this.options);
			if (this.column.newColumn)
			{
				setTimeout(() => {
					item.onRoleEditMode();
					item.roleInput.value = '';
				})
			}
		}
		else if (this.type === Member.TYPE)
		{
			item = new Member(this.options);
		}
		else if (this.type === Title.TYPE)
		{
			item = new Title(this.options);
		}
		else if (this.type === VariableSelector.TYPE)
		{
			item = new VariableSelector(this.options);
		}
		else if (this.type === MultiSelector.TYPE)
		{
			item = new MultiSelector(this.options);
		}
		else if (this.type === Toggler.TYPE)
		{
			item = new Toggler(this.options);
		}

		if (item)
		{
			Dom.append(item.render(), container);
		}

		if (this.hint)
		{
			const hintOptions = {
				className: 'ui-access-rights-column-item-notify',
				...this.options
			};
			Dom.append((new Hint(hintOptions)).render(), container);
		}

		if (this.type === UserGroupTitle.TYPE)
		{
			Dom.append((new UserGroupTitle(this.options)).render(), container);
		}

		if (this.controller)
		{
			Dom.append((new Controller(this.options)).render(), container);
		}

		return container;
	}
}

const namespace = Reflection.namespace('BX.UI.AccessRights');
namespace.ColumnItem = ColumnItem;
