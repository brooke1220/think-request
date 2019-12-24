<?php

namespace app\common;

use ReflectionClass;
use Brooke\Supports\Str;
use app\common\contracts\Validator;

class Validate extends \think\Validate
{
	protected $request;

	public function __construct(Request $request)
	{
		if(! $request instanceof Validator){
			throw new ValidateException('This Request is not an instance of ' . Validator::class);
		}

		$this->request = $request;

		parent::__construct($request->rules(), $request->messages(), $request->fields());

		if($scene = $this->getRequestScene()) $this->setScene($scene);
	}

	public function setScene(array $scene)
	{
		$this->scene = $scene;
	}

	public function buildCustomRuleName($rule)
	{
		return 'custom_rule_' . $rule;
	}

	public function require_custom_rules($value, $rule, $data = [], $field, $title)
	{
		return call_user_func_array(
			[ $this->request, Str::camel($this->buildCustomRuleName($rule)) ],
			compact('value', 'rule', 'data', 'field', 'title')
		);
	}

	public function getRequestScene()
	{
		$scene = [];

		if(! empty($this->request->scene)){
			$scene = array_merge($scene, $this->request->scene);
		}

		if(method_exists($this->request, 'scene')){
			$scene = array_merge($scene, $this->request->scene());
		}

		$methods = array_filter(get_class_methods($this->request), function($method){
			return strchr($method, 'scene') == $method;
		});

		if(! empty($methods)){
			foreach($methods as $method){
				$name = Str::camel(str_replace('scene', '', $method));
				$scene[$name] = $this->request->{$method}();
			}
		}

		return $scene;
	}

	public function __call($method, $args)
	{
		return call_user_func_array([$this->request, $method], $args);
	}
}
