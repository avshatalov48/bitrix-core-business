import {Type} from 'main.core';
import {BuilderModel} from 'ui.vue3.vuex';

import {PlacementType} from 'im.v2.const';

import type {MarketApplication} from '../type/market';

export class MarketModel extends BuilderModel
{
	getName(): string
	{
		return 'market';
	}

	getState()
	{
		return {
			collection: new Map(),
			placementCollection: {
				[PlacementType.contextMenu]: new Set(),
				[PlacementType.navigation]: new Set(),
				[PlacementType.textarea]: new Set(),
				[PlacementType.sidebar]: new Set(),
				[PlacementType.smilesSelector]: new Set()
			},
		};
	}

	getElementState(): MarketApplication
	{
		return {
			id: 0,
			title: '',
			options: {
				role: '',
				extranet: '',
				context: null,
				width: null,
				height: null,
				color: null,
				iconName: null
			},
			placement: '',
			order: 0,
			loadConfiguration: {
				ID: 0,
				PLACEMENT: '',
				PLACEMENT_ID: 0,
			},
		};
	}

	getGetters()
	{
		return {
			getByPlacement: (state) => (placement: string): MarketApplication[] =>
			{
				const appIds = [...state.placementCollection[placement].values()];

				return appIds.map(id => {
					return state.collection.get(id);
				});
			},
			getById: (state) => (id: number): number =>
			{
				return state.collection.get(id);
			},
		};
	}

	getActions()
	{
		return {
			set: (store, payload) =>
			{
				const {items} = payload;

				items.forEach((item: MarketApplication) => {
					store.commit('setPlacementCollection', {placement: item.placement, id: item.id});
					store.commit('setCollection', item);
				});
			},
		};
	}

	getMutations()
	{
		return {
			setPlacementCollection: (state, payload: MarketApplication) =>
			{
				state.placementCollection[payload.placement].add(payload.id);
			},
			setCollection: (state, payload: MarketApplication) =>
			{
				state.collection.set(payload.id, {...this.getElementState(), ...this.#validate(payload)});
			},
		};
	}

	#validate(app: Object): MarketApplication
	{
		const result = {};

		if (Type.isNumber(app.id) || Type.isStringFilled(app.id))
		{
			result.id = app.id.toString();
		}

		if (Type.isString(app.title))
		{
			result.title = app.title;
		}

		result.options = this.#validateOptions(app.options);

		if (Type.isString(app.placement))
		{
			result.placement = app.placement;
		}

		if (Type.isNumber(app.order))
		{
			result.order = app.order;
		}

		result.loadConfiguration = this.#validateLoadConfiguration(app.loadConfiguration);

		return result;
	}

	#validateOptions(options: Object)
	{
		const result = {
			context: null,
			width: null,
			height: null,
			color: null,
			iconName: null
		};

		if (!Type.isPlainObject(options))
		{
			return result;
		}

		if (Type.isArrayFilled(options.context))
		{
			result.context = options.context;
		}
		if (Type.isNumber(options.width))
		{
			result.width = options.width;
		}

		if (Type.isNumber(options.height))
		{
			result.height = options.height;
		}

		if (Type.isStringFilled(options.color))
		{
			result.color = options.color;
		}

		if (Type.isStringFilled(options.iconName))
		{
			result.iconName = options.iconName;
		}

		return result;
	}

	#validateLoadConfiguration(configuration: Object)
	{
		const result = {
			ID: 0,
			PLACEMENT: '',
			PLACEMENT_ID: 0,
		};

		if (!Type.isPlainObject(configuration))
		{
			return result;
		}

		if (Type.isNumber(configuration.ID))
		{
			result.ID = configuration.ID;
		}

		if (Type.isStringFilled(configuration.PLACEMENT))
		{
			result.PLACEMENT = configuration.PLACEMENT;
		}

		if (Type.isNumber(configuration.PLACEMENT_ID))
		{
			result.PLACEMENT_ID = configuration.PLACEMENT_ID;
		}

		return result;
	}
}