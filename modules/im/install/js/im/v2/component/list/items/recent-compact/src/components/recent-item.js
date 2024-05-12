import { Core } from 'im.v2.application.core';
import { ChatType } from 'im.v2.const';
import { Avatar, AvatarSize } from 'im.v2.component.elements';

import type { JsonObject } from 'main.core';
import type { ImModelRecentItem, ImModelChat } from 'im.v2.model';

// @vue/component
export const RecentItem = {
	name: 'RecentItem',
	components: { Avatar },
	props:
	{
		item: {
			type: Object,
			required: true,
		},
	},
	data(): JsonObject
	{
		return {};
	},
	computed:
	{
		AvatarSize: () => AvatarSize,
		recentItem(): ImModelRecentItem
		{
			return this.item;
		},
		formattedCounter(): string
		{
			return this.dialog.counter > 99 ? '99+' : this.dialog.counter.toString();
		},
		dialog(): ImModelChat
		{
			return this.$store.getters['chats/get'](this.recentItem.dialogId, true);
		},
		isUser(): boolean
		{
			return this.dialog.type === ChatType.user;
		},
		isChatMuted(): boolean
		{
			if (this.isUser)
			{
				return false;
			}

			const isMuted = this.dialog.muteList.find((element) => {
				return element === Core.getUserId();
			});

			return Boolean(isMuted);
		},
		invitation(): { isActive: boolean, originator: number, canResend: boolean }
		{
			return this.recentItem.invitation;
		},
		wrapClasses(): Object
		{
			return { '--pinned': this.recentItem.pinned };
		},
		itemClasses(): Object
		{
			return { '--no-counter': this.dialog.counter === 0 };
		},
	},
	methods:
	{
		loc(phraseCode: string): string
		{
			return this.$Bitrix.Loc.getMessage(phraseCode);
		},
	},
	// language=Vue
	template: `
		<div :data-id="recentItem.dialogId" :class="wrapClasses" class="bx-im-list-recent-compact-item__wrap">
			<div :class="itemClasses" class="bx-im-list-recent-compact-item__container" ref="container">
				<div class="bx-im-list-recent-compact-item__avatar_container">
					<div v-if="invitation.isActive" class="bx-im-list-recent-compact-item__avatar_invitation"></div>
					<Avatar v-else :dialogId="recentItem.dialogId" :size="AvatarSize.M" :withSpecialTypes="false" />
					<div v-if="dialog.counter > 0" :class="{'--muted': isChatMuted}" class="bx-im-list-recent-compact-item__avatar_counter">
						{{ formattedCounter }}
					</div>
				</div>
			</div>
		</div>
	`,
};
