import { Type } from 'main.core';

import { Analytics } from 'im.v2.lib.analytics';

import { EntityCounter } from './entity-counter';

export const EntityType = {
	tasks: 'tasks',
	files: 'files',
	calendar: 'calendar',
};

// @vue/component
export const EntityLink = {
	name: 'EntityLink',
	components: { EntityCounter },
	props:
	{
		dialogId: {
			type: String,
			required: true,
		},
		compactMode: {
			type: Boolean,
			required: true,
		},
		type: {
			type: String,
			required: true,
		},
		title: {
			type: String,
			required: true,
		},
		url: {
			type: String,
			required: true,
		},
		counter: {
			type: [Number, null],
			default: null,
		},
	},
	computed:
	{
		showCounter(): boolean
		{
			return !Type.isNull(this.counter) && this.counter > 0;
		},
	},
	methods:
	{
		onLinkClick()
		{
			Analytics.getInstance().collabEntities.onClick(this.dialogId, this.type);

			BX.SidePanel.Instance.open(this.url, {
				cacheable: false,
				customLeftBoundary: 0,
			});
		},
	},
	template: `
		<a :href="url" @click.prevent="onLinkClick" class="bx-im-collab-header__link" :class="'--' + type">
			<span v-if="compactMode" class="bx-im-collab-header__link-icon"></span>
			<span v-else class="bx-im-collab-header__link-text">{{ title }}</span>
			<EntityCounter v-if="showCounter" :counter="counter" />
		</a>
	`,
};
