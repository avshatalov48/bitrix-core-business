import 'ui.design-tokens';
import './datepick.variables.css';
import {VueDatePick} from './vue-date-pick/vueDatePick.js';
import {Loc} from 'main.core';
import {PopupWindow} from 'main.popup';
import {Vue} from 'ui.vue';

Vue.component('bx-date-pick',
{
	props: ["value", "hasTime", "sundayFirstly", "format"],
	components: {
		'date-pick': VueDatePick,
	},
	data: function () {
		return {
			format: null
		};
	},
	template: `
		<date-pick 
			:value="value"
			:show="true"
			:hasInputElement="false"
			:pickTime="hasTime"
			:startWeekOnSunday="sundayFirstly"
			:format="format"
			:weekdays="getWeekdays()"
			:months="getMonths()"
			:setTimeCaption="getMessage('TIME') + ':'"
			:closeButtonCaption="getMessage('CLOSE')"
			:selectableYearRange="120"
			@input="setDate"
			@close="close()"
		></date-pick>
	`,
	methods: {
		setDate(value, stopClose)
		{
			this.value = value;
			if (!stopClose)
			{
				this.close();
			}

			this.$emit('input', value);
		},
		close()
		{
			this.$emit('close');
		},
		getMessage(code)
		{
			return Loc.getMessage('UI_VUE_COMPONENT_DATEPICK_' + code)
		},
		getWeekdays()
		{
			let list = [];
			for (let n = 1; n <= 7; n++)
			{
				//Loc.getMessage();
				list.push(this.getMessage('DAY_' + n));
			}

			return list;
		},
		getMonths()
		{
			let list = [];
			for (let n = 1; n <= 12; n++)
			{
				list.push(this.getMessage('MONTH_' + n));
			}

			return list;
		},
	}
});


class DatePick
{
	#vue: Vue;

	constructor (options: Object = {})
	{
		this.node = options.node;
		this.popupOptions = options.popupOptions || {};
		this.value = options.value;
		this.hasTime = !!options.hasTime;
		this.sundayFirstly = !!options.sundayFirstly;
		this.format = options.format || (options.hasTime ? Loc.getMessage('FORMAT_DATETIME') : Loc.getMessage('FORMAT_DATE'));
		this.events = options.events || {
			change: null,
		};
	}

	show ()
	{
		if (!this.popup)
		{
			this.popup = new PopupWindow(Object.assign(
				{
					autoHide: true,
					closeByEsc: true,
					contentPadding: 0,
					padding: 0,
					animation: "fading-slide",
				},
				this.popupOptions,
				{
					bindElement: this.node,
					content: this.render(),
				}
			));
		}

		this.popup.show();
	}

	hide ()
	{
		if (this.popup)
		{
			this.popup.close();
		}
	}

	toggle ()
	{
		if (this.popup)
		{
			this.popup.isShown() ? this.hide() : this.show();
		}
		else
		{
			this.show();
		}
	}

	render ()
	{
		this.#vue = Vue.create({
			el: document.createElement('div'),
			data: {
				picker: this,
			},
			template: `
				<bx-date-pick
					v-model="picker.value"
					:hasTime="picker.hasTime"
					:sundayFirstly="picker.sundayFirstly"
					:format="picker.format"
					@close="picker.hide()"
					@input="onChange()"
				>
				</bx-date-pick>
			`,
			methods: {
				onChange()
				{
					this.picker.onChange();
				}
			}
		});

		return this.#vue.$el;
	}

	onChange()
	{
		if (this.events.change)
		{
			this.events.change(this.value);
		}
	}
}

export {
	DatePick,
}