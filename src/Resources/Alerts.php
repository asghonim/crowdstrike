<?php

namespace Zinad\Crowdstrike\Resources;

class Alerts extends Resource
{
    /**
     * Query alert IDs matching a FQL filter.
     *
     * @param string|null $filter  FQL filter string
     * @param string|null $sort    e.g. "created_timestamp|desc"
     * @param int|null    $limit   Max results (default 100)
     * @param int|null    $offset  Pagination offset
     * @param string|null $q       Full-text search
     */
    public function query(
        ?string $filter = null,
        ?string $sort = null,
        ?int $limit = null,
        ?int $offset = null,
        ?string $q = null,
    ): array {
        return $this->httpGet('/alerts/queries/alerts/v2', [
            'filter' => $filter,
            'sort' => $sort,
            'limit' => $limit,
            'offset' => $offset,
            'q' => $q,
        ]);
    }

    /**
     * Retrieve alert details by IDs.
     *
     * @param string[] $ids Alert IDs
     */
    public function getByIds(array $ids): array
    {
        return $this->httpPost('/alerts/entities/alerts/v2', ['ids' => $ids]);
    }

    /**
     * Search alerts and return full details in one call (combined).
     *
     * @param string|null $filter  FQL filter string
     * @param string|null $sort    Sort expression
     * @param int|null    $limit   Max results
     * @param int|null    $offset  Pagination offset
     * @param string|null $q       Full-text search
     */
    public function search(
        ?string $filter = null,
        ?string $sort = null,
        ?int $limit = null,
        ?int $offset = null,
        ?string $q = null,
    ): array {
        return $this->httpPost('/alerts/combined/alerts/v1', array_filter([
            'filter' => $filter,
            'sort' => $sort,
            'limit' => $limit,
            'offset' => $offset,
            'q' => $q,
        ], fn ($v) => $v !== null));
    }

    /**
     * Update one or more alerts (status, assigned_to, etc.).
     *
     * @param array $ids     Alert IDs to update
     * @param array $payload Fields to update (e.g. ['status' => 'closed'])
     */
    public function update(array $ids, array $payload): array
    {
        return $this->httpPatch('/alerts/entities/alerts/v3', array_merge(['ids' => $ids], $payload));
    }

    /**
     * Retrieve aggregate metric values for alerts.
     *
     * @param array $aggregates Array of aggregate query objects
     */
    public function aggregate(array $aggregates): array
    {
        return $this->httpPost('/alerts/aggregates/alerts/v2', $aggregates);
    }
}
