import {ChatDialog} from 'im.v2.component.dialog.chat';
import {ChatTextarea} from 'im.v2.component.textarea';
import {Logger} from 'im.v2.lib.logger';

import {OpenlineHeader} from './components/openline-header';
import {OpenlineActionPanel} from './components/openline-action-panel';
import {OpenlineSidebar} from './components/openline-sidebar';
import './css/openline-content.css';

// @vue/component
export const OpenlineContent = {
	name: 'OpenlineContent',
	components: {OpenlineHeader, ChatDialog, ChatTextarea, OpenlineActionPanel, OpenlineSidebar},
	props: {
		entityId: {
			type: String,
			default: ''
		}
	},
	data()
	{
		return {
			panelOpened: false
		};
	},
	created()
	{
		Logger.warn('Content: Openline created');
	},
	methods:
	{
		toggleRightPanel()
		{
			this.panelOpened = !this.panelOpened;
		}
	},
	template: `
		<div class="bx-im-content-openline__container">
			<div class="bx-im-content-openline__content">
				<template v-if="entityId !== 0">
					<OpenlineHeader :dialogId="entityId" @toggleRightPanel="toggleRightPanel" />
					<div class="bx-im-content-openline__dialog_container">
						<div class="bx-im-content-openline__dialog_content">
							<ChatDialog />
						</div>
					</div>
					<OpenlineActionPanel />
					<ChatTextarea />
				</template>
				<template v-else>
					<div class="bx-im-content-openline__not-selected">
						<div class="bx-im-content-openline__not-selected_text">
							Choose openline from list
						</div>
					</div>
				</template>
			</div>
			<!-- Right Panel -->
			<transition name="right-panel-transition">
				<OpenlineSidebar v-if="panelOpened" />
			</transition>
		</div>
	`
};