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
import { AttachLinks } from "../mixin/attachLinks";
import { Utils } from 'im.lib.utils';

export const AttachTypeLink =
{
	property: 'LINK',
	name: 'bx-im-view-element-attach-link',
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
			getLinkName(element)
			{
				return element.NAME? element.NAME: element.LINK;
			},
			getDescription(element)
			{
				const text = element.HTML? element.HTML: element.DESC;
				return Utils.text.decode(text);
			}
		},
		computed:
		{
			imageComponentName()
			{
				return AttachTypeImage.name;
			},
		},
		components:
		{
			[AttachTypeImage.name]: AttachTypeImage.component
		},
		//language=Vue
		template: `
			<div class="bx-im-element-attach-type-link">
				<template v-for="(element, index) in config.LINK">
					<div class="bx-im-element-attach-type-link-element" :key="index">
						<a 
							v-if="element.LINK"
							:href="element.LINK"
							target="_blank"
							class="bx-im-element-attach-type-link-name" 
							@click="openLink({element: element, event: $event})"
						>
							{{getLinkName(element)}}
						</a>
						<span 
							v-else
							class="bx-im-element-attach-type-ajax-link"
							@click="openLink({element: element, event: $event})"
						>
							{{getLinkName(element)}}
						</span>
						<div v-if="element.DESC || element.HTML" class="bx-im-element-attach-type-link-desc" v-html="getDescription(element)"></div>
						<div 
							v-if="element.PREVIEW" 
							class="bx-im-element-attach-type-link-image"
							@click="openLink({element: element, event: $event})"
						>
							<component :is="imageComponentName" :config="getImageConfig(element)" :color="color"/>
						</div>
					</div>
				</template>
			</div>
		`
	},
};