<?php
declare(strict_types=1);
namespace Quid\Orm;
use Quid\Main;

// _colCell
trait _colCell
{
	// dynamique
	protected $callback = []; // permet d'entreposer un ou plusieurs committed callback, lors d'une opération réussi
	protected $exception = null; // permet d'entreposer une exception, lors d'une opération raté


	// exception
	// retourne le message de l'exception 
	// si lang est true, retourne le texte indiquant que le champ est requis
	public function exception(bool $lang=false) 
	{
		$return = true;
		$exception = $this->getException();
		
		if(is_array($exception) && array_key_exists('message',$exception) && array_key_exists('messageArgs',$exception))
		{
			$return = $exception['message'];
			
			if($lang === true)
			{
				$lang = $this->db()->lang();
				$message = $exception['messageArgs'];
				
				if(is_array($message))
				{
					$safe = $lang->safe(...array_values($message));
					
					if(is_string($safe))
					$return = $safe;
				}
			}
		}
		
		return $return;
	}
	
	
	// ruleException
	// retourne l'exception si présente
	public function ruleException(bool $lang=false):?string
	{
		$return = $this->exception($lang);
		
		if($return === true)
		$return = null;
		
		return $return;
	}


	// hasCommittedCallback
	// retourne vrai si l'objet contient un commited callback
	public function hasCommittedCallback(string $key):bool
	{
		return (!empty($this->getCommittedCallback($key)))? true:false;
	}
	
	
	// getCommittedCallback
	// retourne le commited callback
	public function getCommittedCallback(string $key):?callable
	{
		return $this->callback[$key] ?? null;
	}
	
	
	// setCommittedCallback
	// ajoute un commited callback
	public function setCommittedCallback(string $key,callable $callback):void 
	{
		$this->callback[$key] = $callback;
		
		return;
	}
	

	// clearCommittedCallback
	// vide le callback à appeler après un commit, insert ou update
	public function clearCommittedCallback():void
	{
		$this->callback = [];
		
		return;
	}
	
	
	// hasException
	// retourne vrai si l'objet contient une exception
	public function hasException():bool
	{
		return (!empty($this->exception))? true:false;
	}
	
	
	// getException
	// retourne le message d'exception lié à l'objet
	public function getException():?array
	{
		return $this->exception;
	}
	
	
	// setException
	// entrepose le message d'exception dans l'objet
	public function setException(Main\CatchableException $exception):void
	{
		$message['message'] = $exception->getMessage();
		$message['messageArgs']  = $exception->messageArgs();
		$this->exception = $message;
		
		return;
	}
	
	
	// clearException
	// vide l'exception lié à une insertion raté
	public function clearException():void
	{
		$this->exception = null;
		
		return;
	}
}
?>