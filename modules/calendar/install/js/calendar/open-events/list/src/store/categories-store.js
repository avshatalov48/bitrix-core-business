import { CategoryModel } from '../model/category/category';

export const CategoriesStore = {
	state(): Object
	{
		return {
			selectedCategoryId: 0,
			categories: [],
		};
	},
	actions:
	{
		setCategories: (store, categories: CategoryModel[]) => {
			store.commit('setCategories', categories);
		},
		selectCategory: (store, categoryId) => {
			store.commit('selectCategory', categoryId);
		},
	},
	mutations:
	{
		setCategories: (state, categories: CategoryModel[]) => {
			state.categories = categories;
		},
		selectCategory: (state, categoryId) => {
			state.selectedCategoryId = categoryId;
		},
	},
	getters:
	{
		categories: (state): CategoryModel[] => state.categories.map((category) => {
			category.isSelected = category.id === state.selectedCategoryId;

			return category;
		}),
		selectedCategory: (state): CategoryModel => state.categories.find((it) => it.id === state.selectedCategoryId),
		selectedCategoryId: (state): number => state.selectedCategoryId,
	},
};