import { Button as ButtonComponent, ButtonSize, ButtonColor } from 'im.v2.component.elements';
import { SupervisorBaseMessage } from 'im.v2.component.message.supervisor.base';

import { metaData } from './const/tools';
import './css/update-features.css';

import type { ImModelMessage } from 'im.v2.model';
import type { SupervisorComponentParams } from '../../base/src/const/features';

const TOOL_ID_PARAMS_KEY = 'toolId';
// @vue/component
export const SupervisorUpdateFeatureMessage = {
	name: 'SupervisorUpdateFeatureMessage',
	components: { ButtonComponent, SupervisorBaseMessage },
	props: {
		item: {
			type: Object,
			required: true,
		},
		dialogId: {
			type: String,
			required: true,
		},
	},
	computed:
	{
		message(): ImModelMessage
		{
			return this.item;
		},
		ButtonSize: () => ButtonSize,
		ButtonColor: () => ButtonColor,
		toolId(): string
		{
			return this.message.componentParams[TOOL_ID_PARAMS_KEY];
		},
		toolData(): SupervisorComponentParams
		{
			return metaData[this.toolId];
		},
		modifierImageClass(): string
		{
			return `--${this.toolId}`;
		},
	},
	template: `
		<SupervisorBaseMessage
			:item="item"
			:dialogId="dialogId"
			:title="toolData.title"
			:description="toolData.description"
		>
			<template #image>
				<div :class="['bx-im-message-update-features__image-wrapper', modifierImageClass]" />
			</template>
			<template #actions>
				<ButtonComponent
					:size="ButtonSize.L"
					:isRounded="true"
					:color="ButtonColor.Success"
					:text="toolData.detailButton.text"
					@click="toolData.detailButton.callback"
				/>
				<ButtonComponent
					:size="ButtonSize.L"
					:color="ButtonColor.LightBorder"
					:isRounded="true"
					:text="toolData.infoButton.text"
					@click="toolData.infoButton.callback"
				/>
			</template>
		</SupervisorBaseMessage>
	`,
};
