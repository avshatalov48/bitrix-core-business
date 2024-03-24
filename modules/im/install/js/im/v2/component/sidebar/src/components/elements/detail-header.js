import { Button as ChatButton, ButtonSize, ButtonColor } from 'im.v2.component.elements';

import './css/detail-header.css';

// @vue/component
export const DetailHeader = {
	name: 'DetailHeader',
	components: { ChatButton },
	props: {
		title: {
			type: String,
			required: true,
		},
		secondLevel: {
			type: Boolean,
			default: false,
		},
		withAddButton: {
			type: Boolean,
			default: false,
		},
	},
	emits: ['back', 'addClick'],
	computed:
	{
		ButtonSize: () => ButtonSize,
		ButtonColor: () => ButtonColor,
	},
	template: `
		<div class="bx-im-sidebar-detail-header__container bx-im-sidebar-detail-header__scope">
			<div class="bx-im-sidebar-detail-header__title-container">
				<button
					:class="{'bx-im-messenger__cross-icon': !secondLevel, 'bx-im-sidebar__back-icon': secondLevel}"
					@click="$emit('back')"
				></button>
				<div class="bx-im-sidebar-detail-header__title-text">{{ title }}</div>
				<div v-if="withAddButton" class="bx-im-sidebar-detail-header__add-button" ref="add-button">
					<ChatButton
						:text="$Bitrix.Loc.getMessage('IM_SIDEBAR_ADD_BUTTON_TEXT')"
						:size="ButtonSize.S"
						:color="ButtonColor.PrimaryLight"
						:isRounded="true"
						:isUppercase="false"
						icon="plus"
						@click="$emit('addClick', {target: $refs['add-button']})"
					/>
				</div>
			</div>
		</div>
	`,
};
