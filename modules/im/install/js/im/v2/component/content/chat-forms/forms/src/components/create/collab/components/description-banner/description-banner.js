import { Loc, Dom } from 'main.core';

import { openHelpdeskArticle } from 'im.v2.lib.helpdesk';

import './css/description-banner.css';

const LINK_CSS_CLASS = 'bx-im-create-collab-description-banner__link';
const TITLE_LINK_MODIFIER = '--title';
const INVITE_LINK_MODIFIER = '--invite';

// @vue/component
export const DescriptionBanner = {
	name: 'DescriptionBanner',
	computed:
	{
		preparedTitle(): string
		{
			return Loc.getMessage('IM_CREATE_COLLAB_BANNER_TITLE', {
				'[learn-more]': `<span class="${LINK_CSS_CLASS} ${TITLE_LINK_MODIFIER} --solid">`,
				'[/learn-more]': '</span>',
			});
		},
		preparedInviteText(): string
		{
			return Loc.getMessage('IM_CREATE_COLLAB_BANNER_TEXT_2', {
				'[learn-more]': `<span class="${LINK_CSS_CLASS} ${INVITE_LINK_MODIFIER} --dashed">`,
				'[/learn-more]': '</span>',
			});
		},
	},
	methods:
	{
		onTitleClick(event: PointerEvent)
		{
			if (!Dom.hasClass(event.target, TITLE_LINK_MODIFIER))
			{
				return;
			}

			const ARTICLE_CODE = '22706764';
			openHelpdeskArticle(ARTICLE_CODE);
		},
		onInviteClick(event: PointerEvent)
		{
			if (!Dom.hasClass(event.target, INVITE_LINK_MODIFIER))
			{
				return;
			}

			const ARTICLE_CODE = '22706836';
			openHelpdeskArticle(ARTICLE_CODE);
		},
		loc(phraseCode: string): string
		{
			return this.$Bitrix.Loc.getMessage(phraseCode);
		},
	},
	template: `
		<div class="bx-im-create-collab-description-banner__container" ref="container">
			<div class="bx-im-create-collab-description-banner__icon"></div>
			<div class="bx-im-create-collab-description-banner__content">
				<div class="bx-im-create-collab-description-banner__title" v-html="preparedTitle" @click="onTitleClick"></div>
				<div class="bx-im-create-collab-description-banner__text">
					{{ loc('IM_CREATE_COLLAB_BANNER_TEXT_1') }}
				</div>
				<div class="bx-im-create-collab-description-banner__text" v-html="preparedInviteText" @click="onInviteClick"></div>
			</div>
		</div>
	`,
};
