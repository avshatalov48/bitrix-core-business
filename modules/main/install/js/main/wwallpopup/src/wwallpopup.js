import { Cache, Loc, Tag, Type } from 'main.core';
import { Popup } from 'main.popup';
import { Button } from 'ui.buttons';

export class WwallPopup
{
	constructor(options)
	{
		this.colorTheme = options.colorTheme || 'danger';
		this.title = options.title || null;
		this.subtitle = options.subtitle || null;
		this.text = options.text || null;
		this.isToolTipShow = Type.isBoolean(options.isToolTipShow) ? options.isToolTipShow : false;
		this.closeIcon = Type.isBoolean(options.closeIcon) ? options.closeIcon : true;
		this.isSuccess = Type.isBoolean(options.isSuccess) ? options.isSuccess : false;
		this.cache = new Cache.MemoryCache();
		this.buttons = options.buttons || null;
	}

	getTitleWrapper(): HTMLElement
	{
		return this.cache.remember('titleBox', () => {
			return Tag.render`
				<div class='adm-security-popup_title-box'>
					${this.getTitle()}
				</div>
			`;
		});
	}

	getTitle()
	{
		const title = this.title || Loc.getMessage('SEC_WWALL_POPUP_TITLE');
		const toolTip = this.isToolTipShow ? this.getTooltip() : '';

		return Tag.render`
			<div class='adm-security-popup_title'>
				${title}
			</div>
			${toolTip}
		`;
	}

	getTooltip()
	{
		return Tag.render`
			<a class='tooltip adm-security-popup_tooltip adm-security-info_link' href='https://dev.1c-bitrix.ru/learning/course/index.php?COURSE_ID=35&LESSON_ID=27172' target='_blank'>
				${Loc.getMessage('SEC_WWALL_POPUP_TITLE_ABOUT')}
			</a>
		`;
	}

	getContent(): HTMLElement
	{
		return this.cache.remember('popupContentWarningWrap', () => {
			return Tag.render`
				<div class='adm-security-popup_wrap --${this.colorTheme}'>
					${this.getTitleWrapper()}
					<div class='adm-security-popup_content'>
						<div class="adm-security-popup_icon"></div>
						<div class="adm-security-popup_inner">
							<div class="adm-security-popup_inner-title">
								${this.subtitle || this.getSubtitle()} 
							</div>
							<div class="adm-security-popup_info">
								${this.text || this.getPopupInfo()}
							</div>
						</div>
					</div>
				</div>
			`;
		});
	}

	getSubtitle(): ?string
	{
		return this.isSuccess ? Loc.getMessage('SEC_WWALL_POPUP_ATTACK_TITLE')
			: (this.isPortal ? Loc.getMessage('SEC_WWALL_POPUP_WARNING_TITLE_CP') : Loc.getMessage('SEC_WWALL_POPUP_WARNING_TITLE'));
	}

	getPopupInfo(): ?string
	{
		return this.isSuccess ? Loc.getMessage('SEC_WWALL_POPUP_ATTACK_CONTENT')
			: (this.isPortal ? Loc.getMessage('SEC_WWALL_POPUP_WARNING_CONTENT_CP') : Loc.getMessage('SEC_WWALL_POPUP_WARNING_CONTENT'));
	}

	setButtons()
	{
		if (this.buttons)
		{
			return this.createCustomButtons();
		}

		return this.createDefaultButtons();
	}

	createDefaultButtons(): [Button, Button]
	{
		return [
			new Button({
				text: this.isSuccess ? Loc.getMessage('SEC_WWALL_POPUP_ACTION_OPEN') : Loc.getMessage('SEC_WWALL_POPUP_ACTION_UPDATE'),
				className: this.isSuccess ? 'adm-security-popup-btn-accept' : 'adm-security-popup-btn-refresh',
				events: {
					click: () => {
						document.location.href = '/bitrix/admin/update_system.php';
					},
				},
			}),
			new Button({
				text: this.isSuccess ? Loc.getMessage('SEC_WWALL_POPUP_ACTION_CONTINUE') : Loc.getMessage('SEC_WWALL_POPUP_ACTION_IGNORE'),
				className: 'adm-security-popup-btn-close',
				events: {
					click: () => {
						this.close();
					},
				},
			}),
		];
	}

	createCustomButtons()
	{
		const buttons = [];
		if (this.buttons.primary)
		{
			buttons.push(
				new Button({
					text: this.buttons.primary.text,
					className: this.setButtonStyle(this.buttons.primary.type),
					events: {
						click: () => {
							if (this.buttons.primary.onclick)
							{
								this.buttons.primary.onclick();
							}
						},
					},
				}),
			);
		}

		if (this.buttons.secondary)
		{
			buttons.push(
				new Button({
					text: this.buttons.secondary.text,
					className: this.setButtonStyle(this.buttons.secondary.type),
					events: {
						click: () => {
							if (this.buttons.secondary.onclick)
							{
								this.buttons.secondary.onclick();
							}
						},
					},
				}),
			);
		}

		return buttons;
	}

	setButtonStyle(type): string
	{
		switch (type)
		{
			case 'accept':
				return 'adm-security-popup-btn-accept';
			case 'refresh':
				return 'adm-security-popup-btn-refresh';
			case 'close':
			default:
				return 'adm-security-popup-btn-close';
		}
	}

	show()
	{
		this.popup = new Popup({
			className: 'adm-security-popup',
			closeIcon: this.closeIcon,
			contentBackground: 'transparent',
			overlay: true,
			minWidth: 500,
			content: this.getContent(),
			buttons: this.setButtons(),
			events: {
				onPopupClose() {
					this.destroy();
				},
				onPopupDestroy() {
					this.popup = null;
				},
			},
		});
		this.popup.show();
	}

	close()
	{
		this.popup.close();
	}
}
