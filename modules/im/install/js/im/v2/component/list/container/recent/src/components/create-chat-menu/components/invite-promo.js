import { Analytics } from 'im.v2.lib.analytics';
import { InviteManager } from 'im.v2.lib.invite';
import { PromoManager } from 'im.v2.lib.promo';
import { PromoId } from 'im.v2.const';

import '../css/invite-promo.css';

// @vue/component
export const InvitePromo = {
	name: 'InvitePromo',
	emits: ['close'],
	methods:
	{
		onContainerClick(): void
		{
			const analyticsContext = Analytics.getInstance().sliderInvite.getRecentCreateMenuContext();
			InviteManager.openInviteSlider(analyticsContext);
		},
		onCloseClick(): void
		{
			void PromoManager.getInstance().markAsWatched(PromoId.recentCreateChatInviteUsers);
			this.$emit('close');
		},
		loc(phraseCode: string): string
		{
			return this.$Bitrix.Loc.getMessage(phraseCode);
		},
	},
	template: `
		<div @click="onContainerClick" class="bx-im-recent-invite-promo__container">
			<div class="bx-im-recent-invite-promo__icon"></div>
			<div class="bx-im-recent-invite-promo__content">
				<div class="bx-im-recent-invite-promo__title">
					{{ loc('IM_RECENT_CREATE_INVITE_TITLE') }}
				</div>
				<div class="bx-im-recent-invite-promo__subtitle">
					{{ loc('IM_RECENT_CREATE_INVITE_SUBTITLE') }}
				</div>
			</div>
			<div @click.stop="onCloseClick" class="bx-im-recent-invite-promo__close-icon"></div>
		</div>
	`,
};
