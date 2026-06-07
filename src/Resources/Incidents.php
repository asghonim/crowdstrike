<?php

namespace Zinad\Crowdstrike\Resources;

class Incidents extends Resource
{
    /**
     * Query incident IDs matching a FQL filter.
     *
     * @param string|null $filter  FQL filter string (e.g. "status:20")
     * @param string|null $sort    Sort expression (e.g. "status.desc")
     * @param int|null    $limit   Max results
     * @param int|null    $offset  Pagination offset
     */
    public function query(
        ?string $filter = null,
        ?string $sort = null,
        ?int $limit = null,
        ?int $offset = null,
    ): array {
        return $this->httpGet('/incidents/queries/incidents/v1', [
            'filter' => $filter,
            'sort' => $sort,
            'limit' => $limit,
            'offset' => $offset,
        ]);
    }

    /**
     * Retrieve incident details by IDs.
     *
     * @param string[] $ids Incident IDs
     */
    public function getByIds(array $ids): array
    {
        return $this->httpPost('/incidents/entities/incidents/GET/v1', ['ids' => $ids]);
    }

    /**
     * Perform an action on one or more incidents.
     *
     * @param string   $actionName      e.g. "update_status", "update_name", "update_description"
     * @param string[] $ids             Incident IDs
     * @param array    $actionParams    Action-specific parameters
     * @param bool     $updateDetects   Update associated detections
     * @param bool     $overwriteDetects Overwrite detection fields
     */
    public function action(
        string $actionName,
        array $ids,
        array $actionParams = [],
        bool $updateDetects = false,
        bool $overwriteDetects = false,
    ): array {
        return $this->httpPost(
            '/incidents/entities/incident-actions/v1',
            [
                'action_parameters' => array_map(
                    fn($k, $v) => ['name' => $k, 'value' => $v],
                    array_keys($actionParams),
                    $actionParams
                ),
                'ids' => $ids,
            ],
            [
                'update_detects' => $updateDetects ? 'true' : 'false',
                'overwrite_detects' => $overwriteDetects ? 'true' : 'false',
            ]
        );
    }

    /**
     * Query behavior IDs associated with incidents.
     *
     * @param string|null $filter  FQL filter string
     * @param string|null $sort    Sort expression (e.g. "timestamp.asc")
     * @param int|null    $limit   Max results
     * @param int|null    $offset  Pagination offset
     */
    public function queryBehaviors(
        ?string $filter = null,
        ?string $sort = null,
        ?int $limit = null,
        ?int $offset = null,
    ): array {
        return $this->httpGet('/incidents/queries/behaviors/v1', [
            'filter' => $filter,
            'sort' => $sort,
            'limit' => $limit,
            'offset' => $offset,
        ]);
    }

    /**
     * Retrieve behavior details by IDs.
     *
     * @param string[] $ids Behavior IDs
     */
    public function getBehaviorsByIds(array $ids): array
    {
        return $this->httpPost('/incidents/entities/behaviors/GET/v1', ['ids' => $ids]);
    }

    /**
     * Get CrowdScore (crowdsourced security score) over time.
     *
     * @param string|null $filter  FQL filter string
     * @param string|null $sort    Sort expression (e.g. "score.desc")
     * @param int|null    $limit   Max results
     * @param int|null    $offset  Pagination offset
     */
    public function getCrowdScores(
        ?string $filter = null,
        ?string $sort = null,
        ?int $limit = null,
        ?int $offset = null,
    ): array {
        return $this->httpGet('/incidents/combined/crowdscores/v1', [
            'filter' => $filter,
            'sort' => $sort,
            'limit' => $limit,
            'offset' => $offset,
        ]);
    }
}
