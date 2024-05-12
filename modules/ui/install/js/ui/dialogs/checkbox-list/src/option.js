const viewMode = {
	view: 'view',
	edit: 'edit',
};

export const CheckboxListOption = {
	props: [
		'id',
		'title',
		'isChecked',
		'isLocked',
		'isEditable',
	],

	data()
	{
		return {
			viewMode: viewMode.view,
			titleData: this.title,
			isCheckedValue: this.isChecked,
		};
	},

	methods: {
		getId(): string
		{
			return this.id;
		},
		getValue(): boolean
		{
			return this.isCheckedValue;
		},
		setValue(value: boolean): void
		{
			this.isCheckedValue = value;
		},
		getTitle(): string
		{
			return this.$refs.title?.innerText ?? this.titleData;
		},
		setTitle(title: string): void
		{
			this.titleData = title;
		},
		setStateFromProps(value: ?boolean = null): void
		{
			this.viewMode = viewMode.view;
			this.titleData = this.title;
			this.isCheckedValue = (value === null ? this.isChecked : value);
		},
		getOptionClassName({ isChecked, isLocked }): []
		{
			return [
				'ui-ctl',
				'ui-ctl-checkbox',
				'ui-checkbox-list__field-item_label',
				{ '--checked': isChecked },
				{ '--disabled': isLocked },
				{ '--editable': !(this.isViewMode || isLocked) },
			];
		},
		getLabelClassName(): []
		{
			return [
				'ui-ctl-label-text',
				'ui-checkbox-list__field-item_text',
				{ '--editable': (this.isEditMode && !this.isLocked) },
			];
		},
		handleCheckBox(event): void
		{
			if (this.isLocked)
			{
				return;
			}

			this.isCheckedValue = !this.isCheckedValue;
		},
		onToggleViewMode(): void
		{
			this.viewMode = this.isEditMode ? viewMode.view : viewMode.edit;

			if (this.viewMode === viewMode.view)
			{
				return;
			}

			void this.$nextTick(() => this.setFocusOnTitle());
		},
		setFocusOnTitle(): void
		{
			this.$refs.title.focus();

			const range = document.createRange();
			const selection = window.getSelection();

			range.selectNodeContents(this.$refs.title);
			range.collapse(false);

			selection.removeAllRanges();
			selection.addRange(range);
		},
		onChangeTitle({ target }): void
		{
			this.titleData = target.innerText;
		},
	},

	computed: {
		isEditMode(): boolean
		{
			return this.viewMode === viewMode.edit;
		},
		isViewMode(): boolean
		{
			return this.viewMode === viewMode.view;
		},
		labelClassName(): string
		{
			return this.getLabelClassName();
		},
	},

	template: `
		<label
			:title="titleData"
			:class="getOptionClassName({ isChecked: isCheckedValue, isLocked })"
		>
			<input
				type="checkbox"
				class="ui-ctl-element ui-checkbox-list__field-item_input"
				:checked="isCheckedValue"
				:disabled="(isLocked === true || isEditMode) ? 'disabled' : null"
				@click="this.handleCheckBox"
			>
			<div
				:class="labelClassName"
				:contenteditable="(isViewMode || isLocked) ? 'false' : 'true'"
				@keydown.enter.prevent
				@blur="onChangeTitle"
				ref="title"
			>
				{{ titleData }}
			</div>
	
			<div v-if="isLocked" class="ui-checkbox-list__field-item_locked"></div>
			<div
				v-else-if="isEditable"
				class="ui-checkbox-list__field-item_edit"
				@click.prevent="onToggleViewMode"
			></div>
		</label>
	`,
};
