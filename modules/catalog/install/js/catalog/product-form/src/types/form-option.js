import { FormElementPosition } from './form-element-position';
import type { BasketItem } from './basket-item';
import type { BasketMeasure } from './basket-measure';
import type { BasketTax } from './basket-tax';
import type { DiscountTypes } from 'catalog.product-calculator';
import { FormInputCode } from './form-input-code';
import type { FormCompilationOption } from './form-compilation-option';
import { FormCompilationType } from './form-compilation-type';
import { FormErrorCode } from './form-error-code';

export type FormOption = {
	basket: Array<BasketItem>,
	measures: Array<BasketMeasure>,
	iblockId?: number,
	basePriceId?: number,
	taxList: Array<BasketTax>,
	currencySymbol?: string,
	singleProductMode: boolean,
	showResults: boolean,
	showCompilationModeSwitcher: boolean,
	disabledCompilationModeSwitcher: boolean,
	enableEmptyProductError: boolean,
	enableCatalogSaving: boolean,
	currency?: string,
	pricePrecision: number,
	allowedDiscountTypes: Array<DiscountTypes>,
	taxIncluded: 'Y' | 'N',
	showDiscountBlock: 'Y' | 'N',
	showTaxBlock: 'Y' | 'N',
	newItemPosition: FormElementPosition,
	buttonsPosition: FormElementPosition,
	visibleBlocks: Array<FormInputCode>,
	validationCodes: Array<FormErrorCode>,
	requiredFields: Array<FormErrorCode>,
	editableFields: Array<FormInputCode>,
	urlBuilderContext: string,
	hideUnselectedProperties: boolean,
	compilationFormType: FormCompilationType,
	compilationFormOption: FormCompilationOption,
	isBlockedExistedPrice: boolean,
	isCatalogHidden: boolean,
	isCatalogDiscountSetEnabled: boolean,
	isCatalogPriceEditEnabled: boolean,
	isCatalogPriceSaveEnabled: boolean,
	fieldHints: {},
	facebookFailProducts?: Array,
	isAllowedFacebookRegion: boolean,
	ownerId: ?number,
	ownerTypeId: ?number,
	dialogId: ?string,
	sessionId: ?number,
};
