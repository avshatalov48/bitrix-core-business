import { Store } from 'ui.vue3.vuex';

export const EventsStore = {
	state(): Object
	{
		return {
			events: [],
			areEventsLoading: false,
			areEventsUpdating: false,
			isFilterMode: false,
		};
	},
	actions:
	{
		setEventsLoading: (store: Store, areEventsLoading: boolean) => {
			store.commit('setEventsLoading', areEventsLoading);
		},
		setEventsUpdating: (store: Store, areEventsUpdating: boolean) => {
			store.commit('setEventsUpdating', areEventsUpdating);
		},
		setEvents: (store: Store, events) => {
			store.commit('setEvents', events);
		},
		setFilterMode: (store, isFilterMode: boolean) => {
			store.commit('setFilterMode', isFilterMode);
		},
	},
	mutations:
	{
		setEventsLoading: (state, areEventsLoading: boolean) => {
			state.areEventsLoading = areEventsLoading;
		},
		setEventsUpdating: (state, areEventsUpdating: boolean) => {
			state.areEventsUpdating = areEventsUpdating;
		},
		setEvents: (state, events: EventModel[]) => {
			state.events = events;
		},
		setFilterMode: (state, isFilterMode: boolean) => {
			state.isFilterMode = isFilterMode;
		},
	},
	getters:
	{
		areEventsLoading: (state): boolean => state.areEventsLoading,
		areEventsUpdating: (state): boolean => state.areEventsUpdating,
		events: (state): EventModel[] => state.events,
		isFilterMode: (state): boolean => state.isFilterMode,
	},
};
