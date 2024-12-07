import { ajax, Uri } from 'main.core';

type GroupFeature = {
	featureName: string,
	name: string,
	customName: string,
	id: number,
	active: boolean,
}

export class Controller
{
	static paths: {
		pathToUsers: string,
		pathToCommonSpace: string,
		pathToFeatures: string,
		pathToInvite: string,
	};

	static getGroupData(groupId: number, select: Array<string>): Promise
	{
		return BX.ajax.runAction('socialnetwork.api.workgroup.get', {
			data: {
				params: {
					select,
					groupId,
				},
			},
		}).then((response) => {
			return {
				id: response.data.ID,
				name: response.data.NAME,
				description: response.data.DESCRIPTION,
				avatar: response.data?.AVATAR,
				isPin: response.data?.IS_PIN,
				privacyCode: response.data?.PRIVACY_CODE,
				isSubscribed: response.data.USER_DATA?.IS_SUBSCRIBED,
				numberOfMembers: response.data?.NUMBER_OF_MEMBERS,
				listOfMembers: response.data?.LIST_OF_MEMBERS,
				groupMembersList: response.data?.GROUP_MEMBERS_LIST,
				actions: {
					canEdit: response.data?.ACTIONS?.EDIT,
					canInvite: response.data?.ACTIONS?.INVITE,
					canLeave: response.data?.ACTIONS?.LEAVE,
					canFollow: response.data?.ACTIONS?.FOLLOW,
					canPin: response.data?.ACTIONS?.PIN,
					canEditFeatures: response.data?.ACTIONS?.EDIT_FEATURES,
				},
				counters: response.data?.COUNTERS,
				efficiency: response.data?.EFFICIENCY,
				subject: response.data?.SUBJECT_DATA?.NAME,
				dateCreate: response.data?.DATE_CREATE,
				features: response.data?.FEATURES,
			};
		}).catch((error) => {
			// eslint-disable-next-line no-console
			console.log(error);
		});
	}

	static inviteUsers(spaceId: number, users: number[]): Promise
	{
		return BX.ajax.runAction('socialnetwork.api.workgroup.updateInvitedUsers', {
			data: {
				spaceId,
				users: [0, ...users],
			},
		});
	}

	static changePrivacy(groupId: number, privacyCode: 'open' | 'closed' | 'secret'): Promise
	{
		const fields = {};

		if (privacyCode === 'open')
		{
			fields.VISIBLE = 'Y';
			fields.OPENED = 'Y';
			fields.EXTERNAL = 'N';
		}

		if (privacyCode === 'closed')
		{
			fields.VISIBLE = 'Y';
			fields.OPENED = 'N';
			fields.EXTERNAL = 'N';
		}

		if (privacyCode === 'secret')
		{
			fields.VISIBLE = 'N';
			fields.OPENED = 'N';
			fields.EXTERNAL = 'N';
		}

		return ajax.runAction('socialnetwork.api.workgroup.update', {
			data: {
				groupId,
				fields,
			},
		});
	}

	static changeTitle(groupId: number, title: string): Promise
	{
		return ajax.runAction('socialnetwork.api.workgroup.update', {
			data: {
				groupId,
				fields: {
					NAME: title,
				},
			},
		});
	}

	static changeDescription(groupId: number, description: string): Promise
	{
		return ajax.runAction('socialnetwork.api.workgroup.update', {
			data: {
				groupId,
				fields: {
					DESCRIPTION: description,
				},
			},
		});
	}

	static changeTags(groupId: number, tags: Array<string>): Promise
	{
		return ajax.runAction('socialnetwork.api.workgroup.update', {
			data: {
				groupId,
				fields: {
					KEYWORDS: tags.join(','),
				},
			},
		});
	}

	static changeFeature(groupId: number, feature: GroupFeature): Promise
	{
		return ajax.runAction('socialnetwork.api.workgroup.setFeature', {
			data: {
				groupId,
				feature,
			},
		});
	}

	static updatePhoto(groupId: number, photo: File): Promise
	{
		const formData = new FormData();
		// eslint-disable-next-line no-param-reassign
		photo.name ??= 'tmp.png';
		formData.append('newPhoto', photo, photo.name);
		formData.append('groupId', groupId);

		return ajax.runAction('socialnetwork.api.workgroup.updatePhoto', {
			data: formData,
		});
	}

	static changePin(groupId: number, isPinned: boolean): Promise
	{
		return ajax.runAction('socialnetwork.api.workgroup.changePin', {
			data: {
				groupIdList: [groupId],
				action: isPinned ? 'pin' : 'unpin',
			},
		});
	}

	static setSubscription(groupId: number, isSubscribed: boolean): Promise
	{
		return ajax.runAction('socialnetwork.api.workgroup.setSubscription', {
			data: {
				params: {
					groupId,
					value: isSubscribed ? 'Y' : 'N',
				},
			},
		});
	}

	static leaveGroup(groupId: number): Promise
	{
		return ajax.runAction('socialnetwork.api.workgroup.leave', {
			data: {
				groupId,
			},
		});
	}

	static deleteGroup(groupId: number): Promise
	{
		return ajax.runAction('socialnetwork.api.workgroup.delete', {
			data: {
				groupId,
			},
		});
	}

	static openGroupUsers(mode: 'all' | 'in' | 'out')
	{
		const availableModes = {
			all: 'members',
			in: 'requests_in',
			out: 'requests_out',
		};

		const uri = new Uri(this.paths.pathToUsers);
		uri.setQueryParams({
			mode: availableModes[mode],
		});

		BX.SidePanel.Instance.open(uri.toString(), {
			width: 1200,
			cacheable: false,
			loader: 'group-users-loader',
		});
	}

	static openGroupFeatures()
	{
		BX.SidePanel.Instance.open(this.paths.pathToFeatures, {
			width: 800,
			loader: 'group-features-loader',
		});
	}

	static openGroupInvite()
	{
		BX.SidePanel.Instance.open(this.paths.pathToInvite, {
			width: 950,
			loader: 'group-invite-loader',
		});
	}

	static openCommonSpace()
	{
		location.href = this.paths.pathToCommonSpace;
	}
}
