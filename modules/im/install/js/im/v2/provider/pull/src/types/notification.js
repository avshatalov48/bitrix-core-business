import type {RawUser} from './common';

export type NotifyAddParams = {
	counter: number,
	date: string,
	id: number,
	link: string,
	onlyFlash: boolean,
	originalTag: string, //"BLOG|COMMENT|2|3"
	original_tag: string,
	params: {
		CAN_ANSWER: 'Y' | 'N'
	},
	read: boolean | null,
	settingName: string, //"blog|comment"
	silent: 'Y' | 'N',
	tag: string, // "ab65eb17c7ff6d962122d9de3c622dc3"
	text: string,
	title: string,
	type: number,
	userAvatar: string,
	userColor: string,
	userId: number,
	userLink: string,
	userName: string,
	users: RawUser[]
};