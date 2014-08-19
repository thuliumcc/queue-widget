<?php

require __DIR__ . DIRECTORY_SEPARATOR . 'queue-widget.config.php';

if (!function_exists('json_last_error_msg')) {
    function json_last_error_msg()
    {
        static $errors = array(
            JSON_ERROR_NONE => null,
            JSON_ERROR_DEPTH => 'Maximum stack depth exceeded',
            JSON_ERROR_STATE_MISMATCH => 'Underflow or the modes mismatch',
            JSON_ERROR_CTRL_CHAR => 'Unexpected control character found',
            JSON_ERROR_SYNTAX => 'Syntax error, malformed JSON',
            JSON_ERROR_UTF8 => 'Malformed UTF-8 characters, possibly incorrectly encoded'
        );
        $error = json_last_error();
        return array_key_exists($error, $errors) ? $errors[$error] : "Unknown error ({$error})";
    }
}

QueueWidgetDataSource::serve($configuration);

class QueueWidgetDataSource
{
    private $authBasicHash;
    private $url;
    private $queueId;
    /**
     * @var Cache
     */
    private $cache;

    public static function serve($configuration)
    {
        $dataSource = new self($configuration, $_REQUEST['queue_id']);
        echo $dataSource->renderQueueInfo();
    }

    private function __construct($configuration, $queue_id)
    {
        $this->url = $configuration['api_url'];
        $this->authBasicHash = $this->getAuthBasicHash($configuration['user'], $configuration['password']);
        $this->queueId = $this->getQueueId($queue_id, $configuration);
        $this->currentQueueName = $this->getQueueName($this->queueId, $configuration);
        $this->createCache($configuration['cache']);
        if (!$this->isQueuePermitted($configuration, $queue_id)) {
            throw new Exception('Permission denied.');
        }
    }

    private function getQueueName($queueId, $configuration)
    {
        if (isset($configuration['queue_names'][$queueId])) {
            return $configuration['queue_names'][$queueId];
        }
        throw new Exception("Queue name is not defined (queue_id=$queueId).");
    }

    private function getQueueId($requestedQueueId, $configuration)
    {
        if ($requestedQueueId) {
            return $requestedQueueId;
        }
        return $configuration['permitted_queue_ids'][0];
    }

    private function getAuthBasicHash($user, $password)
    {
        return base64_encode("$user:$password");
    }

    private function isQueuePermitted($configuration, $queue_id)
    {
        return in_array($queue_id, $configuration['permitted_queue_ids']);
    }

    private function fetchDataFromThulium($url)
    {
        $headers = $this->getHeaders(array(
            "Accept" => "text/html",
            "Connection" => "Close",
            "Authorization" => "Basic " . $this->authBasicHash
        ));
        $context = stream_context_create(array(
            'http' => array(
                'header' => $headers,
                'method' => 'GET',
                'timeout' => 5.0,
                'ignore_errors' => true
            )
        ));
        return file_get_contents($url, null, $context);
    }

    private function getQueueWaitingStatsServiceUrl()
    {
        return "{$this->url}/queues/{$this->queueId}/waiting_stats";
    }

    private function getQueueWaitingStats()
    {
        $data = $this->fetchDataFromThulium($this->getQueueWaitingStatsServiceUrl());
        return $this->parseRetrievedData($data);
    }

    private function renderQueueInfo()
    {
        $queueWaitingStats = $this->getWaitingStatsFromApiOrCache();
        return json_encode(array(
            'queue' => $this->currentQueueName,
            'approx_wait' => $this->formatTime($queueWaitingStats['approx_wait_time']),
            'count' => $queueWaitingStats['in_queue']
        ));
    }

    private function formatTime($approx_wait_time)
    {
        return ($approx_wait_time) ?: '00:00:00';
    }

    private function parseRetrievedData($data)
    {
        $parsedData = json_decode($data, true);
        if ($parsedData === NULL) {
            throw new Exception('Error parsing data (' . json_last_error_msg() . '). Data: ' . $data);
        }
        return $parsedData;
    }

    private function getHeaders($headers)
    {
        $headersString = '';
        foreach ($headers as $name => $value) {
            $headersString .= "$name: $value\r\n";
        }
        return $headersString;
    }

    private function getWaitingStatsFromApiOrCache()
    {
        $cachedData = ($this->cache) ? $this->cache->get($this->queueId) : null;
        if ($cachedData) {
            return $cachedData;
        } else {
            $freshData = $this->getQueueWaitingStats();
            $this->setWaitingStatsToCache($freshData);
            return $freshData;
        }
    }

    private function setWaitingStatsToCache($data)
    {
        if ($this->cache) {
            $this->cache->set($this->queueId, $data);
        }
    }

    private function createCache($cacheSettings)
    {
        if ($cacheSettings['enabled']) {
            set_include_path(get_include_path() . PATH_SEPARATOR . $cacheSettings['class_path']);
            require_once $cacheSettings['class'] . ".php";
            $this->cache = new $cacheSettings['class']($cacheSettings);
        }
    }
}
