<?php
/*
 * Gpio.php
 * 
 * Copyright 2017  <pi@gaia>
 * 
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
 * MA 02110-1301, USA.
 * 
 * TODO : Deleguer l'acces direct au GPIO a une classe modele "GpioAccess"
 * TODO : limite le nombre d'objets possibles en fonction du modele de raspberry
 * 
 */
namespace Syntropia;
require 'GpioAccess.php';

class GpioException extends \Exception
{
	public function __construct($message, $code = 0)
	{
		parent::__construct($message, $code);
	}

	public function __toString()
	{
		return $this->message;
	}
}

class Gpio
{

	private static $_number_of_gpios = 0;
	private $_name;
	private $_pin;

	public function __construct($name, $pin) // $name = string, $pin = int

	{		
		
		// note : ajouter des controles
		$this->_name=$name;
		$this->_pin=$pin;
		
		// instancie un GpioAccess
		$this->_gpioaccess=new GpioAccess($pin);
		
		// incremente le nombre de gpios initialises
		self::$_number_of_gpios = self::$_number_of_gpios + 1 ;
		
	}

	public function __toString()
	{
		return 'name='.$this->_name.', number='.$this->_pin.', state='.$this->getState();
	}
	
	public function setState($state) // attend un booleen

	{
		
		// initialise le parametre a passer a la commande shell selon l'etat
		if ($state)
		{
			$state_parameter='1';
		}
		else
		{
			$state_parameter='0';
		}
		
		// execute la commande shell pour modifier l'etat du gpio
		
		$command = 'gpio -g mode ' . $this->_pin . ' out && gpio -g write ' . $this->_pin . ' ' . $state_parameter ;
		exec($command, $sortie_script, $return_var);
		
		
		// exception en cas d'errorlevel different de 0
		if ($return_var != 0)
		{
			throw new GpioException('Probleme acces GPIO');
		}
		
	}
	
	public function getState()
	
	{
		// recupere l'etat physique du gpio via une commande shell, plutot que de se fier � l'etat de l'objet
		$command = 'gpio -g read ' . $this->_pin;
		exec($command, $sortie_script, $return_var);

		// 
		if ($return_var != 0)
		{
			throw new GpioException('Probleme acces GPIO');
		}
		
		
		// lit la sortie standard de la commande d'etat du GPIO
		foreach($sortie_script as $ligne)
		{
			if ($ligne == '1')
			{
				return true;
			}
			else if ($ligne == '0')
			{
				return false;
			}
		}
		
		// si etat GPIO ni 0 ni 1, retourne -1
		return -1;
		
	}
	
	public static function getNumber()
	
	{
		return self::$_number_of_gpios;
	}
	
}




?>
