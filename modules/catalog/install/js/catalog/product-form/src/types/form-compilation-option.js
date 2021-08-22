import {FormCompilationType} from "./form-compilation-type";

export type FormCompilationOption = {
	type: FormCompilationType,
	hasStore: boolean,
	isLimitedStore: boolean,
	hiddenInfoMessage: boolean,
	disabledSwitcher: boolean,
}