<?php

namespace rdx\behatvars;

class BehatVariablesDatabase {

	static protected $instance;

	protected $storage = array();

	/**
	 *
	 */
	public function _get($name) {
		if (!isset($this->storage[$name])) {
			throw new \Exception("'$name' does not exist.");
		}

		return $this->storage[$name];
	}

	/**
	 *
	 */
	public function _set($name, $value) {
		if (!is_scalar($value)) {
			$type = gettype($value);
			throw new \Exception("Storing value must be scalar, but it's a '$type'.");
		}

		$this->storage[$name] = $value;
	}

	/**
	 *
	 */
	public function _all() {
		return $this->storage;
	}

	/**
	 *
	 */
	public function _clear() {
		$this->storage = array();
	}

	/**
	 *
	 */
	static public function instance() {
		if (!self::$instance) {
			self::$instance = new static;
		}

		return self::$instance;
	}

	/**
	 *
	 */
	static public function __callStatic($method, $args) {
		$instance = static::instance();
		return call_user_func_array(array($instance, '_' . $method), $args);
	}

}
