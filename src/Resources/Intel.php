<?php

namespace Zinad\Crowdstrike\Resources;

class Intel extends Resource
{
    // -------------------------------------------------------------------------
    // Threat Actors
    // -------------------------------------------------------------------------

    /**
     * Search threat actors and return full details.
     *
     * @param string|null   $filter  FQL filter string
     * @param string|null   $sort    Sort expression
     * @param int|null      $limit   Max results
     * @param int|null      $offset  Pagination offset
     * @param string|null   $q       Full-text search
     * @param string[]      $fields  Fields to include
     */
    public function searchActors(
        ?string $filter = null,
        ?string $sort = null,
        ?int $limit = null,
        ?int $offset = null,
        ?string $q = null,
        array $fields = [],
    ): array {
        return $this->httpGet('/intel/combined/actors/v1', [
            'filter' => $filter,
            'sort' => $sort,
            'limit' => $limit,
            'offset' => $offset,
            'q' => $q,
            'fields' => $fields ?: null,
        ]);
    }

    /**
     * Retrieve threat actor details by IDs.
     *
     * @param string[] $ids    Actor IDs
     * @param string[] $fields Fields to include
     */
    public function getActorsByIds(array $ids, array $fields = []): array
    {
        return $this->httpGet('/intel/entities/actors/v1', [
            'ids' => $ids,
            'fields' => $fields ?: null,
        ]);
    }

    /**
     * Query threat actor IDs matching a FQL filter.
     *
     * @param string|null $filter  FQL filter string
     * @param string|null $sort    Sort expression
     * @param int|null    $limit   Max results
     * @param int|null    $offset  Pagination offset
     * @param string|null $q       Full-text search
     */
    public function queryActors(
        ?string $filter = null,
        ?string $sort = null,
        ?int $limit = null,
        ?int $offset = null,
        ?string $q = null,
    ): array {
        return $this->httpGet('/intel/queries/actors/v1', [
            'filter' => $filter,
            'sort' => $sort,
            'limit' => $limit,
            'offset' => $offset,
            'q' => $q,
        ]);
    }

    // -------------------------------------------------------------------------
    // Indicators
    // -------------------------------------------------------------------------

    /**
     * Search intel indicators and return full details.
     *
     * @param string|null $filter           FQL filter string
     * @param string|null $sort             Sort expression
     * @param int|null    $limit            Max results
     * @param int|null    $offset           Pagination offset
     * @param string|null $q                Full-text search
     * @param bool        $includeDeleted   Include deleted indicators
     * @param bool        $includeRelations Include related entities
     */
    public function searchIndicators(
        ?string $filter = null,
        ?string $sort = null,
        ?int $limit = null,
        ?int $offset = null,
        ?string $q = null,
        bool $includeDeleted = false,
        bool $includeRelations = true,
    ): array {
        return $this->httpGet('/intel/combined/indicators/v1', [
            'filter' => $filter,
            'sort' => $sort,
            'limit' => $limit,
            'offset' => $offset,
            'q' => $q,
            'include_deleted' => $includeDeleted ? 'true' : 'false',
            'include_relations' => $includeRelations ? 'true' : 'false',
        ]);
    }

    /**
     * Retrieve intel indicator details by IDs (POST).
     *
     * @param string[] $ids Indicator IDs
     */
    public function getIndicatorsByIds(array $ids): array
    {
        return $this->httpPost('/intel/entities/indicators/GET/v1', ['ids' => $ids]);
    }

    /**
     * Query intel indicator IDs matching a FQL filter.
     *
     * @param string|null $filter           FQL filter string
     * @param string|null $sort             Sort expression
     * @param int|null    $limit            Max results
     * @param int|null    $offset           Pagination offset
     * @param string|null $q                Full-text search
     * @param bool        $includeDeleted   Include deleted indicators
     * @param bool        $includeRelations Include related entities
     */
    public function queryIndicators(
        ?string $filter = null,
        ?string $sort = null,
        ?int $limit = null,
        ?int $offset = null,
        ?string $q = null,
        bool $includeDeleted = false,
        bool $includeRelations = true,
    ): array {
        return $this->httpGet('/intel/queries/indicators/v1', [
            'filter' => $filter,
            'sort' => $sort,
            'limit' => $limit,
            'offset' => $offset,
            'q' => $q,
            'include_deleted' => $includeDeleted ? 'true' : 'false',
            'include_relations' => $includeRelations ? 'true' : 'false',
        ]);
    }

    // -------------------------------------------------------------------------
    // Reports
    // -------------------------------------------------------------------------

