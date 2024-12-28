import { Manual } from 'ui.manual';

import { PromoId, UserType } from 'im.v2.const';
import { PromoManager } from 'im.v2.lib.promo';
import { Core } from 'im.v2.application.core';

import '../css/collab-helpdesk.css';

import type { JsonObject } from 'main.core';

const INTRANET_MANUAL_CODE = 'collab';
const COLLABER_MANUAL_CODE = 'collab_guest';

// @vue/component
export const CollabHelpdeskPreview = {
	name: 'CollabHelpdeskPreview',
	props:
	{
		dialogId: {
			type: String,
			required: true,
		},
	},
	data(): JsonObject
	{
		return {
			needToShow: PromoManager.getInstance().needToShow(PromoId.collabHelpdeskSidebar),
		};
	},
	computed:
	{
		isCurrentUserCollaber(): boolean
		{
			const currentUser = this.$store.getters['users/get'](Core.getUserId(), true);

			return currentUser.type === UserType.collaber;
		},
	},
	methods:
	{
		close()
		{
			this.needToShow = false;
			void PromoManager.getInstance().markAsWatched(PromoId.collabHelpdeskSidebar);
		},
		openHelpdesk()
		{
			const manualCode = this.isCurrentUserCollaber ? COLLABER_MANUAL_CODE : INTRANET_MANUAL_CODE;

			const urlParams = {
				utm_source: 'portal',
				utm_content: 'widget',
			};

			Manual.show(manualCode, urlParams);
		},
		loc(phraseCode: string, replacements: {[string]: string} = {}): string
		{
			return this.$Bitrix.Loc.getMessage(phraseCode, replacements);
		},
	},
	template: `
		<div v-if="needToShow" class="bx-im-sidebar-collab-helpdesk__container" @click="openHelpdesk">
			<div class="bx-im-sidebar-collab-helpdesk__icon"></div>
			<div class="bx-im-sidebar-collab-helpdesk__content">
				<div class="bx-im-sidebar-collab-helpdesk__title">
					{{ loc('IM_SIDEBAR_COLLAB_HELPDESK_TITLE') }}
				</div>
				<div class="bx-im-sidebar-collab-helpdesk__description --line-clamp-3">
					{{ loc('IM_SIDEBAR_COLLAB_HELPDESK_DESCRIPTION') }}
				</div>
			</div>
			<div class="bx-im-sidebar-collab-helpdesk__close" @click.stop="close"></div>
		</div>
	`,
};
