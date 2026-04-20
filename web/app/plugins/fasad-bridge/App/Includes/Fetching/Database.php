<?php

namespace FasadBridge\Includes\Fetching;

class Database extends Fetcher
{

    protected $postId;

    /**
     * Database constructor.
     *
     * @param $postId
     */
    public function __construct($postId)
    {
        parent::__construct();
        $this->postId = $postId;
    }

    /**
     * Use like ->getAttribute("economy")
     *
     * @param string $attr
     * @return mixed
     */
    public function getAttribute(string $attr)
    {
        $meta = get_post_meta($this->postId, $this->prefix . $attr, true);
        return maybe_unserialize($meta);
    }

    /**
     * Use like ->getAttribute("economy.apartment.fee")
     *
     * @param string $attr
     * @return mixed|null
     */
    public function getNestedAttribute(string $attr)
    {
        $keys = explode(".", $attr);

        $data = $this->getAttribute($keys[0]);
        array_shift($keys);

        $value = $this->attributeLoop($keys, $data);

        return $value;
    }

    /**
     * @return object
     */
    public function getData()
    {
        $meta = get_post_meta($this->postId);

        $metaFormatted = [];
        foreach ($meta as $key => $item) {
            $newKey = str_replace($this->prefix, '', $key);
            if (array_key_exists(0, $item)) {
                $metaFormatted[$newKey] = maybe_unserialize(maybe_unserialize($item[0]));
            }
        }

        return json_decode(json_encode($metaFormatted));
    }

}