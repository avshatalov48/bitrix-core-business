export const CategoriesSearchStore = {
	state(): Object
	{
		return {
			isSearchMode: false,
			categoriesQuery: '',
		};
	},
	actions:
	{
		setSearchMode: (store, isSearchMode: boolean) => {
			store.commit('setSearchMode', isSearchMode);
		},
		setCategoriesQuery: (store, categoriesQuery: string) => {
			store.commit('setCategoriesQuery', categoriesQuery);
		},
	},
	mutations:
	{
		setSearchMode: (state, isSearchMode: boolean) => {
			state.isSearchMode = isSearchMode;
		},
		setCategoriesQuery: (state, categoriesQuery: string) => {
			state.categoriesQuery = categoriesQuery;
		},
	},
	getters:
	{
		isSearchMode: (state): boolean => state.isSearchMode,
		categoriesQuery: (state): string => state.categoriesQuery,
	},
};
