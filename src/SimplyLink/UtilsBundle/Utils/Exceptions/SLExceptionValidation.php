<?php
/**
 * Created by PhpStorm.
 * User: ronfridman
 * Date: 13/04/2017
 * Time: 13:17
 */

namespace Simplylink\UtilsBundle\Utils\Exceptions;


use Symfony\Component\Form\FormInterface;

/**
 * Use this class to create ApiError for any validation errors
 * Easy errors extraction from FORM object - use addFormErrors() function
 *
 * Class ApiErrorValidation
 */
class SLExceptionValidation extends BaseSLException
{
    public function getExceptionCode()
    {
        return 400;
    }
    
    
    public function __construct()
    {
        $title = 'There was a validation error';
        $userText = $title;
        parent::__construct($title,$userText);
    }
    
    public function getExceptionType()
    {
        return 'validation_error';
    }
    
    
    /**
     * Add validation errors from $form into ApiError.
     * The errors will convert into json and return to the requester
     * @param FormInterface $form
     */
    public function addFormErrors(FormInterface $form)
    {
        $errors = $this->getErrorsFromForm($form);
        $this->addAdditionalInfo(['errors' => $errors]);
    }


    /**
     * Convert form errors into array of errors.
     * add error messages and KEY=>VALUE for each validation error fields
     *
     * @param FormInterface $form
     * @return array
     */
    private function getErrorsFromForm(FormInterface $form)
    {
        $errors = array();
        foreach ($form->getErrors() as $error) {
            $errors[] = $error->getMessage();
        }
        foreach ($form->all() as $childForm) {
            if ($childForm instanceof FormInterface) {
                if ($childErrors = $this->getErrorsFromForm($childForm)) {
                    $errors[$childForm->getName()] = $childErrors;
                }
            }
        }
        return $errors;
    }

}