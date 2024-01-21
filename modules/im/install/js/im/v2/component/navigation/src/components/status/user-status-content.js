import {UserStatus, UserStatusSize} from 'im.v2.component.elements';
import {UserStatus as UserStatusType} from 'im.v2.const';
import {Utils} from 'im.v2.lib.utils';

import {StatusService} from '../../classes/status-service';

// @vue/component
export const UserStatusContent = {
	name: 'UserStatusContent',
	components: {UserStatus},
	emits: ['close'],
	computed:
	{
		UserStatusSize: () => UserStatusSize,
		UserStatusType: () => UserStatusType,
		statusList(): string[]
		{
			return [UserStatusType.online, UserStatusType.dnd];
		}
	},
	methods:
	{
		onStatusClick(statusName: string)
		{
			this.getStatusService().changeStatus(statusName);
			this.$emit('close');
		},
		getStatusService(): StatusService
		{
			if (!this.statusService)
			{
				this.statusService = new StatusService();
			}

			return this.statusService;
		},
		getStatusText(status: string): string
		{
			return Utils.user.getStatusText(status);
		}
	},
	template:
	`
		<div class="bx-im-user-status-popup__scope bx-im-user-status-popup__container">
			<div
				v-for="status in statusList"
				:key="status"
				@click="onStatusClick(status)"
				class="bx-im-user-status-popup__item"
			>
				<UserStatus :status="status" :size="UserStatusSize.M" />
				<div class="bx-im-user-status-popup__text">{{ getStatusText(status) }}</div>
			</div>
		</div>
	`
};