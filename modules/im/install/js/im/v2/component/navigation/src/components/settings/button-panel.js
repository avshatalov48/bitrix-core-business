import { Core } from 'im.v2.application.core';
import { Utils } from 'im.v2.lib.utils';
import { Button as ChatButton, ButtonSize, ButtonColor } from 'im.v2.component.elements';
import { DesktopApi } from 'im.v2.lib.desktop-api';

// @vue/component
export const ButtonPanel = {
	name: 'ButtonPanel',
	components: { ChatButton },
	emits: ['openProfile', 'logout'],
	computed:
	{
		ButtonSize: () => ButtonSize,
		ButtonColor: () => ButtonColor,
		currentUserId(): number
		{
			return Core.getUserId();
		},
		profileUri(): string
		{
			return Utils.user.getProfileLink(this.currentUserId);
		},
		isDesktop(): boolean
		{
			return DesktopApi.isDesktop();
		},
	},
	methods:
	{
		loc(phraseCode: string): string
		{
			return this.$Bitrix.Loc.getMessage(phraseCode);
		},
		onLogoutClick()
		{
			void DesktopApi.logout();
		},
	},
	template: `
		<div class="bx-im-user-settings-popup__button-panel">
			<a :href="profileUri" target="_blank" class="bx-im-user-settings-popup__user_link">
				<ChatButton
					:color="ButtonColor.PrimaryBorder"
					:size="ButtonSize.M"
					:isUppercase="false"
					:isRounded="true"
					:text="loc('IM_USER_SETTINGS_OPEN_PROFILE')"
					@click="$emit('openProfile')"
				/>
			</a>
			<ChatButton
				v-if="isDesktop" 
				:color="ButtonColor.DangerBorder"
				:size="ButtonSize.M"
				:isUppercase="false"
				:isRounded="true"
				:text="loc('IM_USER_SETTINGS_LOGOUT')"
				@click="onLogoutClick"
			/>
		</div>
	`,
};
