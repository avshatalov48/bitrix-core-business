import { Popup } from 'main.popup';
import { Tag, Loc, Event } from 'main.core';
import { SidePanel } from 'main.sidepanel';
import { AirButtonStyle, Button, ButtonSize } from 'ui.buttons';
import { Outline } from 'ui.icon-set.api.core';
import './error-popup.css';

export type WarningPopupParams = {
	groupId: number,
	errors: Array<Object>,
};

type PopupContentConfig = {
	header: String,
	descriptionParagraphs: Array<String>,
	helperCode: ?Number,
	buttons: Array<Button>,
};

export class ErrorPopup
{
	ERROR_CODE_HAS_FLOWS = 10002;

	#groupId: number;
	#errors: Array<Object>;
	#popup: Popup = null;

	constructor(params: WarningPopupParams)
	{
		this.#groupId = params.groupId;
		this.#errors = this.#prepareErrors(params.errors);
	}

	#prepareErrors(errors: Array<Object>): Array<Object>
	{
		const supportedCodes = new Set([this.ERROR_CODE_HAS_FLOWS]);

		const supportedErrors = errors.filter(
			(error) => supportedCodes.has(error.code),
		);

		if (errors.length > supportedErrors.length)
		{
			const unsupportedErrors = errors.filter(
				(error) => !supportedCodes.has(error.code),
			);
			console.error('Unexpected errors', unsupportedErrors);
		}

		return supportedErrors;
	}

	show()
	{
		this.#getPopup().show();
	}

	#getPopup(): Popup
	{
		if (!this.#popup)
		{
			this.#popup = new Popup({
				cacheable: false,
				width: 400,
				borderRadius: 'var(--ui-border-radius-3xl)',
				angle: false,
				closeIcon: false,
				content: this.#renderPopupContent(),
				closeByEsc: false,
				autoHide: false,
				padding: 18,
				contentPadding: 0,
				overlay: true,
				className: 'socialnetwork-collab-converter-error-popup',
			});
		}

		return this.#popup;
	}

	#renderPopupContent(): HTMLElement
	{
		const config = this.#getContentConfig();

		return Tag.render`
			<div class="socialnetwork-collab-converter-error-popup-content">
				${this.#renderHeader(config)}
				${this.#renderDescription(config)}
				${this.#renderLinkMore(config)}
				${this.#renderButtons(config)}
			</div>
		`;
	}

	#renderHeader(config: PopupContentConfig): HTMLElement
	{
		const headerText = config.header.length > 0
			? Tag.render`<div class="socialnetwork-collab-converter-error-popup-header-text">${config.header}</div>`
			: ''
		;

		const closeIcon = Tag.render`
			<div class="ui-icon-set --${Outline.CROSS_L} socialnetwork-collab-converter-error-popup-close-icon"/>
		`;

		Event.bind(closeIcon, 'click', () => this.#popup?.close());

		return Tag.render`
			<div class="socialnetwork-collab-converter-error-popup-header">
				${headerText}
				${closeIcon}
			</div>
		`;
	}

	#renderDescription(config: PopupContentConfig): HTMLElement
	{
		const nodes = [];

		config.descriptionParagraphs.forEach((paragraph) => {
			const node = Tag.render`
				<div class="socialnetwork-collab-converter-error-popup-description-paragraph">
					${paragraph}
				</div>
			`;
			nodes.push(node);
		});

		return Tag.render`
			<div class="socialnetwork-collab-converter-error-popup-description">
				${nodes}
			</div>
		`;
	}

	#renderLinkMore(config: PopupContentConfig): ?HTMLElement
	{
		if (!config.helperCode)
		{
			return null;
		}

		const node = Tag.render`
			<div class="socialnetwork-collab-converter-error-popup-link-more">
				<div class="ui-icon-set --${Outline.KNOWLEDGE_BASE} socialnetwork-collab-converter-error-popup-link-more-icon"></div>
				<div class="socialnetwork-collab-converter-error-popup-link-more-text">
					${Loc.getMessage('SN_COLLAB_CONVERTER_LINK_MORE')}
				</div>
			</div>
		`;

		Event.bind(node, 'click', () => BX.Helper.show(`redirect=detail&code=${config.helperCode}`));

		return node;
	}

	#renderButtons(config: PopupContentConfig): HTMLElement
	{
		return Tag.render`
			<div class="socialnetwork-collab-converter-error-popup-buttons">
				${config.buttons.map((button: Button) => button.render())}
			</div>
		`;
	}

	#getContentConfig(): ?PopupContentConfig
	{
		let config: PopupContentConfig = null;

		if (this.#hasMultipleErrors())
		{
			config = this.#getMultipleErrorsPopupConfig();
		}
		else if (this.#hasOnlyFlowError())
		{
			config = this.#getOnlyFlowErrorPopupConfig();
		}

		return config;
	}

	#getMultipleErrorsPopupConfig(): PopupContentConfig
	{
		const closeButton = new Button({
			useAirDesign: true,
			noCaps: true,
			size: ButtonSize.LARGE,
			text: Loc.getMessage('SN_COLLAB_CONVERTER_GET_IT'),
			onclick: () => this.#popup?.close(),
		});

		return {
			header: '',
			descriptionParagraphs: [
				Loc.getMessage('SN_COLLAB_CONVERTER_ERROR_POPUP_CONTENT_MULTIPLE'),
			],
			helperCode: '25356654#int',
			buttons: [
				closeButton,
			],
		};
	}

	#getOnlyFlowErrorPopupConfig(): PopupContentConfig
	{
		const flowsLink = Loc.getMessage('SN_COLLAB_CONVERTER_FLOW_URL_TEMPLATE', {
			'#groupId#': this.#groupId,
		});

		const clickHandler = (event) => {
			SidePanel.Instance.open(flowsLink);
			this.#popup?.close();
		};

		const openFlowsButton = new Button({
			useAirDesign: true,
			noCaps: true,
			size: ButtonSize.LARGE,
			text: Loc.getMessage('SN_COLLAB_CONVERTER_ERROR_POPUP_BUTTON_CONFIRM_FLOWS'),
			onclick: clickHandler,
		});

		const cancelButton = new Button({
			useAirDesign: true,
			noCaps: true,
			style: AirButtonStyle.OUTLINE,
			size: ButtonSize.LARGE,
			text: Loc.getMessage('SN_COLLAB_CONVERTER_CANCEL'),
			onclick: () => this.#popup?.close(),
		});

		return {
			header: Loc.getMessage('SN_COLLAB_CONVERTER_ERROR_POPUP_TITLE_FLOWS'),
			descriptionParagraphs: [
				Loc.getMessage('SN_COLLAB_CONVERTER_ERROR_POPUP_CONTENT_FLOWS'),
			],
			helperCode: 21_307_012,
			buttons: [
				openFlowsButton,
				cancelButton,
			],
		};
	}

	#hasMultipleErrors(): boolean
	{
		return this.#errors.length > 1;
	}

	#hasOnlyFlowError(): boolean
	{
		return this.#errors.length === 1 && this.#errors[0].code === this.ERROR_CODE_HAS_FLOWS;
	}
}
