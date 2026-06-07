<?php

namespace Zinad\Crowdstrike\Resources;

class RealTimeResponse extends Resource
{
    // -------------------------------------------------------------------------
    // Single-host sessions
    // -------------------------------------------------------------------------

    /**
     * Initialize a Real Time Response session on a single host.
     *
     * @param string      $deviceId       Target device ID
     * @param string|null $origin         Session origin identifier
     * @param bool        $queueOffline   Queue commands if host is offline
     * @param int         $timeout        Request timeout in seconds
     * @param string      $timeoutDuration Human-readable timeout (e.g. "30s")
     */
    public function initSession(
        string $deviceId,
        ?string $origin = null,
        bool $queueOffline = false,
        int $timeout = 30,
        string $timeoutDuration = '30s',
    ): array {
        return $this->httpPost(
            '/real-time-response/entities/sessions/v1',
            array_filter([
                'device_id' => $deviceId,
                'origin' => $origin,
                'queue_offline' => $queueOffline,
            ]),
            ['timeout' => $timeout, 'timeout_duration' => $timeoutDuration]
        );
    }

    /**
     * Close a Real Time Response session.
     *
     * @param string $sessionId Session ID to close
     */
    public function closeSession(string $sessionId): array
    {
        return $this->httpDelete('/real-time-response/entities/sessions/v1', [
            'session_id' => $sessionId,
        ]);
    }

    /**
     * Refresh a session to prevent timeout.
     *
     * @param string $sessionId Session ID
     * @param string $deviceId  Device ID
     */
    public function refreshSession(string $sessionId, string $deviceId): array
    {
        return $this->httpPost('/real-time-response/entities/refresh-session/v1', [
            'session_id' => $sessionId,
            'device_id' => $deviceId,
        ]);
    }

    /**
     * Execute a read-only RTR command on a single host.
     *
     * @param string $sessionId    Active session ID
     * @param string $baseCommand  RTR command (e.g. "ls", "ps", "netstat")
     * @param string $commandString Full command string including arguments
     */
    public function executeCommand(
        string $sessionId,
        string $baseCommand,
        string $commandString,
    ): array {
        return $this->httpPost('/real-time-response/entities/command/v1', [
            'session_id' => $sessionId,
            'base_command' => $baseCommand,
            'command_string' => $commandString,
        ]);
    }

    /**
     * Get the result of a previously executed command.
     *
     * @param string $cloudRequestId Cloud request ID from executeCommand
     * @param int    $sequenceId     Result sequence number (start at 0)
     */
    public function getCommandResult(string $cloudRequestId, int $sequenceId = 0): array
    {
        return $this->httpGet('/real-time-response/entities/command/v1', [
            'cloud_request_id' => $cloudRequestId,
            'sequence_id' => $sequenceId,
        ]);
    }

    /**
     * Execute an active-responder RTR command (requires active-responder scope).
     *
     * @param string $sessionId    Active session ID
     * @param string $baseCommand  RTR command (e.g. "put", "run", "reg set")
     * @param string $commandString Full command string
     */
    public function executeActiveResponderCommand(
        string $sessionId,
        string $baseCommand,
        string $commandString,
    ): array {
        return $this->httpPost('/real-time-response/entities/active-responder-command/v1', [
            'session_id' => $sessionId,
            'base_command' => $baseCommand,
            'command_string' => $commandString,
        ]);
    }

    /**
     * Get the result of a previously executed active-responder command.
     */
    public function getActiveResponderCommandResult(string $cloudRequestId, int $sequenceId = 0): array
    {
        return $this->httpGet('/real-time-response/entities/active-responder-command/v1', [
            'cloud_request_id' => $cloudRequestId,
            'sequence_id' => $sequenceId,
        ]);
    }

