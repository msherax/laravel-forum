<?php namespace Atrakeur\Forum\Models;

use \stdClass;

abstract class AbstractForumBaseModel extends \Eloquent {

	protected function rememberAttribute($item, $function)
	{
		$cacheItem = get_class($this).$this->id.$item;

		$value = \Cache::rememberForever($cacheItem, $function);

		return $value;
	}

	protected static function clearAttributeCache($model)
	{
		foreach ($model->appends as $attribute) {
			$cacheItem = get_class($model).$model->id.$attribute;
			\Cache::forget($cacheItem);
		}
	}

	public function toObject()
	{
		return $this->convertToObject($this);
	}

	public function convertToObject($value)
	{
		if ($value instanceof \Eloquent)
		{
			$attributes = $value->toArray();
			$relations  = $value->relationsToArray();
			
			$object = new stdClass();
			foreach($attributes AS $key => $attribute)
			{
				if (array_key_exists($key, $relations)) 
				{
					$key = camel_case($key);
					$object->$key = $this->convertToObject($value->$key);
				}
				else 
				{
					$object->$key = $attribute;
				}
			}
			return $object;
		}
		
		if ($value instanceof \Illuminate\Database\Eloquent\Collection)
		{
			$array = array();
			foreach($value AS $key => $element)
			{
				$array[$key] = $this->convertToObject($element);
			}
			return $array;
		}
		return $value;
	}

}
