import {Parser} from 'im.v2.lib.parser';

import type {AttachGridItemConfig} from 'im.v2.const';

export const AttachGridItemDisplayType = {
	block: 'block',
	line: 'line',
	row: 'row'
};
const DisplayType = AttachGridItemDisplayType;

// @vue/component
export const AttachGridItem = {
	name: 'AttachGridItem',
	props:
	{
		config: {
			type: Object,
			default: () => {}
		}
	},
	computed:
	{
		DisplayType: () => DisplayType,
		internalConfig(): AttachGridItemConfig
		{
			return this.config;
		},
		display(): $Values<typeof DisplayType>
		{
			return this.internalConfig.DISPLAY.toLowerCase();
		},
		width(): string
		{
			if (!this.value || !this.internalConfig.WIDTH)
			{
				return '';
			}

			return `${this.internalConfig.WIDTH}px`;
		},
		value(): string
		{
			if (!this.internalConfig.VALUE)
			{
				return '';
			}

			return Parser.decodeText(this.internalConfig.VALUE);
		},
		color(): string
		{
			return this.internalConfig.COLOR || '';
		},
		name(): string
		{
			return this.internalConfig.NAME;
		},
		link(): string
		{
			return this.internalConfig.LINK;
		}
	},
	template: `
		<div v-if="display === DisplayType.block" :style="{width}" class="bx-im-attach-grid__item --block">
			<div class="bx-im-attach-grid__name">{{ name }}</div>
			<div v-if="link" class="bx-im-attach-grid__value --link">
				<a :href="link" target="_blank" :style="{color}" v-html="value"></a>
			</div>
			<div v-else v-html="value" :style="{color}" class="bx-im-attach-grid__value"></div>
		</div>
		<div v-if="display === DisplayType.line" :style="{width}" class="bx-im-attach-grid__item --line">
			<div class="bx-im-attach-grid__name">{{ name }}</div>
			<div v-if="link" :style="{color}" class="bx-im-attach-grid__value --link">
				<a :href="link" target="_blank" v-html="value"></a>
			</div>
			<div v-else class="bx-im-attach-grid__value" :style="{color}" v-html="value"></div>
		</div>
		<div v-if="display === DisplayType.row" class="bx-im-attach-grid__item --row">
			<table>
				<tbody>
					<tr>
						<td v-if="name" :colspan="value? 1: 2" :style="{width}" class="bx-im-attach-grid__name">
							{{ name }}
						</td>
						<td
							v-if="value && link"
							:colspan="name? 1: 2"
							:style="{color}"
							class="bx-im-attach-grid__value --link"
						>
							<a :href="link" target="_blank" v-html="value"></a>
						</td>
						<td
							v-if="value && !link"
							:colspan="name? 1: 2"
							:style="{color}"
							v-html="value"
							class="bx-im-attach-grid__value"
						>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
	`
};