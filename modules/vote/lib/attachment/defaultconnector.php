<?
namespace Bitrix\Vote\Attachment;

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Vote\Channel;

Loc::loadMessages(__FILE__);

class DefaultConnector extends Connector implements Storable
{
	private $storage = null;
	private $canRead = array();
	private $canEdit = array();

	/**
	 * @param integer $userId UserID.
	 * @return bool
	 */
	public function canRead($userId)
	{
		if (!array_key_exists($userId, $this->canRead) && $this->isStorable())
			$this->canRead[$userId] = $this->getStorage()->canRead($userId);
		return (isset($this->canRead[$userId]) ? $this->canRead[$userId] : false);
	}

	/**
	 * @param integer $userId UserID.
	 * @return bool
	 */
	public function canEdit($userId)
	{
		if (!array_key_exists($userId, $this->canEdit) && $this->isStorable())
			$this->canEdit[$userId] = $this->getStorage()->canEditVote($userId);
		return (isset($this->canEdit[$userId]) ? $this->canEdit[$userId] : false);
	}

	/**
	 * @param Channel $channel Group of votes.
	 * @return $this
	 */
	public function setStorage(Channel $channel)
	{
		$this->storage = $channel;

		return $this;
	}

	/**
	 * @return Channel|null
	 */
	public function getStorage()
	{
		return $this->storage;
	}

	/**
	 * @return bool
	 */
	public function isStorable()
	{
		return ($this->storage !== null);
	}
}
