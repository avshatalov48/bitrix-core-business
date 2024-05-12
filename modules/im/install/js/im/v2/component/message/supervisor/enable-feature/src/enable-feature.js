import { Button as ButtonComponent, ButtonSize } from 'im.v2.component.elements';
import { SupervisorBaseMessage } from 'im.v2.component.message.supervisor.base';

import { metaData } from './const/tools';
import './css/enable-feature.css';

import type { SupervisorComponentParams } from '../../base/src/const/features';
import type { ImModelMessage } from 'im.v2.model';
import type { CustomColorScheme } from 'im.v2.component.elements';

const TOOL_ID_PARAMS_KEY = 'toolId';
const BUTTON_COLOR = '#52c1e7';

// @vue/component
export const SupervisorEnableFeatureMessage = {
	name: 'SupervisorEnableFeatureMessage',
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
		buttonColorScheme(): CustomColorScheme
		{
			return {
				backgroundColor: 'transparent',
				borderColor: BUTTON_COLOR,
				iconColor: BUTTON_COLOR,
				textColor: BUTTON_COLOR,
				hoverColor: 'transparent',
			};
		},
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
				<div :class="['bx-im-message-enable-feature__image', modifierImageClass]" />
			</template>
			<template #actions>
				<ButtonComponent
					:size="ButtonSize.L"
					:isRounded="true"
					:text="toolData.detailButton.text"
					@click="toolData.detailButton.callback"
				/>
				<ButtonComponent
					:size="ButtonSize.L"
					:customColorScheme="buttonColorScheme"
					:isRounded="true"
					:text="toolData.infoButton.text"
					@click="toolData.infoButton.callback"
				/>
			</template>
		</SupervisorBaseMessage>
	`,
};
