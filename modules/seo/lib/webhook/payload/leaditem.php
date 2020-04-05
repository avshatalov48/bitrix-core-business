<?
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage seoproxy
 * @copyright 2001-2018 Bitrix
 */
namespace Bitrix\Seo\WebHook\Payload;

/**
 * Class LeadItem
 *
 * @package Bitrix\Seo\WebHook\Payload
 *
 * @method string getLeadId()
 * @method $this setLeadId(string $leadId)
 * @method string getGroupId()
 * @method $this setGroupId(string $groupId)
 * @method string getUserId()
 * @method $this setUserId(string $userId)
 * @method string getFormId()
 * @method $this setFormId(string $formId)
 * @method array getAnswers()
 * @method $this setAnswers(array $answers)
 */
class LeadItem extends Item
{
	/** @var  array $data Data. */
	protected $data = [
		'leadId' => null,
		'groupId' => null,
		'userId' => null,
		'formId' => null,
		'answers' => [],
		'source' => null,
	];

	/**
	 * Set answers.
	 *
	 * @param string $key Key.
	 * @param array $values Values.
	 * @return $this
	 */
	public function addAnswer($key, array $values)
	{
		$answers = $this->getAnswers();
		$answers[$key] = $values;
		return $this->setAnswers($answers);
	}
}

