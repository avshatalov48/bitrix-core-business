import { Loc } from 'main.core';

import '../../css/description-banner.css';

// @vue/component
export const DescriptionBanner = {
	name: 'DescriptionBanner',
	emits: ['close'],
	computed:
	{
		preparedText(): string
		{
			return Loc.getMessage('IM_RECENT_CREATE_COLLAB_DESCRIPTION_BANNER', {
				'[color_highlight]': '<span class="bx-im-create-chat-menu-description-banner__highlight">',
				'[/color_highlight]': '</span>',
			});
		},
	},
	template: `
		<div class="bx-im-create-chat-menu-description-banner__container">
			<div class="bx-im-create-chat-menu-description-banner__content" v-html="preparedText"></div>
			<div class="bx-im-create-chat-menu-description-banner__close-icon" @click.stop="$emit('close')"></div>
		</div>
	`,
};
