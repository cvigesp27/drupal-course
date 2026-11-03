<?php

namespace Drupal\courses\Plugin\Block;

use Drupal\Core\Block\Attribute\Block;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\courses\Service\CoursesManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Displays a list of featured courses.
 */
#[Block(
  id: 'coursesy_featured',
  admin_label: new TranslatableMarkup('Featured courses'),
)]
class FeaturedCoursesBlock extends BlockBase implements ContainerFactoryPluginInterface {
  
  public function __construct(array $configuration, $plugin_id, $plugin_definition, protected CoursesManager $directoryManager,) {
        parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition,): static {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('courses.manager'),
    );
  }

  public function defaultConfiguration(): array {
    return [
        'number_of_courses' => 5,
    ] + parent::defaultConfiguration();
  }

  public function blockForm($form, FormStateInterface $form_state,): array {
    $form = parent::blockForm($form, $form_state);

    $form['number_of_courses'] = [
      '#type' => 'number',
      '#title' => $this->t('Number of courses'),
      '#default_value' => $this->configuration['number_of_courses'],
      '#min' => 1,
      '#max' => 10,
      '#required' => TRUE,
    ];

    return $form;
  }

  public function blockSubmit($form, FormStateInterface $form_state): void {
    parent::blockSubmit($form, $form_state);

    $this->configuration['number_of_courses'] = (int) $form_state->getValue('number_of_courses');
  }

  public function build(): array {
    $courses = $this->directoryManager->getCourses(
        $this->configuration['number_of_courses']
    );
    
    // $build = [
    //     '#theme' => 'courses_featured',
    //     '#title' => $this->t('Featured Courses'),
    //     '#courses' => $courses,
    // ];

    // $cache = CacheableMetadata::createFromRenderArray($build);

    // foreach ($courses as $course) {
    //     $cache->addCacheableDependency($course);
    // }

    // $cache->addCacheTags(['node_list']);

    // $cache->applyTo($build);

    // return $build;

    $items = [];

    foreach ($courses as $course) {
        $items[] = [
        'name' => $course->getTitle(),
        ];
    }

    return [
        '#theme' => 'courses_featured',
        '#title' => $this->t('Featured Courses'),
        '#courses' => $items,
        '#cache' => [
            'tags' => ['node_list'],
            'contexts' => ['user.permissions'],
            'max-age' => 3600,
        ],
    ];
    }
}