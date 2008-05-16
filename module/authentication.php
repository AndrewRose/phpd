<?php
/* 
  phpd Copyright (C) 2007, 2008 Andrew Rose
  rose.andrew@gmail.com
  http://andrewrose.co.uk
  http://andrew-rose.blogspot.com

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License as published by
  the Free Software Foundation; either version 2 of the License, or
  (at your option) any later version.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

class Phpd_Module_Authentication implements Phpd_Module
{
	public $phpd;
	private $defaultApplication;

        public function init()
        {
		if(!$this->phpd->reg->exists('_phpd.module.Authentication.application'))
		{
			$this->phpd->log->write('No default application set for Authentication module!');
			return FALSE;
		}

		$this->defaultApplication = $this->phpd->reg->get('_phpd.module.Authentication.application');

		if(!$this->phpd->reg->exists('_phpd.module.Authentication.adaptor'))
		{
			$this->phpd->log->write('No default adaptor set for Authentication module!');
			return FALSE;
		}

		$class = 'Aplc_Auth_Adaptor_'.$this->phpd->reg->get('_phpd.module.Authentication.adaptor');
		if(!Aplc::classExists($class))
		{
			$this->phpd->log->write('Invalid Auth adaptor: '.$class);
			return FALSE;
		}

		$this->auth = new $class;
		$this->auth->init($this->phpd->reg->getReference('_phpd.module.Authentication'));

		return TRUE;
        }

        public function request()
        {
		// if the user has no user object then set them as a guest
		if(!$this->phpd->reg->exists('_phpd.user.id'))
		{
			$this->phpd->reg->set('_phpd.user.id', 0);
			$this->phpd->reg->set('_phpd.user.application', $this->defaultApplication);
		}

		if($this->phpd->reg->get('_phpd.module.Authentication.application') == $this->phpd->reg->get('_phpd.user.application') )
		{
			$this->auth->authenticate();
		}

		return TRUE;
        }

	public function response()
	{
		return TRUE;
	}

        public function cleanup()
        {
		return TRUE;
        }

        public function deinit()
        {
		return TRUE;
        }
}
