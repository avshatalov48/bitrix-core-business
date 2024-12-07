import { EventEmitter } from 'main.core.events';
import { Type } from 'main.core';

export interface EntitySelectorOptions {
	options: Object,
	entitiesIdsEncoder?: (item: string | number) => { entityName: string; id: string | number },
	entitiesIdsDecoder?: (item: Object) => string | number,
	normalizeType?: (originalType: string) => string,
}

export default class EntitySelectorAdapter
{
	#options: EntitySelectorOptions;

	constructor(options: EntitySelectorOptions)
	{
		this.#options = options;
	}

	show(columnId: string, accessCodes: { [key: string]: string; }, targetNode: HTMLElement)
	{
		const preselectedItems = [];
		for (const code in accessCodes)
		{
			if (!Object.hasOwn(accessCodes, code))
			{
				continue;
			}

			const data = this.#encoderId(code);
			preselectedItems.push([data.entityName, data.id]);
		}

		const options = {
			...this.#options.options,
			targetNode,
			preselectedItems,
			events: {
				'Item:onSelect': (event) => {
					const item = event.data.item;
					this.#onItemSelect(item, columnId);
				},
				'Item:onDeselect': (event) => {
					const item = event.data.item;
					this.#onDeselect(item, columnId);
				},
			},
		};

		const dialog = new BX.UI.EntitySelector.Dialog(options);

		dialog.show();
	}

	#onItemSelect(item: Object, columnId: string): void
	{
		let id = item.id;
		const decoder = this.#options.entitiesIdsDecoder;
		if (Type.isFunction(decoder))
		{
			id = decoder(item);
		}

		let type = item.entityId;
		const normalizeType = this.#options.normalizeType;
		if (Type.isFunction(normalizeType))
		{
			type = normalizeType(item.entityId);
		}

		const option = {
			accessCodes: {
				[id]: type,
			},
			columnId,
			item: {
				id,
				entityId: item.id,
				name: item.title.text,
				avatar: item.avatar,
			},
		};

		EventEmitter.emit('BX.UI.AccessRights:addToAccessCodes', option);
	}

	#onDeselect(item: Object, columnId: string)
	{
		const id = this.#decodeId(item);
		const type = this.#normalizeType(item.entityId);

		const option = {
			accessCodes: {
				[id]: type,
			},
			columnId,
		};

		EventEmitter.emit('BX.UI.AccessRights:removeFromAccessCodes', option);
	}

	#normalizeType(type: string): string
	{
		const normalizeType = this.#options.normalizeType;
		if (Type.isFunction(normalizeType))
		{
			return normalizeType(type);
		}

		return type;
	}

	#decodeId(item: Object): string | number
	{
		const decoder = this.#options.entitiesIdsDecoder;
		if (Type.isFunction(decoder))
		{
			return decoder(item);
		}

		return item.id;
	}

	#encoderId(code: string | number): { entityName: string; id: string | number }
	{
		const encoder = this.#options.entitiesIdsEncoder;

		if (Type.isFunction(encoder))
		{
			return encoder(code);
		}

		return code;
	}
}
