import { Event } from 'main.core';
import { Menu, Popup } from 'main.popup';
import { Counter } from 'ui.cnt';
import { mapGetters } from 'ui.vue3.vuex';
import { EventModel } from '../../../model/event/open-event';
import '../css/name-with-counter.css';

export const NameWithCounter = {
	emits: ['openEvent'],
	props: {
		event: EventModel,
	},
	computed: {
		...mapGetters({
			selectedCategoryId: 'selectedCategoryId',
		}),
		formattedRrule(): string
		{
			if (this.event.rrule.amount === 0 || this.event.rrule.amount === Infinity)
			{
				return '';
			}

			return this.$Bitrix.Loc.getMessage('CALENDAR_OPEN_EVENTS_LIST_NUM_MEETING_OF_AMOUNT', {
				'#NUM#': this.event.rrule.num,
				'#AMOUNT#': this.event.rrule.amount,
			});
		},
	},
	methods: {
		renderCounter(): void
		{
			this.$refs.counter.innerHTML = '';
			if (this.event.isNew)
			{
				new Counter({ value: 1, color: Counter.Color.DANGER }).renderTo(this.$refs.counter);
			}
		},
		bindRrulePopup(): void
		{
			if (!this.$refs.rrule)
			{
				return;
			}

			const popup = new Popup({
				bindElement: this.$refs.rrule,
				content: this.event.rruleDescription,
				darkMode: true,
				bindOptions: { position: 'top' },
				offsetTop: -10,
				angle: true,
				autoHide: true,
			});

			this.bindShowOnHover(popup);
		},
		bindShowOnHover(popup: Menu|Popup): void
		{
			if (popup instanceof Menu)
			{
				popup = popup.getPopupWindow();
			}

			const bindElement = popup.bindElement;
			const container = popup.getPopupContainer();

			let hoverElement = null;

			const closeMenuHandler = () => {
				setTimeout(() => {
					if (!container.contains(hoverElement) && !bindElement.contains(hoverElement))
					{
						popup.close();
					}
				}, 100);
			};
			const showMenuHandler = () => {
				setTimeout(() => {
					if (bindElement.contains(hoverElement))
					{
						popup.show();
					}
				}, 300);
			};
			const clickHandler = () => {
				if (!popup.isShown())
				{
					popup.show();
				}
			};

			Event.bind(document, 'mouseover', (event) => {
				hoverElement = event.target;
			});
			Event.bind(bindElement, 'mouseenter', showMenuHandler);
			Event.bind(bindElement, 'mouseleave', closeMenuHandler);
			Event.bind(container, 'mouseleave', closeMenuHandler);
			Event.bind(bindElement, 'click', clickHandler);

			const adjustPosition = () => {
				const angleLeft = Popup.getOption('angleMinBottom');
				const popupWidth = popup.getPopupContainer().offsetWidth;
				const elementWidth = popup.bindElement.offsetWidth;

				popup.setOffset({ offsetLeft: elementWidth / 2 - popupWidth / 2 });
				popup.adjustPosition();

				if (popup.angle)
				{
					popup.setAngle({ offset: popupWidth / 2 + angleLeft });
				}
			};

			popup.subscribeFromOptions({
				onShow: () => {
					adjustPosition();
					document.addEventListener('scroll', adjustPosition, true);
				},
				onClose: () => {
					document.removeEventListener('scroll', adjustPosition, true);
				},
			});
		},
	},
	mounted(): void
	{
		this.renderCounter();
		this.bindRrulePopup();
	},
	watch: {
		event(): void
		{
			this.renderCounter();
		},
	},
	template: `
		<div class="calendar-open-events-list-item-name">
			<div
				class="calendar-open-events-list-item__event-name-with-counter"
				@click="$emit('openEvent')"
			>
				<div class="calendar-open-events-list-event-name-category" v-if="selectedCategoryId === 0">
					{{ event.categoryName }}
				</div>
				<div v-show="event.isNew" ref="counter"></div>
				<div class="calendar-open-events-list-item__event-name" :title="event.name">
					{{ event.name }}
				</div>
			</div>
			<div class="calendar-open-events-list-event-time">
				<div class="calendar-open-events-list-event-time-datetime">
					{{ event.formattedDateTime }}
				</div>
				<div class="calendar-open-events-list-event-time-full-day" v-if="event.isFullDay">
					{{ $Bitrix.Loc.getMessage('CALENDAR_OPEN_EVENTS_LIST_ALL_DAY') }}
				</div>
				<div
					class="calendar-open-events-list-event-time-recursion"
					ref="rrule"
					v-if="event.rrule"
				>
					<div class="ui-icon-set --refresh-3"></div>
					<div class="calendar-open-events-list-event-time-rrule" v-if="formattedRrule">
						{{ formattedRrule }}
					</div>
				</div>
			</div>
		</div>
	`,
}
