<?php

namespace Drupal\course\Plugin\QueueWorker;

use Drupal\Core\Logger\LoggerChannelInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Queue\Attribute\QueueWorker;
use Drupal\Core\Queue\QueueWorkerBase;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Override;
use phootwork\collection\Queue;
use Symfony\Component\DependencyInjection\ContainerInterface;

#[QueueWorker(
    id: 'courses_notifications',
    title: new TranslatableMarkup('Notificaciones de cursos'),
    cron: ['time' => 30]
)]
final class CourseNotificationWorker extends QueueWorkerBase implements ContainerFactoryPluginInterface{

    #[Override]
    public function __construct(array $configuration, $plugin_id, $plugin_definition, protected LoggerChannelInterface $logger)
    {
        return parent::__construct($configuration, $plugin_id, $plugin_definition);
    }

    #[Override]
    public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition)
    {
        return new static($container->get('logger.factory')->get('courses'), $configuration, $plugin_id, $plugin_definition);
    } 

    #[Override]
    public function processItem($data)
    {
        // Comprobar datos son correctos
        if(!is_array($data) || empty($data['course_id'])){
            $this->logger->warning('Se ha recibido un anotificacion sin ID');
            return;
        }

        $course_id = (int) $data['course_id'];
        $opertaion = $data['opertaion'] ?? 'N/A';

        // Cargar entidad y enviar el correo o lo que sea
        $this->logger->info('Se ha procesador la notificacion del curso @id. Estatus: @operation', ['@id' => $course_id,'@operation' => $opertaion]);
    }
}