const {scrollTo, highlight} = BX.Landing.Utils;

/**
 * @param {object} entry
 * @return {Promise}
 */
export default function replaceLanding(entry)
{
	return new Promise((resolve, reject) => {
		top.window.location.reload();
		resolve();
	});
}