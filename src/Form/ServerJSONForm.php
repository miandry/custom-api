<?php

namespace Drupal\server_json\Form;


use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class ContentExportSettingForm.
 */
class ServerJSONForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'server_json.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'server_json_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
     $config = $this->config('server_json.settings');
      $id = \Drupal::request()->get('id');
      if($id && $id == 'settings'){
         \Drupal::service('server_json')->updateSettings();
      }
      $form['path_url'] = [
          '#type' => 'textfield',
          '#title' => $this->t('Url Server json'),
          '#default_value' => $config->get('path_url'),
          '#description' => 'For import orders from mobile  , and push product in mobile also edit order'

      ];

      $form['path_file'] = [
          '#type' => 'textfield',
          '#title' => $this->t('Path Server Page file json'),
          '#description' => 'For deploy page-external in mobile , Please make sure to have permission to read the folder',

          '#default_value' => $config->get('path_file'),
      ];

      $form['sites'] = [
          '#type' => 'textarea',
          '#title' => $this->t('Sites API deploy'),
          '#description' => 'For example : http://tanabeautyfull.gasy.pro|http://eroso.mizara.net ',
          '#default_value' => $config->get('sites'),
          '#description' => 'For import product between two differents sites'
      ];
//      $form['settings_json'] = [
//          '#type' => 'container',
//          '#attributes' => ['class' => ['paragraph-type-title']],
//          'label' => ['#markup' => '<a href="/admin/config/deploy?id=settings">click to update settings json</a>'],
//      ];

   ///   $results = \Drupal::service('filereader')->readDirectory('/Volumes/ARCHIVE/projects/miandy_project/nodejs/api/api');
   //   kint($results);
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);
     $this->config('server_json.settings')
       ->set('path_url', $form_state->getValue('path_url'))
         ->set('path_file', $form_state->getValue('path_file'))
         ->set('sites', $form_state->getValue('sites'))
         ->save();
  }

}
