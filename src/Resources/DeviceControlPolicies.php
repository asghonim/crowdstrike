<?php

namespace Zinad\Crowdstrike\Resources;

class DeviceControlPolicies extends Resource
{
    /**
     * Query device control policy IDs matching a FQL filter.
     */
    public function query(
        ?string $filter = null,
        ?string $sort = null,
        ?int $limit = null,
        ?int $offset = null,
    ): array {
        return $this->httpGet('/policy/queries/device-control/v1', [
            'filter' => $filter,
            'sort' => $sort,
            'limit' => $limit,
            'offset' => $offset,
        ]);
    }

    /**
     * List device control policies with full details.
     */
    public function list(
        ?string $filter = null,
        ?string $sort = null,
        ?int $limit = null,
        ?int $offset = null,
    ): array {
        return $this->httpGet('/policy/combined/device-control/v1', [
            'filter' => $filter,
            'sort' => $sort,
            'limit' => $limit,
            'offset' => $offset,
        ]);
    }

    /**
     * Retrieve device control policies by IDs.
     *
     * @param string[] $ids Policy IDs
     */
    public function getByIds(array $ids): array
    {
        return $this->httpGet('/policy/entities/device-control/v2', ['ids' => $ids]);
    }

    /**
     * Create a new device control policy.
     */
    public function create(array $payload): array
    {
        return $this->httpPost('/policy/entities/device-control/v2', $payload);
    }

    /**
     * Update a device control policy.
     */
    public function update(array $payload): array
    {
        return $this->httpPatch('/policy/entities/device-control/v2', $payload);
    }

    /**
     * Delete device control policies by IDs.
     *
     * @param string[] $ids Policy IDs
     */
    public function delete(array $ids): array
    {
        return $this->httpDelete('/policy/entities/device-control/v1', ['ids' => $ids]);
    }

    /**
     * Perform an action on a device control policy.
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

        return $this->httpPost('/policy/entities/device-control-actions/v1', [
            'action_parameters' => $actionParameters,
            'ids' => $ids,
        ], ['action_name' => $actionName]);
    }

    /**
     * Set device control policy precedence order.
     *
     * @param string   $platformName e.g. "Windows", "Mac", "Linux"
     * @param string[] $ids          Policy IDs in precedence order
     */
    public function setPrecedence(string $platformName, array $ids): array
    {
        return $this->httpPost('/policy/entities/device-control-precedence/v1', [
            'platform_name' => $platformName,
            'ids' => $ids,
        ]);
    }

    /**
     * Get the default device control policy.
     */
    public function getDefault(): array
    {
        return $this->httpGet('/policy/entities/default-device-control/v1');
    }
}
