<?php

namespace Zinad\Crowdstrike\Resources;

class SensorUpdatePolicies extends Resource
{
    /**
     * Query sensor update policy IDs matching a FQL filter.
     */
    public function query(
        ?string $filter = null,
        ?string $sort = null,
        ?int $limit = null,
        ?int $offset = null,
    ): array {
        return $this->httpGet('/policy/queries/sensor-update/v1', [
            'filter' => $filter,
            'sort' => $sort,
            'limit' => $limit,
            'offset' => $offset,
        ]);
    }

    /**
     * List sensor update policies with full details.
     */
    public function list(
        ?string $filter = null,
        ?string $sort = null,
        ?int $limit = null,
        ?int $offset = null,
    ): array {
        return $this->httpGet('/policy/combined/sensor-update/v2', [
            'filter' => $filter,
            'sort' => $sort,
            'limit' => $limit,
            'offset' => $offset,
        ]);
    }

    /**
     * Retrieve sensor update policies by IDs.
     *
     * @param string[] $ids Policy IDs
     */
    public function getByIds(array $ids): array
    {
        return $this->httpGet('/policy/entities/sensor-update/v2', ['ids' => $ids]);
    }

    /**
     * Create a new sensor update policy.
     */
    public function create(array $payload): array
    {
        return $this->httpPost('/policy/entities/sensor-update/v2', $payload);
    }

    /**
     * Update a sensor update policy.
     */
    public function update(array $payload): array
    {
        return $this->httpPatch('/policy/entities/sensor-update/v2', $payload);
    }

    /**
     * Delete sensor update policies by IDs.
     *
     * @param string[] $ids Policy IDs
     */
    public function delete(array $ids): array
    {
        return $this->httpDelete('/policy/entities/sensor-update/v1', ['ids' => $ids]);
    }

    /**
     * Perform an action on a sensor update policy.
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

        return $this->httpPost('/policy/entities/sensor-update-actions/v1', [
            'action_parameters' => $actionParameters,
            'ids' => $ids,
        ], ['action_name' => $actionName]);
    }

    /**
     * Get available sensor builds for a platform.
     *
     * @param string|null $platform e.g. "windows", "mac", "linux", "zlinux"
     * @param string|null $stage    e.g. "prod", "early_adopter"
     */
    public function getBuilds(?string $platform = null, ?string $stage = null): array
    {
        return $this->httpGet('/policy/combined/sensor-update-builds/v1', [
            'platform' => $platform,
            'stage' => $stage,
        ]);
    }

    /**
     * Get kernel compatibility information for sensor updates.
     */
    public function getKernels(?string $filter = null, ?int $offset = null, ?int $limit = null): array
    {
        return $this->httpGet('/policy/combined/sensor-update-kernels/v1', [
            'filter' => $filter,
            'offset' => $offset,
            'limit' => $limit,
        ]);
    }

    /**
     * Reveal the uninstall token for a device.
     *
     * @param string $deviceId    Device ID
     * @param string $auditMessage Audit message explaining why
     */
    public function revealUninstallToken(string $deviceId, string $auditMessage): array
    {
        return $this->httpPost('/policy/combined/reveal-uninstall-token/v1', [
            'device_id' => $deviceId,
            'audit_message' => $auditMessage,
        ]);
    }
}
