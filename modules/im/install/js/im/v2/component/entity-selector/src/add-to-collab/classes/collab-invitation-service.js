import { RestMethod } from 'im.v2.const';
import { runAction } from 'im.v2.lib.rest';
import { Utils } from 'im.v2.lib.utils';

type AddEmployeesToCollabRequest = {
	dialogId: string,
	members: number[],
};

export class CollabInvitationService
{
	addEmployees({ dialogId, members }: AddEmployeesToCollabRequest): Promise
	{
		const payload = {
			data: {
				dialogId,
				members: Utils.user.prepareSelectorIds(members),
			},
		};

		return runAction(RestMethod.socialnetworkMemberAdd, payload)
			.catch((error) => {
				console.error('CollabInvitationService: add employee error', error);
			});
	}

	copyLink(collabId: number): Promise<string>
	{
		const payload = {
			data: { collabId },
		};

		return runAction(RestMethod.intranetInviteGetLinkByCollabId, payload)
			.catch((errors) => {
				console.error('CollabInvitationService: getting invite link error', errors);
				throw errors;
			});
	}

	updateLink(collabId: number): Promise<null>
	{
		const payload = {
			data: { collabId },
		};

		return runAction(RestMethod.intranetInviteRegenerateLinkByCollabId, payload)
			.catch((errors) => {
				console.error('CollabInvitationService: updating invite link error', errors);
				throw errors;
			});
	}
}
