import GroupSharing from './groupsharing';
import { User } from '../../model';

type GroupSharingConfig = {
	link: {
		url: string,
		hash: string,
		rule: Object,
	},
	userCalendarSettings: {
		week_holidays: string,
		week_start: string,
		work_time_start: string,
		work_time_end: string,
	},
	user: User,
}

export default class GroupSharingController
{
	static #groupSharing: GroupSharing = null;
	static #groupId: number = null;
	static #bindElement: number = null;
	static #config = null;

	static async getGroupSharing(groupId: number, bindElement: HTMLElement): Promise<GroupSharing>
	{
		if (
			GroupSharingController.#groupSharing
			&& GroupSharingController.#groupId === groupId
			&& GroupSharingController.#bindElement === bindElement
		)
		{
			return GroupSharingController.#groupSharing;
		}

		const config = await this.#getSharingConfig(groupId);

		GroupSharingController.#groupSharing = new GroupSharing({
			bindElement,
			context: 'calendar',
			calendarContext: {
				sharingObjectType: 'group',
				sharingObjectId: groupId,
				externalSharing: true,
			},
			userInfo: {
				id: config.user.id,
				name: config.user.name,
				avatar: config.user.avatar,
				isCollabUser: config.user.isCollabUser,
			},
			sharingConfig: config.link,
			calendarSettings: config.userCalendarSettings,
		});

		GroupSharingController.#config = config;
		GroupSharingController.#groupId = groupId;
		GroupSharingController.#bindElement = bindElement;

		return this.#groupSharing;
	}

	static async #getSharingConfig(groupId: number): Promise<GroupSharingConfig>
	{
		if (
			this.#groupId === groupId
			&& this.#config
		)
		{
			return new Promise((resolve: Function): void => {
				void resolve(this.#config);
			});
		}

		return this.#requestSharingConfig(groupId);
	}

	static async #requestSharingConfig(groupId: number): Promise<GroupSharingConfig>
	{
		const action = 'calendar.api.sharinggroupajax.enableAndGetSharingConfig';
		const response = await BX.ajax.runAction(action, { data: { groupId } });

		return response.data;
	}
}
