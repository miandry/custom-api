<?php

namespace Drupal\server_json\Plugin\Action;

use Drupal\views_bulk_operations\Action\ViewsBulkOperationsActionBase;
use Drupal\views_bulk_operations\Action\ViewsBulkOperationsPreconfigurationInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Plugin\PluginFormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\core\Cache\Cache;
/**
 * An example action covering most of the possible options.
 *
 * If type is left empty, action will be selectable for all
 * entity types.
 *
 * @Action(
 *   id = "import_api_product",
 *   label = @Translation("Import API Product"),
 *   type = "",
 *   confirm = TRUE,
 *   pass_context = FALSE,
 *   pass_view = FALSE
 * )
 */
class VBImportAPIProduct extends ViewsBulkOperationsActionBase implements ViewsBulkOperationsPreconfigurationInterface {


//   public function executeMultiple(array $objects) {
//       \Drupal::service('server_json')->processProduct($objects);
//       return sprintf('Success' );
//   }
  /**
   * {@inheritdoc}
   */
  public function execute($entity = NULL) {
    /*
     * All config resides in $this->configuration.
     * Passed view rows will be available in $this->context.
     * Data about the view used to select results and optionally
     * the batch context are available in $this->context or externally
     * through the public getContext() method.
     * The entire ViewExecutable object  with selected result
     * rows is available in $this->view or externally through
     * the public getView() method.
     */
      $parser_node_json = \Drupal::service('entity_parser.manager');
      $array['content'] =   $parser_node_json->parser($entity);
      $array['url'] = $this->configuration['url'];
      $queue = \Drupal::queue('Import_content_site');
      $queue->createItem($array);
      return sprintf('Success');
  }


  /**
   * {@inheritdoc}
   */
  public function buildPreConfigurationForm(array $form, array $values, FormStateInterface $form_state) {
    return $form;
  }

  /**
   * Configuration form builder.
   *
   * If this method has implementation, the action is
   * considered to be configurable.
   *
   * @param array $form
   *   Form array.
   * @param Drupal\Core\Form\FormStateInterface $form_state
   *   The form state object.
   *
   * @return array
   *   The configuration form.
   */
  public function buildConfigurationForm(array $form, FormStateInterface &$form_state) {
      $config = \Drupal::config("server_json.settings");
      $output = ($config) ? $config->get('sites') : '';
      $output_array = explode('|', $output);
      $options = [];
      foreach ($output_array as $item){
          $options[trim($item)] = trim($item);
      }
      $form['url'] = [
          '#type' => 'select',
          '#title' => $this->t('URL site'),
          '#options' => $options,
          '#required' => TRUE
      ];
    return $form;

  }


  /**
   * Submit handler for the action configuration form.
   *
   * If not implemented, the cleaned form values will be
   * passed direclty to the action $configuration parameter.
   *
   * @param array $form
   *   Form array.
   * @param Drupal\Core\Form\FormStateInterface $form_state
   *   The form state object.
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {

      $this->configuration['url'] =  trim($form_state->getValue('url').'/action/produit/insert');

      // This is not required here, when this method is not defined,
    // form values are assigned to the action configuration by default.
    // This function is a must only when user input processing is needed.
//    $form_data = $form_state->get('views_bulk_operations');
//    $tags_full = [];
//    foreach ($form_data['list'] as $id){
//      $tags_full = Cache::mergeTags($tags_full, ["node:id:".$id[0]]);
//    }
//    \Drupal::cache()->set('views_bulk_operations_pdf' , $form_data['list'], Cache::PERMANENT , $tags_full);
//
//    // kint($form_data['list']);die();

  //  $base = new ExportView();
//    global $base_url;
  //  $base->utility->helper->redirectTo('export/pdf/produit');
  }

  /**
   * {@inheritdoc}
   */
  public function access($object, AccountInterface $account = NULL, $return_as_object = FALSE) {
    if ($object->getEntityType() === 'node') {
      $access = $object->access('update', $account, TRUE)
        ->andIf($object->status->access('edit', $account, TRUE));
      return $return_as_object ? $access : $access->isAllowed();
    }
     // kint($object->getEntityType());die();
    // Other entity types may have different
    // access methods and properties.
    return TRUE;
  }

}
