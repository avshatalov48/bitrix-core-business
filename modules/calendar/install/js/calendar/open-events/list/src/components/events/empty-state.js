import './css/empty-state.css';

export const EmptyState = {
	template: `
		<div class="calendar-open-events-list-events-empty">
			<div class="calendar-open-events-list-events-empty-icon"></div>
			<div class="calendar-open-events-list-events-empty-title">
				{{ $Bitrix.Loc.getMessage('CALENDAR_OPEN_EVENTS_LIST_EMPTY_STATE') }}
			</div>
		</div>
	`,
};
