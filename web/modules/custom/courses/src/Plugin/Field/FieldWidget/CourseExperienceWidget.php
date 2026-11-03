<?php

declare(strict_types=1);

namespace Drupal\courses\Plugin\Field\FieldWidget;

use Drupal\Core\Field\Attribute\FieldWidget;
use Drupal\Core\Field\Plugin\Field\FieldWidget\OptionsButtonsWidget;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;

#[FieldWidget(
  id: 'course_experience_radios',
  label: new TranslatableMarkup('Courses: new experience selector'),
  field_types: ['list_string'],
)]
final class CourseExperienceWidget extends OptionsButtonsWidget {
    /**
   * Añade una explicación al selector de experiencia.
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state,) {
    // Reutilizamos el formulario de opciones de Drupal core.
    $element = parent::formElement($items, $delta, $element, $form, $form_state,);

    // Explicamos a los editores qué representa el campo.
    $element['#description'] = $this->t(
      'Select the experience required.',
    );

    return $element;
  }
}