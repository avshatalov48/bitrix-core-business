import { DialogBlockType as BlockType } from 'im.v2.const';

import { DateGroupTitle } from './date-group-title';

import type { JsonObject } from 'main.core';
import type { DateGroupItem } from '../../classes/collection-manager/collection-manager';

// @vue/component
export const DateGroup = {
	name: 'DateGroup',
	components: { DateGroupTitle },
	props:
	{
		item: {
			type: Object,
			required: true,
		},
	},
	data(): JsonObject
	{
		return {};
	},
	computed:
	{
		BlockType: () => BlockType,
		dateGroup(): DateGroupItem
		{
			return this.item;
		},
	},
	template: `
		<div class="bx-im-message-list-date-group__container">
			<DateGroupTitle :title="dateGroup.dateTitle" />
			<template v-for="dateGroupItem in dateGroup.items" >
				<slot
					name="dateGroupItem"
					:dateGroupItem="dateGroupItem"
					:isMarkedBlock="dateGroupItem.type === BlockType.markedMessages"
					:isNewMessagesBlock="dateGroupItem.type === BlockType.newMessages"
					:isAuthorBlock="dateGroupItem.type === BlockType.authorGroup"
				></slot>
			</template>
		</div>
	`,
};
