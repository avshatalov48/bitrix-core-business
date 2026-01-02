import { BIcon, Outline as OutlineIcons } from 'ui.icon-set.api.vue';

import { Color } from 'im.v2.const';
import { HealthCheckManager } from 'im.v2.lib.health-check';

import { HealthCheckService } from './classes/service-health';

import './css/service-health-panel.css';

import type { ServiceHealthResponse } from './classes/service-health';

const ICON_SIZE = 18;

// @vue/component
export const ServiceHealthPanel = {
	components: { BIcon },
	data(): { isShow: boolean, title: string, text: string }
	{
		return {
			isShow: false,
			title: '',
			text: '',
		};
	},
	computed:
	{
		Color: () => Color,
		OutlineIcons: () => OutlineIcons,
		ICON_SIZE: () => ICON_SIZE,
		isServiceHealthPanelShown(): boolean
		{
			return HealthCheckManager.getInstance().getIsShown();
		},
		processedTitle(): string
		{
			return BX.util.strip_tags(this.title);
		},
		processedText(): string
		{
			return BX.util.strip_tags(this.text);
		},
	},
	async created()
	{
		if (this.isServiceHealthPanelShown)
		{
			return;
		}

		const data = await (new HealthCheckService()).getServiceHealthStatus();
		if (!data)
		{
			return;
		}
		this.setData(data);
		this.show();
	},
	methods:
	{
		setData(data: ServiceHealthResponse): void
		{
			const { statusTitle, statusInfo } = data;
			this.title = statusTitle;
			this.text = statusInfo;
		},
		show(): void
		{
			this.isShow = true;
		},
		hide(): void
		{
			this.isShow = false;
			HealthCheckManager.getInstance().setIsShown(true);
		},
	},
	template: `
		<div v-if="isShow" class="bx-im-content-chat__service-health-panel">
			<div class="bx-im-content-chat__service-health-panel_content">
				<div
					v-if="processedTitle"
					:title="processedTitle"
					class="bx-im-content-chat__service-health-panel_title --ellipsis"
				>
					{{ processedTitle }}
				</div>
				<div
					v-if="processedText"
					:title="processedText"
					class="bx-im-content-chat__service-health-panel_text --line-clamp-4"
				>
					{{ processedText }}
				</div>
			</div>
			<button
				class="bx-im-content-chat__service-health-panel_close-button"
				@click="hide"
			>
				<BIcon
					:name="OutlineIcons.CROSS_M"
					:color="Color.white"
					:size="ICON_SIZE"
					:hoverable="false"
				/>
			</button>
		</div>
	`,
};
