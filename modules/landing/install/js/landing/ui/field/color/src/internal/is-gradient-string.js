import {matcherHex} from "./is-hex";
import {regexpToString} from "./regexp";

export const matcherGradient = /^(linear|radial)-gradient\(.*\)$/i;
export const matcherGradientAngle = /^(linear|radial)-gradient\(.*?((\d)+deg).*?\)$/ig;
const hexMatcher = regexpToString(matcherHex);
export const matcherGradientColors = new RegExp(
	'((rgba|hsla)?\\([\\d% .,]+\\)|transparent|' + hexMatcher + ')+', 'ig'
);
// todo: whooooouuuu, is so not-good

// todo: add hex greaident match

// todo: for tests
// "linear-gradient(45deg, rgb(71, 155, 255) 0%, rgb(0, 207, 78) 100%)"
// "linear-gradient(45deg, #123321 0%, #543asdbd 100%)"
// "linear-gradient(rgb(71, 155, 255) 0%, rgb(0, 207, 78) 100%)"
// "radial-gradient(circle farthest-side, rgb(34, 148, 215), rgb(39, 82, 150))"

export default function isGradientString(rgbString: string): boolean
{
	return !!rgbString.trim().match(matcherGradient);
}