import { Type, ajax } from 'main.core';
import { UI } from 'ui.notification';
import { type BaseEvent } from 'main.core.events';

import { VoteApplication } from 'vote.application';
import { type BackendVote } from './type';

export const BackendModuleId = 'im';
export const BackendEntityType = 'Bitrix\\Vote\\Attachment\\ImMessageConnector';

export type EntityAuthParams = {
	moduleId: string,
	entityType: string,
};

export class ImVoteService
{
	static instance: ImVoteService;
	#app: VoteApplication = null;

	static init(): ImVoteService
	{
		return ImVoteService.getInstance();
	}

	static getInstance(): ImVoteService
	{
		if (!ImVoteService.instance)
		{
			ImVoteService.instance = new ImVoteService();
		}

		return ImVoteService.instance;
	}

	constructor()
	{
		this.#app = VoteApplication.init();
		this.#app.subscribe('loadVotes', ({ data }: BaseEvent) => {
			const { entityIds, voteIds } = data;
			this.#load(entityIds, voteIds);
		});
	}

	async #load(entityIds: Array<number>, voteIds: Array<string>): Promise<void>
	{
		try
		{
			const response = await this.#getManyVotes(entityIds);
			if (!response?.data?.items)
			{
				this.#setLoading(voteIds, false);

				return;
			}

			response.data.items.forEach((item) => {
				this.#updateStore(item);
			});
		}
		catch (ex)
		{
			this.#app.handleLoadError(entityIds);
			this.#notifyAjaxError(ex);
			this.#setLoading(voteIds, false);
		}
	}

	#notifyAjaxError(ex): void
	{
		if (Type.isObject(ex) && Type.isArrayFilled(ex.errors))
		{
			const content = ex?.errors[0]?.message ?? 'Unexpected error';
			UI.Notification.Center.notify({
				content,
				autoHideDelay: 4000,
			});
		}
		else
		{
			console.error(ex);
		}
	}

	#getManyVotes(entityIds: Array<number>): Promise
	{
		return ajax.runAction('vote.AttachedVote.getMany', {
			data: {
				...this.#getEntityParams(),
				entityIds,
			},
		});
	}

	#setLoading(voteIds: Array<string>, isLoading: boolean): void
	{
		voteIds.forEach((voteId) => {
			this.#app.getStore().dispatch('vote/setLoadingStatus', {
				isLoading,
				voteId,
			});
		});
	}

	async sendVote(ballot: Record<number, number[]>, voteId: string, entityId: number): Promise<void>
	{
		this.#setLoading([voteId], true);
		try
		{
			const response = await this.#sendBackendVote(ballot, entityId);
			if (!response?.data?.attach)
			{
				this.#setLoading([voteId], false);

				return;
			}

			this.#updateStore(response.data.attach);
		}
		catch (ex)
		{
			this.#setLoading([voteId], false);
			throw ex;
		}
	}

	async revokeVote(entityId: number, voteId: string): Promise<boolean>
	{
		this.#setLoading([voteId], true);
		try
		{
			const response = await this.#sendVoteRevokeRequest(entityId);
			this.#updateStore(response?.data?.attach);

			return true;
		}
		catch (response)
		{
			this.#setLoading([voteId], false);
			console.error(response.errors[0].code);
			throw response;
		}
	}

	completeVote(entityId: number): Promise<boolean>
	{
		return new Promise((resolve, reject) => {
			this.#sendVoteStopRequest(entityId)
				.then(() => {
					resolve(true);
				})
				.catch((response) => {
					console.error(response.errors[0].code);
					reject(response);
				});
		});
	}

	#sendVoteStopRequest(entityId: number): Promise<void>
	{
		return ajax.runAction('vote.AttachedVote.stop', {
			data: {
				...this.#getEntityParams(),
				entityId,
			},
		});
	}

	#sendVoteRevokeRequest(entityId: number): Promise
	{
		return ajax.runAction('vote.AttachedVote.recall', {
			data: {
				...this.#getEntityParams(),
				entityId,
			},
		});
	}

	#sendBackendVote(ballot: Record<number, number[]>, entityId: number): Promise
	{
		return ajax.runAction('vote.AttachedVote.vote', {
			data: {
				...this.#getEntityParams(),
				entityId,
				ballot,
			},
		});
	}

	#getEntityParams(): EntityAuthParams
	{
		return {
			moduleId: BackendModuleId,
			entityType: BackendEntityType,
		};
	}

	#updateStore(payload: ?BackendVote): void
	{
		if (!payload)
		{
			return;
		}

		this.#app.getStore().dispatch('vote/setCurrentUserVotes', payload.userAnswerMap);
		this.#app.getStore().dispatch('vote/addVote', payload);
		this.#app.getStore().dispatch('vote/addQuestion', payload.QUESTIONS);
		this.#app.getStore().dispatch('vote/addAnswer', payload.QUESTIONS);
	}
}
