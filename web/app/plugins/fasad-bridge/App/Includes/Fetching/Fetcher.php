<?php


namespace FasadBridge\Includes\Fetching;

use FasadBridge\Includes\Interfaces\FetcherInterface;

abstract class Fetcher implements FetcherInterface
{
    protected $prefix = "_fasad_";

    public function __construct()
    {
    }

    /**
     *  Loop through data by keys
     *
     * @param $keys
     * @param $data
     * @return mixed|null
     */
    public function attributeLoop($keys, $data)
    {
        foreach ($keys as $key) {
            if (isset($data->{$key})) {
                $data = $data->{$key};
            } else {
                $data = null;
                break;
            }
        }
        return $data;
    }

    /**
     * Get db-prefix
     * @return string
     */
    public function getPrefix(){
        return $this->prefix;
    }
}
