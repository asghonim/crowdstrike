<?php

namespace Zinad\Crowdstrike\Resources;

class Devices extends Resource
{
    /**
     * Query device IDs matching a FQL filter.
     *
     * @param string|null $filter  FQL filter string (e.g. "platform_name:'Windows'")
     * @param string|null $sort    Sort expression
     * @param int|null    $limit   Max results
     * @param int|null    $offset  Pagination offset
     */
    public function query(
        ?string $filter = null,
        ?string $sort = null,
        ?int $limit = null,
        ?int $offset = null,
    ): array {
        return $this->httpGet('/devices/queries/devices/v1', [
            'filter' => $filter,
            'sort' => $sort,
            'limit' => $limit,
            'offset' => $offset,
        ]);
    }

    /**
     * Scroll through all device IDs (supports deep pagination via offset token).
     *
     * @param string|null $filter  FQL filter string
     * @param string|null $sort    Sort expression
     * @param int|null    $limit   Max results per page
     * @param string|null $offset  Scroll token from previous response
     */
    public function scroll(
        ?string $filter = null,
        ?string $sort = null,
        ?int $limit = null,
        ?string $offset = null,
    ): array {
        return $this->httpGet('/devices/queries/devices-scroll/v1', [
            'filter' => $filter,
            'sort' => $sort,
            'limit' => $limit,
            'offset' => $offset,
        ]);
    }

    /**
     * Retrieve device details by IDs.
     *
     * @param string[] $ids Device IDs (up to 100 per call)
     */
    public function getByIds(array $ids): array
    {
        return $this->httpPost('/devices/entities/devices/v2', ['ids' => $ids]);
    }

    /**
     * List devices with full detail (combined query + entity fetch).
     *
     * @param string|null $filter  FQL filter string
     * @param string|null $sort    Sort expression
     * @param int|null    $limit   Max results
     * @param string|null $offset  Pagination offset
     * @param string|null $fields  Comma-separated fields to include
     */
    public function list(
        ?string $filter = null,
        ?string $sort = null,
        ?int $limit = null,
        ?string $offset = null,
        ?string $fields = null,
    ): array {
        return $this->httpGet('/devices/combined/devices/v1', [
            'filter' => $filter,
            'sort' => $sort,
            'limit' => $limit,
            'offset' => $offset,
            'fields' => $fields,
        ]);
    }

    /**
     * Get the online state of one or more devices.
     *
     * @param string[] $ids Device IDs
     */
    public function getOnlineState(array $ids): array
    {
        return $this->httpGet('/devices/entities/online-state/v1', ['ids' => $ids]);
    }

    /**
     * Perform an action on one or more devices.
     *
     * @param string   $actionName  e.g. "contain", "lift_containment", "hide_host", "unhide_host"
     * @param string[] $ids         Device IDs
     * @param string   $comment     Optional audit comment
     */
    public function action(string $actionName, array $ids, string $comment = ''): array
    {
        $body = ['ids' => array_map(fn ($id) => ['id' => $id], $ids)];
        if ($comment !== '') {
            $body['comment'] = $comment;
        }

        return $this->httpPost('/devices/entities/devices-actions/v2', $body, [
            'action_name' => $actionName,
        ]);
    }

    /**
     * Add or remove tags on devices.
     *
     * @param string[] $ids    Device IDs
     * @param string[] $tags   Tags to add
     * @param string   $action "add" or "remove"
     */
    public function updateTags(array $ids, array $tags, string $action = 'add'): array
    {
        return $this->httpPatch('/devices/entities/devices/tags/v1', [
            'device_ids' => $ids,
            'tags' => $tags,
            'action' => $action,
        ]);
    }

    /**
     * Get device login history.
     *
     * @param string[] $ids Device IDs
     */
    public function getLoginHistory(array $ids): array
    {
        return $this->httpPost('/devices/combined/devices/login-history/v1', ['ids' => $ids]);
    }

    /**
     * Get device network address history.
     *
     * @param string[] $ids Device IDs
     */
    public function getNetworkAddressHistory(array $ids): array
    {
        return $this->httpPost('/devices/combined/devices/network-address-history/v1', ['ids' => $ids]);
    }
}
