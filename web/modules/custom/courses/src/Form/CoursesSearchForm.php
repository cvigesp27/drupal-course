<?php

namespace Drupal\courses\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\courses\Service\CoursesManager;
use Override;
use Symfony\Component\DependencyInjection\ContainerInterface;


class CoursesSearchForm extends FormBase{

    protected CoursesManager $coursesManager;

    public function __construct(CoursesManager $coursesManager)
    {
        $this->coursesManager = $coursesManager;
    }

    
    public static function create(ContainerInterface $container)
    {
        return new static($container->get('courses.manager'));
    }

    // ID del formulario
    public function getFormId(): string{
        return 'courses_form';
    }

    //Construye el formulario
    public function buildForm(array $form, FormStateInterface $form_state)
    {
        /**
         * textfield
         * hidden
         * url
         * textarea
         * select
         * 
         */
        $form['name'] = [
            '#type' => 'textfield',
            '#title' => $this->t('Buscador de cursos'),
            '#description' => $this->t('Introduce un nombre de curso'),
            '#default_value' => $form_state->getValue('name', ''),
        ];  

        $form['actions'] = [
            '#type' => 'actions',
        ];

        $form['actions']['submit'] = [
            '#type' => 'submit',
            '#value' => $this->t('Buscar')
        ];

        if($form_state->get('search_submitted')){
            $query = (string) $form_state->getValue('name','');
            $courses = $this->coursesManager->findByName($query);

            $items = [];

            foreach($courses as $course){
                $items[] = $course['name'];
            }

            $form['result'] = [
                '#theme' => 'item_list',
                '#title' => $this->t('Resultados'),
                '#items' => $items,
                '#empty' => $this->t('Sin resultados'),
            ];
        }

        return $form;
    }

    //Validar datos
    public function validateForm(array &$form, FormStateInterface $form_state)
    {
        $name = trim((string) $form_state->getValue('name'));

        if(strlen($name) > 10){
            $form_state->setErrorByName('name',$this->t('DEMASIADAS LETRAS'));
        }
    }


    //Procesar el envio
    #[Override]
    public function submitForm(array &$form, FormStateInterface $form_state)
    {
        $form_state->set('search_submitted',TRUE);

        $form_state->setRebuild();  
    }
}