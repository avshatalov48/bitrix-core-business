import {Button, ButtonSize, ButtonColor} from 'im.v2.component.elements';

// @vue/component
export const ButtonPanel = {
	components: {Button},
	emits: ['openProfile', 'logout'],
	data()
	{
		return {};
	},
	computed:
	{
		ButtonSize: () => ButtonSize,
		ButtonColor: () => ButtonColor
	},
	methods:
	{
		loc(phraseCode: string): string
		{
			return this.$Bitrix.Loc.getMessage(phraseCode);
		}
	},
	template: `
		<div class="bx-im-user-settings-popup__button-panel">
			<div class="bx-im-user-settings-popup__button-panel_button">
				<Button
					:color="ButtonColor.PrimaryBorder"
					:size="ButtonSize.M"
					:isUppercase="false"
					:isRounded="true"
					:text="loc('IM_USER_SETTINGS_OPEN_PROFILE')"
					@click="$emit('openProfile')"
				/>
			</div>
<!--			<div class="bx-im-user-settings-popup__button-panel_button">-->
<!--				<Button-->
<!--					:color="ButtonColor.DangerBorder"-->
<!--					:size="ButtonSize.M"-->
<!--					:isUppercase="false"-->
<!--					:isRounded="true"-->
<!--					:text="loc('IM_USER_SETTINGS_LOGOUT')"-->
<!--					@click="$emit('logout')"-->
<!--				/>-->
<!--			</div>-->
		</div>
	`
};