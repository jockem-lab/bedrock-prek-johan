<?php

namespace FasadBridge\Includes\Synchronization;

use FasadApiconnect\Includes\ApiConnectionHandler;
use FasadBridge\Includes\Interfaces\FasadObjectHandlerInterface;
use FasadBridge\Includes\Interfaces\OutputInterface;
use FasadBridge\Includes\PublicSettings;

/**
 * Responsible to synchronize data retrieved from ApiConnectionHandler
 *
 * Class Synchronizer
 *
 * @package FasadBridge\Includes\Synchronization
 */
abstract class Synchronizer implements FasadObjectHandlerInterface
{
    /**
     * @var ApiConnectionHandler
     */
    protected $apiConnectionHandler;

    public $prefix = "_fasad_";

    protected $formatter;

    protected $params;

    /**
     * Synchronizer constructor.
     */
    public function __construct(ApiConnectionHandler $apiConnectionHandler, OutputInterface $formatter = null, array $params = [])
    {
        $this->apiConnectionHandler = $apiConnectionHandler;
        if($formatter == null){
            $formatter = new NullOutput();
        }
        $this->formatter = $formatter;
        $this->params = $params;
    }

    /**
     * Get all the posts for current post type except the given ids
     *
     * @param array $ids
     * @param string $postType
     * @param bool $includeDraft
     * @param bool $includePrivate
     * @return int[]|\WP_Post[]
     */
    public function getAllExceptIds(array $ids, string $postType = '', bool $includeDraft = false, bool $includePrivate = false)
    {
        if (empty($postType)) {
            $postType = $this->postType;
        }

        $args = [
            'post_type'      => $postType,
            'posts_per_page' => -1,
            "post_status"    => ['publish'],
            'post__not_in'   => $ids,
        ];

        if ($includeDraft) {
            $args['post_status'][] = 'draft';
        }

        if ($includePrivate) {
            $args['post_status'][] = 'private';
        }

        return get_posts($args);
    }

    /**
     * Get alla posts for current post type
     *
     * @param string $postType
     * @param bool $includeDraft
     * @param bool $includePrivate
     * @return int[]|\WP_Post[]
     */
    public function getAll(string $postType = '', bool $includeDraft = false, bool $includePrivate = false)
    {
        if (empty($postType)) {
            $postType = $this->postType;
        }

        $args = [
            'post_type'      => $postType,
            'posts_per_page' => -1,
            "post_status"    => ['publish'],
        ];

        if ($includeDraft) {
            $args['post_status'][] = 'draft';
        }

        if ($includePrivate) {
            $args['post_status'][] = 'private';
        }

        return get_posts($args);
    }

    /**
     * Get 1 post by fasad id
     *
     * @param        $id
     * @param string $idName
     * @param string|null $postType
     * @param bool $includeDraft
     * @param bool $includePrivate
     * @return int|\WP_Post|null
     */
    public function getByFasadId($id, $idName = "id", $postType = null, $includeDraft = false, $includePrivate = false)
    {

        if ($postType === null) {
            $postType = $this->postType;
        }

        $args = [
            "post_type"      => $postType,
            "posts_per_page" => 1,
            "post_status"    => ['publish'],
            "meta_query"     => [
                [
                    "key"   => $this->prefix . $idName,
                    "value" => $id
                ]
            ]
        ];

        if ($includeDraft) {
            $args['post_status'][] = 'draft';
        }

        if ($includePrivate) {
            $args['post_status'][] = 'private';
        }

        $wpPosts = get_posts($args);

        return isset($wpPosts[0]) ? $wpPosts[0] : null;
    }

    /**
     * Loop through data and save it as a single value or as a serialized object|array
     *
     * @param $item
     * @param $postId
     */
    public function savePostMeta($item, $postId)
    {
        $this->formatter->output("Sparar metadata i post $postId.");
        foreach ($item as $key => $value) {
            $value = apply_filters('fasad_bridge_save_meta_value', $value, $key, $postId);
            if (is_object($value) || is_array($value)) {
                // Complex data must be serialized first!
                update_post_meta($postId, $this->prefix . $key, serialize($value));
            } else {
                update_post_meta($postId, $this->prefix . $key, $value);
            }
        }
    }

    public function setOrder($postId, $order = 0)
    {
        wp_update_post([
            'ID' => $postId,
            'menu_order' => $order
        ]);
    }

    /**
     * Create a new post
     *
     * @param $item
     * @param null $postType
     * @return int|\WP_Error
     */
    public function createPost($item, $postType = null)
    {

        if ($postType === null) {
            $postType = $this->postType;
        }

        $postStatus = "publish";
        if ($postType == PublicSettings::FASAD_PROTECTED_POST_TYPE && !apply_filters('fasad_bridge_publish_protected', false)) {
            $postStatus = "private";
        }

        $post = [
            "post_content" => "",
            "post_status"  => $postStatus,
            "post_type"    => $postType
        ];

        $post["post_title"] = apply_filters('fasad_bridge_object_title', $this->getTitle($item, $postType), $item, $post);

        // Insert the post into the database.
        $postId = wp_insert_post(apply_filters('fasad_bridge_create_post', (array)$post));

        $this->formatter->output("Skapade posten $postId.");

        return $postId;
    }

    /**
     * Delete all posts of current post type
     */
    public function deleteAll()
    {
        $wpPosts = $this->getAll();
        $this->deleteByPosts($wpPosts);
        $type = strtolower(basename(str_replace('\\', '/', get_class($this))));
        do_action_ref_array('fasad_bridge_' . $type . '_complete', [$wpPosts,  'deleted']);
    }

    /**
     * Delete given posts for current post type
     *
     * @param array $wpPosts
     * @return int number of deleted posts
     */
    public function deleteByPosts(array $wpPosts)
    {
        $deleted = 0;
        if (!empty($wpPosts)) {
            foreach ($wpPosts as $wpPost) {
                if($this->delete($wpPost->ID)){
                    $deleted++;
                }
            }
        }
        return $deleted;
    }

    /**
     * Delete post by id
     *
     * @param $id
     * @return WP_Post|false|null Post data on success, false or null on failure.
     */
    private function delete($id)
    {
        $this->formatter->output("Tar bort post $id");
        return wp_delete_post($id, true);
    }

    protected function deleteCheck($removablePosts, $fetchedPosts, $threshold = 20): array
    {
        $deleteCheck = [
            'doDelete' => true,
            'reason'   => ''
        ];
        //Delete if force
        if (isset($this->params['force']) && $this->params['force'] === 'all') {
            $deleteCheck['reason'] = 'force';
            return $deleteCheck;
        }
        //Fetched posts are less than 0, error from api?
        if ($fetchedPosts < 0) {
            $deleteCheck['doDelete'] = false;
            $deleteCheck['reason']   = 'fetched posts less than 0';
        }
        //No need to delete
        if (!is_array($removablePosts) || count($removablePosts) <= 0) {
            $deleteCheck['doDelete'] = false;
            $deleteCheck['reason']   = 'removable posts less than 0';
        }
        //Do not delete if removablePosts are more than or equal to fetched posts, if removablePosts are more than threshold
        //ie: do not allow massdeletions
        if (count($removablePosts) >= $fetchedPosts && count($removablePosts) > $threshold) {
            $deleteCheck['doDelete'] = false;
            $deleteCheck['reason']   = 'massdeletion block';
        }
        return $deleteCheck;
    }
}