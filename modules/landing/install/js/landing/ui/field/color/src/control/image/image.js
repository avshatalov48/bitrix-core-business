import {BaseEvent} from 'main.core.events';
import {Dom, Tag, Type, Loc, Text} from 'main.core';

import {Backend} from 'landing.backend';
import {PageObject} from 'landing.pageobject';
import BaseControl from "../base_control/base_control";
import BgImageValue from '../../bg_image_value';
import './css/image.css';

export default class Image extends BaseControl
{
	options: {
		block: BX.Landing.Block,
		styleNode: BX.Landing.UI.Style,
		contentRoot: ?HTMLDivElement,
		selector: string,
	};

	constructor(options)
	{
		super();
		this.setEventNamespace('BX.Landing.UI.Field.Color.Image');
		this.options = options;

		// todo: set dimensions from block
		const rootWindow = PageObject.getRootWindow();
		this.imgField = new rootWindow.BX.Landing.UI.Field.Image({
			id: 'landing_ui_color_image_' + Text.getRandom().toLowerCase(),
			className: 'landing-ui-field-color-image-image',
			compactMode: true,
			disableLink: true,
			// selector: options.selector,
			disableAltField: true,
			allowClear: true,
			dimensions: {width: 1920},
			uploadParams: {
				action: "Block::uploadFile",
				block: this.options.block.id,
			},
			contentRoot: this.options.contentRoot,
		});
		this.imgField.subscribe('change', this.onImageChange.bind(this));

		this.sizeField = new BX.Landing.UI.Field.Dropdown({
			// todo: need commented fields?
			id: 'landing_ui_color_image_size_' + Text.getRandom().toLowerCase(),
			// title: 'size field title',
			// description: 'ButtonGroup size description',
			title: Loc.getMessage('LANDING_FIELD_COLOR-BG_SIZE_TITLE'),
			className: 'landing-ui-field-color-image-size',
			// selector: this.options.selector,
			items: BgImageValue.getSizeItemsForButtons(),
			onChange: this.onSizeChange.bind(this),
			contentRoot: this.options.contentRoot,
		});

		this.attachmentField = new BX.Landing.UI.Field.Checkbox({
			// todo: need commented fields?
			id: 'landing_ui_color_image_attach_' + Text.getRandom().toLowerCase(),
			className: 'landing-ui-field-color-image-attachment',
			// title: 'attachement field title',
			// description: 'ButtonGroup size description',
			multiple: false,
			compact: true,
			// selector: options.selector,
			items: [
				{name: Loc.getMessage('LANDING_FIELD_COLOR-BG_FIXED'), value: true},
			],
			onChange: this.onAttachmentChange.bind(this),
		});
	}

	buildLayout(): HTMLDivElement
	{
		return Tag.render`
			<div class="landing-ui-field-color-image">
				${this.imgField.getLayout()}
				${this.sizeField.getLayout()}
				${this.attachmentField.getLayout()}
			</div>
		`;
	}

	onImageChange(event: BaseEvent)
	{
		const value = this.getValue() || new BgImageValue();
		if (event.getData().value.src)
		{
			value.setUrl(event.getData().value.src);
			value.setFileId(event.getData().value.id);
			if (event.getData().value.src2x)
			{
				value.setUrl2x(event.getData().value.src2x);
				value.setFileId2x(event.getData().value.id2x);
			}
		}
		else
		{
			value.setUrl(null);
			value.setFileId(null);
			value.setUrl2x(null);
			value.setFileId2x(null);
		}

		this.setValue(value);
		this.onChange();

		this.saveNode(value);
	}

	saveNode(value: BgImageValue)
	{
		const style = this.options.styleNode;
		const block = this.options.block;

		let selector;
		if (
			style.selector === block.selector
			|| style.selector === block.makeAbsoluteSelector(block.selector)
		)
		{
			selector = '#wrapper';
		}
		else if (!style.isSelectGroup())
		{
			selector = BX.Landing.Utils.join(
				style.selector.split("@")[0],
				"@",
				style.getElementIndex(style.getNode()[0])
			);
		}
		else
		{
			selector = style.selector.split("@")[0];
		}

		const data = {[selector]: {}};
		data[selector].id = value.getFileId() || -1;
		data[selector].id2x = value.getFileId2x() || -1;

		Backend.getInstance()
			.action(
				"Landing\\Block::updateNodes",
				{
					block: this.options.block.id,
					data: data,
				},
			)
	}

	onSizeChange(size: string)
	{
		if (Type.isString(size))
		{
			const value = this.getValue() || new BgImageValue();
			value.setSize(size);
			this.setValue(value);
			this.onChange();
		}
	}

	onAttachmentChange(event)
	{
		if (event instanceof BaseEvent)
		{
			const value = this.getValue() || new BgImageValue();
			value.setAttachment(
				BgImageValue.getAttachmentValueByBool(this.attachmentField.getValue()),
			);
			this.setValue(value);
			this.onChange();
		}
	}

	onChange(event: ?BaseEvent)
	{
		this.cache.delete('value');
		this.emit('onChange', {data: {image: this.getValue()}});
	}

	getValue(): ?BgImageValue
	{
		// todo: get size and attachement from controls
		return this.cache.remember('value', () => {
			const imgValue = this.imgField.getValue();
			const url = imgValue.src;
			if (url === null)
			{
				return null;
			}
			else
			{
				const value = new BgImageValue({
					url: url,
					fileId: imgValue.id,
				});
				if (imgValue.src2x)
				{
					value.setUrl2x(imgValue.src2x);
					value.setFileId2x(imgValue.fileId2x);
				}
				const size = this.sizeField.getValue();
				if (size !== null)
				{
					value.setSize(size);
				}

				value.setAttachment(BgImageValue.getAttachmentValueByBool(this.attachmentField.getValue()));

				// todo: set overlay

				return value;
			}
		});
	}

	setValue(value: ?BgImageValue)
	{
		if (this.isNeedSetValue(value))
		{
			// todo: can delete prev image
			super.setValue(value);

			if (value === null)
			{
				this.imgField.setValue({src: ''}, true);
				// todo: what set size and attachement?
			}
			else
			{
				if (value.getUrl() !== null)
				{
					this.setActive();
				}

				const imgFieldValue = {
					type: 'image',
					src: value.getUrl(),
					id: value.getFileId(),
				};
				if (value.getUrl2x())
				{
					imgFieldValue.src2x = value.getUrl2x();
					imgFieldValue.id2x = value.getFileId2x();
				}
				this.imgField.setValue(imgFieldValue, true);
				this.sizeField.setValue(value.getSize(), true);
				this.attachmentField.setValue([value.getAttachment(true)]);
			}
		}
	}

	setActive(): void
	{
		Dom.addClass(this.imgField.getLayout(), Image.ACTIVE_CLASS);
	}

	unsetActive(): void
	{
		Dom.removeClass(this.imgField.getLayout(), Image.ACTIVE_CLASS);
	}
}
