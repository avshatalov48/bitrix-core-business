export default class DistanceCalculator
{
	/**
	 * @param {number} lat1
	 * @param {number} lon1
	 * @param {number} lat2
	 * @param {number} lon2
	 * @returns {number}
	 */
	static getDistanceFromLatLonInKm(lat1: number, lon1: number, lat2: number, lon2: number): number
	{
		const R = 6371; // Radius of the earth in km
		const dLat = DistanceCalculator.deg2rad(lat2 - lat1);
		const dLon = DistanceCalculator.deg2rad(lon2 - lon1);
		const a =	Math.sin(dLat / 2) * Math.sin(dLat / 2)
			+ Math.cos(DistanceCalculator.deg2rad(lat1)) * Math.cos(DistanceCalculator.deg2rad(lat2))
			* Math.sin(dLon / 2) * Math.sin(dLon / 2);

		const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
		return R * c;
	}

	/**
	 * @param {number} deg
	 * @returns {number}
	 */
	static deg2rad(deg: number): number
	{
		return deg * (Math.PI / 180);
	}
}
