<?php

namespace Drupal\courses\Form;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\node\NodeInterface;
use Override;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class CoursesDeleteForm extends ConfirmFormBase {
    protected EntityTypeManagerInterface $entityTypeManager;

    protected ?NodeInterface $course = NULL;

    public function __construct(EntityTypeManagerInterface $entity_type_manager) {
        $this->entityTypeManager = $entity_type_manager;    
    }

    public static function create(ContainerInterface $container): static {
        return new static(
            $container->get('entity_type.manager')
        );
    }

    public function getFormId(): string {
        return 'courses_delete_form';
    }

    public function buildForm(array $form, FormStateInterface $form_state, ?NodeInterface $node = NULL,)
    {
        // La ruta debe proporcionarnos un nodo válido.
        if ($node === NULL || $node->bundle() !== 'course') {
            throw new NotFoundHttpException();
        }

        // Guardamos el nodo para mostrar su nombre en la confirmación.
        $this->course = $node;

        // Guardamos el ID para recuperar el nodo al confirmar.
        $form_state->set('course_id', $node->id());

        return parent::buildForm($form, $form_state);
    }

    public function getQuestion(): string {
        return $this->t('¿Seguroooo que quieres eliminar el course @name?', ['@name' => $this->course?->label() ?? '']);
    }

    public function getDescription(): string {
        return $this->t('Esta operación no se puede deshacer.');
    }

    public function getConfirmText(): string {
        return $this->t('Eliminar');
    }

    public function getCancelUrl(): Url {
        return Url::fromRoute('courses.list');
    }

    public function submitForm(array &$form, FormStateInterface $form_state): void {
        $id = $form_state->get('course_id');

        // Recuperamos la entidad antes de eliminarla.
        $course = $this->entityTypeManager
            ->getStorage('node')
            ->load($id);

        // Comprobamos de nuevo que existe y que es un course.
        if (!$course instanceof NodeInterface || $course->bundle() !== 'course') {
            throw new NotFoundHttpException();
        }

        $name = $course->label();
        $course->delete();

        $this->messenger()->addStatus($this->t('Se ha eliminado el course @name.', ['@name' => $name,]));

        $form_state->setRedirect('courses.list');
    }
}