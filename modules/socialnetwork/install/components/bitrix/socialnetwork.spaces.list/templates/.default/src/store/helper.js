import { RecentService } from '../api/load/recent-service';
import { Modes } from '../const/mode';
import { FilterModeTypes } from '../const/filter-mode';
import { SpaceUserRoles } from '../const/space';

import type { SpaceModel } from '../model/space-model';
import type { InvitationModel } from '../model/invitation-model';
import type { RecentActivityModel } from '../model/recent-activity-model';

export class Helper
{
	static instance = null;
	static getInstance(): Helper
	{
		if (!this.instance)
		{
			this.instance = new this();
		}

		return this.instance;
	}

	buildSpaces(spaces): SpaceModel[]
	{
		return spaces.map((spaceData) => ({
			id: parseInt(spaceData.id, 10),
			name: spaceData.name,
			isPinned: spaceData.isPinned,
			isSelected: RecentService.getInstance().getSelectedSpaceId() === parseInt(spaceData.id, 10),
			recentActivity: this.buildRecentActivity(spaceData.recentActivityData),
			avatar: spaceData.avatar,
			visibilityType: spaceData.visibilityType,
			counter: parseInt(spaceData.counter, 10),
			lastSearchDate: new Date(this.convertTimestampFromPhp(spaceData.lastSearchDateTimestamp)),
			lastSearchDateTimestamp: spaceData.lastSearchDateTimestamp * 1000,
			userRole: spaceData.userRole,
			follow: spaceData.follow,
			theme: [],
			permissions: spaceData.permissions,
		}));
	}

	convertTimestampFromPhp(timestamp: number | string): number
	{
		return parseInt(timestamp, 10) * 1000;
	}

	buildInvitations(invitations): InvitationModel[]
	{
		return invitations.map((invitationData) => ({
			spaceId: parseInt(invitationData.spaceId, 10),
			message: invitationData.message,
			invitationDateTimestamp: this.convertTimestampFromPhp(invitationData.invitationDateTimestamp),
			invitationDate: new Date(this.convertTimestampFromPhp(invitationData.invitationDateTimestamp)),
		}));
	}

	buildRecentActivity(recentActivityData): RecentActivityModel
	{
		const recentActivity: RecentActivityModel = {};

		recentActivity.description = recentActivityData.description;
		recentActivity.typeId = recentActivityData.typeId;
		recentActivity.entityId = parseInt(recentActivityData.entityId, 10);
		recentActivity.timestamp = this.convertTimestampFromPhp(recentActivityData.timestamp);
		recentActivity.date = new Date(recentActivity.timestamp);
		recentActivity.secondaryEntityId = recentActivityData.secondaryEntityId;

		return recentActivity;
	}

	getStringCapitalized(string: string): string
	{
		return string[0].toUpperCase() + string.slice(1);
	}

	getModelNameByListViewMode(mode: string): string
	{
		let result = `${mode}ListSpaceIds`;
		if (mode === Modes.search)
		{
			result = 'searchResultFromServerSpaceIds';
		}

		return result;
	}

	doAddSpaceToRecentList(space: SpaceModel, lastRecentSpace: SpaceModel, filterMode: string): boolean
	{
		const doDateActivityFits = lastRecentSpace.recentActivity.date < space.recentActivity.date;
		const doUserRoleFitsFilterMode = this.doSpaceUserRoleFitsFilterMode(space.userRole, filterMode);

		return (doDateActivityFits && doUserRoleFitsFilterMode) || space.id === 0;
	}

	doSpaceUserRoleFitsFilterMode(userRole: string, filterMode: string): boolean
	{
		if ([SpaceUserRoles.nonMember, SpaceUserRoles.applicant].includes(userRole) && filterMode === FilterModeTypes.my)
		{
			return false;
		}

		if (userRole === SpaceUserRoles.member && filterMode === FilterModeTypes.other)
		{
			return false;
		}

		return userRole !== SpaceUserRoles.invited;
	}
}
