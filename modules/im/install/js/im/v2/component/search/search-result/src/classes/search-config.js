import {Type, Extension} from 'main.core';

import {Core} from 'im.v2.application.core';

import {SearchItem} from './search-item';

export type SearchConfig = {
	currentUser: boolean,
	excludeUsers: [],
	extranet: boolean,
	chats: boolean,
	bots: boolean,
	departments: boolean,
	network: boolean,
};

type EntitySelectorProviderEntity = {
	id: string,
	options: Object,
	dynamicLoad: boolean,
	dynamicSearch: boolean,
};

type EntitySelectorRequestConfig = {
	dialog: {
		entities: EntitySelectorProviderEntity[],
		preselectedItems: [],
		clearUnavailableItems: boolean,
		context: string,
		id: string,
	}
};

const CHAT_TYPE_OPEN = 'O';
const CHAT_TYPE_PRIVATE = 'C';

export class Config
{
	#config: SearchConfig = {};
	#store: Object;
	#currentUserId: number;
	#settings: Object;
	#networkSearchButtonClicked: boolean = false;

	constructor(config: SearchConfig)
	{
		this.#config = {...this.#getDefaultConfig(), ...config};
		this.#store = Core.getStore();
		this.#settings = Extension.getSettings('im.v2.component.search.search-result');
		this.#currentUserId = Core.getUserId();
	}

	getRecentRequestConfig(): EntitySelectorRequestConfig
	{
		const entities = [this.#getUserEntity(), this.#getChatEntity(), this.#getBotsEntity(), this.#getChatUserEntity()];

		return this.#get(entities);
	}

	getSearch(): EntitySelectorRequestConfig
	{
		const entities = [this.#getUserEntity(), this.#getChatEntity(), this.#getBotsEntity(), this.#getChatUserEntity(),
			this.#getDepartmentEntity(), this.#getNetworkEntity()
		];

		return this.#get(entities);
	}

	getDepartmentUsers(): EntitySelectorRequestConfig
	{
		const entities = [this.#getUserEntity(), this.#getBotsEntity(), this.#getDepartmentEntity()];

		return this.#get(entities);
	}

	getNetwork(): EntitySelectorRequestConfig
	{
		const entities = [this.#getNetworkEntity()];

		return this.#get(entities);
	}

	enableNetworkSearch(): void
	{
		this.#networkSearchButtonClicked = true;
	}

	disableNetworkSearch(): void
	{
		this.#networkSearchButtonClicked = false;
	}

	isItemAllowed(item: SearchItem): boolean
	{
		if (item.isUser() && item.isExtranet() && !this.#needExtranet())
		{
			return false;
		}

		if (item.isNetworkType() && !this.#needNetwork())
		{
			return false;
		}

		if (item.isChat() && !this.#needChats())
		{
			return false;
		}

		if (item.isUser() && !this.#needCurrentUser() && item.getId() === this.#currentUserId)
		{
			return false;
		}

		return true;
	}

	isNetworkAvailable(): boolean
	{
		return this.#settings.get('isNetworkAvailable', false) && this.#config.network;
	}

	#get(entities: EntitySelectorProviderEntity[])
	{
		entities = entities.filter((entity: ?EntitySelectorProviderEntity) => !Type.isNil(entity));

		return {
			dialog: {
				entities: entities,
				preselectedItems: [],
				clearUnavailableItems: false,
				context: 'IM_CHAT_SEARCH',
				id: 'im-search',
			}
		};
	}

	#getBotsEntity(): ?EntitySelectorProviderEntity
	{
		if (!this.#needBots())
		{
			return null;
		}

		return {
			id: 'im-bot',
			options: {
				searchableBotTypes: [
					'H',
					'B',
					'S',
					'N',
				],
				fillDialogWithDefaultValues: false,
			},
			dynamicLoad: true,
			dynamicSearch: true,
		};
	}

	#getUserEntity(): EntitySelectorProviderEntity
	{
		return {
			id: 'user',
			dynamicLoad: true,
			dynamicSearch: true,
			filters: [
				{
					id: 'im.userDataFilter'
				}
			]
		};
	}

	#getChatUserEntity(): ?EntitySelectorProviderEntity
	{
		if (!this.#needChats())
		{
			return null;
		}

		return {
			id: 'im-chat-user',
			options: {
				searchableChatTypes: [CHAT_TYPE_OPEN, CHAT_TYPE_PRIVATE],
				fillDialogWithDefaultValues: false,
			},
			dynamicLoad: true,
			dynamicSearch: true,
		};
	}

	#getNetworkEntity(): ?EntitySelectorProviderEntity
	{
		if (!this.#needNetwork())
		{
			return;
		}

		return {
			id: 'imbot-network',
			dynamicSearch: true,
			options: {
				'filterExistingLines': true,
			}
		};
	}

	#getDepartmentEntity(): ?EntitySelectorProviderEntity
	{
		if (!this.#needDepartments)
		{
			return null;
		}

		return {
			id: 'department',
			dynamicLoad: true,
			dynamicSearch: true,
			options: {
				selectMode: 'usersAndDepartments',
				allowSelectRootDepartment: true,
			},
			filters: [
				{
					id: 'im.departmentDataFilter'
				}
			]
		};
	}

	#getChatEntity(): EntitySelectorProviderEntity
	{
		if (!this.#needChats())
		{
			return null;
		}

		return {
			id: 'im-chat',
			options: {
				searchableChatTypes: [CHAT_TYPE_PRIVATE, CHAT_TYPE_OPEN],
				fillDialogWithDefaultValues: false,
			},
			dynamicLoad: true,
			dynamicSearch: true,
		};
	}

	#needChats(): boolean
	{
		return this.#config.chats;
	}

	#needBots(): boolean
	{
		return this.#config.bots;
	}

	#needNetwork(): boolean
	{
		return this.#networkSearchButtonClicked && this.isNetworkAvailable();
	}

	#needDepartments(): boolean
	{
		if (this.#isCurrentUserExtranet() || !this.#isDepartmentsAvailable())
		{
			return false;
		}

		return this.#config.departments;
	}

	#isDepartmentsAvailable(): boolean
	{
		return this.#settings.get('isDepartmentsAvailable', false);
	}

	#needExtranet(): boolean
	{
		return this.#config.extranet;
	}

	#needCurrentUser(): boolean
	{
		return this.#config.currentUser;
	}

	#isCurrentUserExtranet(): boolean
	{
		const user = this.#store.getters['users/get'](this.#currentUserId, true);

		return user.extranet;
	}

	#getDefaultConfig(): SearchConfig
	{
		return {
			currentUser: true,
			excludeUsers: [],
			extranet: true,
			chats: true,
			bots: true,
			departments: true,
			network: true,
		};
	}
}