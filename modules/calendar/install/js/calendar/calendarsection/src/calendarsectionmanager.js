import {Entry} from "calendar.entry";
import {Util} from "calendar.util";
import {Loc, Type} from "main.core";

export class CalendarSectionManager {
	static newEntrySectionId = null;

	static getNewEntrySectionId()
	{
		return CalendarSectionManager.newEntrySectionId;
	}

	static setNewEntrySectionId(sectionId)
	{
		CalendarSectionManager.newEntrySectionId = parseInt(sectionId);
	}

	static getSectionGroupList(options = {})
	{
		let
			type = options.type,
			ownerId = options.ownerId,
			userId = options.userId,
			followedUserList = options.trackingUsersList || Util.getFollowedUserList(userId),
			sectionGroups = [],
			title;

		// 1. Main group - depends from current view
		if (type === 'user')
		{
			if (userId === ownerId)
			{
				title = Loc.getMessage('EC_SEC_SLIDER_MY_CALENDARS_LIST');
			}
			else
			{
				title = Loc.getMessage('EC_SEC_SLIDER_USER_CALENDARS_LIST');
			}
		}
		else if (type === 'group')
		{
			title = Loc.getMessage('EC_SEC_SLIDER_GROUP_CALENDARS_LIST');
		}
		else if (type === 'location')
		{
			title = Loc.getMessage('EC_SEC_SLIDER_TYPE_LOCATION_LIST');
		}
		else if (type === 'resource')
		{
			title = Loc.getMessage('EC_SEC_SLIDER_TYPE_RESOURCE_LIST');
		}
		else
		{
			title = Loc.getMessage('EC_SEC_SLIDER_TITLE_COMP_CAL');
		}

		sectionGroups.push({
			title: title,
			type: type,
			belongsToView: true
		});

		if (type !== 'user' || userId !== ownerId)
		{
			sectionGroups.push({
				title: Loc.getMessage('EC_SEC_SLIDER_MY_CALENDARS_LIST'),
				type: 'user',
				ownerId: userId
			});
		}

		// 2. Company calendar
		if (type !== 'company' && type !== 'company_calendar')
		{
			sectionGroups.push({
				title: Loc.getMessage('EC_SEC_SLIDER_TITLE_COMP_CAL'),
				type: 'company'
			});
		}

		// 3. Users calendars
		if (Type.isArray(followedUserList))
		{
			followedUserList.forEach(function(user)
			{
				if (parseInt(user.ID) !== ownerId || type !== 'user')
				{
					sectionGroups.push({
						title: BX.util.htmlspecialchars(user.FORMATTED_NAME),
						type: 'user',
						ownerId: parseInt(user.ID)
					});
				}
			}, this);
		}

		// 4. Groups calendars
		sectionGroups.push({
			title: Loc.getMessage('EC_SEC_SLIDER_POPUP_MENU_ADD_GROUP'),
			type: 'group'
		});

		// 5. Resources calendars
		sectionGroups.push({
			title: Loc.getMessage('EC_SEC_SLIDER_TITLE_RESOURCE_CAL'),
			type: 'resource'
		});

		// 6. Location calendars
		sectionGroups.push({
			title: Loc.getMessage('EC_SEC_SLIDER_TITLE_LOCATION_CAL'),
			type: 'location'
		});

		return sectionGroups;
	}
}