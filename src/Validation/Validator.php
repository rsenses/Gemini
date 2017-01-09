<?php

namespace App\Validation;

use Exception;
use Respect\Validation\Exceptions\NestedValidationException;
use Respect\Validation\Exceptions\ValidationException;

/**
 *
 */
class Validator implements ValidatorInterface
{
    protected $errors = [];

    public function validate($request, array $rules)
    {
        foreach ($rules as $field => $rule) {
            try {
                $param = isset($_FILES[$field]) ? $_FILES[$field]['tmp_name'] : $request->getParam($field);
                $rule->setName(ucfirst($field))->assert($param);
            } catch (NestedValidationException $e) {
                $this->errors[$field] = $e->getMessages();
            }
        }

        $_SESSION['validationErrors'] = $this->errors;

        return $this;
    }

    public function validateArray(array $data, array $rules)
    {
        foreach ($rules as $field => $rule) {
            try {
                $rule->setName(ucfirst($field))->assert($data[$field]);
            } catch (ValidationException $e) {
                throw new Exception($e->getMessage(), 1);
            }
        }
    }

    public function failed()
    {
        return !empty($this->errors);
    }
}
