<?php

namespace FasadBridge\Includes\Synchronization;

use FasadApiConnect\Includes\ApiConnectionHandler;
use FasadBridge\Includes\Interfaces\OutputInterface;
use FasadBridge\Includes\PublicSettings;

/**
 * Class Realtor
 *
 * @package FasadBridge\Includes\Synchronization
 */
class Realtor extends Synchronizer
{
    protected $postType = "fasad_realtor";
    protected $realtors = [];
    protected $params;

    public $handledPostIds = [];

    private $syncResults = [
        'fetched'     => -1,
        'updated'     => 0,
        'created'     => 0,
        'skipped'     => 0,
        'deleted'     => 0,
        'synced'      => 0,
        'syncedIds'   => [],
        'deleteCheck' => []
    ];

    public function __construct(ApiConnectionHandler $apiConnectionHandler, OutputInterface $formatter = null)
    {
        parent::__construct($apiConnectionHandler, $formatter);
    }

    /**
     * Not used, realtors are synced in the office sync
     */
    public function synchronize(){}

    /**
     * Get the office title
     *
     * @param $item
     * @return string
     */
    public function getTitle($item, $postType) {
        return $item->firstname . ' ' . $item->lastname;
    }

    /**
     * Create an array with all realtors
     *
     * @param array $office
     */
    public function setRealtors($office)
    {
        if (!empty($office->realtors)) {
            foreach ($office->realtors as $realtor) {
                if (array_key_exists($realtor->id, $this->realtors)) {
                    $this->appendOfficeData($office->id, $realtor);
                } else {
                    $this->realtors[$realtor->id] = $realtor;
                    $this->setOfficeData($office->id, $realtor);
                }
                if ($realtor->mainOffice) {
                    $this->replaceRealtorData($realtor);
                }
            }
        }
    }


    /**
     * Create an officeData property on the realtor,
     * containing serialized realtor data for the chosen office
     *
     * @param int $officeId
     * @param object $realtor
     * @param array $officeData
     */
    private function setOfficeData($officeId, $realtor, $officeData = [])
    {
        $officeData[$officeId] = $realtor;
        $this->realtors[$realtor->id]->officeData = serialize($officeData);
    }

    /**
     * Append realtor data for another office to the officeData property
     *
     * @param int $officeId
     * @param object $realtor
     */
    private function appendOfficeData($officeId, $realtor)
    {
        $officeData = unserialize($this->realtors[$realtor->id]->officeData);
        $this->setOfficeData($officeId, $realtor, $officeData);
    }

    /**
     * Replace the data on the realtor object, but keep officeData
     *
     * @param object $realtor
     */
    private function replaceRealtorData($realtor)
    {
        $tmp = $this->realtors[$realtor->id]->officeData;
        $this->realtors[$realtor->id] = $realtor;
        $this->realtors[$realtor->id]->officeData = $tmp;
    }

    /**
     * Save realtors from an office
     */
    public function saveRealtors($params)
    {
        $this->params = $params;
        $this->formatter->output(PHP_EOL . "- Laddar in mäklare -" . PHP_EOL);
        $this->syncResults['fetched'] = count($this->realtors);
        if (!empty($this->realtors)) {
            $this->save($this->realtors);
        }
        $this->deleteRemoved();
        return $this->syncResults;
    }

    /**
     * Creating and saving of realtor post
     *
     * @param $data
     */
    public function save($data)
    {
        foreach ($data as $key => $realtor) {
            $existingPost = $this->getByFasadId($realtor->id);

            if ($existingPost) {
                $postId = $existingPost->ID;
                $action = 'updated';
            } else {
                $postId = $this->createPost($realtor);
                $action = 'created';
            }
            $this->savePostMeta($realtor, $postId);
            $this->setOrder($postId, $realtor->sequence);

            $this->handledPostIds[] = $postId;
            $this->syncResults['syncedIds'][] = $realtor->id;
            $this->syncResults[$action]++;
            $this->syncResults['synced']++;
            do_action_ref_array('fasad_bridge_realtor_complete', [$postId, $realtor, $action, ['formatter' => $this->formatter]]);
        }
    }

    /**
     * Delete all realtors the were not included from the API
     */
    public function deleteRemoved()
    {
        $removablePosts = $this->getAllExceptIds($this->handledPostIds);
        $fetchedPosts = $this->syncResults['fetched'];

        $deleteCheck = $this->deleteCheck($removablePosts, $fetchedPosts, 4); //No more than 4 realtors should be able to be deleted at once?
        $this->syncResults['deleteCheck'] = $deleteCheck;
        if ($deleteCheck['doDelete']) {
            $this->syncResults['deleted'] = $this->deleteByPosts($removablePosts);
        }
    }

}
