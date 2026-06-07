<?php

namespace Zinad\Crowdstrike\Resources;

class HostGroups extends Resource
{
    /**
     * Query host group IDs matching a FQL filter.
     *
     * @param string|null $filter  FQL filter string
     * @param string|null $sort    Sort expression (e.g. "created_by.desc")
     * @param int|null    $limit   Max results
     * @param int|null    $offset  Pagination offset
     */
    public function query(
        ?string $filter = null,
        ?string $sort = null,
        ?int $limit = null,
        ?int $offset = null,
    ): array {
        return $this->httpGet('/devices/queries/host-groups/v1', [
            'filter' => $filter,
            'sort' => $sort,
            'limit' => $limit,
            'offset' => $offset,
        ]);
    }

    /**
     * List host groups with full details.
     *
     * @param string|null $filter  FQL filter string
     * @param string|null $sort    Sort expression
     * @param int|null    $limit   Max results
     * @param int|null    $offset  Pagination offset
     */
    public function list(
        ?string $filter = null,
        ?string $sort = null,
        ?int $limit = null,
        ?int $offset = null,
    ): array {
        return $this->httpGet('/devices/combined/host-groups/v1', [
            'filter' => $filter,
            'sort' => $sort,
            'limit' => $limit,
            'offset' => $offset,
        ]);
    }

    /**
     * Retrieve host groups by IDs.
     *
     * @param string[] $ids Host group IDs
     */
    public function getByIds(array $ids): array
    {
        return $this->httpGet('/devices/entities/host-groups/v1', ['ids' => $ids]);
    }

    /**
     * Create a new host group.
     *
     * @param array $payload Group definition (name, group_type, description, assignment_rule)
     */
    public function create(array $payload): array
    {
        return $this->httpPost('/devices/entities/host-groups/v1', $payload);
    }

    /**
     * Update a host group.
     *
     * @param array $payload Must include 'id' and fields to update
     */
    public function update(array $payload): array
    {
        return $this->httpPatch('/devices/entities/host-groups/v1', $payload);
    }

    /**
     * Delete host groups by IDs.
     *
     * @param string[] $ids Host group IDs to delete
     */
    public function delete(array $ids): array
    {
        return $this->httpDelete('/devices/entities/host-groups/v1', ['ids' => $ids]);
    }

    /**
     * Add or remove hosts from a host group.
     *
     * @param string   $actionName  "add-hosts" or "remove-hosts"
     * @param string   $groupId     Host group ID
     * @param string[] $deviceIds   Device IDs
     */
    public function action(string $actionName, string $groupId, array $deviceIds): array
    {
        return $this->httpPost(
            '/devices/entities/host-group-actions/v1',
            [
                'id' => $groupId,
                'action_parameters' => [['name' => 'filter', 'value' => implode(',', $deviceIds)]],
            ],
            ['action_name' => $actionName]
        );
    }

    /**
     * Query members of a host group.
     *
     * @param string      $groupId  Host group ID
     * @param string|null $filter   FQL filter string
     * @param string|null $sort     Sort expression
     * @param int|null    $limit    Max results
     * @param int|null    $offset   Pagination offset
     */
    public function queryMembers(
        string $groupId,
        ?string $filter = null,
        ?string $sort = null,
        ?int $limit = null,
        ?int $offset = null,
    ): array {
        return $this->httpGet('/devices/queries/host-group-members/v1', [
            'id' => $groupId,
            'filter' => $filter,
            'sort' => $sort,
            'limit' => $limit,
            'offset' => $offset,
        ]);
    }

    /**
     * List members of a host group with full device details.
     *
     * @param string      $groupId  Host group ID
     * @param string|null $filter   FQL filter string
     * @param string|null $sort     Sort expression
     * @param int|null    $limit    Max results
     * @param int|null    $offset   Pagination offset
     */
    public function listMembers(
        string $groupId,
        ?string $filter = null,
        ?string $sort = null,
        ?int $limit = null,
        ?int $offset = null,
    ): array {
        return $this->httpGet('/devices/combined/host-group-members/v1', [
            'id' => $groupId,
            'filter' => $filter,
            'sort' => $sort,
            'limit' => $limit,
            'offset' => $offset,
        ]);
    }
}
