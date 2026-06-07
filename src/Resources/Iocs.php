<?php

namespace Zinad\Crowdstrike\Resources;

class Iocs extends Resource
{
    /**
     * Query IOC indicator IDs matching a FQL filter.
     *
     * @param string|null $filter     FQL filter string
     * @param string|null $sort       Sort expression (e.g. "metadata.product_version")
     * @param int|null    $limit      Max results
     * @param int|null    $offset     Pagination offset
     * @param string|null $after      Cursor for keyset pagination
     * @param bool|null   $fromParent Include parent CID indicators
     */
    public function query(
        ?string $filter = null,
        ?string $sort = null,
        ?int $limit = null,
        ?int $offset = null,
        ?string $after = null,
        ?bool $fromParent = null,
    ): array {
        return $this->httpGet('/iocs/queries/indicators/v1', [
            'filter' => $filter,
            'sort' => $sort,
            'limit' => $limit,
            'offset' => $offset,
            'after' => $after,
            'from_parent' => $fromParent !== null ? ($fromParent ? 'true' : 'false') : null,
        ]);
    }

    /**
     * Search IOC indicators and return full details (combined).
     *
     * @param string|null $filter     FQL filter string
     * @param string|null $sort       Sort expression
     * @param int|null    $limit      Max results
     * @param int|null    $offset     Pagination offset
     * @param string|null $after      Cursor for keyset pagination
     * @param bool|null   $fromParent Include parent CID indicators
     */
    public function search(
        ?string $filter = null,
        ?string $sort = null,
        ?int $limit = null,
        ?int $offset = null,
        ?string $after = null,
        ?bool $fromParent = null,
    ): array {
        return $this->httpGet('/iocs/combined/indicator/v1', [
            'filter' => $filter,
            'sort' => $sort,
            'limit' => $limit,
            'offset' => $offset,
            'after' => $after,
            'from_parent' => $fromParent !== null ? ($fromParent ? 'true' : 'false') : null,
        ]);
    }

    /**
     * Retrieve IOC indicators by IDs.
     *
     * @param string[] $ids Indicator IDs
     */
    public function getByIds(array $ids): array
    {
        return $this->httpGet('/iocs/entities/indicators/v1', ['ids' => $ids]);
    }

    /**
     * Create new IOC indicators.
     *
     * @param array $indicators  Array of indicator objects
     * @param bool  $retrodetects Enable retroactive detection
     * @param bool  $ignoreWarnings Skip validation warnings
     */
    public function create(
        array $indicators,
        bool $retrodetects = false,
        bool $ignoreWarnings = false,
    ): array {
        return $this->httpPost(
            '/iocs/entities/indicators/v1',
            ['indicators' => $indicators],
            [
                'retrodetects' => $retrodetects ? 'true' : 'false',
                'ignore_warnings' => $ignoreWarnings ? 'true' : 'false',
            ]
        );
    }

    /**
     * Update existing IOC indicators.
     *
     * @param array $indicators  Array of indicator update objects (must include 'id')
     * @param bool  $retrodetects Enable retroactive detection
     * @param bool  $ignoreWarnings Skip validation warnings
     */
    public function update(
        array $indicators,
        bool $retrodetects = false,
        bool $ignoreWarnings = false,
    ): array {
        return $this->httpPatch(
            '/iocs/entities/indicators/v1',
            ['indicators' => $indicators],
            [
                'retrodetects' => $retrodetects ? 'true' : 'false',
                'ignore_warnings' => $ignoreWarnings ? 'true' : 'false',
            ]
        );
    }

    /**
     * Delete IOC indicators.
     *
     * @param string[]    $ids     Indicator IDs to delete
     * @param string|null $filter  FQL filter (alternative to IDs)
     * @param string|null $comment Audit comment
     * @param bool|null   $fromParent Target parent CID
     */
    public function delete(
        array $ids = [],
        ?string $filter = null,
        ?string $comment = null,
        ?bool $fromParent = null,
    ): array {
        return $this->httpDelete('/iocs/entities/indicators/v1', array_filter([
            'ids' => $ids ?: null,
            'filter' => $filter,
            'comment' => $comment,
            'from_parent' => $fromParent !== null ? ($fromParent ? 'true' : 'false') : null,
        ]));
    }

    /**
     * Get the number of devices an IOC has affected.
     *
     * @param string $type  Indicator type (e.g. "sha256", "domain", "ipv4")
     * @param string $value Indicator value
     */
    public function getDeviceCount(string $type, string $value): array
    {
        return $this->httpGet('/iocs/aggregates/indicators/device-count/v1', [
            'type' => $type,
            'value' => $value,
        ]);
    }

    /**
     * Query devices that have observed a specific IOC.
     *
     * @param string      $type   Indicator type
     * @param string      $value  Indicator value
     * @param int|null    $limit  Max results
     * @param string|null $offset Pagination offset
     */
    public function queryDevices(
        string $type,
        string $value,
        ?int $limit = null,
        ?string $offset = null,
    ): array {
        return $this->httpGet('/iocs/queries/indicators/devices/v1', [
            'type' => $type,
            'value' => $value,
            'limit' => $limit,
            'offset' => $offset,
        ]);
    }

    /**
     * Query processes that have run a specific IOC on a device.
     *
     * @param string      $type      Indicator type
     * @param string      $value     Indicator value
     * @param string      $deviceId  Device ID
     * @param int|null    $limit     Max results
     * @param string|null $offset    Pagination offset
     */
    public function queryProcesses(
        string $type,
        string $value,
        string $deviceId,
        ?int $limit = null,
        ?string $offset = null,
    ): array {
        return $this->httpGet('/iocs/queries/indicators/processes/v1', [
            'type' => $type,
            'value' => $value,
            'device_id' => $deviceId,
            'limit' => $limit,
            'offset' => $offset,
        ]);
    }
}
