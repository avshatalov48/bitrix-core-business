import { mapGetters } from 'ui.vue3.vuex';
import { CategoryManager } from '../../data-manager/category-manager/category-manager';
import type { CategoryModel } from '../../model/category/category';
import { Category } from './category';

export const CategoryList = {
	computed: {
		...mapGetters({
			categories: 'categories',
			isSearchMode: 'isSearchMode',
			categoriesQuery: 'categoriesQuery',
		}),
		allCategory(): CategoryModel
		{
			return this.categories.find((category) => category.id === 0);
		},
		sortedCategories(): CategoryModel[]
		{
			return [...this.categories]
				.filter((category) => category.id > 0)
				.sort((a: CategoryModel, b: CategoryModel) => {
					if (a.isBanned !== b.isBanned)
					{
						return a.isBanned - b.isBanned;
					}

					return b.updatedAt - a.updatedAt;
				})
			;
		},
	},
	mounted(): void
	{
		void this.loadOnScroll();
		this.$refs.categoryList.addEventListener('scroll', this.loadOnScroll);
		CategoryManager.subscribe('update', this.onCategoriesUpdatedHandler);
	},
	beforeUnmount(): void
	{
		this.$refs.categoryList.removeEventListener('scroll', this.loadOnScroll);
		CategoryManager.unsubscribe('update', this.onCategoriesUpdatedHandler);
	},
	watch: {
		categories()
		{
			void this.$nextTick(() => this.loadOnScroll());
		},
	},
	methods: {
		async onCategoriesUpdatedHandler(): Promise<void>
		{
			const categories = await this.getCategories();

			this.$store.dispatch('setCategories', categories);
		},
		async loadOnScroll(): Promise
		{
			const scrollTop = this.$refs.categoryList.scrollTop;
			const scrollHeight = this.$refs.categoryList.scrollHeight;
			const offsetHeight = this.$refs.categoryList.offsetHeight;

			if (scrollTop + 1 >= scrollHeight - offsetHeight)
			{
				const categories = await this.loadMore();

				if (categories.length > 0)
				{
					this.$store.dispatch('setCategories', categories);
				}
			}
		},
		getCategories(): Promise<CategoryModel[]>
		{
			if (this.isSearchMode)
			{
				return CategoryManager.searchCategories(this.categoriesQuery);
			}

			return CategoryManager.getCategories();
		},
		loadMore(): Promise<CategoryModel[]>
		{
			if (this.isSearchMode)
			{
				return CategoryManager.searchMore();
			}

			return CategoryManager.loadMore();
		},
	},
	components: {
		Category,
	},
	template: `
		<div class="calendar-open-events-list-category-list --calendar-scroll-bar" ref="categoryList">
			<Category :category="allCategory" v-show="!isSearchMode"/>
			<Category v-for="category of sortedCategories" :category="category"/>
		</div>
	`,
};
