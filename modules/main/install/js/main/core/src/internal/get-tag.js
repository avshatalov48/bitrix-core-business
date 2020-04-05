/**
 * Gets object.toString result
 * @param value
 * @return {string}
 */
export default function getTag(value: any)
{
	return Object.prototype.toString.call(value);
}