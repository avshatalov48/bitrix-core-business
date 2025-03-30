import { Type } from 'main.core';
import { TileWidgetComponent } from 'ui.uploader.tile-widget';
import { InputManager } from './inputmanager';

export {Main, AppContext};

declare type AppContext = {
	id: number,
	entityId: string,
	fieldName: string,
	multiple: boolean,
};

const Main = {
	data()
	{
		const data =  {
			fileTokens: [],
		};

		return this.getPreparedData(data);
	},
	props: {
		controlId: {
			type: String,
			required: true,
		},
		container: {
			type: HTMLElement,
			required: true,
		},
		context: {
			type: Object,
			required: true,
		},
		filledValues: {
			type: Object,
		},
	},
	components: {
		InputManager,
		TileWidgetComponent,
	},
	computed: {
		uploaderOptions()
		{
			return {
				controller: 'main.fileUploader.fieldFileUploaderController',
				controllerOptions: this.context,
				files: this.fileTokens,
				events: {
					onUploadComplete: () => {
						void this.$nextTick(() => {
							this.fileTokens = this.getFileIdList();
						});
					},
					"File:onRemove": (event) => {
						const eventData = event.getData();
						if (Type.isObject(eventData) && Type.isObject(eventData["file"]))
						{
							const file = eventData["file"];
							if (Type.isObject(file))
							{
								const fileId = file.getServerFileId();
								if (Type.isNumber(fileId))
								{
									this.$refs.inputManager.addDeleted(fileId);
								}
								else {
									this.$refs.inputManager.addDeleted(this.getRealFileId(file));
								}
							}
						}
					},
				},
				multiple: this.context.multiple,
				autoUpload: true,
				treatOversizeImageAsFile: true,
			};
		},
		widgetOptions(): Object
		{
			return {};
		},
	},
	methods: {
		getPreparedData(data: Object): Object
		{
			const { filledValues } = this;

			if (Type.isArrayFilled(filledValues))
			{
				data.fileTokens = filledValues;
			}

			return data;
		},
		getFileIdList(): number[]
		{
			const ids = [];

			this.$refs.uploader.uploader.getFiles().forEach((file) => {
				if (file.isComplete())
				{
					const realFileId = this.getRealFileId(file);
					if (Type.isNumber(realFileId))
					{
						ids.push(realFileId);
					}
					else
					{
						ids.push(file.getServerFileId());
					}
				}
			});

			return ids;
		},
		getRealFileId(file: Object) {
			const { realFileId } = file.getCustomData();

			return Type.isNumber(realFileId) ? realFileId : null;
		},
		updateInputManagerValues(): void
		{
			this.$refs.inputManager.setValues(this.fileTokens);
		},
	},
	template: `
	<div class="main-field-file-wrapper">
		<InputManager
			ref="inputManager"
			:controlId="controlId"
			:controlName="context.fieldName"
			:multiple="context.multiple"
			:filledValues="filledValues"
		/>
		<TileWidgetComponent
			ref="uploader"
			:uploaderOptions="uploaderOptions"
			:widgetOptions="widgetOptions"
		/>
	</div>`,
	created()
	{
		this.$watch(
			'fileTokens',
			this.updateInputManagerValues,
			{
				deep: true,
			},
		);
	},
};
