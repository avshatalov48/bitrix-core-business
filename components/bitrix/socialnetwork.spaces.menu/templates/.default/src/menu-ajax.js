export class MenuAjax
{
	static getGroupData(groupId: number): Promise
	{
		return BX.ajax.runAction('socialnetwork.api.workgroup.get', {
				data: {
					params: {
						select: [
							'ACTIONS',
							'NUMBER_OF_MEMBERS',
							'LIST_OF_MEMBERS',
							'GROUP_MEMBERS_LIST',
							'PRIVACY_TYPE',
							'PIN',
							'USER_DATA',
							'COUNTERS',
						],
						groupId,
					},
				},
			})
			.then((response) => {
				return {
					name: response.data.NAME,
					isPin: response.data.IS_PIN,
					privacyCode: response.data.PRIVACY_CODE,
					isSubscribed: response.data.USER_DATA?.IS_SUBSCRIBED,
					numberOfMembers: response.data.NUMBER_OF_MEMBERS,
					listOfMembers: response.data.LIST_OF_MEMBERS,
					groupMembersList: response.data.GROUP_MEMBERS_LIST,
					actions: {
						canEdit: response.data.ACTIONS?.EDIT,
						canInvite: response.data.ACTIONS?.INVITE,
					},
					counters: response.data.COUNTERS,
				};
			})
			.catch((error) => console.log(error));
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
}
