<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
    
# THE MISSING MODEL: A small, chainable model class for CodeIgniter, loosely inspired by jQuery
# 
# MIT Licensed: http://opensource.org/licenses/MIT
# https://github.com/lukifer/TheMissingModel


if (!class_exists('CI_Model')) require_once (BASEPATH . 'core/Model.php');

class MY_Model extends CI_Model {
	
	public $table = ''; # required in subclass
	public $primaryKey = 'id';
	
	public $data = array();
	public $validation = array();
	public $errors = array();
	
	# Convenience functions
	public function count()		{ return count($this->data);		}
	public function hasData()	{ return count($this->data) > 0;	}
	
	
	# Populate internal data with manual SQL 
	public function sql($queryString)
	{
		$sql = $this->db->query($queryString);

		if(!$sql->num_rows())
			$this->data = array();
		else
			$this->data = $sql->result_array();
		
		return $this;
	}
	
	
	# Simple WHERE + LEFT JOIN. More complicated queries should use ->sql(), or custom functions
	public function query($where, $select = '*', $join = array())
	{
		# If we're joining, assume that we don't want a protected SELECT statement
		$this->db->select($select, empty($join));
	
		if(is_numeric($where))
			$this->db->where($this->table.'.'.$this->primaryKey, $where)->limit(1);

		else if(is_array($where) && !empty($where))
			foreach($where as $k => &$v)
			{
				if(!strchr($k, '.')) $k = $this->table.'.'.$k;
				$this->db->where($k, $v);
			}
		
		# Quick and dirty joining. Not recommended for complex queries.
		if(!empty($join))
		{	if(!is_array($join)) $join = array($join);
			foreach($join as $k => &$j)
				$this->db->join($k, $j, 'left');
		}		
		
		$sql = $this->db->get($this->table);
		$this->data = $sql->result_array();

		return $this;
	}
	
	
	# Retrieve all internal data
	public function getAll()
	{
		return $this->get();
	}
	
	
	# Get a specific item
	public function get($k)
	{
		if(empty($this->data)) return false;
	
		else if(isset($this->data[$k]))
			return $this->data[$k];
		
		else return false;
	}
	
	
	# Get the next item, and increment counter. Useful for looping.
	public function getOne($first = false)
	{
		if(empty($this->data)) return false;
	
		if($first) reset($this->data);
		return current($this->data);
	}
	
	
	public function rewind()
	{
		if(empty($this->data)) return false;
		
		reset($this->data);
		
		return $this;
	}
	
	
	# Get all results as an associative array, indexed by primary key or the specified field name
	public function getIndexed($keyField = false)
	{
		if($keyField === false) $keyField = $this->primaryKey;
		return $this->indexArray($this->data, $keyField);
	}
	
	
	# Utility function to create an associative array. Does not affect internal data.
	public function indexArray($array, $field = false)
	{
		$indexed = array();
		if($field === false) $field = $this->primaryKey;
	
		if(!empty($array)) foreach($array as &$item)
			$indexed[$item[$field]] = $item;
		
		return $indexed;
	}
	

	# Create a key-value store, such as for building <option> elements. Does not affect internal data. 
	public function getKeyValue($nameField = 'name', $keyField = false)
	{	
		if($keyField === false) $keyField = $this->primaryKey;
		return $this->createKeyValue($this->data, $nameField, $keyField);
	}


	# Utility function to create a key-value store. 
	public function keyValue($array, $nameField = 'name', $keyField = false)
	{	
		if($keyField === false) $keyField = $this->primaryKey;
		
		$newArray = array();
		if(!empty($array)) foreach($array as $k => &$a)
		{
			$key = $a[$keyField];
			if(empty($key)) $newArray[$k] = $a[$nameField];
			else $newArray[$key] = $a[$nameField];
		}
		return $newArray;
	}
	
	
	# Get a value from the first item in internal data
	public function value($field)
	{
		if($this->count() < 1) return null;
		
		$current = current($this->data);
		if(empty($current)) return false;
		
		return isset($current[$field]) ? $current[$field] : false;
	}


	# Set a value on the first item in internal data
	public function setValue($field, $val)
	{
		if($this->count() < 1) return $this;

		$key = key($this->data);
		if(@empty($this->data[$key])) return $this;
		
		$this->data[$key][$field] = $val;
		
		return $this;
	}


	# Save internal data to database, doing an INSERT or UPDATE based on presence of primary key
	# If only some fields should be saved, pass the field keys in the first argument
	public function save($fields = false, $primaryKey = false)
	{
		if($primaryKey === false) $primaryKey = $this->primaryKey;

		# If passed a string (single field), convert it to an array
		if($fields !== false && !is_array($fields))
			$fields = array($fields);

		# Loop through internal data, saving each
		if(!empty($this->data)) foreach($this->data as $datumIndex => $datum)
		{
			# No fields provided: save all data
			if(!$fields)
			{
				$fields = array();
				foreach($datum as $k => $v)
					if($k != $primaryKey) $fields[] = $k;
				if(empty($fields)) continue;
			}
	
			# Otherwise, save only the requested field(s)
			foreach($fields as &$f)
				if(isset($datum[$f])) $this->db->set($f, $datum[$f]);

			# No primary key: INSERT, and if successful, save the resulting primary key
			if(!isset($datum[$primaryKey]))
			{	
				$this->db->insert($this->table, $datum);
				
				if($this->db->affected_rows() == 1)
					$this->data[$datumIndex][$primaryKey] = $this->db->insert_id();
			}
			
			# Primary key present: UPDATE instead
			else
			{
				$this->db->where($primaryKey, $datum[$primaryKey]);
				$this->db->update($this->table);
		
				# Unfortunately, mysql_affected_rows() can't be trusted with UPDATE results,
				# so currently there is no error handling here.
			}
		}
		
		return $this;
	}
	
	
	# Replace internal data with single associative array, and add new row to database
	public function insert($datum, $primaryKey = false)
	{
		if($primaryKey === false) $primaryKey = $this->primaryKey;

		$this->data = array($datum);
		$this->save();
		
		$id = $this->db->insert_id();
		$this->setValue($primaryKey, $id);
		
		return $this;
	}


	# Simple wrapper for CI's form validation, entirely optional
	# Allows rules to be set in the model ($this->validation), rather than the controller
	public function validate()
	{
		if(empty($this->validation)) return true;
	
		$this->load->library('form_validation');
		$this->form_validation->set_rules($this->validation);
		
		return $this->form_validation->run();
	}
	
	
	# No errors equals success, even if no internal data is present
	public function success()
	{
		return !(count($this->errors) > 0);
	}
	
	
	# Push an error string onto the stack
	public function error($errorString)
	{
		$this->errors[] = $errorString;
		return $this;
	}

	
	# Kill existing data with fire
	public function clear()
	{
		$this->data = array();
		$this->errors = array();
		
		return $this;
	}
	
	# Erase internal errors
	public function clearErrors()
	{
		$this->errors = array();
		return $this;
	}
}

# END class MY_Model