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
import {Utils} from "im.utils";

export const AttachTypeRich =
{
	property: 'RICH_LINK',
	name: 'bx-messenger-element-attach-rich',
	component:
	{
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
			}
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
		template: `
			<div class="bx-im-element-attach-type-rich">
				<template v-for="(element, index) in config.RICH_LINK">
					<div class="bx-im-element-attach-type-rich-element" :key="index">
						<div v-if="element.PREVIEW" class="bx-im-element-attach-type-rich-image" @click="openLink(element)">
							<component :is="imageComponentName" :config="getImageConfig(element)" :color="color"/>
						</div>
						<div class="bx-im-element-attach-type-rich-name" @click="openLink(element)" v-html="element.NAME"></div>
						<div v-if="element.DESC" class="bx-im-element-attach-type-rich-desc" v-html="element.DESC"></div>
					</div>
				</template>
			</div>
		`
	},
};