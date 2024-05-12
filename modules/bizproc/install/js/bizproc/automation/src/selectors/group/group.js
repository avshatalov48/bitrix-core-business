import { Type, Runtime } from 'main.core';
import { MenuGroupItem, Field } from '../types';

export class Group
{
	#items: [] = [];
	#groups: Object<string, MenuGroupItem> = {};
	#setSuperTitle: boolean;

	constructor(data: { fields: [], setSuperTitle: boolean })
	{
		if (this.constructor === Group)
		{
			throw new Error('Object of Abstract Class cannot be created');
		}

		if (!Type.isArray(data.fields))
		{
			throw new TypeError('fields must be an array');
		}

		this.#setSuperTitle = Type.isBoolean(data.setSuperTitle) ? data.setSuperTitle : true;
	}

	get items(): Array<Field>
	{
		return this.#items;
	}

	get groups(): MenuGroupItem[]
	{
		return Object.values(this.#groups);
	}

	get groupsWithChildren(): MenuGroupItem[]
	{
		return this.groups.filter((group) => group.children.length > 0);
	}

	addGroup(groupId: string, group: MenuGroupItem): void
	{
		this.#groups[groupId] = this.#normalizeGroup(group);
	}

	hasGroup(groupId: string): boolean
	{
		return Object.hasOwn(this.#groups, groupId);
	}

	addGroupItem(groupId: string, item: MenuGroupItem)
	{
		if (this.hasGroup(groupId))
		{
			const normalizedItem = this.#normalizeGroup(item, this.#groups[groupId].title);
			this.#groups[groupId].children.push(normalizedItem);
		}
	}

	#normalizeGroup(group: MenuGroupItem, superGroupTitle: ?string = null): MenuGroupItem
	{
		const normalizedGroup: MenuGroupItem = Runtime.clone(group);

		if (!Type.isBoolean(normalizedGroup.searchable))
		{
			normalizedGroup.searchable = true;
		}

		if (!Type.isArray(normalizedGroup.children))
		{
			normalizedGroup.children = [];
		}

		normalizedGroup.children = (
			normalizedGroup.children
				.map((childGroup) => this.#normalizeGroup(childGroup, normalizedGroup.title))
		);

		if (
			this.#setSuperTitle
			&& Type.isStringFilled(superGroupTitle)
			&& !Type.isStringFilled(normalizedGroup.supertitle)
		)
		{
			normalizedGroup.supertitle = superGroupTitle;
		}

		if (!Type.isArrayFilled(normalizedGroup.children) && normalizedGroup.searchable === true)
		{
			this.#items.push(normalizedGroup);
		}

		return {
			entityId: 'bp',
			tabs: 'recents',
			...normalizedGroup,
		};
	}
}
