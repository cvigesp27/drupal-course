<?php

declare(strict_types=1);

namespace Drupal\developer_directory\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use Symfony\Component\Routing\Attribute\Route;

/**
 * Returns responses for Developer Directory routes.
 */
final class DeveloperDirectoryController extends ControllerBase {

  public function list(): array {
    $developers = ['David', 'Rubén', 'Airam', 'Antonio', 'Elí', 'Yesica', 'Cristian'];

    $items = [];
    foreach($developers as $name){
      $items[] = [
        '#type' => 'link',
        '#title' => $name,
        '#url' => \Drupal\Core\Url::fromRoute('developer_directory.view', ['name' => $name])
      ];
    }

    $build['content'] = [
      '#theme' => 'item_list',
      '#title' => $this->t('Desarrolladores'),
      '#items' => $items
    ];

    return $build;
  }

  public function view(string $name): array {
    $developers = [
      'David' => [
        'role' => "Fullstack developer",
        'experience' => '20 years',
        'skills' => ['HTML', 'CSS', 'JavaScript'],
      ],
      'Rubén' => [
        'role' => "Frontend developer",
        'experience' => 15,
        'skills' => ['HTML', 'CSS', 'JavaScript'],
      ],
      'Airam' => [
        'role' => "DevOps",
        'experience' => 5,
        'skills' => ['HTML', 'CSS', 'JavaScript'],
      ],
      'Antonio' => [
        'role' => "Frontend developer",
        'experience' => 15,
        'skills' => ['HTML', 'CSS', 'JavaScript'],
      ],
      'Elí' => [
        'role' => "DevOps",
        'experience' => 5,
        'skills' => ['HTML', 'CSS', 'JavaScript'],
      ],
      'Yesica' => [
        'role' => "Frontend developer",
        'experience' => 15,
        'skills' => [],
      ],
      'Cristian' => [
        'role' => "DevOps",
        'experience' => 5,
        'skills' => ['HTML', 'CSS', 'JavaScript'],
      ],
    ];

    if(!isset($developers[$name])) throw new NotFoundHttpException();
    $developer = $developers[$name];

    return [
      '#theme' => 'developer_profile',
      '#name' => $name,      
      '#role' => $developer['role'],
      '#experience' => $developer['experience'],
      '#skills' => $developer['skills']
    ];
  }

  public function admin(): array{
    return [
      '#markup' => $this->t('Página de administración del directorio de desarrolladores')
    ];
  }
}