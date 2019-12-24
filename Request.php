<?php
declare(strict_types=1);

namespace app\common;

use think\Request as BaseRequest;
use think\exception\ValidateException;

class Request extends BaseRequest
{
    protected $scene;

    protected $validate;

	public function __construct(array $options = [])
    {
        parent::__construct($options);

        $this->validate = new Validate( $this );

        $this->autoValidate();
    }

    public function getScene() :string
    {
    	$scene = tap(app('request'), function($request){
            return $request->action() ?:
            \Arr::last(explode('/', str_replace('\\', '/', \Arr::get($request->routeInfo(), 'route'))));
        });

        if(strpos($scene, '@') !== false) $scene = \Arr::last(explode('@', $scene));

        return $scene;
    }

    public function getValidate() :Validate
    {
        return $this->validate;
    }

    public function autoValidate()
    {
		$validate = $this->getValidate();

        if ($validate->hasScene($this->getScene())) $validate->scene($this->getScene());

        if (!$validate->check(app('request')->param())) {
            throw new ValidateException($validate->getError());
        }
    }

    public function fields() :array
    {
    	return [];
    }
}
