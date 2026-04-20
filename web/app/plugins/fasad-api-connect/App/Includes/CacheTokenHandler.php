<?php

namespace FasadApiConnect\Includes;

use FasadApiConnect\Includes\Interfaces\TokenHandlerInterface;

/** Taking care of the access token
 *
 * Class CacheTokenHandler
 * @package FasadBridge\Includes
 */
class CacheTokenHandler implements TokenHandlerInterface
{
    const ACCESS_TOKEN_NAME = "fasad_access_token";

    public function set($accessToken, $expires)
    {
        set_transient(self::ACCESS_TOKEN_NAME, $accessToken, $expires);
    }

    public function get()
    {
        return get_transient(self::ACCESS_TOKEN_NAME);
    }

    public function delete()
    {
        delete_transient(self::ACCESS_TOKEN_NAME);
    }
}