<?php

/**
 * @file
 * Contains \Drupal\example_queue\Plugin\QueueWorker\ExampleQueueWorker.
 */

namespace Drupal\server_json\Plugin\QueueWorker;

use Drupal\Core\Queue\QueueWorkerBase;

/**
 * Processes tasks for module.
 *
 * @QueueWorker(
 *   id = "Import_content_site",
 *   title = @Translation("Import Product to another  Queue worker"),
 *   cron = {"time" = 60}
 * )
 */

class ImportSiteProcess extends QueueWorkerBase
{
    /**
     * {@inheritdoc}
     */
    public function processItem($array) {
        $url = $array['url'];
        $content = $array['content'];
        $response = \Drupal::service('product_json')->senAPIItem($url, $content);
        return $response ;
    }
}