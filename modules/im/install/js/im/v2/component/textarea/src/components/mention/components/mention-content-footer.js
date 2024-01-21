import { Loader } from 'im.v2.component.elements';

import '../css/mention-content-footer.css';

// @vue/component
export const MentionContentFooter = {
	name: 'MentionContentFooter',
	components: { Loader },
	props:
	{
		isLoading: {
			type: Boolean,
			default: false,
		},
	},
	computed:
	{
		arrowsControlTitle(): string
		{
			return this.$Bitrix.Loc.getMessage('IM_TEXTAREA_MENTION_ARROWS_CONTROL').replace('##ARROWS_ICON##', '');
		},
		enterControlTitle(): string
		{
			return this.$Bitrix.Loc.getMessage('IM_TEXTAREA_MENTION_ENTER_CONTROL');
		},
		escControlTitle(): string
		{
			return this.$Bitrix.Loc.getMessage('IM_TEXTAREA_MENTION_ESC_CONTROL');
		},
	},
	template: `
		<div class="bx-im-mention-content-footer__container bx-im-mention-content-footer__scope">
			<div class="bx-im-mention-content-footer__controls">
				<div class="bx-im-mention-content-footer__control">
					<span class="bx-im-mention-content-footer__arrows-control-key"></span>
					<span class="bx-im-mention-content-footer__control-description">
						{{ arrowsControlTitle }}
					</span>
				</div>
				<div class="bx-im-mention-content-footer__control">
					<span class="bx-im-mention-content-footer__control-key">Enter</span>
					<span class="bx-im-mention-content-footer__control-description">{{ enterControlTitle }}</span>
				</div>
				<div class="bx-im-mention-content-footer__control">
					<span class="bx-im-mention-content-footer__control-key">Esc</span>
					<span class="bx-im-mention-content-footer__control-description">{{ escControlTitle }}</span>
				</div>
			</div>
			<Loader v-if="isLoading" class="bx-im-mention-content-footer__loader" />
		</div>
	`,
};
