import { NotificationTypesCodes } from 'im.const';

// @vue/component
export const NotificationItemHeader = {
	props: {
		listItem: {
			type: Object,
			required: true
		},
		isExtranet: {
			type: Boolean,
			default: false
		}
	},
	computed:
	{
		moreUsers()
		{
			const phrase = this.$Bitrix.Loc.getMessage('IM_NOTIFICATIONS_MORE_USERS').split('#COUNT#');

			return {
				start: phrase[0],
				end: this.listItem.params.USERS.length + phrase[1]
			};
		},
		isMoreUsers()
		{
			return this.listItem.params.hasOwnProperty('USERS') && this.listItem.params.USERS.length > 0;
		},
		isAbleToDelete()
		{
			return this.listItem.sectionCode === NotificationTypesCodes.simple;
		},
	},
	methods:
	{
		onDeleteClick(event)
		{
			if (event.item.sectionCode === NotificationTypesCodes.simple)
			{
				this.$emit('deleteClick', event);
			}
		},
		onMoreUsersClick(event)
		{
			if (event.users)
			{
				this.$emit('moreUsersClick', {
					event: event.event,
					content: {
						type: 'USERS',
						value: event.users
					}
				});
			}
		},
		onUserTitleClick(event)
		{
			if (window.top["BXIM"] && event.userId > 0)
			{
				window.top["BXIM"].openMessenger(event.userId);
			}
		},
	},
	//language=Vue
	template: `
		<div class="bx-im-notifications-item-content-header">
			<div v-if="listItem.title" class="bx-im-notifications-item-header-title">
				<span
					v-if="!listItem.systemType"
					@click.prevent="onUserTitleClick({userId: listItem.authorId, event: $event})"
					class="bx-im-notifications-item-header-title-text-link"
					:class="[isExtranet ? '--extranet' : '']"
				>
					{{ listItem.title.value }}
				</span>
				<span v-else class="bx-im-notifications-item-header-title-text">{{ listItem.title.value }}</span>
				<span
					v-if="isMoreUsers && !listItem.systemType"
					class="bx-im-notifications-item-header-more-users"
				>
					{{ moreUsers.start }}
					<span class="bx-messenger-ajax" @click="onMoreUsersClick({users: listItem.params.USERS, event: $event})">
						{{ moreUsers.end }}
					</span>
				</span>
			</div>
			<div class="bx-im-notifications-item-content-header-right">
				<div class="bx-im-notifications-item-header-date">
					{{ listItem.date.value }}
				</div>
				<span
					v-if="isAbleToDelete"
					class="bx-im-notifications-item-header-delete"
					@click="onDeleteClick({item: listItem, event: $event})">
				</span>
			</div>
		</div>
	`
};