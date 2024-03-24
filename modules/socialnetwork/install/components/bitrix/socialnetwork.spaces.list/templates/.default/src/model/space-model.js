import type { AvatarModel } from './avatar-model';
import type { RecentActivityModel } from './recent-activity-model';

export type SpaceModel = {
	id: number,
	name: string,
	isPinned: boolean,
	isSelected: boolean,
	avatar: AvatarModel,
	recentActivity: RecentActivityModel,
	visibilityType: string,
	counter: number,
	lastSearchDate: Date,
	lastSearchDateTimestamp: number,
	userRole: string,
	follow: boolean,
	theme: any,
	permissions: Permissions,
};

export type Permissions = {
	canLeave: boolean,
}
