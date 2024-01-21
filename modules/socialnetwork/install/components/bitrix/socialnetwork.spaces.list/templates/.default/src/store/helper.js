import type { SpaceModel } from '../model/space-model';
import type { InvitationModel } from '../model/invitation-model';
import { RecentService } from '../api/load/recent-service';
import { Modes } from '../const/mode';
import { FilterModeTypes } from '../const/filter-mode';
import { SpaceUserRoles } from '../const/space';

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
			dateActivity: new Date(parseInt(spaceData.dateActivityTimestamp, 10) * 1000),
			dateActivityTimestamp: spaceData.dateActivityTimestamp * 1000,
			lastActivityDescription: spaceData.lastActivityDescription,
			avatar: spaceData.avatar,
			visibilityType: spaceData.visibilityType,
			counter: parseInt(spaceData.counter, 10),
			lastSearchDate: new Date(parseInt(spaceData.lastSearchDateTimestamp, 10) * 1000),
			lastSearchDateTimestamp: spaceData.lastSearchDateTimestamp * 1000,
			userRole: spaceData.userRole,
			follow: spaceData.follow,
		}));
	}

	buildInvitations(invitations): InvitationModel[]
	{
		return invitations.map((invitationData) => ({
			spaceId: parseInt(invitationData.spaceId, 10),
			message: invitationData.message,
			invitationDateTimestamp: invitationData.invitationDateTimestamp * 1000,
			invitationDate: new Date(parseInt(invitationData.invitationDateTimestamp, 10) * 1000),
		}));
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