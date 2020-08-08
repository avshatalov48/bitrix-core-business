<?

namespace Bitrix\Sender\Integration\Yandex\Toloka;

use Bitrix\Main\Loader;
use Bitrix\Main\SystemException;
use Bitrix\Sender\Integration\Yandex\Toloka\DTO\Pool;
use Bitrix\Sender\Integration\Yandex\Toloka\DTO\Project;
use Bitrix\Sender\Integration\Yandex\Toloka\DTO\Task;
use Bitrix\Sender\Integration\Yandex\Toloka\DTO\TaskSuite;
use Bitrix\Seo\Service;
use COption;

class ApiRequest extends BaseApiObject
{
	public const ACCESS_CODE = 'toloka_access_code';

	/**
	 * ApiRequest constructor.
	 */
	public function __construct()
	{
		if (!Loader::includeModule('seo'))
		{
			throw new SystemException('Module seo not installed.');
		}

		if (!Service::isRegistered())
		{
			Service::register();
		}
		$authorizeData = Service::getAuthorizeData(\Bitrix\Seo\Engine\Bitrix::ENGINE_ID, 'M');

		$this->setAccessToken(COption::GetOptionString('sender', self::ACCESS_CODE));
		$this->setClientId($authorizeData['client_id']);
		$this->setClientSecret($authorizeData['client_secret']);
	}

	public function getTaskList($params = [])
	{
		$this->sendRequest(
			[
				'methodName' => 'project.list',
				'pool_id'    => $params['pool_id'] ?? 0,
				'limit'      => $params['limit'] ?? 10,
				'status'     => $params['status'] ?? 'ACTIVE',
				'sort'       => $params['sort'] ?? '-id',
			]
		);

		return $this->result;
	}

	public function getPoolList($params = [])
	{
		$this->sendRequest(
			[
				'methodName' => 'pool.list',
				'parameters' => [
					'project_id' => $params['project_id'] ?? 0,
					'limit'      => $params['limit'] ?? 10,
					'sort'       => $params['sort'] ?? '-id',
				]
			]
		);

		return $this->result;
	}

	public function getProjectList($params = [])
	{
		$this->sendRequest(
			[
				'methodName' => 'project.list',
				'parameters' => [

					'limit'  => $params['limit'] ?? 10,
					'status' => $params['status'] ?? 'ACTIVE',
					'sort'   => $params['sort'] ?? '-id',
				]
			]
		);

		return $this->result;
	}

	public function getGeoList($params = [])
	{
		$this->sendRequest(
			[
				'methodName' => 'geo.list',
				'parameters' => [
					'limit'  => $params['limit'] ?? 10,
					'regionType' => $params['regionType'] ?? 'all',
					'lang'   => strtoupper(LANGUAGE_ID),
					'name'   =>  $params['name'] ? ucfirst($params['name']): ''
				]
			]
		);

		return $this->result;
	}

	public function getProjectInfo($params = [])
	{
		$this->sendRequest(
			[
				'methodName' => 'project.info',
				'id' => $params['id'] ?? 0,
			]
		);

		return $this->result;
	}

	public function createProject(Project $project)
	{
		$this->sendRequest(
			[
				'methodName' => 'project.create',
				'parameters' => $project->toArray()
			]
		);

		return $this->result;
	}

	public function createPool(Pool $pool)
	{
		$this->sendRequest(
			[
				'methodName' => 'pool.create',
				'parameters' => $pool->toArray()
			]
		);

		return $this->result;
	}

	public function editProject(Project $project)
	{

		$this->sendRequest(
			[
				'methodName' => 'project.edit',
				'parameters' => array_merge(
					['id' => $project->getId()],
					$project->toArray()
				)
			]
		);

		return $this->result;
	}

	public function editPool(Pool $pool)
	{
		$this->sendRequest(
			[
				'methodName' => 'pool.edit',
				'parameters' => array_merge(
					['id' => $pool->getId()],
					$pool->toArray()
				)
			]
		);

		return $this->result;
	}

	public function createTask(Task $task)
	{
		$this->sendRequest(
			[
				'methodName' => 'task.create',
				'parameters' => $task->toArray()

			]
		);

		return $this->result;
	}

	public function createTaskSuite(TaskSuite $taskSuite)
	{
		$this->sendRequest(
			[
				'methodName' => 'task-suites.create',
				'parameters' => $taskSuite->toArray()

			]
		);

		return $this->result;
	}

	public function getOperations()
	{
		$this->sendRequest(
			[
				'methodName' => 'operations.list'
			]
		);

		return $this->result;
	}

	public function createTasks(array $taskList)
	{
		$tasks = [];
		foreach ($taskList as $item)
		{
			$tasks[] = $item->toArray();
		}

		$this->sendRequest(
			[
				'methodName' => 'task.create',
				'parameters' => $tasks
			]
		);

		return $this->result;
	}

	public function stopTaskSuite(string $suiteId)
	{
		$this->sendRequest(
			[
				'methodName' => 'task-suites.stop',
				'parameters' => [
					'id' => $suiteId,
					'overlap' => 0
				]
			]
		);

		return $this->result;
	}

	public function deleteTasks(int $poolId)
	{
		$this->sendRequest(
			[
				'methodName' => 'task.delete',
				'parameters' => [
					'id' => $poolId
				]
			]
		);

		return $this->result;
	}

	public function openPool($poolId)
	{
		$this->sendRequest(
			[
				'methodName' => 'pool.open',
				'id'         => $poolId
			]
		);

		return $this->result;
	}

	public function closePool($poolId)
	{
		$this->sendRequest(
			[
				'methodName' => 'pool.close',
				'parameters' => [
					'id'         => $poolId
				]
			]
		);

		return $this->result;
	}

	function getScope(): string
	{
		return 'seo.client.toloka.';
	}
}