<?php

namespace Drupal\courses\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\courses\Service\CoursesManager;
use Override;
use Symfony\Component\DependencyInjection\ContainerInterface;

class CoursesRegistrationForm extends FormBase{
    protected CoursesManager $coursesManager;

    public function __construct(CoursesManager $coursesManager)
    {
        $this->coursesManager = $coursesManager;
    }

    public static function create(ContainerInterface $container): static{
        return new static($container->get('courses.manager'));
    }

    /**
     * Identificador único del formulario
     */    
    public function getFormId(): string
    {
        return 'courses_registration_form';
    }

    
    public function buildForm(array $form, FormStateInterface $form_state)
    {
        $form['identity'] = [
            '#type' => 'fieldset',
            '#title' => $this->t('Course data'),
        ];

        $form['identity']['name'] = [
            '#type' => 'textfield',
            '#title' => $this->t('Name'),
            '#required' => true,
            '#maxlength' => 40,
            '#default_value' => $form_state->getValue('name', '')
        ];

        $form['identity']['price'] = [
            '#type' => 'number',
            '#title' => $this->t('Price'),
            '#required' => true,
            '#default_value' => $form_state->getValue('price', '')
        ];

        $form['identity']['sale'] = [
            '#type' => 'checkbox',
            '#title' => $this->t('Sale avalible'),
            '#default_value' => $form_state->getValue('sale', 0)
        ];

         $form['identity']['sale_price'] = [
            '#type' => 'number',
            '#title' => $this->t('Sale price'),
            '#states' => [
                'visible' => [
                    ':input[name="sale"]' => ['checked' => true]
                ]
            ]
        ];

        $form['identity']['teacher'] = [
            '#type' => 'textfield',
            '#title' => $this->t('Teacher'),
            '#required' => true,
            '#maxlength' => 40,
            '#default_value' => $form_state->getValue('teacher', '')
        ];

        $form['experience'] = [
            '#type' => 'select',
            '#title' => $this->t('Experience required'),
            '#options' => [
                '' => $this->t('- Select and option -'),
                'junior' => $this->t('Junior'),
                'mid' => $this->t('Mid'),
                'senior' => $this->t('Senior')
            ],
            '#required' => true,
            '#default_value' => $form_state->getValue('experience', '')
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
            '#default_value' => $form_state->getValue('tags', [])
        ];


        $form['comments'] = [
            '#type' => 'textarea',
            '#title' => $this->t('Comments'),
            '#rows' => 4,
            '#maxlength' => 1000,
            '#default_value' => $form_state->getValue('comments', '')
        ];

        $result = $form_state->get('registration_result');

        // Contenedor en el que mostraremos el resultado.
        $form['result'] = [
            '#type' => 'container',
            '#attributes' => [
                'id' => 'courses-registration-result',
            ],
        ];

        if($result !== null){
            $form['result']['summary'] = [
                '#theme' => 'item_list',
                '#title' => $this->t('Course saved'),
                '#items' => [
                    $this->t('Name: @name', ['@name' => $result['name'],]),
                    $this->t('Experience: @experience', ['@experience' => $result['experience'],]),
                    $this->t('Price: @price', [
                        '@price' => $result['sale'] ? $result['sale_price'] : $result['price'],
                    ]),
                ],
            ];
        }

        $form['actions'] = [
            '#type' => 'actions'
        ];

        $form['actions']['submit'] = [
            '#type' => 'submit',
            '#value' => $this->t('Register'),
            '#ajax' => [
                'callback' => '::ajaxRefreshResult',
                'wrapper' => 'course-registration-result'
            ]
        ];

        return $form;
    }
    
    public function validateForm(array &$form, FormStateInterface $form_state)
    {
        $name = trim((string) $form_state->getValue('name'));
        if($name === ''){
            $form_state->setErrorByName('name', $this->t('Name cannot be empty'));
        }

        $price = (int) $form_state->getValue('price');
        if(!filter_var($price, FILTER_VALIDATE_INT)){
            $form_state->setErrorByName('price', $this->t('Price must be a number.'));
        }

        $sale = $form_state->getValue('sale');
        if($sale === true && ((int) $form_state->getValue('sale_price')) === 0){
            $form_state->setErrorByName('sale_price', $this->t('Must add a sale price.'));
        }

        $comments = (string) $form_state->getValue('comments');
        if(strlen($comments) > 1000){
            $form_state->setErrorByName('comments', $this->t('Comments cannot be more than 1000 caracters.'));
        }
    }

    
    public function submitForm(array &$form, FormStateInterface $form_state)
    {
        $data = [
            'name' => $form_state->getValue('name'),
            'price' => $form_state->getValue('price'),
            'sale' => $form_state->getValue('sale'),
            'teacher' => $form_state->getValue('teacher'),
            'duration' => $form_state->getValue('duration'),
            'experience' => $form_state->getValue('experience'),
            'tags' => array_values(array_filter($form_state->getValue('tags') ?? [])),            
            'comments' => $form_state->getValue('comments'),
        ];

        $result = $this->coursesManager->processRegistration($data);

        $form_state->set('registration_result', $result);

        $this->messenger()->addStatus($this->t('Data has been procesed.'));

        $form_state->setRebuild();
    }

    public function ajaxRefreshResult(array &$form, FormStateInterface $form_state,): array {
        return $form['result'];
    }
}