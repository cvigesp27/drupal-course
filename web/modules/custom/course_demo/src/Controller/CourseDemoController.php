<?php

namespace Drupal\course_demo\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 *  Main controller
 */
class CourseDemoController extends ControllerBase{
    /**
     * Welcome page
     */
    public function hello(string $name): array{
        return [
            '#markup' => $this->t('Hello @name',['@name' => $name]),
            ];
    }

    /**
     * Admin config controller
     */
    public function admin(): array{
        return[
            '#markup' => $this->t('Página de administración de Course Demo'),
        ];
    }
}