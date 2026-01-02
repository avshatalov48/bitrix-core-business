import type { RawUser } from './common';

type RawBotLegacyData = {
	id: string,
	code: string,
	type: string,
	openline: boolean,
	backgroundId: string,
}

export type BotAddParams = {
	bot: RawBotLegacyData,
	user: RawUser,
	userInGroup: [],
};

export type BotUpdateParams = BotAddParams;
