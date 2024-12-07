import { Loc } from 'main.core';
import { Counter } from 'ui.cnt';
import { mapGetters } from 'ui.vue3.vuex';
import { EventManager } from '../../data-manager/event-manager/event-manager';
import { CategoryModel } from '../../model/category/category';
import 'ui.icon-set.main';

export const Category = {
	props: {
		category: CategoryModel,
	},
	computed: {
		...mapGetters({
			selectedCategoryId: 'selectedCategoryId',
		}),
	},
	methods: {
		async onClick(): void
		{
			await this.$store.dispatch('selectCategory', this.category.id);
			await this.$store.dispatch('setEventsLoading', true);

			const events = await EventManager.getEvents(this.category.id);

			if (this.selectedCategoryId !== this.category.id)
			{
				return;
			}

			await this.$store.dispatch('setEvents', events);
			await this.$store.dispatch('setEventsLoading', false);
		},
		getEventCountPhrase(eventsCount: number): string
		{
			return Loc.getMessagePlural('CALENDAR_OPEN_EVENTS_LIST_CATEGORY_EVENTS_COUNT', eventsCount, {
				'#COUNT#': eventsCount,
			});
		},
		renderCounter(): void
		{
			this.$refs.counter.innerHTML = '';
			if (this.category.newCount > 0)
			{
				new Counter({
					value: this.category.newCount,
					color: this.category.isMuted ? Counter.Color.GRAY : Counter.Color.DANGER,
				}).renderTo(this.$refs.counter);
			}
		},
	},
	mounted(): void
	{
		this.renderCounter();
	},
	watch: {
		category(): void
		{
			this.renderCounter();
		},
	},
	template: `
		<div
			class="calendar-open-events-list-category"
			:class="{
				'--banned': category.isBanned,
				'--selected': category.isSelected,
				'--all-category': category.id === 0,
			}"
			:data-category-id="category.id"
		>
			<div class="calendar-open-events-list-category-inner" @click="onClick">
				<div class="calendar-open-events-list-category-title">
					<div class="ui-icon-set --calendar-2" v-if="category.id === 0"></div>
					<div
						class="calendar-open-events-list-category-title-name"
						:title="category.name"
					>
						<span>{{ category.name }}</span>
						<div class="ui-icon-set --sound-off" v-if="category.isMuted && !category.isBanned"></div>
						<div class="ui-icon-set --lock" v-if="category.closed"></div>
					</div>
					<div ref="counter"></div>
				</div>
				<div
					class="calendar-open-events-list-category-info"
					v-html="getEventCountPhrase(category.eventsCount)"
					v-if="category.id !== 0"
				>
				</div>
			</div>
		</div>
	`,
};
