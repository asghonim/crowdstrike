<?php

namespace Zinad\Crowdstrike\Resources;

class UserManagement extends Resource
{
    /**
     * Query user UUIDs matching a FQL filter.
     *
     * @param string|null $filter  FQL filter string
     * @param string|null $sort    Sort expression (e.g. "uid|asc")
     * @param int|null    $limit   Max results
     * @param int|null    $offset  Pagination offset
     */
    public function query(
        ?string $filter = null,
        ?string $sort = null,
        ?int $limit = null,
        ?int $offset = null,
    ): array {
        return $this->httpGet('/user-management/queries/users/v1', [
            'filter' => $filter,
            'sort' => $sort,
            'limit' => $limit,
            'offset' => $offset,
        ]);
    }

    /**
     * Retrieve user details by UUIDs.
     *
     * @param string[] $uuids User UUIDs
     */
    public function getByIds(array $uuids): array
    {
        return $this->httpPost('/user-management/entities/users/GET/v1', ['ids' => $uuids]);
    }

    /**
     * Create a new user.
     *
     * @param array $payload User details (uid/email, first_name, last_name, password)
     * @param bool  $validateOnly Validate without creating
     */
    public function create(array $payload, bool $validateOnly = false): array
    {
        return $this->httpPost('/user-management/entities/users/v1', $payload, [
            'validate_only' => $validateOnly ? 'true' : 'false',
        ]);
    }

    /**
     * Update a user.
     *
     * @param string $userUuid  User UUID to update
     * @param array  $payload   Fields to update (first_name, last_name)
     */
    public function update(string $userUuid, array $payload): array
    {
        return $this->httpPatch('/user-management/entities/users/v1', $payload, [
            'user_uuid' => $userUuid,
        ]);
    }

    /**
     * Delete a user.
     *
     * @param string $userUuid User UUID to delete
     */
    public function delete(string $userUuid): array
    {
        return $this->httpDelete('/user-management/entities/users/v1', [
            'user_uuid' => $userUuid,
        ]);
    }

    /**
     * Perform an action on a user (enable, disable, reset_2fa, reset_password).
     *
     * @param string $actionName e.g. "enable", "disable", "reset_2fa", "reset_password"
     * @param array  $payload    Action payload
     */
    public function action(string $actionName, array $payload): array
    {
        return $this->httpPost('/user-management/entities/user-actions/v1', array_merge(
            ['action_name' => $actionName],
            $payload
        ));
    }

    /**
     * List roles assigned to a user.
     *
     * @param string      $userUuid   User UUID
     * @param string|null $cid        CID (customer ID) scope
     * @param bool        $directOnly Return only directly assigned roles
     * @param string|null $filter     FQL filter string
     * @param int|null    $offset     Pagination offset
     * @param int|null    $limit      Max results
     * @param string|null $sort       Sort expression
     */
    public function getUserRoles(
        string $userUuid,
        ?string $cid = null,
        bool $directOnly = false,
        ?string $filter = null,
        ?int $offset = null,
        ?int $limit = null,
        ?string $sort = null,
    ): array {
        return $this->httpGet('/user-management/combined/user-roles/v1', [
            'user_uuid' => $userUuid,
            'cid' => $cid,
            'direct_only' => $directOnly ? 'true' : 'false',
            'filter' => $filter,
            'offset' => $offset,
            'limit' => $limit,
            'sort' => $sort,
        ]);
    }

    /**
     * Grant or revoke roles for a user.
     *
     * @param string   $actionName "grant" or "revoke"
     * @param string   $userUuid   User UUID
     * @param string[] $roleIds    Role IDs
     * @param string|null $cid     CID scope
     */
    public function roleAction(
        string $actionName,
        string $userUuid,
        array $roleIds,
        ?string $cid = null,
    ): array {
        return $this->httpPost('/user-management/entities/user-role-actions/v1', array_filter([
            'action' => $actionName,
            'cid' => $cid,
            'role_ids' => $roleIds,
            'user_uuid' => $userUuid,
        ]));
    }

    /**
     * Query available roles.
     *
     * @param string|null $cid        CID scope
     * @param string|null $userUuid   Filter by user UUID
     * @param string|null $action     Filter by action ("grant" or "revoke")
     */
    public function queryRoles(
        ?string $cid = null,
        ?string $userUuid = null,
        ?string $action = null,
    ): array {
        return $this->httpGet('/user-management/queries/roles/v1', [
            'cid' => $cid,
            'user_uuid' => $userUuid,
            'action' => $action,
        ]);
    }

    /**
     * Retrieve role details by IDs.
     *
     * @param string[]    $ids Role IDs
     * @param string|null $cid CID scope
     */
    public function getRolesByIds(array $ids, ?string $cid = null): array
    {
        return $this->httpGet('/user-management/entities/roles/v1', [
            'ids' => $ids,
            'cid' => $cid,
        ]);
    }

    /**
     * Retrieve aggregate metrics about users.
     *
     * @param array $aggregates Array of aggregate query objects
     */
    public function aggregate(array $aggregates): array
    {
        return $this->httpPost('/user-management/aggregates/users/v1', $aggregates);
    }
}
