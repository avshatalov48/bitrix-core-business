/**
 * Bitrix Messenger
 * Vue component
 *
 * Rich (attach type)
 *
 * @package bitrix
 * @subpackage im
 * @copyright 2001-2019 Bitrix
 */

import "./rich.css";
import {AttachTypeImage} from "./image";
import { AttachLinks } from "../mixin/attachLinks";

export const AttachTypeRich =
{
	property: 'RICH_LINK',
	name: 'bx-im-view-element-attach-rich',
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
		methods:
		{
			getImageConfig(element)
			{
				return {IMAGE: [{
					NAME: element.NAME,
					PREVIEW: element.PREVIEW,
					WIDTH: element.WIDTH,
					HEIGHT: element.HEIGHT,
				}]};
			},
		},
		computed:
		{
			imageComponentName()
			{
				return AttachTypeImage.name;
			}
		},
		components:
		{
			[AttachTypeImage.name]: AttachTypeImage.component
		},
		//language=Vue
		template: `
			<div class="bx-im-element-attach-type-rich">
				<template v-for="(element, index) in config.RICH_LINK">
					<div class="bx-im-element-attach-type-rich-element" :key="index">
						<div v-if="element.PREVIEW" class="bx-im-element-attach-type-rich-image" @click="openLink({element: element, event: $event})">
							<component :is="imageComponentName" :config="getImageConfig(element)" :color="color"/>
						</div>
						<div class="bx-im-element-attach-type-rich-name" @click="openLink({element: element, event: $event})">{{element.NAME}}</div>
						<div v-if="element.HTML || element.DESC" class="bx-im-element-attach-type-rich-desc">{{element.HTML || element.DESC}}</div>
					</div>
				</template>
			</div>
		`
	},
};