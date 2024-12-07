import {ajax, Extension, Loc, Tag, Text, Type} from 'main.core';
import {EventEmitter} from "main.core.events";
import {ErrorCollection} from "./error-collection";
import {ImageCollection} from "./image-collection";
import {ProductOption} from "./product-option";
import {DiscountType, ProductCalculator, TaxForPriceStrategy} from "catalog.product-calculator";
import {FieldCollection} from "./field-collection";
import {StoreCollection} from "./store-collection";
import {RightActionDictionary} from "./right-action-dictionary";

const instances = new Map();

class ProductModel
{
	#fieldCollection = null;
	#errorCollection = null;
	#imageCollection = null;
	#storeCollection = null;
	#productRights = null;
	#calculator = null;
	#offerId = null;
	#skuTree = null;

	static SAVE_NOTIFICATION_CATEGORY = 'MODEL_SAVE'

	static getById(id: string): ?ProductModel
	{
		return instances.get(id) || null;
	}

	constructor(options: ProductOption = {})
	{
		this.options = options || {};
		this.id = this.options.id || Text.getRandom();

		this.#errorCollection = new ErrorCollection(this);
		this.#imageCollection = new ImageCollection(this);
		this.#fieldCollection = new FieldCollection(this);
		this.#storeCollection = new StoreCollection(this);

		const settings = Extension.getSettings('catalog.product-model');
		this.#productRights = settings.get('catalogProductRights');

		if (settings.get('isExternalCatalog'))
		{
			this.setOption('isSaveable', false);
		}

		if (Type.isObject(options.fields))
		{
			this.initFields(options.fields, false);
		}

		if (this.#isStoreCollectionEnabled())
		{
			if (Type.isNil(options.storeMap))
			{
				this.#storeCollection.refresh();
			}
			else
			{
				this.#storeCollection.init(options.storeMap);
			}
		}

		if (Type.isObject(options.skuTree))
		{
			this.setSkuTree(options.skuTree);
		}

		if (Type.isObject(options.imageInfo))
		{
			// this.getImageCollection().setMorePhotoValues(options.imageInfo.morePhoto);
		}

		this.#calculator = new ProductCalculator(this.#getDefaultCalculationFields(), {
			currencyId: this.options.currency,
			pricePrecision: this.options.pricePrecision || 2,
			commonPrecision: this.options.pricePrecision || 2,
		});
		this.#calculator.setCalculationStrategy(new TaxForPriceStrategy(this.#calculator));