    /**
     * Execute an admin RTR command (requires admin scope).
     *
     * @param string $sessionId    Active session ID
     * @param string $baseCommand  RTR command (e.g. "put-and-run", "runscript")
     * @param string $commandString Full command string
     */
    public function executeAdminCommand(
        string $sessionId,
        string $baseCommand,
        string $commandString,
    ): array {
        return $this->httpPost('/real-time-response/entities/admin-command/v1', [
            'session_id' => $sessionId,
            'base_command' => $baseCommand,
            'command_string' => $commandString,
        ]);
    }

    /**
     * Get the result of a previously executed admin command.
     */
    public function getAdminCommandResult(string $cloudRequestId, int $sequenceId = 0): array
    {
        return $this->httpGet('/real-time-response/entities/admin-command/v1', [
            'cloud_request_id' => $cloudRequestId,
            'sequence_id' => $sequenceId,
        ]);
    }

    // -------------------------------------------------------------------------
    // Batch (multi-host) sessions
    // -------------------------------------------------------------------------

    /**
     * Initialize a batch RTR session across multiple hosts.
     *
     * @param string[]    $deviceIds        Target device IDs
     * @param bool        $queueOffline     Queue commands for offline hosts
     * @param int         $timeout          Timeout in seconds
     * @param string      $timeoutDuration  Human-readable timeout
     * @param string      $hostTimeout      Per-host timeout duration
     */
    public function batchInitSession(
        array $deviceIds,
        bool $queueOffline = false,
        int $timeout = 30,
        string $timeoutDuration = '30s',
        string $hostTimeout = '20s',
    ): array {
        return $this->httpPost(
            '/real-time-response/combined/batch-init-session/v1',
            [
                'host_ids' => $deviceIds,
                'queue_offline' => $queueOffline,
            ],
            [
                'timeout' => $timeout,
                'timeout_duration' => $timeoutDuration,
                'host_timeout_duration' => $hostTimeout,
            ]
        );
    }

    /**
     * Execute a read-only command on a batch of hosts.
     *
     * @param string $batchId       Batch session ID from batchInitSession
     * @param string $baseCommand   RTR command
     * @param string $commandString Full command string
     * @param int    $timeout       Timeout in seconds
     * @param string $timeoutDuration Human-readable timeout
     * @param string $hostTimeout   Per-host timeout duration
     */
    public function batchCommand(
        string $batchId,
        string $baseCommand,
        string $commandString,
        int $timeout = 30,
        string $timeoutDuration = '30s',
        string $hostTimeout = '20s',
    ): array {
        return $this->httpPost(
            '/real-time-response/combined/batch-command/v1',
            [
                'batch_id' => $batchId,
                'base_command' => $baseCommand,
                'command_string' => $commandString,
            ],
            [
                'timeout' => $timeout,
                'timeout_duration' => $timeoutDuration,
                'host_timeout_duration' => $hostTimeout,
            ]
        );
    }

    /**
     * Execute an active-responder command on a batch of hosts.
     */
    public function batchActiveResponderCommand(
        string $batchId,
        string $baseCommand,
        string $commandString,
        int $timeout = 30,
        string $timeoutDuration = '30s',
        string $hostTimeout = '20s',
    ): array {
        return $this->httpPost(
            '/real-time-response/combined/batch-active-responder-command/v1',
            [
                'batch_id' => $batchId,
                'base_command' => $baseCommand,
                'command_string' => $commandString,
            ],
            [
                'timeout' => $timeout,
                'timeout_duration' => $timeoutDuration,
                'host_timeout_duration' => $hostTimeout,
            ]
        );
    }

    /**
     * Execute an admin command on a batch of hosts.
     */
    public function batchAdminCommand(
        string $batchId,
        string $baseCommand,
        string $commandString,
        int $timeout = 30,
        string $timeoutDuration = '30s',
        string $hostTimeout = '20s',
    ): array {
        return $this->httpPost(
            '/real-time-response/combined/batch-admin-command/v1',
            [
                'batch_id' => $batchId,
                'base_command' => $baseCommand,
                'command_string' => $commandString,
            ],
            [
                'timeout' => $timeout,
                'timeout_duration' => $timeoutDuration,
                'host_timeout_duration' => $hostTimeout,
            ]
        );
    }

