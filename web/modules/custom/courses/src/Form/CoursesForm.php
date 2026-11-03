<?php

namespace Drupal\courses\Form;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\courses\Service\CoursesManager;
use Drupal\courses\Service\CoursesNotificationQueue;
use Drupal\node\NodeInterface;
use Override;

class CoursesForm extends FormBase {

    public function __construct(protected CoursesManager $directoryManager,protected CoursesNotificationQueue $notificationQueue)
    {}

    public static function create(ContainerInterface $container): static{
        return new static($container->get('courses.manager'), $container->get('courses.notification_queue'));
    }

    public function getFormId(): string
    {
        return 'courses_course_form';
    }

    #[Override]
    public function buildForm(array $form, FormStateInterface $form_state, ?NodeInterface $course = NULL)
    {
        $form['name'] = [
            '#type' => 'textfield',
            '#title' => $this->t('Name'),
            '#required' => true,
            '#maxlength' => 120,
            '#default_value' => $course?->getTitle() ?? '',
        ];

        $form['duration'] = [
            '#type' => 'number',
            '#title' => $this->t('Duration'),
            '#required' => true,
            '#default_value' =>  $course?->get('field_duration')->value ?? '',
        ];

        $form['price'] = [
            '#type' => 'number',
            '#title' => $this->t('Price'),
            '#required' => true,
            '#default_value' =>  $course?->get('field_price')->value ?? '',
        ];

        $form['sale'] = [
            '#type' => 'checkbox',
            '#title' => $this->t('On sale'),
            '#default_value' => (bool) $course?->get('field_sale')->value ?? false
        ];

        $form['experience'] = [
            '#type' => 'select',
            '#title' => $this->t('Experience'),
            '#options' => [                
                'junior' => $this->t('Junior'),
                'mid' => $this->t('Mid'),
                'senior' => $this->t('Senior')
            ],
            '#required' => true,
            '#default_value' => $course?->get('field_experience')->value ?? 'junior',
        ];

        $form['tags'] = [
            '#type' => 'checkboxes',
            '#title' => $this->t('Tags'),
            '#options' => [
                'php' => 'PHP',
                'drupal' => 'Drupal',
                'symfony' => 'Symfony',
                'javascript' => 'Javascript',
                'python' => 'Python',
                'java' => 'Java',
                'springboot' => 'Springboot',
            ],
            '#default_value' => $course?->get('field_tags')->value ?? ''
        ];

        $form['teacher'] = [
            '#type' => 'textfield',
            '#title' => $this->t('Teacher'),
            '#required' => true,
            '#maxlength' => 120,
            '#default_value' => $course?->get('field_teacher')->value ?? '',
        ];

        $form['comments'] = [
            '#type' => 'textarea',
            '#title' => $this->t('Comments'),            
            '#default_value' => $course?->get('field_comments')->value ?? '',
        ];

        

        $form['actions'] = [
            '#type' => 'actions'
        ];

        $form['actions']['submit'] = [
            '#type' => 'submit',
            '#value' => $course ? $this->t('Update course') : $this->t('Create course')
        ];

        if($course !== null){
            $form_state->set('course_id', $course->id());
        }

        return $form;
    }

    #[Override]
    public function validateForm(array &$form, FormStateInterface $form_state)
    {
        $name = (string) $form_state->getValue('name');
        if(strlen($name) > 10){
            $form_state->setErrorByName('name', $this->t('El nombre no puede ser mayor a 10.'));
        }
    }

    #[Override]
    public function submitForm(array &$form, FormStateInterface $form_state)
    {
        $values = [
            'name' => $form_state->getValue('name'),
            'duration' => $form_state->getValue('duration'),
            'price' => $form_state->getValue('price'),
            'sale' => $form_state->getValue('sale'),
            'experience' => $form_state->getValue('experience'),
            'tags' => $form_state->getValue('tags'),
            'teacher' => $form_state->getValue('teacher'),
            'comments' => $form_state->getValue('comments'),
        ];

        $courseId = $form_state->get('course_id');

        if($courseId !== null){
            // Modificación
            $course = $this->directoryManager->getCourse($courseId);

            if($course === null){
                throw new \Symfony\Component\Httpkernel\Exception\NotFoundHttpException();
            }

            $course = $this->directoryManager->updateCourse($course, $values);

            $this->notificationQueue->enqueue(
                (int) $course->id(),
                'updated');

            $this->messenger()->addStatus($this->t('Actualizado el course @name.', ['@name' => $course->getTitle()]));

        }else{
            // Inserción
            $course = $this->directoryManager->createCourse($values);
            
            
            $this->notificationQueue->enqueue(
                (int) $course->id(),
                'created');

            $this->messenger()->addStatus($this->t('Creado el course @name.', ['@name' => $course->getTitle()]));
        }                

        $form_state->setRedirect('courses.list');
    }
}