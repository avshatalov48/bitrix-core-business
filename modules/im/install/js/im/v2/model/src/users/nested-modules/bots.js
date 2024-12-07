import { BotCode, BotType, RawBotType } from 'im.v2.const';
import { BuilderModel, type GetterTree, type ActionTree, type MutationTree } from 'ui.vue3.vuex';

import { convertObjectKeysToCamelCase } from '../../utils/format';

import type { JsonObject } from 'main.core';
import type { ImModelBot } from 'im.v2.model';

type BotsState = {
	collection: {[dialogId: string]: ImModelBot},
};

type BotPayload = {
	userId: string | number,
	botData: ImModelBot,
};

export class BotsModel extends BuilderModel
{
	getState(): BotsState
	{
		return {
			collection: {},
		};
	}

	getElementState(): ImModelBot
	{
		return {
			code: '',
			type: BotType.bot,
			appId: '',
			isHidden: false,
			isSupportOpenline: false,
			isHuman: false,
		};
	}

	getGetters(): GetterTree
	{
		return {
			/** @function users/bots/getByUserId */
			getByUserId: (state: BotsState) => (userId: string | number): ?ImModelBot => {
				return state.collection[userId];
			},
			/** @function users/bots/isNetwork */
			isNetwork: (state: BotsState) => (userId: string | number): boolean => {
				return state.collection[userId]?.type === BotType.network;
			},
			/** @function users/bots/isSupport */
			isSupport: (state: BotsState) => (userId: string | number): boolean => {
				return state.collection[userId]?.type === BotType.support24;
			},
			/** @function users/bots/getCopilotUserId */
			getCopilotUserId: (state: BotsState): ?number => {
				for (const [userId, bot] of Object.entries(state.collection))
				{
					if (bot.code === BotCode.copilot)
					{
						return Number.parseInt(userId, 10);
					}
				}

				return null;
			},
			/** @function users/bots/isCopilot */
			isCopilot: (state: BotsState, getters) => (userId: number | string): boolean => {
				const copilotUserId = getters.getCopilotUserId;

				return copilotUserId === Number.parseInt(userId, 10);
			},
		};
	}

	getActions(): ActionTree
	{
		return {
			/** @function users/bots/set */
			set: (store, payload: BotPayload) => {
				const { userId, botData } = payload;
				if (!botData)
				{
					return;
				}
				store.commit('set', {
					userId,
					botData: { ...this.getElementState(), ...this.formatFields(botData) },
				});
			},
		};
	}

	getMutations(): MutationTree
	{
		return {
			set: (state: BotsState, payload: BotPayload) => {
				const { userId, botData } = payload;
				// eslint-disable-next-line no-param-reassign
				state.collection[userId] = botData;
			},
		};
	}

	formatFields(fields: JsonObject): ImModelBot
	{
		const result: ImModelBot = convertObjectKeysToCamelCase(fields);
		if (result.type === RawBotType.human)
		{
			result.type = BotType.bot;
			result.isHuman = true;
		}

		const TYPES_MAPPED_TO_DEFAULT_BOT = [RawBotType.openline, RawBotType.supervisor];
		if (TYPES_MAPPED_TO_DEFAULT_BOT.includes(result.type))
		{
			result.type = BotType.bot;
		}

		return result;
	}
}
