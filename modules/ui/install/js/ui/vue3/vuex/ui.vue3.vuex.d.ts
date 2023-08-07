declare module 'ui.vue3.vuex'
{
	class Store<S> {
		state: S;
		getters: any;

		dispatch: (type: string, payload?: any) => Promise<any>;
		commit: (type: string, payload?: any) => void;

		subscribe<P>(fn: (mutation: P, state: S) => any): () => void;
		subscribeAction<P>(fn): () => void;
		watch<T>(getter: (state: S, getters: any) => T, cb: (value: T, oldValue: T) => void): () => void;
	}

	class BuilderModel<S, R> {
		static create();
		getName(): string;
		getState(): S;
		getElementState(): any;
		getGetters(): GetterTree<S, R>;
		getActions(): ActionTree<S, R>;
		getMutations(): MutationTree<S>;
		getNestedModules(): NestedModuleTree<S, R>;
	}

	export interface GetterTree<S, R> {
		[key: string]: Getter<S, R>;
	}

	export interface ActionTree<S, R> {
		[key: string]: Action<S, R>;
	}

	export interface MutationTree<S> {
		[key: string]: Mutation<S>;
	}

	export interface NestedModuleTree<S, R> {
		[moduleName: string]: BuilderModel<S, R>
	}

	type Getter<S, R> = (state: S, getters: any, rootState: R, rootGetters: any) => any;
	type Action<S, R> = (this: Store<R>, payload?: any) => any;
	type Mutation<S> = (state: S, payload?: any) => any;
}