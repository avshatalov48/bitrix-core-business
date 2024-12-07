import { CategoryEditForm } from '../edit-form/category-edit-form';
import 'ui.icon-set.main';
import 'ui.icon-set.actions';
import './header.css';

export const CategoriesTitle = {
	methods: {
		onSearchClick(): void
		{
			this.$store.dispatch('setSearchMode', true);
		},
		onAddClick(): void
		{
			this.$refs.editForm.show({
				create: true,
			});
		},
	},
	components: {
		CategoryEditForm,
	},
	template: `
		<div class="calendar-open-events-list-categories-title">
			<div class="calendar-open-events-list-categories-title-text">
				{{ $Bitrix.Loc.getMessage('CALENDAR_OPEN_EVENTS_LIST_CATEGORIES') }}
			</div>
			<div class="calendar-open-events-list-categories-title-button" @click="onSearchClick()">
				<div class="ui-icon-set --search-2"></div>
			</div>
			<div class="calendar-open-events-list-categories-title-button" @click="onAddClick()">
				<div class="ui-icon-set --plus-30"></div>
			</div>
		</div>
		<CategoryEditForm ref="editForm"/>
	`,
};
