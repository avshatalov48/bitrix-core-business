import { defineStore } from 'ui.vue3.pinia';
import { GroupData } from '@/types/group';

export const useGlobalState = defineStore('global-state', {
	state: () => ({
		searchApplied: false,
		filtersApplied: false,
		currentGroup: GroupData,
		shouldShowWelcomeStub: true,
	})
});