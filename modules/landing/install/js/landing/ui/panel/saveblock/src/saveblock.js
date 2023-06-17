import {Backend} from 'landing.backend';
import {Env} from 'landing.env';
import {ImageCompressor} from 'landing.imagecompressor';
import {Loc} from 'landing.loc';
import {Main} from 'landing.main';
import {Screenshoter} from 'landing.screenshoter';
import {MessageCard} from 'landing.ui.card.messagecard';
import {TextField} from 'landing.ui.field.textfield';
import {Content} from 'landing.ui.panel.content';
import {Dom, Cache, Tag} from 'main.core';

import 'translit';
import './css/style.css';

/**
 * @memberOf BX.Landing.UI.Panel
 */
export class SaveBlock extends Content
{
	static getInstance(): SaveBlock
	{
		if (!SaveBlock.instance)
		{
			SaveBlock.instance = new SaveBlock('landing_save_block_panel');
		}

		return SaveBlock.instance;
	}

	cache = new Cache.MemoryCache();
	bock = null;
	previewFileIds = [];

	constructor(id, data)
	{
		data = data || {};
		data.title = Loc.getMessage('LANDING_SAVE_BLOCK_PANEL_TITLE_MSGVER_1');
		data.showFromRight = true;

		if (!data.block)
		{
			return;
		}

		super(id, data);
		this.block = data.block;
		this.mainInstance = Main.getInstance();

		Dom.addClass(this.layout, 'landing-ui-panel-save-block');
		Dom.addClass(this.overlay, 'landing-ui-panel-save-block');

		this.setButtons();
		this.renderTo(window.parent.document.body);
	}

	setButtons()
	{
		this.appendFooterButton(
			new BX.Landing.UI.Button.BaseButton('save_block_content', {
				text: Loc.getMessage('BLOCK_SAVE'),
				onClick: this.onSave.bind(this),
				className: 'landing-ui-button-content-save',
			}),
		);
		this.appendFooterButton(
			new BX.Landing.UI.Button.BaseButton('cancel_block_content', {
				text: Loc.getMessage('BLOCK_CANCEL'),
				onClick: this.hide.bind(this),
				className: 'landing-ui-button-content-cancel',
			}),
		);
	}

	getTitleField(): TextField
	{
		return this.cache.remember('titleField', () => {
			return new TextField({
				title: Loc.getMessage('LANDING_SAVE_BLOCK_PANEL_FIELD_TITLE'),
				textOnly: true
			});
		});
	}

	getSectionsField(): BX.Landing.UI.Field.MultiSelect
	{
		return this.cache.remember('sectionsField', () => {
			const items = [];
			const { blocks } = Env.getInstance().getOptions();
			Object.keys(blocks).map(key => {
				if (key !== 'last' && key !== 'separator_apps' && key.indexOf('.') === -1)
				{
					items.push({value: key, name: blocks[key].name});
				}
			});
			return new BX.Landing.UI.Field.MultiSelect({
				title: Loc.getMessage('LANDING_SAVE_BLOCK_PANEL_FIELD_SECTIONS'),
				items
			});
		});
	}

	getTemplateRefField(): BX.Landing.UI.Field.Checkbox
	{
		return this.cache.remember('templateRefField', () => {
			return new BX.Landing.UI.Field.Checkbox({
				items: [
					{value: 'N', name: Loc.getMessage('LANDING_SAVE_BLOCK_PANEL_FIELD_TEMPLATE_REF')}
				]
			});
		});
	}

	getPreviewField()
	{
		return this.cache.remember('preview', () => {
			return new BX.Landing.UI.Field.Image({
				title: Loc.getMessage('LANDING_SAVE_BLOCK_PANEL_FIELD_PREVIEW'),
				disableLink: true,
				disableAltField: true,
				uploadParams: {
					action: 'Block::uploadFile',
					block: this.block.id
				},
				content: {
					src: '/bitrix/images/1.gif',
					id : -1,
					alt : ''
				},
				dimensions: {
					width: 1200,
					height: 600
				}
			});
		});
	}

