import '../css/message-panel.css';

import type { ForwardedEntityConfig, PanelContext } from 'im.v2.provider.service';

type ForwardedEntityPanelContext = PanelContext & {
	entityConfig: ForwardedEntityConfig,
}

// @vue/component
export const ForwardEntityPanel = {
	name: 'ForwardEntityPanel',
	props:
	{
		context: {
			type: Object,
			required: true,
		},
	},
	emits: ['close'],
	computed:
	{
		forwardedEntityContext(): ForwardedEntityPanelContext
		{
			return this.context;
		},
		config(): ForwardedEntityConfig
		{
			return this.forwardedEntityContext.entityConfig;
		},
	},
	methods:
	{
		loc(phraseCode: string): string
		{
			return this.$Bitrix.Loc.getMessage(phraseCode);
		},
	},
	template: `
		<div class="bx-im-message-panel__container">
			<div class="bx-im-message-panel__icon --forward"></div>
			<div class="bx-im-message-panel__content">
				<div class="bx-im-message-panel__title">{{ config.title }}</div>
				<div class="bx-im-message-panel__text">
					<span class="bx-im-message-panel__forward-author">author</span>
					<span class="bx-im-message-panel__forward-message-text">message</span>
				</div>
			</div>
			<div @click="$emit('close')" class="bx-im-message-panel__close"></div>
		</div>
	`,
};
