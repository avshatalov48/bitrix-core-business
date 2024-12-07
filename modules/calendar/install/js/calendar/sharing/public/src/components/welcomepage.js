import {Dom} from 'main.core';
import { DateSelector } from './calendar/dateselector';

export const WelcomePage = {
	props: {
		owner: Object,
	},
	mounted()
	{
		this.setPageVisualSettings();
	},
	methods: {
		async closeWelcomePage()
		{
			// await BX.ajax.runAction('calendar.api.sharingajax.saveFirstEntry');

			this.$Bitrix.eventEmitter.emit('calendar:sharing:changeApplicationType', {type: 'calendar'});
		},
		setPageVisualSettings()
		{
			const htmlNode = document.querySelector('html');
			const bodyNode = document.querySelector('body');

			if (Dom.hasClass(htmlNode, 'calendar-sharing--bg-gray'))
			{
				Dom.removeClass(htmlNode, 'calendar-sharing--bg-gray')
			}
			if (!Dom.hasClass(htmlNode, 'calendar-sharing--bg-blue'))
			{
				Dom.addClass(htmlNode, 'calendar-sharing--bg-blue')
			}
			if (!Dom.hasClass(htmlNode, 'calendar-sharing-html-body-center'))
			{
				Dom.addClass(htmlNode, 'calendar-sharing-html-body-center');
			}
			if (!Dom.hasClass(bodyNode, 'calendar-sharing-html-body-center'))
			{
				Dom.addClass(bodyNode, 'calendar-sharing-html-body-center');
			}
		},
	},
	template: `
		<div class="calendar-sharing-welcome-page__container calendar-sharing--subtract">
			<div class="calendar-sharing-welcome-page__photo ui-icon ui-icon-common-user">
				<img class="calendar-sharing-welcome-page__photo_item" :src="owner.photo" alt="" v-if="owner.photo">
				<i class="calendar-sharing-welcome-page__photo_item" v-else></i>
			</div>
			<div class="calendar-sharing-welcome-page_title">
				{{ owner.name }} {{ owner.lastName }}
			</div>
			<div class="calendar-sharing-welcome-page_subtitle">
				{{ $Bitrix.Loc.getMessage('CALENDAR_SHARING_WELCOME_PAGE_TEXT') }}
			</div>
			<button class="ui-btn ui-btn-success ui-btn-round" @click="closeWelcomePage">
				{{ $Bitrix.Loc.getMessage('CALENDAR_SHARING_WELCOME_PAGE_NEXT') }}
			</button>
		</div>
	`
};
