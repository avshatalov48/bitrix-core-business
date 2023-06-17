/**
 * Bitrix Messenger
 * Vue component
 *
 * Grid (attach type)
 *
 * @package bitrix
 * @subpackage im
 * @copyright 2001-2019 Bitrix
 */

import "./grid.css";
import {Utils} from "im.lib.utils";
import {MessagesModel} from "im.model";
import { AttachLinks } from "../mixin/attachLinks";

export const AttachTypeGrid =
{
	property: 'GRID',
	name: 'bx-im-view-element-attach-grid',
	component:
	{
		mixins: [
			AttachLinks
		],
		props:
		{
			config: {type: Object, default: {}},
			color: {type: String, default: 'transparent'},
		},
		created()
		{
			if (Utils.platform.isBitrixMobile())
			{
				this.maxCellWith = Math.floor(Math.min(screen.availWidth, screen.availHeight)/4);
			}
			else
			{
				this.maxCellWith = null;
			}
		},
		methods:
		{
			getWidth(element)
			{
				if (element.DISPLAY !== 'row')
				{
					return element.WIDTH? element.WIDTH+'px': '';
				}

				if (!element.VALUE)
				{
					return false;
				}

				if (this.maxCellWith && element.WIDTH > this.maxCellWith)
				{
					return this.maxCellWith+'px';
				}

				return element.WIDTH? element.WIDTH+'px': '';
			},

			getValueColor(element)
			{
				if (!element.COLOR)
				{
					return false;
				}

				return element.COLOR;
			},

			getValue(element)
			{
				if (!element.VALUE)
				{
					return '';
				}

				return Utils.text.decode(element.VALUE);
			},
		},
		//language=Vue
		template: `
			<div class="bx-im-element-attach-type-grid">
				<template v-for="(element, index) in config.GRID">
					<template v-if="element.DISPLAY.toLowerCase() === 'block'">
						<div class="bx-im-element-attach-type-grid-display bx-im-element-attach-type-display-block" :style="{width: getWidth(element)}">
							<div class="bx-im-element-attach-type-grid-element-name">{{element.NAME}}</div>
							<template v-if="element.LINK">
								<div class="bx-im-element-attach-type-grid-element-value bx-im-element-attach-type-grid-element-value-link">
									<a :href="element.LINK" target="_blank" @click="openLink({element: element, event: $event})" :style="{color: getValueColor(element)}" v-html="getValue(element)"></a>
								</div>
							</template>
							<template v-else>
								<div class="bx-im-element-attach-type-grid-element-value" :style="{color: getValueColor(element)}" v-html="getValue(element)"></div>
							</template>
						</div>
					</template>
					<template v-else-if="element.DISPLAY.toLowerCase() === 'line'">
						<div class="bx-im-element-attach-type-grid-display bx-im-element-attach-type-display-card" :style="{width: getWidth(element)}">
							<div class="bx-im-element-attach-type-grid-element-name">{{element.NAME}}</div>
							<template v-if="element.LINK">
								<div
									class="bx-im-element-attach-type-grid-element-value bx-im-element-attach-type-grid-element-value-link"
									:style="{color: element.COLOR? element.COLOR: ''}"
								>
									<a :href="element.LINK" target="_blank" @click="openLink({element: element, event: $event})" v-html="getValue(element)"></a>
								</div>
							</template>
							<template v-else>
								<div class="bx-im-element-attach-type-grid-element-value" :style="{color: element.COLOR? element.COLOR: ''}" v-html="getValue(element)"></div>
							</template>
						</div>
					</template>
					<template v-else-if="element.DISPLAY.toLowerCase() === 'row'">
						<div class="bx-im-element-attach-type-grid-display bx-im-element-attach-type-display-column">
							<table class="bx-im-element-attach-type-display-column-table">
								<tbody>
									<tr>
										<template v-if="element.NAME">
											<td class="bx-im-element-attach-type-grid-element-name" :colspan="element.VALUE? 1: 2" :style="{width: getWidth(element)}">{{element.NAME}}</td>
										</template>
										<template v-if="element.VALUE">
											<template v-if="element.LINK">
												<td
													class="bx-im-element-attach-type-grid-element-value bx-im-element-attach-type-grid-element-value-link"
													:colspan="element.NAME? 1: 2"
													:style="{color: element.COLOR? element.COLOR: ''}"
												>
													<a :href="element.LINK" target="_blank" @click="openLink({element: element, event: $event})" v-html="getValue(element)"></a>
												</td>
											</template>
											<template v-else>
												<td class="bx-im-element-attach-type-grid-element-value" :colspan="element.NAME? 1: 2" :style="{color: element.COLOR? element.COLOR: ''}" v-html="getValue(element)"></td>
											</template>
										</template>
									</tr>
								</tbody>
							</table>
						</div>
					</template>
				</template>
			</div>
		`
	},
};