export interface IColorValue {
	getName(): string;
	setValue(any): {};
	setOpacity(): {};
	getOpacity(): number;
	getStyleString(): string;
	getStyleStringForOpacity(): string;
}