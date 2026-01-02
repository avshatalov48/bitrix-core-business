import { Loc } from 'main.core';

import { MessengerPopup } from 'im.v2.component.elements';

import '../css/auto-delete-hint.css';

import type { JsonObject } from 'main.core';

const POPUP_ID = 'im-auto-delete-hint-popup';
const ARTICLE_CODE = '24402288';

// @vue/component
export const AutoDeleteHint = {
	name: 'AutoDeleteHint',
	components: { MessengerPopup },
	props:
	{
		bindElement: {
			type: Object,
			required: true,
		},
	},
	emits: ['close'],
	computed:
	{
		POPUP_ID: () => POPUP_ID,
		ARTICLE_CODE: () => ARTICLE_CODE,
		config(): JsonObject
		{
			return {
				darkMode: true,
				bindElement: this.bindElement,
				angle: true,
				width: 317,
				closeIcon: true,
				offsetLeft: 45,
				className: 'bx-im-auto-delete-hint__scope',
				contentBorderRadius: 0,
			};
		},
		hintText(): string {
			const articleLink = `BX.Helper?.show('redirect=detail&code=${ARTICLE_CODE}')`;

			return Loc.getMessage('IM_SIDEBAR_AUTODELETE_NO_PERMISSION', {
				'[helpdesklink]': `<a class="bx-im-auto-delete-hint__hide" onclick="${articleLink}">`,
				'[/helpdesklink]': '</a>',
			});
		},
	},
	template: `
		<MessengerPopup
			:config="config"
			@close="$emit('close')"
			:id="POPUP_ID"
		>
			<div class="bx-im-sidebar-auto-delete-hint__container">
				<div class="bx-im-auto-delete-hint__text" v-html="hintText" />
			</div>
		</MessengerPopup>
	`,
};
