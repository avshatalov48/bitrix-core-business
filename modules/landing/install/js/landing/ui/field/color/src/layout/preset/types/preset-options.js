import ColorValue from '../../../color_value';
import GradientValue from '../../../gradient_value';

export type PresetOptions = {
	id?: string,
	type?: 'color' | 'gradient',
	items: [ColorValue | GradientValue],
};

export const defaultType = 'color';
export const gradientType = 'gradient';