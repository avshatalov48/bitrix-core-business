import { ChatAvatar, AvatarSize } from 'im.v2.component.elements';

import type { ImModelCallItem } from 'im.v2.model';

// @vue/component
export const ActiveCall = {
	name: 'ActiveCall',
	components: { ChatAvatar },
	props:
	{
		item: {
			type: Object,
			required: true,
		},
	},
	emits: ['click'],
	computed:
	{
		AvatarSize: () => AvatarSize,
		activeCall(): ImModelCallItem
		{
			return this.item;
		},
	},
	methods:
	{
		onClick(event)
		{
			const recentItem = this.$store.getters['recent/get'](this.activeCall.dialogId);
			if (!recentItem)
			{
				return;
			}
			this.$emit('click', { item: recentItem, $event: event });
		},
		loc(phraseCode: string): string
		{
			return this.$Bitrix.Loc.getMessage(phraseCode);
		},
	},
	template: `
		<div :data-id="activeCall.dialogId" class="bx-im-list-recent-compact-item__wrap">
			<div @click="onClick" class="bx-im-list-recent-compact-item__container">
				<div class="bx-im-list-recent-compact-item__avatar_container">
					<ChatAvatar 
						:avatarDialogId="activeCall.dialogId"
						:contextDialogId="activeCall.dialogId"
						:size="AvatarSize.M" 
						:withSpecialTypes="false" 
					/>
					<div class="bx-im-list-recent-compact-active-call__icon" :class="'--' + activeCall.state"></div>
				</div>
			</div>
		</div>
	`,
};
