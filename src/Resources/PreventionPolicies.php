<?php

namespace Zinad\Crowdstrike\Resources;

class PreventionPolicies extends Resource
{
    /**
     * Query prevention policy IDs matching a FQL filter.
     */
    public function query(
        ?string $filter = null,
        ?string $sort = null,
        ?int $limit = null,
        ?int $offset = null,
    ): array {
        return $this->httpGet('/policy/queries/prevention/v1', [
            'filter' => $filter,
            'sort' => $sort,
            'limit' => $limit,
            'offset' => $offset,
        ]);
    }

    /**
     * List prevention policies with full details.
     */
    public function list(
        ?string $filter = null,
        ?string $sort = null,
        ?int $limit = null,
        ?int $offset = null,
    ): array {
        return $this->httpGet('/policy/combined/prevention/v1', [
            'filter' => $filter,
            'sort' => $sort,
            'limit' => $limit,
            'offset' => $offset,
        ]);
    }

    /**
     * Retrieve prevention policies by IDs.
     *
     * @param string[] $ids Policy IDs
     */
    public function getByIds(array $ids): array
    {
        return $this->httpGet('/policy/entities/prevention/v1', ['ids' => $ids]);
    }

    /**
     * Create a new prevention policy.
     */
    public function create(array $payload): array
    {
        return $this->httpPost('/policy/entities/prevention/v1', $payload);
    }

    /**
     * Update a prevention policy.
     */
    public function update(array $payload): array
    {
        return $this->httpPatch('/policy/entities/prevention/v1', $payload);
    }

    /**
     * Delete prevention policies by IDs.
     *
     * @param string[] $ids Policy IDs
     */
    public function delete(array $ids): array
    {
        return $this->httpDelete('/policy/entities/prevention/v1', ['ids' => $ids]);
    }

    /**
     * Perform an action on a prevention policy (enable, disable, add-host-group, remove-host-group).
     *
     * @param string   $actionName e.g. "enable", "disable", "add-host-group", "remove-host-group"
     * @param string[] $ids        Policy IDs
     * @param array    $params     Action-specific parameters
     */
    public function action(string $actionName, array $ids, array $params = []): array
    {
        $actionParameters = array_map(
            fn ($k, $v) => ['name' => $k, 'value' => $v],
            array_keys($params),
            $params
        );

        return $this->httpPost('/policy/entities/prevention-actions/v1', [
            'action_parameters' => $actionParameters,
            'ids' => $ids,
        ], ['action_name' => $actionName]);
    }

    /**
     * Set prevention policy precedence order.
     *
     * @param string   $platformName e.g. "Windows", "Mac", "Linux"
     * @param string[] $ids          Policy IDs in precedence order
     */
    public function setPrecedence(string $platformName, array $ids): array
    {
        return $this->httpPost('/policy/entities/prevention-precedence/v1', [
            'platform_name' => $platformName,
            'ids' => $ids,
        ]);
    }

    /**
     * Query members of a prevention policy.
     */
    public function queryMembers(
        string $policyId,
        ?string $filter = null,
        ?string $sort = null,
        ?int $limit = null,
        ?int $offset = null,
    ): array {
        return $this->httpGet('/policy/queries/prevention-members/v1', [
            'id' => $policyId,
            'filter' => $filter,
            'sort' => $sort,
            'limit' => $limit,
            'offset' => $offset,
        ]);
    }
}
