<?php

namespace FasadApiConnect\Includes;

use FasadApiConnect\Includes\Interfaces\TokenHandlerInterface;

/**
 * Handle all calls to FasAd API
 */
class ApiConnectionHandler
{

    /**
     * @var TokenHandlerInterface
     */
    protected $tokenHandler;

    /**
     * @var array The generic headers to be sent in all calls except the login
     */
    protected $headers;

    /**
     * @var string The access token needed for ALL calls
     */
    protected $accessToken;

    /**
     * @var string The secret to login and retrieve access token
     */
    protected $clientSecret;

    /**
     * @var string The password to login and retrieve access token
     */
    protected $clientPassword;


    /**
     * FasadApi constructor.
     */
    public function __construct()
    {
        $this->tokenHandler = new CacheTokenHandler();
        $this->setClientParameters();
        $this->setAccessToken();
        $this->setHeaders();
    }


    private function setClientParameters()
    {
        // Get options from plugin option page.
        $options = get_option("fasad-api-connect-options");

        if (!empty($options)) {
            $this->clientSecret = $options["client-secret"];
            $this->clientPassword = $options["client-password"];
        }
    }


    /**
     * Set the access token from tokenHandler or login to retrieve a new one
     */
    private function setAccessToken()
    {
        $cacheToken = $this->tokenHandler->get();

        $this->accessToken = $cacheToken ?: $this->login();
    }

    /**
     * Set the headers for all calls except login
     */
    private function setHeaders()
    {
        $this->headers = [
            "Accept"        => "application/json",
            "Authorization" => "Bearer " . $this->accessToken
        ];
    }

    /** Login to retrieve a access token
     *
     * @return string Returning the access token retrieved
     */
    public function login()
    {
        $allowedRetries = 2;
        $retries        = 0;
        do {
            $retries++;
            $apiUrl          = apply_filters('fasad_api_url', API_URL);
            $args            = [];
            $args["method"]  = "POST";
            $args["headers"] = ["Accept" => "application/json",];
            $args["body"]    = [
                "client_secret" => $this->clientSecret,
                "password"      => $this->clientPassword,
            ];

            $response     = wp_remote_request($apiUrl . '/v1/auth/login', $args);
            $responseCode = wp_remote_retrieve_response_code($response);
            $responseBody = json_decode(wp_remote_retrieve_body($response));

            $accessToken = null;
            if ($responseCode == "200" && isset($responseBody->access_token)) {
                $accessToken = $responseBody->access_token;

                // Save token
                $this->tokenHandler->set($accessToken, ($responseBody->expires_in * 0.9));
                break;
            }
        } while ($retries < $allowedRetries);

        return $accessToken;
    }

    /**
     * @return array
     */
    public function getPublishedListingsCompact($per_page = null)
    {
        return $this->getPaginatedItems("/v2/listings/published_compact" . ($per_page ? "/" . $per_page : ""));
    }

    /**
     * @return array
     */
    public function getUnpublishedListingsCompact($per_page = null)
    {
        return $this->getPaginatedItems("/v2/listings/unpublished_compact" . ($per_page ? "/" . $per_page : ""));
    }

    /**
     * @return array
     */
    public function getSoldListingsCompact($per_page = null)
    {
        return $this->getPaginatedItems("/v2/listings/sold_compact" . ($per_page ? "/" . $per_page : ""));
    }

    /**
     * @return array
     */
    public function getPublishedListings()
    {
        return $this->getPaginatedItems("/v2/listings/published");
    }

    /**
     * @return array
     */
    public function getSoldListings()
    {
        return $this->getPaginatedItems("/v2/listings/sold");
    }

    /**
     * @return array
     */
    public function getLeasedListings()
    {
        return $this->getPaginatedItems("/v2/listings/leased");
    }

    /**
     * @return array
     */
    public function getProtectedListings()
    {
        return $this->getPaginatedItems("/v2/listings/protected");
    }

    /**
     * @param $id
     * @return array|object
     * @throws \Exception
     */
    public function getListing($id)
    {
        return $this->doRequest("GET", "/v2/listings/$id");
    }

    /**
     * @return array
     */
    public function getOffices()
    {
        return $this->getPaginatedItems("/v2/offices");
    }

    public function getShowings($listingId)
    {
        return $this->doRequest("GET", "/v2/listings/$listingId/showings");
    }

    public function getBiddings($listingId)
    {
        return $this->doRequest("GET", "/v2/listings/$listingId/biddings");
    }

    public function getDocuments($listingId)
    {
        return $this->doRequest("GET", "/v2/listings/$listingId/documents");
    }

    public function getServitudes($listingId)
    {
        return $this->doRequest("GET", "/v2/listings/$listingId/servitudes");
    }

    public function bankidAuth($ssn)
    {
        return $this->doRequest("POST", "/v2/bankid-auth?ssn=$ssn");
    }

