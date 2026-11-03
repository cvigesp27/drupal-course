<?php

declare(strict_types=1);

namespace Drupal\courses\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Field\Attribute\FieldFormatter;
use Drupal\Core\StringTranslation\TranslatableMarkup;

#[FieldFormatter(
  id: 'course_experience_badge',
  label: new TranslatableMarkup('Course: experience required'),
  field_types: ['list_string'],
)]
final class CourseExperienceFormatter extends FormatterBase {
    /**
   * Construye la salida del campo.
   */
  public function viewElements(FieldItemListInterface $items, $langcode): array {
    $elements = [];

    // Adjunta el CSS necesario para representar las etiquetas
    $elements['#attached']['library'][] = 'courses/experience_badges';

    // Obtenemos las opciones configuradas para el campo.
    $allowed_values = $this->fieldDefinition
      ->getFieldStorageDefinition()
      ->getSetting('allowed_values');

    foreach ($items as $delta => $item) {
      // Valor almacenado: junior, mid o senior.
      $value = $item->value;

      // Etiqueta configurada en Drupal: Junior, Intermedio o Senior.
      $label = $allowed_values[$value] ?? $value;

      // Elegimos una clase CSS conocida para cada nivel.
      $classes = match ($value) {
        'junior' => ['experience-badge', 'experience-badge--junior'],
        'mid' => ['experience-badge', 'experience-badge--mid'],
        'senior' => ['experience-badge', 'experience-badge--senior'],
        default => ['experience-badge'],
      };

      // Render array: Drupal escapa el texto de la etiqueta.
      $elements[$delta] = [
        '#type' => 'container',
        '#attributes' => [
          'class' => $classes,
        ],
        'label' => [
          '#plain_text' => (string) $label,
        ],
      ];
    }

    return $elements;
  }  
}