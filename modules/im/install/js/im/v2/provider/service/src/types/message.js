import {reactionType as Reaction} from 'ui.reactions-select';

export type ImRestMessageResult = {
	chat_id: number,
	files: ImRestFile[],
	messages: ImRestMessage[],
	users: ImRestUser[],
	usersShort: UserShort[],
	reactions: RawReactions[],
	hasPrevPage?: boolean,
	hasNextPage?: boolean
};

type UserShort = {
	id: number,
	name: string,
	avatar: string
};

type ReactionType = $Values<typeof Reaction>;

type RawReactions = {
	messageId: number,
	reactionCounters: {[reactionType: string]: number},
	reactionUsers: {[reactionType: string]: number[]},
	ownReactions?: ReactionType[]
};

export type ImRestMessage = {
	author_id: number,
	chat_id: number,
	date: string,
	id: number,
	params: Object,
	text: string,
	unread: boolean,
	uuid?: string
};

export type ImRestFile = {
	// TODO
};

export type ImRestUser = {
	id: number,
	absent: boolean,
	active: boolean,
	avatar: string,
	birthday: string,
	bot: boolean,
	color: string,
	connector: boolean,
	departments: number[],
	desktop_last_date: string,
	external_auth_id: string,
	extranet: boolean,
	first_name: string,
	gender: string,
	idle: boolean,
	last_activity_date: string,
	last_name: string,
	mobile_last_date: string,
	name: string,
	network: boolean,
	phones: boolean | Object,
	status: string,
	work_position: string
};