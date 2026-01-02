import { Runtime } from 'main.core';
import { EventEmitter, type BaseEvent } from 'main.core.events';
import { createStore, type Store } from 'ui.vue3.vuex';
import { ChatType } from 'im.v2.const';

import { Core } from 'im.v2.application.core';
import { VoteModel } from 'vote.store.vote';
import { VotePullHandler } from 'vote.provider.pull';

const VOTES_COUNT_TO_LOAD = 50;

export class VoteApplication extends EventEmitter
{
	static instance: VoteApplication;
	store: Store;
	#visibleVotes: Array<number> = [];
	#shouldLoadVotes: boolean = true;
	#startIndexToLoadVotes: number = 0;

	static init(): VoteApplication
	{
		return VoteApplication.getInstance();
	}

	static getInstance(): VoteApplication
	{
		if (!VoteApplication.instance)
		{
			VoteApplication.instance = new VoteApplication();
		}

		return VoteApplication.instance;
	}

	constructor()
	{
		super();
		this.setEventNamespace('BX.VOTE.APPLICATION');
		this.createStore();
		this.initPull();
		this.#subscribeOnEvents();
	}

	#subscribeOnEvents(): void
	{
		EventEmitter.subscribe('vote-message-batch', this.#onMessageIsVisible.bind(this));
	}

	#onMessageIsVisible(event: BaseEvent): void
	{
		const { messageId } = event.getData();
		if (this.#visibleVotes.includes(messageId))
		{
			return;
		}

		this.#visibleVotes.push(messageId);
		if (!this.#shouldLoadVotes)
		{
			return;
		}

		Runtime.debounce(() => this.#prepareToLoadVotes())();
		this.#shouldLoadVotes = false;
	}

	handleLoadError(entityIds: Array<number>): void
	{
		this.#visibleVotes = this.#visibleVotes.filter((id) => !entityIds.includes(id));
		this.#startIndexToLoadVotes = 0;
	}

	#prepareToLoadVotes(): void
	{
		const entityIds = this.#visibleVotes.slice(this.#startIndexToLoadVotes, this.#visibleVotes.length);
		const requestCount = Math.ceil(entityIds.length / VOTES_COUNT_TO_LOAD);
		this.#startIndexToLoadVotes = this.#visibleVotes.length;
		this.#shouldLoadVotes = true;
		for (let i = 0; i < requestCount; i++)
		{
			const startIndex = i * VOTES_COUNT_TO_LOAD;
			const batchOfEntities = entityIds.slice(startIndex, Math.min(startIndex + VOTES_COUNT_TO_LOAD, entityIds.length));
			const voteIds = batchOfEntities.map((entityId) => {
				const { componentParams } = Core.getStore().getters['messages/getById'](entityId);

				return componentParams.id;
			});
			this.emit('loadVotes', { entityIds: batchOfEntities, voteIds });
		}
	}

	createStore(): void
	{
		this.store = createStore({
			modules: {
				vote: {
					namespaced: true,
					...VoteModel,
				},
			},
		});
	}

	getStore(): Store
	{
		return this.store;
	}

	initPull(): void
	{
		this.pullClient = BX.PULL;
		if (!this.pullClient)
		{
			return;
		}

		this.pullClient.subscribe(new VotePullHandler({ store: this.store }));
	}

	static canCreateVoteInChat(currentChatType: string): boolean
	{
		const availableChatTypes = [
			ChatType.chat,
			ChatType.open,
			ChatType.general,
			ChatType.call,
			ChatType.crm,
			ChatType.sonetGroup,
			ChatType.calendar,
			ChatType.tasks,
			ChatType.mail,
			ChatType.generalChannel,
			ChatType.channel,
			ChatType.openChannel,
			ChatType.collab,
		];

		return availableChatTypes.includes(currentChatType);
	}
}
