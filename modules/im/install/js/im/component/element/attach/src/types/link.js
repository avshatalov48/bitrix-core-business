/**
 * Bitrix Messenger
 * Vue component
 *
 * Link (attach type)
 *
 * @package bitrix
 * @subpackage im
 * @copyright 2001-2019 Bitrix
 */

import "./link.css";
import {AttachTypeImage} from "./image";
import {Utils} from "im.utils";

export const AttachTypeLink =
{
	property: 'LINK',
	name: 'bx-messenger-element-attach-link',
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
			<div class="bx-im-element-attach-type-link">
				<template v-for="(element, index) in config.LINK">
					<div class="bx-im-element-attach-type-link-element" :key="index">
						<div v-if="element.PREVIEW" class="bx-im-element-attach-type-link-image" @click="openLink(element)">
							<component :is="imageComponentName" :config="getImageConfig(element)" :color="color"/>
						</div>
						<div class="bx-im-element-attach-type-link-name" @click="openLink(element)" v-html="element.NAME"></div>
						<div v-if="element.DESC" class="bx-im-element-attach-type-link-desc" v-html="element.DESC"></div>
					</div>
				</template>
			</div>
		`
	},
};