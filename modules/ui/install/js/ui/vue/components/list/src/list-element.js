/**
 * Bitrix UI
 * Base list element
 *
 * @package bitrix
 * @subpackage ui
 * @copyright 2001-2020 Bitrix
 */

import {Vue} from 'ui.vue';

Vue.component('bx-list-element',
{
	props: [
		'rawListItem',
		'itemTypes'
	],
	computed:
		{
			imageStyle()
			{
				return {};
			},

			imageClass()
			{
				return 'bx-vue-list-item-image';
			},

			avatarText()
			{
				let words = this.listItem.title.value.split(' ');
				if (words.length > 1)
				{
					return words[0].charAt(0) + words[1].charAt(0);
				}
				else if (words.length === 1)
				{
					return words[0].charAt(0);
				}
			},

			listItemStyle()
			{
				return {};
			},

			listItem()
			{
				return this.rawListItem;
			}
		},
		template: `
		<div class="bx-vue-list-item" :style="listItemStyle">
			<template v-if="listItem.template !== itemTypes.placeholder">
				<div v-if="listItem.avatar" class="bx-vue-list-item-image-wrap">
					<img v-if="listItem.avatar.url" :src="listItem.avatar.url" :style="imageStyle" :class="imageClass" alt="">
					<div v-else-if="!listItem.avatar.url" :style="imageStyle" class="bx-vue-list-item-image-text">{{ avatarText }}</div>	
					<div v-if="listItem.avatar.topLeftIcon" :class="'bx-vue-list-icon-avatar-top-left bx-vue-list-avatar-top-left-' + listItem.avatar.topLeftIcon"></div>
					<div v-if="listItem.avatar.bottomRightIcon" :class="'bx-vue-list-icon-avatar-bottom-right bx-vue-list-avatar-bottom-right-' + listItem.avatar.bottomRightIcon"></div>
				</div>
				<div class="bx-vue-list-item-content">
					<div class="bx-vue-list-item-content-header">
						<div v-if="listItem.title" class="bx-vue-list-item-header-title">
							<div v-if="listItem.title.leftIcon" :class="'bx-vue-list-icon-title-left bx-vue-list-icon-title-left-' + listItem.title.leftIcon"></div>
							<span class="bx-vue-list-item-header-title-text">{{ listItem.title.value }}</span>
							<div v-if="listItem.title.rightIcon" :class="'bx-vue-list-icon-title-right bx-vue-list-icon-title-right-' + listItem.title.rightIcon"></div>
						</div>
						<div v-if="listItem.date" class="bx-vue-list-item-header-date">
							<div v-if="listItem.date.leftIcon" :class="'bx-vue-list-icon-date-left bx-vue-list-icon-date-left-' + listItem.date.leftIcon"></div>
							{{ listItem.date.value }}
						</div>
					</div>
					<div class="bx-vue-list-item-content-bottom">
						<div v-if="listItem.subtitle" class="bx-vue-list-item-bottom-subtitle">
							<div v-if="listItem.subtitle.leftIcon" :class="'bx-vue-list-icon-subtitle-left bx-vue-list-icon-subtitle-left-' + listItem.subtitle.leftIcon"></div>
							<span class="bx-vue-list-item-bottom-subtitle-text">{{ listItem.subtitle.value }}</span>
						</div>
						<div class="bx-vue-list-item-bottom-counter">
							<div v-if="listItem.counter.leftIcon" :class="'bx-vue-list-icon-counter-left bx-vue-list-icon-counter-left-' + listItem.counter.leftIcon"></div>
							<div v-if="listItem.counter.value > 0" class="bx-vue-list-item-bottom-counter-value">{{ listItem.counter.value }}</div>
							<div v-else-if="listItem.notification" class="bx-vue-list-item-bottom-counter-notification"></div>
						</div>
					</div>
				</div>
			</template>
			<template v-else-if="listItem.template === itemTypes.placeholder">
				<div class="bx-vue-list-item-image-wrap"><img src="https://www.ischool.berkeley.edu/sites/default/files/default_images/avatar.jpeg" alt="" class="bx-vue-list-item-image"></div>
				<div class="bx-vue-list-item-content">
					<div class="bx-vue-list-item-content-header">
						<div class="bx-vue-list-item-placeholder-title"></div>
					</div>
					<div class="bx-vue-list-item-content-bottom">
						<div class="bx-vue-list-item-bottom-subtitle">
							<div class="bx-vue-list-item-placeholder-subtitle"></div>
						</div>
					</div>
				</div>
			</template>
		</div>
	`
});