    /**
     * Initiate a file download from a batch of hosts.
     *
     * @param string   $batchId  Batch session ID
     * @param string   $filePath File path to retrieve
     * @param string[] $optional Optional host IDs (subset of batch)
     */
    public function batchGetFile(
        string $batchId,
        string $filePath,
        array $optional = [],
        int $timeout = 30,
        string $timeoutDuration = '30s',
        string $hostTimeout = '20s',
    ): array {
        $body = ['batch_id' => $batchId, 'file_path' => $filePath];
        if (!empty($optional)) {
            $body['optional_hosts'] = $optional;
        }

        return $this->httpPost(
            '/real-time-response/combined/batch-get-command/v1',
            $body,
            [
                'timeout' => $timeout,
                'timeout_duration' => $timeoutDuration,
                'host_timeout_duration' => $hostTimeout,
            ]
        );
    }

    /**
     * Poll the status of a batch file retrieval.
     *
     * @param string $batchGetCmdReqId Request ID from batchGetFile
     */
    public function getBatchGetFileStatus(string $batchGetCmdReqId): array
    {
        return $this->httpGet('/real-time-response/combined/batch-get-command/v1', [
            'batch_get_cmd_req_id' => $batchGetCmdReqId,
        ]);
    }

    /**
     * Refresh a batch session.
     *
     * @param string   $batchId        Batch session ID
     * @param string[] $hostsToRemove  Optional hosts to remove from the batch
     */
    public function batchRefreshSession(string $batchId, array $hostsToRemove = []): array
    {
        $body = ['batch_id' => $batchId];
        if (!empty($hostsToRemove)) {
            $body['hosts_to_remove'] = $hostsToRemove;
        }

        return $this->httpPost('/real-time-response/combined/batch-refresh-session/v1', $body);
    }

    // -------------------------------------------------------------------------
    // Files & Scripts
    // -------------------------------------------------------------------------

    /**
     * List PUT-files available for RTR.
     */
    public function queryPutFiles(?string $filter = null, ?string $sort = null, ?int $limit = null, ?string $offset = null): array
    {
        return $this->httpGet('/real-time-response/queries/put-files/v1', [
            'filter' => $filter,
            'sort' => $sort,
            'limit' => $limit,
            'offset' => $offset,
        ]);
    }

    /**
     * Retrieve PUT-file metadata by IDs.
     *
     * @param string[] $ids File IDs
     */
    public function getPutFilesByIds(array $ids): array
    {
        return $this->httpGet('/real-time-response/entities/put-files/v2', ['ids' => $ids]);
    }

    /**
     * List custom RTR scripts.
     */
    public function queryScripts(?string $filter = null, ?string $sort = null, ?int $limit = null, ?string $offset = null): array
    {
        return $this->httpGet('/real-time-response/queries/scripts/v1', [
            'filter' => $filter,
            'sort' => $sort,
            'limit' => $limit,
            'offset' => $offset,
        ]);
    }

    /**
     * Retrieve script metadata by IDs.
     *
     * @param string[] $ids Script IDs
     */
    public function getScriptsByIds(array $ids): array
    {
        return $this->httpGet('/real-time-response/entities/scripts/v2', ['ids' => $ids]);
    }

    /**
     * Query active RTR sessions.
     */
    public function querySessions(
        ?string $filter = null,
        ?string $sort = null,
        ?int $limit = null,
        ?string $offset = null,
    ): array {
        return $this->httpGet('/real-time-response/queries/sessions/v1', [
            'filter' => $filter,
            'sort' => $sort,
            'limit' => $limit,
            'offset' => $offset,
        ]);
    }

    /**
     * Retrieve RTR session details by IDs.
     *
     * @param string[] $ids Session IDs
     */
    public function getSessionsByIds(array $ids): array
    {
        return $this->httpPost('/real-time-response/entities/sessions/GET/v1', ['ids' => $ids]);
    }
}
