<?php


namespace FasadBridge\Includes\Synchronization;

use FasadApiConnect\Includes\ApiConnectionHandler;
use FasadBridge\Includes\Interfaces\OutputInterface;
use FasadBridge\Includes\PublicSettings;

/**
 * Class Office
 *
 * @package FasadBridge\Includes\Synchronization
 */
class Office extends Synchronizer
{
    protected $postType = PublicSettings::FASAD_OFFICE_POST_TYPE;
    protected $realtorSync;
    protected $syncRealtors = true;

    public $handledPostIds = [];

    private $syncResults = [
        'office'  => [
            'fetched'     => -1,
            'updated'     => 0,
            'created'     => 0,
            'skipped'     => 0,
            'deleted'     => 0,
            'synced'      => 0,
            'syncedIds'   => [],
            'deleteCheck' => [],
        ],
        'realtor' => []
    ];

    public function __construct(ApiConnectionHandler $apiConnectionHandler, OutputInterface $formatter = null, array $params = [])
    {
        parent::__construct($apiConnectionHandler, $formatter, $params);
        $this->syncRealtors = apply_filters('fasad_synchronize_realtors', $this->syncRealtors);
        if ($this->syncRealtors) {
            $this->realtorSync = new Realtor($apiConnectionHandler, $formatter);
        }
    }

    /**
     * Do synchronize.
     *
     * Will sync all offices for this API key.
     * Will delete the rest.
     */
    public function synchronize()
    {
        $this->saveOffices();

        $removablePosts = $this->getAllExceptIds($this->handledPostIds);
        $fetchedPosts = $this->syncResults['office']['fetched'];
        $deleteCheck = $this->deleteCheck($removablePosts, $fetchedPosts, 1); //No more than 1 office should be able to be deleted at once?
        $this->syncResults['office']['deleteCheck'] = $deleteCheck;
        if ($deleteCheck['doDelete']) {
            $this->syncResults['office']['deleted'] = $this->deleteByPosts($removablePosts);
        }

        return $this->syncResults;
    }

    /**
     * Get the office title
     *
     * @param $item
     * @return string
     */
    public function getTitle($item, $postType)
    {
        if ($item->publicAlias) {
            return trim($item->publicAlias);
        } else {
            return trim($item->alias);
        }
    }

    /**
     * Get offices from api and save them
     */
    public function saveOffices()
    {
        $offices = $this->apiConnectionHandler->getOffices();
        $this->syncResults['office']['fetched'] = count($offices);
        $this->formatter->output(PHP_EOL . "- Laddar in kontor -" . PHP_EOL);
        do_action_ref_array('fasad_bridge_offices_begin', ['office', $this->params]);

        $this->save($offices);
        do_action_ref_array('fasad_bridge_offices_complete', [$offices, 'office', $this->params]);
    }

    /**
     * Creating and saving of office post
     *
     * @param $data
     */
    public function save($data)
    {
        foreach ($data as $office) {
            $existingPost = $this->getByFasadId($office->id);

            if ($existingPost) {
                $postId = $existingPost->ID;
                $action = 'updated';
            } else {
                $postId = $this->createPost($office);
                $action = 'created';
            }
            $this->savePostMeta($office, $postId);

            $this->handledPostIds[] = $postId;
            $this->syncResults['office']['syncedIds'][] = $office->id;

            if ($this->syncRealtors) {
                $this->realtorSync->setRealtors($office);
            }
            $this->syncResults['office'][$action]++;
            $this->syncResults['office']['synced']++;
            do_action_ref_array('fasad_bridge_office_complete', [$postId, $office, $action]);
        }
        if ($this->syncRealtors) {
            $this->syncResults['realtor'] = $this->realtorSync->saveRealtors($this->params);
        }
    }

}