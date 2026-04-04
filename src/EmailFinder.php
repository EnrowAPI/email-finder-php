<?php

namespace EmailFinder;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class EmailFinder
{
    private const BASE_URL = 'https://api.enrow.io';

    private static function request(string $apiKey, string $method, string $path, ?array $body = null): array
    {
        $client = new Client(['base_uri' => self::BASE_URL]);

        $options = [
            'headers' => [
                'x-api-key' => $apiKey,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ],
        ];

        if ($body !== null) {
            $options['json'] = $body;
        }

        try {
            $response = $client->request($method, $path, $options);
            return json_decode($response->getBody()->getContents(), true);
        } catch (RequestException $e) {
            if ($e->hasResponse()) {
                $data = json_decode($e->getResponse()->getBody()->getContents(), true);
                $message = $data['message'] ?? 'API error ' . $e->getResponse()->getStatusCode();
                throw new \RuntimeException($message, $e->getResponse()->getStatusCode(), $e);
            }
            throw new \RuntimeException($e->getMessage(), 0, $e);
        }
    }

    /**
     * Find an email address for a person at a company.
     *
     * @param string $apiKey Your Enrow API key.
     * @param array $params {
     *     @type string $fullName        Full name of the person (required).
     *     @type string $companyDomain   Company domain (e.g. "apple.com").
     *     @type string $companyName     Company name (e.g. "Apple Inc.").
     *     @type array  $custom          Custom data to attach to the result.
     *     @type string $countryCode     ISO country code to narrow results.
     *     @type bool   $retrieveGender  Whether to retrieve gender information.
     *     @type string $webhook         Webhook URL for async notification.
     * }
     * @return array Search result containing an id to poll with get().
     */
    public static function find(string $apiKey, array $params): array
    {
        $body = ['fullname' => $params['fullName']];

        if (!empty($params['companyDomain'])) {
            $body['company_domain'] = $params['companyDomain'];
        }
        if (!empty($params['companyName'])) {
            $body['company_name'] = $params['companyName'];
        }
        if (!empty($params['custom'])) {
            $body['custom'] = $params['custom'];
        }

        $settings = [];
        if (!empty($params['countryCode'])) {
            $settings['country_code'] = $params['countryCode'];
        }
        if (isset($params['retrieveGender'])) {
            $settings['retrieve_gender'] = $params['retrieveGender'];
        }
        if (!empty($params['webhook'])) {
            $settings['webhook'] = $params['webhook'];
        }
        if ($settings) {
            $body['settings'] = $settings;
        }

        return self::request($apiKey, 'POST', '/email/find/single', $body);
    }

    /**
     * Get the result of a single email search.
     *
     * @param string $apiKey Your Enrow API key.
     * @param string $id     The search ID returned by find().
     * @return array Email result with email, qualification, status, etc.
     */
    public static function get(string $apiKey, string $id): array
    {
        return self::request($apiKey, 'GET', '/email/find/single?id=' . urlencode($id));
    }

    /**
     * Find email addresses for multiple people in a single batch.
     *
     * @param string $apiKey Your Enrow API key.
     * @param array $params {
     *     @type array  $searches    Array of searches, each with fullName, companyDomain, companyName, custom.
     *     @type string $countryCode ISO country code.
     *     @type string $webhook     Webhook URL for async notification.
     * }
     * @return array Batch result containing batchId, total, status.
     */
    public static function findBulk(string $apiKey, array $params): array
    {
        $searches = array_map(function (array $search): array {
            $item = ['fullname' => $search['fullName']];
            if (!empty($search['companyDomain'])) {
                $item['company_domain'] = $search['companyDomain'];
            }
            if (!empty($search['companyName'])) {
                $item['company_name'] = $search['companyName'];
            }
            if (!empty($search['custom'])) {
                $item['custom'] = $search['custom'];
            }
            return $item;
        }, $params['searches']);

        $body = ['searches' => $searches];

        $settings = [];
        if (!empty($params['countryCode'])) {
            $settings['country_code'] = $params['countryCode'];
        }
        if (isset($params['retrieveGender'])) {
            $settings['retrieve_gender'] = $params['retrieveGender'];
        }
        if (!empty($params['webhook'])) {
            $settings['webhook'] = $params['webhook'];
        }
        if ($settings) {
            $body['settings'] = $settings;
        }

        return self::request($apiKey, 'POST', '/email/find/bulk', $body);
    }

    /**
     * Get the results of a bulk email search.
     *
     * @param string $apiKey Your Enrow API key.
     * @param string $id     The batch ID returned by findBulk().
     * @return array Batch results with status, completed count, and results array.
     */
    public static function getBulk(string $apiKey, string $id): array
    {
        return self::request($apiKey, 'GET', '/email/find/bulk?id=' . urlencode($id));
    }
}
