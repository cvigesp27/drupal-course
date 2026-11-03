<?php

namespace Drupal\courses\Service;

use Drupal\Core\Queue\QueueFactory;

final class CoursesNotificationQueue{

    public function __construct(private QueueFactory $queueFactory)
    {
        
    }

    public function enqueue(int $courseId, string $operation): void {
        $queue = $this->queueFactory->get('courses_notifications');

        // Guardar los datos necesarios para procesarlos luego

        $queue->createItem([
            'course_id' => $courseId,
            'operation' => $operation,
        ]);
    }
}
