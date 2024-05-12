import { Core } from 'im.v2.application.core';
import { Button as MessengerButton, ButtonSize, ButtonColor } from 'im.v2.component.elements';

import '../css/empty-state.css';

import type { JsonObject } from 'main.core';

// @vue/component
export const EmptyState = {
	name: 'EmptyState',
	components: { MessengerButton },
	data(): JsonObject
	{
		return {};
	},
	computed:
	{
		ButtonSize: () => ButtonSize,
		ButtonColor: () => ButtonColor,
		inviteUsersLink(): string
		{
			const AJAX_PATH = '/bitrix/services/main/ajax.php';
			const COMPONENT_NAME = 'bitrix:intranet.invitation';
			const ACTION_NAME = 'getSliderContent';
			const params = new URLSearchParams({
				action: ACTION_NAME,
				site_id: Core.getSiteId(),
				c: COMPONENT_NAME,
				mode: 'ajax',
			});

			return `${AJAX_PATH}?${params.toString()}`;
		},
	},
	methods:
	{
		onInviteUsersClick()
		{
			BX.SidePanel.Instance.open(this.inviteUsersLink);
		},
		loc(phraseCode: string): string
		{
			return this.$Bitrix.Loc.getMessage(phraseCode);
		},
	},
	template: `
		<div class="bx-im-list-recent-empty-state__container">
			<div class="bx-im-list-recent-empty-state__image"></div>
			<div class="bx-im-list-recent-empty-state__title">{{ loc('IM_LIST_RECENT_EMPTY_STATE_TITLE') }}</div>
			<div class="bx-im-list-recent-empty-state__subtitle">{{ loc('IM_LIST_RECENT_EMPTY_STATE_SUBTITLE') }}</div>
			<div class="bx-im-list-recent-empty-state__button">
				<MessengerButton
					:size="ButtonSize.L"
					:isRounded="true"
					:text="loc('IM_LIST_RECENT_EMPTY_STATE_INVITE_USERS')"
					@click="onInviteUsersClick"
				/>
			</div>
		</div>
	`,
};
