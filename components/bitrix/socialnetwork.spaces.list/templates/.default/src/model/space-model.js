import type { AvatarModel } from './avatar-model';

export type SpaceModel = {
	id: number,
	name: string,
	isPinned: boolean,
	dateActivity: Date,
	dateActivityTimestamp: number,
	lastActivityDescription: string,
	isSelected: boolean,
	avatar: AvatarModel,
	visibilityType: string,
	counter: number,
	lastSearchDate: Date,
	lastSearchDateTimestamp: number,
	userRole: string,
	follow: boolean,
};