	getMessage(): MessageCard
	{
		return this.cache.remember('message', () => {
			return new MessageCard({
				id: 'fieldsMessage',
				header: Loc.getMessage('LANDING_SAVE_BLOCK_PANEL_MESSAGE_TITLE_MSGVER_1'),
				description: Loc.getMessage('LANDING_SAVE_BLOCK_PANEL_MESSAGE_TEXT_MSGVER_1'),
				//icon: messageIcon,
				restoreState: true
			});
		});
	}

	getForm(): BX.Landing.UI.Form.BaseForm
	{
		return this.cache.remember('form', () => {
			return new BX.Landing.UI.Form.BaseForm({
				fields: [
					this.getTitleField(),
					this.getSectionsField(),
					this.mainInstance.getTemplateCode() ? this.getTemplateRefField() : null,
					this.getPreviewField()
				]
			});
		});
	}

	makeScreenshot()
	{
		this.getPreviewField().showLoader();

		void Screenshoter
			.makeBlockScreenshot(this.block.id)
			.then((sourceFile) => {
				return ImageCompressor.compress(sourceFile, {
					maxWidth: 830,
					maxHeight: 300,
				});
			})
			.then((compressedFile) => {
				return Backend
					.getInstance()
					.upload(compressedFile, {
						block: this.block.id,
						temp: true
					});
			})
			.then((response: {id: string, src: string}) => {
				this.getPreviewField().setValue(response);
				this.getPreviewField().hideLoader();
			});
	}

	show(options?: any): Promise<any>
	{
		Dom.style(this.footer, 'display', null);

		this.getTitleField().setValue(this.block?.manifest?.block?.name);
		this.getSectionsField().setValue(this.block?.manifest?.block?.section || []);
		this.getTemplateRefField().setValue(['Y']);
		this.getPreviewField().setValue({src: this.block?.manifest?.preview || this.block?.manifest?.block?.preview || ''});

		this.makeScreenshot();

		this.clear();
		Dom.prepend(this.getMessage().getLayout(), this.content);
		this.appendForm(this.getForm());
		return super.show();
	}

	getFailMessage()
	{
		return this.cache.remember('failMessage', () => {
			return Tag.render`
				<div class="landing-ui-panel-save-block-fail">
					<div class="landing-ui-panel-save-block-fail-header">
						${Loc.getMessage('LANDING_SAVE_BLOCK_PANEL_FAIL_MESSAGE_TITLE')}
					</div>
				</div>
			`;
		});
	}

	onSave()
	{
		const backend = Backend.getInstance();
		const title = this.getTitleField().getValue();
		const templateRef = this.getTemplateRefField().getValue().length > 0;
		const preview = this.getPreviewField().getValue();
		const blockCode = this.block?.manifest?.code;
		let sections = this.getSectionsField().getValue();

		this.clear();
		this.hide();

		if (!blockCode)
		{
			return;
		}

		backend.action(
			'Landing::favoriteBlock',
			{
				lid: this.block.lid,
				block: this.block.id,
				meta: {
					name: title,
					section: sections,
					preview: Math.max(preview.id, 0),
					tpl_code: templateRef ? this.mainInstance.getTemplateCode() : null
				}
			},
			{
				code: blockCode
			}
		).then((newBlockId) => {
			if (newBlockId)
			{
				top.BX.UI.Notification.Center.notify({
					content: Loc.getMessage('LANDING_SAVE_BLOCK_PANEL_SUCCESS')
				});
				sections.push('last');
				sections.map(section => {
					this.mainInstance.addNewBlockToCategory(
						section,
						{
							code: blockCode,
							codeOriginal: blockCode + '@' + newBlockId,
							name: title,
							preview: preview.src,
							section: sections,
							favorite: true,
							favoriteMy: true,
							repo_id: this.block.repoId,
						}
					);
				});
			}
			else
			{
				Dom.append(this.getFailMessage(), this.content);
			}
		});
	}
}
