import {Dom, Cache, Tag, Type, Text, Event} from 'main.core';
import {Loader} from 'main.loader';
import {Content} from 'landing.ui.panel.content';
import {Loc} from 'landing.loc';
import {Backend} from 'landing.backend';
import {Env} from 'landing.env';
import {SliderHacks} from 'landing.sliderhacks';
import 'translit';
import './css/style.css';

/**
 * @memberOf BX.Landing.UI.Panel
 */
export class CreatePage extends Content
{
	static getInstance(): CreatePage
	{
		if (!CreatePage.instance)
		{
			CreatePage.instance = new CreatePage('landing_create_page_panel', {
				title: Loc.getMessage('LANDING_CREATE_PAGE_PANEL_TITLE'),
			});
		}

		return CreatePage.instance;
	}

	cache = new Cache.MemoryCache();

	constructor(id, data)
	{
		super(id, data);

		Dom.addClass(this.layout, 'landing-ui-panel-create-page');
		Dom.addClass(this.overlay, 'landing-ui-panel-create-page');

		this.appendFooterButton(
			new BX.Landing.UI.Button.BaseButton('save_block_content', {
				text: BX.Landing.Loc.getMessage('BLOCK_SAVE'),
				onClick: this.onSave.bind(this),
				className: 'landing-ui-button-content-save',
			}),
		);

		this.appendFooterButton(
			new BX.Landing.UI.Button.BaseButton('cancel_block_content', {
				text: BX.Landing.Loc.getMessage('BLOCK_CANCEL'),
				onClick: this.hide.bind(this),
				className: 'landing-ui-button-content-cancel',
			}),
		);

		this.renderTo(document.body);
	}

	getTitleField(): BX.Landing.UI.Field.Text
	{
		return this.cache.remember('titleField', () => {
			return new BX.Landing.UI.Field.Text({
				title: Loc.getMessage('LANDING_CREATE_PAGE_PANEL_FIELD_PAGE_TITLE'),
				textOnly: true,
			});
		});
	}

	getCodeField(): BX.Landing.UI.Field.Text
	{
		return this.cache.remember('codeField', () => {
			return new BX.Landing.UI.Field.Text({
				title: Loc.getMessage('LANDING_CREATE_PAGE_PANEL_FIELD_PAGE_CODE'),
				textOnly: true,
			});
		});
	}

	getForm(): BX.Landing.UI.Form.BaseForm
	{
		return this.cache.remember('form', () => {
			return new BX.Landing.UI.Form.BaseForm({
				fields: [
					this.getTitleField(),
					this.getCodeField(),
				],
			});
		});
	}

	show({title = ''} = {}): Promise<any>
	{
		Dom.style(this.footer, 'display', null);

		this.range = document.getSelection().getRangeAt(0);
		this.node = (() => {
			if (BX.Landing.Block.Node.Text.currentNode.isEditable())
			{
				return BX.Landing.Block.Node.Text.currentNode;
			}

			return BX.Landing.UI.Field.Text.currentField;
		})();

		const capitalizedTitle = title.replace(/^\w/, c => c.toUpperCase());
		this.getTitleField().setValue(capitalizedTitle);

		const translitedTitle = BX.translit(title, {
			change_case: 'L',
			replace_space: '-',
			replace_other: '',
		});
		this.getCodeField().setValue(translitedTitle);

		this.clear();
		this.appendForm(this.getForm());

		return super.show();
	}

	getSuccessMessage(id)
	{
		const envOptions = Env.getInstance().getOptions();
		const urlMask = envOptions.params.sef_url.landing_view;
		const siteId = envOptions.site_id;

		const editLink = urlMask
			.replace('#site_show#', siteId)
			.replace('#landing_edit#', id);

		return Tag.render`
			<div class="landing-ui-panel-create-page-success">
				<div class="landing-ui-panel-create-page-success-header">
					${Loc.getMessage('LANDING_CREATE_PAGE_PANEL_SUCCESS_MESSAGE_TITLE')}
				</div>
				<div class="landing-ui-panel-create-page-actions">
					<a href="${editLink}" target="_blank">${Loc.getMessage('LANDING_CONTENT_PANEL_TITLE')}</a> &nbsp;
				</div>
			</div>
		`;
	}

	getFailMessage()
	{
		return this.cache.remember('failMessage', () => {
			return Tag.render`
				<div class="landing-ui-panel-create-page-fail">
					<div class="landing-ui-panel-create-page-fail-header">
						${Loc.getMessage('LANDING_CREATE_PAGE_PANEL_FAIL_MESSAGE_TITLE')}
					</div>
				</div>
			`;
		});
	}

	onSave()
	{
		const backend = Backend.getInstance();
		const title = this.getTitleField().getValue();
		const code = BX.translit(
			this.getCodeField().getValue(),
			{
				change_case: 'L',
				replace_space: '-',
				replace_other: '',
			},
		);
		const {folder_id: folderId} = Env.getInstance().getOptions();
		const loader = new Loader();

		this.clear();
		loader.show(this.body);

		void backend
			.createPage({title, code, folderId})
			.then((result) => {
				return new Promise((resolve) => {
					setTimeout(() => resolve(result), 500);
				});
			})
			.then((result) => {
				loader.hide();

				if (Type.isNumber(result))
				{
					const successMessage = this.getSuccessMessage(result);

					if (
						Env.getInstance().getType() === 'KNOWLEDGE'
						|| Env.getInstance().getType() === 'GROUP'
					)
					{
						const link = successMessage.querySelector('a');
						if (link)
						{
							Event.bind(link, 'click', (event) => {
								event.preventDefault();
								void SliderHacks.reloadSlider(link.href, window.parent);
							});
						}
					}

					Dom.append(successMessage, this.content);

					const value = {
						href: `#landing${result}`,
					};
					document.getSelection().removeAllRanges();
					document.getSelection().addRange(this.range);
					this.node.enableEdit();

					const tmpHref = Text.encode(`${value.href}${Text.getRandom()}`);
					const selection = document.getSelection();

					document.execCommand('createLink', false, tmpHref);

					const link = selection.anchorNode
						.parentElement
						.parentElement
						.parentElement
						.querySelector(`[href="${tmpHref}"]`);

					if (link)
					{
						Dom.attr(link, 'href', value.href);
						Dom.attr(link, 'target', value.target);

						if (Type.isString(value.text))
						{
							link.innerText = value.text;
						}

						if (Type.isPlainObject(value.attrs))
						{
							Dom.attr(link, value.attrs);
						}
					}

					Dom.style(this.footer, 'display', 'none');
				}
				else
				{
					Dom.append(this.getFailMessage(), this.content);
				}
			});
	}
}