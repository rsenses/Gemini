<?php

namespace App\Validation;

/**
 *
 */
interface ValidatorInterface
{
    public function validate($request, array $rules);

    public function failed();
}
