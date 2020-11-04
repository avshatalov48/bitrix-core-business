import { Extension, Runtime, Type } from 'main.core';
import { EventEmitter } from 'main.core.events';

import ItemCollection from '../item/item-collection';
import SearchField from '../search/search-field';

import type Item from '../item/item';
import type { EntityOptions } from './entity-options';
import type { SearchFieldOptions } from '../search/search-field-options';
import type { ItemOptions } from '../item/item-options';
import type { TagItemOptions } from '../tag-selector/tag-item-options';
import type { ItemBadgeOptions } from '../item/item-badge-options';

/**
 * @memberof BX.UI.EntitySelector
 */
export default class Entity extends EventEmitter
{
	static extensions: string[] = null;
	static defaultOptions: { [entityId: string]: { [key: string]: any } } = null;

	id: string = null;
	options: { [key: string]: any } = {};
	searchable: boolean = true;
	searchFields: ItemCollection<SearchField> = new ItemCollection();
	dynamicLoad: boolean = false;
	dynamicSearch: boolean = false;
	searchCacheLimits: RegExp[] = [];

	itemOptions: { [key: string]: any } = {};
	tagOptions: { [key: string]: any } = {};
	badgeOptions: ItemBadgeOptions[] = [];

	constructor(entityOptions: EntityOptions)
	{
		super();
		this.setEventNamespace('BX.UI.EntitySelector.Entity');

		let options: EntityOptions = Type.isPlainObject(entityOptions) ? entityOptions : {};
		if (!Type.isStringFilled(options.id))
		{
			throw new Error('EntitySelector.Entity: "id" parameter is required.');
		}

		const defaultOptions = this.constructor.getEntityOptions(options.id) || {};
		options = Runtime.merge({}, defaultOptions, options);

		this.id = options.id;
		this.options = Type.isPlainObject(options.options) ? options.options : {};
		this.itemOptions = Type.isPlainObject(options.itemOptions) ? options.itemOptions : {};
		this.tagOptions = Type.isPlainObject(options.tagOptions) ? options.tagOptions : {};
		this.badgeOptions = Type.isArray(options.badgeOptions) ? options.badgeOptions : [];

		this.setSeachable(options.searchable);
		this.setDynamicLoad(options.dynamicLoad);
		this.setDynamicSearch(options.dynamicSearch);
		this.setSearchFields(options.searchFields);
		this.setSearchCacheLimits(options.searchCacheLimits);
	}

	static getDefaultOptions()
	{
		if (this.defaultOptions === null)
		{
			this.defaultOptions = {};
			this.getExtensions().forEach((extension: string) => {
				const settings = Extension.getSettings(extension);
				const entities: [] = settings.get('entities', []);
				entities.forEach(entity => {
					if (Type.isStringFilled(entity.id) && Type.isPlainObject(entity.options))
					{
						this.defaultOptions[entity.id] = JSON.parse(JSON.stringify(entity.options)); // clone
					}
				});
			});
		}

		return this.defaultOptions;
	}

	static getExtensions(): string[]
	{
		if (this.extensions === null)
		{
			const settings = Extension.getSettings('ui.entity-selector');
			this.extensions = settings.get('extensions', []);
		}

		return this.extensions;
	}

	static getEntityOptions(entityId: string)
	{
		return this.getDefaultOptions()[entityId] || null;
	}

	static getItemOptions(entityId: string, entityType: string)
	{
		if (!Type.isStringFilled(entityId))
		{
			return null;
		}

		const options = this.getEntityOptions(entityId);
		const itemOptions = options && options['itemOptions'] ? options['itemOptions'] : null;

		if (Type.isUndefined(entityType))
		{
			return itemOptions;
		}
		else
		{
			return itemOptions && itemOptions[entityType] ? itemOptions[entityType] : null;
		}
	}

	static getItemOption(entityId: string, entityType: string, option: string)
	{
		if (!Type.isStringFilled(entityType) || !Type.isStringFilled(option))
		{
			return null;
		}

		const options = this.getItemOptions(entityId, entityType);

		return options && !Type.isUndefined(options[option]) ? options[option] : null;
	}

	static getTagOptions(entityId: string, entityType?: string)
	{
		if (!Type.isStringFilled(entityId))
		{
			return null;
		}

		const options = this.getEntityOptions(entityId);
		const tagOptions = options && options['tagOptions'] ? options['tagOptions'] : null;

		if (Type.isUndefined(entityType))
		{
			return tagOptions;
		}
		else
		{
			return tagOptions && !Type.isUndefined(tagOptions[entityType]) ? tagOptions[entityType] : null;
		}
	}

