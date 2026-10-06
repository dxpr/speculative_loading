<?php

namespace Drupal\speculative_loading\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configure Speculative Loading settings.
 */
class SpeculativeLoadingSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'speculative_loading_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['speculative_loading.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('speculative_loading.settings');

    $form['description'] = [
      '#type' => 'markup',
      '#markup' => $this->t('This section allows you to control how URLs that your users navigate to are speculatively loaded to improve performance. Rules are added to frontend pages only, never to administration pages.'),
      '#prefix' => '<p>',
      '#suffix' => '</p>',
    ];

    // Beta releases had no 'enabled' key and were always active.
    $enabled = $config->get('enabled');
    if ($enabled === NULL) {
      $enabled = TRUE;
    }
    $form['enabled'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable speculative loading'),
      '#description' => $this->t('When disabled, no speculation rules are added to pages.'),
      '#default_value' => $enabled,
    ];

    // Mode options.
    $form['mode'] = [
      '#type' => 'radios',
      '#title' => $this->t('Speculation Mode'),
      '#default_value' => $config->get('mode'),
      '#options' => [
        'prefetch' => $this->t('Prefetch'),
        'prerender' => $this->t('Prerender'),
      ],
      '#description' => $this->t('Prerendering will lead to faster load times than prefetching. However, in case of interactive content, prefetching may be a safer choice.'),
      '#required' => TRUE,
    ];

    // Eagerness options.
    $form['eagerness'] = [
      '#type' => 'radios',
      '#title' => $this->t('Eagerness'),
      '#default_value' => $config->get('eagerness'),
      '#options' => [
        'conservative' => $this->t('Conservative (typically on click)'),
        'moderate' => $this->t('Moderate (typically on hover)'),
        'eager' => $this->t('Eager (on slightest suggestion)'),
      ],
      '#description' => $this->t('The eagerness setting defines the heuristics based on which the loading is triggered. "Eager" will have the minimum delay to start speculative loads, "Conservative" increases the chance that only URLs the user actually navigates to are loaded.'),
      '#required' => TRUE,
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('speculative_loading.settings')
      ->set('enabled', (bool) $form_state->getValue('enabled'))
      ->set('mode', $form_state->getValue('mode'))
      ->set('eagerness', $form_state->getValue('eagerness'))
      ->save();

    parent::submitForm($form, $form_state);
  }

}
