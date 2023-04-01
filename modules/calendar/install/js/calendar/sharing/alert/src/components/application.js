import { Dom, Type, Loc } from 'main.core';
import { Util } from 'calendar.util';
import '../css/alert.css';

export const Application = {
	props: {
		link: {
			type: Object,
			default: null
		},
	},
	name: 'Application',
	data()
	{
		return {
			type: '',
			returnButton: {
				text: Loc.getMessage('CALENDAR_SHARING_ALERT_RETURN_BUTTON'),
				disabled: false,
			}
		};
	},
	created()
	{
		if (this.link && Type.isObject(this.link))
		{
			this.type = 'event';
		}
		else
		{
			this.type = 'calendar';
		}

		this.setPageVisualSettings();
	},
	methods: {
		setPageVisualSettings()
		{
			const htmlNode = document.querySelector('html');
			const bodyNode = document.querySelector('body');

			if (!Dom.hasClass(bodyNode, 'calendar-sharing--public-body'))
			{
				Dom.addClass(bodyNode, 'calendar-sharing--public-body');
			}
			if (!Dom.hasClass(htmlNode, 'calendar-sharing--public-html'))
			{
				Dom.addClass(htmlNode, 'calendar-sharing--public-html');
			}

			Dom.addClass(htmlNode, 'calendar-sharing--bg-red');
			Dom.addClass(htmlNode, 'calendar-sharing--alert');

			if (Util.isMobileBrowser())
			{
				if (!Dom.hasClass(bodyNode, 'calendar-sharing--public-body-mobile'))
				{
					Dom.addClass(bodyNode, 'calendar-sharing--public-body-mobile');
				}
				if (!Dom.hasClass(htmlNode, 'calendar-sharing--public-html-mobile'))
				{
					Dom.addClass(htmlNode, 'calendar-sharing--public-html-mobile');
				}
			}
		},
		handleReturnButtonClick()
		{
			this.returnButton.disabled = true;
			if (this.link.userLinkHash)
			{
				const sharingPath = '/pub/calendar-sharing/';

				window.location.href = document.location.origin
					+ sharingPath
					+ this.link.userLinkHash
				;
			}
			this.returnButton.disabled = false;
		},
	},
	template: `
		<div class="calendar-sharing-alert-container">
			<div class="calendar-sharing-alert-icon"></div>
			<div class="calendar-sharing-alert-info" v-if="type === 'event'">
				<div class="calendar-sharing-alert-title">
					{{ $Bitrix.Loc.getMessage('CALENDAR_SHARING_ALERT_EVENT_TITLE') }}
				</div>
				<div class="calendar-sharing-alert-description">
					{{ $Bitrix.Loc.getMessage('CALENDAR_SHARING_ALERT_EVENT_DESCRIPTION') }}
				</div>
				<div class="ui-btn-container ui-btn-container-center calendar-shared-alert_btn-box" v-if="link.userLinkHash">
					<button
						class="ui-btn ui-btn-success ui-btn-round"
						@click="handleReturnButtonClick"
						:disabled="returnButton.disabled"
					>
						{{ returnButton.text }}
					</button>
				</div>
			</div>
			<div class="calendar-sharing-alert-info" v-else>
				<div class="calendar-sharing-alert-title">
					{{ $Bitrix.Loc.getMessage('CALENDAR_SHARING_ALERT_CALENDAR_TITLE') }}
				</div>
				<div class="calendar-sharing-alert-description">
					{{ $Bitrix.Loc.getMessage('CALENDAR_SHARING_ALERT_CALENDAR_DESCRIPTION') }}
				</div>
			</div>
		</div>
	`
};