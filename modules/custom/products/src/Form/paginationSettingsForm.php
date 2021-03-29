<?php

namespace Drupal\products\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
* Configure example settings for this site.
*/
class paginationSettingsForm extends ConfigFormBase {

/**
* Config settings.
*
* @var string
*/
const SETTINGS = 'products.settings';

/**
* {@inheritdoc}
*/
public function getFormId() {
return 'products_admin_settings';
}

/**
* {@inheritdoc}
*/
protected function getEditableConfigNames() {
return [
static::SETTINGS,
];
}

/**
* {@inheritdoc}
*/
public function buildForm(array $form, FormStateInterface $form_state) {
$config = $this->config(static::SETTINGS);

$form['items_per_page'] = [
'#type' => 'textfield',
'#title' => $this->t('Items per page'),
'#default_value' => $config->get('items_per_page'),
];

return parent::buildForm($form, $form_state);
}

/**
* {@inheritdoc}
*/
public function submitForm(array &$form, FormStateInterface $form_state) {
// Retrieve the configuration.
$this->configFactory->getEditable(static::SETTINGS)
// Set the submitted configuration setting.
->set('items_per_page', $form_state->getValue('items_per_page'))
->save();

parent::submitForm($form, $form_state);
}

}
