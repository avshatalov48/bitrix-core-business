import { Type } from 'main.core';
import { EventEmitter, BaseEvent } from 'main.core.events';
import { CategoryModel } from '../../model/category/category';
import { PullRequests } from './pull-requests';
import { CategoryApi } from '../../api/category-api';
import type { ListParams } from '../../api/category-api';

const ListKeys = Object.freeze({
	notBanned: 'notBanned',
	banned: 'banned',
	search: 'search',
});

class Manager extends EventEmitter
{
	#categories: CategoryModel[] = [];
	#categoryIds: {[string]: number[]} = {};
	#categoryPromises: {[string]: {[number]: Promise}} = {};
	#lastLoadedPage: {[string]: number} = {};
	#loadedLists = {
		[ListKeys.notBanned]: false,
		[ListKeys.banned]: false,
		[ListKeys.search]: false,
	};
	#query: string;

	constructor()
	{
		super();

		this.setEventNamespace('Calendar.OpenEvents.List.CategoryManager');

		this.#subscribeToPull();
	}

	#subscribeToPull(): void
	{
		if (!BX.PULL)
		{
			console.info('BX.PULL not initialized');

			return;
		}

		const pullRequests = new PullRequests();
		pullRequests.subscribe('create', this.#createCategoryPull.bind(this));
		pullRequests.subscribe('update', this.#updateCategoryPull.bind(this));
		pullRequests.subscribe('delete', this.#deleteCategoryPull.bind(this));
		pullRequests.subscribe('eventScorerUpdated', this.#onPullEventScorerUpdated.bind(this));

		BX.PULL.subscribe(pullRequests);
	}

	#createCategoryPull(event: BaseEvent): void
	{
		const { fields } = event.getData();

		if (this.#getCategory(fields.id))
		{
			return;
		}

		this.#addNewCategory(fields);
	}

	#updateCategoryPull(event: BaseEvent): void
	{
		const { fields } = event.getData();

		this.#updateCategory(fields.id, fields);
	}

	#deleteCategoryPull(event: BaseEvent): void
	{
		const { fields } = event.getData();

		this.#categories = this.#categories.filter((category) => category.id !== fields.id)

		this.emit('update');
	}

	#onPullEventScorerUpdated(event: BaseEvent): void
	{
		const { fields: { categoriesCounter } } = event.getData();

		this.#updateCounters(categoriesCounter);
	}

	async addCategory(fields: CreateCategoryDto): Promise<void>
	{
		const categoryDto = await CategoryApi.add(fields);

		this.#addNewCategory(categoryDto);
	}

	#addNewCategory(categoryDto: CategoryDto): void
	{
		categoryDto.updatedAt = Date.now();

		const category = new CategoryModel(categoryDto);

		this.#categories.push(category);
		this.#categoryIds[ListKeys.notBanned]?.push(category.id);

		this.emit('update');
	}

	async updateCategory(fields: UpdateCategoryDto): Promise<void>
	{
		const category = this.#getCategory(fields.id);

		category.channel.title = fields.name;

		return CategoryApi.update(fields);
	}

	async setMute(categoryId: number, isMuted: boolean): Promise<void>
	{
		this.#updateCategory(categoryId, { isMuted });

		void CategoryApi.setMute(categoryId, isMuted);
	}

	async setBan(categoryId: number, isBanned: boolean): Promise<void>
	{
		this.#updateCategory(categoryId, { isBanned });

		void CategoryApi.setBan(categoryId, isBanned);
	}

	async getChannelInfo(categoryId: number): Promise<ChannelInfo>
	{
		const category = this.#categories.find((category) => category.id === categoryId);

		category.channel ??= await CategoryApi.getChannelInfo(categoryId);

		return category.channel;
	}

	async bubbleUp(categoryId: number): void
	{
		const category = this.#getCategory(categoryId) ?? await this.#loadCategoryById(categoryId);

		this.#updateCategory(category.id, {
			updatedAt: Date.now(),
		});
	}

	async searchMore(): Promise<CategoryModel[]>
	{
		if (this.#loadedLists[ListKeys.search])
		{
			return [];
		}

		const query = this.#query;

		const listKey = this.#getListKey({ query });

		const countBefore = this.#getListIds(listKey).length;

		const lastPage = this.#lastLoadedPage[listKey] ?? -1;
		const categories = await this.getCategories({
			query,
			page: lastPage + 1,
		});

		if (categories.length === countBefore)
		{
			this.#loadedLists[listKey] = true;
		}

		return categories;
	}

	async loadMore(): Promise<CategoryModel[]>
	{
		if (this.#loadedLists[ListKeys.notBanned] && this.#loadedLists[ListKeys.banned])
		{
			return [];
		}

		const isBanned = this.#loadedLists[ListKeys.notBanned] && !this.#loadedLists[ListKeys.banned];

		const listKey = this.#getListKey({ isBanned });

		const countBefore = this.#getListIds(listKey).length;

		const lastPage = this.#lastLoadedPage[listKey] ?? -1;
		const categories = await this.getCategories({
			isBanned,
			page: lastPage + 1,
		});

		if (categories.length === countBefore)
		{
			this.#loadedLists[listKey] = true;

			return this.loadMore();
		}

		return categories;
	}

	async searchCategories(query: string): Promise<CategoryModel[]>
	{
		if (query !== this.#query)
		{
			this.#loadedLists[ListKeys.search] = false;
			delete this.#lastLoadedPage[ListKeys.search];
			delete this.#categoryPromises[ListKeys.search];
			delete this.#categoryIds[ListKeys.search];
		}

		this.#query = query;

		return this.getCategories({ query });
	}

	async getCategories(params: ListParams = { isBanned: false }): Promise<CategoryModel[]>
	{
		const listKey = this.#getListKey(params);

		const categories = await this.#loadCategories(params);

		const alreadyLoadedIds = this.#categories.map((it) => it.id);
		const newCategories = categories.filter((it) => !alreadyLoadedIds.includes(it.id));

		this.#categories.push(...newCategories);

		const alreadyLoadedListIds = this.#getListIds(listKey);
		const newListCategories = categories.filter((it) => !alreadyLoadedListIds.includes(it.id));

		this.#categoryIds[listKey] ??= [];
		this.#categoryIds[listKey].push(...newListCategories.map((it) => it.id));

		return this.#prepareCategories(listKey);
	}

	#prepareCategories(listKey: string): CategoryModel[]
	{
		const listIds = this.#getListIds(listKey);

		return this.#categories
			.filter((category) => listIds.includes(category.id))
			.map((category) => new CategoryModel(category.fields))
		;
	}

	#getListIds(listKey: string): number[]
	{
		const listKeys = listKey === ListKeys.search ? [ListKeys.search] : [ListKeys.notBanned, ListKeys.banned];

		const listIds = Object.entries(this.#categoryIds)
			.filter(([listKey]) => listKeys.includes(listKey))
			.flatMap(([, categoryIds]) => categoryIds)
		;

		return [...new Set(listIds)];
	}

	async #loadCategories(params: ListParams): Promise<CategoryModel[]>
	{
		const isBanned = params.isBanned ?? null;
		const query = params.query ?? '';
		const page = params.page ?? 0;

		const listKey = this.#getListKey(params);

		this.#categoryPromises[listKey] ??= {};
		this.#categoryPromises[listKey][page] ??= CategoryApi.list({ isBanned, query, page });

		const categories: CategoryDto[] = await this.#categoryPromises[listKey][page];

		this.#lastLoadedPage[listKey] = page;

		return categories.map((category) => new CategoryModel(category));
	}

	#getListKey({ isBanned, query }: ListParams): string
	{
		if (Type.isStringFilled(query))
		{
			return ListKeys.search;
		}

		if (isBanned === true)
		{
			return ListKeys.banned;
		}

		return ListKeys.notBanned;
	}

	async #loadCategoryById(categoryId: number): Promise<CategoryModel>
	{
		const promiseByIdKey = 'byId';

		this.#categoryPromises[promiseByIdKey] ??= {};
		this.#categoryPromises[promiseByIdKey][categoryId] ??= CategoryApi.list({ categoryId });

		const categories: CategoryDto[] = await this.#categoryPromises[promiseByIdKey][categoryId];
		const categoryDto: CategoryDto = categories.find((it) => it.id === categoryId);

		const category = new CategoryModel(categoryDto);

		this.#categories.push(category);

		const listKey = category.isBanned ? ListKeys.banned : ListKeys.notBanned;

		this.#categoryIds[listKey]?.push(category.id);

		return category;
	}

	#updateCounters(categoryCounters: any): void
	{
		for (const [id, newCount] of Object.entries(categoryCounters))
		{
			const categoryId = parseInt(id, 10);
			const category = this.#getCategory(categoryId);
			if (category === null)
			{
				continue;
			}

			const eventsCreated = newCount > category.newCount;
			const updatedAt = eventsCreated ? Date.now() : category.updatedAt;

			this.#updateCategory(categoryId, { newCount, updatedAt });
		}
	}

	incrementNewCounter(categoryId: number): void
	{
		const category = this.#getCategory(categoryId);
		if (category === null)
		{
			return;
		}

		this.#updateCategory(categoryId, {
			newCount: category.newCount + 1,
		});
	}

	decrementNewCounter(categoryId: number): void
	{
		const category = this.#getCategory(categoryId);
		if (category === null)
		{
			return;
		}

		this.#updateCategory(categoryId, {
			newCount: category.newCount - 1,
		});
	}

	#getCategory(categoryId: number): ?CategoryModel
	{
		return this.#categories.find((category: CategoryModel) => category.id === categoryId) ?? null;
	}

	#updateCategory(categoryId: number, fields: CategoryDto): void
	{
		this.#categories = this.#categories.map((category: CategoryModel) => {
			if (category.id !== categoryId)
			{
				return category;
			}

			return this.#buildCategoryModel(category, fields);
		});

		this.emit('update');
	}

	#buildCategoryModel(category: CategoryModel, fields: CategoryDto = {}): CategoryModel
	{
		return new CategoryModel({
			id: category.id,
			closed: fields.closed ?? category.closed,
			name: fields.name ?? category.name,
			description: fields.description ?? category.description,
			eventsCount: fields.eventsCount ?? category.eventsCount,
			permissions: category.permissions,
			channelId: category.channelId,
			isMuted: fields.isMuted ?? category.isMuted,
			isBanned: fields.isBanned ?? category.isBanned,
			newCount: fields.newCount ?? category.newCount,
			isSelected: fields.isSelected ?? category.isSelected,
			updatedAt: fields.updatedAt ?? category.updatedAt,
			channel: fields.channel ?? category.channel,
		});
	}
}

export const CategoryManager = new Manager();
