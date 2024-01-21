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

export const EntityId = 'im-recent-v2';
const ContextId = 'IM_CHAT_SEARCH';
const SearchDialogId = 'search-experimental';

export const getSearchConfig = (findByParticipants: boolean = false): EntitySelectorRequestConfig => {
	const entity = {
		id: EntityId,
		dynamicLoad: true,
		dynamicSearch: true,
		options: {
			withChatByUsers: findByParticipants,
		},
	};

	return {
		dialog: {
			entities: [
				entity,
			],
			preselectedItems: [],
			clearUnavailableItems: false,
			context: ContextId,
			id: SearchDialogId,
		},
	};
};
