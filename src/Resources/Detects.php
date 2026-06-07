<?php

namespace Zinad\Crowdstrike\Resources;

class Detects extends Resource
{
    /**
     * Query detection IDs matching a FQL filter.
     *
     * @param string|null $filter  FQL filter string
     * @param string|null $sort    e.g. "first_behavior|desc"
     * @param int|null    $limit   Max results
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
        return $this->httpGet('/detects/queries/detects/v1', [
            'filter' => $filter,
            'sort' => $sort,
            'limit' => $limit,
            'offset' => $offset,
            'q' => $q,
        ]);
    }

    /**
     * Retrieve detection summaries by IDs.
     *
     * @param string[] $ids Detection IDs
     */
    public function getByIds(array $ids): array
    {
        return $this->httpPost('/detects/entities/summaries/GET/v1', ['ids' => $ids]);
    }

    /**
     * Update one or more detections.
     *
     * @param array $payload Must include 'ids' and at least one field to update
     *                       e.g. ['ids' => [...], 'status' => 'true_positive', 'assigned_to_uuid' => '...']
     */
    public function update(array $payload): array
    {
        return $this->httpPatch('/detects/entities/detects/v2', $payload);
    }

    /**
     * Retrieve aggregate metric values for detections.
     *
     * @param array $aggregates Array of aggregate query objects
     */
    public function aggregate(array $aggregates): array
    {
        return $this->httpPost('/detects/aggregates/detects/GET/v1', $aggregates);
    }
}
