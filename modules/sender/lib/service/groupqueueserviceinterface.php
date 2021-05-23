<?php

namespace Bitrix\Sender\Service;

interface GroupQueueServiceInterface
{
    public function addToDB(int $type, int $entityId, int $groupId);
    public function releaseGroup(int $type, int $entityId, int $groupId);

    public function isReleased(int $groupId): bool;
}