		instances.set(this.id, this);
	}

	#getDefaultCalculationFields(): {}
	{
		const defaultPrice = Text.toNumber(this.#fieldCollection.getField('PRICE'));
		const basePrice = Type.isNumber(this.#fieldCollection.getField('BASE_PRICE'))
			? Text.toNumber(this.#fieldCollection.getField('BASE_PRICE'))
			: defaultPrice;

		return {
			'QUANTITY': Text.toNumber(this.#fieldCollection.getField('QUANTITY')),
			'BASE_PRICE': basePrice,
			'PRICE': defaultPrice,
			'PRICE_NETTO': basePrice,
			'PRICE_BRUTTO': defaultPrice,
			'PRICE_EXCLUSIVE': this.#fieldCollection.getField('PRICE_EXCLUSIVE') || defaultPrice,
			'DISCOUNT_TYPE_ID': this.#fieldCollection.getField('DISCOUNT_TYPE_ID') || DiscountType.PERCENTAGE,
			'DISCOUNT_RATE': Text.toNumber(this.#fieldCollection.getField('DISCOUNT_RATE')),
			'DISCOUNT_SUM': Text.toNumber(this.#fieldCollection.getField('DISCOUNT_SUM')),
			'TAX_INCLUDED': this.#fieldCollection.getField('TAX_INCLUDED') || 'N',
			'TAX_RATE': Text.toNumber(this.#fieldCollection.getField('TAX_RATE')) || 0,
			'CUSTOMIZED': this.#fieldCollection.getField('CUSTOMIZED') || 'N',
		};
	}

	checkAccess(action: RightActionDictionary): boolean
	{
		return Text.toBoolean(this.#productRights[action] ?? false);
	}

	getOption(name: string, defaultValue: any = null): any
	{
		return this.options[name] ?? defaultValue
	}

	setOption(name: string, value: any = null): this
	{
		this.options[name] = value;

		return this;
	}

	setSkuTree(skuTree: {} = null): this
	{
		this.#skuTree = skuTree;

		return this;
	}

	clearSkuTree(): this
	{
		this.#skuTree = null;

		return this;
	}

	getSkuTree(): ?{}
	{
		return this.#skuTree;
	}

	getCalculator(): ProductCalculator
	{
		return this.#calculator;
	}

	getErrorCollection(): ErrorCollection
	{
		return this.#errorCollection;
	}

	getImageCollection(): ImageCollection
	{
		return this.#imageCollection;
	}

	getFields(): {}
	{
		return this.#fieldCollection.getFields();
	}

	getStoreCollection(): StoreCollection
	{
		return this.#storeCollection;
	}

	getField(fieldName: string): any
	{
		return this.#fieldCollection.getField(fieldName);
	}

	setField(fieldName: string, value: any): ProductModel
	{
		this.#fieldCollection.setField(fieldName, value);

		if (
			(
				fieldName === 'SKU_ID' || fieldName === 'PRODUCT_ID'
			)
			&& this.getSkuId() !== this.#offerId
		)
		{
			this.#offerId = this.getSkuId();
			if (this.#offerId > 0 && this.#isStoreCollectionEnabled())
			{
				this.#storeCollection.refresh();
			}
		}

		return this;
	}

	setFields(fields): ProductModel
	{
		Object.keys(fields).forEach((key) => {
			this.setField(key, fields[key]);
		});

		return this;
	}

	initFields(fields: {}, refreshStoreInfo: boolean = true): ProductModel
	{
		this.#fieldCollection.initFields(fields);
		this.#offerId = this.getSkuId();
		if (refreshStoreInfo && this.#isStoreCollectionEnabled())
		{
			this.#storeCollection.refresh();
		}

		return this;
	}

	removeField(fieldName): ProductModel
	{
		this.#fieldCollection.removeField(fieldName);

		return this;
	}

	isChanged(): boolean
	{
		return this.#fieldCollection.isChanged();
	}

	isNew(): boolean
	{
		return this.getOption('isNew', false);
	}
	
	#isStoreCollectionEnabled(): boolean
	{
		return this.getOption('isStoreCollectable', true);
	}

	getSkuId(): ?number
	{
		return this.getField('SKU_ID') || this.getProductId();
	}

	getProductId(): ?number
	{
		return this.getField('PRODUCT_ID') || null;
	}

	isCatalogExisted(): boolean
	{
		return this.getSkuId() > 0;
	}

	isEmpty(): boolean
	{
		return this.getProductId() === null && !this.isSimple();
	}

	isSimple(): boolean
	{
		return this.getOption('isSimpleModel', false);
	}

	getIblockId(): boolean
	{
		return this.getOption('iblockId', 0);
	}

	getBasePriceId(): number
	{
		return this.getOption('basePriceId', 0);
	}

	getCurrency(): number
	{
		return this.getOption('currency', null);
	}

	getDetailPath(): string
	{
		return this.getOption('detailPath', '');
	}

	setDetailPath(value: string): void
	{
		this.options['detailPath'] = value || '';
	}

	isService(): boolean
	{
		const type = parseInt(this.getField('TYPE'));
		return type === 7; // \Bitrix\Catalog\ProductTable::TYPE_SERVICE
	}

	showSaveNotifier(id: string, options: {})
	{
		if (!this.isCatalogExisted())
		{
			return;
		}

		const title = options.title || '';
		const closeEventName = BX.UI.Notification.Event.getFullName('onClose');
		const cancelEventName = BX.UI.Notification.Event.getFullName('onCancel');

		new Promise((resolve) => {
			const currentBalloon = BX.UI.Notification.Center.getBalloonByCategory(ProductModel.SAVE_NOTIFICATION_CATEGORY);
			if (currentBalloon && currentBalloon.getId() !== id)
			{
				setTimeout(() => {
					currentBalloon.close();
					setTimeout(resolve, 400);
				}, 200);
			}
			else
			{
				resolve();
			}
		})
			.then(() => {
				let notify = BX.UI.Notification.Center.getBalloonById(id);
				if (!notify)
				{
					const notificationOptions = {
						id,
						closeButton: true,
						category: ProductModel.SAVE_NOTIFICATION_CATEGORY,
						autoHideDelay: 4000,
						content: Tag.render`<div>${title}</div>`,

					};

					if (options.disableCancel !== true)
					{
						notificationOptions.actions = [
							{
								title: options.declineCancelTitle || Loc.getMessage('CATALOG_PRODUCT_MODEL_SAVING_NOTIFICATION_DECLINE_SAVE'),
								events: {
									click: (event, balloon) => {
										BX.removeAllCustomEvents(notify, closeEventName);
										balloon.fireEvent('onCancel');
										balloon.close();
									}
								}
							}
						];
					}

					notify = BX.UI.Notification.Center.notify(notificationOptions);
				}

				BX.removeAllCustomEvents(notify, closeEventName);
				notify.addEvent('onClose', () => {
					if (Type.isFunction(options?.events?.onSave))
					{
						(options.events.onSave)();
					}
				});

				BX.removeAllCustomEvents(notify, cancelEventName);
				notify.addEvent('onCancel', () => {
					if (Type.isFunction(options?.events?.onCancel))
					{
						(options.events.onCancel)();
					}
				});

				notify.show();
			});
	}

	static getLastActiveSaveNotification(): ?BX.UI.Notification.Balloon
	{
		return BX.UI.Notification.Center.getBalloonByCategory(ProductModel.SAVE_NOTIFICATION_CATEGORY);
	}

	save(savingFieldNames: []): ?Promise
	{
		if (!this.isSaveable())
		{
			return;
		}

		return new Promise((resolve, reject) => {
			let ajaxResult;
			if (this.isSimple())
			{
				ajaxResult = this.#createProduct();
			}
			else
			{
				ajaxResult = this.#updateProduct(savingFieldNames);
			}

			ajaxResult
				.then((event) => {
					this.#fieldCollection.clearChanged(savingFieldNames);
					resolve(event);
				})
				.catch(reject)
		});
	}

	isSaveable(): boolean
	{
		if (!this.getOption('isSaveable', true) || this.isEmpty())
		{
			return false;
		}

		return this.isSimple()
			? this.checkAccess(RightActionDictionary.ACTION_PRODUCT_ADD)
			: this.checkAccess(RightActionDictionary.ACTION_PRODUCT_EDIT)
		;
	}

	onErrorCollectionChange()
	{
		EventEmitter.emit(this,'onErrorsChange');
	}

	onChangeStoreData()
	{
		EventEmitter.emit(this,'onChangeStoreData');
	}

	#updateProduct(savingFieldNames: [])
	{
		if (this.getIblockId() <= 0)
		{
			return Promise.reject({
				status: 'error',
				errors: [
					'The iblock id is not set for the model.'
				],
			});
		}

		if (!this.#fieldCollection.isChanged())
		{
			return Promise.resolve({
				status: 'success',
				data: {
					id: this.getSkuId(),
				},
			});
		}

		let savedFields = {};
		if (!Type.isArray(savingFieldNames) || savingFieldNames.length === 0)
		{
			savedFields = this.#fieldCollection.getChangedFields();
		}
		else
		{
			const changedFields = this.#fieldCollection.getChangedFields();
			Object.keys(changedFields).forEach((key) => {
				if (savingFieldNames.includes(key))
				{
					if (key === 'PRICE' || key === 'BASE_PRICE')
					{
						savedFields['PRICES'] = savedFields['PRICES'] || {};
						savedFields['PRICES'][this.getBasePriceId()] = {
							PRICE: changedFields[key],
							CURRENCY: this.getCurrency(),
						};
					}
					else
					{
						savedFields[key] = changedFields[key];
					}
				}
			});
		}

		return ajax.runAction(
			'catalog.productSelector.updateSku',
			{
				json: {
					id: this.getSkuId(),
					updateFields: savedFields,
					oldFields: this.#fieldCollection.getChangedValues(),
				}
			}
		);
	}

	#createProduct(): Promise
	{
		const fields = {
			NAME: this.#fieldCollection.getField('NAME', ''),
			IBLOCK_ID: this.getIblockId()
		};

		const price = this.#fieldCollection.getField('BASE_PRICE', null);
		if (!Type.isNil(price))
		{
			fields['PRICE'] = price;
		}

		const barcode = this.#fieldCollection.getField('BARCODE', null);
		if (!Type.isNil(barcode))
		{
			fields['BARCODE'] = barcode;
		}

		fields['CURRENCY'] = this.getCurrency();
		const currency = this.#fieldCollection.getField('CURRENCY', null);
		if (Type.isStringFilled(currency))
		{
			fields['CURRENCY'] = currency;
		}

		return ajax.runAction(
			'catalog.productSelector.createProduct',
			{
				json: {
					fields
				}
			}
		)
	}
}

export {ProductModel, RightActionDictionary}
