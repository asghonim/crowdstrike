<?php

namespace Zinad\Crowdstrike\Resources;

class Spotlight extends Resource
{
    /**
     * Search vulnerabilities and return full details.
     *
     * @param string      $filter  FQL filter string (required by API)
     * @param string|null $sort    Sort expression
     * @param int|null    $limit   Max results (max 400)
     * @param string|null $after   Cursor for keyset pagination
     * @param string[]    $facet   Facets to include (e.g. ["cve", "host_info"])
     */
    public function searchVulnerabilities(
        string $filter,
        ?string $sort = null,
        ?int $limit = null,
        ?string $after = null,
        array $facet = [],
    ): array {
        return $this->httpGet('/spotlight/combined/vulnerabilities/v1', [
            'filter' => $filter,
            'sort' => $sort,
            'limit' => $limit,
            'after' => $after,
            'facet' => $facet ?: null,
        ]);
    }

    /**
     * Retrieve vulnerability details by IDs.
     *
     * @param string[] $ids Vulnerability IDs
     */
    public function getVulnerabilitiesByIds(array $ids): array
    {
        return $this->httpGet('/spotlight/entities/vulnerabilities/v2', ['ids' => $ids]);
    }

    /**
     * Query vulnerability IDs matching a FQL filter.
     *
     * @param string      $filter  FQL filter string (required by API)
     * @param string|null $sort    Sort expression
     * @param int|null    $limit   Max results (max 400)
     * @param string|null $after   Cursor for keyset pagination
     */
    public function queryVulnerabilities(
        string $filter,
        ?string $sort = null,
        ?int $limit = null,
        ?string $after = null,
    ): array {
        return $this->httpGet('/spotlight/queries/vulnerabilities/v1', [
            'filter' => $filter,
            'sort' => $sort,
            'limit' => $limit,
            'after' => $after,
        ]);
    }

    /**
     * Retrieve evaluation logic details by IDs.
     *
     * @param string[] $ids Evaluation logic IDs
     */
    public function getEvaluationLogicByIds(array $ids): array
    {
        return $this->httpGet('/spotlight/entities/evaluation-logic/v1', ['ids' => $ids]);
    }

    /**
     * Search evaluation logic records.
     *
     * @param string      $filter  FQL filter string
     * @param string|null $sort    Sort expression
     * @param int|null    $limit   Max results
     * @param string|null $after   Cursor for keyset pagination
     */
    public function searchEvaluationLogic(
        string $filter,
        ?string $sort = null,
        ?int $limit = null,
        ?string $after = null,
    ): array {
        return $this->httpGet('/spotlight/combined/evaluation-logic/v1', [
            'filter' => $filter,
            'sort' => $sort,
            'limit' => $limit,
            'after' => $after,
        ]);
    }

    /**
     * Retrieve remediation details by IDs.
     *
     * @param string[] $ids Remediation IDs
     */
    public function getRemediationsByIds(array $ids): array
    {
        return $this->httpGet('/spotlight/entities/remediations/v2', ['ids' => $ids]);
    }
}
