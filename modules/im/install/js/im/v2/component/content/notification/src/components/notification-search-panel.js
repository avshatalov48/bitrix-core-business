import 'ui.forms';
import '../css/notification-search-panel.css';

// @vue/component
export const NotificationSearchPanel = {
	name: 'NotificationSearchPanel',
	props: {
		schema: {
			type: Object,
			required: true
		}
	},
	emits: ['search'],
	data: function()
	{
		return {
			searchQuery: '',
			searchType: '',
			searchDate: '',
		};
	},
	computed:
	{
		filterTypes()
		{
			const originalSchema = {...this.schema};

			// get rid of some subcategories
			const modulesToRemove = [
				'timeman', 'mail', 'disk', 'bizproc', 'voximplant', 'sender', 'blog', 'vote', 'socialnetwork',
				'imopenlines', 'photogallery', 'intranet', 'forum'
			];
			modulesToRemove.forEach(moduleId => {
				if (originalSchema[moduleId])
				{
					delete originalSchema[moduleId].LIST;
				}
			});

			// rename some groups
			if (originalSchema.calendar)
			{
				originalSchema.calendar.NAME = this.$Bitrix.Loc.getMessage('IM_NOTIFICATIONS_SEARCH_FILTER_TYPE_CALENDAR');
			}
			if (originalSchema.sender)
			{
				originalSchema['sender'].NAME = this.$Bitrix.Loc.getMessage('IM_NOTIFICATIONS_SEARCH_FILTER_TYPE_SENDER');
			}
			if (originalSchema.blog)
			{
				originalSchema.blog.NAME = this.$Bitrix.Loc.getMessage('IM_NOTIFICATIONS_SEARCH_FILTER_TYPE_BLOG');
			}
			if (originalSchema.socialnetwork)
			{
				originalSchema['socialnetwork'].NAME = this.$Bitrix.Loc.getMessage('IM_NOTIFICATIONS_SEARCH_FILTER_TYPE_SOCIALNETWORK');
			}
			if (originalSchema.intranet)
			{
				originalSchema['intranet'].NAME = this.$Bitrix.Loc.getMessage('IM_NOTIFICATIONS_SEARCH_FILTER_TYPE_INTRANET');
			}

			// we need only this modules in this order!
			const modulesToShowInFilter = [
				'tasks', 'calendar', 'crm', 'timeman', 'mail', 'disk', 'bizproc', 'voximplant', 'sender',
				'blog', 'vote', 'socialnetwork', 'imopenlines', 'photogallery', 'intranet', 'forum'
			];
			const notificationFilterTypes = [];
			modulesToShowInFilter.forEach(moduleId => {
				if (originalSchema[moduleId])
				{
					notificationFilterTypes.push(originalSchema[moduleId]);
				}
			});

			return notificationFilterTypes;
		},
	},
	watch:
	{
		searchQuery()
		{
			this.search();
		},
		searchType()
		{
			this.search();
		},
		searchDate()
		{
			this.search();
		},
	},
	methods:
	{
		search()
		{
			this.$emit('search', {
				searchQuery: this.searchQuery,
				searchType: this.searchType,
				searchDate: this.searchDate
			});
		},
		onDateFilterClick(event)
		{
			if (BX && BX.calendar && BX.calendar.get().popup)
			{
				BX.calendar.get().popup.close();
			}

			// eslint-disable-next-line bitrix-rules/no-bx
			BX.calendar({
				node: event.target,
				field: event.target,
				bTime: false,
				callback_after: () => {
					this.searchDate = event.target.value;
				}
			});

			return false;
		}
	},
	template: `
		<div class="bx-im-notifications-header-filter-box">
			<div class="ui-ctl ui-ctl-after-icon ui-ctl-dropdown ui-ctl-sm ui-ctl-w25">
				<div class="ui-ctl-after ui-ctl-icon-angle"></div>
				<select class="ui-ctl-element" v-model="searchType">
					<option value="">
						{{ $Bitrix.Loc.getMessage('IM_NOTIFICATIONS_SEARCH_FILTER_TYPE_PLACEHOLDER') }}
					</option>
					<template v-for="group in filterTypes">
						<template v-if="group.LIST">
							<optgroup :label="group.NAME">
								<option v-for="option in group.LIST" :value="option.ID">
									{{ option.NAME }}
								</option>
							</optgroup>
						</template>
						<template v-else>
							<option :value="group.MODULE_ID">
								{{ group.NAME }}
							</option>
						</template>
					</template>
				</select>
			</div>
			<div class="ui-ctl ui-ctl-textbox ui-ctl-after-icon ui-ctl-sm ui-ctl-w50">
				<button class="ui-ctl-after ui-ctl-icon-clear" @click.prevent="searchQuery=''"></button>
				<input
					autofocus
					type="text"
					class="ui-ctl-element"
					v-model="searchQuery"
					:placeholder="$Bitrix.Loc.getMessage('IM_NOTIFICATIONS_SEARCH_FILTER_TEXT_PLACEHOLDER')"
				>
			</div>
			<div class="ui-ctl ui-ctl-after-icon ui-ctl-before-icon ui-ctl-sm ui-ctl-w25">
				<div class="ui-ctl-before ui-ctl-icon-calendar"></div>
				<input
					type="text"
					class="ui-ctl-element ui-ctl-textbox"
					v-model="searchDate"
					@focus.prevent.stop="onDateFilterClick"
					@click.prevent.stop="onDateFilterClick"
					:placeholder="$Bitrix.Loc.getMessage('IM_NOTIFICATIONS_SEARCH_FILTER_DATE_PLACEHOLDER')"
					readonly
				>
				<button class="ui-ctl-after ui-ctl-icon-clear" @click.prevent="searchDate=''"></button>
			</div>
		</div>
	`
};