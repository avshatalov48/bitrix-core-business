(function() {
	BX.namespace('BX.Iblock');

	class PropertyFieldDate
	{
		constructor(options)
		{
			const container = document.querySelector(options.selector);
			const app = BX.Vue3.BitrixVue.createApp({
				data()
				{
					return {
						items: [],
						isShowTime: false,
						isMultiple: false,
						controlName: null,
					};
				},
				methods: {
					showCalendar(item, control)
					{
						BX.calendar({
							node: control,
							value: item.value,
							bTime: this.isShowTime,
							bHideTime: false,
							callback: (dt) => {
								item.value = BX.calendar.ValueToString(dt, this.isShowTime);
							},
						});
					},
					appendNew()
					{
						this.appendValue('');
					},
					appendValue(value)
					{
						let name = this.controlName;
						if (this.isMultiple)
						{
							name += '[]';
						}

						this.items.push({
							name,
							value,
						});

						if (!this.isMultiple)
						{
							this.items = this.items.slice(-1);
						}
					},
				},
			});
			const vm = app.mount(container);

			// fill
			if (BX.Type.isBoolean(options.isShowTime))
			{
				vm.isShowTime = options.isShowTime;
			}

			if (BX.Type.isBoolean(options.isMultiple))
			{
				vm.isMultiple = options.isMultiple;
			}

			if (BX.Type.isString(options.controlName))
			{
				vm.controlName = options.controlName;
			}

			if (BX.Type.isArray(options.values) && options.values.length > 0)
			{
				options.values.forEach((i) => {
					vm.appendValue(i);
				});
			}
			else
			{
				vm.appendNew();
			}
		}
	}

	BX.Iblock.PropertyFieldDate = PropertyFieldDate;
})();
