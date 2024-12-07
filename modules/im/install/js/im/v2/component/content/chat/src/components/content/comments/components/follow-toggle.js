import { Core } from 'im.v2.application.core';
import { Toggle, ToggleSize } from 'im.v2.component.elements';

import { CommentsService } from '../classes/comments-service';

import '../css/follow-toggle.css';

import type { JsonObject } from 'main.core';
import type { ImModelChat } from 'im.v2.model';

// @vue/component
export const FollowToggle = {
	name: 'FollowToggle',
	components: { Toggle },
	props:
	{
		dialogId: {
			type: String,
			required: true,
		},
	},
	data(): JsonObject
	{
		return {};
	},
	computed:
	{
		ToggleSize: () => ToggleSize,
		dialog(): ImModelChat
		{
			return this.$store.getters['chats/get'](this.dialogId, true);
		},
		isFollowed(): boolean
		{
			return !this.dialog.muteList.includes(Core.getUserId());
		},
	},
	methods:
	{
		onToggleClick(): void
		{
			if (this.isFollowed)
			{
				CommentsService.unsubscribe(this.dialogId);

				return;
			}

			CommentsService.subscribe(this.dialogId);
		},
		loc(phraseCode: string): string
		{
			return this.$Bitrix.Loc.getMessage(phraseCode);
		},
	},
	template: `
		<div @click="onToggleClick" class="bx-im-comments-header-follow__container">
			<div class="bx-im-comments-header-follow__text">{{ loc('IM_CONTENT_COMMENTS_FOLLOW_TOGGLE_TEXT') }}</div>
			<Toggle :size="ToggleSize.M" :isEnabled="isFollowed" />
		</div>
	`,
};
