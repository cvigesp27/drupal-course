<?php

namespace Drupal\courses\Service;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\node\NodeInterface;

class CoursesManager {

    public function __construct(protected EntityTypeManagerInterface $entityManager)
        { }

    public function getCourses(int $limit = 10): array{
        $storage = $this->entityManager->getStorage('node');

        $ids = $storage->getQuery()
            ->accessCheck(true)
            ->condition('type', 'course')
            ->condition('status', 1)
            ->sort('title', 'ASC')
            ->range(0, max(1, $limit))
            ->execute();
        
        return $storage->loadMultiple($ids);
    }

    public function getCourse(int $id): ?NodeInterface{
        $node = $this->entityManager->getStorage('node')->load($id);

        if(!$node instanceof NodeInterface){
            return null;
        }

        if($node->bundle() !== 'course' || !$node->access('view')){
            return null;
        }

        return $node;
    }

    public function findByName(string $query): array {
        $matches = [];
        $query = trim($query);

        foreach($this->getCourses() as $course){
            if($query === '' || stripos($course['name'], $query !== FALSE)){
                $matches[] = $course;
            }
        }

        return $matches;
    }

    public function processRegistration(array $data): array{
        return[
            'name' => trim($data['name']),
            'duration' => trim($data['duration']),
            'price' => trim($data['price']),
            'sale' => !empty($data['sale']),
            'experience' => trim($data['experience'] ?? ''),
            'tags' => trim($data['tags'] ?? []),
            'teacher' => trim($data['teacher']),
            'comments' => trim($data['comments']),
        ];
    }

    public function createCourse(array $values): NodeInterface{
        $storage = $this->entityManager->getStorage('node');

        $course = $storage->create([
            'type' => 'course',
            'title' => $values['name'],
            'field_duration' => $values['duration'],
            'field_price' => $values['price'],
            'field_sale' => $values['sale'],
            'field_experience' => $values['experience'],
            'field_tags' => $values['tags'],
            'field_teacher' => $values['teacher'],
            'field_comments' => $values['comments'],
            'status' => 1,
        ]);

        $course->save();

        return $course;
    }

    public function updateCourse(NodeInterface $course, array $values): NodeInterface{

        $course->setTitle($values['name']);
        $course->set('field_duration', $values['duration']);
        $course->set('field_price', $values['price']);
        $course->set('field_sale', $values['sale']);
        $course->set('field_experience', ['uri' => $values['experience']]);
        $course->set('field_tags', $values['tags']);
        $course->set('field_teacher', $values['teacher']);
        $course->set('field_comments', $values['comment']);

        $course->save();

        return $course;
    }
}