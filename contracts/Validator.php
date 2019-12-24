<?php

namespace app\common\contracts;

interface Validator
{
    public function rules();

    public function messages();

    public function fields();
}
