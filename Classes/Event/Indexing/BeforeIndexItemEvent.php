<?php

declare(strict_types=1);

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

namespace ApacheSolrForTypo3\Solr\Event\Indexing;

use ApacheSolrForTypo3\Solr\IndexQueue\Item;
use ApacheSolrForTypo3\Solr\Task\IndexQueueWorkerTask;

/**
 * This event is dispatched before the indexing of an item starts
 *
 * @author Lars Tode <lars.tode@dkd.de>
 */
final class BeforeIndexItemEvent
{
    /**
     * @var Item
     */
    private Item $item;

    /**
     * @var IndexQueueWorkerTask|null
     */
    private ?IndexQueueWorkerTask $task;

    private string $runId;

    /**
     * @param Item $item
     * @param IndexQueueWorkerTask|null $task
     * @param string $runId
     */
    public function __construct(Item $item, ?IndexQueueWorkerTask $task, string $runId)
    {
        $this->item = $item;
        $this->task = $task;
        $this->runId = $runId;
    }

    /**
     * @return Item
     */
    public function getItem(): Item
    {
        return $this->item;
    }

    /**
     * @param Item $item
     */
    public function setItem(Item $item): void
    {
        $this->item = $item;
    }

    /**
     * @return IndexQueueWorkerTask|null
     */
    public function getTask(): ?IndexQueueWorkerTask
    {
        return $this->task;
    }

    /**
     * @return string
     */
    public function getRunId(): string
    {
        return $this->runId;
    }
}
