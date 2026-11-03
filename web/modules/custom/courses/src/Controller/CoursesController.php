<?php

declare(strict_types=1);

namespace Drupal\courses\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\courses\Service\CoursesManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Returns responses for Developer Directory routes.
 */
final class CoursesController extends ControllerBase {

  protected CoursesManager $coursesManager;

  public function __construct(CoursesManager $course_manager)
  {
    $this->coursesManager = $course_manager;
  }

 
  public static function create(ContainerInterface $container): static
  {
    return new static(
      $container->get('courses.manager')
    );
  } 

  public function list(): array {
    $courses = $this->coursesManager->getCourses();

    // Lectura escritura de la config
    // $config = $this->configFactory()->getEditable('courses-settings');
    // $config->set('congiguration','ejemplo de config cambiada');
    // $config->save();

    // Solo lectura
    // $config = $this->config('courses.settings');
    // $limit = $config->get('courses_per_page');

    $items = [];
    foreach($courses as $course){
      $items[] = [
        '#type' => 'link',
        '#title' => $course['name'],
        '#url' => \Drupal\Core\Url::fromRoute('courses.details', ['name' => $course['name']])
      ];
    }

    $build['content'] = [
      '#theme' => 'item_list',
      '#title' => $this->t('Cursos de informática'),
      '#items' => $items
    ];

    return $build;
  }

  
  #[Route(
    '/developers/{id}',
    name: 'courses.view',
    requirements: [
      '_permission' => 'access content',
      'id' => '\d+',
    ]
  )]
  public function view(int $id): array {
    $course = $this->coursesManager->getCourse($id);

    if(!$course) throw new NotFoundHttpException(); 

    $experienceField = $course->get('field_experience');
    $experienceOptions = $experienceField->getFieldDefinition()->getSetting('allowed_values');
    $experienceLabel = $experienceOptions[$experienceField->value] ?? $experienceField->value;    

    $tagsField = $course->get('field_tags');
    $tagsOptions = $tagsField->getFieldDefinition()->getSetting('allowed_values');
    $tagsLabel = $tagsOptions[$tagsField->value] ?? $tagsField->value;    



    return [
      '#theme' => 'course_details',
      '#name' => $course->getTitle(),      
      '#dutation' => $course->get('field_duration')->value,
      '#price' => $course->get('field_price')->value,
      '#experience' => $experienceLabel,
      '#tags' => $tagsLabel,      
      '#sale' => (bool) $course->get('field_sale')->value,
      '#teacher' => $course->get('field_teacher')->value,
      '#comments' => $course->get('field_comments')->value,  
    ];
  }
}