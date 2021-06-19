import {Vue} from "ui.vue";

import { EventEmitter } from 'main.core.events';
import { EventType } from "im.const";

export const QuotePanel = {
	/**
	 * @emits EventType.dialog.quotePanelClose
	 */
	props:
		{
			quotePanelData: {
				type: Object,
				default: function() {
					return {
						id: 0,
						title: '',
						description: '',
						color: ''
					}
				}
			},
			canClose: {default: true}
		},
	methods:
		{
			close(event)
			{
				EventEmitter.emit(EventType.dialog.quotePanelClose, event);
			},
		},
	computed:
		{
			formattedTittle()
			{
				return this.quotePanelData.title? this.quotePanelData.title.substr(0, 255): this.$Bitrix.Loc.getMessage('IM_QUOTE_PANEL_DEFAULT_TITLE');
			},
			formattedDescription()
			{
				return this.quotePanelData.description? this.quotePanelData.description.substr(0, 255): '';
			},
		},
	template: `
	<transition enter-active-class="bx-im-quote-panel-animation-show" leave-active-class="bx-im-quote-panel-animation-close">				
		<div v-if="quotePanelData.id > 0" class="bx-im-quote-panel">
			<div class="bx-im-quote-panel-wrap">
				<div class="bx-im-quote-panel-box" :style="{borderLeftColor: quotePanelData.color}">
					<div class="bx-im-quote-panel-box-title" :style="{color: quotePanelData.color}">{{formattedTittle}}</div>
					<div class="bx-im-quote-panel-box-desc">{{formattedDescription}}</div>
				</div>
				<div v-if="canClose" class="bx-im-quote-panel-close" @click="close"></div>
			</div>
		</div>
	</transition>
`
};