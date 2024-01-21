import { Event } from 'main.core';
import { EventEmitter } from 'main.core.events';

import { Logger } from 'im.v2.lib.logger';
import { LayoutManager } from 'im.v2.lib.layout';
import { EventType, Layout } from 'im.v2.const';

import { OpenlinesLoader } from './components/loader';

import './css/openlines-content.css';

import type { ImModelLayout } from 'im.v2.model';

const IFRAME_PATH = '/desktop_app/';
const IFRAME_PARAMS = {
	IFRAME: 'Y',
	IM_BACKGROUND: 'light',
	IM_LINES: 'Y',
	IM_MENU: 'N',
	IM_STATUS: 'N',
	IM_V2_LAYOUT: 'Y',
};

// @vue/component
export const OpenlinesContent = {
	name: 'OpenlinesContent',
	components: { OpenlinesLoader },
	props: {
		entityId: {
			type: String,
			default: '',
		},
	},
	data()
	{
		return {
			isLoading: true,
		};
	},
	computed:
	{
		iframeLink(): string
		{
			const params = new URLSearchParams(IFRAME_PARAMS);

			return `${IFRAME_PATH}?${params.toString()}`;
		},
		layout(): ImModelLayout
		{
			return this.$store.getters['application/getLayout'];
		},
	},
	watch:
	{
		layout: {
			handler(newLayout: ImModelLayout, prevLayout: ImModelLayout)
			{
				if (newLayout.name !== Layout.openlines.name)
				{
					return;
				}

				if (this.dialogIdChangedFromFrame)
				{
					this.dialogIdChangedFromFrame = false;

					return;
				}

				if (!newLayout.entityId || newLayout.entityId === prevLayout.entityId)
				{
					return;
				}

				this.sendOpenEvent();
			},
			flush: 'post',
		},
	},
	created()
	{
		Logger.warn('Content: Openlines created');
		this.subscribeToEvents();
	},
	beforeUnmount()
	{
		this.unsubscribeEvents();
	},
	methods:
	{
		subscribeToEvents()
		{
			Event.bind(window, EventType.lines.onInit, this.onLinesInit);
			Event.bind(window, EventType.lines.onChatOpen, this.onLinesChatOpen);

			EventEmitter.subscribe(EventType.slider.onClose, this.unregisterSliderBindings);
		},
		unsubscribeEvents()
		{
			Event.unbind(window, EventType.lines.onInit, this.onLinesInit);
			Event.unbind(window, EventType.lines.onChatOpen, this.onLinesChatOpen);

			EventEmitter.unsubscribe(EventType.slider.onClose, this.unregisterSliderBindings);
		},
		async onLinesInit()
		{
			if (!this.isLoading)
			{
				return;
			}
			await this.$nextTick();
			this.isLoading = false;
			this.sendOpenEvent();
			this.registerSliderBindings();
		},
		onLinesChatOpen(event: CustomEvent)
		{
			if (this.entityId === event.detail)
			{
				return;
			}
			this.dialogIdChangedFromFrame = true;

			void LayoutManager.getInstance().setLayout({
				name: Layout.openlines.name,
				entityId: event.detail,
			});
		},
		sendOpenEvent()
		{
			if (!this.entityId)
			{
				return;
			}

			const openEvent = new CustomEvent(EventType.lines.openChat, { detail: this.entityId });
			this.$refs.frame.contentWindow.dispatchEvent(openEvent);
		},
		registerSliderBindings()
		{
			this.frameDocument = this.$refs.frame.contentDocument;
			if (BX.SidePanel.Instance?.registerAnchorListener)
			{
				BX.SidePanel.Instance.registerAnchorListener(this.frameDocument);

				return;
			}

			Event.bind(this.frameDocument, 'click', BX.SidePanel.Instance.handleAnchorClick, { capture: true });
		},
		unregisterSliderBindings()
		{
			if (!this.frameDocument)
			{
				return;
			}

			if (BX.SidePanel.Instance?.unregisterAnchorListener)
			{
				BX.SidePanel.Instance.unregisterAnchorListener(this.frameDocument);

				return;
			}

			Event.unbind(this.frameDocument, 'click', BX.SidePanel.Instance.handleAnchorClick, { capture: true });
		},
	},
	template: `
		<div class="bx-im-content-openlines__container">
			<iframe class="bx-im-content-openlines__iframe" :src="iframeLink" ref="frame" />
			<OpenlinesLoader v-if="isLoading" />
		</div>
	`,
};
