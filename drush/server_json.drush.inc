<?php

use Drupal\Core\Database\Database;

function server_json_drush_command()
{
    $items = [];

    $items['deploy-product'] = [
        'aliases' => ['dp'],
        'arguments' => [
            'action' => 'The split configuration to export, if none is given do a normal import.',
       ],
    ];
    return $items;
}

function drush_server_json_deploy_product($action)
{
    $config = \Drupal::config('server_json.settings');
    $path_file = $config->get('path_product');
    if($path_file){
    \Drupal::service('server_json')->udpateFileContent('[]', $path_file);
    }

    $bundle = 'produit';
    $entity_name = NULL;
    $db = Database::getConnection();
    $query = $db->select('node_field_data', 'n');
    $query->fields('n', ['nid', 'type']);
    $query->condition('n.type', $bundle, '=');
    $query->condition('n.status', 1, '=');
    $resultat = $query->execute()->fetchAllAssoc('nid');
    if (!empty($resultat)) {
        $total = sizeof($resultat);
        $operations = [];
        $i = 1;
        foreach ($resultat as $index => $item) {
            $info = [
                'nid' => $item->nid,
                'index' => $i,
                'total' => $total,
                'action' => $action

            ];
            $i++;
            $operations[] = [
                'server_json_index_batch_process',
                [$info],
            ];
        }
        // Set up the batch job.
        $batch = [
            'operations' => $operations,
            'title' => t('Depoly  process'),
            'init_message' => t('Starting...'),
            'progress_message' => t('Completed @current of @total.'),
            'error_message' => t('An error occurred'),
            'finished' => 'server_json_batch_finish',
            'file' => drupal_get_path('module', 'server_json') . '/drush/server_json.batch.inc',
        ];
        // Start the batch job.
        batch_set($batch);
        drush_backend_batch_process();
        //[0]["value"];

    }


}