    public function bankidAuthLaunch($type)
    {
        return $this->doRequest("POST", "/v2/bankid-auth/launch", "type=$type");
    }

    public function bankidStatus($uuid)
    {
        return $this->doRequest("GET", "/v2/bankid-auth/$uuid");
    }

    public function bankidStatusPoll($uuid, $type, $try)
    {
        return $this->doRequest("GET", "/v2/bankid-auth/$uuid/poll", "type=$type&try=$try");
    }

    public function bankidCancel($uuid)
    {
        return $this->doRequest("DELETE", "/v2/bankid-auth/$uuid");
    }

    public function customerpagesListings($uuid)
    {
        return $this->doRequest("GET", "/v2/customer-pages/$uuid/listings");
    }

    public function customerpagesCustomer($uuid)
    {
        return $this->doRequest("GET", "/v2/customer-pages/$uuid");
    }

    public function customerpagesListing($uuid, $listingId)
    {
        return $this->doRequest("GET", "/v2/customer-pages/$uuid/listings/$listingId");
    }

    public function customerpagesDocuments($uuid, $listingId)
    {
        return $this->doRequest("GET", "/v2/customer-pages/$uuid/listings/$listingId/documents");
    }

    public function customerpagesSaveQuestionnaire($uuid, $listingId, $params)
    {
        return $this->doRequest("POST", "/v2/customer-pages/$uuid/listings/$listingId/questionnaire", null, $params);
    }

    public function customerpagesChecklistItems($uuid, $listingId)
    {
        return $this->doRequest("GET", "/v2/customer-pages/$uuid/listings/$listingId/checklistitems");
    }

    public function customerpagesAddAnswer($uuid, $listingId, $itemId, $answer)
    {
        return $this->doRequest("POST", "/v2/customer-pages/$uuid/listings/$listingId/checklistitems/$itemId/answers", "answer=" . $answer);
    }

    public function customerpagesSaveDocument($uuid, $listingId, $itemId, $params)
    {
        return $this->doRequest("POST", "/v2/customer-pages/$uuid/listings/$listingId/checklistitems/$itemId/documents", null, $params);
    }

    public function addSecureBid($params)
    {
        return $this->doRequest("POST", "/v2/secure-bids", null, $params);
    }

    public function secureBidStatus($uuid)
    {
        return $this->doRequest("GET", "/v2/secure-bids/$uuid");
    }

    public function getSearchCriterias()
    {
        return $this->doRequest("GET", "/v2/searchcriterias");
    }

    /**
     * This method should remain private so we can manage all endpoints from this class.
     *
     *
     * @param string $method
     * @param string $endpoint
     * @return array|object
     *
     * @throws \Exception
     */

    private function doRequest($method, $endpoint, $query = null, $params = [])
    {
        $apiUrl = apply_filters('fasad_api_url', API_URL);
        $response = null;
        $args = [];
        $args["method"] = $method;
        if(isset($params['headers'])){
            $args["headers"] = array_merge($this->headers, $params['headers']);
        }else{
            $args["headers"] = $this->headers;
        }
        $args["timeout"] = 30;
        if(isset($params['body'])){
            $args["body"] = $params['body'];
        }

        $url = $apiUrl . $endpoint;
        if ($query) {
            $url .= "?" . $query;
        }

        $apiResponse = wp_remote_request($url, $args);
        $apiResponseCode = wp_remote_retrieve_response_code($apiResponse);
        $apiResponseBody = wp_remote_retrieve_body($apiResponse);
        if ($apiResponseCode == "200" || $apiResponseCode == "201") {
            $response = $apiResponseBody;
        } elseif ($apiResponseCode == "204") {
            $response = "";
        } elseif (is_wp_error($apiResponse)) {
            error_log(print_r($apiResponse, true));
            throw new \Exception($apiResponse->get_error_message());
        } else {
            error_log(print_r($apiResponse, true));
            throw new \Exception("Response code $apiResponseCode, responseBody $apiResponseBody");
        }

        return json_decode($response);
    }

    /** Do multiple requests to paginated data. Will return the objects inside data attribute.
     *
     * @param $endpoint
     * @return array
     * @throws \Exception
     */
    private function getPaginatedItems($endpoint)
    {
        $items = [];
        $nextUrlQueryParams = "page=1";

        // Keep going as long as we have a links.next url
        while ($nextUrlQueryParams) {

            $responseBody = $this->doRequest("GET", $endpoint, $nextUrlQueryParams);

            if ($responseBody && isset($responseBody->data)) {
                $items = array_merge($items, $responseBody->data);
                $nextUrl = !empty($responseBody->links->next) ? parse_url($responseBody->links->next) : [];
                if (isset($nextUrl["query"])) {
                    $nextUrlQueryParams = $nextUrl["query"];
                } else {
                    $nextUrlQueryParams = null;
                }
            } else {
                $nextUrlQueryParams = null;
                $items = [];
            }
        }

        return $items;
    }
}