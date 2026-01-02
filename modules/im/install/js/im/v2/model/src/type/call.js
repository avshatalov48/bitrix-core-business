import type { ChatType } from 'im.v2.const';

export type Call = {
	id?: number,
	uuid: string,
	state: string,
	associatedEntity: CallAssociatedEntity
};

export type CallAssociatedEntity = {
	advanced: {
		chatType: string,
		entityType: $Values<typeof ChatType>,
		entityId: string,
		entityData1?: string,
		entityData2?: string,
		entityData3?: string,
	},
	id: string,
	chatId: number,
	name: string,
	avatar: string,
	avatarColor: string,
	type: $Values<typeof ChatType>,
	userCounter: number,
};
