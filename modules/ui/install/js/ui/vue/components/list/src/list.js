/**
 * Bitrix UI
 * Base list
 *
 * @package bitrix
 * @subpackage ui
 * @copyright 2001-2020 Bitrix
 */

import "./list.css";
import "./list-element";

import {Vue} from 'ui.vue';

Vue.component('bx-list',
{
	data()
	{
		return {
			generalSectionName: 'general',
			showSectionNames: true,
			resultList: {},
			itemTypes: {
				default: 'default',
				placeholder: 'placeholder'
			},
			cssPrefix: '',
			observer: null,
			elementComponent: 'bx-list-element',
		}
	},
	created()
	{
		this.initObserver();
	},
	methods:
	{
		/* region 01. Data validation */
		validateData(listData)
		{
			let result = [];

			listData.items.forEach(listItem => {
				result.push(this.validateItem(listItem));
			});

			this.list = result;

			this.validateSections(listData.sections);
		},

		validateItem(listItem)
		{
			let itemResult = {};

			if (typeof listItem.id === "number" || typeof listItem.id === "string")
			{
				itemResult.id = listItem.id.toString();
			}

			if (typeof listItem.type !== "undefined" && this.itemTypes[listItem.type])
			{
				itemResult.type = listItem.type;
			}
			else
			{
				itemResult.type = this.itemTypes.default;
			}

			if (typeof listItem.title !== "undefined")
			{
				itemResult.title = {};

				if (typeof listItem.title === 'object' && listItem.title)
				{
					if (typeof listItem.title.value === 'string')
					{
						itemResult.title.value = listItem.title.value;
					}
					if (typeof listItem.title.leftIcon === 'string')
					{
						itemResult.title.leftIcon = listItem.title.leftIcon;
					}
					if (typeof listItem.title.rightIcon === 'string')
					{
						itemResult.title.rightIcon = listItem.title.rightIcon;
					}
				}
				else if (typeof listItem.title === 'string')
				{
					itemResult.title.value = listItem.title;
				}
			}

			if (typeof listItem.subtitle !== "undefined")
			{
				itemResult.subtitle = {};

				if (typeof listItem.subtitle === 'object' && listItem.subtitle)
				{
					if (typeof listItem.subtitle.value === 'string')
					{
						itemResult.subtitle.value = listItem.subtitle.value;
					}
					if (typeof listItem.subtitle.leftIcon === 'string')
					{
						itemResult.subtitle.leftIcon = listItem.subtitle.leftIcon;
					}
				}
				else if (typeof listItem.subtitle === 'string')
				{
					itemResult.subtitle.value = listItem.subtitle;
				}
			}

			if (typeof listItem.avatar !== 'undefined')
			{
				itemResult.avatar = {};

				if (typeof listItem.avatar === 'object' && listItem.avatar)
				{
					//TODO: avatar processing
					if (typeof listItem.avatar.url === 'string')
					{
						itemResult.avatar.url = listItem.avatar.url;
					}
					if (typeof listItem.avatar.topLeftIcon === 'string')
					{
						itemResult.avatar.topLeftIcon = listItem.avatar.topLeftIcon;
					}
					if (typeof listItem.avatar.bottomRightIcon === 'string')
					{
						itemResult.avatar.bottomRightIcon = listItem.avatar.bottomRightIcon;
					}
				}
				else if (typeof listItem.avatar === 'string')
				{
					//TODO: avatar processing
					itemResult.avatar.url = listItem.avatar;
				}
			}

			if (typeof listItem.date !== 'undefined')
			{
				itemResult.date = {};

				if (typeof listItem.date === 'object' && listItem.date && !(listItem.date instanceof Date))
				{
					if (listItem.date.value instanceof Date)
					{
						itemResult.date.value = this.formatDate(listItem.date.value);
					}
					if (typeof listItem.date.leftIcon === 'string')
					{
						itemResult.date.leftIcon = listItem.date.leftIcon;
					}
				}
				else if (listItem.date instanceof Date)
				{
					itemResult.date.value = this.formatDate(listItem.date);
				}
			}

			if (typeof listItem.sectionCode === 'string')
			{
				itemResult.sectionCode = listItem.sectionCode;
			}

			if (typeof listItem.counter === 'number')
			{
				itemResult.counter = this.formatCounter(listItem.counter);
			}

			if (typeof listItem.notification === 'boolean')
			{
				itemResult.notification = listItem.notification;
			}

			return itemResult;
		},

		validateSections(sections)
		{
			if (sections && sections.length > 0)
			{
				sections.forEach(element => {
					if (typeof element === 'string' && element.length > 0)
					{
						this.sections.push(element)
					}
				});
			}

			if (this.sections.length === 0)
			{
				this.sections = [this.generalSectionName];
				this.list.map(element => {
					element.sectionCode = this.generalSectionName;
					return element;
				});
			}
		},

		formatCounter(counter)
		{
			if (counter > 999)
			{
				counter = 999;
			}
			else if (counter < 0)
			{
				counter = 0
			}

			return counter;
		},

		initObserver()
		{
			this.observer = new IntersectionObserver(function(entries){
				entries.forEach(entry => {
					if (entry.isIntersecting && entry.intersectionRatio === 1)
					{
						// console.warn('I SEE ', entry);
					}
				});
			}, {threshold: [0, 1]});
		},
		/* endregion 01. Data validation */

		/* region 02. Events handling */
		onScroll(event)
		{

		},

		onClick(event, id)
		{

		}
		/* endregion 02. Events handling */
	},
	computed:
	{
		wrapperStyle()
		{
			return this.cssPrefix + ' bx-vue-list-wrap'
		},

		list()
		{
			return [];
		},

		sections()
		{
			return [];
		},

		sectionedList()
		{
			this.sections.forEach(section => {
				Vue.set(this.resultList, section, []);

				let listForSection = this.list.filter(item => {
					return item.sectionCode === section;
				});

				this.resultList[section] = [...listForSection];
			});

			return this.resultList;
		}
	},
	directives:
	{
		'bx-list-observer':
		{
			inserted(element, bindings, vnode)
			{
				vnode.context.observer.observe(element);
			}
		}
	},
	template: `
		<div :class="wrapperStyle" @scroll="onScroll">
			<template v-for="section in sections">
				<div v-if="sections.length > 1 && sectionedList[section].length > 0 && showSectionNames" class="bx-vue-list-section">{{ section }}</div>
				<div
					v-for="listItem in sectionedList[section]"
					:key="listItem.id"
					@click="onClick($event, listItem.id)"
					@click.right="onRightClick($event, listItem.id)"
					v-bx-list-observer :data-id="listItem.id"
				>
					<component :is="elementComponent" :rawListItem="listItem" :itemTypes="itemTypes" />
				</div>
			</template>
		</div>
	`
});