    /**
     * Search intel reports and return full details.
     *
     * @param string|null $filter  FQL filter string
     * @param string|null $sort    Sort expression
     * @param int|null    $limit   Max results
     * @param int|null    $offset  Pagination offset
     * @param string|null $q       Full-text search
     * @param string[]    $fields  Fields to include
     */
    public function searchReports(
        ?string $filter = null,
        ?string $sort = null,
        ?int $limit = null,
        ?int $offset = null,
        ?string $q = null,
        array $fields = [],
    ): array {
        return $this->httpGet('/intel/combined/reports/v1', [
            'filter' => $filter,
            'sort' => $sort,
            'limit' => $limit,
            'offset' => $offset,
            'q' => $q,
            'fields' => $fields ?: null,
        ]);
    }

    /**
     * Retrieve intel report details by IDs.
     *
     * @param string[] $ids    Report IDs
     * @param string[] $fields Fields to include
     */
    public function getReportsByIds(array $ids, array $fields = []): array
    {
        return $this->httpGet('/intel/entities/reports/v1', [
            'ids' => $ids,
            'fields' => $fields ?: null,
        ]);
    }

    /**
     * Query intel report IDs matching a FQL filter.
     *
     * @param string|null $filter  FQL filter string
     * @param string|null $sort    Sort expression
     * @param int|null    $limit   Max results
     * @param int|null    $offset  Pagination offset
     * @param string|null $q       Full-text search
     */
    public function queryReports(
        ?string $filter = null,
        ?string $sort = null,
        ?int $limit = null,
        ?int $offset = null,
        ?string $q = null,
    ): array {
        return $this->httpGet('/intel/queries/reports/v1', [
            'filter' => $filter,
            'sort' => $sort,
            'limit' => $limit,
            'offset' => $offset,
            'q' => $q,
        ]);
    }

    // -------------------------------------------------------------------------
    // Malware
    // -------------------------------------------------------------------------

    /**
     * Search malware families and return full details.
     *
     * @param string|null $filter  FQL filter string
     * @param string|null $sort    Sort expression
     * @param int|null    $limit   Max results
     * @param int|null    $offset  Pagination offset
     * @param string|null $q       Full-text search
     * @param string[]    $fields  Fields to include
     */
    public function searchMalware(
        ?string $filter = null,
        ?string $sort = null,
        ?int $limit = null,
        ?int $offset = null,
        ?string $q = null,
        array $fields = [],
    ): array {
        return $this->httpGet('/intel/combined/malware/v1', [
            'filter' => $filter,
            'sort' => $sort,
            'limit' => $limit,
            'offset' => $offset,
            'q' => $q,
            'fields' => $fields ?: null,
        ]);
    }

    /**
     * Retrieve malware details by IDs.
     *
     * @param string[] $ids Malware IDs
     */
    public function getMalwareByIds(array $ids): array
    {
        return $this->httpGet('/intel/entities/malware/v1', ['ids' => $ids]);
    }

    /**
     * Query malware IDs matching a FQL filter.
     *
     * @param string|null $filter  FQL filter string
     * @param string|null $sort    Sort expression
     * @param int|null    $limit   Max results
     * @param int|null    $offset  Pagination offset
     * @param string|null $q       Full-text search
     */
    public function queryMalware(
        ?string $filter = null,
        ?string $sort = null,
        ?int $limit = null,
        ?int $offset = null,
        ?string $q = null,
    ): array {
        return $this->httpGet('/intel/queries/malware/v1', [
            'filter' => $filter,
            'sort' => $sort,
            'limit' => $limit,
            'offset' => $offset,
            'q' => $q,
        ]);
    }

    // -------------------------------------------------------------------------
    // Vulnerabilities
    // -------------------------------------------------------------------------

    /**
     * Retrieve vulnerability details by IDs.
     *
     * @param string[] $ids Vulnerability IDs
     */
    public function getVulnerabilitiesByIds(array $ids): array
    {
        return $this->httpPost('/intel/entities/vulnerabilities/GET/v1', ['ids' => $ids]);
    }

    /**
     * Query vulnerability IDs matching a FQL filter.
     *
     * @param string|null $filter  FQL filter string
     * @param string|null $sort    Sort expression
     * @param int|null    $limit   Max results
     * @param string|null $offset  Pagination offset
     * @param string|null $q       Full-text search
     */
    public function queryVulnerabilities(
        ?string $filter = null,
        ?string $sort = null,
        ?int $limit = null,
        ?string $offset = null,
        ?string $q = null,
    ): array {
        return $this->httpGet('/intel/queries/vulnerabilities/v1', [
            'filter' => $filter,
            'sort' => $sort,
            'limit' => $limit,
            'offset' => $offset,
            'q' => $q,
        ]);
    }
}
