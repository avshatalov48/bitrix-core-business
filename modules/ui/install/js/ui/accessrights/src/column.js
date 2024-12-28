import { EventEmitter } from "main.core.events";
import { Dom, Event, Reflection, Tag, Type } from 'main.core';

import Section from "./section";
import ColumnItem from "./columnitem";
import Role from "./item/role";
import Member from "./item/member";
import Title from "./item/title";
import UserGroupTitle from "./item/usergrouptitle";
import Toggler from "./item/toggler";
import VariableSelector from "./item/variableselector";
import MultiSelector from "./item/multivariable/multiselector";

type ColumnOptions = {
	grid: Grid;
	items: [];
	userGroup: [];
	accessCodes: [];
	section: Section;
	headSection: Section;
	newColumn: Column;
	openPopupEvent: string;
	popupContainer: HTMLElement;
};

export default class Column {
	constructor(options: ColumnOptions)
	{
		this.layout = {
			container: null
		};
		this.grid = options.grid ? options.grid : null;
		this.items = options.items ? options.items : [];
		this.userGroup = options.userGroup ? options.userGroup : null;
		this.accessCodes = options.accessCodes ? options.accessCodes : null;
		this.section = options.section ? options.section : null;
		this.headSection = options.headSection;
		this.newColumn = options.newColumn ? options.newColumn : null;
		this.openPopupEvent = options.grid.openPopupEvent ? options.grid.openPopupEvent : null;
		this.popupContainer = options.grid.popupContainer ? options.grid.popupContainer : null;
	}

	getItem(options): ColumnItem
	{
		options = options || {};

		const defaultParam = {
			group: options.group,
			changerOptions: options.changerOptions,
		};
		let param = {
			...defaultParam
		};

		if (options.type === UserGroupTitle.TYPE)
		{
			param = {
				type: options.type,
				text: options.title,
				controller: options.controller,
				...defaultParam,
			}
		}

		if (options.type === Title.TYPE)
		{
			param = {
				...defaultParam,
				id: options.id,
				groupHead: options.groupHead,
				type: options.type,
				hint: options.hint,
				text: options.title,
				controller: options.controller
			}
		}

		if (options.type === Toggler.TYPE)
		{
			param = {
				...defaultParam,
				type: options.type,
				access: options.access,
			}
		}

		if (options.type === VariableSelector.TYPE || options.type === MultiSelector.TYPE)
		{
			param = {
				...defaultParam,
				type: options.type,
				text: options.title,
				variables: options.variables,
				access: options.access,
			}
		}

		if (options.type === MultiSelector.TYPE)
		{
			param.allSelectedCode = options.allSelectedCode;
			param.enableSearch = options.enableSearch;
			param.showAvatars = options.showAvatars;
			param.compactView = options.compactView;
			param.hintTitle = options.hintTitle;
			param.disableSelectAll = options.disableSelectAll || false;
		}

		if (options.type === Role.TYPE)
		{
			param = {
				...defaultParam,
				type: options.type,
				text: options.title,
			}
		}

		if (options.type === Member.TYPE)
		{
			const accessCodes = [];

			for (const item in options.members)
			{
				accessCodes[item] = options.members[item].type;
			}

			param = {
				type: options.type,
				accessCodes: accessCodes
			}
		}

		param.column = this;
		param.userGroup = this.userGroup;
		param.openPopupEvent = this.openPopupEvent;
		param.popupContainer = this.popupContainer;
		param.currentValue = null;

		param.grid = this.grid;

		if (
			options.type === VariableSelector.TYPE
			|| options.type === MultiSelector.TYPE
			|| options.type === Toggler.TYPE
		)
		{
			const accessId = param.access.id.toString();
			const accessRights = param.userGroup?.accessRights ?? [];

			for (let i = 0; i < accessRights.length; i++)
			{
				if (accessId !== accessRights[i].id.toString())
				{
					continue;
				}

				if (options.type === MultiSelector.TYPE)
				{
					param.currentValue = param.currentValue ?? [];

					if (Type.isArray(accessRights[i].value))
					{
						param.currentValue = [...param.currentValue, ...accessRights[i].value]
					}
					else
					{
						param.currentValue.push(accessRights[i].value);
					}
				}
				else
				{
					param.currentValue = accessRights[i].value;
				}
			}
		}

		return new ColumnItem(param);
	}

	getUserGroup(): []
	{
		return this.userGroup;
	}

	remove(): void
	{
		if (Dom.hasClass(this.layout.container, 'ui-access-rights-column-new'))
		{
			this.resetClassNew();
		}

		Dom.addClass(this.layout.container,'ui-access-rights-column-remove')
		Dom.style(this.layout.container, 'width', this.layout.container.offsetWidth + 'px')

		Event.bind(this.layout.container, 'animationend', () => {
			Dom.style(this.layout.container, 'minWidth', '0px')
			Dom.style(this.layout.container, 'maxWidth', '0px')
		});


		setTimeout(() => {
			Dom.remove(this.layout.container);
		}, 500);
	}

	resetClassNew(): void
	{
		Dom.removeClass(this.layout.container,'ui-access-rights-column-new');
	}

	render(): HTMLElement
	{
		if (!this.layout.container)
		{
			const itemsFragment = document.createDocumentFragment();

			if (this.headSection)
			{
				this.userGroup.type = Role.TYPE;
				Dom.append(this.getItem(this.userGroup).render(), itemsFragment);

				this.userGroup.type = Member.TYPE;
				Dom.append(this.getItem(this.userGroup).render(), itemsFragment);
			}

			for (const data of this.items)
			{
				const item = this.getItem(data);
				Dom.append(item.render(), itemsFragment);
			}

			this.layout.container = Tag.render`<div class='ui-access-rights-column'></div>`;
			if (this.newColumn)
			{
				Dom.addClass('ui-access-rights-column-new', this.layout.container)
			}

			EventEmitter.subscribe('BX.UI.AccessRights:refresh', this.resetClassNew.bind(this));

			Dom.append(itemsFragment, this.layout.container);

			return this.layout.container;
		}
	}
}

const namespace = Reflection.namespace('BX.UI.AccessRights');
namespace.Column = Column;
