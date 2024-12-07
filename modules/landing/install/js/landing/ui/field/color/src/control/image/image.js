import 'ui.design-tokens';

import {BaseEvent} from 'main.core.events';
import {Dom, Tag, Type, Loc, Text} from 'main.core';
import {Image as ImageField} from 'landing.ui.field.image';
import {Backend} from 'landing.backend';
import {Env} from 'landing.env';
import {PageObject} from 'landing.pageobject';
import BaseControl from "../base_control/base_control";
import BgImageValue from '../../bg_image_value';
import './css/image.css';

export default class Image extends BaseControl
{
	// todo: move to type
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
		this.imgField = new ImageField({
			id: 'landing_ui_color_image_' + Text.getRandom().toLowerCase(),
			className: 'landing-ui-field-color-image-image',
			contextType: ImageField.CONTEXT_TYPE_STYLE,
			compactMode: true,
			disableLink: true,
			disableAltField: true,
			allowClear: true,
			isAiImageAvailable: Env.getInstance().getOptions()['ai_image_available'],
			isAiImageActive: Env.getInstance().getOptions()['ai_image_active'],
			aiUnactiveInfoCode: Env.getInstance().getOptions()['ai_unactive_info_code'],
			dimensions: {width: 1920},
			uploadParams: {
				action: "Block::uploadFile",
				block: this.options.block.id,
			},
			contentRoot: this.options.contentRoot,
		});
		this.imgField.subscribe('change', this.onImageChange.bind(this));

		this.sizeField = new BX.Landing.UI.Field.Dropdown({
			id: 'landing_ui_color_image_size_' + Text.getRandom().toLowerCase(),
			title: Loc.getMessage('LANDING_FIELD_COLOR-BG_SIZE_TITLE'),
			className: 'landing-ui-field-color-image-size',
			items: BgImageValue.getSizeItemsForButtons(),
			onChange: this.onSizeChange.bind(this),
			contentRoot: this.options.contentRoot,
		});

		this.attachmentField = new BX.Landing.UI.Field.Checkbox({
			id: 'landing_ui_color_image_attach_' + Text.getRandom().toLowerCase(),
			className: 'landing-ui-field-color-image-attachment',
			multiple: false,
			compact: true,
			items: [
				{ name: Loc.getMessage('LANDING_FIELD_COLOR-BG_FIXED'), value: 'fixed' },
			],
			onChange: this.onAttachmentChange.bind(this),
			value: [this.getAttachmentValue()],
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
					lid: this.options.block.lid,
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
				this.sizeField.setValue(this.getSizeValue(), true);
				this.attachmentField.setValue([this.getAttachmentValue()]);
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

	getAttachmentValue(): string
	{
		if (
			this.options
			&& this.options.block
			&& this.options.block.content
			&& Dom.hasClass(this.options.block.content, 'g-bg-image')
		)
		{
			const blockContentStyle = window.getComputedStyle(this.options.block.content);
			const bgAttachmentValue = blockContentStyle.getPropertyValue('background-attachment');

			return bgAttachmentValue.includes('fixed') ? 'fixed' : 'scroll';
		}

		return 'scroll';
	}

	getSizeValue(): string
	{
		if (
			this.options
			&& this.options.block
			&& this.options.block.content
			&& Dom.hasClass(this.options.block.content, 'g-bg-image')
		)
		{
			const blockContentStyle = window.getComputedStyle(this.options.block.content);
			const bgSizeValue = blockContentStyle.getPropertyValue('background-size');

			return bgSizeValue.includes('cover') ? 'cover' : 'auto';
		}

		return 'cover';
	}
}
