import { Button as MessengerButton, ButtonSize, ButtonColor } from 'im.v2.component.elements';

// @vue/component
export const ButtonPanel = {
	components: { MessengerButton },
	props:
	{
		isCreating: {
			type: Boolean,
			required: true,
		},
		createButtonTitle: {
			type: String,
			required: true,
		},
	},
	emits: ['create', 'cancel'],
	data()
	{
		return {};
	},
	computed:
	{
		ButtonSize: () => ButtonSize,
		ButtonColor: () => ButtonColor,
	},
	methods:
	{
		loc(phraseCode: string): string
		{
			return this.$Bitrix.Loc.getMessage(phraseCode);
		},
	},
	template: `
		<div class="bx-im-content-create-chat__buttons">
			<div class="bx-im-content-create-chat__buttons_create">
				<MessengerButton
					:size="ButtonSize.XL"
					:color="ButtonColor.Success"
					:text="createButtonTitle"
					:isLoading="isCreating"
					:isDisabled="isCreating"
					@click="$emit('create')"
				/>
			</div>
			<div class="bx-im-content-create-chat__buttons_cancel">
				<MessengerButton
					:size="ButtonSize.XL"
					:color="ButtonColor.Link"
					:text="loc('IM_CREATE_CHAT_CANCEL')"
					:isDisabled="isCreating"
					@click="$emit('cancel')"
				/>
			</div>
		</div>
	`,
};
