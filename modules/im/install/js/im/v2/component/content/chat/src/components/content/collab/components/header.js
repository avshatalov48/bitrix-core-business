import { ChatHeader } from 'im.v2.component.content.elements';

import type { JsonObject } from 'main.core';

type EntityLink = {
	title: string,
	clickHandler: () => void;
};

const GROUP_ID_PARAM_NAME = 'COLLAB_GROUP_ID';

// @vue/component
export const CollabHeader = {
	name: 'CollabHeader',
	components: { ChatHeader },
	props:
	{
		dialogId: {
			type: String,
			default: '',
		},
	},
	data(): JsonObject
	{
		return {
			groupId: 0,
		};
	},
	computed:
	{
		entityLinks(): EntityLink[]
		{
			return [
				{
					title: 'Tasks',
					clickHandler: () => {
						BX.SidePanel.Instance.open(`https://kotlyarchuk.bx/workgroups/group/${this.groupId}/tasks/`);
					},
				},
				{
					title: 'Files',
					clickHandler: () => {
						BX.SidePanel.Instance.open(`https://kotlyarchuk.bx/workgroups/group/${this.groupId}/disk/path/`);
					},
				},
				{
					title: 'Calendar',
					clickHandler: () => {
						BX.SidePanel.Instance.open(`https://kotlyarchuk.bx/workgroups/group/${this.groupId}/calendar/`);
					},
				},
			];
		},
	},
	created()
	{
		const urlQuery = new URLSearchParams(window.location.search);
		this.groupId = urlQuery.get(GROUP_ID_PARAM_NAME) ?? 1;
	},
	// language=Vue
	template: `
		<ChatHeader :dialogId="dialogId" class="bx-im-collab-header__container">
			<template #before-actions>
				<div class="bx-im-collab-header__links-container">
					<div v-for="{ title, clickHandler } in entityLinks" :key="title" @click="clickHandler" class="bx-im-collab-header__links-container_item">
						{{ title }}
					</div>
				</div>
			</template>
		</ChatHeader>
	`,
};
