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
		this.newLabel = new Label({
			text: this.localize.CATALOG_FORM_COMPILATION_PRODUCT_NEW_LABEL,
			color: LabelColor.PRIMARY,
			fill: true
		});
		this.popup = null;
		this.compilationLink = null;

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
			description = this.localize.CATALOG_FORM_COMPILATION_INFO_MESSAGE_BODY_MARKETING_2;
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
						'salescenter.compilation.createCompilation',
						{
							data: {
								productIds,
								options: {
									ownerId: this.$root.$app.options.ownerId,
									ownerTypeId: this.$root.$app.options.ownerTypeId,
									dialogId: this.$root.$app.options.dialogId,
									sessionId: this.$root.$app.options.sessionId,
								}
							}
						}
					)
						.then(response => {
							this.compilationLink = response.data.link ?? null;
							EventEmitter.emit(
								this.$root.$app,
								'ProductForm:onCompilationCreated',
								{
									compilationId: response.data.compilationId ?? null,
									ownerId: response.data.ownerId ?? null,
								}
							);
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
			this.$root.$app.changeFormOption('isCompilationMode', value);
		},
		getOnBeforeCreationStorePopupContent()
		{
			const loaderContent = Tag.render`
				<div class="catalog-product-form-popup--loader-block"></div>
			`;

			const node = Tag.render`
				<div class="catalog-product-form-popup--container">
					<div class="catalog-product-form-popup--title">${Loc.getMessage('CATALOG_FORM_POPUP_BEFORE_MARKET_CREATING1')}</div>
					${loaderContent}
					<div class="catalog-product-form-popup--text">${Loc.getMessage('CATALOG_FORM_POPUP_BEFORE_MARKET_CREATING_INFO1')}</div>
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
		getOnAfterCreationStorePopupContent(creationStorePopup)
		{
			const continueButton = Tag.render`
				<button class="ui-btn ui-btn-md ui-btn-primary">
					${Loc.getMessage('CATALOG_FORM_POPUP_AFTER_MARKET_CREATING_CONTINUE')}
				</button>
			`;
			Event.bind(continueButton, 'click', this.closeCreationStorePopup.bind(this, creationStorePopup));
			return Tag.render`
				<div class="catalog-product-form-popup--container">
					<div class="catalog-product-form-popup--title">${Loc.getMessage('CATALOG_FORM_POPUP_AFTER_MARKET_CREATING1')}</div>
					<div class="catalog-product-form-popup--loader-block catalog-product-form-popup--done"></div>
					<div class="catalog-product-form-popup--text">${Loc.getMessage('CATALOG_FORM_POPUP_AFTER_MARKET_CREATING_INFO1')}</div>
					<div class="catalog-product-form-popup--button-container">${continueButton}</div>
				</div>
			`;
		},
		closeCreationStorePopup(creationStorePopup)
		{
			creationStorePopup.close();
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
					<label class="ui-ctl ui-ctl-checkbox catalog-pf-product-panel-compilation-checkbox-container" @click="onLabelClick">
						<input
							type="checkbox"
							:disabled="compilationOptions.disabledSwitcher"
							class="ui-ctl-element"
							@change="setSetting"
							data-setting-id="isCompilationMode"
						>
						<div class="ui-ctl-label-text">{{localize.CATALOG_FORM_COMPILATION_PRODUCT_SWITCHER_2}}</div>
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
			<div class="catalog-pf-product-panel-compilation-price-info">{{localize.CATALOG_FORM_COMPILATION_PRICE_NOTIFICATION}}</div>
			<div class="catalog-pf-product-panel-compilation-message" ref="message"></div>
		</div>
	`
});