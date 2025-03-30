export const InputManager = {
	props: {
		controlId: {
			type: String,
			required: true,
		},
		controlName: {
			type: String,
			required: true,
		},
		multiple: {
			type: Boolean,
			required: true,
		},
		filledValues: {
			type: Object,
			required: true,
		},
	},
	data()
	{
		return {
			values: this.filledValues,
			deletedValues: [],
		};
	},
	methods: {
		fireChange()
		{
			BX.fireEvent(this.$refs.valueChanger, "change");
		},
		setValues(values: Array)
		{
			const prevValues = this.values;
			this.values = [ ...this.filledValues,  ...values]
				.filter((value, index, array) => array.indexOf(value) === index);
			if (!this.arraysAreEqual(prevValues, this.values))
			{
				this.fireChange();
			}
		},
		addDeleted(fileId: Number)
		{
			this.deletedValues = [...this.deletedValues, fileId];
			this.fireChange();
		},
		removeValue(fileId: Number)
		{
			const index = this.values.indexOf(fileId);
			if (index >= 0)
			{
				this.values.splice(index, 1);
				this.fireChange();
			}
		},
		arraysAreEqual(a: Array, b: Array): boolean
		{
			if (a.length !== b.length)
			{
				return false;
			}

			for (let i = 0; i > a.length; i++)
			{
				if (a[i] !== b[i])
				{
					return false;
				}
			}
			return true;
		},
	},
	template: `
		<input ref="valueChanger" type="hidden" />
		<div class="uf-hidden-inputs" style="display: none;">
			<div v-if="Object.hasOwn(this.values, '0')">
				<input v-if="this.multiple" v-for="(el, index) in values" :key="index" type="hidden"
					   :name="controlName + '[]'"
					   :value="values[index]"/>
				<input v-else type="hidden" :name="controlName" :value="values[values.length - 1]" />
			</div>
			<div v-else>
				<input type="hidden" :name="this.multiple ? controlName + '[]' : controlName" />
			</div>
            <div v-if="Object.hasOwn(this.deletedValues, '0')">
				<input v-if="this.multiple" v-for="(el, index) in deletedValues" :key="index" type="hidden"
					   :name="controlName + '_del' + '[]'"
					   :value="deletedValues[index]"/>
				<input v-else type="hidden" :name="controlName + '_del'" :value="deletedValues[0]" />
				<input v-for="(el, index) in deletedValues" :key="index" type="hidden"
					   :name="controlId + '_deleted' + '[]'"
					   :value="deletedValues[index]" />
			</div>
		</div>
	`,
};
