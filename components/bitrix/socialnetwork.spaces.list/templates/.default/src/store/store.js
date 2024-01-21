import { createStore } from 'ui.vue3.vuex';
import { AddFormStore } from './add-form-store';
import { MainStore } from './main-store';

export const Store = createStore({
	modules: {
		addForm: AddFormStore,
		main: MainStore,
	},
});
