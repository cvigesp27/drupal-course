<?php

declare(strict_types=1);

namespace Drupal\courses\Plugin\views\filter;

use Drupal\Core\Form\FormStateInterface;
use Drupal\views\Attribute\ViewsFilter;
use Drupal\views\Plugin\views\filter\FilterPluginBase;

#[ViewsFilter('course_title_prefix')]
final class CourseTitlePrefix extends FilterPluginBase {
  /**
   * Define las opciones de configuración del filtro.
   */
  protected function defineOptions(): array {
    $options = parent::defineOptions();

    // Sin valor significa que no se aplica el filtro.
    $options['value'] = ['default' => '',];

    return $options;
  }

  /**
   * Construye el formulario de configuración del filtro.
   */
    protected function valueForm(&$form, FormStateInterface $form_state,): void {
        $letters = [
        '' => $this->t('- Cualquier inicial -'),
        ];

        foreach (range('A', 'Z') as $letter) {
            $letters[$letter] = $letter;
        }

        $form['value'] = [
        '#type' => 'select',
        '#title' => $this->t('Letra inicial'),
        '#options' => $letters,
        '#default_value' => $this->value ?? '',
        ];
    }

    /**
     * Añade la condición a la consulta de Views.
     */
  public function query(): void {
    // Views puede entregar el valor como cadena o como array.
    $value = $this->value;

    if (is_array($value)) $value = reset($value);

    // Si no hay una letra seleccionada, no aplicamos el filtro.
    if (!is_string($value) || $value === '') {
      return;
    }

    // Garantiza que Views disponga del alias de la tabla necesaria.
    $this->ensureMyTable();

    // Añade una condición SQL parametrizada sobre el nombre del nodo.
    $this->query->addWhereExpression(
      $this->options['group'],
      'UPPER(' . $this->tableAlias . '.title) LIKE :course_title_prefix',
      [
        ':courses_title_prefix' => strtoupper($value) . '%',
      ],
    );
  }

  /**
   * Resume el filtro en la interfaz administrativa de Views.
   */
  public function adminSummary(): string {
    if (empty($this->value)) {
      return (string) $this->t('Any letter');
    }

    return (string) $this->t(
      'Name begins with @letter',
      ['@letter' => $this->value],
    );
  }
}