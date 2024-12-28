import { ref } from 'ui.vue3';
import { Type, Loc } from 'main.core';
import { EventEmitter, BaseEvent } from 'main.core.events';

const fetchAlarmTime = 5000;

export const Content = {
	props: {
		defaultData: {
			type: Object,
			default: null,
		},
		clickable: {
			type: Boolean,
			default: false,
		},
	},

	data()
	{
		return {
			isFetching: false,
			timeout: null,
		};
	},

	created()
	{
		EventEmitter.subscribe('landing:widgetvue:engine:onSetData', this.onSetData);
	},

	beforeUnmount()
	{
		EventEmitter.unsubscribe('landing:widgetvue:engine:onSetData', this.onSetData);
	},

	methods: {
		onSetData(event: BaseEvent)
		{
			clearTimeout(this.timeout);
			this.$bitrix.eventEmitter.emit('landing:widgetvue:engine:endContentLoad');
			this.$bitrix.eventEmitter.emit('landing:widgetvue:engine:onHideMessage');
			this.isFetching = false;

			if (Type.isObject(event.getData().data))
			{
				const data = event.getData().data;
				Object.keys(data).forEach(code => {
					if (this[code] !== undefined)
					{
						this[code] = data[code];
					}
					// todo: and crete refs if not exists?
				});
			}
		},

		fetch(params: {} = {})
		{
			if (!this.clickable || this.isFetching)
			{
				console.info('Events is disabled now');

				return;
			}

			this.isFetching = true;
			this.$bitrix.eventEmitter.emit('landing:widgetvue:engine:startContentLoad');

			this.timeout = setTimeout(() => {
				this.$bitrix.eventEmitter.emit('landing:widgetvue:engine:onMessage', {
					message: Loc.getMessage('LANDING_WIDGETVUE_LOADER_TOO_LONG'),
				});
				this.$bitrix.eventEmitter.emit('landing:widgetvue:engine:endContentLoad');
			}, fetchAlarmTime);

			this.$bitrix.Application.get().fetch(params);
		},

		openApplication(params: {} = {})
		{
			if (!this.clickable)
			{
				console.info('Events is disabled now');

				return;
			}

			this.$bitrix.Application.get().openApplication(params);
		},

		openPath(path: string)
		{
			if (!this.clickable)
			{
				console.info('Events is disabled now');

				return;
			}

			this.$bitrix.Application.get().openPath(path);
		},
	},

	setup(props)
	{
		// todo: to docs. All refs must be implicated in default? or we can create, but v-for can be broken

		// todo: or create refs via data? or pass when create
		const dataRefs = {};
		if (Type.isObject(props.defaultData))
		{
			Object.keys(props.defaultData).forEach(code => {
				dataRefs[code] = ref(props.defaultData[code]);
			});
		}

		return dataRefs;
	},
};
