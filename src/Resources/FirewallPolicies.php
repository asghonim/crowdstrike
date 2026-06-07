<?php

namespace Zinad\Crowdstrike\Resources;

class FirewallPolicies extends Resource
{
    /**
     * Query firewall policy IDs matching a FQL filter.
     */
    public function query(
        ?string $filter = null,
        ?string $sort = null,
        ?int $limit = null,
        ?int $offset = null,
    ): array {
        return $this->httpGet('/policy/queries/firewall/v1', [
            'filter' => $filter,
            'sort' => $sort,
            'limit' => $limit,
            'offset' => $offset,
        ]);
    }

    /**
     * List firewall policies with full details.
     */
    public function list(
        ?string $filter = null,
        ?string $sort = null,
        ?int $limit = null,
        ?int $offset = null,
    ): array {
        return $this->httpGet('/policy/combined/firewall/v1', [
            'filter' => $filter,
            'sort' => $sort,
            'limit' => $limit,
            'offset' => $offset,
        ]);
    }

    /**
     * Retrieve firewall policies by IDs.
     *
     * @param string[] $ids Policy IDs
     */
    public function getByIds(array $ids): array
    {
        return $this->httpGet('/policy/entities/firewall/v1', ['ids' => $ids]);
    }

    /**
     * Create a new firewall policy.
     *
     * @param array       $payload  Policy definition
     * @param string|null $cloneId  Clone from existing policy ID
     */
    public function create(array $payload, ?string $cloneId = null): array
    {
        return $this->httpPost('/policy/entities/firewall/v1', $payload, [
            'clone_id' => $cloneId,
        ]);
    }

    /**
     * Update a firewall policy.
     */
    public function update(array $payload): array
    {
        return $this->httpPatch('/policy/entities/firewall/v1', $payload);
    }

    /**
     * Delete firewall policies by IDs.
     *
     * @param string[] $ids Policy IDs
     */
    public function delete(array $ids): array
    {
        return $this->httpDelete('/policy/entities/firewall/v1', ['ids' => $ids]);
    }

    /**
     * Perform an action on a firewall policy.
     *
     * @param string   $actionName e.g. "enable", "disable", "add-host-group", "remove-host-group"
     * @param string[] $ids        Policy IDs
     * @param array    $params     Action-specific parameters
     */
    public function action(string $actionName, array $ids, array $params = []): array
    {
        $actionParameters = array_map(
            fn($k, $v) => ['name' => $k, 'value' => $v],
            array_keys($params),
            $params
        );

        return $this->httpPost('/policy/entities/firewall-actions/v1', [
            'action_parameters' => $actionParameters,
            'ids' => $ids,
        ], ['action_name' => $actionName]);
    }

    /**
     * Set firewall policy precedence order.
     *
     * @param string   $platformName e.g. "Windows", "Mac", "Linux"
     * @param string[] $ids          Policy IDs in precedence order
     */
    public function setPrecedence(string $platformName, array $ids): array
    {
        return $this->httpPost('/policy/entities/firewall-precedence/v1', [
            'platform_name' => $platformName,
            'ids' => $ids,
        ]);
    }
}
