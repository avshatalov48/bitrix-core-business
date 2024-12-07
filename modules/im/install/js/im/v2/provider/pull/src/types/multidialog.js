import type { ImModelSidebarMultidialogItem } from 'im.v2.model';
import type { RawChat, RawUser } from './common';

export type ChangeMultidialogStatusParams = {
	multidialog: ImModelSidebarMultidialogItem,
	chat?: RawChat,
	bot?: RawUser,
};

export type ChangeMultidialogSessionsLimitParams = {
	botId: number,
	limit: number,
};

export type AddMultidialogParams = {
	multidialog: ImModelSidebarMultidialogItem,
	chat: RawChat,
	count: number,
};
