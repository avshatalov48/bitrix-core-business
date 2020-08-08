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

export const AttachTypeGrid =
{
	property: 'GRID',
	name: 'bx-im-view-element-attach-grid',
	component:
	{
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
			openLink(element)
			{
				if (element.LINK)
				{
					Utils.platform.openNewPage(element.LINK);
				}
				else
				{
					// element.NETWORK_ID
					// element.USER_ID
					// element.CHAT_ID
					// TODO exec openDialog with params
				}
			},
			getWidth(element)
			{
				if (this.type !== 'row')
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

			getValue(element)
			{
				if (!element.VALUE)
				{
					return '';
				}

				return MessagesModel.decodeBbCode({text: element.VALUE});
			},
		},
		computed:
		{
			type()
			{
				return this.config.GRID[0].DISPLAY.toLowerCase();
			},
		},
		template: `
			<div class="bx-im-element-attach-type-grid">
				<template v-if="type === 'block'">
					<template v-for="(element, index) in config.GRID">
						<div class="bx-im-element-attach-type-grid-display bx-im-element-attach-type-display-block" :style="{width: getWidth(element)}">
							<div class="bx-im-element-attach-type-grid-element-name" v-html="element.NAME"></div>
							<template v-if="element.LINK">
								<div class="bx-im-element-attach-type-grid-element-value bx-im-element-attach-type-grid-element-value-link" @click="openLink(element)" v-html="getValue(element)"></div>
							</template>
							<template v-else>
								<div class="bx-im-element-attach-type-grid-element-value" v-html="getValue(element)"></div>
							</template>
						</div>	
					</template>
				</template>
				<template v-else-if="type === 'line'">
					<template v-for="(element, index) in config.GRID">
						<div class="bx-im-element-attach-type-grid-display bx-im-element-attach-type-display-card" :style="{width: getWidth(element)}">
							<div class="bx-im-element-attach-type-grid-element-name" v-html="element.NAME"></div>
							<template v-if="element.LINK">
								<div class="bx-im-element-attach-type-grid-element-value bx-im-element-attach-type-grid-element-value-link" @click="openLink(element)" v-html="getValue(element)" :style="{color: element.COLOR? element.COLOR: ''}"></div>
							</template>
							<template v-else>
								<div class="bx-im-element-attach-type-grid-element-value" v-html="getValue(element)" :style="{color: element.COLOR? element.COLOR: ''}"></div>
							</template>
						</div>
					</template>
				</template>
				<template v-else-if="type === 'row'">
					<div class="bx-im-element-attach-type-grid-display bx-im-element-attach-type-display-column">
						<table class="bx-im-element-attach-type-display-column-table">
							<tbody>
								<template v-for="(element, index) in config.GRID">
									<tr>
										<template v-if="element.NAME">
											<td class="bx-im-element-attach-type-grid-element-name" :colspan="element.VALUE? 1: 2" v-html="element.NAME" :style="{width: getWidth(element)}"></td>
										</template>
										<template v-if="element.VALUE">
											<template v-if="element.LINK">
												<td class="bx-im-element-attach-type-grid-element-value bx-im-element-attach-type-grid-element-value-link" @click="openLink(element)" v-html="getValue(element)" :colspan="element.NAME? 1: 2" :style="{color: element.COLOR? element.COLOR: ''}"></td>
											</template>
											<template v-else>
												<td class="bx-im-element-attach-type-grid-element-value" v-html="getValue(element)" :colspan="element.NAME? 1: 2" :style="{color: element.COLOR? element.COLOR: ''}"></td>
											</template>
										</template>
									</tr>
								</template>
							</tbody>
						</table>
					</div>
				</template>
			</div>
		`
	},
};