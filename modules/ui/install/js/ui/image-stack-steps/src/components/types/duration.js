import { Type } from 'main.core';
import { DateTimeFormat } from 'main.date';

import { Text } from './text';

export const Duration = {
	name: 'ui-image-stack-steps-duration',
	components: {
		Text,
	},
	props: {
		duration: {
			type: Number,
			required: true,
			validator: (value) => {
				return value >= 0;
			},
		},
		realtime: {
			type: Boolean,
			required: true,
		},
		realtimeBoundary: {
			type: Number,
			required: false,
		},
		format: {
			type: [String, Array],
			required: false,
		},
	},
	data(): {}
	{
		return {
			defaultFormat: [['s', 'sdiff'], ['i', 'idiff'], ['H', 'Hdiff'], ['d', 'ddiff'], ['m', 'mdiff'], ['Y', 'Ydiff']],
			defaultRealtimeBoundary: 60 * 60, // 1 hour
			computedDuration: 0,
			startTime: Math.floor(Date.now() / 1000),
			timer: null,
		};
	},
	watch: {
		duration()
		{
			this.computedDuration = 0;
			this.startTime = Math.floor(Date.now() / 1000);
		},
		isRealtime(realtime: boolean)
		{
			if (realtime)
			{
				this.startTime = Math.floor(Date.now() / 1000);
				this.computedDuration = 0;
				this.startTimer();
			}
			else
			{
				this.stopTimer();
			}
		},
	},
	computed: {
		text(): string
		{
			const duration = this.isRealtime ? this.duration + this.computedDuration : this.duration;

			return DateTimeFormat.format(this.getFormat(), 0, duration);
		},
		isRealtime(): boolean
		{
			return this.realtime;
		},
	},
	mounted()
	{
		this.startTimer();
	},
	unmounted()
	{
		this.stopTimer();
	},
	methods: {
		startTimer()
		{
			if (!this.isRealtime)
			{
				return;
			}

			this.timer = setInterval(() => {
				if (!this.isRealtime)
				{
					this.stopTimer();

					return;
				}

				if (this.duration + this.computedDuration < (this.realtimeBoundary || this.defaultRealtimeBoundary))
				{
					this.computedDuration = Math.floor(Date.now() / 1000) - this.startTime;

					return;
				}

				this.stopTimer();
			}, 1000);
		},
		stopTimer()
		{
			if (this.timer)
			{
				clearInterval(this.timer);
				this.timer = null;
			}
		},
		getFormat(): [] | string
		{
			if (Type.isArray(this.format) || Type.isString(this.format))
			{
				return this.format;
			}

			return this.defaultFormat;
		},
	},
	template: `
		<Text :text="text"/>
	`,
};
