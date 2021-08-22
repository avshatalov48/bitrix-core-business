import {Vuex} from 'ui.vue.vuex';
import {Vue} from 'ui.vue';
import {config} from '../../config';
import {BaseEvent, EventEmitter} from 'main.core.events';
import {Popup} from 'main.popup';
import {FormMode} from '../../types/form-mode';
import {FormCompilationType} from '../../types/form-compilation-type';
import {FormHelpdeskCode} from '../../types/form-helpdesk-code';
import {ajax, Event, Tag, Dom, Loc, Type} from 'main.core';
import {Loader} from 'main.loader';
import {Label, LabelColor} from 'ui.label';
import {MessageCard} from 'ui.messagecard';
import 'ui.vue.components.hint';
import 'ui.notification';
import 'ui.info-helper';
import 'main.qrcode';
import 'clipboard';
import 'helper';

Vue.component(config.templatePanelCompilation,
{
	props: {
		compilationOptions: Object,
		mode: String,
	},
	created()
	{
		EventEmitter.subscribe('BX.Catalog.ProductSelector:onChange', this.destroyPopup.bind(this));
		EventEmitter.subscribe('BX.Catalog.ProductSelector:onClear', this.destroyPopup.bind(this));
		this.newLabel = new Label({
			text: this.localize.CATALOG_FORM_COMPILATION_PRODUCT_NEW_LABEL,
			color: LabelColor.PRIMARY,
			fill: true
		});

		const moreMessageButton = Tag.render`
			<a class="ui-btn ui-btn-primary">${this.localize.CATALOG_FORM_COMPILATION_INFO_BUTTON_MORE}</a>
		`;

		Event.bind(moreMessageButton, 'click', this.openHelpDesk);

		let header = '';
		let description = '';
		if (this.isFacebookForm())
		{
			header = this.localize.CATALOG_FORM_COMPILATION_INFO_MESSAGE_TITLE_FACEBOOK;
			description = Tag.render`
				<p>${this.localize.CATALOG_FORM_COMPILATION_INFO_MESSAGE_BODY_FACEBOOK_FIRST_BLOCK}</p>
				<p>${this.localize.CATALOG_FORM_COMPILATION_INFO_MESSAGE_BODY_FACEBOOK_SECOND_BLOCK}</p>
			`;
		}
		else
		{
			header = this.localize.CATALOG_FORM_COMPILATION_INFO_MESSAGE_TITLE;
			description = this.localize.CATALOG_FORM_COMPILATION_INFO_MESSAGE_BODY_MARKETING;
		}

		this.message = new MessageCard({
			id: 'compilationInfo',
			header,
			description,
			angle: false,
			hidden: true,
			actionElements: [moreMessageButton]
		});

		EventEmitter.subscribe(this.message, 'onClose', this.hideMessage);
	},
	mounted()
	{
		this.$refs.label.appendChild(this.newLabel.render());
		this.$refs.message.appendChild(this.message.getLayout());
		if (!this.compilationOptions.hiddenInfoMessage)
		{
			this.showMessage();
		}
	},
	data()
	{
		return {
			compilationLink: null,
		}
	},
	methods:
	{
		isFacebookForm()
		{
			return this.compilationOptions.type === FormCompilationType.FACEBOOK;
		},
		openHelpDesk()
		{
			this.helpdeskCode =
				this.isFacebookForm()
					? FormHelpdeskCode.COMPILATION_FACEBOOK
					: FormHelpdeskCode.COMMON_COMPILATION
			;

			top.BX.Helper.show('redirect=detail&code=' + this.helpdeskCode);
		},
		showPopup(event: BaseEvent): void
		{
			if (this.compilationOptions.disabledSwitcher)
			{
				return;
			}

			if (this.isFacebookForm())
			{
				this.openHelpDesk();
				return;
			}

			if (this.popup instanceof Popup)
			{
				this.popup.setBindElement(this.$refs.qrLink);
				this.popup.show();
				return;
			}
			const basket = this.$store.getters['productList/getBasket']();
			const productIds = basket.map((basketItem) => {
				return basketItem?.fields?.skuId
			});

			return new Promise(
				(resolve, reject) => {
					ajax.runAction(
						'salescenter.api.store.getLinkToProductCollection',
						{
							json: {
								productIds
							}
						}
					)
						.then(response => {
							this.compilationLink = response.data.link
							this.popup = new Popup({
								bindElement: event.target,
								content: this.getQRPopupContent(),
								width: 375,
								closeIcon: { top: '5px', right: '5px' },
								padding: 0,
								closeByEsc: true,
								autoHide: true,
								cacheable: true,
								animation: 'fading-slide',
								angle: { offset: 30 }
							});

							this.popup.show();
							resolve();
						})
						.catch(() => reject());
				});
		},
		destroyPopup(): void
		{
			if (this.popup instanceof Popup)
			{
				this.popup.destroy();
				this.popup = null;
			}
		},
		getQRPopupContent(): HTMLElement
		{
			if (!this.compilationLink)
			{
				return '';
			}

			const buttonCopy = Tag.render`
				<div class="catalog-pf-product-qr-popup-copy">${this.localize.CATALOG_FORM_COMPILATION_QR_COPY}</div>
			`;

			Event.bind(buttonCopy, 'click', () => {
				BX.clipboard.copy(this.compilationLink);
				BX.UI.Notification.Center.notify({
					content: this.localize.CATALOG_FORM_COMPILATION_QR_COPY_NOTIFY_MESSAGE,
					autoHideDelay: 2000,
				});
			});

			const qrWrapper = Tag.render`<div class="catalog-pf-product-qr-popup-image"></div>`;

			const content = Tag.render`
					<div class="catalog-pf-product-qr-popup">
						<div class="catalog-pf-product-qr-popup-content">
							<div class="catalog-pf-product-qr-popup-text">${this.localize.CATALOG_FORM_COMPILATION_QR_POPUP_TITLE}</div>
							${qrWrapper}
							<div class="catalog-pf-product-qr-popup-buttons">
								<a href="${this.compilationLink}" target="_blank" class="ui-btn ui-btn-light-border ui-btn-round">${this.localize.CATALOG_FORM_COMPILATION_QR_POPUP_INPUT_TITLE}</a>
							</div>
						</div>
						<div class="catalog-pf-product-qr-popup-bottom">
							<a href="${this.compilationLink}" target="_blank" class="catalog-pf-product-qr-popup--url">${this.compilationLink}</a>
							${buttonCopy}
						</div>					
					</div>
				`;

			new QRCode(qrWrapper, {
				text: this.compilationLink,
				width: 250,
				height: 250
			});

			return content;
		},
		setSetting(event)
		{
			const value = event.target.checked ? 'Y' : 'N';
			if (!this.compilationOptions.hasStore)
			{
				this.compilationOptions.disabledSwitcher = true;
				const creationStorePopup = new Popup({
					bindElement: event.target,
					className: 'catalog-product-form-popup--creating-shop',
					content: this.getOnBeforeCreationStorePopupContent(),
					width: 310,
					overlay: true,
					padding: 17,
					animation: 'fading-slide',
					angle: false,
				});

				creationStorePopup.show();
				ajax.runAction('salescenter.api.store.getStoreInfo', {
					json: {}}
				)
					.then((response) => {
						if (Type.isStringFilled(response.data?.deactivatedStore?.TITLE))
						{
							const title = Loc.getMessage(
								'CATALOG_FORM_COMPILATION_UNPUBLISHED_STORE',
								{'#STORE_TITLE#': Tag.safe`${response.data?.deactivatedStore?.TITLE}`}
							);

							BX.UI.Notification.Center.notify({
								content: Tag.render`
									<div>
										<span>${title}</span>
										<a href="/shop/stores/" target="_blank">
											${Loc.getMessage('CATALOG_FORM_COMPILATION_UNPUBLISHED_STORE_LINK')}
										</a>
									</div>
								`,
							});
						}
						creationStorePopup.setContent(
							this.getOnAfterCreationStorePopupContent()
						);

						creationStorePopup.setClosingByEsc(true);
						creationStorePopup.setAutoHide(true);

						creationStorePopup.show();
						this.$root.$app.changeFormOption('isCompilationMode', value);
						this.compilationOptions.disabledSwitcher = this.compilationOptions.isLimitedStore;
						this.compilationOptions.hasStore = true;
					});
			}
			else
			{
				this.$root.$app.changeFormOption('isCompilationMode', value);
			}
		},
		getOnBeforeCreationStorePopupContent()
		{
			const loaderContent = Tag.render`
				<div class="catalog-product-form-popup--loader-block"></div>
			`;

			const node = Tag.render`
				<div class="catalog-product-form-popup--container">
					<div class="catalog-product-form-popup--title">${Loc.getMessage('CATALOG_FORM_POPUP_BEFORE_MARKET_CREATING')}</div>
					${loaderContent}
					<div class="catalog-product-form-popup--text">${Loc.getMessage('CATALOG_FORM_POPUP_BEFORE_MARKET_CREATING_INFO')}</div>
				</div>
			`;

			const loader = new Loader({
				color: "#2fc6f6",
				target: loaderContent,
				size: 40,
			});

			loader.show();

			return node;
		},
		getOnAfterCreationStorePopupContent()
		{
			return Tag.render`
				<div class="catalog-product-form-popup--container">
					<div class="catalog-product-form-popup--title">${Loc.getMessage('CATALOG_FORM_POPUP_AFTER_MARKET_CREATING')}</div>
					<div class="catalog-product-form-popup--loader-block catalog-product-form-popup--done"></div>
					<div class="catalog-product-form-popup--text">${Loc.getMessage('CATALOG_FORM_POPUP_AFTER_MARKET_CREATING_INFO')}</div>
				</div>
			`;
		},
		onLabelClick()
		{
			if (this.compilationOptions.isLimitedStore)
			{
				BX.UI.InfoHelper.show('limit_sites_number');
			}
		},
		onClickHint(event: BaseEvent)
		{
			event.preventDefault();
			event.stopImmediatePropagation();
			if (!this.message)
			{
				return;
			}
			if (this.message.isShown())
			{
				this.hideMessage()
			}
			else
			{
				this.showMessage();
			}
		},
		showMessage()
		{
			if (this.message)
			{
				Dom.addClass(this.$refs.hintIcon, 'catalog-pf-product-panel-message-arrow-target');
				this.message.show();
			}
		},
		hideMessage()
		{
			if (this.message)
			{
				Dom.removeClass(this.$refs.hintIcon, 'catalog-pf-product-panel-message-arrow-target');
			}
			this.message.hide();
			this.$root.$app.changeFormOption('hiddenCompilationInfoMessage', 'Y');
		}
	},
	computed:
	{
		localize()
		{
			return Vue.getFilteredPhrases('CATALOG_');
		},

		showQrLink()
		{
			return this.mode === FormMode.COMPILATION;
		},

		...Vuex.mapState({
			productList: state => state.productList,
		})
	},
	// language=Vue
	template: `
		<div>
			<div class="catalog-pf-product-panel-compilation">
				<div class="catalog-pf-product-panel-compilation-wrapper">
					<label class="ui-ctl ui-ctl-checkbox" @click="onLabelClick">
						<input 
							type="checkbox" 
							:disabled="compilationOptions.disabledSwitcher"
							class="ui-ctl-element" 
							@change="setSetting" 
							data-setting-id="isCompilationMode"
						>
						<div class="ui-ctl-label-text">{{localize.CATALOG_FORM_COMPILATION_PRODUCT_SWITCHER}}</div>
						<div ref="hintIcon">
							<div data-hint-init="vue" class="ui-hint" @click="onClickHint">
								<span class="ui-hint-icon"></span>
							</div>
						</div>
						<div ref="label"></div>
						<div class="tariff-lock" v-if="compilationOptions.isLimitedStore"></div>
					</label>
				</div>
				<div 				
					v-if="showQrLink"
					class="catalog-pf-product-panel-compilation-link --icon-qr"
					@click="showPopup"
					ref="qrLink"
				>
					{{localize.CATALOG_FORM_COMPILATION_QR_LINK}}
				</div>
			</div>
			<div class="catalog-pf-product-panel-compilation-message" ref="message"></div>
		</div>
	`
});