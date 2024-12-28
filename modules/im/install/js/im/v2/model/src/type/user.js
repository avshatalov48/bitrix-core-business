import { BotType, UserType } from 'im.v2.const';

export type User = {
	id: number,
	name: string,
	firstName: string,
	lastName: string,
	avatar: string,
	color: string,
	workPosition: string,
	gender: 'M' | 'F',
	isAdmin: boolean,
	type: $Values<typeof UserType>,
	network: boolean,
	connector: boolean,
	externalAuthId: string,
	status: string,
	idle: boolean,
	lastActivityDate: false | Date,
	mobileLastDate: false | Date,
	birthday: string,
	isBirthday: boolean,
	absent: false | Date,
	isAbsent: boolean,
	departments: number[],
	phones: {
		personalMobile: string,
		workPhone: string,
		innerPhone: string,
	}
};

export type Bot = {
	code: string,
	type: $Values<typeof BotType>,
	appId: string,
	isHidden: boolean,
	isSupportOpenline: boolean,
	isHuman: boolean,
};
