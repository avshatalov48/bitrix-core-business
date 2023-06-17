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
			type: 'calendar',
		};
	},
	created()
	{
	},
	methods: {
	},
	template: `
		<div class="calendar-sharing-alert-container">
			<div class="calendar-sharing-alert-info">
				<div class="calendar-sharing-alert-info-empty --icon-cross">
					<div class="calendar-sharing-alert-title">
						{{ $Bitrix.Loc.getMessage('CALENDAR_SHARING_ALERT_CALENDAR_TITLE') }}
					</div>
					<div class="calendar-sharing-alert-description">
						{{ $Bitrix.Loc.getMessage('CALENDAR_SHARING_ALERT_CALENDAR_DESCRIPTION') }}
					</div>
				</div>
			</div>
		</div>
	`
};