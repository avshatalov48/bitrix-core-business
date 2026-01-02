/* eslint-disable no-param-reassign */
import { Store } from '../vuex';
import { BuilderModel } from './model';

export class BuilderEntityModel<State, Item> extends BuilderModel
{
	constructor(): BuilderEntityModel
	{
		super();

		// eslint-disable-next-line no-constructor-return
		return new Proxy(this, {
			get: (target: BuilderEntityModel, property: string): function => {
				if (property in BuilderEntityModel.defaultModel)
				{
					return (): Object => ({
						...BuilderEntityModel.defaultModel[property](target),
						...target[property]?.(),
					});
				}

				return target[property];
			},
		});
	}

	static defaultModel = {
		getState: () => ({
			collection: {},
		}),
		getGetters: () => ({
			getAll: (state: State): Item[] => Object.values(state.collection),
			getIds: (state: State): number[] => Object.values(state.collection).map(({ id }) => id),
			getById: (state: State) => (id: number | string): Item => state.collection[id],
			getByIds: (state: State, { getAll }): Item[] => (ids: number[]): Item[] => {
				return getAll.filter((item: Item) => ids.includes(item.id));
			},
		}),
		getActions: () => ({
			insert: (store: Store, item: Item): void => {
				store.commit('insert', item);
			},
			insertMany: (store: Store, items: Item[]): void => {
				items.forEach((item: Item) => store.commit('insert', item));
			},
			upsert: (store: Store, item: Item): void => {
				store.commit('upsert', item);
			},
			upsertMany: (store: Store, items: Item[]): void => {
				items.forEach((item: Item) => store.commit('upsert', item));
			},
			update: (store: Store, payload: { id: number | string, fields: Item }): void => {
				store.commit('update', payload);
			},
			delete: (store: Store, id: number): void => {
				store.commit('delete', id);
			},
			deleteMany: (store: Store, ids: number[]): void => {
				ids.forEach((id: number) => store.commit('delete', id));
			},
		}),
		getMutations: (target: BuilderEntityModel) => ({
			insert: (state: State, item: ?Item): void => {
				if (item)
				{
					state.collection[item.id] ??= { ...target.getElementState?.(), ...item };
				}
			},
			upsert: (state: State, item: ?Item): void => {
				if (item)
				{
					state.collection[item.id] ??= target.getElementState?.();
					state.collection[item.id] = { ...state.collection[item.id], ...item };
				}
			},
			update: (state: State, { id, fields }: { id: number | string, fields: Item }): void => {
				const updatedItem = { ...state.collection[id], ...fields };
				delete state.collection[id];
				state.collection[fields.id ?? id] = updatedItem;
			},
			delete: (state: State, id: number): void => {
				delete state.collection[id];
			},
		}),
	};
}
