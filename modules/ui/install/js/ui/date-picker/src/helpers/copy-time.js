export function copyTime(from: Date, to: Date): void
{
	to.setUTCHours(from.getUTCHours());
	to.setUTCMinutes(from.getUTCMinutes());
	to.setUTCSeconds(from.getUTCSeconds());
}
