<?php
namespace Bitrix\UI\Avatar\Mask;

use Bitrix\Main\Application;
use Bitrix\Main\FileTable;
use Bitrix\Main;
use Bitrix\Main\ORM;
use Bitrix\Main\ORM\Fields\DatetimeField;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\ORM\Fields\StringField;
use Bitrix\Main\ORM\Fields\TextField;
use Bitrix\Main\ORM\Query\Join;
use Bitrix\UI\Avatar\Mask\Owner\DefaultOwner;

class GroupTable extends ORM\Data\DataManager
{
	public static function getTableName()
	{
		return 'b_ui_avatar_mask_group';
	}

	public static function getMap()
	{
		$connection = Application::getConnection();
		return array(
			(new IntegerField('ID'))
				->configurePrimary()
				->configureAutocomplete(),
			new DatetimeField('TIMESTAMP_X'),

			(new StringField('OWNER_TYPE'))->configureRequired()->configureSize(100),
			(new StringField('OWNER_ID', []))->configureRequired()->configureSize(20),
			(new IntegerField('SORT', []))->configureDefaultValue(100),

			(new StringField('TITLE'))->configureRequired(),
			new TextField('DESCRIPTION'),
		);
	}
}

class Group
{
	protected int $id;
	protected array $data;
	protected DefaultOwner $owner;

	public function __construct(int $id)
	{
		if ($id > 0 && ($this->data = GroupTable::getById($id)->fetch()))
		{
			$this->id = $id;
			if (is_subclass_of($this->data['OWNER_TYPE'], DefaultOwner::class))
			{
				$this->owner = new $this->data['OWNER_TYPE']($this->data['OWNER_ID']);
			}
			else
			{
				throw new Main\ArgumentTypeException("Mask group ({$this->data['OWNER_TYPE']}) is unreachable.");
			}
		}
		else
		{
			throw new Main\ObjectNotFoundException("Mask group id ($id) is not found.");
		}
	}

	public function getId(): int
	{
		return $this->id;
	}

	public function isEditableBy(Mask\Consumer $consumer): bool
	{
		if ($consumer->isAdmin())
		{
			return true;
		}
		if ($this->getOwner() instanceof Owner\User && $this->getOwner()->getId() === $consumer->getId())
		{
			return true;
		}
		return false;
	}

	public function update(array $data): Main\Result
	{
		$dataToSave = array_intersect_key($data, ['TITLE' => null, 'DESCRIPTION' => null]);
		if (!empty($dataToSave))
		{
			GroupTable::update($this->getId(), $dataToSave);
		}

		return new Main\Result();
	}

	public function delete(): Main\Result
	{
		return GroupTable::delete($this->getId());
	}

	public function getOwner(): DefaultOwner
	{
		return $this->owner;
	}

	public static function createOrGet(DefaultOwner $owner, string $title, ?string $description = null): ?Group
	{
		if ($group = GroupTable::getList([
			'select' => ['ID', 'DESCRIPTION'],
			'filter' => [
				'=OWNER_TYPE' => get_class($owner),
				'=OWNER_ID' => $owner->getId(),
				'=TITLE' => $title
			]
		])->fetch())
		{
			$groupId = $group['ID'];
			if ($group['DESCRIPTION'] != $description)
			{
				GroupTable::update($groupId, ['DESCRIPTION' => $description]);
			}
		}
		else
		{
			$groupId = GroupTable::add(['fields' => [
				'OWNER_TYPE' => get_class($owner),
				'OWNER_ID' => $owner->getId(),
				'TITLE' => $title,
				'DESCRIPTION' => $description
			]])->getId();
		};
		if ($groupId > 0)
		{
			return static::getInstance($groupId);
		}
		return null;
	}

	public static function getInstance($id): ?Group
	{
		try
		{
			return new static($id);
		}
		catch (Main\ObjectNotFoundException $exception)
		{
			return null;
		}
	}
}
