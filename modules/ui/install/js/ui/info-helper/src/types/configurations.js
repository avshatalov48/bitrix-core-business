export type FeaturePromoterConfiguration = {
	code: string,
	bindElement?: HTMLElement,
}

export type PopupProviderConfiguration = {
	code: string,
	bindElement: HTMLElement,
	dataSource?: Promise,
}