	static getTagOption(entityId: string, entityType: string, option: string)
	{
		if (!Type.isStringFilled(entityType) || !Type.isStringFilled(option))
		{
			return null;
		}

		const options = this.getTagOptions(entityId, entityType);

		return options && options[option] ? options[option] : null;
	}

	getId(): string
	{
		return this.id;
	}

	getOptions(): { [key: string]: any }
	{
		return this.options;
	}

	getItemOptions(): { [key: string]: any }
	{
		return this.itemOptions;
	}

	getItemOption(item: Item, option: string): any
	{
		const entityType = item.getEntityType();
		if (this.itemOptions[entityType] && !Type.isUndefined(this.itemOptions[entityType][option]))
		{
			return this.itemOptions[entityType][option];
		}
		else if (this.itemOptions['default'] && !Type.isUndefined(this.itemOptions['default'][option]))
		{
			return this.itemOptions['default'][option];
		}

		return null;
	}

	getTagOptions(): { [key: string]: any }
	{
		return this.tagOptions;
	}

	getTagOption(item: Item, option: string): any
	{
		const entityType = item.getEntityType();
		if (this.tagOptions[entityType] && !Type.isUndefined(this.tagOptions[entityType][option]))
		{
			return this.tagOptions[entityType][option];
		}
		else if (this.tagOptions['default'] && !Type.isUndefined(this.tagOptions['default'][option]))
		{
			return this.tagOptions['default'][option];
		}

		return null;
	}

	getBadges(item: Item)
	{
		const entityTypeBadges = this.getItemOption(item, 'badges') || [];
		const badges = [...entityTypeBadges];

		this.badgeOptions.forEach((badge) => {
			if (Type.isPlainObject(badge.conditions))
			{
				for (const condition in badge.conditions)
				{
					if (item.getCustomData().get(condition) !== badge.conditions[condition])
					{
						return;
					}
				}

				badges.push(badge);
			}
		});

		return badges;
	}

	isSearchable(): boolean
	{
		return this.searchable;
	}

	setSeachable(flag: boolean): this
	{
		if (Type.isBoolean(flag))
		{
			this.searchable = flag;
		}

		return this;
	}

	getSearchFields(): ItemCollection<SearchField>
	{
		return this.searchFields;
	}

	setSearchFields(searchFields: SearchFieldOptions[])
	{
		this.searchFields.clear();

		// Default Search Fields
		const titleField = new SearchField({ name: 'title', searchable: true, system: true, type: 'string' });
		const subtitleField = new SearchField({ name: 'subtitle', searchable: true, system: true, type: 'string' });
		this.searchFields.add(titleField);
		this.searchFields.add(subtitleField);

		// Custom Search Fields
		const customFields = Type.isArray(searchFields) ? searchFields : [];
		customFields.forEach(fieldOptions => {
			const field = new SearchField(fieldOptions);
			if (field.isSystem()) // Entity can override default fields.
			{
				// delete a default title field
				if (field.getName() === 'title')
				{
					this.searchFields.delete(titleField);
				}
				else if (field.getName() === 'subtitle')
				{
					this.searchFields.delete(subtitleField);
				}
			}
			this.searchFields.add(field);
		});
	}

	setSearchCacheLimits(limits: string[])
	{
		if (Type.isArrayFilled(limits))
		{
			limits.forEach((limit: string) => {
				if (Type.isStringFilled(limit))
				{
					this.searchCacheLimits.push(new RegExp(limit, 'i'));
				}
			});
		}
	}

	getSearchCacheLimits(): RegExp[]
	{
		return this.searchCacheLimits;
	}

	hasDynamicLoad()
	{
		return this.dynamicLoad;
	}

	setDynamicLoad(flag: boolean): this
	{
		if (Type.isBoolean(flag))
		{
			this.dynamicLoad = flag;
		}

		return this;
	}

	hasDynamicSearch()
	{
		return this.dynamicSearch;
	}

	setDynamicSearch(flag: boolean): this
	{
		if (Type.isBoolean(flag))
		{
			this.dynamicSearch = flag;
		}

		return this;
	}

	toJSON()
	{
		return {
			id: this.getId(),
			options: this.getOptions(),
			searchable: this.isSearchable(),
			dynamicLoad: this.hasDynamicLoad(),
			dynamicSearch: this.hasDynamicSearch(),
		};
	}
}